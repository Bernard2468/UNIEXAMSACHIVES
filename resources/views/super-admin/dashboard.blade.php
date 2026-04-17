@extends('super-admin.layout')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* System Font Stack */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    /* Modern Button Component Styles - Whitish Grey Theme */
    .modern-action-button {
        position: relative;
        overflow: hidden;
        border: 2px solid rgba(229, 231, 235, 0.6);
        cursor: pointer;
        transition: all 0.5s ease-out;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        text-decoration: none;
        display: block;
        width: 100%;
        padding: 1rem;
        border-radius: 1rem;
        background: linear-gradient(to bottom right, rgba(249, 250, 251, 0.95), rgba(243, 244, 246, 0.95), rgba(249, 250, 251, 0.95));
    }

    .modern-action-button:hover {
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        transform: scale(1.02) translateY(-4px);
        border-color: rgba(209, 213, 219, 0.8);
    }

    .modern-action-button:active {
        transform: scale(0.95);
    }

    /* Moving gradient layer */
    .modern-action-button::before {
        content: '';
        position: absolute;
        inset: 0;
        background: linear-gradient(to right, transparent, rgba(255, 255, 255, 0.5), transparent);
        transform: translateX(-100%);
        transition: transform 1s ease-out;
        z-index: 1;
    }

    .modern-action-button:hover::before {
        transform: translateX(100%);
    }

    /* Overlay glow */
    .modern-action-button::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 1rem;
        background: linear-gradient(to right, rgba(255, 255, 255, 0.3), rgba(255, 255, 255, 0.1), rgba(255, 255, 255, 0.3));
        opacity: 0;
        transition: opacity 0.5s;
        z-index: 1;
    }

    .modern-action-button:hover::after {
        opacity: 1;
    }

    /* Content wrapper */
    .modern-button-content {
        position: relative;
        z-index: 10;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    /* Icon container - default grey */
    .modern-button-icon {
        padding: 0.75rem;
        border-radius: 0.5rem;
        background: linear-gradient(to bottom right, rgba(229, 231, 235, 0.8), rgba(243, 244, 246, 0.6));
        backdrop-filter: blur(4px);
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modern-action-button:hover .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(209, 213, 219, 0.9), rgba(229, 231, 235, 0.7));
    }

    /* Colorful icon containers - Primary (Indigo) */
    .button-icon-primary .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(99, 102, 241, 0.5), rgba(129, 140, 248, 0.3));
    }

    .button-icon-primary:hover .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(129, 140, 248, 0.6), rgba(99, 102, 241, 0.4));
    }

    /* Colorful icon containers - Info (Blue) */
    .button-icon-info .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(59, 130, 246, 0.5), rgba(96, 165, 250, 0.3));
    }

    .button-icon-info:hover .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(96, 165, 250, 0.6), rgba(59, 130, 246, 0.4));
    }

    /* Colorful icon containers - Success (Green) */
    .button-icon-success .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(34, 197, 94, 0.5), rgba(74, 222, 128, 0.3));
    }

    .button-icon-success:hover .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(74, 222, 128, 0.6), rgba(34, 197, 94, 0.4));
    }

    /* Colorful icon containers - Warning (Yellow) */
    .button-icon-warning .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(234, 179, 8, 0.5), rgba(250, 204, 21, 0.3));
    }

    .button-icon-warning:hover .modern-button-icon {
        background: linear-gradient(to bottom right, rgba(250, 204, 21, 0.6), rgba(234, 179, 8, 0.4));
    }

    /* Icon SVG and Icon Fonts - default grey */
    .modern-button-icon svg,
    .modern-button-icon i {
        width: 1.75rem;
        height: 1.75rem;
        color: #374151;
        transition: all 0.3s;
        font-size: 1.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modern-action-button:hover .modern-button-icon svg,
    .modern-action-button:hover .modern-button-icon i {
        color: #1f2937;
        transform: scale(1.1);
    }

    /* Icon SVG and Icon Fonts - white for colorful icon containers */
    .button-icon-primary .modern-button-icon svg,
    .button-icon-primary .modern-button-icon i,
    .button-icon-info .modern-button-icon svg,
    .button-icon-info .modern-button-icon i,
    .button-icon-success .modern-button-icon svg,
    .button-icon-success .modern-button-icon i,
    .button-icon-warning .modern-button-icon svg,
    .button-icon-warning .modern-button-icon i {
        color: white;
    }

    .button-icon-primary:hover .modern-button-icon svg,
    .button-icon-primary:hover .modern-button-icon i,
    .button-icon-info:hover .modern-button-icon svg,
    .button-icon-info:hover .modern-button-icon i,
    .button-icon-success:hover .modern-button-icon svg,
    .button-icon-success:hover .modern-button-icon i,
    .button-icon-warning:hover .modern-button-icon svg,
    .button-icon-warning:hover .modern-button-icon i {
        color: rgba(255, 255, 255, 0.9);
        transform: scale(1.1);
    }

    /* Text container */
    .modern-button-text {
        flex: 1;
        text-align: left;
    }

    .modern-button-title {
        color: #1f2937;
        font-weight: 700;
        font-size: 1.125rem;
        margin: 0;
        transition: color 0.3s;
    }

    .modern-action-button:hover .modern-button-title {
        color: #111827;
    }

    .modern-button-subtitle {
        color: #6b7280;
        font-size: 0.875rem;
        margin: 0;
        transition: color 0.3s;
    }

    .modern-action-button:hover .modern-button-subtitle {
        color: #4b5563;
    }

    /* Arrow */
    .modern-button-arrow {
        opacity: 0.4;
        transition: all 0.3s;
    }

    .modern-action-button:hover .modern-button-arrow {
        opacity: 1;
        transform: translateX(4px);
    }

    .modern-button-arrow svg {
        width: 1.25rem;
        height: 1.25rem;
        color: #374151;
    }

    /* Size variants */
    .modern-button-sm {
        padding: 0.75rem;
        border-radius: 0.75rem;
    }

    .modern-button-md {
        padding: 1rem;
        border-radius: 1rem;
    }

    .modern-button-lg {
        padding: 1.5rem;
        border-radius: 1.5rem;
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

    /* Modern Statistics Card Styles - Adapted from React Component */
    .stat-card {
        position: relative;
        width: 100%;
        height: 224px; /* h-56 equivalent */
        border-radius: 1rem; /* rounded-2xl */
        overflow: hidden;
        padding: 1.5rem; /* p-6 */
        color: white;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        display: flex;
        align-items: flex-end;
        isolation: isolate;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        cursor: pointer;
    }

    .stat-card:hover {
        transform: scale(1.03);
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
    }

    /* Background Image & Overlay */
    .stat-card-bg {
        position: absolute;
        inset: 0;
        z-index: -1;
    }

    .stat-card-bg img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    /* Dark overlay for text readability */
    .stat-card-dark-overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.5); /* Dark overlay */
        z-index: -1;
    }

    .stat-card-overlay {
        position: absolute;
        inset: 0;
        background: rgba(59, 130, 246, 0.3); /* Lower brightness, reduced opacity */
        z-index: -1;
    }

    /* Card specific overlays - darker colors with lower brightness */
    .stat-card-subscriptions .stat-card-overlay {
        background: rgba(67, 56, 202, 0.25); /* Darker indigo, lower opacity */
    }

    .stat-card-revenue .stat-card-overlay {
        background: rgba(190, 24, 93, 0.25); /* Darker pink, lower opacity */
    }

    .stat-card-expiring .stat-card-overlay {
        background: rgba(217, 119, 6, 0.25); /* Darker yellow/orange, lower opacity */
    }

    .stat-card-payments .stat-card-overlay {
        background: rgba(37, 99, 235, 0.25); /* Darker blue, lower opacity */
    }

    /* Main Content Grid */
    .stat-card-content {
        width: 100%;
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 1rem;
        align-items: flex-end;
    }

    /* Left Section: Info */
    .stat-card-info {
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 100%;
    }

    .stat-card-title {
        font-size: 1.25rem; /* text-xl */
        font-weight: 700;
        line-height: 1.25;
        margin: 0 0 0.5rem 0;
        opacity: 0;
        animation: fadeInUp 0.6s ease forwards;
    }

    .stat-card-subtitle {
        font-size: 0.875rem; /* text-sm */
        margin: 0;
        opacity: 0;
        animation: fadeInUp 0.6s ease 0.1s forwards;
    }

    .stat-card-subtitle.visible {
        opacity: 0.8;
    }

    /* Right Section: Large Number */
    .stat-card-number {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .stat-card-number h1 {
        font-size: 6rem; /* text-8xl */
        font-weight: 700;
        letter-spacing: -0.05em;
        color: rgba(255, 255, 255, 0.9);
        user-select: none;
        margin: 0;
        opacity: 0;
        animation: fadeInUp 0.6s ease 0.2s forwards;
    }

    /* Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Top Left Icon */
    .stat-card-icon {
        position: absolute;
        top: 1.5rem;
        left: 1.5rem;
        font-size: 3.125rem; /* 50px */
        opacity: 0.3;
        z-index: 1;
        transition: opacity 0.3s ease, transform 0.3s ease;
    }

    .stat-card:hover .stat-card-icon {
        opacity: 0.5;
        transform: scale(1.1);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .stat-card {
            height: 200px;
            padding: 1.25rem;
        }

        .stat-card-number h1 {
            font-size: 4rem;
        }

        .stat-card-title {
            font-size: 1.125rem;
        }

        .stat-card-icon {
            font-size: 2.5rem;
            top: 1rem;
            left: 1rem;
        }
    }

</style>
@endpush

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Dashboard</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-dashboard-web"></i>
                    <span> - Dashboard</span>
                </div>
            </div>
            <p class="page-header-description">Complete system overview and management</p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row">
        {{-- Subscriptions Card --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="stat-card stat-card-subscriptions">
                <i class="icofont-tasks-alt stat-card-icon"></i>
                <div class="stat-card-bg">
                    <img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?w=900&auto=format&fit=crop&q=80" alt="Subscriptions">
                </div>
                <div class="stat-card-dark-overlay"></div>
                <div class="stat-card-overlay"></div>
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h2 class="stat-card-title">Active Subscriptions</h2>
                        <p class="stat-card-subtitle visible">Total: {{ $stats['total_subscriptions'] }}</p>
                    </div>
                    <div class="stat-card-number">
                        <h1>{{ $stats['active_subscriptions'] }}</h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- Revenue Card --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="stat-card stat-card-revenue">
                <i class="icofont-money stat-card-icon"></i>
                <div class="stat-card-bg">
                    <img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?w=900&auto=format&fit=crop&q=80" alt="Revenue">
                </div>
                <div class="stat-card-dark-overlay"></div>
                <div class="stat-card-overlay"></div>
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h2 class="stat-card-title">Monthly Revenue</h2>
                        <p class="stat-card-subtitle visible">Total: GHS {{ number_format($stats['total_revenue'], 2) }}</p>
                    </div>
                    <div class="stat-card-number">
                        <h1>@php
                            $revenue = $stats['monthly_revenue'];
                            if ($revenue >= 1000000) {
                                echo number_format($revenue / 1000000, 1) . 'M';
                            } elseif ($revenue >= 1000) {
                                echo number_format($revenue / 1000, 1) . 'K';
                            } else {
                                echo number_format($revenue, 0);
                            }
                        @endphp</h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- Expiring Soon Card --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="stat-card stat-card-expiring">
                <i class="icofont-warning-alt stat-card-icon"></i>
                <div class="stat-card-bg">
                    <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762423693/expiring-soon-phrase-written-on-260nw-1660836226_yryti0.jpg" alt="Expiring">
                </div>
                <div class="stat-card-dark-overlay"></div>
                <div class="stat-card-overlay"></div>
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h2 class="stat-card-title">Expiring Soon</h2>
                        <p class="stat-card-subtitle visible">Next 30 days</p>
                    </div>
                    <div class="stat-card-number">
                        <h1>{{ $stats['expiring_soon'] }}</h1>
                    </div>
                </div>
            </div>
        </div>

        {{-- Payments Card --}}
        <div class="col-xl-3 col-lg-6 col-md-6 col-12 mb-4">
            <div class="stat-card stat-card-payments">
                <i class="icofont-credit-card stat-card-icon"></i>
                <div class="stat-card-bg">
                    <img src="https://images.unsplash.com/photo-1563013544-824ae1b704d3?w=900&auto=format&fit=crop&q=80" alt="Payments">
                </div>
                <div class="stat-card-dark-overlay"></div>
                <div class="stat-card-overlay"></div>
                <div class="stat-card-content">
                    <div class="stat-card-info">
                        <h2 class="stat-card-title">Successful Payments</h2>
                        <p class="stat-card-subtitle visible">Pending: {{ $stats['pending_payments'] }}</p>
                    </div>
                    <div class="stat-card-number">
                        <h1>{{ $stats['successful_payments'] }}</h1>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <i class="icofont-lightning-ray"></i> Quick Actions
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('super-admin.subscriptions.create') }}" class="modern-action-button modern-button-md button-icon-primary">
                                <div class="modern-button-content">
                                    <div class="modern-button-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M5 12h14"></path>
                                            <path d="M12 5v14"></path>
                                        </svg>
                                    </div>
                                    <div class="modern-button-text">
                                        <p class="modern-button-title">New Subscription</p>
                                        <p class="modern-button-subtitle">Create new subscription</p>
                                    </div>
                                    <div class="modern-button-arrow">
                                        <svg viewBox="0 0 24 24" stroke="currentColor" fill="none">
                                            <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('super-admin.subscriptions.index') }}" class="modern-action-button modern-button-md button-icon-info">
                                <div class="modern-button-content">
                                    <div class="modern-button-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <line x1="8" y1="6" x2="21" y2="6"></line>
                                            <line x1="8" y1="12" x2="21" y2="12"></line>
                                            <line x1="8" y1="18" x2="21" y2="18"></line>
                                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                        </svg>
                                    </div>
                                    <div class="modern-button-text">
                                        <p class="modern-button-title">All Subscriptions</p>
                                        <p class="modern-button-subtitle">View all subscriptions</p>
                                    </div>
                                    <div class="modern-button-arrow">
                                        <svg viewBox="0 0 24 24" stroke="currentColor" fill="none">
                                            <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('super-admin.payments.index') }}" class="modern-action-button modern-button-md button-icon-success">
                                <div class="modern-button-content">
                                    <div class="modern-button-icon">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                                            <line x1="1" y1="10" x2="23" y2="10"></line>
                                        </svg>
                                    </div>
                                    <div class="modern-button-text">
                                        <p class="modern-button-title">Payments</p>
                                        <p class="modern-button-subtitle">Manage payments</p>
                                    </div>
                                    <div class="modern-button-arrow">
                                        <svg viewBox="0 0 24 24" stroke="currentColor" fill="none">
                                            <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                        <div class="col-md-3 mb-3">
                            <a href="{{ route('super-admin.settings.index') }}" class="modern-action-button modern-button-md button-icon-warning">
                                <div class="modern-button-content">
                                    <div class="modern-button-icon">
                                        <i class="icofont-settings-alt"></i>
                                    </div>
                                    <div class="modern-button-text">
                                        <p class="modern-button-title">Settings</p>
                                        <p class="modern-button-subtitle">System settings</p>
                                    </div>
                                    <div class="modern-button-arrow">
                                        <svg viewBox="0 0 24 24" stroke="currentColor" fill="none">
                                            <path d="M9 5l7 7-7 7" stroke-width="2" stroke-linejoin="round" stroke-linecap="round"></path>
                                        </svg>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerts Section --}}
    @if($expiringSubscriptions->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-warning">
                <h5><i class="icofont-warning"></i> Expiring Subscriptions Alert</h5>
                <p>{{ $expiringSubscriptions->count() }} subscription(s) expiring in the next 14 days:</p>
                <ul class="mb-0">
                    @foreach($expiringSubscriptions->take(5) as $sub)
                    <li>
                        <strong>{{ $sub->institution_name }}</strong> - 
                        Expires: {{ $sub->subscription_end_date->format('M d, Y') }} 
                        ({{ $sub->days_until_expiry }} days)
                        <a href="{{ route('super-admin.subscriptions.show', $sub->id) }}" class="btn btn-sm btn-warning ml-2">View</a>
                    </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    {{-- Main Content Grid --}}
    <div class="row">
        {{-- Recent Subscriptions --}}
        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <i class="icofont-list"></i> Recent Subscriptions
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentSubscriptions as $subscription)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid #eee;">
                        <div>
                            <h6 class="mb-1">{{ $subscription->institution_name }}</h6>
                            <small class="text-muted">
                                <span class="badge badge-{{ $subscription->status_badge_color }}">{{ ucfirst($subscription->status) }}</span>
                                {{ $subscription->subscription_plan }}
                            </small>
                        </div>
                        <div class="text-right">
                            <strong>{{ $subscription->formatted_renewal_amount }}</strong><br>
                            <small class="text-muted">{{ $subscription->subscription_end_date->format('M d, Y') }}</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No subscriptions yet</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Recent Payments --}}
        <div class="col-xl-6 col-lg-6 col-md-12 mb-4">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <i class="icofont-credit-card"></i> Recent Payments
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    @forelse($recentPayments as $payment)
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-3" style="border-bottom: 1px solid #eee;">
                        <div>
                            <h6 class="mb-1">{{ $payment->subscription->institution_name ?? 'N/A' }}</h6>
                            <small class="text-muted">
                                <span class="badge badge-{{ $payment->status_badge_color }}">{{ ucfirst($payment->status) }}</span>
                                {{ $payment->created_at->format('M d, Y H:i') }}
                            </small>
                        </div>
                        <div class="text-right">
                            <strong>{{ $payment->formatted_amount }}</strong><br>
                            <small class="text-muted">{{ $payment->payment_method_display }}</small>
                        </div>
                    </div>
                    @empty
                    <p class="text-muted text-center">No payments yet</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Upcoming Maintenance --}}
    @if($upcomingMaintenance->count() > 0)
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <i class="icofont-tools"></i> Upcoming Maintenance
                </div>
                <div class="card-body">
                    @foreach($upcomingMaintenance as $maintenance)
                    <div class="alert alert-info mb-2">
                        <strong>{{ $maintenance->title }}</strong><br>
                        <small>{{ $maintenance->scheduled_start->format('M d, Y H:i') }} - {{ $maintenance->scheduled_end->format('H:i') }}</small>
                        <span class="badge badge-{{ $maintenance->impact_badge_color }} ml-2">{{ ucfirst($maintenance->impact_level) }} Impact</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
