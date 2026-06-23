@extends('layout.app')

@section('content')
@include('frontend.header')

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:opsz,wght@9..40,400;9..40,500;9..40,600;9..40,700&family=Outfit:wght@300;400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
    .folder-page-root {
        font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
        min-height: 100vh;
        padding: 32px 0 88px;
        color: #0f172a;
        line-height: 1.5;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
    }
    .folder-page-root input,
    .folder-page-root button,
    .folder-page-root select,
    .folder-page-root textarea,
    .folder-page-root h1,
    .folder-page-root h2,
    .folder-page-root h3,
    .folder-page-root h4,
    .folder-page-root p,
    .folder-page-root a,
    .folder-page-root span,
    .folder-page-root label,
    .folder-page-root div {
        font-family: inherit;
    }
    /* Preserve Font Awesome's icon font on <i> tags */
    .folder-page-root .fas,
    .folder-page-root .far,
    .folder-page-root .fal,
    .folder-page-root .fab,
    .folder-page-root .fa-solid,
    .folder-page-root .fa-regular,
    .folder-page-root .fa-light,
    .folder-page-root .fa-brands,
    .folder-page-root [class^="fa-"],
    .folder-page-root [class*=" fa-"] {
        font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
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
    .folder-banner .meta span .meta-icon-img {
        width: 20px; height: 20px;
        margin-right: 6px;
        vertical-align: -4px;
        object-fit: contain;
    }
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
    /* Answer-key badge — same plasticine key icon used on the archive (Explorer) pages. */
    .ftile .key-badge {
        position:absolute; top:4px; left:4px;
        width:28px; height:28px; padding:3px;
        border-radius:8px;
        background:#fff; border:1px solid #e2e8f0;
        display:flex; align-items:center; justify-content:center;
        cursor:pointer; z-index:3;
        box-shadow:0 2px 6px rgba(15,23,42,0.16);
        transition: transform .15s, box-shadow .15s;
    }
    .ftile .key-badge img { width:100%; height:100%; object-fit:contain; display:block; }
    .ftile .key-badge:hover { transform: scale(1.12); box-shadow:0 4px 10px rgba(15,23,42,0.24); }

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
    $iconFiles = 'https://res.cloudinary.com/dsypclqxk/image/upload/v1782226720/a0bc05b8-6e1d-4338-b9bd-189fe64f6c6b.png';
    $iconExams = 'https://res.cloudinary.com/dsypclqxk/image/upload/v1782226060/1e7ab43a-082a-4fa2-bf5d-8cad70910469.png';
    $iconCalendar = 'https://res.cloudinary.com/dsypclqxk/image/upload/v1782225885/ba1eed08-96d1-484a-aa27-da2bf660e15d.png';
    $iconPassword = 'https://res.cloudinary.com/dsypclqxk/image/upload/v1782226976/d5ea208a-dc09-40e0-92d4-8194eff97801.png';
    $iconKey = 'https://img.icons8.com/plasticine/100/key-security.png';

    // Smart "Back" target: prefer the page the user came from, but only if it's
    // on this app (prevents open-redirect via tampered ?from= values).
    $backUrl = route('dashboard.folders.index');
    $backLabel = 'All Folders';
    $from = request('from');
    if ($from) {
        $parsed = parse_url($from);
        $appHost = parse_url(url('/'), PHP_URL_HOST);
        $sameOrigin = !isset($parsed['host']) || $parsed['host'] === $appHost;
        if ($sameOrigin) {
            $backUrl = $from;
            // Friendly label based on the originating path
            $path = $parsed['path'] ?? '';
            $backLabel = match(true) {
                str_contains($path, '/exams-documents') => 'All Documents',
                str_contains($path, '/all-files') || str_contains($path, '/all-upload-files') => 'All Files',
                str_contains($path, '/all-upload-exams') || str_contains($path, '/all-exams') || str_contains($path, '/upload-exams') => 'All Exams',
                str_contains($path, '/folders') => 'All Folders',
                default => 'Back',
            };
        }
    }
    // Preserve the from= value when navigating to edit/security so back chains keep working.
    $forwardFrom = $from ? '?from=' . urlencode($from) : '';

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
                'own' => $file->user_id === auth()->id(),
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
                'own' => $exam->user_id === auth()->id(),
                'view' => asset($exam->exam_document),
                'download' => route('download.exam', $exam->id),
                'has_key' => !empty($exam->answer_key),
                'key_view' => $exam->answer_key ? asset($exam->answer_key) : null,
                'bundle' => route('download.exam.bundle', $exam->id),
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
                    <span><img src="{{ $iconFiles }}" alt="" class="meta-icon-img" aria-hidden="true">{{ $folderFiles->count() }} {{ Str::plural('file', $folderFiles->count()) }}</span>
                    <span><img src="{{ $iconExams }}" alt="" class="meta-icon-img" aria-hidden="true">{{ $folderExams->count() }} {{ Str::plural('exam', $folderExams->count()) }}</span>
                    <span><img src="{{ $iconCalendar }}" alt="" class="meta-icon-img" aria-hidden="true">Created {{ $folder->created_at?->format('M j, Y') }}</span>
                    @if($isLocked && $isOwner)<span><img src="{{ $iconPassword }}" alt="" class="meta-icon-img" aria-hidden="true">Password protected</span>@endif
                    @if($isOwner && $folder->members->count() > 0)
                        <span><i class="fas fa-user-group" style="color:#0ea5e9;"></i>Shared with {{ $folder->members->count() }} {{ Str::plural('person', $folder->members->count()) }}</span>
                    @endif
                    @if(!$isOwner)
                        @php
                            $ownerName = $folder->user
                                ? (trim(($folder->user->first_name ?? '') . ' ' . ($folder->user->last_name ?? '')) ?: $folder->user->email)
                                : 'Someone';
                            $myPerm = $folder->effectivePermissionFor(auth()->user()) ?? 'viewer';
                        @endphp
                        <span><i class="fas fa-user-group" style="color:#0ea5e9;"></i>Shared by {{ $ownerName }} · {{ ucfirst($myPerm) }}</span>
                    @endif
                </div>
            </div>
            <div class="actions">
                <a href="{{ $backUrl }}" class="fbtn"><i class="fas fa-arrow-left"></i> Back to {{ $backLabel }}</a>
                @if($isOwner || $folder->canEditContents(auth()->user()))
                    <button type="button" class="fbtn primary" id="openAddModal"><i class="fas fa-plus"></i> Add items</button>
                @endif
                @if($isOwner)
                    <button type="button" class="fbtn" id="openMembersModal">
                        <i class="fas fa-share-nodes"></i> Share
                        @if($folder->members->count() > 0)
                            <span style="background:#0ea5e9; color:#fff; font-size:11px; padding:1px 7px; border-radius:100px; margin-left:2px;">{{ $folder->members->count() }}</span>
                        @endif
                    </button>
                    <a href="{{ route('dashboard.folders.edit', $folder) }}{{ $forwardFrom }}" class="fbtn"><i class="fas fa-pen"></i> Edit</a>
                    <a href="{{ route('dashboard.folders.security', $folder) }}{{ $forwardFrom }}" class="fbtn"><i class="fas fa-shield-halved"></i> Security</a>
                    <form action="{{ route('dashboard.folders.destroy', $folder) }}" method="POST" style="display:inline;" onsubmit="return confirm('Delete this folder? Its items will be detached but not deleted.');">
                        @csrf @method('DELETE')
                        <button type="submit" class="fbtn danger"><i class="fas fa-trash"></i> Delete folder</button>
                    </form>
                @else
                    {{-- "Leave" only removes a direct share. Hide it for users whose
                         access comes solely from a group — they can't leave a group here. --}}
                    @if($folder->members->contains('id', auth()->id()))
                        <form action="{{ route('dashboard.folders.leave', $folder) }}" method="POST" style="display:inline;" onsubmit="return confirm('Leave this folder? You will lose access to its contents.');">
                            @csrf @method('DELETE')
                            <button type="submit" class="fbtn danger"><i class="fas fa-right-from-bracket"></i> Leave folder</button>
                        </form>
                    @endif
                @endif
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
                            data-can-edit="{{ $it->own ? '1' : '0' }}"
                            data-has-key="{{ !empty($it->has_key) ? '1' : '0' }}"
                            data-key-view-url="{{ $it->key_view ?? '' }}"
                            data-bundle-url="{{ $it->bundle ?? '' }}"
                            data-name="{{ $displayName }}"
                            title="{{ $displayName }}.{{ $it->ext }}">
                            <button type="button" class="kebab" aria-label="More"><i class="fas fa-ellipsis-vertical"></i></button>
                            @if(!empty($it->has_key))
                                <button type="button" class="key-badge" aria-label="View answer key" title="Answer key attached — click to view"><img src="{{ $iconKey }}" alt="" loading="lazy"></button>
                            @endif
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
    <a href="#" data-action="view-key" class="ctx-key-item" style="display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:13.5px; font-weight:500; color:#1e293b; border-radius:7px; text-decoration:none;"><i class="fas fa-key" style="width:14px; color:#d97706;"></i> View answer key</a>
    <a href="#" data-action="download" style="display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:13.5px; font-weight:500; color:#1e293b; border-radius:7px; text-decoration:none;"><i class="fas fa-download" style="width:14px; color:#64748b;"></i> <span class="ctx-dl-label">Download</span></a>
    <a href="#" data-action="edit" style="display:flex; align-items:center; gap:10px; padding:10px 12px; font-size:13.5px; font-weight:500; color:#1e293b; border-radius:7px; text-decoration:none;"><i class="fas fa-pen" style="width:14px; color:#64748b;"></i> Edit details</a>
    @if($isOwner)
    <div data-ctx-remove-sep style="height:1px; background:#f1f5f9; margin:4px 0;"></div>
    <button type="button" data-action="remove" style="display:flex; align-items:center; gap:10px; width:100%; padding:10px 12px; background:transparent; border:none; text-align:left; font-size:13.5px; font-weight:500; color:#dc2626; border-radius:7px; cursor:pointer; font-family:inherit;"><i class="fas fa-folder-minus" style="width:14px;"></i> Remove from folder</button>
    @endif
</div>


{{-- ===== ADD ITEMS MODAL ===== --}}
@php
    $aiIcon = function ($path) {
        $ext = strtolower(pathinfo($path ?? '', PATHINFO_EXTENSION) ?: 'pdf');
        $class = match (true) {
            $ext === 'pdf' => 'pdf',
            in_array($ext, ['doc', 'docx']) => 'doc',
            in_array($ext, ['xls', 'xlsx', 'csv']) => 'xls',
            in_array($ext, ['ppt', 'pptx']) => 'ppt',
            in_array($ext, ['png', 'jpg', 'jpeg', 'gif', 'webp']) => 'img',
            default => 'unk',
        };
        $glyph = match ($class) {
            'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'xls' => 'fa-file-excel',
            'ppt' => 'fa-file-powerpoint', 'img' => 'fa-file-image', default => 'fa-file',
        };
        return ['ext' => $ext, 'class' => $class, 'glyph' => $glyph];
    };
@endphp
<div class="mdrawer-backdrop" id="addModal">
    <aside class="mdrawer" role="dialog" aria-modal="true" aria-label="Add items to folder">
        <header class="mdrawer__head">
            <div class="mdrawer__title">
                <span class="mdrawer__ic"><i class="fas fa-plus"></i></span>
                <div style="min-width:0;">
                    <h3>Add to folder</h3>
                    <p>{{ $folder->name }}</p>
                </div>
            </div>
            <button type="button" class="mdrawer__close" id="addDrawerClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </header>

        <nav class="mdrawer__seg" role="tablist">
            <button type="button" class="mb-tab active" data-add-tab="files" role="tab">
                <img src="{{ $iconFiles }}" alt="" class="drawer-tab-icon" aria-hidden="true"> Files
                <span class="mb-tab-badge">{{ $availableFiles->count() }}</span>
            </button>
            <button type="button" class="mb-tab" data-add-tab="exams" role="tab">
                <img src="{{ $iconExams }}" alt="" class="drawer-tab-icon" aria-hidden="true"> Exams
                <span class="mb-tab-badge">{{ $availableExams->count() }}</span>
            </button>
            <span class="mdrawer__ind" id="addSegInd" aria-hidden="true"></span>
        </nav>

        <div class="ai-toolbar">
            <div class="ai-search">
                <i class="fas fa-search"></i>
                <input type="text" id="aiSearch" placeholder="Search by name…" autocomplete="off">
            </div>
            <div class="ai-view" role="group" aria-label="View mode">
                <button type="button" data-view="grid" class="active" title="Grid view" aria-label="Grid view"><i class="fas fa-table-cells-large"></i></button>
                <button type="button" data-view="list" title="List view" aria-label="List view"><i class="fas fa-list-ul"></i></button>
            </div>
        </div>

        <div class="mdrawer__body">
            <form id="addFilesForm" action="{{ route('dashboard.folders.add-files', $folder) }}" method="POST" data-pane="files">
                @csrf
                <div class="ai-items ai-grid" id="aiListFiles" data-kind="files">
                    @forelse($availableFiles as $f)
                        @php $ic = $aiIcon($f->document_file); @endphp
                        <label class="ai-item" data-search="{{ strtolower($f->file_title . ' ' . $ic['ext']) }}">
                            <input type="checkbox" name="file_ids[]" value="{{ $f->id }}">
                            <span class="ai-ico {{ $ic['class'] }}"><i class="fas {{ $ic['glyph'] }}"></i></span>
                            <span class="ai-name">{{ $f->file_title }}</span>
                            <span class="ai-meta">{{ strtoupper($ic['ext']) }}</span>
                            <span class="ai-check"><i class="fas fa-check"></i></span>
                        </label>
                    @empty
                        <div class="empty-box" style="grid-column:1/-1; padding:36px 8px;">
                            <div class="ico-c"><img src="{{ $iconFiles }}" alt="" class="drawer-empty-icon" aria-hidden="true"></div>
                            <h4>No files to add</h4>
                            <p>Upload files first, then add them here.</p>
                        </div>
                    @endforelse
                    <div class="ai-noresult" style="display:none; grid-column:1/-1;">No files match your search.</div>
                </div>
            </form>

            <form id="addExamsForm" action="{{ route('dashboard.folders.add-exams', $folder) }}" method="POST" data-pane="exams" style="display:none;">
                @csrf
                <div class="ai-items ai-grid" id="aiListExams" data-kind="exams">
                    @forelse($availableExams as $e)
                        @php $ic = $aiIcon($e->exam_document); @endphp
                        <label class="ai-item" data-search="{{ strtolower($e->course_title . ' ' . $e->course_code . ' ' . $ic['ext']) }}">
                            <input type="checkbox" name="exam_ids[]" value="{{ $e->id }}">
                            <span class="ai-ico {{ $ic['class'] }}"><i class="fas {{ $ic['glyph'] }}"></i></span>
                            <span class="ai-name">{{ $e->course_title }} ({{ $e->course_code }})</span>
                            @if($e->answer_key)
                                <span class="ai-key" title="Answer key attached"><img src="{{ $iconKey }}" alt="" aria-hidden="true"></span>
                            @endif
                            <span class="ai-meta">{{ strtoupper($ic['ext']) }}</span>
                            <span class="ai-check"><i class="fas fa-check"></i></span>
                        </label>
                    @empty
                        <div class="empty-box" style="grid-column:1/-1; padding:36px 8px;">
                            <div class="ico-c"><img src="{{ $iconExams }}" alt="" class="drawer-empty-icon" aria-hidden="true"></div>
                            <h4>No exams to add</h4>
                            <p>Upload exam documents first, then add them here.</p>
                        </div>
                    @endforelse
                    <div class="ai-noresult" style="display:none; grid-column:1/-1;">No exams match your search.</div>
                </div>
            </form>
        </div>

        <footer class="mdrawer__foot">
            <div class="ai-foot-left">
                <button type="button" class="ai-link" data-ai-all>Select all</button>
                <button type="button" class="ai-link" data-ai-clear>Clear</button>
                <span class="ai-count"><b id="aiCount">0</b> selected</span>
            </div>
            <button type="submit" form="addFilesForm" class="mdrawer__done" id="aiSubmit" disabled><i class="fas fa-check"></i> Add</button>
        </footer>
    </aside>
</div>

@if($isOwner)
@php
    // Users available to invite into this folder — excludes the owner and
    // anyone already on the members list. Mirrors the create-folder picker.
    $existingMemberIds = $folder->members->pluck('id')->all();
    $invitableUsers = \App\Models\User::query()
        ->where('is_approve', true)
        ->where('id', '!=', auth()->id())
        ->whereNotIn('id', $existingMemberIds)
        ->with(['position:id,name', 'department:id,name'])
        ->orderBy('first_name')
        ->orderBy('last_name')
        ->get(['id', 'first_name', 'last_name', 'email', 'profile_picture', 'position_id', 'department_id']);
@endphp
{{-- ===== MEMBERS MODAL ===== --}}
<style>
.members-list { max-height: 260px; overflow-y: auto; margin-top: 4px; }
.member-row {
    display:flex; align-items:center; gap:12px;
    padding: 10px 8px;
    border-radius: 8px;
    transition: background .12s;
}
.member-row:hover { background:#f8fafc; }
.member-row .avatar {
    width: 36px; height: 36px; border-radius: 50%;
    object-fit: cover; flex-shrink: 0;
    background: #f1f5f9;
}
.member-row .info { flex:1; min-width: 0; }
.member-row .info .name { font-size: 13.5px; font-weight: 600; color:#0f172a; }
.member-row .info .email { font-size: 12px; color:#64748b; white-space: nowrap; overflow:hidden; text-overflow: ellipsis; }
.member-row select {
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    padding: 6px 10px; font-size: 12.5px; font-weight: 500;
    background:#fff; color:#0f172a;
    cursor:pointer; font-family: inherit;
}
.member-row .remove-btn {
    border: none; background: transparent;
    color: #94a3b8;
    width: 28px; height: 28px; border-radius: 6px;
    cursor: pointer; transition: all .12s;
    display:flex; align-items:center; justify-content:center;
}
.member-row .remove-btn:hover { background:#fef2f2; color:#dc2626; }

/* ===== Members modal tabs ===== */
.mb-tabs {
    display: flex; gap: 4px;
    background: #f1f5f9;
    padding: 4px;
    border-radius: 10px;
    margin-bottom: 14px;
}
.mb-tab {
    flex: 1;
    display: inline-flex; align-items: center; justify-content: center; gap: 8px;
    padding: 10px 12px;
    border-radius: 8px;
    background: transparent; border: none; cursor: pointer;
    font-size: 13px; font-weight: 600;
    color: #64748b;
    font-family: inherit;
    letter-spacing: -0.005em;
    transition: background .15s, color .15s, box-shadow .15s;
}
.mb-tab:hover { color: #334155; }
.mb-tab.active {
    background: #fff; color: #0f172a;
    box-shadow: 0 1px 3px rgba(15,23,42,0.08);
}
.mb-tab i { font-size: 12px; }
.mb-tab-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 22px; height: 20px;
    padding: 0 7px;
    border-radius: 100px;
    background: #e2e8f0; color: #475569;
    font-size: 11px; font-weight: 700;
    line-height: 1;
}
.mb-tab.active .mb-tab-badge { background: #e0f2fe; color: #0369a1; }

.mb-panel { display: none; }
.mb-panel.active { display: block; }
.mb-panel-hint {
    font-size: 12.5px; color: #64748b;
    margin: 0 0 10px;
    line-height: 1.45;
}

/* ===== Member picker (matches the create-folder Share-with-people UI) ===== */
.mp-picker {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    background: #fff;
    overflow: hidden;
}
.mp-toolbar {
    display:flex; align-items:center; gap:12px;
    padding: 10px 12px;
    background:#f8fafc;
    border-bottom: 1px solid #e2e8f0;
    flex-wrap: wrap;
}
.mp-search-box { position: relative; flex:1; min-width:200px; display:flex; align-items:center; }
.mp-search-box i {
    position:absolute; left:12px; top:50%; transform:translateY(-50%);
    color:#94a3b8; font-size:13px; pointer-events:none;
}
.mp-search-box input {
    width:100%; padding: 9px 12px 9px 34px;
    border: 1px solid #e2e8f0; border-radius:8px;
    background:#fff; font-size:13px; color:#0f172a;
    outline: none; transition: border-color .15s, box-shadow .15s;
    font-family: inherit;
}
.mp-search-box input:focus {
    border-color:#0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.15);
}
.mp-stats { font-size:12px; color:#475569; font-weight:600; white-space:nowrap; }
.mp-stats #mpSelectedCount { color:#0ea5e9; font-weight:700; }

.mp-list { max-height: 280px; overflow-y: auto; background:#fff; }
.mp-list::-webkit-scrollbar { width:8px; }
.mp-list::-webkit-scrollbar-track { background:#f1f5f9; }
.mp-list::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }

.mp-user {
    display:flex; align-items:center; gap:12px;
    padding: 10px 14px;
    border-bottom:1px solid #f1f5f9;
    cursor:pointer; transition: background .12s;
}
.mp-user:last-of-type { border-bottom: none; }
.mp-user:hover { background:#f8fafc; }
.mp-user.selected { background:#eff6ff; }
.mp-user.selected:hover { background:#dbeafe; }

.mp-av {
    width:40px; height:40px; border-radius:50%;
    flex-shrink:0; overflow:hidden; background:#f1f5f9;
    display:flex; align-items:center; justify-content:center;
}
.mp-av img { width:100%; height:100%; object-fit:cover; }
.mp-av-fallback {
    width:100%; height:100%;
    display:flex; align-items:center; justify-content:center;
    background:#e0f2fe; color:#0284c7; font-weight:700; font-size:13px;
}
.mp-info { flex:1; min-width:0; }
.mp-name {
    display:flex; align-items:center; gap:8px;
    font-size:13.5px; font-weight:600; color:#0f172a;
    line-height:1.3;
}
.mp-name > span:first-child { overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.mp-pos {
    display:inline-flex; align-items:center; gap:4px;
    background:#eff6ff; color:#1e40af;
    padding: 1px 8px; border-radius:100px;
    font-size:10.5px; font-weight:600; white-space:nowrap;
}
.mp-pos i { font-size:9px; }
.mp-meta {
    display:flex; align-items:center; gap:6px;
    font-size:11.5px; color:#64748b; margin-top:2px;
    overflow:hidden; text-overflow:ellipsis; white-space:nowrap;
}
.mp-dot { color:#cbd5e1; }

.mp-perm {
    border: 1px solid #e2e8f0; border-radius:6px;
    background:#fff; color:#475569;
    padding: 4px 8px;
    font-size:11.5px; font-weight:600;
    font-family: inherit; cursor:pointer; flex-shrink:0;
    transition: border-color .15s, color .15s, background .15s;
}
.mp-perm:hover { border-color:#cbd5e1; }
.mp-user.selected .mp-perm {
    border-color:#bfdbfe; color:#1e40af; background:#eff6ff;
}
.mp-perm:focus { outline:none; border-color:#0ea5e9; }

.mp-check {
    position:relative;
    display:flex; align-items:center; justify-content:center;
    flex-shrink:0; cursor:pointer;
    width:22px; height:22px;
}
.mp-checkbox { position:absolute; opacity:0; width:0; height:0; pointer-events:none; }
.mp-checkmark {
    width:20px; height:20px;
    border:2px solid #cbd5e1; border-radius:5px;
    background:#fff; position:relative;
    transition: all .15s;
}
.mp-user:hover .mp-checkmark { border-color:#94a3b8; }
.mp-checkbox:checked + .mp-checkmark { background:#0ea5e9; border-color:#0ea5e9; }
.mp-checkbox:checked + .mp-checkmark::after {
    content:''; position:absolute;
    left:5px; top:1px; width:6px; height:11px;
    border:solid #fff; border-width:0 2px 2px 0;
    transform:rotate(45deg);
}

.mp-empty, .mp-no-results {
    padding: 28px 18px; text-align:center;
    color:#94a3b8; font-size:13px;
}

.mp-actions {
    display:flex; align-items:center; gap:8px;
    padding:10px 12px;
    background:#f8fafc;
    border-top:1px solid #e2e8f0;
    flex-wrap: wrap;
}
.mp-action-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding: 6px 12px;
    border:1px solid #e2e8f0; border-radius:8px;
    background:#fff; color:#475569;
    font-size:12px; font-weight:600;
    cursor:pointer; font-family:inherit;
    transition: all .15s;
}
.mp-action-btn:hover { background:#eff6ff; color:#1e40af; border-color:#bfdbfe; }
.mp-action-btn.primary {
    background:#0ea5e9; color:#fff; border-color:#0ea5e9;
    margin-left:auto;
}
.mp-action-btn.primary:hover { background:#0284c7; border-color:#0284c7; color:#fff; }
.mp-action-btn.primary:disabled {
    background:#cbd5e1; border-color:#cbd5e1;
    cursor:not-allowed; color:#fff;
}
.mp-action-btn.primary:disabled:hover { background:#cbd5e1; border-color:#cbd5e1; color:#fff; }
.mp-action-btn i { font-size:11px; }

.shared-chips { display:flex; flex-wrap: wrap; gap:6px; margin-top: 8px; }
.shared-chip {
    display:flex; align-items:center; gap:8px;
    background:#e0f2fe; color:#0c4a6e;
    padding: 5px 8px 5px 5px;
    border-radius: 100px;
    font-size: 12.5px; font-weight: 500;
}
.shared-chip img { width:22px; height:22px; border-radius:50%; }
.shared-chip button {
    border:none; background:transparent; color:#0c4a6e;
    cursor:pointer; padding: 0 2px; font-size: 12px;
}
.shared-chip button:hover { color:#dc2626; }

/* ===== Groups tab: current grant rows ===== */
.group-row {
    display:flex; align-items:center; gap:12px;
    padding: 10px 8px;
    border-radius: 8px;
    transition: background .12s;
}
.group-row:hover { background:#f8fafc; }
.group-row .grp-ico {
    width: 36px; height: 36px; border-radius: 9px;
    flex-shrink: 0;
    background:#eff6ff; color:#0369a1;
    display:flex; align-items:center; justify-content:center;
    font-size: 15px;
}
.group-row .info { flex:1; min-width:0; }
.group-row .info .name { font-size: 13.5px; font-weight: 600; color:#0f172a; }
.group-row .info .sub { font-size: 12px; color:#64748b; }
.group-row .info .sub .type-tag { color:#475569; font-weight:600; }
.group-row select {
    border: 1.5px solid #e2e8f0; border-radius: 8px;
    padding: 6px 10px; font-size: 12.5px; font-weight: 500;
    background:#fff; color:#0f172a;
    cursor:pointer; font-family: inherit;
}
.group-row .remove-btn {
    border: none; background: transparent;
    color: #94a3b8;
    width: 28px; height: 28px; border-radius: 6px;
    cursor: pointer; transition: all .12s;
    display:flex; align-items:center; justify-content:center;
}
.group-row .remove-btn:hover { background:#fef2f2; color:#dc2626; }

/* ===== Groups tab: "add a group" composer ===== */
.grp-add {
    border: 1px solid #e2e8f0; border-radius: 12px;
    background:#f8fafc;
    padding: 14px;
    margin-top: 14px;
}
.grp-add .grp-add-title {
    font-size: 12px; font-weight: 700; color:#0f172a;
    text-transform: uppercase; letter-spacing: 0.06em;
    margin: 0 0 10px;
}
.grp-add .grp-grid {
    display:grid; grid-template-columns: 1fr 1fr; gap: 10px;
}
.grp-add label.grp-field { display:block; }
.grp-add .grp-field-label {
    font-size: 11.5px; font-weight: 600; color:#475569; margin-bottom: 5px; display:block;
}
.grp-add select {
    width:100%;
    border: 1.5px solid #e2e8f0; border-radius: 9px;
    padding: 9px 11px; font-size: 13px; font-weight: 500;
    background:#fff; color:#0f172a; cursor:pointer; font-family: inherit;
    transition: border-color .15s, box-shadow .15s;
}
.grp-add select:focus { outline:none; border-color:#0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); }
.grp-add select:disabled { background:#f1f5f9; color:#94a3b8; cursor:not-allowed; }
.grp-add .grp-add-foot {
    display:flex; align-items:center; justify-content:flex-end; gap:10px;
    margin-top: 12px;
}

/* ===== Link tab ===== */
.link-panel .link-box {
    border: 1px solid #e2e8f0; border-radius: 12px;
    background:#fff; padding: 14px;
}
.link-row { display:flex; gap:8px; align-items:center; }
.link-row input {
    flex:1; min-width:0;
    border: 1.5px solid #e2e8f0; border-radius: 9px;
    padding: 9px 11px; font-size: 12.5px;
    background:#f8fafc; color:#475569; font-family: inherit;
}
.link-row input:focus { outline:none; border-color:#0ea5e9; }
.link-actions { display:flex; flex-wrap:wrap; gap:8px; margin-top: 12px; }
.link-meta { font-size: 12px; color:#94a3b8; margin-top: 10px; }
.link-empty { text-align:center; padding: 8px 4px 4px; }
.link-empty .link-ico {
    width:56px; height:56px; border-radius:16px; margin: 0 auto 12px;
    background:#eff6ff; color:#0ea5e9;
    display:flex; align-items:center; justify-content:center; font-size: 22px;
}
.link-empty p { font-size: 13px; color:#64748b; margin: 0 0 14px; line-height:1.5; }

/* ============================================================
   SHARE & ACCESS DRAWER  (replaces the old centered modal)
   Right-anchored, full-height, segmented nav. Wraps the existing
   panel / list / picker markup in a modern shell — same IDs, so
   all behaviour is unchanged.
   ============================================================ */
body.mdrawer-lock { overflow: hidden; }

.mdrawer-backdrop {
    position: fixed; inset: 0;
    background: rgba(15, 23, 42, 0.42);
    z-index: 9998;
    opacity: 0; visibility: hidden;
    transition: opacity .28s ease, visibility .28s ease;
}
.mdrawer-backdrop.open { opacity: 1; visibility: visible; }

.mdrawer {
    position: absolute; top: 0; right: 0; height: 100%;
    width: 460px; max-width: 94vw;
    background: #fff;
    display: flex; flex-direction: column;
    box-shadow: -22px 0 50px rgba(15, 23, 42, 0.14);
    transform: translateX(100%);
    transition: transform .34s cubic-bezier(.22, .61, .36, 1);
    /* A deliberate type pairing for this elevated surface:
         Sora    → display / headlines (personality, modern)
         Outfit  → controls (keeps parity with the system sidebar)
         DM Sans → reading text (crisp, clear at small sizes)
       Outfit stays in every fallback, so it degrades to the system face if a
       webfont is slow. This panel renders outside .folder-page-root, so it must
       declare its own fonts explicitly. */
    --font-display: 'Sora', 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-ui: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    --font-text: 'DM Sans', 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-family: var(--font-text);
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    text-rendering: optimizeLegibility;
}
.mdrawer-backdrop.open .mdrawer { transform: translateX(0); }

/* Type roles — base is the reading face (DM Sans); specific zones opt in to
   the display (Sora) or control (Outfit) face. Font Awesome glyphs untouched. */
.mdrawer h3, .mdrawer p, .mdrawer span, .mdrawer label, .mdrawer a, .mdrawer div { font-family: inherit; }
.mdrawer button, .mdrawer select, .mdrawer input, .mdrawer textarea,
.mdrawer .mb-tab-badge { font-family: var(--font-ui); }
.mdrawer__title h3,
.mdrawer .grp-add-title,
.mdrawer .empty-box h4 { font-family: var(--font-display); }
.mdrawer .fas, .mdrawer .far, .mdrawer .fab,
.mdrawer [class^="fa-"], .mdrawer [class*=" fa-"] {
    font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
}

/* Header */
.mdrawer__head {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 20px 22px;
    border-bottom: 1px solid #eef2f7;
    flex-shrink: 0;
}
.mdrawer__title { display: flex; align-items: center; gap: 13px; min-width: 0; }
.mdrawer__ic {
    width: 42px; height: 42px; border-radius: 12px; flex-shrink: 0;
    background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd;
    display: flex; align-items: center; justify-content: center;
    font-size: 16px;
}
.mdrawer__title h3 {
    margin: 0; font-size: 17px; font-weight: 700;
    letter-spacing: -0.015em; color: #0f172a; line-height: 1.2;
}
.mdrawer__title p {
    margin: 3px 0 0; font-size: 12.5px; color: #94a3b8; font-weight: 500;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 320px;
}
.mdrawer__close {
    width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
    border: 1px solid #e2e8f0; background: #fff; color: #64748b; cursor: pointer;
    font-size: 15px; display: flex; align-items: center; justify-content: center;
    transition: background .15s, color .15s, border-color .15s;
}
.mdrawer__close:hover { background: #f1f5f9; color: #0f172a; }

/* Password note */
.mdrawer__note {
    margin: 14px 22px 0;
    display: flex; align-items: flex-start; gap: 9px;
    background: #eff6ff; border: 1px solid #dbeafe; color: #1e40af;
    border-radius: 11px; padding: 11px 13px;
    font-size: 12.5px; line-height: 1.45; font-weight: 500;
}
.mdrawer__note i { color: #0ea5e9; margin-top: 1px; }

/* Segmented nav — keeps the .mb-tab class the JS switcher targets */
.mdrawer__seg {
    position: relative;
    display: flex; gap: 2px;
    padding: 12px 14px 0;
    border-bottom: 1px solid #eef2f7;
    flex-shrink: 0;
    overflow-x: auto;
    scrollbar-width: none;
}
.mdrawer__seg::-webkit-scrollbar { display: none; }
.mdrawer__seg .mb-tab {
    flex: 1 0 auto;
    display: inline-flex; align-items: center; justify-content: center; gap: 7px;
    padding: 9px 12px 13px; border: none; background: transparent; cursor: pointer;
    font-family: var(--font-ui); font-size: 13.5px; font-weight: 600; color: #94a3b8;
    border-radius: 9px 9px 0 0; white-space: nowrap;
    transition: color .18s ease;
}
.mdrawer__seg .mb-tab i { font-size: 13px; }
.mdrawer__seg .mb-tab .drawer-tab-icon {
    width: 20px; height: 20px;
    object-fit: contain;
    flex-shrink: 0;
}
.mdrawer .empty-box .ico-c .drawer-empty-icon {
    width: 44px; height: 44px;
    object-fit: contain;
}
.mdrawer__seg .mb-tab:hover { color: #475569; }
.mdrawer__seg .mb-tab.active { color: #0284c7; }
.mdrawer__seg .mb-tab-badge {
    display: inline-flex; align-items: center; justify-content: center;
    min-width: 20px; height: 19px; padding: 0 6px; border-radius: 100px;
    background: #f1f5f9; color: #64748b; font-size: 11px; font-weight: 700; line-height: 1;
    transition: background .18s ease, color .18s ease;
}
.mdrawer__seg .mb-tab.active .mb-tab-badge { background: #e0f2fe; color: #0284c7; }

/* Sliding accent underline — positioned by JS to track the active tab. */
.mdrawer__ind {
    position: absolute; left: 0; bottom: 0;
    height: 2.5px; width: 0;
    background: #0ea5e9; border-radius: 3px 3px 0 0;
    transition: left .28s cubic-bezier(.4, 0, .2, 1), width .28s cubic-bezier(.4, 0, .2, 1);
    pointer-events: none;
}

/* Body + footer */
.mdrawer__body { flex: 1; overflow-y: auto; padding: 20px 22px 24px; }
.mdrawer__body::-webkit-scrollbar { width: 9px; }
.mdrawer__body::-webkit-scrollbar-track { background: #f8fafc; }
.mdrawer__body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 6px; }
.mdrawer__body::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
.mdrawer__foot {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 13px 22px; border-top: 1px solid #eef2f7; flex-shrink: 0;
}
.mdrawer__foot .hint { font-size: 12px; color: #94a3b8; font-weight: 500; }
.mdrawer__done {
    display: inline-flex; align-items: center; gap: 8px;
    padding: 10px 22px; border-radius: 10px; border: none; cursor: pointer;
    background: #0ea5e9; color: #fff; font-family: inherit; font-size: 13px; font-weight: 600;
    transition: background .15s, box-shadow .15s;
}
.mdrawer__done:hover { background: #0284c7; box-shadow: 0 6px 16px rgba(14, 165, 233, 0.30); }

/* Reused panel markup, tuned for the roomier drawer */
.mdrawer .mb-panel-hint { font-size: 12.5px; color: #64748b; line-height: 1.5; margin: 0 0 16px; }
.mdrawer .members-list { max-height: none; }
.mdrawer .member-row .info .name,
.mdrawer .group-row .info .name { font-size: 14px; }
.mdrawer .member-row .info .email { font-size: 12.5px; }

/* ============================================================
   ADD-ITEMS DRAWER — Windows Explorer-style file/exam picker
   (grid ⇄ list toggle; reuses the .mdrawer chrome + type system)
   ============================================================ */
.ai-toolbar {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 22px 12px; flex-shrink: 0;
}
.ai-search { position: relative; flex: 1; display: flex; align-items: center; }
.ai-search i { position: absolute; left: 12px; color: #94a3b8; font-size: 13px; pointer-events: none; }
.ai-search input {
    width: 100%; padding: 9px 12px 9px 33px;
    border: 1px solid #e2e8f0; border-radius: 9px; background: #f8fafc;
    font-size: 13px; color: #0f172a; outline: none;
    transition: border-color .15s, box-shadow .15s, background .15s;
}
.ai-search input:focus { border-color: #0ea5e9; background: #fff; box-shadow: 0 0 0 3px rgba(14,165,233,0.12); }
.ai-view { display: flex; gap: 2px; background: #f1f5f9; border-radius: 9px; padding: 3px; flex-shrink: 0; }
.ai-view button {
    width: 34px; height: 30px; border: none; background: transparent; border-radius: 7px;
    color: #94a3b8; cursor: pointer; font-size: 13px;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, color .15s, box-shadow .15s;
}
.ai-view button:hover { color: #475569; }
.ai-view button.active { background: #fff; color: #0284c7; box-shadow: 0 1px 2px rgba(15,23,42,0.10); }

.ai-items.ai-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(116px, 1fr)); gap: 8px; }
.ai-items.ai-list { display: flex; flex-direction: column; gap: 2px; }

.ai-item {
    position: relative; display: flex; cursor: pointer; user-select: none;
    border: 1.5px solid transparent; border-radius: 11px;
    transition: background .12s, border-color .12s;
}
.ai-item input { position: absolute; opacity: 0; width: 0; height: 0; pointer-events: none; }
.ai-check {
    width: 20px; height: 20px; border-radius: 6px; flex-shrink: 0;
    border: 2px solid #cbd5e1; background: #fff;
    display: flex; align-items: center; justify-content: center;
    transition: background .15s, border-color .15s, opacity .15s;
}
.ai-check i { font-size: 10px; color: #fff; opacity: 0; transition: opacity .12s; }
.ai-item:hover .ai-check { border-color: #94a3b8; }
.ai-item.selected .ai-check { background: #0ea5e9; border-color: #0ea5e9; }
.ai-item.selected .ai-check i { opacity: 1; }

/* File-type icon tints (match the folder-contents grid) */
.ai-ico.pdf i { color: #ef4444; }
.ai-ico.doc i { color: #2563eb; }
.ai-ico.xls i { color: #16a34a; }
.ai-ico.ppt i { color: #ea580c; }
.ai-ico.img i { color: #a855f7; }
.ai-ico.unk i { color: #64748b; }

/* Grid mode */
.ai-grid .ai-item { flex-direction: column; align-items: center; text-align: center; padding: 16px 8px 12px; }
.ai-grid .ai-item:hover { background: #f8fafc; border-color: #e2e8f0; }
.ai-grid .ai-item.selected { background: #eff6ff; border-color: #bae6fd; }
.ai-grid .ai-ico { width: 54px; height: 54px; display: flex; align-items: center; justify-content: center; margin-bottom: 6px; }
.ai-grid .ai-ico i { font-size: 38px; }
.ai-grid .ai-name { font-size: 12.5px; font-weight: 500; color: #1e293b; line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; word-break: break-word; }
.ai-grid .ai-meta { font-size: 10.5px; font-weight: 600; color: #94a3b8; margin-top: 3px; letter-spacing: .04em; }
.ai-grid .ai-check { position: absolute; top: 8px; right: 8px; opacity: 0; }
.ai-grid .ai-item:hover .ai-check,
.ai-grid .ai-item.selected .ai-check { opacity: 1; }

/* List mode */
.ai-list .ai-item { flex-direction: row; align-items: center; gap: 12px; padding: 9px 12px; border-radius: 9px; }
.ai-list .ai-item:hover { background: #f8fafc; }
.ai-list .ai-item.selected { background: #eff6ff; }
.ai-list .ai-ico { width: 28px; flex-shrink: 0; display: flex; align-items: center; justify-content: center; }
.ai-list .ai-ico i { font-size: 20px; }
.ai-list .ai-name { flex: 1; min-width: 0; font-size: 13.5px; font-weight: 500; color: #1e293b;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.ai-list .ai-meta { font-size: 11px; font-weight: 700; color: #475569; background: #f1f5f9;
    padding: 3px 8px; border-radius: 5px; letter-spacing: .04em; flex-shrink: 0; }

/* Answer-key marker on exam rows in the Add-items drawer — same plasticine key
   icon as the tiles. Top-left badge in grid view, small inline icon in list view. */
.ai-key {
    position: absolute; top: 6px; left: 6px;
    width: 22px; height: 22px; padding: 2px;
    border-radius: 6px; background: #fff; border: 1px solid #e2e8f0;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 1px 4px rgba(15,23,42,0.14);
    z-index: 2;
}
.ai-key img { width: 100%; height: 100%; object-fit: contain; display: block; }
.ai-list .ai-key { position: static; width: 20px; height: 20px; padding: 0; border: none; box-shadow: none; flex-shrink: 0; }

.ai-noresult { padding: 26px 8px; text-align: center; color: #94a3b8; font-size: 13px; }

/* Footer selection cluster */
.ai-foot-left { display: flex; align-items: center; gap: 6px; }
.ai-link {
    border: none; background: transparent; cursor: pointer;
    font-family: var(--font-ui); font-size: 12.5px; font-weight: 600; color: #0284c7;
    padding: 4px 6px; border-radius: 6px;
}
.ai-link:hover { background: #eff6ff; }
.ai-foot-left .ai-count { font-size: 12px; color: #64748b; font-weight: 500; margin-left: 4px; }
.ai-foot-left .ai-count b { color: #0284c7; font-weight: 700; }
.mdrawer__done:disabled { background: #cbd5e1; cursor: not-allowed; box-shadow: none; }

@media (max-width: 520px) {
    .mdrawer { width: 100%; max-width: 100%; }
}
/* Larger, comfortable reading on big monitors without ballooning anything. */
@media (min-width: 1600px) {
    .mdrawer { width: 520px; }
    .mdrawer__title h3 { font-size: 18.5px; }
    .mdrawer__title p { font-size: 13px; }
    .mdrawer__seg .mb-tab { font-size: 14px; }
    .mdrawer .mb-panel-hint { font-size: 13px; }
    .mdrawer .member-row .info .name,
    .mdrawer .group-row .info .name { font-size: 14.5px; }
    .mdrawer .member-row .info .email,
    .mdrawer .group-row .info .sub { font-size: 13px; }
}
</style>

<div class="mdrawer-backdrop" id="membersModal">
    <aside class="mdrawer" role="dialog" aria-modal="true" aria-label="Share and access">
        <header class="mdrawer__head">
            <div class="mdrawer__title">
                <span class="mdrawer__ic"><i class="fas fa-share-nodes"></i></span>
                <div style="min-width:0;">
                    <h3>Share &amp; access</h3>
                    <p>{{ $folder->name }}</p>
                </div>
            </div>
            <button type="button" class="mdrawer__close" id="mdrawerClose" aria-label="Close"><i class="fas fa-xmark"></i></button>
        </header>

        @if($isLocked)
            <div class="mdrawer__note">
                <i class="fas fa-shield-halved"></i>
                <span>Password-protected folder. Shared users get direct access without entering the password.</span>
            </div>
        @endif

        @php
            $initialMemberCount = $folder->members->count();
            $invitableCount = $invitableUsers->count();
            // Default tab: jump straight to "Add people" if there's nobody to manage yet.
            $defaultTab = $initialMemberCount > 0 ? 'members' : 'add';
        @endphp

        <nav class="mdrawer__seg" role="tablist">
            <button type="button" class="mb-tab {{ $defaultTab === 'members' ? 'active' : '' }}" data-mb-tab="members" role="tab">
                <i class="fas fa-user-check"></i> Members
                <span class="mb-tab-badge" id="mbBadgeMembers">{{ $initialMemberCount }}</span>
            </button>
            <button type="button" class="mb-tab {{ $defaultTab === 'add' ? 'active' : '' }}" data-mb-tab="add" role="tab">
                <i class="fas fa-user-plus"></i> Add
                <span class="mb-tab-badge" id="mbBadgeAdd">{{ $invitableCount }}</span>
            </button>
            <button type="button" class="mb-tab" data-mb-tab="groups" role="tab">
                <i class="fas fa-people-group"></i> Groups
                <span class="mb-tab-badge" id="mbBadgeGroups">{{ $folder->grants->count() }}</span>
            </button>
            <button type="button" class="mb-tab" data-mb-tab="link" role="tab">
                <i class="fas fa-link"></i> Link
            </button>
            <span class="mdrawer__ind" id="mbSegInd" aria-hidden="true"></span>
        </nav>

        <div class="mdrawer__body">

        {{-- ============ TAB: CURRENT MEMBERS ============ --}}
        <div class="mb-panel {{ $defaultTab === 'members' ? 'active' : '' }}" id="mbPanelMembers" role="tabpanel">
            <p class="mb-panel-hint">Everyone with direct access. Adjust a role or remove someone.</p>
            <div class="members-list" id="membersList">
                <div class="empty-box" style="padding: 24px 8px;">
                    <div class="ico-c" style="width:60px; height:60px; font-size:22px;"><i class="fas fa-user-plus"></i></div>
                    <h4>No members yet</h4>
                    <p>Switch to <strong>Add people</strong> to invite collaborators.</p>
                </div>
            </div>
        </div>

        {{-- ============ TAB: ADD PEOPLE ============ --}}
        <div class="mb-panel {{ $defaultTab === 'add' ? 'active' : '' }}" id="mbPanelAdd" role="tabpanel">
            <p class="mb-panel-hint">Select people and choose a role. Each one is notified instantly.</p>

            <div class="mp-picker">
                <div class="mp-toolbar">
                    <div class="mp-search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="memberPickerSearch" placeholder="Search by name, email, position, or department..." autocomplete="off">
                    </div>
                    <div class="mp-stats">
                        <span id="mpSelectedCount">0</span> of <span id="mpTotalCount">{{ $invitableCount }}</span> selected
                    </div>
                </div>

                <div class="mp-list" id="mpList">
                    @forelse($invitableUsers as $u)
                        @php
                            $firstName = $u->first_name ?: '';
                            $lastName = $u->last_name ?: '';
                            $fullName = trim($firstName . ' ' . $lastName);
                            if ($fullName === '') { $fullName = $u->email; }
                            $initials = strtoupper(substr($firstName !== '' ? $firstName : 'U', 0, 1) . substr($lastName !== '' ? $lastName : '', 0, 1));
                            if ($initials === '') { $initials = strtoupper(substr($u->email, 0, 1)); }
                            $positionName = optional($u->position)->name;
                            $departmentName = optional($u->department)->name;
                            $searchTerms = strtolower(trim($fullName . ' ' . $u->email . ' ' . ($positionName ?? '') . ' ' . ($departmentName ?? '')));
                            $avatarUrl = $u->profile_picture ? asset('profile_pictures/' . $u->profile_picture) : null;
                        @endphp
                        <div class="mp-user" data-user-id="{{ $u->id }}" data-search="{{ $searchTerms }}">
                            <div class="mp-av">
                                @if($avatarUrl)
                                    <img src="{{ $avatarUrl }}" alt="">
                                @else
                                    <div class="mp-av-fallback">{{ $initials }}</div>
                                @endif
                            </div>
                            <div class="mp-info">
                                <div class="mp-name">
                                    <span>{{ $fullName }}</span>
                                    @if($positionName)
                                        <span class="mp-pos"><i class="fas fa-briefcase"></i> {{ $positionName }}</span>
                                    @endif
                                </div>
                                <div class="mp-meta">
                                    <span class="mp-email">{{ $u->email }}</span>
                                    @if($departmentName)
                                        <span class="mp-dot">·</span>
                                        <span class="mp-dept">{{ $departmentName }}</span>
                                    @endif
                                </div>
                            </div>
                            <select class="mp-perm" data-user-id="{{ $u->id }}">
                                <option value="viewer" selected>Viewer</option>
                                <option value="editor">Editor</option>
                            </select>
                            <label class="mp-check">
                                <input type="checkbox" class="mp-checkbox" value="{{ $u->id }}">
                                <span class="mp-checkmark"></span>
                            </label>
                        </div>
                    @empty
                        <div class="mp-empty">Everyone in the system is already a member of this folder.</div>
                    @endforelse
                    <div class="mp-no-results" id="mpNoResults" style="display:none;">No users match your search.</div>
                </div>

                @if($invitableCount > 0)
                    <div class="mp-actions">
                        <button type="button" class="mp-action-btn" id="mpSelectAll"><i class="fas fa-check-circle"></i> Select All</button>
                        <button type="button" class="mp-action-btn" id="mpClearAll"><i class="fas fa-times-circle"></i> Clear All</button>
                        <button type="button" class="mp-action-btn primary" id="mpInviteBtn" disabled><i class="fas fa-user-plus"></i> Invite <span id="mpInviteCount">0</span></button>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ TAB: GROUPS ============ --}}
        <div class="mb-panel" id="mbPanelGroups" role="tabpanel">
            <p class="mb-panel-hint">Share with an entire group. Access follows membership — joiners gain it, leavers lose it. For a one-off person, use <strong>Add</strong>.</p>

            <div class="members-list" id="folderGroupsList">
                <div class="empty-box" style="padding: 24px 8px;">
                    <div class="ico-c" style="width:60px; height:60px; font-size:22px;"><i class="fas fa-people-group"></i></div>
                    <h4>No groups yet</h4>
                    <p>Use the picker below to share with a department, staff category, committee, office, leadership pool, or everyone.</p>
                </div>
            </div>

            <div class="grp-add">
                <div class="grp-add-title">Share with a group</div>
                <div class="grp-grid">
                    <label class="grp-field">
                        <span class="grp-field-label">Group type</span>
                        <select id="grpType">
                            <option value="" selected disabled>Choose a type…</option>
                            @foreach($audienceTypes as $at)
                                <option value="{{ $at['type'] }}">{{ $at['label'] }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="grp-field">
                        <span class="grp-field-label">Group</span>
                        <select id="grpValue" disabled>
                            <option value="" selected disabled>Select a type first…</option>
                        </select>
                    </label>
                </div>
                <div class="grp-grid" style="margin-top:10px;">
                    <label class="grp-field">
                        <span class="grp-field-label">Permission</span>
                        <select id="grpPerm">
                            <option value="viewer" selected>Viewer — can open &amp; download</option>
                            <option value="editor">Editor — can also add their own items</option>
                        </select>
                    </label>
                    <div></div>
                </div>
                <div class="grp-add-foot">
                    <button type="button" class="mp-action-btn primary" id="grpAddBtn" style="margin-left:0;" disabled><i class="fas fa-plus"></i> Share with group</button>
                </div>
            </div>
        </div>

        {{-- ============ TAB: LINK ============ --}}
        @php $joinUrl = $folder->share_token ? route('dashboard.folders.join', ['folder' => $folder->id, 'token' => $folder->share_token]) : ''; @endphp
        <div class="mb-panel link-panel" id="mbPanelLink" role="tabpanel">
            <p class="mb-panel-hint">Anyone with this link joins as a <strong>Viewer</strong> and appears under Members. Reset or turn it off anytime.</p>

            <div class="link-box" id="linkActive" style="{{ $folder->share_token ? '' : 'display:none;' }}">
                <div class="link-row">
                    <input type="text" id="linkUrl" readonly value="{{ $joinUrl }}" onclick="this.select()">
                    <button type="button" class="mp-action-btn primary" id="linkCopyBtn" style="margin-left:0;"><i class="fas fa-copy"></i> Copy</button>
                </div>
                <div class="link-actions">
                    <button type="button" class="mp-action-btn" id="linkResetBtn"><i class="fas fa-rotate"></i> Reset link</button>
                    <button type="button" class="mp-action-btn" id="linkDisableBtn" style="color:#dc2626; border-color:#fecaca;"><i class="fas fa-link-slash"></i> Turn off link</button>
                </div>
                <div class="link-meta" id="linkMeta">{{ $folder->share_token_created_at ? 'Link active · created ' . $folder->share_token_created_at->diffForHumans() : 'Link active' }}</div>
            </div>

            <div class="link-empty" id="linkEmpty" style="{{ $folder->share_token ? 'display:none;' : '' }}">
                <div class="link-ico"><i class="fas fa-link"></i></div>
                <p>No share link yet. Create one to give access to several people at once — handy for a whole class or team.</p>
                <button type="button" class="mp-action-btn primary" id="linkCreateBtn" style="margin-left:0;"><i class="fas fa-link"></i> Create share link</button>
            </div>
        </div>

        </div>{{-- /.mdrawer__body --}}

        <footer class="mdrawer__foot">
            <span class="hint">Changes are saved automatically</span>
            <button type="button" class="mdrawer__done" id="mdrawerDone">Done</button>
        </footer>
    </aside>
</div>
@endif

<div class="toast-mini" id="folderToast"><i class="fas fa-circle-check"></i><span></span></div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    // ===================================================================
    //  ADD-ITEMS DRAWER (Explorer-style grid/list picker)
    // ===================================================================
    const openBtn = document.getElementById('openAddModal');
    const addDrawer = document.getElementById('addModal');
    const addTabs = addDrawer ? addDrawer.querySelectorAll('[data-add-tab]') : [];
    const filesForm = document.getElementById('addFilesForm');
    const examsForm = document.getElementById('addExamsForm');
    const aiSearch = document.getElementById('aiSearch');
    const aiViewBtns = addDrawer ? addDrawer.querySelectorAll('.ai-view button') : [];
    const aiListFiles = document.getElementById('aiListFiles');
    const aiListExams = document.getElementById('aiListExams');
    const aiSubmit = document.getElementById('aiSubmit');
    const aiCount = document.getElementById('aiCount');
    const addSegInd = document.getElementById('addSegInd');
    let aiActive = 'files';

    const aiActiveList = () => (aiActive === 'files' ? aiListFiles : aiListExams);

    function positionAddIndicator() {
        if (!addSegInd) return;
        const nav = addSegInd.parentElement;
        const active = nav ? nav.querySelector('.mb-tab.active') : null;
        if (!active) return;
        addSegInd.style.width = active.offsetWidth + 'px';
        addSegInd.style.left = active.offsetLeft + 'px';
    }

    function aiUpdateCount() {
        const list = aiActiveList();
        const n = list ? list.querySelectorAll('input:checked').length : 0;
        if (aiCount) aiCount.textContent = n;
        if (aiSubmit) {
            aiSubmit.disabled = n === 0;
            const noun = aiActive === 'files' ? 'file' : 'exam';
            aiSubmit.innerHTML = n === 0
                ? '<i class="fas fa-check"></i> Add'
                : '<i class="fas fa-check"></i> Add ' + n + ' ' + noun + (n === 1 ? '' : 's');
        }
    }

    function aiFilter() {
        const list = aiActiveList();
        if (!list) return;
        const q = (aiSearch ? aiSearch.value : '').toLowerCase().trim();
        let visible = 0;
        list.querySelectorAll('.ai-item').forEach(it => {
            const match = q === '' || (it.getAttribute('data-search') || '').includes(q);
            it.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        const nores = list.querySelector('.ai-noresult');
        if (nores) nores.style.display = (visible === 0 && list.querySelectorAll('.ai-item').length > 0) ? '' : 'none';
    }

    function aiSwitch(kind) {
        aiActive = kind;
        addTabs.forEach(t => t.classList.toggle('active', t.getAttribute('data-add-tab') === kind));
        if (filesForm) filesForm.style.display = kind === 'files' ? '' : 'none';
        if (examsForm) examsForm.style.display = kind === 'exams' ? '' : 'none';
        if (aiSubmit) aiSubmit.setAttribute('form', kind === 'files' ? 'addFilesForm' : 'addExamsForm');
        if (aiSearch) aiSearch.value = '';
        aiFilter();
        positionAddIndicator();
        aiUpdateCount();
    }

    function aiSetView(view) {
        aiViewBtns.forEach(b => b.classList.toggle('active', b.getAttribute('data-view') === view));
        [aiListFiles, aiListExams].forEach(l => {
            if (!l) return;
            l.classList.toggle('ai-grid', view === 'grid');
            l.classList.toggle('ai-list', view === 'list');
        });
        try { localStorage.setItem('folderAddView', view); } catch (e) {}
    }

    [aiListFiles, aiListExams].forEach(list => {
        if (!list) return;
        list.addEventListener('change', e => {
            const cb = e.target.closest('input[type="checkbox"]');
            if (!cb) return;
            const item = cb.closest('.ai-item');
            if (item) item.classList.toggle('selected', cb.checked);
            aiUpdateCount();
        });
    });

    document.querySelectorAll('[data-ai-all]').forEach(b => b.addEventListener('click', () => {
        const list = aiActiveList();
        if (!list) return;
        list.querySelectorAll('.ai-item').forEach(it => {
            if (it.style.display === 'none') return;
            const cb = it.querySelector('input');
            if (cb) cb.checked = true;
            it.classList.add('selected');
        });
        aiUpdateCount();
    }));
    document.querySelectorAll('[data-ai-clear]').forEach(b => b.addEventListener('click', () => {
        const list = aiActiveList();
        if (!list) return;
        list.querySelectorAll('.ai-item').forEach(it => {
            const cb = it.querySelector('input');
            if (cb) cb.checked = false;
            it.classList.remove('selected');
        });
        aiUpdateCount();
    }));

    addTabs.forEach(t => t.addEventListener('click', () => aiSwitch(t.getAttribute('data-add-tab'))));
    aiViewBtns.forEach(b => b.addEventListener('click', () => aiSetView(b.getAttribute('data-view'))));
    aiSearch?.addEventListener('input', aiFilter);
    window.addEventListener('resize', positionAddIndicator);
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(positionAddIndicator);

    function openAddDrawer() {
        addDrawer.classList.add('open');
        document.body.classList.add('mdrawer-lock');
        setTimeout(positionAddIndicator, 90);
    }
    function closeAddDrawer() {
        addDrawer.classList.remove('open');
        document.body.classList.remove('mdrawer-lock');
    }

    if (openBtn && addDrawer) {
        openBtn.addEventListener('click', openAddDrawer);
        addDrawer.addEventListener('click', e => { if (e.target === addDrawer) closeAddDrawer(); });
        document.getElementById('addDrawerClose')?.addEventListener('click', closeAddDrawer);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && addDrawer.classList.contains('open')) closeAddDrawer();
        });
        let savedView = 'grid';
        try { savedView = localStorage.getItem('folderAddView') || 'grid'; } catch (e) {}
        aiSetView(savedView);
        aiUpdateCount();
    }

    // --- Toast helper ---
    const toast = document.getElementById('folderToast');
    const toastMsg = toast?.querySelector('span');
    function notify(msg, type) {
        if (!toast) return;
        toast.style.background = type === 'err' ? '#7f1d1d' : '#0f172a';
        const icon = toast.querySelector('i');
        if (icon) icon.className = type === 'err' ? 'fas fa-triangle-exclamation' : 'fas fa-circle-check';
        if (toastMsg) toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(window._folderToastT);
        window._folderToastT = setTimeout(() => toast.classList.remove('show'), 2400);
    }

    // --- Live count helpers (banner + section heading) ---
    const sectionCount = document.querySelector('.folder-section-head h3 .count');
    const metaFiles = document.querySelector('.folder-banner .meta span:nth-child(1)');
    const metaExams = document.querySelector('.folder-banner .meta span:nth-child(2)');
    function adjustCount(el, delta) {
        if (!el) return;
        const text = el.textContent;
        const m = text.match(/(\d+)/);
        if (!m) return;
        const next = Math.max(0, parseInt(m[1], 10) + delta);
        el.textContent = text.replace(/(\d+)/, next);
    }
    function adjustTotals(kind, delta) {
        adjustCount(sectionCount, delta);
        if (kind === 'file') adjustCount(metaFiles, delta);
        if (kind === 'exam') adjustCount(metaExams, delta);
    }

    // --- Context menu ---
    const ctx = document.getElementById('folderCtx');
    const ctxEditAction = ctx.querySelector('[data-action="edit"]');
    let active = null;
    function openCtx(x, y, tile) {
        active = tile;
        // "Edit details" edits the underlying file/exam record, so only the
        // item's owner may see it. Viewers (and editors who don't own the item)
        // get view/download only.
        if (ctxEditAction) ctxEditAction.style.display = tile.dataset.canEdit === '1' ? '' : 'none';
        // Key actions appear only for exams that have an answer key; the download
        // then bundles exam + key into a ZIP.
        const hasKey = tile.dataset.hasKey === '1';
        const keyItem = ctx.querySelector('.ctx-key-item');
        if (keyItem) keyItem.style.display = hasKey ? '' : 'none';
        const dlLabel = ctx.querySelector('.ctx-dl-label');
        if (dlLabel) dlLabel.textContent = hasKey ? 'Download all (ZIP)' : 'Download';
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
        const keyBadge = tile.querySelector('.key-badge');
        if (keyBadge) {
            keyBadge.addEventListener('mousedown', e => e.stopPropagation());
            keyBadge.addEventListener('click', e => {
                e.preventDefault(); e.stopPropagation();
                const keyUrl = tile.getAttribute('data-key-view-url');
                if (keyUrl) window.open(keyUrl, '_blank');
            });
        }
    });
    document.addEventListener('click', e => { if (!ctx.contains(e.target)) closeCtx(); });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeCtx(); });

    function fadeOut(tile) {
        tile.style.transition = 'opacity .2s, transform .2s';
        tile.style.opacity = '0';
        tile.style.transform = 'scale(0.85)';
        setTimeout(() => { if (tile.parentNode) tile.parentNode.removeChild(tile); }, 200);
    }
    function restore(tile) {
        tile.style.transition = 'opacity .2s, transform .2s';
        tile.style.opacity = '1';
        tile.style.transform = 'scale(1)';
    }

    async function removeFromFolder(tile) {
        const url = tile.getAttribute('data-remove-url');
        const kind = tile.querySelector('.sub')?.textContent?.toLowerCase().includes('exam') ? 'exam' : 'file';

        // Optimistic UI
        fadeOut(tile);
        adjustTotals(kind, -1);

        let res, raw = '', data = {};
        try {
            res = await fetch(url, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            raw = await res.text();
            try { data = raw ? JSON.parse(raw) : {}; } catch (_) { data = {}; }
        } catch (err) {
            console.error('[folder] remove fetch failed:', err);
            restore(tile);
            adjustTotals(kind, +1);
            notify('Could not reach the server.', 'err');
            return;
        }
        if (res.ok && (data.ok ?? true)) {
            notify(data.message || 'Removed from folder', 'ok');
        } else {
            console.error('[folder] remove rejected:', res.status, raw);
            restore(tile);
            adjustTotals(kind, +1);
            notify(data.message || ('Could not remove (status ' + res.status + ')'), 'err');
        }
    }

    ctx.querySelectorAll('[data-action]').forEach(b => {
        b.addEventListener('click', e => {
            e.preventDefault();
            if (!active) return;
            const a = b.getAttribute('data-action');
            const tile = active;
            closeCtx();
            if (a === 'view') window.open(tile.getAttribute('data-view-url'), '_blank');
            else if (a === 'view-key') { const kUrl = tile.getAttribute('data-key-view-url'); if (kUrl) window.open(kUrl, '_blank'); }
            else if (a === 'download') {
                const hasKey = tile.getAttribute('data-has-key') === '1';
                const bundleUrl = tile.getAttribute('data-bundle-url');
                window.location.href = (hasKey && bundleUrl) ? bundleUrl : tile.getAttribute('data-download-url');
            }
            else if (a === 'edit') window.location.href = tile.getAttribute('data-edit-url');
            else if (a === 'remove') {
                if (!confirm('Remove "' + (tile.getAttribute('data-name') || 'this item') + '" from this folder?\nThe item itself will not be deleted.')) return;
                removeFromFolder(tile);
            }
        });
    });

    // ===================================================================
    //  MEMBERS MODAL (owner only — element won't exist for non-owners)
    // ===================================================================
    const membersBtn = document.getElementById('openMembersModal');
    const membersModal = document.getElementById('membersModal');
    const membersList = document.getElementById('membersList');
    const FOLDER_ID = {{ $folder->id }};

    // ---- Tabs: "Current members" vs "Add people" ----
    const mbTabs = membersModal ? membersModal.querySelectorAll('.mb-tab') : [];
    const mbPanels = {
        members: document.getElementById('mbPanelMembers'),
        add: document.getElementById('mbPanelAdd'),
        groups: document.getElementById('mbPanelGroups'),
        link: document.getElementById('mbPanelLink'),
    };
    const mbBadgeMembers = document.getElementById('mbBadgeMembers');
    const mbBadgeAdd = document.getElementById('mbBadgeAdd');

    // Slide the accent underline under the active tab (offset metrics share the
    // tab's coordinate space, so it stays aligned even if the nav scrolls).
    const mbSegInd = document.getElementById('mbSegInd');
    function positionSegIndicator() {
        if (!mbSegInd) return;
        const nav = mbSegInd.parentElement;
        const active = nav ? nav.querySelector('.mb-tab.active') : null;
        if (!active) return;
        mbSegInd.style.width = active.offsetWidth + 'px';
        mbSegInd.style.left = active.offsetLeft + 'px';
    }

    function switchMembersTab(name) {
        if (!mbPanels[name]) return;
        mbTabs.forEach(t => {
            t.classList.toggle('active', t.getAttribute('data-mb-tab') === name);
        });
        Object.keys(mbPanels).forEach(key => {
            mbPanels[key]?.classList.toggle('active', key === name);
        });
        positionSegIndicator();
    }
    mbTabs.forEach(t => {
        t.addEventListener('click', () => switchMembersTab(t.getAttribute('data-mb-tab')));
    });
    window.addEventListener('resize', positionSegIndicator);
    // Re-align once the Outfit webfont finishes loading (tab widths shift).
    if (document.fonts && document.fonts.ready) document.fonts.ready.then(positionSegIndicator);

    function setBadge(el, n) {
        if (el) el.textContent = String(Math.max(0, n));
    }

    // ---- Invite picker (compose-memo style: full list + filter + batch invite) ----
    const mpSearch = document.getElementById('memberPickerSearch');
    const mpList = document.getElementById('mpList');
    const mpNoResults = document.getElementById('mpNoResults');
    const mpSelectedCount = document.getElementById('mpSelectedCount');
    const mpSelectAll = document.getElementById('mpSelectAll');
    const mpClearAll = document.getElementById('mpClearAll');
    const mpInviteBtn = document.getElementById('mpInviteBtn');
    const mpInviteCount = document.getElementById('mpInviteCount');

    // Map<string user_id, 'viewer' | 'editor'>
    const pickerSelections = new Map();
    const mpRows = mpList ? Array.from(mpList.querySelectorAll('.mp-user')) : [];

    function updatePickerCount() {
        const n = pickerSelections.size;
        if (mpSelectedCount) mpSelectedCount.textContent = String(n);
        if (mpInviteCount) mpInviteCount.textContent = String(n);
        if (mpInviteBtn) mpInviteBtn.disabled = n === 0;
    }

    function setPickerRow(row, isSelected, permission) {
        const userId = row.getAttribute('data-user-id');
        if (!userId) return;
        const cb = row.querySelector('.mp-checkbox');
        const sel = row.querySelector('.mp-perm');
        if (isSelected) {
            row.classList.add('selected');
            if (cb) cb.checked = true;
            if (sel && permission) sel.value = permission;
            pickerSelections.set(String(userId), permission || (sel ? sel.value : 'viewer') || 'viewer');
        } else {
            row.classList.remove('selected');
            if (cb) cb.checked = false;
            if (sel) sel.value = 'viewer';
            pickerSelections.delete(String(userId));
        }
        updatePickerCount();
    }

    function applyPickerFilter() {
        const q = (mpSearch?.value || '').toLowerCase().trim();
        let visible = 0;
        mpRows.forEach(row => {
            const terms = row.getAttribute('data-search') || '';
            const match = q === '' || terms.includes(q);
            row.style.display = match ? '' : 'none';
            if (match) visible++;
        });
        if (mpNoResults) mpNoResults.style.display = (mpRows.length > 0 && visible === 0) ? '' : 'none';
    }

    mpRows.forEach(row => {
        row.addEventListener('click', e => {
            if (e.target.closest('.mp-perm')) return;
            // Clicks inside the checkbox label toggle the (hidden) input natively,
            // which fires the 'change' listener below. Don't also toggle here or the
            // two cancel out and the checkbox appears unresponsive.
            if (e.target.closest('.mp-check')) return;
            const cb = row.querySelector('.mp-checkbox');
            if (!cb) return;
            setPickerRow(row, !cb.checked);
        });
        const cb = row.querySelector('.mp-checkbox');
        if (cb) cb.addEventListener('change', () => setPickerRow(row, cb.checked));

        const sel = row.querySelector('.mp-perm');
        if (sel) {
            sel.addEventListener('click', e => e.stopPropagation());
            sel.addEventListener('mousedown', e => e.stopPropagation());
            sel.addEventListener('change', e => {
                e.stopPropagation();
                const uid = row.getAttribute('data-user-id');
                if (!uid) return;
                if (pickerSelections.has(String(uid))) {
                    pickerSelections.set(String(uid), sel.value);
                } else {
                    setPickerRow(row, true, sel.value);
                }
            });
        }
    });

    mpSearch?.addEventListener('input', applyPickerFilter);
    mpSelectAll?.addEventListener('click', () => {
        mpRows.forEach(row => { if (row.style.display !== 'none') setPickerRow(row, true); });
    });
    mpClearAll?.addEventListener('click', () => {
        mpRows.forEach(row => setPickerRow(row, false));
    });

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    async function loadMembers() {
        if (!membersList) return;
        try {
            const res = await fetch('{{ route("dashboard.folders.members", $folder) }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            if (!data.ok) return;
            renderMembers(data.members);
        } catch (e) {
            console.error('[members] load failed:', e);
        }
    }
    function renderMembers(members) {
        const count = members ? members.length : 0;
        setBadge(mbBadgeMembers, count);
        if (count === 0) {
            membersList.innerHTML = '<div class="empty-box" style="padding:24px 8px;"><div class="ico-c" style="width:60px;height:60px;font-size:22px;"><i class="fas fa-user-plus"></i></div><h4>No members yet</h4><p>Switch to <strong>Add people</strong> to invite collaborators.</p></div>';
            return;
        }
        membersList.innerHTML = members.map(m => `
            <div class="member-row" data-user-id="${m.id}">
                <img class="avatar" src="${escapeHtml(m.avatar)}" alt="">
                <div class="info">
                    <div class="name">${escapeHtml(m.name)}</div>
                    <div class="email">${escapeHtml(m.email)}</div>
                </div>
                <select class="member-perm" data-user-id="${m.id}">
                    <option value="viewer" ${m.permission === 'viewer' ? 'selected' : ''}>Viewer</option>
                    <option value="editor" ${m.permission === 'editor' ? 'selected' : ''}>Editor</option>
                </select>
                <button type="button" class="remove-btn" data-user-id="${m.id}" title="Remove from folder"><i class="fas fa-xmark"></i></button>
            </div>
        `).join('');

        // Wire permission change
        membersList.querySelectorAll('.member-perm').forEach(sel => {
            sel.addEventListener('change', () => updateMemberPermission(sel.getAttribute('data-user-id'), sel.value));
        });
        // Wire remove
        membersList.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', () => removeMember(btn.getAttribute('data-user-id')));
        });
    }

    async function updateMemberPermission(userId, permission) {
        try {
            const res = await fetch(`/dashboard/folders/${FOLDER_ID}/members/${userId}`, {
                method: 'PATCH',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ permission }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) notify('Permission updated', 'ok');
            else notify(data.message || 'Could not update permission', 'err');
        } catch (e) { notify('Network error', 'err'); }
    }

    async function removeMember(userId) {
        const row = membersList.querySelector(`.member-row[data-user-id="${userId}"]`);
        if (!row) return;
        if (!confirm('Remove this member from the folder?')) return;
        row.style.transition = 'opacity .2s'; row.style.opacity = '0.4';
        try {
            const res = await fetch(`/dashboard/folders/${FOLDER_ID}/members/${userId}`, {
                method: 'DELETE',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                row.remove();
                notify('Member removed', 'ok');
                // Update the "Current members" badge.
                const remaining = membersList.querySelectorAll('.member-row').length;
                setBadge(mbBadgeMembers, remaining);
                if (remaining === 0) renderMembers([]);
            } else {
                row.style.opacity = '1';
                notify(data.message || 'Could not remove member', 'err');
            }
        } catch (e) {
            row.style.opacity = '1';
            notify('Network error', 'err');
        }
    }

    async function inviteSelected() {
        if (pickerSelections.size === 0) return;
        const members = [];
        pickerSelections.forEach((permission, userId) => {
            members.push({ user_id: Number(userId), permission });
        });

        const originalHtml = mpInviteBtn.innerHTML;
        mpInviteBtn.disabled = true;
        mpInviteBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Inviting...';

        try {
            const res = await fetch('{{ route("dashboard.folders.share", $folder) }}', {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ members }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                const addedIds = (data.added || []).map(a => String(a.id));
                // Remove invited rows from the picker so they can't be added twice.
                addedIds.forEach(id => {
                    const row = mpList?.querySelector(`.mp-user[data-user-id="${id}"]`);
                    if (row) row.remove();
                });
                // Drop them from the in-memory row list + selection map.
                for (let i = mpRows.length - 1; i >= 0; i--) {
                    if (addedIds.includes(mpRows[i].getAttribute('data-user-id'))) {
                        mpRows.splice(i, 1);
                    }
                }
                pickerSelections.clear();
                if (mpSearch) mpSearch.value = '';
                applyPickerFilter();
                updatePickerCount();
                const mpTotal = document.getElementById('mpTotalCount');
                if (mpTotal) mpTotal.textContent = String(mpRows.length);
                setBadge(mbBadgeAdd, mpRows.length);
                notify(addedIds.length + ' member(s) invited', 'ok');
                loadMembers();
                // Auto-switch back to "Current members" so the owner sees the result.
                switchMembersTab('members');
            } else {
                notify(data.message || 'Could not add members', 'err');
            }
        } catch (e) {
            notify('Network error', 'err');
        } finally {
            mpInviteBtn.innerHTML = originalHtml;
            updatePickerCount();
        }
    }

    mpInviteBtn?.addEventListener('click', inviteSelected);

    // ===================================================================
    //  GROUPS TAB — share with a whole department / category / committee /
    //  office / leadership pool / everyone (owner only)
    // ===================================================================
    const AUDIENCE_TYPES = @json($audienceTypes ?? []);
    const audienceMap = {};
    (AUDIENCE_TYPES || []).forEach(t => { audienceMap[t.type] = t; });

    const groupsList = document.getElementById('folderGroupsList');
    const grpType = document.getElementById('grpType');
    const grpValue = document.getElementById('grpValue');
    const grpPerm = document.getElementById('grpPerm');
    const grpAddBtn = document.getElementById('grpAddBtn');
    const mbBadgeGroups = document.getElementById('mbBadgeGroups');

    function renderGroups(grants) {
        if (!groupsList) return;
        const count = grants ? grants.length : 0;
        setBadge(mbBadgeGroups, count);
        if (count === 0) {
            groupsList.innerHTML = '<div class="empty-box" style="padding:24px 8px;"><div class="ico-c" style="width:60px;height:60px;font-size:22px;"><i class="fas fa-people-group"></i></div><h4>No groups yet</h4><p>Use the picker below to share with a department, staff category, committee, office, leadership pool, or everyone.</p></div>';
            return;
        }
        groupsList.innerHTML = grants.map(g => {
            const n = g.member_count;
            const memberTxt = (n === null || n === undefined) ? '' : (' · ' + n + ' ' + (n === 1 ? 'person' : 'people'));
            return `
            <div class="group-row" data-grant-id="${g.id}" data-type="${escapeHtml(g.type)}" data-value="${escapeHtml(g.value)}">
                <div class="grp-ico"><i class="fas ${escapeHtml(g.icon)}"></i></div>
                <div class="info">
                    <div class="name">${escapeHtml(g.value_label)}</div>
                    <div class="sub"><span class="type-tag">${escapeHtml(g.type_label)}</span>${escapeHtml(memberTxt)}</div>
                </div>
                <select class="group-perm" data-grant-id="${g.id}">
                    <option value="viewer" ${g.permission === 'viewer' ? 'selected' : ''}>Viewer</option>
                    <option value="editor" ${g.permission === 'editor' ? 'selected' : ''}>Editor</option>
                </select>
                <button type="button" class="remove-btn" data-grant-id="${g.id}" title="Remove group access"><i class="fas fa-xmark"></i></button>
            </div>`;
        }).join('');

        groupsList.querySelectorAll('.group-perm').forEach(sel => {
            sel.addEventListener('change', () => updateGrantPermission(sel.getAttribute('data-grant-id'), sel.value));
        });
        groupsList.querySelectorAll('.remove-btn').forEach(btn => {
            btn.addEventListener('click', () => removeGrant(btn.getAttribute('data-grant-id')));
        });
    }

    async function loadGroups() {
        if (!groupsList) return;
        try {
            const res = await fetch('{{ route("dashboard.folders.grants", $folder) }}', {
                credentials: 'same-origin',
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json();
            if (data.ok) renderGroups(data.grants);
        } catch (e) { console.error('[groups] load failed:', e); }
    }

    function populateGroupValues() {
        if (!grpType || !grpValue) return;
        const def = audienceMap[grpType.value];
        if (!def) {
            grpValue.innerHTML = '<option value="" selected disabled>Select a type first…</option>';
            grpValue.disabled = true;
            updateGrpAddState();
            return;
        }
        const opts = def.options || [];
        grpValue.innerHTML = '';
        if (opts.length === 1) {
            // Value-less audience (e.g. "Everyone") — nothing to choose.
            const o = opts[0];
            const opt = document.createElement('option');
            opt.value = o.value; opt.textContent = o.label; opt.selected = true;
            grpValue.appendChild(opt);
            grpValue.disabled = true;
        } else if (opts.length === 0) {
            grpValue.innerHTML = '<option value="" selected disabled>No ' + def.label.toLowerCase() + ' available</option>';
            grpValue.disabled = true;
        } else {
            const ph = document.createElement('option');
            ph.value = ''; ph.disabled = true; ph.selected = true;
            ph.textContent = 'Select a ' + def.label.toLowerCase() + '…';
            grpValue.appendChild(ph);
            opts.forEach(o => {
                const opt = document.createElement('option');
                opt.value = o.value; opt.textContent = o.label;
                grpValue.appendChild(opt);
            });
            grpValue.disabled = false;
        }
        updateGrpAddState();
    }

    function updateGrpAddState() {
        if (!grpAddBtn) return;
        let ok = false;
        if (grpType && grpType.value) {
            const def = audienceMap[grpType.value];
            if (def) {
                const opts = def.options || [];
                if (opts.length === 1) ok = true;
                else {
                    const sel = grpValue && grpValue.selectedOptions && grpValue.selectedOptions[0];
                    ok = !!(sel && !sel.disabled);
                }
            }
        }
        grpAddBtn.disabled = !ok;
    }

    async function updateGrantPermission(grantId, permission) {
        const row = groupsList.querySelector(`.group-row[data-grant-id="${grantId}"]`);
        if (!row) return;
        const type = row.getAttribute('data-type');
        const value = row.getAttribute('data-value') || '';
        try {
            const res = await fetch('{{ route("dashboard.folders.grants.add", $folder) }}', {
                method: 'POST', credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ audience_type: type, audience_value: value, permission }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) notify('Group permission updated', 'ok');
            else notify(data.message || 'Could not update group', 'err');
        } catch (e) { notify('Network error', 'err'); }
    }

    async function removeGrant(grantId) {
        const row = groupsList.querySelector(`.group-row[data-grant-id="${grantId}"]`);
        if (!row) return;
        if (!confirm('Remove this group\'s access?\nPeople who only had access through this group will lose it.')) return;
        row.style.transition = 'opacity .2s'; row.style.opacity = '0.4';
        try {
            const res = await fetch(`/dashboard/folders/${FOLDER_ID}/grants/${grantId}`, {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                row.remove();
                const remaining = groupsList.querySelectorAll('.group-row').length;
                setBadge(mbBadgeGroups, remaining);
                if (remaining === 0) renderGroups([]);
                notify('Group access removed', 'ok');
            } else {
                row.style.opacity = '1';
                notify(data.message || 'Could not remove group', 'err');
            }
        } catch (e) {
            row.style.opacity = '1';
            notify('Network error', 'err');
        }
    }

    async function addGroup() {
        if (!grpType || !grpType.value || !grpAddBtn) return;
        const type = grpType.value;
        const value = grpValue ? (grpValue.value || '') : '';
        const permission = grpPerm ? grpPerm.value : 'viewer';
        const original = grpAddBtn.innerHTML;
        grpAddBtn.disabled = true;
        grpAddBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sharing...';
        try {
            const res = await fetch('{{ route("dashboard.folders.grants.add", $folder) }}', {
                method: 'POST', credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ audience_type: type, audience_value: value, permission }),
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) {
                notify('Folder shared with group', 'ok');
                grpType.value = '';
                populateGroupValues();
                if (grpPerm) grpPerm.value = 'viewer';
                loadGroups();
            } else {
                notify(data.message || 'Could not share with group', 'err');
            }
        } catch (e) {
            notify('Network error', 'err');
        } finally {
            grpAddBtn.innerHTML = original;
            updateGrpAddState();
        }
    }

    grpType?.addEventListener('change', populateGroupValues);
    grpValue?.addEventListener('change', updateGrpAddState);
    grpAddBtn?.addEventListener('click', addGroup);

    // ===================================================================
    //  LINK TAB — revocable "anyone with the link" sharing (owner only)
    // ===================================================================
    const linkActive = document.getElementById('linkActive');
    const linkEmpty = document.getElementById('linkEmpty');
    const linkUrl = document.getElementById('linkUrl');
    const linkMeta = document.getElementById('linkMeta');
    const linkCreateBtn = document.getElementById('linkCreateBtn');
    const linkResetBtn = document.getElementById('linkResetBtn');
    const linkDisableBtn = document.getElementById('linkDisableBtn');
    const linkCopyBtn = document.getElementById('linkCopyBtn');

    function showLinkActive(url) {
        if (linkUrl) linkUrl.value = url;
        if (linkActive) linkActive.style.display = '';
        if (linkEmpty) linkEmpty.style.display = 'none';
        if (linkMeta) linkMeta.textContent = 'Link active · just now';
    }
    function showLinkEmpty() {
        if (linkActive) linkActive.style.display = 'none';
        if (linkEmpty) linkEmpty.style.display = '';
    }

    async function createOrResetLink(isReset, btn) {
        const original = btn ? btn.innerHTML : '';
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> ' + (isReset ? 'Resetting...' : 'Creating...'); }
        try {
            const res = await fetch('{{ route("dashboard.folders.share-link", $folder) }}', {
                method: 'POST', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok && data.url) { showLinkActive(data.url); notify(isReset ? 'Link reset' : 'Share link created', 'ok'); }
            else notify(data.message || 'Could not create link', 'err');
        } catch (e) { notify('Network error', 'err'); }
        finally { if (btn) { btn.disabled = false; btn.innerHTML = original; } }
    }

    async function disableLink() {
        if (!confirm('Turn off the share link?\nThe current link will stop working. People who already joined keep their access.')) return;
        const original = linkDisableBtn ? linkDisableBtn.innerHTML : '';
        if (linkDisableBtn) { linkDisableBtn.disabled = true; linkDisableBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Turning off...'; }
        try {
            const res = await fetch('{{ route("dashboard.folders.share-link", $folder) }}', {
                method: 'DELETE', credentials: 'same-origin',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await res.json().catch(() => ({}));
            if (res.ok && data.ok) { showLinkEmpty(); notify('Share link turned off', 'ok'); }
            else notify(data.message || 'Could not turn off link', 'err');
        } catch (e) { notify('Network error', 'err'); }
        finally { if (linkDisableBtn) { linkDisableBtn.disabled = false; linkDisableBtn.innerHTML = original; } }
    }

    function copyLink() {
        if (!linkUrl) return;
        linkUrl.select();
        const done = () => notify('Link copied to clipboard', 'ok');
        const fallback = () => { try { document.execCommand('copy'); done(); } catch (_) { notify('Press Ctrl+C to copy', 'err'); } };
        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(linkUrl.value).then(done).catch(fallback);
        } else { fallback(); }
    }

    linkCreateBtn?.addEventListener('click', () => createOrResetLink(false, linkCreateBtn));
    linkResetBtn?.addEventListener('click', () => {
        if (confirm('Reset the link?\nThe old link will stop working immediately.')) createOrResetLink(true, linkResetBtn);
    });
    linkDisableBtn?.addEventListener('click', disableLink);
    linkCopyBtn?.addEventListener('click', copyLink);

    function openMembersDrawer() {
        membersModal.classList.add('open');
        document.body.classList.add('mdrawer-lock');
        loadMembers();
        loadGroups();
        // Position the underline once the panel is laid out, then focus search
        // only if we're landing on the Add tab.
        setTimeout(() => {
            positionSegIndicator();
            if (mbPanels.add?.classList.contains('active')) mpSearch?.focus();
        }, 90);
    }
    function closeMembersDrawer() {
        membersModal.classList.remove('open');
        document.body.classList.remove('mdrawer-lock');
    }

    if (membersBtn && membersModal) {
        membersBtn.addEventListener('click', openMembersDrawer);
        // Click the dimmed area (outside the drawer panel) to dismiss.
        membersModal.addEventListener('click', e => { if (e.target === membersModal) closeMembersDrawer(); });
        document.getElementById('mdrawerClose')?.addEventListener('click', closeMembersDrawer);
        document.getElementById('mdrawerDone')?.addEventListener('click', closeMembersDrawer);
        document.addEventListener('keydown', e => {
            if (e.key === 'Escape' && membersModal.classList.contains('open')) closeMembersDrawer();
        });
    }
})();
</script>
@endpush
