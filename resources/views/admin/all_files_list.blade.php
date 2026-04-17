@extends('layout.app')

@push('styles')
<!-- Inter Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Modern All All Files Page Styles - Consistent Theme */
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .all-pending-files-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .all-pending-files-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="all-pending-files-grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(100,116,139,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23all-pending-files-grid)" /></svg>');
        opacity: 0.7;
    }

    .all-pending-files-hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #475569;
        margin-bottom: 1rem;
        background: linear-gradient(135deg, #475569 0%, #64748b 50%, #94a3b8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 1.1rem;
        color: #475569;
        margin-bottom: 2rem;
    }

    .hero-stats {
        display: flex;
        justify-content: center;
        gap: 3rem;
        margin-top: 2rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 2.2rem;
        font-weight: 700;
        color: #64748b;
        display: block;
    }

    .stat-label {
        font-size: 0.9rem;
        color: #475569;
        margin-top: 0.5rem;
    }

    /* Search and Filter Section */
    .search-filter-section {
        background: white;
        padding: 2rem 0;
        border-bottom: 1px solid #e5e7eb;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .search-box {
        position: relative;
        max-width: 600px;
        margin: 0 auto;
    }

    .search-input {
        width: 100%;
        padding: 15px 50px 15px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        font-size: 1rem;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    .search-input:focus {
        outline: none;
        border-color: #64748b;
        background: white;
        box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.1);
    }

    .search-btn {
        position: absolute;
        right: 8px;
        top: 50%;
        transform: translateY(-50%);
        background: #64748b;
        border: none;
        padding: 8px 12px;
        border-radius: 50px;
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .search-btn:hover {
        background: #475569;
    }

    .filter-tabs {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .filter-tab {
        padding: 8px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 25px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .filter-tab:hover,
    .filter-tab.active {
        border-color: #64748b;
        background: #64748b;
        color: white;
        text-decoration: none;
    }

    /* Modern File Cards */
    .files-section {
        background: #f9fafb;
        padding: 3rem 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .all-pending-files-list {
        display: flex;
        flex-direction: column;
        gap: 1.5rem;
    }

    .all-pending-file-card {
        display: flex;
        align-items: center;
        border-radius: 16px;
        padding: 1.5rem;
        min-height: 120px;
        background: white;
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
    }

    .all-pending-file-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        border-radius: 16px 0 0 16px;
    }

    .all-pending-file-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .file-card-header {
        height: 80px;
        width: 80px;
        border-radius: 16px;
        margin-right: 1.5rem;
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        position: relative;
    }

    .file-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: white;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .pdf-file .file-icon i {
        color: #dc3545;
        font-size: 1.8rem;
    }

    .doc-file .file-icon i {
        color: #2563eb;
        font-size: 1.8rem;
    }

    .file-card-body {
        flex: 1;
        display: flex;
        align-items: center;
        gap: 1rem;
        min-width: 0;
    }

    .file-main-info {
        flex: 1;
        min-width: 0;
        max-width: 350px;
    }

    .file-title {
        margin: 0 0 0.5rem 0;
        font-size: 1.2rem;
        font-weight: 600;
        color: #111827;
        line-height: 1.3;
    }

    .file-title a {
        color: inherit;
        text-decoration: none;
        white-space: normal;
        word-wrap: break-word;
    }

    .file-title a:hover {
        color: #64748b;
    }

    .file-meta {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
        align-items: center;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.85rem;
        color: #6b7280;
        white-space: nowrap;
    }

    .meta-item i {
        color: #64748b;
        font-size: 0.9rem;
        width: 16px;
        text-align: center;
    }

    .file-depositor-section {
        min-width: 200px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .depositor-info {
        display: flex;
        align-items: center;
        gap: 0.8rem;
    }

    .depositor-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1rem;
        box-shadow: 0 2px 8px rgba(148, 163, 184, 0.3);
    }

    .depositor-avatar i {
        font-size: 1.1rem;
    }

    .depositor-name {
        font-size: 0.9rem;
        font-weight: 500;
        color: #374151;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 140px;
    }

    .file-actions {
        display: flex;
        gap: 0.5rem;
        min-width: 180px;
        justify-content: center;
        flex-shrink: 0;
    }

    .action-btn {
        padding: 10px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        min-width: 40px;
        height: 40px;
        cursor: pointer;
    }

    .action-btn:hover {
        text-decoration: none;
        transform: translateY(-2px);
    }

    .action-btn.view {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .action-btn.view:hover {
        background: #3b82f6;
        color: white;
    }

    .action-btn.download {
        border-color: #10b981;
        color: #10b981;
    }

    .action-btn.download:hover {
        background: #10b981;
        color: white;
    }

    .action-btn.approve {
        border-color: #10b981;
        color: #10b981;
        background: rgba(16, 185, 129, 0.1);
    }

    .action-btn.approve:hover {
        background: #10b981;
        color: white;
    }

    .action-btn.delete {
        border-color: #ef4444;
        color: #ef4444;
    }

    .action-btn.delete:hover {
        background: #ef4444;
        color: white;
    }

    .action-btn.disabled {
        opacity: 0.5;
        cursor: not-allowed;
        pointer-events: none;
        background: #f3f4f6;
        color: #9ca3af;
        border-color: #d1d5db;
    }

    .file-status {
        min-width: 120px;
        display: flex;
        justify-content: center;
        flex-shrink: 0;
    }

    .status-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .status-badge.pending {
        background: rgba(254, 243, 199, 0.8);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .status-badge.approved {
        background: rgba(220, 252, 231, 0.8);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .no-files {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .no-files i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #64748b;
        opacity: 0.6;
    }

    /* Responsive Design */
    @media (max-width: 1200px) {
        .file-card-body {
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .file-main-info {
            max-width: 100%;
            min-width: 0;
        }
        
        .file-depositor-section,
        .file-actions,
        .file-status {
            min-width: auto;
            width: 100%;
            justify-content: center;
        }
    }

    @media (max-width: 768px) {
        .all-pending-file-card {
            flex-direction: column;
            text-align: center;
            padding: 1.5rem;
        }

        .file-card-header {
            margin: 0 auto 1rem;
        }

        .file-card-body {
            flex-direction: column;
            align-items: center;
            padding: 0;
            width: 100%;
        }

        .file-main-info {
            text-align: center;
            margin-bottom: 1rem;
            width: 100%;
            max-width: 100%;
        }

        .file-meta {
            justify-content: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .file-depositor-section {
            justify-content: center;
            margin: 1rem 0;
            min-width: auto;
        }

        .file-actions {
            margin-top: 1rem;
            width: 100%;
            justify-content: center;
        }

        .hero-stats {
            gap: 1.5rem;
        }

        .hero-title {
            font-size: 2rem;
        }
    }
</style>
@endpush

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
                {{-- sidebar menu --}}
                @include('components.sidebar')
                <div class="col-xl-9 col-lg-9 col-md-12">
                    <!-- Hero Section -->
                    <div class="all-pending-files-hero">
                        <div class="container">
                            <div class="all-pending-files-hero-content">
                                <h1 class="hero-title">All All Files</h1>
                                <p class="hero-subtitle">System-wide overview of all files awaiting approval</p>
                                
                                <div class="hero-stats">
                                    <div class="stat-item">
                                        <span class="stat-number">{{ count($files) }}</span>
                                        <div class="stat-label">Total Files</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Filter Section -->
                    <div class="search-filter-section">
                        <div class="container">
                            <div class="search-box">
                                <input type="text" class="search-input" id="searchInput" placeholder="Search all pending files by title, depositor, or document ID...">
                                <button class="search-btn" onclick="performSearch()">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            
                            <div class="filter-tabs">
                                <a href="#" class="filter-tab active" data-filter="all">All Files</a>
                                <a href="#" class="filter-tab" data-filter="pdf">PDF Files</a>
                                <a href="#" class="filter-tab" data-filter="doc">Word Documents</a>
                                <a href="#" class="filter-tab" data-filter="recent">Recently Added</a>
                            </div>
                        </div>
                    </div>

                    <!-- Files Section -->
                    <div class="files-section">
                        <div class="container">
                            @if (count($files) > 0)
                                <div class="all-pending-files-list">
                                    @foreach ($files as $file)
                                        @php
                                            $extension = pathinfo($file->document_file, PATHINFO_EXTENSION);
                                            $isPdf = strtolower($extension) === 'pdf';
                                            $isDoc = in_array(strtolower($extension), ['doc', 'docx']);
                                        @endphp
                                        <div class="all-pending-file-card {{ $isPdf ? 'pdf-file' : ($isDoc ? 'doc-file' : '') }}" data-type="{{ strtolower($extension) }}" data-search="{{ strtolower($file->file_title . ' ' . $file->depositor_name . ' ' . $file->document_id) }}">
                                            <div class="file-card-header">
                                                <div class="file-icon">
                                                    @if($isPdf)
                                                        <i class="fas fa-file-pdf"></i>
                                                    @elseif($isDoc)
                                                        <i class="fas fa-file-word"></i>
                                                    @else
                                                        <i class="fas fa-file"></i>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <div class="file-card-body">
                                                <div class="file-main-info">
                                                    <h4 class="file-title">
                                                        <a href="#" title="{{ $file->file_title }}">{{ $file->file_title }}</a>
                                                    </h4>
                                                    <div class="file-meta">
                                                        <div class="meta-item">
                                                            <i class="fas fa-hashtag"></i>
                                                            <span>{{ $file->document_id }}</span>
                                                        </div>
                                                        <div class="meta-item">
                                                            <i class="fas fa-calendar"></i>
                                                            <span>{{ $file->year_deposit }}</span>
                                                        </div>
                                                        <div class="meta-item">
                                                            <i class="fas fa-file-alt"></i>
                                                            <span>{{ strtoupper($extension) }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="file-depositor-section">
                                                    <div class="depositor-info">
                                                        <div class="depositor-avatar">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="depositor-name">{{ $file->depositor_name }}</div>
                                                    </div>
                                                </div>
                                                
                                                <div class="file-actions">
                                                    @if($isPdf)
                                                        <a href="{{ asset($file->document_file) }}" target="_blank" class="action-btn view" title="View PDF">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                    @endif
                                                    @if(\App\Http\Controllers\Dashboard\FilesController::canDownloadFile($file))
                                                        <a href="{{ route('download.file', $file->id) }}" class="action-btn download" title="Download File">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                    @else
                                                        <span class="action-btn download disabled" title="You can only download your own files">
                                                            <i class="fas fa-download"></i>
                                                        </span>
                                                    @endif
                                                    <a href="{{ route('files.edit', $file->id) }}" class="action-btn edit" title="Edit File">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <form action="{{ route('file.destroy', $file->id) }}" method="post" style="display: inline;">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="action-btn delete" title="Delete File" onclick="return confirm('Are you sure you want to delete this file?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-files">
                                    <i class="fas fa-folder-open"></i>
                                    <h4>No Files</h4>
                                    <p>You haven't uploaded any files yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<script>
// Search and Filter functionality for All All Files
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterTabs = document.querySelectorAll('.filter-tab');
    const fileCards = document.querySelectorAll('.all-pending-file-card');

    // Search functionality
    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase();
        const activeFilter = document.querySelector('.filter-tab.active').getAttribute('data-filter');

        fileCards.forEach(card => {
            const searchData = card.getAttribute('data-search');
            const cardType = card.getAttribute('data-type');
            
            let showBySearch = searchData.includes(searchTerm);
            let showByFilter = true;

            // Apply filters
            if (activeFilter !== 'all') {
                if (activeFilter === 'pdf') {
                    showByFilter = cardType === 'pdf';
                } else if (activeFilter === 'doc') {
                    showByFilter = cardType === 'doc' || cardType === 'docx';
                } else if (activeFilter === 'recent') {
                    // For recent, we could add date logic here
                    showByFilter = true; // For now, show all
                }
            }

            // Show/hide card
            if (showBySearch && showByFilter) {
                card.style.display = 'flex';
                card.style.animation = 'fadeIn 0.3s ease';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // Search on input
    searchInput.addEventListener('input', performSearch);

    // Filter tabs
    filterTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all tabs
            filterTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Perform search with new filter
            performSearch();
        });
    });

    // Add fade-in animation
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    `;
    document.head.appendChild(style);
});

// Make performSearch global for the search button
function performSearch() {
    const event = new Event('input');
    document.getElementById('searchInput').dispatchEvent(event);
}
</script>
