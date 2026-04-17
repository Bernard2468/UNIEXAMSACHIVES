<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Models\SystemSubscription;
use App\Services\SubscriptionManager;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    private SubscriptionManager $subscriptionManager;

    public function __construct()
    {
        $this->subscriptionManager = new SubscriptionManager();
    }

    /**
     * Display a listing of payments
     */
    public function index(Request $request)
    {
        $query = PaymentTransaction::with(['subscription', 'user']);

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

        // Statistics
        $stats = [
            'total' => PaymentTransaction::count(),
            'completed' => PaymentTransaction::completed()->count(),
            'pending' => PaymentTransaction::pending()->count(),
            'failed' => PaymentTransaction::failed()->count(),
            'total_revenue' => PaymentTransaction::completed()->sum('amount'),
            'monthly_revenue' => PaymentTransaction::completed()
                ->whereYear('paid_at', now()->year)
                ->whereMonth('paid_at', now()->month)
                ->sum('amount'),
        ];

        return view('super-admin.payments.index', compact('payments', 'stats'));
    }

    /**
     * Display the specified payment
     */
    public function show(int $id)
    {
        $payment = PaymentTransaction::with([
            'subscription',
            'user',
            'processor'
        ])->findOrFail($id);

        return view('super-admin.payments.show', compact('payment'));
    }

    /**
     * Handle payment callback from Paystack
     */
    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            // Redirect based on user role
            if (auth()->check() && auth()->user()->isSuperAdmin()) {
                return redirect()->route('super-admin.dashboard')
                    ->with('error', 'Invalid payment reference.');
            }
            return redirect()->route('dashboard')
                ->with('error', 'Invalid payment reference.');
        }

        $result = $this->subscriptionManager->verifyAndCompletePayment($reference);

        if ($result['success']) {
            // Redirect based on user role
            if (auth()->check() && auth()->user()->isSuperAdmin()) {
                return redirect()->route('super-admin.payments.show', $result['transaction']->id)
                    ->with('success', 'Payment completed successfully!');
            }
            // For regular admins or other users, redirect to dashboard with success message
            return redirect()->route('dashboard')
                ->with('success', 'Payment completed successfully!');
        }

        // Redirect based on user role for failed payments
        if (auth()->check() && auth()->user()->isSuperAdmin()) {
            return redirect()->route('super-admin.dashboard')
                ->with('error', $result['message'] ?? 'Payment verification failed.');
        }
        return redirect()->route('dashboard')
            ->with('error', $result['message'] ?? 'Payment verification failed.');
    }

    /**
     * Retry a failed payment
     */
    public function retry(int $id)
    {
        $payment = PaymentTransaction::findOrFail($id);

        if (!$payment->canRetry()) {
            return back()->with('error', 'This payment cannot be retried.');
        }

        $payment->retry();

        $callbackUrl = route('super-admin.payments.callback');
        
        $result = $this->subscriptionManager->initiateManualRenewal(
            $payment->subscription,
            auth()->id(),
            $callbackUrl
        );

        if ($result['success']) {
            return redirect()->away($result['payment_url']);
        }

        return back()->with('error', $result['message'] ?? 'Failed to retry payment.');
    }

    /**
     * Refund a payment
     */
    public function refund(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
            'confirm' => 'required|accepted',
        ]);

        $payment = PaymentTransaction::findOrFail($id);

        if (!$payment->canRefund()) {
            return back()->with('error', 'This payment cannot be refunded.');
        }

        $payment->refund($validated['reason']);

        return back()->with('success', 'Payment refunded successfully.');
    }

    /**
     * Download payment receipt as PDF
     */
    public function downloadReceipt(int $id)
    {
        $payment = PaymentTransaction::with(['subscription', 'user'])->findOrFail($id);

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Receipt is only available for completed payments.');
        }

        // Generate invoice number if not exists
        if (!$payment->invoice_number) {
            $payment->generateInvoiceNumber();
        }

        $pdf = Pdf::loadView('super-admin.payments.receipt-pdf', compact('payment'));

        return $pdf->download("receipt-{$payment->invoice_number}.pdf");
    }

    /**
     * View payment receipt (HTML preview)
     */
    public function viewReceipt(int $id)
    {
        $payment = PaymentTransaction::with(['subscription', 'user'])->findOrFail($id);

        if ($payment->status !== 'completed') {
            return back()->with('error', 'Receipt is only available for completed payments.');
        }

        if (!$payment->invoice_number) {
            $payment->generateInvoiceNumber();
        }

        return view('super-admin.payments.receipt', compact('payment'));
    }

    /**
     * Export payments data
     */
    public function export(Request $request)
    {
        $query = PaymentTransaction::with(['subscription', 'user']);

        // Apply same filters as index
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $payments = $query->get();

        $filename = 'payments_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Invoice Number',
                'Transaction Reference',
                'Institution',
                'Type',
                'Amount',
                'Currency',
                'Status',
                'Payment Method',
                'Customer Email',
                'Paid At',
                'Created At',
            ]);

            // Data rows
            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->id,
                    $payment->invoice_number,
                    $payment->transaction_reference,
                    $payment->subscription->institution_name ?? 'N/A',
                    $payment->transaction_type,
                    $payment->amount,
                    $payment->currency,
                    $payment->status,
                    $payment->payment_method_display,
                    $payment->customer_email,
                    $payment->paid_at ? $payment->paid_at->format('Y-m-d H:i:s') : 'N/A',
                    $payment->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Generate financial report
     */
    public function report(Request $request)
    {
        $period = $request->get('period', 'monthly'); // daily, weekly, monthly, yearly
        $startDate = $request->get('start_date', now()->startOfMonth());
        $endDate = $request->get('end_date', now()->endOfMonth());

        $query = PaymentTransaction::completed()
            ->whereBetween('paid_at', [$startDate, $endDate]);

        $report = [
            'total_transactions' => $query->count(),
            'total_revenue' => $query->sum('amount'),
            'average_transaction' => $query->avg('amount'),
            'by_type' => $query->clone()
                ->select('transaction_type', \DB::raw('count(*) as count'), \DB::raw('sum(amount) as total'))
                ->groupBy('transaction_type')
                ->get(),
            'by_method' => $query->clone()
                ->select('payment_method', \DB::raw('count(*) as count'), \DB::raw('sum(amount) as total'))
                ->whereNotNull('payment_method')
                ->groupBy('payment_method')
                ->get(),
            'by_day' => $query->clone()
                ->select(\DB::raw('DATE(paid_at) as date'), \DB::raw('count(*) as count'), \DB::raw('sum(amount) as total'))
                ->groupBy('date')
                ->orderBy('date')
                ->get(),
        ];

        return view('super-admin.payments.report', compact('report', 'period', 'startDate', 'endDate'));
    }
}

