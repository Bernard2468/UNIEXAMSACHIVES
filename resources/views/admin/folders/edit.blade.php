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
    
    .edit-folder-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .edit-folder-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="edit-folder-grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(100,116,139,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23edit-folder-grid)" /></svg>');
        opacity: 0.7;
    }

    .edit-folder-hero-content {
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

    .form-section {
        background: #f9fafb;
        padding: 3rem 0;
    }

    .form-container {
        max-width: 600px;
        margin: 0 auto;
        background: white;
        border-radius: 16px;
        padding: 2rem;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .form-group {
        margin-bottom: 1.5rem;
    }

    .form-label {
        display: block;
        font-weight: 600;
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }

    .form-control {
        width: 100%;
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        font-size: 1rem;
        background: #f9fafb;
        transition: all 0.3s ease;
    }

    .form-control:focus {
        outline: none;
        border-color: #64748b;
        background: white;
        box-shadow: 0 0 0 3px rgba(100, 116, 139, 0.1);
    }

    .color-picker-container {
        display: flex;
        gap: 1rem;
        align-items: center;
        flex-wrap: wrap;
    }

    .color-input {
        width: 60px;
        height: 40px;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        cursor: pointer;
        padding: 0;
    }

    .color-presets {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }

    .color-preset {
        width: 32px;
        height: 32px;
        border-radius: 6px;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.3s ease;
    }

    .color-preset:hover,
    .color-preset.active {
        border-color: #64748b;
        transform: scale(1.1);
    }

    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #e5e7eb;
    }

    .btn {
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        font-size: 0.95rem;
        border: 2px solid;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .btn-primary {
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
        color: white;
        border-color: #64748b;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(100, 116, 139, 0.3);
        color: white;
    }

    .btn-secondary {
        background: white;
        color: #6b7280;
        border-color: #e5e7eb;
    }

    .btn-secondary:hover {
        background: #f9fafb;
        color: #374151;
        text-decoration: none;
    }

    .folder-preview {
        background: #f9fafb;
        border: 2px solid #e5e7eb;
        border-radius: 8px;
        padding: 1rem;
        margin-top: 1rem;
    }

    .folder-preview-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .preview-icon {
        width: 40px;
        height: 40px;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.2rem;
    }

    .preview-info h4 {
        margin: 0 0 0.25rem 0;
        font-size: 1rem;
        font-weight: 600;
        color: #111827;
    }

    .preview-info p {
        margin: 0;
        font-size: 0.85rem;
        color: #6b7280;
    }

    @media (max-width: 768px) {
        .form-container {
            margin: 0 1rem;
            padding: 1.5rem;
        }
        
        .hero-title {
            font-size: 2rem;
        }
        
        .form-actions {
            flex-direction: column;
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
                    <div class="edit-folder-hero">
                        <div class="container">
                            <div class="edit-folder-hero-content">
                                <h1 class="hero-title">Edit Folder</h1>
                                <p class="hero-subtitle">Update your folder settings and appearance</p>
                            </div>
                        </div>
                    </div>

                    <!-- Form Section -->
                    <div class="form-section">
                        <div class="form-container">
                            <form action="{{ route('dashboard.folders.update', $folder) }}" method="POST" id="editFolderForm">
                                @csrf
                                @method('PUT')
                                
                                <div class="form-group">
                                    <label for="name" class="form-label">
                                        <i class="fas fa-folder me-2"></i>Folder Name *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('name') is-invalid @enderror" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $folder->name) }}" 
                                           placeholder="Enter folder name"
                                           required>
                                    @error('name')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                

                                <div class="form-group">
                                    <label for="description" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Description
                                    </label>
                                    <textarea class="form-control @error('description') is-invalid @enderror" 
                                              id="description" 
                                              name="description" 
                                              rows="3" 
                                              placeholder="Optional description for this folder">{{ old('description', $folder->description) }}</textarea>
                                    @error('description')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="color" class="form-label">
                                        <i class="fas fa-palette me-2"></i>Folder Color *
                                    </label>
                                    <div class="color-picker-container">
                                        <input type="color" 
                                               class="color-input @error('color') is-invalid @enderror" 
                                               id="color" 
                                               name="color" 
                                               value="{{ old('color', $folder->color) }}">
                                        
                                        <div class="color-presets">
                                            <div class="color-preset {{ $folder->color == '#64748b' ? 'active' : '' }}" style="background: #64748b;" data-color="#64748b" title="Default Gray"></div>
                                            <div class="color-preset {{ $folder->color == '#3b82f6' ? 'active' : '' }}" style="background: #3b82f6;" data-color="#3b82f6" title="Blue"></div>
                                            <div class="color-preset {{ $folder->color == '#10b981' ? 'active' : '' }}" style="background: #10b981;" data-color="#10b981" title="Green"></div>
                                            <div class="color-preset {{ $folder->color == '#f59e0b' ? 'active' : '' }}" style="background: #f59e0b;" data-color="#f59e0b" title="Orange"></div>
                                            <div class="color-preset {{ $folder->color == '#ef4444' ? 'active' : '' }}" style="background: #ef4444;" data-color="#ef4444" title="Red"></div>
                                            <div class="color-preset {{ $folder->color == '#8b5cf6' ? 'active' : '' }}" style="background: #8b5cf6;" data-color="#8b5cf6" title="Purple"></div>
                                            <div class="color-preset {{ $folder->color == '#06b6d4' ? 'active' : '' }}" style="background: #06b6d4;" data-color="#06b6d4" title="Cyan"></div>
                                            <div class="color-preset {{ $folder->color == '#84cc16' ? 'active' : '' }}" style="background: #84cc16;" data-color="#84cc16" title="Lime"></div>
                                        </div>
                                    </div>
                                    @error('color')
                                        <div class="text-danger mt-1">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Live Preview -->
                                <div class="folder-preview">
                                    <h5 style="margin-bottom: 1rem; color: #374151;">Preview:</h5>
                                    <div class="folder-preview-content">
                                        <div class="preview-icon" id="previewIcon" style="background: {{ $folder->color }};">
                                            <i class="fas fa-folder"></i>
                                        </div>
                                        <div class="preview-info">
                                            <h4 id="previewName">{{ $folder->name }}</h4>
                                            <p id="previewDescription">{{ $folder->description ?: 'No description' }}</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-actions">
                                    <a href="{{ route('dashboard.folders.show', $folder) }}" class="btn btn-secondary">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i>
                                        Update Folder
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const nameInput = document.getElementById('name');
    const descriptionInput = document.getElementById('description');
    const colorInput = document.getElementById('color');
    const colorPresets = document.querySelectorAll('.color-preset');
    
    const previewIcon = document.getElementById('previewIcon');
    const previewName = document.getElementById('previewName');
    const previewDescription = document.getElementById('previewDescription');

    function updatePreview() {
        const name = nameInput.value.trim() || 'Folder Name';
        const description = descriptionInput.value.trim() || 'No description';
        const color = colorInput.value;

        previewName.textContent = name;
        previewDescription.textContent = description;
        previewIcon.style.background = color;
    }

    nameInput.addEventListener('input', updatePreview);
    descriptionInput.addEventListener('input', updatePreview);
    colorInput.addEventListener('input', function() {
        updatePreview();
        colorPresets.forEach(preset => {
            preset.classList.toggle('active', preset.dataset.color === this.value);
        });
    });

    colorPresets.forEach(preset => {
        preset.addEventListener('click', function() {
            const color = this.dataset.color;
            colorInput.value = color;
            colorPresets.forEach(p => p.classList.remove('active'));
            this.classList.add('active');
            updatePreview();
        });
    });

    updatePreview();
});
</script>
@endsection