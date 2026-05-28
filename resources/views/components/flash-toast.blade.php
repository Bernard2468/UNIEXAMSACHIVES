{{--
    Global toast system — server flash + programmatic API.

    ARCHITECTURE — bulletproof by design:
      • The toast stack <div> is ALWAYS in the DOM (even with zero initial
        toasts), so client-side JS can append at any time.
      • Initial toasts (from session flash + $errors) are server-rendered
        as static HTML, so they're visible even if JS is broken or blocked.
      • Visibility is CSS-only (animation + animation-fill-mode: both).
        If JS never runs the toast still ends up visible.
      • Programmatic API: window.toast(type, message, opts?)
        - Use from ANY JS code: AJAX callbacks, button handlers, push events.
        - Dedupes within 3s so retries don't spam.
        - Caps the visible stack at 5; oldest auto-dismiss when over.
      • Bottom-right position (Slack / Linear / Vercel convention).
      • Reduced-motion respected.

    Public API:
      window.toast('success', 'Saved!')
      window.toast('error', 'Network failed', { duration: 10000 })
      window.toast({ type, message, title, duration })
      window.toast.dismissAll()
      window.toast.test()    // shows one of each type — handy for QA
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
    $iconFor = fn (string $t) => match($t) {
        'success' => '✓',
        'error'   => '!',
        'warning' => '!',
        'info'    => 'i',
        default   => 'i',
    };
@endphp

<style>
    /* =================================================================
       BOTTOM-RIGHT STACK — Slack / Linear / Vercel convention.
       Stack is bottom-anchored; new toasts append to the bottom of the
       flex column so the newest sits nearest the corner where the user's
       eye lands. Older ones float up as the column grows.
       ================================================================= */
    .flash-toast-stack {
        position: fixed;
        bottom: 24px;
        right: 24px;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 400px;
        width: max-content;
        /* Max signed-32-bit int — guaranteed above the preloader (999999)
           and any third-party overlay. */
        z-index: 2147483647;
        pointer-events: none;
    }

    .flash-toast {
        pointer-events: auto;
        display: flex; align-items: flex-start; gap: 12px;
        padding: 13px 16px; padding-right: 38px;
        min-width: 280px;
        max-width: 400px;
        background: #ffffff;
        border-radius: 12px;
        border-left: 4px solid #2563eb;
        box-shadow:
            0 14px 36px rgba(15, 23, 42, 0.22),
            0 4px 10px rgba(15, 23, 42, 0.10),
            0 0 0 1px rgba(15, 23, 42, 0.04);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13.5px; line-height: 1.45;
        color: #1f2937;
        position: relative;
        /* CSS-driven slide-in. animation-fill-mode: both retains the final
           state, so even if JS NEVER runs the toast ends up visible. */
        opacity: 0; transform: translateY(20px);
        animation: flashToastIn 0.36s cubic-bezier(0.16, 1, 0.3, 1) both;
        will-change: transform, opacity;
    }
    @keyframes flashToastIn {
        from { opacity: 0; transform: translateY(20px) scale(0.96); }
        to   { opacity: 1; transform: translateY(0)    scale(1);    }
    }
    /* Leave: collapse the box so neighbours fall smoothly into the gap. */
    .flash-toast.flash-toast--leave {
        animation: flashToastOut 0.28s cubic-bezier(0.4, 0, 1, 1) both;
        overflow: hidden;
        pointer-events: none;
    }
    @keyframes flashToastOut {
        0%   { opacity: 1; transform: translateY(0) scale(1);    max-height: 240px; margin-top: 0;    }
        100% { opacity: 0; transform: translateY(8px) scale(0.97); max-height: 0;   margin-top: -10px; }
    }

    .flash-toast__icon {
        flex: 0 0 22px; width: 22px; height: 22px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; color: #fff; font-weight: 700;
        font-size: 13px;
        margin-top: 1px;
    }
    .flash-toast__body { flex: 1; min-width: 0; }
    .flash-toast__title { font-weight: 600; margin: 0 0 2px; color: #0f172a; font-size: 13px; }
    .flash-toast__msg { margin: 0; word-wrap: break-word; color: #374151; }
    .flash-toast__close {
        position: absolute; top: 8px; right: 10px;
        background: transparent; border: 0; cursor: pointer;
        color: #9ca3af; font-size: 20px; line-height: 1;
        padding: 2px 4px;
        border-radius: 4px;
        transition: color .15s, background .15s;
    }
    .flash-toast__close:hover { color: #1f2937; background: rgba(15, 23, 42, 0.05); }

    .flash-toast--success { border-left-color: #16a34a; }
    .flash-toast--success .flash-toast__icon { background: #16a34a; }
    .flash-toast--error   { border-left-color: #dc2626; }
    .flash-toast--error   .flash-toast__icon { background: #dc2626; }
    .flash-toast--warning { border-left-color: #d97706; }
    .flash-toast--warning .flash-toast__icon { background: #d97706; }
    .flash-toast--info    { border-left-color: #2563eb; }
    .flash-toast--info    .flash-toast__icon { background: #2563eb; }

    /* Mobile — stretch close to the edges with safe-area insets. */
    @media (max-width: 540px) {
        .flash-toast-stack {
            left: 12px; right: 12px; bottom: 12px;
            bottom: calc(12px + env(safe-area-inset-bottom));
            max-width: none; width: auto;
        }
        .flash-toast { min-width: 0; max-width: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .flash-toast { animation: none; opacity: 1; transform: none; }
        .flash-toast.flash-toast--leave { animation: none; opacity: 0; }
    }
</style>

{{-- ALWAYS-PRESENT STACK. Even when there are no initial toasts, the empty
     stack must exist so window.toast() can append into it later. --}}
<div class="flash-toast-stack" id="flashToastStack" aria-live="polite" aria-atomic="false">
    @foreach($initialToasts as $i => $t)
        <div class="flash-toast flash-toast--{{ $t['type'] }}" role="status" data-toast-source="server">
            <span class="flash-toast__icon" aria-hidden="true">{{ $iconFor($t['type']) }}</span>
            <div class="flash-toast__body">
                <p class="flash-toast__title">{{ $titleFor($t['type']) }}</p>
                <p class="flash-toast__msg">{{ $t['message'] }}</p>
            </div>
            <button type="button" class="flash-toast__close" aria-label="Dismiss">&times;</button>
        </div>
    @endforeach
</div>

<script>
(function () {
    /* =================================================================
       Toast engine. Designed so:
         1. Server-rendered toasts work even if THIS script never runs
            (CSS animation + animation-fill-mode handle visibility).
         2. JS-created toasts (via window.toast(...)) go through the
            SAME setup() pipeline as server-rendered ones — single code path.
         3. Dedup window prevents spam from rapid identical calls.
         4. Stack limit auto-dismisses oldest when over 5 visible.
       ================================================================= */
    var STACK_ID    = 'flashToastStack';
    var STACK_LIMIT = 5;
    var DEDUP_MS    = 3000;
    var TITLES = {
        success: 'Success',
        error:   'Something went wrong',
        warning: 'Heads up',
        info:    'Info'
    };
    var ICONS = { success: '✓', error: '!', warning: '!', info: 'i' };
    var VALID_TYPES = ['success', 'error', 'warning', 'info'];

    var recent = new Map();   // dedup tracker (type+message → timestamp)

    function getStack() {
        return document.getElementById(STACK_ID);
    }

    function escapeHtml(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
        });
    }

    function shouldShow(type, message) {
        var key = type + '' + message;
        var now = Date.now();
        var last = recent.get(key);
        if (last && (now - last) < DEDUP_MS) return false;
        recent.set(key, now);
        // Trim old entries so the map doesn't grow unbounded.
        if (recent.size > 64) {
            recent.forEach(function (t, k) {
                if (now - t > 30000) recent.delete(k);
            });
        }
        return true;
    }

    function enforceLimit(stack) {
        var visible = stack.querySelectorAll('.flash-toast:not(.flash-toast--leave)');
        var over = visible.length - STACK_LIMIT;
        for (var i = 0; i < over; i++) {
            // Drop oldest (first child of the flex column).
            dismiss(visible[i]);
        }
    }

    function dismiss(toast) {
        if (!toast || toast.classList.contains('flash-toast--leave')) return;
        toast.classList.add('flash-toast--leave');
        // Fallback removal if 'animationend' never fires (e.g. reduced motion).
        var removeT = setTimeout(function () { if (toast.parentNode) toast.parentNode.removeChild(toast); }, 600);
        toast.addEventListener('animationend', function onEnd() {
            clearTimeout(removeT);
            if (toast.parentNode) toast.parentNode.removeChild(toast);
        }, { once: true });
    }

    function setup(toast, opts) {
        opts = opts || {};
        var isLongLived = toast.classList.contains('flash-toast--error') || toast.classList.contains('flash-toast--warning');
        var duration = typeof opts.duration === 'number' ? opts.duration : (isLongLived ? 6500 : 5000);
        var timer = duration > 0 ? setTimeout(function () { dismiss(toast); }, duration) : null;

        var closeBtn = toast.querySelector('.flash-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function (e) {
                e.stopPropagation();
                if (timer) clearTimeout(timer);
                dismiss(toast);
            });
        }
        toast.addEventListener('mouseenter', function () { if (timer) clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () {
            if (duration > 0) timer = setTimeout(function () { dismiss(toast); }, 2500);
        });
    }

    function create(type, message, opts) {
        opts = opts || {};
        var stack = getStack();
        if (!stack) return null;

        if (VALID_TYPES.indexOf(type) === -1) type = 'info';
        if (!shouldShow(type, message)) return null;

        var title = opts.title || TITLES[type] || 'Notice';
        var html =
            '<span class="flash-toast__icon" aria-hidden="true">' + escapeHtml(ICONS[type] || 'i') + '</span>' +
            '<div class="flash-toast__body">' +
                '<p class="flash-toast__title">' + escapeHtml(title) + '</p>' +
                '<p class="flash-toast__msg">' + escapeHtml(message) + '</p>' +
            '</div>' +
            '<button type="button" class="flash-toast__close" aria-label="Dismiss">&times;</button>';

        var toast = document.createElement('div');
        toast.className = 'flash-toast flash-toast--' + type;
        toast.setAttribute('role', type === 'error' || type === 'warning' ? 'alert' : 'status');
        toast.setAttribute('data-toast-source', 'js');
        toast.innerHTML = html;

        stack.appendChild(toast);
        setup(toast, opts);
        enforceLimit(stack);
        return toast;
    }

    // -------- PUBLIC API ----------------------------------------------
    function api(typeOrOpts, message, opts) {
        if (typeOrOpts && typeof typeOrOpts === 'object' && typeOrOpts.message) {
            return create(typeOrOpts.type || 'info', String(typeOrOpts.message), typeOrOpts);
        }
        // toast('Just a message') → assume success
        if (typeof typeOrOpts === 'string' && message === undefined) {
            return create('success', typeOrOpts, opts);
        }
        return create(String(typeOrOpts || 'info'), String(message || ''), opts);
    }
    api.dismissAll = function () {
        var stack = getStack(); if (!stack) return;
        stack.querySelectorAll('.flash-toast').forEach(dismiss);
    };
    api.test = function () {
        ['success', 'info', 'warning', 'error'].forEach(function (t, i) {
            setTimeout(function () { api(t, t.charAt(0).toUpperCase() + t.slice(1) + ' — toast working ✓'); }, i * 350);
        });
    };
    // Short aliases used by some teams' conventions — both call through.
    api.success = function (m, o) { return api('success', m, o); };
    api.error   = function (m, o) { return api('error',   m, o); };
    api.warning = function (m, o) { return api('warning', m, o); };
    api.info    = function (m, o) { return api('info',    m, o); };

    window.toast = api;

    // Attach behaviour to server-rendered toasts (they're already visible).
    function attachInitial() {
        var stack = getStack();
        if (!stack) return;
        stack.querySelectorAll('.flash-toast').forEach(function (t) { setup(t, {}); });
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', attachInitial);
    } else {
        attachInitial();
    }
})();
</script>
