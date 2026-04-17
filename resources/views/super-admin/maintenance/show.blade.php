@extends('super-admin.layout')

@section('title', 'Maintenance Details')

@push('styles')
<style>
    /* System Font Stack */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    /* Centered Container */
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Page Header Style. */
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

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1rem;
        color: #1f2937;
        font-weight: 500;
    }

    /* Badge Styling */
    .badge-modern {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-planned {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-in-progress {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-low {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-medium {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-high {
        background: #fed7aa;
        color: #9a3412;
    }

    .badge-critical {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Action Buttons */
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
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
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

    .btn-modern-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-modern-success:hover {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .btn-modern-warning:hover {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-modern-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.1));
        transform: translateY(-1px);
    }

    /* Description Box */
    .description-box {
        background: #f9fafb;
        border-radius: 0.5rem;
        padding: 1rem;
        border-left: 4px solid #667eea;
        margin-top: 0.5rem;
    }

    .description-box p {
        margin: 0;
        color: #374151;
        line-height: 1.6;
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Maintenance Details</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-tools"></i>
                    <span> - Maintenance Details</span>
                </div>
            </div>
            <p class="page-header-description">View detailed information about this maintenance</p>
        </div>
    </div>

    {{-- Actions --}}
    <div class="mb-4">
        <a href="{{ route('super-admin.maintenance.index') }}" class="btn-modern btn-modern-primary">
            <i class="icofont-arrow-left"></i> Back to List
        </a>

        @if(in_array($maintenance->status, ['planned', 'notified']))
            <a href="{{ route('super-admin.maintenance.edit', $maintenance->id) }}" class="btn-modern btn-modern-primary">
                <i class="icofont-edit"></i> Edit
            </a>
            <form method="POST" action="{{ route('super-admin.maintenance.start', $maintenance->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-modern btn-modern-success" 
                        onclick="return confirm('Are you sure you want to start this maintenance?')">
                    <i class="icofont-play"></i> Start Maintenance
                </button>
            </form>
            <form method="POST" action="{{ route('super-admin.maintenance.cancel', $maintenance->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-modern btn-modern-danger" 
                        onclick="return confirm('Are you sure you want to cancel this maintenance?')">
                    <i class="icofont-close"></i> Cancel
                </button>
            </form>
        @endif

        @if($maintenance->status === 'in_progress')
            <form method="POST" action="{{ route('super-admin.maintenance.complete', $maintenance->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-modern btn-modern-success" 
                        onclick="return confirm('Are you sure you want to mark this maintenance as completed?')">
                    <i class="icofont-check"></i> Complete Maintenance
                </button>
            </form>
        @endif

        @if($maintenance->rollback_available && $maintenance->status === 'completed')
            <form method="POST" action="{{ route('super-admin.maintenance.rollback', $maintenance->id) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn-modern btn-modern-warning" 
                        onclick="return confirm('Are you sure you want to rollback this maintenance?')">
                    <i class="icofont-undo"></i> Rollback
                </button>
            </form>
        @endif

        @if($maintenance->status !== 'in_progress')
            <form method="POST" action="{{ route('super-admin.maintenance.destroy', $maintenance->id) }}" class="d-inline" id="delete-maintenance-form">
                @csrf
                @method('DELETE')
                <input type="hidden" name="confirm" value="1">
                <button type="submit" class="btn-modern btn-modern-danger" 
                        onclick="return confirm('Are you sure you want to delete this maintenance? This action cannot be undone.')">
                    <i class="icofont-trash"></i> Delete
                </button>
            </form>
        @endif
    </div>

    {{-- Basic Information --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-info-circle"></i>
                Basic Information
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Title</span>
                    <span class="info-value">{{ $maintenance->title }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Type</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $maintenance->maintenance_type)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="info-value">
                        @if($maintenance->status == 'planned')
                            <span class="badge-modern badge-planned">Planned</span>
                        @elseif($maintenance->status == 'in_progress')
                            <span class="badge-modern badge-in-progress">In Progress</span>
                        @elseif($maintenance->status == 'completed')
                            <span class="badge-modern badge-completed">Completed</span>
                        @elseif($maintenance->status == 'cancelled')
                            <span class="badge-modern badge-cancelled">Cancelled</span>
                        @else
                            <span class="badge-modern">{{ ucfirst($maintenance->status) }}</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Impact Level</span>
                    <span class="info-value">
                        @if($maintenance->impact_level == 'low')
                            <span class="badge-modern badge-low">Low</span>
                        @elseif($maintenance->impact_level == 'medium')
                            <span class="badge-modern badge-medium">Medium</span>
                        @elseif($maintenance->impact_level == 'high')
                            <span class="badge-modern badge-high">High</span>
                        @elseif($maintenance->impact_level == 'critical')
                            <span class="badge-modern badge-critical">Critical</span>
                        @else
                            <span class="badge-modern">{{ ucfirst($maintenance->impact_level) }}</span>
                        @endif
                    </span>
                </div>
            </div>

            @if($maintenance->description)
            <div class="info-item">
                <span class="info-label">Description</span>
                <div class="description-box">
                    <p>{{ $maintenance->description }}</p>
                </div>
            </div>
            @endif

            @if($maintenance->technical_details)
            <div class="info-item" style="margin-top: 1rem;">
                <span class="info-label">Technical Details</span>
                <div class="description-box">
                    <p>{{ $maintenance->technical_details }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Schedule Information --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-calendar"></i>
                Schedule
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Scheduled Start</span>
                    <span class="info-value">{{ $maintenance->scheduled_start->format('M d, Y h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Scheduled End</span>
                    <span class="info-value">{{ $maintenance->scheduled_end->format('M d, Y h:i A') }}</span>
                </div>
                @if($maintenance->actual_start)
                <div class="info-item">
                    <span class="info-label">Actual Start</span>
                    <span class="info-value">{{ $maintenance->actual_start->format('M d, Y h:i A') }}</span>
                </div>
                @endif
                @if($maintenance->actual_end)
                <div class="info-item">
                    <span class="info-label">Actual End</span>
                    <span class="info-value">{{ $maintenance->actual_end->format('M d, Y h:i A') }}</span>
                </div>
                @endif
                @if($maintenance->estimated_downtime_minutes)
                <div class="info-item">
                    <span class="info-label">Estimated Downtime</span>
                    <span class="info-value">{{ $maintenance->estimated_downtime_minutes }} minutes</span>
                </div>
                @endif
                @if($maintenance->actual_downtime_minutes)
                <div class="info-item">
                    <span class="info-label">Actual Downtime</span>
                    <span class="info-value">{{ $maintenance->actual_downtime_minutes }} minutes</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Additional Information --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-settings"></i>
                Additional Information
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Requires Downtime</span>
                    <span class="info-value">{{ $maintenance->requires_downtime ? 'Yes' : 'No' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Display Banner</span>
                    <span class="info-value">{{ $maintenance->display_banner ? 'Yes' : 'No' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Rollback Available</span>
                    <span class="info-value">{{ $maintenance->rollback_available ? 'Yes' : 'No' }}</span>
                </div>
                @if($maintenance->performer)
                <div class="info-item">
                    <span class="info-label">Performed By</span>
                    <span class="info-value">{{ $maintenance->performer->first_name }} {{ $maintenance->performer->last_name }}</span>
                </div>
                @endif
                @if($maintenance->approver)
                <div class="info-item">
                    <span class="info-label">Approved By</span>
                    <span class="info-value">{{ $maintenance->approver->first_name }} {{ $maintenance->approver->last_name }}</span>
                </div>
                @endif
                @if($maintenance->approved_at)
                <div class="info-item">
                    <span class="info-label">Approved At</span>
                    <span class="info-value">{{ $maintenance->approved_at->format('M d, Y h:i A') }}</span>
                </div>
                @endif
            </div>

            @if($maintenance->rollback_procedure)
            <div class="info-item" style="margin-top: 1rem;">
                <span class="info-label">Rollback Procedure</span>
                <div class="description-box">
                    <p>{{ $maintenance->rollback_procedure }}</p>
                </div>
            </div>
            @endif

            @if($maintenance->completion_notes)
            <div class="info-item" style="margin-top: 1rem;">
                <span class="info-label">Completion Notes</span>
                <div class="description-box">
                    <p>{{ $maintenance->completion_notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Notifications --}}
    @if($maintenance->notifications && $maintenance->notifications->count() > 0)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-notification"></i>
                Notifications ({{ $maintenance->notifications->count() }})
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Title</th>
                            <th>Sent At</th>
                            <th>Recipients</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maintenance->notifications as $notification)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}</td>
                            <td>{{ $notification->title }}</td>
                            <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                            <td>{{ $notification->total_recipients ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

