<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Updated</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .container {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #28a745;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #007bff;
            margin-bottom: 10px;
        }
        .title {
            color: #28a745;
            font-size: 20px;
            margin-bottom: 20px;
        }
        .content {
            margin-bottom: 30px;
        }
        .highlight {
            background-color: #d4edda;
            padding: 15px;
            border-left: 4px solid #28a745;
            margin: 20px 0;
            border-radius: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            color: #6c757d;
            font-size: 14px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #28a745;
            color: #ffffff;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .button:hover {
            background-color: #218838;
        }
        .success-icon {
            font-size: 48px;
            color: #28a745;
            margin-bottom: 20px;
        }
        .security-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">University Digital Archival System</div>
            <div class="title">Password Updated Successfully!</div>
        </div>
        
        <div class="content">
            <div style="text-align: center; margin-bottom: 20px;">
                <div class="success-icon">🔒</div>
            </div>
            
            <p>Dear <strong>{{ $firstname }}</strong>,</p>
            
            <p>Great news! Your password has been successfully updated and your account is now more secure.</p>
            
            <div class="highlight">
                <strong>Password Update Status: SUCCESSFUL</strong><br>
                <strong>Account Email:</strong> {{ $email }}<br>
                <strong>Update Time:</strong> {{ now()->format('F j, Y \a\t g:i A') }}
            </div>
            
            <p><strong>What this means:</strong></p>
            <ul>
                <li>✅ Your temporary password has been replaced with your new secure password</li>
                <li>✅ You can now log in using your new password</li>
                <li>✅ Your account is fully activated and secure</li>
                <li>✅ You have access to all system features</li>
            </ul>
            
            <div class="security-note">
                <strong>🔐 Security Reminder:</strong>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Keep your new password secure and don't share it with anyone</li>
                    <li>Use a strong, unique password that you don't use elsewhere</li>
                    <li>Consider changing your password periodically for enhanced security</li>
                </ul>
            </div>
            
            <p><strong>Next Steps:</strong></p>
            <ul>
                <li>You can now log in to your account using your new password</li>
                <li>Access the dashboard and start using all system features</li>
                <li>Upload and manage exam materials</li>
                <li>Use the advance communication system (if applicable)</li>
            </ul>
            
            <p>Welcome to the University Digital Archival System! We're excited to have you fully onboard.</p>
            
            <div style="text-align: center;">
                <a href="{{ url('/login') }}" class="button">Login to Your Account</a>
            </div>
        </div>
        
        <div class="footer">
            <p>This is an automated message from the University Digital Archival System.</p>
            <p>Please do not reply to this email.</p>
            <p>&copy; {{ date('Y') }} University Digital Archival System. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
