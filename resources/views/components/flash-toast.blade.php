{{--
    Global flash → toast renderer.
    Reads session('success'|'error'|'warning'|'info') and the validation
    $errors bag, then renders animated, auto-dismissing toasts.

    Included once in layout/app.blade.php, so any controller that returns
    ->with('success', '…') or back()->with('error', '…') automatically
    surfaces a toast without per-view wiring.
--}}
@php
    $toasts = [];

    foreach (['success', 'error', 'warning', 'info'] as $key) {
        $msg = session($key);
        if (is_string($msg) && $msg !== '') {
            $toasts[] = ['type' => $key, 'message' => $msg];
        }
    }

    // Validation errors (only show on pages that opted into the $errors bag).
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
        z-index: 99999;
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
        opacity: 0; transform: translateX(20px);
        transition: opacity .25s ease, transform .25s ease;
    }
    .flash-toast.flash-toast--show { opacity: 1; transform: translateX(0); }
    .flash-toast.flash-toast--leave { opacity: 0; transform: translateX(20px); }
    .flash-toast__icon {
        flex: 0 0 22px; width: 22px; height: 22px;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 50%; color: #fff; font-weight: 700;
    }
    .flash-toast__body { flex: 1; min-width: 0; }
    .flash-toast__title { font-weight: 600; margin: 0 0 2px; color: #0f172a; }
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
(function () {
    var stack = document.getElementById('flashToastStack');
    if (!stack) return;
    var toasts = stack.querySelectorAll('.flash-toast');
    toasts.forEach(function (toast, i) {
        // Stagger the slide-in slightly so multiple toasts feel sequenced.
        setTimeout(function () { toast.classList.add('flash-toast--show'); }, 60 + i * 80);

        var dismiss = function () {
            toast.classList.add('flash-toast--leave');
            toast.classList.remove('flash-toast--show');
            setTimeout(function () { toast.remove(); }, 260);
        };
        // Auto-dismiss after 4.5s for success/info, 6s for error/warning.
        var lifespan = toast.classList.contains('flash-toast--error') || toast.classList.contains('flash-toast--warning')
            ? 6000 : 4500;
        var timer = setTimeout(dismiss, lifespan + i * 80);

        var closeBtn = toast.querySelector('.flash-toast__close');
        if (closeBtn) {
            closeBtn.addEventListener('click', function () { clearTimeout(timer); dismiss(); });
        }
        // Pause auto-dismiss on hover.
        toast.addEventListener('mouseenter', function () { clearTimeout(timer); });
        toast.addEventListener('mouseleave', function () { timer = setTimeout(dismiss, 2500); });
    });
})();
</script>
@endif
