<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Super Admin Login - Metascholar Consult</title>
    
    <!-- Icofont -->
    <link rel="stylesheet" href="{{ asset('css/icofont.min.css') }}">
    <!-- Main CSS (includes modern-auth-container styles) -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        /* Super Admin specific customization */
        .super-admin-icon {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            border-radius: 50%;
            border: 2px solid rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.3));
            transition: transform 0.3s ease;
        }
        
        .super-admin-icon:hover {
            transform: scale(1.1);
            border-color: rgba(255, 255, 255, 1);
            background: rgba(255, 255, 255, 0.2);
        }

        /* Ensure full viewport height */
        .modern-auth-container {
            min-height: 100vh;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            gap: 0.5rem;
        }

        .forgot-password-link {
            color: #3b82f6;
            font-size: 0.9rem;
            text-decoration: none;
            font-weight: 500;
            border-bottom: 1px solid transparent;
            transition: color 0.2s ease, border-color 0.2s ease;
        }

        .forgot-password-link:hover {
            color: #1d4ed8;
            border-bottom-color: #1d4ed8;
        }
    </style>
</head>
<body>
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
                <h1 class="orbit-title">Super Admin Portal</h1>
                <h2 class="orbit-subtitle" id="typewriter-subtitle"></h2>
                <div class="orbit-container">
                    <div class="orbit-path"></div>
                    <div class="orbit-icon orbit-icon-1">
                        <div class="super-admin-icon">
                            <i class="icofont-shield"></i>
                        </div>
                    </div>
                    <div class="orbit-icon orbit-icon-2">
                        <i class="icofont-settings"></i>
                    </div>
                    <div class="orbit-icon orbit-icon-3">
                        <i class="icofont-chart-bar-graph"></i>
                    </div>
                    <div class="orbit-icon orbit-icon-4">
                        <i class="icofont-database"></i>
                    </div>
                    <div class="orbit-icon orbit-icon-5">
                        <i class="icofont-lock"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Auth Form -->
        <div class="auth-right-side">
            <div class="auth-tabs">
                <!-- Single Tab for Super Admin Login -->
                <div class="tab-buttons">
                    <button class="tab-btn active" data-tab="login">
                        <span class="tab-text">Super Admin Sign In</span>
                        <div class="tab-indicator"></div>
                    </button>
                </div>

                <!-- Login Form -->
                <div class="auth-form-panel active" id="login-panel">
                    <div class="form-container">
                        <div class="form-header">
                            <h2 class="form-title">Super Admin Access</h2>
                            <p class="form-subtitle">Metascholar Consult Ltd - System Management</p>
                        </div>

                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        @if(session('success'))
                            <div class="alert alert-success">
                                {{ session('success') }}
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

                        <form action="{{ route('super-admin.login.post') }}" method="POST" class="animated-form">
                            @csrf
                            
                            <div class="form-group">
                                <div class="input-container">
                                    <input type="email" 
                                           name="email" 
                                           id="login-email" 
                                           class="animated-input" 
                                           placeholder="Enter super admin email" 
                                           value="{{ old('email') }}"
                                           required 
                                           autofocus>
                                </div>
                            </div>

                            <div class="form-group">
                                <div class="input-container">
                                    <input type="password" 
                                           name="password" 
                                           id="login-password" 
                                           class="animated-input" 
                                           placeholder="Enter super admin password" 
                                           required>
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
                                <a class="forgot-password-link"
                                   href="{{ route('password.request', ['context' => 'super-admin', 'email' => old('email', 'metascholarlimited@gmail.com')]) }}">
                                    Forgot password?
                                </a>
                            </div>

                            <button type="submit" class="submit-btn">
                                <span>Access Super Admin Panel</span>
                                <div class="btn-ripple"></div>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Initialize auth components when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            initializeAuthForm();
            setTimeout(typewriterEffect, 1000);
        });

        // Initialize form functionality
        function initializeAuthForm() {
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
            const form = document.querySelector('.animated-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = this.querySelector('.submit-btn');
                    if (submitBtn) {
                        submitBtn.classList.add('loading');
                        
                        setTimeout(() => {
                            submitBtn.classList.remove('loading');
                        }, 2000);
                    }
                });
            }
        }

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
            if (e.target.classList.contains('submit-btn') || e.target.closest('.submit-btn')) {
                const btn = e.target.classList.contains('submit-btn') ? e.target : e.target.closest('.submit-btn');
                const ripple = btn.querySelector('.btn-ripple');
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

        // Typewriter Effect for Subtitle
        function typewriterEffect() {
            const subtitle = document.getElementById('typewriter-subtitle');
            if (!subtitle) return;
            
            const texts = [
                'System Management & Control',
                'Subscription Administration',
                'Payment Processing',
                'Security & Maintenance'
            ];
            let currentIndex = 0;
            let isTyping = true;
            let charIndex = 0;
            
            function type() {
                const currentText = texts[currentIndex];
                if (isTyping && charIndex < currentText.length) {
                    subtitle.textContent = currentText.slice(0, charIndex + 1);
                    charIndex++;
                    setTimeout(type, 100);
                } else if (isTyping && charIndex >= currentText.length) {
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
                    setTimeout(erase, 50);
                } else if (!isTyping && charIndex <= 0) {
                    currentIndex = (currentIndex + 1) % texts.length;
                    isTyping = true;
                    charIndex = 0;
                    setTimeout(type, 500);
                }
            }
            
            type();
        }
    </script>
</body>
</html>
