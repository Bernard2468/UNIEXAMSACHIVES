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

                        <div class="ps-page-header">
                            <div>
                                <h1 class="ps-page-title">Offices<span class="ps-title-bar"></span></h1>
                                <p class="ps-page-sub">Institutional offices that forms route through. Add their members and designate the head of each.</p>
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
                                </div>
                            </div>

                            @if($offices->isNotEmpty() || $search !== '')
                                <div class="ps-card__search-wrap">
                                    @include('components.premium-search-bar', [
                                        'placeholder'      => 'Search offices by name, slug, or head…',
                                        'countLabel'       => 'offices',
                                        'id'               => 'offices-index-search',
                                        'ajax'             => true,
                                        'resultsContainer' => '#offices-results',
                                    ])
                                </div>
                            @endif

                            <div id="offices-results" data-search-results>
                                <span data-psb-meta data-total="{{ $offices->total() }}" hidden></span>
                            @if($offices->isEmpty())
                                @if($search !== '')
                                    <div class="ps-empty">
                                        <div class="ps-empty__icon">
                                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                        </div>
                                        <p class="ps-empty__text">No offices match “{{ $search }}”. Try a different keyword.</p>
                                    </div>
                                @else
                                    <div class="ps-empty">
                                        <div class="ps-empty__icon">
                                            <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"/></svg>
                                        </div>
                                        <p class="ps-empty__text">No offices yet. Create one to start routing forms.</p>
                                        <button type="button" class="ps-btn-primary" id="triggerOfficeModalEmpty">
                                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                            Create first office
                                        </button>
                                    </div>
                                @endif
                            @else
                                <div class="off-grid">
                                    @foreach($offices as $office)
                                        @php
                                            $head = $office->users->where('pivot.is_head', true)->where('pivot.is_active', true)->first();
                                            $activeCount = $office->users->where('pivot.is_active', true)->count();
                                            $headName = $head ? trim(($head->first_name ?? '') . ' ' . ($head->last_name ?? '')) : null;
                                        @endphp
                                        <a href="{{ route('offices.show', $office->id) }}"
                                           class="off-card"
                                           data-search="{{ strtolower(implode(' ', array_filter([
                                               $office->name,
                                               $office->slug,
                                               $office->description,
                                               $headName,
                                               $office->is_active ? 'active' : 'inactive',
                                           ]))) }}">
                                            <div class="off-card__top">
                                                <div class="off-card__icon">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"/></svg>
                                                </div>
                                                @if($office->is_active)
                                                    <span class="off-pill off-pill--ok"><span class="off-pill-dot"></span>Active</span>
                                                @else
                                                    <span class="off-pill off-pill--warn"><span class="off-pill-dot"></span>Inactive</span>
                                                @endif
                                            </div>
                                            <h3 class="off-card__title">{{ $office->name }}</h3>
                                            <code class="off-card__slug">{{ $office->slug }}</code>

                                            <div class="off-card__stats">
                                                <div class="off-stat">
                                                    <div class="off-stat__label">Members</div>
                                                    <div class="off-stat__value">{{ $activeCount }}</div>
                                                </div>
                                                <div class="off-stat">
                                                    <div class="off-stat__label">Head</div>
                                                    <div class="off-stat__value {{ $head ? '' : 'off-stat__value--warn' }}">
                                                        {{ $headName ?: '—' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    @endforeach
                                </div>

                                @if($offices->hasPages())
                                    <div class="pagination-wrapper">
                                        <div class="pagination-info">
                                            Showing <strong>{{ $offices->firstItem() }}</strong>–<strong>{{ $offices->lastItem() }}</strong> of <strong>{{ $offices->total() }}</strong>
                                        </div>
                                        <div class="pagination-controls">
                                            <ul class="pagination">
                                                @if($offices->onFirstPage())
                                                    <li class="pagination-item"><span class="pagination-link icon disabled"><i class="icofont-arrow-left"></i></span></li>
                                                @else
                                                    <li class="pagination-item"><a href="{{ $offices->appends(request()->only('q'))->previousPageUrl() }}" class="pagination-link icon"><i class="icofont-arrow-left"></i></a></li>
                                                @endif
                                                @for ($i = max(1, $offices->currentPage()-2); $i <= min($offices->lastPage(), $offices->currentPage()+2); $i++)
                                                    <li class="pagination-item">
                                                        @if ($i == $offices->currentPage())
                                                            <span class="pagination-link active">{{ $i }}</span>
                                                        @else
                                                            <a href="{{ $offices->appends(request()->only('q'))->url($i) }}" class="pagination-link">{{ $i }}</a>
                                                        @endif
                                                    </li>
                                                @endfor
                                                @if ($offices->hasMorePages())
                                                    <li class="pagination-item"><a href="{{ $offices->appends(request()->only('q'))->nextPageUrl() }}" class="pagination-link icon"><i class="icofont-arrow-right"></i></a></li>
                                                @else
                                                    <li class="pagination-item"><span class="pagination-link icon disabled"><i class="icofont-arrow-right"></i></span></li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            @endif
                            </div>{{-- /#offices-results --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- New office modal --}}
<div class="modal fade" id="officeModal" tabindex="-1" aria-labelledby="officeModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" style="max-width: 520px;">
        <div class="ps-modal">
            <div class="ps-modal__hd">
                <div class="ps-modal__hd-left">
                    <div class="ps-modal__hd-icon" aria-hidden="true">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"/></svg>
                    </div>
                    <div>
                        <h5 class="ps-modal__title" id="officeModalTitle">New office</h5>
                        <p class="ps-modal__sub">An office that forms route through (e.g. Finance, Registrar).</p>
                    </div>
                </div>
                <button type="button" class="ps-modal__close" data-bs-dismiss="modal" aria-label="Close">
                    <svg width="16" height="16" viewBox="0 0 14 14" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><path d="M11 3L3 11M3 3l8 8"/></svg>
                </button>
            </div>
            <div class="ps-modal__body">
                <form method="POST" action="{{ route('offices.store') }}" id="officeCreateForm">
                    @csrf
                    <div class="ps-modal__field">
                        <label class="ps-modal__label" for="officeName">Office name <span class="ps-modal__req">*</span></label>
                        <input type="text" id="officeName" name="name" class="ps-modal__input" placeholder="e.g. Finance Office" required autocomplete="off" maxlength="255">
                    </div>
                    <div class="ps-modal__field">
                        <label class="ps-modal__label" for="officeSlug">Slug <span class="off-optional">auto-generated if blank</span></label>
                        <input type="text" id="officeSlug" name="slug" class="ps-modal__input" placeholder="e.g. finance-office" autocomplete="off" maxlength="120" pattern="[A-Za-z0-9_\-]*">
                        <p class="ps-modal__help">URL-friendly identifier. Letters, numbers, dashes only.</p>
                    </div>
                    <div class="ps-modal__field">
                        <label class="ps-modal__label" for="officeEmail">Email <span class="off-optional">optional</span></label>
                        <input type="email" id="officeEmail" name="email" class="ps-modal__input" placeholder="finance@cug.edu.gh" autocomplete="off" maxlength="255">
                    </div>
                    <div class="ps-modal__field">
                        <label class="ps-modal__label" for="officeDescription">Description <span class="off-optional">optional</span></label>
                        <textarea id="officeDescription" name="description" class="ps-modal__input" rows="2" placeholder="What does this office do?" maxlength="2000"></textarea>
                    </div>
                    <div class="ps-modal__foot">
                        <button type="button" class="ps-modal__btn-cancel" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="ps-modal__btn-save" id="officeCreateSubmit">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Save office</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');
.ps-wrap, .ps-wrap * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.ps-wrap { max-width: 1080px; padding: 4px 0 60px; }

/* Page header */
.ps-page-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb; }
.ps-page-title { font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em; line-height: 1.1; margin: 0; display: inline-flex; flex-direction: column; }
.ps-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.ps-page-sub { margin: 12px 0 0; font-size: 0.88rem; color: #8a8fa0; font-weight: 400; max-width: 520px; line-height: 1.5; }

.off-optional { font-weight: 400; font-size: 0.72rem; color: #b0b5c0; margin-left: 4px; }

/* Primary button */
.ps-btn-primary { display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px; background: #0c0c0c; color: #fff; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; white-space: nowrap; flex-shrink: 0; margin-top: 14px; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.ps-btn-primary:hover { background: #1f2937; color: #fff; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* Alerts */
.ps-alert { display: flex; align-items: flex-start; gap: 10px; padding: 12px 14px; border-radius: 10px; margin-bottom: 16px; font-size: 0.875rem; font-weight: 500; border: 1.5px solid transparent; }
.ps-alert--ok  { background: #f0fdf4; border-color: #bbf7d0; color: #15803d; }
.ps-alert--err { background: #fef2f2; border-color: #fecaca; color: #b91c1c; }
.ps-alert__x { margin-left: auto; background: none; border: none; cursor: pointer; opacity: .45; color: inherit; padding: 0; display: flex; }

/* Card */
.ps-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.ps-card__hd { padding: 18px 24px 14px; border-bottom: 1.5px solid #f5f5f5; }
.ps-card__title { font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 0; display: inline-flex; flex-direction: column; }
.ps-card__bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.ps-card__count { margin: 8px 0 0; font-size: 0.78rem; color: #b0b5c0; }
.ps-card__search-wrap { padding: 0 18px 6px; border-bottom: 1.5px solid #f5f5f5; margin-bottom: 4px; }
.is_dark .ps-card__search-wrap { border-color: #1e2330; }

/* Office cards grid */
.off-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px, 1fr)); gap: 14px; padding: 18px 18px 20px; }
.off-card { display: flex; flex-direction: column; padding: 18px; background: #fff; border: 1.5px solid #ebebeb; border-radius: 14px; text-decoration: none !important; color: inherit; transition: all .18s; position: relative; }
.off-card:hover { border-color: #0c0c0c; transform: translateY(-2px); box-shadow: 0 8px 24px rgba(12,12,12,.08); color: inherit; }
.off-card__top { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 16px; }
.off-card__icon { width: 38px; height: 38px; border-radius: 10px; background: #0c0c0c; color: #fff; display: inline-flex; align-items: center; justify-content: center; }
.off-card__title { font-size: 1rem; font-weight: 700; color: #111827; margin: 0 0 6px; letter-spacing: -0.02em; line-height: 1.2; }
.off-card__slug { font-size: 0.7rem; color: #6b7280; background: #f3f4f6; padding: 2px 8px; border-radius: 99px; display: inline-block; font-family: 'JetBrains Mono', monospace !important; font-weight: 500; align-self: flex-start; }
.off-card__stats { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; padding-top: 16px; margin-top: 16px; border-top: 1.5px dashed #ebebeb; }
.off-stat__label { font-size: 0.66rem; color: #b0b5c0; text-transform: uppercase; letter-spacing: 0.06em; font-weight: 600; margin-bottom: 3px; }
.off-stat__value { font-size: 0.84rem; font-weight: 600; color: #111827; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.off-stat__value--warn { color: #d97706; }

.off-pill { display: inline-flex; align-items: center; gap: 5px; font-size: 0.66rem; padding: 3px 9px; border-radius: 99px; font-weight: 600; letter-spacing: 0.4px; text-transform: uppercase; }
.off-pill-dot { width: 5px; height: 5px; border-radius: 50%; background: currentColor; }
.off-pill--ok { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
.off-pill--warn { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }

/* Empty */
.ps-empty { padding: 60px 24px; text-align: center; }
.ps-empty__icon { display: inline-flex; padding: 18px; background: #f9fafb; border: 1.5px solid #ebebeb; border-radius: 16px; color: #d1d5db; margin-bottom: 16px; }
.ps-empty__text { font-size: 0.9rem; color: #9ca3af; margin-bottom: 20px; }

/* Modal — reuses the positions modal styling.
   pointer-events: auto is REQUIRED because we use .ps-modal instead of Bootstrap's
   .modal-content. Bootstrap sets .modal-dialog{pointer-events:none} so clicks pass
   through to the backdrop; .modal-content normally re-enables them. We must do the
   same on .ps-modal or the modal is visible but completely uninteractive. */
.ps-modal { background: #fff; border-radius: 18px; overflow: hidden; border: 1.5px solid #ebebeb; font-family: 'Outfit', sans-serif !important; pointer-events: auto; box-shadow: 0 24px 60px -12px rgba(12,12,12,0.22), 0 0 0 1px rgba(12,12,12,0.04); }
.ps-modal * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
#officeModal .modal-dialog { pointer-events: auto; }
.ps-modal__hd { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; padding: 20px 22px 16px; border-bottom: 1.5px solid #f3f4f6; }
.ps-modal__hd-left { display: flex; align-items: flex-start; gap: 12px; min-width: 0; }
.ps-modal__hd-icon { width: 38px; height: 38px; flex: 0 0 38px; border-radius: 10px; background: #0c0c0c; color: #fff; display: inline-flex; align-items: center; justify-content: center; }
.ps-modal__title { font-size: 1.02rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em; margin: 2px 0 4px; }
.ps-modal__sub { font-size: 0.82rem; color: #9ca3af; margin: 0; line-height: 1.4; }
.ps-modal__close { background: none; border: 1.5px solid transparent; cursor: pointer; padding: 6px; color: #9ca3af; border-radius: 8px; display: flex; transition: all .15s; flex-shrink: 0; }
.ps-modal__close:hover { background: #f3f4f6; color: #0c0c0c; border-color: #e5e7eb; }
.ps-modal__body { padding: 20px 22px 22px; }
.ps-modal__field { margin-bottom: 14px; }
.ps-modal__label { display: block; font-size: 0.78rem; font-weight: 600; color: #374151; margin-bottom: 7px; }
.ps-modal__req { color: #dc2626; margin-left: 2px; }
.ps-modal__help { font-size: 0.72rem; color: #9ca3af; margin: 6px 0 0; line-height: 1.4; }
.ps-modal__input { display: block; width: 100%; padding: 11px 14px; background: #fff; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.88rem; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s; font-family: 'Outfit', sans-serif !important; pointer-events: auto; }
.ps-modal__input:hover { border-color: #d1d5db; }
.ps-modal__input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.08); }
.ps-modal__input::placeholder { color: #d4d7de; }
.ps-modal__input:invalid:not(:placeholder-shown) { border-color: #fecaca; }
textarea.ps-modal__input { resize: vertical; min-height: 64px; }
.ps-modal__foot { display: flex; justify-content: flex-end; gap: 10px; padding-top: 8px; margin-top: 4px; border-top: 1.5px solid #f3f4f6; padding-top: 16px; }
.ps-modal__btn-cancel { padding: 10px 20px; background: none; border: 1.5px solid #e5e7eb; border-radius: 10px; font-size: 0.85rem; font-weight: 600; color: #6b7280; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.ps-modal__btn-cancel:hover { border-color: #d1d5db; color: #374151; background: #f9fafb; }
.ps-modal__btn-save { display: inline-flex; align-items: center; gap: 7px; padding: 10px 20px; background: #0c0c0c; color: #fff; border: none; border-radius: 10px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.ps-modal__btn-save:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }

/* Pagination */
.pagination-wrapper { display: flex; align-items: center; justify-content: space-between; padding: 14px 20px; border-top: 1.5px solid #f5f5f5; gap: 1rem; flex-wrap: wrap; }
.pagination-info { font-size: 0.82rem; color: #6b7280; }
.pagination-info strong { color: #111827; font-weight: 600; }
.pagination { display: flex; list-style: none; margin: 0; padding: 0; gap: 0.25rem; }
.pagination-link { display: inline-flex; align-items: center; justify-content: center; min-width: 2rem; height: 2rem; padding: 0 0.5rem; border: 1px solid #e5e7eb; border-radius: 0.375rem; font-size: 0.82rem; color: #374151; text-decoration: none; background: #fff; font-family: 'Outfit', sans-serif !important; }
.pagination-link:hover:not(.disabled):not(.active) { background: #f3f4f6; }
.pagination-link.active { background: #0c0c0c; color: #fff; border-color: #0c0c0c; font-weight: 600; }
.pagination-link.disabled { color: #d4d7de; cursor: not-allowed; }

/* Dark mode */
.is_dark .ps-page-title  { color: #f3f4f6; }
.is_dark .ps-title-bar   { background: #f3f4f6; }
.is_dark .ps-page-sub    { color: #6b7280; }
.is_dark .ps-page-header { border-color: #1e2330; }
.is_dark .ps-btn-primary { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ps-card        { background: #111827; border-color: #1e2330; }
.is_dark .ps-card__hd    { border-color: #1e2330; }
.is_dark .ps-card__title { color: #f3f4f6; }
.is_dark .ps-card__bar   { background: #f3f4f6; }
.is_dark .off-card       { background: #111827; border-color: #1e2330; }
.is_dark .off-card:hover { border-color: #f3f4f6; }
.is_dark .off-card__icon { background: #f3f4f6; color: #0c0c0c; }
.is_dark .off-card__title { color: #f3f4f6; }
.is_dark .off-card__stats { border-color: #1e2330; }
.is_dark .off-stat__value { color: #f3f4f6; }
.is_dark .ps-modal { background: #111827; border-color: #1e2330; }
.is_dark .ps-modal__hd { border-color: #1e2330; }
.is_dark .ps-modal__title { color: #f3f4f6; }
.is_dark .ps-modal__input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .ps-modal__input:focus { border-color: #f3f4f6; }
.is_dark .ps-modal__btn-save { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ps-modal__hd-icon { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ps-modal__sub { color: #6b7280; }
.is_dark .ps-modal__help { color: #6b7280; }
.is_dark .ps-modal__foot { border-color: #1e2330; }
.is_dark .ps-modal__hd { border-color: #1e2330; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var modalEl = document.getElementById('officeModal');
    if (!modalEl) return;

    // Reparent the modal to <body> so no ancestor's overflow/transform/z-index
    // can clip it or break Bootstrap's fixed-position backdrop placement.
    if (modalEl.parentNode !== document.body) {
        document.body.appendChild(modalEl);
    }

    var instance = null;
    function show(e) {
        if (e) { e.preventDefault(); e.stopPropagation(); }
        try {
            if (window.bootstrap && bootstrap.Modal) {
                instance = instance || bootstrap.Modal.getOrCreateInstance(modalEl, {
                    backdrop: 'static',
                    keyboard: false,
                });
                instance.show();
            } else if (window.jQuery) {
                // Fallback: Bootstrap 4-style jQuery plugin if BS5 global missing
                window.jQuery(modalEl).modal({ backdrop: 'static', keyboard: false });
                window.jQuery(modalEl).modal('show');
            } else {
                console.error('[offices] Bootstrap not available to open modal.');
            }
        } catch (err) {
            console.error('[offices] Failed to open office modal:', err);
        }
    }

    // Delegated so the trigger keeps working after AJAX search swaps the
    // results region (which may re-render the empty-state "Create" button).
    document.addEventListener('click', function (e) {
        if (e.target.closest('#triggerOfficeModal, #triggerOfficeModalEmpty')) {
            show(e);
        }
    });
});
</script>
@endsection
