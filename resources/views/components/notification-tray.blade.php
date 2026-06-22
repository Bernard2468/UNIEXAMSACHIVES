@if (Auth::check())
    @php
        $totalNotifications = ($newMessagesCount ?? 0) + ($newReplyNotifications ?? 0);
        $badgeDisplay = $totalNotifications > 99 ? '99+' : (string) $totalNotifications;
        $hasUnread = $totalNotifications > 0;
    @endphp
    <div class="notification-tray-wrapper">
        <button class="notification-tray-trigger {{ $hasUnread ? 'has-unread' : '' }}"
                onclick="toggleNotificationTray(event)"
                aria-label="{{ $hasUnread ? $totalNotifications . ' unread notification' . ($totalNotifications === 1 ? '' : 's') : 'Notifications, no unread' }}">
            <span class="notification-bell-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-bell-icon">
                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
            </span>
            <span class="notification-badge {{ $totalNotifications >= 10 ? 'is-multi' : 'is-single' }}"
                  style="{{ $hasUnread ? '' : 'display:none;' }}"
                  aria-hidden="true">{{ $badgeDisplay }}</span>
            <span class="notification-badge-ping" style="{{ $hasUnread ? '' : 'display:none;' }}" aria-hidden="true"></span>
        </button>

        <div id="notification-tray-popover" class="notification-tray-popover">
            <!-- Header with toggle and mark all -->
            <div class="notification-tray-header">
                <h2 class="notification-tray-title">Notifications</h2>
                <div class="notification-tray-header-actions">
                    <button class="notification-clear-btn" onclick="clearNotificationTray()" title="Clear all notifications">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="3 6 5 6 21 6"></polyline>
                            <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                        </svg>
                        <span>Clear</span>
                    </button>
                    <form method="POST" action="{{ route('dashboard.notifications.markAllUnified') }}" class="notification-mark-all-form">
                        @csrf
                        <button type="submit" class="notification-mark-all-btn" title="Mark all as read">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"></polyline>
                            </svg>
                            <span>Mark all as read</span>
                        </button>
                    </form>
                    <button class="notification-view-toggle nt-push-toggle"
                            id="ntPushToggle"
                            type="button"
                            onclick="ntHandlePushToggle(event)"
                            title="Enable browser notifications"
                            style="display:none;">
                        <svg id="ntPushOnIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display:none;">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            <circle cx="19" cy="5" r="3" fill="#16a34a" stroke="#16a34a"/>
                        </svg>
                        <svg id="ntPushOffIcon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                            <line x1="2" y1="2" x2="22" y2="22" stroke-width="2.4"/>
                        </svg>
                    </button>
                    <button class="notification-view-toggle" onclick="toggleNotificationView()" title="Toggle view">
                        <svg id="view-list-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <svg id="view-carousel-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <rect x="3" y="3" width="7" height="7"></rect>
                            <rect x="14" y="3" width="7" height="7"></rect>
                            <rect x="14" y="14" width="7" height="7"></rect>
                            <rect x="3" y="14" width="7" height="7"></rect>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Content area -->
            <div id="notification-tray-content" class="notification-tray-content">
                <!-- Carousel view (opt-in via the view toggle) -->
                <div id="notification-carousel-view" class="notification-carousel-view" style="display: none;">
                    <div class="notification-carousel-container">
                        <button class="notification-carousel-btn notification-carousel-prev" onclick="notificationCarouselPrev()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="15 18 9 12 15 6"></polyline>
                            </svg>
                        </button>
                        <div class="notification-carousel-card-wrapper">
                            <div id="notification-carousel-card" class="notification-carousel-card">
                                <!-- Content will be populated by JavaScript -->
                            </div>
                        </div>
                        <button class="notification-carousel-btn notification-carousel-next" onclick="notificationCarouselNext()">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- List view (default) -->
                <div id="notification-list-view" class="notification-list-view">
                    <div id="notification-list-items" class="notification-list-items">
                        <!-- Content will be populated by JavaScript -->
                    </div>
                </div>

                <!-- Empty state -->
                <div id="notification-empty" class="notification-empty" style="display: none;">
                    <p>No notifications</p>
                </div>
            </div>
        </div>
    </div>

    <style>
        .notification-tray-wrapper {
            position: relative;
            margin-right: 12px;
        }

        .notification-tray-trigger {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 8px;
            border-radius: 50%;
            transition: background-color 0.2s;
        }

        .notification-tray-trigger:hover {
            background-color: rgba(0, 0, 0, 0.05);
        }

        .notification-bell-wrap {
            display: inline-flex;
            transform-origin: 50% 4px; /* pivot at bell stem */
        }
        .notification-bell-icon {
            color: currentColor;
            display: block;
        }

        /* Bell wobble — fires once on .ringing, then resets. Cubic-bezier gives a
           tactile "tap" feel (top apps: Linear, Gmail) rather than a uniform sway. */
        @keyframes nt-bell-ring {
            0%   { transform: rotate(0); }
            10%  { transform: rotate(14deg); }
            20%  { transform: rotate(-12deg); }
            30%  { transform: rotate(10deg); }
            40%  { transform: rotate(-8deg); }
            50%  { transform: rotate(6deg); }
            60%  { transform: rotate(-4deg); }
            70%  { transform: rotate(2deg); }
            100% { transform: rotate(0); }
        }
        .notification-tray-trigger.ringing .notification-bell-wrap {
            animation: nt-bell-ring 0.9s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }

        /* ===== BADGE (industry-standard pattern) =====
           - White ring (2px) so it pops on ANY header background (light/dark/colored)
           - Single-digit = perfect circle; multi-digit = pill
           - Resting pulse-ring around badge while unread > 0 (subtle, infinite)
           - Bounce-pop scale on count change */
        .notification-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: #fff;
            font-size: 10.5px;
            line-height: 18px;
            min-width: 18px;
            height: 18px;
            padding: 0 5px;
            border-radius: 999px;
            font-weight: 700;
            text-align: center;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            font-variant-numeric: tabular-nums;
            box-shadow: 0 0 0 2px #fff, 0 1px 3px rgba(220, 38, 38, 0.35);
            letter-spacing: -0.2px;
            transform-origin: center;
            transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
            z-index: 2;
        }
        .notification-badge.is-single {
            width: 18px;
            padding: 0;
        }
        .notification-badge.popping {
            animation: nt-badge-pop 0.55s cubic-bezier(0.34, 1.56, 0.64, 1) both;
        }
        @keyframes nt-badge-pop {
            0%   { transform: scale(1); }
            40%  { transform: scale(1.45); }
            70%  { transform: scale(0.92); }
            100% { transform: scale(1); }
        }

        /* Resting pulse — telegraphs "you have unread" without being annoying.
           Sized to match the badge; fades out so it doesn't draw the eye constantly. */
        .notification-badge-ping {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            border-radius: 999px;
            background: #ef4444;
            opacity: 0.55;
            z-index: 1;
            pointer-events: none;
            animation: nt-badge-ping 1.8s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        @keyframes nt-badge-ping {
            0%   { transform: scale(0.95); opacity: 0.55; }
            70%  { transform: scale(2.1);  opacity: 0; }
            100% { transform: scale(2.1);  opacity: 0; }
        }
        /* Dark header support — replace the white ring with the dark surface so it still pops */
        .is_dark .notification-badge {
            box-shadow: 0 0 0 2px #0f172a, 0 1px 3px rgba(220, 38, 38, 0.5);
        }
        @media (prefers-reduced-motion: reduce) {
            .notification-tray-trigger.ringing .notification-bell-wrap,
            .notification-badge.popping,
            .notification-badge-ping {
                animation: none !important;
            }
        }

        .notification-tray-popover {
            position: absolute;
            right: 0;
            top: 44px;
            width: 420px;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
            display: none;
            z-index: 1000;
            overflow: hidden;
            animation: fadeIn 0.2s ease-out;
        }

        .notification-tray-popover.show {
            display: block;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(-8px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .notification-tray-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 16px;
            border-bottom: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .notification-tray-title {
            font-size: 14px;
            font-weight: 600;
            margin: 0;
            color: #111827;
        }

        .notification-tray-header-actions {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .notification-mark-all-form {
            margin: 0;
        }

        .notification-clear-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border: none;
            background: transparent;
            color: #ef4444;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .notification-clear-btn:hover {
            background: #fee2e2;
            color: #dc2626;
        }

        .notification-clear-btn svg {
            width: 14px;
            height: 14px;
        }

        .notification-mark-all-btn {
            display: flex;
            align-items: center;
            gap: 4px;
            padding: 6px 10px;
            border: none;
            background: transparent;
            color: #6b7280;
            font-size: 12px;
            font-weight: 500;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .notification-mark-all-btn:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .notification-mark-all-btn svg {
            width: 14px;
            height: 14px;
        }

        .notification-view-toggle {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            background: transparent;
            color: #6b7280;
            cursor: pointer;
            border-radius: 6px;
            transition: all 0.2s;
        }

        .notification-view-toggle:hover {
            background: #f3f4f6;
            color: #111827;
        }

        .notification-view-toggle svg {
            width: 16px;
            height: 16px;
        }

        .notification-tray-content {
            max-height: 400px;
            overflow: hidden;
        }

        /* Carousel view styles */
        .notification-carousel-view {
            padding: 16px 0;
        }

        .notification-carousel-container {
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .notification-carousel-btn {
            position: absolute;
            z-index: 10;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            border: none;
            background: #fff;
            color: #6b7280;
            cursor: pointer;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            transition: all 0.2s;
        }

        .notification-carousel-btn:hover {
            background: #f9fafb;
            color: #111827;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
        }

        .notification-carousel-btn svg {
            width: 16px;
            height: 16px;
        }

        .notification-carousel-prev {
            left: 8px;
        }

        .notification-carousel-next {
            right: 8px;
        }

        .notification-carousel-card-wrapper {
            flex: 1;
            margin: 0 48px;
        }

        .notification-carousel-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 20px;
            transition: all 0.3s;
        }

        .notification-carousel-card-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 12px;
        }

        .notification-carousel-card-title-row {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            flex: 1;
        }

        .notification-carousel-card-icon {
            width: 18px;
            height: 18px;
            color: #6b7280;
            flex-shrink: 0;
            margin-top: 2px;
        }

        .notification-carousel-card-title {
            font-size: 15px;
            font-weight: 600;
            color: #111827;
            margin: 0;
            line-height: 1.4;
            flex: 1;
        }

        .notification-carousel-card-time {
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
            margin-left: 12px;
        }

        .notification-carousel-card-description {
            font-size: 13px;
            color: #6b7280;
            line-height: 1.5;
            margin: 0;
            margin-top: 8px;
        }

        /* List view styles */
        .notification-list-view {
            max-height: 400px;
            overflow-y: auto;
        }

        .notification-list-items {
            display: flex;
            flex-direction: column;
        }

        .notification-list-item {
            display: flex;
            flex-direction: column;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            transition: background-color 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .notification-list-item-top {
            display: flex;
            flex-direction: row;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 4px;
        }

        .notification-list-item:hover {
            background: #f9fafb;
        }

        .notification-list-item-header {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .notification-list-item-title-row {
            display: flex;
            align-items: center;
            gap: 10px;
            flex: 1;
            min-width: 0;
        }

        .notification-list-item-icon {
            width: 18px;
            height: 18px;
            color: #6b7280;
            flex-shrink: 0;
        }

        .notification-list-item-title {
            font-size: 14px;
            font-weight: 600;
            color: #111827;
            flex: 1;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .notification-list-item-time {
            font-size: 12px;
            color: #9ca3af;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .notification-list-item-description {
            font-size: 12px;
            color: #6b7280;
            line-height: 1.4;
            margin-top: 4px;
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }

        .notification-empty {
            padding: 32px 16px;
            text-align: center;
            color: #9ca3af;
            font-size: 14px;
        }

        /* ==================== MODERN TRAY UPGRADES (nt-*) ====================
           Scoped prefix to avoid colliding with existing tray classes. */

        /* "X new since you were last here" pill — shown at top of tray when applicable */
        .nt-new-pill {
            margin: 10px 16px 4px;
            padding: 6px 10px;
            background: #eef2ff;
            color: #4338ca;
            border-radius: 999px;
            font-size: 11.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #c7d2fe;
            width: fit-content;
        }
        .nt-new-pill .nt-new-pill__dot {
            width: 6px; height: 6px; border-radius: 50%;
            background: #4338ca;
            animation: nt-pill-blink 1.4s ease-in-out infinite;
        }
        @keyframes nt-pill-blink {
            0%, 100% { opacity: 1; }
            50%      { opacity: 0.35; }
        }
        .is_dark .nt-new-pill {
            background: rgba(67, 56, 202, 0.15);
            color: #a5b4fc;
            border-color: rgba(99, 102, 241, 0.35);
        }
        .is_dark .nt-new-pill .nt-new-pill__dot { background: #a5b4fc; }

        /* Bucket group header */
        .nt-group-header {
            padding: 10px 16px 4px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: #9ca3af;
            background: transparent;
            position: sticky;
            top: 0;
            background: #fff;
            z-index: 1;
        }
        .is_dark .nt-group-header {
            color: #6b7280;
            background: #1f2937;
        }

        /* Avatar (photo or initials) */
        .nt-avatar {
            width: 32px; height: 32px;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11.5px;
            font-weight: 700;
            color: #fff;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            box-shadow: 0 0 0 2px #fff;
            position: relative;
        }
        .is_dark .nt-avatar { box-shadow: 0 0 0 2px #1f2937; }
        /* Clip the photo to a circle on the IMG itself (not via overflow:hidden on
           the avatar) so the corner category pip can overflow outside the avatar. */
        .nt-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 50%; }
        /* Deterministic palette for initial-only avatars — H selected by initial */
        .nt-avatar[data-hue="1"]  { background: linear-gradient(135deg, #ef4444, #f97316); }
        .nt-avatar[data-hue="2"]  { background: linear-gradient(135deg, #f59e0b, #eab308); }
        .nt-avatar[data-hue="3"]  { background: linear-gradient(135deg, #10b981, #14b8a6); }
        .nt-avatar[data-hue="4"]  { background: linear-gradient(135deg, #0ea5e9, #6366f1); }
        .nt-avatar[data-hue="5"]  { background: linear-gradient(135deg, #8b5cf6, #ec4899); }

        /* Tiny category pip on the avatar (form/memo/system) */
        .nt-avatar__pip {
            position: absolute;
            bottom: -2px; right: -2px;
            width: 14px; height: 14px;
            border-radius: 50%;
            background: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 0 1.5px #fff, 0 1px 2px rgba(0,0,0,0.15);
        }
        .is_dark .nt-avatar__pip { background: #1f2937; box-shadow: 0 0 0 1.5px #1f2937, 0 1px 2px rgba(0,0,0,0.4); }
        .nt-avatar__pip svg { width: 9px; height: 9px; }
        .nt-avatar__pip--form   { color: #2563eb; }
        .nt-avatar__pip--memo   { color: #16a34a; }
        .nt-avatar__pip--reply  { color: #d97706; }
        .nt-avatar__pip--system { color: #6b7280; }

        /* List item updates — keep the existing .notification-list-item shell intact */
        .nt-list-item-with-avatar {
            display: flex; gap: 10px;
            padding: 12px 16px;
            border-bottom: 1px solid #f3f4f6;
            text-decoration: none; color: inherit;
            transition: background-color 0.15s;
            cursor: pointer;
            position: relative;
        }
        .nt-list-item-with-avatar:hover { background: #f9fafb; }
        .is_dark .nt-list-item-with-avatar { border-color: #374151; }
        .is_dark .nt-list-item-with-avatar:hover { background: #1f2937; }
        .nt-list-item-with-avatar.is-fresh::before {
            content: '';
            position: absolute;
            left: 4px; top: 50%;
            transform: translateY(-50%);
            width: 3px; height: 28px;
            background: #6366f1;
            border-radius: 2px;
        }
        /* Subtle unread dot (top-right) for items that aren't "fresh" but
           are still unread (read before tray was last opened, etc.). */
        .nt-list-item-with-avatar.is-unread:not(.is-fresh)::after {
            content: '';
            position: absolute;
            top: 14px; right: 14px;
            width: 7px; height: 7px;
            border-radius: 50%;
            background: #3b82f6;
            box-shadow: 0 0 0 2px #fff;
        }
        .is_dark .nt-list-item-with-avatar.is-unread:not(.is-fresh)::after { box-shadow: 0 0 0 2px #1f2937; }

        /* Read items — visually dimmer so unread items dominate the eye
           (Slack / Linear / GitHub convention). Items still clickable,
           still show avatar, just lose the "demands attention" weight. */
        .nt-list-item-with-avatar.is-read { background: transparent; }
        .nt-list-item-with-avatar.is-read .nt-list-item__title { font-weight: 500; color: #4b5563; }
        .nt-list-item-with-avatar.is-read .nt-list-item__msg   { color: #9ca3af; }
        .nt-list-item-with-avatar.is-read .nt-list-item__actor strong { color: #6b7280; }
        .nt-list-item-with-avatar.is-read .nt-avatar { filter: saturate(0.55); opacity: 0.85; }
        .is_dark .nt-list-item-with-avatar.is-read .nt-list-item__title { color: #9ca3af; }
        .is_dark .nt-list-item-with-avatar.is-read .nt-list-item__msg   { color: #6b7280; }
        .nt-list-item__body { flex: 1; min-width: 0; }

        /* Contextual eyebrow — the "what to do" tag on a single memo card
           (Forwarded to you / Awaiting your forward / Approval needed). Replaces
           the old duplicate notification rows. Colour by urgency:
           memo = neutral, action = blue (needs you), urgent = amber (act now). */
        .nt-eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
            padding: 3px 8px;
            border-radius: 999px;
            margin-bottom: 5px;
        }
        .nt-eyebrow::before {
            content: '';
            width: 5px; height: 5px;
            border-radius: 50%;
            background: currentColor;
        }
        .nt-eyebrow--memo   { background: #f1f5f9; color: #475569; }
        .nt-eyebrow--action { background: #eff6ff; color: #1d4ed8; }
        .nt-eyebrow--urgent { background: #fef3c7; color: #b45309; }
        .is_dark .nt-eyebrow--memo   { background: rgba(148,163,184,0.18); color: #cbd5e1; }
        .is_dark .nt-eyebrow--action { background: rgba(29,78,216,0.22);  color: #93c5fd; }
        .is_dark .nt-eyebrow--urgent { background: rgba(180,83,9,0.22);   color: #fcd34d; }
        .nt-list-item__header-row {
            display: flex; justify-content: space-between; align-items: baseline; gap: 8px;
            margin-bottom: 2px;
        }
        .nt-list-item__title {
            font-size: 13.5px;
            font-weight: 600;
            color: #111827;
            line-height: 1.35;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .is_dark .nt-list-item__title { color: #f9fafb; }
        .nt-list-item__time {
            font-size: 11px; color: #9ca3af;
            white-space: nowrap;
            flex-shrink: 0;
        }
        .nt-list-item__actor {
            font-size: 11.5px; color: #6b7280;
            margin-top: 2px;
        }
        .nt-list-item__actor strong { color: #374151; font-weight: 600; }
        .is_dark .nt-list-item__actor strong { color: #d1d5db; }
        .nt-list-item__msg {
            font-size: 12px; color: #6b7280;
            line-height: 1.4;
            margin-top: 4px;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .is_dark .nt-list-item__msg { color: #9ca3af; }

        /* Inline action buttons */
        .nt-actions {
            display: flex; gap: 6px;
            margin-top: 8px;
            flex-wrap: wrap;
        }
        .nt-action-btn {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            font-size: 11.5px;
            font-weight: 600;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.15s;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .nt-action-btn--primary {
            background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe;
        }
        .nt-action-btn--primary:hover { background: #dbeafe; color: #1e40af; }
        .nt-action-btn--success {
            background: #ecfdf5; color: #047857; border-color: #a7f3d0;
        }
        .nt-action-btn--success:hover { background: #d1fae5; color: #065f46; }
        .is_dark .nt-action-btn--primary {
            background: rgba(29, 78, 216, 0.18); color: #93c5fd; border-color: rgba(59, 130, 246, 0.4);
        }
        .is_dark .nt-action-btn--success {
            background: rgba(4, 120, 87, 0.18); color: #6ee7b7; border-color: rgba(16, 185, 129, 0.4);
        }

        /* Carousel card upgrade — avatar + actions inside the card */
        .nt-carousel-actor-row {
            display: flex; align-items: center; gap: 10px;
            margin-top: 12px;
            padding-top: 10px;
            border-top: 1px dashed #e5e7eb;
        }
        .is_dark .nt-carousel-actor-row { border-color: #374151; }

        /* Dark mode support */
        .is_dark .notification-tray-popover {
            background: #1f2937;
            border-color: #374151;
        }

        .is_dark .notification-tray-header {
            background: #111827;
            border-color: #374151;
        }

        .is_dark .notification-tray-title {
            color: #f9fafb;
        }

        .is_dark .notification-mark-all-btn {
            color: #9ca3af;
        }

        .is_dark .notification-mark-all-btn:hover {
            background: #374151;
            color: #f9fafb;
        }

        .is_dark .notification-view-toggle {
            color: #9ca3af;
        }

        .is_dark .notification-view-toggle:hover {
            background: #374151;
            color: #f9fafb;
        }

        .is_dark .notification-carousel-card {
            background: #1f2937;
            border-color: #374151;
        }

        .is_dark .notification-carousel-card-title {
            color: #f9fafb;
        }

        .is_dark .notification-carousel-card-description {
            color: #d1d5db;
        }

        .is_dark .notification-list-item {
            border-color: #374151;
        }

        .is_dark .notification-list-item:hover {
            background: #1f2937;
        }

        .is_dark .notification-list-item-title {
            color: #f9fafb;
        }

        .is_dark .notification-list-item-description {
            color: #d1d5db;
        }

        .is_dark .notification-carousel-btn {
            background: #1f2937;
            color: #9ca3af;
        }

        .is_dark .notification-carousel-btn:hover {
            background: #374151;
            color: #f9fafb;
        }
    </style>

    <script>
        // Notification tray state. List view is the default (industry standard:
        // Slack, Linear, GitHub, Notion). Carousel is opt-in via the toggle.
        let notificationTrayState = {
            isOpen: false,
            isCarousel: false,
            currentIndex: 0,
            items: []
        };

        // Toggle notification tray
        function toggleNotificationTray(e) {
            e.stopPropagation();
            const popover = document.getElementById('notification-tray-popover');
            if (!popover) return;
            
            notificationTrayState.isOpen = !notificationTrayState.isOpen;
            if (notificationTrayState.isOpen) {
                popover.classList.add('show');
                refreshNotificationTray();
                if (typeof ntRefreshPushButton === 'function') ntRefreshPushButton();
            } else {
                popover.classList.remove('show');
            }
        }

        // Close notification tray when clicking outside
        document.addEventListener('click', function(e) {
            const popover = document.getElementById('notification-tray-popover');
            const trigger = document.querySelector('.notification-tray-trigger');
            if (popover && !popover.contains(e.target) && !trigger?.contains(e.target)) {
                popover.classList.remove('show');
                notificationTrayState.isOpen = false;
            }
        });

        // Clear notification tray
        function clearNotificationTray() {
            if (!confirm('Are you sure you want to delete all notifications? This action cannot be undone.')) {
                return;
            }
            
            const clearBtn = document.querySelector('.notification-clear-btn');
            if (!clearBtn) return;
            
            const originalHTML = clearBtn.innerHTML;
            clearBtn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><span>Clearing...</span>';
            clearBtn.disabled = true;
            
            // Delete all notifications
            fetch('{{ route("dashboard.notifications.clearAll") }}', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Clear the notification tray
                    notificationTrayState.items = [];
                    notificationTrayState.currentIndex = 0;
                    
                    // Update UI
                    const emptyState = document.getElementById('notification-empty');
                    const content = document.getElementById('notification-tray-content');
                    if (emptyState) emptyState.style.display = 'block';
                    if (content) content.style.display = 'none';
                    
                    // Update badge
                    if (typeof updateNotificationBadge === 'function') {
                        updateNotificationBadge(0, 0);
                    } else {
                        const badge = document.querySelector('.notification-badge');
                        if (badge) badge.style.display = 'none';
                    }
                    
                    // Refresh the tray to reflect changes
                    if (typeof refreshNotificationTray === 'function') {
                        refreshNotificationTray();
                    }
                }
                
                // Reset button
                clearBtn.innerHTML = originalHTML;
                clearBtn.disabled = false;
            })
            .catch(error => {
                console.error('Error clearing notifications:', error);
                alert('Error clearing notifications. Please try again.');
                clearBtn.innerHTML = originalHTML;
                clearBtn.disabled = false;
            });
        }

        // Toggle between carousel and list view
        function toggleNotificationView() {
            notificationTrayState.isCarousel = !notificationTrayState.isCarousel;
            const carouselView = document.getElementById('notification-carousel-view');
            const listView = document.getElementById('notification-list-view');
            const listIcon = document.getElementById('view-list-icon');
            const carouselIcon = document.getElementById('view-carousel-icon');

            if (notificationTrayState.isCarousel) {
                carouselView.style.display = 'block';
                listView.style.display = 'none';
                listIcon.style.display = 'none';
                carouselIcon.style.display = 'block';
            } else {
                carouselView.style.display = 'none';
                listView.style.display = 'block';
                listIcon.style.display = 'block';
                carouselIcon.style.display = 'none';
            }
        }

        // Carousel navigation
        function notificationCarouselNext() {
            if (notificationTrayState.items.length === 0) return;
            notificationTrayState.currentIndex = (notificationTrayState.currentIndex + 1) % notificationTrayState.items.length;
            renderCarouselCard();
        }

        function notificationCarouselPrev() {
            if (notificationTrayState.items.length === 0) return;
            notificationTrayState.currentIndex = (notificationTrayState.currentIndex - 1 + notificationTrayState.items.length) % notificationTrayState.items.length;
            renderCarouselCard();
        }

        // ====================== MODERN TRAY HELPERS ======================
        const NT_ESC = (s) => String(s ?? '').replace(/[&<>"']/g, (c) => ({
            '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
        }[c]));

        function ntHueFromInitials(initials) {
            if (!initials) return 1;
            const code = (initials.charCodeAt(0) || 65) - 65;
            return (Math.abs(code) % 5) + 1;
        }

        function ntCategoryPip(category) {
            // Tiny SVG corner badge on the avatar — visual category tag.
            const cat = (category || 'system').toLowerCase();
            const map = {
                form:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
                memo:   '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="16" rx="2"/><polyline points="22 6 12 13 2 6"/></svg>',
                reply:  '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 17 4 12 9 7"/><path d="M20 18v-2a4 4 0 0 0-4-4H4"/></svg>',
                system: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
            };
            return `<span class="nt-avatar__pip nt-avatar__pip--${cat}" aria-hidden="true">${map[cat] || map.system}</span>`;
        }

        function ntRenderAvatar(actor, category) {
            const initials = NT_ESC((actor && actor.initials) || '·');
            const hue = ntHueFromInitials(initials);
            const inner = actor && actor.avatar
                ? `<img src="${NT_ESC(actor.avatar)}" alt="${NT_ESC(actor.name || '')}" onerror="this.outerHTML='${initials}';">`
                : initials;
            return `<span class="nt-avatar" data-hue="${hue}" aria-hidden="true">${inner}${ntCategoryPip(category)}</span>`;
        }

        function ntRenderActions(actions) {
            if (!Array.isArray(actions) || actions.length === 0) return '';
            const html = actions.map(a => `
                <a href="${NT_ESC(a.url || '#')}"
                   class="nt-action-btn nt-action-btn--${NT_ESC(a.style || 'primary')}"
                   data-nt-action="1">${NT_ESC(a.label || 'Open')}</a>
            `).join('');
            return `<div class="nt-actions">${html}</div>`;
        }

        function ntBucketLabel(bucket) {
            return { today: 'Today', yesterday: 'Yesterday', earlier: 'Earlier' }[bucket] || 'Earlier';
        }

        // Contextual eyebrow shown above a memo card's subject so the single card
        // tells the user what to do (Forwarded to you / Awaiting your forward /
        // Approval needed) — replacing the old duplicate notification rows.
        function ntEyebrow(item) {
            if (!item || !item.context_label) return '';
            const style = (item.context_style || 'memo').toLowerCase();
            return `<div class="nt-eyebrow nt-eyebrow--${NT_ESC(style)}">${NT_ESC(item.context_label)}</div>`;
        }

        // ---------------- PUSH TOGGLE (browser notifications) ----------------
        async function ntRefreshPushButton() {
            const btn = document.getElementById('ntPushToggle');
            if (!btn || !window.udtsPush) return;
            if (!window.udtsPush.supported) { btn.style.display = 'none'; return; }

            const state = await window.udtsPush.refresh();
            const isOn  = state.subscribed && state.permission === 'granted' && state.push_enabled;

            btn.style.display = 'inline-flex';
            const on  = document.getElementById('ntPushOnIcon');
            const off = document.getElementById('ntPushOffIcon');
            if (on)  on.style.display  = isOn ? 'block' : 'none';
            if (off) off.style.display = isOn ? 'none'  : 'block';

            if (state.permission === 'denied') {
                btn.title = 'Browser notifications blocked — enable them in site settings';
            } else if (isOn) {
                btn.title = 'Browser notifications ON · click to disable';
            } else {
                btn.title = 'Enable browser notifications';
            }
        }

        async function ntHandlePushToggle(e) {
            e.preventDefault();
            e.stopPropagation();
            if (!window.udtsPush || !window.udtsPush.supported) return;

            const state = window.udtsPush.state;
            const isOn  = state.subscribed && state.permission === 'granted' && state.push_enabled;

            if (state.permission === 'denied') {
                alert("Browser notifications are blocked.\n\nTo turn them on:\n1. Click the lock icon in your address bar\n2. Set 'Notifications' to 'Allow'\n3. Reload the page");
                return;
            }

            if (isOn) {
                await window.udtsPush.unsubscribe();
            } else {
                const res = await window.udtsPush.subscribe();
                if (!res.ok && res.reason === 'no_vapid_key') {
                    alert('Browser push is not configured for this site yet.');
                }
            }
            ntRefreshPushButton();
        }

        // Mark a single notification as read on click-through (top-app behavior).
        async function ntMarkOneRead(id) {
            try {
                await fetch(`/dashboard/notifications/${id}/mark-read`, {
                    method: 'POST',
                    credentials: 'same-origin',
                    // keepalive lets the request survive the immediate
                    // window.location navigation that follows on click-through,
                    // so the read flag is reliably persisted.
                    keepalive: true,
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                        'Accept': 'application/json',
                    }
                });
            } catch (e) { /* non-fatal */ }
        }

        // Render carousel card (single-card view)
        function renderCarouselCard() {
            const card = document.getElementById('notification-carousel-card');
            if (!card || notificationTrayState.items.length === 0) return;

            const item = notificationTrayState.items[notificationTrayState.currentIndex];
            const url = item.url || '#';
            const displayTitle = NT_ESC(item.title || 'Notification');
            const showMessage = item.message && item.message.trim() !== '';
            const actorLine = item.actor
                ? `<span class="nt-list-item__actor"><strong>${NT_ESC(item.actor.name)}</strong></span>`
                : '';

            card.innerHTML = `
                ${ntEyebrow(item)}
                <div class="notification-carousel-card-header">
                    <div class="notification-carousel-card-title-row" style="gap:12px;">
                        ${ntRenderAvatar(item.actor, item.category)}
                        <h3 class="notification-carousel-card-title">${displayTitle}</h3>
                    </div>
                    <span class="notification-carousel-card-time">${NT_ESC(item.time || 'just now')}</span>
                </div>
                ${showMessage ? `<p class="notification-carousel-card-description">${NT_ESC(item.message)}</p>` : ''}
                ${item.actor ? `<div class="nt-carousel-actor-row">${actorLine}</div>` : ''}
                ${ntRenderActions(item.actions)}
            `;

            // Card-level click → open URL + mark read; inline action clicks should
            // NOT bubble to the card click, hence the data-nt-action guard below.
            card.onclick = (e) => {
                if (e.target.closest('[data-nt-action]')) return;
                // Only EmailCampaignRecipient memo rows ('memo_recipient') are
                // marked read server-side by the memo chat endpoint. Genuine
                // Notification rows — including ones typed 'memo' (e.g. the
                // "Request Approved — Proceed to Form" alert) — must be marked
                // read here, otherwise their unread count never clears.
                if (!item.is_read && item.id && item.type !== 'memo_recipient') {
                    ntMarkOneRead(item.id);
                }
                if (url !== '#') window.location.href = url;
            };
        }

        // Render list view — grouped by date bucket with avatars and inline actions
        function renderListView() {
            const listContainer = document.getElementById('notification-list-items');
            if (!listContainer) return;

            if (notificationTrayState.items.length === 0) {
                listContainer.innerHTML = '';
                return;
            }

            // "X new since you were last here" pill — only when there ARE new items
            const newCount = notificationTrayState.items.filter(i => i.is_new_since_seen).length;
            const pillHtml = newCount > 0
                ? `<div class="nt-new-pill" role="status">
                       <span class="nt-new-pill__dot"></span>
                       ${newCount} new since you were last here
                   </div>`
                : '';

            // Group by bucket preserving server-side order (already newest-first)
            const groups = { today: [], yesterday: [], earlier: [] };
            notificationTrayState.items.forEach(item => {
                (groups[item.bucket] || groups.earlier).push(item);
            });

            const renderGroup = (key, items) => {
                if (!items.length) return '';
                const rows = items.map(item => {
                    const url = item.url || '#';
                    const title = NT_ESC(item.title || 'Notification');
                    const showMessage = item.message && item.message.trim() !== '';
                    const actorLine = item.actor
                        ? `<div class="nt-list-item__actor"><strong>${NT_ESC(item.actor.name)}</strong></div>`
                        : '';
                    const classes = [
                        item.is_new_since_seen ? 'is-fresh'   : '',
                        item.is_read           ? 'is-read'    : 'is-unread',
                    ].filter(Boolean).join(' ');
                    return `
                        <div class="nt-list-item-with-avatar ${classes}"
                             data-nt-id="${NT_ESC(item.id)}"
                             data-nt-type="${NT_ESC(item.type)}"
                             data-nt-url="${NT_ESC(url)}">
                            ${ntRenderAvatar(item.actor, item.category)}
                            <div class="nt-list-item__body">
                                ${ntEyebrow(item)}
                                <div class="nt-list-item__header-row">
                                    <div class="nt-list-item__title">${title}</div>
                                    <span class="nt-list-item__time">${NT_ESC(item.time || 'just now')}</span>
                                </div>
                                ${actorLine}
                                ${showMessage ? `<div class="nt-list-item__msg">${NT_ESC(item.message)}</div>` : ''}
                                ${ntRenderActions(item.actions)}
                            </div>
                        </div>
                    `;
                }).join('');
                return `<div class="nt-group-header">${ntBucketLabel(key)}</div>${rows}`;
            };

            listContainer.innerHTML = pillHtml +
                renderGroup('today', groups.today) +
                renderGroup('yesterday', groups.yesterday) +
                renderGroup('earlier', groups.earlier);

            // Delegate click → mark-one-read + navigate. Inline action buttons
            // (which have their own data-nt-action attr) handle navigation themselves.
            listContainer.querySelectorAll('.nt-list-item-with-avatar').forEach(row => {
                row.addEventListener('click', (e) => {
                    if (e.target.closest('[data-nt-action]')) return;
                    const id      = row.getAttribute('data-nt-id');
                    const type    = row.getAttribute('data-nt-type');
                    const url     = row.getAttribute('data-nt-url');
                    const isRead  = row.classList.contains('is-read');
                    // Only EmailCampaignRecipient memo rows ('memo_recipient') are
                    // marked read server-side by the memo /show endpoint, so we skip
                    // the roundtrip for those. Genuine Notification rows — including
                    // ones typed 'memo' like "Request Approved — Proceed to Form" —
                    // have a real notification id and MUST be marked read here, or
                    // their unread count lingers even after the user opens them.
                    if (!isRead && id && type !== 'memo_recipient') {
                        ntMarkOneRead(id);
                    }
                    if (url && url !== '#') window.location.href = url;
                });
            });
        }

        // Refresh notification tray content (exposed globally for polling)
        window.refreshNotificationTray = function() {
            // Fetch memos
            fetch('{{ route('dashboard.memos.recent') }}', {
                credentials: 'same-origin',
                cache: 'no-cache',
                headers: {
                    'Cache-Control': 'no-cache',
                    'Pragma': 'no-cache'
                }
            })
            .then(r => r.json())
            .then(memoData => {
                // Fetch reply notifications
                fetch('/dashboard/notifications', {
                    credentials: 'same-origin',
                    cache: 'no-cache',
                    headers: {
                        'Cache-Control': 'no-cache',
                        'Pragma': 'no-cache'
                    }
                })
                .then(r => r.json())
                .then(notificationData => {
                    // Combine and format items
                    const items = [];
                    
                    // Memos — render BOTH unread and recently-read (read ones
                    // are dimmed via CSS so Mark-as-read doesn't wipe the tray).
                    if (memoData.memos && memoData.memos.length > 0) {
                        memoData.memos.forEach(memo => {
                            items.push({
                                id: memo.id,
                                type: 'memo_recipient',
                                category: 'memo',
                                title: memo.subject,
                                message: memo.message || '',
                                context_label: memo.context_label || null,
                                context_style: memo.context_style || 'memo',
                                time: memo.created_at,
                                created_at_iso: memo.created_at_iso || null,
                                is_read: !!memo.is_read,
                                is_new_since_seen: !memo.is_read,
                                bucket: memo.bucket || 'earlier',
                                actor: memo.actor || null,
                                actions: memo.actions || [],
                                url: memo.url
                            });
                        });
                    }

                    // Notifications (forms, replies, system) — same pattern.
                    if (notificationData.notifications && notificationData.notifications.length > 0) {
                        notificationData.notifications.forEach(n => {
                            items.push({
                                id: n.id,
                                type: n.type,
                                category: n.category || 'system',
                                title: n.title || n.message,
                                message: n.message || '',
                                time: n.time_ago,
                                is_read: !!n.is_read,
                                is_new_since_seen: !!n.is_new_since_seen && !n.is_read,
                                bucket: n.bucket || 'earlier',
                                actor: n.actor || null,
                                actions: n.actions || [],
                                url: n.url
                            });
                        });
                    }

                    // Sort merged items newest-first by created_at_iso when present.
                    items.sort((a, b) => {
                        const ta = a.created_at_iso ? Date.parse(a.created_at_iso) : 0;
                        const tb = b.created_at_iso ? Date.parse(b.created_at_iso) : 0;
                        return tb - ta;
                    });

                    // Preserve current index if items haven't changed significantly
                    const previousItems = notificationTrayState.items;
                    const itemsChanged = !previousItems || 
                        previousItems.length !== items.length ||
                        items.some((item, idx) => !previousItems[idx] || item.id !== previousItems[idx].id);
                    
                    // Only reset index if items changed or if current index is out of bounds
                    if (itemsChanged || notificationTrayState.currentIndex >= items.length) {
                        notificationTrayState.currentIndex = 0;
                    } else {
                        // Keep current index, but ensure it's within bounds
                        if (notificationTrayState.currentIndex >= items.length) {
                            notificationTrayState.currentIndex = 0;
                        }
                    }
                    
                    notificationTrayState.items = items;

                    // Show/hide empty state
                    const emptyState = document.getElementById('notification-empty');
                    const content = document.getElementById('notification-tray-content');
                    if (items.length === 0) {
                        emptyState.style.display = 'block';
                        content.style.display = 'none';
                        notificationTrayState.currentIndex = 0;
                    } else {
                        emptyState.style.display = 'none';
                        content.style.display = 'block';
                        // Only re-render if in carousel view
                        if (notificationTrayState.isCarousel) {
                            renderCarouselCard();
                        }
                        renderListView();
                    }
                })
                .catch(err => {
                    console.log('Error fetching notifications:', err);
                    // Fallback to just memos (unread + recently-read).
                    const items = [];
                    if (memoData.memos && memoData.memos.length > 0) {
                        memoData.memos.forEach(memo => {
                            items.push({
                                id: memo.id,
                                type: 'memo_recipient',
                                category: 'memo',
                                title: memo.subject,
                                message: memo.message || '',
                                context_label: memo.context_label || null,
                                context_style: memo.context_style || 'memo',
                                time: memo.created_at,
                                created_at_iso: memo.created_at_iso || null,
                                is_read: !!memo.is_read,
                                is_new_since_seen: !memo.is_read,
                                bucket: memo.bucket || 'earlier',
                                actor: memo.actor || null,
                                actions: memo.actions || [],
                                url: memo.url
                            });
                        });
                    }
                    
                    // Preserve current index if items haven't changed significantly
                    const previousItems = notificationTrayState.items;
                    const itemsChanged = !previousItems || 
                        previousItems.length !== items.length ||
                        items.some((item, idx) => !previousItems[idx] || item.id !== previousItems[idx].id);
                    
                    // Only reset index if items changed or if current index is out of bounds
                    if (itemsChanged || notificationTrayState.currentIndex >= items.length) {
                        notificationTrayState.currentIndex = 0;
                    } else {
                        // Keep current index, but ensure it's within bounds
                        if (notificationTrayState.currentIndex >= items.length) {
                            notificationTrayState.currentIndex = 0;
                        }
                    }
                    
                    notificationTrayState.items = items;
                    
                    // Only re-render if in carousel view
                    if (notificationTrayState.isCarousel) {
                        renderCarouselCard();
                    }
                    renderListView();
                });
            })
            .catch(err => {
                console.log('Error fetching memos:', err);
            });
        };

        // Handle mark all as read
        document.addEventListener('DOMContentLoaded', function() {
            const markAllForm = document.querySelector('.notification-mark-all-form');
            if (markAllForm) {
                markAllForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const button = this.querySelector('.notification-mark-all-btn');
                    const originalHTML = button.innerHTML;
                    button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg><span>Marking...</span>';
                    button.disabled = true;
                    
                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: new URLSearchParams(new FormData(this))
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            button.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg><span>All marked as read</span>';
                            button.style.color = '#10b981';
                            
                            // Refresh the notification tray
                            refreshNotificationTray();
                            
                            // Update badge (using existing function if available)
                            if (typeof updateNotificationBadge === 'function') {
                                updateNotificationBadge(0, 0);
                            } else {
                                const badge = document.querySelector('.notification-badge');
                                if (badge) badge.style.display = 'none';
                            }
                            
                            setTimeout(() => {
                                button.innerHTML = originalHTML;
                                button.style.color = '';
                                button.disabled = false;
                            }, 2000);
                        }
                    })
                    .catch(error => {
                        console.error('Error marking all as read:', error);
                        button.innerHTML = originalHTML;
                        button.disabled = false;
                    });
                });
            }
        });
    </script>
@endif

