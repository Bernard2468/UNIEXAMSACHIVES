@php
    // Don't show subscription widget for super admins
    if (auth()->check() && auth()->user()->isSuperAdmin()) {
        $subscription = null;
    } else {
        $subscription = \App\Models\SystemSubscription::active()->first();
    if (!$subscription) {
        $subscription = \App\Models\SystemSubscription::where('status', 'expired')
            ->where(function($q) {
                $q->whereRaw('DATE_ADD(subscription_end_date, INTERVAL grace_period_days DAY) >= CURDATE()');
            })
            ->first();
    }
        if (!$subscription) {
            $subscription = \App\Models\SystemSubscription::latest()->first();
        }
    }
    
    // Determine status colors
    $statusColors = [
        'active' => ['bg' => '#10b981', 'light' => '#d1fae5', 'border' => '#34d399'],
        'expiring_soon' => ['bg' => '#f59e0b', 'light' => '#fef3c7', 'border' => '#fbbf24'],
        'expired' => ['bg' => '#ef4444', 'light' => '#fee2e2', 'border' => '#f87171'],
        'suspended' => ['bg' => '#dc2626', 'light' => '#fecaca', 'border' => '#ef4444']
    ];
    
    $currentStatus = $subscription ? $subscription->status : 'active';
    $colors = $statusColors[$currentStatus] ?? $statusColors['active'];
@endphp

@if($subscription)
<div class="modern-subscription-card mb-4">
    <div class="subscription-header" onclick="toggleSubscriptionCard()" style="cursor: pointer;">
        <div class="header-left">
            <div class="icon-wrapper" style="background: {{ $colors['light'] }}; border-color: {{ $colors['border'] }};">
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="{{ $colors['bg'] }}" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                    <line x1="16" x2="16" y1="2" y2="6"/>
                    <line x1="8" x2="8" y1="2" y2="6"/>
                    <line x1="3" x2="21" y1="10" y2="10"/>
                </svg>
            </div>
            <div class="header-text">
                <h3 class="subscription-title">Subscription Status</h3>
                <p class="subscription-subtitle">Monitor your account</p>
            </div>
        </div>
        <div class="header-right">
            <div class="status-badge" style="background: {{ $colors['light'] }}; border-color: {{ $colors['border'] }};">
                <span style="color: {{ $colors['bg'] }};">{{ ucfirst(str_replace('_', ' ', $subscription->status)) }}</span>
            </div>
            <button type="button" class="collapse-btn" aria-label="Toggle subscription details">
                <svg class="chevron-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </button>
        </div>
    </div>

    <div class="subscription-content" id="subscriptionContent">

    {{-- Status Alert Box --}}
    <div class="status-alert-box" style="background: {{ $colors['light'] }}; border-left-color: {{ $colors['bg'] }};">
        <div class="alert-icon" style="color: {{ $colors['bg'] }};">
            @if($subscription->status === 'active')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
            @elseif($subscription->status === 'expiring_soon')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                    <line x1="12" x2="12" y1="9" y2="13"/>
                    <line x1="12" x2="12.01" y1="17" y2="17"/>
                </svg>
            @elseif($subscription->status === 'expired' && $subscription->is_in_grace_period)
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" x2="12" y1="8" y2="12"/>
                    <line x1="12" x2="12.01" y1="16" y2="16"/>
                </svg>
            @elseif($subscription->status === 'suspended')
                <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="4.93" x2="19.07" y1="4.93" y2="19.07"/>
                </svg>
            @endif
        </div>
        <div class="alert-content">
            @if($subscription->status === 'active')
                <h4 style="color: {{ $colors['bg'] }};">All Systems Active</h4>
                <p>Your subscription is active and all features are available.</p>
            @elseif($subscription->status === 'expiring_soon')
                <h4 style="color: {{ $colors['bg'] }};">Action Required</h4>
                <p>Your subscription expires in <strong>{{ $subscription->days_until_expiry }} {{ $subscription->days_until_expiry === 1 ? 'day' : 'days' }}</strong>. Renew now to avoid interruption.</p>
            @elseif($subscription->status === 'expired' && $subscription->is_in_grace_period)
                <h4 style="color: {{ $colors['bg'] }};">Grace Period Active</h4>
                <p>Your subscription has expired. You have <strong>{{ $subscription->grace_period_days }} days</strong> grace period remaining.</p>
            @elseif($subscription->status === 'suspended')
                <h4 style="color: {{ $colors['bg'] }};">Account Suspended</h4>
                <p>Your subscription has been suspended. Please renew immediately to restore access.</p>
            @endif
        </div>
    </div>

    {{-- Subscription Details Grid --}}
    <div class="subscription-details-grid">
        <div class="detail-item">
            <div class="detail-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10"/>
                    <path d="m15 9-6 6"/>
                    <path d="m9 9 6 6"/>
                </svg>
            </div>
            <div class="detail-content">
                <span class="detail-label">Plan Type</span>
                <span class="detail-value">{{ ucfirst($subscription->subscription_plan) }}</span>
            </div>
        </div>
        
        <div class="detail-item">
            <div class="detail-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/>
                    <path d="M21 3v5h-5"/>
                </svg>
            </div>
            <div class="detail-content">
                <span class="detail-label">Renewal Cycle</span>
                <span class="detail-value">{{ ucfirst(str_replace('_', ' ', $subscription->renewal_cycle)) }}</span>
            </div>
        </div>
        
        <div class="detail-item">
            <div class="detail-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M8 2v4"/>
                    <path d="M16 2v4"/>
                    <rect width="18" height="18" x="3" y="4" rx="2"/>
                    <path d="M3 10h18"/>
                </svg>
            </div>
            <div class="detail-content">
                <span class="detail-label">Expires On</span>
                <span class="detail-value">{{ $subscription->subscription_end_date->format('M d, Y') }}</span>
            </div>
        </div>
        
        <div class="detail-item">
            <div class="detail-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" x2="12" y1="2" y2="22"/>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                </svg>
            </div>
            <div class="detail-content">
                <span class="detail-label">Renewal Amount</span>
                <span class="detail-value">{{ $subscription->formatted_renewal_amount }}</span>
            </div>
        </div>
    </div>

    {{-- Progress Section --}}
    @if($subscription->status !== 'suspended')
    @php
        // Calculate progress based on remaining time (not elapsed)
        // Full bar (100%) when subscription is new, empty (0%) when about to expire
        $totalDays = $subscription->subscription_start_date->diffInDays($subscription->subscription_end_date);
        $now = now();
        $daysRemaining = $now->isBefore($subscription->subscription_end_date) 
            ? $now->diffInDays($subscription->subscription_end_date) 
            : 0;
        $progress = $totalDays > 0 ? max(0, min(100, ($daysRemaining / $totalDays) * 100)) : 0;
        
        // Color based on remaining time (reverse of elapsed)
        // Green when plenty of time left, red when about to expire
        $progressColor = $progress < 10 ? '#ef4444' : ($progress < 25 ? '#f59e0b' : '#10b981');
    @endphp
    <div class="progress-section">
        <div class="progress-header">
            <span class="progress-label">Subscription Period</span>
            <span class="progress-percentage">{{ number_format($progress, 1) }}% remaining</span>
        </div>
        <div class="modern-progress-bar">
            <div class="progress-fill" style="width: {{ $progress }}%; background: {{ $progressColor }};"></div>
        </div>
        <div class="progress-dates">
            <span class="date-text">{{ $subscription->subscription_start_date->format('M d, Y') }}</span>
            <span class="date-text">{{ $subscription->subscription_end_date->format('M d, Y') }}</span>
        </div>
    </div>
    @endif

    {{-- Action Buttons --}}
    {{-- Renew actions: only for Admin (is_admin=0). Same logic as super-admin roles page. --}}
    <div class="subscription-actions">
        <div class="actions-group">
            @if($subscription->status === 'active' || $subscription->status === 'expiring_soon')
                @if(auth()->user()->is_admin == 0 && !auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('admin.subscriptions.renew', $subscription->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="modern-btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/>
                            <path d="M21 3v5h-5"/>
                        </svg>
                        <span>Renew Now</span>
                    </button>
                </form>
                @endif
            @elseif($subscription->status === 'expired' || $subscription->status === 'suspended')
                @if(auth()->user()->is_admin == 0 && !auth()->user()->isSuperAdmin())
                <form method="POST" action="{{ route('admin.subscriptions.renew', $subscription->id) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="modern-btn btn-danger">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 1 1-9-9c2.52 0 4.93 1 6.74 2.74L21 8"/>
                            <path d="M21 3v5h-5"/>
                        </svg>
                        <span>Renew Immediately</span>
                    </button>
                </form>
                @else
                <div class="contact-admin-notice">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M12 16v-4"/>
                        <path d="M12 8h.01"/>
                    </svg>
                    <span>Please contact your administrator to renew the subscription.</span>
                </div>
                @endif
            @endif
            
            @if(auth()->user()->isRegularUser())
                <div class="action-separator"></div>
                <a href="{{ route('dashboard.payment-history.index') }}" class="modern-btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>
                        <line x1="1" y1="10" x2="23" y2="10"></line>
                    </svg>
                    <span>View All Payment History</span>
                </a>
            @endif
        </div>
    </div>

    {{-- Auto-Renewal Badge --}}
    @if($subscription->auto_renewal)
    <div class="auto-renewal-badge">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
            <polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <span>Auto-renewal enabled</span>
    </div>
    @endif
    
    </div> {{-- End subscription-content --}}
</div>
@endif

<style>
/* Modern Subscription Card Styles */
.modern-subscription-card {
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    background: #ffffff;
    border-radius: 16px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04), 0 1px 2px rgba(0, 0, 0, 0.06);
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid #e9ecef;
}

.modern-subscription-card:hover {
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06), 0 4px 8px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

/* Header Section */
.subscription-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #f0f1f3;
    background: #fafbfc;
    transition: background 0.2s ease;
}

.subscription-header:hover {
    background: #f8f9fa;
}

.header-left {
    display: flex;
    align-items: center;
    gap: 16px;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 16px;
}

.icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid;
    transition: all 0.3s ease;
}

.icon-wrapper i {
    font-size: 28px;
}

.modern-subscription-card:hover .icon-wrapper {
    transform: scale(1.05);
}

.header-text {
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.subscription-title {
    margin: 0;
    font-size: 20px;
    font-weight: 700;
    color: #212529;
    letter-spacing: -0.025em;
}

.subscription-subtitle {
    margin: 0;
    font-size: 14px;
    color: #6c757d;
    font-weight: 400;
}

.status-badge {
    padding: 10px 20px;
    border-radius: 12px;
    border: 2px solid;
    font-size: 14px;
    font-weight: 600;
    letter-spacing: 0.025em;
    transition: all 0.3s ease;
}

.status-badge:hover {
    transform: scale(1.05);
}

/* Collapse Button */
.collapse-btn {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: #ffffff;
    border: 1px solid #dee2e6;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 0;
    color: #6c757d;
}

.collapse-btn:hover {
    background: #f8f9fa;
    border-color: #ced4da;
    transform: scale(1.05);
}

.collapse-btn:active {
    transform: scale(0.95);
}

.chevron-icon {
    transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modern-subscription-card.collapsed .chevron-icon {
    transform: rotate(-180deg);
}

/* Collapsible Content */
.subscription-content {
    max-height: 2000px;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.3s ease;
    opacity: 1;
}

.modern-subscription-card.collapsed .subscription-content {
    max-height: 0;
    opacity: 0;
}

/* Status Alert Box */
.status-alert-box {
    margin: 24px;
    padding: 20px;
    border-radius: 12px;
    border-left: 4px solid;
    display: flex;
    gap: 16px;
    align-items: flex-start;
    transition: all 0.3s ease;
}

.status-alert-box:hover {
    transform: translateX(4px);
}

.alert-icon {
    font-size: 28px;
    line-height: 1;
    margin-top: 2px;
}

.alert-content {
    flex: 1;
}

.alert-content h4 {
    margin: 0 0 6px 0;
    font-size: 16px;
    font-weight: 700;
    letter-spacing: -0.025em;
}

.alert-content p {
    margin: 0;
    font-size: 14px;
    color: #495057;
    line-height: 1.6;
}

/* Subscription Details Grid */
.subscription-details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
    margin: 0 24px;
}

.detail-item {
    background: #fafbfc;
    padding: 20px;
    display: flex;
    gap: 16px;
    align-items: center;
    transition: all 0.3s ease;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.detail-item:hover {
    background: #ffffff;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
    transform: translateY(-2px);
}

.detail-icon {
    width: 44px;
    height: 44px;
    background: #f8f9fa;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 22px;
    color: #6c757d;
    border: 1px solid #e9ecef;
}

.detail-content {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.detail-label {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.detail-value {
    font-size: 15px;
    color: #212529;
    font-weight: 600;
}

/* Progress Section */
.progress-section {
    padding: 24px;
    background: #fafbfc;
    margin: 24px;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.progress-label {
    font-size: 14px;
    color: #495057;
    font-weight: 600;
}

.progress-percentage {
    font-size: 14px;
    color: #6c757d;
    font-weight: 600;
}

.modern-progress-bar {
    height: 12px;
    background: #e9ecef;
    border-radius: 12px;
    overflow: hidden;
    position: relative;
}

.progress-fill {
    height: 100%;
    border-radius: 12px;
    transition: width 1s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
    overflow: hidden;
}

.progress-fill::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
    animation: shimmer 2s infinite;
}

@keyframes shimmer {
    0% { transform: translateX(-100%); }
    100% { transform: translateX(100%); }
}

.progress-dates {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
}

.date-text {
    font-size: 12px;
    color: #6c757d;
    font-weight: 500;
}

/* Action Buttons */
.subscription-actions {
    padding: 24px;
    border-top: 1px solid #f0f1f3;
    background: #fafbfc;
}

.actions-group {
    display: flex;
    align-items: center;
    gap: 12px;
    flex-wrap: wrap;
}

.action-separator {
    width: 1px;
    height: 32px;
    background: #dee2e6;
    margin: 0 4px;
    flex-shrink: 0;
}

.modern-btn {
    flex: 0 0 auto;
    padding: 14px 24px;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    letter-spacing: 0.025em;
}

.modern-btn i {
    font-size: 18px;
}

.btn-primary {
    background: #3b82f6;
    color: #ffffff;
}

.btn-primary:hover {
    background: #2563eb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.25);
    color: #ffffff;
}

.btn-secondary {
    background: #ffffff;
    color: #495057;
    border: 1px solid #dee2e6;
}

.btn-secondary:hover {
    background: #f8f9fa;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    color: #212529;
    border-color: #ced4da;
}

.btn-danger {
    background: #ef4444;
    color: #ffffff;
}

.btn-danger:hover {
    background: #dc2626;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
    color: #ffffff;
}

.btn-block {
    width: 100%;
}

.contact-admin-notice {
    width: 100%;
    padding: 16px;
    background: #fef2f2;
    border-radius: 10px;
    color: #991b1b;
    display: flex;
    align-items: center;
    gap: 12px;
    font-size: 14px;
}

.contact-admin-notice i {
    font-size: 20px;
}

/* Auto-Renewal Badge */
.auto-renewal-badge {
    margin: 0 24px 24px;
    padding: 12px 16px;
    background: #d1fae5;
    border-radius: 10px;
    color: #065f46;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    font-weight: 600;
}

.auto-renewal-badge i {
    font-size: 16px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .subscription-header {
        padding: 20px;
    }
    
    .header-left {
        flex: 1;
    }
    
    .header-right {
        gap: 12px;
    }
    
    .subscription-details-grid {
        grid-template-columns: 1fr;
    }
    
    .subscription-actions {
        padding: 20px;
    }
    
    .actions-group {
        flex-direction: column;
        align-items: stretch;
    }
    
    .action-separator {
        width: 100%;
        height: 1px;
        margin: 8px 0;
    }
    
    .modern-btn {
        width: 100%;
    }
    
    .icon-wrapper {
        width: 48px;
        height: 48px;
    }
    
    .icon-wrapper svg {
        width: 24px;
        height: 24px;
    }
    
    .subscription-title {
        font-size: 18px;
    }
    
    .subscription-subtitle {
        font-size: 13px;
    }
}
</style>

<script>
function toggleSubscriptionCard() {
    const card = document.querySelector('.modern-subscription-card');
    const isCollapsed = card.classList.contains('collapsed');
    
    if (isCollapsed) {
        card.classList.remove('collapsed');
        // Save state to localStorage
        localStorage.setItem('subscriptionCardCollapsed', 'false');
    } else {
        card.classList.add('collapsed');
        // Save state to localStorage
        localStorage.setItem('subscriptionCardCollapsed', 'true');
    }
}

// Restore collapsed state on page load
document.addEventListener('DOMContentLoaded', function() {
    const card = document.querySelector('.modern-subscription-card');
    const isCollapsed = localStorage.getItem('subscriptionCardCollapsed') === 'true';
    
    if (isCollapsed && card) {
        card.classList.add('collapsed');
    }
});
</script>

