<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to University Digital Transformation Suite (UDTS)</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Georgia, Times, 'Times New Roman', serif;
            background-color: #ffffff;
            color: #1a1a1a;
            line-height: 1.6;
            -webkit-text-size-adjust: 100%;
        }
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }
        .header {
            background-color: #f0f4f8;
            padding: 15px 20px;
            text-align: center;
            border-bottom: 1px solid #e2e8f0;
        }
        .logo-section {
            margin-bottom: 0;
        }
        .logo {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 0;
        }
        .logo-image {
            height: 50px;
            width: auto;
            max-width: 250px;
        }
        .tagline {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            margin-top: 5px;
        }
        .content {
            background-color: #f8fafc;
            padding: 0 30px 30px 30px;
        }
        .content-card {
            background-color: #f8fafc;
            border: none;
            border-radius: 0;
            padding: 20px 0;
            margin: 0;
            box-shadow: none;
        }
        .greeting {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #000000;
            margin-bottom: 15px;
            font-weight: bold;
        }
        .message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #000000;
            margin-bottom: 20px;
            line-height: 1.5;
        }
        .success-box {
            background-color: #f0f9ff;
            border: 1px solid #bae6fd;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #0ea5e9;
        }
        .success-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 18px;
            font-weight: bold;
            color: #0c4a6e;
            margin-bottom: 10px;
            display: block;
        }
        .success-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #0c4a6e;
            line-height: 1.5;
        }
        .approval-status {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .status-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            display: block;
        }
        .status-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #92400e;
            line-height: 1.5;
        }
        .next-steps {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .steps-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 10px;
            display: block;
        }
        .steps-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .steps-list li {
            font-family: Georgia, Times, 'Times New Roman', serif;
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            color: #000000;
            line-height: 1.4;
            padding-left: 0;
            position: relative;
        }
        .steps-list li:last-child {
            margin-bottom: 0;
        }
        .steps-list li::before {
            content: "• ";
            position: static;
            font-weight: bold;
            color: #000000;
            font-size: 16px;
        }
        .cta-button {
            display: inline-block;
            background-color: #3182ce;
            color: #ffffff;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            font-family: Georgia, Times, 'Times New Roman', serif;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: left;
            border-top: none;
        }
        .footer-disclaimer {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #333333;
            margin-top: 10px;
            line-height: 1.4;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .header {
                padding: 10px 15px;
            }
            .content, .footer {
                padding: 20px;
            }
            .content-card {
                padding: 20px;
                margin: 10px 0;
            }
            .logo-image {
                height: 45px;
                max-width: 220px;
            }
            .cta-button {
                display: block;
                width: 100%;
                text-align: center;
                margin: 15px 0;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="logo">
                    <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1761222538/cug_logo_new_e9d6v9.jpg" alt="University Digital Transformation Suite (UDTS)" class="logo-image" />
                </div>
                <div class="tagline">Excellence in Academic Digital Archiving</div>
            </div>
        </div>
        
        <!-- Content -->
        <div class="content">
            <div class="content-card">
                <div class="greeting">Hi {{ $firstname }},</div>
                
                <div class="message">
                    Welcome to the University Digital Transformation Suite (UDTS)! 
                    We're excited to have you join our digital archival community and look forward to collaborating with you.
                    1. You will enjoy seamless access to our Digital Archival System once approved.
                    2. You will enjoy seamless access to our Advanced Communication System once approved.
                    3. You will enjoy seamless access to our University Internal Memo Management System once approved.
                </div>
                
                <div class="success-box">
                    <div class="success-title">
                        ✅ Registration Successful!
                    </div>
                    <div class="success-message">
                        Your account has been created successfully. You can now access our digital archival system once approved and start contributing to our academic archive.
                    </div>
                </div>
                
                <div class="approval-status">
                    <div class="status-title">
                        ⏳ Account Pending Approval
                    </div>
                    <div class="status-message">
                        Your account is currently pending approval from our administrators. You'll receive an email notification once your account has been approved and activated.
                    </div>
                </div>
                
                <div class="next-steps">
                    <div class="steps-title">
                        What Happens Next?
                    </div>
                    <ul class="steps-list">
                        <li>Your registration details have been submitted for review</li>
                        <li>Our administrators will review your application within 24-48 hours</li>
                        <li>You'll receive an approval email once your account is activated</li>
                        <li>You can then log in and start using the digital archival system</li>
                    </ul>
                </div>
                
                <div style="text-align: center;">
                    <a href="{{ url('/') }}" class="cta-button">Visit Our Website</a>
                </div>
                
                <div style="text-align: left; margin: 15px 0;">
                    <p style="font-family: Georgia, Times, 'Times New Roman', serif; color: #000000; font-size: 16px; line-height: 1.4;">
                        Thank you for choosing the University Digital Transformation Suite (UDTS). We're committed to providing you with the best tools and support for your academic archival needs.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-disclaimer">
                This email was sent to {{ $email }} because you registered on our University Digital Transformation Suite (UDTS). 
                If you didn't create this account, please contact us immediately.
            </div>
        </div>
    </div>
</body>
</html>
