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

    /* Admin portal notice styling */
    .admin-portal-notice {
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.3);
        border-radius: 12px;
        padding: 12px 16px;
        margin-top: 16px;
        text-align: center;
    }

    .admin-portal-notice p {
        margin: 0;
        font-size: 0.9rem;
        color: #991b1b;
        font-weight: 500;
    }

    .admin-portal-link {
        color: #ef4444;
        text-decoration: none;
        font-weight: 600;
        border-bottom: 1px solid #ef4444;
        transition: all 0.3s ease;
    }

    .admin-portal-link:hover {
        color: #dc2626;
        border-bottom-color: #dc2626;
    }

    /* Forgot password link styling */
    .form-options {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin: 1rem 0;
    }

    .forgot-password-link {
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
        border-bottom: 1px solid transparent;
    }

    .forgot-password-link:hover {
        color: #3b82f6;
        border-bottom-color: #3b82f6;
        text-decoration: none;
    }

    /* Password hint & strength meter */
    .password-strength {
        display: flex;
        flex-direction: column;
        gap: 0.25rem;
        /* Visually attach to password field, give breathing room above button */
        margin-top: 0.35rem;
        margin-bottom: 0.85rem;
    }

    .password-strength-label {
        display: flex;
        align-items: center;
        gap: 0.35rem;
        font-weight: 500;
    }

    .password-strength-label .strength-value {
        text-transform: uppercase;
        letter-spacing: 0.04em;
        font-size: 0.75rem;
    }

    .password-strength-label .strength-icon {
        width: 16px;
        height: 16px;
        border-radius: 999px;
        display: inline-block;
    }

    .password-strength-bar {
        position: relative;
        width: 100%;
        height: 6px;
        border-radius: 999px;
        background: #e5e7eb;
        overflow: hidden;
    }

    .password-strength-fill {
        position: absolute;
        inset: 0;
        width: 0%;
        border-radius: inherit;
        transition: width 0.25s ease, background 0.25s ease;
    }

    .password-strength.weak .password-strength-fill {
        width: 33%;
        background: linear-gradient(90deg, #f97373, #ef4444);
    }

    .password-strength.medium .password-strength-fill {
        width: 66%;
        background: linear-gradient(90deg, #facc15, #eab308);
    }

    .password-strength.strong .password-strength-fill {
        width: 100%;
        background: linear-gradient(90deg, #22c55e, #16a34a);
    }

    .password-strength.weak .password-strength-label {
        color: #b91c1c;
    }

    .password-strength.medium .password-strength-label {
        color: #92400e;
    }

    .password-strength.strong .password-strength-label {
        color: #065f46;
    }

    .password-strength-message {
        margin: 0;
        font-size: 0.78rem;
        color: #6b7280;
    }

    .password-strength.weak .password-strength-message {
        color: #b91c1c;
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
            <h1 class="orbit-title">University Digital Transformation Suite (UDTS)</h1>
            <h2 class="orbit-subtitle" id="typewriter-subtitle"></h2>
            <div class="orbit-container">
                <div class="orbit-path"></div>
                <div class="orbit-icon orbit-icon-1">
                    <img src="{{ asset('img/crest.ico') }}" alt="University Crest" class="crest-image">
                </div>
                <div class="orbit-icon orbit-icon-2">
                    <i class="icofont-book"></i>
                </div>
                <div class="orbit-icon orbit-icon-3">
                    <i class="icofont-library"></i>
                </div>
                <div class="orbit-icon orbit-icon-4">
                    <i class="icofont-document"></i>
                </div>
                <div class="orbit-icon orbit-icon-5">
                    <i class="icofont-search-1"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Side - Auth Forms -->
    <div class="auth-right-side">
        <div class="auth-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="login">
                    <span class="tab-text">Sign In</span>
                    <div class="tab-indicator"></div>
                </button>
                <button class="tab-btn" data-tab="register">
                    <span class="tab-text">Sign Up</span>
                    <div class="tab-indicator"></div>
                </button>
            </div>

            <!-- Login Form -->
            <div class="auth-form-panel active" id="login-panel">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Welcome Back</h2>
                        <p class="form-subtitle">Sign in to your account</p>
                        <div class="admin-portal-notice">
                            <p><strong>Note:</strong> This portal is exclusively for Regular users. Administrative users should use the <a href="{{ route('admin.login') }}" class="admin-portal-link">Admin Portal</a> instead.</p>
                        </div>
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
                    @if(session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif

                    <form action="{{route('login')}}" method="POST" class="animated-form">
                        @csrf
                        <div class="form-group">
                            <div class="input-container">
                                <input type="email" name="email" id="login-email" class="animated-input" placeholder="Enter your email" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('login-password')" style="display: none;">
                                    <i class="icofont-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <input type="password" name="password" id="login-password" class="animated-input" placeholder="Enter your password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('login-password')">
                                    <i class="icofont-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="form-options">
                            <label class="checkbox-container">
                                <input type="checkbox" name="remember">
                                <span class="checkmark"></span>
                                Remember me
                            </label>
                            <a href="{{ route('password.request') }}" class="forgot-password-link">
                                Forgot Password?
                            </a>
                        </div>

                        <button type="submit" class="submit-btn">
                            <span>Sign In</span>
                            <div class="btn-ripple"></div>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Register Form -->
            <div class="auth-form-panel" id="register-panel">
                <div class="form-container">
                    <div class="form-header">
                        <h2 class="form-title">Create Account</h2>
                        <p class="form-subtitle">Join our digital archive community</p>
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

                    <form method="POST" action="{{ route('register') }}" class="animated-form">
                        @csrf
                        <div class="form-row">
                            <div class="form-group">
                                <div class="input-container">
                                    <input type="text" name="first_name" id="register-firstname" class="animated-input" placeholder="First name" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="input-container">
                                    <input type="text" name="last_name" id="register-lastname" class="animated-input" placeholder="Last name" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <input type="email" name="email" id="register-email" class="animated-input" placeholder="Enter your email" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <select name="department_id" id="register-department" class="animated-input">
                                    <option value="" disabled selected>Choose your Department/Faculty/Unit</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}">{{ $department->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <select name="staff_category" id="register-staff-category" class="animated-input">
                                    <option value="" disabled selected>Choose your Staff Category</option>
                                    <option value="Junior Staff">Junior Staff</option>
                                    <option value="Senior Staff">Senior Staff</option>
                                    <option value="Senior Member (Non-Teaching)">Senior Member (Non-Teaching)</option>
                                    <option value="Senior Member (Teaching)">Senior Member (Teaching)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <select name="position_id" id="register-position" class="animated-input">
                                    <option value="" selected>Choose your Position (Optional)</option>
                                    @foreach($positions as $position)
                                        <option value="{{ $position->id }}">{{ $position->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="input-container">
                                <input type="password" name="password" id="register-password" class="animated-input" placeholder="Create a password" required>
                                <button type="button" class="password-toggle" onclick="togglePassword('register-password')">
                                    <i class="icofont-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength" id="register-password-strength">
                                <div class="password-strength-label">
                                    <span class="strength-icon"></span>
                                    <span>Strength:</span>
                                    <span class="strength-value"></span>
                                </div>
                                <div class="password-strength-bar">
                                    <span class="password-strength-fill"></span>
                                </div>
                                <p class="password-strength-message"></p>
                            </div>
                        </div>

                        <button type="submit" class="submit-btn" id="register-submit-btn" disabled>
                            <span>Create Account</span>
                            <div class="btn-ripple"></div>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Function to initialize tabs
function initializeAuthTabs() {
    // Tab switching functionality
    const tabBtns = document.querySelectorAll('.tab-btn');
    const formPanels = document.querySelectorAll('.auth-form-panel');
    
    // Check if elements exist before proceeding
    if (tabBtns.length === 0 || formPanels.length === 0) {
        return;
    }
    
    // Add click event listeners to tab buttons
    tabBtns.forEach((btn) => {
        if (btn) {
            btn.addEventListener('click', () => {
                const targetTab = btn.getAttribute('data-tab');
                
                if (!targetTab) return;
                
                // Remove active class from all buttons and panels
                tabBtns.forEach(b => b.classList.remove('active'));
                formPanels.forEach(p => p.classList.remove('active'));
                
                // Add active class to clicked button and corresponding panel
                btn.classList.add('active');
                
                const targetPanel = document.getElementById(targetTab + '-panel');
                if (targetPanel) {
                    targetPanel.classList.add('active');
                }
            });
        }
    });

    // Input focus effects
    const inputs = document.querySelectorAll('.animated-input');
    inputs.forEach(input => {
        if (input && input.parentElement) {
            input.addEventListener('focus', function() {
                this.parentElement.classList.add('focused');
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.classList.remove('focused');
            });
        }
    });

    // Form submission animations
    const forms = document.querySelectorAll('.animated-form');
    forms.forEach(form => {
        if (form) {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('.submit-btn');
                if (submitBtn) {
                    submitBtn.classList.add('loading');
                    
                    // Remove loading class after form submission
                    setTimeout(() => {
                        submitBtn.classList.remove('loading');
                    }, 2000);
                }
            });
        }
    });
    
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', initializeAuthTabs);

// Password toggle functionality
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const toggle = input.parentElement?.querySelector('.password-toggle i');
    
    if (input && toggle) {
        if (input.type === 'password') {
            input.type = 'text';
            toggle.className = 'icofont-eye-blocked';
        } else {
            input.type = 'password';
            toggle.className = 'icofont-eye';
        }
    }
}

// Add ripple effect to buttons
document.addEventListener('click', function(e) {
    if (e.target.classList.contains('submit-btn')) {
        const ripple = e.target.querySelector('.btn-ripple');
        if (ripple) {
            ripple.style.left = (e.offsetX - ripple.offsetWidth / 2) + 'px';
            ripple.style.top = (e.offsetY - ripple.offsetHeight / 2) + 'px';
            ripple.classList.add('active');
            
            setTimeout(() => {
                ripple.classList.remove('active');
            }, 600);
        }
    }
});

// Simple password strength evaluator for the register form
function evaluatePasswordStrength(password) {
    if (!password) {
        return { level: '', label: '', message: '' };
    }

    let score = 0;

    if (password.length >= 9) score++; // more than 8 chars
    if (password.length >= 12) score++; // bonus for length
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) score++; // mixed case
    if (/\d/.test(password)) score++; // numbers
    if (/[^A-Za-z0-9]/.test(password)) score++; // symbols

    // Map score to buckets with a bias towards warning on weak passwords
    if (!password || password.length < 9 || score <= 1) {
        return { level: 'weak', label: 'Weak', message: 'Password is too weak. Use at least 9 characters with letters, numbers & symbols.' };
    } else if (score <= 3) {
        return { level: 'medium', label: 'Okay', message: 'Decent start. Make it longer or add symbols to strengthen it.' };
    }

    return { level: 'strong', label: 'Strong', message: 'Great! This password offers strong protection for your account.' };
}

function attachPasswordStrengthListener() {
    const passwordInput = document.getElementById('register-password');
    const strengthContainer = document.getElementById('register-password-strength');
    const submitBtn = document.getElementById('register-submit-btn');

    if (!passwordInput || !strengthContainer) return;

    const valueSpan = strengthContainer.querySelector('.strength-value');
    const messageEl = strengthContainer.querySelector('.password-strength-message');

    const fillEl = strengthContainer.querySelector('.password-strength-fill');

    function updateStrength() {
        const value = passwordInput.value;
        const evaluation = evaluatePasswordStrength(value);

        strengthContainer.classList.remove('weak', 'medium', 'strong');

        if (!value) {
            if (valueSpan) valueSpan.textContent = '';
            if (messageEl) messageEl.textContent = '';
            if (fillEl) fillEl.style.width = '0%';
            if (submitBtn) submitBtn.disabled = true;
            return;
        }

        strengthContainer.classList.add(evaluation.level);

        // Let CSS classes control the bar width/color when there is input
        if (fillEl) {
            fillEl.style.width = '';
        }

        if (valueSpan) {
            valueSpan.textContent = evaluation.label;
        }

        if (messageEl) {
            messageEl.textContent = evaluation.message;
        }

        // Enable submit only when password is strong
        if (submitBtn) {
            submitBtn.disabled = evaluation.level !== 'strong';
        }
    }

    // Evaluate on input and on initial load (which keeps bar empty until typing)
    passwordInput.addEventListener('input', updateStrength);
    updateStrength();
}

document.addEventListener('DOMContentLoaded', attachPasswordStrengthListener);

// Typewriter Effect for Subtitle
function typewriterEffect() {
    const subtitle = document.getElementById('typewriter-subtitle');
    const texts = [
        'Secure Digital Repository',
        'Academic Excellence',
        'Knowledge Preservation',
        'Innovation Hub'
    ];
    let currentIndex = 0;
    let isTyping = true;
    let charIndex = 0;
    
    function type() {
        const currentText = texts[currentIndex];
        if (isTyping && charIndex < currentText.length) {
            subtitle.textContent = currentText.slice(0, charIndex + 1);
            charIndex++;
            setTimeout(type, 100); // Typing speed
        } else if (isTyping && charIndex >= currentText.length) {
            // Finished typing, wait 2 seconds
            setTimeout(() => {
                isTyping = false;
                erase();
            }, 2000);
        }
    }
    
    function erase() {
        const currentText = texts[currentIndex];
        if (!isTyping && charIndex > 0) {
            subtitle.textContent = currentText.slice(0, charIndex - 1);
            charIndex--;
            setTimeout(erase, 50); // Erasing speed (faster than typing)
        } else if (!isTyping && charIndex <= 0) {
            // Finished erasing, move to next text
            currentIndex = (currentIndex + 1) % texts.length;
            isTyping = true;
            charIndex = 0;
            setTimeout(type, 500); // Small pause before restarting
        }
    }
    
    // Start the typewriter effect
    type();
}

// Initialize typewriter effect when page loads
document.addEventListener('DOMContentLoaded', function() {
    // Start typewriter effect after a short delay
    setTimeout(typewriterEffect, 1000);
});
</script>
@endpush
