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

                        <div class="form-page-header">
                            <div>
                                <h1 class="form-page-title">All Forms<span class="form-title-bar"></span></h1>
                                <p class="form-page-sub">Pick a form to start filling. It will route automatically through each office once signed.</p>
                            </div>
                            <a href="{{ route('admin.forms.portal') }}" class="gallery-portal-btn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg>
                                Forms portal
                            </a>
                        </div>

                        @include('components.premium-search-bar', [
                            'placeholder'      => 'Search forms by name, code, or description…',
                            'target'           => '.gallery-card[data-search]',
                            'countLabel'       => 'forms',
                            'id'               => 'forms-gallery-search',
                            'hideWhenFilter'   => '.gallery-card--placeholder',
                        ])

                        <div class="gallery-grid" data-gallery-grid>
                            @foreach($forms as $form)
                                <a href="{{ route('admin.forms.compose', $form->slug()) }}"
                                   class="gallery-card"
                                   data-search="{{ strtolower($form->code() . ' ' . $form->title() . ' ' . $form->description()) }}">
                                    <div class="gallery-card__top">
                                        <div class="gallery-card__code">{{ $form->code() }}</div>
                                        <div class="gallery-card__steps">{{ count($form->stages()) }} stages</div>
                                    </div>
                                    <h3 class="gallery-card__title">{{ $form->title() }}</h3>
                                    <p class="gallery-card__desc">{{ \Illuminate\Support\Str::limit($form->description(), 100) }}</p>
                                    <div class="gallery-card__cta">
                                        <span>Start form</span>
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                                    </div>
                                </a>
                            @endforeach

                            <div class="gallery-card gallery-card--placeholder">
                                <div class="gallery-card__top">
                                    <div class="gallery-card__code gallery-card__code--ghost">+</div>
                                </div>
                                <h3 class="gallery-card__title">More forms coming</h3>
                                <p class="gallery-card__desc">New form types can be dropped in without re-deployment.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
.form-shell, .form-shell * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.form-shell { max-width: 1020px; padding: 4px 0 60px; }

.form-page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb; flex-wrap: wrap; }
.form-page-title { font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em; line-height: 1.1; margin: 0; display: inline-flex; flex-direction: column; }
.form-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.form-page-sub { margin: 12px 0 0; font-size: 0.88rem; color: #8a8fa0; font-weight: 400; max-width: 520px; line-height: 1.5; }

.gallery-portal-btn { display: inline-flex; align-items: center; gap: 7px; padding: 9px 16px; background: #fff; color: #374151; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.84rem; font-weight: 600; text-decoration: none; margin-top: 14px; transition: all .15s; }
.gallery-portal-btn:hover { border-color: #0c0c0c; color: #0c0c0c; text-decoration: none; transform: translateY(-1px); }

.alert { padding: 12px 14px; border-radius: 10px; margin-bottom: 14px; font-size: 0.86rem; font-weight: 500; border: 1.5px solid transparent; }
.alert-success { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }

/* ── Grid: smaller, denser cards ── */
.gallery-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(255px, 1fr)); gap: 14px; }

.gallery-card { display: flex; flex-direction: column; padding: 18px 18px 16px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 14px; text-decoration: none !important; color: inherit; transition: all .18s; min-height: 180px; position: relative; }
.gallery-card:hover { border-color: #0c0c0c; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(12,12,12,.08); color: inherit; }
.gallery-card--placeholder { background: #fafafa; border: 1.5px dashed #e5e7eb; cursor: default; }
.gallery-card--placeholder:hover { transform: none; box-shadow: none; border-color: #e5e7eb; }

.gallery-card__top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 14px; }
.gallery-card__code { display: inline-flex; align-items: center; justify-content: center; min-width: 38px; height: 30px; padding: 0 10px; border-radius: 7px; background: #0c0c0c; color: #fff; font-weight: 700; font-size: 0.78rem; letter-spacing: 0.06em; }
.gallery-card__code--ghost { background: #f3f4f6; color: #b0b5c0; font-size: 1.1rem; }
.gallery-card__steps { font-size: 0.7rem; color: #9ca3af; font-weight: 500; background: #f9fafb; border: 1px solid #ebebeb; padding: 3px 9px; border-radius: 99px; }

.gallery-card__title { font-size: 0.98rem; font-weight: 700; color: #111827; margin: 0 0 6px; letter-spacing: -0.02em; line-height: 1.25; }
.gallery-card__desc { color: #9ca3af; font-size: 0.78rem; line-height: 1.5; margin: 0 0 14px; flex: 1; }

.gallery-card__cta { display: inline-flex; align-items: center; gap: 6px; color: #0c0c0c; font-weight: 600; font-size: 0.82rem; margin-top: auto; padding-top: 10px; border-top: 1.5px dashed #ebebeb; }
.gallery-card:hover .gallery-card__cta svg { transform: translateX(2px); }
.gallery-card__cta svg { transition: transform .15s; }

/* Dark mode */
.is_dark .form-page-title { color: #f3f4f6; }
.is_dark .form-title-bar { background: #f3f4f6; }
.is_dark .form-page-sub { color: #6b7280; }
.is_dark .form-page-header { border-color: #1e2330; }
.is_dark .gallery-portal-btn { background: #111827; color: #d1d5db; border-color: #2d3748; }
.is_dark .gallery-portal-btn:hover { border-color: #f3f4f6; color: #f3f4f6; }
.is_dark .gallery-card { background: #111827; border-color: #1e2330; }
.is_dark .gallery-card:hover { border-color: #f3f4f6; }
.is_dark .gallery-card__code { background: #f3f4f6; color: #0c0c0c; }
.is_dark .gallery-card__title { color: #f3f4f6; }
.is_dark .gallery-card__cta { color: #f3f4f6; }
.is_dark .gallery-card--placeholder { background: #0f172a; border-color: #2d3748; }
.is_dark .gallery-card__steps { background: #0f172a; border-color: #2d3748; color: #9ca3af; }
</style>
@endsection
