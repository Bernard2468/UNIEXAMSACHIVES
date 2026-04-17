<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\SystemSubscription;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentHistoryController extends Controller
{
    /**
     * Display payment history for the current institution's subscription
     */
    public function index(Request $request)
    {
        // Get the current subscription (there's only one subscription per system)
        $subscription = SystemSubscription::latest()->first();

        if (!$subscription) {
            return view('dashboard.payment-history.index', [
                'payments' => collect([]),
                'subscription' => null,
                'stats' => [
                    'total' => 0,
                    'completed' => 0,
                    'pending' => 0,
                    'failed' => 0,
                    'total_revenue' => 0,
                ]
            ]);
        }

        // Get payments for this subscription
        $query = PaymentTransaction::where('subscription_id', $subscription->id)
            ->with(['subscription', 'user']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by transaction type
        if ($request->has('type') && $request->type !== 'all') {
            $query->where('transaction_type', $request->type);
        }

        // Filter by date range
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('transaction_reference', 'like', "%{$request->search}%")
                  ->orWhere('gateway_reference', 'like', "%{$request->search}%")
                  ->orWhere('invoice_number', 'like', "%{$request->search}%")
                  ->orWhere('customer_email', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $payments = $query->paginate(20);

        // Statistics for this subscription only
        $stats = [
            'total' => PaymentTransaction::where('subscription_id', $subscription->id)->count(),
            'completed' => PaymentTransaction::where('subscription_id', $subscription->id)->completed()->count(),
            'pending' => PaymentTransaction::where('subscription_id', $subscription->id)->pending()->count(),
            'failed' => PaymentTransaction::where('subscription_id', $subscription->id)->failed()->count(),
            'total_revenue' => PaymentTransaction::where('subscription_id', $subscription->id)->completed()->sum('amount'),
        ];

        return view('dashboard.payment-history.index', compact('payments', 'subscription', 'stats'));
    }

    /**
     * Download invoice as PDF
     */
    public function downloadInvoice(int $id)
    {
        $payment = PaymentTransaction::with(['subscription', 'user'])->findOrFail($id);

        // Verify this payment belongs to the current subscription
        $subscription = SystemSubscription::latest()->first();
        if (!$subscription || $payment->subscription_id !== $subscription->id) {
            return back()->with('error', 'Payment not found.');
        }

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Invoice is only available for completed payments.');
        }

        // Generate invoice number if not exists
        if (!$payment->invoice_number) {
            $payment->generateInvoiceNumber();
        }

        $pdf = Pdf::loadView('dashboard.payment-history.invoice-pdf', compact('payment'))
            ->setPaper('a4', 'portrait')
            ->setOption('enable-local-file-access', true)
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true);

        return $pdf->download("invoice-{$payment->invoice_number}.pdf");
    }

    /**
     * View invoice (HTML preview)
     */
    public function viewInvoice(int $id)
    {
        $payment = PaymentTransaction::with(['subscription', 'user'])->findOrFail($id);

        // Verify this payment belongs to the current subscription
        $subscription = SystemSubscription::latest()->first();
        if (!$subscription || $payment->subscription_id !== $subscription->id) {
            return back()->with('error', 'Payment not found.');
        }

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Invoice is only available for completed payments.');
        }

        if (!$payment->invoice_number) {
            $payment->generateInvoiceNumber();
        }

        return view('dashboard.payment-history.invoice', compact('payment'));
    }
}

