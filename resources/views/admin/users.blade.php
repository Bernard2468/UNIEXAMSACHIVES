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
                            <div class="search-box">
                                <input type="text" class="search-input" id="searchInput" placeholder="Search users by name or email...">
                                <button class="search-btn" onclick="performSearch()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            
                            <div class="filter-tabs">
                                <a href="#" class="filter-tab active" data-filter="all">All Users</a>
                                <a href="#" class="filter-tab" data-filter="approved">Approved Only</a>
                                <a href="#" class="filter-tab" data-filter="pending">Pending Only</a>
                                <a href="#" class="filter-tab" data-filter="recent">Recently Added</a>
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
                        <div class="container">
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
                                                    <div style="display:flex; gap:0.5rem;">
                                                        @if (!$user->is_approve)
                                                        <form action="{{ route('users.approve', $user->id) }}" method="post" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="action-btn approve" style="min-width:auto; padding:8px 12px;">
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                        @else
                                                        <form action="{{ route('users.disapprove', $user->id) }}" method="post" style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="action-btn disapprove" style="min-width:auto; padding:8px 12px;">
                                                                <i class="fas fa-thumbs-down"></i>
                                                            </button>
                                                        </form>
                                                        @endif
                                                        <form action="{{ route('users.destroy', $user->id) }}" method="post" style="display: inline;" id="delete-user-table-form-{{ $user->id }}">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="button" class="action-btn delete" style="min-width:auto; padding:8px 12px;" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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
                                    <h4>No Users Found</h4>
                                    <p>There are no users currently registered in the system.</p>
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
</script>

@endsection

<script>
// Search and Filter functionality for Users Management
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const userCards = document.querySelectorAll('.user-card');
    const gridView = document.getElementById('gridView');
    const tableView = document.getElementById('tableView');
    const gridViewBtn = document.getElementById('gridViewBtn');
    const tableViewBtn = document.getElementById('tableViewBtn');
    const tableRows = document.querySelectorAll('#usersTable tbody tr');

    // Search functionality
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('.filter-tab.active').getAttribute('data-filter');

        // Grid items
        userCards.forEach(card => {
            const searchData = card.getAttribute('data-search');
            const cardStatus = card.getAttribute('data-status');
            let showBySearch = searchData.includes(searchTerm);
            let showByFilter = activeFilter === 'all' || (activeFilter === 'approved' && cardStatus === 'approved') || (activeFilter === 'pending' && cardStatus === 'pending') || (activeFilter === 'recent');
            card.style.display = (showBySearch && showByFilter) ? 'block' : 'none';
            if (showBySearch && showByFilter) card.style.animation = 'fadeIn 0.3s ease';
        });

        // Table rows
        tableRows.forEach(row => {
            const searchData = row.getAttribute('data-search');
            const rowStatus = row.getAttribute('data-status');
            let showBySearch = searchData.includes(searchTerm);
            let showByFilter = activeFilter === 'all' || (activeFilter === 'approved' && rowStatus === 'approved') || (activeFilter === 'pending' && rowStatus === 'pending') || (activeFilter === 'recent');
            row.style.display = (showBySearch && showByFilter) ? '' : 'none';
        });
    }

    // Search on input
    searchInput.addEventListener('input', performSearch);

    // Filter tabs
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Perform search with new filter
            performSearch();
        });
    });

    // Add fade-in animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);

    // Toggle view handlers
    gridViewBtn.addEventListener('click', function() {
        gridViewBtn.classList.add('active');
        tableViewBtn.classList.remove('active');
        gridView.style.display = 'grid';
        tableView.style.display = 'none';
    });

    tableViewBtn.addEventListener('click', function() {
        tableViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
        gridView.style.display = 'none';
        tableView.style.display = 'block';
    });
});

// Make performSearch global for the search button
function performSearch() {
    const event = new Event('input');
    document.getElementById('searchInput').dispatchEvent(event);
}

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
