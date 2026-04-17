@extends('super-admin.layout')

@section('title', 'Edit Maintenance')

@push('styles')
<style>
    /* System Font Stack */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    /* Centered Container */
    .page-container {
        max-width: 1000px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Page Header Style */
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

    /* Modern Card */
    .modern-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .modern-card-header {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #1f2937;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modern-card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.125rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modern-card-body {
        padding: 1.5rem;
    }

    /* Form Styling */
    .form-group-modern {
        margin-bottom: 1.5rem;
    }

    .form-label-modern {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-control-modern {
        width: 100%;
        border-radius: 0.5rem;
        border: 1px solid #d1d5db;
        padding: 0.625rem 0.875rem;
        font-size: 0.875rem;
        transition: all 0.2s;
    }

    .form-control-modern:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        outline: none;
    }

    .form-text-modern {
        font-size: 0.75rem;
        color: #6b7280;
        margin-top: 0.25rem;
    }

    /* Checkbox Styling */
    .checkbox-modern {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .checkbox-modern input[type="checkbox"] {
        width: 1.25rem;
        height: 1.25rem;
        cursor: pointer;
    }

    .checkbox-modern label {
        font-size: 0.875rem;
        color: #374151;
        cursor: pointer;
        margin: 0;
    }

    /* Button Styling */
    .btn-modern {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-modern-primary {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.05));
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .btn-modern-primary:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.1), rgba(156, 163, 175, 0.05));
        color: #374151;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }

    .btn-modern-secondary:hover {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.15), rgba(156, 163, 175, 0.1));
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Edit Maintenance</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-tools"></i>
                    <span> - Edit Maintenance</span>
                </div>
            </div>
            <p class="page-header-description">Update maintenance schedule details</p>
        </div>
    </div>

    <form method="POST" action="{{ route('super-admin.maintenance.update', $maintenance->id) }}">
        @csrf
        @method('PUT')

        {{-- Basic Information --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5>
                    <i class="icofont-info-circle"></i>
                    Basic Information
                </h5>
            </div>
            <div class="modern-card-body">
                <div class="form-group-modern">
                    <label class="form-label-modern">Maintenance Type *</label>
                    <select name="maintenance_type" class="form-control form-control-modern" required>
                        <option value="">Select Type</option>
                        <option value="scheduled_maintenance" {{ $maintenance->maintenance_type == 'scheduled_maintenance' ? 'selected' : '' }}>Scheduled Maintenance</option>
                        <option value="emergency_maintenance" {{ $maintenance->maintenance_type == 'emergency_maintenance' ? 'selected' : '' }}>Emergency Maintenance</option>
                        <option value="system_update" {{ $maintenance->maintenance_type == 'system_update' ? 'selected' : '' }}>System Update</option>
                        <option value="security_patch" {{ $maintenance->maintenance_type == 'security_patch' ? 'selected' : '' }}>Security Patch</option>
                        <option value="database_optimization" {{ $maintenance->maintenance_type == 'database_optimization' ? 'selected' : '' }}>Database Optimization</option>
                        <option value="server_upgrade" {{ $maintenance->maintenance_type == 'server_upgrade' ? 'selected' : '' }}>Server Upgrade</option>
                        <option value="backup_restore" {{ $maintenance->maintenance_type == 'backup_restore' ? 'selected' : '' }}>Backup/Restore</option>
                        <option value="other" {{ $maintenance->maintenance_type == 'other' ? 'selected' : '' }}>Other</option>
                    </select>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Title *</label>
                    <input type="text" name="title" class="form-control form-control-modern" 
                           value="{{ $maintenance->title }}" required>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Description *</label>
                    <textarea name="description" class="form-control form-control-modern" 
                              rows="4" required>{{ $maintenance->description }}</textarea>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Technical Details</label>
                    <textarea name="technical_details" class="form-control form-control-modern" 
                              rows="3">{{ $maintenance->technical_details }}</textarea>
                    <small class="form-text-modern">Internal notes for technical team</small>
                </div>
            </div>
        </div>

        {{-- Schedule --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5>
                    <i class="icofont-calendar"></i>
                    Schedule
                </h5>
            </div>
            <div class="modern-card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Scheduled Start *</label>
                            <input type="datetime-local" name="scheduled_start" 
                                   class="form-control form-control-modern" 
                                   value="{{ $maintenance->scheduled_start->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group-modern">
                            <label class="form-label-modern">Scheduled End *</label>
                            <input type="datetime-local" name="scheduled_end" 
                                   class="form-control form-control-modern" 
                                   value="{{ $maintenance->scheduled_end->format('Y-m-d\TH:i') }}" required>
                        </div>
                    </div>
                </div>

                <div class="form-group-modern">
                    <label class="form-label-modern">Impact Level *</label>
                    <select name="impact_level" class="form-control form-control-modern" required>
                        <option value="low" {{ $maintenance->impact_level == 'low' ? 'selected' : '' }}>Low - Minimal impact</option>
                        <option value="medium" {{ $maintenance->impact_level == 'medium' ? 'selected' : '' }}>Medium - Some services affected</option>
                        <option value="high" {{ $maintenance->impact_level == 'high' ? 'selected' : '' }}>High - Major services affected</option>
                        <option value="critical" {{ $maintenance->impact_level == 'critical' ? 'selected' : '' }}>Critical - System unavailable</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- Downtime & Banner --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5>
                    <i class="icofont-ui-settings"></i>
                    Downtime & Notifications
                </h5>
            </div>
            <div class="modern-card-body">
                <div class="checkbox-modern">
                    <input type="checkbox" name="requires_downtime" id="requires_downtime" value="1" 
                           {{ $maintenance->requires_downtime ? 'checked' : '' }}>
                    <label for="requires_downtime">Requires Downtime</label>
                </div>

                <div class="form-group-modern" id="downtime_info" style="display: {{ $maintenance->requires_downtime ? 'block' : 'none' }};">
                    <label class="form-label-modern">Estimated Downtime</label>
                    <div class="alert alert-info" style="margin: 0; padding: 12px; background: #e7f3ff; border: 1px solid #b3d9ff; border-radius: 6px; color: #0066cc;">
                        <i class="icofont-info-circle"></i> 
                        <span id="calculated_downtime">Calculated automatically from Scheduled Start and End times</span>
                    </div>
                </div>

                <div class="checkbox-modern">
                    <input type="checkbox" name="display_banner" id="display_banner" value="1" 
                           {{ $maintenance->display_banner ? 'checked' : '' }}>
                    <label for="display_banner">Display Banner to Users</label>
                </div>

                <div id="banner_options" style="display: {{ $maintenance->display_banner ? 'block' : 'none' }};">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Banner Display From</label>
                                <input type="datetime-local" name="banner_display_from" 
                                       class="form-control form-control-modern"
                                       value="{{ $maintenance->banner_display_from ? $maintenance->banner_display_from->format('Y-m-d\TH:i') : '' }}">
                                <small class="form-text-modern">When to start showing banner (optional)</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group-modern">
                                <label class="form-label-modern">Banner Display Until</label>
                                <input type="datetime-local" name="banner_display_until" 
                                       class="form-control form-control-modern"
                                       value="{{ $maintenance->banner_display_until ? $maintenance->banner_display_until->format('Y-m-d\TH:i') : '' }}">
                                <small class="form-text-modern">When to stop showing banner (optional)</small>
                            </div>
                        </div>
                    </div>

                    <div class="form-group-modern">
                        <label class="form-label-modern">Banner Message</label>
                        <textarea name="banner_message" class="form-control form-control-modern" 
                                  rows="2">{{ $maintenance->banner_message }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Danger Zone - Update Actions --}}
        <div class="modern-card">
            <div class="modern-card-header" style="background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);">
                <h5 style="color: #991b1b;">
                    <i class="icofont-warning"></i>
                    Update Maintenance
                </h5>
            </div>
            <div class="modern-card-body">
                <p style="color: #1f2937; margin-bottom: 1.5rem; font-weight: 600;">
                    <i class="icofont-info-circle"></i> 
                    By clicking "Update Maintenance", you are confirming that all changes are correct and the maintenance schedule will be updated.
                </p>
                <div style="border-top: 1px solid #e5e7eb; margin: 1.5rem 0; padding-top: 1.5rem;"></div>
                <div class="d-flex justify-content-between align-items-center">
                    <a href="{{ route('super-admin.maintenance.show', $maintenance->id) }}" class="btn-modern btn-modern-secondary">
                        <i class="icofont-close"></i> Cancel
                    </a>
                    <button type="submit" class="btn-modern" 
                            style="background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05)); 
                                   color: #dc2626; 
                                   border: 2px solid rgba(239, 68, 68, 0.3);
                                   font-weight: 700;"
                            onclick="return confirm('Are you sure you want to update this maintenance? Please review all changes before proceeding.')">
                        Update Maintenance
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    // Toggle downtime info display
    document.getElementById('requires_downtime')?.addEventListener('change', function() {
        document.getElementById('downtime_info').style.display = this.checked ? 'block' : 'none';
        if (this.checked) {
            calculateDowntime();
        }
    });

    // Calculate downtime from scheduled start and end
    function calculateDowntime() {
        const startInput = document.querySelector('input[name="scheduled_start"]');
        const endInput = document.querySelector('input[name="scheduled_end"]');
        const downtimeDisplay = document.getElementById('calculated_downtime');
        
        if (!startInput || !endInput || !downtimeDisplay) return;
        
        function updateDowntime() {
            const start = new Date(startInput.value);
            const end = new Date(endInput.value);
            
            if (start && end && end > start) {
                const diffMs = end - start;
                const diffMinutes = Math.floor(diffMs / (1000 * 60));
                const hours = Math.floor(diffMinutes / 60);
                const minutes = diffMinutes % 60;
                
                let displayText = '';
                if (hours > 0) {
                    displayText = `${hours} hour${hours > 1 ? 's' : ''}`;
                    if (minutes > 0) {
                        displayText += ` ${minutes} minute${minutes > 1 ? 's' : ''}`;
                    }
                } else {
                    displayText = `${minutes} minute${minutes > 1 ? 's' : ''}`;
                }
                
                downtimeDisplay.textContent = `Estimated Downtime: ${displayText} (${diffMinutes} minutes)`;
            } else {
                downtimeDisplay.textContent = 'Please set both Scheduled Start and End times';
            }
        }
        
        startInput.addEventListener('change', updateDowntime);
        endInput.addEventListener('change', updateDowntime);
        updateDowntime();
    }
    
    // Initialize on page load if requires_downtime is checked
    if (document.getElementById('requires_downtime')?.checked) {
        calculateDowntime();
    }

    // Toggle banner options
    document.getElementById('display_banner')?.addEventListener('change', function() {
        document.getElementById('banner_options').style.display = this.checked ? 'block' : 'none';
    });
</script>
@endpush
@endsection

