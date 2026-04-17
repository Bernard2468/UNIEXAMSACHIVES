<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Receipt — {{ $payment->invoice_number ?? $payment->transaction_reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 12px; color: #374151; background: #fff; }
        .page { padding: 36px 40px; }

        /* Header */
        .header-table { width: 100%; border-collapse: collapse; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 3px solid #01b2ac; }
        .brand-name { font-size: 20px; font-weight: bold; color: #01b2ac; }
        .brand-sub  { font-size: 10px; color: #9ca3af; margin-top: 3px; }
        .receipt-label { font-size: 22px; font-weight: bold; color: #1f2937; text-align: right; letter-spacing: 2px; }
        .receipt-meta  { font-size: 10px; color: #6b7280; text-align: right; margin-top: 4px; }

        /* Amount box */
        .amount-box {
            background: #f0fdfb; border: 2px solid #99f6e4;
            padding: 18px; text-align: center; margin: 20px 0; border-radius: 6px;
        }
        .amount-label { font-size: 10px; font-weight: bold; color: #6b7280; text-transform: uppercase; letter-spacing: 1px; }
        .amount-value { font-size: 30px; font-weight: bold; color: #01b2ac; margin: 6px 0 8px; }
        .paid-badge {
            display: inline-block; background: #d1fae5; color: #065f46;
            padding: 3px 12px; border-radius: 4px; font-size: 10px; font-weight: bold;
            text-transform: uppercase; letter-spacing: 1px;
        }

        /* Two column */
        .two-col { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .two-col td { vertical-align: top; padding: 0; width: 50%; }
        .col-right { padding-left: 24px !important; }

        .section-title {
            font-size: 9px; font-weight: bold; color: #9ca3af;
            text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb; padding-bottom: 5px; margin-bottom: 10px;
        }

        .info-row { margin-bottom: 7px; }
        .info-label { font-size: 9px; color: #9ca3af; font-weight: bold; text-transform: uppercase; }
        .info-value { font-size: 11px; color: #1f2937; font-weight: 500; }

        /* Line items */
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        .items-table th {
            background: #f9fafb; padding: 8px; text-align: left;
            font-size: 9px; font-weight: bold; color: #6b7280;
            text-transform: uppercase; letter-spacing: 1px;
            border-bottom: 1px solid #e5e7eb;
        }
        .items-table th.right { text-align: right; }
        .items-table td {
            padding: 9px 8px; font-size: 11px; color: #1f2937;
            border-bottom: 1px solid #f3f4f6;
        }
        .items-table td.right { text-align: right; font-weight: 600; }
        .items-table .total-row td {
            font-weight: bold; border-top: 2px solid #e5e7eb;
            border-bottom: none; font-size: 13px;
        }
        .items-table .total-row td.right { color: #01b2ac; font-size: 15px; }

        /* Footer */
        .footer {
            margin-top: 32px; padding-top: 14px; border-top: 1px solid #e5e7eb;
            font-size: 9px; color: #9ca3af; text-align: center; line-height: 1.7;
        }

        .divider { border: none; border-top: 1px solid #f3f4f6; margin: 16px 0; }
    </style>
</head>
<body>
<div class="page">

    {{-- Header --}}
    <table class="header-table">
        <tr>
            <td>
                <div class="brand-name">Metascholar Consult</div>
                <div class="brand-sub">Central Control System</div>
            </td>
            <td style="text-align:right; vertical-align:top">
                <div class="receipt-label">RECEIPT</div>
                <div class="receipt-meta">{{ $payment->invoice_number ?? $payment->transaction_reference }}</div>
                <div class="receipt-meta">{{ $payment->paid_at ? $payment->paid_at->format('F d, Y') : $payment->created_at->format('F d, Y') }}</div>
            </td>
        </tr>
    </table>

    {{-- Amount Hero --}}
    <div class="amount-box">
        <div class="amount-label">Total Amount Paid</div>
        <div class="amount-value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</div>
        <span class="paid-badge">✓ Paid</span>
    </div>

    {{-- Billed To & Payment Details --}}
    <table class="two-col">
        <tr>
            <td>
                <div class="section-title">Billed To</div>
                <div class="info-row">
                    <div class="info-value" style="font-size:13px;font-weight:bold">
                        {{ $payment->customer_name ?? ($payment->subscription->institution_name ?? 'N/A') }}
                    </div>
                </div>
                @if($payment->subscription)
                <div class="info-row">
                    <div class="info-label">Institution Code</div>
                    <div class="info-value">{{ $payment->subscription->institution_code }}</div>
                </div>
                @endif
                <div class="info-row">
                    <div class="info-label">Email</div>
                    <div class="info-value">{{ $payment->customer_email ?? 'N/A' }}</div>
                </div>
                @if($payment->customer_phone)
                <div class="info-row">
                    <div class="info-label">Phone</div>
                    <div class="info-value">{{ $payment->customer_phone }}</div>
                </div>
                @endif
            </td>
            <td class="col-right">
                <div class="section-title">Payment Details</div>
                <div class="info-row">
                    <div class="info-label">Invoice Number</div>
                    <div class="info-value">{{ $payment->invoice_number ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Transaction Reference</div>
                    <div class="info-value" style="font-size:10px">{{ $payment->transaction_reference }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Method</div>
                    <div class="info-value">{{ $payment->payment_method_display ?? 'N/A' }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Gateway</div>
                    <div class="info-value">{{ ucfirst($payment->payment_gateway ?? 'N/A') }}</div>
                </div>
                <div class="info-row">
                    <div class="info-label">Payment Date</div>
                    <div class="info-value">{{ $payment->paid_at ? $payment->paid_at->format('M d, Y') : 'N/A' }}</div>
                </div>
            </td>
        </tr>
    </table>

    <hr class="divider">

    {{-- Line Items --}}
    <div class="section-title">Summary</div>
    <table class="items-table">
        <thead>
            <tr>
                <th style="width:40%">Description</th>
                <th>Plan</th>
                <th>Type</th>
                <th class="right">Amount</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $payment->subscription->institution_name ?? 'Subscription Payment' }}</td>
                <td>{{ $payment->subscription ? ucfirst($payment->subscription->subscription_plan) : 'N/A' }}</td>
                <td>{{ ucfirst(str_replace('_', ' ', $payment->transaction_type)) }}</td>
                <td class="right">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
            </tr>
            @if($payment->discount_amount && $payment->discount_amount > 0)
            <tr>
                <td colspan="3" style="text-align:right;color:#6b7280">Discount:</td>
                <td class="right" style="color:#059669">-{{ $payment->currency }} {{ number_format($payment->discount_amount, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="3" style="text-align:right">Total Paid</td>
                <td class="right">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
            </tr>
        </tbody>
    </table>

    {{-- Footer --}}
    <div class="footer">
        <p>This is an official receipt generated by Metascholar Consult Central Control System.</p>
        <p>Generated on {{ now()->format('F d, Y \a\t h:i A') }} &nbsp;·&nbsp; For queries, contact your system administrator.</p>
    </div>

</div>
</body>
</html>
