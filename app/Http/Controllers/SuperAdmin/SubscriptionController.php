<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SystemSubscription;
use App\Models\SystemSetting;
use App\Services\SubscriptionManager;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    private SubscriptionManager $subscriptionManager;

    public function __construct()
    {
        $this->subscriptionManager = new SubscriptionManager();
    }

    /**
     * Display a listing of subscriptions
     */
    public function index(Request $request)
    {
        $query = SystemSubscription::with(['creator', 'updater']);

        // Filter by status
        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter by plan
        if ($request->has('plan') && $request->plan !== 'all') {
            $query->where('subscription_plan', $request->plan);
        }

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('institution_name', 'like', "%{$request->search}%")
                  ->orWhere('institution_code', 'like', "%{$request->search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $subscriptions = $query->paginate(20);

        // Statistics for filters
        $stats = [
            'total' => SystemSubscription::count(),
            'active' => SystemSubscription::active()->count(),
            'expiring_soon' => SystemSubscription::expiringSoon()->count(),
            'expired' => SystemSubscription::expired()->count(),
            'suspended' => SystemSubscription::where('status', 'suspended')->count(),
        ];

        // Get subscription pricing settings
        $basePrice = (float) SystemSetting::get('subscription_base_price', 5000.00);
        $currency = SystemSetting::get('default_currency', 'GHS');

        $pricing = [
            'base_price' => $basePrice,
            'currency' => $currency,
            '1_year' => $basePrice,
            '2_years' => $basePrice * 2,
            '3_years' => $basePrice * 3,
        ];

        return view('super-admin.subscriptions.index', compact('subscriptions', 'stats', 'pricing'));
    }

    /**
     * Show the form for creating a new subscription
     */
    public function create()
    {
        return view('super-admin.subscriptions.create');
    }

    /**
     * Store a newly created subscription
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_code' => 'nullable|string|max:100|unique:system_subscriptions,institution_code',
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'required|date|after:subscription_start_date',
            'renewal_cycle' => 'required|in:monthly,quarterly,semi_annual,annual',
            'renewal_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'hosting_package_type' => 'nullable|string|max:255',
            'auto_renewal' => 'boolean',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'admin_notes' => 'nullable|string',
        ]);

        // Generate institution code if not provided
        if (empty($validated['institution_code'])) {
            $validated['institution_code'] = Str::slug($validated['institution_name']);
        }

        $validated['created_by'] = auth()->id();
        $validated['auto_renewal'] = $request->has('auto_renewal');

        $subscription = $this->subscriptionManager->createSubscription($validated);

        return redirect()->route('super-admin.subscriptions.show', $subscription->id)
            ->with('success', 'Subscription created successfully.');
    }

    /**
     * Display the specified subscription
     */
    public function show(int $id)
    {
        $subscription = SystemSubscription::with([
            'creator',
            'updater',
            'transactions' => function($query) {
                $query->latest()->take(10);
            },
            'systemNotifications' => function($query) {
                $query->latest()->take(5);
            }
        ])->findOrFail($id);

        // Calculate statistics
        $stats = [
            'total_paid' => $subscription->getTotalRevenue(),
            'successful_payments' => $subscription->completedTransactions()->count(),
            'failed_payments' => $subscription->transactions()->where('status', 'failed')->count(),
            'pending_payments' => $subscription->pendingTransactions()->count(),
        ];

        return view('super-admin.subscriptions.show', compact('subscription', 'stats'));
    }

    /**
     * Show the form for editing the specified subscription
     */
    public function edit(int $id)
    {
        $subscription = SystemSubscription::findOrFail($id);
        return view('super-admin.subscriptions.edit', compact('subscription'));
    }

    /**
     * Update the specified subscription
     */
    public function update(Request $request, int $id)
    {
        $subscription = SystemSubscription::findOrFail($id);

        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'institution_code' => 'required|string|max:100|unique:system_subscriptions,institution_code,' . $id,
            'subscription_plan' => 'required|in:basic,standard,premium,enterprise',
            'subscription_start_date' => 'required|date',
            'subscription_end_date' => 'required|date|after:subscription_start_date',
            'renewal_cycle' => 'required|in:monthly,quarterly,semi_annual,annual',
            'renewal_amount' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'hosting_package_type' => 'nullable|string|max:255',
            'auto_renewal' => 'boolean',
            'grace_period_days' => 'required|integer|min:0|max:30',
            'admin_notes' => 'nullable|string',
        ]);

        $validated['updated_by'] = auth()->id();
        $validated['auto_renewal'] = $request->has('auto_renewal');

        $subscription->update($validated);

        return redirect()->route('super-admin.subscriptions.show', $subscription->id)
            ->with('success', 'Subscription updated successfully.');
    }

    /**
     * Initiate subscription renewal
     * 
     * Note: Allows super admins and regular admins (role='user' in database, displayed as "Admin" in UI)
     * See ROLE_TERMINOLOGY.md for role terminology documentation.
     */
    public function renew(Request $request, int $id)
    {
        // Allow super admins and regular admins (role='user' in database = "Admin" in UI) to renew
        $user = auth()->user();
        if (!$user->isSuperAdmin() && !$user->isRegularUser()) {
            abort(403, 'Unauthorized. Admin access required.');
        }

        $subscription = SystemSubscription::findOrFail($id);

        $callbackUrl = route('super-admin.payments.callback');
        
        $result = $this->subscriptionManager->initiateManualRenewal(
            $subscription,
            auth()->id(),
            $callbackUrl
        );

        if ($result['success']) {
            return redirect()->away($result['payment_url']);
        }

        return back()->with('error', $result['message'] ?? 'Failed to initiate renewal payment.');
    }

    /**
     * Suspend a subscription
     */
    public function suspend(Request $request, int $id)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $subscription = SystemSubscription::findOrFail($id);
        $subscription->suspend($validated['reason']);

        return back()->with('success', 'Subscription suspended successfully.');
    }

    /**
     * Reactivate a suspended subscription
     */
    public function reactivate(int $id)
    {
        $subscription = SystemSubscription::findOrFail($id);

        if ($subscription->status !== 'suspended' && $subscription->status !== 'expired') {
            return back()->with('warning', 'Only suspended or expired subscriptions can be reactivated.');
        }

        $subscription->reactivate();

        return back()->with('success', 'Subscription reactivated successfully.');
    }

    /**
     * Delete a subscription
     */
    public function destroy(Request $request, int $id)
    {
        $validated = $request->validate([
            'confirm' => 'required|accepted',
        ]);

        $subscription = SystemSubscription::findOrFail($id);

        // Prevent deletion of active subscriptions
        if ($subscription->status === 'active') {
            return back()->with('error', 'Cannot delete an active subscription. Please suspend it first.');
        }

        $institutionName = $subscription->institution_name;
        $subscription->delete();

        return redirect()->route('super-admin.subscriptions.index')
            ->with('success', "Subscription for {$institutionName} deleted successfully.");
    }

    /**
     * Export subscriptions data
     */
    public function export(Request $request)
    {
        $subscriptions = SystemSubscription::with(['creator', 'transactions'])->get();

        $filename = 'subscriptions_' . now()->format('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($subscriptions) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'ID',
                'Institution Name',
                'Institution Code',
                'Plan',
                'Status',
                'Start Date',
                'End Date',
                'Renewal Cycle',
                'Renewal Amount',
                'Currency',
                'Auto Renewal',
                'Total Revenue',
                'Created At',
            ]);

            // Data rows
            foreach ($subscriptions as $subscription) {
                fputcsv($file, [
                    $subscription->id,
                    $subscription->institution_name,
                    $subscription->institution_code,
                    $subscription->subscription_plan,
                    $subscription->status,
                    $subscription->subscription_start_date->format('Y-m-d'),
                    $subscription->subscription_end_date->format('Y-m-d'),
                    $subscription->renewal_cycle,
                    $subscription->renewal_amount,
                    $subscription->currency,
                    $subscription->auto_renewal ? 'Yes' : 'No',
                    $subscription->getTotalRevenue(),
                    $subscription->created_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Bulk update subscription statuses
     */
    public function bulkUpdateStatuses()
    {
        $results = $this->subscriptionManager->checkExpiringSubscriptions();

        return back()->with('success', "Status check completed. Updated: {$results['checked']} subscriptions.");
    }
}

