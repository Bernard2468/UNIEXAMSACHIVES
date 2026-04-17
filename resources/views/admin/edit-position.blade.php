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
                    <div class="ep-wrap">

                        {{-- Page header --}}
                        <div class="ep-page-header">
                            <a href="{{ route('positions.index') }}" class="ep-back">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                                Back to positions
                            </a>
                            <h1 class="ep-page-title">Edit position<span class="ep-title-bar"></span></h1>
                            <p class="ep-page-sub">Update the title of this staff position or role.</p>
                        </div>

                        {{-- Edit card --}}
                        <div class="ep-card">
                            <div class="ep-card__hd">
                                <h2 class="ep-card__title">Position details<span class="ep-card__bar"></span></h2>
                                <p class="ep-card__sub">Editing: <strong>{{ $position->name }}</strong></p>
                            </div>
                            <div class="ep-card__body">
                                <form action="{{ route('positions.update', $position->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')

                                    @if(session('success'))
                                    <div class="ep-alert ep-alert--ok">
                                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        <span>{{ session('success') }}</span>
                                    </div>
                                    @endif

                                    @if($errors->any())
                                    <div class="ep-alert ep-alert--err">
                                        <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                        <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                                    </div>
                                    @endif

                                    <div class="ep-field">
                                        <label class="ep-label">Position / Role name</label>
                                        <input class="ep-input" type="text" name="name" value="{{ old('name', $position->name) }}" placeholder="e.g. Head of Department" required autofocus>
                                    </div>

                                    <div class="ep-foot">
                                        <a href="{{ route('positions.index') }}" class="ep-btn-cancel">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                                            Cancel
                                        </a>
                                        <button type="submit" class="ep-btn-save">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Save changes
                                        </button>
                                    </div>
                                </form>
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

.ep-wrap, .ep-wrap * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.ep-wrap { max-width: 680px; padding: 4px 0 60px; }

/* ── Page header ── */
.ep-page-header { margin-bottom: 28px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb; }

.ep-back {
    display: inline-flex; align-items: center; gap: 6px; font-size: 0.82rem;
    font-weight: 600; color: #9ca3af; text-decoration: none;
    margin-bottom: 18px; transition: color .15s;
}
.ep-back:hover { color: #374151; text-decoration: none; }

.ep-page-title {
    font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em;
    line-height: 1.1; margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ep-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.ep-page-sub { margin: 14px 0 0; font-size: 0.9rem; color: #8a8fa0; }

/* ── Card ── */
.ep-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.ep-card__hd { padding: 20px 26px 16px; border-bottom: 1.5px solid #f5f5f5; }
.ep-card__title {
    font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em;
    margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ep-card__bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.ep-card__sub { margin: 8px 0 0; font-size: 0.82rem; color: #9ca3af; }
.ep-card__sub strong { color: #374151; font-weight: 600; }
.ep-card__body { padding: 24px 26px 26px; }

/* ── Alerts ── */
.ep-alert {
    display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px;
    border-radius: 10px; margin-bottom: 20px; font-size: 0.875rem; font-weight: 500;
    border: 1.5px solid transparent;
}
.ep-alert--ok  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.ep-alert--err { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

/* ── Field ── */
.ep-field { margin-bottom: 24px; }
.ep-label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 8px; letter-spacing: .01em; }
.ep-input {
    display: block; width: 100%; padding: 11px 14px;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.9rem; color: #111827; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.ep-input:hover { border-color: #d1d5db; }
.ep-input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.08); }
.ep-input::placeholder { color: #d4d7de; }

/* ── Form footer ── */
.ep-foot {
    display: flex; align-items: center; gap: 10px;
    padding-top: 20px; border-top: 1.5px solid #f5f5f5; margin-top: 4px;
    justify-content: flex-end;
}
.ep-btn-cancel {
    display: inline-flex; align-items: center; gap: 6px; padding: 9px 18px;
    background: none; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; color: #6b7280; cursor: pointer;
    text-decoration: none; transition: all .15s;
}
.ep-btn-cancel:hover { border-color: #d1d5db; color: #374151; background: #f9fafb; text-decoration: none; }
.ep-btn-save {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 20px;
    background: #0c0c0c; color: #fff; border: none; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; cursor: pointer;
    transition: background .15s, transform .12s, box-shadow .15s;
    font-family: 'Outfit', sans-serif !important;
}
.ep-btn-save:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }
.ep-btn-save:active { transform: translateY(0); box-shadow: none; }

/* ── Dark mode ── */
.is_dark .ep-page-title  { color: #f3f4f6; }
.is_dark .ep-title-bar   { background: #f3f4f6; }
.is_dark .ep-page-sub    { color: #6b7280; }
.is_dark .ep-page-header { border-color: #1e2330; }
.is_dark .ep-card        { background: #111827; border-color: #1e2330; }
.is_dark .ep-card__hd    { border-color: #1e2330; }
.is_dark .ep-card__title { color: #f3f4f6; }
.is_dark .ep-card__bar   { background: #f3f4f6; }
.is_dark .ep-card__sub strong { color: #d1d5db; }
.is_dark .ep-label       { color: #d1d5db; }
.is_dark .ep-input       { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .ep-input:focus { border-color: #f3f4f6; }
.is_dark .ep-foot        { border-color: #1e2330; }
.is_dark .ep-btn-save    { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ep-btn-save:hover { background: #e5e7eb; }
</style>

@endsection
