@extends('super-admin.layout')

@section('title', 'System Settings')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
    /* System Font Stack */
    body {
        font-family: 'DM Sans', system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
    }

    /* Compact Modern Button Styles for Settings Page */
    .settings-action-button {
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(229, 231, 235, 0.6);
        cursor: pointer;
        transition: all 0.5s ease-out;
        box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.06);
        text-decoration: none;
        display: block;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 0.75rem;
        background: linear-gradient(to bottom right, rgba(249, 250, 251, 0.95), rgba(243, 244, 246, 0.95), rgba(249, 250, 251, 0.95));
        color: #1f2937;
        font-weight: 600;
        font-size: 0.875rem;
    }

    .settings-action-button:hover {
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.05);
        transform: scale(1.02) translateY(-2px);
        border-color: rgba(209, 213, 219, 0.8);
    }

    .settings-action-button:active {
        transform: scale(0.98);
    }

    /* Moving gradient layer */
    .settings-action-button::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.5), transparent);
        transform: translateX(-100%);
        transition: transform 1s ease-out;
        z-index: 1;
    }

    .settings-action-button:hover::before {
        transform: translateX(100%);
    }

    /* Overlay glow */
    .settings-action-button::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 0.75rem;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.3));
        opacity: 0;
        transition: opacity 0.5s;
        z-index: 1;
    }

    .settings-action-button:hover::after {
        opacity: 1;
    }

    /* Content wrapper */
    .settings-button-content {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }

    /* Icon styling */
    .settings-action-button i {
        font-size: 1rem;
        transition: transform 0.3s;
    }

    .settings-action-button:hover i {
        transform: scale(1.1);
    }

    /* Color variants for different button types */
    .settings-button-info {
        background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.15), rgba(96, 165, 250, 0.1));
        border: 2px solid rgba(59, 130, 246, 0.3);
        color: #1e40af;
    }

    .settings-button-info .settings-button-icon {
        background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.3), rgba(96, 165, 250, 0.2));
    }

    .settings-button-warning {
        background: linear-gradient(to bottom right, rgba(234, 179, 8, 0.15), rgba(250, 204, 21, 0.1));
        border: 2px solid rgba(234, 179, 8, 0.3);
        color: #92400e;
    }

    .settings-button-warning .settings-button-icon {
        background: linear-gradient(to bottom right, rgba(234, 179, 8, 0.3), rgba(250, 204, 21, 0.2));
    }

    .settings-button-success {
        background: linear-gradient(to bottom right, rgba(34, 197, 94, 0.15), rgba(74, 222, 128, 0.1));
        border: 2px solid rgba(34, 197, 94, 0.3);
        color: #166534;
    }

    .settings-button-success .settings-button-icon {
        background: linear-gradient(to bottom right, rgba(34, 197, 94, 0.3), rgba(74, 222, 128, 0.2));
    }

    .settings-button-secondary {
        background: linear-gradient(to bottom right, rgba(107, 114, 128, 0.15), rgba(156, 163, 175, 0.1));
        border: 2px solid rgba(107, 114, 128, 0.3);
        color: #374151;
    }

    .settings-button-secondary .settings-button-icon {
        background: linear-gradient(to bottom right, rgba(107, 114, 128, 0.3), rgba(156, 163, 175, 0.2));
    }

    .settings-button-primary {
        background: linear-gradient(to bottom right, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.1));
        border: 2px solid rgba(99, 102, 241, 0.3);
        color: #4338ca;
    }

    .settings-button-primary .settings-button-icon {
        background: linear-gradient(to bottom right, rgba(99, 102, 241, 0.3), rgba(129, 140, 248, 0.2));
    }

    .settings-button-danger {
        background: linear-gradient(to bottom right, rgba(239, 68, 68, 0.15), rgba(248, 113, 113, 0.1));
        border: 2px solid rgba(239, 68, 68, 0.3);
        color: #991b1b;
    }

    .settings-button-danger .settings-button-icon {
        background: linear-gradient(to bottom right, rgba(239, 68, 68, 0.3), rgba(248, 113, 113, 0.2));
    }

    /* Page Header Style - Hostinger Style */
    .page-header-modern {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .page-header-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .page-header-separator {
        width: 1px;
        height: 2rem;
        background-color: #d1d5db;
        margin: 0;
    }

    .page-header-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .page-header-breadcrumb i {
        font-size: 1rem;
    }

    .page-header-description {
        margin-top: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    /* Centered Container - Hostinger Style */
    .settings-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Modern Card Styling */
    .settings-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        overflow: hidden;
        transition: box-shadow 0.3s ease, transform 0.2s ease;
    }

    .settings-card:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    }

    .settings-card-header {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #1f2937;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .settings-card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.125rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .settings-card-header i {
        font-size: 1.25rem;
    }

    .settings-card-body {
        padding: 1.5rem;
    }

    /* Form Group Styling */
    .settings-form-group {
        padding: 1.25rem 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .settings-form-group:last-child {
        border-bottom: none;
    }

    .settings-form-label {
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
    }

    .settings-form-label small {
        display: block;
        font-weight: 400;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    /* Input Styling */
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.625rem 0.875rem;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    /* Reminder Day Input Boxes - Verification Code Style */
    .reminder-day-input {
        border: 2px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem;
        font-size: 1.1rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s ease;
        background: white;
    }

    .reminder-day-input:focus {
        border-color: #01b2ac;
        box-shadow: 0 0 0 3px rgba(1, 178, 172, 0.1);
        outline: none;
        transform: scale(1.05);
    }

    .reminder-day-input:hover {
        border-color: #01b2ac;
    }

    /* Danger Zone Card */
    .settings-card-danger {
        border-color: #ef4444;
    }

    .settings-card-danger .settings-card-header {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
    }

    /* Responsive */
    @media (max-width: 768px) {
        .settings-container {
            padding: 0 1rem;
        }

        .settings-card-body {
            padding: 1rem;
        }

        .settings-form-group {
            padding: 1rem 0;
        }
    }

    /* ── Premium input overrides ── */
    .form-control {
        border: 1.5px solid #e2e8f0 !important;
        border-radius: 9px !important;
        padding: 10px 14px !important;
        font-family: 'DM Sans', system-ui, sans-serif !important;
        font-size: 13.5px !important;
        color: #0f172a !important;
        background: #fff !important;
        transition: border-color .2s ease, box-shadow .2s ease !important;
        outline: none !important;
        box-shadow: none !important;
    }
    .form-control:focus {
        border-color: #01b2ac !important;
        box-shadow: 0 0 0 3px rgba(1,178,172,.1) !important;
    }
    .form-control[readonly] {
        background: #f8fafc !important;
        color: #64748b !important;
        cursor: not-allowed !important;
    }
    textarea.form-control { resize: vertical !important; }

    /* ── Premium toggle (matching subscription plans) ── */
    .sp-toggle-row {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .sp-toggle-switch {
        position: relative;
        width: 46px; height: 24px;
        flex-shrink: 0;
    }
    .sp-toggle-switch input {
        opacity: 0; width: 0; height: 0;
        position: absolute;
    }
    .sp-toggle-slider {
        position: absolute; inset: 0;
        background: #e2e8f0;
        border-radius: 24px;
        cursor: pointer;
        transition: all .25s ease;
    }
    .sp-toggle-slider::before {
        content: '';
        position: absolute;
        width: 18px; height: 18px;
        border-radius: 50%;
        background: #fff;
        top: 3px; left: 3px;
        transition: all .25s ease;
        box-shadow: 0 1px 4px rgba(0,0,0,.18);
    }
    .sp-toggle-switch input:checked + .sp-toggle-slider { background: #01b2ac; }
    .sp-toggle-switch input:checked + .sp-toggle-slider::before { transform: translateX(22px); }
    .sp-toggle-switch input:disabled + .sp-toggle-slider { opacity: .5; cursor: not-allowed; }

    .sp-status-pill {
        font-family: 'Outfit', system-ui, sans-serif;
        font-size: 11px; font-weight: 700;
        padding: 3px 10px; border-radius: 20px;
        letter-spacing: .3px; text-transform: uppercase;
        transition: all .2s;
        flex-shrink: 0;
    }
    .sp-status-pill.on  { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .sp-status-pill.off { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }

    /* ── Security card special design ── */
    .ss-card .settings-card-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        border-bottom: none;
        padding: 20px 24px;
    }
    .ss-card .settings-card-header h5 {
        color: #f1f5f9;
        font-family: 'Outfit', system-ui, sans-serif;
        font-size: 15px; font-weight: 700;
        display: flex; align-items: center; gap: 14px;
    }
    .ss-header-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(1,178,172,.18);
        display: flex; align-items: center; justify-content: center;
        color: #01b2ac; font-size: 17px; flex-shrink: 0;
    }
    .ss-header-text { display: flex; flex-direction: column; gap: 2px; }
    .ss-header-text small {
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 12px; font-weight: 400; color: #94a3b8;
    }

    .ss-card .settings-card-body { padding: 0; }

    .ss-row {
        display: flex; align-items: center;
        padding: 20px 24px; gap: 24px;
        border-bottom: 1px solid #f8fafc;
        transition: background .15s;
    }
    .ss-row:last-child { border-bottom: none; }
    .ss-row:hover { background: #fafbfc; }

    .ss-row-info { flex: 1; min-width: 0; }
    .ss-row-title {
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 14px; font-weight: 600; color: #0f172a; margin-bottom: 3px;
    }
    .ss-row-desc { font-size: 12.5px; color: #64748b; line-height: 1.5; }
    .ss-row-hint {
        display: flex; align-items: center; gap: 5px;
        font-size: 11.5px; color: #94a3b8; margin-top: 6px;
        font-style: italic;
    }

    .ss-row-control { flex-shrink: 0; display: flex; align-items: center; }

    /* Domain input */
    .ss-domain-wrap {
        display: flex; align-items: center;
        border: 1.5px solid #e2e8f0; border-radius: 10px;
        overflow: hidden; background: #fff;
        transition: border-color .2s, box-shadow .2s, opacity .2s;
        min-width: 260px;
    }
    .ss-domain-wrap:focus-within {
        border-color: #01b2ac;
        box-shadow: 0 0 0 3px rgba(1,178,172,.1);
    }
    .ss-domain-prefix {
        padding: 11px 13px;
        background: #f8fafc; color: #475569;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 14px; font-weight: 700;
        border-right: 1.5px solid #e2e8f0;
        user-select: none; letter-spacing: 0;
    }
    .ss-domain-input {
        flex: 1; padding: 11px 14px;
        border: none; outline: none;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 13.5px; color: #0f172a;
        background: transparent;
    }
    .ss-domain-input::placeholder { color: #94a3b8; }
    .ss-domain-wrap.ss-dimmed { opacity: .4; pointer-events: none; }

    @media (max-width: 768px) {
        .ss-row { flex-direction: column; align-items: flex-start; gap: 14px; }
        .ss-domain-wrap { min-width: 0; width: 100%; }
    }
</style>
@endpush

@section('content')
<div class="settings-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">System Settings</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-settings-alt"></i>
                    <span> - System Settings</span>
                </div>
            </div>
            <p class="page-header-description">Configure system-wide settings and preferences</p>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <form method="POST" action="{{ route('super-admin.settings.test-paystack') }}">
                @csrf
                <button type="submit" class="settings-action-button settings-button-info">
                    <div class="settings-button-content">
                        <i class="icofont-verification-check"></i>
                        <span>Test Paystack</span>
                    </div>
                </button>
            </form>
        </div>
        <div class="col-md-3 mb-3">
            <form method="POST" action="{{ route('super-admin.settings.clear-cache') }}">
                @csrf
                <button type="submit" class="settings-action-button settings-button-warning">
                    <div class="settings-button-content">
                        <i class="icofont-refresh"></i>
                        <span>Clear Cache</span>
                    </div>
                </button>
            </form>
        </div>
        <div class="col-md-3 mb-3">
            <form method="POST" action="{{ route('super-admin.settings.optimize') }}">
                @csrf
                <button type="submit" class="settings-action-button settings-button-success">
                    <div class="settings-button-content">
                        <i class="icofont-speed-meter"></i>
                        <span>Optimize</span>
                    </div>
                </button>
            </form>
        </div>
    </div>

    {{-- Settings Form --}}
    <form method="POST" action="{{ route('super-admin.settings.update') }}">
        @csrf

        @foreach($settings as $category => $categorySettings)

        {{-- Security category — premium dedicated card --}}
        @if($category === 'security')
        @php
            $restrictSetting = $categorySettings->firstWhere('key', 'restrict_email_domain');
            $domainSetting   = $categorySettings->firstWhere('key', 'allowed_email_domain');
            $isRestricted    = $restrictSetting && $restrictSetting->typed_value;
        @endphp
        <div class="settings-card ss-card" id="security">
            <div class="settings-card-header">
                <h5>
                    <div class="ss-header-icon"><i class="fas fa-shield-alt"></i></div>
                    <div class="ss-header-text">
                        Security
                        <small>Control who can access the system based on email domain</small>
                    </div>
                </h5>
            </div>

            <div class="settings-card-body">

                {{-- ── Toggle row ── --}}
                @if($restrictSetting)
                <div class="ss-row">
                    <div class="ss-row-info">
                        <div class="ss-row-title">{{ $restrictSetting->label }}</div>
                        <div class="ss-row-desc">{{ $restrictSetting->description }}</div>
                    </div>
                    <div class="ss-row-control" style="gap: 12px;">
                        <span id="restrict_badge" class="sp-status-pill {{ $isRestricted ? 'on' : 'off' }}">
                            {{ $isRestricted ? 'Active' : 'Inactive' }}
                        </span>
                        <label class="sp-toggle-switch" style="margin:0">
                            <input type="checkbox"
                                   id="restrict_email_domain"
                                   name="restrict_email_domain"
                                   {{ $isRestricted ? 'checked' : '' }}
                                   {{ !$restrictSetting->is_editable ? 'disabled' : '' }}>
                            <span class="sp-toggle-slider"></span>
                        </label>
                    </div>
                </div>
                @endif

                {{-- ── Domain input row ── --}}
                @if($domainSetting)
                <div class="ss-row" id="domain_row">
                    <div class="ss-row-info">
                        <div class="ss-row-title">{{ $domainSetting->label }}</div>
                        <div class="ss-row-desc">{{ $domainSetting->description }}</div>
                        <div class="ss-row-hint" id="domain_hint" style="{{ $isRestricted ? 'display:none' : '' }}">
                            <i class="fas fa-info-circle"></i>
                            Enable the restriction toggle above to activate domain filtering
                        </div>
                    </div>
                    <div class="ss-row-control">
                        <div class="ss-domain-wrap {{ $isRestricted ? '' : 'ss-dimmed' }}" id="domain_wrap">
                            <span class="ss-domain-prefix">{{ '@' }}</span>
                            <input type="text"
                                   class="ss-domain-input"
                                   id="allowed_email_domain"
                                   name="allowed_email_domain"
                                   value="{{ $domainSetting->value }}"
                                   placeholder="cug.edu.gh"
                                   {{ !$domainSetting->is_editable ? 'readonly' : '' }}>
                        </div>
                    </div>
                </div>
                @endif

            </div>
        </div>

        @else
        <div class="settings-card" id="{{ $category }}">
            <div class="settings-card-header">
                <h5>
                    <i class="icofont-ui-settings"></i> 
                    {{ ucwords(str_replace('_', ' ', $category)) }}
                </h5>
            </div>
            <div class="settings-card-body">
                @foreach($categorySettings as $setting)
                <div class="settings-form-group">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="settings-form-label">
                                {{ $setting->label }}
                                @if($setting->description)
                                <small>{{ $setting->description }}</small>
                                @endif
                            </label>
                        </div>
                        <div class="col-md-8">
                            @if($setting->data_type === 'boolean')
                                <div class="sp-toggle-row">
                                    <label class="sp-toggle-switch" style="margin:0">
                                        <input type="checkbox"
                                               id="{{ $setting->key }}"
                                               name="{{ $setting->key }}"
                                               {{ $setting->typed_value ? 'checked' : '' }}
                                               {{ !$setting->is_editable ? 'disabled' : '' }}>
                                        <span class="sp-toggle-slider"></span>
                                    </label>
                                    <span class="sp-status-pill {{ $setting->typed_value ? 'on' : 'off' }}" id="pill_{{ $setting->key }}">
                                        {{ $setting->typed_value ? 'Enabled' : 'Disabled' }}
                                    </span>
                                </div>
                            @elseif($setting->data_type === 'json')
                                @if($setting->key === 'renewal_reminder_days')
                                    @php
                                        // Get the days array - try typed_value first, then decode JSON
                                        $days = $setting->typed_value;
                                        if (!is_array($days)) {
                                            $days = json_decode($setting->value, true);
                                        }
                                        if (!is_array($days) || empty($days)) {
                                            $days = [30, 14, 7, 1];
                                        }
                                        // Ensure we have 4 values
                                        $days = array_pad($days, 4, 0);
                                        $days = array_slice($days, 0, 4);
                                        $day1 = (int) ($days[0] ?? 30);
                                        $day2 = (int) ($days[1] ?? 14);
                                        $day3 = (int) ($days[2] ?? 7);
                                        $day4 = (int) ($days[3] ?? 1);
                                    @endphp
                                    <div class="d-flex gap-2 align-items-center">
                                        <input type="number" 
                                               class="reminder-day-input" 
                                               name="renewal_reminder_days[]" 
                                               value="{{ $day1 }}"
                                               min="0"
                                               max="365"
                                               style="width: 80px;"
                                               {{ !$setting->is_editable ? 'readonly' : '' }}
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); if(this.nextElementSibling && this.value.length === 3) this.nextElementSibling.focus();">
                                        <input type="number" 
                                               class="reminder-day-input" 
                                               name="renewal_reminder_days[]" 
                                               value="{{ $day2 }}"
                                               min="0"
                                               max="365"
                                               style="width: 80px;"
                                               {{ !$setting->is_editable ? 'readonly' : '' }}
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); if(this.nextElementSibling && this.value.length === 3) this.nextElementSibling.focus();">
                                        <input type="number" 
                                               class="reminder-day-input" 
                                               name="renewal_reminder_days[]" 
                                               value="{{ $day3 }}"
                                               min="0"
                                               max="365"
                                               style="width: 80px;"
                                               {{ !$setting->is_editable ? 'readonly' : '' }}
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3); if(this.nextElementSibling && this.value.length === 3) this.nextElementSibling.focus();">
                                        <input type="number" 
                                               class="reminder-day-input" 
                                               name="renewal_reminder_days[]" 
                                               value="{{ $day4 }}"
                                               min="0"
                                               max="365"
                                               style="width: 80px;"
                                               {{ !$setting->is_editable ? 'readonly' : '' }}
                                               oninput="this.value = this.value.replace(/[^0-9]/g, '').slice(0, 3);">
                                        <span class="text-muted ms-2" style="font-size: 0.875rem;">days before expiry</span>
                                    </div>
                                @else
                                    <textarea class="form-control" 
                                              name="{{ $setting->key }}" 
                                              rows="3"
                                              {{ !$setting->is_editable ? 'readonly' : '' }}>{{ $setting->value }}</textarea>
                                @endif
                            @else
                                <input type="{{ $setting->data_type === 'integer' ? 'number' : 'text' }}" 
                                       class="form-control" 
                                       name="{{ $setting->key }}" 
                                       value="{{ $setting->value }}"
                                       {{ !$setting->is_editable ? 'readonly' : '' }}
                                       @if($setting->key === 'paystack_secret_key' || $setting->key === 'paystack_webhook_secret') type="password" @endif>
                            @endif
                            
                            @if($setting->requires_restart)
                            <small class="text-warning d-block mt-2">
                                <i class="icofont-warning"></i> Requires application restart
                            </small>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @endforeach

        <div class="text-right mb-4">
            <button type="submit" class="settings-action-button settings-button-primary" style="display: inline-block; width: auto; padding: 0.875rem 1.5rem;">
                <div class="settings-button-content">
                    <i class="icofont-save"></i>
                    <span>Save All Settings</span>
                </div>
            </button>
        </div>
    </form>

    {{-- Maintenance Mode Toggle --}}
    <div class="settings-card settings-card-danger">
        <div class="settings-card-header">
            <h5><i class="icofont-warning"></i> Danger Zone</h5>
        </div>
        <div class="settings-card-body">
            <h6>Maintenance Mode</h6>
            <p class="text-muted">When enabled, only super admins can access the system.</p>
            <form method="POST" action="{{ route('super-admin.settings.toggle-maintenance') }}" class="d-inline">
                @csrf
                @if(App\Models\SystemSetting::getMaintenanceMode())
                <button type="submit" class="settings-action-button settings-button-success" style="display: inline-block; width: auto; padding: 0.875rem 1.5rem;">
                    <div class="settings-button-content">
                        <i class="icofont-check-circled"></i>
                        <span>Disable Maintenance Mode</span>
                    </div>
                </button>
                @else
                <input type="hidden" name="enable" value="1">
                <button type="submit" class="settings-action-button settings-button-danger" onclick="return confirm('Are you sure? This will block all users except super admins.')" style="display: inline-block; width: auto; padding: 0.875rem 1.5rem;">
                    <div class="settings-button-content">
                        <i class="icofont-ban"></i>
                        <span>Enable Maintenance Mode</span>
                    </div>
                </button>
                @endif
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // ── Security toggle: sync badge + domain dimming ──
    (function () {
        var toggle = document.getElementById('restrict_email_domain');
        var badge  = document.getElementById('restrict_badge');
        var wrap   = document.getElementById('domain_wrap');
        var hint   = document.getElementById('domain_hint');

        function sync() {
            if (!toggle) return;
            var on = toggle.checked;

            if (badge) {
                badge.textContent = on ? 'Active' : 'Inactive';
                badge.className = 'sp-status-pill ' + (on ? 'on' : 'off');
            }
            if (wrap) {
                wrap.classList.toggle('ss-dimmed', !on);
            }
            if (hint) {
                hint.style.display = on ? 'none' : 'flex';
            }
        }

        if (toggle) toggle.addEventListener('change', sync);
    })();

    // ── Generic boolean toggles: live pill update ──
    document.querySelectorAll('.sp-toggle-row .sp-toggle-switch input[type="checkbox"]').forEach(function(input) {
        var pill = document.getElementById('pill_' + input.id);
        if (!pill) return;
        input.addEventListener('change', function() {
            pill.textContent  = input.checked ? 'Enabled' : 'Disabled';
            pill.className = 'sp-status-pill ' + (input.checked ? 'on' : 'off');
        });
    });

    // Smooth scroll to anchor on page load
    document.addEventListener('DOMContentLoaded', function() {
        if (window.location.hash) {
            const hash = window.location.hash.substring(1);
            const element = document.getElementById(hash);
            if (element) {
                // Small delay to ensure page is fully rendered
                setTimeout(function() {
                    element.scrollIntoView({ 
                        behavior: 'smooth', 
                        block: 'start' 
                    });
                    // Add a highlight effect
                    element.style.transition = 'box-shadow 0.3s ease';
                    element.style.boxShadow = '0 0 0 3px rgba(1, 178, 172, 0.3)';
                    setTimeout(function() {
                        element.style.boxShadow = '';
                    }, 2000);
                }, 100);
            }
        }
    });
</script>
@endpush

@endsection

