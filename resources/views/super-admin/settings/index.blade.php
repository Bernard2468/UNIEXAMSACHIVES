@extends('super-admin.layout')

@section('title', 'System Settings')

@push('styles')
<style>
    /* System Font Stack */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
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
                                <div class="custom-control custom-switch">
                                    <input type="checkbox" 
                                           class="custom-control-input" 
                                           id="{{ $setting->key }}" 
                                           name="{{ $setting->key }}"
                                           {{ $setting->typed_value ? 'checked' : '' }}
                                           {{ !$setting->is_editable ? 'disabled' : '' }}>
                                    <label class="custom-control-label" for="{{ $setting->key }}">
                                        {{ $setting->typed_value ? 'Enabled' : 'Disabled' }}
                                    </label>
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

