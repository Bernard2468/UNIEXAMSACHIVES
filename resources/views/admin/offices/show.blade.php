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

                        <a href="{{ route('offices.index') }}" class="off-back">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            Back to offices
                        </a>

                        <div class="ps-page-header" style="margin-top: 8px;">
                            <div>
                                <h1 class="ps-page-title">{{ $office->name }}<span class="ps-title-bar"></span></h1>
                                <p class="ps-page-sub">
                                    <code class="off-card__slug" style="margin-right: 8px;">{{ $office->slug }}</code>
                                    @if($office->is_active)
                                        <span class="off-pill off-pill--ok">Active</span>
                                    @else
                                        <span class="off-pill off-pill--warn">Inactive</span>
                                    @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('offices.destroy', $office->id) }}"
                                  onsubmit="return confirm('Delete this office? This cannot be undone.');">
                                @csrf @method('DELETE')
                                <button type="submit" class="off-btn off-btn--danger">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                    Delete office
                                </button>
                            </form>
                        </div>

                        @if(session('success'))
                            <div class="ps-alert ps-alert--ok">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>{{ session('success') }}</span>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="ps-alert ps-alert--err">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        {{-- ===== Office settings ===== --}}
                        <div class="ps-card" style="margin-bottom: 22px;">
                            <div class="ps-card__hd">
                                <div>
                                    <h2 class="ps-card__title">Office Details<span class="ps-card__bar"></span></h2>
                                    <p class="ps-card__count">Name, description and active status</p>
                                </div>
                            </div>
                            <div style="padding: 0 16px 18px;">
                                <form method="POST" action="{{ route('offices.update', $office->id) }}">
                                    @csrf @method('PUT')
                                    <div class="off-form-row">
                                        <div class="off-form__group">
                                            <label class="off-form__label">Office name</label>
                                            <input type="text" name="name" class="off-form__input" value="{{ $office->name }}" required>
                                        </div>
                                        <div class="off-form__group">
                                            <label class="off-form__label">Email</label>
                                            <input type="email" name="email" class="off-form__input" value="{{ $office->email }}">
                                        </div>
                                    </div>
                                    <div class="off-form__group">
                                        <label class="off-form__label">Description</label>
                                        <textarea name="description" class="off-form__input" rows="2">{{ $office->description }}</textarea>
                                    </div>
                                    <div class="off-form__group">
                                        <label class="off-toggle">
                                            <input type="checkbox" name="is_active" value="1" @checked($office->is_active)>
                                            <span>Office is active (visible to the form workflow)</span>
                                        </label>
                                    </div>
                                    <div class="off-form__actions" style="border-top: none; padding-top: 6px;">
                                        <button type="submit" class="off-btn off-btn--primary">Save changes</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ===== Members ===== --}}
                        <div class="ps-card">
                            <div class="ps-card__hd">
                                <div>
                                    <h2 class="ps-card__title">Members<span class="ps-card__bar"></span></h2>
                                    <p class="ps-card__count">{{ $office->users->where('pivot.is_active', true)->count() }} active</p>
                                </div>
                            </div>

                            @if($office->users->isEmpty())
                                <div class="off-empty">
                                    <p>No members yet. Forms cannot be routed here until at least one member is added.</p>
                                </div>
                            @else
                                <div class="ps-table-shell">
                                    <table class="ps-table">
                                        <thead>
                                            <tr>
                                                <th class="ps-th">Name</th>
                                                <th class="ps-th">Email</th>
                                                <th class="ps-th">Role</th>
                                                <th class="ps-th">Status</th>
                                                <th class="ps-th ps-th--actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($office->users as $member)
                                                <tr class="ps-tr">
                                                    <td class="ps-td ps-td--name">
                                                        {{ trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}
                                                    </td>
                                                    <td class="ps-td" style="color: #6b7280; font-size: 13px;">{{ $member->email }}</td>
                                                    <td class="ps-td">
                                                        <form method="POST" action="{{ route('offices.members.update', [$office->id, $member->id]) }}" style="display:inline;">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '1' : '0' }}">
                                                            <input type="hidden" name="is_head"   value="{{ $member->pivot->is_head ? '0' : '1' }}">
                                                            <button type="submit" class="off-link {{ $member->pivot->is_head ? 'off-link--head' : '' }}">
                                                                {{ $member->pivot->is_head ? '★ Head' : 'Make head' }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td class="ps-td">
                                                        <form method="POST" action="{{ route('offices.members.update', [$office->id, $member->id]) }}" style="display:inline;">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="is_head"   value="{{ $member->pivot->is_head ? '1' : '0' }}">
                                                            <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '0' : '1' }}">
                                                            <button type="submit" class="off-link {{ $member->pivot->is_active ? 'off-link--active' : 'off-link--inactive' }}">
                                                                {{ $member->pivot->is_active ? 'Active' : 'Inactive' }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td class="ps-td ps-td--actions">
                                                        <form method="POST" action="{{ route('offices.members.remove', [$office->id, $member->id]) }}"
                                                              onsubmit="return confirm('Remove this member from the office?');" style="display:inline;">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="ps-action ps-action--del">
                                                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                                                Remove
                                                            </button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div style="padding: 18px; border-top: 1px solid #f3f4f6; background: #fafbfc;">
                                <h3 style="font-size: 14px; font-weight: 600; margin: 0 0 12px; color: #374151;">Add a member</h3>
                                <form method="POST" action="{{ route('offices.members.add', $office->id) }}" class="off-add-member">
                                    @csrf
                                    <select name="user_id" class="off-form__input" required style="flex: 2 1 280px;">
                                        <option value="">— Pick a user —</option>
                                        @foreach($candidates as $u)
                                            <option value="{{ $u->id }}">
                                                {{ trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) }} — {{ $u->email }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label class="off-toggle" style="margin-bottom: 0;">
                                        <input type="checkbox" name="is_head" value="1">
                                        <span>Make head</span>
                                    </label>
                                    <button type="submit" class="off-btn off-btn--primary">Add member</button>
                                </form>
                                @if($candidates->isEmpty())
                                    <p style="color: #9ca3af; font-size: 12.5px; margin: 10px 0 0;">All users are already members of this office, or there are no other users in the system.</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.off-back { color: #1d4ed8; text-decoration: none; font-size: 13.5px; font-weight: 500; display: inline-flex; align-items: center; gap: 6px; }
.off-back:hover { text-decoration: underline; }

.off-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
.off-form__group { margin-bottom: 14px; }
.off-form__label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.off-form__input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: 14px; }
.off-form__input:focus { outline: none; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,.1); }
.off-form__actions { display: flex; justify-content: flex-end; gap: 10px; padding-top: 14px; }

.off-toggle { display: inline-flex; gap: 8px; align-items: center; font-size: 13.5px; color: #374151; cursor: pointer; margin: 0; }
.off-toggle input { accent-color: #1d4ed8; }

.off-btn { padding: 9px 18px; border-radius: 7px; font-weight: 600; font-size: 14px; border: 1px solid transparent; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; }
.off-btn--primary { background: #1d4ed8; color: #fff; }
.off-btn--primary:hover { background: #1e40af; }
.off-btn--ghost { background: #fff; color: #4b5563; border-color: #d1d5db; }
.off-btn--ghost:hover { border-color: #1d4ed8; color: #1d4ed8; }
.off-btn--danger { background: #fff; color: #dc2626; border-color: #fecaca; }
.off-btn--danger:hover { background: #fee2e2; border-color: #dc2626; }

.off-link { background: none; border: none; cursor: pointer; font-weight: 500; color: #6b7280; font-size: 13px; }
.off-link--head { color: #1d4ed8; font-weight: 600; }
.off-link--active { color: #10b981; font-weight: 600; }
.off-link--inactive { color: #9ca3af; }
.off-link:hover { text-decoration: underline; }

.off-add-member { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; }

.off-card__slug { font-size: 11px; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 4px; }
.off-pill { font-size: 10.5px; padding: 3px 10px; border-radius: 99px; font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase; }
.off-pill--ok { background: #ecfdf5; color: #065f46; }
.off-pill--warn { background: #fef3c7; color: #92400e; }
.off-empty { padding: 40px 20px; text-align: center; color: #6b7280; }

@media (max-width: 768px) {
    .off-form-row { grid-template-columns: 1fr; }
}
</style>
@endsection
