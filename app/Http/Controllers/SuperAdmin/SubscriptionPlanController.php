<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class SubscriptionPlanController extends Controller
{
    public function index()
    {
        $plans = SubscriptionPlan::ordered()->get();

        return view('super-admin.subscription-plans.index', compact('plans'));
    }

    public function store(Request $request)
    {
        $validated = $this->validatePlan($request);

        SubscriptionPlan::create($validated);

        return redirect()
            ->route('super-admin.subscription-plans.index')
            ->with('success', 'Subscription plan "' . $validated['name'] . '" created successfully.');
    }

    public function update(Request $request, SubscriptionPlan $subscriptionPlan)
    {
        $validated = $this->validatePlan($request);

        $subscriptionPlan->update($validated);

        return redirect()
            ->route('super-admin.subscription-plans.index')
            ->with('success', 'Plan "' . $subscriptionPlan->name . '" updated successfully.');
    }

    public function destroy(SubscriptionPlan $subscriptionPlan)
    {
        $name = $subscriptionPlan->name;
        $subscriptionPlan->delete();

        return redirect()
            ->route('super-admin.subscription-plans.index')
            ->with('success', 'Plan "' . $name . '" deleted.');
    }

    public function toggleActive(int $id)
    {
        $plan = SubscriptionPlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);

        $status = $plan->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->route('super-admin.subscription-plans.index')
            ->with('success', 'Plan "' . $plan->name . '" ' . $status . '.');
    }

    private function validatePlan(Request $request): array
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'description'   => 'nullable|string|max:500',
            'price'         => 'required|numeric|min:0',
            'currency'      => 'required|string|size:3',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annual,annual,one_time',
            'features'      => 'nullable|array',
            'features.*'    => 'string|max:200',
            'display_order' => 'nullable|integer|min:0',
        ]);

        $data['is_active']     = $request->boolean('is_active');
        $data['is_featured']   = $request->boolean('is_featured');
        $data['display_order'] = $data['display_order'] ?? 0;
        $data['currency']      = strtoupper($data['currency']);

        // Strip empty feature strings
        $data['features'] = array_values(array_filter($data['features'] ?? [], fn($f) => trim($f) !== ''));

        if (empty($data['features'])) {
            $data['features'] = null;
        }

        return $data;
    }
}
