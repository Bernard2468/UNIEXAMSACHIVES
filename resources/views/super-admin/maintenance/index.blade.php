@extends('super-admin.layout')

@section('title', 'Maintenance')

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

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }

    .stat-card-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .stat-card-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1f2937;
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

    /* Table Styling */
    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table thead {
        background: #f9fafb;
    }

    .modern-table th {
        padding: 0.875rem 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
        color: #1f2937;
    }

    .modern-table tbody tr:hover {
        background: #f9fafb;
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

    /* Action Buttons */
    .btn-modern {
        padding: 0.5rem 1rem;
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

    /* Action buttons container */
    .action-buttons {
        display: flex;
        gap: 0.5rem;
        align-items: center;
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Maintenance</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-tools"></i>
                    <span> - Maintenance</span>
                </div>
            </div>
            <p class="page-header-description">Manage system maintenance schedules</p>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-label">Total</div>
            <div class="stat-card-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Planned</div>
            <div class="stat-card-value">{{ $stats['planned'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">In Progress</div>
            <div class="stat-card-value">{{ $stats['in_progress'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Completed</div>
            <div class="stat-card-value">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Upcoming</div>
            <div class="stat-card-value">{{ $stats['upcoming'] }}</div>
        </div>
    </div>

    {{-- Upcoming Maintenance --}}
    @if($upcoming->count() > 0)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-calendar"></i>
                Upcoming Maintenance (Next 14 Days)
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Scheduled Start</th>
                            <th>Impact</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcoming as $maintenance)
                        <tr>
                            <td><strong>{{ $maintenance->title }}</strong></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $maintenance->maintenance_type)) }}</td>
                            <td>{{ $maintenance->scheduled_start->format('M d, Y h:i A') }}</td>
                            <td>{{ ucfirst($maintenance->impact_level) }}</td>
                            <td>
                                @if($maintenance->status == 'planned')
                                    <span class="badge-modern badge-planned">Planned</span>
                                @elseif($maintenance->status == 'in_progress')
                                    <span class="badge-modern badge-in-progress">In Progress</span>
                                @else
                                    <span class="badge-modern">{{ ucfirst($maintenance->status) }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('super-admin.maintenance.show', $maintenance->id) }}" 
                                       class="btn-modern btn-modern-primary">View</a>
                                    @if($maintenance->status !== 'in_progress')
                                    <form method="POST" action="{{ route('super-admin.maintenance.destroy', $maintenance->id) }}" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this maintenance? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="submit" class="btn-modern btn-modern-danger">
                                            <i class="icofont-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- All Maintenance Logs --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-list"></i>
                All Maintenance Logs
            </h5>
        </div>
        <div class="modern-card-body">
            @if($maintenanceLogs->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Scheduled Start</th>
                            <th>Status</th>
                            <th>Impact</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($maintenanceLogs as $maintenance)
                        <tr>
                            <td><strong>{{ $maintenance->title }}</strong></td>
                            <td>{{ ucfirst(str_replace('_', ' ', $maintenance->maintenance_type)) }}</td>
                            <td>{{ $maintenance->scheduled_start->format('M d, Y h:i A') }}</td>
                            <td>
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
                            </td>
                            <td>{{ ucfirst($maintenance->impact_level) }}</td>
                            <td>
                                <div class="action-buttons">
                                    <a href="{{ route('super-admin.maintenance.show', $maintenance->id) }}" 
                                       class="btn-modern btn-modern-primary">View</a>
                                    @if($maintenance->status !== 'in_progress')
                                    <form method="POST" action="{{ route('super-admin.maintenance.destroy', $maintenance->id) }}" 
                                          class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this maintenance? This action cannot be undone.')">
                                        @csrf
                                        @method('DELETE')
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="submit" class="btn-modern btn-modern-danger">
                                            <i class="icofont-trash"></i> Delete
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $maintenanceLogs->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="icofont-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                <p class="text-muted mt-3">No maintenance logs found</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="text-right mb-4">
        <a href="{{ route('super-admin.maintenance.create') }}" class="btn-modern btn-modern-primary">
            <i class="icofont-plus"></i> Schedule Maintenance
        </a>
    </div>
</div>
@endsection

