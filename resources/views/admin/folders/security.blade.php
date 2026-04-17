@extends('layout.app')

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
                @include('components.sidebar')
                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="edit-folder-hero" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%); padding: 60px 0 40px;">
                        <div class="container">
                            <div class="edit-folder-hero-content" style="text-align:center;">
                                <h1 class="hero-title" style="font-size:2.0rem;font-weight:700;color:#475569;">Folder Security</h1>
                                <p class="hero-subtitle" style="color:#475569;">Manage password protection for "{{ $folder->name }}"</p>
                            </div>
                        </div>
                    </div>

                    <div class="form-section" style="background:#f9fafb;padding:3rem 0;">
                        <div class="container" style="max-width:720px;">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="form-container" style="background:white;border-radius:16px;padding:2rem;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                <form action="{{ route('dashboard.folders.security.update', $folder) }}" method="POST">
                                    @csrf
                                    @if(request('return_to'))
                                        <input type="hidden" name="return_to" value="{{ request('return_to') }}">
                                    @endif

                                    @if(!empty($folder->password_hash))
                                    <div class="mb-3">
                                        <label class="form-label" for="current_password" style="font-weight:600;color:#374151;">Current Folder Password</label>
                                        <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" placeholder="Enter current folder password">
                                        @error('current_password')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    @endif

                                    <div class="mb-3">
                                        <label class="form-label" for="new_password" style="font-weight:600;color:#374151;">New Password</label>
                                        <input type="password" id="new_password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" placeholder="Enter new password">
                                        @error('new_password')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label" for="new_password_confirmation" style="font-weight:600;color:#374151;">Confirm New Password</label>
                                        <input type="password" id="new_password_confirmation" name="new_password_confirmation" class="form-control" placeholder="Re-enter new password">
                                    </div>

                                    <div class="form-check" style="margin: 1rem 0;">
                                        <input class="form-check-input" type="checkbox" value="1" id="remove_password" name="remove_password">
                                        <label class="form-check-label" for="remove_password">Remove password protection</label>
                                    </div>

                                    <div style="display:flex;gap:0.75rem;justify-content:flex-end;">
                                        <a href="{{ route('dashboard.folders.show', $folder) }}" class="btn btn-secondary" style="border:2px solid #e5e7eb;">Back</a>
                                        <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#64748b 0%,#475569 100%);border:2px solid #64748b;">Save Security</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection


