@extends('layout.app')

@push('styles')
<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
/* ── Shared dashboard design language (ps-*). This page references these classes
   for its header / alerts / section card but — unlike positions & offices — never
   defined them locally, so they rendered unstyled. Bring in the same subset so the
   page is polished and consistent on every breakpoint. ── */
.ps-wrap, .ps-wrap * { font-family:'Outfit', sans-serif !important; box-sizing:border-box; }
/* icofont glyphs must keep their own face, or the blanket Outfit rule above
   renders every icon as an empty box (doubled selector beats `.ps-wrap *`). */
.ps-wrap [class^="icofont-"], .ps-wrap [class*=" icofont-"] { font-family:IcoFont !important; }
.ps-wrap { max-width:900px; padding:4px 0 60px; }
.ps-page-header { display:flex; align-items:flex-start; justify-content:space-between; gap:16px; margin-bottom:24px; padding-bottom:24px; border-bottom:1.5px solid #ebebeb; }
.ps-page-title { font-size:2rem; font-weight:800; color:#0c0c0c; letter-spacing:-0.045em; line-height:1.1; margin:0 0 4px; display:inline-flex; flex-direction:column; }
.ps-title-bar { display:block; width:2.4rem; height:3.5px; background:#0c0c0c; border-radius:3px; margin-top:9px; }
.ps-page-sub { margin:12px 0 0; font-size:0.9rem; color:#8a8fa0; font-weight:400; }
.ps-alert { display:flex; align-items:flex-start; gap:10px; padding:12px 14px; border-radius:10px; margin-bottom:16px; font-size:0.875rem; font-weight:500; border:1.5px solid transparent; }
.ps-alert--ok  { background:#f0fdf4; border-color:#bbf7d0; color:#15803d; }
.ps-alert--err { background:#fef2f2; border-color:#fecaca; color:#b91c1c; }
.ps-alert__x { margin-left:auto; background:none; border:none; cursor:pointer; opacity:.45; color:inherit; padding:0; display:flex; align-items:center; font-size:20px; line-height:1; }
.ps-alert__x:hover { opacity:1; }
.ps-card { background:#fff; border:1.5px solid #ebebeb; border-radius:16px; overflow:hidden; }
.ps-card__hd { padding:18px 24px 14px; border-bottom:1.5px solid #f5f5f5; display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.ps-card__title { font-size:0.95rem; font-weight:700; color:#0c0c0c; letter-spacing:-0.02em; margin:0 0 4px; display:inline-flex; flex-direction:column; }
.ps-card__bar { display:block; width:1.7rem; height:2.5px; background:#0c0c0c; border-radius:2px; margin-top:6px; }
.ps-card__count { margin:8px 0 0; font-size:0.8rem; color:#b0b5c0; }

.lh-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(280px, 1fr)); gap:20px; }
.lh-card { border:1px solid #e2e8f0; border-radius:14px; overflow:hidden; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,0.05); display:flex; flex-direction:column; transition:all 0.2s ease; }
.lh-card:hover { box-shadow:0 6px 20px rgba(0,0,0,0.08); transform:translateY(-2px); }
.lh-card.inactive { opacity:0.55; }
.lh-card-preview { height:160px; background:#f8fafc; display:flex; align-items:center; justify-content:center; overflow:hidden; border-bottom:1px solid #e2e8f0; }
.lh-card-preview img { width:100%; height:100%; object-fit:contain; object-position:center; }
.lh-card-body { padding:14px 16px; display:flex; flex-direction:column; gap:6px; flex:1; }
.lh-card-title { font-size:15px; font-weight:700; color:#1e293b; margin:0; }
.lh-card-desc { font-size:12px; color:#64748b; margin:0; }
.lh-card-meta { display:flex; gap:8px; align-items:center; flex-wrap:wrap; margin-top:6px; }
.lh-badge { font-size:10px; font-weight:600; padding:3px 8px; border-radius:10px; text-transform:uppercase; letter-spacing:0.4px; }
.lh-badge--active { background:#dcfce7; color:#15803d; }
.lh-badge--inactive { background:#fee2e2; color:#b91c1c; }
.lh-badge--order { background:#e0e7ff; color:#3730a3; }
.lh-badge--scope { background:#fef3c7; color:#92400e; }
.lh-badge--scope.is-global { background:#e0f2fe; color:#075985; }
.lh-card-actions { padding:10px 16px; border-top:1px solid #f1f5f9; display:flex; gap:8px; flex-wrap:wrap; background:#fafbfc; }
.lh-btn { display:inline-flex; align-items:center; gap:6px; padding:6px 12px; border-radius:8px; font-size:12px; font-weight:600; border:1px solid transparent; cursor:pointer; transition:all 0.15s ease; text-decoration:none; }
.lh-btn--primary { background:#1a4a9b; color:#fff; }
.lh-btn--primary:hover { background:#143a7a; color:#fff; }
.lh-btn--ghost { background:#fff; color:#475569; border-color:#cbd5e1; }
.lh-btn--ghost:hover { border-color:#94a3b8; color:#1e293b; }
.lh-btn--danger { background:#fff; color:#b91c1c; border-color:#fecaca; }
.lh-btn--danger:hover { background:#fee2e2; }
.lh-btn--warn { background:#fff; color:#a16207; border-color:#fde68a; }
.lh-btn--warn:hover { background:#fef9c3; }
.lh-empty { padding:60px 20px; text-align:center; color:#64748b; }
.lh-empty i { font-size:48px; color:#cbd5e1; display:block; margin-bottom:12px; }

/* Upload form */
.lh-upload-card { background:#fff; border:1px dashed #c7d7f5; border-radius:14px; padding:24px; margin-bottom:24px; }
.lh-upload-grid { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
.lh-upload-grid--single { grid-template-columns:1fr; }
.lh-field label { display:block; font-size:12px; font-weight:600; color:#475569; margin-bottom:6px; text-transform:uppercase; letter-spacing:0.3px; }
.lh-field input[type=text],
.lh-field input[type=file],
.lh-field select { width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:#fff; }
.lh-field input[type=text]:focus,
.lh-field input[type=file]:focus,
.lh-field select:focus { outline:none; border-color:#1a4a9b; box-shadow:0 0 0 3px rgba(26,74,155,0.12); }
.lh-upload-actions { display:flex; justify-content:flex-end; margin-top:16px; }

/* Edit modal — lives outside .ps-wrap, so opt it into Outfit for parity with the
   rest of the page (icofont icons keep their face via icofont.css's own !important). */
.lh-modal, .lh-modal * { font-family:'Outfit', sans-serif; }
.lh-modal-backdrop { display:none; position:fixed; inset:0; background:rgba(15,23,42,0.55); z-index:9999; align-items:center; justify-content:center; padding:20px; }
.lh-modal-backdrop.open { display:flex; }
.lh-modal { background:#fff; border-radius:14px; width:100%; max-width:520px; max-height:90vh; overflow-y:auto; box-shadow:0 20px 60px rgba(0,0,0,0.25); }
.lh-modal-hd { padding:18px 22px; border-bottom:1px solid #e2e8f0; display:flex; justify-content:space-between; align-items:center; }
.lh-modal-hd h3 { margin:0; font-size:17px; font-weight:700; color:#1e293b; }
.lh-modal-x { background:transparent; border:0; font-size:22px; color:#64748b; cursor:pointer; line-height:1; }
.lh-modal-bd { padding:22px; }
.lh-modal-ft { padding:14px 22px; border-top:1px solid #e2e8f0; display:flex; justify-content:flex-end; gap:8px; }
.lh-current-preview { margin-top:8px; padding:8px; border:1px solid #e2e8f0; border-radius:8px; background:#f8fafc; }
.lh-current-preview img { width:100%; height:100px; object-fit:contain; }

/* Reorder handles */
.lh-card[draggable=true] { cursor:grab; }
.lh-card.dragging { opacity:0.4; }

/* Mobile-only affordances — hidden on desktop (the upload form is inline there
   and reordering is done by dragging cards). */
.lh-upload-fab { display:none; }
.lh-drawer-hd  { display:none; }
.lh-reorder    { display:none; }

@media (max-width: 600px) {
    .lh-upload-grid { grid-template-columns:1fr; }
}

/* ===================== MOBILE APP-LIKE LAYOUT (≤767) ===================== */
@media (max-width: 767px) {
    /* De-squish trio (official Mobile breakpoint). Below 992 the sidebar is an
       off-canvas drawer, so the content column is full-width; collapse the
       container/row/col gutters and give the wrap one clean 16px gutter. */
    .dashboardarea .container-fluid.full__width__padding { padding-left:0; padding-right:0; }
    .dashboardarea .dashboard > .container-fluid > .row { margin-left:0; margin-right:0; }
    .dashboardarea .dashboard > .container-fluid > .row > [class*="col-"] { padding-left:0; padding-right:0; }
    .ps-wrap { padding-left:16px; padding-right:16px; }

    .lh-upload-grid { grid-template-columns:1fr; }

    /* iOS Safari zooms on focus of any control < 16px — bump all controls. */
    .lh-field input[type=text],
    .lh-field input[type=file],
    .lh-field select { font-size:16px; padding:12px 13px; }

    /* ---- Upload: a prominent action button that opens the form as a
            full-screen slide-in drawer, decluttering the top of the page. ---- */
    .lh-upload-fab {
        display:flex; align-items:center; justify-content:center; gap:9px;
        width:100%; margin-bottom:18px;
        padding:15px 18px; border:none; border-radius:14px;
        background:linear-gradient(135deg, #1a4a9b, #143a7a); color:#fff;
        font-size:15px; font-weight:700; cursor:pointer; font-family:inherit;
        box-shadow:0 8px 22px rgba(26,74,155,0.28);
    }
    .lh-upload-fab i { font-size:17px; }

    .lh-upload-card { display:none; }              /* hidden until opened */
    .lh-upload-card.open {
        display:flex; flex-direction:column;
        position:fixed; inset:0; z-index:10000;
        margin:0; padding:0; border:none; border-radius:0; background:#fff;
        animation:lhDrawerIn .3s cubic-bezier(.22,.61,.36,1);
    }
    @keyframes lhDrawerIn { from { transform:translateX(100%); } to { transform:none; } }

    .lh-drawer-hd {
        display:flex; align-items:center; justify-content:space-between; gap:12px;
        flex-shrink:0; padding:16px; border-bottom:1px solid #e2e8f0; background:#fff;
    }
    .lh-drawer-hd h3 { margin:0; font-size:17px; font-weight:700; color:#1e293b; }
    .lh-drawer-x {
        width:36px; height:36px; border-radius:10px; flex-shrink:0;
        border:1px solid #e2e8f0; background:#fff; color:#64748b;
        font-size:20px; line-height:1; cursor:pointer;
        display:flex; align-items:center; justify-content:center;
    }
    .lh-upload-card.open > h3 { display:none; }     /* redundant with drawer header */
    .lh-upload-card.open > p  { margin:14px 16px 0 !important; }  /* beat the inline margin */
    .lh-upload-card.open form {
        flex:1; display:flex; flex-direction:column; min-height:0;
        overflow-y:auto; padding:16px;
    }
    .lh-upload-card.open .lh-upload-actions {
        position:sticky; bottom:0; margin:18px -16px 0;
        padding:14px 16px calc(14px + env(safe-area-inset-bottom));
        border-top:1px solid #e2e8f0; background:#fff;
    }
    .lh-upload-card.open .lh-upload-actions .lh-btn { flex:1; justify-content:center; padding:13px; font-size:15px; }

    /* Tighter section-card density on phones. */
    .ps-card__hd { padding:16px 16px 12px; }
    .lh-list-body { padding:14px !important; }   /* beat the inline padding:20px */

    /* ---- Cards → clean single-column app list items ---- */
    .lh-grid { grid-template-columns:1fr; gap:14px; }
    .lh-card { position:relative; border-radius:16px; }
    .lh-card-preview { height:130px; }
    .lh-card-body { padding:14px 16px; }
    .lh-card-title { font-size:16px; }

    /* Reorder chevrons (top-right of the preview) — HTML5 drag doesn't work on
       touch, so give a tap alternative that reuses the same persist endpoint. */
    .lh-reorder {
        display:flex; flex-direction:column; gap:4px;
        position:absolute; top:10px; right:10px; z-index:2;
    }
    .lh-reorder button {
        width:34px; height:30px; border:none; border-radius:9px;
        background:rgba(255,255,255,0.92); color:#334155;
        box-shadow:0 2px 8px rgba(15,23,42,0.16);
        display:flex; align-items:center; justify-content:center; cursor:pointer;
        -webkit-backdrop-filter:blur(4px); backdrop-filter:blur(4px);
    }
    .lh-reorder button:active { transform:scale(0.92); }
    .lh-reorder button:disabled { opacity:0.35; cursor:default; }

    /* Action buttons → icon-over-label, equal width (mobile toolbar feel) */
    .lh-card-actions { gap:8px; padding:10px 12px; }
    .lh-card-actions form { flex:1; display:flex; }
    .lh-card-actions .lh-btn {
        flex:1; width:100%; flex-direction:column; gap:4px;
        padding:9px 4px; font-size:10.5px; line-height:1.2; border-radius:10px;
    }
    .lh-card-actions .lh-btn i { font-size:17px; }

    /* Drag hint is desktop-only (touch can't HTML5-drag). */
    .lh-reorder-hint { display:none; }

    /* ---- Edit modal → full-screen slide-in drawer ---- */
    .lh-modal-backdrop { padding:0; align-items:stretch; }
    .lh-modal {
        width:100%; max-width:100%; height:100dvh; max-height:100dvh;
        border-radius:0; overflow:hidden;
        display:flex; flex-direction:column;
        animation:lhDrawerIn .3s cubic-bezier(.22,.61,.36,1);
    }
    .lh-modal-hd { flex-shrink:0; }
    .lh-modal form { flex:1; display:flex; flex-direction:column; min-height:0; }
    .lh-modal-bd { flex:1; overflow-y:auto; }
    .lh-modal-ft { flex-shrink:0; padding-bottom:calc(14px + env(safe-area-inset-bottom)); }
    .lh-modal-ft .lh-btn { flex:1; justify-content:center; padding:13px; font-size:15px; }
}
</style>
@endpush

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100">
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="ps-wrap">

                        {{-- Page header --}}
                        <div class="ps-page-header">
                            <div>
                                <h1 class="ps-page-title">System Letterheads<span class="ps-title-bar"></span></h1>
                                <p class="ps-page-sub">Upload and manage the official letterheads that staff can pick when composing internal memos.</p>
                            </div>
                        </div>

                        {{-- Alerts --}}
                        @if(session('success'))
                        <div class="ps-alert ps-alert--ok">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            <span>{{ session('success') }}</span>
                            <button class="ps-alert__x" onclick="this.closest('.ps-alert').remove()">&times;</button>
                        </div>
                        @endif

                        @if(session('error'))
                        <div class="ps-alert ps-alert--err">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span>{{ session('error') }}</span>
                            <button class="ps-alert__x" onclick="this.closest('.ps-alert').remove()">&times;</button>
                        </div>
                        @endif

                        @if($errors->any())
                        <div class="ps-alert ps-alert--err">
                            <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                            <button class="ps-alert__x" onclick="this.closest('.ps-alert').remove()">&times;</button>
                        </div>
                        @endif

                        {{-- Upload form --}}
                        {{-- Mobile: a prominent button opens the form as a full-screen drawer. --}}
                        <button type="button" class="lh-upload-fab" onclick="openUploadPanel()">
                            <i class="icofont-cloud-upload"></i> Upload new letterhead
                        </button>
                        <div class="lh-upload-card" id="lhUploadPanel">
                            {{-- Mobile-only drawer header (hidden on desktop). --}}
                            <div class="lh-drawer-hd">
                                <h3>Upload letterhead</h3>
                                <button type="button" class="lh-drawer-x" onclick="closeUploadPanel()" aria-label="Close">&times;</button>
                            </div>
                            <h3 style="margin:0 0 4px 0; font-size:16px; font-weight:700; color:#1e293b;">
                                <i class="icofont-cloud-upload"></i> Upload new letterhead
                            </h3>
                            <p style="margin:0 0 16px 0; font-size:13px; color:#64748b;">
                                Recommended: a wide banner image (e.g. 1600&times;300 px) saved as PNG or JPG. Max 5&nbsp;MB.
                            </p>
                            <form method="POST" action="{{ route('dashboard.system-letterheads.store') }}" enctype="multipart/form-data">
                                @csrf
                                <div class="lh-upload-grid">
                                    <div class="lh-field">
                                        <label for="lh-name">Letterhead name <span style="color:#dc2626">*</span></label>
                                        <input type="text" id="lh-name" name="name" maxlength="255" required value="{{ old('name') }}" placeholder="e.g. Faculty of Engineering">
                                    </div>
                                    <div class="lh-field">
                                        <label for="lh-desc">Short description</label>
                                        <input type="text" id="lh-desc" name="description" maxlength="255" value="{{ old('description') }}" placeholder="e.g. For engineering faculty memos">
                                    </div>
                                </div>
                                {{-- Scope: who is allowed to use this letterhead --}}
                                <div class="lh-upload-grid" style="margin-top:14px;">
                                    <div class="lh-field">
                                        <label for="lh-scope-type">Who can use this?</label>
                                        <select id="lh-scope-type" name="scope_type" onchange="lhToggleScope('lh')">
                                            <option value="global" {{ old('scope_type', 'global') === 'global' ? 'selected' : '' }}>Everyone (global letterhead)</option>
                                            <option value="department" {{ old('scope_type') === 'department' ? 'selected' : '' }}>A specific faculty / department / unit</option>
                                            <option value="office" {{ old('scope_type') === 'office' ? 'selected' : '' }}>A specific office</option>
                                        </select>
                                        <p style="font-size:11px; color:#64748b; margin:6px 0 0 0;">Only members of the chosen unit (plus its parent faculty) will see it in the composer. Global appears for everyone.</p>
                                    </div>
                                    <div class="lh-field lh-scope-dep lh-scope-dep--department" data-prefix="lh" style="{{ old('scope_type') === 'department' ? '' : 'display:none;' }}">
                                        <label for="lh-scope-department">Faculty / department / unit <span style="color:#dc2626">*</span></label>
                                        <select id="lh-scope-department" name="scope_department_id">
                                            <option value="">— Select —</option>
                                            @foreach($departments as $d)
                                                <option value="{{ $d->id }}" {{ (string) old('scope_department_id') === (string) $d->id ? 'selected' : '' }}>{{ $d->name }} ({{ ucfirst($d->type) }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="lh-field lh-scope-dep lh-scope-dep--office" data-prefix="lh" style="{{ old('scope_type') === 'office' ? '' : 'display:none;' }}">
                                        <label for="lh-scope-office">Office <span style="color:#dc2626">*</span></label>
                                        <select id="lh-scope-office" name="scope_office_id">
                                            <option value="">— Select —</option>
                                            @foreach($offices as $o)
                                                <option value="{{ $o->id }}" {{ (string) old('scope_office_id') === (string) $o->id ? 'selected' : '' }}>{{ $o->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>

                                <div class="lh-upload-grid lh-upload-grid--single" style="margin-top:14px;">
                                    <div class="lh-field">
                                        <label for="lh-image">Letterhead image <span style="color:#dc2626">*</span></label>
                                        <input type="file" id="lh-image" name="image" accept="image/png,image/jpeg,image/jpg,image/webp" required>
                                    </div>
                                </div>
                                <div class="lh-upload-actions">
                                    <button type="submit" class="lh-btn lh-btn--primary">
                                        <i class="icofont-upload"></i> Upload letterhead
                                    </button>
                                </div>
                            </form>
                        </div>

                        {{-- Letterhead list --}}
                        <div class="ps-card">
                            <div class="ps-card__hd">
                                <div>
                                    <h2 class="ps-card__title">All letterheads<span class="ps-card__bar"></span></h2>
                                    <p class="ps-card__count">
                                        {{ $letterheads->count() }} {{ $letterheads->count() === 1 ? 'letterhead' : 'letterheads' }}
                                        &middot; {{ $letterheads->where('is_active', true)->count() }} visible to staff
                                    </p>
                                </div>
                                <p class="lh-reorder-hint" style="margin:0; font-size:12px; color:#64748b;">
                                    <i class="icofont-info-circle"></i> Drag cards to reorder how they appear in the memo composer.
                                </p>
                            </div>

                            <div class="lh-list-body" style="padding:20px;">
                            @if($letterheads->count() === 0)
                                <div class="lh-empty">
                                    <i class="icofont-paper"></i>
                                    <p>No letterheads yet. Upload your first one using the form above.</p>
                                </div>
                            @else
                                <div class="lh-grid" id="lh-grid">
                                @foreach($letterheads as $lh)
                                    <div class="lh-card {{ $lh->is_active ? '' : 'inactive' }}" draggable="true" data-id="{{ $lh->id }}">
                                        <div class="lh-card-preview">
                                            <img src="{{ $lh->image_url }}" alt="{{ $lh->name }}">
                                        </div>
                                        {{-- Mobile-only reorder (touch can't HTML5-drag). --}}
                                        <div class="lh-reorder">
                                            <button type="button" class="lh-move-up" aria-label="Move up"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"/></svg></button>
                                            <button type="button" class="lh-move-down" aria-label="Move down"><svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg></button>
                                        </div>
                                        <div class="lh-card-body">
                                            <h4 class="lh-card-title">{{ $lh->name }}</h4>
                                            @if($lh->description)
                                                <p class="lh-card-desc">{{ $lh->description }}</p>
                                            @endif
                                            <div class="lh-card-meta">
                                                <span class="lh-badge {{ $lh->is_active ? 'lh-badge--active' : 'lh-badge--inactive' }}">
                                                    {{ $lh->is_active ? 'Active' : 'Hidden' }}
                                                </span>
                                                <span class="lh-badge lh-badge--order">Order #{{ $lh->display_order }}</span>
                                                <span class="lh-badge lh-badge--scope {{ $lh->scope_type ? '' : 'is-global' }}">
                                                    <i class="icofont-{{ $lh->scope_type ? 'building' : 'globe' }}"></i> {{ $lh->scope_label }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="lh-card-actions">
                                            <button type="button" class="lh-btn lh-btn--ghost"
                                                    onclick="openEditModal({{ $lh->id }}, '{{ addslashes($lh->name) }}', '{{ addslashes($lh->description ?? '') }}', '{{ $lh->image_url }}', '{{ $lh->scope_type ?? 'global' }}', '{{ $lh->scope_id }}')">
                                                <i class="icofont-edit"></i> Edit
                                            </button>

                                            <form method="POST" action="{{ route('dashboard.system-letterheads.toggle', $lh->id) }}" style="display:inline;">
                                                @csrf
                                                <button type="submit" class="lh-btn lh-btn--warn">
                                                    @if($lh->is_active)
                                                        <i class="icofont-eye-blocked"></i> Deactivate
                                                    @else
                                                        <i class="icofont-eye"></i> Activate
                                                    @endif
                                                </button>
                                            </form>

                                            <form method="POST" action="{{ route('dashboard.system-letterheads.destroy', $lh->id) }}" style="display:inline;"
                                                  onsubmit="return confirm('Delete &quot;{{ addslashes($lh->name) }}&quot;? This cannot be undone. If this letterhead is still used by past memos, deletion will be blocked.');">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="lh-btn lh-btn--danger">
                                                    <i class="icofont-trash"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
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

{{-- Edit modal --}}
<div class="lh-modal-backdrop" id="lh-edit-modal" onclick="if(event.target===this) closeEditModal()">
    <div class="lh-modal">
        <div class="lh-modal-hd">
            <h3>Edit letterhead</h3>
            <button type="button" class="lh-modal-x" onclick="closeEditModal()">&times;</button>
        </div>
        <form method="POST" id="lh-edit-form" enctype="multipart/form-data">
            @csrf @method('PUT')
            <div class="lh-modal-bd">
                <div class="lh-field" style="margin-bottom:14px;">
                    <label for="lh-edit-name">Letterhead name <span style="color:#dc2626">*</span></label>
                    <input type="text" id="lh-edit-name" name="name" maxlength="255" required>
                </div>
                <div class="lh-field" style="margin-bottom:14px;">
                    <label for="lh-edit-desc">Short description</label>
                    <input type="text" id="lh-edit-desc" name="description" maxlength="255">
                </div>
                <div class="lh-field" style="margin-bottom:14px;">
                    <label for="lh-edit-scope-type">Who can use this?</label>
                    <select id="lh-edit-scope-type" name="scope_type" onchange="lhToggleScope('lh-edit')">
                        <option value="global">Everyone (global letterhead)</option>
                        <option value="department">A specific faculty / department / unit</option>
                        <option value="office">A specific office</option>
                    </select>
                </div>
                <div class="lh-field lh-scope-dep lh-scope-dep--department" data-prefix="lh-edit" style="margin-bottom:14px; display:none;">
                    <label for="lh-edit-scope-department">Faculty / department / unit <span style="color:#dc2626">*</span></label>
                    <select id="lh-edit-scope-department" name="scope_department_id">
                        <option value="">— Select —</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}">{{ $d->name }} ({{ ucfirst($d->type) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="lh-field lh-scope-dep lh-scope-dep--office" data-prefix="lh-edit" style="margin-bottom:14px; display:none;">
                    <label for="lh-edit-scope-office">Office <span style="color:#dc2626">*</span></label>
                    <select id="lh-edit-scope-office" name="scope_office_id">
                        <option value="">— Select —</option>
                        @foreach($offices as $o)
                            <option value="{{ $o->id }}">{{ $o->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="lh-field">
                    <label>Current image</label>
                    <div class="lh-current-preview"><img id="lh-edit-current" src="" alt="Current letterhead"></div>
                </div>
                <div class="lh-field" style="margin-top:14px;">
                    <label for="lh-edit-image">Replace image (optional)</label>
                    <input type="file" id="lh-edit-image" name="image" accept="image/png,image/jpeg,image/jpg,image/webp">
                    <p style="font-size:11px; color:#64748b; margin:6px 0 0 0;">Leave empty to keep the current image.</p>
                </div>
            </div>
            <div class="lh-modal-ft">
                <button type="button" class="lh-btn lh-btn--ghost" onclick="closeEditModal()">Cancel</button>
                <button type="submit" class="lh-btn lh-btn--primary"><i class="icofont-save"></i> Save changes</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
const LH_UPDATE_URL = "{{ url('dashboard/system-letterheads') }}";
const LH_REORDER_URL = "{{ route('dashboard.system-letterheads.reorder') }}";
const LH_CSRF = "{{ csrf_token() }}";

/* Show only the scope-detail select that matches the chosen scope type. */
function lhToggleScope(prefix) {
    const type = document.getElementById(prefix + '-scope-type').value;
    document.querySelectorAll('.lh-scope-dep[data-prefix="' + prefix + '"]').forEach(el => {
        el.style.display = el.classList.contains('lh-scope-dep--' + type) ? '' : 'none';
    });
}

function openEditModal(id, name, desc, imgUrl, scopeType, scopeId) {
    document.getElementById('lh-edit-form').action = LH_UPDATE_URL + '/' + id;
    document.getElementById('lh-edit-name').value = name;
    document.getElementById('lh-edit-desc').value = desc;
    document.getElementById('lh-edit-current').src = imgUrl;
    document.getElementById('lh-edit-image').value = '';

    document.getElementById('lh-edit-scope-type').value = scopeType || 'global';
    document.getElementById('lh-edit-scope-department').value = scopeType === 'department' ? (scopeId || '') : '';
    document.getElementById('lh-edit-scope-office').value = scopeType === 'office' ? (scopeId || '') : '';
    lhToggleScope('lh-edit');

    document.getElementById('lh-edit-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeEditModal() {
    document.getElementById('lh-edit-modal').classList.remove('open');
    document.body.style.overflow = '';
}

/* Upload form: inline on desktop, full-screen slide-in drawer on mobile. */
function openUploadPanel() {
    document.getElementById('lhUploadPanel').classList.add('open');
    document.body.style.overflow = 'hidden';
}
function closeUploadPanel() {
    document.getElementById('lhUploadPanel').classList.remove('open');
    document.body.style.overflow = '';
}

document.addEventListener('DOMContentLoaded', () => {
    // Restore the upload form's scope fields after a validation redirect.
    lhToggleScope('lh');
    // If a failed upload bounced back with errors, reopen the drawer on mobile so
    // the user sees the form (and the errors) rather than a hidden panel.
    @if($errors->any() || old('name'))
    if (window.matchMedia('(max-width: 767px)').matches) openUploadPanel();
    @endif
});

/* Drag-and-drop reorder */
(function() {
    const grid = document.getElementById('lh-grid');
    if (!grid) return;
    let dragged = null;

    grid.addEventListener('dragstart', (e) => {
        const card = e.target.closest('.lh-card');
        if (!card) return;
        dragged = card;
        card.classList.add('dragging');
        e.dataTransfer.effectAllowed = 'move';
    });

    grid.addEventListener('dragend', () => {
        if (dragged) dragged.classList.remove('dragging');
        dragged = null;
        persistOrder();
    });

    grid.addEventListener('dragover', (e) => {
        e.preventDefault();
        if (!dragged) return;
        const target = e.target.closest('.lh-card');
        if (!target || target === dragged) return;
        const rect = target.getBoundingClientRect();
        const before = (e.clientY - rect.top) < (rect.height / 2);
        grid.insertBefore(dragged, before ? target : target.nextSibling);
    });

    /* Mobile tap-reorder — HTML5 drag events never fire on touch, so the up/down
       chevrons move a card one step and persist via the same endpoint. */
    grid.addEventListener('click', (e) => {
        const upBtn = e.target.closest('.lh-move-up');
        const downBtn = e.target.closest('.lh-move-down');
        if (!upBtn && !downBtn) return;
        const card = e.target.closest('.lh-card');
        if (!card) return;
        if (upBtn && card.previousElementSibling) {
            grid.insertBefore(card, card.previousElementSibling);
        } else if (downBtn && card.nextElementSibling) {
            grid.insertBefore(card.nextElementSibling, card);
        } else {
            return; // already at an end — nothing moved
        }
        refreshMoveButtons();
        persistOrder();
    });

    // Disable "up" on the first card and "down" on the last.
    function refreshMoveButtons() {
        const cards = Array.from(grid.querySelectorAll('.lh-card'));
        cards.forEach((c, i) => {
            const up = c.querySelector('.lh-move-up');
            const down = c.querySelector('.lh-move-down');
            if (up) up.disabled = (i === 0);
            if (down) down.disabled = (i === cards.length - 1);
        });
    }
    refreshMoveButtons();

    function persistOrder() {
        const ids = Array.from(grid.querySelectorAll('.lh-card')).map(c => parseInt(c.dataset.id, 10));
        fetch(LH_REORDER_URL, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': LH_CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ order: ids })
        }).then(r => {
            if (!r.ok) throw new Error('reorder failed');
        }).catch(() => {
            alert('Failed to save the new order. Please refresh the page and try again.');
        });
    }
})();
</script>
@endpush
