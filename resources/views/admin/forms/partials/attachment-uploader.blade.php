{{--
    Shared file-upload dropzone used by every form that captures attachments
    (compose page for stage 1, show page for every subsequent stage).

    Behaviour:
      - Click anywhere on the dropzone to open the OS file picker.
      - Picking files APPENDS to the current selection rather than replacing
        it (the native <input type=file multiple> would otherwise overwrite
        the previous pick).
      - Each picked file appears as a row below the dropzone with a red ×
        button — clicking the × removes that specific file from the
        submission before it's sent.
      - The actual <input type=file>'s FileList is rebuilt via DataTransfer
        on every change so the form submits exactly the visible list.
      - Duplicate files (same name + size) are silently skipped.

    Optional variables:
      - $inputId  : id for the <input type=file> + DOM hook (default attachmentsInput)
      - $listId   : id for the preview list container (default uploadList)
      - $accept   : accept="" attribute (default "" = any file type)
      - $helpText : small grey line under the title (default sensible)
--}}
@php
    $inputId  = $inputId  ?? 'attachmentsInput';
    $listId   = $listId   ?? 'uploadList';
    $accept   = $accept   ?? '';
    $helpText = $helpText ?? 'PDF, DOC, JPG, PNG — multiple files allowed';
@endphp

<label class="upload-dropzone" for="{{ $inputId }}">
    <input type="file" name="attachments[]" multiple id="{{ $inputId }}" hidden @if($accept) accept="{{ $accept }}" @endif>
    <div class="upload-dropzone__icon">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
    </div>
    <div class="upload-dropzone__text">
        <strong>Click to choose files</strong>
        <small>{{ $helpText }}</small>
    </div>
</label>
<div class="upload-list" id="{{ $listId }}" data-uploader-input="{{ $inputId }}"></div>

@once
<style>
.upload-list__item--removable { padding-right: 8px; }
.upload-list__remove {
    flex-shrink: 0;
    width: 28px; height: 28px;
    display: inline-flex; align-items: center; justify-content: center;
    background: transparent;
    border: 1.5px solid #fee2e2;
    border-radius: 8px;
    color: #b91c1c;
    cursor: pointer;
    transition: background .15s, border-color .15s, transform .15s;
    padding: 0;
}
.upload-list__remove:hover {
    background: #fef2f2;
    border-color: #fecaca;
    transform: scale(1.04);
}
.upload-list__remove:active { transform: scale(0.96); }
.upload-list__remove svg { display: block; }
.is_dark .upload-list__remove { border-color: rgba(185, 28, 28, 0.35); color: #fca5a5; }
.is_dark .upload-list__remove:hover { background: rgba(185, 28, 28, 0.15); border-color: rgba(185, 28, 28, 0.55); }

/* Empty-state hint shown when the list is rendered but no files picked yet */
.upload-list__empty {
    margin-top: 8px;
    padding: 10px 12px;
    font-size: 0.78rem;
    color: #9ca3af;
    font-style: italic;
    text-align: center;
    border: 1px dashed #ebebeb;
    border-radius: 8px;
}
.is_dark .upload-list__empty { color: #6b7280; border-color: #2d3748; }
</style>
<script>
/**
 * Multi-instance attachment-uploader wiring.
 *
 * Initialises every <div class="upload-list" data-uploader-input="…"> on the
 * page. Each list manages a DataTransfer accumulator and keeps the bound
 * <input type=file>'s FileList in sync.
 *
 * Guarded by a global flag so re-includes of the partial don't double-bind.
 */
(function () {
    if (window.__attachmentUploaderInit) return;
    window.__attachmentUploaderInit = true;

    function humanSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        const kb = bytes / 1024;
        if (kb < 1024) return kb.toFixed(1) + ' KB';
        return (kb / 1024).toFixed(1) + ' MB';
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function bindUploader(listEl) {
        const inputId = listEl.dataset.uploaderInput;
        const input = inputId ? document.getElementById(inputId) : null;
        if (!input) return;

        // Accumulator — the source of truth for the bound input's FileList.
        const dt = new DataTransfer();

        function rerender() {
            listEl.innerHTML = '';
            const files = Array.from(dt.files);
            if (files.length === 0) return; // no empty placeholder needed — the dropzone already prompts

            files.forEach(function (f, idx) {
                const row = document.createElement('div');
                row.className = 'upload-list__item upload-list__item--removable';
                row.innerHTML =
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>' +
                    '<span class="upload-list__name">' + escapeHtml(f.name) + '</span>' +
                    '<span class="upload-list__size">' + humanSize(f.size) + '</span>' +
                    '<button type="button" class="upload-list__remove" data-remove-index="' + idx + '" aria-label="Remove ' + escapeHtml(f.name) + '" title="Remove this file">' +
                    '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>' +
                    '</button>';
                listEl.appendChild(row);
            });
        }

        input.addEventListener('change', function () {
            // Append new picks to the accumulator (skipping exact duplicates),
            // then write the accumulator back to the input so the form
            // submits everything visible — not just the latest pick.
            Array.from(input.files).forEach(function (f) {
                const isDup = Array.from(dt.files).some(function (x) {
                    return x.name === f.name && x.size === f.size && x.lastModified === f.lastModified;
                });
                if (!isDup) dt.items.add(f);
            });
            try { input.files = dt.files; } catch (e) { /* some legacy browsers reject — list still renders */ }
            rerender();
        });

        listEl.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-remove-index]');
            if (!btn) return;
            const idx = parseInt(btn.dataset.removeIndex, 10);
            if (isNaN(idx)) return;
            dt.items.remove(idx);
            try { input.files = dt.files; } catch (e) { /* see above */ }
            rerender();
        });
    }

    function initAll() {
        document.querySelectorAll('.upload-list[data-uploader-input]').forEach(bindUploader);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAll);
    } else {
        initAll();
    }
})();
</script>
@endonce
