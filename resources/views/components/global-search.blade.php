{{--
    Global "Search Everything" — header widget.

    Debounced live dropdown of permission-scoped results grouped by type, plus a
    "See all results" path to the full results page (route search.index). All
    visibility is decided server-side by GlobalSearchService — this is display only.

    Behaviour: ⌘K / Ctrl-K focuses; ↑/↓ move the active row; Enter opens it (or
    the results page when nothing is active); Esc clears/closes. Recent queries
    are remembered in localStorage. Only rendered for authenticated users.

    Safe to include more than once per page (desktop nav + mobile nav): the CSS/JS
    is emitted once via @once and the script initialises every instance it finds.
--}}
<div class="uds"
     data-global-search
     data-suggest-url="{{ route('search.suggest') }}"
     data-results-url="{{ route('search.index') }}">
    <div class="uds__shell" data-uds-shell>
        <span class="uds__icon" aria-hidden="true">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/>
            </svg>
        </span>
        <input type="search"
               class="uds__input"
               data-uds-input
               placeholder="Search…"
               autocomplete="off"
               spellcheck="false"
               role="combobox"
               aria-expanded="false"
               aria-autocomplete="list"
               aria-label="Search everything across the system"
               enterkeyhint="search">
        <span class="uds__spinner" data-uds-spinner hidden aria-hidden="true"></span>
        <button type="button" class="uds__clear" data-uds-clear aria-label="Clear search" hidden>
            <svg width="13" height="13" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
        </button>
        <kbd class="uds__kbd" data-uds-kbd aria-hidden="true"></kbd>
    </div>

    <div class="uds__panel" data-uds-panel role="listbox" aria-label="Search results" hidden></div>
</div>

@once
@push('styles')
<style>
.uds { position: relative; width: 100%; max-width: 560px; margin: 0 auto; font-family: 'Outfit', system-ui, -apple-system, 'Segoe UI', sans-serif; }

.uds__shell {
    position: relative; display: flex; align-items: center;
    background: #f6f7f9; border: 1.5px solid #e5e7eb; border-radius: 999px;
    padding: 2px 6px 2px 4px; transition: border-color .18s, box-shadow .22s, background .18s;
}
.uds__shell:focus-within {
    background: #fff; border-color: #0c0c0c;
    box-shadow: 0 0 0 4px rgba(12,12,12,.06), 0 10px 26px rgba(12,12,12,.08);
}
.uds__icon { flex-shrink: 0; width: 38px; height: 38px; display: flex; align-items: center; justify-content: center; color: #9ca3af; transition: color .18s; }
.uds__shell:focus-within .uds__icon { color: #0c0c0c; }
.uds__input {
    flex: 1; min-width: 0; border: none; background: transparent; outline: none;
    padding: 9px 6px 9px 0; font-size: .92rem; font-weight: 500; color: #111827;
    font-family: inherit; letter-spacing: -.01em;
}
.uds__input::placeholder { color: #9aa0ac; font-weight: 400; }
.uds__input::-webkit-search-cancel-button, .uds__input::-webkit-search-decoration { -webkit-appearance: none; appearance: none; }

.uds__kbd {
    flex-shrink: 0; display: inline-flex; align-items: center; gap: 1px;
    padding: 4px 8px; margin-right: 4px; font-size: .66rem; font-weight: 600;
    color: #9ca3af; background: #fff; border: 1px solid #e5e7eb; border-radius: 6px; font-family: inherit;
}
.uds__shell:focus-within .uds__kbd, .uds.is-filled .uds__kbd { display: none; }

.uds__clear {
    flex-shrink: 0; width: 30px; height: 30px; display: inline-flex; align-items: center; justify-content: center;
    border: none; background: #ececf0; color: #6b7280; border-radius: 999px; cursor: pointer; margin-right: 4px; transition: all .15s;
}
.uds__clear:hover { background: #0c0c0c; color: #fff; }
.uds__clear[hidden] { display: none; }

.uds__spinner {
    flex-shrink: 0; width: 16px; height: 16px; margin-right: 10px;
    border: 2px solid #e5e7eb; border-top-color: #0c0c0c; border-radius: 50%;
    animation: uds-spin .6s linear infinite;
}
.uds__spinner[hidden] { display: none; }
@keyframes uds-spin { to { transform: rotate(360deg); } }

/* ── Dropdown panel ── */
.uds__panel {
    position: absolute; top: calc(100% + 8px); left: 0; right: 0; z-index: 1050;
    background: #fff; border: 1.5px solid #ececf0; border-radius: 16px;
    box-shadow: 0 18px 50px rgba(12,12,12,.16); overflow: hidden;
    max-height: min(70vh, 560px); overflow-y: auto; padding: 6px;
    animation: uds-pop .14s ease;
}
.uds__panel[hidden] { display: none; }
@keyframes uds-pop { from { opacity: 0; transform: translateY(-6px); } to { opacity: 1; transform: translateY(0); } }

.uds__grouphd {
    display: flex; align-items: center; justify-content: space-between;
    padding: 10px 12px 5px; font-size: .66rem; font-weight: 700; letter-spacing: .07em;
    text-transform: uppercase; color: #9ca3af;
}
.uds__grouphd small { font-weight: 600; color: #c3c7cf; letter-spacing: 0; text-transform: none; }

.uds__opt {
    display: flex; align-items: center; gap: 11px; padding: 9px 11px; border-radius: 11px;
    cursor: pointer; text-decoration: none; color: inherit; scroll-margin: 8px;
}
.uds__opt:hover, .uds__opt.is-active { background: #f4f5f7; }
.uds__opt.is-active { box-shadow: inset 0 0 0 1.5px #e5e7eb; }

.uds__optIcon {
    flex-shrink: 0; width: 34px; height: 34px; border-radius: 9px; display: flex; align-items: center; justify-content: center;
    background: #f0f1f4; color: #4b5563;
}
.uds__optIcon svg { width: 17px; height: 17px; }
.uds__optBody { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 3px; }
.uds__optTitle { display: block; font-size: .88rem; font-weight: 600; color: #1f2937; line-height: 1.25; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.uds__optTitle mark { background: #fde68a; color: inherit; padding: 0 1px; border-radius: 3px; }
.uds__optSub { display: block; font-size: .74rem; color: #9ca3af; line-height: 1.2; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.uds__optSub mark { background: #fde68a; color: inherit; border-radius: 3px; }
.uds__optMeta { flex-shrink: 0; display: flex; align-items: center; gap: 8px; }
.uds__badge {
    font-size: .64rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase;
    padding: 3px 8px; border-radius: 999px; background: #eef1f6; color: #64748b; white-space: nowrap;
}
.uds__enter { flex-shrink: 0; color: #cbd0d8; font-size: .8rem; opacity: 0; }
.uds__opt.is-active .uds__enter { opacity: 1; color: #6b7280; }

.uds__foot {
    display: flex; align-items: center; gap: 10px; margin-top: 4px; padding: 11px 12px;
    border-top: 1.5px solid #f1f2f4; border-radius: 11px; cursor: pointer; text-decoration: none;
    color: #374151; font-size: .84rem; font-weight: 600;
}
.uds__foot:hover, .uds__foot.is-active { background: #0c0c0c; color: #fff; }
.uds__foot .uds__footKey { margin-left: auto; font-size: .7rem; opacity: .7; }

.uds__state { padding: 26px 18px; text-align: center; }
.uds__state svg { width: 30px; height: 30px; color: #d1d5db; margin-bottom: 8px; }
.uds__state p { margin: 0; font-size: .84rem; color: #9ca3af; font-weight: 500; }
.uds__recentHd {
    display: flex; align-items: center; justify-content: space-between; padding: 10px 12px 5px;
    font-size: .66rem; font-weight: 700; letter-spacing: .07em; text-transform: uppercase; color: #9ca3af;
}
.uds__recentClear { background: none; border: none; cursor: pointer; font-size: .7rem; font-weight: 600; color: #9ca3af; padding: 2px 4px; }
.uds__recentClear:hover { color: #0c0c0c; }

/* ── Dark mode ── */
.is_dark .uds__shell { background: #0f172a; border-color: #2d3748; }
.is_dark .uds__shell:focus-within { background: #111827; border-color: #f3f4f6; box-shadow: 0 0 0 4px rgba(243,244,246,.08), 0 10px 26px rgba(0,0,0,.4); }
.is_dark .uds__icon { color: #6b7280; }
.is_dark .uds__shell:focus-within .uds__icon { color: #f3f4f6; }
.is_dark .uds__input { color: #f3f4f6; }
.is_dark .uds__input::placeholder { color: #6b7280; }
.is_dark .uds__kbd { background: #1e2330; border-color: #2d3748; color: #9ca3af; }
.is_dark .uds__clear { background: #1e2330; color: #9ca3af; }
.is_dark .uds__clear:hover { background: #f3f4f6; color: #0c0c0c; }
.is_dark .uds__spinner { border-color: #2d3748; border-top-color: #f3f4f6; }
.is_dark .uds__panel { background: #111827; border-color: #1e2330; box-shadow: 0 18px 50px rgba(0,0,0,.5); }
.is_dark .uds__opt:hover, .is_dark .uds__opt.is-active { background: #1a2233; }
.is_dark .uds__opt.is-active { box-shadow: inset 0 0 0 1.5px #2d3748; }
.is_dark .uds__optIcon { background: #1e2330; color: #cbd5e1; }
.is_dark .uds__optTitle { color: #e5e7eb; }
.is_dark .uds__optTitle mark, .is_dark .uds__optSub mark { background: #a16207; color: #fff; }
.is_dark .uds__badge { background: #1e2330; color: #94a3b8; }
.is_dark .uds__foot { border-color: #1e2330; color: #e5e7eb; }
.is_dark .uds__foot:hover, .is_dark .uds__foot.is-active { background: #f3f4f6; color: #0c0c0c; }

/* ── Responsive: keep it usable in the mobile header ── */
@media (max-width: 991px) {
    .uds { max-width: 100%; margin: 4px 0 0; }
}
</style>
@endpush

@push('scripts')
<script>
(function () {
    if (window.__udsInit) return;
    window.__udsInit = true;

    var isMac = /Mac|iPhone|iPad|iPod/.test(navigator.platform || navigator.userAgent);
    var RECENT_KEY = 'udts_recent_searches';

    var ICONS = {
        page:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        recent:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>',
        file:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
        folder:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9l-.8-1.2A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>',
        exam:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        memo:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        form:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>',
        committee:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        user:       '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        department: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M13 9v.01M13 12v.01M13 15v.01"/></svg>',
        position:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7.4L12 17l-6.3 4.4L8 14 2 9.4h7.6z"/></svg>',
        office:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1M9 11h1M9 15h1M14 7h1M14 11h1M14 15h1"/></svg>',
        policy:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        manual:     '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h6"/></svg>'
    };

    var SEARCH_ICON = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>';
    var ERROR_ICON  = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg>';

    function esc(s) {
        return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
            return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
        });
    }
    function highlight(text, q) {
        var safe = esc(text);
        if (!q) return safe;
        var terms = q.trim().split(/\s+/).filter(Boolean).map(function (t) {
            return t.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        });
        if (!terms.length) return safe;
        try {
            return safe.replace(new RegExp('(' + terms.join('|') + ')', 'ig'), '<mark>$1</mark>');
        } catch (e) { return safe; }
    }
    function iconFor(type) { return ICONS[type] || ICONS.page; }

    function getRecent() {
        try { return JSON.parse(localStorage.getItem(RECENT_KEY) || '[]'); } catch (e) { return []; }
    }
    function pushRecent(q) {
        q = (q || '').trim();
        if (q.length < 2) return;
        var list = getRecent().filter(function (x) { return x.toLowerCase() !== q.toLowerCase(); });
        list.unshift(q);
        try { localStorage.setItem(RECENT_KEY, JSON.stringify(list.slice(0, 6))); } catch (e) {}
    }

    var instances = [];

    function initInstance(root, idx) {
        if (root.dataset.udsReady) return;
        root.dataset.udsReady = '1';

        var input    = root.querySelector('[data-uds-input]');
        var panel    = root.querySelector('[data-uds-panel]');
        var spinner  = root.querySelector('[data-uds-spinner]');
        var clearBtn = root.querySelector('[data-uds-clear]');
        var kbd      = root.querySelector('[data-uds-kbd]');
        var suggestUrl = root.dataset.suggestUrl;
        var resultsUrl = root.dataset.resultsUrl;

        panel.id = 'uds-panel-' + idx;
        input.setAttribute('aria-controls', panel.id);

        var timer = null, controller = null, options = [], activeIndex = -1;
        var DEBOUNCE = 220;

        if (kbd) kbd.textContent = isMac ? '⌘K' : 'Ctrl K';

        function setFilled(q) {
            root.classList.toggle('is-filled', q.length > 0);
            if (clearBtn) clearBtn.hidden = q.length === 0;
        }
        function openPanel() { panel.hidden = false; input.setAttribute('aria-expanded', 'true'); }
        function closePanel() {
            panel.hidden = true; input.setAttribute('aria-expanded', 'false');
            input.removeAttribute('aria-activedescendant');
            activeIndex = -1; options = [];
        }
        function collectOptions() {
            options = Array.prototype.slice.call(panel.querySelectorAll('[data-uds-nav]'));
            options.forEach(function (el, i) { el.id = 'uds-' + idx + '-opt-' + i; });
            activeIndex = -1;
        }
        function setActive(i) {
            if (!options.length) return;
            if (activeIndex >= 0 && options[activeIndex]) options[activeIndex].classList.remove('is-active');
            activeIndex = (i + options.length) % options.length;
            var el = options[activeIndex];
            el.classList.add('is-active');
            el.scrollIntoView({ block: 'nearest' });
            input.setAttribute('aria-activedescendant', el.id);
        }
        function goResults(q) { pushRecent(q); window.location.href = resultsUrl + '?q=' + encodeURIComponent(q); }

        function renderRecent() {
            var recent = getRecent();
            var html = '';
            if (recent.length) {
                html += '<div class="uds__recentHd"><span>Recent searches</span>'
                     + '<button type="button" class="uds__recentClear" data-uds-recentclear>Clear</button></div>';
                recent.forEach(function (q) {
                    html += '<a href="#" class="uds__opt" data-uds-nav data-uds-recent="' + esc(q) + '">'
                         + '<span class="uds__optIcon">' + ICONS.recent + '</span>'
                         + '<span class="uds__optBody"><span class="uds__optTitle">' + esc(q) + '</span></span>'
                         + '<span class="uds__optMeta"><span class="uds__enter">↵</span></span></a>';
                });
            } else {
                html += '<div class="uds__state">' + SEARCH_ICON
                     + '<p>Search files, folders, exams, memos, forms, people &amp; more.</p></div>';
            }
            panel.innerHTML = html;
            collectOptions();
            openPanel();
        }

        function renderResults(data) {
            var q = data.q || '';
            var html = '';
            (data.groups || []).forEach(function (group) {
                html += '<div class="uds__grouphd"><span>' + esc(group.label) + '</span>'
                     + (group.has_more ? '<small>top ' + group.items.length + '</small>' : '') + '</div>';
                group.items.forEach(function (item) {
                    var badge = item.badge ? '<span class="uds__badge">' + esc(item.badge) + '</span>' : '';
                    var sub = item.subtitle ? '<span class="uds__optSub">' + highlight(item.subtitle, q) + '</span>' : '';
                    html += '<a href="' + esc(item.url) + '" class="uds__opt" data-uds-nav>'
                         + '<span class="uds__optIcon">' + iconFor(item.type) + '</span>'
                         + '<span class="uds__optBody"><span class="uds__optTitle">' + highlight(item.title, q) + '</span>' + sub + '</span>'
                         + '<span class="uds__optMeta">' + badge + '<span class="uds__enter">↵</span></span></a>';
                });
            });
            if (!(data.groups || []).length) {
                html += '<div class="uds__state">' + SEARCH_ICON
                     + '<p>No matches for &ldquo;' + esc(q) + '&rdquo;. Only items you own or that are shared with you appear here.</p></div>';
            }
            html += '<a href="' + esc(resultsUrl) + '?q=' + encodeURIComponent(q) + '" class="uds__foot" data-uds-nav>'
                 + '<span>See all results for &ldquo;' + esc(q) + '&rdquo;</span>'
                 + '<span class="uds__footKey">Enter</span></a>';
            panel.innerHTML = html;
            collectOptions();
            openPanel();
        }

        function renderError() {
            panel.innerHTML = '<div class="uds__state">' + ERROR_ICON
                + '<p>Search hit a snag — press Enter to open full results.</p></div>';
            collectOptions();
            openPanel();
        }

        function runSearch(q) {
            if (controller) controller.abort();
            controller = new AbortController();
            if (spinner) spinner.hidden = false;
            fetch(suggestUrl + '?q=' + encodeURIComponent(q), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin',
                signal: controller.signal
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (spinner) spinner.hidden = true;
                if ((input.value || '').trim() !== q) return; // stale
                renderResults(data);
            })
            .catch(function (err) {
                if (err && err.name === 'AbortError') return;
                if (spinner) spinner.hidden = true;
                renderError();
            });
        }

        function schedule() {
            var q = (input.value || '').trim();
            setFilled(q);
            clearTimeout(timer);
            if (controller) controller.abort();
            if (q.length === 0) { renderRecent(); return; }
            if (q.length < 2) { panel.innerHTML = '<div class="uds__state"><p>Keep typing…</p></div>'; collectOptions(); openPanel(); return; }
            timer = setTimeout(function () { runSearch(q); }, DEBOUNCE);
        }

        input.addEventListener('input', schedule);
        input.addEventListener('focus', function () {
            var q = (input.value || '').trim();
            if (q.length === 0) renderRecent();
            else if (panel.hidden) schedule();
            else openPanel();
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowDown') { e.preventDefault(); if (panel.hidden) schedule(); else setActive(activeIndex + 1); }
            else if (e.key === 'ArrowUp') { e.preventDefault(); setActive(activeIndex - 1); }
            else if (e.key === 'Enter') {
                var q = (input.value || '').trim();
                if (activeIndex >= 0 && options[activeIndex]) {
                    var el = options[activeIndex];
                    if (el.hasAttribute('data-uds-recent')) {
                        e.preventDefault();
                        input.value = el.getAttribute('data-uds-recent');
                        schedule();
                        return;
                    }
                    pushRecent(q); // let the anchor's href navigate
                    return;
                }
                if (q.length >= 1) { e.preventDefault(); goResults(q); }
            }
            else if (e.key === 'Escape') {
                if (!panel.hidden) { e.preventDefault(); closePanel(); }
                else if (input.value) { input.value = ''; setFilled(''); }
            }
        });

        panel.addEventListener('click', function (e) {
            if (e.target.closest('[data-uds-recentclear]')) {
                e.preventDefault();
                try { localStorage.removeItem(RECENT_KEY); } catch (err) {}
                renderRecent();
                return;
            }
            var recentEl = e.target.closest('[data-uds-recent]');
            if (recentEl) {
                e.preventDefault();
                input.value = recentEl.getAttribute('data-uds-recent');
                input.focus();
                schedule();
                return;
            }
            if (e.target.closest('[data-uds-nav]')) { pushRecent((input.value || '').trim()); }
        });

        if (clearBtn) clearBtn.addEventListener('click', function () {
            input.value = ''; setFilled(''); input.focus(); renderRecent();
        });

        instances.push({ root: root, input: input, close: closePanel });
    }

    function boot() {
        document.querySelectorAll('[data-global-search]').forEach(initInstance);

        document.addEventListener('click', function (e) {
            instances.forEach(function (inst) { if (!inst.root.contains(e.target)) inst.close(); });
        });

        // The ⌘K / Ctrl-K shortcut is owned by the search launcher (it opens the
        // command palette), so it is intentionally not bound here to avoid a
        // double action. Expose the instances so the launcher can focus one.
        window.__udsInstances = instances;
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();
</script>
@endpush
@endonce
