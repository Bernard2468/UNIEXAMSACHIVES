<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Memo Assignment Successful - University Internal Memo Management System</title>
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
        .success-box {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #22c55e;
        }
        .success-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 18px;
            font-weight: bold;
            color: #166534;
            margin-bottom: 10px;
            display: block;
        }
        .success-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #166534;
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
        .assignee-info {
            background-color: #fef3c7;
            border: 1px solid #fbbf24;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            border-left: 4px solid #f59e0b;
        }
        .assignee-title {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            display: block;
        }
        .assignee-message {
            font-family: Georgia, Times, 'Times New Roman', serif;
            font-size: 16px;
            color: #92400e;
            line-height: 1.5;
        }
        .cta-button {
            display: inline-block;
            background-color: #059669;
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
                <div class="greeting">Hi {{ $assigner->first_name }} {{ $assigner->last_name }},</div>
                
                <div class="message">
                    Great news! Your memo has been successfully assigned and the assignee has been notified.
                </div>
                
                <div class="success-box">
                    <div class="success-title">
                        ✅ Memo Assignment Successful!
                    </div>
                    <div class="success-message">
                        Your memo "{{ $memo->subject }}" has been successfully assigned and the recipient has been notified via email.
                    </div>
                </div>
                
                <div class="memo-details">
                    <div class="details-title">
                        📋 Assignment Details
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Memo Subject:</span>
                        <span class="detail-value">{{ $memo->subject }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Assigned To:</span>
                        <span class="detail-value">{{ $assignee->first_name }} {{ $assignee->last_name }}</span>
                    </div>
                    <div class="detail-row">
                        <span class="detail-label">Assignee Email:</span>
                        <span class="detail-value">{{ $assignee->email }}</span>
                    </div>
                    @if($office)
                    <div class="detail-row">
                        <span class="detail-label">Office/Department:</span>
                        <span class="detail-value">{{ $office }}</span>
                    </div>
                    @endif
                    <div class="detail-row">
                        <span class="detail-label">Assignment Date:</span>
                        <span class="detail-value">{{ now()->format('F j, Y \a\t g:i A') }}</span>
                    </div>
                </div>
                
                <div class="assignee-info">
                    <div class="assignee-title">
                        📧 Notification Sent
                    </div>
                    <div class="assignee-message">
                        {{ $assignee->first_name }} {{ $assignee->last_name }} has been notified via email about this assignment and can now access the memo through their dashboard.
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <a href="{{ route('dashboard.uimms.chat', $memo->id) }}" class="cta-button">View Memo Conversation</a>
                </div>
                
                <div style="text-align: left; margin: 15px 0;">
                    <p style="font-family: Georgia, Times, 'Times New Roman', serif; color: #000000; font-size: 16px; line-height: 1.4;">
                        You can continue to monitor the memo's progress and participate in the conversation through the UIMMS portal.
                    </p>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <div class="footer-disclaimer">
                This email confirms that your memo assignment was successful. The assignee has been notified and can now access the memo through their dashboard.
            </div>
        </div>
    </div>
</body>
</html>
