{{--
    Windows Explorer-style document/folder grid.

    Required:
      $pageTitle        : string (e.g. "My Exams")
      $pageSubtitle     : string
      $folders          : Collection of Folder (each with files_count, exams_count)
      $items            : Collection of items normalized as objects with keys:
                          - id, kind ('file'|'exam'), title, meta (string), date (Y-m-d),
                            view_url, download_url, edit_url, destroy_url, extension

    Optional:
      $allowNewFolder   : bool (default true)
      $itemKind         : 'file' or 'exam' (controls drag-drop payload type)
--}}

@php
    $allowNewFolder = $allowNewFolder ?? true;
    $itemKind = $itemKind ?? 'file';
    $showItemsSection = $showItemsSection ?? true;
    $itemsSectionLabel = $itemsSectionLabel ?? ($itemKind === 'exam' ? 'Exam Documents' : 'Files');
    $emptyStateText = $emptyStateText ?? 'Documents you upload will appear here.';
    $sharedFolders = $sharedFolders ?? collect();
@endphp

@push('styles')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
/* ============ EXPLORER ROOT ============ */
.exp-root {
    font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    min-height: 100vh;
    padding: 32px 0 88px;
    color: #0f172a;
    line-height: 1.5;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
/* Make form controls inherit Outfit (browsers default them to system UI font) */
.exp-root input,
.exp-root button,
.exp-root select,
.exp-root textarea,
.exp-root h1,
.exp-root h2,
.exp-root h3,
.exp-root h4,
.exp-root p,
.exp-root a,
.exp-root span,
.exp-root label,
.exp-root div {
    font-family: inherit;
}
/* …but DO NOT override Font Awesome icons. They need their own font-family
   to render glyphs from the icon font's private-use code points. */
.exp-root .fas,
.exp-root .far,
.exp-root .fal,
.exp-root .fab,
.exp-root .fa-solid,
.exp-root .fa-regular,
.exp-root .fa-light,
.exp-root .fa-brands,
.exp-root [class^="fa-"],
.exp-root [class*=" fa-"] {
    font-family: "Font Awesome 6 Free", "Font Awesome 6 Brands", "FontAwesome" !important;
}
.exp-shell {
    max-width: 1280px;
    margin: 0 auto;
    padding: 0 24px;
}

/* ============ HEADER BAR ============ */
.exp-header {
    background: #fff;
    border-radius: 14px;
    border: 1px solid #e2e8f0;
    padding: 22px 24px;
    margin-bottom: 18px;
    box-shadow: 0 1px 2px rgba(15,23,42,0.04);
}
.exp-header-top {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 18px;
    flex-wrap: wrap;
}
.exp-title { display:flex; align-items:center; gap:14px; min-width: 0; }
.exp-title .icon-bubble {
    width: 46px; height: 46px; border-radius: 12px;
    background: linear-gradient(135deg, #0ea5e9, #0284c7);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-size:18px; flex-shrink: 0;
    box-shadow: 0 4px 14px rgba(14,165,233,0.28);
}
.exp-title h1 {
    font-size: 22px;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
    letter-spacing: -0.01em;
    line-height: 1.25;
}
.exp-title p {
    font-size: 14px;
    color: #64748b;
    margin: 4px 0 0;
    font-weight: 400;
    line-height: 1.45;
}

.exp-toolbar { display:flex; align-items:center; gap:10px; flex-wrap: wrap; }
.exp-search {
    position: relative;
    min-width: 240px;
    flex: 1;
    max-width: 340px;
}
.exp-search input {
    width: 100%;
    padding: 10px 14px 10px 40px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 14px;
    font-weight: 400;
    background: #f8fafc;
    color: #0f172a;
    outline: none;
    transition: all .2s;
}
.exp-search input::placeholder { color:#94a3b8; font-weight: 400; }
.exp-search input:focus { border-color:#0ea5e9; background:#fff; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.exp-search i { position:absolute; top:50%; left:14px; transform:translateY(-50%); color:#94a3b8; font-size:14px; }

.exp-btn {
    display:inline-flex; align-items:center; gap:8px;
    background:#0ea5e9; color:#fff; border:none;
    padding:10px 18px; border-radius:10px;
    font-size:13.5px; font-weight:600;
    letter-spacing: -0.005em;
    cursor:pointer; transition: all .2s;
    text-decoration:none;
}
.exp-btn:hover { background:#0284c7; transform: translateY(-1px); color:#fff; text-decoration:none; box-shadow: 0 6px 16px rgba(14,165,233,0.28); }
.exp-btn.ghost { background:#f1f5f9; color:#475569; }
.exp-btn.ghost:hover { background:#e2e8f0; color:#1e293b; box-shadow:none; }

/* ============ BREADCRUMB ============ */
.exp-crumbs {
    display:flex; align-items:center; gap:8px;
    font-size: 13px; color:#64748b;
    margin-top: 16px;
    font-weight: 400;
}
.exp-crumbs a { color:#475569; text-decoration:none; transition: color .15s; }
.exp-crumbs a:hover { color:#0ea5e9; }
.exp-crumbs .sep { color:#cbd5e1; font-size:10px; }

/* ============ SECTION ============ */
.exp-section {
    background:#fff;
    border:1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px 24px 24px;
    margin-bottom: 16px;
}
.exp-section-head {
    display:flex; align-items:center; justify-content:space-between;
    margin-bottom: 16px;
    flex-wrap: wrap; gap: 8px;
}
.exp-section-head h3 {
    font-size: 12.5px;
    font-weight: 700;
    color:#0f172a;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin: 0;
    display:flex; align-items:center; gap:10px;
}
.exp-section-head h3 .count {
    background:#f1f5f9; color:#475569;
    font-size: 11.5px; padding: 3px 9px; border-radius:100px;
    font-weight:600;
    letter-spacing: 0;
    text-transform: none;
}

/* ============ GRID ============ */
.exp-grid {
    display:grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 10px;
}

/* ============ TILE (file/exam/folder) ============ */
.exp-tile {
    position: relative;
    display:flex; flex-direction:column; align-items:center;
    padding: 16px 10px 12px;
    border-radius: 10px;
    border: 1.5px solid transparent;
    background: transparent;
    cursor: pointer;
    transition: all .12s ease;
    text-decoration:none;
    color: inherit;
    user-select: none;
}
.exp-tile:hover {
    background:#f1f5fa;
    border-color:#e2e8f0;
    text-decoration:none;
    color: inherit;
}
.exp-tile.selected {
    background: rgba(14,165,233,0.10);
    border-color: rgba(14,165,233,0.45);
}
.exp-tile.drag-over {
    background: rgba(14,165,233,0.16) !important;
    border-color: #0ea5e9 !important;
    transform: scale(1.04);
}
.exp-tile .ico {
    width: 64px; height: 64px;
    display:flex; align-items:center; justify-content:center;
    margin-bottom: 8px;
    position: relative;
}
.exp-tile .ico i { font-size: 48px; line-height: 1; }
.exp-tile .name {
    font-size: 13.5px;
    font-weight: 500;
    color: #1e293b;
    text-align: center;
    line-height: 1.35;
    word-break: break-word;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    max-width: 100%;
    letter-spacing: -0.005em;
}
.exp-tile .sub {
    font-size: 11.5px;
    color: #94a3b8;
    margin-top: 4px;
    text-align:center;
    font-weight: 400;
    letter-spacing: 0.01em;
}

/* File-type icon colors */
.ico.pdf i  { color:#ef4444; }
.ico.doc i  { color:#2563eb; }
.ico.xls i  { color:#16a34a; }
.ico.ppt i  { color:#ea580c; }
.ico.csv i  { color:#0d9488; }
.ico.img i  { color:#a855f7; }
.ico.unk i  { color:#64748b; }

/* Folder icon */
.ico.folder {
    position: relative;
}
.ico.folder svg {
    width: 64px; height: 50px;
    filter: drop-shadow(0 3px 6px rgba(15,23,42,0.10));
}
.ico.folder .lock-badge {
    position:absolute;
    right: 2px; bottom: 2px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background:#0f172a; color:#fbbf24;
    display:flex; align-items:center; justify-content:center;
    font-size: 9px;
    border: 2px solid #fff;
}

/* Tile context (right-click) menu indicator on hover */
.exp-tile .kebab {
    position:absolute; top:6px; right:6px;
    width: 22px; height: 22px;
    border-radius: 6px;
    background:#fff;
    border: 1px solid #e2e8f0;
    color:#64748b;
    display: none;
    align-items:center; justify-content:center;
    font-size: 11px;
    cursor:pointer;
    transition: all .15s;
}
.exp-tile:hover .kebab { display: flex; }
.exp-tile .kebab:hover { background:#0ea5e9; color:#fff; border-color:#0ea5e9; }

/* ============ CONTEXT MENU ============ */
.exp-menu {
    position: fixed;
    z-index: 9999;
    min-width: 200px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    box-shadow: 0 10px 30px rgba(15,23,42,0.18);
    padding: 6px;
    display: none;
}
.exp-menu.open { display:block; }
.exp-menu button, .exp-menu a {
    display: flex; align-items:center; gap:10px;
    width: 100%;
    padding: 10px 12px;
    background: transparent;
    border: none;
    text-align: left;
    font-size: 13.5px;
    font-weight: 500;
    color: #1e293b;
    border-radius: 7px;
    cursor: pointer;
    text-decoration: none;
    font-family: inherit;
    transition: background .12s;
}
.exp-menu button:hover, .exp-menu a:hover { background:#f1f5f9; color:#0f172a; text-decoration:none; }
.exp-menu button.danger { color:#dc2626; }
.exp-menu button.danger:hover { background:#fef2f2; color:#dc2626; }
.exp-menu .divider { height:1px; background:#f1f5f9; margin:4px 0; }
.exp-menu i { width: 14px; color:#64748b; }
.exp-menu button.danger i { color:#dc2626; }

/* ============ EMPTY STATE ============ */
.exp-empty {
    text-align: center; padding: 56px 20px;
    color: #94a3b8;
}
.exp-empty .ico-circle {
    width: 84px; height: 84px;
    border-radius: 24px;
    background:#f1f5f9; color:#94a3b8;
    display:flex; align-items:center; justify-content:center;
    font-size: 34px;
    margin: 0 auto 18px;
}
.exp-empty h4 {
    color:#1e293b;
    font-size: 17px;
    font-weight: 600;
    margin: 0 0 8px;
    letter-spacing: -0.005em;
}
.exp-empty p {
    font-size: 14px;
    margin: 0;
    color: #64748b;
    line-height: 1.5;
}

/* ============ NEW FOLDER MODAL ============ */
.exp-modal-backdrop {
    position: fixed; inset: 0;
    background: rgba(15,23,42,0.55);
    z-index: 9998;
    display:none;
    align-items:center; justify-content:center;
    backdrop-filter: blur(2px);
}
.exp-modal-backdrop.open { display:flex; }
.exp-modal {
    background:#fff;
    border-radius: 16px;
    width: 92%;
    max-width: 440px;
    padding: 24px 26px 22px;
    box-shadow: 0 20px 60px rgba(15,23,42,0.25);
}
.exp-modal h3 {
    font-size: 18px;
    font-weight: 700;
    color:#0f172a;
    margin: 0 0 18px;
    display:flex; align-items:center; gap:10px;
    letter-spacing: -0.01em;
}
.exp-modal h3 i { color:#0ea5e9; }
.exp-modal label {
    font-size: 13px;
    font-weight: 600;
    color:#334155;
    display:block;
    margin-bottom: 8px;
    letter-spacing: 0.005em;
}
.exp-modal input[type="text"] {
    width: 100%; padding: 12px 14px;
    border: 1.5px solid #e2e8f0; border-radius: 10px;
    font-size: 14px;
    font-weight: 400;
    color:#0f172a; outline: none;
    transition: all .2s;
    font-family: inherit;
}
.exp-modal input[type="text"]::placeholder { color:#94a3b8; }
.exp-modal input[type="text"]:focus { border-color:#0ea5e9; box-shadow: 0 0 0 3px rgba(14,165,233,0.1); }
.exp-colors {
    display:flex; gap:8px; margin-top: 6px; flex-wrap: wrap;
}
.exp-color {
    width: 28px; height: 28px;
    border-radius: 8px;
    cursor:pointer;
    border: 2.5px solid transparent;
    transition: transform .15s, border-color .15s;
}
.exp-color:hover { transform: scale(1.1); }
.exp-color.selected { border-color: #0f172a; }
.exp-modal-actions {
    display:flex; justify-content:flex-end; gap:10px;
    margin-top: 18px;
}

/* ============ TOAST ============ */
.exp-toast {
    position: fixed; bottom: 24px; left: 50%;
    transform: translateX(-50%) translateY(20px);
    background: #0f172a; color:#f8fafc;
    padding: 13px 20px;
    border-radius: 10px;
    font-size: 13.5px;
    font-weight: 500;
    box-shadow: 0 10px 30px rgba(15,23,42,0.25);
    opacity: 0; pointer-events: none;
    transition: all .25s ease;
    z-index: 10000;
    display:flex; align-items:center; gap:10px;
}
.exp-toast.show { opacity: 1; transform: translateX(-50%) translateY(0); }
.exp-toast.ok i { color:#22c55e; }
.exp-toast.err i { color:#ef4444; }
.exp-toast.err { background:#7f1d1d; }

@media (max-width: 640px) {
    .exp-grid { grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); }
    .exp-tile .ico { width: 54px; height: 54px; }
    .exp-tile .ico i { font-size: 40px; }
    .exp-search { min-width: 0; flex: 1 1 100%; max-width: none; }
}
</style>
@endpush

<div class="exp-root">
    <div class="exp-shell">

        {{-- ===== HEADER ===== --}}
        <div class="exp-header">
            <div class="exp-header-top">
                <div class="exp-title">
                    <div class="icon-bubble"><i class="fas fa-folder-tree"></i></div>
                    <div>
                        <h1>{{ $pageTitle }}</h1>
                        <p>{{ $pageSubtitle }}</p>
                    </div>
                </div>
                <div class="exp-toolbar">
                    <div class="exp-search">
                        <i class="fas fa-search"></i>
                        <input type="text" id="expSearchInput" placeholder="Search items by name...">
                    </div>
                    @if($allowNewFolder)
                        <button type="button" class="exp-btn" id="expNewFolderBtn"><i class="fas fa-folder-plus"></i> New Folder</button>
                    @endif
                    <a href="{{ route('dashboard.folders.index') }}" class="exp-btn ghost"><i class="fas fa-folder"></i> All Folders</a>
                </div>
            </div>
            <div class="exp-crumbs">
                <a href="{{ route('dashboard') }}"><i class="fas fa-home"></i> Dashboard</a>
                <span class="sep"><i class="fas fa-chevron-right"></i></span>
                <span style="color:#1e293b; font-weight:600; letter-spacing:-0.005em;">{{ $pageTitle }}</span>
            </div>
        </div>

        {{-- ===== FOLDERS ===== --}}
        @if($folders->count() > 0)
            <div class="exp-section">
                <div class="exp-section-head">
                    <h3><i class="fas fa-folder" style="color:#fbbf24;"></i> Folders <span class="count">{{ $folders->count() }}</span></h3>
                </div>
                <div class="exp-grid" id="expFoldersGrid">
                    @foreach($folders as $folder)
                        @php
                            $folderCount = ($folder->files_count ?? 0) + ($folder->exams_count ?? 0);
                            $folderColor = $folder->color ?: '#fbbf24';
                            $isLocked = !empty($folder->password_hash);
                        @endphp
                        @php
                            $folderShowUrl = route('dashboard.folders.show', $folder)
                                . '?from=' . urlencode(url()->full());
                        @endphp
                        <a href="{{ $folderShowUrl }}"
                            class="exp-tile exp-folder-tile"
                            data-folder-id="{{ $folder->id }}"
                            data-folder-name="{{ $folder->name }}"
                            data-folder-locked="{{ $isLocked ? '1' : '0' }}"
                            data-search="{{ strtolower($folder->name) }}"
                            title="{{ $folder->name }}{{ $isLocked ? ' (locked)' : '' }}">
                            <div class="ico folder">
                                <svg viewBox="0 0 64 50" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 10 Q2 4 8 4 L22 4 L28 10 L56 10 Q62 10 62 16 L62 42 Q62 48 56 48 L8 48 Q2 48 2 42 Z"
                                          fill="{{ $folderColor }}" opacity="0.95"/>
                                    <path d="M2 14 Q2 8 8 8 L60 8 Q64 8 62 14 L57 44 Q56 48 50 48 L8 48 Q2 48 2 42 Z"
                                          fill="{{ $folderColor }}" opacity="0.7"/>
                                </svg>
                                @if($isLocked)
                                    <span class="lock-badge"><i class="fas fa-lock"></i></span>
                                @endif
                            </div>
                            <div class="name">{{ $folder->name }}</div>
                            <div class="sub">{{ $folderCount }} {{ Str::plural('item', $folderCount) }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== SHARED WITH ME ===== --}}
        @if($sharedFolders->count() > 0)
            <div class="exp-section">
                <div class="exp-section-head">
                    <h3><i class="fas fa-user-group" style="color:#0ea5e9;"></i> Shared with me <span class="count">{{ $sharedFolders->count() }}</span></h3>
                </div>
                <div class="exp-grid">
                    @foreach($sharedFolders as $folder)
                        @php
                            $folderCount = ($folder->files_count ?? 0) + ($folder->exams_count ?? 0);
                            $folderColor = $folder->color ?: '#0ea5e9';
                            $owner = $folder->user;
                            $ownerName = $owner
                                ? (trim(($owner->first_name ?? '') . ' ' . ($owner->last_name ?? '')) ?: ($owner->name ?: $owner->email))
                                : 'Someone';
                            $perm = $folder->pivot->permission ?? 'viewer';
                            $folderShowUrl = route('dashboard.folders.show', $folder)
                                . '?from=' . urlencode(url()->full());
                        @endphp
                        <a href="{{ $folderShowUrl }}"
                            class="exp-tile"
                            data-search="{{ strtolower($folder->name . ' ' . $ownerName) }}"
                            title="{{ $folder->name }} — shared by {{ $ownerName }} ({{ ucfirst($perm) }})">
                            <div class="ico folder">
                                <svg viewBox="0 0 64 50" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 10 Q2 4 8 4 L22 4 L28 10 L56 10 Q62 10 62 16 L62 42 Q62 48 56 48 L8 48 Q2 48 2 42 Z"
                                          fill="{{ $folderColor }}" opacity="0.95"/>
                                    <path d="M2 14 Q2 8 8 8 L60 8 Q64 8 62 14 L57 44 Q56 48 50 48 L8 48 Q2 48 2 42 Z"
                                          fill="{{ $folderColor }}" opacity="0.7"/>
                                </svg>
                                <span class="lock-badge" style="background:#0ea5e9; color:#fff; font-size:8px;" title="Shared with you"><i class="fas fa-user-group"></i></span>
                            </div>
                            <div class="name">{{ $folder->name }}</div>
                            <div class="sub">{{ $folderCount }} {{ Str::plural('item', $folderCount) }} · {{ $ownerName }}</div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- ===== ITEMS ===== --}}
        @if($showItemsSection)
        <div class="exp-section">
            <div class="exp-section-head">
                <h3>
                    <i class="fas fa-file" style="color:#0ea5e9;"></i> {{ $itemsSectionLabel }}
                    <span class="count">{{ $items->count() }}</span>
                </h3>
                @if($folders->count() > 0 && $items->count() > 0)
                <div style="font-size:12.5px; color:#94a3b8; font-weight:400;">
                    <i class="fas fa-info-circle"></i> Drag items onto a folder to organize them
                </div>
                @endif
            </div>

            @if($items->count() > 0)
                <div class="exp-grid" id="expItemsGrid">
                    @foreach($items as $it)
                        @php
                            $ext = strtolower($it['extension'] ?? '');
                            $iconClass = match(true) {
                                $ext === 'pdf' => 'pdf',
                                in_array($ext, ['doc','docx']) => 'doc',
                                in_array($ext, ['xls','xlsx','csv']) => 'xls',
                                in_array($ext, ['ppt','pptx']) => 'ppt',
                                in_array($ext, ['png','jpg','jpeg','gif','webp']) => 'img',
                                default => 'unk',
                            };
                            $iconFa = match($iconClass) {
                                'pdf' => 'fa-file-pdf',
                                'doc' => 'fa-file-word',
                                'xls' => 'fa-file-excel',
                                'ppt' => 'fa-file-powerpoint',
                                'img' => 'fa-file-image',
                                default => 'fa-file',
                            };
                            $displayName = $it['title'] . '_' . $it['date'];
                        @endphp
                        <div class="exp-tile exp-item-tile"
                            data-id="{{ $it['id'] }}"
                            data-kind="{{ $it['kind'] }}"
                            data-search="{{ strtolower($it['title'] . ' ' . $it['meta'] . ' ' . $it['date']) }}"
                            data-view-url="{{ $it['view_url'] }}"
                            data-download-url="{{ $it['download_url'] }}"
                            data-edit-url="{{ $it['edit_url'] }}"
                            data-destroy-url="{{ $it['destroy_url'] }}"
                            data-name="{{ $displayName }}"
                            draggable="true"
                            title="{{ $displayName }}.{{ $ext }}">
                            <button type="button" class="kebab" aria-label="More"><i class="fas fa-ellipsis-vertical"></i></button>
                            <div class="ico {{ $iconClass }}"><i class="fas {{ $iconFa }}"></i></div>
                            <div class="name">{{ $displayName }}</div>
                            <div class="sub">{{ strtoupper($ext) }} &middot; {{ $it['date'] }}</div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="exp-empty">
                    <div class="ico-circle"><i class="fas fa-folder-open"></i></div>
                    <h4>Nothing here yet</h4>
                    <p>{{ $emptyStateText }}</p>
                </div>
            @endif
        </div>
        @endif

        @if(!$showItemsSection && $folders->count() === 0)
            <div class="exp-section">
                <div class="exp-empty">
                    <div class="ico-circle"><i class="fas fa-folder-plus"></i></div>
                    <h4>No folders yet</h4>
                    <p>Create your first folder to start organizing your files and exams.</p>
                </div>
            </div>
        @endif

    </div>
</div>

{{-- ===== CONTEXT MENU ===== --}}
<div class="exp-menu" id="expContextMenu">
    <a href="#" data-action="view"><i class="fas fa-eye"></i> Open / View</a>
    <a href="#" data-action="download"><i class="fas fa-download"></i> Download</a>
    <div class="divider"></div>
    <a href="#" data-action="edit"><i class="fas fa-pen"></i> Edit details</a>
    <button type="button" data-action="delete" class="danger"><i class="fas fa-trash"></i> Delete</button>
</div>

{{-- ===== NEW FOLDER MODAL ===== --}}
@if($allowNewFolder)
<div class="exp-modal-backdrop" id="expNewFolderModal">
    <div class="exp-modal">
        <h3><i class="fas fa-folder-plus"></i> Create new folder</h3>
        <label for="expFolderName">Folder name</label>
        <input type="text" id="expFolderName" placeholder="e.g. Year 1 Exams" maxlength="120" autofocus>
        <label style="margin-top:14px;">Color</label>
        <div class="exp-colors" id="expColorPicker">
            <div class="exp-color selected" data-color="#fbbf24" style="background:#fbbf24;"></div>
            <div class="exp-color" data-color="#0ea5e9" style="background:#0ea5e9;"></div>
            <div class="exp-color" data-color="#10b981" style="background:#10b981;"></div>
            <div class="exp-color" data-color="#a855f7" style="background:#a855f7;"></div>
            <div class="exp-color" data-color="#ef4444" style="background:#ef4444;"></div>
            <div class="exp-color" data-color="#64748b" style="background:#64748b;"></div>
        </div>
        <div class="exp-modal-actions">
            <button type="button" class="exp-btn ghost" id="expFolderCancel">Cancel</button>
            <button type="button" class="exp-btn" id="expFolderCreate"><i class="fas fa-check"></i> Create</button>
        </div>
    </div>
</div>
@endif

{{-- Hidden CSRF + delete form --}}
<form id="expDeleteForm" method="POST" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<div class="exp-toast" id="expToast"><i class="fas fa-circle-check"></i> <span></span></div>

@push('scripts')
<script>
(function() {
    'use strict';

    const CSRF = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        || document.querySelector('input[name="_token"]')?.value
        || '{{ csrf_token() }}';

    // --------- SEARCH ---------
    const searchInput = document.getElementById('expSearchInput');
    const tiles = document.querySelectorAll('.exp-tile');
    function filterTiles() {
        const q = (searchInput.value || '').toLowerCase().trim();
        tiles.forEach(t => {
            const s = t.getAttribute('data-search') || '';
            t.style.display = (q === '' || s.includes(q)) ? '' : 'none';
        });
    }
    if (searchInput) searchInput.addEventListener('input', filterTiles);

    // --------- TOAST ---------
    const toast = document.getElementById('expToast');
    const toastMsg = toast?.querySelector('span');
    function notify(msg, type) {
        if (!toast) return;
        toast.classList.remove('ok', 'err');
        toast.classList.add(type === 'err' ? 'err' : 'ok');
        const icon = toast.querySelector('i');
        if (icon) icon.className = type === 'err' ? 'fas fa-triangle-exclamation' : 'fas fa-circle-check';
        if (toastMsg) toastMsg.textContent = msg;
        toast.classList.add('show');
        clearTimeout(window._expToastT);
        window._expToastT = setTimeout(() => toast.classList.remove('show'), 2400);
    }

    // --------- CONTEXT MENU ---------
    const menu = document.getElementById('expContextMenu');
    let activeTile = null;
    function openMenu(x, y, tile) {
        activeTile = tile;
        menu.style.left = Math.min(x, window.innerWidth - 220) + 'px';
        menu.style.top = Math.min(y, window.innerHeight - 220) + 'px';
        menu.classList.add('open');
    }
    function closeMenu() {
        menu.classList.remove('open');
        activeTile = null;
    }
    document.querySelectorAll('.exp-item-tile').forEach(tile => {
        tile.addEventListener('contextmenu', e => {
            e.preventDefault();
            openMenu(e.clientX, e.clientY, tile);
        });
        tile.addEventListener('dblclick', () => {
            const url = tile.getAttribute('data-view-url');
            if (url) window.open(url, '_blank');
        });
        const kebab = tile.querySelector('.kebab');
        if (kebab) {
            kebab.addEventListener('click', e => {
                e.preventDefault();
                e.stopPropagation();
                const r = kebab.getBoundingClientRect();
                openMenu(r.right + 4, r.bottom + 4, tile);
            });
        }
    });
    document.addEventListener('click', e => {
        if (!menu.contains(e.target)) closeMenu();
    });
    document.addEventListener('keydown', e => { if (e.key === 'Escape') closeMenu(); });

    menu.querySelectorAll('[data-action]').forEach(btn => {
        btn.addEventListener('click', e => {
            e.preventDefault();
            if (!activeTile) return;
            const action = btn.getAttribute('data-action');
            if (action === 'view') {
                window.open(activeTile.getAttribute('data-view-url'), '_blank');
            } else if (action === 'download') {
                window.location.href = activeTile.getAttribute('data-download-url');
            } else if (action === 'edit') {
                window.location.href = activeTile.getAttribute('data-edit-url');
            } else if (action === 'delete') {
                if (!confirm('Delete "' + (activeTile.getAttribute('data-name') || 'this item') + '"? This cannot be undone.')) return;
                const form = document.getElementById('expDeleteForm');
                form.action = activeTile.getAttribute('data-destroy-url');
                form.submit();
            }
            closeMenu();
        });
    });

    // --------- DRAG & DROP (items -> folders) ---------
    // NOTE: `dragend` fires AFTER `drop` and resets the global tracker, so we
    // must capture the tile reference into a local variable inside the drop
    // handler before awaiting the fetch — otherwise the post-fetch DOM update
    // throws on a null reference and is misreported as a network error.
    let draggedTile = null;
    document.querySelectorAll('.exp-item-tile').forEach(tile => {
        tile.addEventListener('dragstart', e => {
            draggedTile = tile;
            tile.classList.add('selected');
            try { e.dataTransfer.setData('text/plain', tile.getAttribute('data-id')); } catch (err) {}
            e.dataTransfer.effectAllowed = 'move';
        });
        tile.addEventListener('dragend', () => {
            tile.classList.remove('selected');
            draggedTile = null;
            document.querySelectorAll('.exp-folder-tile.drag-over').forEach(f => f.classList.remove('drag-over'));
        });
    });

    function bumpFolderCount(folderEl, delta) {
        const sub = folderEl.querySelector('.sub');
        if (!sub) return;
        const m = sub.textContent.match(/^(\d+)/);
        if (!m) return;
        const n = Math.max(0, parseInt(m[1], 10) + delta);
        sub.textContent = n + ' ' + (n === 1 ? 'item' : 'items');
    }

    function fadeOutTile(tile) {
        tile.style.transition = 'opacity .25s, transform .25s';
        tile.style.opacity = '0';
        tile.style.transform = 'scale(0.85)';
        setTimeout(() => { if (tile.parentNode) tile.parentNode.removeChild(tile); }, 250);
    }

    function restoreTile(tile) {
        tile.style.transition = 'opacity .2s, transform .2s';
        tile.style.opacity = '1';
        tile.style.transform = 'scale(1)';
        tile.classList.remove('selected');
    }

    document.querySelectorAll('.exp-folder-tile').forEach(folder => {
        folder.addEventListener('dragover', e => {
            if (!draggedTile) return;
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            folder.classList.add('drag-over');
        });
        folder.addEventListener('dragleave', () => folder.classList.remove('drag-over'));
        folder.addEventListener('drop', async e => {
            e.preventDefault();
            folder.classList.remove('drag-over');

            // Capture into a local before the async work: `dragend` will set
            // the shared `draggedTile` to null synchronously after this.
            const tile = draggedTile;
            if (!tile) return;

            const folderId = folder.getAttribute('data-folder-id');
            const itemId = tile.getAttribute('data-id');
            const kind = tile.getAttribute('data-kind');
            const isLocked = folder.getAttribute('data-folder-locked') === '1';

            if (isLocked) {
                notify('This folder is locked. Open it and unlock first.', 'err');
                return;
            }

            // Optimistic UI: hide the tile and bump the folder count
            // immediately. Roll back if the server rejects the move.
            fadeOutTile(tile);
            bumpFolderCount(folder, +1);

            let res, raw = '', data = {};
            try {
                res = await fetch('/dashboard/folders/' + folderId + '/move-item', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({ type: kind, item_id: itemId }),
                });
                raw = await res.text();
                try { data = raw ? JSON.parse(raw) : {}; } catch (_) { data = {}; }
            } catch (err) {
                console.error('[explorer] move-item fetch failed:', err);
                restoreTile(tile);
                bumpFolderCount(folder, -1);
                notify('Could not reach the server. Check your connection.', 'err');
                return;
            }

            if (res.ok && data.ok) {
                notify(data.message || 'Moved to folder', 'ok');
            } else {
                // Roll the optimistic update back.
                console.error('[explorer] move-item server rejected:', res.status, raw);
                restoreTile(tile);
                bumpFolderCount(folder, -1);
                const msg = data.message
                    || (res.status === 419 ? 'Session expired — please refresh and try again.' : null)
                    || (res.status === 423 ? 'Folder is locked.' : null)
                    || (res.status === 403 ? 'You do not have access to that folder.' : null)
                    || ('Could not move item (status ' + (res.status || '?') + ')');
                notify(msg, 'err');
            }
        });
    });

    // --------- NEW FOLDER MODAL ---------
    const modal = document.getElementById('expNewFolderModal');
    const openBtn = document.getElementById('expNewFolderBtn');
    const cancelBtn = document.getElementById('expFolderCancel');
    const createBtn = document.getElementById('expFolderCreate');
    const nameInput = document.getElementById('expFolderName');
    const colorPicker = document.getElementById('expColorPicker');
    let selectedColor = '#fbbf24';

    if (openBtn && modal) {
        openBtn.addEventListener('click', () => {
            modal.classList.add('open');
            nameInput.value = '';
            setTimeout(() => nameInput.focus(), 50);
        });
        cancelBtn.addEventListener('click', () => modal.classList.remove('open'));
        modal.addEventListener('click', e => { if (e.target === modal) modal.classList.remove('open'); });
        colorPicker.querySelectorAll('.exp-color').forEach(c => {
            c.addEventListener('click', () => {
                colorPicker.querySelectorAll('.exp-color').forEach(x => x.classList.remove('selected'));
                c.classList.add('selected');
                selectedColor = c.getAttribute('data-color');
            });
        });
        createBtn.addEventListener('click', async () => {
            const name = (nameInput.value || '').trim();
            if (!name) { nameInput.focus(); return; }
            createBtn.disabled = true;
            try {
                const res = await fetch('{{ route('dashboard.folders.quick-create') }}', {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': CSRF,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ name, color: selectedColor }),
                });
                const data = await res.json().catch(() => ({}));
                if (res.ok && data.ok) {
                    notify('Folder created', 'ok');
                    modal.classList.remove('open');
                    // Reload to render the new folder cleanly
                    setTimeout(() => window.location.reload(), 600);
                } else {
                    notify((data.message || 'Could not create folder'), 'err');
                }
            } catch (err) {
                notify('Network error while creating folder', 'err');
            } finally {
                createBtn.disabled = false;
            }
        });
        nameInput?.addEventListener('keydown', e => {
            if (e.key === 'Enter') createBtn.click();
        });
    }
})();
</script>
@endpush
