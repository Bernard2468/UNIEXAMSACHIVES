<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject }}</title>
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
            font-family: Georgia, Times, 'Times New Roman', serif;
        }
        .content-card {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .greeting {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #000000;
            margin-bottom: 12px;
            font-weight: bold;
        }
        .message-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 14px;
            color: #1a1a1a;
            margin-bottom: 20px;
            font-weight: bold;
            background-color:rgb(255, 240, 240);
            padding: 10px;
            border-radius: 8px;
            border: 1px solidrgb(255, 240, 240);
        
        }
        .message-content {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #1a1a1a;
            margin-bottom: 20px;
            line-height: 1.6;
            background-color: #f0f8ff;
            padding: 20px;
            border-radius: 8px;
            border: 1px solid #e6f3ff;
        }
        .message-content p { 
            margin: 0 0 12px 0; 
            color: #1a1a1a;
        }
        .message-content ul { 
            margin: 12px 0; 
            padding-left: 20px; 
            color: #1a1a1a;
        }
        .message-content ol { 
            margin: 12px 0; 
            padding-left: 20px; 
            color: #1a1a1a;
        }
        .message-content h1, .message-content h2, .message-content h3 { 
            margin: 16px 0 8px 0; 
            color: #1a1a1a;
            font-weight: bold;
        }
        .message-content strong, .message-content b {
            color: #1a1a1a;
            font-weight: bold;
        }
        .message-content em, .message-content i {
            color: #1a1a1a;
            font-style: italic;
        }
        .message-content a {
            color: #1e40af;
            text-decoration: underline;
        }
        .message-content a:hover {
            color: #1d4ed8;
        }
        .attachments {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .attachments-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            display: block;
        }
        .attachments-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #92400e;
            line-height: 1.5;
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
                padding: 15px !important;
                margin: 10px 0;
            }
            .logo-image {
                height: 45px;
                max-width: 220px;
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
                <div class="greeting">Dear {{ $user->first_name }} {{ $user->last_name }},</div>
                <p>You have received an important message from the University Advanced Communication System.</p>
                <br>
                <p>Subject:</p>
                <div class="message-title">{{ $subject }}</div>
                <p>Memo Message:</p>
                <div class="message-content">
                    {!! $message !!}
                </div>
                
                @if($campaign->attachments && count($campaign->attachments) > 0)
                    <div class="attachments">
                        <div class="attachments-title">
                            📎 Attachments Included
                        </div>
                        <div class="attachments-message">
                            This message includes {{ count($campaign->attachments) }} attachment(s) that have been sent with this communication.
                        </div>
                    </div>
                @endif
                
                <div style="text-align: left; margin: 15px 0;">
                    <p style="font-family: Georgia, Times, 'Times New Roman', serif; color: #000000; font-size: 16px; line-height: 1.4;">
                        If you have any questions about this message, please contact our support team.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-disclaimer">
                This is an automated message from the University Advanced Communication System. 
                Please do not reply to this email.
            </div>
        </div>
    </div>
</body>
</html>