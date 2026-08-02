<footer class="ft-root" id="ft-root">
    <div class="ft-inner">

        <div class="ft-main">
            <nav class="ft-nav" aria-label="Footer navigation">
                <a href="{{ route('dashboard') }}" class="ft-link">Dashboard</a>
                <a href="{{ route('dashboard.all.exams') }}" class="ft-link">Exam Archives</a>
                <a href="{{ route('dashboard.all.files') }}" class="ft-link">Academic Files</a>
                <a href="{{ route('departments.index') }}" class="ft-link">Departments</a>
            </nav>

            <a href="mailto:support@academicdigital.space" class="ft-support">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                Contact support
            </a>
        </div>

        <div class="ft-bar">
            <span class="ft-bar__copy">
                &copy; {{ date('Y') }} Metascholar Consult LTD
            </span>
            <a href="https://metascholar.academicdigital.space" target="_blank" rel="noopener" class="ft-bar__brand">
                Powered by Metascholar
            </a>
        </div>

    </div>
</footer>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700&display=swap');

.ft-root,
.ft-root * {
    font-family: 'Outfit', sans-serif !important;
    box-sizing: border-box;
}

.ft-root {
    --ft-ink: #475569;
    --ft-muted: #94a3b8;
    --ft-line: #eceff3;
    background: linear-gradient(165deg, #ffffff 0%, #fffcf2 54%, #f6fbf5 100%);
    border-top: 1px solid #f3e8ea;
    margin-top: auto;
    opacity: 0;
    transform: translateY(12px);
    transition: opacity .45s cubic-bezier(.4,0,.2,1), transform .45s cubic-bezier(.4,0,.2,1);
}

.ft-root.ft-visible {
    opacity: 1;
    transform: translateY(0);
}

.ft-inner {
    max-width: 1120px;
    margin: 0 auto;
    padding: 0 24px;
}

.ft-main {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px 28px;
    padding: 22px 0 16px;
    border-bottom: 1px solid var(--ft-line);
    flex-wrap: wrap;
}

.ft-nav {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 6px 4px;
}

.ft-link {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 999px;
    text-decoration: none;
    color: var(--ft-ink);
    font-size: 0.84rem;
    font-weight: 500;
    transition: background .15s, color .15s;
}

.ft-link:hover {
    background: linear-gradient(90deg, #fff7f8 0%, #fffdf3 52%, #f6fbf6 100%);
    color: #1f2937;
    text-decoration: none;
}

.ft-support {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 8px 14px;
    border-radius: 999px;
    border: 1px solid #f5d7db;
    background: linear-gradient(95deg, #fff1f3 0%, #fffbe9 100%);
    color: #7a1f26;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: transform .12s, box-shadow .15s, background .15s;
}

.ft-support:hover {
    background: linear-gradient(95deg, #ffe9ed 0%, #fff7d9 100%);
    color: #7a1f26;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 6px 14px rgba(214, 69, 79, .12);
}

.ft-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px 20px;
    padding: 12px 0 16px;
    flex-wrap: wrap;
}

.ft-bar__copy,
.ft-bar__brand {
    font-size: 0.78rem;
    color: var(--ft-muted);
    font-weight: 400;
}

.ft-bar__brand {
    font-weight: 600;
    text-decoration: none;
    transition: color .15s;
}

.ft-bar__brand:hover {
    color: #1f2937;
    text-decoration: none;
}

/* Tablet 768–1023 */
@media (max-width: 1023px) {
    .ft-inner { padding: 0 20px; }
    .ft-main { gap: 16px; padding: 20px 0 14px; }
}

/* Mobile ≤767 */
@media (max-width: 767px) {
    .ft-inner { padding: 0 16px; }

    .ft-main {
        flex-direction: column;
        align-items: stretch;
        gap: 14px;
        padding: 18px 0 14px;
    }

    .ft-nav {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 6px;
    }

    .ft-link {
        justify-content: center;
        padding: 10px 12px;
        border-radius: 12px;
        background: rgba(255, 255, 255, 0.7);
        border: 1px solid #eef1f4;
        font-size: 0.82rem;
    }

    .ft-support {
        justify-content: center;
        width: 100%;
        border-radius: 12px;
        padding: 11px 14px;
    }

    .ft-bar {
        flex-direction: column;
        align-items: center;
        text-align: center;
        gap: 4px;
        padding: 12px 0 18px;
    }
}

@media (prefers-reduced-motion: reduce) {
    .ft-root { transition: none !important; opacity: 1 !important; transform: none !important; }
}
</style>

<script>
(function() {
    var footer = document.getElementById('ft-root');
    if (!footer) return;

    var io = new IntersectionObserver(function(entries) {
        entries.forEach(function(entry) {
            if (entry.isIntersecting) {
                entry.target.classList.add('ft-visible');
                io.unobserve(entry.target);
            }
        });
    }, { threshold: 0.06 });

    io.observe(footer);
})();
</script>
