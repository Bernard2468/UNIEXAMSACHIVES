@extends('layout.app')

@push('styles')
<!-- Inter Font -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

<!-- Font Awesome Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Modern Users Management Page Styles - Consistent Theme */
    * {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    
    .users-hero {
        background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 50%, #cbd5e1 100%);
        padding: 60px 0 40px;
        position: relative;
        overflow: hidden;
    }

    .users-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="users-grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(100,116,139,0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23users-grid)" /></svg>');
        opacity: 0.7;
    }

    .users-hero-content {
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

    /* ============================================================
       SMART FILTER TOOLBAR
       ============================================================ */
    .uf-searchrow {
        display: flex;
        align-items: center;
        gap: 12px;
        max-width: 760px;
        margin: 0 auto;
    }
    .uf-searchrow .search-box { flex: 1; margin: 0; max-width: none; }

    .uf-filter-btn {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 13px 20px;
        border: 2px solid #e5e7eb;
        border-radius: 50px;
        background: #fff;
        color: #475569;
        font-weight: 600;
        font-size: 0.92rem;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: nowrap;
        flex-shrink: 0;
    }
    .uf-filter-btn:hover { border-color: #64748b; background: #f8fafc; }
    .uf-filter-btn.has-filters { border-color: #64748b; color: #1e293b; }
    .uf-filter-count {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 20px;
        height: 20px;
        padding: 0 6px;
        border-radius: 999px;
        background: #64748b;
        color: #fff;
        font-size: 0.72rem;
        font-weight: 700;
        line-height: 1;
    }
    .uf-filter-count[hidden] { display: none; }

    .uf-controls {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        flex-wrap: wrap;
        max-width: 760px;
        margin: 1.25rem auto 0;
    }
    .uf-controls__right { display: flex; align-items: center; gap: 10px; }

    /* Segmented control (status + account type) */
    .uf-segment {
        display: inline-flex;
        background: #f1f5f9;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        padding: 4px;
        gap: 2px;
    }
    .uf-segment button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        border: none;
        background: transparent;
        border-radius: 9px;
        color: #64748b;
        font-weight: 600;
        font-size: 0.85rem;
        cursor: pointer;
        transition: all 0.18s ease;
        white-space: nowrap;
    }
    .uf-segment button:hover { color: #1e293b; }
    .uf-segment button.active {
        background: #fff;
        color: #1e293b;
        box-shadow: 0 1px 3px rgba(15,23,42,0.12);
    }
    .uf-segment button i { font-size: 0.8rem; }
    .uf-segment--wide { display: flex; width: 100%; }
    .uf-segment--wide button { flex: 1; justify-content: center; }

    /* Sort select */
    .uf-sort {
        position: relative;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 0 14px;
        height: 40px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #fff;
        color: #64748b;
    }
    .uf-sort i { font-size: 0.85rem; }
    .uf-sort select {
        border: none;
        background: transparent;
        font-weight: 600;
        font-size: 0.85rem;
        color: #1e293b;
        cursor: pointer;
        outline: none;
        -webkit-appearance: none;
        appearance: none;
        padding-right: 4px;
    }

    /* Active filter chips */
    .uf-active-chips {
        display: flex;
        flex-wrap: wrap;
        gap: 8px;
        justify-content: center;
        max-width: 760px;
        margin: 1rem auto 0;
    }
    .uf-active-chips:empty { margin: 0; }
    .uf-active-chip {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 6px 8px 6px 14px;
        border-radius: 999px;
        background: #eef2f7;
        border: 1px solid #dbe2ea;
        color: #334155;
        font-size: 0.82rem;
        font-weight: 600;
        animation: chipIn 0.18s ease;
    }
    .uf-active-chip .uf-chip-x {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 18px;
        height: 18px;
        border-radius: 50%;
        border: none;
        background: #cbd5e1;
        color: #475569;
        cursor: pointer;
        font-size: 0.65rem;
        transition: all 0.15s ease;
    }
    .uf-active-chip .uf-chip-x:hover { background: #94a3b8; color: #fff; }
    .uf-active-chip.uf-clear-all {
        background: transparent;
        border: 1px dashed #cbd5e1;
        color: #64748b;
        cursor: pointer;
    }
    .uf-active-chip.uf-clear-all:hover { border-color: #94a3b8; color: #1e293b; }
    @keyframes chipIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: none; } }

    /* ---- Filter panel: LEFT side drawer (desktop) / bottom sheet (mobile) ----
       Mirrors the folders "Share & Add-items" drawer (.mdrawer) chrome and the
       header avatar bottom-sheet so the whole system feels consistent. */
    body.uf-lock { overflow: hidden; }

    .uf-sheet-backdrop {
        position: fixed; inset: 0;
        background: rgba(15, 23, 42, 0.42);
        z-index: 10050;
        opacity: 0; visibility: hidden;
        transition: opacity .28s ease, visibility .28s ease;
    }
    .uf-sheet-backdrop.open { opacity: 1; visibility: visible; }

    /* Desktop: full-height drawer anchored to the LEFT, slides in from the edge */
    .uf-sheet {
        position: fixed; top: 0; left: 0; height: 100%;
        width: 420px; max-width: 94vw;
        z-index: 10060;
        background: #fff;
        display: flex; flex-direction: column;
        box-shadow: 22px 0 50px rgba(15, 23, 42, 0.14);
        transform: translateX(-100%);
        transition: transform .34s cubic-bezier(.22, .61, .36, 1);
        -webkit-font-smoothing: antialiased;
    }
    .uf-sheet.open { transform: translateX(0); }

    .uf-sheet__head {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px 22px;
        border-bottom: 1px solid #eef2f7;
        flex-shrink: 0;
    }
    .uf-sheet__grip { display: none; }
    .uf-sheet__head h4 {
        flex: 1;
        margin: 0;
        font-size: 17px;
        font-weight: 700;
        letter-spacing: -0.015em;
        color: #0f172a;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .uf-sheet__head h4 i { color: #64748b; font-size: 0.95rem; }
    .uf-sheet__close {
        width: 36px; height: 36px;
        display: grid; place-items: center;
        border: 1px solid #e2e8f0; border-radius: 10px;
        background: #fff; color: #64748b; cursor: pointer; transition: .15s;
        flex-shrink: 0;
    }
    .uf-sheet__close:hover { background: #f1f5f9; color: #0f172a; }

    .uf-sheet__body { flex: 1; padding: 22px; overflow-y: auto; }
    .uf-sheet__body::-webkit-scrollbar { width: 9px; }
    .uf-sheet__body::-webkit-scrollbar-track { background: #f8fafc; }
    .uf-sheet__body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
    .uf-group { margin-bottom: 20px; }
    .uf-group:last-child { margin-bottom: 0; }
    .uf-group__label {
        display: flex; align-items: center; gap: 7px;
        margin-bottom: 10px;
        font-size: 0.78rem; font-weight: 700; letter-spacing: 0.02em;
        text-transform: uppercase; color: #64748b;
    }
    .uf-group__label i { color: #94a3b8; font-size: 0.8rem; }

    .uf-chipset { display: flex; flex-wrap: wrap; gap: 8px; }
    .uf-chip {
        display: inline-flex; align-items: center; margin: 0;
        padding: 9px 14px; border-radius: 999px;
        border: 1.5px solid #e2e8f0; background: #fff;
        color: #475569; font-size: 0.85rem; font-weight: 600;
        cursor: pointer; transition: all 0.15s ease; user-select: none;
    }
    .uf-chip input { position: absolute; opacity: 0; pointer-events: none; }
    .uf-chip:hover { border-color: #cbd5e1; background: #f8fafc; }
    .uf-chip.is-checked,
    .uf-chip:has(input:checked) {
        border-color: #64748b; background: #64748b; color: #fff;
        box-shadow: 0 2px 8px rgba(100,116,139,0.3);
    }

    .uf-field { position: relative; }
    .uf-field select {
        width: 100%; box-sizing: border-box;
        padding: 12px 40px 12px 14px;
        border: 1.5px solid #e2e8f0; border-radius: 12px;
        background: #fff; color: #0f172a;
        font-size: 0.92rem; font-weight: 500;
        cursor: pointer; outline: none;
        -webkit-appearance: none; appearance: none;
        transition: 0.2s;
    }
    .uf-field select:focus { border-color: #64748b; box-shadow: 0 0 0 3px rgba(100,116,139,0.12); }
    .uf-field__chev {
        position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
        font-size: 0.7rem; color: #94a3b8; pointer-events: none;
    }

    .uf-sheet__foot {
        display: flex; gap: 10px; padding: 14px 22px;
        border-top: 1px solid #eef2f7; background: #fff; flex-shrink: 0;
    }
    .uf-btn {
        flex: 1; padding: 12px; border-radius: 12px;
        font-weight: 700; font-size: 0.9rem; cursor: pointer;
        border: 1px solid transparent; transition: 0.2s;
    }
    .uf-btn--ghost { background: #fff; border-color: #e2e8f0; color: #475569; }
    .uf-btn--ghost:hover { background: #f5f6f8; color: #0f172a; }
    .uf-btn--primary { background: #0f172a; color: #fff; }
    .uf-btn--primary:hover { background: #1e293b; }

    /* Modern User Cards */
    .users-section {
        background: #f9fafb;
        padding: 3rem 0;
    }

    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
    }

    .users-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
        gap: 1.5rem;
    }

    .user-card {
        background: white;
        border-radius: 20px;
        padding: 2rem;
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .user-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(135deg, #64748b 0%, #475569 100%);
    }

    .user-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .user-card-header {
        display: flex;
        align-items: center;
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .user-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #94a3b8 0%, #64748b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 1.8rem;
        box-shadow: 0 4px 12px rgba(148, 163, 184, 0.3);
        flex-shrink: 0;
    }

    .user-info {
        flex: 1;
        min-width: 0;
    }

    .user-name {
        font-size: 1.4rem;
        font-weight: 600;
        color: #111827;
        margin: 0 0 0.5rem 0;
        line-height: 1.3;
    }

    .user-email {
        font-size: 0.95rem;
        color: #6b7280;
        margin: 0;
        word-break: break-all;
    }

    .user-status-section {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1.5rem;
    }

    .status-badge {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        padding: 8px 16px;
        border-radius: 25px;
        font-size: 0.85rem;
        font-weight: 600;
    }

    .status-badge.approved {
        background: rgba(220, 252, 231, 0.8);
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.3);
    }

    .status-badge.pending {
        background: rgba(254, 243, 199, 0.8);
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.3);
    }

    .user-actions {
        display: flex;
        gap: 0.8rem;
        flex-wrap: wrap;
    }

    .action-btn {
        padding: 12px 16px;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        background: white;
        color: #6b7280;
        text-decoration: none;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: center;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        cursor: pointer;
        border: none;
        min-width: 100px;
        justify-content: center;
    }

    .action-btn:hover {
        text-decoration: none;
        transform: translateY(-2px);
    }

    .action-btn.approve {
        background: #10b981;
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .action-btn.approve:hover {
        background: #059669;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .action-btn.disapprove {
        background: #ef4444;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .action-btn.disapprove:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .action-btn.delete {
        background: #ef4444;
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .action-btn.delete:hover {
        background: #dc2626;
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    .no-users {
        text-align: center;
        padding: 4rem 2rem;
        color: #6b7280;
        background: white;
        border-radius: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        grid-column: 1 / -1;
    }

    /* Table View Styles */
    /* Modern segmented grid/table toggle — matches the .uf-segment language */
    .view-toggle {
        display: inline-flex;
        gap: 2px;
        padding: 4px;
        border: 1px solid #e5e7eb;
        border-radius: 12px;
        background: #f1f5f9;
    }
    .view-toggle button {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 14px;
        border: none;
        background: transparent;
        color: #64748b;
        font-weight: 600;
        font-size: 0.85rem;
        border-radius: 9px;
        cursor: pointer;
        transition: all 0.18s ease;
    }
    .view-toggle button i { font-size: 0.9rem; }
    .view-toggle button:hover { color: #1e293b; }
    .view-toggle button.active {
        background: #fff;
        color: #1e293b;
        box-shadow: 0 1px 3px rgba(15,23,42,0.12);
    }
    .users-table-wrapper {
        background: white;
        border-radius: 16px;
        border: 1px solid #f3f4f6;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        overflow: auto;
    }
    table.users-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
    }
    table.users-table th, table.users-table td {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.95rem;
    }
    table.users-table th {
        background: #f8fafc;
        color: #475569;
        position: sticky;
        top: 0;
        z-index: 1;
    }
    .status-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 10px;
        border-radius: 999px;
        font-weight: 600;
        font-size: 0.85rem;
    }
    .status-chip.approved { background: rgba(220, 252, 231, 0.8); color: #059669; border: 1px solid rgba(16,185,129,0.3); }
    .status-chip.pending { background: rgba(254, 243, 199, 0.8); color: #d97706; border: 1px solid rgba(245,158,11,0.3); }

    /* Pagination */
    .pagination-wrapper {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1rem 1.5rem;
        border-top: 1px solid #e5e7eb;
        background: #f9fafb;
        border-radius: 0 0 16px 16px;
        flex-wrap: wrap;
    }
    .pagination-info {
        font-size: 0.875rem;
        color: #6b7280;
        white-space: nowrap;
    }
    .pagination-info strong { color: #1f2937; font-weight: 600; }
    .pagination-controls { display: flex; align-items: center; gap: 0.5rem; }
    .pagination { display: flex; list-style: none; margin: 0; padding: 0; gap: 0.25rem; }
    .pagination-item { display: inline-block; }
    .pagination-link {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 2.5rem;
        height: 2.5rem;
        padding: 0 0.5rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: white;
        color: #374151;
        font-size: 0.875rem;
        text-decoration: none;
        cursor: pointer;
        transition: all 0.2s;
    }
    .pagination-link:hover:not(.disabled):not(.active) {
        background: #f3f4f6;
        border-color: #d1d5db;
        color: #1f2937;
    }
    .pagination-link.active {
        background: #64748b;
        color: white;
        border-color: #64748b;
        font-weight: 600;
    }
    .pagination-link.disabled {
        color: #9ca3af;
        cursor: not-allowed;
        background: #f9fafb;
        opacity: 0.5;
    }
    .pagination-link.icon { width: 2.5rem; padding: 0; }
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
    .page-size-label { font-size: 0.875rem; color: #6b7280; }
    .page-size-select {
        padding: 0.5rem 2rem 0.5rem 0.75rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        background: white;
        color: #374151;
        cursor: pointer;
    }
    @media (max-width: 768px) {
        .pagination-wrapper { flex-direction: column; align-items: stretch; }
        .pagination-controls { justify-content: center; }
        .page-size-selector { justify-content: center; }
    }

    /* Live search loading state */
    .search-input.is-searching {
        background-image: linear-gradient(90deg, transparent, rgba(100,116,139,0.08), transparent);
        background-size: 200% 100%;
        animation: searchShimmer 1.2s linear infinite;
    }
    @keyframes searchShimmer {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }
    #usersResultsContainer.is-loading {
        opacity: 0.55;
        pointer-events: none;
        transition: opacity 0.15s ease;
    }
    .search-spinner {
        position: absolute;
        right: 56px;
        top: 50%;
        transform: translateY(-50%);
        width: 18px;
        height: 18px;
        border: 2px solid #cbd5e1;
        border-top-color: #475569;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
        display: none;
    }
    .search-spinner.visible { display: block; }
    @keyframes spin { to { transform: translateY(-50%) rotate(360deg); } }

    /* Staff Category Styles */
    .staff-category-badge {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(147, 197, 253, 0.8);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.3);
        margin-top: 0.5rem;
    }

    .staff-category-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        background: rgba(147, 197, 253, 0.8);
        color: #1e40af;
        border: 1px solid rgba(59, 130, 246, 0.3);
    }

    /* Position Badge Styles */
    .position-badge {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        background: rgba(196, 181, 253, 0.8);
        color: #5b21b6;
        border: 1px solid rgba(139, 92, 246, 0.3);
        margin-top: 0.5rem;
    }

    .position-chip {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        padding: 3px 8px;
        border-radius: 10px;
        font-size: 0.7rem;
        font-weight: 600;
        background: rgba(196, 181, 253, 0.8);
        color: #5b21b6;
        border: 1px solid rgba(139, 92, 246, 0.3);
        white-space: nowrap;
    }

    .position-badge i,
    .position-chip i {
        font-size: 0.65rem;
    }

    .no-category {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.9rem;
    }

    .no-users i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #64748b;
        opacity: 0.6;
    }

    /* Responsive Design — Tablet (768–1023px) */
    @media (max-width: 1023px) {
        .users-list { grid-template-columns: 1fr; }
    }

    /* ============================================================
       MOBILE APP EXPERIENCE (≤ 767px)
       ============================================================ */
    @media (max-width: 767px) {
        /* ── Edge-to-edge, de-squished layout (same recipe as the memo/compose
           pages). The page frame adds 30px (.full__width__padding) + 30px row
           gutter + a centered Bootstrap .container max-width, which squeezes the
           content into a narrow middle column. Zero those and give each section a
           single clean 16px app gutter that runs to both edges. */
        .dashboardarea .full__width__padding { padding-left: 0 !important; padding-right: 0 !important; }
        .col-xl-9.col-lg-9.col-md-12 { padding-left: 0; padding-right: 0; }
        .users-hero .container,
        .search-filter-section .container,
        .users-section .container {
            max-width: none; width: 100%;
            padding-left: 16px; padding-right: 16px;
        }

        /* Compact, app-like hero */
        .users-hero { padding: 20px 0 16px; background: #fff; }
        .users-hero::before { display: none; }
        .users-hero-content > div:first-child { align-items: center !important; }
        .hero-title {
            font-size: 1.35rem; margin-bottom: 2px;
            background: none; -webkit-text-fill-color: initial; color: #0f172a;
        }
        .hero-subtitle { display: none; }

        /* Add User becomes a compact icon pill */
        .add-user-btn { padding: 11px 14px !important; border-radius: 12px !important; font-size: 0 !important; gap: 0 !important; }
        .add-user-btn i { font-size: 16px; }

        /* Stats as a horizontal scroll strip of pills */
        .hero-stats {
            gap: 10px; margin-top: 16px;
            justify-content: flex-start;
            overflow-x: auto; padding-bottom: 4px;
            -webkit-overflow-scrolling: touch; scrollbar-width: none;
        }
        .hero-stats::-webkit-scrollbar { display: none; }
        .stat-item {
            flex: 0 0 auto; min-width: 96px;
            background: #f8fafc; border: 1px solid #eef2f7;
            border-radius: 14px; padding: 12px 14px;
        }
        .stat-number { font-size: 1.4rem; }
        .stat-label { font-size: 0.72rem; margin-top: 2px; }

        /* Search / filter section */
        .search-filter-section { padding: 14px 0; }
        .uf-searchrow { gap: 8px; }
        /* 16px keeps iOS Safari from auto-zooming the page on input focus */
        .search-input { padding: 12px 46px 12px 16px; font-size: 16px; }
        .uf-filter-btn { padding: 12px 14px; }
        .uf-filter-btn__label { display: none; }

        /* Same anti-zoom rule for every other focusable field on the page */
        .uf-field select,
        .uf-sort select,
        .animated-input,
        .efi-input, .efi-select { font-size: 16px; }

        /* Status segmented control spans full width, scrolls if needed */
        .uf-controls { margin-top: 12px; gap: 10px; }
        .uf-segment { flex: 1; }
        .uf-segment#statusSegment { width: 100%; }
        .uf-segment#statusSegment button { flex: 1; justify-content: center; padding: 9px 8px; }
        .uf-segment#statusSegment button span { display: none; }
        .uf-segment#statusSegment button i { font-size: 0.95rem; }
        .uf-controls__right { width: 100%; justify-content: space-between; }
        .view-toggle button span { display: none; }

        /* Cards: app list rows */
        .users-section { padding: 16px 0 90px; }
        .users-section .container { padding-left: 16px; padding-right: 16px; }
        .users-list { gap: 12px; }
        .user-card { padding: 16px; border-radius: 16px; }
        .user-card::before { height: 3px; }
        .user-card-header { flex-direction: row; text-align: left; gap: 12px; margin-bottom: 12px; align-items: center; }
        .user-avatar { width: 52px; height: 52px; font-size: 1.15rem; }
        .user-name { font-size: 1.05rem; margin-bottom: 2px; }
        .user-email { font-size: 0.82rem; word-break: break-word; }

        .user-status-section { flex-direction: row; flex-wrap: wrap; gap: 8px; align-items: center; margin-bottom: 14px; }
        .status-badge, .staff-category-badge, .position-badge { margin-top: 0; padding: 5px 11px; font-size: 0.75rem; }

        /* Icon-only action buttons on mobile */
        .user-actions { justify-content: flex-start; gap: 10px; }
        .user-actions .action-btn {
            min-width: 0; width: 44px; height: 44px; padding: 0;
            border-radius: 12px; flex: 0 0 auto;
        }
        .user-actions .action-btn .btn-label { display: none; }
        .user-actions .action-btn i { font-size: 1rem; }
        .user-actions form { display: inline-flex !important; }

        /* Filter drawer becomes a BOTTOM SHEET — same idiom as the header avatar
           menu and the UIMMS "Minute-to" sheet, so it feels native across the app. */
        .uf-sheet {
            top: auto; left: 0; right: 0; bottom: 0;
            height: auto; width: 100%; max-width: 100%;
            max-height: 85dvh;
            border-radius: 22px 22px 0 0;
            transform: translateY(100%);
            transition: transform .3s cubic-bezier(.4, 0, .2, 1);
            box-shadow: 0 -18px 50px rgba(15, 23, 42, 0.28);
        }
        .uf-sheet.open { transform: translateY(0); }
        .uf-sheet__grip {
            display: block; position: absolute; top: 8px; left: 50%; transform: translateX(-50%);
            width: 42px; height: 4px; border-radius: 999px; background: #e2e8f0;
        }
        .uf-sheet__head { position: relative; padding-top: 20px; }
        .uf-sheet__foot { padding-bottom: calc(14px + env(safe-area-inset-bottom)); }
    }

    /* Wide / landscape phones: two-up card grid */
    @media (max-width: 767px) and (min-width: 600px) {
        .users-list { grid-template-columns: 1fr 1fr; }
    }

    /* ===== Users table actions: 3-dot kebab menu (all screen sizes) ===== */
    .uactions { position: relative; display: inline-block; }

    .uactions-toggle {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 38px;
        height: 38px;
        border: 1px solid #e2e8f0;
        background: #fff;
        border-radius: 10px;
        cursor: pointer;
        transition: background 0.2s ease, border-color 0.2s ease;
    }
    .uactions-toggle:hover { background: #f4f6fb; border-color: #cbd5e1; }
    .uactions-toggle .more-icon { width: 18px; height: 18px; opacity: 0.6; }

    /* the floating dropdown (portalled to <body> + positioned in JS) */
    .uactions-menu {
        display: none;
        position: fixed;
        z-index: 1000;
        flex-direction: column;
        align-items: stretch;
        gap: 2px;
        width: 210px;
        padding: 6px;
        background: #fff;
        border: 1px solid #e9edf4;
        border-radius: 12px;
        box-shadow: 0 14px 38px rgba(20, 30, 55, 0.18);
    }
    .uactions-menu.open { display: flex; }
    .uactions-menu form { display: block; width: 100%; margin: 0; }

    .uact {
        display: inline-flex;
        align-items: center;
        justify-content: flex-start;
        width: 100%;
        gap: 12px;
        padding: 10px 12px;
        border: none;
        background: transparent;
        border-radius: 9px;
        cursor: pointer;
        color: #283041;
        font-size: 13.5px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.2s ease, color 0.2s ease;
    }
    .uact > i { width: 20px; text-align: center; font-size: 15px; }
    .uact-label { display: inline; line-height: 1; }
    .uact.approve { color: #2f8f63; }
    .uact.approve:hover { background: #e8f4ee; }
    .uact.disapprove, .uact.delete { color: #c0392b; }
    .uact.disapprove:hover, .uact.delete:hover { background: #fbecef; }
    .uact.edit { color: #2563eb; }
    .uact.edit:hover { background: #eaf1fe; }
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
                    <div class="users-hero">
                        <div class="container">
                            <div class="users-hero-content">
                                <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                    <div>
                                        <h1 class="hero-title">Manage Users Account</h1>
                                        <p class="hero-subtitle">Administer user accounts and permissions</p>
                                    </div>
                                    <button type="button" class="add-user-btn" id="addUserBtn" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; font-size: 15px; cursor: pointer; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3); transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;">
                                        <i class="fas fa-user-plus"></i> Add User
                                    </button>
                                </div>
                                
                                <div class="hero-stats">
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $totalUsers }}</span>
                                        <div class="stat-label">Total Users</div>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $approvedCount }}</span>
                                        <div class="stat-label">Approved</div>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-number">{{ $pendingCount }}</span>
                                        <div class="stat-label">Pending</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Search and Smart Filter Section -->
                    <div class="search-filter-section">
                        <div class="container">
                            <form method="GET" action="{{ route('dashboard.users') }}" role="search" id="usersSearchForm">
                                <input type="hidden" name="filter" value="{{ $activeFilter ?? 'all' }}" id="statusFilterInput">
                                <input type="hidden" name="per_page" value="{{ request('per_page', 15) }}">

                                {{-- Row 1: search + Filters trigger --}}
                                <div class="uf-searchrow">
                                    <div class="search-box">
                                        <input type="text" name="search" class="search-input" id="searchInput" placeholder="Search users by name or email..." value="{{ $search ?? '' }}" autocomplete="off">
                                        <div class="search-spinner" id="searchSpinner" aria-hidden="true"></div>
                                        <button type="button" class="search-btn" id="clearSearchBtn" style="right: 56px; background:#94a3b8; display: {{ !empty($search) ? 'flex' : 'none' }};" title="Clear search">
                                            <i class="fas fa-times"></i>
                                        </button>
                                        <button type="submit" class="search-btn" title="Search">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                    <button type="button" class="uf-filter-btn" id="openFiltersBtn" aria-haspopup="dialog" aria-expanded="false">
                                        <i class="fas fa-sliders-h"></i>
                                        <span class="uf-filter-btn__label">Filters</span>
                                        <span class="uf-filter-count" id="filterCountBadge" {{ ($activeFacetCount ?? 0) > 0 ? '' : 'hidden' }}>{{ $activeFacetCount ?? 0 }}</span>
                                    </button>
                                </div>

                                {{-- Row 2: status segmented control + sort + view toggle --}}
                                <div class="uf-controls">
                                    <div class="uf-segment" id="statusSegment" role="group" aria-label="Account status">
                                        <button type="button" data-status="all" class="{{ ($activeFilter ?? 'all') === 'all' ? 'active' : '' }}"><i class="fas fa-users"></i><span>All</span></button>
                                        <button type="button" data-status="approved" class="{{ ($activeFilter ?? '') === 'approved' ? 'active' : '' }}"><i class="fas fa-check-circle"></i><span>Approved</span></button>
                                        <button type="button" data-status="pending" class="{{ ($activeFilter ?? '') === 'pending' ? 'active' : '' }}"><i class="fas fa-clock"></i><span>Pending</span></button>
                                    </div>

                                    <div class="uf-controls__right">
                                        <div class="uf-sort">
                                            <i class="fas fa-arrow-down-short-wide"></i>
                                            <select name="sort" id="sortSelect" aria-label="Sort users">
                                                <option value="recent" {{ ($sort ?? 'recent') === 'recent' ? 'selected' : '' }}>Newest first</option>
                                                <option value="oldest" {{ ($sort ?? '') === 'oldest' ? 'selected' : '' }}>Oldest first</option>
                                                <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Name A–Z</option>
                                                <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Name Z–A</option>
                                            </select>
                                        </div>
                                        <div class="view-toggle" role="group" aria-label="View toggle">
                                            <button type="button" id="gridViewBtn" class="active" title="Grid view"><i class="fas fa-th-large"></i><span>Grid</span></button>
                                            <button type="button" id="tableViewBtn" title="Table view"><i class="fas fa-table"></i><span>Table</span></button>
                                        </div>
                                    </div>
                                </div>

                                {{-- Row 3: active filter chips (rendered by JS) --}}
                                <div class="uf-active-chips" id="activeChips" aria-live="polite"></div>

                                {{-- Filter panel: left side-drawer on desktop, bottom sheet on mobile --}}
                                <div class="uf-sheet-backdrop" id="filterBackdrop"></div>
                                <div class="uf-sheet" id="filterSheet" role="dialog" aria-modal="true" aria-label="Filter users" aria-hidden="true">
                                    <div class="uf-sheet__head">
                                        <span class="uf-sheet__grip" aria-hidden="true"></span>
                                        <h4><i class="fas fa-sliders-h"></i> Filters</h4>
                                        <button type="button" class="uf-sheet__close" id="closeFiltersBtn" aria-label="Close filters"><i class="fas fa-times"></i></button>
                                    </div>
                                    <div class="uf-sheet__body">
                                        <div class="uf-group">
                                            <span class="uf-group__label"><i class="fas fa-user-tag"></i> Staff Category</span>
                                            <div class="uf-chipset">
                                                @foreach(($staffCategoryOptions ?? []) as $cat)
                                                    <label class="uf-chip">
                                                        <input type="checkbox" name="staff_category[]" value="{{ $cat }}" {{ in_array($cat, $selectedCategories ?? []) ? 'checked' : '' }}>
                                                        <span>{{ $cat }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        </div>

                                        <div class="uf-group">
                                            <span class="uf-group__label"><i class="fas fa-building-columns"></i> Department / Faculty / Unit</span>
                                            <div class="uf-field">
                                                <select name="department_id" id="deptFilter">
                                                    <option value="">All departments</option>
                                                    @foreach($departments as $d)
                                                        <option value="{{ $d->id }}" {{ (string)($selectedDepartmentId ?? '') === (string)$d->id ? 'selected' : '' }}>{{ $d->name }}</option>
                                                    @endforeach
                                                </select>
                                                <i class="fas fa-chevron-down uf-field__chev"></i>
                                            </div>
                                        </div>

                                        <div class="uf-group">
                                            <span class="uf-group__label"><i class="fas fa-briefcase"></i> Position</span>
                                            <div class="uf-field">
                                                <select name="position_id" id="positionFilter">
                                                    <option value="">Any position</option>
                                                    @foreach($positions as $p)
                                                        <option value="{{ $p->id }}" {{ (string)($selectedPositionId ?? '') === (string)$p->id ? 'selected' : '' }}>{{ $p->name }}</option>
                                                    @endforeach
                                                </select>
                                                <i class="fas fa-chevron-down uf-field__chev"></i>
                                            </div>
                                        </div>

                                        <div class="uf-group">
                                            <span class="uf-group__label"><i class="fas fa-id-badge"></i> Account Type</span>
                                            <div class="uf-segment uf-segment--wide" id="accountTypeSegment" role="group" aria-label="Account type">
                                                <button type="button" data-account="" class="{{ empty($selectedAccountType) ? 'active' : '' }}">All</button>
                                                <button type="button" data-account="individual" class="{{ ($selectedAccountType ?? '') === 'individual' ? 'active' : '' }}">Individual</button>
                                                <button type="button" data-account="office" class="{{ ($selectedAccountType ?? '') === 'office' ? 'active' : '' }}">Office</button>
                                            </div>
                                            <input type="hidden" name="account_type" id="accountTypeInput" value="{{ $selectedAccountType ?? '' }}">
                                        </div>
                                    </div>
                                    <div class="uf-sheet__foot">
                                        <button type="button" class="uf-btn uf-btn--ghost" id="clearFiltersBtn">Clear all</button>
                                        <button type="button" class="uf-btn uf-btn--primary" id="applyFiltersBtn">Show results</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Users Section -->
                    <div class="users-section">
                        <div class="container" id="usersResultsContainer">
                            @if ($users->total() > 0)
                                <div class="users-list" id="gridView">
                                    @foreach ($users as $user)
                                        <div class="user-card" data-status="{{ $user->is_approve ? 'approved' : 'pending' }}" data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                            <div class="user-card-header">
                                                <div class="user-avatar">
                                                    {{ strtoupper(substr($user->first_name, 0, 1) . substr($user->last_name, 0, 1)) }}
                                                </div>
                                                <div class="user-info">
                                                    <h3 class="user-name">{{ $user->first_name }} {{ $user->last_name }}</h3>
                                                    <p class="user-email">{{ $user->email }}</p>
                                                </div>
                                            </div>
                                            
                                            <div class="user-status-section">
                                                <div class="status-badge {{ $user->is_approve ? 'approved' : 'pending' }}">
                                                    <i class="fas {{ $user->is_approve ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                                    {{ $user->is_approve ? 'Approved' : 'Pending' }}
                                                </div>
                                                @if($user->staff_category)
                                                    <div class="staff-category-badge">
                                                        <i class="fas fa-user-tag"></i>
                                                        {{ $user->staff_category }}
                                                    </div>
                                                @endif
                                                @if($user->position)
                                                    <div class="position-badge">
                                                        <i class="fas fa-briefcase"></i>
                                                        {{ $user->position->name }}
                                                    </div>
                                                @endif
                                            </div>
                                            
                                            <div class="user-actions">
                                                @if (!$user->is_approve)
                                                    <form action="{{ route('users.approve', $user->id) }}" method="post" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="action-btn approve" title="Approve">
                                                            <i class="fas fa-check"></i>
                                                            <span class="btn-label">Approve</span>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('users.disapprove', $user->id) }}" method="post" style="display: inline;">
                                                        @csrf
                                                        <button type="submit" class="action-btn disapprove" title="Disapprove">
                                                            <i class="fas fa-thumbs-down"></i>
                                                            <span class="btn-label">Disapprove</span>
                                                        </button>
                                                    </form>
                                                @endif

                                                <button type="button" class="action-btn edit-info" title="Edit Info" style="background:#f0f9ff; color:#0369a1; border:1px solid #bae6fd;" onclick="openEditInfoModal({{ $user->id }}, '{{ addslashes($user->first_name . ' ' . $user->last_name) }}', '{{ addslashes($user->email) }}', '{{ $user->department_id }}', '{{ addslashes($user->staff_category ?? '') }}', '{{ $user->position_id }}', '{{ $user->account_type ?? 'individual' }}', '{{ $user->secondaryDepartments->pluck('id')->join(',') }}')">
                                                    <i class="fas fa-user-edit"></i>
                                                    <span class="btn-label">Edit Info</span>
                                                </button>

                                                <form action="{{ route('users.destroy', $user->id) }}" method="post" style="display: inline;" id="delete-user-form-{{ $user->id }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="action-btn delete" title="Delete" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                        <i class="fas fa-trash"></i>
                                                        <span class="btn-label">Delete</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="users-table-wrapper" id="tableView" style="display:none;">
                                    <table class="users-table" id="usersTable">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Staff Category</th>
                                                <th>Status</th>
                                                <th>Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($users as $index => $user)
                                            <tr data-status="{{ $user->is_approve ? 'approved' : 'pending' }}" data-search="{{ strtolower($user->first_name . ' ' . $user->last_name . ' ' . $user->email) }}">
                                                <td>{{ $users->firstItem() + $loop->index }}</td>
                                                <td>
                                                    <div style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap;">
                                                        <span>{{ $user->first_name }} {{ $user->last_name }}</span>
                                                        @if($user->position)
                                                            <span class="position-chip">
                                                                <i class="fas fa-briefcase"></i>
                                                                {{ $user->position->name }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>{{ $user->email }}</td>
                                                <td>
                                                    @if($user->staff_category)
                                                        <span class="staff-category-chip">
                                                            <i class="fas fa-user-tag"></i>
                                                            {{ $user->staff_category }}
                                                        </span>
                                                    @else
                                                        <span class="no-category">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="status-chip {{ $user->is_approve ? 'approved' : 'pending' }}">
                                                        <i class="fas {{ $user->is_approve ? 'fa-check-circle' : 'fa-clock' }}"></i>
                                                        {{ $user->is_approve ? 'Approved' : 'Pending' }}
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="uactions">
                                                        <button type="button" class="uactions-toggle" title="Actions" aria-haspopup="true" aria-expanded="false" onclick="toggleUserActions(this, event)">
                                                            <img src="https://img.icons8.com/glyph-neue/64/more.png" alt="Actions" class="more-icon">
                                                        </button>
                                                        <div class="uactions-menu">
                                                            @if (!$user->is_approve)
                                                            <form action="{{ route('users.approve', $user->id) }}" method="post">
                                                                @csrf
                                                                <button type="submit" class="uact approve" title="Approve">
                                                                    <i class="fas fa-check"></i>
                                                                    <span class="uact-label">Approve</span>
                                                                </button>
                                                            </form>
                                                            @else
                                                            <form action="{{ route('users.disapprove', $user->id) }}" method="post">
                                                                @csrf
                                                                <button type="submit" class="uact disapprove" title="Disapprove">
                                                                    <i class="fas fa-thumbs-down"></i>
                                                                    <span class="uact-label">Disapprove</span>
                                                                </button>
                                                            </form>
                                                            @endif
                                                            <button type="button" class="uact edit" title="Edit Info (Email / Account Category / Department / Staff Category / Position)" onclick="openEditInfoModal({{ $user->id }}, '{{ addslashes($user->first_name . ' ' . $user->last_name) }}', '{{ addslashes($user->email) }}', '{{ $user->department_id }}', '{{ addslashes($user->staff_category ?? '') }}', '{{ $user->position_id }}', '{{ $user->account_type ?? 'individual' }}', '{{ $user->secondaryDepartments->pluck('id')->join(',') }}')">
                                                                <i class="fas fa-user-edit"></i>
                                                                <span class="uact-label">Edit info</span>
                                                            </button>
                                                            <form action="{{ route('users.destroy', $user->id) }}" method="post" id="delete-user-table-form-{{ $user->id }}">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="button" class="uact delete" title="Delete" onclick="confirmDeleteUser({{ $user->id }}, '{{ $user->first_name }} {{ $user->last_name }}')">
                                                                    <i class="fas fa-trash"></i>
                                                                    <span class="uact-label">Delete</span>
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>

                                @if ($users->hasPages())
                                <div class="pagination-wrapper">
                                    <div class="pagination-info">
                                        Showing <strong>{{ $users->firstItem() }}</strong> to <strong>{{ $users->lastItem() }}</strong> of <strong>{{ $users->total() }}</strong> users
                                    </div>
                                    <div class="pagination-controls">
                                        <ul class="pagination">
                                            @if ($users->onFirstPage())
                                                <li class="pagination-item">
                                                    <span class="pagination-link icon disabled"><i class="fas fa-chevron-left"></i></span>
                                                </li>
                                            @else
                                                <li class="pagination-item">
                                                    <a href="{{ $users->previousPageUrl() }}" class="pagination-link icon"><i class="fas fa-chevron-left"></i></a>
                                                </li>
                                            @endif
                                            @php
                                                $currentPage = $users->currentPage();
                                                $lastPage = $users->lastPage();
                                                $startPage = max(1, $currentPage - 2);
                                                $endPage = min($lastPage, $currentPage + 2);
                                            @endphp
                                            @if ($startPage > 1)
                                                <li class="pagination-item">
                                                    <a href="{{ $users->url(1) }}" class="pagination-link">1</a>
                                                </li>
                                                @if ($startPage > 2)
                                                    <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                                                @endif
                                            @endif
                                            @for ($i = $startPage; $i <= $endPage; $i++)
                                                <li class="pagination-item">
                                                    @if ($i == $currentPage)
                                                        <span class="pagination-link active">{{ $i }}</span>
                                                    @else
                                                        <a href="{{ $users->url($i) }}" class="pagination-link">{{ $i }}</a>
                                                    @endif
                                                </li>
                                            @endfor
                                            @if ($endPage < $lastPage)
                                                @if ($endPage < $lastPage - 1)
                                                    <li class="pagination-item"><span class="pagination-ellipsis">...</span></li>
                                                @endif
                                                <li class="pagination-item">
                                                    <a href="{{ $users->url($lastPage) }}" class="pagination-link">{{ $lastPage }}</a>
                                                </li>
                                            @endif
                                            @if ($users->hasMorePages())
                                                <li class="pagination-item">
                                                    <a href="{{ $users->nextPageUrl() }}" class="pagination-link icon"><i class="fas fa-chevron-right"></i></a>
                                                </li>
                                            @else
                                                <li class="pagination-item">
                                                    <span class="pagination-link icon disabled"><i class="fas fa-chevron-right"></i></span>
                                                </li>
                                            @endif
                                        </ul>
                                    </div>
                                    <div class="page-size-selector">
                                        <span class="page-size-label">Per page:</span>
                                        <select class="page-size-select" onchange="changePageSize(this.value)">
                                            <option value="10" {{ request('per_page', 15) == 10 ? 'selected' : '' }}>10</option>
                                            <option value="15" {{ request('per_page', 15) == 15 ? 'selected' : '' }}>15</option>
                                            <option value="25" {{ request('per_page', 15) == 25 ? 'selected' : '' }}>25</option>
                                            <option value="50" {{ request('per_page', 15) == 50 ? 'selected' : '' }}>50</option>
                                        </select>
                                    </div>
                                </div>
                                @endif
                            @else
                                <div class="no-users">
                                    <i class="fas fa-users"></i>
                                    @if(!empty($search))
                                        <h4>No matches for "{{ $search }}"</h4>
                                        <p>No users match your search. Try a different name or email.</p>
                                        <a href="{{ route('dashboard.users') }}" class="filter-tab" style="margin-top:1rem; display:inline-block;">Clear search</a>
                                    @else
                                        <h4>No Users Found</h4>
                                        <p>There are no users currently registered in the system.</p>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<!-- Add User Modal -->
<div id="addUserModal" class="add-user-modal" style="display: none;">
    <div class="add-user-modal-overlay"></div>
    <div class="add-user-modal-content">
        <div class="add-user-modal-header">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            <button type="button" class="close-modal-btn" id="closeAddUserModal">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="add-user-modal-body">
            <form id="addUserForm" method="POST" action="{{ route('users.store') }}">
                @csrf
                <div class="form-row">
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" name="first_name" id="add-user-firstname" class="animated-input" placeholder="First name" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="input-container">
                            <input type="text" name="last_name" id="add-user-lastname" class="animated-input" placeholder="Last name" required>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <input type="email" name="email" id="add-user-email" class="animated-input" placeholder="Enter email address" required>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <select name="department_id" id="add-user-department" class="animated-input" required>
                            <option value="" disabled selected>Choose Primary Department/Faculty/Unit</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}">{{ $department->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="dept-multi-label"><i class="fas fa-layer-group"></i> Secondary Departments <span class="dept-opt">optional</span></label>
                    <div class="dept-multiselect" id="add-user-secondary-departments">
                        @foreach($departments as $department)
                            <label class="dept-check" data-dept-id="{{ $department->id }}">
                                <input type="checkbox" name="secondary_department_ids[]" value="{{ $department->id }}">
                                <span>{{ $department->name }}</span>
                            </label>
                        @endforeach
                    </div>
                    <small class="form-text text-muted">Extra departments this person also belongs to (e.g. teaches or takes a course elsewhere). They'll see those departments' shared folders.</small>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <select name="staff_category" id="add-user-staff-category" class="animated-input" required>
                            <option value="" disabled selected>Choose Staff Category</option>
                            <option value="Junior Staff">Junior Staff</option>
                            <option value="Senior Staff">Senior Staff</option>
                            <option value="Senior Member (Non-Teaching)">Senior Member (Non-Teaching)</option>
                            <option value="Senior Member (Teaching)">Senior Member (Teaching)</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <select name="position_id" id="add-user-position" class="animated-input">
                            <option value="" selected>Choose Position (Optional)</option>
                            @foreach($positions as $position)
                                <option value="{{ $position->id }}">{{ $position->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <input type="password" name="temporary_password" id="add-user-password" class="animated-input" placeholder="Set temporary password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="toggleAddUserPassword()">
                            <i class="icofont-eye"></i>
                        </button>
                    </div>
                    <small class="form-text text-muted">User will be required to change this password on first login</small>
                </div>

                <div class="form-group">
                    <div class="input-container">
                        <input type="password" name="temporary_password_confirmation" id="add-user-password-confirm" class="animated-input" placeholder="Confirm temporary password" required minlength="8">
                        <button type="button" class="password-toggle" onclick="toggleAddUserPasswordConfirm()">
                            <i class="icofont-eye"></i>
                        </button>
                    </div>
                </div>

                <div class="form-actions-modal">
                    <button type="button" class="cancel-btn" id="cancelAddUserBtn">Cancel</button>
                    <button type="submit" class="submit-btn-modal">
                        <i class="fas fa-user-plus"></i> Add User
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Info Modal — clean monochrome design (namespaced .efi-*) -->
<div id="editInfoModal" class="add-user-modal efi-modal" style="display: none;">
    <div class="add-user-modal-overlay efi-overlay"></div>
    <div class="efi-card" role="dialog" aria-modal="true" aria-labelledby="efiTitle">
        <button type="button" class="efi-close" onclick="closeEditInfoModal()" aria-label="Close">
            <i class="fas fa-times"></i>
        </button>

        <div class="efi-head">
            <div class="efi-avatar"><span id="editInfoInitials">--</span></div>
            <div class="efi-head__text">
                <h3 class="efi-title" id="efiTitle">Edit User Info</h3>
                <p class="efi-sub" id="editInfoUserName">—</p>
            </div>
        </div>

        <form id="editInfoForm" method="POST" class="efi-body">
            @csrf
            @method('PATCH')

            <div class="efi-field">
                <label class="efi-label"><i class="fas fa-envelope"></i> Email address</label>
                <div class="efi-control">
                    <input type="email" name="email" id="editInfoEmail" class="efi-input" placeholder="name@institution.edu" required>
                </div>
            </div>

            <div class="efi-field">
                <label class="efi-label"><i class="fas fa-id-badge"></i> Account Category</label>
                <div class="efi-control">
                    <select name="account_type" id="editInfoAccountType" class="efi-select" required>
                        <option value="individual">Individual Staff Account</option>
                        <option value="office">Institutional Office Account</option>
                    </select>
                    <i class="fas fa-chevron-down efi-control__chev"></i>
                </div>
            </div>

            <div class="efi-field">
                <label class="efi-label"><i class="fas fa-building-columns"></i> Primary Department / Faculty / Unit</label>
                <div class="efi-control">
                    <select name="department_id" id="editInfoDepartment" class="efi-select" required>
                        <option value="" disabled>Choose department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <i class="fas fa-chevron-down efi-control__chev"></i>
                </div>
            </div>

            <div class="efi-field">
                <label class="efi-label"><i class="fas fa-layer-group"></i> Secondary Departments <span class="efi-opt">optional</span></label>
                <div class="efi-multiselect" id="editInfoSecondaryDepartments">
                    @foreach($departments as $dept)
                        <label class="efi-check" data-dept-id="{{ $dept->id }}">
                            <input type="checkbox" name="secondary_department_ids[]" value="{{ $dept->id }}">
                            <span>{{ $dept->name }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="efi-grid">
                <div class="efi-field">
                    <label class="efi-label"><i class="fas fa-user-tag"></i> Staff Category</label>
                    <div class="efi-control">
                        <select name="staff_category" id="editInfoCategory" class="efi-select" required>
                            <option value="" disabled>Choose category</option>
                            <option value="Junior Staff">Junior Staff</option>
                            <option value="Senior Staff">Senior Staff</option>
                            <option value="Senior Member (Non-Teaching)">Senior Member (Non-Teaching)</option>
                            <option value="Senior Member (Teaching)">Senior Member (Teaching)</option>
                        </select>
                        <i class="fas fa-chevron-down efi-control__chev"></i>
                    </div>
                </div>

                <div class="efi-field">
                    <label class="efi-label"><i class="fas fa-briefcase"></i> Position <span class="efi-opt">optional</span></label>
                    <div class="efi-control">
                        <select name="position_id" id="editInfoPosition" class="efi-select">
                            <option value="">No position</option>
                            @foreach($positions as $pos)
                                <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                            @endforeach
                        </select>
                        <i class="fas fa-chevron-down efi-control__chev"></i>
                    </div>
                </div>
            </div>

            <div class="efi-actions">
                <button type="button" class="efi-btn efi-btn--ghost" onclick="closeEditInfoModal()">Cancel</button>
                <button type="submit" class="efi-btn efi-btn--save">
                    <span>Save Changes</span>
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ===== Edit User Info — clean monochrome modal (scoped .efi-*) ===== */
#editInfoModal .efi-overlay{
    background:rgba(15,23,42,.42);
    -webkit-backdrop-filter:blur(6px);
    backdrop-filter:blur(6px);
    animation:efiFade .25s ease;
}
.efi-card{
    position:relative; z-index:10001;
    width:92%; max-width:480px; max-height:92vh; overflow:hidden auto;
    padding:28px 28px 24px;
    border-radius:20px;
    background:#ffffff;
    border:1px solid #ebedf1;
    box-shadow:0 24px 64px -18px rgba(15,23,42,.30), 0 2px 6px rgba(15,23,42,.05);
    color:#0f172a;
    animation:efiIn .4s cubic-bezier(.2,.8,.2,1);
    scrollbar-width:thin;
}
.efi-card::-webkit-scrollbar{width:8px}
.efi-card::-webkit-scrollbar-thumb{background:#e2e8f0; border-radius:8px}
.efi-head, .efi-body{ position:relative; z-index:1; }
.efi-close{
    position:absolute; top:14px; right:14px; width:34px; height:34px; z-index:3;
    display:grid; place-items:center; border-radius:10px;
    background:transparent; border:1px solid #ebedf1;
    color:#94a3b8; font-size:14px; cursor:pointer; transition:.2s;
}
.efi-close:hover{ background:#f5f6f8; color:#0f172a; }

.efi-head{ display:flex; align-items:center; gap:14px; margin-bottom:22px; }
.efi-avatar{
    position:relative; flex:0 0 auto; width:50px; height:50px; border-radius:14px;
    display:grid; place-items:center; font-weight:700; font-size:17px; letter-spacing:.5px;
    color:#ffffff; background:#0f172a;
}
.efi-avatar__ring{ display:none; }
.efi-title{ margin:0; font-size:1.3rem; font-weight:700; color:#0f172a; letter-spacing:-.01em; }
.efi-title i{ display:none; }
.efi-sub{ margin:2px 0 0; font-size:.875rem; color:#64748b; }

.efi-body{ display:block; }
.efi-grid{ display:grid; grid-template-columns:1fr 1fr; gap:14px; }
.efi-field{ margin-bottom:14px; }
.efi-label{ display:flex; align-items:center; gap:7px; margin-bottom:7px; font-size:.78rem; font-weight:600; color:#475569; }
.efi-label i{ font-size:.78rem; color:#94a3b8; }
.efi-opt{ font-weight:500; color:#94a3b8; font-size:.78rem; }
.efi-hint{ margin:6px 0 0; font-size:.75rem; line-height:1.4; color:#94a3b8; }
.efi-control{ position:relative; }
.efi-control__chev{ position:absolute; right:13px; top:50%; transform:translateY(-50%); font-size:.7rem; color:#94a3b8; pointer-events:none; transition:.2s; }
.efi-input, .efi-select{
    width:100%; box-sizing:border-box; padding:12px 14px;
    border-radius:12px; font-size:.92rem; font-weight:500; color:#0f172a;
    background:#ffffff; border:1px solid #e2e8f0;
    outline:none; transition:.2s ease; -webkit-appearance:none; appearance:none;
}
.efi-select{ padding-right:36px; cursor:pointer; }
.efi-input::placeholder{ color:#cbd5e1; }
.efi-input:focus, .efi-select:focus{
    border-color:#0f172a;
    box-shadow:0 0 0 3px rgba(15,23,42,.08);
}
.efi-control:focus-within .efi-control__chev{ color:#0f172a; transform:translateY(-50%) rotate(180deg); }
.efi-select option{ color:#0f172a; }

.efi-note{ display:flex; align-items:center; gap:8px; margin:4px 0 0; padding:10px 13px; border-radius:10px;
    font-size:.78rem; color:#64748b; background:#f5f6f8; border:1px solid #eef0f3; }
.efi-note i{ color:#94a3b8; }

/* Secondary-department checkbox list */
.efi-multiselect{
    max-height:168px; overflow-y:auto; padding:6px;
    border:1px solid #e2e8f0; border-radius:12px; background:#fff;
    display:flex; flex-direction:column; gap:2px; scrollbar-width:thin;
}
.efi-multiselect::-webkit-scrollbar{ width:8px }
.efi-multiselect::-webkit-scrollbar-thumb{ background:#e2e8f0; border-radius:8px }
.efi-check{
    display:flex; align-items:center; gap:10px; margin:0; padding:9px 11px;
    border-radius:9px; cursor:pointer; font-size:.9rem; font-weight:500; color:#0f172a;
    transition:background .15s ease;
}
.efi-check:hover{ background:#f5f6f8; }
.efi-check input{ width:16px; height:16px; accent-color:#0f172a; cursor:pointer; flex:0 0 auto; }
.efi-check.is-locked{ opacity:.4; cursor:not-allowed; }
.efi-check.is-locked span::after{ content:' (primary)'; color:#94a3b8; font-size:.75rem; }

.efi-actions{ display:flex; gap:10px; justify-content:flex-end; margin-top:20px; padding-top:18px; border-top:1px solid #eef0f3; }
.efi-btn{ padding:11px 22px; border-radius:11px; font-weight:600; font-size:.9rem; cursor:pointer; border:1px solid transparent; transition:.2s; }
.efi-btn--ghost{ background:#ffffff; border-color:#e2e8f0; color:#475569; }
.efi-btn--ghost:hover{ background:#f5f6f8; color:#0f172a; }
.efi-btn--save{ background:#0f172a; color:#ffffff; box-shadow:0 6px 16px -7px rgba(15,23,42,.55); }
.efi-btn--save span{ display:inline-flex; align-items:center; gap:8px; }
.efi-btn--save:hover{ background:#1e293b; transform:translateY(-1px); box-shadow:0 10px 22px -8px rgba(15,23,42,.55); }

@keyframes efiIn{ from{ opacity:0; transform:translateY(14px) scale(.985); } to{ opacity:1; transform:none; } }
@keyframes efiFade{ from{opacity:0} to{opacity:1} }
@media (max-width:520px){ .efi-grid{ grid-template-columns:1fr; } .efi-card{ padding:24px 18px 20px; } }
@media (prefers-reduced-motion:reduce){ .efi-card,.efi-overlay{ animation:none; } }
</style>

<style>
.add-user-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    z-index: 10000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.add-user-modal-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(4px);
}

.add-user-modal-content {
    position: relative;
    background: white;
    border-radius: 20px;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    z-index: 10001;
    animation: slideUp 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.add-user-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 32px;
    border-bottom: 1px solid #e2e8f0;
}

.add-user-modal-header h3 {
    font-size: 24px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.close-modal-btn {
    background: none;
    border: none;
    font-size: 24px;
    color: #64748b;
    cursor: pointer;
    padding: 5px;
    transition: color 0.2s;
}

.close-modal-btn:hover {
    color: #1e293b;
}

.add-user-modal-body {
    padding: 32px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
}

.form-group {
    margin-bottom: 1.5rem;
}

.input-container {
    position: relative;
}

.animated-input {
    width: 100%;
    padding: 18px 24px;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    font-size: 1rem;
    background: #f8fafc;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    outline: none;
    color: #0f172a;
    font-weight: 500;
    box-sizing: border-box;
}

.animated-input:focus {
    border-color: #3b82f6;
    background: white;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    transform: translateY(-1px);
}

.password-toggle {
    position: absolute;
    right: 16px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #64748b;
    cursor: pointer;
    font-size: 18px;
    padding: 5px;
}

.form-text {
    display: block;
    margin-top: 8px;
    font-size: 13px;
    color: #64748b;
}

/* Secondary-department checkbox list (Add User modal) */
.dept-multi-label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    font-size: 0.95rem;
    font-weight: 600;
    color: #334155;
}
.dept-multi-label i { color: #94a3b8; }
.dept-opt { font-weight: 500; color: #94a3b8; font-size: 0.85rem; }
.dept-multiselect {
    max-height: 180px;
    overflow-y: auto;
    padding: 8px;
    border: 2px solid #e2e8f0;
    border-radius: 16px;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 2px;
}
.dept-check {
    display: flex;
    align-items: center;
    gap: 12px;
    margin: 0;
    padding: 11px 14px;
    border-radius: 12px;
    cursor: pointer;
    font-size: 0.95rem;
    font-weight: 500;
    color: #0f172a;
    transition: background 0.15s ease;
}
.dept-check:hover { background: #eef2f7; }
.dept-check input { width: 17px; height: 17px; accent-color: #2563eb; cursor: pointer; flex: 0 0 auto; }
.dept-check.is-locked { opacity: 0.4; cursor: not-allowed; }
.dept-check.is-locked span::after { content: ' (primary)'; color: #94a3b8; font-size: 0.8rem; }

.form-actions-modal {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
    padding-top: 24px;
    border-top: 1px solid #e2e8f0;
}

.cancel-btn {
    padding: 14px 28px;
    border: 2px solid #e2e8f0;
    border-radius: 12px;
    background: white;
    color: #64748b;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.cancel-btn:hover {
    border-color: #cbd5e1;
    background: #f8fafc;
}

.submit-btn-modal {
    padding: 14px 28px;
    border: none;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
    color: white;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
}

.submit-btn-modal:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* ============================================================
   MOBILE (≤767px): Add User + Edit Info modals become BOTTOM SHEETS
   — consistent with the header avatar sheet and the filter drawer.
   Placed last so these overrides win by source order.
   ============================================================ */
@keyframes ufModalSheetUp { from { transform: translateY(100%); } to { transform: translateY(0); } }

@media (max-width: 767px) {
    /* Dock the panels to the bottom of the screen */
    .add-user-modal { align-items: flex-end; }

    /* Add User */
    .add-user-modal-content {
        width: 100%;
        max-width: 100%;
        max-height: 92dvh;
        border-radius: 22px 22px 0 0;
        animation: ufModalSheetUp .3s cubic-bezier(.4, 0, .2, 1);
        box-shadow: 0 -18px 50px rgba(15, 23, 42, 0.28);
    }
    .add-user-modal-content::before {
        content: '';
        display: block;
        width: 42px; height: 4px; border-radius: 999px;
        background: #e2e8f0; margin: 10px auto 0;
    }
    .add-user-modal-header { padding: 14px 20px; }
    .add-user-modal-body { padding: 20px; padding-bottom: calc(20px + env(safe-area-inset-bottom)); }
    .form-row { grid-template-columns: 1fr; gap: 0; }

    /* Edit Info */
    #editInfoModal.efi-modal { align-items: flex-end; }
    #editInfoModal .efi-card {
        width: 100%;
        max-width: 100%;
        max-height: 92dvh;
        border-radius: 22px 22px 0 0;
        padding-top: 22px;
        padding-bottom: calc(20px + env(safe-area-inset-bottom));
        animation: ufModalSheetUp .3s cubic-bezier(.4, 0, .2, 1);
        box-shadow: 0 -18px 50px rgba(15, 23, 42, 0.28);
    }
    #editInfoModal .efi-card::before {
        content: '';
        display: block;
        width: 42px; height: 4px; border-radius: 999px;
        background: #e2e8f0;
        position: absolute; top: 8px; left: 50%; transform: translateX(-50%);
    }
    #editInfoModal .efi-close { top: 16px; }
}
</style>

<script>
// Change page size (pagination)
function changePageSize(size) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', size);
    url.searchParams.set('page', '1');
    window.location.href = url.toString();
}

// Add User Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const addUserBtn = document.getElementById('addUserBtn');
    const addUserModal = document.getElementById('addUserModal');
    const closeModalBtn = document.getElementById('closeAddUserModal');
    const cancelBtn = document.getElementById('cancelAddUserBtn');
    
    if (addUserBtn) {
        addUserBtn.addEventListener('click', function() {
            addUserModal.style.display = 'flex';
            document.body.style.overflow = 'hidden';
        });
    }
    
    function closeModal() {
        addUserModal.style.display = 'none';
        document.body.style.overflow = 'auto';
        document.getElementById('addUserForm').reset();
        if (typeof syncSecondaryDeptLock === 'function') {
            syncSecondaryDeptLock('add-user-secondary-departments', '');
        }
    }
    
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', closeModal);
    }
    
    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }
    
    // Close on overlay click
    if (addUserModal) {
        addUserModal.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-user-modal-overlay')) {
                closeModal();
            }
        });
    }
});

function toggleAddUserPassword() {
    const passwordInput = document.getElementById('add-user-password');
    const icon = event.target.closest('.password-toggle').querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('icofont-eye');
        icon.classList.add('icofont-eye-blocked');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('icofont-eye-blocked');
        icon.classList.add('icofont-eye');
    }
}

function toggleAddUserPasswordConfirm() {
    const passwordInput = document.getElementById('add-user-password-confirm');
    const icon = event.target.closest('.password-toggle').querySelector('i');
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        icon.classList.remove('icofont-eye');
        icon.classList.add('icofont-eye-blocked');
    } else {
        passwordInput.type = 'password';
        icon.classList.remove('icofont-eye-blocked');
        icon.classList.add('icofont-eye');
    }
}

// Grey out the secondary-department checkbox that matches the chosen primary
// (a department can't be both). The server also enforces this, but this keeps
// the UI honest as the primary changes.
function syncSecondaryDeptLock(containerId, primaryId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    container.querySelectorAll('.efi-check, .dept-check').forEach(function (label) {
        const isPrimary = String(label.getAttribute('data-dept-id')) === String(primaryId);
        const box = label.querySelector('input[type="checkbox"]');
        label.classList.toggle('is-locked', isPrimary && !!primaryId);
        if (box) {
            box.disabled = isPrimary && !!primaryId;
            if (isPrimary) box.checked = false;
        }
    });
}

function openEditInfoModal(userId, userName, currentEmail, departmentId, staffCategory, positionId, accountType, secondaryIds) {
    const modal = document.getElementById('editInfoModal');
    const form = document.getElementById('editInfoForm');
    const nameLabel = document.getElementById('editInfoUserName');
    const initials = document.getElementById('editInfoInitials');
    const emailInput = document.getElementById('editInfoEmail');

    form.action = '/dashboard/users/' + userId + '/details';
    nameLabel.textContent = userName;
    if (initials) {
        const parts = (userName || '').trim().split(/\s+/).filter(Boolean);
        const text = parts.length
            ? ((parts[0][0] || '') + (parts.length > 1 ? parts[parts.length - 1][0] : '')).toUpperCase()
            : '--';
        initials.textContent = text || '--';
    }
    emailInput.value = currentEmail || '';
    document.getElementById('editInfoDepartment').value = departmentId || '';
    document.getElementById('editInfoCategory').value = staffCategory || '';
    document.getElementById('editInfoAccountType').value = accountType || 'individual';
    document.getElementById('editInfoPosition').value = positionId || '';

    // Pre-check the user's current secondary departments.
    const selected = String(secondaryIds || '').split(',').map(s => s.trim()).filter(Boolean);
    document.querySelectorAll('#editInfoSecondaryDepartments input[type="checkbox"]').forEach(function (box) {
        box.checked = selected.indexOf(String(box.value)) !== -1;
    });
    syncSecondaryDeptLock('editInfoSecondaryDepartments', departmentId);

    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    emailInput.focus();
}

// Keep the secondary list in sync when the admin changes the primary.
document.addEventListener('DOMContentLoaded', function () {
    const editPrimary = document.getElementById('editInfoDepartment');
    if (editPrimary) {
        editPrimary.addEventListener('change', function () {
            syncSecondaryDeptLock('editInfoSecondaryDepartments', this.value);
        });
    }
    const addPrimary = document.getElementById('add-user-department');
    if (addPrimary) {
        addPrimary.addEventListener('change', function () {
            syncSecondaryDeptLock('add-user-secondary-departments', this.value);
        });
    }
});

function closeEditInfoModal() {
    const modal = document.getElementById('editInfoModal');
    modal.style.display = 'none';
    document.body.style.overflow = 'auto';
}

document.addEventListener('DOMContentLoaded', function() {
    const editModal = document.getElementById('editInfoModal');
    if (editModal) {
        editModal.addEventListener('click', function(e) {
            if (e.target.classList.contains('add-user-modal-overlay')) {
                closeEditInfoModal();
            }
        });
    }
});
</script>

<script>
/* ===== Users table actions kebab (portal to <body> so the table cannot clip it) ===== */
function closeUserActions() {
    document.querySelectorAll('.uactions-menu.open').forEach(function (m) {
        m.classList.remove('open');
        m.style.position = '';
        m.style.top = '';
        m.style.left = '';
        if (m._home && m._home.cell) {
            m._home.cell.appendChild(m);
            if (m._home.toggle) { m._home.toggle.setAttribute('aria-expanded', 'false'); }
        }
    });
}

function toggleUserActions(btn, event) {
    if (event) { event.stopPropagation(); }
    var cell = btn.closest('.uactions');
    var menu = btn._menu || (cell ? cell.querySelector('.uactions-menu') : null);
    if (!menu) { return; }
    btn._menu = menu;

    var isOpen = menu.classList.contains('open');
    closeUserActions();
    if (isOpen) { return; }

    if (!menu._home) { menu._home = { cell: cell, toggle: btn }; }

    document.body.appendChild(menu);                     // portal out of the table
    menu.style.position = 'fixed';
    menu.classList.add('open');

    var r = btn.getBoundingClientRect();
    var mw = menu.offsetWidth || 210;
    var mh = menu.offsetHeight || 0;
    var left = r.right - mw;                             // right-align to the button
    if (left < 8) { left = 8; }
    var top = r.bottom + 6;
    if (top + mh > window.innerHeight - 8) {             // no room below -> flip above
        var above = r.top - 6 - mh;
        top = (above < 8) ? 8 : above;
    }
    menu.style.left = left + 'px';
    menu.style.top = top + 'px';
    btn.setAttribute('aria-expanded', 'true');
}

document.addEventListener('click', function (e) {
    if (!e.target.closest('.uactions') && !e.target.closest('.uactions-menu')) {
        closeUserActions();
    }
});
document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') { closeUserActions(); }
});
window.addEventListener('scroll', closeUserActions, true);
window.addEventListener('resize', closeUserActions);
</script>

@endsection

<script>
// View toggle + live search for Users Management
(function() {
    let currentView = 'grid';
    let searchDebounce = null;
    let activeRequest = null;

    function applyView() {
        const gridView = document.getElementById('gridView');
        const tableView = document.getElementById('tableView');
        const gridViewBtn = document.getElementById('gridViewBtn');
        const tableViewBtn = document.getElementById('tableViewBtn');
        if (!gridView || !tableView || !gridViewBtn || !tableViewBtn) return;

        if (currentView === 'table') {
            tableViewBtn.classList.add('active');
            gridViewBtn.classList.remove('active');
            gridView.style.display = 'none';
            tableView.style.display = 'block';
        } else {
            gridViewBtn.classList.add('active');
            tableViewBtn.classList.remove('active');
            gridView.style.display = 'grid';
            tableView.style.display = 'none';
        }
    }

    function bindViewToggle() {
        const gridViewBtn = document.getElementById('gridViewBtn');
        const tableViewBtn = document.getElementById('tableViewBtn');
        if (gridViewBtn) gridViewBtn.addEventListener('click', function() { currentView = 'grid'; applyView(); });
        if (tableViewBtn) tableViewBtn.addEventListener('click', function() { currentView = 'table'; applyView(); });
    }

    function runLiveSearch() {
        const form = document.getElementById('usersSearchForm');
        const container = document.getElementById('usersResultsContainer');
        const spinner = document.getElementById('searchSpinner');
        const input = document.getElementById('searchInput');
        if (!form || !container) return;

        const params = new URLSearchParams(new FormData(form));
        params.set('page', '1');
        const url = form.action + '?' + params.toString();

        if (activeRequest) activeRequest.abort();
        const controller = new AbortController();
        activeRequest = controller;

        container.classList.add('is-loading');
        if (spinner) spinner.classList.add('visible');
        if (input) input.classList.add('is-searching');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
            signal: controller.signal,
            credentials: 'same-origin'
        })
        .then(function(r) { return r.text(); })
        .then(function(html) {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const fresh = doc.getElementById('usersResultsContainer');
            if (fresh) {
                container.innerHTML = fresh.innerHTML;
                applyView();
            }
            window.history.replaceState({}, '', url);
        })
        .catch(function(err) {
            if (err.name !== 'AbortError') console.error('Live search failed:', err);
        })
        .finally(function() {
            if (activeRequest === controller) {
                container.classList.remove('is-loading');
                if (spinner) spinner.classList.remove('visible');
                if (input) input.classList.remove('is-searching');
                activeRequest = null;
            }
        });
    }

    function scheduleSearch() {
        clearTimeout(searchDebounce);
        searchDebounce = setTimeout(runLiveSearch, 300);
    }

    function updateClearButton() {
        const input = document.getElementById('searchInput');
        const clearBtn = document.getElementById('clearSearchBtn');
        if (!input || !clearBtn) return;
        clearBtn.style.display = input.value.trim() !== '' ? 'flex' : 'none';
    }

    /* ---------- Smart filter: labels for active chips ---------- */
    const CATEGORY_ICON = 'fa-user-tag';
    function deptLabel(id) {
        const opt = document.querySelector('#deptFilter option[value="' + CSS.escape(id) + '"]');
        return opt ? opt.textContent.trim() : id;
    }
    function positionLabel(id) {
        const opt = document.querySelector('#positionFilter option[value="' + CSS.escape(id) + '"]');
        return opt ? opt.textContent.trim() : id;
    }

    function collectActiveFacets() {
        const form = document.getElementById('usersSearchForm');
        if (!form) return [];
        const facets = [];
        form.querySelectorAll('input[name="staff_category[]"]:checked').forEach(function (box) {
            facets.push({ key: 'cat:' + box.value, icon: 'fa-user-tag', label: box.value,
                clear: function () {
                    box.checked = false;
                    const l = box.closest('.uf-chip'); if (l) l.classList.remove('is-checked');
                } });
        });
        const dept = document.getElementById('deptFilter');
        if (dept && dept.value) {
            facets.push({ key: 'dept', icon: 'fa-building-columns', label: deptLabel(dept.value),
                clear: function () { dept.value = ''; } });
        }
        const pos = document.getElementById('positionFilter');
        if (pos && pos.value) {
            facets.push({ key: 'pos', icon: 'fa-briefcase', label: positionLabel(pos.value),
                clear: function () { pos.value = ''; } });
        }
        const acct = document.getElementById('accountTypeInput');
        if (acct && acct.value) {
            const label = acct.value === 'office' ? 'Office accounts' : 'Individual accounts';
            facets.push({ key: 'acct', icon: 'fa-id-badge', label: label, clear: function () {
                acct.value = '';
                document.querySelectorAll('#accountTypeSegment button').forEach(function (b) {
                    b.classList.toggle('active', b.getAttribute('data-account') === '');
                });
            }});
        }
        return facets;
    }

    function updateFilterBadge(count) {
        const badge = document.getElementById('filterCountBadge');
        const btn = document.getElementById('openFiltersBtn');
        if (!badge) return;
        if (count > 0) { badge.textContent = count; badge.hidden = false; if (btn) btn.classList.add('has-filters'); }
        else { badge.hidden = true; if (btn) btn.classList.remove('has-filters'); }
    }

    function renderActiveChips() {
        const wrap = document.getElementById('activeChips');
        if (!wrap) return;
        const facets = collectActiveFacets();
        updateFilterBadge(facets.length);
        wrap.innerHTML = '';
        if (!facets.length) return;
        facets.forEach(function (f) {
            const chip = document.createElement('span');
            chip.className = 'uf-active-chip';
            chip.innerHTML = '<i class="fas ' + f.icon + '"></i>' +
                '<span>' + f.label.replace(/</g, '&lt;') + '</span>' +
                '<button type="button" class="uf-chip-x" aria-label="Remove filter"><i class="fas fa-times"></i></button>';
            chip.querySelector('.uf-chip-x').addEventListener('click', function () {
                f.clear();
                renderActiveChips();
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
            wrap.appendChild(chip);
        });
        const clearAll = document.createElement('button');
        clearAll.type = 'button';
        clearAll.className = 'uf-active-chip uf-clear-all';
        clearAll.textContent = 'Clear all';
        clearAll.addEventListener('click', clearAllFilters);
        wrap.appendChild(clearAll);
    }

    function clearAllFilters() {
        document.querySelectorAll('#usersSearchForm input[name="staff_category[]"]').forEach(function (b) {
            b.checked = false;
            const l = b.closest('.uf-chip'); if (l) l.classList.remove('is-checked');
        });
        const dept = document.getElementById('deptFilter'); if (dept) dept.value = '';
        const pos = document.getElementById('positionFilter'); if (pos) pos.value = '';
        const acct = document.getElementById('accountTypeInput'); if (acct) acct.value = '';
        document.querySelectorAll('#accountTypeSegment button').forEach(function (b) {
            b.classList.toggle('active', b.getAttribute('data-account') === '');
        });
        renderActiveChips();
        clearTimeout(searchDebounce);
        runLiveSearch();
    }

    /* ---------- Filter drawer open/close (left drawer ⇄ bottom sheet) ----------
       Same mechanics as the folders .mdrawer / header avatar sheet: toggle .open
       on the backdrop + panel and lock body scroll. CSS decides side-drawer vs
       bottom-sheet by breakpoint. */
    function openFilterSheet() {
        const sheet = document.getElementById('filterSheet');
        const backdrop = document.getElementById('filterBackdrop');
        const btn = document.getElementById('openFiltersBtn');
        if (!sheet) return;
        sheet.classList.add('open');
        sheet.setAttribute('aria-hidden', 'false');
        if (backdrop) backdrop.classList.add('open');
        if (btn) btn.setAttribute('aria-expanded', 'true');
        document.body.classList.add('uf-lock');
    }
    function closeFilterSheet() {
        const sheet = document.getElementById('filterSheet');
        const backdrop = document.getElementById('filterBackdrop');
        const btn = document.getElementById('openFiltersBtn');
        if (!sheet) return;
        sheet.classList.remove('open');
        sheet.setAttribute('aria-hidden', 'true');
        if (backdrop) backdrop.classList.remove('open');
        if (btn) btn.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('uf-lock');
    }

    function bindSmartFilters() {
        const statusInput = document.getElementById('statusFilterInput');

        // Status segmented control
        document.querySelectorAll('#statusSegment button').forEach(function (b) {
            b.addEventListener('click', function () {
                if (statusInput) statusInput.value = b.getAttribute('data-status');
                document.querySelectorAll('#statusSegment button').forEach(function (x) { x.classList.remove('active'); });
                b.classList.add('active');
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        });

        // Account type segmented control (writes to hidden input)
        document.querySelectorAll('#accountTypeSegment button').forEach(function (b) {
            b.addEventListener('click', function () {
                const acct = document.getElementById('accountTypeInput');
                if (acct) acct.value = b.getAttribute('data-account');
                document.querySelectorAll('#accountTypeSegment button').forEach(function (x) { x.classList.remove('active'); });
                b.classList.add('active');
                renderActiveChips();
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        });

        // Sort select
        const sortSelect = document.getElementById('sortSelect');
        if (sortSelect) sortSelect.addEventListener('change', function () { clearTimeout(searchDebounce); runLiveSearch(); });

        // Staff category chips + dept/position selects → live
        document.querySelectorAll('#usersSearchForm input[name="staff_category[]"]').forEach(function (box) {
            const label = box.closest('.uf-chip');
            if (label) label.classList.toggle('is-checked', box.checked); // initial state (:has fallback)
            box.addEventListener('change', function () {
                if (label) label.classList.toggle('is-checked', box.checked);
                renderActiveChips(); clearTimeout(searchDebounce); runLiveSearch();
            });
        });
        ['deptFilter', 'positionFilter'].forEach(function (id) {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', function () { renderActiveChips(); clearTimeout(searchDebounce); runLiveSearch(); });
        });

        // Sheet triggers
        const openBtn = document.getElementById('openFiltersBtn');
        const closeBtn = document.getElementById('closeFiltersBtn');
        const backdrop = document.getElementById('filterBackdrop');
        const applyBtn = document.getElementById('applyFiltersBtn');
        const clearBtn = document.getElementById('clearFiltersBtn');
        if (openBtn) openBtn.addEventListener('click', function () {
            const sheet = document.getElementById('filterSheet');
            if (sheet && sheet.classList.contains('open')) closeFilterSheet(); else openFilterSheet();
        });
        if (closeBtn) closeBtn.addEventListener('click', closeFilterSheet);
        if (backdrop) backdrop.addEventListener('click', closeFilterSheet);
        if (applyBtn) applyBtn.addEventListener('click', function () { closeFilterSheet(); clearTimeout(searchDebounce); runLiveSearch(); });
        if (clearBtn) clearBtn.addEventListener('click', clearAllFilters);

        // Close on Escape (backdrop click already wired above)
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeFilterSheet(); });
    }

    document.addEventListener('DOMContentLoaded', function() {
        bindViewToggle();
        bindSmartFilters();
        renderActiveChips();
        updateClearButton();

        const input = document.getElementById('searchInput');
        const form = document.getElementById('usersSearchForm');
        const clearBtn = document.getElementById('clearSearchBtn');

        if (input) {
            input.addEventListener('input', function() {
                updateClearButton();
                scheduleSearch();
            });
            input.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && input.value) {
                    input.value = '';
                    updateClearButton();
                    scheduleSearch();
                }
            });
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                if (!input) return;
                input.value = '';
                updateClearButton();
                input.focus();
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        }
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                clearTimeout(searchDebounce);
                runLiveSearch();
            });
        }
    });
})();

// Confirmation modal function for user deletion
function confirmDeleteUser(userId, userName) {
    confirmDelete(
        `Are you sure you want to delete "${userName}"?`,
        function() {
            // Try both possible form IDs (card view and table view)
            const cardForm = document.getElementById('delete-user-form-' + userId);
            const tableForm = document.getElementById('delete-user-table-form-' + userId);
            
            if (cardForm) {
                cardForm.submit();
            } else if (tableForm) {
                tableForm.submit();
            }
        }
    );
}
</script>
