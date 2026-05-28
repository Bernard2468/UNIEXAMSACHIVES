{{--
    Global flash → toast renderer.
    Reads session('success'|'error'|'warning'|'info') and the validation
    $errors bag, then renders animated, auto-dismissing toasts.

    Visibility is driven by CSS animation (`animation-fill-mode: both`), NOT
    by a JS class. So even if JavaScript fails to run, the toast is visible.
    JS is only responsible for auto-dismiss and the close button — the
    "always show" guarantee belongs to the CSS.
--}}
@php
    $toasts = [];

    foreach (['success', 'error', 'warning', 'info'] as $key) {
        $msg = session($key);
        if (is_string($msg) && $msg !== '') {
            $toasts[] = ['type' => $key, 'message' => $msg];
        }
    }

    if (isset($errors) && $errors->any()) {
        foreach ($errors->all() as $err) {
            $toasts[] = ['type' => 'error', 'message' => $err];
        }
    }
@endphp

@if(!empty($toasts))
<style>
    .flash-toast-stack {
        position: fixed; top: 24px; right: 24px;
        display: flex; flex-direction: column; gap: 10px;
        /* Max int — guaranteed above the preloader (z-index 999999) and
           anything else on the page. */
        z-index: 2147483647;
        max-width: 380px;
        pointer-events: none;
    }
    .flash-toast {
        pointer-events: auto;
        display: flex; align-items: flex-start; gap: 12px;
        padding: 12px 14px; padding-right: 36px;
        background: #fff;
        border-radius: 10px;
        border-left: 4px solid #2563eb;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.18), 0 2px 6px rgba(15, 23, 42, 0.08);
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        font-size: 13.5px; line-height: 1.45;
        color: #1f2937;
        position: relative;
        /* CSS-driven slide-in. `animation-fill-mode: both` retains the final
           state. If JS never runs, the toast still ends up visible. */
        opacity: 0; transform: translateX(20px);
        animation: flashToastIn 0.32s cubic-bezier(0.16, 1, 0.3, 1) both;
        animation-delay: 0.08s;
    }
    .flash-toast:nth-child(2) { animation-delay: 0.16s; }
    .flash-toast:nth-child(3) { animation-delay: 0.24s; }
    .flash-toast:nth-child(4) { animation-delay: 0.32s; }
    .flash-toast:nth-child(n+5) { animation-delay: 0.40s; }

    @keyframes flashToastIn {
        from { opacity: 0; transform: translateX(20px); }
        to   { opacity: 1; transform: translateX(0); }
    }
    /* JS-controlled leave animation. If JS never fires, this never applies
       and the toast just stays visible — which is what we want as the
       fail-safe. */
    .flash-toast.flash-toast--leave {
        animation: flashToastOut 0.24s ease-in both;
    }
    @keyframes flashToastOut {
        from { opacity: 1; transform: translateX(0); }
        to   { opacity: 0; transform: translateX(20px); }
    }

    .flash-toast__icon {
        flex: 0 0 22px; width: 22px; height: 22px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; color: #fff; font-weight: 700;
    }
    .flash-toast__body { flex: 1; min-width: 0; }
    .flash-toast__title { font-weight: 600; margin: 0 0 2px; color: #0f172a; font-size: 13px; }
    .flash-toast__msg { margin: 0; word-wrap: break-word; }
    .flash-toast__close {
        position: absolute; top: 6px; right: 8px;
        background: transparent; border: 0; cursor: pointer;
        color: #94a3b8; font-size: 18px; line-height: 1;
        padding: 4px;
    }
    .flash-toast__close:hover { color: #475569; }

    .flash-toast--success { border-left-color: #16a34a; }
    .flash-toast--success .flash-toast__icon { background: #16a34a; }
    .flash-toast--error { border-left-color: #dc2626; }
    .flash-toast--error .flash-toast__icon { background: #dc2626; }
    .flash-toast--warning { border-left-color: #d97706; }
    .flash-toast--warning .flash-toast__icon { background: #d97706; }
    .flash-toast--info { border-left-color: #2563eb; }
    .flash-toast--info .flash-toast__icon { background: #2563eb; }

    @media (max-width: 540px) {
        .flash-toast-stack { left: 12px; right: 12px; top: 12px; max-width: none; }
    }
    @media (prefers-reduced-motion: reduce) {
        .flash-toast { animation: none; opacity: 1; transform: none; }
        .flash-toast.flash-toast--leave { animation: none; opacity: 0; }
    }
</style>

<div class="flash-toast-stack" id="flashToastStack" aria-live="polite" aria-atomic="true">
    @foreach($toasts as $i => $t)
        @php
            $title = match($t['type']) {
                'success' => 'Success',
                'error'   => 'Something went wrong',
                'warning' => 'Heads up',
                'info'    => 'Info',
                default   => 'Notice',
            };
            $icon = match($t['type']) {
                'success' => '✓',
                'error'   => '!',
                'warning' => '!',
                'info'    => 'i',
                default   => 'i',
            };
        @endphp
        <div class="flash-toast flash-toast--{{ $t['type'] }}" data-toast-index="{{ $i }}" role="status">
            <span class="flash-toast__icon" aria-hidden="true">{{ $icon }}</span>
            <div class="flash-toast__body">
                <p class="flash-toast__title">{{ $title }}</p>
                <p class="flash-toast__msg">{{ $t['message'] }}</p>
            </div>
            <button type="button" class="flash-toast__close" aria-label="Dismiss">&times;</button>
        </div>
    @endforeach
</div>

<script>
/* CSS handles the appearance — JS is purely for ergonomics:
   - Auto-dismiss after a lifespan (longer for errors/warnings)
   - Pause auto-dismiss on hover
   - Close button click
   If this script never runs (JS error elsewhere, blocked, etc.), the toast
   is STILL visible thanks to the CSS animation. It just won't auto-dismiss. */
(function () {
    var stack = document.getElementById('flashToastStack');
    if (!stack) return;
    var toasts = stack.querySelectorAll('.flash-toast');

    toasts.forEach(function (toast, i) {
        var dismiss = function () {
            toast.classList.add('flash-toast--leave');
            setTimeout(function () { toast.remove(); }, 260);
        };
        var lifespan = toast.classList.contains('flash-toast--error') || toast.classList.contains('flash-toast--warning')
            ? 6500 : 5000;
        // Add the per-toast stagger so visible-time feels consistent.
        var timer = setTimeout(dismiss, lifespan + i * 120);

        var closeBtn = toast.querySelector('.flash-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { clearTimeout(timer); dismiss(); });
        }
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () { timer = setTimeout(dismiss, 2500); });
    });
})();
</script>
@endif
