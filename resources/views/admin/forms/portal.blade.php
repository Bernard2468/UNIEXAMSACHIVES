@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding">
        <div class="row">
            @include('components.create_section')
        </div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div class="dashboard__content__wraper">
                        <div class="dashboard__section__title">
                            <h4>Forms Portal</h4>
                            <div class="dashboard__section__actions">
                                <a href="{{ route('admin.forms.gallery') }}" class="responsive-btn compose-btn">
                                    <div class="svgWrapper">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="svgIcon">
                                            <path stroke="#fff" stroke-width="2" d="M12 5v14m-7-7h14"></path>
                                        </svg>
                                        <div class="text">New Form</div>
                                    </div>
                                </a>
                            </div>
                        </div>

                        <div class="form-tabs">
                            @php
                                $tabs = [
                                    'awaiting'  => ['Awaiting My Action', $counts['awaiting']],
                                    'mine'      => ['My Submitted', $counts['mine']],
                                    'drafts'    => ['Drafts', $counts['drafts']],
                                    'signed'    => ['Signed by Me', $counts['signed']],
                                    'completed' => ['Completed', $counts['completed']],
                                ];
                            @endphp
                            @foreach($tabs as $key => [$label, $count])
                                <a href="{{ route('admin.forms.portal', ['tab' => $key]) }}"
                                   class="form-tab {{ $tab === $key ? 'form-tab--active' : '' }}">
                                    {{ $label }}
                                    <span class="form-tab__count">{{ $count }}</span>
                                </a>
                            @endforeach
                        </div>

                        @include('components.premium-search-bar', [
                            'placeholder'      => 'Search by reference, form, title, person, or status…',
                            'countLabel'       => 'submissions',
                            'id'               => 'forms-portal-search',
                            'ajax'             => true,
                            'resultsContainer' => '#forms-portal-results',
                        ])

                        <div id="forms-portal-results" data-search-results>
                            <span data-psb-meta data-total="{{ $submissions->total() }}" hidden></span>
                        @if($submissions->isEmpty())
                            <div class="empty-state">
                                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#9ca3af" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <path d="M9 9h6M9 13h6M9 17h4"></path>
                                </svg>
                                <h5>Nothing here yet</h5>
                                <p>
                                    @if(!empty($search))
                                        No submissions match “{{ $search }}” in this tab.
                                    @elseif($tab === 'awaiting')
                                        No forms are currently waiting on your action.
                                    @else
                                        No forms match this view.
                                    @endif
                                </p>
                                @if($tab === 'awaiting')
                                    <a href="{{ route('admin.forms.gallery') }}" class="btn-action btn-action--primary">Start a new form</a>
                                @endif
                            </div>
                        @else
                            <div class="form-list">
                                <table class="form-list__table">
                                    <thead>
                                        <tr>
                                            <th>Reference</th>
                                            <th>Form</th>
                                            <th>Title</th>
                                            <th>Requisitioner</th>
                                            <th>Status</th>
                                            <th>Awaiting</th>
                                            <th>Updated</th>
                                            <th></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($submissions as $s)
                                            @php
                                                $creatorName = trim((optional($s->creator)->first_name ?? '') . ' ' . (optional($s->creator)->last_name ?? ''));
                                                $awaitingName = $s->currentAssignee
                                                    ? trim(($s->currentAssignee->first_name ?? '') . ' ' . ($s->currentAssignee->last_name ?? ''))
                                                    : '';
                                                $officeName = $s->currentOffice?->name ?? '';
                                            @endphp
                                            <tr data-search="{{ strtolower(implode(' ', array_filter([
                                                $s->reference,
                                                $s->form_code,
                                                $s->title,
                                                $creatorName,
                                                $s->status,
                                                $awaitingName,
                                                $officeName,
                                            ]))) }}">
                                                <td><code>{{ $s->reference }}</code></td>
                                                <td><span class="form-list__code">{{ $s->form_code }}</span></td>
                                                <td>{{ $s->title ?? '—' }}</td>
                                                <td>{{ $creatorName ?: '—' }}</td>
                                                <td><span class="status-pill status-pill--{{ $s->status }}">{{ str_replace('_', ' ', $s->status) }}</span></td>
                                                <td>
                                                    @if($s->currentAssignee)
                                                        {{ $awaitingName }}
                                                        @if($officeName)<small style="color:#9ca3af;">— {{ $officeName }}</small>@endif
                                                    @else
                                                        —
                                                    @endif
                                                </td>
                                                <td>
                                                    <small>{{ $s->updated_at->diffForHumans() }}</small>
                                                    @if($s->stale_severity)
                                                        <span class="stale-pill stale-pill--{{ $s->stale_severity }}" title="No movement in {{ $s->stale_days }} day{{ $s->stale_days === 1 ? '' : 's' }}">
                                                            {{ $s->stale_days }}d
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ route('admin.forms.show', $s->id) }}" class="btn-action btn-action--ghost" style="padding: 6px 12px; font-size: 12.5px;">Open</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div style="margin-top: 16px;">
                                {{ $submissions->links() }}
                            </div>
                        @endif
                        </div>{{-- /#forms-portal-results --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.form-tabs { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 22px; padding-bottom: 8px; border-bottom: 1px solid #e5e7eb; }
.form-tab { display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px; border-radius: 8px 8px 0 0; color: #4b5563; font-weight: 500; font-size: 13.5px; text-decoration: none; border: 1px solid transparent; border-bottom: none; transition: all 0.15s; }
.form-tab:hover { background: #f3f4f6; color: #1d4ed8; text-decoration: none; }
.form-tab--active { background: #fff; color: #1d4ed8; border-color: #e5e7eb; border-bottom: 2px solid #1d4ed8; margin-bottom: -2px; }
.form-tab__count { background: #f3f4f6; color: #4b5563; padding: 2px 8px; border-radius: 99px; font-size: 11px; font-weight: 600; }
.form-tab--active .form-tab__count { background: #1d4ed8; color: #fff; }

.empty-state { text-align: center; padding: 60px 20px; background: #fff; border-radius: 12px; border: 1px solid #e5e7eb; }
.empty-state h5 { margin: 14px 0 6px; color: #111827; }
.empty-state p { color: #6b7280; margin-bottom: 18px; }

.form-list { background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; overflow: auto; }
.form-list__table { width: 100%; border-collapse: collapse; }
.form-list__table th { text-align: left; padding: 12px 14px; background: #f9fafb; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; font-weight: 600; }
.form-list__table td { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; font-size: 13.5px; color: #374151; }
.form-list__table tbody tr:hover { background: #f9fafb; }
.form-list__table code { background: #f3f4f6; padding: 2px 8px; border-radius: 4px; font-size: 12px; color: #1d4ed8; font-weight: 600; }
.form-list__code { display: inline-block; padding: 3px 8px; background: #eff6ff; color: #1d4ed8; border-radius: 4px; font-size: 11px; font-weight: 700; letter-spacing: 0.5px; }

.stale-pill { display: inline-block; margin-left: 8px; padding: 2px 7px; border-radius: 99px; font-size: 10px; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; vertical-align: middle; }
.stale-pill--warn   { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
.stale-pill--danger { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
.is_dark .stale-pill--warn   { background: #422006; color: #fde68a; border-color: #78350f; }
.is_dark .stale-pill--danger { background: #450a0a; color: #fca5a5; border-color: #7f1d1d; }
</style>

@include('admin.forms.partials.shared-styles')
@endsection
