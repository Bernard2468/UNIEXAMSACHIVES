@extends('super-admin.layout')

@section('title', 'Subscription Plans')

@push('styles')
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800;900&family=DM+Sans:ital,opsz,wght@0,9..40,300;0,9..40,400;0,9..40,500;0,9..40,600;0,9..40,700;1,9..40,400&display=swap" rel="stylesheet">
<style>
    .sp-page { font-family: 'DM Sans', sans-serif; }

    /* ── PAGE HEADER ── */
    .sp-header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        margin-bottom: 28px;
        flex-wrap: wrap;
        gap: 16px;
    }
    .sp-header-text h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 26px;
        font-weight: 800;
        color: #0f172a;
        margin: 0 0 4px;
        letter-spacing: -0.6px;
    }
    .sp-header-text p {
        font-size: 13.5px;
        color: #64748b;
        margin: 0;
    }
    .btn-new-plan {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #01b2ac;
        color: #fff;
        border: none;
        padding: 11px 22px;
        border-radius: 10px;
        font-family: 'Outfit', sans-serif;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all .2s ease;
        white-space: nowrap;
        text-decoration: none;
    }
    .btn-new-plan:hover {
        background: #019e99;
        color: #fff;
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(1,178,172,.28);
    }

    /* ── STATS STRIP ── */
    .sp-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 14px;
        margin-bottom: 28px;
    }
    .sp-stat-card {
        background: #fff;
        border-radius: 12px;
        padding: 18px 22px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 1px 4px rgba(0,0,0,.05);
        display: flex;
        align-items: center;
        gap: 14px;
    }
    .sp-stat-icon {
        width: 44px; height: 44px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        font-size: 18px;
        flex-shrink: 0;
    }
    .sp-stat-icon.teal  { background: #e6faf9; color: #01b2ac; }
    .sp-stat-icon.green { background: #ecfdf5; color: #10b981; }
    .sp-stat-icon.amber { background: #fffbeb; color: #f59e0b; }
    .sp-stat-info h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 28px; font-weight: 800;
        color: #0f172a; margin: 0; line-height: 1;
    }
    .sp-stat-info p { font-size: 12.5px; color: #64748b; margin: 4px 0 0; }

    /* ── PLAN GRID ── */
    .sp-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 40px;
    }

    /* ── PLAN CARD ── */
    .plan-card {
        background: #fff;
        border-radius: 16px;
        border: 1.5px solid #f1f5f9;
        overflow: hidden;
        transition: all .2s ease;
        display: flex;
        flex-direction: column;
        position: relative;
    }
    .plan-card:hover {
        border-color: #01b2ac;
        box-shadow: 0 8px 32px rgba(1,178,172,.12);
        transform: translateY(-2px);
    }
    .plan-card.inactive { opacity: .62; }
    .plan-card.inactive:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 16px rgba(0,0,0,.06);
        transform: translateY(-1px);
        opacity: .72;
    }
    .plan-card-accent { height: 4px; background: #01b2ac; }
    .plan-card.inactive .plan-card-accent { background: #cbd5e1; }

    .plan-card-body {
        padding: 22px 24px;
        flex: 1;
        display: flex;
        flex-direction: column;
    }

    /* Badges */
    .plan-badges { display: flex; gap: 6px; margin-bottom: 12px; min-height: 22px; flex-wrap: wrap; }
    .badge-featured, .badge-active, .badge-inactive {
        display: inline-flex; align-items: center; gap: 4px;
        font-family: 'Outfit', sans-serif; font-size: 10.5px; font-weight: 700;
        padding: 3px 9px; border-radius: 20px;
        text-transform: uppercase; letter-spacing: .4px;
    }
    .badge-featured  { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }
    .badge-active    { background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0; }
    .badge-inactive  { background: #f1f5f9; color: #94a3b8; border: 1px solid #e2e8f0; }

    .plan-name {
        font-family: 'Outfit', sans-serif;
        font-size: 19px; font-weight: 700;
        color: #0f172a; margin-bottom: 5px; letter-spacing: -.3px;
    }
    .plan-description {
        font-size: 13px; color: #64748b;
        margin-bottom: 16px; line-height: 1.55; min-height: 36px;
    }

    /* Pricing */
    .plan-pricing { display: flex; align-items: baseline; gap: 3px; margin-bottom: 3px; }
    .plan-currency {
        font-family: 'Outfit', sans-serif;
        font-size: 15px; font-weight: 600; color: #475569; margin-top: 5px;
    }
    .plan-amount {
        font-family: 'Outfit', sans-serif;
        font-size: 42px; font-weight: 900;
        color: #0f172a; line-height: 1; letter-spacing: -2px;
    }
    .plan-cycle { font-size: 12.5px; color: #94a3b8; margin-bottom: 16px; }

    .plan-divider { height: 1px; background: #f1f5f9; margin: 14px 0; }

    /* Features */
    .plan-features { list-style: none; padding: 0; margin: 0 0 18px; flex: 1; }
    .plan-features li {
        display: flex; align-items: flex-start; gap: 9px;
        font-size: 13px; color: #374151;
        padding: 4px 0; line-height: 1.45;
    }
    .plan-features li .feat-icon { color: #01b2ac; font-size: 13px; margin-top: 1px; flex-shrink: 0; }
    .plan-features-empty { font-size: 12.5px; color: #9ca3af; font-style: italic; padding: 6px 0; }

    /* Actions */
    .plan-actions {
        display: flex; gap: 7px;
        padding-top: 14px; border-top: 1px solid #f1f5f9;
    }
    .btn-plan-edit {
        flex: 1; display: flex; align-items: center; justify-content: center; gap: 6px;
        background: #f8fafc; color: #374151;
        border: 1.5px solid #e2e8f0; padding: 8px 12px;
        border-radius: 8px; font-family: 'DM Sans', sans-serif;
        font-size: 13px; font-weight: 500; cursor: pointer;
        transition: all .2s ease; text-decoration: none;
    }
    .btn-plan-edit:hover { background: #f1f5f9; border-color: #94a3b8; color: #0f172a; }

    .btn-plan-icon {
        display: flex; align-items: center; justify-content: center;
        background: #f8fafc; color: #374151;
        border: 1.5px solid #e2e8f0; padding: 8px 11px;
        border-radius: 8px; font-size: 13px; cursor: pointer;
        transition: all .2s ease;
    }
    .btn-plan-icon:hover  { background: #f1f5f9; border-color: #94a3b8; }
    .btn-plan-icon.danger { background: #fff5f5; color: #ef4444; border-color: #fecaca; }
    .btn-plan-icon.danger:hover { background: #fee2e2; border-color: #f87171; }

    /* ── EMPTY STATE ── */
    .sp-empty {
        text-align: center; padding: 72px 40px;
        background: #fff; border-radius: 16px;
        border: 1.5px dashed #e2e8f0;
    }
    .sp-empty-icon { font-size: 46px; color: #cbd5e1; margin-bottom: 14px; }
    .sp-empty h3 {
        font-family: 'Outfit', sans-serif;
        font-size: 19px; font-weight: 700; color: #374151; margin-bottom: 6px;
    }
    .sp-empty p { font-size: 13.5px; color: #94a3b8; margin-bottom: 22px; }

    /* ── MODAL ── */
    .sp-modal .modal-content {
        border-radius: 16px; border: none;
        box-shadow: 0 20px 60px rgba(0,0,0,.14);
    }
    .sp-modal .modal-header {
        padding: 22px 26px 14px; border-bottom: 1px solid #f1f5f9;
    }
    .sp-modal .modal-title {
        font-family: 'Outfit', sans-serif;
        font-size: 19px; font-weight: 700; color: #0f172a;
    }
    .sp-modal .modal-body  { padding: 22px 26px; }
    .sp-modal .modal-footer {
        padding: 14px 26px 22px;
        border-top: 1px solid #f1f5f9; gap: 10px;
    }

    /* Form controls */
    .form-group { margin-bottom: 16px; }
    .form-label-sp {
        font-family: 'DM Sans', sans-serif;
        font-size: 12.5px; font-weight: 600;
        color: #374151; margin-bottom: 6px; display: block; letter-spacing: .2px;
    }
    .form-ctrl {
        width: 100%; padding: 10px 13px;
        border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-family: 'DM Sans', sans-serif; font-size: 13.5px;
        color: #0f172a; transition: border-color .2s ease;
        background: #fff; outline: none;
    }
    .form-ctrl:focus { border-color: #01b2ac; box-shadow: 0 0 0 3px rgba(1,178,172,.1); }
    .form-ctrl-select {
        appearance: none;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-repeat: no-repeat;
        background-position: right 11px center;
        background-size: 15px;
        padding-right: 36px;
    }
    .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-hint  { font-size: 11.5px; color: #94a3b8; margin-top: 4px; }
    .req        { color: #ef4444; }

    /* Toggle switch */
    .toggle-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 11px 15px; background: #f8fafc;
        border-radius: 10px; border: 1.5px solid #f1f5f9;
    }
    .toggle-label span { font-family: 'DM Sans',sans-serif; font-size: 13px; font-weight: 600; color: #374151; }
    .toggle-label small { display: block; font-size: 11.5px; color: #94a3b8; margin-top: 2px; }
    .toggle-switch { position: relative; width: 42px; height: 22px; flex-shrink: 0; }
    .toggle-switch input { opacity: 0; width: 0; height: 0; }
    .toggle-slider {
        position: absolute; inset: 0;
        background: #e2e8f0; border-radius: 22px; cursor: pointer; transition: all .2s;
    }
    .toggle-slider::before {
        content: ''; position: absolute;
        width: 16px; height: 16px; border-radius: 50%;
        background: #fff; top: 3px; left: 3px;
        transition: all .2s; box-shadow: 0 1px 3px rgba(0,0,0,.15);
    }
    .toggle-switch input:checked + .toggle-slider { background: #01b2ac; }
    .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }

    /* Features builder */
    .features-builder { border: 1.5px solid #e2e8f0; border-radius: 10px; overflow: hidden; }
    .features-list {
        padding: 10px; display: flex; flex-direction: column;
        gap: 7px; max-height: 190px; overflow-y: auto;
    }
    .features-list:empty::after {
        content: 'No features yet — click Add Feature below.';
        display: block; font-size: 12px; color: #94a3b8;
        font-style: italic; padding: 6px 4px;
    }
    .feature-item { display: flex; align-items: center; gap: 7px; }
    .feature-item input {
        flex: 1; padding: 7px 11px;
        border: 1.5px solid #e2e8f0; border-radius: 7px;
        font-family: 'DM Sans',sans-serif; font-size: 13px; outline: none;
        transition: border-color .2s;
    }
    .feature-item input:focus { border-color: #01b2ac; }
    .btn-rm-feat {
        width: 28px; height: 28px; border: none;
        background: #fee2e2; color: #ef4444; border-radius: 7px;
        cursor: pointer; display: flex; align-items: center; justify-content: center;
        font-size: 13px; flex-shrink: 0; transition: background .2s;
    }
    .btn-rm-feat:hover { background: #fecaca; }
    .btn-add-feat {
        display: flex; align-items: center; gap: 7px;
        width: 100%; padding: 9px 13px;
        background: #f8fafc; border: none; border-top: 1.5px solid #f1f5f9;
        font-family: 'DM Sans',sans-serif; font-size: 12.5px; color: #64748b;
        cursor: pointer; transition: background .2s;
    }
    .btn-add-feat:hover { background: #f1f5f9; color: #374151; }

    /* Submit / cancel buttons */
    .btn-sp-primary {
        background: #01b2ac; color: #fff; border: none;
        padding: 10px 22px; border-radius: 9px;
        font-family: 'Outfit',sans-serif; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all .2s;
    }
    .btn-sp-primary:hover { background: #019e99; color: #fff; transform: translateY(-1px); }
    .btn-sp-secondary {
        background: #fff; color: #374151;
        border: 1.5px solid #e2e8f0; padding: 10px 22px; border-radius: 9px;
        font-family: 'DM Sans',sans-serif; font-size: 14px; font-weight: 500;
        cursor: pointer; transition: all .2s;
    }
    .btn-sp-secondary:hover { background: #f8fafc; border-color: #94a3b8; }
    .btn-sp-danger {
        background: #ef4444; color: #fff; border: none;
        padding: 10px 22px; border-radius: 9px;
        font-family: 'Outfit',sans-serif; font-size: 14px; font-weight: 600;
        cursor: pointer; transition: all .2s;
    }
    .btn-sp-danger:hover { background: #dc2626; color: #fff; }

    /* Responsive */
    @media (max-width: 1140px) { .sp-grid { grid-template-columns: repeat(2,1fr); } }
    @media (max-width: 900px)  { .sp-stats { grid-template-columns: 1fr; } }
    @media (max-width: 768px)  {
        .sp-grid { grid-template-columns: 1fr; }
        .sp-header { flex-direction: column; }
        .form-row { grid-template-columns: 1fr; }
    }
</style>
@endpush

@section('content')
<div class="sp-page">

    {{-- ── PAGE HEADER ── --}}
    <div class="sp-header">
        <div class="sp-header-text">
            <h1>Subscription Plans</h1>
            <p>Define the plans institutions subscribe to — name, price, billing cycle, and features.</p>
        </div>
        <button class="btn-new-plan" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="fas fa-plus"></i> New Plan
        </button>
    </div>

    {{-- ── STATS ── --}}
    <div class="sp-stats">
        <div class="sp-stat-card">
            <div class="sp-stat-icon teal"><i class="fas fa-layer-group"></i></div>
            <div class="sp-stat-info">
                <h3>{{ $plans->count() }}</h3>
                <p>Total Plans</p>
            </div>
        </div>
        <div class="sp-stat-card">
            <div class="sp-stat-icon green"><i class="fas fa-check-circle"></i></div>
            <div class="sp-stat-info">
                <h3>{{ $plans->where('is_active', true)->count() }}</h3>
                <p>Active Plans</p>
            </div>
        </div>
        <div class="sp-stat-card">
            <div class="sp-stat-icon amber"><i class="fas fa-star"></i></div>
            <div class="sp-stat-info">
                <h3>{{ $plans->where('is_featured', true)->count() }}</h3>
                <p>Featured Plans</p>
            </div>
        </div>
    </div>

    {{-- ── PLANS GRID ── --}}
    @if($plans->isEmpty())
    <div class="sp-empty">
        <div class="sp-empty-icon"><i class="fas fa-layer-group"></i></div>
        <h3>No subscription plans yet</h3>
        <p>Create your first plan. Institutions will choose from these when subscribing.</p>
        <button class="btn-new-plan" data-bs-toggle="modal" data-bs-target="#createPlanModal">
            <i class="fas fa-plus"></i> Create First Plan
        </button>
    </div>
    @else
    <div class="sp-grid">
        @foreach($plans as $plan)
        <div class="plan-card {{ !$plan->is_active ? 'inactive' : '' }}">
            <div class="plan-card-accent"></div>
            <div class="plan-card-body">

                <div class="plan-badges">
                    @if($plan->is_featured)
                        <span class="badge-featured"><i class="fas fa-star" style="font-size:8px"></i> Popular</span>
                    @endif
                    @if($plan->is_active)
                        <span class="badge-active"><i class="fas fa-circle" style="font-size:6px"></i> Active</span>
                    @else
                        <span class="badge-inactive"><i class="fas fa-circle" style="font-size:6px"></i> Inactive</span>
                    @endif
                </div>

                <div class="plan-name">{{ $plan->name }}</div>
                <div class="plan-description">{{ $plan->description ?: 'No description provided.' }}</div>

                <div class="plan-pricing">
                    <span class="plan-currency">{{ $plan->currency }}</span>
                    <span class="plan-amount">{{ number_format($plan->price, 0) }}</span>
                </div>
                <div class="plan-cycle">{{ $plan->billing_cycle_label }}</div>

                <div class="plan-divider"></div>

                @if(!empty($plan->features) && count($plan->features) > 0)
                <ul class="plan-features">
                    @foreach($plan->features as $feature)
                    <li>
                        <span class="feat-icon"><i class="fas fa-check"></i></span>
                        <span>{{ $feature }}</span>
                    </li>
                    @endforeach
                </ul>
                @else
                <div class="plan-features-empty">No features listed.</div>
                @endif

                <div class="plan-actions">
                    <button class="btn-plan-edit"
                        data-id="{{ $plan->id }}"
                        data-name="{{ $plan->name }}"
                        data-description="{{ $plan->description }}"
                        data-price="{{ $plan->price }}"
                        data-currency="{{ $plan->currency }}"
                        data-billing_cycle="{{ $plan->billing_cycle }}"
                        data-is_active="{{ $plan->is_active ? '1' : '0' }}"
                        data-is_featured="{{ $plan->is_featured ? '1' : '0' }}"
                        data-display_order="{{ $plan->display_order }}"
                        data-features="{{ json_encode($plan->features ?? [], JSON_HEX_APOS | JSON_HEX_QUOT) }}"
                        onclick="openEditModal(this)">
                        <i class="fas fa-pen"></i> Edit
                    </button>

                    <form method="POST" action="{{ route('super-admin.subscription-plans.toggle-active', $plan->id) }}" style="display:inline">
                        @csrf
                        <button type="submit" class="btn-plan-icon" title="{{ $plan->is_active ? 'Deactivate plan' : 'Activate plan' }}">
                            <i class="fas {{ $plan->is_active ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                        </button>
                    </form>

                    <button type="button" class="btn-plan-icon danger"
                        title="Delete plan"
                        onclick="confirmDelete({{ $plan->id }}, '{{ addslashes($plan->name) }}')">
                        <i class="fas fa-trash"></i>
                    </button>
                </div>

            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>

{{-- ══════════════════════════════════════════
     CREATE PLAN MODAL
══════════════════════════════════════════ --}}
<div class="modal fade sp-modal" id="createPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('super-admin.subscription-plans.store') }}" id="createPlanForm">
                @csrf
                <div class="modal-body">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-sp">Plan Name <span class="req">*</span></label>
                            <input type="text" name="name" class="form-ctrl" placeholder="e.g. Professional" required maxlength="100" value="{{ old('name') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label-sp">Billing Cycle <span class="req">*</span></label>
                            <select name="billing_cycle" class="form-ctrl form-ctrl-select" required>
                                <option value="monthly"    {{ old('billing_cycle') == 'monthly'    ? 'selected' : '' }}>Monthly</option>
                                <option value="quarterly"  {{ old('billing_cycle') == 'quarterly'  ? 'selected' : '' }}>Quarterly</option>
                                <option value="semi_annual"{{ old('billing_cycle') == 'semi_annual'? 'selected' : '' }}>Semi-Annual</option>
                                <option value="annual"     {{ old('billing_cycle', 'annual') == 'annual' ? 'selected' : '' }}>Annual</option>
                                <option value="one_time"   {{ old('billing_cycle') == 'one_time'   ? 'selected' : '' }}>One-Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-sp">Description</label>
                        <textarea name="description" class="form-ctrl" rows="2" placeholder="Brief description of this plan…" maxlength="500">{{ old('description') }}</textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-sp">Price <span class="req">*</span></label>
                            <input type="number" name="price" class="form-ctrl" placeholder="0.00" required min="0" step="0.01" value="{{ old('price') }}">
                        </div>
                        <div class="form-group">
                            <label class="form-label-sp">Currency</label>
                            <input type="text" name="currency" class="form-ctrl" value="{{ old('currency', 'GHS') }}" maxlength="3" placeholder="GHS" style="text-transform:uppercase">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom:16px">
                        <div class="toggle-row">
                            <div class="toggle-label">
                                <span>Active</span>
                                <small>Make this plan available</small>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" value="1" checked>
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-label">
                                <span>Featured / Popular</span>
                                <small>Show "Popular" badge on card</small>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_featured" value="1">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-sp">Features</label>
                        <div class="features-builder">
                            <div class="features-list" id="createFeaturesList"></div>
                            <button type="button" class="btn-add-feat" onclick="addFeature('createFeaturesList')">
                                <i class="fas fa-plus" style="font-size:10px;color:#01b2ac"></i> Add Feature
                            </button>
                        </div>
                        <div class="form-hint">Each feature shows as a checkmark on the plan card.</div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-sp">Display Order</label>
                        <input type="number" name="display_order" class="form-ctrl" value="{{ old('display_order', 0) }}" min="0" style="max-width:120px">
                        <div class="form-hint">Lower number = appears first. Default is 0.</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sp-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-sp-primary">
                        <i class="fas fa-plus" style="font-size:11px;margin-right:6px"></i> Create Plan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     EDIT PLAN MODAL
══════════════════════════════════════════ --}}
<div class="modal fade sp-modal" id="editPlanModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Plan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" id="editPlanForm">
                @csrf
                @method('PUT')
                <div class="modal-body">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-sp">Plan Name <span class="req">*</span></label>
                            <input type="text" name="name" id="editName" class="form-ctrl" required maxlength="100">
                        </div>
                        <div class="form-group">
                            <label class="form-label-sp">Billing Cycle <span class="req">*</span></label>
                            <select name="billing_cycle" id="editBillingCycle" class="form-ctrl form-ctrl-select" required>
                                <option value="monthly">Monthly</option>
                                <option value="quarterly">Quarterly</option>
                                <option value="semi_annual">Semi-Annual</option>
                                <option value="annual">Annual</option>
                                <option value="one_time">One-Time</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-sp">Description</label>
                        <textarea name="description" id="editDescription" class="form-ctrl" rows="2" maxlength="500"></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label-sp">Price <span class="req">*</span></label>
                            <input type="number" name="price" id="editPrice" class="form-ctrl" required min="0" step="0.01">
                        </div>
                        <div class="form-group">
                            <label class="form-label-sp">Currency</label>
                            <input type="text" name="currency" id="editCurrency" class="form-ctrl" maxlength="3" style="text-transform:uppercase">
                        </div>
                    </div>

                    <div class="form-row" style="margin-bottom:16px">
                        <div class="toggle-row">
                            <div class="toggle-label">
                                <span>Active</span>
                                <small>Make this plan available</small>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_active" id="editIsActive" value="1">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                        <div class="toggle-row">
                            <div class="toggle-label">
                                <span>Featured / Popular</span>
                                <small>Show "Popular" badge on card</small>
                            </div>
                            <label class="toggle-switch">
                                <input type="checkbox" name="is_featured" id="editIsFeatured" value="1">
                                <span class="toggle-slider"></span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-sp">Features</label>
                        <div class="features-builder">
                            <div class="features-list" id="editFeaturesList"></div>
                            <button type="button" class="btn-add-feat" onclick="addFeature('editFeaturesList')">
                                <i class="fas fa-plus" style="font-size:10px;color:#01b2ac"></i> Add Feature
                            </button>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label-sp">Display Order</label>
                        <input type="number" name="display_order" id="editDisplayOrder" class="form-ctrl" min="0" style="max-width:120px">
                        <div class="form-hint">Lower number = appears first.</div>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-sp-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn-sp-primary">
                        <i class="fas fa-save" style="font-size:11px;margin-right:6px"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════
     DELETE CONFIRM MODAL
══════════════════════════════════════════ --}}
<div class="modal fade sp-modal" id="deleteModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:400px">
        <div class="modal-content">
            <div class="modal-header" style="border-bottom:1px solid #fee2e2">
                <h5 class="modal-title" style="color:#ef4444;font-family:'Outfit',sans-serif">
                    <i class="fas fa-exclamation-triangle" style="margin-right:8px"></i> Delete Plan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" style="padding:22px 26px">
                <p style="font-size:14px;color:#374151;margin:0;line-height:1.55">
                    You are about to permanently delete <strong id="deletePlanName"></strong>.
                    This cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-sp-secondary" data-bs-dismiss="modal">Cancel</button>
                <form id="deleteForm" method="POST">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-sp-danger">
                        <i class="fas fa-trash" style="font-size:11px;margin-right:5px"></i> Delete
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function addFeature(listId, value) {
    const list = document.getElementById(listId);
    const val  = value ?? '';
    const item = document.createElement('div');
    item.className = 'feature-item';
    item.innerHTML =
        `<input type="text" name="features[]" value="${val.replace(/"/g, '&quot;').replace(/'/g, '&#39;')}"
                placeholder="e.g. Unlimited exam uploads" maxlength="200">` +
        `<button type="button" class="btn-rm-feat" onclick="this.parentElement.remove()" title="Remove">` +
            `<i class="fas fa-times"></i></button>`;
    list.appendChild(item);
    if (!value) item.querySelector('input').focus();
}

function openEditModal(btn) {
    const d = btn.dataset;
    document.getElementById('editPlanForm').action =
        '{{ url("super-admin/subscription-plans") }}/' + d.id;
    document.getElementById('editName').value          = d.name;
    document.getElementById('editDescription').value   = d.description || '';
    document.getElementById('editPrice').value         = d.price;
    document.getElementById('editCurrency').value      = d.currency;
    document.getElementById('editBillingCycle').value  = d.billing_cycle;
    document.getElementById('editIsActive').checked    = d.is_active === '1';
    document.getElementById('editIsFeatured').checked  = d.is_featured === '1';
    document.getElementById('editDisplayOrder').value  = d.display_order;

    const featList = document.getElementById('editFeaturesList');
    featList.innerHTML = '';
    try {
        JSON.parse(d.features || '[]').forEach(f => addFeature('editFeaturesList', f));
    } catch (e) {}

    new bootstrap.Modal(document.getElementById('editPlanModal')).show();
}

function confirmDelete(id, name) {
    document.getElementById('deletePlanName').textContent = name;
    document.getElementById('deleteForm').action =
        '{{ url("super-admin/subscription-plans") }}/' + id;
    new bootstrap.Modal(document.getElementById('deleteModal')).show();
}
</script>
@endpush
