{{--
    Per-stage attachments — rendered INSIDE the section panel, so a reviewer
    looking at the Requisitioner section sees exactly what the requisitioner
    attached. Same for HOD, Finance, etc.

    Expected variables (all required):
      - $submission       : App\Models\FormSubmission
      - $stageAttachments : Collection of FormAttachment for this stage

    Behavior: clicking a tile (anywhere) opens the global attachment-viewer
    modal scoped to THIS stage's attachments, so prev/next inside the modal
    stays within the section the user clicked from.
--}}
@if(($stageAttachments ?? collect())->count() > 0)
@php
    // Pre-encode this stage's attachments for the modal call. Encoding ONCE
    // for the whole section keeps the markup small and avoids per-tile JSON.
    $stageAttItems = $stageAttachments->map(fn($att) => [
        'id'    => $att->id,
        'url'   => route('admin.forms.attachment', [$submission->id, $att->id]),
        'name'  => $att->name,
        'size'  => $att->human_size,
        'mime'  => (string) $att->mime_type,
        'stage' => $stage->label ?? null,
    ])->values()->toArray();

    // Encode with JSON_HEX_* so all literal quotes become \uXXXX escapes.
    // That makes the JSON safe to drop into an HTML attribute, where Blade's
    // automatic htmlspecialchars() (via the {{ }} echo) just passes through
    // the backslashes harmlessly. DO NOT wrap this in e() — {{ }} already does.
    $stageAttJson = json_encode($stageAttItems, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP);

    // Classify file type for the per-tile icon.
    $tileKind = function (string $mime, string $name): string {
        $lc = strtolower($name);
        if (str_starts_with($mime, 'image/')) return 'image';
        if ($mime === 'application/pdf' || str_ends_with($lc, '.pdf')) return 'pdf';
        if (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true)
            || str_ends_with($lc, '.doc') || str_ends_with($lc, '.docx')) return 'doc';
        if (str_ends_with($lc, '.xls') || str_ends_with($lc, '.xlsx') || str_ends_with($lc, '.csv')) return 'sheet';
        return 'file';
    };
@endphp

<div class="stage-att" data-att-items="{{ $stageAttJson }}">
    <div class="stage-att__head">
        <span class="stage-att__icon">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
        </span>
        <span class="stage-att__label">Attached file{{ $stageAttachments->count() === 1 ? '' : 's' }}</span>
        <span class="stage-att__count">{{ $stageAttachments->count() }}</span>
    </div>

    <div class="stage-att__grid">
        @foreach($stageAttachments as $i => $att)
            @php $kind = $tileKind((string) $att->mime_type, (string) $att->name); @endphp
            <button type="button"
                    class="stage-att__tile"
                    data-att-index="{{ $i }}"
                    onclick="window.attachmentViewer && window.attachmentViewer.open(JSON.parse(this.closest('.stage-att').dataset.attItems), {{ $i }})"
                    aria-label="View {{ $att->name }}">
                <span class="stage-att__tile-icon stage-att__tile-icon--{{ $kind }}">
                    @switch($kind)
                        @case('pdf')
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13h6M9 17h4" stroke-width="1.6"/></svg>
                            @break
                        @case('image')
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                            @break
                        @case('doc')
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/></svg>
                            @break
                        @case('sheet')
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8M8 17h8M12 13v8" stroke-width="1.6"/></svg>
                            @break
                        @default
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                    @endswitch
                </span>
                <span class="stage-att__tile-body">
                    <span class="stage-att__tile-name" title="{{ $att->name }}">{{ $att->name }}</span>
                    <span class="stage-att__tile-size">{{ $att->human_size }}</span>
                </span>
                <span class="stage-att__tile-cta" aria-hidden="true">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span>View</span>
                </span>
            </button>
        @endforeach
    </div>

</div>

@once
{{-- One-shot styles + behavior for stage-attachments. Pushed into the page
     once even if the partial is included for every stage. --}}
<style>
    /* Row container around title text + clip badge — the parent .form-panel__title
       is flex-direction: column (for its underline accent), so we wrap text+badge
       in a horizontal inner row to keep them side-by-side. */
    .stage-title-row { display: inline-flex; align-items: center; gap: 0; flex-wrap: wrap; }

    /* ============ CLIP BADGE — glowing-bubble indicator ============
       Sits next to the section title. White bubble holds the paperclip icon,
       indigo halo around it gently breathes to catch the eye. The number
       sits beside the bubble. Subtle, premium, futuristic — not gaudy. */
    .stage-clip-badge {
        display: inline-flex; align-items: center; gap: 8px;
        align-self: center;          /* vertically centered against the title block */
        flex-shrink: 0;              /* never let the flex parent crush it */
        margin-left: auto;           /* fallback push-right if parent isn't space-between */
        font-family: -apple-system, BlinkMacSystemFont, 'Inter', sans-serif;
        line-height: 1;
        text-transform: none;
        letter-spacing: 0;
    }
    .stage-clip-badge__bubble {
        position: relative;
        width: 30px; height: 30px;
        border-radius: 50%;
        background: linear-gradient(140deg, #ffffff 0%, #f8fafc 100%);
        border: 1.5px solid rgba(99, 102, 241, 0.4);
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        box-shadow:
            0 0 0 3px rgba(99, 102, 241, 0.12),
            0 0 14px rgba(99, 102, 241, 0.45),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
        animation: clipBadgeBreathe 2.6s ease-in-out infinite;
    }
    @keyframes clipBadgeBreathe {
        0%, 100% {
            box-shadow:
                0 0 0 3px rgba(99, 102, 241, 0.12),
                0 0 14px rgba(99, 102, 241, 0.45),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }
        50% {
            box-shadow:
                0 0 0 5px rgba(99, 102, 241, 0.06),
                0 0 24px rgba(99, 102, 241, 0.65),
                inset 0 1px 0 rgba(255, 255, 255, 0.8);
        }
    }
    .stage-clip-badge__img {
        width: 20px; height: 20px;
        display: block;
        pointer-events: none;
        /* slight drop shadow so the icon "sits" inside the bubble */
        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.08));
    }
    .stage-clip-badge__count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 18px; height: 18px;
        padding: 0 6px;
        background: linear-gradient(135deg, #6366f1, #8b5cf6);
        color: #fff;
        border-radius: 999px;
        font-size: 10.5px; font-weight: 800;
        font-variant-numeric: tabular-nums;
        box-shadow: 0 1px 3px rgba(99, 102, 241, 0.35);
    }

    /* Dark mode — invert the bubble surface, keep the indigo halo */
    .is_dark .stage-clip-badge__bubble {
        background: linear-gradient(140deg, #1e293b 0%, #0f172a 100%);
        border-color: rgba(165, 180, 252, 0.45);
        box-shadow:
            0 0 0 3px rgba(99, 102, 241, 0.18),
            0 0 18px rgba(99, 102, 241, 0.55),
            inset 0 1px 0 rgba(255, 255, 255, 0.06);
    }
    .is_dark .stage-clip-badge__img { filter: drop-shadow(0 1px 1px rgba(0, 0, 0, 0.5)); }

    /* Respect reduced-motion users — kill the pulse but keep the glow */
    @media (prefers-reduced-motion: reduce) {
        .stage-clip-badge__bubble { animation: none; }
    }

    .stage-att {
        margin-top: 14px;
        padding: 12px 14px;
        background: #f8fafc;
        border: 1px dashed #e2e8f0;
        border-radius: 12px;
    }
    .is_dark .stage-att { background: rgba(255, 255, 255, 0.025); border-color: rgba(255, 255, 255, 0.08); }

    .stage-att__head {
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 10px;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .is_dark .stage-att__head { color: #cbd5e1; }
    .stage-att__icon {
        width: 22px; height: 22px;
        border-radius: 6px;
        background: #eef2ff;
        color: #4338ca;
        display: inline-flex; align-items: center; justify-content: center;
    }
    .is_dark .stage-att__icon { background: rgba(67, 56, 202, 0.18); color: #a5b4fc; }
    .stage-att__label { letter-spacing: 0.5px; }
    .stage-att__count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 20px; height: 18px;
        padding: 0 6px;
        background: #4338ca; color: #fff;
        border-radius: 999px;
        font-size: 10.5px; font-weight: 700;
        margin-left: 2px;
        letter-spacing: 0;
    }

    .stage-att__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: 10px;
    }

    .stage-att__tile {
        display: flex; align-items: center; gap: 11px;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        cursor: pointer;
        text-align: left;
        font-family: inherit;
        position: relative;
        transition: border-color .15s, box-shadow .15s, transform .15s;
    }
    .stage-att__tile:hover {
        border-color: #4338ca;
        box-shadow: 0 4px 14px rgba(67, 56, 202, 0.12);
        transform: translateY(-1px);
    }
    .stage-att__tile:active { transform: translateY(0); }
    .is_dark .stage-att__tile { background: #111827; border-color: #2d3748; }
    .is_dark .stage-att__tile:hover { border-color: #6366f1; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.18); }

    .stage-att__tile-icon {
        width: 34px; height: 34px;
        border-radius: 8px;
        display: inline-flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .stage-att__tile-icon--pdf   { background: #fef2f2; color: #b91c1c; }
    .stage-att__tile-icon--image { background: #ecfeff; color: #0891b2; }
    .stage-att__tile-icon--doc   { background: #eff6ff; color: #1d4ed8; }
    .stage-att__tile-icon--sheet { background: #ecfdf5; color: #047857; }
    .stage-att__tile-icon--file  { background: #f3f4f6; color: #4b5563; }
    .is_dark .stage-att__tile-icon--pdf   { background: rgba(185, 28, 28, 0.18); color: #fca5a5; }
    .is_dark .stage-att__tile-icon--image { background: rgba(8, 145, 178, 0.18); color: #67e8f9; }
    .is_dark .stage-att__tile-icon--doc   { background: rgba(29, 78, 216, 0.18); color: #93c5fd; }
    .is_dark .stage-att__tile-icon--sheet { background: rgba(4, 120, 87, 0.18); color: #6ee7b7; }
    .is_dark .stage-att__tile-icon--file  { background: rgba(75, 85, 99, 0.25); color: #d1d5db; }

    .stage-att__tile-body { flex: 1; min-width: 0; display: flex; flex-direction: column; gap: 2px; }
    .stage-att__tile-name {
        font-size: 13px; font-weight: 600;
        color: #111827;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .is_dark .stage-att__tile-name { color: #f3f4f6; }
    .stage-att__tile-size { font-size: 11px; color: #9ca3af; font-variant-numeric: tabular-nums; }

    .stage-att__tile-cta {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 5px 10px;
        background: #eef2ff;
        color: #4338ca;
        border-radius: 999px;
        font-size: 11px; font-weight: 600;
        opacity: 0; transform: translateX(4px);
        transition: opacity .15s, transform .15s;
        flex-shrink: 0;
    }
    .stage-att__tile:hover .stage-att__tile-cta { opacity: 1; transform: translateX(0); }
    .is_dark .stage-att__tile-cta { background: rgba(67, 56, 202, 0.22); color: #a5b4fc; }
    /* Touch devices — always show the CTA since there's no hover state. */
    @media (hover: none) {
        .stage-att__tile-cta { opacity: 1; transform: translateX(0); }
    }
</style>
@endonce
@endif
