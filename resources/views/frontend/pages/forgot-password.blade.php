@extends('layout.app')

@php
    $context = $context ?? 'default';
    $isSuperAdminContext = $context === 'super-admin';
    $defaultEmail = $defaultEmail ?? '';
    $redirectRoute = $redirectRoute ?? 'frontend.login';
@endphp

@push('styles')
<style>
    /* Custom styling for the university crest in orbit */
    .crest-image {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.8);
        background: rgba(255, 255, 255, 0.1);
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
        transition: transform 0.3s ease;
        padding: 2px;
    }
    
    .crest-image:hover {
        transform: scale(1.1);
        border-color: rgba(255, 255, 255, 1);
        background: rgba(255, 255, 255, 0.2);
    }
    
    /* Ensure the orbit animation works properly with the image */
    .orbit-icon-1 {
        transform: rotate(0deg) translateX(150px) rotate(0deg);
    }

    /* Back to login link styling */
    .back-to-login {
        text-align: center;
        margin-top: 1rem;
    }

    .back-to-login a {
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border-bottom: 1px solid transparent;
    }

    .back-to-login a:hover {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        text-decoration: none;
    }

    .context-note {
        margin-bottom: 1.25rem;
        padding: 0.85rem 1rem;
        border-radius: 0.75rem;
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.35);
        color: #1d4ed8;
        font-size: 0.9rem;
        display: flex;
        gap: 0.5rem;
        align-items: flex-start;
    }

    .context-note i {
        font-size: 1rem;
        margin-top: 0.15rem;
    }

    .context-note strong {
        display: block;
        font-size: 0.95rem;
        margin-bottom: 0.15rem;
    }

    .context-note small {
        color: #1e3a8a;
        display: block;
        margin-top: 0.35rem;
    }
</style>
@endpush

@section('content')
@include('frontend.auth_header')
@include('frontend.theme_shadow')
@include('components.modern-notifications')

<div class="modern-auth-container">
    <!-- Left Side - Animated Background -->
    <div class="auth-left-side">
        <div class="ripple-background">
            <div class="ripple-circle ripple-1"></div>
            <div class="ripple-circle ripple-2"></div>
            <div class="ripple-circle ripple-3"></div>
            <div class="ripple-circle ripple-4"></div>
        </div>
        
        <div class="tech-orbit-display">
            <h1 class="orbit-title">Password Recovery</h1>
            <h2 class="orbit-subtitle" id="typewriter-subtitle"></h2>
            <div class="orbit-container">
                <div class="orbit-path"></div>
                <div class="orbit-icon orbit-icon-1">
                    <img src="{{ asset('img/crest.ico') }}" alt="University Crest" class="crest-image">
                </div>
                <div class="orbit-icon orbit-icon-2">
                    <i class="icofont-key"></i>
                </div>
                <div class="orbit-icon orbit-icon-3">
                    <i class="icofont-email"></i>
                </div>
                <div class="orbit-icon orbit-icon-4">
                    <i class="icofont-lock"></i>
                </div>
                <div class="orbit-icon orbit-icon-5">
                    <i class="icofont-refresh"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Forgot Password Form -->
    <div class="auth-right-side">
        <div class="auth-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="forgot">
                    <span class="tab-text">Reset Password</span>
                    <div class="tab-indicator"></div>
                </button>
            </div>

            <!-- Forgot Password Form -->
            <div class="auth-form-panel active" id="forgot-panel">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Forgot Password?</h2>
                        @if($isSuperAdminContext)
                            <p class="form-subtitle">Enter the super admin email address and we’ll send a secure reset link to restore your dashboard access.</p>
                        @else
                            <p class="form-subtitle">No worries! Enter your email address and we'll send you a link to reset your password.</p>
                        @endif
                    </div>

                    @if($isSuperAdminContext)
                        <div class="context-note">
                            <i class="icofont-shield"></i>
                            <div>
                                <strong>Super Admin Recovery Mode</strong>
                                Please use the official super admin mailbox to receive the reset instructions. If the mailbox has changed, update it via the CLI reset command first.
                                <small>Need help? Contact the system owner or run <code>php artisan super-admin:reset-password</code>.</small>
                            </div>
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @if (session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('password.email') }}" class="animated-form">
                        @csrf
                        <input type="hidden" name="redirect_to" value="{{ $redirectRoute }}">
                        <div class="form-group">
                            <div class="input-container">
                                <input type="email"
                                       name="email"
                                       id="forgot-email"
                                       class="animated-input"
                                       placeholder="{{ $isSuperAdminContext ? 'Enter super admin email address' : 'Enter your email address' }}"
                                       value="{{ old('email', $defaultEmail) }}"
                                       required
                                       autofocus>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span>Send Reset Link</span>
                            <div class="btn-ripple"></div>
                        </button>
                    </form>

                    <div class="back-to-login">
                        <a href="{{ route($redirectRoute) }}">
                            {{ $isSuperAdminContext ? '← Back to Super Admin Login' : '← Back to Login' }}
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Typewriter effect for subtitle
    const typewriterElement = document.getElementById('typewriter-subtitle');
    const texts = [
        'Secure Account Recovery',
        'Password Reset Portal',
        'Account Security Center',
        'Digital Access Recovery'
    ];
    let textIndex = 0;
    let charIndex = 0;
    let isDeleting = false;

    function typeWriter() {
        const currentText = texts[textIndex];
        
        if (isDeleting) {
            typewriterElement.textContent = currentText.substring(0, charIndex - 1);
            charIndex--;
        } else {
            typewriterElement.textContent = currentText.substring(0, charIndex + 1);
            charIndex++;
        }

        if (!isDeleting && charIndex === currentText.length) {
            setTimeout(() => {
                isDeleting = true;
            }, 2000);
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
        }

        const speed = isDeleting ? 100 : 150;
        setTimeout(typeWriter, speed);
    }

    if (typewriterElement) {
        typeWriter();
    }

    // Form validation and enhancement
    const form = document.querySelector('.animated-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const submitBtn = form.querySelector('.submit-btn');
            const btnText = submitBtn.querySelector('span');
            
            // Show loading state
            submitBtn.disabled = true;
            btnText.textContent = 'Sending...';
            
            // Re-enable after a delay (in case of validation errors)
            setTimeout(() => {
                submitBtn.disabled = false;
                btnText.textContent = 'Send Reset Link';
            }, 3000);
        });
    }

    // Input focus effects
    const inputs = document.querySelectorAll('.animated-input');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
});
</script>
@endpush
