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
                                <h1 class="ps-page-title">Offices<span class="ps-title-bar"></span></h1>
                                <p class="ps-page-sub">Manage the institutional offices that forms route through (Finance Office, Internal Audit, Registrar, etc.) and assign their members.</p>
                            </div>
                            <button class="ps-btn-primary" type="button" id="triggerOfficeModal">
                                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                New office
                            </button>
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

                        @if($errors->any())
                            <div class="ps-alert ps-alert--err">
                                <svg width="16" height="16" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                                <div>@foreach($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>
                            </div>
                        @endif

                        <div class="ps-card">
                            <div class="ps-card__hd">
                                <div>
                                    <h2 class="ps-card__title">All offices<span class="ps-card__bar"></span></h2>
                                    <p class="ps-card__count">{{ $offices->total() }} {{ $offices->total() === 1 ? 'office' : 'offices' }} total</p>
                                </div>
                            </div>

                            @if($offices->isEmpty())
                                <div class="off-empty">
                                    <p>No offices yet. Create one to start routing forms.</p>
                                </div>
                            @else
                                <div class="off-grid">
                                    @foreach($offices as $office)
                                        @php
                                            $head = $office->users->where('pivot.is_head', true)->where('pivot.is_active', true)->first();
                                            $activeCount = $office->users->where('pivot.is_active', true)->count();
                                        @endphp
                                        <a href="{{ route('offices.show', $office->id) }}" class="off-card">
                                            <div class="off-card__top">
                                                <div class="off-card__icon">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"/></svg>
                                                </div>
                                                @if($office->is_active)
                                                    <span class="off-pill off-pill--ok">Active</span>
                                                @else
                                                    <span class="off-pill off-pill--warn">Inactive</span>
                                                @endif
                                            </div>
                                            <h3 class="off-card__title">{{ $office->name }}</h3>
                                            <code class="off-card__slug">{{ $office->slug }}</code>
                                            @if($office->description)
                                                <p class="off-card__desc">{{ Str::limit($office->description, 110) }}</p>
                                            @endif
                                            <div class="off-card__stats">
                                                <div>
                                                    <div class="off-stat__label">Members</div>
                                                    <div class="off-stat__value">{{ $activeCount }} active</div>
                                                </div>
                                                <div>
                                                    <div class="off-stat__label">Head</div>
                                                    <div class="off-stat__value {{ $head ? '' : 'off-stat__value--warn' }}">
                                                        {{ $head ? trim(($head->first_name ?? '') . ' ' . ($head->last_name ?? '')) : 'Not set' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>

                                @if($offices->hasPages())
                                    <div class="pagination-wrapper" style="padding: 14px 16px;">
                                        {{ $offices->links() }}
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- New Office Modal --}}
<div class="off-modal" id="officeModal" style="display:none;">
    <div class="off-modal__backdrop"></div>
    <div class="off-modal__panel">
        <div class="off-modal__head">
            <h3 style="margin:0;">New Office</h3>
            <button type="button" class="off-modal__close" id="closeOfficeModal" aria-label="Close">&times;</button>
        </div>
        <form method="POST" action="{{ route('offices.store') }}">
            @csrf
            <div class="off-form__group">
                <label class="off-form__label">Office name</label>
                <input type="text" name="name" class="off-form__input" placeholder="e.g. Finance Office" required>
            </div>
            <div class="off-form__group">
                <label class="off-form__label">Slug (optional)</label>
                <input type="text" name="slug" class="off-form__input" placeholder="auto-generated from name">
                <p class="off-form__hint">Only letters, numbers, dashes and underscores. Used internally by the form routing system.</p>
            </div>
            <div class="off-form__group">
                <label class="off-form__label">Email (optional)</label>
                <input type="email" name="email" class="off-form__input" placeholder="finance@cug.edu.gh">
            </div>
            <div class="off-form__group">
                <label class="off-form__label">Description</label>
                <textarea name="description" class="off-form__input" rows="3" placeholder="What does this office do?"></textarea>
            </div>
            <div class="off-form__actions">
                <button type="button" class="off-btn off-btn--ghost" id="cancelOfficeModal">Cancel</button>
                <button type="submit" class="off-btn off-btn--primary">Create Office</button>
            </div>
        </form>
    </div>
</div>

<style>
.off-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 16px; padding: 16px; }
.off-card { display: block; padding: 18px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; text-decoration: none; color: inherit; transition: all .15s; }
.off-card:hover { border-color: #1d4ed8; transform: translateY(-2px); box-shadow: 0 6px 22px rgba(29,78,216,0.08); text-decoration: none; color: inherit; }
.off-card__top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.off-card__icon { width: 42px; height: 42px; border-radius: 10px; background: #eff6ff; color: #1d4ed8; display: inline-flex; align-items: center; justify-content: center; }
.off-pill { font-size: 10.5px; padding: 3px 10px; border-radius: 99px; font-weight: 600; letter-spacing: 0.3px; text-transform: uppercase; }
.off-pill--ok { background: #ecfdf5; color: #065f46; }
.off-pill--warn { background: #fef3c7; color: #92400e; }
.off-card__title { font-size: 16px; font-weight: 600; color: #111827; margin: 0 0 6px; }
.off-card__slug { font-size: 11px; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 4px; display: inline-block; }
.off-card__desc { color: #6b7280; font-size: 13px; line-height: 1.5; margin: 12px 0 14px; }
.off-card__stats { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; padding-top: 12px; border-top: 1px solid #f3f4f6; }
.off-stat__label { font-size: 10.5px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 2px; }
.off-stat__value { font-size: 13px; font-weight: 600; color: #111827; }
.off-stat__value--warn { color: #dc2626; }
.off-empty { padding: 60px 20px; text-align: center; color: #6b7280; }

.off-modal { position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; }
.off-modal__backdrop { position: absolute; inset: 0; background: rgba(0,0,0,.5); }
.off-modal__panel { position: relative; background: #fff; padding: 24px; border-radius: 12px; width: 95%; max-width: 560px; box-shadow: 0 20px 50px rgba(0,0,0,.25); max-height: 90vh; overflow-y: auto; }
.off-modal__head { display: flex; justify-content: space-between; align-items: center; padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid #f3f4f6; }
.off-modal__close { background: none; border: none; font-size: 26px; line-height: 1; color: #6b7280; cursor: pointer; padding: 0 6px; }
.off-form__group { margin-bottom: 14px; }
.off-form__label { display: block; font-size: 13px; font-weight: 600; color: #374151; margin-bottom: 6px; }
.off-form__input { width: 100%; padding: 9px 12px; border: 1px solid #d1d5db; border-radius: 7px; font-size: 14px; }
.off-form__input:focus { outline: none; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,.1); }
.off-form__hint { font-size: 11.5px; color: #6b7280; margin: 4px 0 0; }
.off-form__actions { display: flex; justify-content: flex-end; gap: 10px; margin-top: 18px; padding-top: 14px; border-top: 1px solid #f3f4f6; }
.off-btn { padding: 9px 18px; border-radius: 7px; font-weight: 600; font-size: 14px; border: 1px solid transparent; cursor: pointer; }
.off-btn--primary { background: #1d4ed8; color: #fff; }
.off-btn--primary:hover { background: #1e40af; }
.off-btn--ghost { background: #fff; color: #4b5563; border-color: #d1d5db; }
.off-btn--ghost:hover { border-color: #1d4ed8; color: #1d4ed8; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('officeModal');
    const open  = document.getElementById('triggerOfficeModal');
    const close = document.getElementById('closeOfficeModal');
    const cancel = document.getElementById('cancelOfficeModal');
    const backdrop = modal.querySelector('.off-modal__backdrop');

    function show() { modal.style.display = 'flex'; }
    function hide() { modal.style.display = 'none'; }

    open.addEventListener('click', show);
    close.addEventListener('click', hide);
    cancel.addEventListener('click', hide);
    backdrop.addEventListener('click', hide);
});
</script>
@endsection
