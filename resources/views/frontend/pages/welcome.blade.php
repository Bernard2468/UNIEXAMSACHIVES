@extends('layout.app')

@push('styles')
<style>
    /* Modern Landing Page Styles - Clean Professional Theme */
    .landing-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 50%, #dee2e6 100%);
        position: relative;
        overflow: hidden;
    }

    .landing-container::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(108,117,125,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)" /></svg>');
        opacity: 0.5;
    }

    .hero-section {
        position: relative;
        z-index: 2;
        padding: 100px 0;
        text-align: center;
        color: #2c3e50;
    }

    .hero-title {
        font-size: 4rem;
        font-weight: 800;
        margin-bottom: 1rem;
        color: #343a40;
        text-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        animation: fadeInUp 1s ease-out;
        background: linear-gradient(135deg, #343a40 0%, #6c757d 50%, #adb5bd 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
        letter-spacing: -0.02em;
    }

    .hero-subtitle {
        font-size: 1.5rem;
        margin-bottom: 2rem;
        color: #495057;
        opacity: 1;
        animation: fadeInUp 1s ease-out 0.2s both;
    }

    .hero-description {
        font-size: 1.1rem;
        max-width: 600px;
        margin: 0 auto 3rem;
        color: #6c757d;
        opacity: 1;
        line-height: 1.6;
        animation: fadeInUp 1s ease-out 0.4s both;
    }

    .cta-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeInUp 1s ease-out 0.6s both;
    }

    .btn-primary-modern {
        background: linear-gradient(45deg, #007bff, #0056b3);
        border: none;
        padding: 15px 40px;
        border-radius: 50px;
        color: white;
        font-weight: 600;
        font-size: 1.1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        box-shadow: 0 8px 25px rgba(0, 123, 255, 0.3);
        position: relative;
        overflow: hidden;
    }

    .btn-primary-modern:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 35px rgba(0, 123, 255, 0.4);
        color: white;
        text-decoration: none;
        background: linear-gradient(45deg, #0056b3, #004085);
    }

    .btn-secondary-modern {
        background: rgba(255, 255, 255, 0.9);
        border: 2px solid #dee2e6;
        padding: 13px 38px;
        border-radius: 50px;
        color: #495057;
        font-weight: 600;
        font-size: 1.1rem;
        text-decoration: none;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .btn-secondary-modern:hover {
        background: rgba(255, 255, 255, 1);
        border-color: #adb5bd;
        transform: translateY(-2px);
        color: #343a40;
        text-decoration: none;
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .features-section {
        position: relative;
        z-index: 2;
        padding: 80px 0;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
    }

    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        text-align: center;
        margin-bottom: 2rem;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.05);
    }

    .feature-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
    }

    .feature-icon {
        font-size: 3rem;
        color: #007bff;
        margin-bottom: 1rem;
    }

    .feature-title {
        font-size: 1.5rem;
        font-weight: 600;
        margin-bottom: 1rem;
        color: #333;
    }

    .feature-description {
        color: #666;
        line-height: 1.6;
    }

    .stats-section {
        position: relative;
        z-index: 2;
        padding: 60px 0;
        background: rgba(248, 249, 250, 0.9);
        backdrop-filter: blur(10px);
    }

    .stat-item {
        text-align: center;
        color: #333;
    }

    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        color: #007bff;
        display: block;
    }

    .stat-label {
        font-size: 1.1rem;
        font-weight: 500;
        margin-top: 0.5rem;
    }

    .floating-elements {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .floating-shape {
        position: absolute;
        background: rgba(0, 123, 255, 0.1);
        border-radius: 50%;
        animation: float 6s ease-in-out infinite;
    }

    .floating-shape:nth-child(1) {
        width: 60px;
        height: 60px;
        top: 20%;
        left: 10%;
        animation-delay: 0s;
    }

    .floating-shape:nth-child(2) {
        width: 80px;
        height: 80px;
        top: 60%;
        right: 15%;
        animation-delay: 2s;
    }

    .floating-shape:nth-child(3) {
        width: 40px;
        height: 40px;
        bottom: 30%;
        left: 20%;
        animation-delay: 4s;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 2.5rem;
        }
        
        .hero-subtitle {
            font-size: 1.2rem;
        }
        
        .cta-buttons {
            flex-direction: column;
            align-items: center;
        }
        
        .btn-primary-modern,
        .btn-secondary-modern {
            width: 100%;
            max-width: 300px;
        }
    }
</style>
@endpush

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="landing-container">
    <!-- Floating Background Elements -->
    <div class="floating-elements">
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
        <div class="floating-shape"></div>
    </div>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <h1 class="hero-title">Access The University Digital Transformation Suite (UDTS)</h1>
                    <p class="hero-subtitle">Enjoy seamless Advance Communication</p>
                    <p class="hero-description">
                        A comprehensive digital archive of university examinations, academic resources, 
                        and institutional communications. Secure, organized, and always available for 
                        your educational journey.
                    </p>
                    
                    <div class="cta-buttons">
                        <a href="{{ route('frontend.login') }}" class="btn-primary-modern">
                            <i class="icofont-login me-2"></i>
                            Access System
                        </a>
                        <a href="https://www.cug.edu.gh/" target="_blank" class="btn-secondary-modern">
                            <i class="icofont-info-circle me-2"></i>
                            Learn More
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="features-section">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="icofont-library"></i>
                        </div>
                        <h3 class="feature-title">Digital Archive</h3>
                        <p class="feature-description">
                            Comprehensive collection of exam papers, answer keys, and academic resources 
                            organized by department and academic year.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="icofont-search-1"></i>
                        </div>
                        <h3 class="feature-title">Advanced Search</h3>
                        <p class="feature-description">
                            Powerful search functionality to quickly find specific exams, subjects, 
                            or documents across all departments and years.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="icofont-ui-messaging"></i>
                        </div>
                        <h3 class="feature-title">Communication Hub</h3>
                        <p class="feature-description">
                            Integrated messaging and notification system for important announcements 
                            and academic communications.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="icofont-lock"></i>
                        </div>
                        <h3 class="feature-title">Secure Access</h3>
                        <p class="feature-description">
                            Role-based access control ensures that sensitive academic materials 
                            are only accessible to authorized personnel.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="icofont-upload-alt"></i>
                        </div>
                        <h3 class="feature-title">Easy Upload</h3>
                        <p class="feature-description">
                            Streamlined process for faculty and staff to upload new exam materials 
                            with automatic approval workflows.
                        </p>
                    </div>
                </div>
                
                <div class="col-lg-4 col-md-6">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="icofont-chart-growth"></i>
                        </div>
                        <h3 class="feature-title">Analytics Dashboard</h3>
                        <p class="feature-description">
                            Comprehensive insights into system usage, popular resources, 
                            and user engagement metrics.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="stats-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg col-md-6 col-sm-6">
                    <div class="stat-item">
                        <span class="stat-number" data-count="{{ $stats['total_exams'] ?? 0 }}">0</span>
                        <div class="stat-label">Exam Papers</div>
                    </div>
                </div>
                <div class="col-lg col-md-6 col-sm-6">
                    <div class="stat-item">
                        <span class="stat-number" data-count="{{ $stats['total_files'] ?? 0 }}">0</span>
                        <div class="stat-label">Files</div>
                    </div>
                </div>
                <div class="col-lg col-md-6 col-sm-6">
                    <div class="stat-item">
                        <span class="stat-number" data-count="{{ $stats['total_departments'] ?? 0 }}">0</span>
                        <div class="stat-label">Departments</div>
                    </div>
                </div>
                <div class="col-lg col-md-6 col-sm-6">
                    <div class="stat-item">
                        <span class="stat-number" data-count="{{ $stats['total_users'] ?? 0 }}">0</span>
                        <div class="stat-label">Active Users</div>
                    </div>
                </div>
                <div class="col-lg col-md-6 col-sm-6">
                    <div class="stat-item">
                        <span class="stat-number" data-count="{{ $stats['total_visits'] ?? 0 }}">0</span>
                        <div class="stat-label">Total Visits</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// Counter Animation
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    
    counters.forEach(counter => {
        const target = parseInt(counter.getAttribute('data-count'));
        const increment = target / 100;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            counter.textContent = Math.floor(current);
            
            if (current >= target) {
                counter.textContent = target;
                clearInterval(timer);
            }
        }, 20);
    });
}

// Intersection Observer for animations
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            if (entry.target.classList.contains('stats-section')) {
                animateCounters();
                observer.unobserve(entry.target);
            }
        }
    });
}, observerOptions);

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    const statsSection = document.querySelector('.stats-section');
    if (statsSection) {
        observer.observe(statsSection);
    }
});
</script>
@endpush
