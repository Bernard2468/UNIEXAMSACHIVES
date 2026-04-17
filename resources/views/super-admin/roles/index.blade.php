@extends('super-admin.layout')

@section('title', 'User Roles')

@push('styles')
<style>
    /* System Font Stack */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    /* Centered Container */
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Page Header Style */
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

    /* Stats Cards */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }

    .stat-card {
        background: white;
        border-radius: 0.75rem;
        padding: 1.25rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
    }

    .stat-card-label {
        font-size: 0.875rem;
        color: #6b7280;
        margin-bottom: 0.5rem;
    }

    .stat-card-value {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1f2937;
    }

    /* Modern Card */
    .modern-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .modern-card-header {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #1f2937;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modern-card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.125rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modern-card-body {
        padding: 1.5rem;
    }

    /* Table Styling */
    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table thead {
        background: #f9fafb;
    }

    .modern-table th {
        padding: 0.875rem 1rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
        color: #374151;
        border-bottom: 1px solid #e5e7eb;
    }

    .modern-table td {
        padding: 1rem;
        border-bottom: 1px solid #f3f4f6;
        font-size: 0.875rem;
        color: #1f2937;
    }

    .modern-table tbody tr:hover {
        background: #f9fafb;
    }

    /* Badge Styling */
    .badge-modern {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-super-admin {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-admin {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-user {
        background: #e0e7ff;
        color: #3730a3;
    }

    /* Action Buttons */
    .btn-modern {
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
    }

    .btn-modern-primary {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.05));
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .btn-modern-primary:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(248, 113, 113, 0.05));
        color: #991b1b;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-modern-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(248, 113, 113, 0.1));
        transform: translateY(-1px);
    }

    /* Pagination (same as Manage Users page) */
    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 1rem 1rem;
        flex-wrap: wrap;
    }
    .pagination-info { font-size: 0.875rem; color: #6b7280; white-space: nowrap; }
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
    .page-size-selector { display: flex; align-items: center; gap: 0.5rem; }
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
</style>
@endpush

@section('content')
<div class="page-container">
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">User Roles</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-users-alt-3"></i>
                    <span> - User Roles</span>
                </div>
            </div>
            <p class="page-header-description">Manage user roles and permissions</p>
        </div>
    </div>

    {{-- Statistics --}}
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-card-label">Total Users</div>
            <div class="stat-card-value">{{ $users->total() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Super Admins</div>
            <div class="stat-card-value">{{ $superAdmins->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Admins</div>
            <div class="stat-card-value">{{ $admins->count() }}</div>
        </div>
        <div class="stat-card">
            <div class="stat-card-label">Regular Users</div>
            <div class="stat-card-value">{{ $regularUsers->count() }}</div>
        </div>
    </div>

    {{-- Users Table --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-list"></i>
                All Users
            </h5>
        </div>
        <div class="modern-card-body">
            @if($users->count() > 0)
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Department</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td>
                                <strong>{{ $user->first_name }} {{ $user->last_name }}</strong>
                            </td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->isSuperAdmin())
                                    <span class="badge-modern badge-super-admin">Super Admin</span>
                                @elseif($user->is_admin == 0)
                                    <span class="badge-modern badge-admin">Admin</span>
                                @else
                                    <span class="badge-modern badge-user">Regular User</span>
                                @endif
                            </td>
                            <td>{{ $user->department->name ?? 'N/A' }}</td>
                            <td>{{ $user->created_at->format('M d, Y') }}</td>
                            <td>
                                @if($user->isSuperAdmin())
                                    <form method="POST" action="{{ route('super-admin.users.revoke-super-admin', $user->id) }}" 
                                          class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to revoke Super Admin access from this user?')">
                                        @csrf
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="submit" class="btn-modern btn-modern-danger">Revoke Super Admin</button>
                                    </form>
                                @elseif($user->is_admin == 0)
                                    <form method="POST" action="{{ route('super-admin.users.revoke-admin', $user->id) }}" 
                                          class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to make this user a Regular User?')">
                                        @csrf
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="submit" class="btn-modern btn-modern-danger">Revoke Admin</button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('super-admin.users.grant-admin', $user->id) }}" 
                                          class="d-inline" 
                                          onsubmit="return confirm('Are you sure you want to make this user an Admin?')">
                                        @csrf
                                        <input type="hidden" name="confirm" value="1">
                                        <button type="submit" class="btn-modern btn-modern-primary">Grant Admin</button>
                                    </form>
                                @endif
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
            <div class="text-center py-5">
                <i class="icofont-inbox" style="font-size: 3rem; color: #d1d5db;"></i>
                <p class="text-muted mt-3">No users found</p>
            </div>
            @endif
        </div>
    </div>
</div>

<script>
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}
</script>
@endsection

