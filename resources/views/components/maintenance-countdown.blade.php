@php
    // Don't show for super admins
    if (auth()->check() && auth()->user()->isSuperAdmin()) {
        $upcomingMaintenance = null;
    } else {
        // Get upcoming maintenance that should display banner
        $upcomingMaintenance = \App\Models\SystemMaintenanceLog::where('display_banner', true)
            ->whereIn('status', ['planned', 'notified'])
            ->where('scheduled_start', '>', now())
            ->where(function($query) {
                $query->whereNull('banner_display_from')
                      ->orWhere('banner_display_from', '<=', now());
            })
            ->where(function($query) {
                $query->whereNull('banner_display_until')
                      ->orWhere('banner_display_until', '>=', now());
            })
            ->orderBy('scheduled_start', 'asc')
            ->first();
    }
@endphp

@if($upcomingMaintenance)
<!-- Maintenance Countdown in Navbar (injected into header) -->
<div id="maintenance-navbar-countdown" 
     class="maintenance-navbar-countdown maintenance-impact-{{ $upcomingMaintenance->impact_level }}" 
     data-start-time="{{ $upcomingMaintenance->scheduled_start->timestamp * 1000 }}"
     data-maintenance-id="{{ $upcomingMaintenance->id }}"
     style="display: none;">
    <div class="maintenance-nav-item">
        <div class="maintenance-nav-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
            </svg>
        </div>
        <div class="maintenance-nav-countdown" id="navbar-countdown-display">
            <span class="maintenance-nav-label">Maintenance:</span>
            <span id="navbar-days">00</span>d
            <span id="navbar-hours">00</span>h
            <span id="navbar-minutes">00</span>m
            <span id="navbar-seconds">00</span>s
        </div>
        <button class="maintenance-nav-close" onclick="dismissNavbarCountdown()" title="Dismiss">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>
    </div>
</div>

<!-- Maintenance Countdown Modal -->
<div id="maintenance-countdown-modal" class="maintenance-modal" data-start-time="{{ $upcomingMaintenance->scheduled_start->timestamp * 1000 }}">
    <div class="maintenance-modal-overlay"></div>
    <div class="maintenance-modal-content maintenance-impact-{{ $upcomingMaintenance->impact_level }}">
        <button class="maintenance-modal-close">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <line x1="18" y1="6" x2="6" y2="18"></line>
                <line x1="6" y1="6" x2="18" y2="18"></line>
            </svg>
        </button>

        <div class="maintenance-modal-header">
            <div class="maintenance-modal-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                </svg>
            </div>
            <h2 class="maintenance-modal-title">Scheduled Maintenance</h2>
            <p class="maintenance-modal-subtitle">System maintenance is scheduled</p>
        </div>

        <div class="maintenance-modal-body">
            <div class="maintenance-info-card">
                <div class="maintenance-info-item">
                    <span class="maintenance-info-label">Maintenance Title</span>
                    <span class="maintenance-info-value">{{ $upcomingMaintenance->title }}</span>
                </div>
                
                @if($upcomingMaintenance->banner_message ?? $upcomingMaintenance->description)
                <div class="maintenance-info-item">
                    <span class="maintenance-info-label">Description</span>
                    <span class="maintenance-info-value">{{ $upcomingMaintenance->banner_message ?? $upcomingMaintenance->description }}</span>
                </div>
                @endif

                <div class="maintenance-info-row">
                    <div class="maintenance-info-item">
                        <span class="maintenance-info-label">Start</span>
                        <span class="maintenance-info-value">{{ $upcomingMaintenance->scheduled_start->format('M d, Y') }}<br>{{ $upcomingMaintenance->scheduled_start->format('h:i A') }}</span>
                    </div>
                    <div class="maintenance-info-item">
                        <span class="maintenance-info-label">End</span>
                        <span class="maintenance-info-value">{{ $upcomingMaintenance->scheduled_end->format('M d, Y') }}<br>{{ $upcomingMaintenance->scheduled_end->format('h:i A') }}</span>
                    </div>
                </div>

                <div class="maintenance-info-item" style="margin-top: 0.5rem;">
                    <span class="maintenance-info-label">Impact</span>
                    <span class="maintenance-info-badge maintenance-impact-badge-{{ $upcomingMaintenance->impact_level }}">
                        {{ ucfirst($upcomingMaintenance->impact_level) }}
                    </span>
                </div>
            </div>

            <div class="maintenance-countdown-section">
                <h3 class="countdown-title">Time Until Maintenance</h3>
                <div class="countdown-container">
                    <!-- Days - Tens -->
                    <div class="countdown-group">
                        <div class="countdown-label">Days</div>
                        <div class="countdown-wrapper">
                            <div class="nums nums-ten" id="days-tens">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                            <div class="nums nums-one" id="days-ones">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                        </div>
                    </div>

                    <div class="countdown-separator">:</div>

                    <!-- Hours - Tens -->
                    <div class="countdown-group">
                        <div class="countdown-label">Hours</div>
                        <div class="countdown-wrapper">
                            <div class="nums nums-ten" id="hours-tens">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                            <div class="nums nums-one" id="hours-ones">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                        </div>
                    </div>

                    <div class="countdown-separator">:</div>

                    <!-- Minutes - Tens -->
                    <div class="countdown-group">
                        <div class="countdown-label">Minutes</div>
                        <div class="countdown-wrapper">
                            <div class="nums nums-ten" id="minutes-tens">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                            <div class="nums nums-one" id="minutes-ones">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                        </div>
                    </div>

                    <div class="countdown-separator">:</div>

                    <!-- Seconds - Tens -->
                    <div class="countdown-group">
                        <div class="countdown-label">Seconds</div>
                        <div class="countdown-wrapper">
                            <div class="nums nums-ten" id="seconds-tens">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                            <div class="nums nums-one" id="seconds-ones">
                                <div class="num" data-num="0" data-num-next="1"></div>
                                <div class="num" data-num="1" data-num-next="2"></div>
                                <div class="num" data-num="2" data-num-next="3"></div>
                                <div class="num" data-num="3" data-num-next="4"></div>
                                <div class="num" data-num="4" data-num-next="5"></div>
                                <div class="num" data-num="5" data-num-next="6"></div>
                                <div class="num" data-num="6" data-num-next="7"></div>
                                <div class="num" data-num="7" data-num-next="8"></div>
                                <div class="num" data-num="8" data-num-next="9"></div>
                                <div class="num" data-num="9" data-num-next="0"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="maintenance-modal-footer">
            <button class="maintenance-modal-button" onclick="closeMaintenanceModal()">
                Got it, I understand
            </button>
        </div>
    </div>
</div>

<style>
    /* Navbar Countdown Styles - Integrated into existing navbar */
    .maintenance-navbar-countdown {
        display: none;
    }

    .maintenance-nav-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 0.75rem;
        background: rgba(245, 158, 11, 0.1);
        border: 1px solid rgba(245, 158, 11, 0.3);
        border-radius: 8px;
        font-size: 0.8125rem;
        white-space: nowrap;
        animation: fadeIn 0.3s ease-out;
    }

    .maintenance-impact-low .maintenance-nav-item {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.3);
    }

    .maintenance-impact-medium .maintenance-nav-item {
        background: rgba(245, 158, 11, 0.1);
        border-color: rgba(245, 158, 11, 0.3);
    }

    .maintenance-impact-high .maintenance-nav-item {
        background: rgba(249, 115, 22, 0.1);
        border-color: rgba(249, 115, 22, 0.3);
    }

    .maintenance-impact-critical .maintenance-nav-item {
        background: rgba(239, 68, 68, 0.1);
        border-color: rgba(239, 68, 68, 0.3);
    }

    .maintenance-nav-icon {
        flex-shrink: 0;
        color: #f59e0b;
        display: flex;
        align-items: center;
    }

    .maintenance-impact-low .maintenance-nav-icon {
        color: #3b82f6;
    }

    .maintenance-impact-high .maintenance-nav-icon {
        color: #f97316;
    }

    .maintenance-impact-critical .maintenance-nav-icon {
        color: #ef4444;
    }

    .maintenance-nav-countdown {
        display: flex;
        align-items: center;
        gap: 0.25rem;
        font-weight: 600;
        color: #78350f;
    }

    .maintenance-impact-low .maintenance-nav-countdown {
        color: #1e40af;
    }

    .maintenance-impact-high .maintenance-nav-countdown {
        color: #9a3412;
    }

    .maintenance-impact-critical .maintenance-nav-countdown {
        color: #991b1b;
    }

    .maintenance-nav-label {
        font-size: 0.75rem;
        font-weight: 500;
        margin-right: 0.25rem;
    }

    .maintenance-nav-countdown span:not(.maintenance-nav-label) {
        font-weight: 700;
        min-width: 1.5rem;
        text-align: center;
    }

    .maintenance-nav-close {
        flex-shrink: 0;
        background: transparent;
        border: none;
        border-radius: 4px;
        width: 1.25rem;
        height: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #78350f;
        opacity: 0.6;
        padding: 0;
        margin-left: 0.25rem;
    }

    .maintenance-nav-close:hover {
        opacity: 1;
        background: rgba(0, 0, 0, 0.05);
    }

    .maintenance-impact-low .maintenance-nav-close {
        color: #1e40af;
    }

    .maintenance-impact-high .maintenance-nav-close {
        color: #9a3412;
    }

    .maintenance-impact-critical .maintenance-nav-close {
        color: #991b1b;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    /* Responsive adjustments for navbar countdown */
    @media (max-width: 991px) {
        .maintenance-nav-item {
            padding: 0.375rem 0.5rem;
            font-size: 0.75rem;
        }

        .maintenance-nav-label {
            display: none;
        }

        .maintenance-nav-countdown span:not(.maintenance-nav-label) {
            min-width: 1.25rem;
        }
    }

    /* Modal Styles */
    .maintenance-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 99999;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        visibility: hidden;
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        padding: 20px;
    }

    .maintenance-modal.show {
        opacity: 1;
        visibility: visible;
    }

    .maintenance-modal-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    }

    .maintenance-modal-content {
        position: relative;
        background: white;
        border-radius: 20px;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
        max-width: 750px;
        width: 100%;
        max-height: 85vh;
        overflow-y: auto;
        transform: scale(0.9) translateY(20px);
        transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }

    .maintenance-modal.show .maintenance-modal-content {
        transform: scale(1) translateY(0);
    }

    .maintenance-modal-close {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: rgba(0, 0, 0, 0.05);
        border: none;
        border-radius: 50%;
        width: 2.5rem;
        height: 2.5rem;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #6b7280;
        z-index: 10;
    }

    .maintenance-modal-close:hover {
        background: rgba(0, 0, 0, 0.1);
        transform: scale(1.1);
    }

    .maintenance-modal-header {
        text-align: center;
        padding: 1.75rem 1.5rem 1.25rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .maintenance-modal-icon {
        width: 3rem;
        height: 3rem;
        margin: 0 auto 0.75rem;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
        color: #dc2626;
    }

    .maintenance-impact-low .maintenance-modal-icon {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.1), rgba(37, 99, 235, 0.05));
        color: #2563eb;
    }

    .maintenance-impact-medium .maintenance-modal-icon {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
        color: #d97706;
    }

    .maintenance-impact-high .maintenance-modal-icon {
        background: linear-gradient(135deg, rgba(249, 115, 22, 0.1), rgba(234, 88, 12, 0.05));
        color: #ea580c;
    }

    .maintenance-impact-critical .maintenance-modal-icon {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
        color: #dc2626;
    }

    .maintenance-modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 0.375rem;
    }

    .maintenance-modal-subtitle {
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .maintenance-modal-body {
        padding: 1.5rem;
    }

    /* Info Card */
    .maintenance-info-card {
        background: #f9fafb;
        border-radius: 0.75rem;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e5e7eb;
    }

    .maintenance-info-item {
        display: flex;
        flex-direction: column;
        gap: 0.375rem;
        margin-bottom: 0.875rem;
    }

    .maintenance-info-item:last-child {
        margin-bottom: 0;
    }

    .maintenance-info-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.875rem;
        margin-bottom: 0.875rem;
    }

    .maintenance-info-label {
        font-size: 0.6875rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    .maintenance-info-value {
        font-size: 0.875rem;
        color: #1f2937;
        font-weight: 500;
        line-height: 1.5;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    .maintenance-info-badge {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    .maintenance-impact-badge-low {
        background: #dbeafe;
        color: #1e40af;
    }

    .maintenance-impact-badge-medium {
        background: #fef3c7;
        color: #92400e;
    }

    .maintenance-impact-badge-high {
        background: #fed7aa;
        color: #9a3412;
    }

    .maintenance-impact-badge-critical {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Countdown Section */
    .maintenance-countdown-section {
        text-align: center;
    }

    .countdown-title {
        font-size: 1rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.25rem;
    }

    .countdown-container {
        height: 100px;
        position: relative;
        text-align: center;
        display: flex;
        gap: 8px;
        justify-content: center;
        align-items: center;
        flex-wrap: wrap;
    }

    .countdown-group {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 0.375rem;
    }

    .countdown-label {
        font-size: 0.625rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .countdown-wrapper {
        display: flex;
        gap: 3px;
    }

    .countdown-separator {
        font-size: 1rem;
        font-weight: 700;
        color: #1f2937;
        align-self: flex-end;
        padding-bottom: 28px;
    }

    /* Simple Countdown Timer Styles */
    .nums {
        display: inline-block;
        height: 100px;
        position: relative;
        width: 65px;
    }

    .num {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        font-size: 65px;
        font-weight: bold;
        color: #eeeeee;
        border-radius: 10px;
        background: linear-gradient(to bottom, #181818 0%, #222 50%, #2a2a2a 50%, #1a1a1a 100%);
        box-shadow: 0 3px 10px rgba(0, 0, 0, 0.5);
        text-shadow: 0 1px 2px #000;
        display: flex;
        justify-content: center;
        align-items: center;
        transition: opacity 0.3s ease;
    }

    /* Middle divider line */
    .num::before {
        content: "";
        position: absolute;
        top: 50%;
        left: 0;
        width: 100%;
        height: 2px;
        background: #000;
        z-index: 5;
        transform: translateY(-1px);
        box-shadow: 0 1px 1px rgba(0, 0, 0, 0.5);
    }

    /* Inner shadow effects for depth */
    .num::after {
        content: "";
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        border-radius: 10px;
        box-shadow: inset 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 -10px 30px rgba(0, 0, 0, 0.4);
        pointer-events: none;
    }

    /* Active/Hidden state for JavaScript control */
    .num.active {
        display: flex;
        z-index: 10;
        opacity: 1;
    }

    .num.hidden {
        display: none;
        opacity: 0;
    }

    /* Number display */
    .num .number {
        position: relative;
        z-index: 2;
    }

    /* Modal Footer */
    .maintenance-modal-footer {
        padding: 1.25rem 1.5rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        text-align: center;
    }

    .maintenance-modal-button {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.05));
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.2);
        padding: 0.625rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s;
    }

    .maintenance-modal-button:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.1));
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .maintenance-modal-content {
            max-width: 95%;
            border-radius: 16px;
        }

        .maintenance-modal-header {
            padding: 1.5rem 1.25rem 1rem;
        }

        .maintenance-modal-body {
            padding: 1.25rem;
        }

        .maintenance-info-row {
            grid-template-columns: 1fr;
        }

        .countdown-container {
            gap: 6px;
            height: 85px;
        }

        .nums {
            width: 55px;
            height: 85px;
        }

        .num {
            font-size: 55px;
        }

        .countdown-separator {
            font-size: 0.875rem;
            padding-bottom: 24px;
        }

        .maintenance-header-content {
            padding: 0.625rem 1rem;
            flex-wrap: wrap;
        }

        .maintenance-header-countdown {
            font-size: 0.75rem;
        }
    }
</style>

<script>
    (function() {
        const modal = document.getElementById('maintenance-countdown-modal');
        const navbarCountdown = document.getElementById('maintenance-navbar-countdown');
        if (!modal) return;

        const startTime = parseInt(modal.dataset.startTime);
        const maintenanceId = {{ $upcomingMaintenance->id }};
        let countdownInterval;
        let navbarCountdownInterval;

        // Check if modal was dismissed
        const modalDismissed = localStorage.getItem('maintenance-modal-dismissed-' + maintenanceId) === 'true';
        const navbarDismissed = localStorage.getItem('maintenance-navbar-dismissed-' + maintenanceId) === 'true';

        // Show navbar countdown if modal was dismissed and navbar not dismissed
        if (modalDismissed && !navbarDismissed && navbarCountdown) {
            showNavbarCountdown();
        }

        // Show modal on page load if not dismissed
        if (!modalDismissed) {
            setTimeout(() => {
                modal.classList.add('show');
                document.body.style.overflow = 'hidden';
            }, 500);
        } else {
            // Ensure body scroll is enabled if modal is dismissed
            document.body.style.overflow = '';
        }

        // Close modal function
        window.closeMaintenanceModal = function() {
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
                document.body.style.overflow = '';
                // Store in localStorage to not show again this session
                localStorage.setItem('maintenance-modal-dismissed-' + maintenanceId, 'true');
                // Show navbar countdown
                if (!navbarDismissed) {
                    showNavbarCountdown();
                }
            }, 300);
        };

        // Show navbar countdown function
        function showNavbarCountdown() {
            if (navbarCountdown && !navbarDismissed) {
                // Find the navbar right section and inject countdown
                const navRight = document.querySelector('.uda-nav-right');
                if (navRight) {
                    // Insert before notifications/logout button
                    const firstChild = navRight.firstElementChild;
                    if (firstChild) {
                        navRight.insertBefore(navbarCountdown, firstChild);
                    } else {
                        navRight.appendChild(navbarCountdown);
                    }
                    navbarCountdown.style.display = 'block';
                    startNavbarCountdown();
                }
            }
        }

        // Dismiss navbar countdown function
        window.dismissNavbarCountdown = function() {
            if (navbarCountdown) {
                navbarCountdown.style.display = 'none';
                localStorage.setItem('maintenance-navbar-dismissed-' + maintenanceId, 'true');
                if (navbarCountdownInterval) {
                    clearInterval(navbarCountdownInterval);
                }
            }
        };

        // Close button
        const closeBtn = modal.querySelector('.maintenance-modal-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', closeMaintenanceModal);
        }

        // Close on overlay click
        const overlay = modal.querySelector('.maintenance-modal-overlay');
        if (overlay) {
            overlay.addEventListener('click', closeMaintenanceModal);
        }

        // Store current values to detect changes
        let currentValues = {
            daysTens: -1,
            daysOnes: -1,
            hoursTens: -1,
            hoursOnes: -1,
            minutesTens: -1,
            minutesOnes: -1,
            secondsTens: -1,
            secondsOnes: -1
        };

        // Function to update flip countdown
        function updateFlipCountdown() {
            const now = new Date().getTime();
            const distance = startTime - now;

            if (distance < 0) {
                // Maintenance has started
                if (modal && modal.classList.contains('show')) {
                    closeMaintenanceModal();
                }
                if (navbarCountdown && navbarCountdown.style.display !== 'none') {
                    dismissNavbarCountdown();
                }
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Update each digit and trigger flip if changed
            const daysTens = Math.floor(days / 10);
            const daysOnes = days % 10;
            const hoursTens = Math.floor(hours / 10);
            const hoursOnes = hours % 10;
            const minutesTens = Math.floor(minutes / 10);
            const minutesOnes = minutes % 10;
            const secondsTens = Math.floor(seconds / 10);
            const secondsOnes = seconds % 10;

            if (currentValues.daysTens !== daysTens) {
                updateFlipDigit('days-tens', daysTens);
                currentValues.daysTens = daysTens;
            }
            if (currentValues.daysOnes !== daysOnes) {
                updateFlipDigit('days-ones', daysOnes);
                currentValues.daysOnes = daysOnes;
            }
            if (currentValues.hoursTens !== hoursTens) {
                updateFlipDigit('hours-tens', hoursTens);
                currentValues.hoursTens = hoursTens;
            }
            if (currentValues.hoursOnes !== hoursOnes) {
                updateFlipDigit('hours-ones', hoursOnes);
                currentValues.hoursOnes = hoursOnes;
            }
            if (currentValues.minutesTens !== minutesTens) {
                updateFlipDigit('minutes-tens', minutesTens);
                currentValues.minutesTens = minutesTens;
            }
            if (currentValues.minutesOnes !== minutesOnes) {
                updateFlipDigit('minutes-ones', minutesOnes);
                currentValues.minutesOnes = minutesOnes;
            }
            if (currentValues.secondsTens !== secondsTens) {
                updateFlipDigit('seconds-tens', secondsTens);
                currentValues.secondsTens = secondsTens;
            }
            if (currentValues.secondsOnes !== secondsOnes) {
                updateFlipDigit('seconds-ones', secondsOnes);
                currentValues.secondsOnes = secondsOnes;
            }
        }

        // Function to update navbar countdown
        function updateNavbarCountdown() {
            const now = new Date().getTime();
            const distance = startTime - now;

            if (distance < 0) {
                dismissNavbarCountdown();
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            const daysEl = document.getElementById('navbar-days');
            const hoursEl = document.getElementById('navbar-hours');
            const minutesEl = document.getElementById('navbar-minutes');
            const secondsEl = document.getElementById('navbar-seconds');

            if (daysEl) daysEl.textContent = String(days).padStart(2, '0');
            if (hoursEl) hoursEl.textContent = String(hours).padStart(2, '0');
            if (minutesEl) minutesEl.textContent = String(minutes).padStart(2, '0');
            if (secondsEl) secondsEl.textContent = String(seconds).padStart(2, '0');
        }

        // Start navbar countdown
        function startNavbarCountdown() {
            updateNavbarCountdown();
            navbarCountdownInterval = setInterval(updateNavbarCountdown, 1000);
        }

        // Function to update digit - simplified without flip animation
        function updateFlipDigit(containerId, targetValue) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const nums = container.querySelectorAll('.num');
            let currentActive = null;
            
            // Find current active number
            nums.forEach((num) => {
                if (num.classList.contains('active')) {
                    currentActive = num;
                }
            });

            nums.forEach((num) => {
                const numValue = parseInt(num.getAttribute('data-num'));
                
                // Show the target number
                if (numValue === targetValue) {
                    if (!currentActive || currentActive !== num) {
                        // Hide old active
                        if (currentActive) {
                        currentActive.classList.remove('active');
                        currentActive.classList.add('hidden');
                        }
                        
                        // Show new number
                        num.classList.remove('hidden');
                        num.classList.add('active');
                        
                        // Update number display
                        let numberSpan = num.querySelector('.number');
                        if (!numberSpan) {
                            numberSpan = document.createElement('span');
                            numberSpan.className = 'number';
                            num.appendChild(numberSpan);
                        }
                        numberSpan.textContent = targetValue;
                    }
                } else {
                    // Hide non-active numbers
                    if (num !== currentActive) {
                        num.classList.remove('active');
                    num.classList.add('hidden');
                    }
                }
            });
        }

        // Initialize countdown immediately - set initial values
        updateFlipCountdown();
        
        // Start interval for updates
        countdownInterval = setInterval(updateFlipCountdown, 1000);

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (countdownInterval) clearInterval(countdownInterval);
            if (navbarCountdownInterval) clearInterval(navbarCountdownInterval);
        });

        // Ensure body scroll is enabled on page load if modal is not shown
        window.addEventListener('load', function() {
            if (modalDismissed || !modal.classList.contains('show')) {
                document.body.style.overflow = '';
            }
        });
    })();
</script>
@endif
