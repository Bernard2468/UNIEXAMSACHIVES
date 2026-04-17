@extends('layout.app')

@push('styles')
<style>
    .modern-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .table-container {
        overflow-x: auto;
        overflow-y: visible;
    }

    .documents-table {
        width: 100%;
        border-collapse: collapse;
    }

    .documents-table thead {
        background: #f9fafb;
    }

    .documents-table th {
        padding: 1rem 1.5rem;
        text-align: left;
        font-weight: 600;
        color: #374151;
        font-size: 0.875rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e5e7eb;
    }

    .documents-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid #f3f4f6;
        color: #1f2937;
    }

    .documents-table tbody tr {
        background: white;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }

    .documents-table tbody tr:hover {
        background: #f0f9ff !important;
        box-shadow: -4px 0 0 0 #3b82f6;
    }

    .documents-table tbody tr:active {
        box-shadow: -2px 0 0 0 #2563eb;
        background: #e0f2fe !important;
    }

    .document-title-with-icon {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
    }

    .document-title-with-icon::after {
        content: '→';
        opacity: 0;
        transform: translateX(-5px);
        transition: all 0.2s ease;
        color: #3b82f6;
        font-weight: 600;
    }

    .documents-table tbody tr:hover .document-title-with-icon::after {
        opacity: 1;
        transform: translateX(0);
    }

    .file-type-badge {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.625rem;
        border-radius: 0.25rem;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
    }

    .file-type-badge.pdf {
        background-color: #dbeafe;
        color: #1e40af;
    }

    .file-type-badge.doc,
    .file-type-badge.docx {
        background-color: #ddd6fe;
        color: #5b21b6;
    }

    .file-type-badge.zip {
        background-color: #fef3c7;
        color: #92400e;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }

    .btn-action {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 0.375rem;
        font-weight: 600;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.375rem;
    }

    .btn-preview {
        background-color: #e0f2fe;
        color: #1f2937;
        border: 1px solid #bae6fd;
    }

    .btn-preview:hover {
        background-color: #bae6fd;
        border-color: #7dd3fc;
    }

    .btn-preview i {
        color: #1f2937;
    }

    .btn-download {
        background-color: #f3f4f6;
        color: #1f2937;
        border: 1px solid #e5e7eb;
    }

    .btn-download:hover {
        background-color: #e5e7eb;
        border-color: #d1d5db;
    }

    .btn-download i {
        color: #1f2937;
    }

    /* Pagination Styles */
    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        flex-wrap: wrap;
    }

    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
        white-space: nowrap;
    }

    .pagination-info strong {
        color: #1f2937;
        font-weight: 600;
    }

    .pagination-controls {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination {
        display: flex;
        list-style: none;
        margin: 0;
        padding: 0;
        gap: 0.25rem;
    }

    .pagination-item {
        display: inline-block;
    }

    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.375rem;
        background: white;
        color: #374151;
        text-decoration: none;
        font-weight: 500;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .pagination-link:hover:not(.disabled):not(.active) {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #1f2937;
    }

    .pagination-link.active {
        background: #3b82f6;
        color: white;
        border-color: #3b82f6;
        font-weight: 600;
    }

    .pagination-link.disabled {
        color: #9ca3af;
        cursor: not-allowed;
        background: #f9fafb;
        opacity: 0.5;
    }

    .pagination-link.icon {
        width: 2.5rem;
        padding: 0;
    }

    .pagination-ellipsis {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    .page-size-selector {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .page-size-label {
        font-size: 0.875rem;
        color: #6b7280;
        white-space: nowrap;
    }

    .page-size-select {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        background: white;
        color: #374151;
        font-size: 0.875rem;
        cursor: pointer;
        transition: all 0.2s;
    }

    .page-size-select:hover {
        border-color: #9ca3af;
    }

    .page-size-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    /* Document Detail Modal */
    .document-modal {
        display: none;
        position: fixed;
        z-index: 10000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.4);
        backdrop-filter: blur(2px);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .document-modal.show {
        display: flex;
    }

    .document-modal-content {
        background: white;
        border-radius: 0.75rem;
        max-width: 600px;
        width: 100%;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        border: 1px solid #e5e7eb;
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: translateY(-20px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    .document-modal-header {
        padding: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
        display: flex;
        justify-content: space-between;
        align-items: center;
        background: #f9fafb;
        border-radius: 0.75rem 0.75rem 0 0;
    }

    .document-modal-title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .document-modal-close {
        background: none;
        border: none;
        font-size: 1.5rem;
        color: #6b7280;
        cursor: pointer;
        padding: 0.25rem;
        line-height: 1;
        transition: color 0.2s;
        width: 2rem;
        height: 2rem;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
    }

    .document-modal-close:hover {
        color: #1f2937;
        background: #f3f4f6;
    }

    .document-modal-body {
        padding: 1.5rem;
    }

    .document-modal-label {
        font-size: 0.875rem;
        font-weight: 600;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        margin-bottom: 0.5rem;
        display: block;
    }

    .document-modal-name {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #f3f4f6;
    }

    .document-modal-description {
        font-size: 0.9375rem;
        color: #374151;
        line-height: 1.6;
        white-space: pre-wrap;
        word-wrap: break-word;
    }

    .document-modal-info {
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f3f4f6;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    .document-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 0.875rem;
    }

    .document-info-label {
        color: #6b7280;
        font-weight: 500;
    }

    .document-info-value {
        color: #1f2937;
        font-weight: 600;
    }

    /* Search and Filter Styles */
    .table-header-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .table-title-section {
        flex: 1;
    }

    .search-filter-container {
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .search-wrapper {
        position: relative;
        display: flex;
        align-items: center;
    }

    .search-input {
        padding: 0.625rem 2.5rem 0.625rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        width: 220px;
        transition: all 0.2s;
        background: white;
        color: #374151;
    }

    .search-input:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .search-input::placeholder {
        color: #9ca3af;
    }

    .search-icon {
        position: absolute;
        right: 0.75rem;
        color: #6b7280;
        pointer-events: none;
    }

    .filter-select {
        padding: 0.625rem 2rem 0.625rem 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background: white;
        color: #374151;
        cursor: pointer;
        transition: all 0.2s;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%236b7280' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 0.75rem center;
        background-size: 12px;
        padding-right: 2.5rem;
    }

    .filter-select:hover {
        border-color: #9ca3af;
    }

    .filter-select:focus {
        outline: none;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    @media (max-width: 768px) {
        .table-header-controls {
            flex-direction: column;
            align-items: stretch;
        }

        .search-filter-container {
            width: 100%;
        }

        .search-input {
            width: 100%;
        }

        .filter-select {
            flex: 1;
        }

        .pagination-wrapper {
            flex-direction: column;
            align-items: stretch;
        }

        .pagination-controls {
            justify-content: center;
        }

        .page-size-selector {
            justify-content: center;
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
                    <div class="dashboard__content__wraper">
                        <div class="table-header-controls">
                            <div class="table-title-section">
                                <div class="dashboard__section__title">
                                    <h4>System Documentation</h4>
                                </div>
                            </div>
                            <div class="search-filter-container">
                                <div class="search-wrapper">
                                    <input type="text" class="search-input" id="documentSearchInput" placeholder="Search documents...">
                                    <i class="icofont-search-1 search-icon"></i>
                                </div>
                                <select class="filter-select" id="documentTypeFilter">
                                    <option value="all">All Types</option>
                                    <option value="pdf">PDF Only</option>
                                    <option value="doc">Word Only</option>
                                    <option value="zip">ZIP Only</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-xl-12">
                                <div class="modern-card">
                                    <div class="table-container">
                                        <table class="documents-table" id="documentsTable">
                                            <thead>
                                                <tr>
                                                    <th style="width: 60px;">#</th>
                                                    <th>Document Title</th>
                                                    <th>About</th>
                                                    <th style="width: 200px;">Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @if ($documents->count() > 0)
                                                    @foreach ($documents as $document)
                                                    <tr data-file-type="{{ strtolower($document->file_type) }}" onclick="openDocumentModal('{{ addslashes($document->title) }}', '{{ addslashes($document->description) }}', '{{ $document->file_type }}', '{{ $document->file_size }}', '{{ $document->creator->first_name ?? '' }} {{ $document->creator->last_name ?? '' }}')">
                                                        <td>{{ $documents->firstItem() + $loop->index }}</td>
                                                        <td>
                                                            <div class="document-title-with-icon">
                                                                <strong>{{ $document->title }}</strong>
                                                            </div>
                                                        </td>
                                                        <td>{{ Str::limit($document->description, 80) }}</td>
                                                        <td onclick="event.stopPropagation()">
                                                            <div class="action-buttons">
                                                                @if($document->isPdf() && !$document->isZip())
                                                                <a href="{{ route('dashboard.system-documentation.preview', $document->id) }}" target="_blank" class="btn-action btn-preview">
                                                                    <i class="icofont-eye-alt"></i>
                                                                    Preview
                                                                </a>
                                                                @endif
                                                                <a href="{{ route('dashboard.system-documentation.download', $document->id) }}" class="btn-action btn-download">
                                                                    <i class="icofont-download"></i>
                                                                    Download
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @else
                                                    <tr>
                                                        <td colspan="4" class="text-center" style="padding: 2rem; color: #6b7280;">No documents found.</td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    
                                    @if($documents->hasPages())
                                    <div class="pagination-wrapper">
                                        <div class="pagination-info">
                                            Showing <strong>{{ $documents->firstItem() }}</strong> to <strong>{{ $documents->lastItem() }}</strong> of <strong>{{ $documents->total() }}</strong> results
                                        </div>
                                        
                                        <div class="pagination-controls">
                                            <ul class="pagination">
                                                {{-- Previous Page Link --}}
                                                @if ($documents->onFirstPage())
                                                    <li class="pagination-item">
                                                        <span class="pagination-link icon disabled">
                                                            <i class="icofont-arrow-left"></i>
                                                        </span>
                                                    </li>
                                                @else
                                                    <li class="pagination-item">
                                                        <a href="{{ $documents->previousPageUrl() }}" class="pagination-link icon">
                                                            <i class="icofont-arrow-left"></i>
                                                        </a>
                                                    </li>
                                                @endif

                                                {{-- Pagination Elements --}}
                                                @php
                                                    $currentPage = $documents->currentPage();
                                                    $lastPage = $documents->lastPage();
                                                    $startPage = max(1, $currentPage - 2);
                                                    $endPage = min($lastPage, $currentPage + 2);
                                                @endphp

                                                @if($startPage > 1)
                                                    <li class="pagination-item">
                                                        <a href="{{ $documents->url(1) }}" class="pagination-link">1</a>
                                                    </li>
                                                    @if($startPage > 2)
                                                        <li class="pagination-item">
                                                            <span class="pagination-ellipsis">...</span>
                                                        </li>
                                                    @endif
                                                @endif

                                                @for ($i = $startPage; $i <= $endPage; $i++)
                                                    <li class="pagination-item">
                                                        @if ($i == $currentPage)
                                                            <span class="pagination-link active">{{ $i }}</span>
                                                        @else
                                                            <a href="{{ $documents->url($i) }}" class="pagination-link">{{ $i }}</a>
                                                        @endif
                                                    </li>
                                                @endfor

                                                @if($endPage < $lastPage)
                                                    @if($endPage < $lastPage - 1)
                                                        <li class="pagination-item">
                                                            <span class="pagination-ellipsis">...</span>
                                                        </li>
                                                    @endif
                                                    <li class="pagination-item">
                                                        <a href="{{ $documents->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a>
                                                    </li>
                                                @endif

                                                {{-- Next Page Link --}}
                                                @if ($documents->hasMorePages())
                                                    <li class="pagination-item">
                                                        <a href="{{ $documents->nextPageUrl() }}" class="pagination-link icon">
                                                            <i class="icofont-arrow-right"></i>
                                                        </a>
                                                    </li>
                                                @else
                                                    <li class="pagination-item">
                                                        <span class="pagination-link icon disabled">
                                                            <i class="icofont-arrow-right"></i>
                                                        </span>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>

                                        <div class="page-size-selector">
                                            <span class="page-size-label">Per page:</span>
                                            <select class="page-size-select" onchange="changePageSize(this.value)">
                                                <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5</option>
                                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                                                <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                                                <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                                            </select>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Document Detail Modal -->
<div id="documentDetailModal" class="document-modal">
    <div class="document-modal-content">
        <div class="document-modal-header">
            <h3 class="document-modal-title">Document Details</h3>
            <button class="document-modal-close" onclick="closeDocumentModal()">&times;</button>
        </div>
        <div class="document-modal-body">
            <span class="document-modal-label">Document Title</span>
            <div class="document-modal-name" id="modalDocumentTitle"></div>
            <span class="document-modal-label">Description</span>
            <div class="document-modal-description" id="modalDocumentDescription"></div>
            <div class="document-modal-info">
                <div class="document-info-row">
                    <span class="document-info-label">File Type:</span>
                    <span class="document-info-value" id="modalFileType"></span>
                </div>
                <div class="document-info-row">
                    <span class="document-info-label">File Size:</span>
                    <span class="document-info-value" id="modalFileSize"></span>
                </div>
                <div class="document-info-row">
                    <span class="document-info-label">Uploaded By:</span>
                    <span class="document-info-value" id="modalUploadedBy"></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Change page size
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Open document detail modal
function openDocumentModal(title, description, fileType, fileSize, uploadedBy) {
    document.getElementById('modalDocumentTitle').textContent = title;
    document.getElementById('modalDocumentDescription').textContent = description;
    document.getElementById('modalFileType').textContent = fileType.toUpperCase();
    document.getElementById('modalFileSize').textContent = fileSize;
    document.getElementById('modalUploadedBy').textContent = uploadedBy;
    document.getElementById('documentDetailModal').classList.add('show');
    document.body.style.overflow = 'hidden';
}

// Close document detail modal
function closeDocumentModal() {
    document.getElementById('documentDetailModal').classList.remove('show');
    document.body.style.overflow = '';
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('documentDetailModal');
    if (event.target == modal) {
        closeDocumentModal();
    }
}

// Close modal with Escape key
document.addEventListener('keydown', function(event) {
    if (event.key === 'Escape') {
        closeDocumentModal();
    }
});

// Search and Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('documentSearchInput');
    const typeFilter = document.getElementById('documentTypeFilter');
    const tableRows = document.querySelectorAll('#documentsTable tbody tr');

    function performSearchAndFilter() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        const typeValue = typeFilter.value;

        tableRows.forEach(row => {
            // Skip the "no documents" row
            if (row.cells.length < 4) {
                return;
            }

            const documentTitle = row.cells[1].textContent.toLowerCase();
            const description = row.cells[2].textContent.toLowerCase();
            const fileType = row.getAttribute('data-file-type') || '';

            // Search filter
            const matchesSearch = searchTerm === '' || 
                                 documentTitle.includes(searchTerm) || 
                                 description.includes(searchTerm);

            // Type filter
            let matchesType = true;
            if (typeValue !== 'all') {
                if (typeValue === 'doc') {
                    matchesType = fileType === 'doc' || fileType === 'docx';
                } else {
                    matchesType = fileType === typeValue;
                }
            }

            // Show/hide row
            if (matchesSearch && matchesType) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Search on input
    searchInput.addEventListener('input', performSearchAndFilter);

    // Filter on change
    typeFilter.addEventListener('change', performSearchAndFilter);
});
</script>
@endsection
