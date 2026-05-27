@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

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
                    <div class="dashboard__content__wraper">
                        <div class="dashboard__section__title">
                            <h4>{{ $definition->title() }}</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{ route('admin.forms.gallery') }}" class="responsive-btn back-btn">
                                    <div class="svgWrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                            <path stroke="#fff" stroke-width="2" d="M19 12H5m7-7-7 7 7 7"></path>
                                        </svg>
                                        <div class="text">Back to All Forms</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
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

                            <div class="form-panel">
                                <div class="form-panel__head">
                                    <div class="form-panel__code">{{ $definition->code() }}</div>
                                    <div>
                                        <h5 class="form-panel__title">{{ $stage->label }}</h5>
                                        @if($stage->description)
                                            <p class="form-panel__desc">{{ $stage->description }}</p>
                                        @endif
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

                            <div class="form-panel">
                                <div class="form-panel__head">
                                    <div>
                                        <h5 class="form-panel__title">Attachments</h5>
                                        <p class="form-panel__desc">Original supporting documents, invoices, receipts, etc.</p>
                                    </div>
                                </div>
                                <div class="form-panel__body">
                                    <input type="file" name="attachments[]" multiple class="form-control">
                                </div>
                            </div>

                            @if($stage->signatureRequired)
                                <div class="form-panel">
                                    <div class="form-panel__head">
                                        <div>
                                            <h5 class="form-panel__title">Your signature</h5>
                                            <p class="form-panel__desc">Draw your signature below, or reuse your saved one.</p>
                                        </div>
                                    </div>
                                    <div class="form-panel__body">
                                        @include('admin.forms.partials.signature-pad', [
                                            'savedSignature' => $savedSignature,
                                        ])
                                    </div>
                                </div>
                            @endif

                            @if($nextStage)
                                <div class="form-panel">
                                    <div class="form-panel__head">
                                        <div>
                                            <h5 class="form-panel__title">Forward to: {{ $nextStage->label }}</h5>
                                            <p class="form-panel__desc">Pick the specific person in the {{ $nextOffice?->name ?? 'next office' }} who should receive this form.</p>
                                        </div>
                                    </div>
                                    <div class="form-panel__body">
                                        @include('admin.forms.partials.recipient-picker', [
                                            'office'    => $nextOffice,
                                            'fieldName' => 'next_assignee_id',
                                            'required'  => true,
                                        ])
                                    </div>
                                </div>
                            @endif

                            <div class="form-actions">
                                <button type="submit" class="btn-action btn-action--draft" data-action="draft">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline></svg>
                                    Save as Draft
                                </button>
                                <button type="submit" class="btn-action btn-action--primary" data-action="send">
                                    Sign &amp; Forward
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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
            if (!hasSig) {
                e.preventDefault();
                alert('Please sign before forwarding the form, or choose "Use my saved signature".');
                return false;
            }
        }
    });
});
</script>

@include('admin.forms.partials.shared-styles')
@endsection
