{{--
    Read-only display of a signed/locked section.
    - $stage       : App\Forms\FormStage
    - $sectionData : array of saved values keyed by field name
    - $signature   : App\Models\FormSignature|null
    - $signer      : App\Models\User|null
    - $definition  : optional App\Forms\BaseFormDefinition (required if you want
                     displayFieldGroups() to be honoured)
--}}
@php
    use App\Forms\FormField;
    use App\Models\FormAttachment;
    $sectionData = $sectionData ?? [];

    // Field groups that this stage's form definition wants rendered as a
    // single compact table (instead of one dl-row per field). Used for the
    // Promotion form's 14-indicator self-evaluation so downstream approvers
    // don't have to scroll past 14 separate rows to reach the sign button.
    $fieldGroups       = (isset($definition) && $definition) ? $definition->displayFieldGroups($stage->slug) : [];
    $groupedFieldNames = [];                  // set of names consumed by any group
    $groupByAnchor     = [];                  // map: first-field-name → group config
    foreach ($fieldGroups as $g) {
        $names = $g['fieldNames'] ?? [];
        if (empty($names)) continue;
        $groupByAnchor[$names[0]] = $g;
        foreach ($names as $n) { $groupedFieldNames[$n] = true; }
    }
    // Attachments uploaded at THIS stage. Cheap because $submission->attachments
    // is eager-loaded in FormSubmissionController::show — this is just an
    // in-memory ->where() on the loaded Collection.
    $stageAttachments = isset($submission)
        ? $submission->attachments->where('stage_slug', $stage->slug)
        : collect();

    // ── Photo preview ──────────────────────────────────────────────
    // If this stage has an image attachment (e.g. the EPR passport
    // photo), pick the first one and surface it as a card in the
    // section header so the user can immediately confirm the upload
    // succeeded. Mirrors the PDF detection: mime starts with image/
    // OR filename extension is a common image extension.
    $imageExts = ['jpg','jpeg','png','gif','webp','bmp'];
    $stagePhoto = null;
    foreach ($stageAttachments as $att) {
        $mime = strtolower((string) ($att->mime_type ?? ''));
        $name = (string) ($att->name ?? '');
        $path = (string) ($att->path ?? '');
        $ext  = strtolower(pathinfo($name !== '' ? $name : $path, PATHINFO_EXTENSION));
        $isImage = ($mime !== '' && str_starts_with($mime, 'image/'))
                || in_array($ext, $imageExts, true);
        if ($isImage && $path !== '') { $stagePhoto = $att; break; }
    }
@endphp

<div class="form-panel form-panel--locked">
    <div class="form-panel__head @if($stagePhoto) form-panel__head--with-photo-preview @endif">
        <div style="display: flex; align-items: flex-start; gap: 14px; flex: 1; min-width: 0;">
            <span class="form-panel__lockicon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <div>
                <h2 class="form-panel__title">{{ $stage->label }}<span class="form-panel__title-bar"></span></h2>
                @if($signer)
                    <p class="form-panel__desc">
                        Signed by <strong>{{ trim(($signer->first_name ?? '') . ' ' . ($signer->last_name ?? '')) }}</strong>
                        @if(isset($signature) && $signature)
                            on <span style="color:#111827;font-weight:600;border-bottom:1px dashed #94a3b8;padding-bottom:1px;">{{ optional($signature->signed_at)->format('d M Y, H:i') }}</span>
                        @endif
                    </p>
                @elseif(!empty($filler ?? null))
                    {{-- Data-only stage (e.g. CUGA-1A/1B/1C applicant_details where
                         the applicant signs the declaration stage later). Tell the
                         next office WHO submitted this and WHEN, so they know they're
                         attesting to data a real person filled in — not a blank slate. --}}
                    <p class="form-panel__desc">
                        <span style="display: inline-block; padding: 1px 7px; border-radius: 8px; background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; margin-right: 6px; vertical-align: middle;">Filled</span>
                        Filled by <strong>{{ trim(($filler->first_name ?? '') . ' ' . ($filler->last_name ?? '')) ?: 'the applicant' }}</strong>
                        @if(!empty($filledAt ?? null))
                            on <span style="color:#111827;font-weight:600;border-bottom:1px dashed #94a3b8;padding-bottom:1px;">{{ \Illuminate\Support\Carbon::parse($filledAt)->format('d M Y, H:i') }}</span>
                        @endif
                        — please review the details below before adding your section.
                    </p>
                @endif
            </div>
        </div>

        @if($stagePhoto)
            {{-- Photo preview card — visual confirmation that the upload succeeded.
                 The controller serves images inline when the URL carries ?inline=1
                 so the <img> tag below can render directly; without that flag the
                 same endpoint would force a download. --}}
            @php $photoInlineUrl = route('admin.forms.attachment', [$submission->id, $stagePhoto->id]) . '?inline=1'; @endphp
            <a href="{{ $photoInlineUrl }}"
               target="_blank"
               class="photo-preview-card"
               title="Open photograph in a new tab">
                <img class="photo-preview-card__img"
                     src="{{ $photoInlineUrl }}"
                     alt="Uploaded photograph">
                <span class="photo-preview-card__badge" aria-hidden="true">
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </span>
                <span class="photo-preview-card__caption">Photograph uploaded</span>
            </a>
        @elseif($stageAttachments->count() > 0)
            <span class="stage-clip-badge" title="{{ $stageAttachments->count() }} file{{ $stageAttachments->count() === 1 ? '' : 's' }} attached at this stage">
                <span class="stage-clip-badge__bubble">
                    <img src="https://img.icons8.com/officel/80/attach.png" alt="" class="stage-clip-badge__img" loading="lazy" decoding="async">
                </span>
                <span class="stage-clip-badge__count">{{ $stageAttachments->count() }}</span>
            </span>
        @endif
    </div>

    <div class="form-panel__body">
        <dl class="locked-fields">
            @foreach($stage->fields as $field)
                @if($field->type === FormField::TYPE_HEADING) @continue @endif

                {{-- ── Grouped fields: render as a single compact table the first time we hit one ── --}}
                @if(isset($groupByAnchor[$field->name]))
                    @php
                        $group       = $groupByAnchor[$field->name];
                        $groupFields = [];
                        foreach ($group['fieldNames'] as $gn) {
                            foreach ($stage->fields as $gf) {
                                if ($gf->name === $gn) { $groupFields[] = $gf; break; }
                            }
                        }
                        $groupSum = 0; $groupFilled = 0;
                        foreach ($groupFields as $gf) {
                            $gv = $sectionData[$gf->name] ?? null;
                            if (is_numeric($gv)) { $groupSum += (int) $gv; $groupFilled++; }
                        }
                        $denominator = count($groupFields) * 10;
                        $groupPct    = ($groupFilled > 0 && $denominator > 0)
                            ? round(($groupSum / $denominator) * 100, 1)
                            : null;
                    @endphp
                    <div class="locked-fields__row" style="grid-column: span 12;">
                        <dt>{{ $group['label'] }}</dt>
                        <dd>
                            @if(!empty($group['help']))
                                <p style="margin: 0 0 6px; font-size: 0.78rem; color: #6b7280; font-style: italic;">{{ $group['help'] }}</p>
                            @endif
                            <table class="locked-table">
                                <thead><tr>
                                    <th>Description</th>
                                    <th style="width: 140px; text-align: center;">{{ $group['valueColumn'] ?? 'Value' }}</th>
                                </tr></thead>
                                <tbody>
                                    @foreach($groupFields as $gi => $gf)
                                        @php $gv = $sectionData[$gf->name] ?? null; @endphp
                                        <tr>
                                            <td>{{ $gf->label }}</td>
                                            <td style="text-align: center; font-weight: 600;">{{ is_numeric($gv) ? (int) $gv : '—' }}</td>
                                        </tr>
                                    @endforeach
                                    @if(!empty($group['showTotal']))
                                        <tr style="background: #f9fafb;">
                                            <td style="text-align: right; font-weight: 700;">Total Score</td>
                                            <td style="text-align: center; font-weight: 700;">{{ $groupFilled > 0 ? $groupSum : '—' }} / {{ $denominator }}</td>
                                        </tr>
                                        @if($groupPct !== null)
                                            <tr style="background: #f3f4f6;">
                                                <td style="text-align: right; font-weight: 700;">Percentage</td>
                                                <td style="text-align: center; font-weight: 700; color: #15803d;">{{ $groupPct }}%</td>
                                            </tr>
                                        @endif
                                    @endif
                                </tbody>
                            </table>
                        </dd>
                    </div>
                @endif

                {{-- Skip every field that belongs to a group (handled above) --}}
                @if(isset($groupedFieldNames[$field->name])) @continue @endif

                @php $raw = $sectionData[$field->name] ?? null; @endphp
                @if($raw === null || $raw === '' || (is_array($raw) && empty($raw))) @continue @endif

                @php
                    // Tables and textareas are always wide content — force them
                    // to span the full 12-column grid regardless of the field's
                    // declared col, so they never get cramped next to a sibling.
                    $isWideType = in_array($field->type, [FormField::TYPE_TABLE, FormField::TYPE_TEXTAREA], true);
                    $rowCol     = $isWideType ? 12 : max(1, min(12, (int) ($field->col ?? 12)));
                    // Long-label fields (e.g. item 14 "Have you ever been convicted…")
                    // also get full-width to stop the label wrapping over neighbouring cells.
                    if (!$isWideType && mb_strlen((string) $field->label) > 48) {
                        $rowCol = 12;
                    }
                @endphp

                <div class="locked-fields__row" style="grid-column: span {{ $rowCol }};">
                    <dt>{{ $field->label }}</dt>
                    <dd>
                        @switch($field->type)
                            @case(FormField::TYPE_CHECKBOX)
                                {{ !empty($raw) ? 'Yes' : 'No' }}
                                @break
                            @case(FormField::TYPE_CURRENCY)
                                GhS {{ number_format((float) $raw, 2) }}
                                @break
                            @case(FormField::TYPE_RADIO)
                            @case(FormField::TYPE_SELECT)
                                {{ $field->options[$raw] ?? $raw }}
                                @break
                            @case(FormField::TYPE_TABLE)
                                @if(is_array($raw) && count($raw))
                                    <table class="locked-table">
                                        <thead><tr>
                                            <th class="locked-table__index">#</th>
                                            @foreach($field->tableColumns as $col)
                                                <th>{{ $col['label'] ?? $col['name'] }}</th>
                                            @endforeach
                                        </tr></thead>
                                        <tbody>
                                            @foreach($raw as $i => $row)
                                                @if(!is_array($row)) @continue @endif
                                                <tr>
                                                    <td class="locked-table__index">{{ $i + 1 }}.</td>
                                                    @foreach($field->tableColumns as $col)
                                                        <td>{{ $row[$col['name']] ?? '' }}</td>
                                                    @endforeach
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                @else
                                    <em style="color:#9ca3af;">No entries.</em>
                                @endif
                                @break
                            @default
                                {!! nl2br(e($raw)) !!}
                        @endswitch
                    </dd>
                </div>
            @endforeach
        </dl>

        {{-- Per-stage attachments display + viewer (uses global attachmentViewer modal) --}}
        @include('admin.forms.partials.stage-attachments', [
            'submission'       => $submission ?? null,
            'stageAttachments' => $stageAttachments,
            'stage'            => $stage,
        ])

        @if(isset($signature) && $signature)
            @php $check = $signature->verifyChain(); @endphp
            <div class="locked-signature">
                <div class="locked-signature__label">
                    Signature
                    @if($check['valid'])
                        <span class="locked-signature__badge locked-signature__badge--ok" title="Hash chain reconstructs from stored data.">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Verified
                        </span>
                    @else
                        <span class="locked-signature__badge locked-signature__badge--bad" title="{{ $check['reason'] }}">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/><circle cx="12" cy="12" r="10"/></svg>
                            Chain mismatch
                        </span>
                    @endif
                </div>
                @if($signature->image_url)
                    {{-- onerror message intentionally avoids the old "run storage:link" hint:
                         production runs on Hostinger where symlink() is disabled and the
                         /storage/{path} route in web.php serves the file via PHP instead. --}}
                    <img src="{{ $signature->image_url }}" alt="Signature" class="locked-signature__img"
                         onerror="this.outerHTML='<div class=&quot;locked-signature__broken&quot;>Signature image could not be loaded. Please refresh the page; if the problem persists, contact your administrator.</div>';">
                @else
                    <div class="locked-signature__broken">No signature image was captured for this stage.</div>
                @endif
                <div class="locked-signature__meta">
                    SHA-256: <code>{{ substr($signature->chain_hash, 0, 12) }}…</code>
                </div>
            </div>
        @endif
    </div>
</div>

@once
<style>
.locked-table { width: 100%; border-collapse: collapse; margin-top: 4px; font-size: 0.82rem; font-family: 'Outfit', sans-serif !important; }
.locked-table th, .locked-table td { padding: 6px 10px; border-bottom: 1px solid #ebebeb; text-align: left; vertical-align: top; }
.locked-table th { background: #fafafa; font-weight: 600; color: #374151; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1.5px solid #e5e7eb; }
.locked-table td { color: #111827; }
.locked-table__index { width: 32px; color: #9ca3af; font-weight: 600; font-family: 'JetBrains Mono', monospace, sans-serif; }
.is_dark .locked-table th { background: #0f172a; color: #d1d5db; border-color: #2d3748; }
.is_dark .locked-table td { color: #f3f4f6; border-color: #1e2330; }
</style>
@endonce
