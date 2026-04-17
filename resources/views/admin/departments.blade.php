@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding" style="display:none">
        <div class="row">@include('components.create_section')</div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="dp-wrap">

                        {{-- Page header --}}
                        <div class="dp-page-header">
                            <div>
                                <h1 class="dp-page-title">Department / Faculty<span class="dp-title-bar"></span></h1>
                                <p class="dp-page-sub">Manage all departments, faculties, and units registered in the system.</p>
                            </div>
                            <button class="dp-btn-primary" id="triggerDepartmentModal">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                New department
                            </button>
                        </div>

                        {{-- Alerts --}}
                        @if(session('success'))
                        <div class="dp-alert dp-alert--ok">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ session('success') }}</span>
                            <button class="dp-alert__x" onclick="this.closest('.dp-alert').remove()"><svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg></button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="dp-alert dp-alert--err">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                            <button class="dp-alert__x" onclick="this.closest('.dp-alert').remove()"><svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg></button>
                        </div>
                        @endif

                        {{-- Table card --}}
                        <div class="dp-card">
                            <div class="dp-card__hd">
                                <div>
                                    <h2 class="dp-card__title">All departments<span class="dp-card__bar"></span></h2>
                                    <p class="dp-card__count">{{ $departments->total() }} {{ $departments->total() === 1 ? 'entry' : 'entries' }} total</p>
                                </div>
                            </div>

                            @if($departments->count() > 0)
                            <div class="dp-table-shell">
                                <table class="dp-table">
                                    <thead>
                                        <tr>
                                            <th class="dp-th dp-th--id">#</th>
                                            <th class="dp-th">Name</th>
                                            <th class="dp-th dp-th--actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($departments as $dept)
                                        <tr class="dp-tr">
                                            <td class="dp-td dp-td--id">{{ $dept->id }}</td>
                                            <td class="dp-td dp-td--name">{{ $dept->name }}</td>
                                            <td class="dp-td dp-td--actions">
                                                <button type="button" class="dp-action dp-action--edit dp-edit-btn"
                                                    data-name="{{ $dept->name }}"
                                                    data-route="{{ route('departments.update', $dept->id) }}">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    Edit
                                                </button>
                                                <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" style="display:inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="dp-action dp-action--del" onclick="return confirm('Delete this department? This cannot be undone.')">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($departments->hasPages())
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Showing <strong>{{ $departments->firstItem() }}</strong> to <strong>{{ $departments->lastItem() }}</strong> of <strong>{{ $departments->total() }}</strong> results
                    </div>
                    <div class="pagination-controls">
                        <ul class="pagination">
                            @if ($departments->onFirstPage())
                                <li class="pagination-item"><span class="pagination-link icon disabled"><i class="icofont-arrow-left"></i></span></li>
                            @else
                                <li class="pagination-item"><a href="{{ $departments->previousPageUrl() }}" class="pagination-link icon"><i class="icofont-arrow-left"></i></a></li>
                            @endif

                            @php
                                $currentPage = $departments->currentPage();
                                $lastPage    = $departments->lastPage();
                                $startPage   = max(1, $currentPage - 2);
                                $endPage     = min($lastPage, $currentPage + 2);
                            @endphp

                            @if($startPage > 1)
                                <li class="pagination-item"><a href="{{ $departments->url(1) }}" class="pagination-link">1</a></li>
                                @if($startPage > 2)<li class="pagination-item"><span class="pagination-ellipsis">...</span></li>@endif
                            @endif

                            @for ($i = $startPage; $i <= $endPage; $i++)
                                <li class="pagination-item">
                                    @if ($i == $currentPage)
                                        <span class="pagination-link active">{{ $i }}</span>
                                    @else
                                        <a href="{{ $departments->url($i) }}" class="pagination-link">{{ $i }}</a>
                                    @endif
                                </li>
                            @endfor

                            @if($endPage < $lastPage)
                                @if($endPage < $lastPage - 1)<li class="pagination-item"><span class="pagination-ellipsis">...</span></li>@endif
                                <li class="pagination-item"><a href="{{ $departments->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a></li>
                            @endif

                            @if ($departments->hasMorePages())
                                <li class="pagination-item"><a href="{{ $departments->nextPageUrl() }}" class="pagination-link icon"><i class="icofont-arrow-right"></i></a></li>
                            @else
                                <li class="pagination-item"><span class="pagination-link icon disabled"><i class="icofont-arrow-right"></i></span></li>
                            @endif
                        </ul>
                    </div>
                    <div class="page-size-selector">
                        <span class="page-size-label">Per page:</span>
                        <select class="page-size-select" onchange="changePageSize(this.value)">
                            <option value="5"  {{ request('per_page', 10) == 5  ? 'selected' : '' }}>5</option>
                            <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ request('per_page', 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page', 10) == 50 ? 'selected' : '' }}>50</option>
                        </select>
                    </div>
                </div>
                @endif
            </div>
                            @else
                            <div class="dp-empty">
                                <div class="dp-empty__icon">
                                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                </div>
                                <p class="dp-empty__text">No departments yet. Create your first one.</p>
                                <button class="dp-btn-primary" id="triggerDepartmentModalEmpty">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Create first department
                                </button>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Department Modal ── --}}
<div class="modal fade" id="dpEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="dm-modal">
            <div class="dm-modal__hd">
                <div>
                    <h5 class="dm-modal__title">Edit department</h5>
                    <p class="dm-modal__sub">Update the name of this department, faculty, or unit.</p>
                </div>
                <button type="button" class="dm-modal__close" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                </button>
            </div>
            <div class="dm-modal__body">
                <form id="dpEditForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="dm-modal__field">
                        <label class="dm-modal__label">Department / Faculty / Unit name</label>
                        <input class="dm-modal__input" type="text" id="dpEditName" name="name" placeholder="e.g. Faculty of Engineering" required autofocus>
                    </div>
                    <div class="dm-modal__foot">
                        <button type="button" class="dm-modal__btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="dm-modal__btn-save">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Save changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.dp-wrap, .dp-wrap * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.dp-wrap { max-width: 900px; padding: 4px 0 60px; }

/* ── Page header ── */
.dp-page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb;
}
.dp-page-title {
    font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em;
    line-height: 1.1; margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.dp-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.dp-page-sub { margin: 12px 0 0; font-size: 0.9rem; color: #8a8fa0; font-weight: 400; }

/* ── Primary button ── */
.dp-btn-primary {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
    background: #0c0c0c; color: #fff; border: none; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap;
    flex-shrink: 0; margin-top: 14px; transition: background .15s, transform .12s, box-shadow .15s;
    font-family: 'Outfit', sans-serif !important; text-decoration: none;
}
.dp-btn-primary:hover { background: #1f2937; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* ── Alerts ── */
.dp-alert {
    display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px;
    border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; font-weight: 500;
    border: 1.5px solid transparent;
}
.dp-alert--ok  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.dp-alert--err { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
.dp-alert__x { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .45; color: inherit; padding: 0; display: flex; align-items: center; }
.dp-alert__x:hover { opacity: 1; }

/* ── Card ── */
.dp-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.dp-card__hd { padding: 18px 24px 14px; border-bottom: 1.5px solid #f5f5f5; }
.dp-card__title {
    font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em;
    margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.dp-card__bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.dp-card__count { margin: 8px 0 0; font-size: 0.8rem; color: #b0b5c0; }

/* ── Table ── */
.dp-table-shell { overflow-x: auto; }
.dp-table { width: 100%; border-collapse: collapse; }
.dp-th {
    padding: 10px 20px; text-align: left; font-size: 0.72rem; font-weight: 700;
    color: #b0b5c0; letter-spacing: .07em; text-transform: uppercase;
    background: #fafafa; border-bottom: 1.5px solid #f0f0f0;
}
.dp-th--id { width: 60px; }
.dp-th--actions { width: 170px; text-align: right; }
.dp-tr { border-bottom: 1.5px solid #f5f5f5; transition: background .1s; }
.dp-tr:last-child { border-bottom: none; }
.dp-tr:hover { background: #fafafa; }
.dp-td { padding: 13px 20px; font-size: 0.88rem; color: #374151; vertical-align: middle; }
.dp-td--id   { color: #c0c4cf; font-size: 0.8rem; font-weight: 500; }
.dp-td--name { font-weight: 600; color: #111827; }
.dp-td--actions { text-align: right; white-space: nowrap; }

/* ── Action buttons ── */
.dp-action {
    display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px;
    border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid transparent; transition: all .15s; text-decoration: none;
    background: none; font-family: 'Outfit', sans-serif !important; vertical-align: middle;
}
.dp-action--edit { color: #374151; border-color: #e5e7eb; }
.dp-action--edit:hover { background: #f3f4f6; border-color: #d1d5db; color: #111827; text-decoration: none; }
.dp-action--del  { color: #ef4444; border-color: #fee2e2; }
.dp-action--del:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
.dp-action + form { margin-left: 6px; display: inline; }

/* ── Empty ── */
.dp-empty { padding: 52px 24px; text-align: center; }
.dp-empty__icon { display: inline-flex; padding: 18px; background: #f9fafb; border: 1.5px solid #ebebeb; border-radius: 16px; color: #d1d5db; margin-bottom: 16px; }
.dp-empty__text { font-size: 0.9rem; color: #9ca3af; margin-bottom: 20px; }

/* ── Dark mode ── */
.is_dark .dp-page-title  { color: #f3f4f6; }
.is_dark .dp-title-bar   { background: #f3f4f6; }
.is_dark .dp-page-sub    { color: #6b7280; }
.is_dark .dp-page-header { border-color: #1e2330; }
.is_dark .dp-btn-primary { background: #f3f4f6; color: #0c0c0c; }
.is_dark .dp-btn-primary:hover { background: #e5e7eb; color: #0c0c0c; }
.is_dark .dp-card        { background: #111827; border-color: #1e2330; }
.is_dark .dp-card__hd    { border-color: #1e2330; }
.is_dark .dp-card__title { color: #f3f4f6; }
.is_dark .dp-card__bar   { background: #f3f4f6; }
.is_dark .dp-card__count { color: #6b7280; }
.is_dark .dp-th  { background: #0f172a; border-color: #1e2330; color: #6b7280; }
.is_dark .dp-tr  { border-color: #1e2330; }
.is_dark .dp-tr:hover { background: #0f172a; }
.is_dark .dp-td  { color: #d1d5db; }
.is_dark .dp-td--name { color: #f3f4f6; }
.is_dark .dp-action--edit { color: #d1d5db; border-color: #374151; }
.is_dark .dp-action--edit:hover { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
.is_dark .dp-empty__icon { background: #0f172a; border-color: #1e2330; }

/* ── Pagination ── */
.pagination-wrapper {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 20px; border-top: 1.5px solid #f5f5f5;
    gap: 1rem; flex-wrap: wrap;
}
.pagination-info { font-size: 0.875rem; color: #6b7280; white-space: nowrap; }
.pagination-info strong { color: #1f2937; font-weight: 600; }
.pagination-controls { display: flex; align-items: center; gap: 0.5rem; }
.pagination { display: flex; list-style: none; margin: 0; padding: 0; gap: 0.25rem; }
.pagination-item { display: inline-block; }
.pagination-link {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 2rem; height: 2rem; padding: 0 0.5rem;
    border: 1px solid #e5e7eb; border-radius: 0.375rem;
    font-size: 0.875rem; color: #374151; text-decoration: none;
    background: #fff; transition: all 0.2s; font-family: 'Outfit', sans-serif !important;
}
.pagination-link:hover:not(.disabled):not(.active) { background: #f3f4f6; border-color: #d1d5db; color: #1f2937; }
.pagination-link.active { background: #3b82f6; color: #fff; border-color: #3b82f6; font-weight: 600; }
.pagination-link.disabled { color: #9ca3af; cursor: not-allowed; background: #f9fafb; opacity: 0.5; }
.pagination-link.icon { width: 2rem; padding: 0; }
.pagination-ellipsis {
    display: inline-flex; align-items: center; justify-content: center;
    width: 2rem; height: 2rem; font-size: 0.875rem; color: #6b7280;
}
.page-size-selector { display: flex; align-items: center; gap: 0.5rem; }
.page-size-label { font-size: 0.875rem; color: #6b7280; white-space: nowrap; }
.page-size-select {
    padding: 0.4rem 0.65rem; border: 1px solid #d1d5db; border-radius: 0.375rem;
    font-size: 0.875rem; color: #374151; background: #fff; cursor: pointer;
    font-family: 'Outfit', sans-serif !important; transition: all 0.2s;
}
.page-size-select:hover { border-color: #9ca3af; }
.page-size-select:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
@media (max-width: 768px) {
    .pagination-wrapper { flex-direction: column; align-items: stretch; }
    .pagination-controls { justify-content: center; }
    .page-size-selector { justify-content: center; }
}
.is_dark .pagination-wrapper { border-color: #1e2330; }
.is_dark .pagination-info { color: #9ca3af; }
.is_dark .pagination-info strong { color: #f3f4f6; }
.is_dark .pagination-link { background: #111827; border-color: #374151; color: #d1d5db; }
.is_dark .pagination-link:hover:not(.disabled):not(.active) { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
.is_dark .pagination-link.disabled { background: #0f172a; }
.is_dark .page-size-select { background: #111827; border-color: #374151; color: #d1d5db; }
.is_dark .page-size-label { color: #9ca3af; }
</style>

<script>
function changePageSize(size) {
    var url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

document.addEventListener('DOMContentLoaded', function() {
    // ── Create modal triggers ──
    var headerBtn = document.getElementById('triggerDepartmentModal');
    var emptyBtn  = document.getElementById('triggerDepartmentModalEmpty');
    var addModal  = document.getElementById('myDepartmentModal');

    function openAddModal(e) {
        e.preventDefault();
        if (addModal) new bootstrap.Modal(addModal).show();
    }
    if (headerBtn) headerBtn.addEventListener('click', openAddModal);
    if (emptyBtn)  emptyBtn.addEventListener('click', openAddModal);

    // ── Edit modal ──
    var editModal = document.getElementById('dpEditModal');
    var editForm  = document.getElementById('dpEditForm');
    var editName  = document.getElementById('dpEditName');

    document.querySelectorAll('.dp-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editName.value  = btn.dataset.name;
            editForm.action = btn.dataset.route;
            new bootstrap.Modal(editModal).show();
        });
    });
});
</script>

@endsection
