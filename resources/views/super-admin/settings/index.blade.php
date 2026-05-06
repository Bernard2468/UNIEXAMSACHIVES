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
        font-family: 'DM Sans', system-ui, sans-serif;
        font-weight: 600;
        font-size: 14px;
        line-height: 1.4;
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

    /* ── Page Header ── */
    .page-header-modern {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 6px;
    }

    .page-header-title {
        font-family: 'Outfit', system-ui, sans-serif;
        font-size: 22px;
        font-weight: 800;
        color: #0f172a;
        margin: 0;
        letter-spacing: -0.5px;
        line-height: 1.2;
    }

    .page-header-separator {
        width: 1px;
        height: 22px;
        background-color: #d1d5db;
        margin: 0;
        flex-shrink: 0;
    }

    .page-header-breadcrumb {
        display: flex;
        align-items: center;
        gap: 6px;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 13px;
        font-weight: 400;
        color: #64748b;
        margin: 0;
    }

    .page-header-breadcrumb i {
        font-size: 13px;
    }

    .page-header-description {
        margin-top: 6px;
        color: #64748b;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 14px;
        font-weight: 400;
        line-height: 1.5;
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
        color: #1e293b;
        padding: 18px 24px;
        border-bottom: 1px solid #e5e7eb;
    }

    .settings-card-header h5 {
        margin: 0;
        font-family: 'Outfit', system-ui, sans-serif;
        font-weight: 700;
        font-size: 15px;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 10px;
        letter-spacing: -0.1px;
    }

    .settings-card-header i {
        font-size: 15px;
        color: #64748b;
    }

    .settings-card-body {
        padding: 8px 24px 16px;
    }

    /* ── Form Group ── */
    .settings-form-group {
        padding: 18px 0;
        border-bottom: 1px solid #f3f4f6;
    }

    .settings-form-group:last-child {
        border-bottom: none;
    }

    .settings-form-label {
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 14px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 6px;
        line-height: 1.4;
    }

    .settings-form-label small {
        display: block;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 13px;
        font-weight: 400;
        color: #64748b;
        margin-top: 3px;
        line-height: 1.5;
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
        border: 1.5px solid #e2e8f0;
        border-radius: 9px;
        padding: 10px;
        font-family: 'Outfit', system-ui, sans-serif;
        font-size: 15px;
        font-weight: 700;
        text-align: center;
        color: #0f172a;
        transition: all 0.2s ease;
        background: white;
        line-height: 1.2;
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
        font-size: 14px !important;
        font-weight: 400 !important;
        color: #0f172a !important;
        background: #fff !important;
        transition: border-color .2s ease, box-shadow .2s ease !important;
        outline: none !important;
        box-shadow: none !important;
        line-height: 1.5 !important;
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
        letter-spacing: -0.1px;
    }
    .ss-header-icon {
        width: 40px; height: 40px; border-radius: 10px;
        background: rgba(1,178,172,.18);
        display: flex; align-items: center; justify-content: center;
        color: #01b2ac; font-size: 16px; flex-shrink: 0;
    }
    .ss-header-text { display: flex; flex-direction: column; gap: 3px; }
    .ss-header-text small {
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 13px; font-weight: 400; color: #94a3b8;
        line-height: 1.4;
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
        font-size: 14px; font-weight: 600; color: #1e293b; margin-bottom: 4px;
        line-height: 1.4;
    }
    .ss-row-desc {
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 13px; font-weight: 400; color: #64748b; line-height: 1.55;
    }
    .ss-row-hint {
        display: flex; align-items: center; gap: 5px;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 12px; font-weight: 400; color: #94a3b8; margin-top: 6px;
        font-style: italic; line-height: 1.4;
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
        font-size: 14px; font-weight: 600;
        border-right: 1.5px solid #e2e8f0;
        user-select: none; letter-spacing: 0; line-height: 1.4;
    }
    .ss-domain-input {
        flex: 1; padding: 11px 14px;
        border: none; outline: none;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 14px; font-weight: 400; color: #0f172a;
        background: transparent; line-height: 1.4;
    }
    .ss-domain-input::placeholder { color: #94a3b8; font-weight: 400; }
    .ss-domain-wrap.ss-dimmed { opacity: .4; pointer-events: none; }

    @media (max-width: 768px) {
        .ss-row { flex-direction: column; align-items: flex-start; gap: 14px; }
        .ss-domain-wrap { min-width: 0; width: 100%; }
    }

    /* ── Auto-save toast ── */
    .autosave-toast {
        position: fixed;
        bottom: 24px;
        right: 24px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 18px;
        border-radius: 12px;
        font-family: 'DM Sans', system-ui, sans-serif;
        font-size: 13.5px;
        font-weight: 600;
        color: #fff;
        box-shadow: 0 12px 32px rgba(15, 23, 42, .2);
        opacity: 0;
        transform: translateY(12px);
        pointer-events: none;
        transition: opacity .25s ease, transform .25s ease, background-color .2s ease;
        z-index: 9999;
        min-width: 180px;
    }
    .autosave-toast.visible { opacity: 1; transform: translateY(0); }
    .autosave-toast.saving  { background: #475569; }
    .autosave-toast.saved   { background: #059669; }
    .autosave-toast.error   { background: #dc2626; }
    .autosave-toast i { font-size: 14px; }

    /* Subtle saving pulse on the row currently saving */
    .ss-row.saving::after,
    .settings-form-group.saving::after {
        content: '';
        position: absolute;
        inset: 0;
        background: rgba(1, 178, 172, .04);
        pointer-events: none;
        animation: ss-pulse 1.2s ease-in-out infinite;
    }
    .ss-row, .settings-form-group { position: relative; }
    @keyframes ss-pulse {
        0%, 100% { opacity: 0; }
        50%      { opacity: 1; }
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
                                        <span style="font-family:'DM Sans',system-ui,sans-serif; font-size:13px; font-weight:400; color:#64748b; margin-left:8px;">days before expiry</span>
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
                            <div style="font-family:'DM Sans',system-ui,sans-serif; font-size:12px; font-weight:500; color:#d97706; margin-top:6px; display:flex; align-items:center; gap:5px;">
                                <i class="icofont-warning" style="font-size:12px;"></i> Requires application restart
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        @endforeach

    </form>

    {{-- Maintenance Mode Toggle --}}
    <div class="settings-card settings-card-danger">
        <div class="settings-card-header">
            <h5><i class="icofont-warning"></i> Danger Zone</h5>
        </div>
        <div class="settings-card-body">
            <h6 style="font-family:'Outfit',system-ui,sans-serif; font-size:15px; font-weight:700; color:#fff; margin:0 0 6px;">Maintenance Mode</h6>
            <p style="font-family:'DM Sans',system-ui,sans-serif; font-size:13px; font-weight:400; color:rgba(255,255,255,0.75); margin:0 0 18px; line-height:1.55;">When enabled, only super admins can access the system. All other users will see a maintenance page.</p>
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

{{-- Auto-save status toast --}}
<div id="autosaveToast" class="autosave-toast" role="status" aria-live="polite">
    <i class="fas fa-circle-notch fa-spin"></i>
    <span id="autosaveToastText">Saving…</span>
</div>

@push('scripts')
<script>
    // ════════════════════════════════════════════════════════════════
    //  AUTO-SAVE ENGINE
    //  Saves single settings via AJAX. Debounces text inputs (700ms),
    //  toggles save instantly. Shows a small toast for feedback.
    // ════════════════════════════════════════════════════════════════
    (function () {
        const ENDPOINT = "{{ route('super-admin.settings.update-single') }}";
        const csrfToken = document.querySelector('input[name="_token"]')?.value;

        const toast     = document.getElementById('autosaveToast');
        const toastText = document.getElementById('autosaveToastText');
        let toastTimer  = null;

        function showToast(state, message) {
            if (!toast) return;
            toast.classList.remove('saving', 'saved', 'error');
            toast.classList.add(state, 'visible');
            toastText.textContent = message;

            const icon = toast.querySelector('i');
            if (state === 'saving') icon.className = 'fas fa-circle-notch fa-spin';
            else if (state === 'saved') icon.className = 'fas fa-check-circle';
            else icon.className = 'fas fa-exclamation-circle';

            clearTimeout(toastTimer);
            if (state !== 'saving') {
                toastTimer = setTimeout(() => toast.classList.remove('visible'), 1800);
            }
        }

        function markRowSaving(input, on) {
            const row = input.closest('.ss-row, .settings-form-group');
            if (row) row.classList.toggle('saving', on);
        }

        function saveSetting(key, value, inputForUiHook) {
            if (!csrfToken) return;
            showToast('saving', 'Saving…');
            if (inputForUiHook) markRowSaving(inputForUiHook, true);

            return fetch(ENDPOINT, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: 'key=' + encodeURIComponent(key) + '&value=' + encodeURIComponent(value)
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                if (inputForUiHook) markRowSaving(inputForUiHook, false);
                if (ok && data.success) {
                    showToast('saved', 'Saved');
                } else {
                    showToast('error', data.message || 'Save failed');
                }
                return data;
            })
            .catch(() => {
                if (inputForUiHook) markRowSaving(inputForUiHook, false);
                showToast('error', 'Network error');
            });
        }

        // Debounce helper
        function debounce(fn, ms) {
            let t;
            return function (...args) {
                clearTimeout(t);
                t = setTimeout(() => fn.apply(this, args), ms);
            };
        }

        // ── Wire up text / number inputs (debounced) ──
        document.querySelectorAll('input[name].form-control, textarea[name].form-control, .ss-domain-input[name], input[name].reminder-day-input').forEach(input => {
            // renewal_reminder_days[] is an array — handled separately below
            if (input.name && input.name.endsWith('[]')) return;

            const handler = debounce(() => {
                saveSetting(input.name, input.value, input);
            }, 700);

            input.addEventListener('input', handler);
            input.addEventListener('change', handler);
        });

        // ── renewal_reminder_days[] — collect all 4 values, send as JSON array ──
        const dayInputs = document.querySelectorAll('input[name="renewal_reminder_days[]"]');
        if (dayInputs.length) {
            const saveDays = debounce(() => {
                const days = Array.from(dayInputs).map(i => parseInt(i.value, 10) || 0);
                showToast('saving', 'Saving…');
                fetch(ENDPOINT, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ key: 'renewal_reminder_days', value: JSON.stringify(days) })
                })
                .then(r => r.json())
                .then(data => showToast(data.success ? 'saved' : 'error', data.success ? 'Saved' : (data.message || 'Save failed')))
                .catch(() => showToast('error', 'Network error'));
            }, 700);

            dayInputs.forEach(i => {
                i.addEventListener('input', saveDays);
                i.addEventListener('change', saveDays);
            });
        }

        // ── Wire up boolean toggles (instant save + UI sync) ──
        document.querySelectorAll('.sp-toggle-switch input[type="checkbox"][name]').forEach(input => {
            input.addEventListener('change', function () {
                const checked = input.checked;

                // Generic pill update
                const pill = document.getElementById('pill_' + input.id);
                if (pill) {
                    pill.textContent = checked ? 'Enabled' : 'Disabled';
                    pill.className = 'sp-status-pill ' + (checked ? 'on' : 'off');
                }

                // Security toggle UI side-effects
                if (input.id === 'restrict_email_domain') {
                    const badge = document.getElementById('restrict_badge');
                    const wrap  = document.getElementById('domain_wrap');
                    const hint  = document.getElementById('domain_hint');
                    if (badge) {
                        badge.textContent = checked ? 'Active' : 'Inactive';
                        badge.className = 'sp-status-pill ' + (checked ? 'on' : 'off');
                    }
                    if (wrap) wrap.classList.toggle('ss-dimmed', !checked);
                    if (hint) hint.style.display = checked ? 'none' : 'flex';
                }

                saveSetting(input.name, checked ? '1' : '0', input);
            });
        });

        // Prevent accidental submit-on-Enter from doing a full-page POST
        const form = document.querySelector('form[action="{{ route('super-admin.settings.update') }}"]');
        if (form) {
            form.addEventListener('submit', e => e.preventDefault());
        }
    })();

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

