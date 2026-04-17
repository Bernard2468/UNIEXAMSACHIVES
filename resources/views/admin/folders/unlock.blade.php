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
                    <div class="folders-hero" style="background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%); padding: 60px 0 40px;">
                        <div class="container">
                            <div class="folders-hero-content" style="text-align:center;">
                                <h1 class="hero-title" style="font-size:2.0rem;font-weight:700;color:#475569;">Unlock Folder</h1>
                                <p class="hero-subtitle" style="color:#475569;">Enter the password to access "{{ $folder->name }}"</p>
                            </div>
                        </div>
                    </div>

                    <div class="folders-section" style="background:#f9fafb;padding:3rem 0;">
                        <div class="container">
                            @if(session('info'))
                                <div class="alert alert-info alert-dismissible fade show" role="alert">
                                    {{ session('info') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    {{ $errors->first() }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="form-container" style="max-width:480px;margin:0 auto;background:white;border-radius:16px;padding:2rem;box-shadow:0 4px 12px rgba(0,0,0,0.05);">
                                <form action="{{ route('dashboard.folders.unlock', $folder) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label" for="password" style="font-weight:600;color:#374151;">Password</label>
                                        <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" placeholder="Enter folder password" required>
                                        @error('password')
                                            <div class="text-danger mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div style="display:flex;gap:0.75rem;justify-content:space-between;align-items:center;">
                                        <a href="{{ route('dashboard.folders.security', ['folder' => $folder, 'return_to' => route('dashboard.folders.unlock.form', $folder)]) }}" class="btn btn-link" style="padding:0;">Change password</a>
                                        <div style="display:flex;gap:0.75rem;">
                                            <a href="{{ route('dashboard.folders.index') }}" class="btn btn-secondary" style="border:2px solid #e5e7eb;">Cancel</a>
                                            <button type="submit" class="btn btn-primary" style="background:linear-gradient(135deg,#64748b 0%,#475569 100%);border:2px solid #64748b;">Unlock</button>
                                        </div>
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


