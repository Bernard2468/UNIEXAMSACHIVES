@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100 mp-page-compact">
    <div class="container-fluid full__width__padding" style="display:none">
        <div class="row">@include('components.create_section')</div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="mp-wrap">

                        {{-- ── Page header ── --}}
                        <div class="mp-page-header">
                            <h1 class="mp-page-title">My Profile<span class="mp-title-bar"></span></h1>
                            <p class="mp-page-sub">Your account overview and identity on the system.</p>
                        </div>

                        {{-- ── Hero card: avatar + name + status ── --}}
                        <div class="mp-hero">
                            <div class="mp-hero__avatar-shell">
                                @if($data->profile_picture)
                                    <img class="mp-hero__avatar" src="{{ asset('profile_pictures/' . $data->profile_picture) }}" alt="{{ $data->first_name }}">
                                @else
                                    <div class="mp-hero__avatar mp-hero__avatar--initials">
                                        {{ strtoupper(substr($data->first_name, 0, 1)) }}{{ strtoupper(substr($data->last_name, 0, 1)) }}
                                    </div>
                                @endif
                                <span class="mp-hero__status-dot {{ $data->is_approve ? 'mp-hero__status-dot--ok' : 'mp-hero__status-dot--pending' }}"></span>
                            </div>

                            <div class="mp-hero__info">
                                <h2 class="mp-hero__name">{{ $data->first_name }} {{ $data->last_name }}</h2>

                                @if($data->position)
                                    <span class="mp-hero__role">{{ $data->position->name }}</span>
                                @elseif($data->staff_category)
                                    <span class="mp-hero__role">{{ $data->staff_category }}</span>
                                @endif

                                <div class="mp-hero__badges">
                                    <span class="mp-badge {{ $data->is_approve ? 'mp-badge--green' : 'mp-badge--amber' }}">
                                        @if($data->is_approve)
                                            <svg width="11" height="11" viewBox="0 0 12 12" fill="currentColor"><path d="M10.28 2.28a1 1 0 00-1.41 0L4.75 6.38 3.13 4.76a1 1 0 00-1.41 1.41l2.33 2.33a1 1 0 001.41 0l4.83-4.83a1 1 0 000-1.39z"/></svg>
                                            Approved
                                        @else
                                            <svg width="11" height="11" viewBox="0 0 12 12" fill="currentColor"><circle cx="6" cy="6" r="5"/><path fill="#fff" d="M6 3.5v3M6 8h.01" stroke="#fff" stroke-width="1.2" stroke-linecap="round"/></svg>
                                            Pending approval
                                        @endif
                                    </span>
                                    <span class="mp-badge mp-badge--gray">
                                        <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        Since {{ $data->created_at->format('M Y') }}
                                    </span>
                                </div>
                            </div>

                            <a href="{{ route('dashboard.settings') }}" class="mp-hero__edit-btn">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                Edit profile
                            </a>
                        </div>

                        {{-- ── Detail rows ── --}}
                        <div class="mp-card">
                            <div class="mp-card__header">
                                <h3 class="mp-card__title">Account details<span class="mp-card__bar"></span></h3>
                            </div>

                            <div class="mp-rows">

                                <div class="mp-row">
                                    <div class="mp-row__label">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                                        Email address
                                    </div>
                                    <div class="mp-row__value">{{ $data->email }}</div>
                                </div>

                                <div class="mp-row">
                                    <div class="mp-row__label">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                                        Registration date
                                    </div>
                                    <div class="mp-row__value">{{ $data->created_at->format('d F Y') }}</div>
                                </div>

                                <div class="mp-row">
                                    <div class="mp-row__label">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                        Department / Faculty
                                    </div>
                                    <div class="mp-row__value">{{ $data->department->name ?? '—' }}</div>
                                </div>

                                <div class="mp-row">
                                    <div class="mp-row__label">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                        Staff category
                                    </div>
                                    <div class="mp-row__value">{{ $data->staff_category ?? '—' }}</div>
                                </div>

                                <div class="mp-row">
                                    <div class="mp-row__label">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                        Position
                                    </div>
                                    <div class="mp-row__value">{{ $data->position->name ?? '—' }}</div>
                                </div>

                                <div class="mp-row mp-row--last">
                                    <div class="mp-row__label">
                                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                        Account status
                                    </div>
                                    <div class="mp-row__value">
                                        <span class="mp-inline-badge {{ $data->is_approve ? 'mp-inline-badge--green' : 'mp-inline-badge--amber' }}">
                                            {{ $data->is_approve ? 'Approved' : 'Pending approval' }}
                                        </span>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- ── CTA: Edit profile settings ── --}}
                        <div class="mp-cta">
                            <div class="mp-cta__text">
                                <span class="mp-cta__heading">Want to update your information?</span>
                                <span class="mp-cta__sub">Change your name, photo, department, or password in Profile Settings.</span>
                            </div>
                            <a href="{{ route('dashboard.settings') }}" class="mp-cta__btn">
                                Go to Profile Settings
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                        </div>

                    </div>{{-- /mp-wrap --}}
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.mp-wrap,
.mp-wrap * {
    font-family: 'Outfit', sans-serif !important;
    box-sizing: border-box;
}

.mp-page-compact .settings-page-hero { display: none !important; }

/* ── Wrapper ── */
.mp-wrap {
    max-width: 820px;
    padding: 4px 0 60px;
}

/* ── Page header ── */
.mp-page-header {
    margin-bottom: 28px;
    padding-bottom: 24px;
    border-bottom: 1.5px solid #ebebeb;
}

.mp-page-title {
    font-size: 2rem;
    font-weight: 800;
    color: #0c0c0c;
    letter-spacing: -0.045em;
    line-height: 1.1;
    margin: 0 0 4px;
    display: inline-flex;
    flex-direction: column;
}

.mp-title-bar {
    display: block;
    width: 2.4rem;
    height: 3.5px;
    background: #0c0c0c;
    border-radius: 3px;
    margin-top: 9px;
}

.mp-page-sub {
    margin: 14px 0 0;
    font-size: 0.9rem;
    color: #8a8fa0;
    font-weight: 400;
}

/* ── Hero card ── */
.mp-hero {
    display: flex;
    align-items: center;
    gap: 22px;
    padding: 24px 26px;
    background: #fff;
    border: 1.5px solid #ebebeb;
    border-radius: 16px;
    margin-bottom: 18px;
    position: relative;
}

.mp-hero__avatar-shell {
    position: relative;
    flex-shrink: 0;
}

.mp-hero__avatar {
    width: 88px;
    height: 88px;
    border-radius: 50%;
    object-fit: cover;
    display: block;
    background: #e9eaec;
    border: 2.5px solid #ebebeb;
}

.mp-hero__avatar--initials {
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.6rem;
    font-weight: 800;
    color: #374151;
    background: #f3f4f6;
    letter-spacing: -0.03em;
}

.mp-hero__status-dot {
    position: absolute;
    bottom: 5px;
    right: 3px;
    width: 14px;
    height: 14px;
    border-radius: 50%;
    border: 2.5px solid #fff;
}

.mp-hero__status-dot--ok      { background: #22c55e; }
.mp-hero__status-dot--pending { background: #f59e0b; }

.mp-hero__info {
    flex: 1;
    min-width: 0;
}

.mp-hero__name {
    font-size: 1.45rem;
    font-weight: 800;
    color: #0c0c0c;
    letter-spacing: -0.04em;
    margin: 0 0 4px;
    line-height: 1.15;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.mp-hero__role {
    display: block;
    font-size: 0.86rem;
    color: #6b7280;
    font-weight: 500;
    margin-bottom: 10px;
}

.mp-hero__badges {
    display: flex;
    flex-wrap: wrap;
    gap: 7px;
}

.mp-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    letter-spacing: 0.01em;
    border: 1.5px solid transparent;
}

.mp-badge--green  { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.mp-badge--amber  { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.mp-badge--gray   { background: #f9fafb; border-color: #e5e7eb; color: #6b7280; }

.mp-hero__edit-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: #0c0c0c;
    color: #fff;
    border-radius: 9px;
    font-size: 0.82rem;
    font-weight: 600;
    text-decoration: none;
    transition: background .15s, transform .12s, box-shadow .15s;
    white-space: nowrap;
    flex-shrink: 0;
    align-self: flex-start;
}

.mp-hero__edit-btn:hover {
    background: #1f2937;
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(12,12,12,.18);
}

/* ── Detail card ── */
.mp-card {
    background: #fff;
    border: 1.5px solid #ebebeb;
    border-radius: 16px;
    overflow: hidden;
    margin-bottom: 18px;
}

.mp-card__header {
    padding: 18px 24px 14px;
    border-bottom: 1.5px solid #f5f5f5;
}

.mp-card__title {
    font-size: 0.95rem;
    font-weight: 700;
    color: #0c0c0c;
    letter-spacing: -0.02em;
    margin: 0 0 4px;
    display: inline-flex;
    flex-direction: column;
}

.mp-card__bar {
    display: block;
    width: 1.7rem;
    height: 2.5px;
    background: #0c0c0c;
    border-radius: 2px;
    margin-top: 6px;
}

/* ── Info rows ── */
.mp-rows {
    display: flex;
    flex-direction: column;
}

.mp-row {
    display: flex;
    align-items: center;
    gap: 16px;
    padding: 13px 24px;
    border-bottom: 1.5px solid #f5f5f5;
    transition: background .1s;
}

.mp-row:hover { background: #fafafa; }
.mp-row--last { border-bottom: none; }

.mp-row__label {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 200px;
    flex-shrink: 0;
    font-size: 0.83rem;
    font-weight: 600;
    color: #9ca3af;
    letter-spacing: 0.01em;
}

.mp-row__label svg { flex-shrink: 0; color: #d1d5db; }

.mp-row__value {
    flex: 1;
    font-size: 0.9rem;
    font-weight: 500;
    color: #111827;
}

/* ── Inline badge ── */
.mp-inline-badge {
    display: inline-flex;
    align-items: center;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: 0.78rem;
    font-weight: 600;
    border: 1.5px solid transparent;
}

.mp-inline-badge--green { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.mp-inline-badge--amber { background: #fffbeb; border-color: #fde68a; color: #92400e; }

/* ── CTA ── */
.mp-cta {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 20px 24px;
    background: #f9f9fb;
    border: 1.5px solid #ebebeb;
    border-radius: 14px;
}

.mp-cta__text {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.mp-cta__heading {
    font-size: 0.9rem;
    font-weight: 700;
    color: #0c0c0c;
    letter-spacing: -0.01em;
}

.mp-cta__sub {
    font-size: 0.82rem;
    color: #9ca3af;
}

.mp-cta__btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    background: #0c0c0c;
    color: #fff;
    border-radius: 10px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
    flex-shrink: 0;
    transition: background .15s, transform .12s, box-shadow .15s;
}

.mp-cta__btn:hover {
    background: #1f2937;
    color: #fff;
    text-decoration: none;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(12,12,12,.18);
}

/* ── Dark mode ── */
.is_dark .mp-page-title { color: #f3f4f6; }
.is_dark .mp-title-bar  { background: #f3f4f6; }
.is_dark .mp-page-sub   { color: #6b7280; }
.is_dark .mp-page-header { border-color: #1e2330; }

.is_dark .mp-hero,
.is_dark .mp-card,
.is_dark .mp-cta {
    background: #111827;
    border-color: #1e2330;
}

.is_dark .mp-hero__name     { color: #f3f4f6; }
.is_dark .mp-hero__role     { color: #6b7280; }
.is_dark .mp-hero__avatar   { border-color: #1e2330; }
.is_dark .mp-hero__status-dot { border-color: #111827; }
.is_dark .mp-hero__edit-btn { background: #f3f4f6; color: #0c0c0c; }
.is_dark .mp-hero__edit-btn:hover { background: #e5e7eb; color: #0c0c0c; }

.is_dark .mp-card__header  { border-color: #1e2330; }
.is_dark .mp-card__title   { color: #f3f4f6; }
.is_dark .mp-card__bar     { background: #f3f4f6; }

.is_dark .mp-row            { border-color: #1e2330; }
.is_dark .mp-row:hover      { background: #0f172a; }
.is_dark .mp-row__label     { color: #6b7280; }
.is_dark .mp-row__label svg { color: #374151; }
.is_dark .mp-row__value     { color: #e5e7eb; }

.is_dark .mp-cta__heading { color: #f3f4f6; }
.is_dark .mp-cta__sub     { color: #6b7280; }
.is_dark .mp-cta__btn     { background: #f3f4f6; color: #0c0c0c; }
.is_dark .mp-cta__btn:hover { background: #e5e7eb; }

/* ── Responsive ── */
@media (max-width: 680px) {
    .mp-hero { flex-wrap: wrap; }
    .mp-hero__edit-btn { align-self: auto; width: 100%; justify-content: center; }
    .mp-row__label { width: 140px; }
    .mp-cta { flex-direction: column; align-items: flex-start; }
    .mp-cta__btn { width: 100%; justify-content: center; }
    .mp-page-title { font-size: 1.65rem; }
}
</style>

@endsection
