@extends('layout.app')

@push('styles')
<style>
    .committees-header {
        background: #667eea;
        border-radius: 16px;
        padding: 2rem;
        margin-bottom: 2rem;
        color: white;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.3);
    }
    
    .committees-header h4 {
        color: white;
        font-weight: 700;
        margin-bottom: 0.5rem;
        font-size: 1.75rem;
    }
    
    .committees-header p {
        color: rgba(255, 255, 255, 0.9);
        margin: 0;
        font-size: 1rem;
    }
    
    .committee-card-link {
        cursor: pointer;
    }
    
    .committee-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        height: 100%;
    }
    
    .committee-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: #667eea;
    }
    
    .committee-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.15);
        border-color: #667eea;
    }
    
    .view-details-hint {
        color: #667eea;
        font-size: 0.875rem;
        transition: transform 0.3s ease;
    }
    
    .committee-card:hover .view-details-hint {
        transform: translateX(4px);
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
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .committee-description {
        color: #64748b;
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 1.5rem;
        min-height: 60px;
    }
    
    .committee-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-top: 1rem;
        border-top: 1px solid #e2e8f0;
    }
    
    .committee-members {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #64748b;
        font-size: 0.9rem;
    }
    
    .committee-members i {
        color: #667eea;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
    }
    
    .empty-state-icon {
        font-size: 4rem;
        color: #cbd5e1;
        margin-bottom: 1.5rem;
    }
    
    .empty-state h5 {
        color: #1e293b;
        font-weight: 600;
        margin-bottom: 0.5rem;
    }
    
    .empty-state p {
        color: #64748b;
        margin: 0;
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
                        <div class="committees-header">
                            <h4>
                                <i class="fas fa-users-cog" style="margin-right: 0.5rem;"></i>
                                My Committees & Boards
                            </h4>
                            <p>View all committees and boards you are assigned to</p>
                        </div>

                        @if ($userCommittees->count() > 0)
                            <div class="row">
                                @foreach ($userCommittees as $committee)
                                <div class="col-xl-4 col-lg-6 col-md-6 col-12 mb-4">
                                    <a href="{{ route('committees.show', $committee->id) }}" class="committee-card-link" style="text-decoration: none; display: block;">
                                        <div class="committee-card">
                                            <div class="committee-card-header">
                                                <h5 class="committee-title">{{ $committee->name }}</h5>
                                                <span class="committee-status-badge badge bg-{{ $committee->status === 'active' ? 'success' : 'secondary' }}">
                                                    {{ ucfirst($committee->status) }}
                                                </span>
                                            </div>
                                            
                                            @if($committee->description)
                                            <p class="committee-description">
                                                {{ Str::limit($committee->description, 150) }}
                                            </p>
                                            @else
                                            <p class="committee-description" style="font-style: italic; color: #94a3b8;">
                                                No description provided
                                            </p>
                                            @endif
                                            
                                            <div class="committee-footer">
                                                <div class="committee-members">
                                                    <i class="fas fa-users"></i>
                                                    <span><strong>{{ $committee->users->count() }}</strong> {{ $committee->users->count() === 1 ? 'member' : 'members' }}</span>
                                                </div>
                                                <div class="view-details-hint">
                                                    <i class="fas fa-arrow-right"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state">
                                <div class="empty-state-icon">
                                    <i class="fas fa-users-slash"></i>
                                </div>
                                <h5>No Committee or Board Assignments</h5>
                                <p>You are not currently assigned to any committees or boards.<br>Contact your administrator if you believe this is an error.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

