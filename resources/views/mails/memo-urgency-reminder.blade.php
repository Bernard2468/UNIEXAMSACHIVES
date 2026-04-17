<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Urgency Alert: Pending Memo - University Internal Memo Management System</title>
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
        .urgency-alert {
            background-color: #fef2f2;
            border: 1px solid #fca5a5;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #ef4444;
        }
        .urgency-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 18px;
            font-weight: bold;
            color: #dc2626;
            margin-bottom: 10px;
            display: block;
        }
        .urgency-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #dc2626;
            line-height: 1.5;
        }
        .memo-details {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .details-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 15px;
            display: block;
        }
        .detail-row {
            display: flex;
            margin-bottom: 10px;
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #000000;
        }
        .detail-label {
            font-weight: bold;
            min-width: 120px;
            color: #374151;
        }
        .detail-value {
            color: #1f2937;
        }
        .memo-message {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #3b82f6;
        }
        .message-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #000000;
            margin-bottom: 10px;
            display: block;
        }
        .message-content {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #000000;
            line-height: 1.5;
        }
        .action-required {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .action-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            display: block;
        }
        .action-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #92400e;
            line-height: 1.5;
        }
        .cta-button {
            display: inline-block;
            background-color: #ef4444;
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
                <div class="greeting">Hi {{ $recipient->first_name }} {{ $recipient->last_name }},</div>
                
                <div class="message">
                    This is an urgency alert regarding a pending memo that requires your immediate attention.
                </div>
                
                <div class="urgency-alert">
                    <div class="urgency-title">
                        🚩 Urgency Alert - Action Required
                    </div>
                    <div class="urgency-message">
                        {{ $sender->first_name }} {{ $sender->last_name }} has flagged this memo as requiring urgent attention. Please review and respond as soon as possible.
                    </div>
                </div>
                
                <div class="memo-details">
                    <div class="details-title">
                        📝 Memo Details
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Memo Subject:</span>
                        <span class="detail-value">{{ $memo->subject }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Flagged By:</span>
                        <span class="detail-value">{{ $sender->first_name }} {{ $sender->last_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Sender Email:</span>
                        <span class="detail-value">{{ $sender->email }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Alert Date:</span>
                        <span class="detail-value">{{ now()->format('F j, Y \a\t g:i A') }}</span>
                    </div>
                </div>
                
                @if($memo->message)
                <div class="memo-message">
                    <div class="message-title">
                        📄 Memo Content
                    </div>
                    <div class="message-content">
                        {!! $memo->message !!}
                    </div>
                </div>
                @endif
                
                <div class="action-required">
                    <div class="action-title">
                        ⚡ Immediate Action Required
                    </div>
                    <div class="action-message">
                        This memo has been flagged as urgent and requires your immediate attention. Please log in to the UIMMS portal to view the full conversation and respond accordingly.
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <a href="{{ route('dashboard.uimms.chat', $memo->id) }}" class="cta-button">View & Respond to Memo</a>
                </div>
                
                <div style="text-align: left; margin: 15px 0;">
                    <p style="font-family: Georgia, Times, 'Times New Roman', serif; color: #000000; font-size: 16px; line-height: 1.4;">
                        This urgency alert was sent because {{ $sender->first_name }} {{ $sender->last_name }} has indicated that this pending memo requires immediate attention. Please review and respond at your earliest convenience.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-disclaimer">
                This email was sent to {{ $recipient->email }} as an urgency alert for a pending memo in the University Internal Memo Management System. 
                Please log in to your dashboard to view and respond to this memo.
            </div>
        </div>
    </div>
</body>
</html>

