@extends('layout.app')

@push('styles')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    /* ========== MODERN FILE DEPOSITION FORM ========== */
    .modern-file-hero {
        background: #0f172a;
        padding: 56px 0 48px;
        position: relative;
        overflow: hidden;
    }
    .modern-file-hero::before {
        content: '';
        position: absolute;
        top: -80px;
        right: -120px;
        width: 400px;
        height: 400px;
        background: rgba(14, 165, 233, 0.08);
        border-radius: 50%;
    }
    .modern-file-hero::after {
        content: '';
        position: absolute;
        bottom: -60px;
        left: -80px;
        width: 280px;
        height: 280px;
        background: rgba(16, 185, 129, 0.06);
        border-radius: 50%;
    }
    .modern-file-hero .hero-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(14, 165, 233, 0.12);
        color: #7dd3fc;
        font-size: 13px;
        font-weight: 600;
        padding: 6px 14px;
        border-radius: 100px;
        margin-bottom: 16px;
        letter-spacing: 0.3px;
    }
    .modern-file-hero h1 {
        color: #f8fafc;
        font-size: 32px;
        font-weight: 700;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    .modern-file-hero p {
        color: #94a3b8;
        font-size: 15px;
        margin-bottom: 0;
    }
    .modern-file-hero .breadcrumb-nav {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-top: 20px;
    }
    .modern-file-hero .breadcrumb-nav a {
        color: #64748b;
        font-size: 13px;
        text-decoration: none;
        transition: color 0.2s;
    }
    .modern-file-hero .breadcrumb-nav a:hover { color: #7dd3fc; }
    .modern-file-hero .breadcrumb-nav .sep { color: #475569; font-size: 11px; }
    .modern-file-hero .breadcrumb-nav .current { color: #e2e8f0; font-size: 13px; font-weight: 500; }

    /* ========== FORM CONTAINER ========== */
    .modern-file-container {
        padding: 40px 0 80px;
        background: #f8fafc;
        min-height: 60vh;
    }

    /* ========== STEPPER ========== */
    .file-stepper {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0;
        margin-bottom: 40px;
        padding: 0 20px;
    }
    .file-step-item {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }
    .file-step-circle {
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
    .file-step-item.active .file-step-circle {
        background: #0ea5e9;
        color: #fff;
        box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3);
    }
    .file-step-item.completed .file-step-circle {
        background: #10b981;
        color: #fff;
    }
    .file-step-label {
        font-size: 13px;
        font-weight: 600;
        color: #94a3b8;
        white-space: nowrap;
        transition: color 0.3s;
    }
    .file-step-item.active .file-step-label { color: #1e293b; }
    .file-step-item.completed .file-step-label { color: #10b981; }
    .file-step-connector {
        width: 80px;
        height: 2px;
        background: #e2e8f0;
        margin: 0 14px;
        transition: background 0.3s;
        flex-shrink: 0;
    }
    .file-step-connector.completed { background: #10b981; }

    @media (max-width: 768px) {
        .file-step-label { display: none; }
        .file-step-connector { width: 30px; margin: 0 6px; }
    }

    /* ========== FORM CARD ========== */
    .file-form-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        overflow: hidden;
    }
    .file-form-card-header {
        padding: 24px 32px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .file-form-card-header .header-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .file-form-card-header .header-icon.sky { background: #f0f9ff; color: #0ea5e9; }
    .file-form-card-header .header-icon.violet { background: #f5f3ff; color: #8b5cf6; }
    .file-form-card-header .header-icon.teal { background: #f0fdfa; color: #14b8a6; }
    .file-form-card-header h3 {
        font-size: 17px;
        font-weight: 700;
        color: #0f172a;
        margin: 0;
    }
    .file-form-card-header span {
        font-size: 13px;
        color: #94a3b8;
        display: block;
        margin-top: 2px;
    }
    .file-form-card-body {
        padding: 28px 32px 32px;
    }

    /* ========== FORM FIELDS ========== */
    .ff-group {
        margin-bottom: 22px;
    }
    .ff-group label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #334155;
        margin-bottom: 7px;
        letter-spacing: 0.2px;
    }
    .ff-group label .req {
        display: inline-block;
        width: 5px;
        height: 5px;
        background: #ef4444;
        border-radius: 50%;
        margin-left: 4px;
        vertical-align: middle;
    }
    .ff-group input[type="text"],
    .ff-group input[type="email"],
    .ff-group input[type="number"],
    .ff-group input[type="date"],
    .ff-group select {
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
    .ff-group input:focus,
    .ff-group select:focus {
        border-color: #0ea5e9;
        box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.1);
    }
    .ff-group input::placeholder {
        color: #cbd5e1;
    }
    .ff-group select {
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 24 24' fill='none' stroke='%2394a3b8' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 14px center;
        padding-right: 36px;
    }
    .ff-group .icon-wrap {
        position: relative;
    }
    .ff-group .icon-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 14px;
        pointer-events: none;
    }
    .ff-group .icon-wrap input,
    .ff-group .icon-wrap select {
        padding-left: 40px;
    }

    /* ========== FILE UPLOAD ZONE ========== */
    .file-drop-zone {
        border: 2px dashed #cbd5e1;
        border-radius: 14px;
        padding: 40px 24px;
        text-align: center;
        cursor: pointer;
        transition: all 0.25s;
        background: #fafbfc;
        position: relative;
    }
    .file-drop-zone:hover,
    .file-drop-zone.dragover {
        border-color: #0ea5e9;
        background: #f0f9ff;
    }
    .file-drop-zone .drop-icon {
        width: 60px;
        height: 60px;
        border-radius: 16px;
        background: #f0f9ff;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
        font-size: 24px;
        color: #0ea5e9;
    }
    .file-drop-zone h4 {
        font-size: 15px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }
    .file-drop-zone h4 em {
        color: #0ea5e9;
        font-style: normal;
        text-decoration: underline;
    }
    .file-drop-zone p {
        font-size: 13px;
        color: #94a3b8;
        margin: 0;
    }
    .file-drop-zone input[type="file"] {
        position: absolute;
        inset: 0;
        opacity: 0;
        cursor: pointer;
    }
    .file-picked {
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
    .file-picked i { color: #22c55e; }
    .file-picked.visible { display: flex; }

    /* ========== STEP PANELS ========== */
    .file-step-panel { display: none; animation: fileFadeSlide 0.35s ease; }
    .file-step-panel.active { display: block; }
    @keyframes fileFadeSlide {
        from { opacity: 0; transform: translateY(12px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* ========== DECLARATION ========== */
    .file-declaration {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 20px 24px;
        margin-top: 28px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .file-declaration input[type="checkbox"] {
        width: 18px;
        height: 18px;
        border-radius: 4px;
        accent-color: #0ea5e9;
        flex-shrink: 0;
        margin-top: 2px;
        cursor: pointer;
    }
    .file-declaration span {
        font-size: 13px;
        color: #475569;
        line-height: 1.6;
    }

    /* ========== BUTTONS ========== */
    .file-nav-btns {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 28px;
        gap: 12px;
    }
    .fbtn {
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
    .fbtn-back {
        background: #f1f5f9;
        color: #475569;
    }
    .fbtn-back:hover { background: #e2e8f0; }
    .fbtn-next {
        background: #0ea5e9;
        color: #fff;
    }
    .fbtn-next:hover { background: #0284c7; box-shadow: 0 4px 14px rgba(14, 165, 233, 0.3); }
    .fbtn-submit {
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
    .fbtn-submit:hover { background: #059669; box-shadow: 0 4px 16px rgba(16, 185, 129, 0.3); }

    /* ========== SIDEBAR ========== */
    .file-tips-card {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        overflow: hidden;
        position: sticky;
        top: 100px;
    }
    .file-tips-header {
        background: #0f172a;
        padding: 20px 24px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .file-tips-header i {
        color: #fbbf24;
        font-size: 18px;
    }
    .file-tips-header h4 {
        color: #f8fafc;
        font-size: 16px;
        font-weight: 700;
        margin: 0;
    }
    .file-tips-body {
        padding: 20px 24px;
    }
    .file-tip {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        padding: 12px 0;
        border-bottom: 1px solid #f1f5f9;
    }
    .file-tip:last-child { border-bottom: none; }
    .file-tip-num {
        width: 24px;
        height: 24px;
        border-radius: 8px;
        background: #f1f5f9;
        color: #0ea5e9;
        font-size: 11px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    .file-tip p {
        font-size: 13px;
        color: #475569;
        margin: 0;
        line-height: 1.5;
    }
    .file-tip p strong { color: #1e293b; }

    /* ========== PROGRESS BAR ========== */
    .file-progress-bar {
        height: 3px;
        background: #e2e8f0;
        border-radius: 100px;
        margin-bottom: 32px;
        overflow: hidden;
    }
    .file-progress-bar .bar {
        height: 100%;
        background: #0ea5e9;
        border-radius: 100px;
        transition: width 0.4s ease;
    }

    /* ========== ALERTS ========== */
    .file-alert-danger {
        background: #fef2f2;
        border: 1px solid #fecaca;
        border-radius: 12px;
        padding: 16px 20px;
        margin-bottom: 24px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
    }
    .file-alert-danger i {
        color: #ef4444;
        font-size: 18px;
        flex-shrink: 0;
        margin-top: 2px;
    }
    .file-alert-danger ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }
    .file-alert-danger ul li {
        font-size: 13px;
        color: #991b1b;
        padding: 2px 0;
    }

    /* ========== INFO CALLOUT ========== */
    .info-callout {
        background: #f0f9ff;
        border: 1px solid #bae6fd;
        border-radius: 10px;
        padding: 14px 18px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        margin-bottom: 22px;
    }
    .info-callout i {
        color: #0ea5e9;
        font-size: 16px;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .info-callout p {
        font-size: 13px;
        color: #0c4a6e;
        margin: 0;
        line-height: 1.5;
    }
</style>
@endpush

@section('content')
@include('frontend.header')

{{-- HERO SECTION --}}
<div class="modern-file-hero">
    <div class="container">
        <div class="hero-badge">
            <i class="fas fa-folder-open"></i>
            {{ isset($file) ? 'Edit File' : 'File Deposit' }}
        </div>
        <h1>{{ isset($file) ? 'Edit File Document' : 'File Deposition Form' }}</h1>
        <p>{{ isset($file) ? 'Update your file document information below.' : 'Upload and archive institutional files securely to the university system.' }}</p>
        <div class="breadcrumb-nav">
            <a href="{{route('dashboard')}}">Dashboard</a>
            <span class="sep"><i class="fas fa-chevron-right"></i></span>
            <span class="current">Deposit File</span>
        </div>
    </div>
</div>

{{-- FORM BODY --}}
<div class="modern-file-container">
    <div class="container">

        {{-- STEPPER --}}
        <div class="file-stepper" id="fileStepper">
            <div class="file-step-item active" data-step="1">
                <div class="file-step-circle">1</div>
                <div class="file-step-label">Depositor Info</div>
            </div>
            <div class="file-step-connector"></div>
            <div class="file-step-item" data-step="2">
                <div class="file-step-circle">2</div>
                <div class="file-step-label">File Details & Upload</div>
            </div>
            <div class="file-step-connector"></div>
            <div class="file-step-item" data-step="3">
                <div class="file-step-circle">3</div>
                <div class="file-step-label">Unit Selection</div>
            </div>
        </div>

        <div class="file-progress-bar">
            <div class="bar" id="fileProgressBar" style="width: 33%"></div>
        </div>

        <div class="row">
            {{-- MAIN FORM --}}
            <div class="col-xl-8 col-lg-8 col-md-12 col-12">
                <form action="{{ isset($file) ? route('files.update', $file->id) : route('dashboard.file.store') }}" method="post" enctype="multipart/form-data" id="fileDepoForm">
                    @csrf
                    @if(isset($file))
                        @method('PUT')
                    @endif

                    @if ($errors->any())
                    <div class="file-alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <ul>
                            @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    {{-- STEP 1: Depositor Information --}}
                    <div class="file-step-panel active" id="fileStep1">
                        <div class="file-form-card">
                            <div class="file-form-card-header">
                                <div class="header-icon sky"><i class="fas fa-user-circle"></i></div>
                                <div>
                                    <h3>Depositor Information</h3>
                                    <span>Enter your personal contact details</span>
                                </div>
                            </div>
                            <div class="file-form-card-body">
                                <div class="ff-group">
                                    <label>Depositor's Name <span class="req"></span></label>
                                    <div class="icon-wrap">
                                        <i class="fas fa-user"></i>
                                        <input type="text" placeholder="Enter your full name" name="depositor_name" value="{{ old('depositor_name', $file->depositor_name ?? '') }}" required>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ff-group">
                                            <label>Email Address <span class="req"></span></label>
                                            <div class="icon-wrap">
                                                <i class="fas fa-envelope"></i>
                                                <input type="email" placeholder="email@university.edu" name="email" value="{{ old('email', $file->email ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ff-group">
                                            <label>Phone Number <span class="req"></span></label>
                                            <div class="icon-wrap">
                                                <i class="fas fa-phone"></i>
                                                <input type="text" placeholder="Enter phone number" name="phone_number" value="{{ old('phone_number', $file->phone_number ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="file-nav-btns">
                            <div></div>
                            <button type="button" class="fbtn fbtn-next" onclick="fileGoStep(2)">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 2: File Details & Upload --}}
                    <div class="file-step-panel" id="fileStep2">
                        <div class="file-form-card">
                            <div class="file-form-card-header">
                                <div class="header-icon violet"><i class="fas fa-file-lines"></i></div>
                                <div>
                                    <h3>File Details & Upload</h3>
                                    <span>Describe and upload your document</span>
                                </div>
                            </div>
                            <div class="file-form-card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ff-group">
                                            <label>File Title <span class="req"></span></label>
                                            <div class="icon-wrap">
                                                <i class="fas fa-heading"></i>
                                                <input type="text" placeholder="Enter file title" name="file_title" value="{{ old('file_title', $file->file_title ?? '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ff-group">
                                            <label>File Format <span class="req"></span></label>
                                            <div class="icon-wrap">
                                                <i class="fas fa-file-circle-check"></i>
                                                <select name="file_format" required>
                                                    <option value="Pdf" {{ old('file_format', $file->file_format ?? '') == 'Pdf' ? 'selected' : '' }}>PDF</option>
                                                    <option value="Word" {{ old('file_format', $file->file_format ?? '') == 'Word' ? 'selected' : '' }}>Word</option>
                                                    <option value="Excel" {{ old('file_format', $file->file_format ?? '') == 'Excel' ? 'selected' : '' }}>Excel</option>
                                                    <option value="Csv" {{ old('file_format', $file->file_format ?? '') == 'Csv' ? 'selected' : '' }}>CSV</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="ff-group">
                                            <label>Date Created <span class="req"></span></label>
                                            <div class="icon-wrap">
                                                <i class="fas fa-calendar-plus"></i>
                                                <input type="date" name="year_created" value="{{ old('year_created', isset($file) ? \Carbon\Carbon::parse($file->year_created)->format('Y-m-d') : '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="ff-group">
                                            <label>Date Deposited <span class="req"></span></label>
                                            <div class="icon-wrap">
                                                <i class="fas fa-calendar-check"></i>
                                                <input type="date" name="year_deposit" value="{{ old('year_deposit', isset($file) ? \Carbon\Carbon::parse($file->year_deposit)->format('Y-m-d') : '') }}" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="ff-group">
                                    <label>Upload File <span class="req"></span></label>
                                    <div class="file-drop-zone" id="fileDropZone">
                                        <div class="drop-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                                        <h4>Drag & drop or <em>browse files</em></h4>
                                        <p>All common document formats accepted</p>
                                        <input type="file" name="document_file" {{ isset($file) ? '' : 'required' }} onchange="fileShowName(this, 'filePickedName')">
                                    </div>
                                    <div class="file-picked" id="filePickedName">
                                        <i class="fas fa-check-circle"></i>
                                        <span class="fpn-text"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="file-nav-btns">
                            <button type="button" class="fbtn fbtn-back" onclick="fileGoStep(1)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="button" class="fbtn fbtn-next" onclick="fileGoStep(3)">
                                Continue <i class="fas fa-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    {{-- STEP 3: Unit Selection --}}
                    <div class="file-step-panel" id="fileStep3">
                        <div class="file-form-card">
                            <div class="file-form-card-header">
                                <div class="header-icon teal"><i class="fas fa-sitemap"></i></div>
                                <div>
                                    <h3>Unit Selection</h3>
                                    <span>Choose the department this file belongs to</span>
                                </div>
                            </div>
                            <div class="file-form-card-body">
                                <div class="info-callout">
                                    <i class="fas fa-info-circle"></i>
                                    <p>Select the organizational unit where this file should be archived. This helps maintain proper records across all university departments.</p>
                                </div>

                                <div class="ff-group">
                                    <label>Organizational Unit <span class="req"></span></label>
                                    <div class="icon-wrap">
                                        <i class="fas fa-building-columns"></i>
                                        <select name="unit" required>
                                            <option value="Registry" {{ old('unit', $file->unit ?? '') == 'Registry' ? 'selected' : '' }}>Registry</option>
                                            <option value="School of Nursing and Midwifery" {{ old('unit', $file->unit ?? '') == 'School of Nursing and Midwifery' ? 'selected' : '' }}>School of Nursing and Midwifery</option>
                                            <option value="Assurance Directorate" {{ old('unit', $file->unit ?? '') == 'Assurance Directorate' ? 'selected' : '' }}>Assurance Directorate</option>
                                            <option value="Directorate" {{ old('unit', $file->unit ?? '') == 'Directorate' ? 'selected' : '' }}>Directorate</option>
                                            <option value="Finance Directorate" {{ old('unit', $file->unit ?? '') == 'Finance Directorate' ? 'selected' : '' }}>Finance Directorate</option>
                                            <option value="Works and Physical Development Office" {{ old('unit', $file->unit ?? '') == 'Works and Physical Development Office' ? 'selected' : '' }}>Works and Physical Development Office</option>
                                            <option value="Audit" {{ old('unit', $file->unit ?? '') == 'Audit' ? 'selected' : '' }}>Audit</option>
                                            <option value="Guidance and Counselling Unit" {{ old('unit', $file->unit ?? '') == 'Guidance and Counselling Unit' ? 'selected' : '' }}>Guidance and Counselling Unit</option>
                                            <option value="The University Library" {{ old('unit', $file->unit ?? '') == 'The University Library' ? 'selected' : '' }}>The University Library</option>
                                            <option value="Human Resource Unit" {{ old('unit', $file->unit ?? '') == 'Human Resource Unit' ? 'selected' : '' }}>Human Resource Unit</option>
                                            <option value="Hostels" {{ old('unit', $file->unit ?? '') == 'Hostels' ? 'selected' : '' }}>Hostels</option>
                                            <option value="Faculty of Economics and Business Administration" {{ old('unit', $file->unit ?? '') == 'Faculty of Economics and Business Administration' ? 'selected' : '' }}>Faculty of Economics and Business Administration</option>
                                            <option value="Faculty of Education" {{ old('unit', $file->unit ?? '') == 'Faculty of Education' ? 'selected' : '' }}>Faculty of Education</option>
                                            <option value="School of Public Health and Allied Science" {{ old('unit', $file->unit ?? '') == 'School of Public Health and Allied Science' ? 'selected' : '' }}>School of Public Health and Allied Science</option>
                                            <option value="Faculty of Religious and Social Sciences" {{ old('unit', $file->unit ?? '') == 'Faculty of Religious and Social Sciences' ? 'selected' : '' }}>Faculty of Religious and Social Sciences</option>
                                            <option value="Faculty of Computing, Engineering and Mathematical Sciences" {{ old('unit', $file->unit ?? '') == 'Faculty of Computing, Engineering and Mathematical Sciences' ? 'selected' : '' }}>Faculty of Computing, Engineering and Mathematical Sciences</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="file-declaration">
                                    <input type="checkbox" id="fileConsent" required>
                                    <span>I confirm that I have the necessary permissions to deposit this file into the university's archives and that all information provided is accurate.</span>
                                </div>
                            </div>
                        </div>
                        <div class="file-nav-btns">
                            <button type="button" class="fbtn fbtn-back" onclick="fileGoStep(2)">
                                <i class="fas fa-arrow-left"></i> Back
                            </button>
                            <button type="submit" class="fbtn-submit">
                                <i class="fas fa-paper-plane"></i> Deposit File
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            {{-- SIDEBAR --}}
            <div class="col-xl-4 col-lg-4 col-md-12 col-12 mt-4 mt-lg-0">
                <div class="file-tips-card">
                    <div class="file-tips-header">
                        <i class="fas fa-lightbulb"></i>
                        <h4>Upload Tips</h4>
                    </div>
                    <div class="file-tips-body">
                        <div class="file-tip">
                            <div class="file-tip-num">1</div>
                            <p><strong>Check Formats</strong> &mdash; Ensure your files match the selected format type.</p>
                        </div>
                        <div class="file-tip">
                            <div class="file-tip-num">2</div>
                            <p><strong>Compress First</strong> &mdash; Large files should be compressed before uploading.</p>
                        </div>
                        <div class="file-tip">
                            <div class="file-tip-num">3</div>
                            <p><strong>Name Clearly</strong> &mdash; Use descriptive filenames like <em>Budget_Report_Q1_2024.pdf</em>.</p>
                        </div>
                        <div class="file-tip">
                            <div class="file-tip-num">4</div>
                            <p><strong>Correct Unit</strong> &mdash; Double-check the unit selection before submitting.</p>
                        </div>
                        <div class="file-tip">
                            <div class="file-tip-num">5</div>
                            <p><strong>Stable Connection</strong> &mdash; Ensure reliable internet during the upload process.</p>
                        </div>
                        <div class="file-tip">
                            <div class="file-tip-num">6</div>
                            <p><strong>Preview First</strong> &mdash; Open your file to verify content before depositing.</p>
                        </div>
                        <div class="file-tip">
                            <div class="file-tip-num">7</div>
                            <p><strong>Keep Backups</strong> &mdash; Always retain a local copy of deposited files.</p>
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
    const totalSteps = 3;
    let currentStep = 1;

    window.fileGoStep = function(step) {
        currentStep = step;
        // Update panels
        for (let i = 1; i <= totalSteps; i++) {
            const panel = document.getElementById('fileStep' + i);
            if (panel) panel.classList.toggle('active', i === step);
        }
        // Update stepper
        document.querySelectorAll('#fileStepper .file-step-item').forEach(function(el) {
            const s = parseInt(el.getAttribute('data-step'));
            el.classList.remove('active', 'completed');
            if (s === step) el.classList.add('active');
            else if (s < step) el.classList.add('completed');
        });
        // Update connectors
        document.querySelectorAll('#fileStepper .file-step-connector').forEach(function(el, idx) {
            el.classList.toggle('completed', idx < step - 1);
        });
        // Update progress bar
        const pct = Math.round((step / totalSteps) * 100);
        document.getElementById('fileProgressBar').style.width = pct + '%';
        // Scroll to top
        window.scrollTo({ top: document.querySelector('.modern-file-container').offsetTop - 20, behavior: 'smooth' });
    };

    window.fileShowName = function(input, displayId) {
        const display = document.getElementById(displayId);
        if (input.files && input.files.length > 0) {
            display.querySelector('.fpn-text').textContent = input.files[0].name;
            display.classList.add('visible');
        } else {
            display.classList.remove('visible');
        }
    };

    // Drag and drop visual feedback
    document.querySelectorAll('.file-drop-zone').forEach(function(zone) {
        zone.addEventListener('dragover', function(e) { e.preventDefault(); zone.classList.add('dragover'); });
        zone.addEventListener('dragleave', function() { zone.classList.remove('dragover'); });
        zone.addEventListener('drop', function() { zone.classList.remove('dragover'); });
    });
})();
</script>
@endpush
