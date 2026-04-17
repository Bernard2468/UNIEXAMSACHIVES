@extends('super-admin.layout')

@section('title', 'Financial Report')

@push('styles')
<style>
    body { font-family: system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
    .page-container { max-width: 1100px; margin: 0 auto; padding: 0 1.5rem; }
    .page-header-modern { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem; }
    .page-header-title  { font-size: 1.875rem; font-weight: 700; color: #1f2937; margin: 0; }
    .page-header-separator { width: 1px; height: 2rem; background: #d1d5db; }
    .page-header-breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #6b7280; margin: 0; }
    .page-header-description { margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem; }

    /* Period filter */
    .period-bar {
        background: #fff; border: 1px solid #e5e7eb; border-radius: 0.75rem;
        padding: 1.25rem 1.5rem; margin-bottom: 1.5rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .period-bar form { display: flex; align-items: flex-end; gap: 1rem; flex-wrap: wrap; }
    .period-bar label { font-size: 0.8rem; font-weight: 600; color: #374151; display: block; margin-bottom: 4px; }
    .period-bar input, .period-bar select {
        border: 1px solid #d1d5db; border-radius: 0.5rem;
        padding: 0.5rem 0.75rem; font-size: 0.875rem; outline: none;
    }
    .period-bar input:focus, .period-bar select:focus { border-color: #01b2ac; box-shadow: 0 0 0 2px rgba(1,178,172,.1); }

    /* Stats row */
    .stats-row { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin-bottom: 1.5rem; }
    .stat-card {
        background: #fff; border-radius: 0.75rem; padding: 1.25rem 1.5rem;
        border: 1px solid #e5e7eb; box-shadow: 0 1px 3px rgba(0,0,0,.06);
    }
    .stat-label { font-size: 0.8rem; color: #6b7280; font-weight: 600; margin-bottom: 6px; }
    .stat-value { font-size: 1.875rem; font-weight: 800; color: #1f2937; }
    .stat-value.green { color: #059669; }
    .stat-sub   { font-size: 0.75rem; color: #9ca3af; margin-top: 4px; }

    /* Cards */
    .modern-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.08); border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem; overflow: hidden;
    }
    .modern-card-header {
        background: #f9fafb; padding: 1rem 1.5rem; border-bottom: 1px solid #e5e7eb;
    }
    .modern-card-header h5 {
        margin: 0; font-weight: 600; font-size: 1rem;
        display: flex; align-items: center; gap: 0.625rem; color: #1f2937;
    }
    .modern-card-body { padding: 0; }

    /* Tables */
    .report-table { width: 100%; border-collapse: collapse; }
    .report-table thead tr { background: #f9fafb; }
    .report-table th {
        padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem;
        font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .04em;
        border-bottom: 1px solid #e5e7eb;
    }
    .report-table th.right { text-align: right; }
    .report-table td {
        padding: 0.875rem 1rem; font-size: 0.875rem; color: #1f2937;
        border-bottom: 1px solid #f9fafb;
    }
    .report-table td.right { text-align: right; }
    .report-table tbody tr:hover { background: #fafafa; }
    .report-table tbody tr:last-child td { border-bottom: none; }

    /* Progress bar */
    .bar-track { background: #f3f4f6; border-radius: 4px; height: 6px; margin-top: 6px; }
    .bar-fill  { background: #01b2ac; border-radius: 4px; height: 6px; }

    /* Two col grid */
    .two-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; }

    /* Empty */
    .empty-state { text-align: center; padding: 3rem 1rem; color: #9ca3af; }
    .empty-state i { font-size: 2.5rem; margin-bottom: 0.75rem; }

    /* Btn */
    .btn-report {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.875rem;
        font-weight: 600; cursor: pointer; border: none; transition: all .2s; text-decoration: none;
    }
    .btn-report-primary  { background: #01b2ac; color: #fff; }
    .btn-report-primary:hover { background: #019e99; color: #fff; }
    .btn-report-outline { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .btn-report-outline:hover { background: #f9fafb; }

    @media (max-width: 768px) {
        .stats-row { grid-template-columns: 1fr; }
        .two-grid  { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="page-container">

    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Financial Report</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <a href="{{ route('super-admin.payments.index') }}" style="color:#6b7280;text-decoration:none">Payments</a>
                    <span>/</span><span>Report</span>
                </div>
            </div>
            <p class="page-header-description">
                Revenue summary from
                <strong>{{ \Carbon\Carbon::parse($startDate)->format('M d, Y') }}</strong>
                to
                <strong>{{ \Carbon\Carbon::parse($endDate)->format('M d, Y') }}</strong>
            </p>
        </div>
    </div>

    {{-- Period Filter --}}
    <div class="period-bar">
        <form method="GET" action="{{ route('super-admin.payments.report') }}">
            <div>
                <label>Period</label>
                <select name="period">
                    <option value="daily"   {{ $period == 'daily'   ? 'selected' : '' }}>Daily (Last 30 days)</option>
                    <option value="weekly"  {{ $period == 'weekly'  ? 'selected' : '' }}>Weekly (Last 12 weeks)</option>
                    <option value="monthly" {{ $period == 'monthly' ? 'selected' : '' }}>Monthly (Last 12 months)</option>
                    <option value="yearly"  {{ $period == 'yearly'  ? 'selected' : '' }}>Yearly (Last 5 years)</option>
                </select>
            </div>
            <div>
                <label>From Date</label>
                <input type="date" name="start_date" value="{{ \Carbon\Carbon::parse($startDate)->format('Y-m-d') }}">
            </div>
            <div>
                <label>To Date</label>
                <input type="date" name="end_date" value="{{ \Carbon\Carbon::parse($endDate)->format('Y-m-d') }}">
            </div>
            <div>
                <button type="submit" class="btn-report btn-report-primary">
                    <i class="icofont-filter"></i> Apply
                </button>
            </div>
            <div>
                <a href="{{ route('super-admin.payments.export') }}" class="btn-report btn-report-outline">
                    <i class="icofont-download"></i> Export CSV
                </a>
            </div>
        </form>
    </div>

    {{-- Summary Stats --}}
    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-label">Total Transactions</div>
            <div class="stat-value">{{ number_format($report['total_transactions']) }}</div>
            <div class="stat-sub">Completed payments in period</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value green">GHS {{ number_format($report['total_revenue'], 2) }}</div>
            <div class="stat-sub">Gross revenue from completed transactions</div>
        </div>
        <div class="stat-card">
            <div class="stat-label">Avg Transaction Value</div>
            <div class="stat-value">GHS {{ number_format($report['average_transaction'] ?? 0, 2) }}</div>
            <div class="stat-sub">Per completed transaction</div>
        </div>
    </div>

    <div class="two-grid">

        {{-- Revenue by Type --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5><i class="icofont-chart-bar-graph"></i> Revenue by Transaction Type</h5>
            </div>
            <div class="modern-card-body">
                @if($report['by_type']->isNotEmpty())
                @php $maxType = $report['by_type']->max('total'); @endphp
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Type</th>
                            <th class="right">Count</th>
                            <th class="right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['by_type'] as $row)
                        <tr>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $row->transaction_type)) }}
                                @if($maxType > 0)
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:{{ round(($row->total / $maxType) * 100) }}%"></div>
                                </div>
                                @endif
                            </td>
                            <td class="right">{{ number_format($row->count) }}</td>
                            <td class="right"><strong>GHS {{ number_format($row->total, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="empty-state">
                    <i class="icofont-inbox"></i>
                    <p>No transaction data for this period.</p>
                </div>
                @endif
            </div>
        </div>

        {{-- Revenue by Method --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5><i class="icofont-credit-card"></i> Revenue by Payment Method</h5>
            </div>
            <div class="modern-card-body">
                @if($report['by_method']->isNotEmpty())
                @php $maxMethod = $report['by_method']->max('total'); @endphp
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>Method</th>
                            <th class="right">Count</th>
                            <th class="right">Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($report['by_method'] as $row)
                        <tr>
                            <td>
                                {{ ucfirst(str_replace('_', ' ', $row->payment_method ?? 'Unknown')) }}
                                @if($maxMethod > 0)
                                <div class="bar-track">
                                    <div class="bar-fill" style="width:{{ round(($row->total / $maxMethod) * 100) }}%"></div>
                                </div>
                                @endif
                            </td>
                            <td class="right">{{ number_format($row->count) }}</td>
                            <td class="right"><strong>GHS {{ number_format($row->total, 2) }}</strong></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @else
                <div class="empty-state">
                    <i class="icofont-inbox"></i>
                    <p>No payment method data for this period.</p>
                </div>
                @endif
            </div>
        </div>

    </div>

    {{-- Day-by-Day Breakdown --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5><i class="icofont-calendar"></i> Daily Breakdown</h5>
        </div>
        <div class="modern-card-body">
            @if($report['by_day']->isNotEmpty())
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="right">Transactions</th>
                        <th class="right">Revenue</th>
                        <th style="width:200px">Volume</th>
                    </tr>
                </thead>
                <tbody>
                    @php $maxDay = $report['by_day']->max('total'); @endphp
                    @foreach($report['by_day'] as $row)
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($row->date)->format('M d, Y') }}</td>
                        <td class="right">{{ number_format($row->count) }}</td>
                        <td class="right"><strong>GHS {{ number_format($row->total, 2) }}</strong></td>
                        <td>
                            <div class="bar-track">
                                <div class="bar-fill" style="width:{{ $maxDay > 0 ? round(($row->total / $maxDay) * 100) : 0 }}%"></div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-state">
                <i class="icofont-inbox"></i>
                <p>No daily data available for this period.</p>
            </div>
            @endif
        </div>
    </div>

</div>
@endsection
