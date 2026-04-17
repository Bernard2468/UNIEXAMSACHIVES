@extends('layout.app')

@push('styles')
<!-- Inter Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .folder-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .folder-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="folder-grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(100,116,139,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23folder-grid)" /></svg>');
        opacity: 0.7;
    }

    .folder-hero-content {
        position: relative;
        z-index: 2;
    }

    .folder-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1rem;
    }

    .folder-icon {
        width: 80px;
        height: 80px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: {{ $folder->color }};
        color: white;
        font-size: 2rem;
    }

    .folder-info h1 {
        font-size: 2.5rem;
        font-weight: 700;
        color: #475569;
        margin: 0;
        background: linear-gradient(135deg, #475569 0%, #64748b 50%, #94a3b8 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .folder-description {
        color: #475569;
        font-size: 1.1rem;
        margin-top: 0.5rem;
    }

    .folder-stats {
        display: flex;
        gap: 2rem;
        margin-top: 1.5rem;
    }

    .stat-item {
        text-align: center;
    }

    .stat-number {
        font-size: 1.8rem;
        font-weight: 700;
        color: #64748b;
        display: block;
    }

    .stat-label {
        font-size: 0.85rem;
        color: #475569;
        margin-top: 0.25rem;
    }

    .folder-actions {
        display: flex;
        gap: 1rem;
        margin-top: 1.5rem;
        flex-wrap: wrap;
    }

    .action-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 10px 16px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s ease;
        border: 2px solid;
        cursor: pointer;
    }

    .action-btn.primary {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        color: white;
        border-color: #64748b;
    }

    .action-btn.primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
        color: white;
        text-decoration: none;
    }

    .action-btn.secondary {
        background: white;
        color: #f59e0b;
        border-color: #f59e0b;
    }

    .action-btn.secondary:hover {
        background: #f59e0b;
        color: white;
        text-decoration: none;
    }

    .section {
        background: #f9fafb;
        padding: 3rem 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .section-title {
        font-size: 1.5rem;
        font-weight: 600;
        color: #111827;
        margin: 0;
    }

    .add-files-btn {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        border: none;
        padding: 10px 16px;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        transition: all 0.3s ease;
    }

    .add-files-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(16, 185, 129, 0.3);
    }

    .files-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .file-card {
        display: flex;
        align-items: center;
        background: white;
        border-radius: 12px;
        padding: 1.25rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        border: 1px solid #f3f4f6;
        transition: all 0.3s ease;
    }

    .file-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .file-icon-wrapper {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 1rem;
        flex-shrink: 0;
    }

    .file-icon-wrapper.pdf {
        background: rgba(220, 53, 69, 0.1);
        color: #dc3545;
    }

    .file-icon-wrapper.doc {
        background: rgba(37, 99, 235, 0.1);
        color: #2563eb;
    }

    .file-info {
        flex: 1;
        min-width: 0;
    }

    .file-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.25rem 0;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .file-meta {
        display: flex;
        gap: 1rem;
        font-size: 0.85rem;
        color: #6b7280;
    }

    .file-actions {
        display: flex;
        gap: 0.5rem;
        flex-shrink: 0;
    }

    .file-action-btn {
        width: 36px;
        height: 36px;
        border-radius: 8px;
        border: 2px solid #e5e7eb;
        background: white;
        color: #6b7280;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .file-action-btn:hover {
        transform: translateY(-1px);
        text-decoration: none;
    }

    .file-action-btn.view {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .file-action-btn.view:hover {
        background: #3b82f6;
        color: white;
    }

    .file-action-btn.download {
        border-color: #10b981;
        color: #10b981;
    }

    .file-action-btn.download:hover {
        background: #10b981;
        color: white;
    }

    .file-action-btn.remove {
        border-color: #ef4444;
        color: #ef4444;
    }

    .file-action-btn.remove:hover {
        background: #ef4444;
        color: white;
    }

    .no-files {
        text-align: center;
        padding: 3rem 2rem;
        color: #6b7280;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .no-files i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #64748b;
        opacity: 0.6;
    }

    .add-files-modal .modal-dialog {
        max-width: 800px;
    }

    .available-files-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
        gap: 1rem;
        max-height: 400px;
        overflow-y: auto;
        padding: 1rem;
        border: 2px solid #f3f4f6;
        border-radius: 8px;
        background: #f9fafb;
    }

    .available-file-card {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        border: 2px solid #e5e7eb;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .available-file-card:hover {
        border-color: #64748b;
    }

    .available-file-card.selected {
        border-color: #64748b;
        background: rgba(100, 116, 139, 0.05);
    }

    .available-file-checkbox {
        margin-bottom: 0.5rem;
    }

    @media (max-width: 768px) {
        .folder-header {
            flex-direction: column;
            text-align: center;
        }
        
        .folder-info h1 {
            font-size: 2rem;
        }
        
        .folder-stats {
            justify-content: center;
        }
        
        .file-card {
            flex-direction: column;
            text-align: center;
            gap: 1rem;
        }
        
        .file-info {
            text-align: center;
        }
        
        .section-header {
            flex-direction: column;
            gap: 1rem;
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
                @include('components.sidebar')
                <div class="col-xl-9 col-lg-9 col-md-12">
                    <!-- Folder Header -->
                    <div class="folder-hero">
                        <div class="container">
                            <div class="folder-hero-content">
                                <div class="folder-header">
                                    <div class="folder-icon">
                                        <i class="fas fa-folder"></i>
                                    </div>
                                    <div class="folder-info">
                                        <h1>{{ $folder->name }}</h1>
                                        @if($folder->description)
                                            <div class="folder-description">{{ $folder->description }}</div>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="folder-stats">
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $folder->files->count() }}</span>
                                        <div class="stat-label">Files in Folder</div>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $availableFiles->count() }}</span>
                                        <div class="stat-label">Available to Add</div>
                                    </div>
                                </div>
                                
                                <div class="folder-actions">
                                    <a href="{{ route('dashboard.folders.index') }}" class="action-btn secondary">
                                        <i class="fas fa-arrow-left"></i>
                                        Back to Folders
                                    </a>
                                    <a href="{{ route('dashboard.folders.edit', $folder) }}" class="action-btn secondary">
                                        <i class="fas fa-edit"></i>
                                        Edit Folder
                                    </a>
                                    <a href="{{ route('dashboard.folders.security', $folder) }}" class="action-btn secondary" title="Security">
                                        <i class="fas fa-shield-alt"></i>
                                        Security
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Files Section -->
                    <div class="section">
                        <div class="container">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            <div class="section-header">
                                <h2 class="section-title">Files in Folder</h2>
                                @if($availableFiles->count() > 0)
                                    <button class="add-files-btn" data-bs-toggle="modal" data-bs-target="#addFilesModal">
                                        <i class="fas fa-plus"></i>
                                        Add Files
                                    </button>
                                @endif
                            </div>

                            @if($folder->files->count() > 0)
                                <div class="files-list">
                                    @foreach($folder->files as $file)
                                        @php
                                            $extension = pathinfo($file->document_file, PATHINFO_EXTENSION);
                                            $isPdf = strtolower($extension) === 'pdf';
                                            $isDoc = in_array(strtolower($extension), ['doc', 'docx']);
                                        @endphp
                                        <div class="file-card">
                                            <div class="file-icon-wrapper {{ $isPdf ? 'pdf' : ($isDoc ? 'doc' : '') }}">
                                                @if($isPdf)
                                                    <i class="fas fa-file-pdf fa-lg"></i>
                                                @elseif($isDoc)
                                                    <i class="fas fa-file-word fa-lg"></i>
                                                @else
                                                    <i class="fas fa-file fa-lg"></i>
                                                @endif
                                            </div>
                                            
                                            <div class="file-info">
                                                <div class="file-title" title="{{ $file->file_title }}">
                                                    {{ $file->file_title }}
                                                </div>
                                                <div class="file-meta">
                                                    <span><i class="fas fa-hashtag"></i> {{ $file->document_id }}</span>
                                                    <span><i class="fas fa-calendar"></i> {{ $file->year_created }}</span>
                                                    <span><i class="fas fa-user"></i> {{ $file->depositor_name }}</span>
                                                </div>
                                            </div>
                                            
                                            <div class="file-actions">
                                                @if($isPdf)
                                                    <a href="{{ asset($file->document_file) }}" target="_blank" class="file-action-btn view" title="View PDF">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @endif
                                                <a href="{{ asset($file->document_file) }}" download class="file-action-btn download" title="Download File">
                                                    <i class="fas fa-download"></i>
                                                </a>
                                                <form action="{{ route('dashboard.folders.remove-file', [$folder, $file]) }}" method="POST" style="display: inline;" onsubmit="return confirm('Remove this file from the folder?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="file-action-btn remove" title="Remove from Folder">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-files">
                                    <i class="fas fa-folder-open"></i>
                                    <h4>No Files in Folder</h4>
                                    <p>This folder is empty. Add some approved files to organize them here.</p>
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Files Modal -->
@if($availableFiles->count() > 0)
<div class="modal fade add-files-modal" id="addFilesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Files to {{ $folder->name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('dashboard.folders.add-files', $folder) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Select approved files to add to this folder:</p>
                    <div class="available-files-grid">
                        @foreach($availableFiles as $file)
                            @php
                                $extension = pathinfo($file->document_file, PATHINFO_EXTENSION);
                                $isPdf = strtolower($extension) === 'pdf';
                                $isDoc = in_array(strtolower($extension), ['doc', 'docx']);
                            @endphp
                            <div class="available-file-card" onclick="toggleFileSelection(this, {{ $file->id }})">
                                <div class="available-file-checkbox">
                                    <input type="checkbox" name="file_ids[]" value="{{ $file->id }}" id="file_{{ $file->id }}">
                                </div>
                                <div class="file-icon-wrapper {{ $isPdf ? 'pdf' : ($isDoc ? 'doc' : '') }}" style="width: 30px; height: 30px; margin: 0;">
                                    @if($isPdf)
                                        <i class="fas fa-file-pdf"></i>
                                    @elseif($isDoc)
                                        <i class="fas fa-file-word"></i>
                                    @else
                                        <i class="fas fa-file"></i>
                                    @endif
                                </div>
                                <div class="file-title" style="font-size: 0.9rem; margin-top: 0.5rem;" title="{{ $file->file_title }}">
                                    {{ Str::limit($file->file_title, 30) }}
                                </div>
                                <div style="font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                                    {{ $file->document_id }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="addFilesSubmitBtn" disabled>
                        <i class="fas fa-plus"></i>
                        Add Selected Files
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

<script>
function toggleFileSelection(card, fileId) {
    const checkbox = card.querySelector('input[type="checkbox"]');
    checkbox.checked = !checkbox.checked;
    card.classList.toggle('selected', checkbox.checked);
    updateAddButton();
}

function updateAddButton() {
    const selectedFiles = document.querySelectorAll('input[name="file_ids[]"]:checked');
    const submitBtn = document.getElementById('addFilesSubmitBtn');
    
    if (selectedFiles.length > 0) {
        submitBtn.disabled = false;
        submitBtn.innerHTML = `<i class="fas fa-plus"></i> Add ${selectedFiles.length} File(s)`;
    } else {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-plus"></i> Add Selected Files';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[name="file_ids[]"]');
    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updateAddButton);
    });
});
</script>
@endsection