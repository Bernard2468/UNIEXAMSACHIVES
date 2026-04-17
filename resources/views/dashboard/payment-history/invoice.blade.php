<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - {{ $payment->invoice_number ?? $payment->transaction_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif;
            color: #000;
            background: #fff;
            padding: 2rem;
            line-height: 1.6;
        }

        .invoice-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
        }

        /* Header Section */
        .invoice-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .company-info {
            flex: 1;
        }

        .company-logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: #000;
        }

        .company-name {
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #000;
        }

        .company-details {
            font-size: 0.875rem;
            color: #4b5563;
            line-height: 1.8;
        }

        .vat-reg {
            font-weight: 700;
            color: #000;
        }

        .invoice-details {
            text-align: right;
        }

        .invoice-title {
            font-size: 2.5rem;
            font-weight: 700;
            font-family: Georgia, serif;
            margin-bottom: 1.5rem;
            color: #000;
        }

        .invoice-info {
            font-size: 0.875rem;
            line-height: 2;
            color: #4b5563;
        }

        .invoice-info strong {
            color: #000;
            font-weight: 700;
        }

        .payment-status {
            color: #059669;
            font-weight: 600;
            margin-top: 0.5rem;
            font-size: 1rem;
        }

        /* Billed To Section */
        .billed-to {
            margin-bottom: 2rem;
        }

        .billed-to-title {
            font-weight: 700;
            margin-bottom: 1rem;
            color: #000;
        }

        .billed-to-content {
            font-size: 0.875rem;
            line-height: 1.8;
            color: #4b5563;
        }

        /* Table Section */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .invoice-table thead {
            background: transparent;
        }

        .invoice-table th {
            padding: 0.75rem 0.5rem;
            text-align: left;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            color: #000;
            border-bottom: 1px solid #e5e7eb;
        }

        .invoice-table td {
            padding: 1rem 0.5rem;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.875rem;
            color: #1f2937;
        }

        .service-description {
            font-weight: 700;
            color: #000;
        }

        .service-dates {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: normal;
        }

        .amount-bold {
            font-weight: 700;
            color: #000;
        }

        /* Summary Section */
        .invoice-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 1rem;
        }

        .summary-content {
            width: 300px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            font-size: 0.875rem;
        }

        .summary-label {
            color: #4b5563;
        }

        .summary-value {
            color: #1f2937;
        }

        .summary-value.bold {
            font-weight: 700;
            color: #000;
        }

        .summary-value.negative {
            color: #1f2937;
        }

        .summary-total {
            border-top: 1px solid #e5e7eb;
            padding-top: 0.75rem;
            margin-top: 0.5rem;
        }

        @media print {
            body {
                padding: 0;
            }
            .invoice-container {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="invoice-header">
            <div class="company-info">
                <div class="company-logo">UNIEXAMS ARCHIVES</div>
                <div class="company-name">UniExams Archives Ltd.</div>
                <div class="company-details">
                    <div>Ghana</div>
                    <div class="vat-reg">VAT Reg #: N/A</div>
                </div>
            </div>
            <div class="invoice-details">
                <div class="invoice-title">INVOICE</div>
                <div class="invoice-info">
                    <div>Invoice # <strong>{{ $payment->invoice_number ?? $payment->transaction_reference }}</strong></div>
                    <div>Invoice Issued # <strong>{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : $payment->created_at->format('M d, Y') }}</strong></div>
                    <div>Invoice Amount # <strong>{{ $payment->currency }} {{ number_format($payment->amount, 2) }} ({{ $payment->currency }})</strong></div>
                    @if($payment->subscription)
                        <div>Next Billing Date # <strong>{{ $payment->subscription->subscription_end_date->format('M d, Y') }}</strong></div>
                    @endif
                    <div>Order Nr. # <strong>{{ $payment->transaction_reference }}</strong></div>
                </div>
                <div class="payment-status">PAID</div>
            </div>
        </div>

        <!-- Billed To -->
        <div class="billed-to">
            <div class="billed-to-title">BILLED TO</div>
            <div class="billed-to-content">
                <div>{{ $payment->customer_name ?? ($payment->subscription->institution_name ?? 'N/A') }}</div>
                @if($payment->subscription)
                    <div>{{ $payment->subscription->institution_code ?? '' }}</div>
                @endif
                <div>Ghana</div>
                <div>{{ $payment->customer_email ?? ($payment->user->email ?? 'N/A') }}</div>
                @if($payment->customer_phone)
                    <div>{{ $payment->customer_phone }}</div>
                @endif
            </div>
        </div>

        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Discount</th>
                    <th>Total Excl. VAT</th>
                    <th>VAT</th>
                    <th>Amount ({{ $payment->currency }})</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="service-description">
                            {{ ucfirst(str_replace('_', ' ', $payment->transaction_type)) }}
                            @if($payment->subscription)
                                (billed every {{ $payment->subscription->renewal_cycle == 'annual' ? 'year' : $payment->subscription->renewal_cycle }})
                            @endif
                        </div>
                        @if($payment->subscription)
                            <div class="service-dates">
                                {{ $payment->subscription->subscription_start_date->format('M d, Y') }} to {{ $payment->subscription->subscription_end_date->format('M d, Y') }}
                            </div>
                        @endif
                    </td>
                    <td>
                        @if($payment->original_amount && $payment->original_amount != $payment->amount)
                            {{ $payment->currency }} {{ number_format($payment->original_amount, 2) }} x 1
                        @else
                            {{ $payment->currency }} {{ number_format($payment->amount, 2) }} x 1
                        @endif
                    </td>
                    <td>
                        @if($payment->discount_amount && $payment->discount_amount > 0)
                            ({{ $payment->currency }} {{ number_format($payment->discount_amount, 2) }})
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                    <td>{{ $payment->currency }} 0.00</td>
                    <td class="amount-bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <div class="invoice-summary">
            <div class="summary-content">
                <div class="summary-row">
                    <span class="summary-label">Total excl. VAT:</span>
                    <span class="summary-value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Total:</span>
                    <span class="summary-value bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="summary-row">
                    <span class="summary-label">Payments:</span>
                    <span class="summary-value negative">({{ $payment->currency }} {{ number_format($payment->amount, 2) }})</span>
                </div>
                <div class="summary-row summary-total">
                    <span class="summary-label">Amount Due ({{ $payment->currency }}):</span>
                    <span class="summary-value bold">{{ $payment->currency }} 0.00</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

