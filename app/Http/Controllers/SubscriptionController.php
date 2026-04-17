<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\SubscriptionManager;
use App\Services\PaystackService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SubscriptionController extends Controller
{
    private SubscriptionManager $subscriptionManager;
    private PaystackService $paystack;

    public function __construct()
    {
        $this->subscriptionManager = new SubscriptionManager();
        $this->paystack = new PaystackService();
    }

    /**
     * Show locked page when no subscription exists
     */
    public function locked()
    {
        // Check if user is admin (role='user') or super admin
        $user = auth()->user();
        $canSubscribe = $user && ($user->isRegularUser() || $user->isSuperAdmin());

        // Get subscription pricing from settings
        $basePrice = (float) SystemSetting::get('subscription_base_price', 5000.00);
        $currency = SystemSetting::get('default_currency', 'GHS');

        // Calculate prices for different year options
        $pricing = [
            '1' => [
                'years' => 1,
                'name' => '1 Year',
                'price' => $basePrice,
                'description' => 'Annual subscription',
            ],
            '2' => [
                'years' => 2,
                'name' => '2 Years',
                'price' => $basePrice * 2,
                'description' => 'Two-year subscription',
            ],
            '3' => [
                'years' => 3,
                'name' => '3 Years',
                'price' => $basePrice * 3,
                'description' => 'Three-year subscription (Best Value)',
            ],
        ];

        return view('subscription.locked', compact('pricing', 'currency', 'canSubscribe'));
    }

    /**
     * Handle subscription creation and payment initiation
     */
    public function subscribe(Request $request)
    {
        // Only admins (role='user') or super admins can subscribe
        $user = auth()->user();
        if (!$user || (!$user->isRegularUser() && !$user->isSuperAdmin())) {
            return back()->with('error', 'Only administrators can create subscriptions.');
        }

        $validated = $request->validate([
            'institution_name' => 'required|string|max:255',
            'years' => 'required|integer|min:1|max:3',
        ]);

        // Get subscription pricing from settings
        $basePrice = (float) SystemSetting::get('subscription_base_price', 5000.00);
        $currency = SystemSetting::get('default_currency', 'GHS');

        // Calculate amount based on number of years
        $years = (int) $validated['years'];
        $amount = $basePrice * $years;
        
        // Calculate dates: start today, end after selected number of years
        // Examples (if created on Nov 10, 2025):
        // - 1 year: starts Nov 10, 2025, ends Nov 10, 2026
        // - 2 years: starts Nov 10, 2025, ends Nov 10, 2027
        // - 3 years: starts Nov 10, 2025, ends Nov 10, 2028
        $startDate = now()->startOfDay(); // Today at 00:00:00
        // Add exactly the number of years selected (2 years = 2025 + 2 = 2027)
        $endDate = $startDate->copy()->addYears($years)->startOfDay(); // Add years, keep as date (not datetime)

        // Get grace period from settings
        $gracePeriodDays = SystemSetting::getGracePeriodDays();

        // Create subscription
        $subscription = $this->subscriptionManager->createSubscription([
            'institution_name' => $validated['institution_name'],
            'institution_code' => Str::slug($validated['institution_name']),
            'subscription_plan' => 'standard',
            'subscription_start_date' => $startDate,
            'subscription_end_date' => $endDate,
            'renewal_cycle' => 'annual', // Always annual
            'renewal_amount' => $amount,
            'currency' => $currency,
            'auto_renewal' => SystemSetting::getAutoRenewalEnabled(),
            'grace_period_days' => $gracePeriodDays,
            'created_by' => $user->id,
        ]);

        // Initiate payment
        $callbackUrl = route('super-admin.payments.callback');
        
        $result = $this->subscriptionManager->initiateManualRenewal(
            $subscription,
            $user->id,
            $callbackUrl
        );

        if ($result['success']) {
            return redirect()->away($result['payment_url']);
        }

        return back()->with('error', $result['message'] ?? 'Failed to initiate payment. Please try again.');
    }
}

