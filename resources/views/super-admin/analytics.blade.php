@extends('super-admin.layout')

@section('title', 'Analytics')

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

    /* Period Selector */
    .period-selector {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 2rem;
        background: white;
        padding: 1rem;
        border-radius: 0.75rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }

    .period-btn {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: 1px solid #e5e7eb;
        background: white;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }

    .period-btn.active {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.05));
        color: #4338ca;
        border-color: rgba(99, 102, 241, 0.3);
    }

    .period-btn:hover {
        background: #f9fafb;
    }

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        transition: transform 0.2s, box-shadow 0.2s;
    }

    .stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .stat-card-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .stat-card-value {
        font-size: 2rem;
        font-weight: 700;
        color: #1f2937;
    }

    .stat-card-value.currency {
        font-size: 1.75rem;
        color: #059669;
    }

    .stat-card-change {
        font-size: 0.75rem;
        color: #059669;
        margin-top: 0.5rem;
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

    /* Chart Container */
    .chart-container {
        height: 300px;
        position: relative;
        margin-top: 1rem;
    }

    /* Analytics Grid */
    .analytics-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 1.5rem;
    }

    /* List Items */
    .analytics-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .analytics-list-item {
        padding: 0.875rem 0;
        border-bottom: 1px solid #f3f4f6;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .analytics-list-item:last-child {
        border-bottom: none;
    }

    .analytics-list-label {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .analytics-list-value {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
    }
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Analytics</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-chart-bar-graph"></i>
                    <span> - Analytics</span>
                </div>
            </div>
            <p class="page-header-description">Comprehensive system analytics and insights</p>
        </div>
    </div>

    {{-- Period Selector --}}
    <div class="period-selector">
        <a href="{{ route('super-admin.analytics', ['period' => 'daily']) }}" 
           class="period-btn {{ $period == 'daily' ? 'active' : '' }}">Daily</a>
        <a href="{{ route('super-admin.analytics', ['period' => 'weekly']) }}" 
           class="period-btn {{ $period == 'weekly' ? 'active' : '' }}">Weekly</a>
        <a href="{{ route('super-admin.analytics', ['period' => 'monthly']) }}" 
           class="period-btn {{ $period == 'monthly' ? 'active' : '' }}">Monthly</a>
        <a href="{{ route('super-admin.analytics', ['period' => 'yearly']) }}" 
           class="period-btn {{ $period == 'yearly' ? 'active' : '' }}">Yearly</a>
    </div>

    {{-- Revenue Analytics --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-money"></i>
                Revenue Analytics
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-label">
                        <i class="icofont-chart-line"></i>
                        Total Revenue
                    </div>
                    <div class="stat-card-value currency">GHS {{ number_format($analytics['revenue']['total'], 2) }}</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-label">
                        <i class="icofont-chart-bar"></i>
                        Average Transaction
                    </div>
                    <div class="stat-card-value currency">GHS {{ number_format($analytics['revenue']['average'], 2) }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscription Analytics --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-tasks-alt"></i>
                Subscription Analytics
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="analytics-grid">
                <div>
                    <h6 class="mb-3">By Status</h6>
                    <ul class="analytics-list">
                        @foreach($analytics['subscriptions']['by_status'] as $status => $count)
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">{{ ucfirst($status) }}</span>
                            <span class="analytics-list-value">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h6 class="mb-3">By Plan</h6>
                    <ul class="analytics-list">
                        @foreach($analytics['subscriptions']['by_plan'] as $plan => $count)
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">{{ ucfirst($plan) }}</span>
                            <span class="analytics-list-value">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h6 class="mb-3">Metrics</h6>
                    <ul class="analytics-list">
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">Renewal Rate</span>
                            <span class="analytics-list-value">{{ $analytics['subscriptions']['renewal_rate'] }}%</span>
                        </li>
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">Churn Rate</span>
                            <span class="analytics-list-value">{{ $analytics['subscriptions']['churn_rate'] }}%</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- Payment Analytics --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-credit-card"></i>
                Payment Analytics
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="analytics-grid">
                <div>
                    <h6 class="mb-3">By Status</h6>
                    <ul class="analytics-list">
                        @foreach($analytics['payments']['by_status'] as $status => $count)
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">{{ ucfirst($status) }}</span>
                            <span class="analytics-list-value">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h6 class="mb-3">By Payment Method</h6>
                    <ul class="analytics-list">
                        @foreach($analytics['payments']['by_method'] as $method => $count)
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">{{ ucfirst($method) }}</span>
                            <span class="analytics-list-value">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h6 class="mb-3">Metrics</h6>
                    <ul class="analytics-list">
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">Success Rate</span>
                            <span class="analytics-list-value">{{ $analytics['payments']['success_rate'] }}%</span>
                        </li>
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">Avg Transaction</span>
                            <span class="analytics-list-value">GHS {{ number_format($analytics['payments']['average_transaction_value'], 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- User Analytics --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-users-alt-3"></i>
                User Analytics
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="analytics-grid">
                <div>
                    <h6 class="mb-3">By Role</h6>
                    <ul class="analytics-list">
                        @foreach($analytics['users']['by_role'] as $role => $count)
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">
                                @if($role == 'super_admin')
                                    Super Admin
                                @elseif($role == 'admin')
                                    User
                                @elseif($role == 'user')
                                    Admin
                                @else
                                    {{ ucfirst(str_replace('_', ' ', $role)) }}
                                @endif
                            </span>
                            <span class="analytics-list-value">{{ $count }}</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <h6 class="mb-3">User Status</h6>
                    <ul class="analytics-list">
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">Approved Users</span>
                            <span class="analytics-list-value">{{ $analytics['users']['approved_users'] }}</span>
                        </li>
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">Pending Approval</span>
                            <span class="analytics-list-value">{{ $analytics['users']['pending_approval'] }}</span>
                        </li>
                        <li class="analytics-list-item">
                            <span class="analytics-list-label">New This Month</span>
                            <span class="analytics-list-value">{{ $analytics['users']['new_users_this_month'] }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

