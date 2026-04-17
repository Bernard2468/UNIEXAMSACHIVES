<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\SubscriptionPlan;
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
        $user         = auth()->user();
        $canSubscribe = $user && ($user->isRegularUser() || $user->isSuperAdmin());

        // Use the new SubscriptionPlan model if plans have been defined
        $plans = SubscriptionPlan::active()->ordered()->get();

        if ($plans->isNotEmpty()) {
            return view('subscription.locked', compact('plans', 'canSubscribe'));
        }

        // Fall back to old base-price system when no plans exist yet
        $basePrice = (float) SystemSetting::get('subscription_base_price', 5000.00);
        $currency  = SystemSetting::get('default_currency', 'GHS');

        $pricing = [
            '1' => ['years' => 1, 'name' => '1 Year',  'price' => $basePrice,      'description' => 'Annual subscription'],
            '2' => ['years' => 2, 'name' => '2 Years', 'price' => $basePrice * 2,  'description' => 'Two-year subscription'],
            '3' => ['years' => 3, 'name' => '3 Years', 'price' => $basePrice * 3,  'description' => 'Three-year subscription (Best Value)'],
        ];

        return view('subscription.locked', compact('pricing', 'currency', 'canSubscribe'));
    }

    /**
     * Handle subscription creation and payment initiation
     */
    public function subscribe(Request $request)
    {
        $user = auth()->user();
        if (!$user || (!$user->isRegularUser() && !$user->isSuperAdmin())) {
            return back()->with('error', 'Only administrators can create subscriptions.');
        }

        $request->validate(['institution_name' => 'required|string|max:255']);

        $startDate       = now()->startOfDay();
        $gracePeriodDays = SystemSetting::getGracePeriodDays();

        if ($request->filled('plan_id')) {
            // ── New plan-based path ──────────────────────────────────────────
            $request->validate(['plan_id' => 'required|integer|exists:subscription_plans,id']);

            $plan     = SubscriptionPlan::active()->findOrFail($request->plan_id);
            $amount   = $plan->price;
            $currency = $plan->currency;
            $planSlug = $plan->slug;

            $renewalCycle = in_array($plan->billing_cycle, ['monthly', 'quarterly', 'semi_annual', 'annual'])
                ? $plan->billing_cycle
                : 'annual';

            $endDate = match ($plan->billing_cycle) {
                'monthly'     => $startDate->copy()->addMonth(),
                'quarterly'   => $startDate->copy()->addMonths(3),
                'semi_annual' => $startDate->copy()->addMonths(6),
                default       => $startDate->copy()->addYear(),   // annual + one_time
            };
        } else {
            // ── Legacy year-based fallback (when no plans defined) ───────────
            $request->validate(['years' => 'required|integer|min:1|max:3']);

            $basePrice    = (float) SystemSetting::get('subscription_base_price', 5000.00);
            $currency     = SystemSetting::get('default_currency', 'GHS');
            $years        = (int) $request->years;
            $amount       = $basePrice * $years;
            $planSlug     = 'standard';
            $renewalCycle = 'annual';
            $endDate      = $startDate->copy()->addYears($years)->startOfDay();
        }

        $subscription = $this->subscriptionManager->createSubscription([
            'institution_name'        => $request->institution_name,
            'institution_code'        => Str::slug($request->institution_name),
            'subscription_plan'       => $planSlug,
            'subscription_start_date' => $startDate,
            'subscription_end_date'   => $endDate,
            'renewal_cycle'           => $renewalCycle,
            'renewal_amount'          => $amount,
            'currency'                => $currency,
            'auto_renewal'            => SystemSetting::getAutoRenewalEnabled(),
            'grace_period_days'       => $gracePeriodDays,
            'created_by'              => $user->id,
        ]);

        $result = $this->subscriptionManager->initiateManualRenewal(
            $subscription,
            $user->id,
            route('super-admin.payments.callback')
        );

        if ($result['success']) {
            return redirect()->away($result['payment_url']);
        }

        return back()->with('error', $result['message'] ?? 'Failed to initiate payment. Please try again.');
    }
}

