@extends('super-admin.layout')

@section('title', 'Payment Details')

@push('styles')
<style>
    /* System Font Stack */
    body {
        font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
    }

    /* Centered Container */
    .page-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 1.5rem;
    }

    /* Page Header Style */
    .page-header-modern {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 0.5rem;
    }

    .page-header-title {
        font-size: 1.875rem;
        font-weight: 700;
        color: #1f2937;
        margin: 0;
    }

    .page-header-separator {
        width: 1px;
        height: 2rem;
        background-color: #d1d5db;
        margin: 0;
    }

    .page-header-breadcrumb {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-size: 0.875rem;
        color: #6b7280;
        margin: 0;
    }

    .page-header-breadcrumb i {
        font-size: 1rem;
    }

    .page-header-description {
        margin-top: 0.5rem;
        color: #6b7280;
        font-size: 0.875rem;
    }

    /* Modern Card */
    .modern-card {
        background: white;
        border-radius: 1rem;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
        border: 1px solid #e5e7eb;
        margin-bottom: 1.5rem;
        overflow: hidden;
    }

    .modern-card-header {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        color: #1f2937;
        padding: 1.25rem 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .modern-card-header h5 {
        margin: 0;
        font-weight: 600;
        font-size: 1.125rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
    }

    .modern-card-body {
        padding: 1.5rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .info-item {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .info-label {
        font-size: 0.875rem;
        color: #6b7280;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .info-value {
        font-size: 1rem;
        color: #1f2937;
        font-weight: 500;
    }

    .info-value-large {
        font-size: 1.5rem;
        font-weight: 700;
        color: #059669;
    }

    /* Badge Styling */
    .badge-modern {
        display: inline-block;
        padding: 0.375rem 0.75rem;
        border-radius: 0.5rem;
        font-size: 0.75rem;
        font-weight: 600;
    }

    .badge-completed {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-processing {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-failed {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .badge-refunded {
        background: #e0e7ff;
        color: #3730a3;
    }

    /* Action Buttons */
    .btn-modern {
        padding: 0.75rem 1.5rem;
        border-radius: 0.5rem;
        font-size: 0.875rem;
        font-weight: 600;
        border: none;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
        display: inline-block;
        margin-right: 0.5rem;
        margin-bottom: 0.5rem;
    }

    .btn-modern-primary {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.1), rgba(129, 140, 248, 0.05));
        color: #4338ca;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }

    .btn-modern-primary:hover {
        background: linear-gradient(135deg, rgba(99, 102, 241, 0.15), rgba(129, 140, 248, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-success {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.1), rgba(5, 150, 105, 0.05));
        color: #059669;
        border: 1px solid rgba(16, 185, 129, 0.2);
    }

    .btn-modern-success:hover {
        background: linear-gradient(135deg, rgba(16, 185, 129, 0.15), rgba(5, 150, 105, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-danger {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.1), rgba(220, 38, 38, 0.05));
        color: #dc2626;
        border: 1px solid rgba(239, 68, 68, 0.2);
    }

    .btn-modern-danger:hover {
        background: linear-gradient(135deg, rgba(239, 68, 68, 0.15), rgba(220, 38, 38, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-warning {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.1), rgba(217, 119, 6, 0.05));
        color: #d97706;
        border: 1px solid rgba(245, 158, 11, 0.2);
    }

    .btn-modern-warning:hover {
        background: linear-gradient(135deg, rgba(245, 158, 11, 0.15), rgba(217, 119, 6, 0.1));
        transform: translateY(-1px);
    }

    .btn-modern-secondary {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.1), rgba(75, 85, 99, 0.05));
        color: #4b5563;
        border: 1px solid rgba(107, 114, 128, 0.2);
    }

    .btn-modern-secondary:hover {
        background: linear-gradient(135deg, rgba(107, 114, 128, 0.15), rgba(75, 85, 99, 0.1));
        transform: translateY(-1px);
    }

    /* Timeline */
    .timeline {
        position: relative;
        padding-left: 2rem;
    }

    .timeline::before {
        content: '';
        position: absolute;
        left: 0.5rem;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e5e7eb;
    }

    .timeline-item {
        position: relative;
        padding-bottom: 1.5rem;
    }

    .timeline-item::before {
        content: '';
        position: absolute;
        left: -1.5rem;
        top: 0.25rem;
        width: 0.75rem;
        height: 0.75rem;
        border-radius: 50%;
        background: #667eea;
        border: 2px solid white;
        box-shadow: 0 0 0 2px #667eea;
    }

    .timeline-content {
        background: #f9fafb;
        padding: 1rem;
        border-radius: 0.5rem;
        border: 1px solid #e5e7eb;
    }

    .timeline-title {
        font-weight: 600;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }

    .timeline-date {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .timeline-description {
        font-size: 0.875rem;
        color: #4b5563;
        margin-top: 0.5rem;
    }
</style>
@endpush

@section('content')
<div class="page-container">
    {{-- Header --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Payment Details</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <a href="{{ route('super-admin.payments.index') }}" style="color: #6b7280; text-decoration: none;">
                        <i class="icofont-credit-card"></i>
                        <span>Payments</span>
                    </a>
                    <span> / </span>
                    <span>View</span>
                </div>
            </div>
            <p class="page-header-description">Transaction Reference: {{ $payment->transaction_reference }}</p>
        </div>
    </div>

    {{-- Success/Error Messages --}}
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Payment Overview --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-info-circle"></i>
                Payment Overview
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Amount</span>
                    <span class="info-value-large">{{ $payment->currency }} {{ number_format($payment->amount, 2) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span>
                        @if($payment->status == 'completed')
                            <span class="badge-modern badge-completed">Completed</span>
                        @elseif($payment->status == 'pending')
                            <span class="badge-modern badge-pending">Pending</span>
                        @elseif($payment->status == 'processing')
                            <span class="badge-modern badge-processing">Processing</span>
                        @elseif($payment->status == 'failed')
                            <span class="badge-modern badge-failed">Failed</span>
                        @elseif($payment->status == 'cancelled')
                            <span class="badge-modern badge-cancelled">Cancelled</span>
                        @elseif($payment->status == 'refunded')
                            <span class="badge-modern badge-refunded">Refunded</span>
                        @else
                            <span class="badge-modern">{{ ucfirst($payment->status) }}</span>
                        @endif
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Transaction Type</span>
                    <span class="info-value">{{ ucfirst(str_replace('_', ' ', $payment->transaction_type)) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method</span>
                    <span class="info-value">{{ $payment->payment_method_display ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Invoice Number</span>
                    <span class="info-value">{{ $payment->invoice_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Transaction Reference</span>
                    <span class="info-value">{{ $payment->transaction_reference }}</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Subscription Information --}}
    @if($payment->subscription)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-building"></i>
                Subscription Information
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Institution Name</span>
                    <span class="info-value">{{ $payment->subscription->institution_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Institution Code</span>
                    <span class="info-value">{{ $payment->subscription->institution_code }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Subscription Plan</span>
                    <span class="info-value">{{ ucfirst($payment->subscription->subscription_plan) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Subscription Status</span>
                    <span class="info-value">{{ ucfirst($payment->subscription->status) }}</span>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('super-admin.subscriptions.show', $payment->subscription->id) }}" class="btn-modern btn-modern-primary">
                    View Subscription
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- Customer Information --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-user"></i>
                Customer Information
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Customer Name</span>
                    <span class="info-value">{{ $payment->customer_name ?? ($payment->user->name ?? 'N/A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Customer Email</span>
                    <span class="info-value">{{ $payment->customer_email ?? ($payment->user->email ?? 'N/A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Customer Phone</span>
                    <span class="info-value">{{ $payment->customer_phone ?? 'N/A' }}</span>
                </div>
                @if($payment->user)
                <div class="info-item">
                    <span class="info-label">Initiated By</span>
                    <span class="info-value">{{ $payment->user->name }} ({{ $payment->user->email }})</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Payment Details --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-list"></i>
                Payment Details
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Payment Gateway</span>
                    <span class="info-value">{{ ucfirst($payment->payment_gateway ?? 'N/A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Gateway Reference</span>
                    <span class="info-value">{{ $payment->gateway_reference ?? 'N/A' }}</span>
                </div>
                @if($payment->authorization_code)
                <div class="info-item">
                    <span class="info-label">Authorization Code</span>
                    <span class="info-value">{{ $payment->authorization_code }}</span>
                </div>
                @endif
                @if($payment->original_amount && $payment->original_amount != $payment->amount)
                <div class="info-item">
                    <span class="info-label">Original Amount</span>
                    <span class="info-value">{{ $payment->currency }} {{ number_format($payment->original_amount, 2) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Discount Amount</span>
                    <span class="info-value">{{ $payment->currency }} {{ number_format($payment->discount_amount ?? 0, 2) }}</span>
                </div>
                @endif
                <div class="info-item">
                    <span class="info-label">Auto Payment</span>
                    <span class="info-value">{{ $payment->is_auto_payment ? 'Yes' : 'No' }}</span>
                </div>
                @if($payment->retry_count > 0)
                <div class="info-item">
                    <span class="info-label">Retry Count</span>
                    <span class="info-value">{{ $payment->retry_count }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Transaction Timeline --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-clock-time"></i>
                Transaction Timeline
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="timeline">
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">Transaction Created</div>
                        <div class="timeline-date">{{ $payment->created_at->format('F d, Y h:i A') }}</div>
                    </div>
                </div>
                @if($payment->paid_at)
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">Payment Completed</div>
                        <div class="timeline-date">{{ $payment->paid_at->format('F d, Y h:i A') }}</div>
                    </div>
                </div>
                @endif
                @if($payment->refunded_at)
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">Payment Refunded</div>
                        <div class="timeline-date">{{ $payment->refunded_at->format('F d, Y h:i A') }}</div>
                    </div>
                </div>
                @endif
                @if($payment->last_retry_at)
                <div class="timeline-item">
                    <div class="timeline-content">
                        <div class="timeline-title">Last Retry Attempt</div>
                        <div class="timeline-date">{{ $payment->last_retry_at->format('F d, Y h:i A') }}</div>
                        <div class="timeline-description">Retry count: {{ $payment->retry_count }}</div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Failure Information --}}
    @if($payment->status == 'failed' && $payment->failure_reason)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-warning"></i>
                Failure Information
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-item">
                <span class="info-label">Failure Reason</span>
                <span class="info-value">{{ $payment->failure_reason }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Admin Notes --}}
    @if($payment->admin_notes)
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-note"></i>
                Admin Notes
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="info-item">
                <span class="info-value" style="white-space: pre-wrap;">{{ $payment->admin_notes }}</span>
            </div>
        </div>
    </div>
    @endif

    {{-- Actions --}}
    <div class="modern-card">
        <div class="modern-card-header">
            <h5>
                <i class="icofont-settings"></i>
                Actions
            </h5>
        </div>
        <div class="modern-card-body">
            <div class="d-flex flex-wrap">
                <a href="{{ route('super-admin.payments.index') }}" class="btn-modern btn-modern-secondary">
                    <i class="icofont-arrow-left"></i> Back to Payments
                </a>

                @if($payment->status == 'completed')
                    <a href="{{ route('super-admin.payments.receipt', $payment->id) }}" class="btn-modern btn-modern-success">
                        <i class="icofont-file-pdf"></i> View Receipt
                    </a>
                    <a href="{{ route('super-admin.payments.receipt.download', $payment->id) }}" class="btn-modern btn-modern-success">
                        <i class="icofont-download"></i> Download Receipt
                    </a>
                    @if($payment->canRefund())
                    <form action="{{ route('super-admin.payments.refund', $payment->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to refund this payment?');">
                        @csrf
                        <input type="hidden" name="reason" value="Admin refund">
                        <input type="hidden" name="confirm" value="1">
                        <button type="submit" class="btn-modern btn-modern-danger">
                            <i class="icofont-refresh"></i> Refund Payment
                        </button>
                    </form>
                    @endif
                @elseif($payment->status == 'failed' && $payment->canRetry())
                    <form action="{{ route('super-admin.payments.retry', $payment->id) }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn-modern btn-modern-warning">
                            <i class="icofont-refresh"></i> Retry Payment
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

