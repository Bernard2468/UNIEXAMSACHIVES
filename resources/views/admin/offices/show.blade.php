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
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                            <span>Back to offices</span>
                        </a>

                        <div class="ps-page-header" style="margin-top: 10px;">
                            <div>
                                <h1 class="ps-page-title">
                                    {{ $office->name }}
                                    <span class="ps-title-bar"></span>
                                </h1>
                                <div class="off-head-meta">
                                    <code class="off-slug">{{ $office->slug }}</code>
                                    @if($office->is_active)
                                        <span class="off-pill off-pill--ok"><span class="off-pill-dot"></span>Active</span>
                                    @else
                                        <span class="off-pill off-pill--warn"><span class="off-pill-dot"></span>Inactive</span>
                                    @endif
                                </div>
                            </div>
                            @php
                                $pendingCount = $office->pendingSubmissions()->count();
                                $confirmMsg = $pendingCount > 0
                                    ? "Delete this office?\\n\\n{$pendingCount} in-progress form" . ($pendingCount === 1 ? '' : 's') . " currently routed here will be CANCELLED.\\n\\nThis cannot be undone."
                                    : 'Delete this office? This cannot be undone.';
                            @endphp
                            <form method="POST" action="{{ route('offices.destroy', $office->id) }}"
                                  onsubmit="return confirm('{{ $confirmMsg }}');"
                                  style="margin-top: 14px;">
                                @csrf @method('DELETE')
                                <button type="submit" class="ps-action ps-action--del" style="padding: 8px 16px; font-size: 0.82rem;">
                                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                    Delete office
                                </button>
                            </form>
                        </div>

                        @if(session('success'))
                            <div class="ps-alert ps-alert--ok">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                <span>{{ session('success') }}</span>
                                <button class="ps-alert__x" onclick="this.closest('.ps-alert').remove()"><svg width="12" height="12" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg></button>
                            </div>
                        @endif
                        @if(session('error'))
                            <div class="ps-alert ps-alert--err">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <span>{{ session('error') }}</span>
                            </div>
                        @endif

                        {{-- ===== Office settings ===== --}}
                        <div class="ps-card" style="margin-bottom: 20px;">
                            <div class="ps-card__hd">
                                <div>
                                    <h2 class="ps-card__title">Office details<span class="ps-card__bar"></span></h2>
                                    <p class="ps-card__count">Name, description and active status</p>
                                </div>
                            </div>
                            <div style="padding: 20px 24px 22px;">
                                <form method="POST" action="{{ route('offices.update', $office->id) }}">
                                    @csrf @method('PUT')
                                    <div class="off-grid-2">
                                        <div class="ps-modal__field">
                                            <label class="ps-modal__label">Office name</label>
                                            <input type="text" name="name" class="ps-modal__input" value="{{ $office->name }}" required>
                                        </div>
                                        <div class="ps-modal__field">
                                            <label class="ps-modal__label">Email <span class="off-optional">optional</span></label>
                                            <input type="email" name="email" class="ps-modal__input" value="{{ $office->email }}" placeholder="finance@cug.edu.gh">
                                        </div>
                                    </div>
                                    <div class="ps-modal__field">
                                        <label class="ps-modal__label">Description <span class="off-optional">optional</span></label>
                                        <textarea name="description" class="ps-modal__input" rows="2" placeholder="What does this office do?">{{ $office->description }}</textarea>
                                    </div>
                                    <div class="off-switch-row">
                                        <label class="off-switch">
                                            <input type="checkbox" name="is_active" value="1" @checked($office->is_active)>
                                            <span class="off-switch__slider"></span>
                                            <span class="off-switch__label">
                                                <strong>Active</strong>
                                                <small>Forms can be routed to this office</small>
                                            </span>
                                        </label>
                                    </div>
                                    <div style="display: flex; justify-content: flex-end; padding-top: 6px;">
                                        <button type="submit" class="ps-modal__btn-save">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                            Save changes
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- ===== Members ===== --}}
                        <div class="ps-card">
                            <div class="ps-card__hd" style="display: flex; align-items: flex-start; justify-content: space-between; gap: 14px;">
                                <div>
                                    <h2 class="ps-card__title">Members<span class="ps-card__bar"></span></h2>
                                    @php $activeMembers = $office->users->where('pivot.is_active', true)->count(); @endphp
                                    <p class="ps-card__count">{{ $activeMembers }} active {{ $activeMembers === 1 ? 'member' : 'members' }}</p>
                                </div>
                                @if($candidates->isNotEmpty())
                                    <button type="button" class="ps-btn-primary" id="openAddMember" style="margin-top: 0;">
                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                        Add member
                                    </button>
                                @endif
                            </div>

                            @if($office->users->isEmpty())
                                <div class="ps-empty">
                                    <div class="ps-empty__icon">
                                        <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="7" r="4"/><path d="M3 21v-2a4 4 0 014-4h4a4 4 0 014 4v2"/><circle cx="17" cy="7" r="3"/><path d="M22 21v-2a4 4 0 00-3-3.87"/></svg>
                                    </div>
                                    <p class="ps-empty__text">No members yet. Forms cannot be routed here until someone is added.</p>
                                    @if($candidates->isNotEmpty())
                                        <button class="ps-btn-primary" id="openAddMemberEmpty">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Add first member
                                        </button>
                                    @endif
                                </div>
                            @else
                                <div class="ps-table-shell">
                                    <table class="ps-table">
                                        <thead>
                                            <tr>
                                                <th class="ps-th">Member</th>
                                                <th class="ps-th">Role</th>
                                                <th class="ps-th">Status</th>
                                                <th class="ps-th ps-th--actions">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($office->users as $member)
                                                @php
                                                    $fullName = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
                                                    $initials = strtoupper(substr($member->first_name ?? '', 0, 1) . substr($member->last_name ?? '', 0, 1));
                                                @endphp
                                                <tr class="ps-tr">
                                                    <td class="ps-td">
                                                        <div class="off-member-cell">
                                                            <div class="off-avatar">
                                                                @if($member->profile_picture)
                                                                    <img src="{{ asset('profile_pictures/' . $member->profile_picture) }}" alt="{{ $fullName }}">
                                                                @else
                                                                    <span>{{ $initials ?: '?' }}</span>
                                                                @endif
                                                            </div>
                                                            <div class="off-member-info">
                                                                <div class="off-member-name">{{ $fullName }}</div>
                                                                <div class="off-member-email">{{ $member->email }}</div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="ps-td">
                                                        @if($member->pivot->is_head)
                                                            <span class="off-role off-role--head">
                                                                <svg width="11" height="11" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                                                Head
                                                            </span>
                                                            <form method="POST" action="{{ route('offices.members.update', [$office->id, $member->id]) }}" style="display:inline-block; margin-left: 6px;">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '1' : '0' }}">
                                                                <input type="hidden" name="is_head"   value="0">
                                                                <button type="submit" class="off-link-btn" title="Demote from head">Demote</button>
                                                            </form>
                                                        @else
                                                            <span class="off-role off-role--member">Member</span>
                                                            <form method="POST" action="{{ route('offices.members.update', [$office->id, $member->id]) }}" style="display:inline-block; margin-left: 6px;">
                                                                @csrf @method('PUT')
                                                                <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '1' : '0' }}">
                                                                <input type="hidden" name="is_head"   value="1">
                                                                <button type="submit" class="off-link-btn" title="Promote to head">Make head</button>
                                                            </form>
                                                        @endif
                                                    </td>
                                                    <td class="ps-td">
                                                        <form method="POST" action="{{ route('offices.members.update', [$office->id, $member->id]) }}" style="display:inline-block;">
                                                            @csrf @method('PUT')
                                                            <input type="hidden" name="is_head"   value="{{ $member->pivot->is_head ? '1' : '0' }}">
                                                            <input type="hidden" name="is_active" value="{{ $member->pivot->is_active ? '0' : '1' }}">
                                                            <button type="submit" class="off-status-toggle {{ $member->pivot->is_active ? 'is-active' : 'is-inactive' }}" title="Click to toggle">
                                                                <span class="off-status-dot"></span>
                                                                {{ $member->pivot->is_active ? 'Active' : 'Inactive' }}
                                                            </button>
                                                        </form>
                                                    </td>
                                                    <td class="ps-td ps-td--actions">
                                                        <form method="POST" action="{{ route('offices.members.remove', [$office->id, $member->id]) }}"
                                                              onsubmit="return confirm('Remove {{ $fullName }} from this office?');" style="display:inline;">
                                                            @csrf @method('DELETE')
                                                            <button type="submit" class="ps-action ps-action--del">
                                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
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
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ===== Add Member Modal — searchable user picker (compose-memo style) ===== --}}
@if($candidates->isNotEmpty())
<div class="off-modal" id="addMemberModal" style="display:none;">
    <div class="off-modal__backdrop"></div>
    <div class="off-modal__panel">
        <div class="off-modal__hd">
            <div>
                <h3 class="off-modal__title">Add a member</h3>
                <p class="off-modal__sub">Search for a user and add them to <strong>{{ $office->name }}</strong>.</p>
            </div>
            <button type="button" class="ps-modal__close" id="closeAddMember">
                <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('offices.members.add', $office->id) }}" id="addMemberForm">
            @csrf
            <input type="hidden" name="user_id" id="picked_user_id" value="">

            <div class="off-search-wrap">
                <svg class="off-search-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="memberSearch" class="off-search-input" placeholder="Search by name or email..." autocomplete="off">
            </div>

            <div class="off-user-list" id="memberUserList">
                @foreach($candidates as $u)
                    @php
                        $cName = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
                        $cInit = strtoupper(substr($u->first_name ?? '', 0, 1) . substr($u->last_name ?? '', 0, 1));
                    @endphp
                    <label class="off-user-item" data-search="{{ strtolower($cName . ' ' . $u->email) }}">
                        <input type="radio" name="picker" value="{{ $u->id }}" class="off-user-radio">
                        <div class="off-avatar off-avatar--sm">
                            @if($u->profile_picture)
                                <img src="{{ asset('profile_pictures/' . $u->profile_picture) }}" alt="{{ $cName }}">
                            @else
                                <span>{{ $cInit ?: '?' }}</span>
                            @endif
                        </div>
                        <div class="off-user-meta">
                            <div class="off-user-name">{{ $cName }}</div>
                            <div class="off-user-email">{{ $u->email }}</div>
                        </div>
                        <div class="off-user-check">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                        </div>
                    </label>
                @endforeach
                <div class="off-user-empty" id="memberEmpty" style="display:none;">
                    <p>No users match that search.</p>
                </div>
            </div>

            <div class="off-modal__footrow">
                <label class="off-toggle-pill">
                    <input type="checkbox" name="is_head" value="1" id="makeHeadCheckbox">
                    <span class="off-toggle-pill__star">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </span>
                    Make head of office
                </label>
                <div class="off-modal__actions">
                    <button type="button" class="ps-modal__btn-cancel" id="cancelAddMember">Cancel</button>
                    <button type="submit" class="ps-modal__btn-save" id="confirmAddBtn" disabled>
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Add to office
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
.ps-wrap, .ps-wrap * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.ps-wrap { max-width: 980px; padding: 4px 0 60px; }

/* ── Page header ── */
.ps-page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb; }
.ps-page-title { font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em; line-height: 1.1; margin: 0; display: inline-flex; flex-direction: column; }
.ps-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }

.off-back { display: inline-flex; align-items: center; gap: 6px; color: #6b7280; text-decoration: none; font-size: 0.82rem; font-weight: 500; transition: color .15s; }
.off-back:hover { color: #0c0c0c; text-decoration: none; }

.off-head-meta { display: flex; align-items: center; gap: 8px; margin-top: 14px; flex-wrap: wrap; }
.off-slug { font-size: 0.72rem; color: #6b7280; background: #f3f4f6; padding: 4px 10px; border-radius: 99px; font-weight: 500; font-family: 'JetBrains Mono', monospace !important; }
.off-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 0.7rem; padding: 4px 10px; border-radius: 99px; font-weight: 600; letter-spacing: 0.4px; text-transform: uppercase; }
.off-pill-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.off-pill--ok { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.off-pill--warn { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

.off-optional { font-weight: 400; font-size: 0.72rem; color: #b0b5c0; margin-left: 4px; }

/* ── Primary button ── */
.ps-btn-primary { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; background: #0c0c0c; color: #fff; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; flex-shrink: 0; margin-top: 14px; transition: background .15s, transform .12s, box-shadow .15s; font-family: 'Outfit', sans-serif !important; }
.ps-btn-primary:hover { background: #1f2937; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* ── Alerts ── */
.ps-alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; font-weight: 500; border: 1.5px solid transparent; }
.ps-alert--ok  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.ps-alert--err { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
.ps-alert__x { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .45; color: inherit; padding: 0; display: flex; }
.ps-alert__x:hover { opacity: 1; }

/* ── Card ── */
.ps-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.ps-card__hd { padding: 18px 24px 14px; border-bottom: 1.5px solid #f5f5f5; }
.ps-card__title { font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0; display: inline-flex; flex-direction: column; }
.ps-card__bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.ps-card__count { margin: 8px 0 0; font-size: 0.78rem; color: #b0b5c0; }

/* ── Form grid ── */
.off-grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
@media (max-width: 720px) { .off-grid-2 { grid-template-columns: 1fr; } }

.ps-modal__field { margin-bottom: 16px; }
.ps-modal__label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 7px; letter-spacing: 0.01em; }
.ps-modal__input { display: block; width: 100%; padding: 11px 14px; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.88rem; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s; font-family: 'Outfit', sans-serif !important; }
.ps-modal__input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.ps-modal__input::placeholder { color: #d4d7de; }
textarea.ps-modal__input { resize: vertical; min-height: 64px; }

.off-switch-row { padding: 14px 0; margin-bottom: 8px; border-top: 1px dashed #ebebeb; border-bottom: 1px dashed #ebebeb; }
.off-switch { display: flex; align-items: center; gap: 14px; cursor: pointer; margin: 0; }
.off-switch input { display: none; }
.off-switch__slider { position: relative; width: 38px; height: 22px; background: #e5e7eb; border-radius: 99px; transition: background .15s; flex-shrink: 0; }
.off-switch__slider::after { content: ''; position: absolute; top: 2px; left: 2px; width: 18px; height: 18px; background: #fff; border-radius: 50%; transition: transform .18s, box-shadow .15s; box-shadow: 0 1px 3px rgba(0,0,0,.15); }
.off-switch input:checked ~ .off-switch__slider { background: #0c0c0c; }
.off-switch input:checked ~ .off-switch__slider::after { transform: translateX(16px); }
.off-switch__label strong { display: block; font-size: 0.86rem; color: #111827; font-weight: 600; line-height: 1.1; }
.off-switch__label small { display: block; font-size: 0.74rem; color: #9ca3af; margin-top: 2px; }

.ps-modal__btn-save { display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; background: #0c0c0c; color: #fff; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.ps-modal__btn-save:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }
.ps-modal__btn-save:disabled { background: #d1d5db; cursor: not-allowed; transform: none; box-shadow: none; }
.ps-modal__btn-cancel { padding: 10px 20px; background: none; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.ps-modal__btn-cancel:hover { border-color: #d1d5db; color: #374151; background: #f9fafb; }

/* ── Members table ── */
.ps-table-shell { overflow-x: auto; }
.ps-table { width: 100%; border-collapse: collapse; }
.ps-th { padding: 12px 24px; text-align: left; font-size: 0.7rem; font-weight: 700; color: #b0b5c0; letter-spacing: .08em; text-transform: uppercase; background: #fafafa; border-bottom: 1.5px solid #f0f0f0; }
.ps-th--actions { width: 120px; text-align: right; }
.ps-tr { border-bottom: 1.5px solid #f5f5f5; transition: background .1s; }
.ps-tr:last-child { border-bottom: none; }
.ps-tr:hover { background: #fafafa; }
.ps-td { padding: 14px 24px; font-size: 0.88rem; color: #374151; vertical-align: middle; }
.ps-td--actions { text-align: right; white-space: nowrap; }

.off-member-cell { display: flex; align-items: center; gap: 12px; }
.off-avatar { width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.78rem; letter-spacing: 0.5px; flex-shrink: 0; overflow: hidden; }
.off-avatar img { width: 100%; height: 100%; object-fit: cover; }
.off-avatar--sm { width: 34px; height: 34px; font-size: 0.72rem; }
.off-member-info { min-width: 0; }
.off-member-name { font-weight: 600; color: #111827; font-size: 0.9rem; line-height: 1.2; }
.off-member-email { color: #9ca3af; font-size: 0.78rem; margin-top: 2px; }

.off-role { display: inline-flex; align-items: center; gap: 5px; font-size: 0.72rem; padding: 3px 10px; border-radius: 99px; font-weight: 600; }
.off-role--head { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.off-role--member { background: #f3f4f6; color: #6b7280; }
.off-link-btn { background: none; border: none; padding: 0; color: #9ca3af; font-size: 0.72rem; cursor: pointer; font-family: 'Outfit', sans-serif !important; font-weight: 500; transition: color .12s; }
.off-link-btn:hover { color: #0c0c0c; text-decoration: underline; }

.off-status-toggle { display: inline-flex; align-items: center; gap: 6px; padding: 4px 10px; border-radius: 99px; font-size: 0.74rem; font-weight: 600; border: 1px solid transparent; cursor: pointer; font-family: 'Outfit', sans-serif !important; transition: all .15s; }
.off-status-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.off-status-toggle.is-active { background: #f0fdf4; color: #15803d; border-color: #bbf7d0; }
.off-status-toggle.is-active:hover { background: #dcfce7; }
.off-status-toggle.is-inactive { background: #f9fafb; color: #9ca3af; border-color: #e5e7eb; }
.off-status-toggle.is-inactive:hover { background: #f3f4f6; }

/* ── Action buttons ── */
.ps-action { display: inline-flex; align-items: center; gap: 5px; padding: 5px 12px; border-radius: 8px; font-size: 0.78rem; font-weight: 600; cursor: pointer; border: 1.5px solid transparent; transition: all .15s; text-decoration: none; background: none; font-family: 'Outfit', sans-serif !important; }
.ps-action--del  { color: #ef4444; border-color: #fee2e2; }
.ps-action--del:hover { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }

/* ── Empty ── */
.ps-empty { padding: 52px 24px; text-align: center; }
.ps-empty__icon { display: inline-flex; padding: 18px; background: #f9fafb; border: 1.5px solid #ebebeb; border-radius: 16px; color: #d1d5db; margin-bottom: 16px; }
.ps-empty__text { font-size: 0.88rem; color: #9ca3af; margin-bottom: 18px; }

/* ── Add Member Modal ── */
.off-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; padding: 20px; font-family: 'Outfit', sans-serif !important; }
.off-modal *, .off-modal { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.off-modal__backdrop { position: absolute; inset: 0; background: rgba(12,12,12,.55); backdrop-filter: blur(4px); }
.off-modal__panel { position: relative; background: #fff; border: 1.5px solid #ebebeb; border-radius: 18px; width: 100%; max-width: 540px; max-height: 92vh; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 24px 60px rgba(0,0,0,.25); }
.off-modal__hd { display: flex; align-items: flex-start; justify-content: space-between; padding: 22px 24px 16px; border-bottom: 1.5px solid #f5f5f5; gap: 14px; }
.off-modal__title { font-size: 1rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0 0 4px; }
.off-modal__sub { font-size: 0.82rem; color: #9ca3af; margin: 0; }
.ps-modal__close { background: none; border: none; cursor: pointer; padding: 6px; color: #9ca3af; border-radius: 7px; display: flex; transition: all .15s; flex-shrink: 0; }
.ps-modal__close:hover { background: #f3f4f6; color: #0c0c0c; }

.off-search-wrap { position: relative; padding: 16px 24px 12px; }
.off-search-icon { position: absolute; top: 50%; left: 38px; transform: translateY(-50%); color: #b0b5c0; pointer-events: none; }
.off-search-input { width: 100%; padding: 11px 14px 11px 40px; background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 10px; font-size: 0.88rem; color: #111827; outline: none; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.off-search-input:focus { background: #fff; border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.off-search-input::placeholder { color: #b0b5c0; }

.off-user-list { overflow-y: auto; max-height: 360px; padding: 0 16px 4px; }
.off-user-list::-webkit-scrollbar { width: 8px; }
.off-user-list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 4px; }

.off-user-item { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; cursor: pointer; transition: background .12s; margin: 0; border: 1.5px solid transparent; }
.off-user-item:hover { background: #fafafa; }
.off-user-item.is-selected { background: #f9fafb; border-color: #0c0c0c; }
.off-user-radio { display: none; }
.off-user-meta { flex: 1; min-width: 0; }
.off-user-name { font-weight: 600; color: #111827; font-size: 0.88rem; line-height: 1.2; }
.off-user-email { color: #9ca3af; font-size: 0.76rem; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.off-user-check { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; color: transparent; flex-shrink: 0; transition: all .15s; }
.off-user-item.is-selected .off-user-check { background: #0c0c0c; border-color: #0c0c0c; color: #fff; }
.off-user-empty { padding: 30px 16px; text-align: center; color: #b0b5c0; font-size: 0.82rem; }

.off-modal__footrow { display: flex; justify-content: space-between; align-items: center; padding: 14px 24px 20px; border-top: 1.5px solid #f5f5f5; gap: 12px; flex-wrap: wrap; }
.off-modal__actions { display: flex; gap: 8px; }
.off-toggle-pill { display: inline-flex; align-items: center; gap: 7px; cursor: pointer; padding: 6px 12px; border: 1.5px solid #ebebeb; border-radius: 99px; font-size: 0.78rem; font-weight: 500; color: #6b7280; transition: all .15s; margin: 0; user-select: none; }
.off-toggle-pill:hover { border-color: #d1d5db; color: #111827; }
.off-toggle-pill input { display: none; }
.off-toggle-pill__star { color: #b0b5c0; display: inline-flex; transition: color .15s; }
.off-toggle-pill:has(input:checked) { background: #0c0c0c; color: #fff; border-color: #0c0c0c; }
.off-toggle-pill:has(input:checked) .off-toggle-pill__star { color: #fbbf24; }

/* Dark mode */
.is_dark .ps-page-title  { color: #f3f4f6; }
.is_dark .ps-title-bar   { background: #f3f4f6; }
.is_dark .ps-page-header { border-color: #1e2330; }
.is_dark .ps-btn-primary { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ps-card        { background: #111827; border-color: #1e2330; }
.is_dark .ps-card__hd    { border-color: #1e2330; }
.is_dark .ps-card__title { color: #f3f4f6; }
.is_dark .ps-card__bar   { background: #f3f4f6; }
.is_dark .ps-th  { background: #0f172a; border-color: #1e2330; color: #6b7280; }
.is_dark .ps-tr  { border-color: #1e2330; }
.is_dark .ps-tr:hover { background: #0f172a; }
.is_dark .ps-td  { color: #d1d5db; }
.is_dark .ps-modal__input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .ps-modal__input:focus { border-color: #f3f4f6; }
.is_dark .off-modal__panel { background: #111827; border-color: #1e2330; }
.is_dark .off-search-input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .off-user-item:hover { background: #0f172a; }
.is_dark .off-user-name { color: #f3f4f6; }
.is_dark .off-member-name { color: #f3f4f6; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modal = document.getElementById('addMemberModal');
    if (!modal) return;

    var open  = document.getElementById('openAddMember');
    var openEmpty = document.getElementById('openAddMemberEmpty');
    var close = document.getElementById('closeAddMember');
    var cancel = document.getElementById('cancelAddMember');
    var backdrop = modal.querySelector('.off-modal__backdrop');
    var search = document.getElementById('memberSearch');
    var list   = document.getElementById('memberUserList');
    var empty  = document.getElementById('memberEmpty');
    var pickedInput = document.getElementById('picked_user_id');
    var confirmBtn  = document.getElementById('confirmAddBtn');
    var items = list ? list.querySelectorAll('.off-user-item') : [];

    function show() { modal.style.display = 'flex'; setTimeout(function(){ search && search.focus(); }, 100); }
    function hide() { modal.style.display = 'none'; }
    open && open.addEventListener('click', show);
    openEmpty && openEmpty.addEventListener('click', show);
    close && close.addEventListener('click', hide);
    cancel && cancel.addEventListener('click', hide);
    backdrop && backdrop.addEventListener('click', hide);

    if (search) {
        search.addEventListener('input', function () {
            var q = search.value.trim().toLowerCase();
            var anyVisible = false;
            items.forEach(function (it) {
                var matches = !q || (it.dataset.search || '').indexOf(q) !== -1;
                it.style.display = matches ? '' : 'none';
                if (matches) anyVisible = true;
            });
            if (empty) empty.style.display = anyVisible ? 'none' : 'block';
        });
    }

    items.forEach(function (it) {
        var radio = it.querySelector('.off-user-radio');
        it.addEventListener('click', function (e) {
            // Don't double-toggle when clicking the radio itself
            items.forEach(function (other) { other.classList.remove('is-selected'); });
            it.classList.add('is-selected');
            if (radio) radio.checked = true;
            if (pickedInput) pickedInput.value = radio.value;
            if (confirmBtn) confirmBtn.disabled = false;
        });
    });
});
</script>
@endsection
