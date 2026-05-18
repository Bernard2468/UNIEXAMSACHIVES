@extends('layout.app')

@section('content')
@include('frontend.header')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .folder-page-root, .folder-page-root * {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    }
    .folder-page-root {
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
        padding: 32px 0 88px;
        color: #0f172a;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .folder-shell { max-width: 1280px; margin: 0 auto; padding: 0 24px; }

    .folder-banner {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 1px 2px rgba(15,23,42,0.04);
        padding: 22px;
        margin-bottom: 18px;
        display: flex;
        align-items: center;
        gap: 22px;
        flex-wrap: wrap;
    }
    .folder-banner .big-folder {
        width: 92px; height: 80px;
        position: relative;
        flex-shrink: 0;
    }
    .folder-banner .big-folder svg { width: 100%; height: 100%; filter: drop-shadow(0 6px 14px rgba(15,23,42,0.12)); }
    .folder-banner .lock-pill {
        position: absolute; bottom: -4px; right: -4px;
        background: #0f172a; color: #fbbf24;
        width: 26px; height: 26px;
        border-radius: 50%;
        display:flex; align-items:center; justify-content:center;
        font-size: 11px;
        border: 3px solid #fff;
    }
    .folder-banner h1 {
        font-size: 24px;
        font-weight: 700;
        color:#0f172a;
        margin: 0 0 6px;
        letter-spacing: -0.015em;
        line-height: 1.25;
    }
    .folder-banner .desc {
        font-size: 14px;
        color:#64748b;
        margin: 0;
        line-height: 1.5;
        font-weight: 400;
    }
    .folder-banner .meta {
        display:flex; gap:20px; margin-top: 12px;
        font-size: 13px;
        font-weight: 500;
        color:#475569;
        flex-wrap: wrap;
    }
    .folder-banner .meta span i { color: #94a3b8; margin-right: 5px; }
    .folder-banner .actions {
        margin-left: auto;
        display:flex; gap:8px; flex-wrap: wrap;
    }
    .fbtn {
        display:inline-flex; align-items:center; gap:8px;
        padding: 10px 16px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 600;
        letter-spacing: -0.005em;
        border: 1.5px solid #e2e8f0;
        background:#fff; color:#475569;
        text-decoration:none;
        cursor: pointer;
        transition: all .15s;
        font-family: inherit;
    }
    .fbtn:hover { background:#f1f5f9; color:#0f172a; text-decoration:none; }
    .fbtn.primary { background:#0ea5e9; color:#fff; border-color:#0ea5e9; }
    .fbtn.primary:hover { background:#0284c7; color:#fff; }
    .fbtn.danger { color:#dc2626; border-color:#fecaca; background:#fef2f2; }
    .fbtn.danger:hover { background:#fee2e2; }

    .alert-pill {
        background:#f0fdf4; border:1px solid #bbf7d0;
        color:#166534;
        font-size: 13.5px;
        font-weight: 500;
        padding:12px 16px; border-radius:10px;
        margin-bottom: 14px;
        display:flex; align-items:center; gap:10px;
    }
    .alert-pill.danger { background:#fef2f2; border-color:#fecaca; color:#991b1b; }

    .folder-section {
        background:#fff;
        border:1px solid #e2e8f0;
        border-radius: 14px;
        padding: 20px 24px 24px;
        margin-bottom: 16px;
    }
    .folder-section-head {
        display:flex; align-items:center; justify-content:space-between;
        margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
    }
    .folder-section-head h3 {
        font-size: 12.5px;
        font-weight: 700;
        color:#0f172a;
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin:0;
        display:flex; align-items:center; gap:10px;
    }
    .folder-section-head h3 .count {
        background:#f1f5f9; color:#475569;
        font-size: 11.5px; padding:3px 9px; border-radius:100px;
        font-weight:600;
        letter-spacing:0;
        text-transform: none;
    }
    .folder-grid {
        display:grid;
        grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
        gap: 10px;
    }
    .ftile {
        position:relative;
        display:flex; flex-direction:column; align-items:center;
        padding: 16px 10px 12px;
        border-radius: 10px;
        border: 1.5px solid transparent;
        background: transparent;
        cursor:pointer;
        transition: all .12s ease;
        text-decoration:none; color:inherit;
        user-select:none;
    }
    .ftile:hover { background:#f1f5fa; border-color:#e2e8f0; }
    .ftile .ico { width:64px; height:64px; display:flex; align-items:center; justify-content:center; margin-bottom:8px; }
    .ftile .ico i { font-size: 48px; }
    .ftile .ico.pdf i { color:#ef4444; }
    .ftile .ico.doc i { color:#2563eb; }
    .ftile .ico.xls i { color:#16a34a; }
    .ftile .ico.ppt i { color:#ea580c; }
    .ftile .ico.csv i { color:#0d9488; }
    .ftile .ico.unk i { color:#64748b; }
    .ftile .name {
        font-size: 13.5px;
        font-weight: 500;
        color:#1e293b;
        text-align:center;
        line-height: 1.35;
        word-break: break-word;
        letter-spacing: -0.005em;
        display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;
    }
    .ftile .sub {
        font-size: 11.5px;
        color:#94a3b8;
        margin-top: 4px;
        text-align:center;
        font-weight: 400;
        letter-spacing: 0.01em;
    }
    .ftile .kebab {
        position:absolute; top:6px; right:6px;
        width:22px; height:22px; border-radius:6px;
        background:#fff; border:1px solid #e2e8f0; color:#64748b;
        display:none; align-items:center; justify-content:center;
        font-size: 11px; cursor:pointer;
    }
    .ftile:hover .kebab { display:flex; }
    .ftile .kebab:hover { background:#0ea5e9; color:#fff; border-color:#0ea5e9; }

    .empty-box {
        text-align:center; padding: 48px 20px;
        color:#94a3b8;
    }
    .empty-box .ico-c {
        width:80px; height:80px; border-radius: 22px;
        background:#f1f5f9; color:#94a3b8;
        display:flex; align-items:center; justify-content:center;
        font-size: 32px; margin: 0 auto 16px;
    }
    .empty-box h4 {
        font-size: 17px;
        font-weight: 600;
        color:#1e293b;
        margin: 0 0 6px;
        letter-spacing: -0.005em;
    }
    .empty-box p {
        font-size: 14px;
        margin: 0;
        color:#64748b;
        line-height: 1.5;
    }

    /* Add-items modal */
    .add-backdrop {
        position:fixed; inset:0; background: rgba(15,23,42,0.55);
        display:none; align-items:center; justify-content:center;
        z-index: 9998; backdrop-filter: blur(2px);
    }
    .add-backdrop.open { display:flex; }
    .add-modal {
        background:#fff; border-radius:16px;
        width: 92%; max-width: 640px;
        padding: 24px 26px;
        box-shadow: 0 20px 60px rgba(15,23,42,0.25);
        max-height: 86vh; display:flex; flex-direction:column;
    }
    .add-modal h3 {
        font-size: 18px;
        font-weight: 700;
        color:#0f172a;
        margin: 0 0 18px;
        display:flex; align-items:center; gap:10px;
        letter-spacing: -0.01em;
    }
    .add-modal h3 i { color:#0ea5e9; }
    .add-tabs { display:flex; gap:4px; background:#f1f5f9; padding:4px; border-radius:10px; margin-bottom: 16px;}
    .add-tab {
        flex:1; padding: 10px 12px; border-radius:8px;
        background:transparent; border:none; cursor:pointer;
        font-size: 13px;
        font-weight: 600;
        color:#64748b;
        font-family:inherit;
        letter-spacing: -0.005em;
    }
    .add-tab.active { background:#fff; color:#0f172a; box-shadow: 0 1px 3px rgba(15,23,42,0.08); }
    .add-list { overflow-y:auto; flex:1; }
    .add-row {
        display:flex; align-items:center; gap:12px;
        padding: 11px 12px; border-radius: 8px;
        cursor:pointer;
    }
    .add-row:hover { background:#f8fafc; }
    .add-row input[type="checkbox"] { width: 16px; height: 16px; cursor:pointer; }
    .add-row .name {
        flex:1;
        font-size: 13.5px;
        font-weight: 500;
        color:#1e293b;
        letter-spacing: -0.005em;
    }
    .add-row .ext {
        font-size: 11px;
        font-weight: 600;
        color:#475569;
        background:#f1f5f9;
        padding: 3px 9px;
        border-radius: 5px;
        letter-spacing: 0.04em;
    }
    .add-actions { display:flex; justify-content:flex-end; gap:10px; margin-top: 18px;}

    .toast-mini {
        position: fixed; bottom: 24px; left: 50%;
        transform: translateX(-50%) translateY(20px);
        background:#0f172a; color:#f8fafc;
        padding: 13px 20px; border-radius:10px;
        font-size: 13.5px;
        font-weight: 500;
        opacity:0; pointer-events:none;
        transition: all .25s; z-index: 10000;
        display:flex; align-items:center; gap:10px;
    }
    .toast-mini.show { opacity:1; transform:translateX(-50%) translateY(0); }
</style>
@endpush

@php
    $folderColor = $folder->color ?: '#fbbf24';
    $isLocked = !empty($folder->password_hash);
    $folderFiles = $folder->files;
    $folderExams = $folder->exams;
    $totalCount = $folderFiles->count() + $folderExams->count();

    $folderItems = collect()
        ->merge($folderFiles->map(function($file) use ($folder) {
            $ext = strtolower(pathinfo($file->document_file ?? '', PATHINFO_EXTENSION) ?: 'pdf');
            $date = $file->year_deposit
                ? \Carbon\Carbon::parse($file->year_deposit)->format('Y-m-d')
                : ($file->created_at ? $file->created_at->format('Y-m-d') : now()->format('Y-m-d'));
            return (object)[
                'kind' => 'file',
                'id' => $file->id,
                'title' => $file->file_title,
                'date' => $date,
                'ext' => $ext,
                'view' => asset($file->document_file),
                'download' => route('download.file', $file->id),
                'edit' => route('files.edit', $file->id),
                'remove' => route('dashboard.folders.remove-file', ['folder' => $folder->id, 'file' => $file->id]),
            ];
        }))
        ->merge($folderExams->map(function($exam) use ($folder) {
            $ext = strtolower(pathinfo($exam->exam_document ?? '', PATHINFO_EXTENSION) ?: 'pdf');
            $date = $exam->created_at ? $exam->created_at->format('Y-m-d') : now()->format('Y-m-d');
            return (object)[
                'kind' => 'exam',
                'id' => $exam->id,
                'title' => $exam->course_title . ' (' . $exam->course_code . ')',
                'date' => $date,
                'ext' => $ext,
                'view' => asset($exam->exam_document),
                'download' => route('download.exam', $exam->id),
                'edit' => route('exams.edit', $exam->id),
                'remove' => route('dashboard.folders.remove-exam', ['folder' => $folder->id, 'exam' => $exam->id]),
            ];
        }))
        ->sortByDesc('date')
        ->values();
@endphp

<div class="folder-page-root">
    <div class="folder-shell">

        {{-- ===== BANNER ===== --}}
        <div class="folder-banner">
            <div class="big-folder">
                <svg viewBox="0 0 64 50" xmlns="http://www.w3.org/2000/svg">
                    <path d="M2 10 Q2 4 8 4 L22 4 L28 10 L56 10 Q62 10 62 16 L62 42 Q62 48 56 48 L8 48 Q2 48 2 42 Z"
                          fill="{{ $folderColor }}" opacity="0.95"/>
                    <path d="M2 14 Q2 8 8 8 L60 8 Q64 8 62 14 L57 44 Q56 48 50 48 L8 48 Q2 48 2 42 Z"
                          fill="{{ $folderColor }}" opacity="0.7"/>
                </svg>
                @if($isLocked)
                    <span class="lock-pill"><i class="fas fa-lock"></i></span>
                @endif
            </div>
            <div style="min-width:0;">
                <h1>{{ $folder->name }}</h1>
                @if($folder->description)
                    <p class="desc">{{ $folder->description }}</p>
                @endif
                <div class="meta">
                    <span><i class="fas fa-file-lines"></i>{{ $folderFiles->count() }} {{ Str::plural('file', $folderFiles->count()) }}</span>
                    <span><i class="fas fa-clipboard-list"></i>{{ $folderExams->count() }} {{ Str::plural('exam', $folderExams->count()) }}</span>
                    <span><i class="fas fa-calendar"></i>Created {{ $folder->created_at?->format('M j, Y') }}</span>
                    @if($isLocked)<span><i class="fas fa-shield-halved" style="color:#0ea5e9;"></i>Password protected</span>@endif
                </div>
            </div>
            <div class="actions">
                <a href="{{ route('dashboard.folders.index') }}" class="fbtn"><i class="fas fa-arrow-left"></i> Back</a>
                <button type="button" class="fbtn primary" id="openAddModal"><i class="fas fa-plus"></i> Add items</button>
                <a href="{{ route('dashboard.folders.edit', $folder) }}" class="fbtn"><i class="fas fa-pen"></i> Edit</a>
                <a href="{{ route('dashboard.folders.security', $folder) }}" class="fbtn"><i class="fas fa-shield-halved"></i> Security</a>
                <form action="{{ route('dashboard.folders.destroy', $folder) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this folder? Its items will be detached but not deleted.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="fbtn danger"><i class="fas fa-trash"></i> Delete folder</button>
                </form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-pill"><i class="fas fa-circle-check" style="color:#22c55e;"></i> {{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert-pill"><i class="fas fa-info-circle" style="color:#0ea5e9;"></i> {{ session('info') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-pill danger"><i class="fas fa-triangle-exclamation"></i> {{ $errors->first() }}</div>
        @endif

        {{-- ===== CONTENTS ===== --}}
        <div class="folder-section">
            <div class="folder-section-head">
                <h3><i class="fas fa-folder-open" style="color:{{ $folderColor }};"></i> Folder contents <span class="count">{{ $totalCount }}</span></h3>
                @if($totalCount > 0)
                    <div style="font-size:12.5px; color:#94a3b8; font-weight:400;"><i class="fas fa-info-circle"></i> Right-click any item for actions</div>
                @endif
            </div>

            @if($totalCount > 0)
                <div class="folder-grid">
                    @foreach($folderItems as $it)
                        @php
                            $iconClass = match(true) {
                                $it->ext === 'pdf' => 'pdf',
                                in_array($it->ext, ['doc','docx']) => 'doc',
                                in_array($it->ext, ['xls','xlsx','csv']) => 'xls',
                                in_array($it->ext, ['ppt','pptx']) => 'ppt',
                                default => 'unk',
                            };
                            $iconFa = match($iconClass) {
                                'pdf' => 'fa-file-pdf',
                                'doc' => 'fa-file-word',
                                'xls' => 'fa-file-excel',
                                'ppt' => 'fa-file-powerpoint',
                                default => 'fa-file',
                            };
                            $displayName = $it->title . '_' . $it->date;
                        @endphp
                        <div class="ftile folder-item"
                            data-view-url="{{ $it->view }}"
                            data-download-url="{{ $it->download }}"
                            data-edit-url="{{ $it->edit }}"
                            data-remove-url="{{ $it->remove }}"
                            data-name="{{ $displayName }}"
                            title="{{ $displayName }}.{{ $it->ext }}">
                            <button type="button" class="kebab" aria-label="More"><i class="fas fa-ellipsis-vertical"></i></button>
                            <div class="ico {{ $iconClass }}"><i class="fas {{ $iconFa }}"></i></div>
                            <div class="name">{{ $displayName }}</div>
                            <div class="sub">{{ strtoupper($it->ext) }} &middot; {{ ucfirst($it->kind) }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-box">
                    <div class="ico-c"><i class="fas fa-folder-open"></i></div>
                    <h4>This folder is empty</h4>
                    <p>Click "Add items" or drag files/exams here from the archive pages.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ===== Context menu ===== --}}
<div id="folderCtx" style="position:fixed; z-index:9999; min-width:200px; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 10px 30px rgba(15,23,42,0.18); padding:6px; display:none;">
    <a href="#" data-action="view" style="display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:13.5px; font-weight:500; color:#1e293b; border-radius:7px; text-decoration:none;"><i class="fas fa-eye" style="width:14px; color:#64748b;"></i> Open / View</a>
    <a href="#" data-action="download" style="display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:13.5px; font-weight:500; color:#1e293b; border-radius:7px; text-decoration:none;"><i class="fas fa-download" style="width:14px; color:#64748b;"></i> Download</a>
    <a href="#" data-action="edit" style="display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:13.5px; font-weight:500; color:#1e293b; border-radius:7px; text-decoration:none;"><i class="fas fa-pen" style="width:14px; color:#64748b;"></i> Edit details</a>
    <div style="height:1px; background:#f1f5f9; margin:4px 0;"></div>
    <button type="button" data-action="remove" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 12px; background:transparent; border:none; text-align:left; font-size:13.5px; font-weight:500; color:#dc2626; border-radius:7px; cursor:pointer; font-family:inherit;"><i class="fas fa-folder-minus" style="width:14px;"></i> Remove from folder</button>
</div>

{{-- Hidden remove form (DELETE) --}}
<form id="folderRemoveForm" method="POST" style="display:none;">@csrf @method('DELETE')</form>

{{-- ===== ADD ITEMS MODAL ===== --}}
<div class="add-backdrop" id="addModal">
    <div class="add-modal">
        <h3><i class="fas fa-plus"></i> Add items to "{{ $folder->name }}"</h3>
        <div class="add-tabs">
            <button type="button" class="add-tab active" data-tab="files">Files ({{ $availableFiles->count() }})</button>
            <button type="button" class="add-tab" data-tab="exams">Exams ({{ $availableExams->count() }})</button>
        </div>

        <form id="addFilesForm" action="{{ route('dashboard.folders.add-files', $folder) }}" method="POST" data-pane="files">
            @csrf
            <div class="add-list">
                @forelse($availableFiles as $f)
                    @php $ext = strtoupper(pathinfo($f->document_file ?? '', PATHINFO_EXTENSION) ?: 'PDF'); @endphp
                    <label class="add-row">
                        <input type="checkbox" name="file_ids[]" value="{{ $f->id }}">
                        <span class="name">{{ $f->file_title }}</span>
                        <span class="ext">{{ $ext }}</span>
                    </label>
                @empty
                    <div class="empty-box"><div class="ico-c"><i class="fas fa-file"></i></div><h4>No files available</h4><p>Upload more files to add them here.</p></div>
                @endforelse
            </div>
            <div class="add-actions">
                <button type="button" class="fbtn" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="fbtn primary" @if($availableFiles->count() === 0) disabled @endif><i class="fas fa-check"></i> Add selected files</button>
            </div>
        </form>

        <form id="addExamsForm" action="{{ route('dashboard.folders.add-exams', $folder) }}" method="POST" data-pane="exams" style="display:none;">
            @csrf
            <div class="add-list">
                @forelse($availableExams as $e)
                    @php $ext = strtoupper(pathinfo($e->exam_document ?? '', PATHINFO_EXTENSION) ?: 'PDF'); @endphp
                    <label class="add-row">
                        <input type="checkbox" name="exam_ids[]" value="{{ $e->id }}">
                        <span class="name">{{ $e->course_title }} ({{ $e->course_code }})</span>
                        <span class="ext">{{ $ext }}</span>
                    </label>
                @empty
                    <div class="empty-box"><div class="ico-c"><i class="fas fa-clipboard-list"></i></div><h4>No exams available</h4><p>Upload more exam documents to add them here.</p></div>
                @endforelse
            </div>
            <div class="add-actions">
                <button type="button" class="fbtn" onclick="document.getElementById('addModal').classList.remove('open')">Cancel</button>
                <button type="submit" class="fbtn primary" @if($availableExams->count() === 0) disabled @endif><i class="fas fa-check"></i> Add selected exams</button>
            </div>
        </form>
    </div>
</div>

<div class="toast-mini" id="folderToast"><i class="fas fa-circle-check"></i><span></span></div>
@endsection

@push('scripts')
<script>
(function() {
    const openBtn = document.getElementById('openAddModal');
    const modal = document.getElementById('addModal');
    const tabs = document.querySelectorAll('.add-tab');
    const filesForm = document.getElementById('addFilesForm');
    const examsForm = document.getElementById('addExamsForm');

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => modal.classList.add('open'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
    }

    tabs.forEach(t => {
        t.addEventListener('click', () => {
            tabs.forEach(x => x.classList.remove('active'));
            t.classList.add('active');
            const tab = t.getAttribute('data-tab');
            filesForm.style.display = tab === 'files' ? '' : 'none';
            examsForm.style.display = tab === 'exams' ? '' : 'none';
        });
    });

    // Context menu
    const ctx = document.getElementById('folderCtx');
    let active = null;
    function openCtx(x, y, tile) {
        active = tile;
        ctx.style.left = Math.min(x, window.innerWidth - 220) + 'px';
        ctx.style.top = Math.min(y, window.innerHeight - 220) + 'px';
        ctx.style.display = 'block';
    }
    function closeCtx() { ctx.style.display = 'none'; active = null; }
    document.querySelectorAll('.folder-item').forEach(tile => {
        tile.addEventListener('contextmenu', e => { e.preventDefault(); openCtx(e.clientX, e.clientY, tile); });
        tile.addEventListener('dblclick', () => { const u = tile.getAttribute('data-view-url'); if (u) window.open(u, '_blank'); });
        const k = tile.querySelector('.kebab');
        if (k) k.addEventListener('click', e => {
            e.preventDefault(); e.stopPropagation();
            const r = k.getBoundingClientRect();
            openCtx(r.right + 4, r.bottom + 4, tile);
        });
    });
    document.addEventListener('click', e => { if (!ctx.contains(e.target)) closeCtx(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCtx(); });

    ctx.querySelectorAll('[data-action]').forEach(b => {
        b.addEventListener('click', e => {
            e.preventDefault();
            if (!active) return;
            const a = b.getAttribute('data-action');
            if (a === 'view') window.open(active.getAttribute('data-view-url'), '_blank');
            else if (a === 'download') window.location.href = active.getAttribute('data-download-url');
            else if (a === 'edit') window.location.href = active.getAttribute('data-edit-url');
            else if (a === 'remove') {
                if (!confirm('Remove "' + (active.getAttribute('data-name') || 'this item') + '" from this folder?\nThe item itself will not be deleted.')) return;
                const f = document.getElementById('folderRemoveForm');
                f.action = active.getAttribute('data-remove-url');
                f.submit();
            }
            closeCtx();
        });
    });
})();
</script>
@endpush
