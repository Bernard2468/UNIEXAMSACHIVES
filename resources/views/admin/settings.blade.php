@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')
<div class="dashboardarea sp_bottom_100 settings-page-compact">
    <div class="container-fluid full__width__padding settings-page-hero" style="display:none">
        <div class="row">@include('components.create_section')</div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="sp-wrap">

                        {{-- ── Page header ── --}}
                        <div class="sp-page-header">
                            <h1 class="sp-page-title">Profile Settings<span class="sp-title-bar"></span></h1>
                            <p class="sp-page-sub">Manage your personal information and account security.</p>
                        </div>

                        {{-- ── Alerts ── --}}
                        @if(session('success'))
                        <div class="sp-alert sp-alert--ok">
                            <svg class="sp-alert__ico" width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ session('success') }}</span>
                            <button class="sp-alert__x" onclick="this.closest('.sp-alert').remove()">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                            </button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="sp-alert sp-alert--err">
                            <svg class="sp-alert__ico" width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                            <button class="sp-alert__x" onclick="this.closest('.sp-alert').remove()">
                                <svg width="14" height="14" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                            </button>
                        </div>
                        @endif

                        {{-- ── Tabs ── --}}
                        <div class="sp-tabs" role="tablist">
                            <button class="sp-tab sp-tab--active" data-panel="sp-profile" role="tab">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
                                Profile
                            </button>
                            <button class="sp-tab" data-panel="sp-password" role="tab">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                Password
                            </button>
                        </div>

                        {{-- ══════════════ PROFILE PANEL ══════════════ --}}
                        <div class="sp-panel sp-panel--active" id="sp-profile">
                            <form action="{{ route('dashboard.user.info') }}" method="post" enctype="multipart/form-data" id="sp-profile-form">
                                @csrf

                                {{-- Basic information --}}
                                <div class="sp-section">
                                    <div class="sp-section__hd">
                                        <h2 class="sp-section__title">Basic information<span class="sp-section__bar"></span></h2>
                                        <p class="sp-section__hint">Your name, email, and profile photo.</p>
                                    </div>

                                    {{-- Avatar row --}}
                                    <div class="sp-avatar-row">
                                        <div class="sp-avatar-shell">
                                            <div class="sp-avatar" id="sp-avatar-box">
                                                @if($data->profile_picture)
                                                    <img id="sp-avatar-img" src="{{ asset('profile_pictures/' . $data->profile_picture) }}" alt="Avatar">
                                                @else
                                                    <img id="sp-avatar-img" src="{{ asset('profile_pictures/default-profile.png') }}" alt="Avatar">
                                                @endif
                                                <label class="sp-avatar__overlay" for="sp-pic-input">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M23 19a2 2 0 01-2 2H3a2 2 0 01-2-2V8a2 2 0 012-2h4l2-3h6l2 3h4a2 2 0 012 2z"/><circle cx="12" cy="13" r="4"/></svg>
                                                    <span>Change</span>
                                                </label>
                                            </div>
                                            <input type="file" id="sp-pic-input" name="profile_picture" accept="image/jpeg,image/png,image/jpg,image/gif" onchange="spPreviewAvatar(this)">
                                        </div>
                                        <div class="sp-avatar-meta">
                                            <span class="sp-avatar-name">{{ $data->first_name }} {{ $data->last_name }}</span>
                                            <span class="sp-avatar-email">{{ $data->email }}</span>
                                            <span class="sp-avatar-note">JPEG, PNG, JPG, or GIF &middot; Max 5 MB</span>
                                        </div>
                                    </div>

                                    {{-- Name + email fields --}}
                                    <div class="sp-grid">
                                        <div class="sp-field">
                                            <label class="sp-label">First name</label>
                                            <input class="sp-input" type="text" name="first_name" placeholder="First name" value="{{ $data->first_name }}" required>
                                        </div>
                                        <div class="sp-field">
                                            <label class="sp-label">Last name</label>
                                            <input class="sp-input" type="text" name="last_name" placeholder="Last name" value="{{ $data->last_name }}" required>
                                        </div>
                                        <div class="sp-field sp-field--full">
                                            <label class="sp-label">Email address</label>
                                            <input class="sp-input" type="email" name="email" placeholder="Email address" value="{{ $data->email }}" required>
                                        </div>
                                    </div>
                                </div>

                                {{-- Organization --}}
                                <div class="sp-section">
                                    <div class="sp-section__hd">
                                        <h2 class="sp-section__title">Organization<span class="sp-section__bar"></span></h2>
                                        <p class="sp-section__hint">Department, staff category, and position — same as when you registered.</p>
                                    </div>

                                    <div class="sp-grid">
                                        <div class="sp-field sp-field--full">
                                            <label class="sp-label">Department / Faculty / Unit</label>
                                            <div class="sp-sel-wrap">
                                                <select class="sp-select" name="department_id" required>
                                                    <option value="" disabled @selected(!old('department_id', $data->department_id))>Choose department</option>
                                                    @foreach($departments as $dept)
                                                        <option value="{{ $dept->id }}" @selected((string)old('department_id', $data->department_id) === (string)$dept->id)>{{ $dept->name }}</option>
                                                    @endforeach
                                                </select>
                                                <svg class="sp-sel-arrow" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                            </div>
                                            @error('department_id')<span class="sp-err">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="sp-field">
                                            <label class="sp-label">Staff category</label>
                                            <div class="sp-sel-wrap">
                                                <select class="sp-select" name="staff_category" required>
                                                    <option value="" disabled @selected(!old('staff_category', $data->staff_category))>Choose category</option>
                                                    <option value="Junior Staff" @selected(old('staff_category', $data->staff_category) === 'Junior Staff')>Junior Staff</option>
                                                    <option value="Senior Staff" @selected(old('staff_category', $data->staff_category) === 'Senior Staff')>Senior Staff</option>
                                                    <option value="Senior Member (Non-Teaching)" @selected(old('staff_category', $data->staff_category) === 'Senior Member (Non-Teaching)')>Senior Member (Non-Teaching)</option>
                                                    <option value="Senior Member (Teaching)" @selected(old('staff_category', $data->staff_category) === 'Senior Member (Teaching)')>Senior Member (Teaching)</option>
                                                </select>
                                                <svg class="sp-sel-arrow" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                            </div>
                                            @error('staff_category')<span class="sp-err">{{ $message }}</span>@enderror
                                        </div>
                                        <div class="sp-field">
                                            <label class="sp-label">Position <span class="sp-optional">optional</span></label>
                                            <div class="sp-sel-wrap">
                                                <select class="sp-select" name="position_id">
                                                    <option value="" @selected(old('position_id', $data->position_id) === null || old('position_id', $data->position_id) === '')>No position</option>
                                                    @foreach($positions as $pos)
                                                        <option value="{{ $pos->id }}" @selected((string)old('position_id', $data->position_id) === (string)$pos->id)>{{ $pos->name }}</option>
                                                    @endforeach
                                                </select>
                                                <svg class="sp-sel-arrow" width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                            </div>
                                            @error('position_id')<span class="sp-err">{{ $message }}</span>@enderror
                                        </div>
                                    </div>

                                    <div class="sp-form-foot">
                                        <button type="submit" class="sp-btn">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Save changes
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- ══════════════ PASSWORD PANEL ══════════════ --}}
                        <div class="sp-panel" id="sp-password">

                            @if(!auth()->user()->password_changed)
                            <div class="sp-warn-banner">
                                <div class="sp-warn-banner__icon">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                </div>
                                <div>
                                    <strong class="sp-warn-banner__title">Password change required</strong>
                                    <p class="sp-warn-banner__body">You are using a temporary password. Change it now to secure your account.</p>
                                </div>
                            </div>
                            @endif

                            <div class="sp-section" style="border-bottom:none; padding-bottom:0; margin-bottom:0;">
                                <div class="sp-section__hd">
                                    <h2 class="sp-section__title">Change password<span class="sp-section__bar"></span></h2>
                                    <p class="sp-section__hint">Use a strong password of at least 8 characters.</p>
                                </div>

                                <form action="{{ route('dashboard.password.update') }}" method="POST">
                                    @csrf
                                    <div class="sp-stack">
                                        <div class="sp-field">
                                            <label class="sp-label">
                                                Current password
                                                @if(!auth()->user()->password_changed)
                                                    <span class="sp-optional">(temporary)</span>
                                                @endif
                                            </label>
                                            <div class="sp-pw-wrap">
                                                <input class="sp-input sp-input--pr" type="password" name="current_password" id="sp-pw-curr" placeholder="Enter current password" required>
                                                <button type="button" class="sp-eye" onclick="spTogglePw('sp-pw-curr', this)" title="Show/hide password">
                                                    <svg class="sp-eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg class="sp-eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                </button>
                                            </div>
                                            @error('current_password')<span class="sp-err">{{ $message }}</span>@enderror
                                        </div>

                                        <div class="sp-field">
                                            <label class="sp-label">New password</label>
                                            <div class="sp-pw-wrap">
                                                <input class="sp-input sp-input--pr" type="password" name="new_password" id="sp-pw-new" placeholder="Minimum 8 characters" required>
                                                <button type="button" class="sp-eye" onclick="spTogglePw('sp-pw-new', this)" title="Show/hide password">
                                                    <svg class="sp-eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg class="sp-eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                </button>
                                            </div>
                                            @error('new_password')<span class="sp-err">{{ $message }}</span>@enderror
                                        </div>

                                        <div class="sp-field">
                                            <label class="sp-label">Confirm new password</label>
                                            <div class="sp-pw-wrap">
                                                <input class="sp-input sp-input--pr" type="password" name="new_password_confirmation" id="sp-pw-conf" placeholder="Repeat new password" required>
                                                <button type="button" class="sp-eye" onclick="spTogglePw('sp-pw-conf', this)" title="Show/hide password">
                                                    <svg class="sp-eye-show" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                    <svg class="sp-eye-hide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="sp-form-foot">
                                            <button type="submit" class="sp-btn">
                                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                                                Update password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>{{-- /sp-wrap --}}
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ STYLES ══════════════ --}}
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

/* ─── Scope: everything inside .sp-wrap uses Outfit ─── */
.sp-wrap,
.sp-wrap * {
    font-family: 'Outfit', sans-serif !important;
    box-sizing: border-box;
}

/* hide hero */
.settings-page-compact .settings-page-hero { display: none !important; }

/* ─────────────── Wrapper ─────────────── */
.sp-wrap {
    max-width: 820px;
    padding: 4px 0 56px;
}

/* ─────────────── Page header ─────────────── */
.sp-page-header {
    margin-bottom: 30px;
    padding-bottom: 26px;
    border-bottom: 1.5px solid #ebebeb;
}

.sp-page-title {
    font-size: 2rem;
    font-weight: 800;
    color: #0c0c0c;
    letter-spacing: -0.045em;
    line-height: 1.1;
    margin: 0 0 4px;
    display: inline-flex;
    flex-direction: column;
    gap: 0;
}

/* The short decorative bar sits right below the title text */
.sp-title-bar {
    display: block;
    width: 2.4rem;
    height: 3.5px;
    background: #0c0c0c;
    border-radius: 3px;
    margin-top: 9px;
}

.sp-page-sub {
    margin: 14px 0 0;
    font-size: 0.9rem;
    color: #8a8fa0;
    font-weight: 400;
}

/* ─────────────── Alerts ─────────────── */
.sp-alert {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 13px 16px;
    border-radius: 11px;
    margin-bottom: 18px;
    font-size: 0.875rem;
    font-weight: 500;
    border: 1.5px solid transparent;
}

.sp-alert--ok  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.sp-alert--err { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }

.sp-alert__ico { flex-shrink: 0; margin-top: 1px; }

.sp-alert__x {
    margin-left: auto;
    background: none;
    border: none;
    cursor: pointer;
    opacity: .45;
    padding: 0;
    color: inherit;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    transition: opacity .15s;
}
.sp-alert__x:hover { opacity: 1; }

/* ─────────────── Tabs ─────────────── */
.sp-tabs {
    display: flex;
    gap: 2px;
    border-bottom: 1.5px solid #ebebeb;
    margin-bottom: 30px;
}

.sp-tab {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 9px 20px 11px;
    background: none;
    border: none;
    border-bottom: 2.5px solid transparent;
    margin-bottom: -1.5px;
    cursor: pointer;
    font-size: 0.9rem;
    font-weight: 500;
    color: #9ca3af;
    transition: color .15s, border-color .15s;
}

.sp-tab:first-child { padding-left: 0; }
.sp-tab:hover { color: #1a1a1a; }

.sp-tab--active {
    color: #0c0c0c;
    font-weight: 700;
    border-bottom-color: #0c0c0c;
}

.sp-tab svg { transition: opacity .15s; opacity: .6; }
.sp-tab--active svg { opacity: 1; }

/* ─────────────── Panels ─────────────── */
.sp-panel { display: none; }
.sp-panel--active { display: block; }

/* ─────────────── Section ─────────────── */
.sp-section {
    margin-bottom: 38px;
    padding-bottom: 34px;
    border-bottom: 1.5px solid #ebebeb;
}

.sp-section__hd { margin-bottom: 22px; }

.sp-section__title {
    font-size: 1.06rem;
    font-weight: 700;
    color: #0c0c0c;
    letter-spacing: -0.025em;
    margin: 0 0 4px;
    display: inline-flex;
    flex-direction: column;
    gap: 0;
}

/* Short accent bar under each section title */
.sp-section__bar {
    display: block;
    width: 1.9rem;
    height: 2.5px;
    background: #0c0c0c;
    border-radius: 2px;
    margin-top: 7px;
}

.sp-section__hint {
    margin: 10px 0 0;
    font-size: 0.86rem;
    color: #b0b5c0;
    line-height: 1.55;
}

/* ─────────────── Avatar row ─────────────── */
.sp-avatar-row {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 18px 20px;
    background: #f9f9fb;
    border: 1.5px solid #ebebeb;
    border-radius: 14px;
    margin-bottom: 26px;
}

.sp-avatar-shell { flex-shrink: 0; }

.sp-avatar {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    overflow: hidden;
    background: #e9eaec;
    cursor: pointer;
}

.sp-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    border-radius: 50%;
    transition: filter .2s;
}

.sp-avatar__overlay {
    position: absolute;
    inset: 0;
    background: rgba(12,12,12,.6);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 4px;
    border-radius: 50%;
    opacity: 0;
    cursor: pointer;
    color: #fff;
    font-size: 0.68rem;
    font-weight: 600;
    letter-spacing: .04em;
    text-transform: uppercase;
    transition: opacity .2s;
}

.sp-avatar:hover .sp-avatar__overlay { opacity: 1; }
.sp-avatar:hover img { filter: brightness(.75); }

/* hide the real file input */
#sp-pic-input { display: none; }

.sp-avatar-meta {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sp-avatar-name {
    font-size: 1rem;
    font-weight: 700;
    color: #0c0c0c;
    letter-spacing: -.02em;
}

.sp-avatar-email { font-size: 0.84rem; color: #6b7280; }

.sp-avatar-note {
    font-size: 0.77rem;
    color: #c8cbd3;
    margin-top: 4px;
}

/* ─────────────── Form grid ─────────────── */
.sp-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px 18px;
}

.sp-field--full { grid-column: 1 / -1; }

.sp-stack {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

/* ─────────────── Labels ─────────────── */
.sp-label {
    display: block;
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    margin-bottom: 7px;
    letter-spacing: .015em;
}

.sp-optional {
    font-weight: 400;
    color: #c0c4cf;
    font-size: .85em;
    margin-left: 4px;
}

/* ─────────────── Inputs ─────────────── */
.sp-input {
    display: block;
    width: 100%;
    padding: 10.5px 13px;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 400;
    color: #111827;
    transition: border-color .15s, box-shadow .15s;
    outline: none;
    appearance: none;
}

.sp-input:hover  { border-color: #cdd0d8; }
.sp-input:focus  { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.08); }
.sp-input::placeholder { color: #d4d7de; font-weight: 400; }

.sp-input--pr { padding-right: 42px; }

/* ─────────────── Select ─────────────── */
.sp-sel-wrap { position: relative; }

.sp-select {
    display: block;
    width: 100%;
    padding: 10.5px 40px 10.5px 13px;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 400;
    color: #111827;
    appearance: none;
    cursor: pointer;
    outline: none;
    transition: border-color .15s, box-shadow .15s;
}

.sp-select:hover { border-color: #cdd0d8; }
.sp-select:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.08); }

.sp-sel-arrow {
    position: absolute;
    right: 11px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
    color: #b0b5c0;
}

/* ─────────────── Password toggle ─────────────── */
.sp-pw-wrap { position: relative; }

.sp-eye {
    position: absolute;
    right: 11px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    cursor: pointer;
    color: #b0b5c0;
    padding: 0;
    display: flex;
    align-items: center;
    transition: color .15s;
    line-height: 1;
}
.sp-eye:hover { color: #374151; }

/* ─────────────── Error text ─────────────── */
.sp-err {
    display: block;
    margin-top: 5px;
    font-size: 0.8rem;
    color: #ef4444;
    font-weight: 500;
}

/* ─────────────── Form footer ─────────────── */
.sp-form-foot {
    margin-top: 22px;
    padding-top: 20px;
    border-top: 1.5px solid #ebebeb;
}

/* ─────────────── Primary button ─────────────── */
.sp-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 22px;
    background: #0c0c0c;
    color: #fff;
    border: none;
    border-radius: 10px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    letter-spacing: .01em;
    transition: background .15s, transform .12s, box-shadow .15s;
    font-family: 'Outfit', sans-serif !important;
}

.sp-btn:hover {
    background: #1f2937;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(12,12,12,.18);
}

.sp-btn:active {
    transform: translateY(0);
    box-shadow: none;
}

/* ─────────────── Warning banner ─────────────── */
.sp-warn-banner {
    display: flex;
    align-items: flex-start;
    gap: 13px;
    padding: 15px 18px;
    background: #fffbeb;
    border: 1.5px solid #fde68a;
    border-radius: 12px;
    margin-bottom: 28px;
    color: #78350f;
}

.sp-warn-banner__icon { flex-shrink: 0; margin-top: 2px; }

.sp-warn-banner__title {
    display: block;
    font-size: 0.9rem;
    font-weight: 700;
    margin-bottom: 3px;
}

.sp-warn-banner__body {
    margin: 0;
    font-size: 0.84rem;
    opacity: .85;
    line-height: 1.5;
}

/* ─────────────── Dark mode ─────────────── */
.is_dark .sp-page-title,
.is_dark .sp-page-title .sp-title-bar { color: #f3f4f6; background: #f3f4f6; }
.is_dark .sp-page-sub { color: #6b7280; }
.is_dark .sp-page-header { border-color: #1e2330; }
.is_dark .sp-tabs { border-color: #1e2330; }
.is_dark .sp-tab { color: #6b7280; }
.is_dark .sp-tab:hover { color: #f3f4f6; }
.is_dark .sp-tab--active { color: #f3f4f6; border-bottom-color: #f3f4f6; }
.is_dark .sp-section { border-color: #1e2330; }
.is_dark .sp-section__title,
.is_dark .sp-section__title .sp-section__bar { color: #f3f4f6; background: #f3f4f6; }
.is_dark .sp-section__hint { color: #6b7280; }
.is_dark .sp-avatar-row { background: #111827; border-color: #1e2330; }
.is_dark .sp-avatar-name { color: #f3f4f6; }
.is_dark .sp-avatar-email { color: #6b7280; }
.is_dark .sp-label { color: #d1d5db; }
.is_dark .sp-input,
.is_dark .sp-select {
    background: #111827;
    border-color: #2d3748;
    color: #f3f4f6;
}
.is_dark .sp-input:hover, .is_dark .sp-select:hover { border-color: #4b5563; }
.is_dark .sp-input:focus, .is_dark .sp-select:focus {
    border-color: #f3f4f6;
    box-shadow: 0 0 0 3px rgba(243,244,246,.1);
}
.is_dark .sp-form-foot { border-color: #1e2330; }
.is_dark .sp-btn { background: #f3f4f6; color: #0c0c0c; }
.is_dark .sp-btn:hover { background: #e5e7eb; }
.is_dark .sp-warn-banner { background: #1c1208; border-color: #78350f; color: #fcd34d; }

/* ─────────────── Responsive ─────────────── */
@media (max-width: 640px) {
    .sp-grid { grid-template-columns: 1fr; }
    .sp-field--full { grid-column: 1; }
    .sp-avatar-row { flex-direction: column; align-items: flex-start; gap: 14px; }
    .sp-page-title { font-size: 1.65rem; }
}
</style>

{{-- ══════════════ SCRIPTS ══════════════ --}}
<script>
// ── Tab switching
(function() {
    var tabs   = document.querySelectorAll('.sp-tab');
    var panels = document.querySelectorAll('.sp-panel');
    tabs.forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = this.dataset.panel;
            tabs.forEach(function(t) { t.classList.remove('sp-tab--active'); });
            panels.forEach(function(p) { p.classList.remove('sp-panel--active'); });
            this.classList.add('sp-tab--active');
            var el = document.getElementById(target);
            if (el) el.classList.add('sp-panel--active');
        });
    });
})();

// ── Avatar preview
function spPreviewAvatar(input) {
    if (!input.files || !input.files[0]) return;
    var file = input.files[0];
    if (file.size > 5 * 1024 * 1024) { alert('Image must be less than 5 MB.'); input.value = ''; return; }
    var ok = ['image/jpeg','image/png','image/jpg','image/gif'];
    if (!ok.includes(file.type)) { alert('Please select a JPEG, PNG, JPG, or GIF image.'); input.value = ''; return; }
    var reader = new FileReader();
    reader.onload = function(e) {
        var img = document.getElementById('sp-avatar-img');
        if (img) img.src = e.target.result;
    };
    reader.readAsDataURL(file);
}

// ── Password visibility toggle
function spTogglePw(fieldId, btn) {
    var f = document.getElementById(fieldId);
    var isHidden = f.type === 'password';
    f.type = isHidden ? 'text' : 'password';
    var showIco = btn.querySelector('.sp-eye-show');
    var hideIco = btn.querySelector('.sp-eye-hide');
    if (showIco) showIco.style.display = isHidden ? 'none' : '';
    if (hideIco) hideIco.style.display = isHidden ? '' : 'none';
}

// ── Auto-switch to password tab on validation errors
@if($errors->has('current_password') || $errors->has('new_password'))
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.querySelector('[data-panel="sp-password"]');
    if (btn) btn.click();
});
@endif
</script>

@endsection