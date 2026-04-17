<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - Metascholar Consult</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        /* Animated background particles */
        .background-particles {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: float 15s infinite ease-in-out;
        }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px);
                opacity: 0;
            }
        }

        /* Main container */
        .maintenance-container {
            position: relative;
            z-index: 1;
            padding: 40px;
            max-width: 1200px;
            width: 100%;
            display: flex;
            gap: 40px;
            align-items: center;
        }

        .maintenance-left {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 30px;
        }

        .maintenance-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            padding-left: 40px;
            border-left: 1px solid rgba(0, 0, 0, 0.1);
        }

        /* Bouncing image */
        .maintenance-image {
            width: 300px;
            height: 300px;
            animation: bounce 3s ease-in-out infinite;
            filter: drop-shadow(0 20px 40px rgba(0, 0, 0, 0.3));
            position: relative;
        }

        .maintenance-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 20px;
            background: white;
            padding: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        @keyframes bounce {
            0%, 100% {
                transform: translateY(0) scale(1);
            }
            50% {
                transform: translateY(-30px) scale(1.05);
            }
        }

        /* Pill-style maintenance message */
        .maintenance-pill {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            background: white;
            padding: 16px 32px;
            border-radius: 50px;
            border: 2px solid #01b2ac;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            animation: pulse 2s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% {
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            }
            50% {
                box-shadow: 0 15px 50px rgba(1, 178, 172, 0.3);
            }
        }

        .maintenance-pill i {
            color: #01b2ac;
            font-size: 24px;
            animation: rotate 4s linear infinite;
        }

        @keyframes rotate {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }

        .maintenance-pill span {
            font-size: 18px;
            font-weight: 700;
            color: #333;
            letter-spacing: 0.5px;
        }

        /* Additional info */
        .maintenance-info {
            background: white;
            border-radius: 20px;
            padding: 30px;
            border: 1px solid #e0e0e0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            text-align: left;
        }

        .maintenance-info h2 {
            color: #333;
            font-size: 28px;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .maintenance-info p {
            color: #666;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 10px;
        }

        .maintenance-info .time {
            color:rgb(48, 56, 54);
            font-weight: 600;
            font-size: 18px;
            margin-top: 15px;
        }

        /* Loading dots */
        .loading-dots {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-top: 50px;
        }

        .loading-dots span {
            width: 12px;
            height: 12px;
            background: #01b2ac;
            border-radius: 50%;
            animation: dot-bounce 1.4s ease-in-out infinite;
        }

        .loading-dots span:nth-child(1) {
            animation-delay: 0s;
        }

        .loading-dots span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .loading-dots span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes dot-bounce {
            0%, 80%, 100% {
                transform: scale(0.8);
                opacity: 0.5;
            }
            40% {
                transform: scale(1.2);
                opacity: 1;
            }
        }

        /* Responsive */
        @media (max-width: 968px) {
            .maintenance-container {
                flex-direction: column;
                gap: 30px;
            }

            .maintenance-left {
                width: 100%;
            }

            .maintenance-right {
                padding-left: 0;
                border-left: none;
                border-top: 1px solid rgba(0, 0, 0, 0.1);
                padding-top: 30px;
                width: 100%;
            }
        }

        @media (max-width: 768px) {
            .maintenance-container {
                padding: 20px;
            }

            .maintenance-image {
                width: 250px;
                height: 250px;
            }

            .maintenance-pill {
                padding: 12px 24px;
            }

            .maintenance-pill span {
                font-size: 16px;
            }

            .maintenance-info {
                padding: 20px;
            }

            .maintenance-info h2 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>
    <!-- Background particles -->
    <div class="background-particles">
        @for($i = 0; $i < 20; $i++)
            <div class="particle" style="left: {{ rand(0, 100) }}%; animation-delay: {{ rand(0, 15) }}s; animation-duration: {{ rand(10, 20) }}s;"></div>
        @endfor
    </div>

    <!-- Main content -->
    <div class="maintenance-container">
        <!-- Left Side: Image and Maintenance Message -->
        <div class="maintenance-left">
            <!-- Bouncing maintenance image -->
            <div class="maintenance-image">
                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1762374398/20943392_yazt5t.jpg" alt="System Maintenance">
            </div>

            <!-- Pill-style maintenance message -->
            <div class="maintenance-pill">
                <i class="icofont-gear"></i>
                <span>System is Under Maintenance</span>
            </div>
        </div>

        <!-- Right Side: All Information -->
        <div class="maintenance-right">
            <!-- Additional information -->
            <div class="maintenance-info">
            <h2>We're Making Things Better!</h2>
            <p>Our team is currently performing scheduled maintenance to improve your experience.</p>
            
            @if(isset($maintenance) && $maintenance)
                @if($maintenance->description)
                    <p style="margin-top: 15px; font-style: italic;">{{ $maintenance->description }}</p>
                @endif
                
                @if($maintenance->scheduled_end)
                    <p class="time" style="margin-top: 20px; margin-bottom: 10px;">
                        <i class="icofont-clock-time"></i> 
                        Expected completion: {{ $maintenance->scheduled_end->format('M d, Y h:i A') }}
                    </p>
                    
                    <!-- Flip-Style Countdown Timer -->
                    <div class="maintenance-countdown-section" style="margin-top: 30px;">
                        <h3 class="countdown-title" style="color: #333; font-size: 1.2rem; margin-bottom: 1.5rem;">Maintenance Ends In</h3>
                        <div class="countdown-container" id="maintenance-countdown-container" 
                             data-end-time="{{ $maintenance->scheduled_end->timestamp * 1000 }}">
                            <!-- Hours - Tens -->
                            <div class="countdown-group">
                                <div class="countdown-label">Hours</div>
                                <div class="countdown-wrapper">
                                    <div class="nums nums-ten" id="hours-tens">
                                        @for($i = 0; $i <= 9; $i++)
                                            <div class="num" data-num="{{ $i }}" data-num-next="{{ ($i + 1) % 10 }}"></div>
                                        @endfor
                                    </div>
                                    <div class="nums nums-one" id="hours-ones">
                                        @for($i = 0; $i <= 9; $i++)
                                            <div class="num" data-num="{{ $i }}" data-num-next="{{ ($i + 1) % 10 }}"></div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="countdown-separator">:</div>

                            <!-- Minutes - Tens -->
                            <div class="countdown-group">
                                <div class="countdown-label">Minutes</div>
                                <div class="countdown-wrapper">
                                    <div class="nums nums-ten" id="minutes-tens">
                                        @for($i = 0; $i <= 9; $i++)
                                            <div class="num" data-num="{{ $i }}" data-num-next="{{ ($i + 1) % 10 }}"></div>
                                        @endfor
                                    </div>
                                    <div class="nums nums-one" id="minutes-ones">
                                        @for($i = 0; $i <= 9; $i++)
                                            <div class="num" data-num="{{ $i }}" data-num-next="{{ ($i + 1) % 10 }}"></div>
                                        @endfor
                                    </div>
                                </div>
                            </div>

                            <div class="countdown-separator">:</div>

                            <!-- Seconds - Tens -->
                            <div class="countdown-group">
                                <div class="countdown-label">Seconds</div>
                                <div class="countdown-wrapper">
                                    <div class="nums nums-ten" id="seconds-tens">
                                        @for($i = 0; $i <= 9; $i++)
                                            <div class="num" data-num="{{ $i }}" data-num-next="{{ ($i + 1) % 10 }}"></div>
                                        @endfor
                                    </div>
                                    <div class="nums nums-one" id="seconds-ones">
                                        @for($i = 0; $i <= 9; $i++)
                                            <div class="num" data-num="{{ $i }}" data-num-next="{{ ($i + 1) % 10 }}"></div>
                                        @endfor
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            @else
                <p style="margin-top: 15px;">We'll be back shortly. Thank you for your patience!</p>
            @endif

            <!-- Loading animation -->
            <div class="loading-dots">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
        </div>
    </div>

    <!-- Icofont for icons -->
    <link rel="stylesheet" href="{{ asset('css/icofont.min.css') }}">

    <style>
        /* Flip-Style Countdown Timer Styles (matching banner component) */
        .maintenance-countdown-section {
            text-align: center;
            margin: 30px 0;
            width: 100%;
        }

        .countdown-title {
            font-size: 1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 1.25rem;
        }

        .countdown-container {
            height: 100px;
            position: relative;
            text-align: center;
            display: flex;
            gap: 8px;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        .countdown-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.375rem;
        }

        .countdown-label {
            font-size: 0.625rem;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .countdown-wrapper {
            display: flex;
            gap: 3px;
        }

        .countdown-separator {
            font-size: 1rem;
            font-weight: 700;
            color: #1f2937;
            align-self: flex-end;
            padding-bottom: 28px;
        }

        /* Simple Countdown Timer Styles */
        .nums {
            display: inline-block;
            height: 100px;
            position: relative;
            width: 65px;
        }

        .num {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            font-size: 65px;
            font-weight: bold;
            color: #eeeeee;
            border-radius: 10px;
            background: linear-gradient(to bottom, #181818 0%, #222 50%, #2a2a2a 50%, #1a1a1a 100%);
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.5);
            text-shadow: 0 1px 2px #000;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 0.3s ease;
        }

        /* Middle divider line */
        .num::before {
            content: "";
            position: absolute;
            top: 50%;
            left: 0;
            width: 100%;
            height: 2px;
            background: #000;
            z-index: 5;
            transform: translateY(-1px);
            box-shadow: 0 1px 1px rgba(0, 0, 0, 0.5);
        }

        /* Inner shadow effects for depth */
        .num::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            border-radius: 10px;
            box-shadow: inset 0 10px 30px rgba(0, 0, 0, 0.4), inset 0 -10px 30px rgba(0, 0, 0, 0.4);
            pointer-events: none;
        }

        /* Active/Hidden state for JavaScript control */
        .num.active {
            display: flex;
            z-index: 10;
            opacity: 1;
        }

        .num.hidden {
            display: none;
            opacity: 0;
        }

        /* Number display */
        .num .number {
            position: relative;
            z-index: 2;
        }


        @media (max-width: 768px) {
            .countdown-container {
                gap: 6px;
                height: 85px;
            }

            .nums {
                width: 55px;
                height: 85px;
            }

            .num {
                font-size: 55px;
            }

            .countdown-separator {
                font-size: 0.875rem;
                padding-bottom: 24px;
            }
        }
    </style>

    <script>
        let checkInterval;
        let countdownInterval;

        // Store current values to detect changes
        let currentValues = {
            hoursTens: -1,
            hoursOnes: -1,
            minutesTens: -1,
            minutesOnes: -1,
            secondsTens: -1,
            secondsOnes: -1
        };

        // Function to update flip countdown
        function updateFlipCountdown() {
            const container = document.getElementById('maintenance-countdown-container');
            if (!container) return;

            const endTime = parseInt(container.dataset.endTime);
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                // Maintenance should be over, check status
                checkMaintenanceStatus();
                return;
            }

            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            // Update each digit and trigger flip if changed
            const hoursTens = Math.floor(hours / 10);
            const hoursOnes = hours % 10;
            const minutesTens = Math.floor(minutes / 10);
            const minutesOnes = minutes % 10;
            const secondsTens = Math.floor(seconds / 10);
            const secondsOnes = seconds % 10;

            if (currentValues.hoursTens !== hoursTens) {
                updateFlipDigit('hours-tens', hoursTens);
                currentValues.hoursTens = hoursTens;
            }
            if (currentValues.hoursOnes !== hoursOnes) {
                updateFlipDigit('hours-ones', hoursOnes);
                currentValues.hoursOnes = hoursOnes;
            }
            if (currentValues.minutesTens !== minutesTens) {
                updateFlipDigit('minutes-tens', minutesTens);
                currentValues.minutesTens = minutesTens;
            }
            if (currentValues.minutesOnes !== minutesOnes) {
                updateFlipDigit('minutes-ones', minutesOnes);
                currentValues.minutesOnes = minutesOnes;
            }
            if (currentValues.secondsTens !== secondsTens) {
                updateFlipDigit('seconds-tens', secondsTens);
                currentValues.secondsTens = secondsTens;
            }
            if (currentValues.secondsOnes !== secondsOnes) {
                updateFlipDigit('seconds-ones', secondsOnes);
                currentValues.secondsOnes = secondsOnes;
            }
        }

        // Function to update digit - simplified without flip animation
        function updateFlipDigit(containerId, targetValue) {
            const container = document.getElementById(containerId);
            if (!container) return;

            const nums = container.querySelectorAll('.num');
            let currentActive = null;
            
            // Find current active number
            nums.forEach((num) => {
                if (num.classList.contains('active')) {
                    currentActive = num;
                }
            });

            nums.forEach((num) => {
                const numValue = parseInt(num.getAttribute('data-num'));
                
                // Show the target number
                if (numValue === targetValue) {
                    if (!currentActive || currentActive !== num) {
                        // Hide old active
                        if (currentActive) {
                            currentActive.classList.remove('active');
                            currentActive.classList.add('hidden');
                        }
                        
                        // Show new number
                        num.classList.remove('hidden');
                        num.classList.add('active');
                        
                        // Update number display
                        let numberSpan = num.querySelector('.number');
                        if (!numberSpan) {
                            numberSpan = document.createElement('span');
                            numberSpan.className = 'number';
                            num.appendChild(numberSpan);
                        }
                        numberSpan.textContent = targetValue;
                    }
                } else {
                    // Hide non-active numbers
                    if (num !== currentActive) {
                        num.classList.remove('active');
                        num.classList.add('hidden');
                    }
                }
            });
        }

        function checkMaintenanceStatus() {
            fetch('/api/check-maintenance-status', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.maintenance_active) {
                    // Maintenance is over, redirect to home
                    window.location.href = '/';
                }
            })
            .catch(error => {
                console.error('Error checking maintenance status:', error);
            });
        }

        // Start countdown if maintenance end time exists
        const container = document.getElementById('maintenance-countdown-container');
        if (container) {
            // Initialize countdown immediately - set initial values
            updateFlipCountdown();
            
            // Start interval for updates
            countdownInterval = setInterval(updateFlipCountdown, 1000);

            // Check maintenance status every 30 seconds
            checkInterval = setInterval(checkMaintenanceStatus, 30000);
        }

        // Clean up on page unload
        window.addEventListener('beforeunload', function() {
            if (checkInterval) clearInterval(checkInterval);
            if (countdownInterval) clearInterval(countdownInterval);
        });
    </script>
</body>
</html>

