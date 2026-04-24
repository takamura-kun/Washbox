<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Maintenance - WashBox</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }
        .maintenance-container {
            max-width: 600px;
            padding: 20px;
        }
        .maintenance-card {
            background: white;
            border-radius: 24px;
            padding: 48px 40px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .maintenance-icon {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #ffd89b 0%, #19547b 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 32px;
            animation: pulse 2s ease-in-out infinite;
        }
        .maintenance-icon i {
            font-size: 56px;
            color: white;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        h1 {
            font-size: 32px;
            font-weight: 700;
            color: #1a202c;
            margin-bottom: 16px;
        }
        .message {
            font-size: 18px;
            color: #4a5568;
            line-height: 1.6;
            margin-bottom: 32px;
        }
        .countdown {
            background: #f7fafc;
            border-radius: 12px;
            padding: 20px;
            margin-top: 24px;
        }
        .countdown-label {
            font-size: 14px;
            color: #718096;
            margin-bottom: 8px;
        }
        .countdown-time {
            font-size: 24px;
            font-weight: 700;
            color: #2d3748;
        }
        .logo {
            width: 80px;
            height: 80px;
            margin: 0 auto 24px;
        }
        .contact-info {
            margin-top: 32px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        .contact-info p {
            font-size: 14px;
            color: #718096;
            margin: 0;
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-card">
            {{-- Logo --}}
            <div class="logo">
                <img src="{{ asset('images/logo.png') }}" alt="WashBox Logo" style="width: 100%; height: 100%; object-fit: contain;">
            </div>

            {{-- Maintenance Icon --}}
            <div class="maintenance-icon">
                <i class="bi bi-tools"></i>
            </div>

            {{-- Title --}}
            <h1>System Maintenance</h1>

            {{-- Message --}}
            <div class="message">
                {{ $message }}
            </div>

            {{-- Countdown Timer (if maintenance end time is set) --}}
            @if($maintenanceEnd)
            <div class="countdown">
                <div class="countdown-label">Expected to be back online:</div>
                <div class="countdown-time" id="countdown">
                    {{ \Carbon\Carbon::parse($maintenanceEnd)->format('M d, Y h:i A') }}
                </div>
            </div>
            @endif

            {{-- Contact Info --}}
            <div class="contact-info">
                <p>Need urgent assistance? Contact our support team.</p>
            </div>
        </div>
    </div>

    <script>
        @if($maintenanceEnd)
        // Countdown timer
        const endTime = new Date("{{ $maintenanceEnd }}").getTime();
        
        function updateCountdown() {
            const now = new Date().getTime();
            const distance = endTime - now;

            if (distance < 0) {
                document.getElementById('countdown').innerHTML = "Maintenance completed!";
                setTimeout(() => location.reload(), 3000);
                return;
            }

            const days = Math.floor(distance / (1000 * 60 * 60 * 24));
            const hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            let countdownText = '';
            if (days > 0) countdownText += days + "d ";
            if (hours > 0) countdownText += hours + "h ";
            if (minutes > 0) countdownText += minutes + "m ";
            countdownText += seconds + "s";

            document.getElementById('countdown').innerHTML = countdownText;
        }

        updateCountdown();
        setInterval(updateCountdown, 1000);
        @endif
    </script>
</body>
</html>
