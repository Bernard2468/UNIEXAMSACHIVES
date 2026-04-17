@extends('layout.app')

@push('styles')
<style>
    /* Committees Management Styles - Matching Communication System */
    .dashboard__section__title {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e9ecef;
    }

    .dashboard__section__title h4 {
        margin: 0;
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
    }

    .responsive-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 12px 24px;
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 12px;
        font-weight: 600;
        font-size: 14px;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    .responsive-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        color: white;
        text-decoration: none;
    }

    .compose-btn .svgWrapper {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .svgIcon {
        width: 20px;
        height: 20px;
    }

    /* Committee Cards */
    .committees-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }

    .committee-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .committee-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #8b5cf6);
    }

    .committee-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
    }

    .committee-card-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }

    .committee-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
        flex: 1;
    }

    .committee-status-badge {
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .committee-status-badge.active {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .committee-status-badge.inactive {
        background: rgba(107, 114, 128, 0.1);
        color: #6b7280;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }

    .committee-description {
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.6;
        margin-bottom: 1rem;
        min-height: 40px;
    }

    .committee-meta {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e9ecef;
    }

    .committee-members-count {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.875rem;
    }

    .committee-members-count i {
        color: #3b82f6;
    }

    .committee-actions {
        display: flex;
        gap: 0.5rem;
    }

    .action-btn {
        padding: 8px 12px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        text-decoration: none;
    }

    .action-btn.manage {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .action-btn.manage:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        color: white;
        text-decoration: none;
    }

    .action-btn.edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .action-btn.edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        color: white;
        text-decoration: none;
    }

    .action-btn.delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .action-btn.delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        color: white;
    }

    .action-btn-icon {
        padding: 8px;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
    }

    .action-btn-icon.edit {
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: white;
    }

    .action-btn-icon.edit:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
        color: white;
        text-decoration: none;
    }

    .action-btn-icon.delete {
        background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
        color: white;
    }

    .action-btn-icon.delete:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        color: white;
    }

    /* Empty State */
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        border: 2px dashed #e2e8f0;
    }

    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }

    .empty-state h5 {
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .empty-state p {
        color: #94a3b8;
        font-size: 0.9rem;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    }

    .modal-header {
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
        border-radius: 16px 16px 0 0;
        padding: 1.5rem;
        border-bottom: none;
    }

    .modal-header .modal-title {
        font-weight: 700;
        font-size: 1.25rem;
    }

    .modal-header .btn-close {
        filter: brightness(0) invert(1);
        opacity: 0.8;
    }

    .modal-header .btn-close:hover {
        opacity: 1;
    }

    .modal-body {
        padding: 2rem;
    }

    .form-label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
    }

    .form-control {
        border-radius: 8px;
        border: 1px solid #e2e8f0;
        padding: 10px 14px;
        transition: all 0.2s ease;
    }

    .form-control:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1.5rem 2rem;
    }

    /* User Selector Styles - Matching Communication System */
    .user-selector {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        overflow: hidden;
        margin-top: 1rem;
    }

    .user-selector-header {
        padding: 24px;
        background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
        color: white;
    }

    .search-container {
        margin-bottom: 20px;
    }

    .search-input-wrapper {
        position: relative;
        display: flex;
        align-items: center;
        background: rgba(255, 255, 255, 0.1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        border-radius: 12px;
        padding: 4px;
        transition: all 0.3s ease;
    }

    .search-input-wrapper:focus-within {
        background: rgba(255, 255, 255, 0.15);
        border-color: rgba(255, 255, 255, 0.3);
        box-shadow: 0 0 0 3px rgba(255, 255, 255, 0.1);
    }

    .search-input {
        flex: 1;
        border: none;
        background: transparent;
        padding: 12px 16px;
        font-size: 14px;
        color: white;
        outline: none;
        font-weight: 500;
    }

    .search-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-icon {
        color: rgba(255, 255, 255, 0.8);
        margin-right: 12px;
        font-size: 16px;
    }

    .user-stats {
        text-align: center;
        font-size: 14px;
        opacity: 0.9;
    }

    .stats-info {
        margin-bottom: 12px;
        font-weight: 500;
    }

    .selected-count {
        font-weight: 700;
        color: #10b981;
    }

    .progress-bar-container {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
        height: 8px;
        overflow: hidden;
    }

    .progress-bar {
        height: 100%;
        background: linear-gradient(90deg, #10b981, #059669);
        width: 0%;
        transition: width 0.8s ease;
        border-radius: 10px;
    }

    .user-list-container {
        max-height: 300px;
        overflow-y: auto;
        background: #f8f9fa;
    }

    .user-list {
        padding: 0;
        margin: 0;
    }

    .user-item {
        display: flex;
        align-items: center;
        padding: 15px 20px;
        border-bottom: 1px solid #e9ecef;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .user-item:hover {
        background: #e9ecef;
        transform: translateX(5px);
    }

    .user-item:last-child {
        border-bottom: none;
    }

    .user-avatar {
        margin-right: 15px;
        flex-shrink: 0;
    }

    .avatar-img {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .avatar-placeholder {
        width: 45px;
        height: 45px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 16px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-weight: 600;
        color: #495057;
        margin-bottom: 4px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .name-separator {
        color: #dee2e6;
        font-weight: 300;
        padding: 0 0.25rem;
    }

    .position-badge-small {
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

    .user-email {
        color: #6c757d;
        font-size: 12px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        margin-bottom: 4px;
    }

    .user-status {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .status-indicator {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background: #28a745;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0.7);
        }
        70% {
            box-shadow: 0 0 0 10px rgba(40, 167, 69, 0);
        }
        100% {
            box-shadow: 0 0 0 0 rgba(40, 167, 69, 0);
        }
    }

    .user-select-checkbox {
        position: relative;
        margin-left: 15px;
    }

    .user-checkbox {
        position: absolute;
        opacity: 0;
        cursor: pointer;
        height: 22px;
        width: 22px;
        z-index: 2;
    }

    .checkmark {
        height: 22px;
        width: 22px;
        background-color: #fff;
        border: 2px solid #dee2e6;
        border-radius: 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
        cursor: pointer;
        position: relative;
        z-index: 1;
    }

    .user-checkbox:checked ~ .checkmark {
        background-color: #28a745;
        border-color: #28a745;
    }

    .user-checkbox:checked ~ .checkmark::after {
        content: '✓';
        color: white;
        font-size: 14px;
        font-weight: bold;
    }

    .user-actions {
        display: flex;
        gap: 0.5rem;
        padding: 16px 24px;
        background: #f8f9fa;
        border-top: 1px solid #e9ecef;
    }

    .user-actions button {
        padding: 8px 16px;
        border-radius: 8px;
        font-size: 0.875rem;
        font-weight: 600;
        border: 1px solid;
        transition: all 0.2s ease;
        cursor: pointer;
    }

    .btn-outline-primary {
        background: white;
        color: #3b82f6;
        border-color: #3b82f6;
    }

    .btn-outline-primary:hover {
        background: #3b82f6;
        color: white;
    }

    .btn-outline-secondary {
        background: white;
        color: #6c757d;
        border-color: #6c757d;
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
    }

    /* Current Members Table */
    .current-members-section {
        margin-bottom: 2rem;
    }

    .current-members-section h6 {
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1rem;
        font-size: 1rem;
    }

    .members-table {
        background: white;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }

    .members-table table {
        margin: 0;
    }

    .members-table thead {
        background: #f8f9fa;
    }

    .members-table th {
        font-weight: 600;
        color: #495057;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 16px;
        border-bottom: 2px solid #e9ecef;
    }

    .members-table td {
        padding: 12px 16px;
        vertical-align: middle;
    }

    .member-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .member-avatar {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .member-avatar-placeholder {
        width: 35px;
        height: 35px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 12px;
        border: 2px solid #fff;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .btn-sm {
        padding: 6px 12px;
        font-size: 0.8rem;
    }

    .section-divider {
        margin: 2rem 0;
        border: none;
        border-top: 2px solid #e9ecef;
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
                    <div class="dashboard__content__wraper">
                        <div class="dashboard__section__title">
                            <h4>Committees & Boards Management</h4>
                            <button type="button" class="responsive-btn compose-btn" data-bs-toggle="modal" data-bs-target="#createCommitteeModal">
                                <div class="svgWrapper">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                        <path stroke="#fff" stroke-width="2" d="M12 5v14m-7-7h14"></path>
                                    </svg>
                                    <div class="text">Create Committee/Board</div>
                                </div>
                            </button>
                        </div>

                        @if(session('success'))
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <i class="icofont-check-circled"></i> {{ session('success') }}
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        @if(count($committees) > 0)
                            <div class="committees-grid">
                                @foreach ($committees as $committee)
                                <div class="committee-card">
                                    <div class="committee-card-header">
                                        <h5 class="committee-title">{{ $committee->name }}</h5>
                                        <span class="committee-status-badge {{ $committee->status }}">
                                            {{ ucfirst($committee->status) }}
                                        </span>
                                    </div>
                                    
                                    <div class="committee-description">
                                        {{ $committee->description ? Str::limit($committee->description, 120) : 'No description provided.' }}
                                    </div>
                                    
                                    <div class="committee-meta">
                                        <div class="committee-members-count">
                                            <i class="fas fa-users"></i>
                                            <span>{{ $committee->users_count }} member(s)</span>
                                        </div>
                                        <div class="committee-actions">
                                            <button type="button" class="action-btn manage" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#manageUsersModal{{ $committee->id }}"
                                                    title="Manage Members">
                                                <i class="fas fa-users"></i> Manage
                                            </button>
                                            <button type="button" class="action-btn-icon edit" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editCommitteeModal{{ $committee->id }}"
                                                    title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('committees.destroy', $committee->id) }}" 
                                                  method="POST" 
                                                  style="display:inline-block;"
                                                  onsubmit="return confirm('Are you sure you want to delete this committee/board?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="action-btn-icon delete" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Modal --}}
                                <div class="modal fade" id="editCommitteeModal{{ $committee->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Committee/Board</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="{{ route('committees.update', $committee->id) }}" method="POST">
                                                @csrf
                                                @method('PUT')
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="name{{ $committee->id }}" class="form-label">Name <span class="text-danger">*</span></label>
                                                        <input type="text" class="form-control" id="name{{ $committee->id }}" 
                                                               name="name" value="{{ $committee->name }}" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="description{{ $committee->id }}" class="form-label">Description</label>
                                                        <textarea class="form-control" id="description{{ $committee->id }}" 
                                                                  name="description" rows="3">{{ $committee->description }}</textarea>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="status{{ $committee->id }}" class="form-label">Status <span class="text-danger">*</span></label>
                                                        <select class="form-control" id="status{{ $committee->id }}" name="status" required>
                                                            <option value="active" {{ $committee->status === 'active' ? 'selected' : '' }}>Active</option>
                                                            <option value="inactive" {{ $committee->status === 'inactive' ? 'selected' : '' }}>Inactive</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary">Update</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                {{-- Manage Users Modal --}}
                                <div class="modal fade" id="manageUsersModal{{ $committee->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Manage Members - {{ $committee->name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                {{-- Current Members Section --}}
                                                <div class="current-members-section">
                                                    <h6>Current Members ({{ $committee->users->count() }})</h6>
                                                    @if($committee->users->count() > 0)
                                                        <div class="members-table">
                                                            <table class="table table-sm mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Member</th>
                                                                        <th>Email</th>
                                                                        <th>Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($committee->users as $user)
                                                                    <tr>
                                                                        <td>
                                                                            <div class="member-info">
                                                                                @if($user->profile_picture)
                                                                                    <img src="{{ asset('profile_pictures/' . $user->profile_picture) }}" alt="{{ $user->first_name }}" class="member-avatar">
                                                                                @else
                                                                                    <div class="member-avatar-placeholder">
                                                                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                                                    </div>
                                                                                @endif
                                                                                <div>
                                                                                    <div style="font-weight: 600; color: #495057;">{{ $user->first_name }} {{ $user->last_name }}</div>
                                                                                    @if($user->position)
                                                                                        <small style="color: #6c757d; font-size: 0.75rem;">{{ $user->position->name }}</small>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </td>
                                                                        <td>{{ $user->email }}</td>
                                                                        <td>
                                                                            <form action="{{ route('committees.remove-user', [$committee->id, $user->id]) }}" 
                                                                                  method="POST" style="display:inline-block;">
                                                                                @csrf
                                                                                <button type="submit" class="btn btn-sm btn-danger" 
                                                                                        onclick="return confirm('Remove this user from the committee?');">
                                                                                    <i class="fas fa-user-minus"></i> Remove
                                                                                </button>
                                                                            </form>
                                                                        </td>
                                                                    </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @else
                                                        <div class="alert alert-info mb-0">
                                                            <i class="icofont-info-circle"></i> No members assigned yet.
                                                        </div>
                                                    @endif
                                                </div>

                                                <hr class="section-divider">

                                                {{-- Add New Members Section --}}
                                                <div>
                                                    <h6>Add New Members</h6>
                                                    <form action="{{ route('committees.add-users', $committee->id) }}" method="POST" id="addUsersForm{{ $committee->id }}">
                                                        @csrf
                                                        <div class="user-selector">
                                                            <div class="user-selector-header">
                                                                <div class="search-container">
                                                                    <div class="search-input-wrapper">
                                                                        <i class="icofont-search search-icon"></i>
                                                                        <input type="text" 
                                                                               class="search-input" 
                                                                               id="user-search{{ $committee->id }}"
                                                                               placeholder="Search by name or email..." 
                                                                               autocomplete="off">
                                                                    </div>
                                                                </div>
                                                                <div class="user-stats">
                                                                    <div class="stats-info">
                                                                        <span class="selected-count" id="selected-count{{ $committee->id }}">0</span> of <span class="total-count">{{ $users->whereNotIn('id', $committee->users->pluck('id'))->count() }}</span> users selected
                                                                    </div>
                                                                    <div class="progress-bar-container">
                                                                        <div class="progress-bar" id="selection-progress{{ $committee->id }}"></div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="user-list-container">
                                                                <div class="user-list" id="user-list{{ $committee->id }}">
                                                                    @foreach ($users as $user)
                                                                        @if (!$committee->users->contains($user->id))
                                                                        <div class="user-item" 
                                                                             data-user-id="{{ $user->id }}" 
                                                                             data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                                                            <div class="user-avatar">
                                                                                @if($user->profile_picture)
                                                                                    <img src="{{ asset('profile_pictures/' . $user->profile_picture) }}" alt="{{ $user->first_name }}" class="avatar-img">
                                                                                @else
                                                                                    <div class="avatar-placeholder">
                                                                                        {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                                                    </div>
                                                                                @endif
                                                                            </div>
                                                                            <div class="user-info">
                                                                                <div class="user-name">
                                                                                    <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                                                                                    @if($user->position)
                                                                                        <span class="name-separator">|</span>
                                                                                        <span class="position-badge-small">
                                                                                            <i class="fas fa-briefcase"></i>
                                                                                            {{ $user->position->name }}
                                                                                        </span>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="user-email">{{ $user->email }}</div>
                                                                                <div class="user-status">
                                                                                    <span class="status-indicator online"></span>
                                                                                    <small>Active User</small>
                                                                                </div>
                                                                            </div>
                                                                            <div class="user-select-checkbox">
                                                                                <input type="checkbox" 
                                                                                       name="user_ids[]" 
                                                                                       value="{{ $user->id }}" 
                                                                                       class="user-checkbox"
                                                                                       id="user_{{ $committee->id }}_{{ $user->id }}">
                                                                                <span class="checkmark"></span>
                                                                            </div>
                                                                        </div>
                                                                        @endif
                                                                    @endforeach
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="user-actions">
                                                                <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-btn{{ $committee->id }}">
                                                                    <i class="icofont-check-circled"></i> Select All
                                                                </button>
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" id="clear-all-btn{{ $committee->id }}">
                                                                    <i class="icofont-close-circled"></i> Clear All
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <div class="mt-3 text-end">
                                                            <button type="submit" class="btn btn-primary">
                                                                <i class="fas fa-user-plus"></i> Add Selected Users
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="icofont-users-alt-5"></i>
                                </div>
                                <h5>No Committees or Boards</h5>
                                <p>Get started by creating your first committee or board.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Create Committee Modal --}}
<div class="modal fade" id="createCommitteeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Committee/Board</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('committees.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-control" id="status" name="status" required>
                            <option value="active" selected>Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize user selection for each committee modal
    @foreach ($committees as $committee)
        initializeUserSelector({{ $committee->id }});
    @endforeach
});

function initializeUserSelector(committeeId) {
    const userSearch = document.getElementById('user-search' + committeeId);
    const userList = document.getElementById('user-list' + committeeId);
    const userItems = userList.querySelectorAll('.user-item');
    const userCheckboxes = userList.querySelectorAll('.user-checkbox');
    const selectAllBtn = document.getElementById('select-all-btn' + committeeId);
    const clearAllBtn = document.getElementById('clear-all-btn' + committeeId);
    const selectedCount = document.getElementById('selected-count' + committeeId);
    const progressBar = document.getElementById('selection-progress' + committeeId);
    const totalCount = parseInt(document.querySelector('#selected-count' + committeeId).nextElementSibling.textContent);

    // Update selected count and progress
    function updateSelectedCount() {
        const checked = Array.from(userCheckboxes).filter(cb => cb.checked).length;
        selectedCount.textContent = checked;
        const percentage = totalCount > 0 ? (checked / totalCount) * 100 : 0;
        progressBar.style.width = percentage + '%';
    }

    // Search functionality
    if (userSearch) {
        userSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            
            userItems.forEach(item => {
                const searchData = item.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    item.style.display = 'flex';
                    item.style.animation = 'slideIn 0.3s ease-out';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // User selection handling
    userCheckboxes.forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            updateSelectedCount();
        });
        
        // Also add click event to the user item for better UX
        const userItem = checkbox.closest('.user-item');
        if (userItem) {
            userItem.addEventListener('click', function(e) {
                // Don't trigger if clicking on the checkbox itself
                if (e.target !== checkbox && !checkbox.contains(e.target)) {
                    checkbox.checked = !checkbox.checked;
                    checkbox.dispatchEvent(new Event('change'));
                }
            });
        }
    });
    
    // Select all functionality
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const visibleCheckboxes = Array.from(userCheckboxes).filter(checkbox => {
                const userItem = checkbox.closest('.user-item');
                return userItem.style.display !== 'none';
            });
            
            visibleCheckboxes.forEach(checkbox => {
                checkbox.checked = true;
            });
            
            updateSelectedCount();
        });
    }
    
    // Clear all functionality
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function() {
            userCheckboxes.forEach(checkbox => {
                checkbox.checked = false;
            });
            
            updateSelectedCount();
        });
    }

    // Initialize count on load
    updateSelectedCount();
}
</script>
@endpush

@endsection
