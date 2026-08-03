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
                                <p class="dp-page-sub">Faculties &amp; schools hold departments; units stand on their own. {{ $total }} {{ $total === 1 ? 'entry' : 'entries' }} total.</p>
                            </div>
                            <button class="dp-btn-primary" id="dpAddBtn" type="button">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                New entry
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

                        {{-- Tabs --}}
                        <div class="dp-tabs" role="tablist">
                            <button class="dp-tab is-active" data-tab="faculty" type="button">
                                Faculties &amp; Schools
                                <span class="dp-tab__count">{{ $faculties->count() }}</span>
                            </button>
                            <button class="dp-tab" data-tab="unit" type="button">
                                Units
                                <span class="dp-tab__count">{{ $units->count() }}</span>
                            </button>
                        </div>

                        {{-- ── Faculties / Schools tab ── --}}
                        <div class="dp-panel is-active" data-panel="faculty">
                            @forelse($faculties as $faculty)
                            <div class="dp-fac">
                                <div class="dp-fac__hd">
                                    <div class="dp-fac__title">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                        <span>{{ $faculty->name }}</span>
                                        <span class="dp-badge dp-badge--fac">Faculty / School</span>
                                    </div>
                                    <div class="dp-fac__actions">
                                        <button type="button" class="dp-action dp-action--add dp-add-dept-btn" data-parent="{{ $faculty->id }}" data-parent-name="{{ $faculty->name }}">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Department
                                        </button>
                                        <button type="button" class="dp-action dp-action--edit dp-edit-btn"
                                            data-name="{{ $faculty->name }}" data-type="faculty" data-parent=""
                                            data-route="{{ route('departments.update', $faculty->id) }}">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                            Edit
                                        </button>
                                        <form action="{{ route('departments.destroy', $faculty->id) }}" method="POST" style="display:inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="dp-action dp-action--del" onclick="return confirm('Delete this faculty/school? This cannot be undone.')">
                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if($faculty->departments->count() > 0)
                                <ul class="dp-deptlist">
                                    @foreach($faculty->departments as $dept)
                                    <li class="dp-dept">
                                        <span class="dp-dept__name">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-3"/></svg>
                                            {{ $dept->name }}
                                        </span>
                                        <span class="dp-dept__actions">
                                            <button type="button" class="dp-action dp-action--edit dp-edit-btn"
                                                data-name="{{ $dept->name }}" data-type="department" data-parent="{{ $dept->parent_id }}"
                                                data-route="{{ route('departments.update', $dept->id) }}">
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                Edit
                                            </button>
                                            <form action="{{ route('departments.destroy', $dept->id) }}" method="POST" style="display:inline">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dp-action dp-action--del" onclick="return confirm('Delete this department? This cannot be undone.')">
                                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                                    Delete
                                                </button>
                                            </form>
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                                @else
                                <p class="dp-fac__none">No departments under this faculty/school yet.</p>
                                @endif
                            </div>
                            @empty
                            <div class="dp-empty">
                                <div class="dp-empty__icon">
                                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                                </div>
                                <p class="dp-empty__text">No faculties or schools yet.</p>
                            </div>
                            @endforelse

                            @if($unassignedDepartments->count() > 0)
                            <div class="dp-fac dp-fac--warn">
                                <div class="dp-fac__hd">
                                    <div class="dp-fac__title">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#b45309" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                        <span>Departments needing a faculty</span>
                                    </div>
                                </div>
                                <ul class="dp-deptlist">
                                    @foreach($unassignedDepartments as $dept)
                                    <li class="dp-dept">
                                        <span class="dp-dept__name">{{ $dept->name }}</span>
                                        <span class="dp-dept__actions">
                                            <button type="button" class="dp-action dp-action--edit dp-edit-btn"
                                                data-name="{{ $dept->name }}" data-type="department" data-parent="{{ $dept->parent_id }}"
                                                data-route="{{ route('departments.update', $dept->id) }}">
                                                Assign faculty
                                            </button>
                                        </span>
                                    </li>
                                    @endforeach
                                </ul>
                            </div>
                            @endif
                        </div>

                        {{-- ── Units tab ── --}}
                        <div class="dp-panel" data-panel="unit">
                            <div class="dp-card">
                                @if($units->count() > 0)
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
                                            @foreach($units as $unit)
                                            <tr class="dp-tr">
                                                <td class="dp-td dp-td--id">{{ $unit->id }}</td>
                                                <td class="dp-td dp-td--name">{{ $unit->name }}</td>
                                                <td class="dp-td dp-td--actions">
                                                    <button type="button" class="dp-action dp-action--edit dp-edit-btn"
                                                        data-name="{{ $unit->name }}" data-type="unit" data-parent=""
                                                        data-route="{{ route('departments.update', $unit->id) }}">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                                        Edit
                                                    </button>
                                                    <form action="{{ route('departments.destroy', $unit->id) }}" method="POST" style="display:inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="dp-action dp-action--del" onclick="return confirm('Delete this unit? This cannot be undone.')">
                                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                                                            Delete
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                @else
                                <div class="dp-empty">
                                    <div class="dp-empty__icon">
                                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                                    </div>
                                    <p class="dp-empty__text">No units yet.</p>
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

{{-- ── Create Modal ── --}}
<div class="modal fade" id="dpAddModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="dm-modal">
            <div class="dm-modal__hd">
                <div>
                    <h5 class="dm-modal__title">New entry</h5>
                    <p class="dm-modal__sub">Create a faculty/school, a department, or a unit.</p>
                </div>
                <button type="button" class="dm-modal__close" data-bs-dismiss="modal">
                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                </button>
            </div>
            <div class="dm-modal__body">
                <form action="{{ route('departments.store') }}" method="POST">
                    @csrf
                    <div class="dm-modal__field">
                        <label class="dm-modal__label">Type</label>
                        <select class="dm-modal__input dp-type-select" name="type" required>
                            <option value="faculty">Faculty / School</option>
                            <option value="department">Department (under a faculty/school)</option>
                            <option value="unit">Unit (standalone)</option>
                        </select>
                    </div>
                    <div class="dm-modal__field dp-parent-field" style="display:none;">
                        <label class="dm-modal__label">Parent faculty / school</label>
                        <select class="dm-modal__input dp-parent-select" name="parent_id">
                            <option value="">— Select a faculty / school —</option>
                            @foreach($facultyOptions as $opt)
                            <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                            @endforeach
                        </select>
                        @if($facultyOptions->isEmpty())
                        <p class="dm-modal__hint">Create a faculty/school first, then add departments under it.</p>
                        @endif
                    </div>
                    <div class="dm-modal__field">
                        <label class="dm-modal__label">Name</label>
                        <input class="dm-modal__input" type="text" name="name" placeholder="e.g. Faculty of Engineering" required>
                    </div>
                    <div class="dm-modal__foot">
                        <button type="button" class="dm-modal__btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="dm-modal__btn-save">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- ── Edit Modal ── --}}
<div class="modal fade" id="dpEditModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 460px;">
        <div class="dm-modal">
            <div class="dm-modal__hd">
                <div>
                    <h5 class="dm-modal__title">Edit entry</h5>
                    <p class="dm-modal__sub">Update the name, type, or parent.</p>
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
                        <label class="dm-modal__label">Type</label>
                        <select class="dm-modal__input dp-type-select" id="dpEditType" name="type" required>
                            <option value="faculty">Faculty / School</option>
                            <option value="department">Department (under a faculty/school)</option>
                            <option value="unit">Unit (standalone)</option>
                        </select>
                    </div>
                    <div class="dm-modal__field dp-parent-field" style="display:none;">
                        <label class="dm-modal__label">Parent faculty / school</label>
                        <select class="dm-modal__input dp-parent-select" id="dpEditParent" name="parent_id">
                            <option value="">— Select a faculty / school —</option>
                            @foreach($facultyOptions as $opt)
                            <option value="{{ $opt['id'] }}">{{ $opt['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="dm-modal__field">
                        <label class="dm-modal__label">Name</label>
                        <input class="dm-modal__input" type="text" id="dpEditName" name="name" placeholder="e.g. Faculty of Engineering" required>
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

/* ── Tabs ── */
.dp-tabs { display: flex; gap: 6px; margin-bottom: 20px; border-bottom: 1.5px solid #ebebeb; }
.dp-tab {
    display: inline-flex; align-items: center; gap: 8px; padding: 10px 4px; margin-bottom: -1.5px;
    background: none; border: none; border-bottom: 2.5px solid transparent; cursor: pointer;
    font-size: 0.9rem; font-weight: 600; color: #9ca3af; font-family: 'Outfit', sans-serif !important;
    margin-right: 22px; transition: color .15s, border-color .15s;
}
.dp-tab:hover { color: #374151; }
.dp-tab.is-active { color: #0c0c0c; border-bottom-color: #0c0c0c; }
.dp-tab__count {
    display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 20px;
    padding: 0 6px; border-radius: 999px; background: #f1f2f4; color: #6b7280;
    font-size: 0.72rem; font-weight: 700;
}
.dp-tab.is-active .dp-tab__count { background: #0c0c0c; color: #fff; }

.dp-panel { display: none; }
.dp-panel.is-active { display: block; }

/* ── Faculty block ── */
.dp-fac { background: #fff; border: 1.5px solid #ebebeb; border-radius: 14px; margin-bottom: 14px; overflow: hidden; }
.dp-fac--warn { border-color: #fde68a; background: #fffbeb; }
.dp-fac__hd { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 14px 18px; border-bottom: 1.5px solid #f5f5f5; flex-wrap: wrap; }
.dp-fac--warn .dp-fac__hd { border-bottom-color: #fde68a; }
.dp-fac__title { display: inline-flex; align-items: center; gap: 9px; font-size: 0.95rem; font-weight: 700; color: #111827; }
.dp-fac__title > svg { color: #6b7280; flex-shrink: 0; }
.dp-badge { font-size: 0.66rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; padding: 3px 8px; border-radius: 6px; }
.dp-badge--fac { background: #eef2ff; color: #4338ca; }
.dp-fac__actions { display: inline-flex; align-items: center; gap: 6px; }
.dp-fac__none { margin: 0; padding: 14px 18px; font-size: 0.83rem; color: #9ca3af; }

.dp-deptlist { list-style: none; margin: 0; padding: 6px 0; }
.dp-dept { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 9px 18px; transition: background .1s; }
.dp-dept:hover { background: #fafafa; }
.dp-dept__name { display: inline-flex; align-items: center; gap: 9px; font-size: 0.88rem; font-weight: 500; color: #374151; }
.dp-dept__name > svg { color: #b0b5c0; flex-shrink: 0; }
.dp-dept__actions { display: inline-flex; align-items: center; gap: 6px; }

/* ── Card + table (Units) ── */
.dp-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
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
.dp-action--add  { color: #4338ca; border-color: #e0e7ff; }
.dp-action--add:hover { background: #eef2ff; border-color: #c7d2fe; }
.dp-action--edit { color: #374151; border-color: #e5e7eb; }
.dp-action--edit:hover { background: #f3f4f6; border-color: #d1d5db; color: #111827; text-decoration: none; }
.dp-action--del  { color: #ef4444; border-color: #fee2e2; }
.dp-action--del:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }

/* ── Empty ── */
.dp-empty { padding: 52px 24px; text-align: center; }
.dp-empty__icon { display: inline-flex; padding: 18px; background: #f9fafb; border: 1.5px solid #ebebeb; border-radius: 16px; color: #d1d5db; margin-bottom: 16px; }
.dp-empty__text { font-size: 0.9rem; color: #9ca3af; margin-bottom: 0; }

/* ── Modal fields ── */
.dm-modal__hint { margin: 8px 0 0; font-size: 0.78rem; color: #b45309; }

@media (max-width: 767px) {
    .dp-fac__hd { align-items: flex-start; }
    .dp-fac__actions { width: 100%; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // ── Tabs ──
    document.querySelectorAll('.dp-tab').forEach(function(tab) {
        tab.addEventListener('click', function() {
            var target = tab.dataset.tab;
            document.querySelectorAll('.dp-tab').forEach(t => t.classList.toggle('is-active', t === tab));
            document.querySelectorAll('.dp-panel').forEach(p => p.classList.toggle('is-active', p.dataset.panel === target));
        });
    });

    // ── Show/hide the parent picker based on selected type ──
    function bindTypeToggle(form) {
        var typeSel   = form.querySelector('.dp-type-select');
        var parentBox = form.querySelector('.dp-parent-field');
        var parentSel = form.querySelector('.dp-parent-select');
        if (!typeSel || !parentBox) return;
        function sync() {
            var isDept = typeSel.value === 'department';
            parentBox.style.display = isDept ? '' : 'none';
            if (parentSel) parentSel.required = isDept;
        }
        typeSel.addEventListener('change', sync);
        sync();
    }

    var addModal  = document.getElementById('dpAddModal');
    var editModal = document.getElementById('dpEditModal');
    var addForm   = addModal ? addModal.querySelector('form') : null;
    var editForm  = document.getElementById('dpEditForm');

    if (addForm)  bindTypeToggle(addForm);
    if (editForm) bindTypeToggle(editForm);

    function openAdd(presetType, presetParent) {
        if (!addForm) return;
        var typeSel   = addForm.querySelector('.dp-type-select');
        var parentSel = addForm.querySelector('.dp-parent-select');
        var nameInput = addForm.querySelector('input[name="name"]');
        typeSel.value = presetType || 'faculty';
        if (parentSel) parentSel.value = presetParent || '';
        if (nameInput) nameInput.value = '';
        typeSel.dispatchEvent(new Event('change'));
        new bootstrap.Modal(addModal).show();
    }

    // New entry
    var addBtn = document.getElementById('dpAddBtn');
    if (addBtn) addBtn.addEventListener('click', function() { openAdd('faculty'); });

    // Add department directly under a faculty
    document.querySelectorAll('.dp-add-dept-btn').forEach(function(btn) {
        btn.addEventListener('click', function() { openAdd('department', btn.dataset.parent); });
    });

    // ── Edit ──
    var editType   = document.getElementById('dpEditType');
    var editParent = document.getElementById('dpEditParent');
    var editName   = document.getElementById('dpEditName');
    document.querySelectorAll('.dp-edit-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            editName.value   = btn.dataset.name || '';
            editType.value   = btn.dataset.type || 'unit';
            if (editParent) editParent.value = btn.dataset.parent || '';
            editForm.action  = btn.dataset.route;
            editType.dispatchEvent(new Event('change'));
            new bootstrap.Modal(editModal).show();
        });
    });
});
</script>

@endsection
