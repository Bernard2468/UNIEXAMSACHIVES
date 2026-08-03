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
                            <button class="sp-tab" data-panel="sp-appearance" role="tab">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6L17 7M7 17l-1.4 1.4"/></svg>
                                Appearance
                            </button>
                            <button class="sp-tab" data-panel="sp-signature" role="tab">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 19l7-7 3 3-7 7-3-3z"/><path d="M18 13l-1.5-7.5L2 2l3.5 14.5L13 18l5-5z"/><path d="M2 2l7.586 7.586"/></svg>
                                Signature
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
                                            <input class="sp-input" type="email" value="{{ $data->email }}" readonly style="background:#f1f5f9; cursor:not-allowed; color:#64748b;">
                                            <small style="display:block; margin-top:4px; color:#94a3b8; font-size:0.8rem;"><img src="https://img.icons8.com/dotty/80/lock-2.png" alt="lock" width="17" height="17" style="margin-right:4px; vertical-align:-3px;">Email can only be changed by System Administrator</small>
                                        </div>
                                    </div>
                                </div>

                                {{-- Organization --}}
                                <div class="sp-section">
                                    <div class="sp-section__hd">
                                        <h2 class="sp-section__title">Organization<span class="sp-section__bar"></span></h2>
                                        <p class="sp-section__hint">Department, staff category, and position. These can only be changed by a System Administrator.</p>
                                    </div>

                                    {{-- Read-only: these fields drive Forms leadership/VC routing, so they
                                         are locked here and can only be changed by an administrator. --}}
                                    <div class="sp-grid">
                                        <div class="sp-field sp-field--full">
                                            <label class="sp-label">Primary Department / Faculty / Unit</label>
                                            <input class="sp-input" type="text" value="{{ $data->department->name ?? '—' }}" readonly style="background:#f1f5f9; cursor:not-allowed; color:#64748b;">
                                            <small style="display:block; margin-top:4px; color:#94a3b8; font-size:0.8rem;"><img src="https://img.icons8.com/dotty/80/lock-2.png" alt="lock" width="17" height="17" style="margin-right:4px; vertical-align:-3px;">Can only be changed by System Administrator</small>
                                        </div>
                                        <div class="sp-field sp-field--full">
                                            <label class="sp-label">Secondary Departments</label>
                                            <input class="sp-input" type="text" value="{{ $data->secondaryDepartments->pluck('name')->join(', ') ?: 'None' }}" readonly style="background:#f1f5f9; cursor:not-allowed; color:#64748b;">
                                            <small style="display:block; margin-top:4px; color:#94a3b8; font-size:0.8rem;"><img src="https://img.icons8.com/dotty/80/lock-2.png" alt="lock" width="17" height="17" style="margin-right:4px; vertical-align:-3px;">Additional departments you belong to. Can only be changed by System Administrator.</small>
                                        </div>
                                        <div class="sp-field">
                                            <label class="sp-label">Staff category</label>
                                            <input class="sp-input" type="text" value="{{ $data->staff_category ?? '—' }}" readonly style="background:#f1f5f9; cursor:not-allowed; color:#64748b;">
                                            <small style="display:block; margin-top:4px; color:#94a3b8; font-size:0.8rem;"><img src="https://img.icons8.com/dotty/80/lock-2.png" alt="lock" width="17" height="17" style="margin-right:4px; vertical-align:-3px;">Locked</small>
                                        </div>
                                        <div class="sp-field">
                                            <label class="sp-label">Position</label>
                                            <input class="sp-input" type="text" value="{{ $data->position->name ?? '—' }}" readonly style="background:#f1f5f9; cursor:not-allowed; color:#64748b;">
                                            <small style="display:block; margin-top:4px; color:#94a3b8; font-size:0.8rem;"><img src="https://img.icons8.com/dotty/80/lock-2.png" alt="lock" width="17" height="17" style="margin-right:4px; vertical-align:-3px;">Locked</small>
                                        </div>
                                        <div class="sp-field sp-field--full">
                                            <label class="sp-label"><i class="fas fa-id-badge" style="margin-right:4px;"></i> Account type</label>
                                            <input class="sp-input" type="text" value="{{ $data->isOfficeAccount() ? 'Institutional Office Account' : 'Individual Staff Account' }}" readonly style="background:#f1f5f9; cursor:not-allowed; color:#64748b;">
                                            <small style="display:block; margin-top:4px; color:#94a3b8; font-size:0.8rem;"><img src="https://img.icons8.com/dotty/80/lock-2.png" alt="lock" width="17" height="17" style="margin-right:4px; vertical-align:-3px;">{{ $data->isOfficeAccount() ? 'This account represents an institutional office.' : 'This is a personal staff account.' }} Can only be changed by a System Administrator.</small>
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

                        {{-- ══════════════ APPEARANCE PANEL ══════════════ --}}
                        <div class="sp-panel" id="sp-appearance">
                            <div class="sp-section" style="border-bottom:none; padding-bottom:0; margin-bottom:0;">
                                <div class="sp-section__hd">
                                    <h2 class="sp-section__title">Text &amp; display size<span class="sp-section__bar"></span></h2>
                                    <p class="sp-section__hint">Make everything easier to read. This resizes all text and interface elements across the whole system and is saved to your account — it follows you on this device automatically.</p>
                                </div>

                                {{-- Segmented A·A·A size selector --}}
                                <div class="fs-track" id="fs-track" role="radiogroup" aria-label="Text and display size">
                                    <span class="fs-indicator" id="fs-indicator"></span>
                                    <button type="button" class="fs-step" role="radio" data-scale="0.9"  aria-label="Small">
                                        <span class="fs-a" style="font-size:13px">A</span><span class="fs-lbl">Small</span>
                                    </button>
                                    <button type="button" class="fs-step fs-step--active" role="radio" data-scale="1" aria-label="Default">
                                        <span class="fs-a" style="font-size:16px">A</span><span class="fs-lbl">Default</span>
                                    </button>
                                    <button type="button" class="fs-step" role="radio" data-scale="1.1" aria-label="Large">
                                        <span class="fs-a" style="font-size:19px">A</span><span class="fs-lbl">Large</span>
                                    </button>
                                    <button type="button" class="fs-step" role="radio" data-scale="1.2" aria-label="Larger">
                                        <span class="fs-a" style="font-size:22px">A</span><span class="fs-lbl">Larger</span>
                                    </button>
                                    <button type="button" class="fs-step" role="radio" data-scale="1.35" aria-label="Largest">
                                        <span class="fs-a" style="font-size:26px">A</span><span class="fs-lbl">Largest</span>
                                    </button>
                                </div>

                                {{-- Live preview --}}
                                <div class="fs-preview">
                                    <span class="fs-preview__eyebrow">Live preview</span>
                                    <h3 class="fs-preview__title">Reading comfortably matters</h3>
                                    <p class="fs-preview__body">This is how text will look everywhere in the University Digital Transformation Suite — memos, forms, dashboards, and tables. Pick the size that feels right; the whole system updates instantly.</p>
                                    <div class="fs-preview__row">
                                        <button type="button" class="sp-btn" onclick="return false;">
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Sample button
                                        </button>
                                        <span class="fs-preview__pill">Sample label</span>
                                    </div>
                                </div>

                                <div class="fs-foot">
                                    <span class="fs-status" id="fs-status" aria-live="polite">
                                        <span class="fs-status__ico">
                                            <svg class="fs-status__spin" width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-dasharray="42" stroke-dashoffset="14"/>
                                            </svg>
                                            <svg class="fs-status__check" width="17" height="17" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                                <circle class="fs-check-ring" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2.4"/>
                                                <path class="fs-check-tick" d="M7.2 12.5l3.2 3.2L16.9 8.9" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"/>
                                            </svg>
                                        </span>
                                        <span class="fs-status__txt">Saved to your account</span>
                                    </span>
                                    <button type="button" class="fs-reset" id="fs-reset">Reset to default</button>
                                </div>
                            </div>
                        </div>

                        {{-- ══════════════ SIGNATURE PANEL ══════════════ --}}
                        <div class="sp-panel" id="sp-signature">
                            <div class="sp-section" style="border-bottom:none; padding-bottom:0; margin-bottom:0;">
                                <div class="sp-section__hd">
                                    <h2 class="sp-section__title">My signature<span class="sp-section__bar"></span></h2>
                                    <p class="sp-section__hint">Draw or upload your signature once — it is offered as "my saved signature" whenever you sign a form or minute a memo, and it appears on official PDFs exactly as saved here.</p>
                                </div>

                                @php $spSavedSig = auth()->user()->savedSignature; @endphp

                                @if($spSavedSig && $spSavedSig->image_url)
                                    <div class="sig-current">
                                        <span class="sig-current__label">Current saved signature</span>
                                        <div class="sig-current__row">
                                            <div class="sig-current__box"><img src="{{ $spSavedSig->image_url }}" alt="Saved signature"></div>
                                            <form action="{{ route('admin.forms.my-signature.destroy') }}" method="POST"
                                                  onsubmit="return confirm('Remove your saved signature? You will have to draw or upload a new one the next time you sign or minute.');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="sig-remove">Remove</button>
                                            </form>
                                        </div>
                                    </div>
                                @else
                                    <div class="sig-empty">No signature saved yet — add one below.</div>
                                @endif

                                <form action="{{ route('admin.forms.my-signature.update') }}" method="POST" id="sig-set-form">
                                    @csrf
                                    <input type="hidden" name="signature_data" id="sig-set-data" value="">

                                    <div class="sig-cap">
                                        <div class="sig-cap__tabs">
                                            <button type="button" class="sig-cap__tab is-active" data-sigset-tab="draw">Draw</button>
                                            <button type="button" class="sig-cap__tab" data-sigset-tab="upload">Upload</button>
                                        </div>

                                        <div data-sigset-panel="draw">
                                            <div class="sig-cap__pad">
                                                <canvas id="sig-set-canvas" width="640" height="200"></canvas>
                                            </div>
                                            <div class="sig-cap__row">
                                                <button type="button" class="sig-clear" id="sig-set-clear">Clear</button>
                                                <span class="sig-cap__hint">Sign above with your mouse, stylus or finger</span>
                                            </div>
                                        </div>

                                        <div data-sigset-panel="upload" hidden>
                                            <label class="sig-upload" for="sig-set-file">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                                                <span>Choose an image of your signature</span>
                                            </label>
                                            <input type="file" id="sig-set-file" accept="image/png,image/jpeg,image/jpg" hidden>
                                            <div class="sig-cap__pad" id="sig-set-upreview" hidden>
                                                <img id="sig-set-upimg" alt="Signature preview" style="max-width:100%; max-height:180px; display:block; margin:0 auto;">
                                            </div>
                                            <p class="sig-cap__hint" style="display:block; margin-top:8px;">PNG or JPG — ideally your signature in dark ink on a plain white background.</p>
                                        </div>
                                    </div>

                                    <div class="sp-form-foot">
                                        <button type="submit" class="sp-btn" id="sig-set-save" disabled>
                                            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            {{ ($spSavedSig && $spSavedSig->image_url) ? 'Replace saved signature' : 'Save signature' }}
                                        </button>
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

/* Font Awesome icons must keep their own font-family — the universal Outfit
   override above otherwise turns every icon into a tofu box (▯). */
.sp-wrap .fa,
.sp-wrap .fas,
.sp-wrap .far,
.sp-wrap .fal { font-family: 'Font Awesome 5 Free' !important; }
.sp-wrap .fab { font-family: 'Font Awesome 5 Brands' !important; }

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

/* ═════════════ Appearance: text size selector ═════════════ */
.fs-track {
    position: relative;
    display: flex;
    gap: 0;
    padding: 5px;
    background: #f4f5f7;
    border: 1.5px solid #ebebeb;
    border-radius: 16px;
    margin-bottom: 24px;
    user-select: none;
}

/* Sliding pill — width is exactly one of 5 equal slots, so translateX(i*100%)
   lands perfectly on each step without any JS measurement. */
.fs-indicator {
    position: absolute;
    top: 5px;
    bottom: 5px;
    left: 5px;
    width: calc((100% - 10px) / 5);
    background: #0c0c0c;
    border-radius: 12px;
    box-shadow: 0 4px 14px rgba(12,12,12,.22);
    transition: transform .34s cubic-bezier(.34,1.56,.64,1);
    z-index: 0;
}

.fs-step {
    position: relative;
    z-index: 1;
    flex: 1 1 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 5px;
    height: 74px;
    background: none;
    border: none;
    cursor: pointer;
    color: #7c828f;
    transition: color .2s;
    padding: 0;
}

.fs-step .fs-a {
    font-weight: 800;
    line-height: 1;
    letter-spacing: -.02em;
    transition: transform .2s;
}

.fs-step .fs-lbl {
    font-size: 0.72rem;
    font-weight: 600;
    letter-spacing: .01em;
}

.fs-step:hover { color: #0c0c0c; }
.fs-step:hover .fs-a { transform: translateY(-1px); }

.fs-step--active { color: #fff; }
.fs-step--active:hover { color: #fff; }

/* ─────────────── Live preview card ─────────────── */
.fs-preview {
    background: #f9f9fb;
    border: 1.5px solid #ebebeb;
    border-radius: 16px;
    padding: 22px 24px 24px;
    margin-bottom: 20px;
}

.fs-preview__eyebrow {
    display: inline-block;
    font-size: 0.68rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #a3a8b4;
    margin-bottom: 10px;
}

.fs-preview__title {
    margin: 0 0 8px;
    font-size: 1.15rem;
    font-weight: 700;
    color: #0c0c0c;
    letter-spacing: -.02em;
}

.fs-preview__body {
    margin: 0 0 18px;
    font-size: 0.9rem;
    line-height: 1.6;
    color: #5b616e;
}

.fs-preview__row {
    display: flex;
    align-items: center;
    gap: 14px;
    flex-wrap: wrap;
}

.fs-preview__pill {
    display: inline-flex;
    align-items: center;
    padding: 6px 14px;
    background: #fff;
    border: 1.5px solid #e5e7eb;
    border-radius: 999px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
}

/* ─────────────── Appearance footer ─────────────── */
.fs-foot {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 14px;
    padding-top: 18px;
    border-top: 1.5px solid #ebebeb;
    flex-wrap: wrap;
}

.fs-status {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 0.82rem;
    font-weight: 600;
    color: #16a34a;            /* idle / saved = green */
    transition: color .25s ease;
}
.fs-status.is-saving { color: #a3a8b4; }   /* muted grey while working */
.fs-status.is-warn   { color: #d97706; }   /* amber = saved locally only */

/* Icon follows the state colour (currentColor); the label stays black. */
.fs-status__txt { color: #0c0c0c; }

/* Icon stage — spinner and check are stacked; we cross-fade between them. */
.fs-status__ico {
    position: relative;
    width: 17px;
    height: 17px;
    flex-shrink: 0;
}
.fs-status__spin,
.fs-status__check {
    position: absolute;
    inset: 0;
}

/* Spinner: opacity-only cross-fade so nothing competes with the rotation
   transform (a `scale` here would blend to a matrix and kill the spin). */
.fs-status__spin {
    opacity: 0;
    transform-origin: center;
    animation: fs-spin .7s linear infinite;
    transition: opacity .2s ease;
}
.fs-status.is-saving .fs-status__spin  { opacity: 1; }

/* Check cross-fades out (scale is fine — it isn't rotating) */
.fs-status__check { opacity: 1; transform: scale(1); transition: opacity .2s ease, transform .2s ease; }
.fs-status.is-saving .fs-status__check { opacity: 0; transform: scale(.6); }

/* Check: the resting/confirmed state */
.fs-check-ring {
    transform-box: fill-box;
    transform-origin: center;
}
.fs-check-tick {
    stroke-dasharray: 22;
    stroke-dashoffset: 0;      /* drawn by default (idle) */
}

/* On a fresh save the ring pops and the tick draws itself in.
   JS re-adds .is-saved (with a reflow) so this replays every time. */
.fs-status.is-saved .fs-check-ring,
.fs-status.is-warn  .fs-check-ring { animation: fs-ring-pop .38s cubic-bezier(.34,1.56,.64,1) both; }
.fs-status.is-saved .fs-check-tick,
.fs-status.is-warn  .fs-check-tick { animation: fs-tick-draw .32s .13s ease both; }

@keyframes fs-spin { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }
@keyframes fs-ring-pop {
    0%   { transform: scale(.3); opacity: 0; }
    60%  { transform: scale(1.12); opacity: 1; }
    100% { transform: scale(1); opacity: 1; }
}
@keyframes fs-tick-draw {
    from { stroke-dashoffset: 22; }
    to   { stroke-dashoffset: 0; }
}

@media (prefers-reduced-motion: reduce) {
    .fs-status__spin { animation-duration: 1.2s; }
    .fs-status.is-saved .fs-check-ring,
    .fs-status.is-warn  .fs-check-ring,
    .fs-status.is-saved .fs-check-tick,
    .fs-status.is-warn  .fs-check-tick { animation: none; }
}

.fs-reset {
    background: none;
    border: none;
    cursor: pointer;
    font-size: 0.84rem;
    font-weight: 600;
    color: #6b7280;
    text-decoration: underline;
    text-underline-offset: 3px;
    padding: 4px 2px;
    transition: color .15s;
}
.fs-reset:hover { color: #0c0c0c; }

/* ─────────────── Responsive ─────────────── */
@media (max-width: 640px) {
    .sp-grid { grid-template-columns: 1fr; }
    .sp-field--full { grid-column: 1; }
    .sp-avatar-row { flex-direction: column; align-items: flex-start; gap: 14px; }
    .sp-page-title { font-size: 1.65rem; }
    .fs-step { height: 66px; }
    .fs-step .fs-lbl { font-size: 0.66rem; }
}

/* ═════════════ Signature panel ═════════════ */
.sig-current { margin-bottom: 20px; }
.sig-current__label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 8px; }
.sig-current__row { display: flex; align-items: center; gap: 14px; flex-wrap: wrap; }
.sig-current__box { width: 220px; height: 84px; background: #fff; border: 1.5px dashed #d4d7de; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 6px; }
.sig-current__box img { max-width: 100%; max-height: 100%; }
.sig-remove { background: #fff; border: 1.5px solid #fecaca; color: #b91c1c; font-size: 0.8rem; font-weight: 600; padding: 8px 14px; border-radius: 9px; cursor: pointer; transition: all .15s; }
.sig-remove:hover { background: #fef2f2; }
.sig-empty { font-size: 0.85rem; color: #8a8fa0; margin-bottom: 18px; padding: 12px 14px; background: #fafafa; border: 1.5px dashed #e5e7eb; border-radius: 10px; }

.sig-cap { background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 12px; padding: 14px; }
.sig-cap__tabs { display: inline-flex; background: #fff; border: 1.5px solid #ebebeb; border-radius: 10px; padding: 4px; gap: 2px; margin-bottom: 12px; }
.sig-cap__tab { padding: 7px 16px; background: transparent; border: none; border-radius: 7px; color: #6b7280; font-size: 0.82rem; font-weight: 600; cursor: pointer; transition: all .15s; }
.sig-cap__tab.is-active { background: #0c0c0c; color: #fff; }
.sig-cap__pad { background: #fff; border: 2px dashed #d4d7de; border-radius: 10px; padding: 8px; }
.sig-cap__pad canvas { width: 100%; height: 180px; touch-action: none; cursor: crosshair; background: #fff; border-radius: 6px; display: block; }
.sig-cap__row { display: flex; justify-content: space-between; align-items: center; margin-top: 8px; padding: 0 4px; }
.sig-clear { background: none; border: none; color: #0c0c0c; font-weight: 600; cursor: pointer; padding: 4px 10px; font-size: 0.82rem; border-radius: 6px; }
.sig-clear:hover { background: #f3f4f6; }
.sig-cap__hint { color: #b0b5c0; font-size: 0.74rem; font-style: italic; }
.sig-upload { display: flex; align-items: center; justify-content: center; gap: 10px; padding: 22px 14px; background: #fff; border: 2px dashed #d4d7de; border-radius: 10px; color: #374151; font-size: 0.85rem; font-weight: 500; cursor: pointer; transition: border-color .15s; margin-bottom: 10px; }
.sig-upload:hover { border-color: #0c0c0c; }
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

// ── Signature: draw / upload / save
(function () {
    var form = document.getElementById('sig-set-form');
    if (!form) return;

    var dataInput = document.getElementById('sig-set-data');
    var saveBtn   = document.getElementById('sig-set-save');
    var canvas    = document.getElementById('sig-set-canvas');
    var ctx       = canvas.getContext('2d');
    var clearBtn  = document.getElementById('sig-set-clear');
    var fileInput = document.getElementById('sig-set-file');
    var upPreview = document.getElementById('sig-set-upreview');
    var upImg     = document.getElementById('sig-set-upimg');

    var drawing = false, last = null, drawn = false;

    function syncState() { saveBtn.disabled = !dataInput.value; }

    function resizeCanvas() {
        var ratio = Math.max(window.devicePixelRatio || 1, 1);
        var rect  = canvas.getBoundingClientRect();
        if (rect.width === 0) return; // panel hidden — retried when tab opens
        canvas.width  = rect.width * ratio;
        canvas.height = rect.height * ratio;
        ctx.scale(ratio, ratio);
        ctx.lineWidth = 2; ctx.lineCap = 'round'; ctx.strokeStyle = '#111827';
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        drawn = false;
        if (currentTab() === 'draw') { dataInput.value = ''; syncState(); }
    }

    function pos(e) {
        var rect = canvas.getBoundingClientRect();
        return {
            x: (e.touches ? e.touches[0].clientX : e.clientX) - rect.left,
            y: (e.touches ? e.touches[0].clientY : e.clientY) - rect.top
        };
    }
    canvas.addEventListener('mousedown',  function (e) { e.preventDefault(); drawing = true; last = pos(e); });
    canvas.addEventListener('touchstart', function (e) { e.preventDefault(); drawing = true; last = pos(e); }, { passive: false });
    function move(e) {
        if (!drawing) return;
        e.preventDefault();
        var cur = pos(e);
        ctx.beginPath(); ctx.moveTo(last.x, last.y); ctx.lineTo(cur.x, cur.y); ctx.stroke();
        last = cur; drawn = true;
        dataInput.value = canvas.toDataURL('image/png');
        syncState();
    }
    canvas.addEventListener('mousemove', move);
    canvas.addEventListener('touchmove', move, { passive: false });
    ['mouseup', 'mouseleave', 'touchend'].forEach(function (ev) {
        canvas.addEventListener(ev, function () { drawing = false; });
    });

    clearBtn.addEventListener('click', function () {
        ctx.fillStyle = '#fff';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        drawn = false; dataInput.value = ''; syncState();
    });

    // Upload: normalise any image onto a white 640×200 canvas so the stored
    // signature has the same shape as drawn/typed ones on forms and PDFs.
    fileInput.addEventListener('change', function () {
        var f = this.files && this.files[0];
        if (!f) return;
        if (f.size > 4 * 1024 * 1024) { alert('Signature image must be less than 4 MB.'); this.value = ''; return; }
        var reader = new FileReader();
        reader.onload = function (e) {
            var img = new Image();
            img.onload = function () {
                var nc = document.createElement('canvas');
                nc.width = 640; nc.height = 200;
                var nctx = nc.getContext('2d');
                nctx.fillStyle = '#fff';
                nctx.fillRect(0, 0, 640, 200);
                var scale = Math.min(640 / img.width, 200 / img.height);
                var w = img.width * scale, h = img.height * scale;
                nctx.drawImage(img, (640 - w) / 2, (200 - h) / 2, w, h);
                dataInput.value = nc.toDataURL('image/png');
                upImg.src = dataInput.value;
                upPreview.hidden = false;
                syncState();
            };
            img.src = e.target.result;
        };
        reader.readAsDataURL(f);
    });

    // Draw ↔ Upload switch (resets the pending capture to avoid ambiguity).
    var capTabs = form.querySelectorAll('[data-sigset-tab]');
    var capPanels = form.querySelectorAll('[data-sigset-panel]');
    function currentTab() {
        var t = form.querySelector('.sig-cap__tab.is-active');
        return t ? t.dataset.sigsetTab : 'draw';
    }
    capTabs.forEach(function (tab) {
        tab.addEventListener('click', function () {
            capTabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); });
            capPanels.forEach(function (p) { p.hidden = p.dataset.sigsetPanel !== tab.dataset.sigsetTab; });
            dataInput.value = ''; fileInput.value = ''; upPreview.hidden = true;
            syncState();
            if (tab.dataset.sigsetTab === 'draw') setTimeout(resizeCanvas, 30);
        });
    });

    // The canvas is zero-width until the Signature tab is shown.
    var sigMainTab = document.querySelector('.sp-tab[data-panel="sp-signature"]');
    if (sigMainTab) sigMainTab.addEventListener('click', function () { setTimeout(resizeCanvas, 30); });
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();
})();

// ── Reopen the Signature tab after a signature save/remove round-trip
@if(in_array(session('success'), ['Signature saved.', 'Saved signature removed.'], true))
document.addEventListener('DOMContentLoaded', function () {
    var t = document.querySelector('.sp-tab[data-panel="sp-signature"]');
    if (t) t.click();
});
@endif

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

// ── Appearance: text / display size selector
(function () {
    var track = document.getElementById('fs-track');
    if (!track) return;
    var KEY       = 'udts_font_scale';
    var steps     = [].slice.call(track.querySelectorAll('.fs-step'));
    var indicator = document.getElementById('fs-indicator');
    var status    = document.getElementById('fs-status');
    var resetBtn  = document.getElementById('fs-reset');
    var statusTxt = status ? status.querySelector('.fs-status__txt') : null;
    var scales    = steps.map(function (b) { return parseFloat(b.dataset.scale); });
    var saveUrl   = '{{ route('dashboard.settings.font-scale') }}';
    var csrf      = '{{ csrf_token() }}';
    var saveTimer = null;

    // state: 'saving' → spinner rotates; 'saved'/'warn' → ring pops + tick draws
    function setStatus(state, text) {
        if (!status) return;
        if (statusTxt) statusTxt.textContent = text;
        status.classList.remove('is-saving', 'is-saved', 'is-warn');
        void status.offsetWidth;              // reflow so the tick draw replays
        status.classList.add('is-' + state);
    }

    function nearestIndex(v) {
        var best = 0, bd = Infinity;
        scales.forEach(function (s, i) {
            var d = Math.abs(s - v);
            if (d < bd) { bd = d; best = i; }
        });
        return best;
    }

    function paint(i) {
        if (indicator) indicator.style.transform = 'translateX(calc(' + i + ' * 100%))';
        steps.forEach(function (b, j) { b.classList.toggle('fs-step--active', j === i); });
    }

    function applyZoom(scale) {
        try {
            document.documentElement.style.zoom = (scale === 1) ? '' : scale;
        } catch (e) {}
        try { localStorage.setItem(KEY, String(scale)); } catch (e) {}
        window.__udtsFontScale = scale;
    }

    function save(scale) {
        setStatus('saving', 'Saving…');
        clearTimeout(saveTimer);
        saveTimer = setTimeout(function () {
            fetch(saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({ scale: scale })
            })
            .then(function (r) { if (!r.ok) throw new Error('bad status'); return r.json(); })
            .then(function () { setStatus('saved', 'Saved to your account'); })
            .catch(function () { setStatus('warn', 'Saved on this device'); });
        }, 250);
    }

    function select(i, doSave) {
        paint(i);
        applyZoom(scales[i]);
        if (doSave) save(scales[i]);
    }

    // Initialise from whatever the page loaded with.
    select(nearestIndex(window.__udtsFontScale || 1), false);

    steps.forEach(function (b, i) {
        b.addEventListener('click', function () { select(i, true); });
    });
    if (resetBtn) {
        resetBtn.addEventListener('click', function () { select(nearestIndex(1), true); });
    }
})();

// ── Auto-switch to password tab on validation errors
@if($errors->has('current_password') || $errors->has('new_password'))
document.addEventListener('DOMContentLoaded', function() {
    var btn = document.querySelector('[data-panel="sp-password"]');
    if (btn) btn.click();
});
@endif
</script>

@endsection