@extends('layout.app')

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

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor" style="flex-shrink:0; margin-top: 2px;"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <div>
                                    <strong>Please fix the following:</strong>
                                    <ul>
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        @endif

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

                            {{-- Stage 1: requisitioner fields --}}
                            <div class="form-panel">
                                <div class="form-panel__head">
                                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                                        <span class="form-panel__step-num">1</span>
                                        <div>
                                            <h2 class="form-panel__title">{{ $stage->label }}<span class="form-panel__title-bar"></span></h2>
                                            @if($stage->description)
                                                <p class="form-panel__desc">{{ $stage->description }}</p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="form-panel__body">
                                    @include('admin.forms.partials.field-renderer', [
                                        'stage'       => $stage,
                                        'sectionData' => $sectionData,
                                        'readonly'    => false,
                                    ])
                                </div>
                            </div>

                            {{-- Stage 2: attachments --}}
                            <div class="form-panel">
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
                                <div class="form-panel">
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
                                <div class="form-panel">
                                    <div class="form-panel__head">
                                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                                            <span class="form-panel__step-num">4</span>
                                            <div>
                                                <h2 class="form-panel__title">Forward to {{ $nextStage->label }}<span class="form-panel__title-bar"></span></h2>
                                                @if($nextStage->isLeadershipPool())
                                                    <p class="form-panel__desc">Choose whether this form is going to a <strong>Dean</strong>, <strong>HOD</strong> or <strong>Director</strong>, then pick the specific person from the list.</p>
                                                @else
                                                    <p class="form-panel__desc">Pick the specific person in <strong>{{ $nextOffice?->name ?? 'the next office' }}</strong> who should receive this form next.</p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-panel__body">
                                        @if($nextStage->isLeadershipPool())
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

                            <div class="form-actions">
                                <button type="submit" class="btn-action btn-action--draft" data-action="draft">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/></svg>
                                    Save as draft
                                </button>
                                <button type="submit" class="btn-action btn-action--primary" data-action="send">
                                    Sign &amp; forward
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
});
</script>

@include('admin.forms.partials.shared-styles')
@endsection
