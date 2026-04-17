@extends('super-admin.layout')

@section('title', 'Payments')

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

    /* Filter Section */
    .filter-section {
        background: white;
        border-radius: 1rem;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }

    .form-control-modern {
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
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Payments</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-credit-card"></i>
                    <span> - Payments</span>
                </div>
            </div>
            <p class="page-header-description">Manage all payment transactions</p>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-label">Total Transactions</div>
            <div class="stat-card-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Completed</div>
            <div class="stat-card-value">{{ $stats['completed'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Pending</div>
            <div class="stat-card-value">{{ $stats['pending'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Failed</div>
            <div class="stat-card-value">{{ $stats['failed'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Total Revenue</div>
            <div class="stat-card-value currency">GHS {{ number_format($stats['total_revenue'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Monthly Revenue</div>
            <div class="stat-card-value currency">GHS {{ number_format($stats['monthly_revenue'], 2) }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-section">
        <form method="GET" action="{{ route('super-admin.payments.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control form-control-modern">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Type</label>
                <select name="type" class="form-control form-control-modern">
                    <option value="all" {{ request('type') == 'all' ? 'selected' : '' }}>All Types</option>
                    <option value="subscription_renewal" {{ request('type') == 'subscription_renewal' ? 'selected' : '' }}>Renewal</option>
                    <option value="initial_payment" {{ request('type') == 'initial_payment' ? 'selected' : '' }}>Initial</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control form-control-modern" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control form-control-modern" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn-modern btn-modern-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    {{-- Payments Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-list"></i>
                Payment Transactions
            </h5>
        </div>
        <div class="modern-card-body">
            @if($payments->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Reference</th>
                            <th>Institution</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Type</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($payments as $payment)
                        <tr>
                            <td>
                                <strong>{{ $payment->transaction_reference }}</strong>
                                @if($payment->invoice_number)
                                <br><small class="text-muted">Invoice: {{ $payment->invoice_number }}</small>
                                @endif
                            </td>
                            <td>{{ $payment->subscription->institution_name ?? 'N/A' }}</td>
                            <td>
                                <strong>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($payment->status == 'completed')
                                    <span class="badge-modern badge-completed">Completed</span>
                                @elseif($payment->status == 'pending')
                                    <span class="badge-modern badge-pending">Pending</span>
                                @elseif($payment->status == 'failed')
                                    <span class="badge-modern badge-failed">Failed</span>
                                @else
                                    <span class="badge-modern">{{ ucfirst($payment->status) }}</span>
                                @endif
                            </td>
                            <td>{{ ucfirst(str_replace('_', ' ', $payment->transaction_type)) }}</td>
                            <td>
                                {{ $payment->created_at->format('M d, Y') }}<br>
                                <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.payments.show', $payment->id) }}" 
                                   class="btn-modern btn-modern-primary">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $payments->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="icofont-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                <p class="text-muted mt-3">No payments found</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

