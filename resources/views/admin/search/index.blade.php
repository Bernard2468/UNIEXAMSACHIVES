@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

@php
    $q          = $q ?? '';
    $groups     = $groups ?? [];
    $activeType = $type ?? null;
    $total      = $total ?? 0;

    // Item-type → inline icon. Mirrors the header dropdown's icon set.
    $icons = [
        'page'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>',
        'file'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/></svg>',
        'folder'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.9a2 2 0 0 1-1.7-.9l-.8-1.2A2 2 0 0 0 7.9 3H4a2 2 0 0 0-2 2v13a2 2 0 0 0 2 2Z"/></svg>',
        'exam'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>',
        'memo'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>',
        'form'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><rect x="8" y="2" width="8" height="4" rx="1"/><path d="M9 14l2 2 4-4"/></svg>',
        'committee'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
        'user'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
        'department' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14M9 9v.01M9 12v.01M9 15v.01M13 9v.01M13 12v.01M13 15v.01"/></svg>',
        'position'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2l2.4 7.4H22l-6 4.6 2.3 7.4L12 17l-6.3 4.4L8 14 2 9.4h7.6z"/></svg>',
        'office'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v16M9 7h1M9 11h1M9 15h1M14 7h1M14 11h1M14 15h1"/></svg>',
        'policy'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
        'manual'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/><path d="M9 7h6"/></svg>',
    ];
    $iconFor = fn ($t) => $icons[$t] ?? $icons['page'];

    // Escape first, then wrap matches — output is safe to render unescaped.
    $hl = function ($text) use ($q) {
        $safe = e((string) $text);
        $needle = trim($q);
        if ($needle === '') return $safe;
        $terms = array_filter(preg_split('/\s+/', $needle), fn ($t) => $t !== '');
        $escaped = array_map(fn ($t) => preg_quote($t, '/'), $terms);
        if (empty($escaped)) return $safe;
        return preg_replace('/(' . implode('|', $escaped) . ')/i', '<mark>$1</mark>', $safe);
    };

    // When a ?type= filter is active, render only that section (chips still list all).
    $shown = $activeType
        ? (isset($groups[$activeType]) ? [$activeType => $groups[$activeType]] : [])
        : $groups;
@endphp

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
                        <div class="uds-results">

                            <div class="uds-results__head">
                                @if($q === '')
                                    <h4>Search everything</h4>
                                    <p>Use the search bar in the header to find files, folders, exams, memos, forms, people and more — everything you own or that has been shared with you.</p>
                                @else
                                    <h4>Results for &ldquo;{{ $q }}&rdquo;</h4>
                                    <p>
                                        @if($total > 0)
                                            <strong>{{ $total }}</strong> {{ Str::plural('result', $total) }} across the system
                                        @else
                                            No matches found
                                        @endif
                                    </p>
                                @endif
                            </div>

                            @if($q !== '' && count($groups))
                                <div class="uds-results__chips">
                                    <a href="{{ route('search.index', ['q' => $q]) }}"
                                       class="uds-chip {{ $activeType === null ? 'is-active' : '' }}">
                                        All <span>{{ $total }}</span>
                                    </a>
                                    @foreach($groups as $key => $group)
                                        <a href="{{ route('search.index', ['q' => $q, 'type' => $key]) }}"
                                           class="uds-chip {{ $activeType === $key ? 'is-active' : '' }}">
                                            {{ $group['label'] }} <span>{{ count($group['items']) }}</span>
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            @forelse($shown as $key => $group)
                                <section class="uds-results__group">
                                    <div class="uds-results__grouphd">
                                        <span class="uds-results__groupIcon">{!! $iconFor($group['items'][0]['type'] ?? 'page') !!}</span>
                                        <h5>{{ $group['label'] }}</h5>
                                        <span class="uds-results__groupCount">{{ count($group['items']) }}</span>
                                    </div>

                                    <div class="uds-results__list">
                                        @foreach($group['items'] as $item)
                                            <a href="{{ $item['url'] }}" class="uds-res">
                                                <span class="uds-res__icon">{!! $iconFor($item['type']) !!}</span>
                                                <span class="uds-res__body">
                                                    <span class="uds-res__title">{!! $hl($item['title']) !!}</span>
                                                    @if(!empty($item['subtitle']))
                                                        <span class="uds-res__sub">{!! $hl($item['subtitle']) !!}</span>
                                                    @endif
                                                </span>
                                                <span class="uds-res__meta">
                                                    @if(!empty($item['badge']))
                                                        <span class="uds-res__badge">{{ $item['badge'] }}</span>
                                                    @endif
                                                    @if(!empty($item['date']))
                                                        <span class="uds-res__date">{{ $item['date'] }}</span>
                                                    @endif
                                                    <svg class="uds-res__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>

                                    @if(!empty($group['has_more']))
                                        <p class="uds-results__more">Showing the top {{ count($group['items']) }} {{ $group['label'] }} — refine your search to narrow further.</p>
                                    @endif
                                </section>
                            @empty
                                <div class="uds-results__empty">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                    @if($q === '')
                                        <h5>Nothing to show yet</h5>
                                        <p>Start typing in the header search to look across the whole system.</p>
                                    @elseif($activeType)
                                        <h5>No {{ $groups[$activeType]['label'] ?? 'results' }} matched</h5>
                                        <p>Try the <a href="{{ route('search.index', ['q' => $q]) }}">All results</a> view, or a different keyword.</p>
                                    @else
                                        <h5>No matches for &ldquo;{{ $q }}&rdquo;</h5>
                                        <p>Only items you own or that are shared with you appear in search. Try a different keyword.</p>
                                    @endif
                                </div>
                            @endforelse

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
.uds-results { font-family: 'Outfit', system-ui, -apple-system, 'Segoe UI', sans-serif; }
.uds-results__head { margin-bottom: 18px; }
.uds-results__head h4 { font-size: 1.4rem; font-weight: 700; color: #0c0c0c; letter-spacing: -.02em; margin: 0 0 4px; }
.uds-results__head p { margin: 0; font-size: .9rem; color: #9ca3af; }
.uds-results__head strong { color: #374151; }

.uds-results__chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 22px; }
.uds-chip {
    display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px; border-radius: 999px;
    background: #f4f5f7; border: 1.5px solid transparent; color: #4b5563; font-size: .82rem; font-weight: 600;
    text-decoration: none; transition: all .15s;
}
.uds-chip span { font-size: .72rem; font-weight: 700; color: #9ca3af; }
.uds-chip:hover { background: #ececf0; color: #111827; }
.uds-chip.is-active { background: #0c0c0c; color: #fff; }
.uds-chip.is-active span { color: rgba(255,255,255,.65); }

.uds-results__group { margin-bottom: 26px; }
.uds-results__grouphd { display: flex; align-items: center; gap: 9px; margin-bottom: 10px; }
.uds-results__groupIcon { width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center; color: #6b7280; }
.uds-results__groupIcon svg { width: 18px; height: 18px; }
.uds-results__grouphd h5 { margin: 0; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #6b7280; }
.uds-results__groupCount { font-size: .72rem; font-weight: 700; color: #b6bcc6; background: #f4f5f7; padding: 2px 9px; border-radius: 999px; }

.uds-results__list { display: flex; flex-direction: column; gap: 6px; }
.uds-res {
    display: flex; align-items: center; gap: 13px; padding: 12px 14px; border-radius: 13px;
    background: #fff; border: 1.5px solid #eef0f3; text-decoration: none; color: inherit; transition: all .15s;
}
.uds-res:hover { border-color: #d7dbe2; box-shadow: 0 6px 18px rgba(12,12,12,.06); transform: translateY(-1px); }
.uds-res__icon { flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; background: #f0f1f4; color: #4b5563; display: flex; align-items: center; justify-content: center; }
.uds-res__icon svg { width: 19px; height: 19px; }
.uds-res__body { min-width: 0; flex: 1; }
.uds-res__title { display: block; font-size: .95rem; font-weight: 600; color: #1f2937; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.uds-res__title mark, .uds-res__sub mark { background: #fde68a; color: inherit; padding: 0 1px; border-radius: 3px; }
.uds-res__sub { display: block; font-size: .8rem; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-top: 2px; }
.uds-res__meta { flex-shrink: 0; display: flex; align-items: center; gap: 12px; }
.uds-res__badge { font-size: .66rem; font-weight: 700; letter-spacing: .03em; text-transform: uppercase; padding: 3px 9px; border-radius: 999px; background: #eef1f6; color: #64748b; white-space: nowrap; }
.uds-res__date { font-size: .74rem; color: #b6bcc6; white-space: nowrap; }
.uds-res__chev { color: #cbd0d8; flex-shrink: 0; }
.uds-res:hover .uds-res__chev { color: #6b7280; }
.uds-results__more { margin: 10px 2px 0; font-size: .76rem; color: #b6bcc6; }

.uds-results__empty { text-align: center; padding: 60px 20px; }
.uds-results__empty svg { width: 44px; height: 44px; color: #d1d5db; margin-bottom: 12px; }
.uds-results__empty h5 { font-size: 1.05rem; font-weight: 700; color: #374151; margin: 0 0 6px; }
.uds-results__empty p { font-size: .88rem; color: #9ca3af; margin: 0; }
.uds-results__empty a { color: #0c0c0c; font-weight: 600; text-decoration: underline; }

/* ── Dark mode ── */
.is_dark .uds-results__head h4 { color: #f3f4f6; }
.is_dark .uds-results__head strong { color: #e5e7eb; }
.is_dark .uds-chip { background: #1a2233; color: #cbd5e1; }
.is_dark .uds-chip:hover { background: #232c40; color: #fff; }
.is_dark .uds-chip.is-active { background: #f3f4f6; color: #0c0c0c; }
.is_dark .uds-chip.is-active span { color: rgba(12,12,12,.55); }
.is_dark .uds-results__groupCount { background: #1a2233; color: #64748b; }
.is_dark .uds-res { background: #111827; border-color: #1e2330; }
.is_dark .uds-res:hover { border-color: #2d3748; box-shadow: 0 6px 18px rgba(0,0,0,.4); }
.is_dark .uds-res__icon { background: #1e2330; color: #cbd5e1; }
.is_dark .uds-res__title { color: #e5e7eb; }
.is_dark .uds-res__title mark, .is_dark .uds-res__sub mark { background: #a16207; color: #fff; }
.is_dark .uds-res__badge { background: #1e2330; color: #94a3b8; }
.is_dark .uds-results__empty h5 { color: #e5e7eb; }
.is_dark .uds-results__empty a { color: #f3f4f6; }
</style>
@endpush

@endsection
