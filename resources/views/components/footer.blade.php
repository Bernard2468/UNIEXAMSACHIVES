<footer class="ft-root @yield('footerClass')" id="ft-root">
    <div class="ft-inner">

        {{-- ── Top: two columns ── --}}
        <div class="ft-cols">

            {{-- Quick navigation --}}
            <div class="ft-col ft-col--nav" data-ft-reveal>
                <div class="ft-col__hd">
                    <span class="ft-col__title">Quick navigation</span>
                    <span class="ft-col__bar"></span>
                </div>
                <nav class="ft-nav">
                    <a href="{{ route('dashboard') }}" class="ft-link">
                        <span class="ft-link__dot"></span>
                        <span class="ft-link__text">Dashboard</span>
                        <svg class="ft-link__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('dashboard.all.exams') }}" class="ft-link">
                        <span class="ft-link__dot"></span>
                        <span class="ft-link__text">Exam Archives</span>
                        <svg class="ft-link__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('dashboard.all.files') }}" class="ft-link">
                        <span class="ft-link__dot"></span>
                        <span class="ft-link__text">Academic Files</span>
                        <svg class="ft-link__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('dashboard.folders.index') }}" class="ft-link">
                        <span class="ft-link__dot"></span>
                        <span class="ft-link__text">Folders</span>
                        <svg class="ft-link__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    <a href="{{ route('dashboard.uimms.portal') }}" class="ft-link">
                        <span class="ft-link__dot"></span>
                        <span class="ft-link__text">Memos</span>
                        <svg class="ft-link__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                    @auth
                        <a href="{{ auth()->user()->is_admin ? route('admin.communication-admin.create') : route('admin.communication.create') }}" class="ft-link">
                            <span class="ft-link__dot"></span>
                            <span class="ft-link__text">Compose Memo</span>
                            <svg class="ft-link__arrow" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                        </a>
                    @endauth
                </nav>
            </div>

            {{-- Support --}}
            <div class="ft-col ft-col--support" data-ft-reveal>
                <div class="ft-col__hd">
                    <span class="ft-col__title">Support center</span>
                    <span class="ft-col__bar"></span>
                </div>
                <div class="ft-support">
                    <div class="ft-support__icon-wrap">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                    </div>
                    <p class="ft-support__text">Need help? Our support team is ready to assist you with any questions.</p>
                    <a href="mailto:support@academicdigital.space" class="ft-support__btn">
                        Contact support
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                    </a>
                </div>
            </div>

        </div>

        {{-- ── Bottom bar ── --}}
        <div class="ft-bar">
            <span class="ft-bar__copy">
                &copy; {{ date('Y') }} Metascholar Consult LTD. All rights reserved.
            </span>
            <span class="ft-bar__sep" aria-hidden="true"></span>
            <span class="ft-bar__dev">
                Powered by
                <a href="https://metascholar.academicdigital.space" target="_blank" class="ft-bar__brand">Metascholar Consult LTD</a>
            </span>
        </div>

    </div>
</footer>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

/* ═══════════════════════════════════════════
   FOOTER — OUTFIT DESIGN SYSTEM
   ═══════════════════════════════════════════ */
.ft-root,
.ft-root * {
    font-family: 'Outfit', sans-serif !important;
    box-sizing: border-box;
}

.ft-root {
    --ft-cug-red: #d6454f;
    --ft-cug-yellow: #f4d76e;
    --ft-cug-green: #78b26a;
    --ft-cug-black: #1f2937;
    --ft-cug-white: #ffffff;
    --ft-ink-soft: #475569;
    --ft-border-soft: #eceff3;
    --ft-surface-a: #ffffff;
    --ft-surface-b: #fffcf2;
    --ft-surface-c: #f6fbf5;
    background: linear-gradient(165deg, var(--ft-surface-a) 0%, var(--ft-surface-b) 54%, var(--ft-surface-c) 100%);
    border-top: 1.5px solid #f3e8ea;
    margin-top: auto;
    /* entry animation */
    opacity: 0;
    transform: translateY(18px);
    transition: opacity .55s cubic-bezier(.4,0,.2,1), transform .55s cubic-bezier(.4,0,.2,1);
}

.ft-root.ft-visible {
    opacity: 1;
    transform: translateY(0);
}

.ft-inner {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ── Two columns ── */
.ft-cols {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    justify-content: center;
    gap: 28px 56px;
    padding: 36px 0 28px;
    border-bottom: 1.5px solid #eceff3;
}

.ft-col--nav {
    flex: 1 1 380px;
    max-width: 480px;
}

.ft-col--support {
    flex: 0 1 300px;
    width: 100%;
    max-width: 300px;
}

/* ── Column header ── */
.ft-col__hd  { margin-bottom: 18px; }

.ft-col__title {
    display: block;
    font-size: 0.72rem;
    font-weight: 700;
    color: #9ca3af;
    letter-spacing: .1em;
    text-transform: uppercase;
    margin-bottom: 8px;
}

.ft-col__bar {
    display: block;
    width: 1.6rem;
    height: 2px;
    background: linear-gradient(90deg, rgba(214, 69, 79, .72), rgba(244, 215, 110, .78), rgba(120, 178, 106, .72));
    border-radius: 2px;
}

/* ── Nav links (2×2) ── */
.ft-nav {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 4px 10px;
}

.ft-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 9px;
    text-decoration: none;
    color: var(--ft-ink-soft);
    font-size: 0.87rem;
    font-weight: 500;
    transition: background .15s, color .15s, transform .15s;
    position: relative;
    overflow: hidden;
}

.ft-link__dot {
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background: #c7d2dd;
    flex-shrink: 0;
    transition: background .15s, transform .2s;
}

.ft-link__text { flex: 1; }

.ft-link__arrow {
    opacity: 0;
    transform: translateX(-6px);
    transition: opacity .2s, transform .2s;
    color: #b4bdc8;
    flex-shrink: 0;
}

.ft-link:hover {
    background: linear-gradient(90deg, #fff7f8 0%, #fffdf3 52%, #f6fbf6 100%);
    color: var(--ft-cug-black);
    transform: translateX(3px);
    text-decoration: none;
}

.ft-link:hover .ft-link__dot {
    background: #d6454f;
    transform: scale(1.4);
}

.ft-link:hover .ft-link__arrow {
    opacity: 1;
    transform: translateX(0);
}

/* ── Support ── */
.ft-support {
    display: flex;
    flex-direction: column;
    gap: 12px;
    padding: 18px;
    background: linear-gradient(155deg, #ffffff 0%, #fffdf4 58%, #f7fbf6 100%);
    border: 1.5px solid #e8edf2;
    border-radius: 14px;
    transition: border-color .18s, box-shadow .18s;
}

.ft-support:hover {
    border-color: #d9e4ef;
    box-shadow: 0 10px 24px rgba(31, 41, 55, .08);
}

.ft-support__icon-wrap {
    display: inline-flex;
    padding: 10px;
    background: #fff;
    border: 1.5px solid #e7edf3;
    border-radius: 10px;
    color: #475569;
    width: fit-content;
}

.ft-support__text {
    font-size: 0.82rem;
    color: #64748b;
    line-height: 1.55;
    margin: 0;
}

.ft-support__btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 16px;
    background: linear-gradient(95deg, #fff1f3 0%, #fffbe9 100%);
    color: #7a1f26;
    border: 1px solid #f5d7db;
    border-radius: 9px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    width: fit-content;
    transition: background .15s, transform .12s, box-shadow .15s;
}

.ft-support__btn:hover {
    background: linear-gradient(95deg, #ffe9ed 0%, #fff7d9 100%);
    color: #7a1f26;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 8px 18px rgba(214, 69, 79, .14);
}

.ft-support__btn svg {
    transition: transform .2s;
}

.ft-support__btn:hover svg {
    transform: translateX(3px);
}

/* ── Bottom bar ── */
.ft-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    padding: 14px 0;
    flex-wrap: wrap;
}

.ft-bar__copy,
.ft-bar__dev {
    font-size: 0.8rem;
    color: #94a3b8;
    font-weight: 400;
}

.ft-bar__sep {
    display: block;
    width: 1px;
    height: 14px;
    background: #e2e8f0;
    flex-shrink: 0;
}

.ft-bar__brand {
    font-weight: 700;
    color: #6b7280;
    text-decoration: none;
    transition: color .15s;
}

.ft-bar__brand:hover {
    color: #0c0c0c;
    text-decoration: none;
}

/* ── Reveal animation for columns ── */
[data-ft-reveal] {
    opacity: 0;
    transform: translateY(14px);
    transition: opacity .45s cubic-bezier(.4,0,.2,1), transform .45s cubic-bezier(.4,0,.2,1);
}

[data-ft-reveal].ft-revealed { opacity: 1; transform: translateY(0); }
[data-ft-reveal]:nth-child(2) { transition-delay: .08s; }

/* ── Responsive ── */
/* Tablet 768–1023 */
@media (max-width: 1023px) {
    .ft-cols { gap: 24px 40px; }
    .ft-col--support { max-width: 280px; }
}

/* Mobile ≤767 */
@media (max-width: 767px) {
    .ft-cols {
        flex-direction: column;
        align-items: stretch;
        justify-content: flex-start;
        gap: 24px;
    }
    .ft-col--nav,
    .ft-col--support {
        flex: 1 1 auto;
        max-width: 100%;
        width: 100%;
    }
    .ft-bar { flex-direction: column; align-items: flex-start; gap: 6px; }
    .ft-bar__sep { display: none; }
}

@media (prefers-reduced-motion: reduce) {
    .ft-root, [data-ft-reveal] { transition: none !important; opacity: 1 !important; transform: none !important; }
}

/* ── Hide the app footer on mobile inside the authenticated dashboard shell ──
   Its "Quick navigation" duplicates the mobile sidebar drawer, so it's redundant
   mid-task. Scoped to pages whose content has .dashboardarea (the app shell), so
   public landing/about/news + auth pages keep their footer. The Dashboard home
   opts back in via .ft-keep-mobile. :has() degrades gracefully (footer stays)
   on browsers without support. */
@media (max-width: 767px) {
    .main_wrapper:has(.dashboardarea) .ft-root:not(.ft-keep-mobile) { display: none !important; }
}
</style>

<script>
(function() {
    // Trigger footer entry + column reveal via IntersectionObserver
    var footer = document.getElementById('ft-root');
    if (!footer) return;

    var io = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('ft-visible');
                // stagger reveal columns
                entry.target.querySelectorAll('[data-ft-reveal]').forEach(function(el) {
                    el.classList.add('ft-revealed');
                });
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });

    io.observe(footer);
})();
</script>
