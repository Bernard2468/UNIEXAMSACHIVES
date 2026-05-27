{{--
    HTML5 signature pad. Posts a base64 PNG in `signature_data`.
    If the user has a saved signature on their profile, they can tick
    "Use my saved signature" instead of redrawing.

    Inputs posted to the controller:
      - signature_data         (base64 PNG, may be empty if reuse_saved=1)
      - reuse_saved_signature  ("1" or "")
--}}
@php $savedSignature = $savedSignature ?? null; @endphp

<div class="sigpad-wrapper">
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

    <div class="sigpad" id="sigpadContainer">
        <canvas id="signaturePad" width="640" height="200"></canvas>
        <div class="sigpad-actions">
            <button type="button" class="btn-link" id="sigpadClearBtn">Clear</button>
            <span class="sigpad-hint">Sign above</span>
        </div>
    </div>

    <input type="hidden" name="signature_data" id="signature_data_input" value="">
    <input type="hidden" name="reuse_saved_signature" id="reuse_saved_signature_input" value="">
</div>

<style>
.sigpad-wrapper { background: #fafafa; padding: 16px; border-radius: 12px; border: 1.5px solid #ebebeb; font-family: 'Outfit', sans-serif !important; }
.sigpad-saved { background: #fff; padding: 12px; border-radius: 10px; border: 1.5px solid #ebebeb; margin-bottom: 12px; display: flex; gap: 14px; align-items: center; flex-wrap: wrap; }
.sigpad-saved__preview { width: 180px; height: 70px; background: #fff; border: 1.5px dashed #d4d7de; border-radius: 6px; display: flex; align-items: center; justify-content: center; padding: 4px; flex-shrink: 0; }
.sigpad-saved__preview img { max-width: 100%; max-height: 100%; }
.sigpad-saved__toggle { display: inline-flex; gap: 8px; align-items: center; font-size: 0.84rem; font-weight: 500; color: #374151; cursor: pointer; margin: 0; }
.sigpad-saved__toggle input { accent-color: #0c0c0c; }
.sigpad { background: #fff; border: 2px dashed #d4d7de; border-radius: 10px; padding: 8px; transition: border-color .15s; }
.sigpad:focus-within { border-color: #0c0c0c; }
.sigpad canvas { width: 100%; max-width: 100%; height: 180px; touch-action: none; cursor: crosshair; background: #fff; border-radius: 6px; }
.sigpad-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding: 0 8px; }
.btn-link { background: none; border: none; color: #0c0c0c; font-weight: 600; cursor: pointer; padding: 4px 10px; font-size: 0.82rem; border-radius: 6px; transition: background .12s; font-family: 'Outfit', sans-serif !important; }
.btn-link:hover { background: #f3f4f6; text-decoration: none; }
.sigpad-hint { color: #b0b5c0; font-size: 0.74rem; font-style: italic; }
.sigpad--disabled canvas { background: #f9fafb; pointer-events: none; opacity: 0.4; }
.is_dark .sigpad-wrapper { background: #0f172a; border-color: #1e2330; }
.is_dark .sigpad-saved { background: #111827; border-color: #2d3748; }
.is_dark .sigpad { background: #111827; border-color: #2d3748; }
.is_dark .sigpad canvas { background: #fff; }
.is_dark .btn-link { color: #f3f4f6; }
</style>

<script>
(function () {
    const canvas = document.getElementById('signaturePad');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    const dataInput = document.getElementById('signature_data_input');
    const reuseInput = document.getElementById('reuse_saved_signature_input');
    const reuseChk = document.getElementById('reuse_saved_signature_checkbox');
    const clearBtn = document.getElementById('sigpadClearBtn');
    const container = document.getElementById('sigpadContainer');

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        const rect = canvas.getBoundingClientRect();
        canvas.width = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2;
        ctx.lineCap = 'round';
        ctx.strokeStyle = '#111827';
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
    }
    resizeCanvas();
    window.addEventListener('resize', resizeCanvas);

    let drawing = false;
    let last = null;
    let isEmpty = true;

    function pos(e) {
        const rect = canvas.getBoundingClientRect();
        const x = (e.touches ? e.touches[0].clientX : e.clientX) - rect.left;
        const y = (e.touches ? e.touches[0].clientY : e.clientY) - rect.top;
        return { x, y };
    }

    function start(e) {
        if (reuseChk && reuseChk.checked) return;
        e.preventDefault();
        drawing = true;
        last = pos(e);
    }

    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        const cur = pos(e);
        ctx.beginPath();
        ctx.moveTo(last.x, last.y);
        ctx.lineTo(cur.x, cur.y);
        ctx.stroke();
        last = cur;
        isEmpty = false;
        syncOutput();
    }

    function end() {
        drawing = false;
    }

    function syncOutput() {
        if (reuseChk && reuseChk.checked) {
            dataInput.value = '';
            reuseInput.value = '1';
            return;
        }
        reuseInput.value = '';
        dataInput.value = isEmpty ? '' : canvas.toDataURL('image/png');
    }

    canvas.addEventListener('mousedown', start);
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('mouseup', end);
    canvas.addEventListener('mouseleave', end);
    canvas.addEventListener('touchstart', start, { passive: false });
    canvas.addEventListener('touchmove', move, { passive: false });
    canvas.addEventListener('touchend', end);

    clearBtn.addEventListener('click', function () {
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        isEmpty = true;
        dataInput.value = '';
    });

    if (reuseChk) {
        reuseChk.addEventListener('change', function () {
            if (this.checked) {
                container.classList.add('sigpad--disabled');
                reuseInput.value = '1';
                dataInput.value = '';
            } else {
                container.classList.remove('sigpad--disabled');
                reuseInput.value = '';
                syncOutput();
            }
        });
    }
})();
</script>
