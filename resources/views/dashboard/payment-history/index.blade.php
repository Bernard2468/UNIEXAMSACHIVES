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
                    <div class="ph-wrap">

                        {{-- ── Page header ── --}}
                        <div class="ph-page-header">
                            <h1 class="ph-page-title">Payment History<span class="ph-title-bar"></span></h1>
                            <p class="ph-page-sub">View and download invoices for all payment transactions on your account.</p>
                        </div>

                        {{-- ── Stat cards ── --}}
                        <div class="ph-stats">
                            <div class="ph-stat">
                                <span class="ph-stat__label">Total transactions</span>
                                <span class="ph-stat__val">{{ $stats['total'] }}</span>
                            </div>
                            <div class="ph-stat">
                                <span class="ph-stat__label">Completed</span>
                                <span class="ph-stat__val ph-stat__val--green">{{ $stats['completed'] }}</span>
                            </div>
                            <div class="ph-stat">
                                <span class="ph-stat__label">Pending</span>
                                <span class="ph-stat__val ph-stat__val--amber">{{ $stats['pending'] }}</span>
                            </div>
                            <div class="ph-stat">
                                <span class="ph-stat__label">Failed</span>
                                <span class="ph-stat__val ph-stat__val--red">{{ $stats['failed'] }}</span>
                            </div>
                            <div class="ph-stat ph-stat--wide">
                                <span class="ph-stat__label">Total revenue</span>
                                <span class="ph-stat__val ph-stat__val--green">{{ $subscription->currency ?? 'GHS' }} {{ number_format($stats['total_revenue'], 2) }}</span>
                            </div>
                        </div>

                        {{-- ── Filters ── --}}
                        <div class="ph-filter-card">
                            <div class="ph-filter-card__hd">
                                <h2 class="ph-filter-title">Filter transactions<span class="ph-filter-bar"></span></h2>
                            </div>
                            <form method="GET" action="{{ route('dashboard.payment-history.index') }}" class="ph-filter-form">
                                <div class="ph-filter-grid">
                                    <div class="ph-field">
                                        <label class="ph-label">Status</label>
                                        <div class="ph-sel-wrap">
                                            <select class="ph-select" name="status">
                                                <option value="all"       {{ request('status') == 'all'       ? 'selected' : '' }}>All statuses</option>
                                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                                <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                                                <option value="failed"    {{ request('status') == 'failed'    ? 'selected' : '' }}>Failed</option>
                                            </select>
                                            <svg class="ph-sel-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                    <div class="ph-field">
                                        <label class="ph-label">Type</label>
                                        <div class="ph-sel-wrap">
                                            <select class="ph-select" name="type">
                                                <option value="all"                  {{ request('type') == 'all'                  ? 'selected' : '' }}>All types</option>
                                                <option value="subscription_renewal" {{ request('type') == 'subscription_renewal' ? 'selected' : '' }}>Renewal</option>
                                                <option value="initial_payment"      {{ request('type') == 'initial_payment'      ? 'selected' : '' }}>Initial</option>
                                            </select>
                                            <svg class="ph-sel-arrow" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9l6 6 6-6"/></svg>
                                        </div>
                                    </div>
                                    <div class="ph-field">
                                        <label class="ph-label">From date</label>
                                        <input class="ph-input" type="date" name="date_from" value="{{ request('date_from') }}">
                                    </div>
                                    <div class="ph-field">
                                        <label class="ph-label">To date</label>
                                        <input class="ph-input" type="date" name="date_to" value="{{ request('date_to') }}">
                                    </div>
                                </div>
                                <div class="ph-filter-foot">
                                    <button type="submit" class="ph-btn-primary">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35"/></svg>
                                        Apply filters
                                    </button>
                                    <a href="{{ route('dashboard.payment-history.index') }}" class="ph-btn-ghost">Clear</a>
                                </div>
                            </form>
                        </div>

                        {{-- ── Transactions table ── --}}
                        <div class="ph-card">
                            <div class="ph-card__hd">
                                <div>
                                    <h2 class="ph-card__title">Transactions<span class="ph-card__bar"></span></h2>
                                    <p class="ph-card__count">{{ $payments->total() }} {{ $payments->total() === 1 ? 'record' : 'records' }} found</p>
                                </div>
                            </div>

                            @if($payments->count() > 0)
                            <div class="ph-table-shell">
                                <table class="ph-table">
                                    <thead>
                                        <tr>
                                            <th class="ph-th">Reference</th>
                                            <th class="ph-th">Amount</th>
                                            <th class="ph-th">Status</th>
                                            <th class="ph-th">Type</th>
                                            <th class="ph-th">Date</th>
                                            <th class="ph-th ph-th--actions">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $payment)
                                        <tr class="ph-tr">
                                            <td class="ph-td">
                                                <span class="ph-ref">{{ $payment->transaction_reference }}</span>
                                                @if($payment->invoice_number)
                                                    <span class="ph-ref-sub">Invoice: {{ $payment->invoice_number }}</span>
                                                @endif
                                            </td>
                                            <td class="ph-td">
                                                <span class="ph-amount">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                                            </td>
                                            <td class="ph-td">
                                                @if($payment->status == 'completed')
                                                    <span class="ph-badge ph-badge--green">Completed</span>
                                                @elseif($payment->status == 'pending')
                                                    <span class="ph-badge ph-badge--amber">Pending</span>
                                                @elseif($payment->status == 'failed')
                                                    <span class="ph-badge ph-badge--red">Failed</span>
                                                @else
                                                    <span class="ph-badge">{{ ucfirst($payment->status) }}</span>
                                                @endif
                                            </td>
                                            <td class="ph-td ph-td--type">{{ ucfirst(str_replace('_', ' ', $payment->transaction_type)) }}</td>
                                            <td class="ph-td">
                                                <span class="ph-date">{{ $payment->created_at->format('d M Y') }}</span>
                                                <span class="ph-time">{{ $payment->created_at->format('g:i A') }}</span>
                                            </td>
                                            <td class="ph-td ph-td--actions">
                                                @if($payment->status == 'completed')
                                                    <a href="{{ route('dashboard.payment-history.view-invoice', $payment->id) }}" class="ph-action ph-action--view" target="_blank">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                                        View
                                                    </a>
                                                    <a href="{{ route('dashboard.payment-history.download-invoice', $payment->id) }}" class="ph-action ph-action--dl">
                                                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                                        Download
                                                    </a>
                                                @else
                                                    <span class="ph-na">—</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            {{-- Pagination --}}
                            <div class="ph-pagination">
                                {{ $payments->links() }}
                            </div>

                            @else
                            <div class="ph-empty">
                                <div class="ph-empty__icon">
                                    <svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>
                                </div>
                                <p class="ph-empty__text">No transactions found for the selected filters.</p>
                            </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

.ph-wrap, .ph-wrap * { font-family: 'Outfit', sans-serif !important; box-sizing: border-box; }
.ph-wrap { max-width: 1000px; padding: 4px 0 60px; }

/* ── Page header ── */
.ph-page-header { margin-bottom: 24px; padding-bottom: 24px; border-bottom: 1.5px solid #ebebeb; }
.ph-page-title {
    font-size: 2rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.045em;
    line-height: 1.1; margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ph-title-bar { display: block; width: 2.4rem; height: 3.5px; background: #0c0c0c; border-radius: 3px; margin-top: 9px; }
.ph-page-sub { margin: 14px 0 0; font-size: 0.9rem; color: #8a8fa0; font-weight: 400; }

/* ── Stat cards ── */
.ph-stats {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
    margin-bottom: 20px;
}
.ph-stat {
    background: #fff; border: 1.5px solid #ebebeb; border-radius: 14px;
    padding: 16px 18px; display: flex; flex-direction: column; gap: 8px;
}
.ph-stat--wide { grid-column: span 2; }
.ph-stat__label { font-size: 0.78rem; font-weight: 600; color: #b0b5c0; letter-spacing: .04em; text-transform: uppercase; }
.ph-stat__val   { font-size: 1.7rem; font-weight: 800; color: #0c0c0c; letter-spacing: -0.04em; line-height: 1; }
.ph-stat__val--green { color: #16a34a; }
.ph-stat__val--amber { color: #d97706; }
.ph-stat__val--red   { color: #dc2626; }

/* ── Filter card ── */
.ph-filter-card {
    background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px;
    overflow: hidden; margin-bottom: 18px;
}
.ph-filter-card__hd { padding: 16px 22px 12px; border-bottom: 1.5px solid #f5f5f5; }
.ph-filter-title {
    font-size: 0.9rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em;
    margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ph-filter-bar { display: block; width: 1.6rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.ph-filter-form { padding: 18px 22px 20px; }
.ph-filter-grid { display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 14px; margin-bottom: 16px; }
.ph-filter-foot { display: flex; align-items: center; gap: 10px; }

/* ── Labels & inputs ── */
.ph-label { display: block; font-size: 0.78rem; font-weight: 600; color: #6b7280; margin-bottom: 6px; letter-spacing: .01em; }
.ph-input {
    display: block; width: 100%; padding: 9px 12px;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 9px;
    font-size: 0.85rem; color: #111827; outline: none; transition: border-color .15s, box-shadow .15s;
}
.ph-input:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.07); }
.ph-sel-wrap { position: relative; }
.ph-select {
    display: block; width: 100%; padding: 9px 36px 9px 12px; appearance: none;
    background: #fff; border: 1.5px solid #e5e7eb; border-radius: 9px;
    font-size: 0.85rem; color: #111827; cursor: pointer; outline: none;
    transition: border-color .15s, box-shadow .15s;
}
.ph-select:focus { border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.07); }
.ph-sel-arrow { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: #b0b5c0; }

/* ── Buttons ── */
.ph-btn-primary {
    display: inline-flex; align-items: center; gap: 7px; padding: 9px 18px;
    background: #0c0c0c; color: #fff; border: none; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all .15s;
    font-family: 'Outfit', sans-serif !important;
}
.ph-btn-primary:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(12,12,12,.18); }
.ph-btn-ghost {
    display: inline-flex; align-items: center; padding: 9px 16px;
    background: none; border: 1.5px solid #e5e7eb; border-radius: 10px;
    font-size: 0.85rem; font-weight: 600; color: #6b7280; cursor: pointer;
    text-decoration: none; transition: all .15s;
}
.ph-btn-ghost:hover { border-color: #d1d5db; color: #374151; background: #f9fafb; text-decoration: none; }

/* ── Table card ── */
.ph-card { background: #fff; border: 1.5px solid #ebebeb; border-radius: 16px; overflow: hidden; }
.ph-card__hd { padding: 18px 24px 14px; border-bottom: 1.5px solid #f5f5f5; }
.ph-card__title {
    font-size: 0.95rem; font-weight: 700; color: #0c0c0c; letter-spacing: -0.02em;
    margin: 0 0 4px; display: inline-flex; flex-direction: column;
}
.ph-card__bar { display: block; width: 1.7rem; height: 2.5px; background: #0c0c0c; border-radius: 2px; margin-top: 6px; }
.ph-card__count { margin: 8px 0 0; font-size: 0.8rem; color: #b0b5c0; }

/* ── Table ── */
.ph-table-shell { overflow-x: auto; }
.ph-table { width: 100%; border-collapse: collapse; }
.ph-th {
    padding: 10px 18px; text-align: left; font-size: 0.72rem; font-weight: 700;
    color: #b0b5c0; letter-spacing: .07em; text-transform: uppercase;
    background: #fafafa; border-bottom: 1.5px solid #f0f0f0;
}
.ph-th--actions { text-align: right; }
.ph-tr { border-bottom: 1.5px solid #f5f5f5; transition: background .1s; }
.ph-tr:last-child { border-bottom: none; }
.ph-tr:hover { background: #fafafa; }
.ph-td { padding: 13px 18px; font-size: 0.87rem; color: #374151; vertical-align: middle; }
.ph-td--type { color: #6b7280; font-size: 0.82rem; }
.ph-td--actions { text-align: right; white-space: nowrap; }

/* ── Cell content ── */
.ph-ref { display: block; font-weight: 600; color: #111827; font-size: 0.85rem; }
.ph-ref-sub { display: block; font-size: 0.78rem; color: #b0b5c0; margin-top: 2px; }
.ph-amount { font-weight: 700; color: #111827; }
.ph-date { display: block; font-weight: 500; color: #374151; }
.ph-time { display: block; font-size: 0.78rem; color: #b0b5c0; margin-top: 2px; }
.ph-na { color: #d1d5db; font-size: 1.1rem; }

/* ── Status badges ── */
.ph-badge {
    display: inline-flex; align-items: center; padding: 4px 10px;
    border-radius: 20px; font-size: 0.75rem; font-weight: 600; border: 1.5px solid transparent;
}
.ph-badge--green { background: #f0fdf4; border-color: #bbf7d0; color: #166534; }
.ph-badge--amber { background: #fffbeb; border-color: #fde68a; color: #92400e; }
.ph-badge--red   { background: #fef2f2; border-color: #fecaca; color: #991b1b; }

/* ── Action buttons ── */
.ph-action {
    display: inline-flex; align-items: center; gap: 5px; padding: 5px 11px;
    border-radius: 8px; font-size: 0.79rem; font-weight: 600; cursor: pointer;
    border: 1.5px solid transparent; transition: all .15s; text-decoration: none;
    background: none; font-family: 'Outfit', sans-serif !important; vertical-align: middle;
}
.ph-action--view { color: #374151; border-color: #e5e7eb; }
.ph-action--view:hover { background: #f3f4f6; border-color: #d1d5db; color: #111827; text-decoration: none; }
.ph-action--dl   { color: #166534; border-color: #bbf7d0; margin-left: 5px; }
.ph-action--dl:hover { background: #f0fdf4; border-color: #86efac; text-decoration: none; }

/* ── Pagination ── */
.ph-pagination { padding: 16px 20px; border-top: 1.5px solid #f5f5f5; }

/* ── Empty ── */
.ph-empty { padding: 52px 24px; text-align: center; }
.ph-empty__icon { display: inline-flex; padding: 18px; background: #f9fafb; border: 1.5px solid #ebebeb; border-radius: 16px; color: #d1d5db; margin-bottom: 16px; }
.ph-empty__text { font-size: 0.9rem; color: #9ca3af; }

/* ── Dark mode ── */
.is_dark .ph-page-title  { color: #f3f4f6; }
.is_dark .ph-title-bar   { background: #f3f4f6; }
.is_dark .ph-page-sub    { color: #6b7280; }
.is_dark .ph-page-header { border-color: #1e2330; }
.is_dark .ph-stat        { background: #111827; border-color: #1e2330; }
.is_dark .ph-stat__val   { color: #f3f4f6; }
.is_dark .ph-filter-card { background: #111827; border-color: #1e2330; }
.is_dark .ph-filter-card__hd { border-color: #1e2330; }
.is_dark .ph-filter-title { color: #f3f4f6; }
.is_dark .ph-filter-bar  { background: #f3f4f6; }
.is_dark .ph-input, .is_dark .ph-select { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .ph-input:focus, .is_dark .ph-select:focus { border-color: #f3f4f6; }
.is_dark .ph-btn-primary { background: #f3f4f6; color: #0c0c0c; }
.is_dark .ph-btn-ghost   { border-color: #374151; color: #9ca3af; }
.is_dark .ph-btn-ghost:hover { border-color: #4b5563; color: #d1d5db; background: #1f2937; }
.is_dark .ph-card        { background: #111827; border-color: #1e2330; }
.is_dark .ph-card__hd    { border-color: #1e2330; }
.is_dark .ph-card__title { color: #f3f4f6; }
.is_dark .ph-card__bar   { background: #f3f4f6; }
.is_dark .ph-card__count { color: #6b7280; }
.is_dark .ph-th  { background: #0f172a; border-color: #1e2330; color: #6b7280; }
.is_dark .ph-tr  { border-color: #1e2330; }
.is_dark .ph-tr:hover { background: #0f172a; }
.is_dark .ph-td  { color: #d1d5db; }
.is_dark .ph-ref { color: #f3f4f6; }
.is_dark .ph-amount { color: #f3f4f6; }
.is_dark .ph-date { color: #d1d5db; }
.is_dark .ph-action--view { color: #d1d5db; border-color: #374151; }
.is_dark .ph-action--view:hover { background: #1f2937; border-color: #4b5563; color: #f3f4f6; }
.is_dark .ph-pagination { border-color: #1e2330; }
.is_dark .ph-empty__icon { background: #0f172a; border-color: #1e2330; }

/* ── Responsive ── */
@media (max-width: 768px) {
    .ph-stats { grid-template-columns: 1fr 1fr; }
    .ph-stat--wide { grid-column: span 2; }
    .ph-filter-grid { grid-template-columns: 1fr 1fr; }
}
@media (max-width: 480px) {
    .ph-stats { grid-template-columns: 1fr; }
    .ph-stat--wide { grid-column: span 1; }
    .ph-filter-grid { grid-template-columns: 1fr; }
}
</style>

@endsection
