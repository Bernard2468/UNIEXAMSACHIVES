{{--
    HTML5 signature pad — DocuSign-style.

    Two ways to sign:
      1. Draw  — canvas pad (mouse / touch / stylus).
      2. Type  — full name rendered in a chosen cursive font, rasterised to PNG.

    Either way the result lands in the same `signature_data` field as a base64
    PNG, so the backend (hash chain, audit trail, saved-signature reuse, PDF)
    does not change.

    Inputs posted to the controller:
      - signature_data         base64 PNG (drawn OR typed). Empty when reusing saved.
      - reuse_saved_signature  "1" or "".
--}}
@php
    use Illuminate\Support\Facades\Auth;
    $savedSignature = $savedSignature ?? null;
    $signerName = $signerName
        ?? trim((Auth::user()->first_name ?? '') . ' ' . (Auth::user()->last_name ?? ''));

    // Font keyed by the CSS font-family name we load from Google Fonts.
    // Each entry: [label, slug used for the CSS hook]. The slug lets us
    // attach a per-font !important rule that beats `.form-shell *` Outfit.
    $sigFonts = [
        'Caveat'         => ['label' => 'Casual',  'slug' => 'caveat'],
        'Dancing Script' => ['label' => 'Flowing', 'slug' => 'dancing-script'],
        'Great Vibes'    => ['label' => 'Elegant', 'slug' => 'great-vibes'],
        'Sacramento'     => ['label' => 'Classic', 'slug' => 'sacramento'],
    ];
@endphp

<link href="https://fonts.googleapis.com/css2?family=Caveat:wght@500;700&family=Dancing+Script:wght@500;700&family=Great+Vibes&family=Sacramento&display=swap" rel="stylesheet">

<div class="sigpad-wrapper" id="sigpadWrapper">

    {{-- Saved signature (offered to both tabs) --}}
    @if($savedSignature && $savedSignature->image_url)
        <div class="sigpad-saved">
            <div class="sigpad-saved__preview">
                <img src="{{ $savedSignature->image_url }}" alt="Saved signature">
            </div>
            <label class="sigpad-saved__toggle">
                <input type="checkbox" id="reuse_saved_signature_checkbox">
                <span>Use my saved signature</span>
            </label>
        </div>
    @endif

    {{-- Tab switcher --}}
    <div class="sigpad-tabs" role="tablist">
        <button type="button" class="sigpad-tab is-active" data-sigpad-tab="draw" role="tab">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/><circle cx="11" cy="11" r="2"/></svg>
            Draw
        </button>
        <button type="button" class="sigpad-tab" data-sigpad-tab="type" role="tab">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="4 7 4 4 20 4 20 7"/><line x1="9" y1="20" x2="15" y2="20"/><line x1="12" y1="4" x2="12" y2="20"/></svg>
            Type
        </button>
    </div>

    {{-- ============================= DRAW PANEL ============================= --}}
    <div class="sigpad-panel" data-sigpad-panel="draw">
        <div class="sigpad" id="sigpadContainer">
            <canvas id="signaturePad" width="640" height="200"></canvas>
            <div class="sigpad-actions">
                <button type="button" class="btn-link" id="sigpadClearBtn">Clear</button>
                <span class="sigpad-hint">Sign above with your mouse, stylus or finger</span>
            </div>
        </div>
    </div>

    {{-- ============================= TYPE PANEL ============================= --}}
    <div class="sigpad-panel" data-sigpad-panel="type" hidden>
        <div class="sigtyped-name">
            <label class="sigtyped-name__label" for="sigtypedName">Your full name</label>
            <input type="text" id="sigtypedName" class="sigtyped-name__input"
                   value="{{ $signerName }}" maxlength="120" autocomplete="off"
                   placeholder="e.g. Jane Mensah">
        </div>

        <div class="sigtyped-fonts">
            @foreach($sigFonts as $family => $meta)
                <label class="sigtyped-card {{ $loop->first ? 'is-selected' : '' }}">
                    <input type="radio" name="_sigtyped_font" value="{{ $family }}"
                           {{ $loop->first ? 'checked' : '' }}>
                    <div class="sigtyped-card__preview sigtyped-card__preview--{{ $meta['slug'] }}"
                         data-sigtyped-preview>{{ $signerName ?: 'Your Name' }}</div>
                    <div class="sigtyped-card__meta">
                        <span>{{ $meta['label'] }}</span>
                        <span class="sigtyped-card__check">
                            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </span>
                    </div>
                </label>
            @endforeach
        </div>

        <p class="sigtyped-disclaimer">
            By typing your name and choosing a style, you agree the rendered image
            represents your legal signature on this form.
        </p>

        <canvas id="sigtypedCanvas" width="640" height="200" hidden></canvas>
    </div>

    {{-- Optional: save what I just signed/typed as my reusable signature --}}
    <label class="sigpad-save-toggle">
        <input type="checkbox" name="save_as_my_signature" value="1" id="save_as_my_signature_checkbox">
        <span>
            {{ $savedSignature && $savedSignature->image_url ? 'Replace my saved signature with this one' : 'Save this as my signature for next time' }}
        </span>
    </label>

    <input type="hidden" name="signature_data" id="signature_data_input" value="">
    <input type="hidden" name="reuse_saved_signature" id="reuse_saved_signature_input" value="">
</div>

<style>
.sigpad-wrapper { background: #fafafa; padding: 16px; border-radius: 12px; border: 1.5px solid #ebebeb; font-family: 'Outfit', sans-serif !important; }
.sigpad-wrapper * { box-sizing: border-box; }

/* Saved sig block — sits above tabs, always visible */
.sigpad-saved { background: #fff; padding: 12px; border-radius: 10px; border: 1.5px solid #ebebeb; margin-bottom: 12px; display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
.sigpad-saved__preview { width: 180px; height: 70px; background: #fff; border: 1.5px dashed #d4d7de; border-radius: 6px; display: flex; align-items: center; justify-content: center; padding: 4px; flex-shrink: 0; }
.sigpad-saved__preview img { max-width: 100%; max-height: 100%; }
.sigpad-saved__toggle { display: inline-flex; gap: 8px; align-items: center; font-size: 0.84rem; font-weight: 500; color: #374151; cursor: pointer; margin: 0; }
.sigpad-saved__toggle input { accent-color: #0c0c0c; }

/* Tab switcher */
.sigpad-tabs { display: inline-flex; background: #fff; border: 1.5px solid #ebebeb; border-radius: 10px; padding: 4px; gap: 2px; margin-bottom: 12px; }
.sigpad-tab { display: inline-flex; align-items: center; gap: 6px; padding: 7px 14px; background: transparent; border: none; border-radius: 7px; color: #6b7280; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.sigpad-tab:hover:not(.is-active) { color: #0c0c0c; }
.sigpad-tab.is-active { background: #0c0c0c; color: #fff; }

/* Draw panel */
.sigpad { background: #fff; border: 2px dashed #d4d7de; border-radius: 10px; padding: 8px; transition: border-color .15s; }
.sigpad:focus-within { border-color: #0c0c0c; }
.sigpad canvas { width: 100%; max-width: 100%; height: 180px; touch-action: none; cursor: crosshair; background: #fff; border-radius: 6px; }
.sigpad-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding: 0 8px; }
.btn-link { background: none; border: none; color: #0c0c0c; font-weight: 600; cursor: pointer; padding: 4px 10px; font-size: 0.82rem; border-radius: 6px; transition: background .12s; font-family: 'Outfit', sans-serif !important; }
.btn-link:hover { background: #f3f4f6; }
.sigpad-hint { color: #b0b5c0; font-size: 0.74rem; font-style: italic; }
.sigpad--disabled canvas { background: #f9fafb; pointer-events: none; opacity: 0.4; }

/* Type panel */
.sigtyped-name { margin-bottom: 14px; }
.sigtyped-name__label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
.sigtyped-name__input { width: 100%; padding: 11px 14px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 10px; font-size: 0.9rem; color: #111827; outline: none; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.sigtyped-name__input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }

/* minmax(0, 1fr) — without the 0 floor, each 1fr column inflates to fit the
   nowrap preview text and pushes the right-hand cards off the panel. */
.sigtyped-fonts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
@media (min-width: 720px) { .sigtyped-fonts { grid-template-columns: repeat(4, minmax(0, 1fr)); } }
.sigtyped-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 12px; padding: 14px 10px 10px; cursor: pointer; transition: all .15s; display: flex; flex-direction: column; gap: 10px; margin: 0; position: relative; min-height: 110px; min-width: 0; overflow: hidden; }
.sigtyped-card:hover { border-color: #0c0c0c; }
.sigtyped-card.is-selected { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.sigtyped-card input { display: none; }
.sigtyped-card__preview { flex: 1; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; color: #111827; line-height: 1.15; text-align: center; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; padding: 0 4px; min-height: 48px; min-width: 0; max-width: 100%; }

/* Per-font overrides — the form-shell wildcard rule forces Outfit with
   !important on every descendant, so each cursive face needs its own
   !important to win the cascade and actually render differently. */
.sigtyped-card__preview--caveat         { font-family: 'Caveat', cursive !important; font-weight: 700; }
.sigtyped-card__preview--dancing-script { font-family: 'Dancing Script', cursive !important; font-weight: 600; }
.sigtyped-card__preview--great-vibes    { font-family: 'Great Vibes', cursive !important; font-size: 1.7rem; }
.sigtyped-card__preview--sacramento     { font-family: 'Sacramento', cursive !important; font-size: 1.55rem; }
.sigtyped-card__meta { display: flex; align-items: center; justify-content: space-between; font-size: 0.7rem; color: #9ca3af; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; }
.sigtyped-card__check { width: 18px; height: 18px; border-radius: 50%; border: 1.5px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; color: transparent; transition: all .15s; }
.sigtyped-card.is-selected .sigtyped-card__check { background: #0c0c0c; color: #fff; border-color: #0c0c0c; }

.sigtyped-disclaimer { margin: 12px 0 0; font-size: 0.72rem; color: #9ca3af; line-height: 1.5; }

.sigpad-save-toggle { display: inline-flex; align-items: center; gap: 8px; margin-top: 14px; padding: 8px 12px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 9px; font-size: 0.8rem; font-weight: 500; color: #374151; cursor: pointer; transition: all .15s; }
.sigpad-save-toggle:hover { border-color: #0c0c0c; color: #0c0c0c; }
.sigpad-save-toggle input { accent-color: #0c0c0c; }
.is_dark .sigpad-save-toggle { background: #111827; border-color: #2d3748; color: #d1d5db; }
.is_dark .sigpad-save-toggle:hover { border-color: #f3f4f6; color: #f3f4f6; }

/* Dark mode */
.is_dark .sigpad-wrapper { background: #0f172a; border-color: #1e2330; }
.is_dark .sigpad-saved { background: #111827; border-color: #2d3748; }
.is_dark .sigpad-tabs { background: #111827; border-color: #1e2330; }
.is_dark .sigpad-tab { color: #9ca3af; }
.is_dark .sigpad-tab.is-active { background: #f3f4f6; color: #0c0c0c; }
.is_dark .sigpad { background: #111827; border-color: #2d3748; }
.is_dark .sigpad canvas { background: #fff; }
.is_dark .btn-link { color: #f3f4f6; }
.is_dark .sigtyped-name__input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .sigtyped-name__input:focus { border-color: #f3f4f6; }
.is_dark .sigtyped-card { background: #111827; border-color: #2d3748; }
.is_dark .sigtyped-card:hover { border-color: #f3f4f6; }
.is_dark .sigtyped-card.is-selected { border-color: #f3f4f6; box-shadow: 0 0 0 3px rgba(255,255,255,.06); }
.is_dark .sigtyped-card__preview { color: #f3f4f6; }
.is_dark .sigtyped-card.is-selected .sigtyped-card__check { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
</style>

<script>
(function () {
    const wrapper      = document.getElementById('sigpadWrapper');
    if (!wrapper) return;

    const dataInput    = document.getElementById('signature_data_input');
    const reuseInput   = document.getElementById('reuse_saved_signature_input');
    const reuseChk     = document.getElementById('reuse_saved_signature_checkbox');

    // ─────────────── DRAW MODE ───────────────
    const canvas       = document.getElementById('signaturePad');
    const ctx          = canvas.getContext('2d');
    const clearBtn     = document.getElementById('sigpadClearBtn');
    const drawContainer= document.getElementById('sigpadContainer');

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect  = canvas.getBoundingClientRect();
        if (rect.width === 0) return;
        canvas.width  = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineWidth   = 2;
        ctx.lineCap     = 'round';
        ctx.strokeStyle = '#111827';
        ctx.fillStyle   = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    let drawing = false, last = null, drawnNonEmpty = false;

    function pos(e) {
        const rect = canvas.getBoundingClientRect();
        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
        return { x, y };
    }
    function start(e) {
        if (reuseChk && reuseChk.checked) return;
        e.preventDefault(); drawing = true; last = pos(e);
    }
    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        const cur = pos(e);
        ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(cur.x, cur.y); ctx.stroke();
        last = cur; drawnNonEmpty = true;
        syncDrawToInput();
    }
    function end() { drawing = false; }

    function syncDrawToInput() {
        if (reuseChk && reuseChk.checked) {
            dataInput.value = ''; reuseInput.value = '1'; return;
        }
        reuseInput.value = '';
        dataInput.value  = drawnNonEmpty ? canvas.toDataURL('image/png') : '';
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move,  { passive: false });
    canvas.addEventListener('touchend', end);

    clearBtn.addEventListener('click', function () {
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        drawnNonEmpty = false;
        dataInput.value = '';
    });

    // ─────────────── TYPE MODE ───────────────
    const typedInput   = document.getElementById('sigtypedName');
    const typedCanvas  = document.getElementById('sigtypedCanvas');
    const fontRadios   = wrapper.querySelectorAll('input[name="_sigtyped_font"]');
    const previewEls   = wrapper.querySelectorAll('[data-sigtyped-preview]');
    const fontCards    = wrapper.querySelectorAll('.sigtyped-card');

    function currentTypedFont() {
        const r = wrapper.querySelector('input[name="_sigtyped_font"]:checked');
        return r ? r.value : 'Caveat';
    }

    function refreshTypedPreviews() {
        const text = (typedInput.value || '').trim() || 'Your Name';
        previewEls.forEach(el => { el.textContent = text; });
    }

    async function renderTypedToCanvas() {
        const text = (typedInput.value || '').trim();
        const family = currentTypedFont();

        if (!text) { return null; }

        // Wait for the chosen webfont to be ready before rendering.
        try { await document.fonts.load(`72px "${family}"`); } catch (_) {}

        const dpr = Math.max(window.devicePixelRatio || 1, 1);
        const cssW = 640, cssH = 200;
        typedCanvas.width  = cssW * dpr;
        typedCanvas.height = cssH * dpr;
        const tctx = typedCanvas.getContext('2d');
        tctx.setTransform(1, 0, 0, 1, 0, 0);
        tctx.scale(dpr, dpr);

        tctx.fillStyle = '#fff';
        tctx.fillRect(0, 0, cssW, cssH);

        tctx.fillStyle    = '#111827';
        tctx.textBaseline = 'middle';
        tctx.textAlign    = 'center';

        let fontSize = 96;
        tctx.font = `${fontSize}px "${family}", cursive`;
        while (tctx.measureText(text).width > cssW * 0.9 && fontSize > 28) {
            fontSize -= 4;
            tctx.font = `${fontSize}px "${family}", cursive`;
        }

        tctx.fillText(text, cssW / 2, cssH / 2);
        return typedCanvas.toDataURL('image/png');
    }

    async function syncTypedToInput() {
        if (reuseChk && reuseChk.checked) {
            dataInput.value = ''; reuseInput.value = '1'; return;
        }
        const png = await renderTypedToCanvas();
        if (png) {
            reuseInput.value = '';
            dataInput.value  = png;
        } else {
            dataInput.value  = '';
        }
    }

    typedInput.addEventListener('input', function () {
        refreshTypedPreviews();
        if (currentTab() === 'type') syncTypedToInput();
    });

    fontCards.forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        card.addEventListener('click', function () {
            fontCards.forEach(c => c.classList.remove('is-selected'));
            card.classList.add('is-selected');
            if (radio) radio.checked = true;
            if (currentTab() === 'type') syncTypedToInput();
        });
    });

    // ─────────────── TAB SWITCHING ───────────────
    const tabs   = wrapper.querySelectorAll('[data-sigpad-tab]');
    const panels = wrapper.querySelectorAll('[data-sigpad-panel]');

    function currentTab() {
        const active = wrapper.querySelector('.sigpad-tab.is-active');
        return active ? active.dataset.sigpadTab : 'draw';
    }

    function switchTab(name) {
        tabs.forEach(t => t.classList.toggle('is-active', t.dataset.sigpadTab === name));
        panels.forEach(p => {
            if (p.dataset.sigpadPanel === name) p.removeAttribute('hidden');
            else p.setAttribute('hidden', '');
        });
        if (name === 'draw') {
            resizeCanvas();
            // Re-stroke the existing draw is lost on resize; preserve only if non-empty.
            if (drawnNonEmpty) syncDrawToInput();
        } else {
            syncTypedToInput();
        }
    }

    tabs.forEach(t => t.addEventListener('click', () => switchTab(t.dataset.sigpadTab)));

    // ─────────────── REUSE SAVED SIGNATURE ───────────────
    if (reuseChk) {
        reuseChk.addEventListener('change', function () {
            if (this.checked) {
                drawContainer.classList.add('sigpad--disabled');
                wrapper.querySelectorAll('.sigtyped-card, .sigtyped-name__input')
                       .forEach(el => el.classList.add('sigpad--disabled'));
                reuseInput.value = '1';
                dataInput.value  = '';
            } else {
                drawContainer.classList.remove('sigpad--disabled');
                wrapper.querySelectorAll('.sigtyped-card, .sigtyped-name__input')
                       .forEach(el => el.classList.remove('sigpad--disabled'));
                reuseInput.value = '';
                if (currentTab() === 'type') syncTypedToInput();
                else syncDrawToInput();
            }
        });
    }
})();
</script>
