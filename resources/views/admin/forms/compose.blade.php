@extends('layout.app')

@php
    /**
     * Multi-step "wizard" mode is opt-in per form: when the form definition
     * returns a non-null composeWizardSteps() the long applicant page is
     * sliced into discrete steps so the user isn't forced to scroll for
     * minutes. All values still post as a single submission — the wizard
     * is presentational only.
     */
    $wizardSteps = $definition->composeWizardSteps();
    $isWizard    = is_array($wizardSteps) && count($wizardSteps) > 1;
@endphp

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding" style="display:none">
        <div class="row">@include('components.create_section')</div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="form-shell">

                        <a href="{{ route('admin.forms.gallery') }}" class="form-back-link">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            <span>Back to All Forms</span>
                        </a>

                        <div class="form-page-header">
                            <div>
                                <span class="form-code-chip">{{ $definition->code() }}</span>
                                <h1 class="form-page-title">{{ $definition->title() }}<span class="form-title-bar"></span></h1>
                                <p class="form-page-sub">{{ $definition->description() }}</p>
                            </div>
                        </div>

                        @include('admin.forms.partials.stage-stepper', [
                            'definition'    => $definition,
                            'currentStage'  => $stage,
                            'completedStages' => [],
                        ])

                        <form method="POST"
                              action="{{ route('admin.forms.store', $definition->slug()) }}"
                              enctype="multipart/form-data"
                              id="formComposeForm"
                              class="form-composer">
                            @csrf

                            <input type="hidden" name="action" id="formActionInput" value="send">

                            {{-- ════════════════════════════════════════════════════════
                                 WIZARD STEPPER (only when the form opts in)
                                 ════════════════════════════════════════════════════════ --}}
                            @if($isWizard)
                                <div class="form-wizard-stepper" id="formWizardStepper" data-wizard-steps='@json($wizardSteps)'>
                                    <div class="form-wizard-stepper__progress">
                                        <div class="form-wizard-stepper__bar" id="formWizardBar" style="width: 0%;"></div>
                                    </div>
                                    <div class="form-wizard-stepper__chips">
                                        @foreach($wizardSteps as $i => $w)
                                            <button type="button"
                                                    class="form-wizard-chip {{ $i === 0 ? 'is-active' : '' }}"
                                                    data-wizard-chip
                                                    data-wizard-target="{{ $w['key'] }}"
                                                    aria-label="Go to step {{ $i + 1 }}: {{ $w['label'] }}">
                                                <span class="form-wizard-chip__circle">{{ $i + 1 }}</span>
                                                <span class="form-wizard-chip__body">
                                                    <span class="form-wizard-chip__num">Step {{ $i + 1 }} of {{ count($wizardSteps) }}</span>
                                                    <span class="form-wizard-chip__label">{{ $w['label'] }}</span>
                                                </span>
                                            </button>
                                            @if($i < count($wizardSteps) - 1)
                                                <span class="form-wizard-chip__connector"></span>
                                            @endif
                                        @endforeach
                                    </div>
                                    <div class="form-wizard-stepper__sub" id="formWizardSub">
                                        {{ $wizardSteps[0]['description'] ?? '' }}
                                    </div>
                                </div>
                            @endif

                            {{-- Stage 1: requisitioner fields --}}
                            <div class="form-panel">
                                <div class="form-panel__head @if($definition->requiresPassportPhoto()) form-panel__head--with-photo @endif">
                                    <div class="form-panel__head-text">
                                        <span class="form-panel__step-num">1</span>
                                        <div>
                                            <h2 class="form-panel__title">{{ $stage->label }}<span class="form-panel__title-bar"></span></h2>
                                            @if($stage->description)
                                                <p class="form-panel__desc">{{ $stage->description }}</p>
                                            @endif
                                        </div>
                                    </div>

                                    @if($definition->requiresPassportPhoto())
                                        {{-- ─────────────────────────────────────────────────
                                             Passport-photo upload box. Click to choose a file;
                                             after upload, preview is shown in the same box.
                                             The chosen file posts as `passport_photo` and the
                                             controller prepends it to attachments[] so the PDF
                                             picks it up as the first image at applicant stage.
                                             ───────────────────────────────────────────────── --}}
                                        <label class="passport-uploader" for="passport_photo_input" id="passportUploaderBox" tabindex="0" aria-label="Upload your passport-size photograph">
                                            <input type="file" name="passport_photo" id="passport_photo_input" accept="image/*" hidden>
                                            <div class="passport-uploader__inner" id="passportUploaderInner">
                                                <svg class="passport-uploader__icon" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
                                                    <rect x="3" y="6" width="18" height="14" rx="2"/>
                                                    <circle cx="12" cy="13" r="3.4"/>
                                                    <path d="M8.5 6l1.5-2.2h4l1.5 2.2"/>
                                                </svg>
                                                <div class="passport-uploader__lines">
                                                    <strong>Passport Photo</strong>
                                                    <small>Click to upload</small>
                                                </div>
                                            </div>
                                            <div class="passport-uploader__overlay">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M3 17l6-6 4 4 8-8"/><path d="M14 7h7v7"/></svg>
                                                <span>Change photo</span>
                                            </div>
                                        </label>
                                    @endif
                                </div>
                                <div class="form-panel__body" id="formWizardFieldsHost">
                                    @include('admin.forms.partials.field-renderer', [
                                        'stage'       => $stage,
                                        'sectionData' => $sectionData,
                                        'readonly'    => false,
                                    ])
                                </div>
                            </div>

                            {{-- Stage 2: attachments — only visible on the final wizard step --}}
                            <div class="form-panel" @if($isWizard) data-wizard-final-only @endif>
                                <div class="form-panel__head">
                                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                                        <span class="form-panel__step-num">2</span>
                                        <div>
                                            <h2 class="form-panel__title">Attachments<span class="form-panel__title-bar"></span></h2>
                                            <p class="form-panel__desc">Invoices, receipts, quotations, original supporting documents — anything that backs up this request.</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-panel__body">
                                    <label class="upload-dropzone" id="uploadDropzone">
                                        <input type="file" name="attachments[]" multiple id="attachmentsInput" hidden>
                                        <div class="upload-dropzone__icon">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                        </div>
                                        <div class="upload-dropzone__text">
                                            <strong>Click to choose files</strong>
                                            <small>PDF, DOC, JPG, PNG — multiple files allowed</small>
                                        </div>
                                    </label>
                                    <div class="upload-list" id="uploadList"></div>
                                </div>
                            </div>

                            {{-- Stage 3: signature --}}
                            @if($stage->signatureRequired)
                                <div class="form-panel" @if($isWizard) data-wizard-final-only @endif>
                                    <div class="form-panel__head">
                                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                                            <span class="form-panel__step-num">3</span>
                                            <div>
                                                <h2 class="form-panel__title">Your signature<span class="form-panel__title-bar"></span></h2>
                                                <p class="form-panel__desc">Draw your signature below — or reuse the one saved on your profile.</p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-panel__body">
                                        @include('admin.forms.partials.signature-pad', [
                                            'savedSignature' => $savedSignature,
                                        ])
                                    </div>
                                </div>
                            @endif

                            {{-- Stage 4: forward to next office / leadership --}}
                            @if($nextStage)
                                <div class="form-panel" @if($isWizard) data-wizard-final-only @endif>
                                    <div class="form-panel__head">
                                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                                            <span class="form-panel__step-num">4</span>
                                            <div>
                                                <h2 class="form-panel__title">Forward to {{ $nextStage->label }}<span class="form-panel__title-bar"></span></h2>
                                                @if($nextStage->isCreatorPool())
                                                    <p class="form-panel__desc">This form returns to you for your declaration after the recommender has commented. No need to pick anyone — it'll come back automatically.</p>
                                                @elseif($nextStage->isLeadershipOrOfficePool())
                                                    <p class="form-panel__desc">Choose <strong>Dean</strong>, <strong>HOD</strong>, <strong>Director</strong>, or <strong>Office</strong> — then pick the specific person, or the office whose head will recommend.</p>
                                                @elseif($nextStage->isLeadershipPool())
                                                    <p class="form-panel__desc">Choose whether this form is going to a <strong>Dean</strong>, <strong>HOD</strong> or <strong>Director</strong>, then pick the specific person from the list.</p>
                                                @else
                                                    <p class="form-panel__desc">Pick the specific person in <strong>{{ $nextOffice?->name ?? 'the next office' }}</strong> who should receive this form next.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-panel__body">
                                        @if($nextStage->isCreatorPool())
                                            @include('admin.forms.partials.creator-recipient-notice', [
                                                'recipient' => $creatorRecipient ?? null,
                                                'nextStage' => $nextStage,
                                            ])
                                        @elseif($nextStage->isLeadershipOrOfficePool())
                                            @include('admin.forms.partials.recommender-picker', [
                                                'leadershipCandidates' => $leadershipCandidates ?? [],
                                                'allOffices'           => $allOffices ?? collect(),
                                                'fieldName'            => 'next_assignee_id',
                                                'categoryFieldName'    => 'next_leadership_category',
                                                'officeFieldName'      => 'next_office_id',
                                                'required'             => true,
                                            ])
                                        @elseif($nextStage->isLeadershipPool())
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
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions" id="formActionsBar">
                                @if($isWizard)
                                    <button type="button" class="btn-action btn-action--ghost" id="formWizardPrev" style="display: none;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                                        Previous
                                    </button>
                                @endif

                                <button type="submit" class="btn-action btn-action--draft" data-action="draft">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                    Save as draft
                                </button>

                                @if($isWizard)
                                    <button type="button" class="btn-action btn-action--primary" id="formWizardNext">
                                        Next
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </button>
                                @endif

                                <button type="submit" class="btn-action btn-action--primary" data-action="send" id="formForwardBtn" @if($isWizard) style="display: none;" @endif>
                                    {{ $stage->signatureRequired ? 'Sign & forward' : 'Forward' }}
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Compose-specific extras (dropzone) */
.upload-dropzone { display: flex; align-items: center; gap: 14px; padding: 16px; background: #fafafa; border: 1.5px dashed #d4d7de; border-radius: 12px; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.upload-dropzone:hover { border-color: #0c0c0c; background: #f5f5f5; }
.upload-dropzone__icon { width: 44px; height: 44px; border-radius: 10px; background: #fff; border: 1.5px solid #ebebeb; color: #0c0c0c; display: inline-flex; align-items: center; justify-content: center; flex-shrink: 0; }
.upload-dropzone__text strong { display: block; font-size: 0.86rem; color: #111827; font-weight: 600; line-height: 1.2; }
.upload-dropzone__text small { display: block; font-size: 0.74rem; color: #9ca3af; margin-top: 3px; }

.upload-list { margin-top: 10px; display: flex; flex-direction: column; gap: 6px; }
.upload-list__item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 10px; font-size: 0.82rem; color: #374151; }
.upload-list__item svg { color: #9ca3af; flex-shrink: 0; }
.upload-list__name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-weight: 500; color: #111827; }
.upload-list__size { color: #9ca3af; font-size: 0.74rem; flex-shrink: 0; }

.is_dark .upload-dropzone { background: #0f172a; border-color: #2d3748; }
.is_dark .upload-dropzone:hover { border-color: #f3f4f6; background: #111827; }
.is_dark .upload-dropzone__icon { background: #111827; border-color: #2d3748; color: #f3f4f6; }
.is_dark .upload-dropzone__text strong { color: #f3f4f6; }
.is_dark .upload-list__item { background: #111827; border-color: #2d3748; }
.is_dark .upload-list__name { color: #f3f4f6; }

/* ════════════════════════════════════════════════════════
   PASSPORT-PHOTO UPLOADER — square click-to-upload box that
   sits opposite the stage description in the panel head.
   ════════════════════════════════════════════════════════ */
.form-panel__head--with-photo {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
}
.form-panel__head--with-photo .form-panel__head-text {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    flex: 1;
    min-width: 0;
}

.passport-uploader {
    position: relative;
    flex: 0 0 auto;
    width: 130px;
    height: 130px;
    border-radius: 14px;
    border: 2px dashed #d4d7de;
    background: linear-gradient(135deg, #fafafa 0%, #f5f5f5 100%);
    color: #6b7280;
    cursor: pointer;
    overflow: hidden;
    transition: all .22s cubic-bezier(.4, 0, .2, 1);
    display: block;
    font-family: 'Outfit', sans-serif !important;
    box-shadow: 0 1px 2px rgba(12, 12, 12, 0.04);
}
.passport-uploader:hover {
    border-color: #0c0c0c;
    background: linear-gradient(135deg, #ffffff 0%, #fafafa 100%);
    color: #111827;
    transform: translateY(-2px);
    box-shadow: 0 10px 28px rgba(12, 12, 12, 0.12);
}
.passport-uploader:focus { outline: none; }
.passport-uploader:focus-visible {
    border-color: #0c0c0c;
    box-shadow: 0 0 0 4px rgba(12, 12, 12, 0.12);
}

.passport-uploader__inner {
    width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 6px;
    padding: 10px;
    text-align: center;
    transition: opacity .2s, transform .2s;
}
.passport-uploader__icon { color: inherit; flex-shrink: 0; }
.passport-uploader__lines { display: flex; flex-direction: column; gap: 1px; line-height: 1.15; }
.passport-uploader__lines strong {
    font-size: 0.78rem;
    font-weight: 700;
    color: #111827;
    letter-spacing: 0.01em;
}
.passport-uploader__lines small {
    font-size: 0.68rem;
    color: #9ca3af;
    font-weight: 500;
}

/* Preview image fills the box once loaded */
.passport-uploader__preview {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 12px;
    opacity: 0;
    transition: opacity .25s ease;
}

/* "Change photo" overlay shown on hover when an image is present */
.passport-uploader__overlay {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    gap: 4px;
    background: linear-gradient(180deg, rgba(12, 12, 12, 0) 0%, rgba(12, 12, 12, 0.85) 100%);
    color: #ffffff;
    font-size: 0.74rem;
    font-weight: 600;
    border-radius: 12px;
    opacity: 0;
    transform: translateY(8px);
    transition: opacity .22s, transform .22s;
    pointer-events: none;
}

.passport-uploader.has-photo {
    border-style: solid;
    border-color: #15803d;
    background: #ffffff;
}
.passport-uploader.has-photo .passport-uploader__preview { opacity: 1; }
.passport-uploader.has-photo .passport-uploader__inner { opacity: 0; }
.passport-uploader.has-photo:hover .passport-uploader__overlay {
    opacity: 1;
    transform: translateY(0);
}

/* A subtle green check ribbon when an image is loaded */
.passport-uploader.has-photo::after {
    content: '';
    position: absolute;
    top: 8px;
    right: 8px;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    background: #15803d url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='white' stroke-width='3' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'/%3E%3C/svg%3E") center / 13px 13px no-repeat;
    box-shadow: 0 2px 6px rgba(21, 128, 61, 0.35);
}

/* Mobile: keep the box reasonable, drop to row layout */
@media (max-width: 720px) {
    .form-panel__head--with-photo {
        flex-direction: column-reverse;
        align-items: stretch;
    }
    .passport-uploader {
        width: 100%;
        height: 110px;
        margin-bottom: 4px;
    }
    .passport-uploader__inner { flex-direction: row; gap: 12px; }
    .passport-uploader__lines { text-align: left; }
}

/* Dark mode */
.is_dark .passport-uploader {
    background: linear-gradient(135deg, #0f172a 0%, #0b1322 100%);
    border-color: #2d3748;
    color: #9ca3af;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.4);
}
.is_dark .passport-uploader:hover {
    background: linear-gradient(135deg, #111827 0%, #0f172a 100%);
    border-color: #f3f4f6;
    color: #f3f4f6;
    box-shadow: 0 10px 28px rgba(0, 0, 0, 0.5);
}
.is_dark .passport-uploader__lines strong { color: #f3f4f6; }
.is_dark .passport-uploader__lines small { color: #6b7280; }
.is_dark .passport-uploader.has-photo { background: #0b1322; border-color: #16a34a; }

/* ════════════════════════════════════════════════════════
   WIZARD STEPPER — modern multi-step compose UI
   ════════════════════════════════════════════════════════ */
.form-wizard-stepper {
    margin: 0 0 22px;
    padding: 18px 22px 16px;
    background: #ffffff;
    border: 1.5px solid #ebebeb;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(12, 12, 12, 0.04);
    position: sticky;
    top: 10px;
    z-index: 6;
    backdrop-filter: saturate(180%) blur(8px);
    font-family: 'Outfit', sans-serif !important;
}
.form-wizard-stepper__progress {
    height: 4px;
    background: #f3f4f6;
    border-radius: 99px;
    overflow: hidden;
    margin-bottom: 14px;
}
.form-wizard-stepper__bar {
    height: 100%;
    background: linear-gradient(90deg, #15803d 0%, #16a34a 50%, #22c55e 100%);
    border-radius: 99px;
    transition: width .35s cubic-bezier(.4, 0, .2, 1);
}
.form-wizard-stepper__chips {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    padding-bottom: 4px;
    scrollbar-width: thin;
}
.form-wizard-stepper__chips::-webkit-scrollbar { height: 4px; }
.form-wizard-stepper__chips::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 2px; }

.form-wizard-chip {
    flex: 0 0 auto;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 8px 14px 8px 8px;
    background: #fafafa;
    border: 1.5px solid #ebebeb;
    border-radius: 99px;
    cursor: pointer;
    transition: all .22s cubic-bezier(.4, 0, .2, 1);
    font-family: 'Outfit', sans-serif !important;
    color: #6b7280;
    text-align: left;
}
.form-wizard-chip:hover {
    background: #f5f5f5;
    border-color: #d1d5db;
    color: #111827;
    transform: translateY(-1px);
}
.form-wizard-chip__circle {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #ffffff;
    border: 1.5px solid #ebebeb;
    color: #6b7280;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 0.78rem;
    flex-shrink: 0;
    transition: all .22s;
}
.form-wizard-chip__body {
    display: flex;
    flex-direction: column;
    gap: 2px;
    line-height: 1;
}
.form-wizard-chip__num {
    font-size: 0.62rem;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    font-weight: 700;
    color: #9ca3af;
}
.form-wizard-chip__label {
    font-size: 0.86rem;
    font-weight: 600;
    color: inherit;
    white-space: nowrap;
}

.form-wizard-chip.is-active {
    background: linear-gradient(135deg, #0c0c0c 0%, #1f2937 100%);
    border-color: #0c0c0c;
    color: #ffffff;
    box-shadow: 0 6px 18px rgba(12, 12, 12, 0.22);
    transform: translateY(-1px);
}
.form-wizard-chip.is-active .form-wizard-chip__circle {
    background: #ffffff;
    border-color: #ffffff;
    color: #0c0c0c;
}
.form-wizard-chip.is-active .form-wizard-chip__num { color: rgba(255, 255, 255, 0.65); }

.form-wizard-chip.is-done {
    background: #ecfdf5;
    border-color: #a7f3d0;
    color: #047857;
}
.form-wizard-chip.is-done .form-wizard-chip__circle {
    background: #15803d;
    border-color: #15803d;
    color: #ffffff;
}
.form-wizard-chip.is-done .form-wizard-chip__num { color: #16a34a; }

.form-wizard-chip__connector {
    flex: 0 0 18px;
    height: 2px;
    background: #ebebeb;
    border-radius: 99px;
}

.form-wizard-stepper__sub {
    margin-top: 12px;
    font-size: 0.82rem;
    color: #6b7280;
    line-height: 1.55;
    transition: opacity .25s;
}

/* Animate when fields toggle between steps */
.form-grid > [data-wizard-hidden="1"] { display: none !important; }
.form-grid {
    transition: opacity .25s ease, transform .25s ease;
}
.form-grid.is-transitioning { opacity: 0; transform: translateY(4px); }

[data-wizard-final-only][data-wizard-hidden="1"] { display: none !important; }

/* Ghost (Previous) button */
.btn-action.btn-action--ghost {
    background: transparent;
    color: #374151;
    border: 1.5px solid #e5e7eb;
}
.btn-action.btn-action--ghost:hover {
    background: #f9fafb;
    border-color: #d1d5db;
    color: #111827;
}

/* Mobile: stack chips vertically with reduced padding */
@media (max-width: 760px) {
    .form-wizard-stepper { padding: 14px 14px 12px; border-radius: 14px; position: relative; top: 0; }
    .form-wizard-chip { padding: 6px 12px 6px 6px; }
    .form-wizard-chip__label { font-size: 0.78rem; }
    .form-wizard-chip__num { display: none; }
}

/* Dark mode */
.is_dark .form-wizard-stepper {
    background: #0b1322;
    border-color: #1e2330;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.4);
}
.is_dark .form-wizard-stepper__progress { background: #1e2330; }
.is_dark .form-wizard-chip { background: #0f172a; border-color: #1e2330; color: #9ca3af; }
.is_dark .form-wizard-chip:hover { background: #111827; border-color: #2d3748; color: #f3f4f6; }
.is_dark .form-wizard-chip__circle { background: #0b1322; border-color: #1e2330; color: #9ca3af; }
.is_dark .form-wizard-chip__num { color: #6b7280; }
.is_dark .form-wizard-chip.is-active {
    background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
    color: #0c0c0c;
    border-color: #f3f4f6;
    box-shadow: 0 6px 18px rgba(243, 244, 246, 0.18);
}
.is_dark .form-wizard-chip.is-active .form-wizard-chip__circle { background: #0c0c0c; color: #f3f4f6; border-color: #0c0c0c; }
.is_dark .form-wizard-chip.is-active .form-wizard-chip__num { color: rgba(12, 12, 12, 0.6); }
.is_dark .form-wizard-chip.is-done { background: rgba(21, 128, 61, 0.18); border-color: #15803d; color: #6ee7b7; }
.is_dark .form-wizard-chip__connector { background: #1e2330; }
.is_dark .form-wizard-stepper__sub { color: #9ca3af; }
.is_dark .btn-action.btn-action--ghost { color: #d1d5db; border-color: #2d3748; }
.is_dark .btn-action.btn-action--ghost:hover { background: #111827; border-color: #4b5563; color: #f3f4f6; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formComposeForm');
    const actionInput = document.getElementById('formActionInput');

    form.addEventListener('click', function (e) {
        const btn = e.target.closest('[data-action]');
        if (!btn) return;
        actionInput.value = btn.dataset.action;
    });

    form.addEventListener('submit', function (e) {
        if (actionInput.value === 'send') {
            const sigInput = document.getElementById('signature_data_input');
            const reuseSaved = document.getElementById('reuse_saved_signature_input');
            const hasSig = (sigInput && sigInput.value && sigInput.value.length > 32)
                || (reuseSaved && reuseSaved.value === '1');
            if (sigInput && !hasSig) {
                e.preventDefault();
                alert('Please sign before forwarding the form, or choose "Use my saved signature".');
                return false;
            }
        }
    });

    // ── File upload preview ──
    const fileInput = document.getElementById('attachmentsInput');
    const uploadList = document.getElementById('uploadList');
    if (fileInput && uploadList) {
        fileInput.addEventListener('change', function () {
            uploadList.innerHTML = '';
            Array.from(fileInput.files).forEach(function (f) {
                const row = document.createElement('div');
                row.className = 'upload-list__item';
                const sizeKb = (f.size / 1024).toFixed(1);
                const sizeText = sizeKb > 1024 ? (sizeKb / 1024).toFixed(1) + ' MB' : sizeKb + ' KB';
                row.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>' +
                    '<span class="upload-list__name"></span>' +
                    '<span class="upload-list__size"></span>';
                row.querySelector('.upload-list__name').textContent = f.name;
                row.querySelector('.upload-list__size').textContent = sizeText;
                uploadList.appendChild(row);
            });
        });
    }

    // ════════════════════════════════════════════════════════
    // PASSPORT-PHOTO UPLOADER — click-to-upload + live preview
    // ════════════════════════════════════════════════════════
    const passportInput = document.getElementById('passport_photo_input');
    const passportBox   = document.getElementById('passportUploaderBox');
    if (passportInput && passportBox) {
        let previewImg = null;

        // Keyboard accessibility — Space / Enter on the focused box opens the picker.
        passportBox.addEventListener('keydown', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
                e.preventDefault();
                passportInput.click();
            }
        });

        passportInput.addEventListener('change', function () {
            const file = passportInput.files && passportInput.files[0];
            if (!file) return;

            // Guard: must be an image. The accept="image/*" attribute is a hint
            // only — paranoid browsers and drag/drop can still slip non-images
            // through.
            if (!file.type || !file.type.startsWith('image/')) {
                alert('Please choose an image file (JPEG, PNG, etc.) for your passport photo.');
                passportInput.value = '';
                return;
            }

            // Reasonable cap to avoid users uploading a 30 MB phone snap.
            const MAX_BYTES = 8 * 1024 * 1024; // 8 MB
            if (file.size > MAX_BYTES) {
                alert('That image is larger than 8 MB. Please choose a smaller passport photo.');
                passportInput.value = '';
                return;
            }

            const reader = new FileReader();
            reader.onload = function (e) {
                if (!previewImg) {
                    previewImg = document.createElement('img');
                    previewImg.className = 'passport-uploader__preview';
                    previewImg.alt = 'Passport photograph preview';
                    // Insert preview before the inner content so the absolute-
                    // positioned overlay still sits on top of it.
                    passportBox.insertBefore(previewImg, passportBox.firstChild.nextSibling);
                }
                previewImg.src = e.target.result;
                passportBox.classList.add('has-photo');
            };
            reader.readAsDataURL(file);
        });
    }

    // ════════════════════════════════════════════════════════
    // WIZARD — multi-step compose page
    // ════════════════════════════════════════════════════════
    const stepperEl = document.getElementById('formWizardStepper');
    if (!stepperEl) return; // Single-step form — nothing else to wire.

    let steps = [];
    try { steps = JSON.parse(stepperEl.dataset.wizardSteps || '[]'); } catch (e) { steps = []; }
    if (steps.length < 2) return;

    const fieldGrid = document.querySelector('#formWizardFieldsHost .form-grid');
    if (!fieldGrid) return;

    // Tag every grid child with the wizard step it belongs to. We walk the
    // children in document order; whenever we hit a child whose data-field-name
    // matches a step's `startAt`, that step becomes the current "owner" for
    // every subsequent child until the next boundary.
    const firstKey = steps[0].key;
    let currentKey = firstKey;
    const stepBoundaryByField = {};
    steps.forEach(function (s) { stepBoundaryByField[s.startAt] = s.key; });

    Array.from(fieldGrid.children).forEach(function (child) {
        const fname = child.dataset.fieldName;
        if (fname && stepBoundaryByField[fname]) {
            currentKey = stepBoundaryByField[fname];
        }
        child.dataset.wizardOwner = currentKey;
    });

    // Panels marked data-wizard-final-only are only visible on the final step.
    const finalKey = steps[steps.length - 1].key;
    const finalOnlyPanels = Array.from(document.querySelectorAll('[data-wizard-final-only]'));

    const chips = Array.from(stepperEl.querySelectorAll('[data-wizard-chip]'));
    const subEl = document.getElementById('formWizardSub');
    const barEl = document.getElementById('formWizardBar');
    const prevBtn = document.getElementById('formWizardPrev');
    const nextBtn = document.getElementById('formWizardNext');
    const forwardBtn = document.getElementById('formForwardBtn');

    let activeIndex = 0;
    const visitedSteps = new Set([firstKey]);

    function applyStep(targetIndex, opts) {
        opts = opts || {};
        targetIndex = Math.max(0, Math.min(steps.length - 1, targetIndex));
        const target = steps[targetIndex];
        activeIndex = targetIndex;
        visitedSteps.add(target.key);

        // Fade the grid during the swap so the change feels intentional.
        fieldGrid.classList.add('is-transitioning');

        setTimeout(function () {
            // Toggle every tagged child of the grid.
            Array.from(fieldGrid.children).forEach(function (child) {
                child.dataset.wizardHidden = (child.dataset.wizardOwner === target.key) ? '0' : '1';
            });
            // Toggle the panels that should only appear on the final step.
            finalOnlyPanels.forEach(function (panel) {
                panel.dataset.wizardHidden = (target.key === finalKey) ? '0' : '1';
            });

            // Stepper chip states.
            chips.forEach(function (chip, i) {
                chip.classList.remove('is-active', 'is-done');
                if (i === activeIndex) {
                    chip.classList.add('is-active');
                } else if (i < activeIndex || visitedSteps.has(steps[i].key)) {
                    chip.classList.add('is-done');
                }
            });

            // Progress bar — % of completed steps INCLUDING the current.
            if (barEl) {
                const pct = Math.round(((activeIndex + 1) / steps.length) * 100);
                barEl.style.width = pct + '%';
            }
            if (subEl) {
                subEl.textContent = target.description || '';
            }

            // Buttons.
            if (prevBtn) {
                prevBtn.style.display = activeIndex === 0 ? 'none' : '';
            }
            const isFinal = activeIndex === steps.length - 1;
            if (nextBtn) {
                nextBtn.style.display = isFinal ? 'none' : '';
            }
            if (forwardBtn) {
                forwardBtn.style.display = isFinal ? '' : 'none';
            }

            fieldGrid.classList.remove('is-transitioning');

            if (!opts.noScroll) {
                // Scroll the user back to the top of the form so the new
                // step's first field is visible.
                stepperEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }, opts.noScroll ? 0 : 140);
    }

    // Validate required fields in the current step before advancing.
    // The field-renderer emits data-required="1" on every field whose
    // FormField was marked required. We treat empty values (or unchecked
    // required-checkbox / no-checked-radio) as invalid and nudge the user
    // to that field. Server-side validation still runs on final submit.
    function validateCurrentStep() {
        const target = steps[activeIndex];
        const requiredFields = Array.from(
            fieldGrid.querySelectorAll('[data-wizard-owner="' + CSS.escape(target.key) + '"][data-required="1"]')
        );
        let firstInvalidField = null;
        let firstInvalidInput = null;

        for (let i = 0; i < requiredFields.length; i++) {
            const field = requiredFields[i];
            const fieldType = field.dataset.fieldType;
            let invalid = false;

            if (fieldType === 'checkbox') {
                const cb = field.querySelector('input[type="checkbox"]');
                if (cb && !cb.checked) invalid = true;
            } else if (fieldType === 'radio') {
                const radios = field.querySelectorAll('input[type="radio"]');
                const anyChecked = Array.from(radios).some(function (r) { return r.checked; });
                if (radios.length && !anyChecked) invalid = true;
            } else if (fieldType === 'table') {
                // Tables: at least one row must have at least one non-empty cell.
                const cellInputs = Array.from(field.querySelectorAll('[data-table-rows] input, [data-table-rows] textarea, [data-table-rows] select'));
                const anyFilled = cellInputs.some(function (i) { return (i.value || '').trim() !== ''; });
                if (!anyFilled) invalid = true;
            } else {
                // text / textarea / number / date / currency / select
                const input = field.querySelector('input, textarea, select');
                if (input && (!input.value || !input.value.trim())) invalid = true;
            }

            if (invalid && !firstInvalidField) {
                firstInvalidField = field;
                firstInvalidInput = field.querySelector('input, textarea, select');
            }
        }

        if (firstInvalidField) {
            if (firstInvalidInput) firstInvalidInput.focus({ preventScroll: false });
            firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstInvalidField.classList.add('is-wizard-invalid-field');
            setTimeout(function () { firstInvalidField.classList.remove('is-wizard-invalid-field'); }, 2200);
            return false;
        }
        return true;
    }

    // Wire chip clicks. Allow jumping back freely; for jumping FORWARD
    // require the in-between steps to validate so the user isn't
    // accidentally skipping required content.
    chips.forEach(function (chip, idx) {
        chip.addEventListener('click', function () {
            if (idx <= activeIndex) {
                applyStep(idx);
                return;
            }
            // Walk forward, validating each step on the way.
            let okIndex = activeIndex;
            for (let i = activeIndex; i < idx; i++) {
                if (i === activeIndex) {
                    if (validateCurrentStep()) {
                        okIndex = i + 1;
                    } else {
                        return;
                    }
                } else {
                    // Briefly land on the intermediate step to validate it.
                    applyStep(i, { noScroll: true });
                    if (validateCurrentStep()) {
                        okIndex = i + 1;
                    } else {
                        return;
                    }
                }
            }
            applyStep(okIndex);
        });
    });

    if (nextBtn) {
        nextBtn.addEventListener('click', function () {
            if (validateCurrentStep()) {
                applyStep(activeIndex + 1);
            }
        });
    }
    if (prevBtn) {
        prevBtn.addEventListener('click', function () { applyStep(activeIndex - 1); });
    }

    // Save-as-draft should always succeed, regardless of step. But the
    // "Sign & forward" button is hidden until the last step — if a user
    // hits Enter inside an input (which natively submits the form), we
    // intercept here and behave like clicking Next: validate this step,
    // advance if valid, otherwise prevent the submit and highlight the
    // missing field.
    form.addEventListener('submit', function (e) {
        if (actionInput.value === 'send' && activeIndex !== steps.length - 1) {
            e.preventDefault();
            e.stopPropagation();
            if (validateCurrentStep()) {
                applyStep(activeIndex + 1);
            }
        }
    }, true); // capture phase so we run before the existing submit handler

    // On load: hide everything except the first step.
    applyStep(0, { noScroll: true });
});
</script>

<style>
.is-wizard-invalid-field {
    position: relative;
    animation: wizardNudge .55s cubic-bezier(.36, .07, .19, .97);
}
.is-wizard-invalid-field .form-field__label::after {
    content: ' — please complete';
    font-size: 0.74rem;
    color: #b91c1c;
    font-weight: 600;
    font-style: italic;
    margin-left: 4px;
}
.is-wizard-invalid-field .form-control,
.is-wizard-invalid-field .form-select,
.is-wizard-invalid-field .radio-group,
.is-wizard-invalid-field .checkbox-pill,
.is-wizard-invalid-field .form-table {
    border-color: #b91c1c !important;
    box-shadow: 0 0 0 3px rgba(185, 28, 28, 0.12) !important;
}
@keyframes wizardNudge {
    0%, 100% { transform: translateX(0); }
    20%, 60% { transform: translateX(-4px); }
    40%, 80% { transform: translateX(4px); }
}
</style>

@include('admin.forms.partials.shared-styles')
@endsection
