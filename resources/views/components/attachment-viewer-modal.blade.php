{{--
    Attachment viewer modal — premium glassmorphism overlay for previewing
    form attachments without leaving the page. Renders PDFs in an <iframe>,
    images in an <img>, and falls back to a "preview unavailable" card for
    types the browser can't render (showing a Download button instead).

    Exposed JS API (called from any "View" button on the page):
        window.attachmentViewer.open(items, startIndex = 0)
            items = [{ url, name, size, mime, stage?, downloadUrl? }, ...]
            - url:         used for the inline preview (img/iframe). ?inline=1 is appended.
            - downloadUrl: OPTIONAL. If the preview and download are served by different
                           endpoints (e.g. the memo chat's .../view vs .../download routes),
                           set this to the download URL. Falls back to `url` when omitted.
        window.attachmentViewer.openOne(item)
        window.attachmentViewer.close()

    Keyboard: ESC closes, ← → navigates between items.
    Click backdrop closes. Body scroll is locked while open.
--}}
@if (Auth::check())
<div id="attachmentViewer" class="att-viewer" role="dialog" aria-modal="true" aria-hidden="true" tabindex="-1">
    <div class="att-viewer__backdrop" data-att-close></div>
    <div class="att-viewer__shell" role="document">
        <header class="att-viewer__header">
            <div class="att-viewer__file">
                <span class="att-viewer__icon" id="attViewerIcon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                </span>
                <div class="att-viewer__file-text">
                    <h3 class="att-viewer__name" id="attViewerName">filename.pdf</h3>
                    <p class="att-viewer__meta">
                        <span id="attViewerMime">PDF</span>
                        <span class="att-viewer__sep">·</span>
                        <span id="attViewerSize">—</span>
                        <span class="att-viewer__stage-wrap" id="attViewerStageWrap" hidden>
                            <span class="att-viewer__sep">·</span>
                            <span class="att-viewer__stage" id="attViewerStage"></span>
                        </span>
                        <span class="att-viewer__counter" id="attViewerCounter" hidden></span>
                    </p>
                </div>
            </div>

            <div class="att-viewer__tools">
                <button class="att-viewer__btn" id="attViewerPrev" type="button" aria-label="Previous attachment" hidden>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="att-viewer__btn" id="attViewerNext" type="button" aria-label="Next attachment" hidden>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <a class="att-viewer__btn att-viewer__btn--download" id="attViewerDownload" href="#" download>
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    <span>Download</span>
                </a>
                <button class="att-viewer__btn att-viewer__btn--close" data-att-close type="button" aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </header>

        <div class="att-viewer__body" id="attViewerBody">
            {{-- The current item's preview node is injected here by JS. --}}
        </div>

        <div class="att-viewer__loading" id="attViewerLoading" hidden>
            <div class="att-viewer__spinner"></div>
            <span>Loading preview…</span>
        </div>
    </div>
</div>

<style>
    /* ============ OVERLAY ============ */
    .att-viewer {
        position: fixed; inset: 0;
        z-index: 2147483646;          /* Below toast (max int) but above preloader */
        display: none;
        align-items: center;
        justify-content: center;
        padding: 28px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Inter', Roboto, sans-serif;
    }
    .att-viewer.is-open { display: flex; }
    .att-viewer__backdrop {
        position: absolute; inset: 0;
        background: rgba(7, 11, 22, 0.78);
        -webkit-backdrop-filter: blur(14px) saturate(140%);
        backdrop-filter: blur(14px) saturate(140%);
        animation: avf-fade 0.22s ease-out;
    }
    @keyframes avf-fade { from { opacity: 0; } to { opacity: 1; } }

    /* ============ SHELL ============ */
    /* IMPORTANT: use an EXPLICIT height (not max-height) so the flex children
       have a real container to fill. Without this the shell collapses to the
       header height because iframes have no intrinsic vertical size — that's
       why the modal was rendering "flat". */
    .att-viewer__shell {
        position: relative;
        width: 100%;
        max-width: 1200px;
        height: 92vh;            /* real height — body's flex:1 now has space */
        min-height: 560px;       /* sane floor on very short viewports */
        background: linear-gradient(180deg, #0f172a 0%, #0b1220 100%);
        border: 1px solid rgba(255, 255, 255, 0.07);
        border-radius: 18px;
        box-shadow:
            0 30px 80px rgba(0, 0, 0, 0.55),
            0 6px 18px rgba(0, 0, 0, 0.35),
            inset 0 1px 0 rgba(255, 255, 255, 0.04);
        display: flex; flex-direction: column;
        overflow: hidden;
        color: #f1f5f9;
        animation: avs-pop 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    @keyframes avs-pop {
        from { opacity: 0; transform: scale(0.96) translateY(10px); }
        to   { opacity: 1; transform: scale(1)    translateY(0); }
    }

    /* ============ HEADER ============ */
    .att-viewer__header {
        display: flex; align-items: center; justify-content: space-between;
        gap: 16px;
        padding: 14px 18px;
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        flex-shrink: 0;
    }
    .att-viewer__file { display: flex; align-items: center; gap: 12px; min-width: 0; flex: 1; }
    .att-viewer__icon {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: inline-flex; align-items: center; justify-content: center;
        background: rgba(99, 102, 241, 0.18);
        color: #a5b4fc;
        flex-shrink: 0;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.08);
    }
    .att-viewer__icon--pdf   { background: rgba(220, 38, 38, 0.22); color: #fca5a5; }
    .att-viewer__icon--image { background: rgba(14, 165, 233, 0.22); color: #7dd3fc; }
    .att-viewer__icon--doc   { background: rgba(37, 99, 235, 0.22); color: #93c5fd; }
    .att-viewer__icon--sheet { background: rgba(16, 185, 129, 0.22); color: #6ee7b7; }
    .att-viewer__file-text { min-width: 0; flex: 1; }
    .att-viewer__name {
        margin: 0; font-size: 14.5px; font-weight: 600;
        color: #f8fafc; line-height: 1.25;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .att-viewer__meta {
        margin: 3px 0 0; font-size: 11.5px; color: #94a3b8;
        display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
    }
    .att-viewer__sep { color: #475569; }
    .att-viewer__stage {
        background: rgba(99, 102, 241, 0.18);
        color: #a5b4fc;
        padding: 1px 8px;
        border-radius: 999px;
        font-size: 10.5px; font-weight: 600;
    }
    .att-viewer__counter { color: #cbd5e1; font-variant-numeric: tabular-nums; }

    .att-viewer__tools { display: flex; gap: 6px; align-items: center; flex-shrink: 0; }
    .att-viewer__btn {
        display: inline-flex; align-items: center; justify-content: center;
        gap: 5px;
        height: 34px;
        min-width: 34px;
        padding: 0 10px;
        background: rgba(255, 255, 255, 0.06);
        color: #e2e8f0;
        border: 1px solid rgba(255, 255, 255, 0.08);
        border-radius: 9px;
        font-size: 12px; font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s, transform 0.15s;
    }
    .att-viewer__btn:hover { background: rgba(255, 255, 255, 0.12); border-color: rgba(255, 255, 255, 0.15); color: #fff; text-decoration: none; }
    .att-viewer__btn:active { transform: scale(0.96); }
    .att-viewer__btn--download {
        background: rgba(99, 102, 241, 0.22);
        border-color: rgba(99, 102, 241, 0.35);
        color: #c7d2fe;
    }
    .att-viewer__btn--download:hover { background: rgba(99, 102, 241, 0.32); color: #fff; }
    .att-viewer__btn--close { padding: 0; width: 34px; }

    /* ============ BODY ============ */
    .att-viewer__body {
        flex: 1 1 auto;
        min-height: 480px;       /* guarantee tall enough for a PDF page */
        position: relative;
        background:
            linear-gradient(45deg, rgba(255,255,255,0.02) 25%, transparent 25%),
            linear-gradient(-45deg, rgba(255,255,255,0.02) 25%, transparent 25%),
            linear-gradient(45deg, transparent 75%, rgba(255,255,255,0.02) 75%),
            linear-gradient(-45deg, transparent 75%, rgba(255,255,255,0.02) 75%);
        background-size: 22px 22px;
        background-position: 0 0, 0 11px, 11px -11px, -11px 0px;
        display: flex; align-items: center; justify-content: center;
        overflow: auto;
    }
    .att-viewer__body iframe {
        width: 100%;
        height: 100%;
        min-height: 480px;       /* defensive — even if parent flex misbehaves */
        border: 0; background: #fff;
        display: block;
    }
    .att-viewer__body img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        border-radius: 10px;
        box-shadow: 0 12px 36px rgba(0, 0, 0, 0.4);
        background: #fff;
    }
    .att-viewer__no-preview {
        text-align: center;
        padding: 48px 32px;
        color: #cbd5e1;
        max-width: 380px;
    }
    .att-viewer__no-preview .att-viewer__no-preview-icon {
        width: 64px; height: 64px;
        margin: 0 auto 16px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.06);
        color: #94a3b8;
        display: inline-flex; align-items: center; justify-content: center;
        box-shadow: inset 0 1px 0 rgba(255,255,255,0.06);
    }
    .att-viewer__no-preview h4 { font-size: 16px; color: #f1f5f9; margin: 0 0 6px; font-weight: 600; }
    .att-viewer__no-preview p  { font-size: 13px; color: #94a3b8; margin: 0 0 20px; line-height: 1.55; }

    /* ============ LOADING ============ */
    .att-viewer__loading {
        position: absolute; inset: 0;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center; gap: 14px;
        color: #cbd5e1; font-size: 13px;
        background: rgba(7, 11, 22, 0.6);
        backdrop-filter: blur(4px);
        z-index: 2;
    }
    .att-viewer__spinner {
        width: 38px; height: 38px;
        border: 3px solid rgba(255, 255, 255, 0.1);
        border-top-color: #a5b4fc;
        border-radius: 50%;
        animation: avs-spin 0.8s linear infinite;
    }
    @keyframes avs-spin { to { transform: rotate(360deg); } }

    /* ============ RESPONSIVE ============ */
    @media (max-width: 720px) {
        .att-viewer { padding: 10px; }
        .att-viewer__shell { max-height: 96vh; border-radius: 14px; }
        .att-viewer__header { padding: 10px 12px; gap: 8px; }
        .att-viewer__icon { width: 32px; height: 32px; }
        .att-viewer__name { font-size: 13px; }
        .att-viewer__btn--download span { display: none; }
        .att-viewer__btn--download { width: 34px; padding: 0; }
    }

    /* Lock body scroll while modal is open */
    body.att-viewer-open { overflow: hidden; }
</style>

<script>
(function () {
    const root      = document.getElementById('attachmentViewer');
    if (!root) return;
    const body      = document.getElementById('attViewerBody');
    const iconEl    = document.getElementById('attViewerIcon');
    const nameEl    = document.getElementById('attViewerName');
    const mimeEl    = document.getElementById('attViewerMime');
    const sizeEl    = document.getElementById('attViewerSize');
    const stageEl   = document.getElementById('attViewerStage');
    const stageWrap = document.getElementById('attViewerStageWrap');
    const counterEl = document.getElementById('attViewerCounter');
    const downloadA = document.getElementById('attViewerDownload');
    const prevBtn   = document.getElementById('attViewerPrev');
    const nextBtn   = document.getElementById('attViewerNext');
    const loadingEl = document.getElementById('attViewerLoading');

    let state = { items: [], index: 0 };

    function classifyKind(mime, name) {
        const lower = String(name || '').toLowerCase();
        if ((mime || '').startsWith('image/')) return 'image';
        if (mime === 'application/pdf' || lower.endsWith('.pdf')) return 'pdf';
        if (lower.endsWith('.doc') || lower.endsWith('.docx')) return 'doc';
        if (lower.endsWith('.xls') || lower.endsWith('.xlsx') || lower.endsWith('.csv')) return 'sheet';
        return 'file';
    }

    function iconSvg(kind) {
        switch (kind) {
            case 'pdf':   return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13h6M9 17h4" stroke-width="1.6"/></svg>';
            case 'image': return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>';
            case 'doc':   return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>';
            case 'sheet': return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8M8 17h8M12 13v8" stroke-width="1.6"/></svg>';
            default:      return '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>';
        }
    }

    function withInline(url) {
        return url + (url.includes('?') ? '&' : '?') + 'inline=1';
    }

    function render() {
        const item = state.items[state.index];
        if (!item) return;

        const kind = classifyKind(item.mime, item.name);

        nameEl.textContent = item.name || 'Attachment';
        mimeEl.textContent = (kind === 'image') ? 'Image'
                          : (kind === 'pdf')   ? 'PDF'
                          : (kind === 'doc')   ? 'Document'
                          : (kind === 'sheet') ? 'Spreadsheet'
                          : 'File';
        sizeEl.textContent = item.size || '';

        if (item.stage) {
            stageEl.textContent = item.stage;
            stageWrap.hidden = false;
        } else {
            stageWrap.hidden = true;
        }

        // Counter "2 / 5"
        if (state.items.length > 1) {
            counterEl.hidden = false;
            counterEl.innerHTML = '<span class="att-viewer__sep">·</span> ' + (state.index + 1) + ' of ' + state.items.length;
            prevBtn.hidden = false;
            nextBtn.hidden = false;
        } else {
            counterEl.hidden = true;
            prevBtn.hidden = true;
            nextBtn.hidden = true;
        }

        iconEl.className = 'att-viewer__icon att-viewer__icon--' + kind;
        iconEl.innerHTML = iconSvg(kind);

        downloadA.href = item.downloadUrl || item.url;
        downloadA.setAttribute('download', item.name || '');

        // Reset body
        body.innerHTML = '';
        loadingEl.hidden = false;

        if (kind === 'image') {
            const img = document.createElement('img');
            img.alt = item.name || '';
            img.onload = () => { loadingEl.hidden = true; };
            img.onerror = () => { loadingEl.hidden = true; renderNoPreview(item, 'Image failed to load.'); };
            img.src = withInline(item.url);
            body.appendChild(img);
        } else if (kind === 'pdf') {
            const iframe = document.createElement('iframe');
            iframe.title = item.name || 'PDF preview';
            iframe.onload  = () => { loadingEl.hidden = true; };
            iframe.onerror = () => { loadingEl.hidden = true; renderNoPreview(item); };
            // Safety net: if onload never fires, hide spinner after 6s.
            setTimeout(() => { loadingEl.hidden = true; }, 6000);
            iframe.src = withInline(item.url);
            body.appendChild(iframe);
        } else {
            loadingEl.hidden = true;
            renderNoPreview(item);
        }
    }

    function renderNoPreview(item, msg) {
        body.innerHTML =
            '<div class="att-viewer__no-preview">' +
            '  <div class="att-viewer__no-preview-icon">' +
            '    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>' +
            '  </div>' +
            '  <h4>' + (msg || 'Preview not available') + '</h4>' +
            '  <p>This file type can\'t be displayed in the browser. Download it to view on your device.</p>' +
            '  <a href="' + (item.downloadUrl || item.url) + '" download class="att-viewer__btn att-viewer__btn--download" style="display:inline-flex;">' +
            '    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>' +
            '    <span>Download file</span>' +
            '  </a>' +
            '</div>';
    }

    function open(items, startIndex) {
        if (!Array.isArray(items) || items.length === 0) return;
        state.items = items;
        state.index = Math.max(0, Math.min(startIndex || 0, items.length - 1));
        root.classList.add('is-open');
        root.setAttribute('aria-hidden', 'false');
        document.body.classList.add('att-viewer-open');
        render();
        // Move keyboard focus into the modal for ESC/arrow keys.
        root.focus({ preventScroll: true });
    }

    function close() {
        root.classList.remove('is-open');
        root.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('att-viewer-open');
        // Free up resources — important for PDF iframes on slow devices.
        body.innerHTML = '';
        state.items = [];
    }

    function next() {
        if (state.items.length < 2) return;
        state.index = (state.index + 1) % state.items.length;
        render();
    }
    function prev() {
        if (state.items.length < 2) return;
        state.index = (state.index - 1 + state.items.length) % state.items.length;
        render();
    }

    // ---- Event wiring ----
    root.addEventListener('click', (e) => {
        if (e.target.closest('[data-att-close]')) close();
    });
    prevBtn.addEventListener('click', prev);
    nextBtn.addEventListener('click', next);

    document.addEventListener('keydown', (e) => {
        if (!root.classList.contains('is-open')) return;
        if (e.key === 'Escape')     { e.preventDefault(); close(); }
        if (e.key === 'ArrowRight') { e.preventDefault(); next(); }
        if (e.key === 'ArrowLeft')  { e.preventDefault(); prev(); }
    });

    // ---- Public API ----
    window.attachmentViewer = {
        open,
        openOne: (item) => open([item], 0),
        close,
        next, prev,
    };
})();
</script>
@endif
