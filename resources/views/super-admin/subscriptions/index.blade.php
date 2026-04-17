@extends('super-admin.layout')

@section('title', 'Subscriptions')

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

    .badge-active {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-expired {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-suspended {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-expiring {
        background: #fef3c7;
        color: #92400e;
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
                <h1 class="page-header-title">Subscriptions</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-tasks-alt"></i>
                    <span> - Subscriptions</span>
                </div>
            </div>
            <p class="page-header-description">Manage all system subscriptions</p>
        </div>
    </div>

    {{-- Subscription Pricing Card --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-money-bag"></i>
                Subscription Pricing
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-card-label">1 Year</div>
                        <div class="stat-card-value" style="font-size: 1.5rem;">{{ $pricing['currency'] }} {{ number_format($pricing['1_year'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-card-label">2 Years</div>
                        <div class="stat-card-value" style="font-size: 1.5rem;">{{ $pricing['currency'] }} {{ number_format($pricing['2_years'], 2) }}</div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="stat-card">
                        <div class="stat-card-label">3 Years</div>
                        <div class="stat-card-value" style="font-size: 1.5rem;">{{ $pricing['currency'] }} {{ number_format($pricing['3_years'], 2) }}</div>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <p class="text-muted mb-2" style="font-size: 0.875rem;">
                    <i class="icofont-info-circle"></i> Base annual price: <strong>{{ $pricing['currency'] }} {{ number_format($pricing['base_price'], 2) }}</strong> per year
                </p>
                <a href="{{ route('super-admin.settings.index') }}#subscription" class="btn-modern btn-modern-primary">
                    <i class="icofont-settings"></i> Configure Annual Price
                </a>
            </div>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-label">Total Subscriptions</div>
            <div class="stat-card-value">{{ $stats['total'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Active</div>
            <div class="stat-card-value">{{ $stats['active'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Expiring Soon</div>
            <div class="stat-card-value">{{ $stats['expiring_soon'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Expired</div>
            <div class="stat-card-value">{{ $stats['expired'] }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Suspended</div>
            <div class="stat-card-value">{{ $stats['suspended'] }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="filter-section">
        <form method="GET" action="{{ route('super-admin.subscriptions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-control form-control-modern">
                    <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>All Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    <option value="suspended" {{ request('status') == 'suspended' ? 'selected' : '' }}>Suspended</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Plan</label>
                <select name="plan" class="form-control form-control-modern">
                    <option value="all" {{ request('plan') == 'all' ? 'selected' : '' }}>All Plans</option>
                    <option value="basic" {{ request('plan') == 'basic' ? 'selected' : '' }}>Basic</option>
                    <option value="standard" {{ request('plan') == 'standard' ? 'selected' : '' }}>Standard</option>
                    <option value="premium" {{ request('plan') == 'premium' ? 'selected' : '' }}>Premium</option>
                    <option value="enterprise" {{ request('plan') == 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control form-control-modern" 
                       placeholder="Institution name or code..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn-modern btn-modern-primary w-100">Filter</button>
            </div>
        </form>
    </div>

    {{-- Subscriptions Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-list"></i>
                All Subscriptions
            </h5>
        </div>
        <div class="modern-card-body">
            @if($subscriptions->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Institution</th>
                            <th>Plan</th>
                            <th>Status</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($subscriptions as $subscription)
                        <tr>
                            <td>
                                <strong>{{ $subscription->institution_name }}</strong><br>
                                <small class="text-muted">{{ $subscription->institution_code }}</small>
                            </td>
                            <td>{{ ucfirst($subscription->subscription_plan) }}</td>
                            <td>
                                @if($subscription->status == 'active')
                                    <span class="badge-modern badge-active">Active</span>
                                @elseif($subscription->status == 'expired')
                                    <span class="badge-modern badge-expired">Expired</span>
                                @elseif($subscription->status == 'suspended')
                                    <span class="badge-modern badge-suspended">Suspended</span>
                                @elseif($subscription->isExpiringSoon())
                                    <span class="badge-modern badge-expiring">Expiring Soon</span>
                                @else
                                    <span class="badge-modern">{{ ucfirst($subscription->status) }}</span>
                                @endif
                            </td>
                            <td>{{ $subscription->subscription_start_date->format('M d, Y') }}</td>
                            <td>{{ $subscription->subscription_end_date->format('M d, Y') }}</td>
                            <td>
                                <strong>{{ $subscription->currency }} {{ number_format($subscription->renewal_amount, 2) }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('super-admin.subscriptions.show', $subscription->id) }}" 
                                   class="btn-modern btn-modern-primary">View</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-4">
                {{ $subscriptions->links() }}
            </div>
            @else
            <div class="text-center py-5">
                <i class="icofont-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                <p class="text-muted mt-3">No subscriptions found</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

