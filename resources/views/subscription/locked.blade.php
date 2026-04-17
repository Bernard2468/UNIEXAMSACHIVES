<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Required - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/icofont.min.css') }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", "Noto Sans", "Liberation Sans", Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Noto Color Emoji";
            background: #f8f9fa;
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }

        .page-container {
            max-width: 900px;
            width: 100%;
        }

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

        .modern-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px 0 rgba(0, 0, 0, 0.06);
            border: 1px solid #e5e7eb;
            margin-bottom: 1.5rem;
            overflow: hidden;
        }

        .modern-card-header {
            background: #f9fafb;
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

        .plans-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .plan-card {
            background: white;
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 2px solid #e5e7eb;
            transition: all 0.2s ease;
            cursor: pointer;
            text-align: center;
        }

        .plan-card:hover {
            border-color: #01b2ac;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            transform: translateY(-2px);
        }

        .plan-card.selected {
            border-color: #01b2ac;
            background: #f0fdfa;
        }

        .plan-name {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        .plan-price {
            font-size: 1.75rem;
            font-weight: 700;
            color: #01b2ac;
            margin-bottom: 0.25rem;
        }

        .plan-cycle {
            font-size: 0.875rem;
            color: #6b7280;
        }

        .plan-badge {
            display: inline-block;
            background: #10b981;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            font-size: 0.75rem;
            font-weight: 600;
            margin-top: 0.5rem;
        }

        .subscribe-form {
            background: #f9fafb;
            border-radius: 0.75rem;
            padding: 1.5rem;
            border: 1px solid #e5e7eb;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
        }

        .form-control {
            border-radius: 0.5rem;
            border: 1px solid #d1d5db;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: #01b2ac;
            box-shadow: 0 0 0 3px rgba(1, 178, 172, 0.1);
            outline: none;
        }

        .btn-subscribe {
            background: #01b2ac;
            color: white;
            border: none;
            padding: 0.875rem 1.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            border-radius: 0.5rem;
            width: 100%;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 1rem;
        }

        .btn-subscribe:hover {
            background: #019a94;
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        }

        .btn-subscribe:disabled {
            opacity: 0.5;
            cursor: not-allowed;
            transform: none;
        }

        .info-text {
            text-align: center;
            color: #6b7280;
            font-size: 0.875rem;
            margin-top: 1rem;
        }

        .alert {
            border-radius: 0.5rem;
            border: 1px solid;
            padding: 1rem;
            margin-bottom: 1.5rem;
        }

        .alert-info {
            background: #dbeafe;
            border-color: #93c5fd;
            color: #1e40af;
        }

        .alert-danger {
            background: #fee2e2;
            border-color: #fca5a5;
            color: #991b1b;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
        }

        .empty-state-icon {
            font-size: 3rem;
            color: #d1d5db;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .plans-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="page-header-modern">
            <h1 class="page-header-title">Subscription Required</h1>
            <div class="page-header-separator"></div>
            <div class="page-header-breadcrumb">
                <i class="icofont-lock"></i>
                <span>System Access</span>
            </div>
        </div>
        <p class="page-header-description">Please subscribe to continue using the system</p>

        <div class="modern-card">
            <div class="modern-card-header">
                <h5>
                    <i class="icofont-info-circle"></i>
                    System Access
                </h5>
            </div>
            <div class="modern-card-body">
                @if(session('info'))
                <div class="alert alert-info">
                    <i class="icofont-info-circle"></i> {{ session('info') }}
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger">
                    <i class="icofont-warning"></i> {{ session('error') }}
                </div>
                @endif

                @if($canSubscribe)
                    <h6 class="mb-3" style="color: #374151; font-weight: 600;">Choose Subscription Duration</h6>
                    
                    <form action="{{ route('subscription.subscribe') }}" method="POST" id="subscribeForm">
                        @csrf
                        
                        <div class="plans-grid" id="plansGrid">
                            @foreach($pricing as $yearKey => $option)
                            <div class="plan-card" data-years="{{ $yearKey }}" onclick="selectYears('{{ $yearKey }}')">
                                <div class="plan-name">{{ $option['name'] }}</div>
                                <div class="plan-price">{{ $currency }} {{ number_format($option['price'], 2) }}</div>
                                <div class="plan-cycle">{{ $option['description'] }}</div>
                                @if($yearKey === '3')
                                <div class="plan-badge">BEST VALUE</div>
                                @endif
                            </div>
                            @endforeach
                        </div>

                        <input type="hidden" name="years" id="selectedYears" required>

                        <div class="subscribe-form">
                            <div class="mb-3">
                                <label for="institution_name" class="form-label">
                                    <i class="icofont-building"></i> Institution Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       class="form-control" 
                                       id="institution_name" 
                                       name="institution_name" 
                                       placeholder="Enter your institution name"
                                       required>
                            </div>

                            <button type="submit" class="btn-subscribe" id="subscribeBtn" disabled>
                                <i class="icofont-credit-card"></i> Subscribe Now
                            </button>

                            <p class="info-text">
                                <i class="icofont-shield"></i> Secure payment powered by Paystack
                            </p>
                        </div>
                    </form>
                @else
                    <div class="empty-state">
                        <div class="empty-state-icon">
                            <i class="icofont-info-circle"></i>
                        </div>
                        <h5 style="color: #1f2937; margin-bottom: 0.5rem;">Contact Administrator</h5>
                        <p style="color: #6b7280;">
                            Only administrators can create subscriptions. Please contact your system administrator to set up a subscription.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        function selectYears(yearKey) {
            // Remove selected class from all cards
            document.querySelectorAll('.plan-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');

            // Set hidden input value
            document.getElementById('selectedYears').value = yearKey;

            // Enable subscribe button
            document.getElementById('subscribeBtn').disabled = false;
        }

        // Form validation
        document.getElementById('subscribeForm').addEventListener('submit', function(e) {
            const years = document.getElementById('selectedYears').value;
            const institutionName = document.getElementById('institution_name').value;

            if (!years || !institutionName.trim()) {
                e.preventDefault();
                alert('Please select a subscription duration and enter your institution name.');
                return false;
            }
        });
    </script>
</body>
</html>
