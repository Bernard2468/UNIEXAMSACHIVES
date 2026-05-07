@extends('layout.app')

@push('styles')
<style>
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
    .orbit-icon-1 {
        transform: rotate(0deg) translateX(150px) rotate(0deg);
    }

    .otp-info-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background: rgba(59, 130, 246, 0.08);
        border: 1px solid rgba(59, 130, 246, 0.35);
        color: #1d4ed8;
        padding: 0.4rem 0.85rem;
        border-radius: 999px;
        font-size: 0.85rem;
        font-weight: 500;
        margin-bottom: 1rem;
    }

    .otp-input-row {
        display: flex;
        justify-content: center;
        gap: 0.45rem;
        margin: 1.25rem 0;
        flex-wrap: nowrap;
    }

    .otp-digit {
        width: 44px;
        height: 52px;
        flex: 0 0 44px;
        padding: 0;
        font-size: 1.35rem;
        font-weight: 700;
        text-align: center;
        border: 2px solid #d1d5db;
        border-radius: 10px;
        background: #ffffff;
        color: #111827;
        transition: all 0.2s ease;
        font-family: 'Courier New', Courier, monospace;
        outline: none;
        -moz-appearance: textfield;
    }

    @media (max-width: 420px) {
        .otp-input-row { gap: 0.3rem; }
        .otp-digit { width: 40px; height: 48px; flex-basis: 40px; font-size: 1.2rem; }
    }
    .otp-digit::-webkit-outer-spin-button,
    .otp-digit::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .otp-digit:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.15);
        transform: translateY(-1px);
    }
    .otp-digit.filled {
        border-color: #10b981;
        background: #f0fdf4;
    }

    .resend-row {
        text-align: center;
        margin-top: 1rem;
        font-size: 0.9rem;
        color: #6b7280;
    }
    .resend-row form { display: inline; }
    .resend-row button {
        background: none;
        border: none;
        color: #3b82f6;
        font-weight: 600;
        cursor: pointer;
        padding: 0;
        text-decoration: underline;
    }
    .resend-row button:disabled {
        color: #9ca3af;
        cursor: not-allowed;
        text-decoration: none;
    }

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
    }

    .masked-email {
        font-weight: 600;
        color: #1f2937;
    }
</style>
@endpush

@section('content')
@include('frontend.auth_header')
@include('frontend.theme_shadow')
@include('components.modern-notifications')

<div class="modern-auth-container">
    <div class="auth-left-side">
        <div class="ripple-background">
            <div class="ripple-circle ripple-1"></div>
            <div class="ripple-circle ripple-2"></div>
            <div class="ripple-circle ripple-3"></div>
            <div class="ripple-circle ripple-4"></div>
        </div>

        <div class="tech-orbit-display">
            <h1 class="orbit-title">Verify Your Email</h1>
            <h2 class="orbit-subtitle" id="typewriter-subtitle"></h2>
            <div class="orbit-container">
                <div class="orbit-path"></div>
                <div class="orbit-icon orbit-icon-1">
                    <img src="{{ asset('img/crest.ico') }}" alt="University Crest" class="crest-image">
                </div>
                <div class="orbit-icon orbit-icon-2"><i class="icofont-email"></i></div>
                <div class="orbit-icon orbit-icon-3"><i class="icofont-shield"></i></div>
                <div class="orbit-icon orbit-icon-4"><i class="icofont-lock"></i></div>
                <div class="orbit-icon orbit-icon-5"><i class="icofont-check-circled"></i></div>
            </div>
        </div>
    </div>

    <div class="auth-right-side">
        <div class="auth-tabs">
            <div class="tab-buttons">
                <button class="tab-btn active" data-tab="verify">
                    <span class="tab-text">Email Verification</span>
                    <div class="tab-indicator"></div>
                </button>
            </div>

            <div class="auth-form-panel active" id="verify-panel">
                <div class="form-container">
                    <div class="form-header">
                        <span class="otp-info-pill"><i class="icofont-email"></i> One-time code sent</span>
                        <h2 class="form-title">Enter Verification Code</h2>
                        <p class="form-subtitle">
                            We sent a 6-digit code to
                            <span class="masked-email">{{ $email }}</span>.
                            Enter it below to confirm this is your email.
                        </p>
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
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if (session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form method="POST" action="{{ route('verification.verify') }}" class="animated-form" id="otp-form">
                        @csrf
                        <input type="hidden" name="otp" id="otp-hidden">

                        <div class="otp-input-row" id="otp-input-row">
                            <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="0" autocomplete="one-time-code" autofocus>
                            <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="1" autocomplete="one-time-code">
                            <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="2" autocomplete="one-time-code">
                            <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="3" autocomplete="one-time-code">
                            <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="4" autocomplete="one-time-code">
                            <input type="text" inputmode="numeric" maxlength="1" class="otp-digit" data-index="5" autocomplete="one-time-code">
                        </div>

                        <button type="submit" class="submit-btn" id="verify-btn">
                            <span>Verify Email</span>
                            <div class="btn-ripple"></div>
                        </button>
                    </form>

                    <div class="resend-row">
                        Didn't get the code?
                        <form method="POST" action="{{ route('verification.resend') }}">
                            @csrf
                            <button type="submit" id="resend-btn">Resend code</button>
                        </form>
                    </div>

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
document.addEventListener('DOMContentLoaded', function() {
    const typewriterElement = document.getElementById('typewriter-subtitle');
    const texts = [
        'Secure Email Verification',
        'One-Time Code Authentication',
        'Confirm Your Identity',
        'Account Activation Step'
    ];
    let textIndex = 0, charIndex = 0, isDeleting = false;
    function typeWriter() {
        if (!typewriterElement) return;
        const currentText = texts[textIndex];
        typewriterElement.textContent = currentText.substring(0, isDeleting ? charIndex - 1 : charIndex + 1);
        charIndex += isDeleting ? -1 : 1;
        if (!isDeleting && charIndex === currentText.length) {
            setTimeout(() => { isDeleting = true; }, 2000);
        } else if (isDeleting && charIndex === 0) {
            isDeleting = false;
            textIndex = (textIndex + 1) % texts.length;
        }
        setTimeout(typeWriter, isDeleting ? 100 : 150);
    }
    if (typewriterElement) typeWriter();

    const digits = document.querySelectorAll('.otp-digit');
    const hidden = document.getElementById('otp-hidden');
    const form = document.getElementById('otp-form');

    function syncHidden() {
        hidden.value = Array.from(digits).map(d => d.value).join('');
    }

    digits.forEach((input, idx) => {
        input.addEventListener('input', (e) => {
            const v = e.target.value.replace(/\D/g, '');
            e.target.value = v.slice(-1);
            if (e.target.value) e.target.classList.add('filled');
            else e.target.classList.remove('filled');

            if (e.target.value && idx < digits.length - 1) {
                digits[idx + 1].focus();
            }
            syncHidden();

            if (hidden.value.length === 6) {
                form.submit();
            }
        });

        input.addEventListener('keydown', (e) => {
            if (e.key === 'Backspace' && !e.target.value && idx > 0) {
                digits[idx - 1].focus();
                digits[idx - 1].value = '';
                digits[idx - 1].classList.remove('filled');
                syncHidden();
            }
            if (e.key === 'ArrowLeft' && idx > 0) digits[idx - 1].focus();
            if (e.key === 'ArrowRight' && idx < digits.length - 1) digits[idx + 1].focus();
        });

        input.addEventListener('paste', (e) => {
            e.preventDefault();
            const pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '').slice(0, 6);
            for (let i = 0; i < digits.length; i++) {
                digits[i].value = pasted[i] || '';
                if (digits[i].value) digits[i].classList.add('filled');
                else digits[i].classList.remove('filled');
            }
            syncHidden();
            const focusIdx = Math.min(pasted.length, digits.length - 1);
            digits[focusIdx].focus();
            if (hidden.value.length === 6) form.submit();
        });
    });

    form.addEventListener('submit', (e) => {
        syncHidden();
        if (hidden.value.length !== 6) {
            e.preventDefault();
            return;
        }
        const btn = document.getElementById('verify-btn');
        btn.disabled = true;
        btn.querySelector('span').textContent = 'Verifying...';
    });

    // Resend cooldown UI (60s)
    const resendBtn = document.getElementById('resend-btn');
    const cooldownKey = 'udts_otp_resend_until';
    function tickCooldown() {
        const until = parseInt(localStorage.getItem(cooldownKey) || '0', 10);
        const remaining = Math.max(0, Math.ceil((until - Date.now()) / 1000));
        if (remaining > 0) {
            resendBtn.disabled = true;
            resendBtn.textContent = `Resend code (${remaining}s)`;
            setTimeout(tickCooldown, 1000);
        } else {
            resendBtn.disabled = false;
            resendBtn.textContent = 'Resend code';
        }
    }
    resendBtn.closest('form').addEventListener('submit', () => {
        localStorage.setItem(cooldownKey, Date.now() + 60_000);
    });
    tickCooldown();
});
</script>
@endpush
