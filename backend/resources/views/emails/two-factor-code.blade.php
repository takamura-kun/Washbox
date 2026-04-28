<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; margin-bottom: 30px; }
        .logo { font-size: 28px; font-weight: bold; color: #4F46E5; }
        .code-box { background: #F3F4F6; border: 2px dashed #4F46E5; padding: 20px; text-align: center; border-radius: 8px; margin: 20px 0; }
        .code { font-size: 36px; font-weight: bold; color: #4F46E5; letter-spacing: 8px; }
        .info { color: #6B7280; font-size: 14px; margin-top: 20px; }
        .warning { background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 15px; margin-top: 20px; }
        .footer { text-align: center; margin-top: 30px; color: #9CA3AF; font-size: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">🧺 WashBox</div>
            <h2>Two-Factor Authentication</h2>
        </div>

        <p>Hello {{ $user->name }},</p>
        
        <p>You are attempting to log in to your WashBox account. Please use the verification code below to complete your login:</p>

        <div class="code-box">
            <div class="code">{{ $code }}</div>
        </div>

        <div class="info">
            <p><strong>⏱️ This code will expire in 10 minutes.</strong></p>
            <p>Enter this code in the app to verify your identity and complete the login process.</p>
        </div>

        <div class="warning">
            <strong>⚠️ Security Notice:</strong><br>
            If you did not attempt to log in, please ignore this email and consider changing your password immediately.
        </div>

        <div class="footer">
            <p>This is an automated message from WashBox Laundry Services.</p>
            <p>Please do not reply to this email.</p>
        </div>
    </div>
</body>
</html>
