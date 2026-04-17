<div class="col-xl-12">
    <div class="uda-hero">
        <div class="uda-hero-grid">
            <!-- Greeting -->
            <div class="uda-greeting">
                <div class="uda-avatar-wrap">
                    <span class="uda-avatar-inner">
                        <img class="uda-avatar" loading="lazy" src="{{ auth()->user()->profile_picture_url }}" alt="Profile Picture">
                    </span>
                    <a href="{{ route('dashboard.settings') }}" class="uda-avatar-edit" title="Update profile picture" aria-label="Update profile picture">
                        <i class="icofont-plus"></i>
                    </a>
                </div>
                <div class="uda-greeting-text">
                    <div class="uda-hello">Hello</div>
                    <div class="uda-name">{{ auth()->user()->first_name }} {{ auth()->user()->last_name }}</div>
                </div>
            </div>

            <!-- Calendar Section -->
            <div class="uda-calendar-section">
                <div class="uda-separator"></div>
                <div class="uda-calendar-widget" onclick="openCalendarModal()" style="cursor: pointer;">
                    <svg class="uda-calendar-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span class="uda-calendar-text">Calendar</span>
                </div>
            </div>

            <!-- Search -->
            <div class="uda-search">
                <form action="{{ route('exam.search') }}" method="GET">
                    <div class="uda-search-box">
                        <i class="icofont-search-1 uda-search-icon" aria-hidden="true"></i>
                        <input class="uda-search-input" type="search" name="query" placeholder="Search for documents..." aria-label="Search for documents">
                        <button type="submit" class="uda-btn uda-btn-primary uda-search-btn">Search</button>
                    </div>
                </form>
            </div>

            <!-- Actions -->
            <div class="uda-actions">
                <a href="{{ route('dashboard.create') }}" class="uda-btn uda-btn-primary">
                    <i class="icofont-plus-circle"></i>
                    <span>Add Exam</span>
                </a>
                <a href="{{ route('dashboard.file.create') }}" class="uda-btn uda-btn-secondary">
                    <i class="icofont-file-alt"></i>
                    <span>Add File</span>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- <style>
.form-control:focus {
  box-shadow: none;
} 

.form-control-underlined {
  border-width: 0;
  border-bottom-width: 1px;
  border-radius: 0;
  padding-left: 0;
}
</style> -->
