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
.sigpad-wrapper { background: #f9fafb; padding: 16px; border-radius: 10px; border: 1px solid #e5e7eb; }
.sigpad-saved { background: #fff; padding: 12px; border-radius: 8px; border: 1px solid #e5e7eb; margin-bottom: 12px; display: flex; gap: 16px; align-items: center; }
.sigpad-saved__preview { width: 200px; height: 80px; background: #fff; border: 1px dashed #d1d5db; display: flex; align-items: center; justify-content: center; padding: 4px; }
.sigpad-saved__preview img { max-width: 100%; max-height: 100%; }
.sigpad-saved__toggle { display: inline-flex; gap: 8px; align-items: center; font-weight: 500; color: #374151; cursor: pointer; margin: 0; }
.sigpad { background: #fff; border: 2px dashed #cbd5e1; border-radius: 10px; padding: 8px; }
.sigpad canvas { width: 100%; max-width: 100%; height: 200px; touch-action: none; cursor: crosshair; background: #fff; border-radius: 4px; }
.sigpad-actions { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding: 0 8px; }
.btn-link { background: none; border: none; color: #1d4ed8; font-weight: 500; cursor: pointer; padding: 4px 8px; }
.btn-link:hover { text-decoration: underline; }
.sigpad-hint { color: #9ca3af; font-size: 12px; }
.sigpad--disabled canvas { background: #f3f4f6; pointer-events: none; opacity: 0.5; }
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
