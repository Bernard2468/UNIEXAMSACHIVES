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
                            <h4>All Forms</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{ route('admin.forms.portal') }}" class="responsive-btn back-btn">
                                    <div class="svgWrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                            <path stroke="#fff" stroke-width="2" d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                                        </svg>
                                        <div class="text">Forms Portal</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success">{{ session('success') }}</div>
                        @endif

                        <p class="forms-gallery-lead">
                            Pick a form below to fill it out. After your section is signed it will be routed to the next office automatically.
                        </p>

                        <div class="forms-gallery">
                            @foreach($forms as $form)
                                <a href="{{ route('admin.forms.compose', $form->slug()) }}" class="form-card">
                                    <div class="form-card__code">{{ $form->code() }}</div>
                                    <div class="form-card__body">
                                        <h5 class="form-card__title">{{ $form->title() }}</h5>
                                        <p class="form-card__desc">{{ $form->description() }}</p>
                                        <div class="form-card__stages">
                                            @foreach($form->stages() as $i => $stage)
                                                <span class="form-card__stage">{{ $i + 1 }}. {{ $stage->label }}</span>
                                            @endforeach
                                        </div>
                                    </div>
                                    <div class="form-card__cta">
                                        Start this form
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="5" y1="12" x2="19" y2="12"></line>
                                            <polyline points="12 5 19 12 12 19"></polyline>
                                        </svg>
                                    </div>
                                </a>
                            @endforeach

                            <div class="form-card form-card--placeholder">
                                <div class="form-card__code">+</div>
                                <div class="form-card__body">
                                    <h5 class="form-card__title">More forms coming soon</h5>
                                    <p class="form-card__desc">Need a new form added to the workflow? Ask the Super Admin to add it — the system is designed so new forms drop in without re-deploying.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.forms-gallery-lead { color: #6b7280; font-size: 15px; margin-bottom: 22px; }
.forms-gallery { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 18px; }
.form-card { display: flex; flex-direction: column; padding: 22px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; text-decoration: none; color: inherit; transition: all 0.2s; position: relative; min-height: 240px; }
.form-card:hover { border-color: #1d4ed8; box-shadow: 0 6px 22px rgba(29,78,216,0.10); transform: translateY(-2px); text-decoration: none; color: inherit; }
.form-card--placeholder { background: #f9fafb; border: 1px dashed #d1d5db; cursor: default; }
.form-card--placeholder:hover { transform: none; box-shadow: none; border-color: #d1d5db; }
.form-card__code { display: inline-flex; align-items: center; justify-content: center; width: 48px; height: 48px; border-radius: 10px; background: #1d4ed8; color: #fff; font-weight: 700; font-size: 18px; margin-bottom: 14px; letter-spacing: 1px; }
.form-card--placeholder .form-card__code { background: #e5e7eb; color: #9ca3af; }
.form-card__body { flex: 1; }
.form-card__title { font-size: 17px; font-weight: 600; color: #111827; margin: 0 0 8px; }
.form-card__desc { color: #6b7280; font-size: 13.5px; line-height: 1.55; margin-bottom: 14px; }
.form-card__stages { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 14px; }
.form-card__stage { font-size: 11px; color: #4b5563; background: #f3f4f6; padding: 4px 10px; border-radius: 99px; }
.form-card__cta { display: inline-flex; align-items: center; gap: 8px; color: #1d4ed8; font-weight: 600; font-size: 14px; margin-top: auto; }
</style>
@endsection
