@extends('layout.app')

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
            <h1 class="orbit-title">Reset Password</h1>
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
                    <i class="icofont-shield"></i>
                </div>
                <div class="orbit-icon orbit-icon-4">
                    <i class="icofont-lock"></i>
                </div>
                <div class="orbit-icon orbit-icon-5">
                    <i class="icofont-check-circled"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Reset Password Form -->
    <div class="auth-right-side">
        <div class="auth-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="reset">
                    <span class="tab-text">New Password</span>
                    <div class="tab-indicator"></div>
                </button>
            </div>

            <!-- Reset Password Form -->
            <div class="auth-form-panel active" id="reset-panel">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Reset Your Password</h2>
                        <p class="form-subtitle">Enter your new password below to complete the reset process.</p>
                    </div>

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

                    <form method="POST" action="{{ route('password.update') }}" class="animated-form">
                        @csrf
                        <input type="hidden" name="token" value="{{ $token }}">

                        <div class="form-group">
                            <div class="input-container">
                                <input type="email" name="email" id="reset-email" class="animated-input" placeholder="Enter your email address" value="{{ old('email', request()->email) }}" required autofocus>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <input type="password" name="password" id="reset-password" class="animated-input" placeholder="Enter new password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('reset-password')">
                                    <i class="icofont-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <input type="password" name="password_confirmation" id="reset-password-confirm" class="animated-input" placeholder="Confirm new password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('reset-password-confirm')">
                                    <i class="icofont-eye"></i>
                                </button>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span>Reset Password</span>
                            <div class="btn-ripple"></div>
                        </button>
                    </form>

                    <div class="back-to-login">
                        <a href="{{ route('frontend.login') }}">← Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = input.nextElementSibling;
    const icon = toggleBtn.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('icofont-eye');
        icon.classList.add('icofont-eye-blocked');
    } else {
        input.type = 'password';
        icon.classList.remove('icofont-eye-blocked');
        icon.classList.add('icofont-eye');
    }
}

document.addEventListener('DOMContentLoaded', function() {
    // Typewriter effect for subtitle
    const typewriterElement = document.getElementById('typewriter-subtitle');
    const texts = [
        'Secure Password Update',
        'Account Security Portal',
        'Password Recovery Complete',
        'Digital Access Restored'
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
            btnText.textContent = 'Resetting...';
            
            // Re-enable after a delay (in case of validation errors)
            setTimeout(() => {
                submitBtn.disabled = false;
                btnText.textContent = 'Reset Password';
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

    // Password matching validation
    const password = document.getElementById('reset-password');
    const confirmPassword = document.getElementById('reset-password-confirm');
    
    function validatePasswords() {
        if (password.value && confirmPassword.value) {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        }
    }
    
    password.addEventListener('input', validatePasswords);
    confirmPassword.addEventListener('input', validatePasswords);
});
</script>
@endpush
