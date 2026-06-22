@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

@php
    use App\Models\FormSubmission;
    $completedStages = $submission->signatures->pluck('stage_slug')->unique()->values()->all();
    $signaturesByStage = $submission->signatures->groupBy('stage_slug');

    // Decide which next office to render for the picker (VC vs normal next).
    $vcField = $definition->vcReferralFieldName();
@endphp

<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding">
        <div class="row">
            @include('components.create_section')
        </div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="form-shell">

                        <a href="{{ route('admin.forms.portal') }}" class="form-back-link">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            <span>Back to Forms portal</span>
                        </a>

                        <div class="form-page-header">
                            <div>
                                <span class="form-code-chip">{{ $submission->form_code }}</span>
                                <h1 class="form-page-title">{{ $definition->title() }}<span class="form-title-bar"></span></h1>
                                <p class="form-page-sub" style="font-family: 'JetBrains Mono', monospace !important; color:#6b7280;">#{{ $submission->reference }}</p>
                            </div>
                        </div>

                        {{-- ====== APPROVED-MEMO AUTHORISATION BADGE ======
                             Shown above the meta strip when the form was started from an
                             approved memo: lets everyone in the trail (HODs, offices,
                             signers, commenters) confirm the request was approved BEFORE
                             the form was filled, and open that approval as a PDF. Visible
                             to anyone who can view the form; the PDF is served by
                             admin.forms.source-memo under the form's policy. --}}
                        @if($submission->sourceCampaign)
                            @php
                                $srcMemo  = $submission->sourceCampaign;
                                $approver = $srcMemo->formUnlocker;
                                $approverName = $approver
                                    ? trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) ?: ($approver->name ?? null)
                                    : null;
                                $memoRefLabel = $srcMemo->reference ?? ('#' . $srcMemo->id);
                                $approvedDate = $srcMemo->form_unlocked_at ? $srcMemo->form_unlocked_at->format('d M Y') : null;
                            @endphp
                            <div style="display:flex;align-items:center;gap:14px;flex-wrap:wrap;
                                        background:#f5fdf9;border:1px solid #e3f3ea;
                                        border-radius:14px;padding:13px 18px;margin:0 0 16px;
                                        box-shadow:0 1px 2px rgba(16,24,40,.04);">
                                <div style="flex:0 0 auto;width:38px;height:38px;border-radius:50%;
                                            background:linear-gradient(135deg,#34d399 0%,#059669 100%);
                                            display:flex;align-items:center;justify-content:center;
                                            box-shadow:0 3px 8px rgba(5,150,105,.28);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                                <div style="flex:1 1 240px;min-width:0;">
                                    <div style="font-weight:700;color:#0f172a;font-size:13.5px;letter-spacing:.2px;display:flex;align-items:center;gap:8px;">
                                        Approved before filling
                                        <span style="background:#d1fae5;color:#047857;font-size:9.5px;font-weight:800;letter-spacing:.6px;text-transform:uppercase;padding:2px 8px;border-radius:20px;">Verified</span>
                                    </div>
                                    <div style="color:#64748b;font-size:12.5px;margin-top:3px;line-height:1.5;">
                                        Authorised by memo
                                        <strong style="font-family:'JetBrains Mono',monospace;color:#0f172a;">{{ $memoRefLabel }}</strong>
                                        @if($approverName) · approved by <strong style="color:#0f172a;">{{ $approverName }}</strong> @endif
                                        @if($approvedDate) · <span style="color:#0f172a;">{{ $approvedDate }}</span> @endif
                                    </div>
                                </div>
                                <a href="{{ route('admin.forms.source-memo', $submission->id) }}" target="_blank" rel="noopener"
                                   style="flex:0 0 auto;display:inline-flex;align-items:center;text-decoration:none;
                                          background:#059669;color:#fff;font-weight:600;font-size:12.5px;
                                          padding:9px 18px;border-radius:10px;box-shadow:0 3px 8px rgba(5,150,105,.25);transition:background .15s;"
                                   onmouseover="this.style.background='#047857'" onmouseout="this.style.background='#059669'">
                                    View approval
                                </a>
                            </div>
                        @endif

                        <div class="form-meta-strip">
                            <div class="form-meta-strip__item">
                                <span class="form-meta-strip__label">Reference</span>
                                <span class="form-meta-strip__value">{{ $submission->reference }}</span>
                            </div>
                            <div class="form-meta-strip__item">
                                <span class="form-meta-strip__label">Form</span>
                                <span class="form-meta-strip__value">{{ $submission->form_code }}</span>
                            </div>
                            <div class="form-meta-strip__item">
                                <span class="form-meta-strip__label">Status</span>
                                <span class="status-pill status-pill--{{ $submission->status }}">{{ str_replace('_', ' ', $submission->status) }}</span>
                            </div>
                            <div class="form-meta-strip__item">
                                <span class="form-meta-strip__label">Requisitioner</span>
                                <span class="form-meta-strip__value">{{ trim((optional($submission->creator)->first_name ?? '') . ' ' . (optional($submission->creator)->last_name ?? '')) }}</span>
                            </div>
                            @if($submission->currentAssignee)
                                <div class="form-meta-strip__item">
                                    <span class="form-meta-strip__label">Awaiting</span>
                                    <span class="form-meta-strip__value">
                                        {{ trim(($submission->currentAssignee->first_name ?? '') . ' ' . ($submission->currentAssignee->last_name ?? '')) }}
                                        @if($submission->currentOffice) <small style="color:#9ca3af; font-weight: 500;">— {{ $submission->currentOffice->name }}</small>@endif
                                        @if($submission->stale_severity)
                                            <span class="stale-pill stale-pill--{{ $submission->stale_severity }}" title="No movement in {{ $submission->stale_days }} day{{ $submission->stale_days === 1 ? '' : 's' }}">
                                                stuck {{ $submission->stale_days }}d
                                            </span>
                                        @endif
                                    </span>
                                </div>
                            @endif
                            @if($submission->requisition_amount)
                                <div class="form-meta-strip__item">
                                    <span class="form-meta-strip__label">Amount</span>
                                    <span class="form-meta-strip__value">GhS {{ number_format($submission->requisition_amount, 2) }}</span>
                                </div>
                            @endif
                            <div class="form-meta-strip__item" style="margin-left: auto;">
                                <a href="{{ route('admin.forms.pdf', $submission->id) }}" target="_blank" class="btn-action btn-action--ghost">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                    PDF
                                </a>
                            </div>
                        </div>

                        @include('admin.forms.partials.stage-stepper', [
                            'definition'      => $definition,
                            'currentStage'    => $currentStage,
                            'completedStages' => $completedStages,
                        ])

                        @if($submission->status === FormSubmission::STATUS_REJECTED)
                            <div class="alert alert-warning">
                                <strong>This form was sent back for revision.</strong> Review the discussion below for the reason, edit your section, and resubmit.
                            </div>
                        @elseif($submission->status === FormSubmission::STATUS_COMPLETED)
                            <div class="alert alert-success">
                                <strong>All approvals received.</strong> This form is fully signed and complete.
                            </div>
                        @endif

                        {{-- ====== PRIOR / COMPLETED STAGES ======
                             A stage renders as a locked panel here when EITHER it has a
                             signature on file OR it has saved section_data — and is not
                             the form's current (editable) stage. The data-only path
                             covers forms where the first stage declares
                             signatureRequired:false because the applicant signs the
                             declaration stage later (CUGA-1A / CUGA-1B / CUGA-1C). EPR /
                             PR / PWA / VMA / leave forms all sign their first stage, so
                             the data path is redundant for them — but harmless. --}}
                        @foreach($definition->stages() as $stage)
                            @php
                                $isSigned        = in_array($stage->slug, $completedStages, true);
                                $isCurrent       = $currentStage && $stage->slug === $currentStage->slug;
                                $stageData       = $submission->sectionData($stage->slug);
                                $stageHasData    = is_array($stageData) && count($stageData) > 0;
                                $shouldRender    = !$isCurrent && ($isSigned || $stageHasData);
                                $skippedOptional = $stage->optional && !$isSigned && !$isCurrent;
                            @endphp

                            @if($shouldRender)
                                @php
                                    $sigs = $signaturesByStage[$stage->slug] ?? collect();
                                    $sig  = $sigs->last();

                                    // ── Filled-by attribution for data-only stages ──
                                    // When the panel has data but no signature (common
                                    // for the first stage of CUGA-1A / CUGA-1B / CUGA-1C
                                    // where the applicant signs the declaration stage
                                    // later), pass the original applicant as the
                                    // "filler" so the recommender / HOD / next office
                                    // can see WHO entered this data and WHEN — they
                                    // need that confidence before they sign anything.
                                    $isFirstStage = $stage->slug === $definition->firstStage()->slug;
                                    $filler   = (!$sig && $isFirstStage) ? $submission->creator : null;
                                    $filledAt = (!$sig && $isFirstStage)
                                        ? ($submission->submitted_at ?? $submission->updated_at ?? $submission->created_at)
                                        : null;
                                @endphp
                                @include('admin.forms.partials.section-display', [
                                    'stage'       => $stage,
                                    'sectionData' => $stageData,
                                    'signature'   => $sig,
                                    'signer'      => $sig?->user,
                                    'filler'      => $filler,
                                    'filledAt'    => $filledAt,
                                    'definition'  => $definition,
                                ])
                            @endif
                        @endforeach

                        {{-- ====== CURRENT STAGE (editable if user is the assignee) ====== --}}
                        @if($currentStage && in_array($submission->status, [FormSubmission::STATUS_IN_PROGRESS, FormSubmission::STATUS_REJECTED, FormSubmission::STATUS_DRAFT], true))
                            <form method="POST"
                                  action="{{ route('admin.forms.sign', $submission->id) }}"
                                  enctype="multipart/form-data"
                                  id="formStageForm"
                                  class="form-composer">
                                @csrf

                                @php
                                    $currentStageAttachments = $submission->attachments->where('stage_slug', $currentStage->slug);
                                    // Fields relocated into the Attachments panel (see BaseFormDefinition::attachmentsPanelFieldNames).
                                    $attachmentsPanelFieldNames = $definition->attachmentsPanelFieldNames($currentStage->slug);
                                @endphp
                                <div class="form-panel @if(!$canFill) form-panel--locked @endif">
                                    <div class="form-panel__head">
                                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                                            <span class="form-panel__code">{{ $submission->form_code }}</span>
                                            <div>
                                                <h2 class="form-panel__title">{{ $currentStage->label }} {{ $canFill ? '— awaiting your action' : '' }}<span class="form-panel__title-bar"></span></h2>
                                                @if($currentStage->description)
                                                    <p class="form-panel__desc">{{ $currentStage->description }}</p>
                                                @endif
                                                @if(!$canFill)
                                                    <p class="form-panel__desc"><em>Awaiting the assigned officer — view only.</em></p>
                                                @endif
                                            </div>
                                        </div>

                                        @if($currentStageAttachments->count() > 0)
                                            <span class="stage-clip-badge" title="{{ $currentStageAttachments->count() }} file{{ $currentStageAttachments->count() === 1 ? '' : 's' }} attached at this stage">
                                                <span class="stage-clip-badge__bubble">
                                                    <img src="https://img.icons8.com/officel/80/attach.png" alt="" class="stage-clip-badge__img" loading="lazy" decoding="async">
                                                </span>
                                                <span class="stage-clip-badge__count">{{ $currentStageAttachments->count() }}</span>
                                            </span>
                                        @endif
                                    </div>
                                    <div class="form-panel__body">
                                        @include('admin.forms.partials.field-renderer', [
                                            'stage'             => $currentStage,
                                            'sectionData'       => $currentStageSectionData ?? $submission->sectionData($currentStage->slug),
                                            'readonly'          => !$canFill,
                                            'excludeFieldNames' => $attachmentsPanelFieldNames,
                                        ])

                                        {{-- Existing attachments at this stage (e.g. inherited from a reassignment) --}}
                                        @include('admin.forms.partials.stage-attachments', [
                                            'submission'       => $submission,
                                            'stageAttachments' => $currentStageAttachments,
                                            'stage'            => $currentStage,
                                        ])
                                    </div>
                                </div>

                                @if($canFill)
                                    <div class="form-panel">
                                        <div class="form-panel__head">
                                            <div>
                                                <h2 class="form-panel__title">Attachments<span class="form-panel__title-bar"></span></h2>
                                                <p class="form-panel__desc">Add files relevant to your section. Click the × on a row to remove a file before forwarding.</p>
                                            </div>
                                        </div>
                                        <div class="form-panel__body">
                                            @include('admin.forms.partials.attachment-uploader')

                                            @if(!empty($attachmentsPanelFieldNames))
                                                <div class="attachments-confirm" style="margin-top: 14px; padding-top: 14px; border-top: 1.5px dashed #ebebeb;">
                                                    @include('admin.forms.partials.field-renderer', [
                                                        'stage'          => $currentStage,
                                                        'sectionData'    => $currentStageSectionData ?? $submission->sectionData($currentStage->slug),
                                                        'readonly'       => false,
                                                        'onlyFieldNames' => $attachmentsPanelFieldNames,
                                                    ])
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    @if($currentStage->signatureRequired)
                                        <div class="form-panel">
                                            <div class="form-panel__head">
                                                <div>
                                                    <h2 class="form-panel__title">Your signature<span class="form-panel__title-bar"></span></h2>
                                                    <p class="form-panel__desc">Sign below or reuse your saved signature.</p>
                                                </div>
                                            </div>
                                            <div class="form-panel__body">
                                                @include('admin.forms.partials.signature-pad', [
                                                    'savedSignature' => $savedSignature,
                                                    // Internal Audit signs in green "audit ink".
                                                    'inkColor'       => $currentStage->isInternalAudit() ? '#15803d' : '#111827',
                                                ])
                                            </div>
                                        </div>
                                    @endif

                                    @if($nextStage || $vcOffice)
                                        <div class="form-panel">
                                            <div class="form-panel__head">
                                                <div>
                                                    <h2 class="form-panel__title">Forward to {{ $nextStage->label ?? '—' }}<span class="form-panel__title-bar"></span></h2>
                                                    @if($nextStage && $nextStage->isCreatorPool())
                                                        <p class="form-panel__desc">This form returns to the applicant for their declaration. No need to pick anyone — it's routed automatically.</p>
                                                    @elseif($nextStage && $nextStage->isLeadershipOrOfficePool())
                                                        <p class="form-panel__desc">Choose <strong>Dean</strong>, <strong>HOD</strong>, <strong>Director</strong>, or <strong>Office</strong> — then pick the specific person, or the office whose head will recommend.</p>
                                                    @elseif($nextStage && $nextStage->isLeadershipPool())
                                                        <p class="form-panel__desc">Choose whether this form is going to a <strong>Dean</strong>, <strong>HOD</strong> or <strong>Director</strong>, then pick the specific person from the list.</p>
                                                    @else
                                                        <p class="form-panel__desc">Pick a specific person to receive this form next.</p>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="form-panel__body">
                                                @if($nextStage && $nextStage->isCreatorPool())
                                                    @include('admin.forms.partials.creator-recipient-notice', [
                                                        'recipient' => $creatorRecipient ?? null,
                                                        'nextStage' => $nextStage,
                                                    ])
                                                @elseif($nextStage && $nextStage->isLeadershipOrOfficePool())
                                                    @include('admin.forms.partials.recommender-picker', [
                                                        'leadershipCandidates' => $leadershipCandidates ?? [],
                                                        'allOffices'           => $allOffices ?? collect(),
                                                        'fieldName'            => 'next_assignee_id',
                                                        'categoryFieldName'    => 'next_leadership_category',
                                                        'officeFieldName'      => 'next_office_id',
                                                        'required'             => true,
                                                    ])
                                                @elseif($nextStage && $nextStage->isLeadershipPool())
                                                    @include('admin.forms.partials.leadership-picker', [
                                                        'leadershipCandidates' => $leadershipCandidates ?? [],
                                                        'fieldName'            => 'next_assignee_id',
                                                        'categoryFieldName'    => 'next_leadership_category',
                                                        'required'             => true,
                                                    ])
                                                @else
                                                    @include('admin.forms.partials.recipient-picker', [
                                                        'office'    => $nextOffice,
                                                        'fieldName' => 'next_assignee_id',
                                                        'required'  => true,
                                                    ])
                                                @endif

                                                @if($vcOffice)
                                                    <p style="margin: 12px 0 0; font-size: 0.78rem; color: #9ca3af; line-height: 1.5;">
                                                        If you tick <strong>"Refer for VC's Approval"</strong> above, the form will be sent to the VC's Office instead. Otherwise it goes to the office shown here.
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    @endif

                                    <div class="form-actions">
                                        <button type="button" class="btn-action btn-action--draft" id="saveDraftBtn">Save Progress</button>
                                        @if($currentStage->slug !== 'requisitioner')
                                            <button type="button" class="btn-action btn-action--danger" id="rejectBtn">Send Back</button>
                                        @endif
                                        <button type="submit" class="btn-action btn-action--primary">
                                            {{ $currentStage->signatureRequired ? 'Sign & Forward' : 'Forward' }}
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                        </button>
                                    </div>
                                @endif
                            </form>

                            {{-- Hidden save-draft form (POSTs the same fields to the save-draft route) --}}
                            @if($canFill)
                                <form method="POST" action="{{ route('admin.forms.save-draft', $submission->id) }}" id="saveDraftForm" enctype="multipart/form-data" style="display:none;">
                                    @csrf
                                </form>
                                {{-- Reject modal form --}}
                                @if($currentStage->slug !== 'requisitioner')
                                    <div id="rejectModal" class="reject-modal" style="display:none;">
                                        <div class="reject-modal__backdrop"></div>
                                        <div class="reject-modal__panel">
                                            <h5 style="margin-top:0;">Send this form back?</h5>
                                            <p style="color:#6b7280;">The form returns to <strong>{{ trim((optional($submission->creator)->first_name ?? '') . ' ' . (optional($submission->creator)->last_name ?? '')) }}</strong> with the reason you provide.</p>
                                            <form method="POST" action="{{ route('admin.forms.reject', $submission->id) }}">
                                                @csrf
                                                <textarea name="reason" rows="4" class="form-control" placeholder="Reason for sending back (required)" required maxlength="2000"></textarea>
                                                <div class="form-actions" style="margin-top: 14px;">
                                                    <button type="button" class="btn-action btn-action--ghost" id="rejectCancelBtn">Cancel</button>
                                                    <button type="submit" class="btn-action btn-action--danger">Send Back</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                @endif
                            @endif
                        @endif

                        @include('admin.forms.partials.audit-trail', [
                            'submission' => $submission,
                        ])

                        @include('admin.forms.partials.comments', [
                            'submission'      => $submission,
                            'comments'        => $comments,
                            'canComment'      => $canComment,
                            'canViewInternal' => $canViewInternal,
                        ])

                        @if($canCancel || $canReassign)
                            <div class="form-actions" style="margin-top: 16px;">
                                @if($canReassign)
                                    <button type="button" class="btn-action btn-action--ghost" id="reassignBtn">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><polyline points="7 23 3 19 7 15"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
                                        Reassign
                                    </button>
                                @endif
                                @if($canCancel)
                                    <form method="POST" action="{{ route('admin.forms.cancel', $submission->id) }}"
                                          onsubmit="return confirm('Cancel this form? This cannot be undone.');" style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn-action btn-action--danger">Cancel Form</button>
                                    </form>
                                @endif
                            </div>
                        @endif

                        @if($canReassign)
                            {{-- Reassign modal --}}
                            <div id="reassignModal" class="reject-modal" style="display:none;">
                                <div class="reject-modal__backdrop"></div>
                                <div class="reject-modal__panel" style="max-width: 560px;">
                                    <h5 style="margin-top:0;">Reassign to another office member</h5>
                                    <p style="color:#6b7280;">
                                        Use this when <strong>{{ trim(($submission->currentAssignee->first_name ?? '') . ' ' . ($submission->currentAssignee->last_name ?? '')) }}</strong>
                                        is on leave or otherwise unavailable. The form moves to another active member of
                                        <strong>{{ $submission->currentOffice->name ?? 'this office' }}</strong>.
                                        The action is logged on the audit trail and visible to the requisitioner via an internal note.
                                    </p>

                                    @if($reassignCandidates->isEmpty())
                                        <div class="alert alert-warning">
                                            No other active members in this office. Add another person to the office first.
                                        </div>
                                    @else
                                        <form method="POST" action="{{ route('admin.forms.reassign', $submission->id) }}">
                                            @csrf
                                            <div style="margin-bottom: 14px;">
                                                <label class="form-field__label" style="display:block; margin-bottom: 8px;">Reassign to</label>
                                                <div class="rs-candidates">
                                                    @foreach($reassignCandidates as $cand)
                                                        @php
                                                            $fn = trim(($cand->first_name ?? '') . ' ' . ($cand->last_name ?? ''));
                                                            $initials = strtoupper(substr($cand->first_name ?? '', 0, 1) . substr($cand->last_name ?? '', 0, 1));
                                                        @endphp
                                                        <label class="rs-card">
                                                            <input type="radio" name="new_assignee_id" value="{{ $cand->id }}" required {{ $loop->first ? 'checked' : '' }}>
                                                            <div class="rs-card__avatar">{{ $initials ?: '?' }}</div>
                                                            <div class="rs-card__meta">
                                                                <div class="rs-card__name">{{ $fn }}</div>
                                                                <div class="rs-card__email">{{ $cand->email }}</div>
                                                            </div>
                                                            <div class="rs-card__check">
                                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                                            </div>
                                                        </label>
                                                    @endforeach
                                                </div>
                                            </div>
                                            <div style="margin-bottom: 4px;">
                                                <label class="form-field__label" style="display:block; margin-bottom: 6px;">Reason (will appear on the audit trail)</label>
                                                <textarea name="reason" rows="3" class="form-control" placeholder="e.g. Cashier is on annual leave until 12 Jun." required maxlength="2000"></textarea>
                                            </div>
                                            <div class="form-actions" style="margin-top: 14px;">
                                                <button type="button" class="btn-action btn-action--ghost" id="reassignCancelBtn">Cancel</button>
                                                <button type="submit" class="btn-action btn-action--primary">Reassign now</button>
                                            </div>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.reject-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; font-family: 'Outfit', sans-serif !important; }
.reject-modal *, .reject-modal { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.reject-modal__backdrop { position: absolute; inset: 0; background: rgba(12,12,12,.55); backdrop-filter: blur(4px); }
.reject-modal__panel { position: relative; background: #fff; border: 1.5px solid #ebebeb; border-radius: 18px; padding: 24px; width: 100%; max-width: 500px; box-shadow: 0 24px 60px rgba(0,0,0,.25); }
.reject-modal__panel h5 { font-size: 1.02rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0 0 6px; }
.reject-modal__panel p { font-size: 0.85rem; color: #6b7280; margin: 0 0 14px; }
.is_dark .reject-modal__panel { background: #111827; border-color: #1e2330; }
.is_dark .reject-modal__panel h5 { color: #f3f4f6; }

/* Reassign candidate picker (compact, inside modal) */
.rs-candidates { display: flex; flex-direction: column; gap: 6px; max-height: 240px; overflow-y: auto; padding: 4px; background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 10px; }
.rs-candidates::-webkit-scrollbar { width: 6px; }
.rs-candidates::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }
.rs-card { display: flex; align-items: center; gap: 10px; padding: 9px 12px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 9px; cursor: pointer; transition: all .12s; margin: 0; }
.rs-card:hover { border-color: #0c0c0c; }
.rs-card.is-selected { background: #f9fafb; border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.rs-card input { display: none; }
.rs-card__avatar { width: 30px; height: 30px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 0.66rem; font-weight: 600; flex-shrink: 0; }
.rs-card__meta { flex: 1; min-width: 0; }
.rs-card__name { font-size: 0.84rem; font-weight: 600; color: #111827; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rs-card__email { font-size: 0.72rem; color: #9ca3af; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rs-card__check { width: 20px; height: 20px; border-radius: 50%; border: 1.5px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; color: transparent; transition: all .12s; flex-shrink: 0; }
.rs-card.is-selected .rs-card__check { background: #0c0c0c; border-color: #0c0c0c; color: #fff; }
.is_dark .rs-candidates { background: #0f172a; border-color: #1e2330; }
.is_dark .rs-card { background: #111827; border-color: #2d3748; }
.is_dark .rs-card.is-selected { background: #0f172a; border-color: #f3f4f6; }
.is_dark .rs-card__name { color: #f3f4f6; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const saveDraftBtn = document.getElementById('saveDraftBtn');
    if (saveDraftBtn) {
        saveDraftBtn.addEventListener('click', function () {
            const main = document.getElementById('formStageForm');
            const draft = document.getElementById('saveDraftForm');
            if (!main || !draft) return;
            const formData = new FormData(main);
            for (const [k, v] of formData.entries()) {
                if (k === '_token') continue;
                if (v instanceof File) {
                    const input = main.querySelector(`[name="${k}"]`);
                    if (input && input.type === 'file' && input.files && input.files.length) {
                        const cloned = input.cloneNode(true);
                        cloned.removeAttribute('id');
                        cloned.style.display = 'none';
                        cloned.dataset.cloned = '1';
                        // We cannot transfer File objects directly via append into another form's FormData
                        // so we rely on the action's separate file input — simpler: keep draft text-only.
                    }
                } else {
                    const existing = draft.querySelector(`[name="${k}"]`);
                    if (existing) existing.remove();
                    const inp = document.createElement('input');
                    inp.type = 'hidden';
                    inp.name = k;
                    inp.value = v;
                    draft.appendChild(inp);
                }
            }
            draft.submit();
        });
    }

    const rejectBtn = document.getElementById('rejectBtn');
    const rejectModal = document.getElementById('rejectModal');
    const rejectCancelBtn = document.getElementById('rejectCancelBtn');
    if (rejectBtn && rejectModal) {
        rejectBtn.addEventListener('click', () => { rejectModal.style.display = 'flex'; });
        rejectCancelBtn.addEventListener('click', () => { rejectModal.style.display = 'none'; });
        rejectModal.querySelector('.reject-modal__backdrop').addEventListener('click', () => { rejectModal.style.display = 'none'; });
    }

    // Reassign modal
    const reassignBtn       = document.getElementById('reassignBtn');
    const reassignModal     = document.getElementById('reassignModal');
    const reassignCancelBtn = document.getElementById('reassignCancelBtn');
    if (reassignBtn && reassignModal) {
        reassignBtn.addEventListener('click', () => { reassignModal.style.display = 'flex'; });
        if (reassignCancelBtn) reassignCancelBtn.addEventListener('click', () => { reassignModal.style.display = 'none'; });
        reassignModal.querySelector('.reject-modal__backdrop').addEventListener('click', () => { reassignModal.style.display = 'none'; });

        // Toggle selected state on the candidate cards.
        const cards = reassignModal.querySelectorAll('.rs-card');
        cards.forEach(card => {
            const radio = card.querySelector('input[type="radio"]');
            if (radio && radio.checked) card.classList.add('is-selected');
            card.addEventListener('click', () => {
                cards.forEach(c => c.classList.remove('is-selected'));
                card.classList.add('is-selected');
                if (radio) radio.checked = true;
            });
        });
    }

    // Sign & Forward client-side guard
    const mainForm = document.getElementById('formStageForm');
    if (mainForm) {
        mainForm.addEventListener('submit', function (e) {
            const sigInput = document.getElementById('signature_data_input');
            const reuseInput = document.getElementById('reuse_saved_signature_input');
            if (!sigInput) return; // stages without signature
            const hasSig = (sigInput.value && sigInput.value.length > 32)
                || (reuseInput && reuseInput.value === '1');
            if (!hasSig) {
                e.preventDefault();
                alert('Please sign before forwarding, or tick "Use my saved signature".');
            }
        });
    }
});
</script>

@include('admin.forms.partials.shared-styles')
@endsection
