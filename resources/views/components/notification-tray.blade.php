@if (Auth::check())
    <div class="notification-tray-wrapper">
        <button class="notification-tray-trigger" onclick="toggleNotificationTray(event)">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-bell-icon">
                <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"></path>
                <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
            </svg>
            @php
                $totalNotifications = ($newMessagesCount ?? 0) + ($newReplyNotifications ?? 0);
            @endphp
            @if($totalNotifications > 0)
                <span class="notification-badge">{{$totalNotifications}}</span>
            @endif
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
                    <button class="notification-view-toggle" onclick="toggleNotificationView()" title="Toggle view">
                        <svg id="view-list-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                            <line x1="8" y1="6" x2="21" y2="6"></line>
                            <line x1="8" y1="12" x2="21" y2="12"></line>
                            <line x1="8" y1="18" x2="21" y2="18"></line>
                            <line x1="3" y1="6" x2="3.01" y2="6"></line>
                            <line x1="3" y1="12" x2="3.01" y2="12"></line>
                            <line x1="3" y1="18" x2="3.01" y2="18"></line>
                        </svg>
                        <svg id="view-carousel-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <!-- Carousel view -->
                <div id="notification-carousel-view" class="notification-carousel-view">
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

                <!-- List view -->
                <div id="notification-list-view" class="notification-list-view" style="display: none;">
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

        .notification-bell-icon {
            color: currentColor;
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: #ef4444;
            color: #fff;
            font-size: 11px;
            line-height: 1;
            padding: 3px 6px;
            border-radius: 999px;
            font-weight: 700;
            min-width: 18px;
            text-align: center;
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

        /* Section headers for list view - removed to reduce clutter */
        .notification-section-header {
            display: none;
        }

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
        // Notification tray state
        let notificationTrayState = {
            isOpen: false,
            isCarousel: true,
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

        // Get icon for notification type
        function getNotificationIcon(type, title) {
            // Determine icon based on type or title
            if (type === 'memo' || title?.toLowerCase().includes('memo')) {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-carousel-card-icon"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>`;
            } else if (type === 'reply' || title?.toLowerCase().includes('reply')) {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-carousel-card-icon"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>`;
            } else {
                return `<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="notification-carousel-card-icon"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>`;
            }
        }

        // Render carousel card
        function renderCarouselCard() {
            const card = document.getElementById('notification-carousel-card');
            if (!card || notificationTrayState.items.length === 0) return;

            const item = notificationTrayState.items[notificationTrayState.currentIndex];
            const icon = getNotificationIcon(item.type, item.title);
            const url = item.url || '#';

            // Show title and message preview for memos
            const displayTitle = item.title || 'Notification';
            const showMessage = item.type === 'memo' && item.message && item.message.trim() !== '';
            const messagePreview = showMessage ? item.message : '';
            
            card.innerHTML = `
                <div class="notification-carousel-card-header">
                    <div class="notification-carousel-card-title-row">
                        ${icon}
                        <h3 class="notification-carousel-card-title">${displayTitle}</h3>
                    </div>
                    <span class="notification-carousel-card-time">${item.time || 'just now'}</span>
                </div>
                ${showMessage ? `<p class="notification-carousel-card-description">${messagePreview}</p>` : ''}
            `;

            // Make card clickable
            card.style.cursor = 'pointer';
            card.onclick = () => {
                if (url !== '#') {
                    window.location.href = url;
                }
            };
        }

        // Render list view
        function renderListView() {
            const listContainer = document.getElementById('notification-list-items');
            if (!listContainer) return;

            if (notificationTrayState.items.length === 0) {
                listContainer.innerHTML = '';
                return;
            }

            let html = '';

            notificationTrayState.items.forEach(item => {
                const icon = getNotificationIcon(item.type, item.title);
                const url = item.url || '#';
                const displayTitle = item.title || 'Notification';
                const showMessage = item.type === 'memo' && item.message && item.message.trim() !== '';
                const messagePreview = showMessage ? item.message : '';
                const unreadDot = !item.is_read ? '<span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: inline-block; flex-shrink: 0;"></span>' : '';

                html += `
                    <a href="${url}" class="notification-list-item">
                        <div class="notification-list-item-top">
                            <div class="notification-list-item-header">
                                <div class="notification-list-item-title-row">
                                    ${icon}
                                    <span class="notification-list-item-title">${displayTitle}</span>
                                    ${unreadDot}
                                </div>
                            </div>
                            <span class="notification-list-item-time">${item.time || 'just now'}</span>
                        </div>
                        ${showMessage ? `<p class="notification-list-item-description">${messagePreview}</p>` : ''}
                    </a>
                `;
            });

            listContainer.innerHTML = html;
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
                    
                    // Add memos - include message preview (only unread)
                    if (memoData.memos && memoData.memos.length > 0) {
                        memoData.memos.forEach(memo => {
                            // Only add unread memos
                            if (!memo.is_read) {
                                items.push({
                                    id: memo.id,
                                    type: 'memo',
                                    title: memo.subject,
                                    message: memo.message || '', // Message preview for memos
                                    time: memo.created_at,
                                    is_read: memo.is_read,
                                    url: memo.url
                                });
                            }
                        });
                    }
                    
                    // Add reply notifications - only unread (only store title, no duplicate description)
                    if (notificationData.notifications && notificationData.notifications.length > 0) {
                        notificationData.notifications.forEach(notification => {
                            // Only add unread notifications
                            if (!notification.is_read) {
                                items.push({
                                    id: notification.id,
                                    type: 'reply',
                                    title: notification.title || notification.message,
                                    time: notification.time_ago,
                                    is_read: notification.is_read,
                                    url: notification.url
                                });
                            }
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
                    // Fallback to just memos (only unread)
                    const items = [];
                    if (memoData.memos && memoData.memos.length > 0) {
                        memoData.memos.forEach(memo => {
                            // Only add unread memos
                            if (!memo.is_read) {
                                items.push({
                                    id: memo.id,
                                    type: 'memo',
                                    title: memo.subject,
                                    message: memo.message || '', // Message preview for memos
                                    time: memo.created_at,
                                    is_read: memo.is_read,
                                    url: memo.url
                                });
                            }
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

