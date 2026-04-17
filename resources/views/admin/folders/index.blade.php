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
    
    .folders-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .folders-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="folders-grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(100,116,139,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23folders-grid)" /></svg>');
        opacity: 0.7;
    }

    .folders-hero-content {
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

    .create-folder-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s ease;
        border: none;
        cursor: pointer;
    }

    .create-folder-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
        color: white;
        text-decoration: none;
    }

    .folders-section {
        background: #f9fafb;
        padding: 3rem 0;
    }

    .folders-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin-top: 2rem;
    }

    .folder-card {
        background: white;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        border: 1px solid #f3f4f6;
        position: relative;
        overflow: hidden;
    }

    .folder-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 4px;
        background: var(--folder-color, #64748b);
        border-radius: 0 0 0 16px;
    }

    .folder-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .folder-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .folder-icon {
        width: 50px;
        height: 50px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: var(--folder-color, #64748b);
        color: white;
        font-size: 1.5rem;
    }

    .folder-info h3 {
        margin: 0 0 0.25rem 0;
        font-size: 1.25rem;
        font-weight: 600;
        color: #111827;
    }

    .folder-info .file-count {
        font-size: 0.85rem;
        color: #6b7280;
    }

    .folder-description {
        color: #6b7280;
        font-size: 0.9rem;
        line-height: 1.5;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .folder-actions {
        display: flex;
        gap: 0.5rem;
        justify-content: flex-end;
    }

    .action-btn {
        padding: 8px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.85rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        cursor: pointer;
    }

    .action-btn:hover {
        text-decoration: none;
        transform: translateY(-1px);
    }

    .action-btn.view {
        border-color: #3b82f6;
        color: #3b82f6;
    }

    .action-btn.view:hover {
        background: #3b82f6;
        color: white;
    }

    .action-btn.edit {
        border-color: #f59e0b;
        color: #f59e0b;
    }

    .action-btn.edit:hover {
        background: #f59e0b;
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

    .no-folders {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .no-folders i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #64748b;
        opacity: 0.6;
    }

    @media (max-width: 768px) {
        .folders-grid {
            grid-template-columns: 1fr;
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
                @include('components.sidebar')
                <div class="col-xl-9 col-lg-9 col-md-12">
                    <!-- Hero Section -->
                    <div class="folders-hero">
                        <div class="container">
                            <div class="folders-hero-content">
                                <h1 class="hero-title">My Folders</h1>
                                <p class="hero-subtitle">organize your approved files into custom folders</p>
                                <a href="{{ route('dashboard.folders.create') }}" class="create-folder-btn">
                                    <i class="fas fa-plus"></i>
                                    Create New Folder
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Folders Section -->
                    <div class="folders-section">
                        <div class="container">
                            @if(session('success'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('success') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            @endif

                            @if (count($folders) > 0)
                                <div class="folders-grid">
                                    @foreach ($folders as $folder)
                                        <div class="folder-card" style="--folder-color: {{ $folder->color }};">
                                            <div class="folder-header">
                                                <div class="folder-icon">
                                                    <i class="fas fa-folder"></i>
                                                </div>
                                                <div class="folder-info">
                                                    <h3>
                                                        <a href="{{ route('dashboard.folders.show', $folder) }}" style="text-decoration:none;color:inherit;">
                                                            {{ $folder->name }}
                                                        </a>
                                                    </h3>
                                                    <div class="file-count">{{ $folder->files_count }} file(s)</div>
                                                </div>
                                            </div>
                                            
                                            @if($folder->description)
                                                <div class="folder-description">
                                                    {{ $folder->description }}
                                                </div>
                                            @endif
                                            
                                            <div class="folder-actions">
                                                <a href="{{ route('dashboard.folders.edit', $folder) }}" class="action-btn edit" title="Edit Folder">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('dashboard.folders.destroy', $folder) }}" method="post" style="display: inline;" id="delete-folder-form-{{ $folder->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn delete" title="Delete Folder" onclick="confirmDeleteFolder({{ $folder->id }}, '{{ addslashes($folder->name) }}')">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="no-folders">
                                    <i class="fas fa-folder-open"></i>
                                    <h4>No Folders Created Yet</h4>
                                    <p>Start organizing your files by creating your first folder.</p>
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
@push('scripts')
<script>
function confirmDeleteFolder(folderId, folderName) {
    confirmDelete(
        `Are you sure you want to delete the folder "${folderName}"?`,
        function() {
            const form = document.getElementById('delete-folder-form-' + folderId);
            if (form) form.submit();
        }
    );
}
</script>
@endpush