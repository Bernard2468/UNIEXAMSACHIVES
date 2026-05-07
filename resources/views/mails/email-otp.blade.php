<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - University Digital Transformation Suite (UDTS)</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Georgia, Times, 'Times New Roman', serif;
            background-color: #ffffff;
            color: #1a1a1a;
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
        }
        .email-container { max-width: 600px; margin: 0 auto; background-color: #ffffff; }
        .header {
            background-color: #f0f4f8;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .logo-image { height: 50px; width: auto; max-width: 250px; }
        .tagline {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-top: 5px;
        }
        .content { background-color: #f8fafc; padding: 0 30px 30px 30px; }
        .content-card { padding: 20px 0; }
        .greeting {
            font-size: 16px;
            color: #000000;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .message {
            font-size: 16px;
            color: #000000;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .otp-box {
            background-color: #f0f9ff;
            border: 2px solid #0ea5e9;
            border-radius: 10px;
            padding: 30px 20px;
            margin: 25px 0;
            text-align: center;
        }
        .otp-label {
            font-size: 14px;
            color: #0c4a6e;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: bold;
            margin-bottom: 12px;
        }
        .otp-code {
            font-family: 'Courier New', Courier, monospace;
            font-size: 42px;
            font-weight: bold;
            color: #0c4a6e;
            letter-spacing: 12px;
            line-height: 1.2;
            padding: 8px 0;
        }
        .otp-expiry {
            font-size: 13px;
            color: #475569;
            margin-top: 12px;
        }
        .warning-box {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-left: 4px solid #f59e0b;
            border-radius: 8px;
            padding: 16px 20px;
            margin: 20px 0;
        }
        .warning-title {
            font-size: 15px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 6px;
        }
        .warning-message {
            font-size: 14px;
            color: #92400e;
            line-height: 1.5;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: left;
        }
        .footer-disclaimer {
            font-size: 14px;
            color: #333333;
            line-height: 1.4;
        }
        @media (max-width: 600px) {
            .email-container { margin: 0 !important; max-width: 100% !important; width: 100% !important; }
            .header { padding: 10px 15px; }
            .content, .footer { padding: 20px; }
            .logo-image { height: 45px; max-width: 220px; }
            .otp-code { font-size: 32px; letter-spacing: 8px; }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1761222538/cug_logo_new_e9d6v9.jpg" alt="University Digital Transformation Suite (UDTS)" class="logo-image" />
                </div>
                <div class="tagline">Excellence in Academic Digital Archiving</div>
            </div>
        </div>

        <div class="content">
            <div class="content-card">
                <div class="greeting">Hi {{ $firstname }},</div>

                <div class="message">
                    Thanks for signing up for the University Digital Transformation Suite (UDTS).
                    To finish creating your account, please confirm that this is your email address by entering the verification code below.
                </div>

                <div class="otp-box">
                    <div class="otp-label">Your Verification Code</div>
                    <div class="otp-code">{{ $otp }}</div>
                    <div class="otp-expiry">This code expires in {{ $expiresInMinutes }} minutes.</div>
                </div>

                <div class="message">
                    Enter this code on the verification page to confirm your email. Once confirmed, your registration will be sent to an administrator for final approval.
                </div>

                <div class="warning-box">
                    <div class="warning-title">Didn't request this?</div>
                    <div class="warning-message">
                        If you didn't sign up for a UDTS account, you can safely ignore this email — no account will be activated without this code.
                        Never share this code with anyone, including UDTS staff.
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <div class="footer-disclaimer">
                This email was sent to {{ $email }} because someone tried to register on the University Digital Transformation Suite (UDTS) using this address.
            </div>
        </div>
    </div>
</body>
</html>
