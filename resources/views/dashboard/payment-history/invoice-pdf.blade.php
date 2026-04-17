<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice - {{ $payment->invoice_number ?? $payment->transaction_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: #000;
            background: #fff;
            padding: 12mm;
            font-size: 10pt;
            line-height: 1.6;
        }

        .invoice-container {
            width: 100%;
            max-width: 100%;
            background: white;
            margin: 0;
            padding: 0;
        }

        /* Header Section - Using Table for Layout */
        .invoice-header {
            width: 100%;
            margin-bottom: 20pt;
            padding-bottom: 12pt;
            border-bottom: 1px solid #e5e7eb;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: top;
            padding: 0;
        }

        .company-info {
            width: 50%;
        }

        .company-logo {
            margin-bottom: 6pt;
        }

        .logo-image {
            max-width: 200pt;
            height: auto;
            display: block;
        }

        .company-name {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: normal;
            margin-bottom: 2pt;
            font-size: 7.5pt;
            color: #4b5563;
        }

        .company-details {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 7.5pt;
            color: #4b5563;
            line-height: 1.3;
        }

        .company-details div {
            margin-bottom: 2pt;
        }

        .vat-reg {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: normal;
            color: #4b5563;
        }

        .invoice-details {
            text-align: right;
            width: 50%;
        }

        .invoice-title {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 6pt;
            color: #000;
            letter-spacing: 0.5pt;
        }

        .invoice-info {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 7.5pt;
            line-height: 1.3;
            color: #4b5563;
        }

        .invoice-info div {
            margin-bottom: 2pt;
        }

        .invoice-info strong {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: #000;
            font-weight: bold;
        }

        .payment-status {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            color: #059669;
            font-weight: 600;
            margin-top: 4pt;
            font-size: 8pt;
        }

        /* Billed To Section */
        .billed-to {
            margin-bottom: 20pt;
        }

        .billed-to-title {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-weight: bold;
            margin-bottom: 6pt;
            color: #000;
            font-size: 8.5pt;
            letter-spacing: 0.3pt;
        }

        .billed-to-content {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            font-size: 8pt;
            line-height: 1.3;
            color: #4b5563;
        }

        .billed-to-content div {
            margin-bottom: 2pt;
        }

        /* Table Section - Inspired by shadcn/ui table structure */
        .table-wrapper {
            width: 100%;
            position: relative;
            margin-bottom: 20pt;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin: 0;
            padding: 0;
            table-layout: fixed;
            caption-side: bottom;
            font-size: 8.5pt;
        }

        .invoice-table thead {
            background: transparent;
        }

        .table-row-header {
            border-bottom: 1px solid #e5e7eb;
            transition: none;
        }

        .table-head {
            padding: 10pt 14pt;
            text-align: left;
            font-weight: bold;
            font-size: 8.5pt;
            text-transform: uppercase;
            color: #000;
            vertical-align: middle;
            letter-spacing: 0.2pt;
            white-space: nowrap;
        }

        .table-head:nth-child(1),
        .table-cell:nth-child(1) {
            width: 22%;
        }

        .table-head:nth-child(1) {
            padding-left: 0;
        }

        .table-head:nth-child(2),
        .table-cell:nth-child(2) {
            width: 10%;
        }

        .table-head:nth-child(3),
        .table-cell:nth-child(3) {
            width: 15%;
            padding-left: 44pt;
        }

        .table-head:nth-child(4),
        .table-cell:nth-child(4) {
            width: 19%;
            padding-left: 44pt;
        }

        .table-head:nth-child(5),
        .table-cell:nth-child(5) {
            width: 10%;
            padding-left: 44pt;
        }

        .table-head:nth-child(6),
        .table-cell:nth-child(6) {
            width: 24%;
            padding-left: 44pt;
        }

        .table-head:nth-child(6) {
            padding-right: 0;
        }

        .table-row {
            border-bottom: 1px solid #f3f4f6;
            transition: none;
        }

        .table-row:last-child {
            border-bottom: 0;
        }

        .table-cell {
            padding: 12pt 14pt;
            text-align: left;
            font-size: 7.5pt;
            color: #1f2937;
            vertical-align: top;
        }

        .table-cell:nth-child(1) {
            word-wrap: break-word;
            padding-left: 0;
        }

        .table-cell:nth-child(2),
        .table-cell:nth-child(3),
        .table-cell:nth-child(4),
        .table-cell:nth-child(5),
        .table-cell:nth-child(6) {
            white-space: nowrap;
        }

        .table-cell:nth-child(6) {
            padding-right: 0;
        }

        .service-description {
            font-weight: bold;
            color: #000;
            font-size: 8.5pt;
            margin-bottom: 4pt;
        }

        .service-dates {
            font-size: 7.5pt;
            color: #6b7280;
            font-weight: normal;
            margin-top: 4pt;
            line-height: 1.5;
        }

        .amount-bold {
            font-weight: bold;
            color: #000;
            font-size: 8.5pt;
        }

        /* Summary Section - Using Table for Layout */
        .invoice-summary {
            width: 100%;
            margin-top: 15pt;
        }

        .summary-table {
            width: 300pt;
            margin-left: auto;
            border-collapse: collapse;
        }

        .summary-table td {
            padding: 0.5pt 0;
            font-size: 7.5pt;
            vertical-align: top;
        }

        .summary-label {
            font-size: 8.5pt;
            color: #4b5563;
            text-align: left;
        }

        .summary-value {
            font-size: 7.5pt;
            color: #1f2937;
            text-align: right;
        }

        .summary-value.bold {
            font-weight: bold;
            color: #000;
        }

        .summary-value.negative {
            color: #1f2937;
        }

        .summary-total {
            border-top: 1px solid #e5e7eb;
            padding-top: 1pt;
            margin-top: 1pt;
        }

        .summary-total td {
            padding-top: 1pt;
        }

        /* PDF-specific optimizations */
        @page {
            margin: 12mm;
            size: A4 portrait;
        }

        /* Ensure proper rendering in PDF */
        .invoice-header {
            page-break-inside: avoid;
        }

        .invoice-table {
            page-break-inside: avoid;
        }

        .invoice-summary {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <table class="header-table invoice-header">
            <tr>
                <td class="company-info">
                    <div class="company-logo">
                        <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1759601411/a49b4ad9-f1b7-4474-b96a-9b2b7bb3784d_afqtge.png" alt="UniExams Archives" class="logo-image">
                    </div>
                    <div class="company-name">Metascholar Consult Limited</div>
                    <div class="company-details">
                        <div>P.O Box SY649, Sunyani</div>
                        <div>BS-0272-8085, Berekum Road</div>
                        <div>www.metascholar.academicdigital.space</div>
                        <div class="vat-reg">VAT Reg #: <strong>CY10301365E</strong></div>
                    </div>
                </td>
                <td class="invoice-details">
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
                </td>
            </tr>
        </table>

        <!-- Billed To -->
        <div class="billed-to">
            <div class="billed-to-title">BILLED TO</div>
            <div class="billed-to-content">
                <div>{{ $payment->customer_name ?? ($payment->subscription->institution_name ?? 'Catholic University of Ghana') }}</div>
                <div>P.O. Box 363, Fiapre</div>
                <div>Sunyani, Bono Region</div>
                <div>Ghana</div>
                <div>{{ $payment->customer_email ?? ($payment->user->email ?? 'N/A') }}</div>
                @if($payment->customer_phone)
                    <div>{{ $payment->customer_phone }}</div>
                @endif
            </div>
        </div>

        <!-- Invoice Table - Clean structure inspired by shadcn/ui -->
        <div class="table-wrapper">
            <table class="invoice-table">
                <thead class="table-header">
                    <tr class="table-row-header">
                        <th class="table-head">Description</th>
                        <th class="table-head">Price</th>
                        <th class="table-head">Discount</th>
                        <th class="table-head">Total Excl. VAT</th>
                        <th class="table-head">VAT</th>
                        <th class="table-head">Amount ({{ $payment->currency }})</th>
                    </tr>
                </thead>
                <tbody class="table-body">
                    <tr class="table-row">
                        <td class="table-cell">
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
                        <td class="table-cell">
                            @if($payment->original_amount && $payment->original_amount != $payment->amount)
                                {{ $payment->currency }} {{ number_format($payment->original_amount, 2) }} x 1
                            @else
                                {{ $payment->currency }} {{ number_format($payment->amount, 2) }} x 1
                            @endif
                        </td>
                        <td class="table-cell">
                            @if($payment->discount_amount && $payment->discount_amount > 0)
                                ({{ $payment->currency }} {{ number_format($payment->discount_amount, 2) }})
                            @else
                                -
                            @endif
                        </td>
                        <td class="table-cell">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                        <td class="table-cell">{{ $payment->currency }} 0.00</td>
                        <td class="table-cell amount-bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Summary -->
        <table class="summary-table invoice-summary">
            <tr>
                <td class="summary-label">Total excl. VAT:</td>
                <td class="summary-value">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Total:</td>
                <td class="summary-value bold">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</td>
            </tr>
            <tr>
                <td class="summary-label">Payments:</td>
                <td class="summary-value negative">({{ $payment->currency }} {{ number_format($payment->amount, 2) }})</td>
            </tr>
            <tr class="summary-total">
                <td class="summary-label">Amount Due ({{ $payment->currency }}):</td>
                <td class="summary-value bold">{{ $payment->currency }} 0.00</td>
            </tr>
        </table>
    </div>
</body>
</html>
