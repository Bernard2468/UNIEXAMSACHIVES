{{--
    Global toast system — server flash + programmatic API.
    Premium glassmorphism design (Sonner / Linear / Vercel aesthetic).

    DESIGN NOTES
      • No left-border accent stripe (user feedback). The type is signalled
        by (a) the colored icon halo and (b) a thin gradient progress bar
        across the bottom.
      • CSS owns both visibility AND timing. The progress bar's animation
        duration IS the toast lifespan; when it finishes (animationend), the
        toast dismisses itself. Hover pauses the animation → pauses the timer
        automatically. No CSS/JS desync possible.
      • Dark slate background regardless of page theme — toasts read as a
        distinct "system overlay" the way top SaaS apps do.
      • All server-rendered AND JS-created toasts go through the same
        setup() pipeline.

    Public API (unchanged):
      window.toast('success', 'Saved!')
      window.toast('error', 'Network failed', { duration: 10000 })
      window.toast({ type, message, title, duration })
      window.toast.dismissAll()
      window.toast.test()
--}}
@php
    $initialToasts = [];

    foreach (['success', 'error', 'warning', 'info'] as $key) {
        $msg = session($key);
        if (is_string($msg) && $msg !== '') {
            $initialToasts[] = ['type' => $key, 'message' => $msg];
        }
    }

    if (isset($errors) && $errors->any()) {
        foreach ($errors->all() as $err) {
            $initialToasts[] = ['type' => 'error', 'message' => $err];
        }
    }

    $titleFor = fn (string $t) => match($t) {
        'success' => 'Success',
        'error'   => 'Something went wrong',
        'warning' => 'Heads up',
        'info'    => 'Info',
        default   => 'Notice',
    };
@endphp

<style>
    /* ============================================================
       STACK — bottom-right, newest near the corner
       ============================================================ */
    .flash-toast-stack {
        position: fixed;
        bottom: 24px;
        right: 24px;
        display: flex;
        flex-direction: column;
        gap: 12px;
        max-width: 420px;
        width: max-content;
        z-index: 2147483647;
        pointer-events: none;
    }

    /* ============================================================
       TOAST CARD — dark glassmorphism, no accent stripe
       ============================================================ */
    .flash-toast {
        --toast-lifespan: 5000ms;
        --toast-accent: #6366f1;
        --toast-accent-glow: rgba(99, 102, 241, 0.45);

        pointer-events: auto;
        position: relative;
        display: flex;
        align-items: flex-start;
        gap: 13px;
        padding: 14px 40px 16px 16px;
        min-width: 320px;
        max-width: 420px;

        color: #f1f5f9;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, system-ui, sans-serif;
        font-size: 13.5px; line-height: 1.45;
        letter-spacing: -0.005em;

        /* Layered dark glass + saturate boost so it stays vivid on any
           page background. */
        background: linear-gradient(180deg, rgba(15, 23, 42, 0.94) 0%, rgba(2, 6, 23, 0.97) 100%);
        -webkit-backdrop-filter: blur(24px) saturate(180%);
        backdrop-filter: blur(24px) saturate(180%);

        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 14px;
        overflow: hidden;

        box-shadow:
            0 24px 60px rgba(0, 0, 0, 0.45),
            0 8px 18px rgba(0, 0, 0, 0.25),
            0 0 0 1px var(--toast-accent-glow),     /* type-tinted edge */
            inset 0 1px 0 rgba(255, 255, 255, 0.06), /* top highlight */
            inset 0 0 0 1px rgba(255, 255, 255, 0.02);

        opacity: 1;
        transform: translateY(0) scale(1);
        animation: flashToastIn 0.48s cubic-bezier(0.34, 1.56, 0.64, 1) backwards;
        will-change: transform, opacity;
        transition: transform 0.22s ease, box-shadow 0.22s ease;
    }
    .flash-toast:hover {
        transform: translateY(-2px) scale(1.005);
        box-shadow:
            0 28px 70px rgba(0, 0, 0, 0.5),
            0 10px 22px rgba(0, 0, 0, 0.3),
            0 0 0 1px var(--toast-accent-glow),
            inset 0 1px 0 rgba(255, 255, 255, 0.08);
    }
    @keyframes flashToastIn {
        from { opacity: 0; transform: translateY(28px) scale(0.92); filter: blur(6px); }
        to   { opacity: 1; transform: translateY(0)    scale(1);    filter: blur(0);   }
    }

    /* Leave: collapse smoothly so the stack falls into place. */
    .flash-toast.flash-toast--leave {
        animation: flashToastOut 0.32s cubic-bezier(0.4, 0, 1, 1) both;
        pointer-events: none;
    }
    @keyframes flashToastOut {
        0%   { opacity: 1; transform: translateY(0)  scale(1);    max-height: 240px; margin-top: 0;    }
        100% { opacity: 0; transform: translateY(10px) scale(0.96); max-height: 0;   margin-top: -12px; }
    }

    /* ============================================================
       ICON — circular halo with type color + soft outer glow
       ============================================================ */
    .flash-toast__icon {
        flex: 0 0 32px;
        width: 32px; height: 32px;
        border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        color: #fff;
        position: relative;
        margin-top: 1px;
        box-shadow:
            0 0 24px var(--toast-accent-glow),
            0 2px 8px rgba(0, 0, 0, 0.25),
            inset 0 1px 0 rgba(255, 255, 255, 0.25);
    }
    .flash-toast__icon svg {
        width: 16px; height: 16px;
        display: block;
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.2));
    }
    /* Subtle pulse on the icon's outer ring while the toast is alive. */
    .flash-toast__icon::after {
        content: '';
        position: absolute; inset: -2px;
        border-radius: 50%;
        border: 1px solid var(--toast-accent-glow);
        opacity: 0.6;
        animation: flashToastIconPulse 2.2s ease-out infinite;
    }
    @keyframes flashToastIconPulse {
        0%   { transform: scale(1);    opacity: 0.6; }
        70%  { transform: scale(1.45); opacity: 0;   }
        100% { transform: scale(1.45); opacity: 0;   }
    }

    /* ============================================================
       TEXT
       ============================================================ */
    .flash-toast__body { flex: 1; min-width: 0; }
    .flash-toast__title {
        font-weight: 600;
        font-size: 13px;
        color: #f8fafc;
        margin: 0 0 3px;
        letter-spacing: -0.01em;
    }
    .flash-toast__msg {
        margin: 0;
        color: #cbd5e1;
        font-size: 12.5px;
        word-wrap: break-word;
        line-height: 1.5;
    }

    /* ============================================================
       CLOSE BUTTON
       ============================================================ */
    .flash-toast__close {
        position: absolute; top: 10px; right: 11px;
        width: 22px; height: 22px;
        background: rgba(255, 255, 255, 0.04);
        border: 1px solid rgba(255, 255, 255, 0.06);
        cursor: pointer;
        color: #94a3b8;
        font-size: 16px; line-height: 1;
        padding: 0;
        border-radius: 6px;
        display: inline-flex; align-items: center; justify-content: center;
        transition: color 0.15s, background 0.15s, border-color 0.15s;
    }
    .flash-toast__close:hover {
        color: #f1f5f9;
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.14);
    }

    /* ============================================================
       PROGRESS BAR — CSS-driven, IS the auto-dismiss timer
       ============================================================ */
    .flash-toast__progress {
        position: absolute;
        bottom: 0; left: 0;
        height: 2px;
        width: 100%;
        background: linear-gradient(90deg,
            transparent 0%,
            var(--toast-accent) 30%,
            var(--toast-accent) 70%,
            transparent 100%);
        opacity: 0.85;
        transform-origin: left center;
        animation: flashToastProgress var(--toast-lifespan) linear forwards;
        box-shadow: 0 0 8px var(--toast-accent-glow);
    }
    @keyframes flashToastProgress {
        from { transform: scaleX(1); }
        to   { transform: scaleX(0); }
    }
    .flash-toast:hover .flash-toast__progress { animation-play-state: paused; }
    .flash-toast--persist .flash-toast__progress { display: none; }

    /* ============================================================
       TYPE COLOR PALETTES (only the icon halo + progress bar)
       ============================================================ */
    .flash-toast--success {
        --toast-accent: #10b981;
        --toast-accent-glow: rgba(16, 185, 129, 0.42);
    }
    .flash-toast--success .flash-toast__icon { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }

    .flash-toast--error {
        --toast-accent: #ef4444;
        --toast-accent-glow: rgba(239, 68, 68, 0.45);
    }
    .flash-toast--error .flash-toast__icon { background: linear-gradient(135deg, #f87171 0%, #dc2626 100%); }

    .flash-toast--warning {
        --toast-accent: #f59e0b;
        --toast-accent-glow: rgba(245, 158, 11, 0.45);
    }
    .flash-toast--warning .flash-toast__icon { background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%); }

    .flash-toast--info {
        --toast-accent: #6366f1;
        --toast-accent-glow: rgba(99, 102, 241, 0.45);
    }
    .flash-toast--info .flash-toast__icon { background: linear-gradient(135deg, #818cf8 0%, #4f46e5 100%); }

    /* ============================================================
       MOBILE — stretch to edges, respect safe-area
       ============================================================ */
    @media (max-width: 540px) {
        .flash-toast-stack {
            left: 12px; right: 12px;
            bottom: calc(12px + env(safe-area-inset-bottom));
            max-width: none; width: auto;
        }
        .flash-toast { min-width: 0; max-width: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .flash-toast { animation: none; opacity: 1; transform: none; filter: none; }
        .flash-toast:hover { transform: none; }
        .flash-toast__icon::after { animation: none; }
        .flash-toast__progress { animation: none; transform: scaleX(0); }
    }
</style>

{{-- ALWAYS-PRESENT STACK so window.toast() can append at any time. --}}
<div class="flash-toast-stack" id="flashToastStack" aria-live="polite" aria-atomic="false">
    @foreach($initialToasts as $i => $t)
        <div class="flash-toast flash-toast--{{ $t['type'] }}" role="{{ in_array($t['type'], ['error','warning'], true) ? 'alert' : 'status' }}" data-toast-source="server">
            <span class="flash-toast__icon" aria-hidden="true">
                @switch($t['type'])
                    @case('success')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        @break
                    @case('error')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        @break
                    @case('warning')
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        @break
                    @default
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                @endswitch
            </span>
            <div class="flash-toast__body">
                <p class="flash-toast__title">{{ $titleFor($t['type']) }}</p>
                <p class="flash-toast__msg">{{ $t['message'] }}</p>
            </div>
            <button type="button" class="flash-toast__close" aria-label="Dismiss">&times;</button>
            <span class="flash-toast__progress" aria-hidden="true"></span>
        </div>
    @endforeach
</div>

<script>
(function () {
    var STACK_ID    = 'flashToastStack';
    var STACK_LIMIT = 5;
    var DEDUP_MS    = 3000;
    var DEFAULT_DURATION  = 5000;
    var LONG_DURATION     = 6500;
    var TITLES = {
        success: 'Success', error: 'Something went wrong',
        warning: 'Heads up', info: 'Info',
    };
    var ICONS_SVG = {
        success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
        error:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
        warning: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>',
        info:    '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    };
    var VALID_TYPES = ['success', 'error', 'warning', 'info'];
    var recent = new Map();

    function getStack() { return document.getElementById(STACK_ID); }
    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
    }
    function shouldShow(type, message) {
        var key = type + '' + message;
        var now = Date.now();
        var last = recent.get(key);
        if (last && (now - last) < DEDUP_MS) return false;
        recent.set(key, now);
        if (recent.size > 64) recent.forEach(function (t, k) { if (now - t > 30000) recent.delete(k); });
        return true;
    }
    function enforceLimit(stack) {
        var visible = stack.querySelectorAll('.flash-toast:not(.flash-toast--leave)');
        var over = visible.length - STACK_LIMIT;
        for (var i = 0; i < over; i++) dismiss(visible[i]);
    }
    function dismiss(toast) {
        if (!toast || toast.classList.contains('flash-toast--leave')) return;
        toast.classList.add('flash-toast--leave');
        var removeT = setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 600);
        toast.addEventListener('animationend', function () {
            clearTimeout(removeT);
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, { once: true });
    }
    /* CSS-driven timing. The progress bar's animationend IS the auto-dismiss.
       Hover pauses the CSS animation, which automatically pauses the timer.
       Zero possibility of CSS/JS desync. */
    function setup(toast, opts) {
        opts = opts || {};
        var isLong = toast.classList.contains('flash-toast--error') || toast.classList.contains('flash-toast--warning');
        var duration = typeof opts.duration === 'number' ? opts.duration : (isLong ? LONG_DURATION : DEFAULT_DURATION);

        if (duration > 0) {
            toast.style.setProperty('--toast-lifespan', duration + 'ms');
            var progress = toast.querySelector('.flash-toast__progress');
            if (progress) {
                // animationend on the progress bar IS the dismiss signal.
                progress.addEventListener('animationend', function () { dismiss(toast); }, { once: true });
            }
        } else {
            toast.classList.add('flash-toast--persist');
        }

        var closeBtn = toast.querySelector('.flash-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                dismiss(toast);
            });
        }
    }
    function create(type, message, opts) {
        opts = opts || {};
        var stack = getStack();
        if (!stack) return null;
        if (VALID_TYPES.indexOf(type) === -1) type = 'info';
        if (!shouldShow(type, message)) return null;

        var title = opts.title || TITLES[type] || 'Notice';
        var html =
            '<span class="flash-toast__icon" aria-hidden="true">' + (ICONS_SVG[type] || ICONS_SVG.info) + '</span>' +
            '<div class="flash-toast__body">' +
                '<p class="flash-toast__title">' + escapeHtml(title) + '</p>' +
                '<p class="flash-toast__msg">' + escapeHtml(message) + '</p>' +
            '</div>' +
            '<button type="button" class="flash-toast__close" aria-label="Dismiss">&times;</button>' +
            '<span class="flash-toast__progress" aria-hidden="true"></span>';

        var toast = document.createElement('div');
        toast.className = 'flash-toast flash-toast--' + type;
        toast.setAttribute('role', (type === 'error' || type === 'warning') ? 'alert' : 'status');
        toast.setAttribute('data-toast-source', 'js');
        toast.innerHTML = html;

        stack.appendChild(toast);
        setup(toast, opts);
        enforceLimit(stack);
        return toast;
    }

    function api(typeOrOpts, message, opts) {
        if (typeOrOpts && typeof typeOrOpts === 'object' && typeOrOpts.message) {
            return create(typeOrOpts.type || 'info', String(typeOrOpts.message), typeOrOpts);
        }
        if (typeof typeOrOpts === 'string' && message === undefined) {
            return create('success', typeOrOpts, opts);
        }
        return create(String(typeOrOpts || 'info'), String(message || ''), opts);
    }
    api.dismissAll = function () { var s = getStack(); if (s) s.querySelectorAll('.flash-toast').forEach(dismiss); };
    api.test = function () {
        ['success', 'info', 'warning', 'error'].forEach(function (t, i) {
            setTimeout(function () { api(t, t.charAt(0).toUpperCase() + t.slice(1) + ' — toast working ✓'); }, i * 380);
        });
    };
    api.success = function (m, o) { return api('success', m, o); };
    api.error   = function (m, o) { return api('error',   m, o); };
    api.warning = function (m, o) { return api('warning', m, o); };
    api.info    = function (m, o) { return api('info',    m, o); };
    window.toast = api;

    // Diagnostic log — kept for future "toast not showing" issues.
    var initialCount = @json(count($initialToasts));
    var initialPayload = @json($initialToasts);
    console.log('[Toast]', initialCount, 'server-rendered toast(s) on this page', initialPayload);

    function attachInitial() {
        var stack = getStack(); if (!stack) return;
        stack.querySelectorAll('.flash-toast').forEach(function (t) { setup(t, {}); });
    }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', attachInitial);
    else attachInitial();
})();
</script>
