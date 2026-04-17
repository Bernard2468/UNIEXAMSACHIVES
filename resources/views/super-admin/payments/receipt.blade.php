@extends('super-admin.layout')

@section('title', 'Receipt — {{ $payment->invoice_number }}')

@push('styles')
<style>
    .page-container { max-width: 760px; margin: 0 auto; padding: 0 1.5rem; }

    .page-header-modern { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem; }
    .page-header-title  { font-size: 1.875rem; font-weight: 700; color: #1f2937; margin: 0; }
    .page-header-separator { width: 1px; height: 2rem; background: #d1d5db; }
    .page-header-breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #6b7280; margin: 0; }
    .page-header-description { margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem; }

    /* Receipt card */
    .receipt-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(0,0,0,.08);
        border: 1px solid #e5e7eb;
        overflow: hidden;
        margin-bottom: 1.5rem;
    }

    /* Top accent */
    .receipt-accent { height: 5px; background: #01b2ac; }

    .receipt-body { padding: 2rem 2.5rem; }

    /* Header row */
    .receipt-header {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 2rem; padding-bottom: 1.5rem; border-bottom: 1px solid #f3f4f6;
    }
    .receipt-brand-name { font-size: 1.25rem; font-weight: 700; color: #01b2ac; }
    .receipt-brand-sub  { font-size: 0.8rem; color: #9ca3af; margin-top: 2px; }
    .receipt-title-block { text-align: right; }
    .receipt-title-block h2 { font-size: 1.5rem; font-weight: 800; color: #1f2937; margin: 0; letter-spacing: 2px; }
    .receipt-title-block p  { font-size: 0.8rem; color: #6b7280; margin: 4px 0 0; }

    /* Amount hero */
    .receipt-amount-hero {
        text-align: center; padding: 1.5rem; background: #f0fdfb;
        border-radius: 0.75rem; border: 1.5px solid #99f6e4;
        margin-bottom: 2rem;
    }
    .receipt-amount-label { font-size: 0.75rem; font-weight: 600; color: #6b7280; text-transform: uppercase; letter-spacing: .05em; }
    .receipt-amount-value { font-size: 2.5rem; font-weight: 800; color: #01b2ac; letter-spacing: -1px; margin: 6px 0; }
    .receipt-paid-badge {
        display: inline-flex; align-items: center; gap: 5px;
        background: #d1fae5; color: #065f46;
        padding: 4px 14px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: .05em;
    }

    /* Two column grid */
    .receipt-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 2rem; }

    .receipt-section-title {
        font-size: 0.7rem; font-weight: 700; color: #9ca3af;
        text-transform: uppercase; letter-spacing: .07em;
        margin-bottom: 0.875rem; padding-bottom: 0.5rem; border-bottom: 1px solid #f3f4f6;
    }

    .receipt-info-row { display: flex; flex-direction: column; gap: 2px; margin-bottom: 0.75rem; }
    .receipt-info-label { font-size: 0.75rem; color: #9ca3af; font-weight: 600; }
    .receipt-info-value { font-size: 0.875rem; color: #1f2937; font-weight: 500; word-break: break-all; }

    /* Line items table */
    .receipt-table { width: 100%; border-collapse: collapse; margin-bottom: 1.5rem; }
    .receipt-table thead tr { background: #f9fafb; }
    .receipt-table th {
        padding: 0.625rem 0.875rem; text-align: left; font-size: 0.75rem;
        font-weight: 700; color: #6b7280; text-transform: uppercase; letter-spacing: .04em;
        border-bottom: 1px solid #e5e7eb;
    }
    .receipt-table th:last-child { text-align: right; }
    .receipt-table td {
        padding: 0.875rem 0.875rem; font-size: 0.875rem; color: #1f2937;
        border-bottom: 1px solid #f9fafb;
    }
    .receipt-table td:last-child { text-align: right; font-weight: 600; }
    .receipt-table .total-row td { font-weight: 700; border-top: 2px solid #e5e7eb; border-bottom: none; font-size: 1rem; }
    .receipt-table .total-row td:last-child { color: #01b2ac; font-size: 1.125rem; }

    /* Footer */
    .receipt-footer {
        text-align: center; padding-top: 1.5rem; border-top: 1px solid #f3f4f6;
        font-size: 0.8rem; color: #9ca3af; line-height: 1.6;
    }

    /* Action buttons */
    .receipt-actions { display: flex; gap: 0.75rem; margin-bottom: 2rem; }
    .btn-receipt {
        display: inline-flex; align-items: center; gap: 6px;
        padding: 0.625rem 1.25rem; border-radius: 0.5rem;
        font-size: 0.875rem; font-weight: 600; cursor: pointer;
        text-decoration: none; transition: all .2s; border: none;
    }
    .btn-receipt-primary { background: #01b2ac; color: #fff; }
    .btn-receipt-primary:hover { background: #019e99; color: #fff; transform: translateY(-1px); }
    .btn-receipt-secondary { background: #fff; color: #374151; border: 1px solid #d1d5db; }
    .btn-receipt-secondary:hover { background: #f9fafb; color: #374151; }

    @media print {
        .receipt-actions, .sidebar, .top-nav, .main-content > *:not(.print-area) { display: none !important; }
        .main-content { margin: 0 !important; padding: 0 !important; }
        .receipt-card { box-shadow: none; border: none; }
    }
</style>
@endpush

@section('content')
<div class="page-container">

    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Receipt</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <a href="{{ route('super-admin.payments.index') }}" style="color:#6b7280;text-decoration:none">Payments</a>
                    <span>/</span>
                    <a href="{{ route('super-admin.payments.show', $payment->id) }}" style="color:#6b7280;text-decoration:none">{{ $payment->transaction_reference }}</a>
                    <span>/</span>
                    <span>Receipt</span>
                </div>
            </div>
            <p class="page-header-description">Payment receipt for invoice {{ $payment->invoice_number ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Action Buttons --}}
    <div class="receipt-actions">
        <a href="{{ route('super-admin.payments.receipt.download', $payment->id) }}" class="btn-receipt btn-receipt-primary">
            <i class="icofont-download"></i> Download PDF
        </a>
        <button onclick="window.print()" class="btn-receipt btn-receipt-secondary">
            <i class="icofont-printer"></i> Print
        </button>
        <a href="{{ route('super-admin.payments.show', $payment->id) }}" class="btn-receipt btn-receipt-secondary">
            <i class="icofont-arrow-left"></i> Back to Payment
        </a>
    </div>

    {{-- Receipt --}}
    <div class="receipt-card print-area">
        <div class="receipt-accent"></div>
        <div class="receipt-body">

            {{-- Header --}}
            <div class="receipt-header">
                <div>
                    <div class="receipt-brand-name">Metascholar Consult</div>
                    <div class="receipt-brand-sub">Central Control System</div>
                </div>
                <div class="receipt-title-block">
                    <h2>RECEIPT</h2>
                    <p>{{ $payment->invoice_number ?? $payment->transaction_reference }}</p>
                    <p>{{ $payment->paid_at ? $payment->paid_at->format('F d, Y') : $payment->created_at->format('F d, Y') }}</p>
                </div>
            </div>

            {{-- Amount Hero --}}
            <div class="receipt-amount-hero">
                <div class="receipt-amount-label">Total Amount Paid</div>
                <div class="receipt-amount-value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</div>
                <div><span class="receipt-paid-badge"><i class="fas fa-check" style="font-size:9px"></i> Paid</span></div>
            </div>

            {{-- Billed To & Payment Details --}}
            <div class="receipt-grid">
                <div>
                    <div class="receipt-section-title">Billed To</div>
                    <div class="receipt-info-row">
                        <span class="receipt-info-value" style="font-weight:700;font-size:1rem">
                            {{ $payment->customer_name ?? ($payment->subscription->institution_name ?? 'N/A') }}
                        </span>
                    </div>
                    @if($payment->subscription)
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Institution Code</span>
                        <span class="receipt-info-value">{{ $payment->subscription->institution_code }}</span>
                    </div>
                    @endif
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Email</span>
                        <span class="receipt-info-value">{{ $payment->customer_email ?? 'N/A' }}</span>
                    </div>
                    @if($payment->customer_phone)
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Phone</span>
                        <span class="receipt-info-value">{{ $payment->customer_phone }}</span>
                    </div>
                    @endif
                </div>
                <div>
                    <div class="receipt-section-title">Payment Details</div>
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Invoice Number</span>
                        <span class="receipt-info-value">{{ $payment->invoice_number ?? 'N/A' }}</span>
                    </div>
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Transaction Reference</span>
                        <span class="receipt-info-value" style="font-size:0.8rem">{{ $payment->transaction_reference }}</span>
                    </div>
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Payment Method</span>
                        <span class="receipt-info-value">{{ $payment->payment_method_display ?? 'N/A' }}</span>
                    </div>
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Gateway</span>
                        <span class="receipt-info-value">{{ ucfirst($payment->payment_gateway ?? 'N/A') }}</span>
                    </div>
                    <div class="receipt-info-row">
                        <span class="receipt-info-label">Payment Date</span>
                        <span class="receipt-info-value">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y h:i A') : 'N/A' }}</span>
                    </div>
                </div>
            </div>

            {{-- Line Items --}}
            <div class="receipt-section-title">Summary</div>
            <table class="receipt-table">
                <thead>
                    <tr>
                        <th style="width:45%">Description</th>
                        <th>Plan</th>
                        <th>Type</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ $payment->subscription->institution_name ?? 'Subscription Payment' }}</td>
                        <td>{{ $payment->subscription ? ucfirst($payment->subscription->subscription_plan) : 'N/A' }}</td>
                        <td>{{ ucfirst(str_replace('_', ' ', $payment->transaction_type)) }}</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                    @if($payment->discount_amount && $payment->discount_amount > 0)
                    <tr>
                        <td colspan="3" style="text-align:right;color:#6b7280">Discount ({{ $payment->discount_code }}):</td>
                        <td style="color:#059669">-{{ $payment->currency }} {{ number_format($payment->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr class="total-row">
                        <td colspan="3" style="text-align:right">Total Paid</td>
                        <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- Footer --}}
            <div class="receipt-footer">
                <p>This is an official receipt generated by Metascholar Consult Central Control System.</p>
                <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }} &nbsp;·&nbsp; For queries, contact your system administrator.</p>
            </div>

        </div>
    </div>

</div>
@endsection
