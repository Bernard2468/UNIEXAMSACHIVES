@extends('layout.app')

@push('styles')
<style>
    /* Detail Page Styles - No Gradients */
    .committee-detail-header {
        background: #1e293b;
        border-radius: 20px;
        padding: 2.5rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
    }
    
    .committee-detail-header::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50px;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
    }
    
    .back-button {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        color: white;
        text-decoration: none;
        margin-bottom: 1.5rem;
        padding: 0.5rem 1rem;
        border-radius: 8px;
        background: rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
        font-weight: 500;
    }
    
    .back-button:hover {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        transform: translateX(-4px);
    }
    
    .committee-title-large {
        font-size: 2.5rem;
        font-weight: 800;
        margin-bottom: 0.5rem;
        color: white;
    }
    
    .committee-meta-info {
        display: flex;
        flex-wrap: wrap;
        gap: 2rem;
        margin-top: 1.5rem;
    }
    
    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .meta-item i {
        font-size: 1.25rem;
        opacity: 0.9;
    }
    
    .meta-item span {
        font-size: 1rem;
        opacity: 0.95;
    }
    
    .status-badge-large {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.9rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .status-badge-large.active {
        background: #10b981;
        color: white;
    }
    
    .status-badge-large.inactive {
        background: #6b7280;
        color: white;
    }
    
    .detail-section {
        background: white;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
    }
    
    .section-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }
    
    .section-title i {
        color: #667eea;
    }
    
    .description-content {
        color: #475569;
        font-size: 1.05rem;
        line-height: 1.8;
        white-space: pre-wrap;
    }
    
    .description-placeholder {
        color: #94a3b8;
        font-style: italic;
        font-size: 1rem;
    }
    
    .members-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 1.5rem;
    }
    
    .member-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .member-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        border-color: #667eea;
        background: white;
    }
    
    .member-avatar-large {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }
    
    .member-avatar-placeholder-large {
        width: 70px;
        height: 70px;
        border-radius: 50%;
        background: #667eea;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.5rem;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        flex-shrink: 0;
    }
    
    .member-info-detail {
        flex: 1;
        min-width: 0;
    }
    
    .member-name-detail {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.25rem;
    }
    
    .member-position-detail {
        color: #667eea;
        font-size: 0.9rem;
        font-weight: 600;
        margin-bottom: 0.25rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .member-email-detail {
        color: #64748b;
        font-size: 0.85rem;
        word-break: break-word;
    }
    
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-top: 1rem;
    }
    
    .info-card {
        background: #f8fafc;
        border-radius: 12px;
        padding: 1.5rem;
        border-left: 4px solid #667eea;
    }
    
    .info-label {
        font-size: 0.85rem;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .info-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1e293b;
    }
    
    .empty-members {
        text-align: center;
        padding: 3rem 2rem;
        background: #f8fafc;
        border-radius: 12px;
        border: 2px dashed #cbd5e1;
    }
    
    .empty-members-icon {
        font-size: 3rem;
        color: #cbd5e1;
        margin-bottom: 1rem;
    }
    
    .empty-members h5 {
        color: #64748b;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .empty-members p {
        color: #94a3b8;
        margin: 0;
    }
    
    @media (max-width: 768px) {
        .committee-title-large {
            font-size: 1.75rem;
        }
        
        .committee-meta-info {
            flex-direction: column;
            gap: 1rem;
        }
        
        .members-grid {
            grid-template-columns: 1fr;
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
                    <div class="dashboard__content__wraper">
                        {{-- Header Section --}}
                        <div class="committee-detail-header">
                            <a href="{{ route('committees.my-committees') }}" class="back-button">
                                <i class="fas fa-arrow-left"></i>
                                Back to My Committees
                            </a>
                            
                            <h1 class="committee-title-large">{{ $committee->name }}</h1>
                            
                            <div class="committee-meta-info">
                                <div class="meta-item">
                                    <i class="fas fa-info-circle"></i>
                                    <span>Committee/Board Details</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <span><strong>{{ $committee->users->count() }}</strong> {{ $committee->users->count() === 1 ? 'Member' : 'Members' }}</span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span>Created {{ $committee->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            
                            <div style="margin-top: 1.5rem;">
                                <span class="status-badge-large {{ $committee->status }}">
                                    <i class="fas fa-{{ $committee->status === 'active' ? 'check-circle' : 'pause-circle' }}"></i>
                                    {{ ucfirst($committee->status) }}
                                </span>
                            </div>
                        </div>

                        {{-- Description Section --}}
                        <div class="detail-section">
                            <h2 class="section-title">
                                <i class="fas fa-file-alt"></i>
                                About This Committee/Board
                            </h2>
                            @if($committee->description)
                                <div class="description-content">{{ $committee->description }}</div>
                            @else
                                <div class="description-placeholder">No description provided for this committee/board.</div>
                            @endif
                        </div>

                        {{-- Statistics Section --}}
                        <div class="detail-section">
                            <h2 class="section-title">
                                <i class="fas fa-chart-bar"></i>
                                Committee Statistics
                            </h2>
                            <div class="info-grid">
                                <div class="info-card">
                                    <div class="info-label">
                                        <i class="fas fa-users"></i> Total Members
                                    </div>
                                    <div class="info-value">{{ $committee->users->count() }}</div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">
                                        <i class="fas fa-calendar-check"></i> Created On
                                    </div>
                                    <div class="info-value">{{ $committee->created_at->format('M d, Y') }}</div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">
                                        <i class="fas fa-clock"></i> Last Updated
                                    </div>
                                    <div class="info-value">{{ $committee->updated_at->format('M d, Y') }}</div>
                                </div>
                                <div class="info-card">
                                    <div class="info-label">
                                        <i class="fas fa-toggle-{{ $committee->status === 'active' ? 'on' : 'off' }}"></i> Status
                                    </div>
                                    <div class="info-value">{{ ucfirst($committee->status) }}</div>
                                </div>
                            </div>
                        </div>

                        {{-- Members Section --}}
                        <div class="detail-section">
                            <h2 class="section-title">
                                <i class="fas fa-users"></i>
                                Committee Members ({{ $committee->users->count() }})
                            </h2>
                            
                            @if($committee->users->count() > 0)
                                <div class="members-grid">
                                    @foreach($committee->users as $user)
                                        <div class="member-card">
                                            @if($user->profile_picture)
                                                <img src="{{ asset('profile_pictures/' . $user->profile_picture) }}" 
                                                     alt="{{ $user->first_name }} {{ $user->last_name }}" 
                                                     class="member-avatar-large">
                                            @else
                                                <div class="member-avatar-placeholder-large">
                                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                </div>
                                            @endif
                                            
                                            <div class="member-info-detail">
                                                <div class="member-name-detail">
                                                    {{ $user->first_name }} {{ $user->last_name }}
                                                </div>
                                                
                                                @if($user->position)
                                                    <div class="member-position-detail">
                                                        <i class="fas fa-briefcase"></i>
                                                        {{ $user->position->name }}
                                                    </div>
                                                @endif
                                                
                                                <div class="member-email-detail">
                                                    <i class="fas fa-envelope"></i>
                                                    {{ $user->email }}
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="empty-members">
                                    <div class="empty-members-icon">
                                        <i class="fas fa-user-slash"></i>
                                    </div>
                                    <h5>No Members Yet</h5>
                                    <p>This committee/board doesn't have any members assigned yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

