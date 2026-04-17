@extends('super-admin.layout')

@section('title', 'Edit Subscription')

@push('styles')
<style>
    .page-container { max-width: 860px; margin: 0 auto; padding: 0 1.5rem; }

    .page-header-modern { display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem; }
    .page-header-title  { font-size: 1.875rem; font-weight: 700; color: #1f2937; margin: 0; }
    .page-header-separator { width: 1px; height: 2rem; background: #d1d5db; }
    .page-header-breadcrumb { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; color: #6b7280; margin: 0; }
    .page-header-description { margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem; }

    .modern-card {
        background: #fff; border-radius: 1rem;
        box-shadow: 0 1px 3px rgba(0,0,0,.1), 0 1px 2px rgba(0,0,0,.06);
        border: 1px solid #e5e7eb; margin-bottom: 1.5rem; overflow: hidden;
    }
    .modern-card-header {
        background: #f9fafb; color: #1f2937;
        padding: 1.25rem 1.5rem; border-bottom: 1px solid #e5e7eb;
    }
    .modern-card-header h5 {
        margin: 0; font-weight: 600; font-size: 1rem;
        display: flex; align-items: center; gap: 0.625rem;
    }
    .modern-card-body { padding: 1.5rem; }

    .form-label { font-size: 0.875rem; font-weight: 600; color: #374151; margin-bottom: 0.375rem; }
    .form-control, .form-select {
        border-radius: 0.5rem; border: 1px solid #d1d5db;
        padding: 0.625rem 0.875rem; font-size: 0.875rem; transition: all .2s;
    }
    .form-control:focus, .form-select:focus {
        border-color: #01b2ac; box-shadow: 0 0 0 3px rgba(1,178,172,.12); outline: none;
    }
    .form-text { font-size: 0.8rem; color: #9ca3af; margin-top: 0.25rem; }

    .btn-submit {
        background: #01b2ac; color: #fff; border: none;
        padding: 0.625rem 1.5rem; border-radius: 0.5rem;
        font-size: 0.875rem; font-weight: 600; cursor: pointer; transition: all .2s;
    }
    .btn-submit:hover { background: #019e99; transform: translateY(-1px); }

    .btn-cancel {
        background: #fff; color: #374151;
        border: 1px solid #d1d5db; padding: 0.625rem 1.5rem;
        border-radius: 0.5rem; font-size: 0.875rem; font-weight: 500;
        text-decoration: none; display: inline-block; transition: all .2s;
    }
    .btn-cancel:hover { background: #f9fafb; color: #374151; }

    .invalid-feedback { font-size: 0.8rem; }
</style>
@endpush

@section('content')
<div class="page-container">

    <div class="row mb-4">
        <div class="col-12">
            <div class="page-header-modern">
                <h1 class="page-header-title">Edit Subscription</h1>
                <div class="page-header-separator"></div>
                <div class="page-header-breadcrumb">
                    <i class="icofont-tasks-alt"></i>
                    <a href="{{ route('super-admin.subscriptions.index') }}" style="color:#6b7280;text-decoration:none">Subscriptions</a>
                    <span>/</span>
                    <a href="{{ route('super-admin.subscriptions.show', $subscription->id) }}" style="color:#6b7280;text-decoration:none">{{ $subscription->institution_name }}</a>
                    <span>/</span>
                    <span>Edit</span>
                </div>
            </div>
            <p class="page-header-description">Update subscription details for <strong>{{ $subscription->institution_name }}</strong></p>
        </div>
    </div>

    <form method="POST" action="{{ route('super-admin.subscriptions.update', $subscription->id) }}" novalidate>
        @csrf
        @method('PUT')

        {{-- Institution Details --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5><i class="icofont-institution"></i> Institution Details</h5>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    <div class="col-md-7">
                        <label class="form-label">Institution Name <span class="text-danger">*</span></label>
                        <input type="text" name="institution_name"
                               class="form-control @error('institution_name') is-invalid @enderror"
                               value="{{ old('institution_name', $subscription->institution_name) }}" required>
                        @error('institution_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Institution Code <span class="text-danger">*</span></label>
                        <input type="text" name="institution_code"
                               class="form-control @error('institution_code') is-invalid @enderror"
                               value="{{ old('institution_code', $subscription->institution_code) }}" required>
                        @error('institution_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Hosting Package Type</label>
                        <input type="text" name="hosting_package_type"
                               class="form-control @error('hosting_package_type') is-invalid @enderror"
                               value="{{ old('hosting_package_type', $subscription->hosting_package_type) }}"
                               placeholder="e.g. Shared, Dedicated">
                        @error('hosting_package_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Plan & Dates --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5><i class="icofont-tasks-alt"></i> Subscription Plan & Dates</h5>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Subscription Plan <span class="text-danger">*</span></label>
                        <select name="subscription_plan" class="form-select @error('subscription_plan') is-invalid @enderror" required>
                            <option value="">Select plan…</option>
                            @if($plans->isNotEmpty())
                                @foreach($plans as $plan)
                                <option value="{{ $plan->slug }}"
                                    {{ old('subscription_plan', $subscription->subscription_plan) == $plan->slug ? 'selected' : '' }}>
                                    {{ $plan->name }} — {{ $plan->currency }} {{ number_format($plan->price, 0) }} {{ $plan->billing_cycle_label }}
                                </option>
                                @endforeach
                                {{-- Keep current value selectable even if plan was removed --}}
                                @if($plans->where('slug', $subscription->subscription_plan)->isEmpty())
                                <option value="{{ $subscription->subscription_plan }}" selected>
                                    {{ ucfirst($subscription->subscription_plan) }} (current)
                                </option>
                                @endif
                            @else
                                @foreach(['basic','standard','premium','enterprise'] as $plan)
                                <option value="{{ $plan }}"
                                    {{ old('subscription_plan', $subscription->subscription_plan) == $plan ? 'selected' : '' }}>
                                    {{ ucfirst($plan) }}
                                </option>
                                @endforeach
                            @endif
                        </select>
                        @error('subscription_plan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="date" name="subscription_start_date"
                               class="form-control @error('subscription_start_date') is-invalid @enderror"
                               value="{{ old('subscription_start_date', $subscription->subscription_start_date->format('Y-m-d')) }}" required>
                        @error('subscription_start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date <span class="text-danger">*</span></label>
                        <input type="date" name="subscription_end_date"
                               class="form-control @error('subscription_end_date') is-invalid @enderror"
                               value="{{ old('subscription_end_date', $subscription->subscription_end_date->format('Y-m-d')) }}" required>
                        @error('subscription_end_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>
        </div>

        {{-- Billing --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5><i class="icofont-credit-card"></i> Billing & Renewal</h5>
            </div>
            <div class="modern-card-body">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Renewal Amount <span class="text-danger">*</span></label>
                        <input type="number" name="renewal_amount"
                               class="form-control @error('renewal_amount') is-invalid @enderror"
                               value="{{ old('renewal_amount', $subscription->renewal_amount) }}" min="0" step="0.01" required>
                        @error('renewal_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Currency <span class="text-danger">*</span></label>
                        <input type="text" name="currency"
                               class="form-control @error('currency') is-invalid @enderror"
                               value="{{ old('currency', $subscription->currency) }}" maxlength="3" style="text-transform:uppercase" required>
                        @error('currency')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Renewal Cycle <span class="text-danger">*</span></label>
                        <select name="renewal_cycle" class="form-select @error('renewal_cycle') is-invalid @enderror" required>
                            @foreach(['monthly'=>'Monthly','quarterly'=>'Quarterly','semi_annual'=>'Semi-Annual','annual'=>'Annual'] as $val => $label)
                            <option value="{{ $val }}"
                                {{ old('renewal_cycle', $subscription->renewal_cycle) == $val ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                            @endforeach
                        </select>
                        @error('renewal_cycle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Grace Period (days) <span class="text-danger">*</span></label>
                        <input type="number" name="grace_period_days"
                               class="form-control @error('grace_period_days') is-invalid @enderror"
                               value="{{ old('grace_period_days', $subscription->grace_period_days) }}" min="0" max="30" required>
                        @error('grace_period_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end pb-1">
                        <div class="form-check">
                            <input type="checkbox" name="auto_renewal" id="auto_renewal"
                                   class="form-check-input" value="1"
                                   {{ old('auto_renewal', $subscription->auto_renewal) ? 'checked' : '' }}>
                            <label class="form-check-label form-label mb-0" for="auto_renewal">Auto-Renew</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Admin Notes --}}
        <div class="modern-card">
            <div class="modern-card-header">
                <h5><i class="icofont-notepad"></i> Admin Notes</h5>
            </div>
            <div class="modern-card-body">
                <textarea name="admin_notes" class="form-control @error('admin_notes') is-invalid @enderror"
                          rows="3" placeholder="Optional internal notes…">{{ old('admin_notes', $subscription->admin_notes) }}</textarea>
                @error('admin_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="d-flex gap-3 mb-5">
            <button type="submit" class="btn-submit">
                <i class="icofont-save me-1"></i> Save Changes
            </button>
            <a href="{{ route('super-admin.subscriptions.show', $subscription->id) }}" class="btn-cancel">Cancel</a>
        </div>

    </form>
</div>
@endsection
