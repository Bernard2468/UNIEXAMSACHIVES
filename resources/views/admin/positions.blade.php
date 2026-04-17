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
                    <div class="ps-wrap">

                        {{-- Page header --}}
                        <div class="ps-page-header">
                            <div>
                                <h1 class="ps-page-title">Positions<span class="ps-title-bar"></span></h1>
                                <p class="ps-page-sub">Manage all staff positions and roles defined in the system.</p>
                            </div>
                            <button class="ps-btn-primary" id="triggerPositionModal">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                New position
                            </button>
                        </div>

                        {{-- Alerts --}}
                        @if(session('success'))
                        <div class="ps-alert ps-alert--ok">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ session('success') }}</span>
                            <button class="ps-alert__x" onclick="this.closest('.ps-alert').remove()"><svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg></button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="ps-alert ps-alert--err">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                            <button class="ps-alert__x" onclick="this.closest('.ps-alert').remove()"><svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg></button>
                        </div>
                        @endif

                        {{-- Table card --}}
                        <div class="ps-card">
                            <div class="ps-card__hd">
                                <div>
                                    <h2 class="ps-card__title">All positions<span class="ps-card__bar"></span></h2>
                                    <p class="ps-card__count">{{ $positions->total() }} {{ $positions->total() === 1 ? 'entry' : 'entries' }} total</p>
                                </div>
                            </div>

                            @if($positions->count() > 0)
                            <div class="ps-table-shell">
                                <table class="ps-table">
                                    <thead>
                                        <tr>
                                            <th class="ps-th ps-th--id">#</th>
                                            <th class="ps-th">Name</th>
                                            <th class="ps-th ps-th--actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($positions as $pos)
                                        <tr class="ps-tr">
                                            <td class="ps-td ps-td--id">{{ $pos->id }}</td>
                                            <td class="ps-td ps-td--name">{{ $pos->name }}</td>
                                            <td class="ps-td ps-td--actions">
                                                <button type="button" class="ps-action ps-action--edit ps-edit-btn"
                                                    data-name="{{ $pos->name }}"
                                                    data-route="{{ route('positions.update', $pos->id) }}">
                                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                    Edit
                                                </button>
                                                <form action="{{ route('positions.destroy', $pos->id) }}" method="POST" style="display:inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="ps-action ps-action--del" onclick="return confirm('Delete this position? This cannot be undone.')">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                                        Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                                @if($positions->hasPages())
                                <div class="pagination-wrapper">
                                    <div class="pagination-info">
                                        Showing <strong>{{ $positions->firstItem() }}</strong> to <strong>{{ $positions->lastItem() }}</strong> of <strong>{{ $positions->total() }}</strong> results
                                    </div>
                                    <div class="pagination-controls">
                                        <ul class="pagination">
                                            @if ($positions->onFirstPage())
                                                <li class="pagination-item"><span class="pagination-link icon disabled"><i class="icofont-arrow-left"></i></span></li>
                                            @else
                                                <li class="pagination-item"><a href="{{ $positions->previousPageUrl() }}" class="pagination-link icon"><i class="icofont-arrow-left"></i></a></li>
                                            @endif

                                            @php
                                                $currentPage = $positions->currentPage();
                                                $lastPage    = $positions->lastPage();
                                                $startPage   = max(1, $currentPage - 2);
                                                $endPage     = min($lastPage, $currentPage + 2);
                                            @endphp

                                            @if($startPage > 1)
                                                <li class="pagination-item"><a href="{{ $positions->url(1) }}" class="pagination-link">1</a></li>
                                                @if($startPage > 2)<li class="pagination-item"><span class="pagination-ellipsis">...</span></li>@endif
                                            @endif

                                            @for ($i = $startPage; $i <= $endPage; $i++)
                                                <li class="pagination-item">
                                                    @if ($i == $currentPage)
                                                        <span class="pagination-link active">{{ $i }}</span>
                                                    @else
                                                        <a href="{{ $positions->url($i) }}" class="pagination-link">{{ $i }}</a>
                                                    @endif
                                                </li>
                                            @endfor

                                            @if($endPage < $lastPage)
                                                @if($endPage < $lastPage - 1)<li class="pagination-item"><span class="pagination-ellipsis">...</span></li>@endif
                                                <li class="pagination-item"><a href="{{ $positions->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a></li>
                                            @endif

                                            @if ($positions->hasMorePages())
                                                <li class="pagination-item"><a href="{{ $positions->nextPageUrl() }}" class="pagination-link icon"><i class="icofont-arrow-right"></i></a></li>
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
                            <div class="ps-empty">
                                <div class="ps-empty__icon">
                                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 7V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v2"/></svg>
                                </div>
                                <p class="ps-empty__text">No positions yet. Create your first one.</p>
                                <button class="ps-btn-primary" id="triggerPositionModalEmpty">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                    Create first position
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

{{-- ── Create Position Modal ── --}}
<div class="modal fade" id="myPositionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="ps-modal">
            <div class="ps-modal__hd">
                <div>
                    <h5 class="ps-modal__title">Add new position</h5>
                    <p class="ps-modal__sub">Enter a title for the staff position or role.</p>
                </div>
                <button type="button" class="ps-modal__close" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                </button>
            </div>
            <div class="ps-modal__body">
                <form action="{{ route('positions.store') }}" method="POST">
                    @csrf
                    <div class="ps-modal__field">
                        <label class="ps-modal__label">Position name</label>
                        <input class="ps-modal__input" type="text" name="name" placeholder="e.g. Head of Department" required autofocus>
                    </div>
                    <div class="ps-modal__foot">
                        <button type="button" class="ps-modal__btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ps-modal__btn-save">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Save position
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Position Modal ── --}}
<div class="modal fade" id="psEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 440px;">
        <div class="ps-modal">
            <div class="ps-modal__hd">
                <div>
                    <h5 class="ps-modal__title">Edit position</h5>
                    <p class="ps-modal__sub">Update the title of this staff position or role.</p>
                </div>
                <button type="button" class="ps-modal__close" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                </button>
            </div>
            <div class="ps-modal__body">
                <form id="psEditForm" action="" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="ps-modal__field">
                        <label class="ps-modal__label">Position / Role name</label>
                        <input class="ps-modal__input" type="text" id="psEditName" name="name" placeholder="e.g. Head of Department" required autofocus>
                    </div>
                    <div class="ps-modal__foot">
                        <button type="button" class="ps-modal__btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ps-modal__btn-save">
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

.ps-wrap, .ps-wrap * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.ps-wrap { max-width: 900px; padding: 4px 0 60px; }

/* ── Page header ── */
.ps-page-header {
    display: flex; align-items: flex-start; justify-content: space-between;
    gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb;
}
.ps-page-title {
    font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em;
    line-height: 1.1; margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ps-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.ps-page-sub { margin: 12px 0 0; font-size: 0.9rem; color: #8a8fa0; font-weight: 400; }

/* ── Primary button ── */
.ps-btn-primary {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
    background: #0c0c0c; color: #fff; border: none; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap;
    flex-shrink: 0; margin-top: 14px; transition: background .15s, transform .12s, box-shadow .15s;
    font-family: 'Outfit', sans-serif !important;
}
.ps-btn-primary:hover { background: #1f2937; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* ── Alerts ── */
.ps-alert {
    display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px;
    border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; font-weight: 500;
    border: 1.5px solid transparent;
}
.ps-alert--ok  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.ps-alert--err { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
.ps-alert__x { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .45; color: inherit; padding: 0; display: flex; align-items: center; }
.ps-alert__x:hover { opacity: 1; }

/* ── Card ── */
.ps-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.ps-card__hd { padding: 18px 24px 14px; border-bottom: 1.5px solid #f5f5f5; }
.ps-card__title {
    font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em;
    margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ps-card__bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.ps-card__count { margin: 8px 0 0; font-size: 0.8rem; color: #b0b5c0; }

/* ── Table ── */
.ps-table-shell { overflow-x: auto; }
.ps-table { width: 100%; border-collapse: collapse; }
.ps-th {
    padding: 10px 20px; text-align: left; font-size: 0.72rem; font-weight: 700;
    color: #b0b5c0; letter-spacing: .07em; text-transform: uppercase;
    background: #fafafa; border-bottom: 1.5px solid #f0f0f0;
}
.ps-th--id { width: 60px; }
.ps-th--actions { width: 170px; text-align: right; }
.ps-tr { border-bottom: 1.5px solid #f5f5f5; transition: background .1s; }
.ps-tr:last-child { border-bottom: none; }
.ps-tr:hover { background: #fafafa; }
.ps-td { padding: 13px 20px; font-size: 0.88rem; color: #374151; vertical-align: middle; }
.ps-td--id   { color: #c0c4cf; font-size: 0.8rem; font-weight: 500; }
.ps-td--name { font-weight: 600; color: #111827; }
.ps-td--actions { text-align: right; white-space: nowrap; }

/* ── Action buttons ── */
.ps-action {
    display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px;
    border-radius: 8px; font-size: 0.8rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid transparent; transition: all .15s; text-decoration: none;
    background: none; font-family: 'Outfit', sans-serif !important; vertical-align: middle;
}
.ps-action--edit { color: #374151; border-color: #e5e7eb; }
.ps-action--edit:hover { background: #f3f4f6; border-color: #d1d5db; color: #111827; text-decoration: none; }
.ps-action--del  { color: #ef4444; border-color: #fee2e2; }
.ps-action--del:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
.ps-action + form { margin-left: 6px; display: inline; }

/* ── Empty ── */
.ps-empty { padding: 52px 24px; text-align: center; }
.ps-empty__icon { display: inline-flex; padding: 18px; background: #f9fafb; border: 1.5px solid #ebebeb; border-radius: 16px; color: #d1d5db; margin-bottom: 16px; }
.ps-empty__text { font-size: 0.9rem; color: #9ca3af; margin-bottom: 20px; }

/* ── Modal ── */
.ps-modal {
    background: #fff; border-radius: 18px; overflow: hidden;
    border: 1.5px solid #ebebeb; font-family: 'Outfit', sans-serif !important;
    pointer-events: auto;
}
.ps-modal * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.ps-modal__hd {
    display: flex; align-items: flex-start; justify-content: space-between;
    padding: 22px 24px 16px; border-bottom: 1.5px solid #f5f5f5;
}
.ps-modal__title {
    font-size: 1rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0 0 4px;
}
.ps-modal__sub { font-size: 0.82rem; color: #9ca3af; margin: 0; }
.ps-modal__close {
    background: none; border: none; cursor: pointer; padding: 4px; color: #9ca3af;
    border-radius: 7px; display: flex; align-items: center; transition: all .15s; flex-shrink: 0;
}
.ps-modal__close:hover { background: #f3f4f6; color: #374151; }
.ps-modal__body { padding: 20px 24px 24px; }
.ps-modal__field { margin-bottom: 20px; }
.ps-modal__label { display: block; font-size: 0.82rem; font-weight: 600; color: #374151; margin-bottom: 7px; }
.ps-modal__input {
    display: block; width: 100%; padding: 10px 13px;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.88rem; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s;
}
.ps-modal__input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.08); }
.ps-modal__input::placeholder { color: #d4d7de; }
.ps-modal__foot { display: flex; justify-content: flex-end; gap: 10px; padding-top: 4px; }
.ps-modal__btn-cancel {
    padding: 9px 18px; background: none; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all .15s;
}
.ps-modal__btn-cancel:hover { border-color: #d1d5db; color: #374151; background: #f9fafb; }
.ps-modal__btn-save {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
    background: #0c0c0c; color: #fff; border: none; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all .15s;
}
.ps-modal__btn-save:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* ── Dark mode ── */
.is_dark .ps-page-title  { color: #f3f4f6; }
.is_dark .ps-title-bar   { background: #f3f4f6; }
.is_dark .ps-page-sub    { color: #6b7280; }
.is_dark .ps-page-header { border-color: #1e2330; }
.is_dark .ps-btn-primary { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ps-btn-primary:hover { background: #e5e7eb; color: #0c0c0c; }
.is_dark .ps-card        { background: #111827; border-color: #1e2330; }
.is_dark .ps-card__hd    { border-color: #1e2330; }
.is_dark .ps-card__title { color: #f3f4f6; }
.is_dark .ps-card__bar   { background: #f3f4f6; }
.is_dark .ps-card__count { color: #6b7280; }
.is_dark .ps-th  { background: #0f172a; border-color: #1e2330; color: #6b7280; }
.is_dark .ps-tr  { border-color: #1e2330; }
.is_dark .ps-tr:hover { background: #0f172a; }
.is_dark .ps-td  { color: #d1d5db; }
.is_dark .ps-td--name { color: #f3f4f6; }
.is_dark .ps-action--edit { color: #d1d5db; border-color: #374151; }
.is_dark .ps-action--edit:hover { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
.is_dark .ps-empty__icon { background: #0f172a; border-color: #1e2330; }
.is_dark .ps-modal { background: #111827; border-color: #1e2330; }
.is_dark .ps-modal__hd   { border-color: #1e2330; }
.is_dark .ps-modal__title { color: #f3f4f6; }
.is_dark .ps-modal__input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .ps-modal__input:focus { border-color: #f3f4f6; }
.is_dark .ps-modal__btn-save { background: #f3f4f6; color: #0c0c0c; }

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
    var triggerBtn = document.getElementById('triggerPositionModal');
    var emptyBtn   = document.getElementById('triggerPositionModalEmpty');
    var addModal   = document.getElementById('myPositionModal');

    function openAddModal(e) {
        e.preventDefault();
        if (addModal) new bootstrap.Modal(addModal).show();
    }
    if (triggerBtn) triggerBtn.addEventListener('click', openAddModal);
    if (emptyBtn)   emptyBtn.addEventListener('click', openAddModal);

    // ── Edit modal ──
    var editModal = document.getElementById('psEditModal');
    var editForm  = document.getElementById('psEditForm');
    var editName  = document.getElementById('psEditName');

    document.querySelectorAll('.ps-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editName.value  = btn.dataset.name;
            editForm.action = btn.dataset.route;
            new bootstrap.Modal(editModal).show();
        });
    });
});
</script>

@endsection
