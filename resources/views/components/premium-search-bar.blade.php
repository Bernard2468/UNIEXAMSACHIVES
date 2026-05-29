{{--
    Premium live-search bar.

    Client mode: instant filter on elements matching `target` with [data-search].
    Server mode: GET form with ?q= — searches full paginated datasets (set server => true).

    @param string $placeholder
    @param string $target
    @param string $countLabel
    @param string $id
    @param string $hideWhenFilter
    @param bool   $server
    @param array  $preserve  Query keys to keep on submit (e.g. ['tab' => $tab])
--}}
@php
    $placeholder    = $placeholder ?? 'Search…';
    $target         = $target ?? '[data-search]';
    $countLabel     = $countLabel ?? 'results';
    $uid            = $id ?? 'psb-' . substr(md5($target . $placeholder), 0, 8);
    $hideWhenFilter = $hideWhenFilter ?? '';
    $server         = !empty($server);
    $preserve       = $preserve ?? [];
    $queryValue     = request('q', '');
@endphp

<div class="premium-search {{ $server ? 'premium-search--server' : '' }}"
     data-premium-search
     data-target="{{ $target }}"
     data-count-label="{{ $countLabel }}"
     @if($hideWhenFilter) data-hide-when-filter="{{ $hideWhenFilter }}" @endif
     @if($server) data-server="1" data-debounce="450" @endif
     id="{{ $uid }}">

    @if($server)
        <form method="GET" action="{{ url()->current() }}" class="premium-search__form" data-premium-search-form>
            @foreach($preserve as $key => $val)
                @if($val !== null && $val !== '')
                    <input type="hidden" name="{{ $key }}" value="{{ $val }}">
                @endif
            @endforeach
    @endif

    <div class="premium-search__shell">
        <div class="premium-search__icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
            </svg>
        </div>
        <input
            type="search"
            class="premium-search__input"
            data-premium-search-input
            name="{{ $server ? 'q' : '' }}"
            value="{{ $server ? $queryValue : '' }}"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            spellcheck="false"
            aria-label="{{ $placeholder }}"
        >
        <kbd class="premium-search__kbd" data-premium-search-kbd aria-hidden="true"></kbd>
        <button type="button" class="premium-search__clear" data-premium-search-clear aria-label="Clear search" {{ $queryValue ? '' : 'hidden' }}>
            <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
        </button>
        <span class="premium-search__spinner" data-premium-search-spinner hidden aria-hidden="true"></span>
    </div>

    @if($server)
        </form>
    @endif

    <div class="premium-search__meta">
        <span class="premium-search__count" data-premium-search-count></span>
        <span class="premium-search__empty" data-premium-search-empty-msg hidden>No matches — try a different keyword</span>
    </div>
</div>

@once
@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

.premium-search { font-family: 'Outfit', sans-serif !important; margin-bottom: 20px; width: 100%; }
.premium-search__form { margin: 0; width: 100%; }

.premium-search__shell {
    position: relative;
    display: flex;
    align-items: center;
    background: linear-gradient(135deg, #fafafa 0%, #fff 50%, #fafafa 100%);
    border: 1.5px solid #e5e7eb;
    border-radius: 14px;
    padding: 4px 6px 4px 4px;
    transition: border-color .2s, box-shadow .25s, background .2s;
    box-shadow: 0 1px 2px rgba(12, 12, 12, 0.04);
}

.premium-search__shell::before {
    content: '';
    position: absolute;
    inset: -1px;
    border-radius: 15px;
    padding: 1.5px;
    background: linear-gradient(135deg, transparent 40%, rgba(12, 12, 12, 0.08) 100%);
    -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
    -webkit-mask-composite: xor;
    mask-composite: exclude;
    pointer-events: none;
    opacity: 0;
    transition: opacity .25s;
}

.premium-search:focus-within .premium-search__shell,
.premium-search__shell:focus-within {
    border-color: #0c0c0c;
    box-shadow: 0 0 0 4px rgba(12, 12, 12, 0.06), 0 8px 24px rgba(12, 12, 12, 0.06);
    background: #fff;
}

.premium-search:focus-within .premium-search__shell::before { opacity: 1; }

.premium-search--loading .premium-search__shell {
    border-color: #0c0c0c;
    opacity: 0.92;
}

.premium-search__icon {
    flex-shrink: 0;
    width: 44px;
    height: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #9ca3af;
    transition: color .2s, transform .2s;
}

.premium-search:focus-within .premium-search__icon { color: #0c0c0c; transform: scale(1.05); }

.premium-search__input {
    flex: 1;
    min-width: 0;
    border: none;
    background: transparent;
    padding: 12px 8px 12px 0;
    font-size: 0.95rem;
    font-weight: 500;
    color: #111827;
    outline: none;
    font-family: 'Outfit', sans-serif !important;
    letter-spacing: -0.01em;
}

.premium-search__input::placeholder { color: #b0b5c0; font-weight: 400; }
.premium-search__input::-webkit-search-cancel-button,
.premium-search__input::-webkit-search-decoration { -webkit-appearance: none; appearance: none; }

.premium-search__kbd {
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    padding: 5px 8px;
    margin-right: 4px;
    font-size: 0.68rem;
    font-weight: 600;
    color: #9ca3af;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 7px;
    font-family: 'Outfit', sans-serif !important;
    transition: opacity .15s;
}

.premium-search:focus-within .premium-search__kbd,
.premium-search--has-value .premium-search__kbd {
    opacity: 0;
    pointer-events: none;
    width: 0;
    padding: 0;
    margin: 0;
    overflow: hidden;
    border: none;
}

.premium-search__clear {
    flex-shrink: 0;
    width: 36px;
    height: 36px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: none;
    background: #f3f4f6;
    color: #6b7280;
    border-radius: 10px;
    cursor: pointer;
    margin-right: 4px;
    transition: all .15s;
}

.premium-search__clear:hover { background: #0c0c0c; color: #fff; }
.premium-search__clear[hidden] { display: none !important; }

.premium-search__spinner {
    width: 18px;
    height: 18px;
    margin-right: 10px;
    border: 2px solid #e5e7eb;
    border-top-color: #0c0c0c;
    border-radius: 50%;
    animation: premium-search-spin .65s linear infinite;
}

.premium-search__spinner[hidden] { display: none !important; }

@keyframes premium-search-spin { to { transform: rotate(360deg); } }

.premium-search__meta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
    margin-top: 8px;
    padding: 0 4px;
    min-height: 1.25rem;
}

.premium-search__count { font-size: 0.75rem; font-weight: 500; color: #9ca3af; }
.premium-search__count strong { color: #374151; font-weight: 700; }
.premium-search__empty { font-size: 0.75rem; font-weight: 600; color: #d97706; }
.premium-search__empty[hidden] { display: none !important; }

[data-search-hidden="true"] { display: none !important; }

.is_dark .premium-search__shell { background: linear-gradient(135deg, #0f172a 0%, #111827 50%, #0f172a 100%); border-color: #2d3748; }
.is_dark .premium-search:focus-within .premium-search__shell { border-color: #f3f4f6; box-shadow: 0 0 0 4px rgba(243, 244, 246, 0.08); background: #111827; }
.is_dark .premium-search__icon { color: #6b7280; }
.is_dark .premium-search:focus-within .premium-search__icon { color: #f3f4f6; }
.is_dark .premium-search__input { color: #f3f4f6; }
.is_dark .premium-search__input::placeholder { color: #6b7280; }
.is_dark .premium-search__kbd { background: #1e2330; border-color: #2d3748; color: #9ca3af; }
.is_dark .premium-search__clear { background: #1e2330; color: #9ca3af; }
.is_dark .premium-search__clear:hover { background: #f3f4f6; color: #0c0c0c; }
.is_dark .premium-search__count { color: #6b7280; }
.is_dark .premium-search__count strong { color: #e5e7eb; }
.is_dark .premium-search__empty { color: #fbbf24; }
.is_dark .premium-search__spinner { border-color: #2d3748; border-top-color: #f3f4f6; }
</style>
@endpush

@push('scripts')
<script>
(function () {
    if (window.__premiumSearchInit) return;
    window.__premiumSearchInit = true;

    var isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform || navigator.userAgent);

    function initBar(root) {
        if (root.dataset.psbReady) return;
        root.dataset.psbReady = '1';

        var input    = root.querySelector('[data-premium-search-input]');
        var clear    = root.querySelector('[data-premium-search-clear]');
        var countEl  = root.querySelector('[data-premium-search-count]');
        var emptyEl  = root.querySelector('[data-premium-search-empty-msg]');
        var kbdEl    = root.querySelector('[data-premium-search-kbd]');
        var spinner  = root.querySelector('[data-premium-search-spinner]');
        var form     = root.querySelector('[data-premium-search-form]');
        var target   = root.dataset.target || '[data-search]';
        var label    = root.dataset.countLabel || 'results';
        var isServer = root.dataset.server === '1';
        var debounce = parseInt(root.dataset.debounce || '450', 10);
        var submitTimer = null;
        var lastSubmitted = isServer ? (input.value || '').trim() : null;

        if (kbdEl) kbdEl.textContent = isMac ? '⌘ K' : 'Ctrl K';

        function items() {
            return Array.prototype.slice.call(document.querySelectorAll(target));
        }

        function setLoading(on) {
            root.classList.toggle('premium-search--loading', on);
            if (spinner) spinner.hidden = !on;
        }

        function applyClient() {
            var q = (input.value || '').trim().toLowerCase();
            var all = items();
            var visible = 0;

            all.forEach(function (el) {
                var hay = (el.getAttribute('data-search') || el.textContent || '').toLowerCase();
                var show = !q || hay.indexOf(q) !== -1;
                el.setAttribute('data-search-hidden', show ? 'false' : 'true');
                if (show) visible++;
            });

            root.classList.toggle('premium-search--has-value', q.length > 0);
            if (clear) clear.hidden = !q.length;

            if (countEl) {
                if (q.length && all.length) {
                    countEl.innerHTML = 'Showing <strong>' + visible + '</strong> of <strong>' + all.length + '</strong> ' + label;
                } else if (all.length) {
                    countEl.innerHTML = '<strong>' + all.length + '</strong> ' + label + (isServer && q.length ? ' on this page' : '');
                } else if (isServer && q.length) {
                    countEl.innerHTML = 'Searching…';
                } else {
                    countEl.innerHTML = '';
                }
            }

            if (emptyEl) {
                emptyEl.hidden = !(q.length && all.length && visible === 0);
            }

            var hideSel = root.dataset.hideWhenFilter;
            if (hideSel) {
                document.querySelectorAll(hideSel).forEach(function (el) {
                    el.setAttribute('data-search-hidden', q.length ? 'true' : 'false');
                });
            }
        }

        function submitServer() {
            if (!form) return;
            var q = (input.value || '').trim();
            if (q === lastSubmitted) return;
            lastSubmitted = q;
            setLoading(true);
            form.submit();
        }

        function scheduleServer() {
            if (!isServer) return;
            clearTimeout(submitTimer);
            submitTimer = setTimeout(submitServer, debounce);
        }

        function apply() {
            applyClient();
            scheduleServer();
        }

        input.addEventListener('input', apply);
        input.addEventListener('search', apply);

        if (clear) {
            clear.addEventListener('click', function () {
                input.value = '';
                lastSubmitted = '__cleared__';
                applyClient();
                if (isServer && form) {
                    setLoading(true);
                    form.submit();
                } else {
                    input.focus();
                }
            });
        }

        document.addEventListener('keydown', function (e) {
            if ((isMac ? e.metaKey : e.ctrlKey) && e.key === 'k') {
                var tag = (document.activeElement && document.activeElement.tagName) || '';
                if (/^(INPUT|TEXTAREA|SELECT)$/.test(tag)) return;
                e.preventDefault();
                input.focus();
                input.select();
            }
        });

        applyClient();
    }

    function boot() {
        document.querySelectorAll('[data-premium-search]').forEach(initBar);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();
</script>
@endpush
@endonce
