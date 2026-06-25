@extends('layout.app')

@push('styles')
<!-- Inter Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Modern Users Management Page Styles - Consistent Theme */
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .users-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .users-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="users-grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(100,116,139,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23users-grid)" /></svg>');
        opacity: 0.7;
    }

    .users-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #475569 0%, #64748b 50%, #94a3b8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        color: #475569;
        margin-bottom: 2rem;
    }

    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 3rem;
        margin-top: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #64748b;
        display: block;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #475569;
        margin-top: 0.5rem;
    }

    /* Search and Filter Section */
    .search-filter-section {
        background: white;
        padding: 2rem 0;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .search-box {
        position: relative;
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        font-size: 1rem;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #64748b;
        background: white;
        box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.1);
    }

    .search-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: #64748b;
        border: none;
        padding: 8px 12px;
        border-radius: 50px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        background: #475569;
    }

    .filter-tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 8px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 25px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .filter-tab:hover,
    .filter-tab.active {
        border-color: #64748b;
        background: #64748b;
        color: white;
        text-decoration: none;
    }

    /* Modern User Cards */
    .users-section {
        background: #f9fafb;
        padding: 3rem 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .users-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .user-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .user-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .user-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .user-card-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.8rem;
        box-shadow: 0 4px 12px rgba(148, 163, 184, 0.3);
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 1.4rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
    }

    .user-email {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
        word-break: break-all;
    }

    .user-status-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .status-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.approved {
        background: rgba(220, 252, 231, 0.8);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-badge.pending {
        background: rgba(254, 243, 199, 0.8);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .user-actions {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        border: none;
        min-width: 100px;
        justify-content: center;
    }

    .action-btn:hover {
        text-decoration: none;
        transform: translateY(-2px);
    }

    .action-btn.approve {
        background: #10b981;
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .action-btn.approve:hover {
        background: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .action-btn.disapprove {
        background: #ef4444;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .action-btn.disapprove:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .action-btn.delete {
        background: #ef4444;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .action-btn.delete:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .no-users {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        grid-column: 1 / -1;
    }

    /* Table View Styles */
    .view-toggle {
        display: inline-flex;
        gap: 0.5rem;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        overflow: hidden;
        background: white;
    }
    .view-toggle button {
        padding: 8px 12px;
        border: none;
        background: transparent;
        color: #6b7280;
        font-weight: 600;
        cursor: pointer;
    }
    .view-toggle button.active {
        background: #64748b;
        color: white;
    }
    .users-table-wrapper {
        background: white;
        border-radius: 16px;
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        overflow: auto;
    }
    table.users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }
    table.users-table th, table.users-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.95rem;
    }
    table.users-table th {
        background: #f8fafc;
        color: #475569;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .status-chip.approved { background: rgba(220, 252, 231, 0.8); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
    .status-chip.pending { background: rgba(254, 243, 199, 0.8); color: #d97706; border: 1px solid rgba(245,158,11,0.3); }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 16px 16px;
        flex-wrap: wrap;
    }
    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
        white-space: nowrap;
    }
    .pagination-info strong { color: #1f2937; font-weight: 600; }
    .pagination-controls { display: flex; align-items: center; gap: 0.5rem; }
    .pagination { display: flex; list-style: none; margin: 0; padding: 0; gap: 0.25rem; }
    .pagination-item { display: inline-block; }
    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: white;
        color: #374151;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pagination-link:hover:not(.disabled):not(.active) {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #1f2937;
    }
    .pagination-link.active {
        background: #64748b;
        color: white;
        border-color: #64748b;
        font-weight: 600;
    }
    .pagination-link.disabled {
        color: #9ca3af;
        cursor: not-allowed;
        background: #f9fafb;
        opacity: 0.5;
    }
    .pagination-link.icon { width: 2.5rem; padding: 0; }
    .pagination-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }
    .page-size-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .page-size-label { font-size: 0.875rem; color: #6b7280; }
    .page-size-select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background: white;
        color: #374151;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .pagination-wrapper { flex-direction: column; align-items: stretch; }
        .pagination-controls { justify-content: center; }
        .page-size-selector { justify-content: center; }
    }

    /* Live search loading state */
    .search-input.is-searching {
        background-image: linear-gradient(90deg, transparent, rgba(100,116,139,0.08), transparent);
        background-size: 200% 100%;
        animation: searchShimmer 1.2s linear infinite;
    }
    @keyframes searchShimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    #usersResultsContainer.is-loading {
        opacity: 0.55;
        pointer-events: none;
        transition: opacity 0.15s ease;
    }
    .search-spinner {
        position: absolute;
        right: 56px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-top-color: #475569;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        display: none;
    }
    .search-spinner.visible { display: block; }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    /* Staff Category Styles */
    .staff-category-badge {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(147, 197, 253, 0.8);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.3);
        margin-top: 0.5rem;
    }

    .staff-category-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(147, 197, 253, 0.8);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    /* Position Badge Styles */
    .position-badge {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(196, 181, 253, 0.8);
        color: #5b21b6;
        border: 1px solid rgba(139, 92, 246, 0.3);
        margin-top: 0.5rem;
    }

    .position-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(196, 181, 253, 0.8);
        color: #5b21b6;
        border: 1px solid rgba(139, 92, 246, 0.3);
        white-space: nowrap;
    }

    .position-badge i,
    .position-chip i {
        font-size: 0.65rem;
    }

    .no-category {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.9rem;
    }

    .no-users i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #64748b;
        opacity: 0.6;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .users-list {
            grid-template-columns: 1fr;
        }

        .user-card {
            padding: 1.5rem;
        }

        .user-card-header {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }

        .user-status-section {
            flex-direction: column;
            gap: 1rem;
            align-items: center;
        }

        .user-actions {
            justify-content: center;
        }

        .hero-stats {
            gap: 1.5rem;
        }

        .hero-title {
            font-size: 2rem;
        }
    }

    /* ===== Users table actions: neat borderless icons + kebab on smaller desktops ===== */
    .uactions { position: relative; display: inline-block; }
    .uactions-toggle { display: none; }
    .uactions-menu { display: flex; align-items: center; gap: 6px; }
    .uactions-menu form { display: inline; margin: 0; }
    .uact-label { display: none; }
    .uact {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: none;
        background: transparent;
        border-radius: 8px;
        cursor: pointer;
        color: #64748b;
        font-size: 15px;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .uact:hover { background: #f1f5f9; }
    .uact.approve { color: #2f8f63; }
    .uact.disapprove { color: #c0392b; }
    .uact.edit { color: #2563eb; }
    .uact.delete { color: #c0392b; }

    @media (max-width: 1599px) {
        /* collapse the row into a 3-dot trigger + floating dropdown */
        .uactions-toggle {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 38px;
            height: 38px;
            border: 1px solid #e2e8f0;
            background: #fff;
            border-radius: 10px;
            cursor: pointer;
            transition: background 0.2s ease, border-color 0.2s ease;
        }
        .uactions-toggle:hover { background: #f4f6fb; border-color: #cbd5e1; }
        .uactions-toggle .more-icon { width: 18px; height: 18px; opacity: 0.6; }

        .uactions-menu {
            display: none;
            position: fixed;
            z-index: 1000;
            flex-direction: column;
            align-items: stretch;
            gap: 2px;
            width: 210px;
            padding: 6px;
            background: #fff;
            border: 1px solid #e9edf4;
            border-radius: 12px;
            box-shadow: 0 14px 38px rgba(20, 30, 55, 0.18);
        }
        .uactions-menu.open { display: flex; }
        .uactions-menu form { display: block; width: 100%; }

        .uact {
            width: 100%;
            height: auto;
            justify-content: flex-start;
            gap: 12px;
            padding: 10px 12px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            color: #283041;
        }
        .uact > i { width: 20px; text-align: center; font-size: 15px; }
        .uact.approve { color: #2f8f63; }
        .uact.approve:hover { background: #e8f4ee; }
        .uact.disapprove, .uact.delete { color: #c0392b; }
        .uact.disapprove:hover, .uact.delete:hover { background: #fbecef; }
        .uact.edit { color: #2563eb; }
        .uact.edit:hover { background: #eaf1fe; }
        .uact-label { display: inline; line-height: 1; }
    }
</style>
@endpush

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')
<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding">
        <div class="row">
          @include('components.create_section')
        </div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                {{-- sidebar menu --}}
                @include('components.sidebar')
                <div class="col-xl-9 col-lg-9 col-md-12">
                    <!-- Hero Section -->
                    <div class="users-hero">
                        <div class="container">
                            <div class="users-hero-content">
                                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                    <div>
                                        <h1 class="hero-title">Manage Users Account</h1>
                                        <p class="hero-subtitle">Administer user accounts and permissions</p>
                                    </div>
                                    <button type="button" class="add-user-btn" id="addUserBtn" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-user-plus"></i> Add User
                                    </button>
                                </div>
                                
                                <div class="hero-stats">
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $totalUsers }}</span>
                                        <div class="stat-label">Total Users</div>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $approvedCount }}</span>
                                        <div class="stat-label">Approved</div>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $pendingCount }}</span>
                                        <div class="stat-label">Pending</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="search-filter-section">
                        <div class="container">
                            <form method="GET" action="{{ route('dashboard.users') }}" class="search-box" role="search" id="usersSearchForm">
                                <input type="text" name="search" class="search-input" id="searchInput" placeholder="Search users by name or email..." value="{{ $search ?? '' }}" autocomplete="off">
                                <input type="hidden" name="filter" value="{{ $activeFilter ?? 'all' }}">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">
                                <div class="search-spinner" id="searchSpinner" aria-hidden="true"></div>
                                <button type="button" class="search-btn" id="clearSearchBtn" style="right: 56px; background:#94a3b8; display: {{ !empty($search) ? 'flex' : 'none' }};" title="Clear search">
                                    <i class="fas fa-times"></i>
                                </button>
                                <button type="submit" class="search-btn">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>

                            <div class="filter-tabs">
                                <a href="{{ route('dashboard.users', ['filter' => 'all', 'search' => $search ?? null, 'per_page' => request('per_page', 15)]) }}" class="filter-tab {{ ($activeFilter ?? 'all') === 'all' ? 'active' : '' }}">All Users</a>
                                <a href="{{ route('dashboard.users', ['filter' => 'approved', 'search' => $search ?? null, 'per_page' => request('per_page', 15)]) }}" class="filter-tab {{ ($activeFilter ?? '') === 'approved' ? 'active' : '' }}">Approved Only</a>
                                <a href="{{ route('dashboard.users', ['filter' => 'pending', 'search' => $search ?? null, 'per_page' => request('per_page', 15)]) }}" class="filter-tab {{ ($activeFilter ?? '') === 'pending' ? 'active' : '' }}">Pending Only</a>
                                <a href="{{ route('dashboard.users', ['filter' => 'recent', 'search' => $search ?? null, 'per_page' => request('per_page', 15)]) }}" class="filter-tab {{ ($activeFilter ?? '') === 'recent' ? 'active' : '' }}">Recently Added</a>
                            </div>
                            <div style="margin-top:1rem; display:flex; justify-content:center;">
                                <div class="view-toggle" role="group" aria-label="View toggle">
                                    <button type="button" id="gridViewBtn" class="active"><i class="fas fa-th-large"></i> Grid</button>
                                    <button type="button" id="tableViewBtn"><i class="fas fa-table"></i> Table</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Users Section -->
                    <div class="users-section">
                        <div class="container" id="usersResultsContainer">
                            @if ($users->total() > 0)
                                <div class="users-list" id="gridView">
                                    @foreach ($users as $user)
                                        <div class="user-card" data-status="{{ $user->is_approve ? 'approved' : 'pending' }}" data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                            <div class="user-card-header">
                                                <div class="user-avatar">
                                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                </div>
                                                <div class="user-info">
                                                    <h3 class="user-name">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                                    <p class="user-email">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="user-status-section">
                                                <div class="status-badge {{ $user->is_approve ? 'approved' : 'pending' }}">
                                                    <i class="fas {{ $user->is_approve ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                                    {{ $user->is_approve ? 'Approved' : 'Pending' }}
                                                </div>
                                                @if($user->staff_category)
                                                    <div class="staff-category-badge">
                                                        <i class="fas fa-user-tag"></i>
                                                        {{ $user->staff_category }}
                                                    </div>
                                                @endif
                                                @if($user->position)
                                                    <div class="position-badge">
                                                        <i class="fas fa-briefcase"></i>
                                                        {{ $user->position->name }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="user-actions">
                                                @if (!$user->is_approve)
                                                    <form action="{{ route('users.approve', $user->id) }}" method="post" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="action-btn approve">
                                                            <i class="fas fa-check"></i>
                                                            Approve
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('users.disapprove', $user->id) }}" method="post" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="action-btn disapprove">
                                                            <i class="fas fa-thumbs-down"></i>
                                                            Disapprove
                                                        </button>
                                                    </form>
                                                @endif

                                                <button type="button" class="action-btn" style="background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;" onclick="openEditInfoModal({{ $user->id }}, '{{ addslashes($user->first_name . ' ' . $user->last_name) }}', '{{ addslashes($user->email) }}', '{{ $user->department_id }}', '{{ addslashes($user->staff_category ?? '') }}', '{{ $user->position_id }}')">
                                                    <i class="fas fa-user-edit"></i>
                                                    Edit Info
                                                </button>

                                                <form action="{{ route('users.destroy', $user->id) }}" method="post" style="display: inline;" id="delete-user-form-{{ $user->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn delete" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                        <i class="fas fa-trash"></i>
                                                        Delete
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="users-table-wrapper" id="tableView" style="display:none;">
                                    <table class="users-table" id="usersTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Staff Category</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $index => $user)
                                            <tr data-status="{{ $user->is_approve ? 'approved' : 'pending' }}" data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                                        <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                                                        @if($user->position)
                                                            <span class="position-chip">
                                                                <i class="fas fa-briefcase"></i>
                                                                {{ $user->position->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if($user->staff_category)
                                                        <span class="staff-category-chip">
                                                            <i class="fas fa-user-tag"></i>
                                                            {{ $user->staff_category }}
                                                        </span>
                                                    @else
                                                        <span class="no-category">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="status-chip {{ $user->is_approve ? 'approved' : 'pending' }}">
                                                        <i class="fas {{ $user->is_approve ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                                        {{ $user->is_approve ? 'Approved' : 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="uactions">
                                                        <button type="button" class="uactions-toggle" title="Actions" aria-haspopup="true" aria-expanded="false" onclick="toggleUserActions(this, event)">
                                                            <img src="https://img.icons8.com/glyph-neue/64/more.png" alt="Actions" class="more-icon">
                                                        </button>
                                                        <div class="uactions-menu">
                                                            @if (!$user->is_approve)
                                                            <form action="{{ route('users.approve', $user->id) }}" method="post">
                                                                @csrf
                                                                <button type="submit" class="uact approve" title="Approve">
                                                                    <i class="fas fa-check"></i>
                                                                    <span class="uact-label">Approve</span>
                                                                </button>
                                                            </form>
                                                            @else
                                                            <form action="{{ route('users.disapprove', $user->id) }}" method="post">
                                                                @csrf
                                                                <button type="submit" class="uact disapprove" title="Disapprove">
                                                                    <i class="fas fa-thumbs-down"></i>
                                                                    <span class="uact-label">Disapprove</span>
                                                                </button>
                                                            </form>
                                                            @endif
                                                            <button type="button" class="uact edit" title="Edit Info (Email / Department / Staff Category / Position)" onclick="openEditInfoModal({{ $user->id }}, '{{ addslashes($user->first_name . ' ' . $user->last_name) }}', '{{ addslashes($user->email) }}', '{{ $user->department_id }}', '{{ addslashes($user->staff_category ?? '') }}', '{{ $user->position_id }}')">
                                                                <i class="fas fa-user-edit"></i>
                                                                <span class="uact-label">Edit info</span>
                                                            </button>
                                                            <form action="{{ route('users.destroy', $user->id) }}" method="post" id="delete-user-table-form-{{ $user->id }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="uact delete" title="Delete" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                                    <i class="fas fa-trash"></i>
                                                                    <span class="uact-label">Delete</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($users->hasPages())
                                <div class="pagination-wrapper">
                                    <div class="pagination-info">
                                        Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                                    </div>
                                    <div class="pagination-controls">
                                        <ul class="pagination">
                                            @if ($users->onFirstPage())
                                                <li class="pagination-item">
                                                    <span class="pagination-link icon disabled"><i class="fas fa-chevron-left"></i></span>
                                                </li>
                                            @else
                                                <li class="pagination-item">
                                                    <a href="{{ $users->previousPageUrl() }}" class="pagination-link icon"><i class="fas fa-chevron-left"></i></a>
                                                </li>
                                            @endif
                                            @php
                                                $currentPage = $users->currentPage();
                                                $lastPage = $users->lastPage();
                                                $startPage = max(1, $currentPage - 2);
                                                $endPage = min($lastPage, $currentPage + 2);
                                            @endphp
                                            @if ($startPage > 1)
                                                <li class="pagination-item">
                                                    <a href="{{ $users->url(1) }}" class="pagination-link">1</a>
                                                </li>
                                                @if ($startPage > 2)
                                                    <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                                                @endif
                                            @endif
                                            @for ($i = $startPage; $i <= $endPage; $i++)
                                                <li class="pagination-item">
                                                    @if ($i == $currentPage)
                                                        <span class="pagination-link active">{{ $i }}</span>
                                                    @else
                                                        <a href="{{ $users->url($i) }}" class="pagination-link">{{ $i }}</a>
                                                    @endif
                                                </li>
                                            @endfor
                                            @if ($endPage < $lastPage)
                                                @if ($endPage < $lastPage - 1)
                                                    <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                                                @endif
                                                <li class="pagination-item">
                                                    <a href="{{ $users->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a>
                                                </li>
                                            @endif
                                            @if ($users->hasMorePages())
                                                <li class="pagination-item">
                                                    <a href="{{ $users->nextPageUrl() }}" class="pagination-link icon"><i class="fas fa-chevron-right"></i></a>
                                                </li>
                                            @else
                                                <li class="pagination-item">
                                                    <span class="pagination-link icon disabled"><i class="fas fa-chevron-right"></i></span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="page-size-selector">
                                        <span class="page-size-label">Per page:</span>
                                        <select class="page-size-select" onchange="changePageSize(this.value)">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </div>
                                </div>
                                @endif
                            @else
                                <div class="no-users">
                                    <i class="fas fa-users"></i>
                                    @if(!empty($search))
                                        <h4>No matches for "{{ $search }}"</h4>
                                        <p>No users match your search. Try a different name or email.</p>
                                        <a href="{{ route('dashboard.users') }}" class="filter-tab" style="margin-top:1rem; display:inline-block;">Clear search</a>
                                    @else
                                        <h4>No Users Found</h4>
                                        <p>There are no users currently registered in the system.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add User Modal -->
<div id="addUserModal" class="add-user-modal" style="display: none;">
    <div class="add-user-modal-overlay"></div>
    <div class="add-user-modal-content">
        <div class="add-user-modal-header">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            <button type="button" class="close-modal-btn" id="closeAddUserModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="add-user-modal-body">
            <form id="addUserForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" name="first_name" id="add-user-firstname" class="animated-input" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" name="last_name" id="add-user-lastname" class="animated-input" placeholder="Last name" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <input type="email" name="email" id="add-user-email" class="animated-input" placeholder="Enter email address" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <select name="department_id" id="add-user-department" class="animated-input" required>
                            <option value="" disabled selected>Choose Department/Faculty/Unit</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <select name="staff_category" id="add-user-staff-category" class="animated-input" required>
                            <option value="" disabled selected>Choose Staff Category</option>
                            <option value="Junior Staff">Junior Staff</option>
                            <option value="Senior Staff">Senior Staff</option>
                            <option value="Senior Member (Non-Teaching)">Senior Member (Non-Teaching)</option>
                            <option value="Senior Member (Teaching)">Senior Member (Teaching)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <select name="position_id" id="add-user-position" class="animated-input">
                            <option value="" selected>Choose Position (Optional)</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <input type="password" name="temporary_password" id="add-user-password" class="animated-input" placeholder="Set temporary password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="toggleAddUserPassword()">
                            <i class="icofont-eye"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">User will be required to change this password on first login</small>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <input type="password" name="temporary_password_confirmation" id="add-user-password-confirm" class="animated-input" placeholder="Confirm temporary password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="toggleAddUserPasswordConfirm()">
                            <i class="icofont-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-actions-modal">
                    <button type="button" class="cancel-btn" id="cancelAddUserBtn">Cancel</button>
                    <button type="submit" class="submit-btn-modal">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Info Modal — clean monochrome design (namespaced .efi-*) -->
<div id="editInfoModal" class="add-user-modal efi-modal" style="display: none;">
    <div class="add-user-modal-overlay efi-overlay"></div>
    <div class="efi-card" role="dialog" aria-modal="true" aria-labelledby="efiTitle">
        <button type="button" class="efi-close" onclick="closeEditInfoModal()" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>

        <div class="efi-head">
            <div class="efi-avatar"><span id="editInfoInitials">--</span></div>
            <div class="efi-head__text">
                <h3 class="efi-title" id="efiTitle">Edit User Info</h3>
                <p class="efi-sub" id="editInfoUserName">—</p>
            </div>
        </div>

        <form id="editInfoForm" method="POST" class="efi-body">
            @csrf
            @method('PATCH')

            <div class="efi-field">
                <label class="efi-label"><i class="fas fa-envelope"></i> Email address</label>
                <div class="efi-control">
                    <input type="email" name="email" id="editInfoEmail" class="efi-input" placeholder="name@institution.edu" required>
                </div>
            </div>

            <div class="efi-field">
                <label class="efi-label"><i class="fas fa-building-columns"></i> Department / Faculty / Unit</label>
                <div class="efi-control">
                    <select name="department_id" id="editInfoDepartment" class="efi-select" required>
                        <option value="" disabled>Choose department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down efi-control__chev"></i>
                </div>
            </div>

            <div class="efi-grid">
                <div class="efi-field">
                    <label class="efi-label"><i class="fas fa-user-tag"></i> Staff Category</label>
                    <div class="efi-control">
                        <select name="staff_category" id="editInfoCategory" class="efi-select" required>
                            <option value="" disabled>Choose category</option>
                            <option value="Junior Staff">Junior Staff</option>
                            <option value="Senior Staff">Senior Staff</option>
                            <option value="Senior Member (Non-Teaching)">Senior Member (Non-Teaching)</option>
                            <option value="Senior Member (Teaching)">Senior Member (Teaching)</option>
                        </select>
                        <i class="fas fa-chevron-down efi-control__chev"></i>
                    </div>
                </div>

                <div class="efi-field">
                    <label class="efi-label"><i class="fas fa-briefcase"></i> Position <span class="efi-opt">optional</span></label>
                    <div class="efi-control">
                        <select name="position_id" id="editInfoPosition" class="efi-select">
                            <option value="">No position</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down efi-control__chev"></i>
                    </div>
                </div>
            </div>

            <div class="efi-actions">
                <button type="button" class="efi-btn efi-btn--ghost" onclick="closeEditInfoModal()">Cancel</button>
                <button type="submit" class="efi-btn efi-btn--save">
                    <span><i class="fas fa-bolt"></i> Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ===== Edit User Info — clean monochrome modal (scoped .efi-*) ===== */
#editInfoModal .efi-overlay{
    background:rgba(15,23,42,.42);
    -webkit-backdrop-filter:blur(6px);
    backdrop-filter:blur(6px);
    animation:efiFade .25s ease;
}
.efi-card{
    position:relative; z-index:10001;
    width:92%; max-width:480px; max-height:92vh; overflow:hidden auto;
    padding:28px 28px 24px;
    border-radius:20px;
    background:#ffffff;
    border:1px solid #ebedf1;
    box-shadow:0 24px 64px -18px rgba(15,23,42,.30), 0 2px 6px rgba(15,23,42,.05);
    color:#0f172a;
    animation:efiIn .4s cubic-bezier(.2,.8,.2,1);
    scrollbar-width:thin;
}
.efi-card::-webkit-scrollbar{width:8px}
.efi-card::-webkit-scrollbar-thumb{background:#e2e8f0; border-radius:8px}
.efi-head, .efi-body{ position:relative; z-index:1; }
.efi-close{
    position:absolute; top:14px; right:14px; width:34px; height:34px; z-index:3;
    display:grid; place-items:center; border-radius:10px;
    background:transparent; border:1px solid #ebedf1;
    color:#94a3b8; font-size:14px; cursor:pointer; transition:.2s;
}
.efi-close:hover{ background:#f5f6f8; color:#0f172a; }

.efi-head{ display:flex; align-items:center; gap:14px; margin-bottom:22px; }
.efi-avatar{
    position:relative; flex:0 0 auto; width:50px; height:50px; border-radius:14px;
    display:grid; place-items:center; font-weight:700; font-size:17px; letter-spacing:.5px;
    color:#ffffff; background:#0f172a;
}
.efi-avatar__ring{ display:none; }
.efi-title{ margin:0; font-size:1.3rem; font-weight:700; color:#0f172a; letter-spacing:-.01em; }
.efi-title i{ display:none; }
.efi-sub{ margin:2px 0 0; font-size:.875rem; color:#64748b; }

.efi-body{ display:block; }
.efi-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.efi-field{ margin-bottom:14px; }
.efi-label{ display:flex; align-items:center; gap:7px; margin-bottom:7px; font-size:.78rem; font-weight:600; color:#475569; }
.efi-label i{ font-size:.78rem; color:#94a3b8; }
.efi-opt{ font-weight:500; color:#94a3b8; font-size:.78rem; }
.efi-control{ position:relative; }
.efi-control__chev{ position:absolute; right:13px; top:50%; transform:translateY(-50%); font-size:.7rem; color:#94a3b8; pointer-events:none; transition:.2s; }
.efi-input, .efi-select{
    width:100%; box-sizing:border-box; padding:12px 14px;
    border-radius:12px; font-size:.92rem; font-weight:500; color:#0f172a;
    background:#ffffff; border:1px solid #e2e8f0;
    outline:none; transition:.2s ease; -webkit-appearance:none; appearance:none;
}
.efi-select{ padding-right:36px; cursor:pointer; }
.efi-input::placeholder{ color:#cbd5e1; }
.efi-input:focus, .efi-select:focus{
    border-color:#0f172a;
    box-shadow:0 0 0 3px rgba(15,23,42,.08);
}
.efi-control:focus-within .efi-control__chev{ color:#0f172a; transform:translateY(-50%) rotate(180deg); }
.efi-select option{ color:#0f172a; }

.efi-note{ display:flex; align-items:center; gap:8px; margin:4px 0 0; padding:10px 13px; border-radius:10px;
    font-size:.78rem; color:#64748b; background:#f5f6f8; border:1px solid #eef0f3; }
.efi-note i{ color:#94a3b8; }

.efi-actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:20px; padding-top:18px; border-top:1px solid #eef0f3; }
.efi-btn{ padding:11px 22px; border-radius:11px; font-weight:600; font-size:.9rem; cursor:pointer; border:1px solid transparent; transition:.2s; }
.efi-btn--ghost{ background:#ffffff; border-color:#e2e8f0; color:#475569; }
.efi-btn--ghost:hover{ background:#f5f6f8; color:#0f172a; }
.efi-btn--save{ background:#0f172a; color:#ffffff; box-shadow:0 6px 16px -7px rgba(15,23,42,.55); }
.efi-btn--save span{ display:inline-flex; align-items:center; gap:8px; }
.efi-btn--save:hover{ background:#1e293b; transform:translateY(-1px); box-shadow:0 10px 22px -8px rgba(15,23,42,.55); }

@keyframes efiIn{ from{ opacity:0; transform:translateY(14px) scale(.985); } to{ opacity:1; transform:none; } }
@keyframes efiFade{ from{opacity:0} to{opacity:1} }
@media (max-width:520px){ .efi-grid{ grid-template-columns:1fr; } .efi-card{ padding:24px 18px 20px; } }
@media (prefers-reduced-motion:reduce){ .efi-card,.efi-overlay{ animation:none; } }
</style>

<style>
.add-user-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.add-user-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.add-user-modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10001;
    animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.add-user-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 32px;
    border-bottom: 1px solid #e2e8f0;
}

.add-user-modal-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #64748b;
    cursor: pointer;
    padding: 5px;
    transition: color 0.2s;
}

.close-modal-btn:hover {
    color: #1e293b;
}

.add-user-modal-body {
    padding: 32px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.input-container {
    position: relative;
}

.animated-input {
    width: 100%;
    padding: 18px 24px;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    font-size: 1rem;
    background: #f8fafc;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    color: #0f172a;
    font-weight: 500;
    box-sizing: border-box;
}

.animated-input:focus {
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
}

.form-text {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: #64748b;
}

.form-actions-modal {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.cancel-btn {
    padding: 14px 28px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    color: #64748b;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cancel-btn:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.submit-btn-modal {
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.submit-btn-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<script>
// Change page size (pagination)
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Add User Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeModalBtn = document.getElementById('closeAddUserModal');
    const cancelBtn = document.getElementById('cancelAddUserBtn');
    
    if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
            addUserModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }
    
    function closeModal() {
        addUserModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('addUserForm').reset();
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }
    
    // Close on overlay click
    if (addUserModal) {
        addUserModal.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-user-modal-overlay')) {
                closeModal();
            }
        });
    }
});

function toggleAddUserPassword() {
    const passwordInput = document.getElementById('add-user-password');
    const icon = event.target.closest('.password-toggle').querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('icofont-eye');
        icon.classList.add('icofont-eye-blocked');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('icofont-eye-blocked');
        icon.classList.add('icofont-eye');
    }
}

function toggleAddUserPasswordConfirm() {
    const passwordInput = document.getElementById('add-user-password-confirm');
    const icon = event.target.closest('.password-toggle').querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('icofont-eye');
        icon.classList.add('icofont-eye-blocked');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('icofont-eye-blocked');
        icon.classList.add('icofont-eye');
    }
}

function openEditInfoModal(userId, userName, currentEmail, departmentId, staffCategory, positionId) {
    const modal = document.getElementById('editInfoModal');
    const form = document.getElementById('editInfoForm');
    const nameLabel = document.getElementById('editInfoUserName');
    const initials = document.getElementById('editInfoInitials');
    const emailInput = document.getElementById('editInfoEmail');

    form.action = '/dashboard/users/' + userId + '/details';
    nameLabel.textContent = userName;
    if (initials) {
        const parts = (userName || '').trim().split(/\s+/).filter(Boolean);
        const text = parts.length
            ? ((parts[0][0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase()
            : '--';
        initials.textContent = text || '--';
    }
    emailInput.value = currentEmail || '';
    document.getElementById('editInfoDepartment').value = departmentId || '';
    document.getElementById('editInfoCategory').value = staffCategory || '';
    document.getElementById('editInfoPosition').value = positionId || '';

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    emailInput.focus();
}

function closeEditInfoModal() {
    const modal = document.getElementById('editInfoModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editInfoModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-user-modal-overlay')) {
                closeEditInfoModal();
            }
        });
    }
});
</script>

<script>
/* ===== Users table actions kebab (portal to <body> so the table cannot clip it) ===== */
function closeUserActions() {
    document.querySelectorAll('.uactions-menu.open').forEach(function (m) {
        m.classList.remove('open');
        m.style.position = '';
        m.style.top = '';
        m.style.left = '';
        if (m._home && m._home.cell) {
            m._home.cell.appendChild(m);
            if (m._home.toggle) { m._home.toggle.setAttribute('aria-expanded', 'false'); }
        }
    });
}

function toggleUserActions(btn, event) {
    if (event) { event.stopPropagation(); }
    var cell = btn.closest('.uactions');
    var menu = btn._menu || (cell ? cell.querySelector('.uactions-menu') : null);
    if (!menu) { return; }
    btn._menu = menu;

    var isOpen = menu.classList.contains('open');
    closeUserActions();
    if (isOpen) { return; }

    if (!menu._home) { menu._home = { cell: cell, toggle: btn }; }

    document.body.appendChild(menu);                     // portal out of the table
    menu.style.position = 'fixed';
    menu.classList.add('open');

    var r = btn.getBoundingClientRect();
    var mw = menu.offsetWidth || 210;
    var mh = menu.offsetHeight || 0;
    var left = r.right - mw;                             // right-align to the button
    if (left < 8) { left = 8; }
    var top = r.bottom + 6;
    if (top + mh > window.innerHeight - 8) {             // no room below -> flip above
        var above = r.top - 6 - mh;
        top = (above < 8) ? 8 : above;
    }
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    btn.setAttribute('aria-expanded', 'true');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.uactions') && !e.target.closest('.uactions-menu')) {
        closeUserActions();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeUserActions(); }
});
window.addEventListener('scroll', closeUserActions, true);
window.addEventListener('resize', closeUserActions);
</script>

@endsection

<script>
// View toggle + live search for Users Management
(function() {
    let currentView = 'grid';
    let searchDebounce = null;
    let activeRequest = null;

    function applyView() {
        const gridView = document.getElementById('gridView');
        const tableView = document.getElementById('tableView');
        const gridViewBtn = document.getElementById('gridViewBtn');
        const tableViewBtn = document.getElementById('tableViewBtn');
        if (!gridView || !tableView || !gridViewBtn || !tableViewBtn) return;

        if (currentView === 'table') {
            tableViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
            gridView.style.display = 'none';
            tableView.style.display = 'block';
        } else {
            gridViewBtn.classList.add('active');
            tableViewBtn.classList.remove('active');
            gridView.style.display = 'grid';
            tableView.style.display = 'none';
        }
    }

    function bindViewToggle() {
        const gridViewBtn = document.getElementById('gridViewBtn');
        const tableViewBtn = document.getElementById('tableViewBtn');
        if (gridViewBtn) gridViewBtn.addEventListener('click', function() { currentView = 'grid'; applyView(); });
        if (tableViewBtn) tableViewBtn.addEventListener('click', function() { currentView = 'table'; applyView(); });
    }

    function runLiveSearch() {
        const form = document.getElementById('usersSearchForm');
        const container = document.getElementById('usersResultsContainer');
        const spinner = document.getElementById('searchSpinner');
        const input = document.getElementById('searchInput');
        if (!form || !container) return;

        const params = new URLSearchParams(new FormData(form));
        params.set('page', '1');
        const url = form.action + '?' + params.toString();

        if (activeRequest) activeRequest.abort();
        const controller = new AbortController();
        activeRequest = controller;

        container.classList.add('is-loading');
        if (spinner) spinner.classList.add('visible');
        if (input) input.classList.add('is-searching');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            signal: controller.signal,
            credentials: 'same-origin'
        })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const fresh = doc.getElementById('usersResultsContainer');
            if (fresh) {
                container.innerHTML = fresh.innerHTML;
                applyView();
            }
            window.history.replaceState({}, '', url);
        })
        .catch(function(err) {
            if (err.name !== 'AbortError') console.error('Live search failed:', err);
        })
        .finally(function() {
            if (activeRequest === controller) {
                container.classList.remove('is-loading');
                if (spinner) spinner.classList.remove('visible');
                if (input) input.classList.remove('is-searching');
                activeRequest = null;
            }
        });
    }

    function scheduleSearch() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(runLiveSearch, 300);
    }

    function updateClearButton() {
        const input = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        if (!input || !clearBtn) return;
        clearBtn.style.display = input.value.trim() !== '' ? 'flex' : 'none';
    }

    function bindFilterTabs() {
        const tabs = document.querySelectorAll('.filter-tab');
        const filterInput = document.querySelector('#usersSearchForm input[name="filter"]');
        tabs.forEach(function(tab) {
            tab.addEventListener('click', function(e) {
                const href = tab.getAttribute('href');
                if (!href || href === '#') return;
                let tabFilter = 'all';
                try {
                    const tabUrl = new URL(href, window.location.origin);
                    tabFilter = tabUrl.searchParams.get('filter') || 'all';
                } catch (err) { return; }
                e.preventDefault();
                if (filterInput) filterInput.value = tabFilter;
                tabs.forEach(function(t) { t.classList.remove('active'); });
                tab.classList.add('active');
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        bindViewToggle();
        bindFilterTabs();
        updateClearButton();

        const input = document.getElementById('searchInput');
        const form = document.getElementById('usersSearchForm');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (input) {
            input.addEventListener('input', function() {
                updateClearButton();
                scheduleSearch();
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && input.value) {
                    input.value = '';
                    updateClearButton();
                    scheduleSearch();
                }
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (!input) return;
                input.value = '';
                updateClearButton();
                input.focus();
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        }
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        }
    });
})();

// Confirmation modal function for user deletion
function confirmDeleteUser(userId, userName) {
    confirmDelete(
        `Are you sure you want to delete "${userName}"?`,
        function() {
            // Try both possible form IDs (card view and table view)
            const cardForm = document.getElementById('delete-user-form-' + userId);
            const tableForm = document.getElementById('delete-user-table-form-' + userId);
            
            if (cardForm) {
                cardForm.submit();
            } else if (tableForm) {
                tableForm.submit();
            }
        }
    );
}
</script>
