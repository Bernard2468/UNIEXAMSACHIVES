{{--
    Global search launcher — the "when the hero search scrolls away" experience.

    • A docked search icon (in the header, desktop) and a floating pill (mobile)
      appear only once the inline hero search leaves the viewport.
    • Clicking either — or pressing ⌘K / Ctrl-K anywhere — opens a centered
      command-palette overlay that reuses the same global-search widget.

    Rendered once globally (from layout.app, @auth). The header trigger button
    itself lives in frontend/header.blade.php; this file owns the mobile trigger,
    the overlay, and all the JS/CSS.
--}}
<div class="udsl" data-uds-launcher>

    {{-- Mobile floating trigger (bottom-left to clear #scrollUp at bottom-right) --}}
    <button type="button" class="uds-trigger uds-trigger--fab" data-udsl-open aria-label="Search everything">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
    </button>

    {{-- Command-palette overlay --}}
    <div class="udsl__overlay" data-udsl-overlay hidden aria-hidden="true">
        <div class="udsl__backdrop" data-udsl-close></div>
        <div class="udsl__dialog" role="dialog" aria-modal="true" aria-label="Search everything">
            <button type="button" class="udsl__x" data-udsl-close aria-label="Close search">
                <svg width="15" height="15" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
            </button>

            @include('components.global-search')

            <div class="udsl__hint">
                <span><kbd>↑</kbd><kbd>↓</kbd> navigate</span>
                <span><kbd>↵</kbd> open</span>
                <span><kbd>esc</kbd> close</span>
            </div>
        </div>
    </div>
</div>

@once
@push('styles')
<style>
/* ── Docked / floating triggers ── */
.uds-trigger { display: none; align-items: center; justify-content: center; cursor: pointer; border: none; padding: 0; }
.uds-trigger.is-shown { display: inline-flex; }

.uds-trigger--header {
    width: 40px; height: 40px; border-radius: 50%;
    background: #f3f4f6; color: #374151; border: 1px solid #e5e7eb; margin-right: 12px;
    transition: background .18s, color .18s, transform .18s, box-shadow .18s;
    animation: uds-trigger-in .22s ease;
}
.uds-trigger--header:hover { background: #0c0c0c; color: #fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(12,12,12,.2); }
.uds-trigger--header svg { width: 18px; height: 18px; }

.uds-trigger--fab {
    position: fixed; left: 20px; bottom: 24px; z-index: 1200;
    width: 52px; height: 52px; border-radius: 50%;
    background: #0c0c0c; color: #fff; box-shadow: 0 12px 30px rgba(12,12,12,.32);
    animation: uds-trigger-in .25s ease;
}
.uds-trigger--fab:hover { transform: translateY(-2px); }
.uds-trigger--fab svg { width: 22px; height: 22px; }
@media (min-width: 992px) { .uds-trigger--fab { display: none !important; } }

@keyframes uds-trigger-in { from { opacity: 0; transform: scale(.8); } to { opacity: 1; transform: scale(1); } }

.is_dark .uds-trigger--header { background: #1e2330; color: #cbd5e1; border-color: #2d3748; }
.is_dark .uds-trigger--header:hover { background: #f3f4f6; color: #0c0c0c; }
.is_dark .uds-trigger--fab { background: #f3f4f6; color: #0c0c0c; box-shadow: 0 12px 30px rgba(0,0,0,.5); }

/* ── Command-palette overlay ── */
html.udsl-lock { overflow: hidden; }

.udsl__overlay {
    position: fixed; inset: 0; z-index: 10000;
    display: flex; align-items: flex-start; justify-content: center;
    padding: 12vh 16px 16px; opacity: 0; transition: opacity .18s ease;
}
.udsl__overlay[hidden] { display: none; }
.udsl__overlay.is-open { opacity: 1; }

.udsl__backdrop { position: absolute; inset: 0; background: rgba(15,23,42,.55); backdrop-filter: blur(6px); -webkit-backdrop-filter: blur(6px); }

.udsl__dialog {
    position: relative; width: 100%; max-width: 640px;
    transform: translateY(-10px) scale(.985); transition: transform .2s ease;
}
.udsl__overlay.is-open .udsl__dialog { transform: translateY(0) scale(1); }

/* Let the reused widget fill the dialog and read like a palette. */
.udsl__dialog .uds { max-width: 100%; margin: 0; }
.udsl__dialog .uds__shell { border-radius: 16px; padding: 4px 8px 4px 6px; box-shadow: 0 24px 60px rgba(0,0,0,.28); border-color: transparent; }
.udsl__dialog .uds__input { font-size: 1.02rem; padding: 13px 8px 13px 0; }
.udsl__dialog .uds__icon { width: 44px; height: 44px; }
.udsl__dialog .uds__panel { box-shadow: 0 24px 60px rgba(0,0,0,.32); }

.udsl__x {
    position: absolute; top: -44px; right: 0; width: 34px; height: 34px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.14); color: #fff; border: none; cursor: pointer; transition: background .15s;
}
.udsl__x:hover { background: rgba(255,255,255,.28); }

.udsl__hint { display: flex; flex-wrap: wrap; gap: 18px; justify-content: center; margin-top: 16px; }
.udsl__hint span { font-size: .74rem; color: rgba(255,255,255,.8); display: inline-flex; align-items: center; gap: 5px; }
.udsl__hint kbd {
    background: rgba(255,255,255,.16); color: #fff; border-radius: 5px; padding: 2px 6px;
    font-size: .68rem; font-family: 'Outfit', system-ui, sans-serif; line-height: 1;
}

@media (max-width: 575px) {
    .udsl__overlay { padding: 8vh 12px 12px; }
    .udsl__x { top: -42px; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    if (window.__udslInit) return;
    window.__udslInit = true;

    function boot() {
        var launcher = document.querySelector('[data-uds-launcher]');
        if (!launcher) return;

        var isMac    = /Mac|iPhone|iPad|iPod/.test(navigator.platform || navigator.userAgent);
        var overlay  = launcher.querySelector('[data-udsl-overlay]');
        var dlgInput = overlay ? overlay.querySelector('[data-uds-input]') : null;
        var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-udsl-open]'));
        var lastFocus = null;

        function isOpen() { return overlay && !overlay.hidden; }

        function open() {
            if (!overlay || isOpen()) return;
            lastFocus = document.activeElement;
            overlay.hidden = false;
            overlay.setAttribute('aria-hidden', 'false');
            document.documentElement.classList.add('udsl-lock');
            requestAnimationFrame(function () {
                overlay.classList.add('is-open');
                if (dlgInput) { dlgInput.focus(); try { dlgInput.select(); } catch (e) {} }
            });
        }

        function close() {
            if (!overlay || !isOpen()) return;
            overlay.classList.remove('is-open');
            document.documentElement.classList.remove('udsl-lock');
            setTimeout(function () {
                overlay.hidden = true;
                overlay.setAttribute('aria-hidden', 'true');
            }, 200);
            if (lastFocus && lastFocus.focus) { try { lastFocus.focus(); } catch (e) {} }
        }

        triggers.forEach(function (t) {
            t.addEventListener('click', function (e) { e.preventDefault(); open(); });
        });
        launcher.querySelectorAll('[data-udsl-close]').forEach(function (c) {
            c.addEventListener('click', function (e) { e.preventDefault(); close(); });
        });

        document.addEventListener('keydown', function (e) {
            if ((isMac ? e.metaKey : e.ctrlKey) && (e.key === 'k' || e.key === 'K')) {
                var tag = (document.activeElement && document.activeElement.tagName) || '';
                var inPalette = overlay && overlay.contains(document.activeElement);
                if (/^(INPUT|TEXTAREA|SELECT)$/.test(tag) && !inPalette) return;
                e.preventDefault();
                isOpen() ? close() : open();
            } else if (e.key === 'Escape' && isOpen()) {
                close();
            }
        });

        // Reveal the docked triggers only when the inline hero search is off-screen
        // (the -110px top margin accounts for the sticky header that covers the top).
        function toggle(show) {
            triggers.forEach(function (t) { t.classList.toggle('is-shown', show); });
        }
        var hero = document.querySelector('.uda-search [data-global-search]');
        if (hero && 'IntersectionObserver' in window) {
            new IntersectionObserver(function (entries) {
                toggle(!entries[0].isIntersecting);
            }, { rootMargin: '-110px 0px 0px 0px', threshold: 0 }).observe(hero);
        } else {
            toggle(true); // no hero on this page → search is always one tap away
        }
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>
@endpush
@endonce
