@if(session('success') || session('error') || session('warning') || session('info'))
    <div id="modern-notifications-container" class="modern-notifications-container">
        @if(session('success'))
            <div class="modern-notification modern-notification--success" data-notification="success">
                <div class="notification-content">
                    <div class="notification-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="notification-text">
                        <h4 class="notification-title">Success!</h4>
                        <p class="notification-message">{{ session('success') }}</p>
                    </div>
                    <button class="notification-close" onclick="closeNotification(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="notification-progress"></div>
            </div>
        @endif

        @if(session('error'))
            <div class="modern-notification modern-notification--error" data-notification="error">
                <div class="notification-content">
                    <div class="notification-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="notification-text">
                        <h4 class="notification-title">Error!</h4>
                        <p class="notification-message">{{ session('error') }}</p>
                    </div>
                    <button class="notification-close" onclick="closeNotification(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="notification-progress"></div>
            </div>
        @endif

        @if(session('warning'))
            <div class="modern-notification modern-notification--warning" data-notification="warning">
                <div class="notification-content">
                    <div class="notification-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M10.29 3.86L1.82 18A2 2 0 0 0 3.64 21H20.36A2 2 0 0 0 22.18 18L13.71 3.86A2 2 0 0 0 10.29 3.86ZM12 17H12.01M12 13H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="notification-text">
                        <h4 class="notification-title">Warning!</h4>
                        <p class="notification-message">{{ session('warning') }}</p>
                    </div>
                    <button class="notification-close" onclick="closeNotification(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="notification-progress"></div>
            </div>
        @endif

        @if(session('info'))
            <div class="modern-notification modern-notification--info" data-notification="info">
                <div class="notification-content">
                    <div class="notification-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M13 16H12V12H11M12 8H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </div>
                    <div class="notification-text">
                        <h4 class="notification-title">Info!</h4>
                        <p class="notification-message">{{ session('info') }}</p>
                    </div>
                    <button class="notification-close" onclick="closeNotification(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="notification-progress"></div>
            </div>
        @endif
    </div>

    <script>
        // Auto-hide notifications after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const notifications = document.querySelectorAll('.modern-notification');
            notifications.forEach(notification => {
                // Start progress bar animation
                const progress = notification.querySelector('.notification-progress');
                if (progress) {
                    progress.style.animation = 'notificationProgress 5s linear forwards';
                }
                
                // Auto-hide after 5 seconds
                setTimeout(() => {
                    hideNotification(notification);
                }, 5000);
            });
        });

        function closeNotification(button) {
            const notification = button.closest('.modern-notification');
            hideNotification(notification);
        }

        function hideNotification(notification) {
            notification.classList.add('notification-hiding');
            setTimeout(() => {
                notification.remove();
                // Note: Session messages will be cleared on next page load
                // This prevents the route error and simplifies the system
            }, 300);
        }

        function showNotification(type, title, message) {
            const container = document.getElementById('modern-notifications-container') || createNotificationContainer();
            const notification = createNotificationElement(type, title, message);
            container.appendChild(notification);
            
            // Trigger entrance animation
            setTimeout(() => {
                notification.classList.add('notification-show');
            }, 100);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                hideNotification(notification);
            }, 5000);
        }

        function createNotificationContainer() {
            const container = document.createElement('div');
            container.id = 'modern-notifications-container';
            container.className = 'modern-notifications-container';
            document.body.appendChild(container);
            return container;
        }

        function createNotificationElement(type, title, message) {
            const notification = document.createElement('div');
            notification.className = `modern-notification modern-notification--${type}`;
            notification.innerHTML = `
                <div class="notification-content">
                    <div class="notification-icon">
                        ${getIconForType(type)}
                    </div>
                    <div class="notification-text">
                        <h4 class="notification-title">${title}</h4>
                        <p class="notification-message">${message}</p>
                    </div>
                    <button class="notification-close" onclick="closeNotification(this)">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </div>
                <div class="notification-progress"></div>
            `;
            return notification;
        }

        function getIconForType(type) {
            const icons = {
                success: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M9 12L11 14L15 10M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                error: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M12 9V13M12 17H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                warning: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M10.29 3.86L1.82 18A2 2 0 0 0 3.64 21H20.36A2 2 0 0 0 22.18 18L13.71 3.86A2 2 0 0 0 10.29 3.86ZM12 17H12.01M12 13H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>',
                info: '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M13 16H12V12H11M12 8H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>'
            };
            return icons[type] || icons.info;
        }
    </script>
@endif
