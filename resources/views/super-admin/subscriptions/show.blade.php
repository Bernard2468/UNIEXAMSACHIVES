@extends('super-admin.layout')

@section('title', 'Subscription Details')

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

    .page-header-breadcrumb a {
        color: #6b7280;
        text-decoration: none;
    }

    .page-header-breadcrumb a:hover {
        color: #4338ca;
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

    .stat-card-value.currency {
        font-size: 1.5rem;
        color: #059669;
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

    .info-value-large {
        font-size: 1.5rem;
        font-weight: 700;
        color: #059669;
    }

    /* Badge Styling */
    .badge-modern {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-expiring-soon {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-expired {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-suspended {
        background: #e5e7eb;
        color: #374151;
    }

    .badge-cancelled {
        background: #1f2937;
        color: #f9fafb;
    }

    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-failed {
        background: #fee2e2;
        color: #991b1b;
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

    .btn-modern-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-modern-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.1));
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

    .btn-modern-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.1), rgba(75, 85, 99, 0.05));
        color: #4b5563;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }

    .btn-modern-secondary:hover {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.15), rgba(75, 85, 99, 0.1));
        transform: translateY(-1px);
    }

    /* Alert Box */
    .alert-box {
        padding: 1rem;
        border-radius: 0.5rem;
        margin-bottom: 1rem;
        border-left: 4px solid;
    }

    .alert-box-warning {
        background: #fef3c7;
        border-color: #f59e0b;
        color: #92400e;
    }

    .alert-box-danger {
        background: #fee2e2;
        border-color: #ef4444;
        color: #991b1b;
    }

    .alert-box-info {
        background: #dbeafe;
        border-color: #3b82f6;
        color: #1e40af;
    }
</style>
@endpush

@section('content')
<div class="page-container">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Subscription Details</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <a href="{{ route('super-admin.subscriptions.index') }}">
                        <i class="icofont-building"></i>
                        <span>Subscriptions</span>
                    </a>
                    <span> / </span>
                    <span>View</span>
                </div>
            </div>
            <p class="page-header-description">{{ $subscription->institution_name }} - {{ $subscription->institution_code }}</p>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Status Alerts --}}
    @if($subscription->is_expired && !$subscription->is_in_grace_period)
    <div class="alert-box alert-box-danger">
        <strong>⚠️ Subscription Expired</strong><br>
        This subscription expired on {{ $subscription->subscription_end_date->format('F d, Y') }}. 
        The grace period has also ended. The subscription is now suspended.
    </div>
    @elseif($subscription->is_expired && $subscription->is_in_grace_period)
    <div class="alert-box alert-box-warning">
        <strong>⚠️ Subscription Expired - In Grace Period</strong><br>
        This subscription expired on {{ $subscription->subscription_end_date->format('F d, Y') }}, 
        but is still within the grace period until {{ $subscription->grace_period_ends_at->format('F d, Y') }}.
    </div>
    @elseif($subscription->is_expiring_soon)
    <div class="alert-box alert-box-warning">
        <strong>⚠️ Subscription Expiring Soon</strong><br>
        This subscription will expire in {{ $subscription->days_until_expiry }} days on {{ $subscription->subscription_end_date->format('F d, Y') }}.
    </div>
    @endif

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-label">Total Revenue</div>
            <div class="stat-card-value currency">{{ $subscription->currency }} {{ number_format($stats['total_paid'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Successful Payments</div>
            <div class="stat-card-value">{{ $stats['successful_payments'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Failed Payments</div>
            <div class="stat-card-value">{{ $stats['failed_payments'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Pending Payments</div>
            <div class="stat-card-value">{{ $stats['pending_payments'] }}</div>
        </div>
    </div>

    {{-- Subscription Overview --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-info-circle"></i>
                Subscription Overview
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span>
                        @if($subscription->status == 'active')
                            <span class="badge-modern badge-active">Active</span>
                        @elseif($subscription->status == 'expiring_soon')
                            <span class="badge-modern badge-expiring-soon">Expiring Soon</span>
                        @elseif($subscription->status == 'expired')
                            <span class="badge-modern badge-expired">Expired</span>
                        @elseif($subscription->status == 'suspended')
                            <span class="badge-modern badge-suspended">Suspended</span>
                        @elseif($subscription->status == 'cancelled')
                            <span class="badge-modern badge-cancelled">Cancelled</span>
                        @else
                            <span class="badge-modern">{{ ucfirst($subscription->status) }}</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Subscription Plan</span>
                    <span class="info-value">{{ ucfirst($subscription->subscription_plan) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Renewal Amount</span>
                    <span class="info-value-large">{{ $subscription->currency }} {{ number_format($subscription->renewal_amount, 2) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Renewal Cycle</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $subscription->renewal_cycle)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Auto Renewal</span>
                    <span class="info-value">{{ $subscription->auto_renewal ? 'Enabled' : 'Disabled' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Grace Period</span>
                    <span class="info-value">{{ $subscription->grace_period_days }} days</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Institution Information --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-building"></i>
                Institution Information
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Institution Name</span>
                    <span class="info-value">{{ $subscription->institution_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Institution Code</span>
                    <span class="info-value">{{ $subscription->institution_code ?? 'N/A' }}</span>
                </div>
                @if($subscription->hosting_package_type)
                <div class="info-item">
                    <span class="info-label">Hosting Package</span>
                    <span class="info-value">{{ $subscription->hosting_package_type }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Subscription Dates --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-calendar"></i>
                Subscription Dates
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Start Date</span>
                    <span class="info-value">{{ $subscription->subscription_start_date->format('F d, Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">End Date</span>
                    <span class="info-value">{{ $subscription->subscription_end_date->format('F d, Y') }}</span>
                </div>
                @if($subscription->days_until_expiry >= 0)
                <div class="info-item">
                    <span class="info-label">Days Until Expiry</span>
                    <span class="info-value">{{ $subscription->days_until_expiry }} days</span>
                </div>
                @else
                <div class="info-item">
                    <span class="info-label">Days Since Expiry</span>
                    <span class="info-value" style="color: #dc2626;">{{ abs($subscription->days_until_expiry) }} days</span>
                </div>
                @endif
                @if($subscription->last_payment_date)
                <div class="info-item">
                    <span class="info-label">Last Payment Date</span>
                    <span class="info-value">{{ $subscription->last_payment_date->format('F d, Y h:i A') }}</span>
                </div>
                @endif
                @if($subscription->next_payment_date)
                <div class="info-item">
                    <span class="info-label">Next Payment Date</span>
                    <span class="info-value">{{ $subscription->next_payment_date->format('F d, Y h:i A') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Payment Information --}}
    @if($subscription->failed_payment_attempts > 0 || $subscription->last_failed_payment_at)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-warning"></i>
                Payment Issues
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Failed Payment Attempts</span>
                    <span class="info-value" style="color: #dc2626;">{{ $subscription->failed_payment_attempts }}</span>
                </div>
                @if($subscription->last_failed_payment_at)
                <div class="info-item">
                    <span class="info-label">Last Failed Payment</span>
                    <span class="info-value">{{ $subscription->last_failed_payment_at->format('F d, Y h:i A') }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>
    @endif

    {{-- Recent Transactions --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-credit-card"></i>
                Recent Transactions
            </h5>
        </div>
        <div class="modern-card-body">
            @if($subscription->transactions->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscription->transactions as $transaction)
                        <tr>
                            <td>
                                <strong>{{ $transaction->transaction_reference }}</strong>
                                @if($transaction->invoice_number)
                                <br><small class="text-muted">Invoice: {{ $transaction->invoice_number }}</small>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $transaction->currency }} {{ number_format($transaction->amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($transaction->status == 'completed')
                                    <span class="badge-modern badge-completed">Completed</span>
                                @elseif($transaction->status == 'pending')
                                    <span class="badge-modern badge-pending">Pending</span>
                                @elseif($transaction->status == 'failed')
                                    <span class="badge-modern badge-failed">Failed</span>
                                @else
                                    <span class="badge-modern">{{ ucfirst($transaction->status) }}</span>
                                @endif
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $transaction->transaction_type)) }}</td>
                            <td>
                                {{ $transaction->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $transaction->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.payments.show', $transaction->id) }}" class="btn-modern btn-modern-primary" style="padding: 0.5rem 1rem; font-size: 0.75rem;">
                                    View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="text-center py-5">
                <i class="icofont-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                <p class="text-muted mt-3">No transactions found</p>
            </div>
            @endif
        </div>
    </div>

    {{-- System Notifications --}}
    @if($subscription->systemNotifications->count() > 0)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-bell"></i>
                Recent Notifications
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscription->systemNotifications as $notification)
                        <tr>
                            <td>{{ ucfirst(str_replace('_', ' ', $notification->notification_type)) }}</td>
                            <td>{{ Str::limit($notification->message, 100) }}</td>
                            <td>{{ $notification->created_at->format('M d, Y h:i A') }}</td>
                            <td>
                                @if($notification->is_read)
                                    <span class="badge-modern badge-completed">Read</span>
                                @else
                                    <span class="badge-modern badge-pending">Unread</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Admin Notes --}}
    @if($subscription->admin_notes)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-note"></i>
                Admin Notes
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-item">
                <span class="info-value" style="white-space: pre-wrap;">{{ $subscription->admin_notes }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-settings"></i>
                Actions
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="d-flex flex-wrap">
                <a href="{{ route('super-admin.subscriptions.index') }}" class="btn-modern btn-modern-secondary">
                    <i class="icofont-arrow-left"></i> Back to Subscriptions
                </a>

                @if($subscription->status == 'suspended' || $subscription->status == 'expired')
                    <form action="{{ route('super-admin.subscriptions.reactivate', $subscription->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-modern btn-modern-success">
                            <i class="icofont-check-circled"></i> Reactivate Subscription
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

