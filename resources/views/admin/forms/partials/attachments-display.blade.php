{{--
    Form attachments — listed and downloadable by EVERY authorized viewer
    (creator, current assignee, prior signatories, active office members,
    super-admin). Without this panel the show page lets stages upload files
    but never lets anyone see them, which is what users were hitting.

    Renders only if the submission has attachments.

    Expected vars:
      - $submission : App\Models\FormSubmission (with attachments + uploader)
      - $definition : App\Forms\BaseFormDefinition
--}}
@php
    $allAttachments = $submission->attachments;
@endphp

@if($allAttachments->count() > 0)
@php
    // Map stage slugs → human labels. We use the form definition when we can
    // (so we get nicely capitalised labels), and fall back to the raw slug.
    $stageLabels = [];
    foreach ($definition->stages() as $st) {
        $stageLabels[$st->slug] = $st->label;
    }

    // Best-effort file-type classification for the row icon.
    $iconFor = function (string $mime, string $name): string {
        if (str_starts_with($mime, 'image/')) return 'image';
        if ($mime === 'application/pdf' || str_ends_with(strtolower($name), '.pdf')) return 'pdf';
        if (in_array($mime, ['application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'], true)
            || str_ends_with(strtolower($name), '.doc')
            || str_ends_with(strtolower($name), '.docx')) return 'doc';
        if (in_array($mime, ['application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], true)
            || str_ends_with(strtolower($name), '.xls')
            || str_ends_with(strtolower($name), '.xlsx')
            || str_ends_with(strtolower($name), '.csv')) return 'sheet';
        return 'file';
    };
@endphp

<div class="form-panel form-attachments">
    <div class="form-panel__head">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <span class="form-panel__step-num form-attachments__num">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"/></svg>
            </span>
            <div>
                <h2 class="form-panel__title">
                    Attachments
                    <span class="form-attachments__count">{{ $allAttachments->count() }}</span>
                    <span class="form-panel__title-bar"></span>
                </h2>
                <p class="form-panel__desc">Supporting documents uploaded at any stage of this form.</p>
            </div>
        </div>
    </div>
    <div class="form-panel__body">
        <ul class="attachment-list">
            @foreach($allAttachments as $att)
                @php
                    $kind = $iconFor((string) $att->mime_type, (string) $att->name);
                    $uploader = $att->uploader;
                    $stageLabel = $stageLabels[$att->stage_slug] ?? ucwords(str_replace('_', ' ', (string) $att->stage_slug));
                @endphp
                <li class="attachment-item">
                    <span class="attachment-item__icon attachment-item__icon--{{ $kind }}">
                        @switch($kind)
                            @case('pdf')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M9 13h6M9 17h4" stroke-width="1.6"/></svg>
                                @break
                            @case('image')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2"/><circle cx="8.5" cy="8.5" r="1.5"/><polyline points="21 15 16 10 5 21"/></svg>
                                @break
                            @case('doc')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="16" y2="17"/><line x1="8" y1="9" x2="10" y2="9"/></svg>
                                @break
                            @case('sheet')
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><path d="M8 13h8M8 17h8M12 13v8" stroke-width="1.6"/></svg>
                                @break
                            @default
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                        @endswitch
                    </span>

                    <div class="attachment-item__body">
                        <div class="attachment-item__line">
                            <span class="attachment-item__name" title="{{ $att->name }}">{{ $att->name }}</span>
                            <span class="attachment-item__size">{{ $att->human_size }}</span>
                        </div>
                        <div class="attachment-item__meta">
                            <span class="attachment-item__stage">{{ $stageLabel }}</span>
                            <span class="attachment-item__dot">·</span>
                            <span class="attachment-item__uploader">
                                Uploaded by <strong>{{ trim(($uploader->first_name ?? '') . ' ' . ($uploader->last_name ?? '')) ?: 'Unknown' }}</strong>
                            </span>
                            <span class="attachment-item__dot">·</span>
                            <span class="attachment-item__time" title="{{ $att->created_at->format('d M Y, H:i') }}">{{ $att->created_at->diffForHumans() }}</span>
                        </div>
                    </div>

                    <a href="{{ route('admin.forms.attachment', [$submission->id, $att->id]) }}"
                       class="attachment-item__action"
                       title="Download {{ $att->name }}">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        <span>Download</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</div>

<style>
    .form-attachments__num { background: #eef2ff !important; color: #4338ca !important; }
    .form-attachments__count {
        display: inline-flex; align-items: center; justify-content: center;
        min-width: 22px; height: 22px; padding: 0 7px;
        background: #eef2ff; color: #4338ca; border-radius: 999px;
        font-size: 11px; font-weight: 700; margin-left: 8px;
        font-family: -apple-system, BlinkMacSystemFont, sans-serif;
    }
    .attachment-list { list-style: none; margin: 0; padding: 0; display: flex; flex-direction: column; gap: 8px; }
    .attachment-item {
        display: flex; align-items: center; gap: 12px;
        padding: 10px 12px;
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        transition: border-color .15s, box-shadow .15s;
    }
    .attachment-item:hover { border-color: #cbd5e1; box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06); }
    .attachment-item__icon {
        width: 40px; height: 40px; flex-shrink: 0;
        display: inline-flex; align-items: center; justify-content: center;
        border-radius: 8px;
    }
    .attachment-item__icon--pdf   { background: #fef2f2; color: #b91c1c; }
    .attachment-item__icon--image { background: #ecfeff; color: #0891b2; }
    .attachment-item__icon--doc   { background: #eff6ff; color: #1d4ed8; }
    .attachment-item__icon--sheet { background: #ecfdf5; color: #047857; }
    .attachment-item__icon--file  { background: #f3f4f6; color: #4b5563; }

    .attachment-item__body { flex: 1; min-width: 0; }
    .attachment-item__line { display: flex; align-items: baseline; gap: 8px; }
    .attachment-item__name {
        flex: 1; min-width: 0;
        font-size: 13.5px; font-weight: 600; color: #111827;
        overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .attachment-item__size { font-size: 11.5px; color: #9ca3af; flex-shrink: 0; font-variant-numeric: tabular-nums; }
    .attachment-item__meta {
        display: flex; flex-wrap: wrap; gap: 6px; align-items: center;
        font-size: 11.5px; color: #6b7280;
        margin-top: 3px;
    }
    .attachment-item__stage { color: #4338ca; background: #eef2ff; padding: 1px 7px; border-radius: 999px; font-weight: 600; font-size: 11px; }
    .attachment-item__uploader strong { color: #374151; font-weight: 600; }
    .attachment-item__dot { color: #d1d5db; }
    .attachment-item__time { color: #9ca3af; }

    .attachment-item__action {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 6px 12px;
        background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe;
        border-radius: 8px; text-decoration: none;
        font-size: 12px; font-weight: 600;
        transition: all .15s;
        flex-shrink: 0;
    }
    .attachment-item__action:hover { background: #dbeafe; color: #1e40af; text-decoration: none; }

    /* Dark mode */
    .is_dark .attachment-item { background: #111827; border-color: #2d3748; }
    .is_dark .attachment-item:hover { border-color: #4b5563; }
    .is_dark .attachment-item__name { color: #f3f4f6; }
    .is_dark .attachment-item__uploader strong { color: #d1d5db; }
    .is_dark .form-attachments__num, .is_dark .form-attachments__count, .is_dark .attachment-item__stage {
        background: rgba(67, 56, 202, 0.18) !important; color: #a5b4fc !important;
    }
    .is_dark .attachment-item__action {
        background: rgba(29, 78, 216, 0.18); color: #93c5fd; border-color: rgba(59, 130, 246, 0.4);
    }
    .is_dark .attachment-item__icon--pdf   { background: rgba(185, 28, 28, 0.18); color: #fca5a5; }
    .is_dark .attachment-item__icon--image { background: rgba(8, 145, 178, 0.18); color: #67e8f9; }
    .is_dark .attachment-item__icon--doc   { background: rgba(29, 78, 216, 0.18); color: #93c5fd; }
    .is_dark .attachment-item__icon--sheet { background: rgba(4, 120, 87, 0.18); color: #6ee7b7; }
    .is_dark .attachment-item__icon--file  { background: rgba(75, 85, 99, 0.25); color: #d1d5db; }
</style>
@endif
