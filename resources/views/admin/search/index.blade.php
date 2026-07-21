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
                        <div class="sr">

                            <header class="sr__head">
                                <p class="sr__eyebrow">Search results</p>
                                @if($q === '')
                                    <h1 class="sr__title">Search everything</h1>
                                    <p class="sr__meta">Find files, folders, exams, memos, forms, people and pages — everything you own or that has been shared with you.</p>
                                @else
                                    <h1 class="sr__title">&ldquo;{{ $q }}&rdquo;</h1>
                                    <p class="sr__meta">
                                        @if($total > 0)
                                            <strong>{{ $total }}</strong> {{ Str::plural('result', $total) }} across the system
                                        @else
                                            No matches found
                                        @endif
                                    </p>
                                @endif
                            </header>

                            @if($q !== '' && count($groups))
                                <nav class="sr__chips" aria-label="Filter by type">
                                    <a href="{{ route('search.index', ['q' => $q]) }}"
                                       class="sr-chip {{ $activeType === null ? 'is-active' : '' }}">
                                        All <span>{{ $total }}</span>
                                    </a>
                                    @foreach($groups as $key => $group)
                                        <a href="{{ route('search.index', ['q' => $q, 'type' => $key]) }}"
                                           class="sr-chip {{ $activeType === $key ? 'is-active' : '' }}">
                                            {{ $group['label'] }} <span>{{ count($group['items']) }}</span>
                                        </a>
                                    @endforeach
                                </nav>
                            @endif

                            @forelse($shown as $key => $group)
                                <section class="sr-group">
                                    <div class="sr-group__head">
                                        <span class="sr-group__icon">{!! $iconFor($group['items'][0]['type'] ?? 'page') !!}</span>
                                        <h2 class="sr-group__label">{{ $group['label'] }}</h2>
                                        <span class="sr-group__count">{{ count($group['items']) }}</span>
                                    </div>

                                    <div class="sr-group__list">
                                        @foreach($group['items'] as $item)
                                            <a href="{{ $item['url'] }}" class="sr-row">
                                                <span class="sr-row__icon">{!! $iconFor($item['type']) !!}</span>
                                                <span class="sr-row__body">
                                                    <span class="sr-row__title">{!! $hl($item['title']) !!}</span>
                                                    @if(!empty($item['subtitle']))
                                                        <span class="sr-row__sub">{!! $hl($item['subtitle']) !!}</span>
                                                    @endif
                                                </span>
                                                <span class="sr-row__meta">
                                                    @if(!empty($item['badge']))
                                                        <span class="sr-row__badge">{{ $item['badge'] }}</span>
                                                    @endif
                                                    @if(!empty($item['date']))
                                                        <span class="sr-row__date">{{ $item['date'] }}</span>
                                                    @endif
                                                    <svg class="sr-row__chev" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
                                                </span>
                                            </a>
                                        @endforeach
                                    </div>

                                    @if(!empty($group['has_more']))
                                        <p class="sr-group__more">Showing the top {{ count($group['items']) }} — refine your search to narrow further.</p>
                                    @endif
                                </section>
                            @empty
                                <div class="sr__empty">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="M20 20l-3.5-3.5"/></svg>
                                    @if($q === '')
                                        <h3>Nothing to show yet</h3>
                                        <p>Start typing in the search bar to look across the whole system.</p>
                                    @elseif($activeType)
                                        <h3>No {{ $groups[$activeType]['label'] ?? 'results' }} matched</h3>
                                        <p>Try the <a href="{{ route('search.index', ['q' => $q]) }}">All results</a> view, or a different keyword.</p>
                                    @else
                                        <h3>No matches for &ldquo;{{ $q }}&rdquo;</h3>
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
.sr { font-family: 'Outfit', system-ui, -apple-system, 'Segoe UI', sans-serif; color: #0f172a; max-width: 820px; }

/* ── Header ── */
.sr__head { padding-bottom: 20px; margin-bottom: 22px; border-bottom: 1px solid #e9ebef; }
.sr__eyebrow { margin: 0 0 8px; font-size: .7rem; font-weight: 700; letter-spacing: .16em; text-transform: uppercase; color: #b6bcc6; }
.sr__title { margin: 0; font-size: 1.85rem; font-weight: 700; letter-spacing: -.025em; color: #0f172a; line-height: 1.15; }
.sr__meta { margin: 8px 0 0; font-size: .92rem; color: #94a3b8; }
.sr__meta strong { color: #0f172a; font-weight: 700; }

/* ── Type chips ── */
.sr__chips { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 30px; }
.sr-chip {
    display: inline-flex; align-items: center; gap: 8px; padding: 7px 15px; border-radius: 999px;
    border: 1.5px solid #e9ebef; color: #64748b; font-size: .82rem; font-weight: 600;
    text-decoration: none; transition: border-color .15s, color .15s, background .15s;
}
.sr-chip span { font-size: .72rem; font-weight: 700; color: #b6bcc6; }
.sr-chip:hover { border-color: #cbd0d8; color: #0f172a; }
.sr-chip.is-active { background: #0f172a; border-color: #0f172a; color: #fff; }
.sr-chip.is-active span { color: rgba(255,255,255,.6); }

/* ── Group: neatly underlined header ── */
.sr-group { margin-bottom: 34px; }
.sr-group__head { display: flex; align-items: center; gap: 10px; padding-bottom: 11px; border-bottom: 1.5px solid #e9ebef; }
.sr-group__icon { color: #0f172a; display: inline-flex; }
.sr-group__icon svg { width: 17px; height: 17px; }
.sr-group__label { margin: 0; flex: 1; font-size: .78rem; font-weight: 700; text-transform: uppercase; letter-spacing: .1em; color: #334155; }
.sr-group__count { font-size: .74rem; font-weight: 700; color: #b6bcc6; }

/* ── Rows: flat, classy, hairline-separated ── */
.sr-group__list { display: flex; flex-direction: column; }
.sr-row {
    display: flex; align-items: center; gap: 15px; padding: 15px 12px;
    border-bottom: 1px solid #f2f3f5; text-decoration: none; color: inherit;
    transition: background .14s, padding-left .14s;
}
.sr-row:hover { background: #f8f9fb; padding-left: 16px; }
.sr-row__icon { flex-shrink: 0; width: 40px; height: 40px; border-radius: 10px; background: #f4f5f7; color: #475569; display: flex; align-items: center; justify-content: center; }
.sr-row__icon svg { width: 19px; height: 19px; }
.sr-row__body { min-width: 0; flex: 1; display: flex; flex-direction: column; gap: 3px; }
.sr-row__title { display: block; font-size: .96rem; font-weight: 600; color: #0f172a; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sr-row__title mark, .sr-row__sub mark { background: transparent; color: inherit; font-weight: 800; box-shadow: inset 0 -.5em 0 #fde68a; border-radius: 2px; }
.sr-row__sub { display: block; font-size: .81rem; color: #94a3b8; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sr-row__meta { flex-shrink: 0; display: flex; align-items: center; gap: 14px; }
.sr-row__badge { font-size: .64rem; font-weight: 700; letter-spacing: .04em; text-transform: uppercase; padding: 4px 10px; border-radius: 999px; border: 1.5px solid #e9ebef; color: #64748b; white-space: nowrap; }
.sr-row__date { font-size: .76rem; color: #b6bcc6; white-space: nowrap; }
.sr-row__chev { color: #cbd0d8; flex-shrink: 0; transition: transform .14s, color .14s; }
.sr-row:hover .sr-row__chev { color: #0f172a; transform: translateX(2px); }
.sr-group__more { margin: 12px 2px 0; font-size: .78rem; color: #b6bcc6; }

/* ── Empty ── */
.sr__empty { text-align: center; padding: 70px 20px; }
.sr__empty svg { width: 44px; height: 44px; color: #d1d5db; margin-bottom: 14px; }
.sr__empty h3 { font-size: 1.1rem; font-weight: 700; color: #334155; margin: 0 0 6px; }
.sr__empty p { font-size: .9rem; color: #94a3b8; margin: 0; }
.sr__empty a { color: #0f172a; font-weight: 600; text-decoration: underline; }

/* ── Dark mode ── */
.is_dark .sr { color: #e5e7eb; }
.is_dark .sr__head { border-color: #1e2330; }
.is_dark .sr__title { color: #f3f4f6; }
.is_dark .sr__meta strong { color: #f3f4f6; }
.is_dark .sr-chip { border-color: #2d3748; color: #94a3b8; }
.is_dark .sr-chip:hover { border-color: #3d4a63; color: #f3f4f6; }
.is_dark .sr-chip.is-active { background: #f3f4f6; border-color: #f3f4f6; color: #0f172a; }
.is_dark .sr-chip.is-active span { color: rgba(15,23,42,.55); }
.is_dark .sr-group__head { border-color: #1e2330; }
.is_dark .sr-group__icon { color: #e5e7eb; }
.is_dark .sr-group__label { color: #cbd5e1; }
.is_dark .sr-row { border-color: #171d2b; }
.is_dark .sr-row:hover { background: #141b29; }
.is_dark .sr-row__icon { background: #1e2330; color: #cbd5e1; }
.is_dark .sr-row__title { color: #f3f4f6; }
.is_dark .sr-row__title mark, .is_dark .sr-row__sub mark { box-shadow: inset 0 -.5em 0 rgba(161,98,7,.7); color: #fff; }
.is_dark .sr-row__badge { border-color: #2d3748; color: #94a3b8; }
.is_dark .sr-row__chev { color: #475569; }
.is_dark .sr-row:hover .sr-row__chev { color: #f3f4f6; }
.is_dark .sr__empty h3 { color: #e5e7eb; }
.is_dark .sr__empty a { color: #f3f4f6; }

@media (max-width: 575px) {
    .sr-row__date { display: none; }
    .sr__title { font-size: 1.5rem; }
}
</style>
@endpush

@endsection
