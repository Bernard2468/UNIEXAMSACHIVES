<!-- 3D Wall Calendar Modal -->
<div id="calendarModal" class="calendar-modal" style="display: none;">
    <div class="calendar-modal-overlay" onclick="closeCalendarModal()"></div>
    <div class="calendar-modal-content">
        <div class="calendar-modal-header">
            <!-- Month Navigation in Header -->
            <div class="calendar-nav-header" id="calendarNavHeader">
                <button class="calendar-nav-btn-header" onclick="changeMonth(-1)">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="15 18 9 12 15 6"></polyline>
                    </svg>
                    <span>Prev Month</span>
                </button>
                <div class="calendar-month-display-header" id="calendarMonthDisplay">January 2025</div>
                <button class="calendar-nav-btn-header" onclick="changeMonth(1)">
                    <span>Next Month</span>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="9 18 15 12 9 6"></polyline>
                    </svg>
                </button>
            </div>
            <button class="calendar-modal-close" onclick="closeCalendarModal()" aria-label="Close calendar">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"></line>
                    <line x1="6" y1="6" x2="18" y2="18"></line>
                </svg>
            </button>
        </div>

        <div class="calendar-modal-body">

            <!-- 3D Wall Calendar Container -->
            <div class="calendar-wall-container" id="calendarWallContainer">
                <div class="calendar-wall" id="calendarWall">
                    <!-- Days will be dynamically generated here -->
                </div>
            </div>

            <!-- Hamburger Sidebar (shown when calendar is collapsed) -->
            <div class="calendar-hamburger-sidebar" id="calendarHamburgerSidebar" style="display: none;">
                <button class="calendar-hamburger-btn" id="calendarHamburgerBtn" onclick="expandCalendar()" aria-label="Show calendar">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="3" y1="12" x2="21" y2="12"></line>
                        <line x1="3" y1="6" x2="21" y2="6"></line>
                        <line x1="3" y1="18" x2="21" y2="18"></line>
                    </svg>
                </button>
            </div>

            <!-- Vertical Separator -->
            <div class="calendar-sidebar-separator" id="calendarSidebarSeparator" style="display: none;"></div>

            <!-- Add Event Form -->
            <div class="calendar-add-form" id="calendarAddForm" style="display: none;">
                <h3 class="form-title">Add New Event</h3>
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label">Event Title</label>
                        <input type="text" id="eventTitle" class="form-input" placeholder="e.g., Meeting, Appointment" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date & Time</label>
                        <input type="datetime-local" id="eventDate" class="form-input" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Type</label>
                        <select id="eventType" class="form-select">
                            <option value="appointment">Appointment</option>
                            <option value="meeting">Meeting</option>
                            <option value="event">Event</option>
                        </select>
                    </div>
                    <div class="form-group form-group-full">
                        <label class="form-label">Description (optional)</label>
                        <textarea id="eventDescription" class="form-textarea" placeholder="Add event description..." rows="2"></textarea>
                    </div>
                </div>
                <div class="form-actions">
                    <button class="calendar-add-btn" onclick="addEvent()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        Add Event
                    </button>
                    <button class="calendar-cancel-btn" onclick="resetEventForm()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Calendar Modal Styles - Matching Compose Memo */
* {
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.calendar-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    padding: 20px;
}

.calendar-modal.show {
    opacity: 1;
    visibility: visible;
}

.calendar-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.75);
    backdrop-filter: blur(8px);
    -webkit-backdrop-filter: blur(8px);
}

.calendar-modal-content {
    position: relative;
    background: #ffffff;
    border-radius: 20px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    max-width: 1100px;
    width: 100%;
    max-height: 99vh;
    overflow: hidden;
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    border: 1px solid rgba(226, 232, 240, 0.8);
    display: flex;
    flex-direction: column;
}

.calendar-modal.show .calendar-modal-content {
    transform: scale(1) translateY(0);
}

.calendar-modal-header {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 16px 24px;
    border-bottom: 1px solid #e5e7eb;
    background: #f8f9fa;
    color: #111827;
    position: relative;
}

.calendar-nav-header {
    display: flex;
    align-items: center;
    gap: 20px;
    justify-content: center;
}

.calendar-nav-btn-header {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 16px;
    background: white;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 14px;
    font-weight: 600;
    color: #374151;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.calendar-nav-btn-header:hover {
    background: #f8fafc;
    border-color: #3b82f6;
    color: #3b82f6;
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(59, 130, 246, 0.1);
}

.calendar-month-display-header {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    min-width: 180px;
    text-align: center;
    letter-spacing: -0.02em;
}

.calendar-hamburger-sidebar {
    width: 80px;
    min-width: 80px;
    background: #f8f9fa;
    display: flex;
    align-items: flex-start;
    justify-content: center;
    padding-top: 20px;
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    opacity: 0;
    transform: translateX(-100px);
    flex-shrink: 0;
    align-self: stretch;
    min-height: 100%;
    will-change: transform, opacity;
}

.calendar-hamburger-sidebar.show {
    opacity: 1;
    transform: translateX(0);
}

.calendar-hamburger-btn {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    color: #6b7280;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
}

.calendar-hamburger-btn:hover {
    background: #f3f4f6;
    border-color: #d1d5db;
    color: #111827;
    transform: scale(1.05);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12);
}

.calendar-sidebar-separator {
    width: 1px;
    min-width: 1px;
    background: #e5e7eb;
    opacity: 0;
    transition: opacity 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    flex-shrink: 0;
    align-self: stretch;
    min-height: 100%;
    will-change: opacity;
}

.calendar-sidebar-separator.show {
    opacity: 1;
}

.calendar-modal-close {
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.2s;
    color: #6b7280;
    position: absolute;
    right: 24px;
}

.calendar-modal-close:hover {
    background: #e5e7eb;
    border-color: #d1d5db;
    color: #111827;
    transform: scale(1.1);
}

.calendar-modal-body {
    padding: 12px 20px 20px 20px;
    overflow-y: auto;
    overflow-x: hidden;
    flex: 1;
    display: flex;
    flex-direction: column;
    min-height: 0;
    position: relative;
    transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
}

.calendar-modal-body.with-sidebar {
    flex-direction: row;
    padding: 20px;
    gap: 0;
}


.calendar-wall-container {
    width: 100%;
    overflow: visible;
    margin: 5px auto 10px;
    perspective: none;
    min-height: 520px;
    max-height: 650px;
    padding: 8px 20px 30px 20px;
    display: flex;
    justify-content: center;
    align-items: flex-start;
    position: relative;
    transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    opacity: 1;
    transform: scale(1) translateY(0);
    will-change: transform, opacity, width, height;
}

.calendar-wall-container.collapsed {
    width: 0;
    height: 0;
    min-height: 0;
    max-height: 0;
    padding: 0;
    margin: 0;
    opacity: 0;
    transform: scale(0.1) translateY(-200px) translateX(-45vw);
    overflow: hidden;
    pointer-events: none;
    transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
}

.calendar-wall.collapsed {
    transform: scale(0.1);
    opacity: 0;
}

.calendar-nav-header {
    transition: all 0.5s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    opacity: 1;
    transform: translateX(0);
    will-change: transform, opacity;
}

.calendar-nav-header.hidden {
    opacity: 0;
    transform: translateX(-20px);
    pointer-events: none;
}

.calendar-wall-container::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

.calendar-wall-container::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.calendar-wall-container::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.calendar-wall-container::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

.calendar-wall {
    display: grid;
    grid-template-columns: repeat(7, 100px);
    gap: 8px;
    transform-style: flat;
    transform: none !important;
    transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    padding: 5px 8px 20px 8px;
    width: fit-content;
    margin: 0;
    opacity: 1;
    will-change: transform, opacity;
}

.calendar-wall.collapsed {
    transform: scale(0.1) !important;
    opacity: 0;
}

.calendar-day-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px;
    min-height: 75px;
    position: relative;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    transition: box-shadow 0.2s;
    transform-style: flat;
    transform: none !important;
}

.calendar-day-card:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
    transform: none !important;
}

.calendar-day-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 4px;
}

.calendar-day-number {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.calendar-day-name {
    font-size: 9px;
    color: #64748b;
    text-transform: uppercase;
    font-weight: 600;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.calendar-day-events {
    position: relative;
    min-height: 40px;
    margin-top: 4px;
}

.calendar-event-dot {
    position: absolute;
    width: 20px;
    height: 20px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 8px;
    font-weight: 700;
    cursor: pointer;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    transform: none;
    transition: box-shadow 0.2s, scale 0.2s;
    z-index: 10;
}

.calendar-event-dot:hover {
    transform: scale(1.1);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
}

.calendar-event-dot.appointment {
    background: #3b82f6;
}

.calendar-event-dot.meeting {
    background: #10b981;
}

.calendar-event-dot.event {
    background: #f59e0b;
}

.calendar-event-count {
    font-size: 9px;
    color: #64748b;
    margin-top: 2px;
    font-weight: 600;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.calendar-day-card.other-month {
    opacity: 0.4;
    background: #f8fafc;
    filter: grayscale(0.3);
}

.calendar-day-card.other-month .calendar-day-number {
    color: #94a3b8 !important;
    font-weight: 500;
}

.calendar-day-card.other-month .calendar-day-name {
    color: #94a3b8 !important;
}

.calendar-day-card.other-month .calendar-event-count {
    color: #94a3b8 !important;
}

.calendar-day-card.today {
    border-color: #3b82f6;
    border-width: 2px;
    background: linear-gradient(135deg, rgba(59, 130, 246, 0.08) 0%, rgba(29, 78, 216, 0.05) 100%);
}

.calendar-add-form {
    padding: 32px;
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #f1f5f9;
    margin-top: 0;
    margin-left: 0;
    margin-right: 0;
    opacity: 0;
    transform: translateY(20px) scale(0.95);
    transition: all 0.6s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    display: none;
    flex: 1;
    width: 100%;
    max-width: none;
    will-change: transform, opacity;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
}

.calendar-add-form.show {
    display: block;
    opacity: 1;
    transform: translateY(0) scale(1);
}

.form-title {
    font-size: 20px;
    font-weight: 700;
    color: #1e293b;
    margin: 0 0 24px 0;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    letter-spacing: -0.02em;
}

.form-grid {
    display: grid;
    grid-template-columns: 2fr 1.5fr 1fr;
    gap: 24px;
    margin-bottom: 24px;
}

.form-group-full {
    grid-column: 1 / -1;
}

.form-label {
    font-size: 15px;
    font-weight: 600;
    color: #374151;
    margin-bottom: 8px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    letter-spacing: 0.01em;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-input,
.form-textarea,
.form-select {
    padding: 16px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    color: #1e293b;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    transition: all 0.3s ease;
    background: #ffffff;
    font-weight: 500;
}

.form-input::placeholder,
.form-textarea::placeholder {
    color: #94a3b8;
    font-weight: 400;
}

.form-input:focus,
.form-textarea:focus,
.form-select:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.form-textarea {
    resize: vertical;
    min-height: 80px;
    line-height: 1.6;
}

.calendar-add-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 24px;
    background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    white-space: nowrap;
    letter-spacing: 0.02em;
}

.calendar-add-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(59, 130, 246, 0.3);
}

.calendar-add-btn:active {
    transform: translateY(0);
}

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
}

.calendar-cancel-btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 24px;
    background: white;
    color: #64748b;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    font-size: 15px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    letter-spacing: 0.02em;
}

.calendar-cancel-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.calendar-day-card {
    cursor: pointer;
}

.calendar-day-card.clickable {
    cursor: pointer;
}

.calendar-day-card.clickable:hover {
    background: #f0f9ff;
    border-color: #3b82f6;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.1);
}

.calendar-day-card.past-date {
    cursor: not-allowed;
    opacity: 0.45;
    background: #f8fafc !important;
    filter: grayscale(0.4);
}

.calendar-day-card.past-date .calendar-day-number {
    color: #94a3b8 !important;
    font-weight: 500;
}

.calendar-day-card.past-date .calendar-day-name {
    color: #94a3b8 !important;
}

.calendar-day-card.past-date .calendar-event-count {
    color: #94a3b8 !important;
}

.calendar-day-card.past-date .calendar-event-dot {
    opacity: 0.5;
    filter: grayscale(0.6);
}

/* Event Hover Card */
.event-hover-card {
    position: fixed !important;
    z-index: 99999 !important;
    width: 320px;
    background: #ffffff !important;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 16px;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.12), 0 0 0 1px rgba(0, 0, 0, 0.05);
    pointer-events: auto;
    opacity: 0;
    visibility: hidden;
    transform: scale(0.95) translateY(-4px);
    transition: opacity 0.15s cubic-bezier(0.16, 1, 0.3, 1), 
                transform 0.15s cubic-bezier(0.16, 1, 0.3, 1),
                visibility 0s 0.15s;
    display: block !important;
}

.event-hover-card.show {
    opacity: 1 !important;
    visibility: visible !important;
    transform: scale(1) translateY(0) !important;
    pointer-events: auto !important;
    display: block !important;
    transition: opacity 0.15s cubic-bezier(0.16, 1, 0.3, 1), 
                transform 0.15s cubic-bezier(0.16, 1, 0.3, 1),
                visibility 0s;
}

.event-hover-card-content {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.event-hover-card-header {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.event-hover-card-title {
    font-size: 16px;
    font-weight: 600;
    color: #1e293b;
    line-height: 1.4;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    margin: 0;
}

.event-hover-card-description {
    font-size: 14px;
    color: #64748b;
    line-height: 1.5;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    margin: 0;
}

.event-hover-card-meta {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    color: #94a3b8;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    padding-top: 8px;
    border-top: 1px solid #f1f5f9;
}

.event-hover-card-meta span {
    display: flex;
    align-items: center;
    gap: 4px;
}

.event-hover-card-meta .separator {
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background: #cbd5e1;
}

.event-hover-card-events-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
    max-height: 300px;
    overflow-y: auto;
    padding-right: 4px;
}

.event-hover-card-events-list::-webkit-scrollbar {
    width: 6px;
}

.event-hover-card-events-list::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 3px;
}

.event-hover-card-events-list::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 3px;
}

.event-hover-card-events-list::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

.event-hover-card-event-item {
    padding: 12px;
    background: #f8fafc;
    border-radius: 8px;
    border-left: 3px solid #3b82f6;
    transition: all 0.2s ease;
}

.event-hover-card-event-item:hover {
    background: #f1f5f9;
    transform: translateX(2px);
}

.event-hover-card-event-item.meeting {
    border-left-color: #10b981;
}

.event-hover-card-event-item.event {
    border-left-color: #f59e0b;
}

.event-hover-card-event-title {
    font-size: 14px;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 4px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.event-hover-card-event-time {
    font-size: 12px;
    color: #64748b;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    margin-top: 4px;
}

.event-hover-card-event-description {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 6px;
    line-height: 1.4;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

/* Event Popover (for click) */
.event-popover {
    position: absolute;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    z-index: 1000;
    min-width: 200px;
    max-width: 300px;
    display: none;
}

.event-popover.show {
    display: block;
    animation: popoverFadeIn 0.2s ease;
}

@keyframes popoverFadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.event-popover-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 4px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.event-popover-date {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 8px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.event-popover-description {
    font-size: 12px;
    color: #374151;
    margin-bottom: 8px;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.event-popover-actions {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-top: 8px;
    padding-top: 8px;
    border-top: 1px solid #e5e7eb;
}

.event-popover-delete {
    background: #ef4444;
    color: white;
    border: none;
    border-radius: 8px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
}

.event-popover-delete:hover {
    background: #dc2626;
}

/* Responsive */
@media (max-width: 1024px) {
    .calendar-wall {
        grid-template-columns: repeat(7, 90px);
        gap: 6px;
    }
    
    .calendar-day-card {
        min-height: 70px;
        padding: 6px;
    }
    
    .calendar-add-form .form-grid {
        grid-template-columns: 1fr;
    }
    
    .calendar-nav-header {
        gap: 12px;
    }
    
    .calendar-nav-btn-header {
        padding: 6px 12px;
        font-size: 12px;
    }
    
    .calendar-month-display-header {
        font-size: 16px;
        min-width: 140px;
    }
}

@media (max-width: 768px) {
    .calendar-modal-content {
        max-width: 100%;
        max-height: 95vh;
    }
    
    .calendar-modal-header {
        flex-direction: column;
        gap: 12px;
        align-items: center;
    }
    
    .calendar-nav-header {
        justify-content: center;
    }
    
    .calendar-modal-close {
        position: absolute;
        right: 16px;
        top: 16px;
    }
    
    .calendar-nav-btn-header span {
        display: none;
    }
    
    .calendar-month-display-header {
        font-size: 16px;
        min-width: auto;
    }
    
    .calendar-wall {
        grid-template-columns: repeat(7, 80px);
        gap: 6px;
    }
    
    .calendar-day-card {
        min-height: 65px;
        padding: 5px;
    }
    
    .calendar-event-dot {
        width: 18px;
        height: 18px;
        font-size: 7px;
    }
    
    .calendar-day-number {
        font-size: 12px;
    }
    
    .calendar-day-name {
        font-size: 8px;
    }
    
    .calendar-add-form .form-grid {
        grid-template-columns: 1fr;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .calendar-add-btn,
    .calendar-cancel-btn {
        width: 100%;
        justify-content: center;
    }
}
</style>

<script>
let currentDate = new Date();
let calendarEvents = [];
let isDragging = false;
let dragStart = { x: 0, y: 0 };
let tiltX = 18;
let tiltY = 0;
let activePopover = null;
let activeHoverCard = null;
let hoverCardTimeout = null;

// Open calendar modal
function openCalendarModal() {
    const modal = document.getElementById('calendarModal');
    modal.style.display = 'flex';
    setTimeout(() => {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }, 10);
    
    // Ensure calendar is expanded and form is hidden
    const form = document.getElementById('calendarAddForm');
    const container = document.getElementById('calendarWallContainer');
    const wall = document.getElementById('calendarWall');
    const navHeader = document.getElementById('calendarNavHeader');
    const sidebar = document.getElementById('calendarHamburgerSidebar');
    const separator = document.getElementById('calendarSidebarSeparator');
    const modalBody = document.querySelector('.calendar-modal-body');
    
    if (form) {
        form.style.display = 'none';
        form.classList.remove('show');
    }
    
    if (container) {
        container.classList.remove('collapsed');
    }
    
    if (wall) {
        wall.classList.remove('collapsed');
    }
    
    if (navHeader) {
        navHeader.classList.remove('hidden');
    }
    
    if (sidebar) {
        sidebar.style.display = 'none';
        sidebar.classList.remove('show');
    }
    
    if (separator) {
        separator.style.display = 'none';
        separator.classList.remove('show');
    }
    
    if (modalBody) {
        modalBody.classList.remove('with-sidebar');
    }
    
    loadEvents();
    renderCalendar();
    setTimeout(() => {
        init3DInteractions();
    }, 100);
}

// Close calendar modal
function closeCalendarModal() {
    const modal = document.getElementById('calendarModal');
    modal.classList.remove('show');
    setTimeout(() => {
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }, 300);
    if (activePopover) {
        activePopover.style.display = 'none';
        activePopover = null;
    }
    hideEventHoverCard();
}

// Load events from API
async function loadEvents() {
    try {
        const response = await fetch('{{ route("dashboard.calendar.events") }}', {
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        });
        const data = await response.json();
        calendarEvents = data;
        renderCalendar();
    } catch (error) {
        console.error('Error loading events:', error);
    }
}

// Change month
function changeMonth(direction) {
    currentDate = new Date(currentDate.getFullYear(), currentDate.getMonth() + direction, 1);
    renderCalendar();
}

// Render calendar
function renderCalendar() {
    const wall = document.getElementById('calendarWall');
    const monthDisplay = document.getElementById('calendarMonthDisplay');
    
    // Update month display
    const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
    monthDisplay.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
    
    // Get first day of month and number of days
    const firstDay = new Date(currentDate.getFullYear(), currentDate.getMonth(), 1);
    const lastDay = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0);
    const daysInMonth = lastDay.getDate();
    const startingDayOfWeek = firstDay.getDay();
    
    // Get previous month's days to fill the grid
    const prevMonth = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, 0);
    const daysInPrevMonth = prevMonth.getDate();
    
    wall.innerHTML = '';
    
    // Add previous month's trailing days
    for (let i = startingDayOfWeek - 1; i >= 0; i--) {
        const day = new Date(currentDate.getFullYear(), currentDate.getMonth() - 1, daysInPrevMonth - i);
        wall.appendChild(createDayCard(day, true));
    }
    
    // Add current month's days
    for (let day = 1; day <= daysInMonth; day++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth(), day);
        wall.appendChild(createDayCard(date, false));
    }
    
    // Add next month's leading days to complete the grid
    const totalCells = wall.children.length;
    const remainingCells = 42 - totalCells; // 6 rows * 7 days
    for (let day = 1; day <= remainingCells; day++) {
        const date = new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, day);
        wall.appendChild(createDayCard(date, true));
    }
    
    // Apply 3D effect
    apply3DEffect();
}

// Create day card
function createDayCard(date, isOtherMonth) {
    const card = document.createElement('div');
    card.className = 'calendar-day-card';
    if (isOtherMonth) card.classList.add('other-month');
    
    const today = new Date();
    today.setHours(0, 0, 0, 0);
    const cardDate = new Date(date);
    cardDate.setHours(0, 0, 0, 0);
    
    // Check if date is in the past
    const isPastDate = cardDate < today;
    
    if (cardDate.toDateString() === today.toDateString() && !isOtherMonth) {
        card.classList.add('today');
    }
    
    // Make clickable if not past date and not other month
    if (!isPastDate && !isOtherMonth) {
        card.classList.add('clickable');
        card.onclick = (e) => {
            // Don't trigger if clicking on event dots
            if (!e.target.closest('.calendar-event-dot')) {
                handleDateClick(date);
            }
        };
    } else if (isPastDate) {
        card.classList.add('past-date');
    }
    
    const dayEvents = getEventsForDay(date);
    const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
    
    card.innerHTML = `
        <div class="calendar-day-header">
            <span class="calendar-day-number">${date.getDate()}</span>
            <span class="calendar-day-name">${dayNames[date.getDay()]}</span>
        </div>
        <div class="calendar-day-events" id="events-${date.toISOString()}">
            ${renderDayEvents(dayEvents, date)}
        </div>
        ${dayEvents.length > 0 ? `<div class="calendar-event-count">${dayEvents.length} event(s)</div>` : ''}
    `;
    
    // Add hover card functionality for dates with events
    if (dayEvents.length > 0) {
        let hoverTimeout = null;
        
        card.addEventListener('mouseenter', (e) => {
            clearTimeout(hoverTimeout);
            hoverTimeout = setTimeout(() => {
                showEventHoverCard(e, date, dayEvents);
            }, 150); // Reduced delay for faster response
        });
        
        card.addEventListener('mouseleave', (e) => {
            clearTimeout(hoverTimeout);
            // Only hide if not moving to hover card
            setTimeout(() => {
                if (activeHoverCard && !activeHoverCard.matches(':hover') && !card.matches(':hover')) {
                    hideEventHoverCard();
                }
            }, 100);
        });
    }
    
    return card;
}

// Handle date click - collapse calendar and show form
function handleDateClick(date) {
    // Format date for datetime-local input (YYYY-MM-DDTHH:mm)
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    const dateStr = `${year}-${month}-${day}T09:00`; // Default to 9 AM
    
    // Reset form fields
    document.getElementById('eventTitle').value = '';
    document.getElementById('eventDate').value = dateStr;
    document.getElementById('eventDescription').value = '';
    document.getElementById('eventType').value = 'appointment';
    
    // Collapse calendar with smooth transition
    collapseCalendar();
}

// Collapse calendar into hamburger
function collapseCalendar() {
    // Hide hover card when collapsing
    hideEventHoverCard();
    
    const container = document.getElementById('calendarWallContainer');
    const wall = document.getElementById('calendarWall');
    const navHeader = document.getElementById('calendarNavHeader');
    const sidebar = document.getElementById('calendarHamburgerSidebar');
    const separator = document.getElementById('calendarSidebarSeparator');
    const form = document.getElementById('calendarAddForm');
    const modalBody = document.querySelector('.calendar-modal-body');
    
    if (container && navHeader) {
        // Use requestAnimationFrame for smoother start
        requestAnimationFrame(() => {
            // Start calendar collapse
            container.classList.add('collapsed');
            if (wall) {
                wall.classList.add('collapsed');
            }
            navHeader.classList.add('hidden');
            
            // Update modal body layout immediately for smooth transition
            if (modalBody) {
                modalBody.classList.add('with-sidebar');
            }
            
            // Show sidebar and separator with coordinated timing
            requestAnimationFrame(() => {
                if (sidebar) {
                    sidebar.style.display = 'flex';
                    // Force reflow
                    sidebar.offsetHeight;
                    requestAnimationFrame(() => {
                        sidebar.classList.add('show');
                    });
                }
                
                if (separator) {
                    separator.style.display = 'block';
                    // Force reflow
                    separator.offsetHeight;
                    requestAnimationFrame(() => {
                        separator.classList.add('show');
                    });
                }
            });
            
            // Show form with perfect timing - after calendar starts collapsing
            setTimeout(() => {
                if (form) {
                    form.style.display = 'block';
                    // Force reflow
                    form.offsetHeight;
                    requestAnimationFrame(() => {
                        form.classList.add('show');
                        // Focus on title input after animation
                        setTimeout(() => {
                            document.getElementById('eventTitle').focus();
                        }, 100);
                    });
                }
            }, 200);
        });
    }
}

// Expand calendar from hamburger
function expandCalendar() {
    const container = document.getElementById('calendarWallContainer');
    const wall = document.getElementById('calendarWall');
    const navHeader = document.getElementById('calendarNavHeader');
    const sidebar = document.getElementById('calendarHamburgerSidebar');
    const separator = document.getElementById('calendarSidebarSeparator');
    const form = document.getElementById('calendarAddForm');
    const modalBody = document.querySelector('.calendar-modal-body');
    
    if (container && navHeader) {
        // Use requestAnimationFrame for smoother start
        requestAnimationFrame(() => {
            // Hide form first with smooth fade out
            if (form) {
                form.classList.remove('show');
                setTimeout(() => {
                    form.style.display = 'none';
                }, 400);
            }
            
            // Hide sidebar and separator with coordinated timing
            if (sidebar) {
                sidebar.classList.remove('show');
                setTimeout(() => {
                    sidebar.style.display = 'none';
                }, 400);
            }
            
            if (separator) {
                separator.classList.remove('show');
                setTimeout(() => {
                    separator.style.display = 'none';
                }, 350);
            }
            
            // Update modal body layout after sidebar starts hiding
            setTimeout(() => {
                if (modalBody) {
                    modalBody.classList.remove('with-sidebar');
                }
                
                // Expand calendar after layout change
                requestAnimationFrame(() => {
                    container.classList.remove('collapsed');
                    if (wall) {
                        wall.classList.remove('collapsed');
                    }
                    navHeader.classList.remove('hidden');
                });
            }, 200);
        });
    }
}

// Reset event form and expand calendar
function resetEventForm() {
    document.getElementById('eventTitle').value = '';
    document.getElementById('eventDate').value = '';
    document.getElementById('eventDescription').value = '';
    document.getElementById('eventType').value = 'appointment';
    
    // Expand calendar back
    expandCalendar();
}

// Get events for a specific day
function getEventsForDay(date) {
    const dateStr = date.toISOString().split('T')[0];
    return calendarEvents.filter(event => {
        const eventDate = new Date(event.date);
        return eventDate.toISOString().split('T')[0] === dateStr;
    });
}

// Render events for a day
function renderDayEvents(events, date) {
    if (events.length === 0) return '';
    
    const panelWidth = 100;
    const gap = 8;
    const availableWidth = panelWidth - 16;
    
    return events.map((event, index) => {
        const left = 4 + (index * 24) % (availableWidth - 30);
        const top = 4 + Math.floor((index * 24) / (availableWidth - 30)) * 22;
        const colorMap = {
            'appointment': '#3b82f6',
            'meeting': '#10b981',
            'event': '#f59e0b'
        };
        const bgColor = event.color || colorMap[event.type] || '#3b82f6';
        
        return `
            <div class="calendar-event-dot ${event.type}" 
                 style="left: ${left}px; top: ${top}px; background: ${bgColor};"
                 onclick="showEventPopover(event, ${event.id}, '${event.title}', '${event.date}', '${(event.description || '').replace(/'/g, "\\'")}', '${event.type}')"
                 onmouseenter="showEventTooltip(event, '${event.title}')">
                •
            </div>
        `;
    }).join('');
}

// Show event popover
function showEventPopover(e, eventId, title, date, description, type) {
    e.stopPropagation();
    
    // Close existing popover
    if (activePopover) {
        activePopover.style.display = 'none';
    }
    
    const popover = document.createElement('div');
    popover.className = 'event-popover show';
    popover.style.position = 'fixed';
    popover.style.left = e.pageX + 10 + 'px';
    popover.style.top = e.pageY + 10 + 'px';
    
    const eventDate = new Date(date);
    const formattedDate = eventDate.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
    
    popover.innerHTML = `
        <div class="event-popover-title">${title}</div>
        <div class="event-popover-date">${formattedDate}</div>
        ${description ? `<div class="event-popover-description">${description}</div>` : ''}
        <div class="event-popover-actions">
            <button class="event-popover-delete" onclick="deleteEvent(${eventId})">Delete</button>
        </div>
    `;
    
    document.body.appendChild(popover);
    activePopover = popover;
    
    // Close on click outside
    setTimeout(() => {
        document.addEventListener('click', function closePopover(e) {
            if (!popover.contains(e.target) && !e.target.closest('.calendar-event-dot')) {
                popover.style.display = 'none';
                document.body.removeChild(popover);
                document.removeEventListener('click', closePopover);
                activePopover = null;
            }
        });
    }, 10);
}

// Show event tooltip on hover
function showEventTooltip(e, title) {
    // Simple tooltip could be added here if needed
}

// Show event hover card
function showEventHoverCard(e, date, events) {
    // Hide existing hover card first
    if (activeHoverCard) {
        hideEventHoverCard();
    }
    
    if (!events || events.length === 0) {
        return;
    }
    
    const hoverCard = document.createElement('div');
    hoverCard.className = 'event-hover-card';
    hoverCard.id = 'eventHoverCard';
    
    // Format date
    const dateStr = date.toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric'
    });
    
    // Build events list
    let eventsHTML = '';
    if (events.length === 1) {
        // Single event - show detailed view
        const event = events[0];
        const eventDate = new Date(event.date);
        const timeStr = eventDate.toLocaleTimeString('en-US', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
        
        const typeLabels = {
            'appointment': 'Appointment',
            'meeting': 'Meeting',
            'event': 'Event'
        };
        
        const shortDateStr = date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric' 
        });
        
        eventsHTML = `
            <div class="event-hover-card-content">
                <div class="event-hover-card-header">
                    <h3 class="event-hover-card-title">${escapeHtml(event.title || 'Untitled Event')}</h3>
                    ${event.description ? `<p class="event-hover-card-description">${escapeHtml(event.description)}</p>` : ''}
                </div>
                <div class="event-hover-card-meta">
                    <span>${timeStr}</span>
                    <span class="separator"></span>
                    <span>${typeLabels[event.type] || 'Event'}</span>
                    <span class="separator"></span>
                    <span>${shortDateStr}</span>
                </div>
            </div>
        `;
    } else {
        // Multiple events - show list
        const shortDateStr = date.toLocaleDateString('en-US', { 
            month: 'short', 
            day: 'numeric',
            year: 'numeric'
        });
        
        eventsHTML = '<div class="event-hover-card-content"><div class="event-hover-card-header"><h3 class="event-hover-card-title">' + events.length + ' Events on ' + shortDateStr + '</h3></div><div class="event-hover-card-events-list">';
        
        events.forEach(event => {
            const eventDate = new Date(event.date);
            const timeStr = eventDate.toLocaleTimeString('en-US', { 
                hour: '2-digit', 
                minute: '2-digit' 
            });
            
            const typeLabels = {
                'appointment': 'Appointment',
                'meeting': 'Meeting',
                'event': 'Event'
            };
            
            eventsHTML += `
                <div class="event-hover-card-event-item ${event.type || 'appointment'}">
                    <div class="event-hover-card-event-title">${escapeHtml(event.title || 'Untitled Event')}</div>
                    <div class="event-hover-card-event-time">${timeStr} · ${typeLabels[event.type] || 'Event'}</div>
                    ${event.description ? `<div class="event-hover-card-event-description">${escapeHtml(event.description)}</div>` : ''}
                </div>
            `;
        });
        
        eventsHTML += '</div></div>';
    }
    
    hoverCard.innerHTML = eventsHTML;
    document.body.appendChild(hoverCard);
    
    // Position the hover card - use the card element from the event
    const cardElement = e.currentTarget || e.target?.closest('.calendar-day-card');
    if (!cardElement) {
        // Fallback positioning
        hoverCard.style.left = '50%';
        hoverCard.style.top = '50%';
        hoverCard.style.transform = 'translate(-50%, -50%)';
        activeHoverCard = hoverCard;
        setTimeout(() => {
            requestAnimationFrame(() => {
                hoverCard.classList.add('show');
            });
        }, 10);
        return;
    }
    
    const cardRect = cardElement.getBoundingClientRect();
    const viewportWidth = window.innerWidth;
    const viewportHeight = window.innerHeight;
    
    // Get dimensions after adding to DOM
    const hoverCardRect = hoverCard.getBoundingClientRect();
    const cardWidth = hoverCardRect.width || 320;
    const cardHeight = hoverCardRect.height || 200;
    
    let left = cardRect.left + (cardRect.width / 2) - (cardWidth / 2);
    let top = cardRect.bottom + 12;
    
    // Adjust if card goes off screen horizontally
    if (left + cardWidth > viewportWidth - 20) {
        left = viewportWidth - cardWidth - 20;
    }
    if (left < 20) {
        left = 20;
    }
    
    // If card would go below viewport, show above instead
    if (top + cardHeight > viewportHeight - 20) {
        top = cardRect.top - cardHeight - 12;
        if (top < 20) {
            top = 20;
        }
    }
    
    hoverCard.style.left = left + 'px';
    hoverCard.style.top = top + 'px';
    hoverCard.style.display = 'block';
    hoverCard.style.visibility = 'visible';
    hoverCard.style.opacity = '0';
    
    // Force reflow to ensure styles are applied
    hoverCard.offsetHeight;
    
    // Trigger animation immediately
    requestAnimationFrame(() => {
        requestAnimationFrame(() => {
            hoverCard.classList.add('show');
            hoverCard.style.opacity = '1';
        });
    });
    
    activeHoverCard = hoverCard;
    
    // Keep card visible when hovering over it
    hoverCard.addEventListener('mouseenter', () => {
        clearTimeout(hoverCardTimeout);
    });
    
    hoverCard.addEventListener('mouseleave', () => {
        hideEventHoverCard();
    });
}

// Hide event hover card
function hideEventHoverCard() {
    if (activeHoverCard) {
        activeHoverCard.classList.remove('show');
        activeHoverCard.style.opacity = '0';
        activeHoverCard.style.visibility = 'hidden';
        setTimeout(() => {
            if (activeHoverCard && activeHoverCard.parentNode) {
                try {
                    activeHoverCard.parentNode.removeChild(activeHoverCard);
                } catch (e) {
                    // Element might already be removed
                }
            }
            activeHoverCard = null;
        }, 250);
    }
}

// Escape HTML to prevent XSS
function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Apply 3D effect - DISABLED
function apply3DEffect() {
    const wall = document.getElementById('calendarWall');
    if (!wall) return;
    
    // Disable 3D transforms - keep calendar flat
    Array.from(wall.children).forEach((card) => {
        card.style.transform = 'none';
        card.style.zIndex = '1';
    });
    
    // Keep wall flat
    wall.style.transform = 'none';
}

// Update wall transform - DISABLED
function updateWallTransform() {
    const wall = document.getElementById('calendarWall');
    if (wall) {
        wall.style.transform = 'none';
    }
}

// Initialize 3D interaction handlers - DISABLED
let interactionHandlersInitialized = false;
function init3DInteractions() {
    // Disabled - no 3D interactions
    interactionHandlersInitialized = true;
}

// Add event
async function addEvent() {
    const title = document.getElementById('eventTitle').value.trim();
    const date = document.getElementById('eventDate').value;
    const description = document.getElementById('eventDescription').value.trim();
    const type = document.getElementById('eventType').value;
    
    if (!title || !date) {
        alert('Please fill in the event title and date.');
        return;
    }
    
    try {
        const response = await fetch('{{ route("dashboard.calendar.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
            body: JSON.stringify({
                title,
                date,
                description,
                event_type: type,
            }),
        });
        
        const data = await response.json();
        
        if (data.success) {
            // Reset form
            resetEventForm();
            
            // Reload events
            await loadEvents();
        } else {
            alert('Error adding event. Please try again.');
        }
    } catch (error) {
        console.error('Error adding event:', error);
        alert('Error adding event. Please try again.');
    }
}

// Delete event
async function deleteEvent(eventId) {
    if (!confirm('Are you sure you want to delete this event?')) {
        return;
    }
    
    try {
        const response = await fetch(`{{ route("dashboard.calendar.destroy", ":id") }}`.replace(':id', eventId), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            },
        });
        
        const data = await response.json();
        
        if (data.success) {
            if (activePopover) {
                activePopover.style.display = 'none';
                document.body.removeChild(activePopover);
                activePopover = null;
            }
            await loadEvents();
        } else {
            alert('Error deleting event. Please try again.');
        }
    } catch (error) {
        console.error('Error deleting event:', error);
        alert('Error deleting event. Please try again.');
    }
}

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeCalendarModal();
    }
});
</script>

