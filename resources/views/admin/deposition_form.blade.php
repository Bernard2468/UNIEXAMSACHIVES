@extends('layout.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ========== MODERN EXAM DEPOSITION FORM ========== */
    .modern-depo-hero {
        background: #0f172a;
        padding: 56px 0 48px;
        position: relative;
        overflow: hidden;
    }
    .modern-depo-hero::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -120px;
        width: 400px;
        height: 400px;
        background: rgba(99, 102, 241, 0.08);
        border-radius: 50%;
    }
    .modern-depo-hero::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: -80px;
        width: 280px;
        height: 280px;
        background: rgba(14, 165, 233, 0.06);
        border-radius: 50%;
    }
    .modern-depo-hero .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(99, 102, 241, 0.12);
        color: #a5b4fc;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 100px;
        margin-bottom: 16px;
        letter-spacing: 0.3px;
    }
    .modern-depo-hero h1 {
        color: #f8fafc;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    .modern-depo-hero p {
        color: #94a3b8;
        font-size: 15px;
        margin-bottom: 0;
    }
    .modern-depo-hero .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
    }
    .modern-depo-hero .breadcrumb-nav a {
        color: #64748b;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.2s;
    }
    .modern-depo-hero .breadcrumb-nav a:hover { color: #a5b4fc; }
    .modern-depo-hero .breadcrumb-nav .sep { color: #475569; font-size: 11px; }
    .modern-depo-hero .breadcrumb-nav .current { color: #e2e8f0; font-size: 13px; font-weight: 500; }

    /* ========== FORM CONTAINER ========== */
    .modern-depo-container {
        padding: 40px 0 80px;
        background: #f8fafc;
        min-height: 60vh;
    }

    /* ========== STEPPER ========== */
    .form-stepper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin-bottom: 40px;
        padding: 0 20px;
    }
    .step-item {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .step-circle {
        width: 40px;
        height: 40px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 15px;
        background: #e2e8f0;
        color: #94a3b8;
        transition: all 0.3s;
        flex-shrink: 0;
    }
    .step-item.active .step-circle {
        background: #6366f1;
        color: #fff;
        box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3);
    }
    .step-item.completed .step-circle {
        background: #10b981;
        color: #fff;
    }
    .step-label {
        font-size: 13px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
        transition: color 0.3s;
    }
    .step-item.active .step-label { color: #1e293b; }
    .step-item.completed .step-label { color: #10b981; }
    .step-connector {
        width: 60px;
        height: 2px;
        background: #e2e8f0;
        margin: 0 12px;
        transition: background 0.3s;
        flex-shrink: 0;
    }
    .step-connector.completed { background: #10b981; }

    @media (max-width: 768px) {
        .step-label { display: none; }
        .step-connector { width: 30px; margin: 0 6px; }
    }

    /* ========== FORM CARD ========== */
    .form-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .form-card-header {
        padding: 24px 32px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .form-card-header .header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .form-card-header .header-icon.blue { background: #eff6ff; color: #3b82f6; }
    .form-card-header .header-icon.purple { background: #f5f3ff; color: #7c3aed; }
    .form-card-header .header-icon.amber { background: #fffbeb; color: #f59e0b; }
    .form-card-header .header-icon.emerald { background: #ecfdf5; color: #10b981; }
    .form-card-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .form-card-header span {
        font-size: 13px;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }
    .form-card-body {
        padding: 28px 32px 32px;
    }

    /* ========== FORM FIELDS ========== */
    .field-group {
        margin-bottom: 22px;
    }
    .field-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 7px;
        letter-spacing: 0.2px;
    }
    .field-group label .required-dot {
        display: inline-block;
        width: 5px;
        height: 5px;
        background: #ef4444;
        border-radius: 50%;
        margin-left: 4px;
        vertical-align: middle;
    }
    .field-group input[type="text"],
    .field-group input[type="email"],
    .field-group input[type="number"],
    .field-group input[type="date"],
    .field-group select {
        width: 100%;
        padding: 11px 16px;
        border: 1.5px solid #e2e8f0;
        border-radius: 10px;
        font-size: 14px;
        color: #1e293b;
        background: #fff;
        transition: all 0.2s;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
    }
    .field-group input:focus,
    .field-group select:focus {
        border-color: #6366f1;
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
    }
    .field-group input::placeholder {
        color: #cbd5e1;
    }
    .field-group select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
    }
    .field-group .input-icon-wrap {
        position: relative;
    }
    .field-group .input-icon-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }
    .field-group .input-icon-wrap input,
    .field-group .input-icon-wrap select {
        padding-left: 40px;
    }

    /* ========== FILE UPLOAD ZONE ========== */
    .file-upload-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 36px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        background: #fafbfc;
        position: relative;
    }
    .file-upload-zone:hover,
    .file-upload-zone.dragover {
        border-color: #6366f1;
        background: #f5f3ff;
    }
    .file-upload-zone .upload-icon {
        width: 56px;
        height: 56px;
        border-radius: 16px;
        background: #eff6ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 22px;
        color: #3b82f6;
    }
    .file-upload-zone h4 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .file-upload-zone h4 em {
        color: #6366f1;
        font-style: normal;
        text-decoration: underline;
    }
    .file-upload-zone p {
        font-size: 13px;
        color: #94a3b8;
        margin: 0;
    }
    .file-upload-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }
    .file-name-display {
        display: none;
        align-items: center;
        gap: 10px;
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        border-radius: 10px;
        padding: 10px 16px;
        margin-top: 10px;
        font-size: 13px;
        color: #166534;
        font-weight: 500;
    }
    .file-name-display i { color: #22c55e; }
    .file-name-display.visible { display: flex; }

    /* ========== STEP PANELS ========== */
    .step-panel { display: none; animation: fadeSlide 0.35s ease; }
    .step-panel.active { display: block; }
    @keyframes fadeSlide {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ========== DECLARATION ========== */
    .declaration-box {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 24px;
        margin-top: 28px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .declaration-box input[type="checkbox"] {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        accent-color: #6366f1;
        flex-shrink: 0;
        margin-top: 2px;
        cursor: pointer;
    }
    .declaration-box span {
        font-size: 13px;
        color: #475569;
        line-height: 1.6;
    }

    /* ========== BUTTONS ========== */
    .form-nav-buttons {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 28px;
        gap: 12px;
    }
    .btn-step {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 28px;
        border-radius: 10px;
        font-size: 14px;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .btn-step-back {
        background: #f1f5f9;
        color: #475569;
    }
    .btn-step-back:hover { background: #e2e8f0; }
    .btn-step-next {
        background: #6366f1;
        color: #fff;
    }
    .btn-step-next:hover { background: #4f46e5; box-shadow: 0 4px 14px rgba(99, 102, 241, 0.3); }
    .btn-submit-form {
        background: #10b981;
        color: #fff;
        padding: 14px 36px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 700;
        border: none;
        cursor: pointer;
        transition: all 0.25s;
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }
    .btn-submit-form:hover { background: #059669; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3); }

    /* ========== SIDEBAR ========== */
    .tips-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        position: sticky;
        top: 100px;
    }
    .tips-card-header {
        background: #0f172a;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .tips-card-header i {
        color: #fbbf24;
        font-size: 18px;
    }
    .tips-card-header h4 {
        color: #f8fafc;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }
    .tips-card-body {
        padding: 20px 24px;
    }
    .tip-item {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .tip-item:last-child { border-bottom: none; }
    .tip-num {
        width: 24px;
        height: 24px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #6366f1;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .tip-item p {
        font-size: 13px;
        color: #475569;
        margin: 0;
        line-height: 1.5;
    }
    .tip-item p strong {
        color: #1e293b;
    }

    /* ========== PROGRESS BAR ========== */
    .form-progress-bar {
        height: 3px;
        background: #e2e8f0;
        border-radius: 100px;
        margin-bottom: 32px;
        overflow: hidden;
    }
    .form-progress-bar .bar {
        height: 100%;
        background: #6366f1;
        border-radius: 100px;
        transition: width 0.4s ease;
    }

    /* ========== ALERTS ========== */
    .modern-alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .modern-alert-danger i {
        color: #ef4444;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .modern-alert-danger ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .modern-alert-danger ul li {
        font-size: 13px;
        color: #991b1b;
        padding: 2px 0;
    }
</style>
@endpush

@section('content')
@include('frontend.header')

{{-- HERO SECTION --}}
<div class="modern-depo-hero">
    <div class="container">
        <div class="hero-badge">
            <i class="fas fa-file-signature"></i>
            {{ isset($exam) ? 'Edit Exam' : 'Exam Deposit' }}
        </div>
        <h1>{{ isset($exam) ? 'Edit Exam Document' : 'Exams Deposition Form' }}</h1>
        <p>{{ isset($exam) ? 'Update your exam document information below.' : 'Submit your exam documents securely to the university archive system.' }}</p>
        <div class="breadcrumb-nav">
            <a href="{{route('dashboard')}}">Dashboard</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Deposit Exam</span>
        </div>
    </div>
</div>

{{-- FORM BODY --}}
<div class="modern-depo-container">
    <div class="container">

        {{-- STEPPER --}}
        <div class="form-stepper" id="examStepper">
            <div class="step-item active" data-step="1">
                <div class="step-circle">1</div>
                <div class="step-label">Personal Info</div>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" data-step="2">
                <div class="step-circle">2</div>
                <div class="step-label">Course Details</div>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" data-step="3">
                <div class="step-circle">3</div>
                <div class="step-label">Exam Info</div>
            </div>
            <div class="step-connector"></div>
            <div class="step-item" data-step="4">
                <div class="step-circle">4</div>
                <div class="step-label">Upload Files</div>
            </div>
        </div>

        <div class="form-progress-bar">
            <div class="bar" id="examProgressBar" style="width: 25%"></div>
        </div>

        <div class="row">
            {{-- MAIN FORM --}}
            <div class="col-xl-8 col-lg-8 col-md-12 col-12">
                <form action="{{ isset($exam) ? route('exams.update', $exam->id) : route('dashboard.exam.store') }}" method="post" enctype="multipart/form-data" id="examDepoForm">
                    @csrf
                    @if(isset($exam))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                    <div class="modern-alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- STEP 1: Personal Information --}}
                    <div class="step-panel active" id="examStep1">
                        <div class="form-card">
                            <div class="form-card-header">
                                <div class="header-icon blue"><i class="fas fa-user"></i></div>
                                <div>
                                    <h3>Personal Information</h3>
                                    <span>Enter your faculty member details</span>
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="field-group">
                                    <label>Full Name <span class="required-dot"></span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" placeholder="Enter your full name" name="instructor_name" value="{{ old('instructor_name', $exam->instructor_name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Staff ID <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-id-badge"></i>
                                                <input type="text" placeholder="e.g. STF-00123" name="student_id" value="{{ old('student_id', $exam->student_id ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Department / Faculty <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-building"></i>
                                                <select name="faculty" required>
                                                    @if (count($departments) > 0)
                                                        @foreach ($departments as $department)
                                                            <option value="{{$department->name}}" {{ old('faculty', $exam->faculty ?? '') == $department->name ? 'selected' : '' }}>{{$department->name}}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>No Department Added</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Email Address <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-envelope"></i>
                                                <input type="email" placeholder="email@university.edu" name="email" value="{{ old('email', $exam->email ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Phone Number <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-phone"></i>
                                                <input type="text" placeholder="Enter phone number" name="phone_number" value="{{ old('phone_number', $exam->phone_number ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-nav-buttons">
                            <div></div>
                            <button type="button" class="btn-step btn-step-next" onclick="examNextStep(2)">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 2: Course Details --}}
                    <div class="step-panel" id="examStep2">
                        <div class="form-card">
                            <div class="form-card-header">
                                <div class="header-icon purple"><i class="fas fa-book"></i></div>
                                <div>
                                    <h3>Course Details</h3>
                                    <span>Provide the course and semester information</span>
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="field-group">
                                    <label>Course Title <span class="required-dot"></span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-graduation-cap"></i>
                                        <input type="text" placeholder="e.g. Introduction to Computer Science" name="course_title" value="{{ old('course_title', $exam->course_title ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="field-group">
                                    <label>Course Code <span class="required-dot"></span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-hashtag"></i>
                                        <input type="text" placeholder="e.g. CS101" name="course_code" value="{{ old('course_code', $exam->course_code ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Semester / Term <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-calendar-alt"></i>
                                                <select name="semester" required>
                                                    <option value="First Semester" {{ old('semester', $exam->semester ?? '') == 'First Semester' ? 'selected' : '' }}>First Semester</option>
                                                    <option value="Second Semester" {{ old('semester', $exam->semester ?? '') == 'Second Semester' ? 'selected' : '' }}>Second Semester</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Academic Year <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-calendar"></i>
                                                <select name="academic_year" required>
                                                    @if (count($years) > 0)
                                                        @foreach ($years as $year)
                                                            <option value="{{$year->year}}" {{ old('academic_year', $exam->academic_year ?? '') == $year->year ? 'selected' : '' }}>{{$year->year}}</option>
                                                        @endforeach
                                                    @else
                                                        <option value="" disabled>No Academic Year Added</option>
                                                    @endif
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-nav-buttons">
                            <button type="button" class="btn-step btn-step-back" onclick="examNextStep(1)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="button" class="btn-step btn-step-next" onclick="examNextStep(3)">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 3: Exam Information --}}
                    <div class="step-panel" id="examStep3">
                        <div class="form-card">
                            <div class="form-card-header">
                                <div class="header-icon amber"><i class="fas fa-clipboard-list"></i></div>
                                <div>
                                    <h3>Exam Information</h3>
                                    <span>Specify the exam type, date, and format</span>
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="field-group">
                                    <label>Exam Type <span class="required-dot"></span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-list-check"></i>
                                        <select name="exams_type" required>
                                            <option value="Midterm" {{ old('exams_type', $exam->exams_type ?? '') == 'Midterm' ? 'selected' : '' }}>Midterm</option>
                                            <option value="Final Exams" {{ old('exams_type', $exam->exams_type ?? '') == 'Final Exams' ? 'selected' : '' }}>Final Exams</option>
                                            <option value="Quiz" {{ old('exams_type', $exam->exams_type ?? '') == 'Quiz' ? 'selected' : '' }}>Quiz</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Exam Date <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-calendar-day"></i>
                                                <input type="date" name="exam_date" value="{{ old('exam_date', $exam->exam_date ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="field-group">
                                            <label>Duration <span class="required-dot"></span></label>
                                            <div class="input-icon-wrap">
                                                <i class="fas fa-clock"></i>
                                                <input type="text" placeholder="e.g. 2 hours" name="duration" value="{{ old('duration', $exam->duration ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="field-group">
                                    <label>Exam Format <span class="required-dot"></span></label>
                                    <div class="input-icon-wrap">
                                        <i class="fas fa-file-alt"></i>
                                        <select name="exam_format" required>
                                            <option value="In-Person Written" {{ old('exam_format', $exam->exam_format ?? '') == 'In-Person Written' ? 'selected' : '' }}>In-Person Written</option>
                                            <option value="Online" {{ old('exam_format', $exam->exam_format ?? '') == 'Online' ? 'selected' : '' }}>Online</option>
                                            <option value="Take-Home" {{ old('exam_format', $exam->exam_format ?? '') == 'Take-Home' ? 'selected' : '' }}>Take-Home</option>
                                            <option value="Oral" {{ old('exam_format', $exam->exam_format ?? '') == 'Oral' ? 'selected' : '' }}>Oral</option>
                                            <option value="Practical" {{ old('exam_format', $exam->exam_format ?? '') == 'Practical' ? 'selected' : '' }}>Practical</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-nav-buttons">
                            <button type="button" class="btn-step btn-step-back" onclick="examNextStep(2)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="button" class="btn-step btn-step-next" onclick="examNextStep(4)">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 4: File Upload --}}
                    <div class="step-panel" id="examStep4">
                        <div class="form-card">
                            <div class="form-card-header">
                                <div class="header-icon emerald"><i class="fas fa-cloud-upload-alt"></i></div>
                                <div>
                                    <h3>Upload Documents</h3>
                                    <span>Attach your exam paper and optional answer key</span>
                                </div>
                            </div>
                            <div class="form-card-body">
                                <div class="field-group">
                                    <label>Exam Document <span class="required-dot"></span></label>
                                    <div class="file-upload-zone" id="examDocZone">
                                        <div class="upload-icon"><i class="fas fa-file-pdf"></i></div>
                                        <h4>Drag & drop or <em>browse</em></h4>
                                        <p>Accepted formats: PDF, DOCX</p>
                                        <input type="file" name="exam_document" accept=".pdf,.docx" {{ isset($exam) ? '' : 'required' }} onchange="showFileName(this, 'examDocName')">
                                    </div>
                                    <div class="file-name-display" id="examDocName">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="fn-text"></span>
                                    </div>
                                </div>

                                <div class="field-group">
                                    <label>Answer Key <span style="font-weight:400; color:#94a3b8;">(optional)</span></label>
                                    <div class="file-upload-zone" id="answerKeyZone">
                                        <div class="upload-icon" style="background:#fffbeb;"><i class="fas fa-key" style="color:#f59e0b;"></i></div>
                                        <h4>Drag & drop or <em>browse</em></h4>
                                        <p>Accepted formats: PDF, DOCX</p>
                                        <input type="file" name="answer_key" accept=".pdf,.docx" onchange="showFileName(this, 'answerKeyName')">
                                    </div>
                                    <div class="file-name-display" id="answerKeyName">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="fn-text"></span>
                                    </div>
                                </div>

                                <div class="declaration-box">
                                    <input type="checkbox" id="examConsent" required>
                                    <span>I, the undersigned, declare that the information provided above is accurate to the best of my knowledge and that the exam questions submitted are in accordance with the university's academic standards and policies.</span>
                                </div>
                            </div>
                        </div>
                        <div class="form-nav-buttons">
                            <button type="button" class="btn-step btn-step-back" onclick="examNextStep(3)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="btn-submit-form">
                                <i class="fas fa-paper-plane"></i> Deposit Document
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-xl-4 col-lg-4 col-md-12 col-12 mt-4 mt-lg-0">
                <div class="tips-card">
                    <div class="tips-card-header">
                        <i class="fas fa-lightbulb"></i>
                        <h4>Upload Tips</h4>
                    </div>
                    <div class="tips-card-body">
                        <div class="tip-item">
                            <div class="tip-num">1</div>
                            <p><strong>Check Formats</strong> &mdash; Ensure files are PDF or DOCX before uploading.</p>
                        </div>
                        <div class="tip-item">
                            <div class="tip-num">2</div>
                            <p><strong>Compress Files</strong> &mdash; Use compression tools for files over 10 MB.</p>
                        </div>
                        <div class="tip-item">
                            <div class="tip-num">3</div>
                            <p><strong>Name Clearly</strong> &mdash; Use descriptive names like <em>CS101_Final_2024.pdf</em>.</p>
                        </div>
                        <div class="tip-item">
                            <div class="tip-num">4</div>
                            <p><strong>Secure Data</strong> &mdash; Sensitive documents are encrypted at rest.</p>
                        </div>
                        <div class="tip-item">
                            <div class="tip-num">5</div>
                            <p><strong>Stable Connection</strong> &mdash; Ensure a reliable internet connection for uploads.</p>
                        </div>
                        <div class="tip-item">
                            <div class="tip-num">6</div>
                            <p><strong>Preview First</strong> &mdash; Review your document before final submission.</p>
                        </div>
                        <div class="tip-item">
                            <div class="tip-num">7</div>
                            <p><strong>Keep Backups</strong> &mdash; Always retain a local copy of your files.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
(function() {
    const totalSteps = 4;
    let currentStep = 1;

    window.examNextStep = function(step) {
        currentStep = step;
        // Update panels
        for (let i = 1; i <= totalSteps; i++) {
            const panel = document.getElementById('examStep' + i);
            if (panel) panel.classList.toggle('active', i === step);
        }
        // Update stepper
        document.querySelectorAll('#examStepper .step-item').forEach(function(el) {
            const s = parseInt(el.getAttribute('data-step'));
            el.classList.remove('active', 'completed');
            if (s === step) el.classList.add('active');
            else if (s < step) el.classList.add('completed');
        });
        // Update connectors
        document.querySelectorAll('#examStepper .step-connector').forEach(function(el, idx) {
            el.classList.toggle('completed', idx < step - 1);
        });
        // Update progress bar
        const pct = Math.round((step / totalSteps) * 100);
        document.getElementById('examProgressBar').style.width = pct + '%';
        // Scroll to top of form
        window.scrollTo({ top: document.querySelector('.modern-depo-container').offsetTop - 20, behavior: 'smooth' });
    };

    window.showFileName = function(input, displayId) {
        const display = document.getElementById(displayId);
        if (input.files && input.files.length > 0) {
            display.querySelector('.fn-text').textContent = input.files[0].name;
            display.classList.add('visible');
        } else {
            display.classList.remove('visible');
        }
    };

    // Drag and drop visual feedback
    document.querySelectorAll('.file-upload-zone').forEach(function(zone) {
        zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
        zone.addEventListener('drop', function() { zone.classList.remove('dragover'); });
    });
})();
</script>
@endpush
