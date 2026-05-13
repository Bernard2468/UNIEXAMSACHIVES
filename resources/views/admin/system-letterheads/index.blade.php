@extends('layout.app')

@push('styles')
<style>
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
.lh-field input[type=file] { width:100%; padding:9px 12px; border:1px solid #cbd5e1; border-radius:8px; font-size:14px; background:#fff; }
.lh-field input[type=text]:focus,
.lh-field input[type=file]:focus { outline:none; border-color:#1a4a9b; box-shadow:0 0 0 3px rgba(26,74,155,0.12); }
.lh-upload-actions { display:flex; justify-content:flex-end; margin-top:16px; }

/* Edit modal */
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

@media (max-width: 600px) {
    .lh-upload-grid { grid-template-columns:1fr; }
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
                        <div class="lh-upload-card">
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
                                <p style="margin:0; font-size:12px; color:#64748b;">
                                    <i class="icofont-info-circle"></i> Drag cards to reorder how they appear in the memo composer.
                                </p>
                            </div>

                            <div style="padding:20px;">
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
                                            </div>
                                        </div>
                                        <div class="lh-card-actions">
                                            <button type="button" class="lh-btn lh-btn--ghost"
                                                    onclick="openEditModal({{ $lh->id }}, '{{ addslashes($lh->name) }}', '{{ addslashes($lh->description ?? '') }}', '{{ $lh->image_url }}')">
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

function openEditModal(id, name, desc, imgUrl) {
    document.getElementById('lh-edit-form').action = LH_UPDATE_URL + '/' + id;
    document.getElementById('lh-edit-name').value = name;
    document.getElementById('lh-edit-desc').value = desc;
    document.getElementById('lh-edit-current').src = imgUrl;
    document.getElementById('lh-edit-image').value = '';
    document.getElementById('lh-edit-modal').classList.add('open');
}
function closeEditModal() {
    document.getElementById('lh-edit-modal').classList.remove('open');
}

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
