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

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
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

                        {{-- ====== PRIOR (SIGNED) STAGES ====== --}}
                        @foreach($definition->stages() as $stage)
                            @php
                                $isSigned   = in_array($stage->slug, $completedStages, true);
                                $isCurrent  = $currentStage && $stage->slug === $currentStage->slug;
                                $skippedOptional = $stage->optional && !$isSigned && !$isCurrent;
                            @endphp

                            @if($isSigned)
                                @php
                                    $sigs = $signaturesByStage[$stage->slug] ?? collect();
                                    $sig  = $sigs->last();
                                @endphp
                                @include('admin.forms.partials.section-display', [
                                    'stage'       => $stage,
                                    'sectionData' => $submission->sectionData($stage->slug),
                                    'signature'   => $sig,
                                    'signer'      => $sig?->user,
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
                                                    <p class="form-panel__desc"><em>This section is waiting on the assigned officer. You can view the form but not edit it.</em></p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-panel__body">
                                        @include('admin.forms.partials.field-renderer', [
                                            'stage'       => $currentStage,
                                            'sectionData' => $submission->sectionData($currentStage->slug),
                                            'readonly'    => !$canFill,
                                        ])
                                    </div>
                                </div>

                                @if($canFill)
                                    <div class="form-panel">
                                        <div class="form-panel__head">
                                            <div>
                                                <h2 class="form-panel__title">Attachments<span class="form-panel__title-bar"></span></h2>
                                                <p class="form-panel__desc">Add files relevant to your section (optional).</p>
                                            </div>
                                        </div>
                                        <div class="form-panel__body">
                                            <input type="file" name="attachments[]" multiple class="form-control">
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
                                                ])
                                            </div>
                                        </div>
                                    @endif

                                    @if($nextStage || $vcOffice)
                                        <div class="form-panel">
                                            <div class="form-panel__head">
                                                <div>
                                                    <h2 class="form-panel__title">Forward to {{ $nextStage->label ?? '—' }}<span class="form-panel__title-bar"></span></h2>
                                                    <p class="form-panel__desc">Pick a specific person to receive this form next.</p>
                                                </div>
                                            </div>
                                            <div class="form-panel__body">
                                                @include('admin.forms.partials.recipient-picker', [
                                                    'office'    => $nextOffice,
                                                    'fieldName' => 'next_assignee_id',
                                                    'required'  => true,
                                                ])

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
                                            Sign &amp; Forward
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

                        @include('admin.forms.partials.comments', [
                            'submission'      => $submission,
                            'comments'        => $comments,
                            'canComment'      => $canComment,
                            'canViewInternal' => $canViewInternal,
                        ])

                        @if($canCancel)
                            <div class="form-actions" style="margin-top: 16px;">
                                <form method="POST" action="{{ route('admin.forms.cancel', $submission->id) }}"
                                      onsubmit="return confirm('Cancel this form? This cannot be undone.');">
                                    @csrf
                                    <button type="submit" class="btn-action btn-action--danger">Cancel Form</button>
                                </form>
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
