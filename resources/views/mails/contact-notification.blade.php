<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Contact Form Submission - University Digital Transformation Suite (UDTS)</title>
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
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #000000;
        }
        .header p {
            margin: 10px 0 0 0;
            color: #666666;
            font-size: 16px;
        }
        .content {
            background-color: #F7F5FB;
            padding: 0 30px 30px 30px;
        }
        .alert-box {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #dc2626;
        }
        .alert-box h3 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 18px;
            font-weight: 600;
        }
        .alert-box p {
            color: #7f1d1d;
            margin: 0;
            font-size: 14px;
        }
        .inquiry-details {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 25px;
            margin: 25px 0;
        }
        .inquiry-details h3 {
            color: #152c6a;
            margin: 0 0 20px 0;
            font-size: 18px;
            font-weight: 600;
            display: flex;
            align-items: center;
        }
        .detail-row {
            display: flex;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e2e8f0;
        }
        .detail-row:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        .detail-label {
            font-weight: 600;
            color: #4a5568;
            width: 120px;
            flex-shrink: 0;
        }
        .detail-value {
            color: #2d3748;
            flex: 1;
        }
        .message-content {
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .message-content h4 {
            color: #152c6a;
            margin: 0 0 15px 0;
            font-size: 16px;
            font-weight: 600;
        }
        .message-text {
            color: #4a5568;
            line-height: 1.7;
            white-space: pre-wrap;
            font-size: 14px;
        }
        .action-buttons {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
        }
        .btn-primary {
            background-color: #152c6a;
            color: white;
        }
        .btn-primary:hover {
            background-color: #1e40af;
        }
        .btn-secondary {
            background-color: #f1f5f9;
            color: #152c6a;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background-color: #e2e8f0;
        }
        .footer {
            background-color: #f5f5f5;
            padding: 20px;
            text-align: left;
            border-top: none;
        }
        .footer p {
            color: #333333;
            margin: 5px 0;
            font-size: 12px;
        }
        .priority-high {
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 15px;
            margin: 20px 0;
            border-left: 4px solid #dc2626;
        }
        .priority-high h4 {
            color: #dc2626;
            margin: 0 0 10px 0;
            font-size: 16px;
            font-weight: 600;
        }
        .priority-high p {
            color: #7f1d1d;
            margin: 0;
            font-size: 14px;
        }
        @media (max-width: 600px) {
            .email-container {
                margin: 0 !important;
                max-width: 100% !important;
                width: 100% !important;
            }
            .header, .content, .footer {
                padding: 20px;
            }
            .detail-row {
                flex-direction: column;
                gap: 5px;
            }
            .detail-label {
                width: auto;
            }
            .action-buttons .btn {
                display: block;
                margin: 10px 0;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <div style="margin-bottom: 15px;">
                 <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1761222538/cug_logo_new_e9d6v9.jpg" alt="University Digital Transformation Suite (UDTS)" style="height: 50px; width: auto; max-width: 250px;" />
            </div>
            <h1>🚨 New Contact Form Submission</h1>
            <p>University Digital Transformation Suite (UDTS) Website</p>
        </div>
        
        <div class="content">
            <div class="alert-box">
                <h3>📬 New Inquiry Received</h3>
                <p>A new contact form submission has been received on <strong>{{ now()->format('F j, Y \a\t g:i A') }}</strong>. Please review the details below and respond promptly.</p>
            </div>
            
            <div class="inquiry-details">
                <h3>📋 Inquiry Details</h3>
                <div class="detail-row">
                    <span class="detail-label">Name:</span>
                    <span class="detail-value"><strong>{{ $name ?? 'Not provided' }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value"><a href="mailto:{{ $email ?? 'Not provided' }}" style="color: #152c6a; text-decoration: none;">{{ $email ?? 'Not provided' }}</a></span>
                </div>
                @if(isset($phone) && $phone)
                <div class="detail-row">
                    <span class="detail-label">Phone:</span>
                    <span class="detail-value"><a href="tel:{{ $phone }}" style="color: #152c6a; text-decoration: none;">{{ $phone }}</a></span>
                </div>
                @endif
                <div class="detail-row">
                    <span class="detail-label">Subject:</span>
                    <span class="detail-value"><strong>{{ $subject ?? 'Not provided' }}</strong></span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Submitted:</span>
                    <span class="detail-value">{{ now()->format('F j, Y \a\t g:i A') }}</span>
                </div>
            </div>
            
            <div class="message-content">
                <h4>💬 Message Content</h4>
                <div class="message-text">{{ $message ?? 'No message provided' }}</div>
            </div>
            
            <div class="priority-high">
                <h4>⚡ Action Required</h4>
                <p>This inquiry requires your attention. Please respond to the customer within 24 hours to maintain our service standards.</p>
            </div>
            
            <div class="action-buttons">
                <a href="mailto:{{ $email ?? '#' }}?subject=Re: {{ $subject ?? 'Inquiry' }}&body=Dear {{ $name ?? 'Valued Customer' }},%0D%0A%0D%0AThank you for contacting the University Digital Transformation Suite (UDTS). We have received your inquiry regarding: {{ $subject ?? 'your request' }}%0D%0A%0D%0A" class="btn btn-primary">
                    📧 Reply to Customer
                </a>
                <a href="mailto:support@academicdigital.space?subject=Follow-up: {{ $subject ?? 'Inquiry' }} - {{ $name ?? 'Customer' }}" class="btn btn-secondary">
                    📋 Create Follow-up Task
                </a>
            </div>
        </div>
        
        <div class="footer">
            <p><strong>University Digital Transformation Suite (UDTS)</strong> - Internal Notification</p>
            <p>This email was automatically generated from the website contact form.</p>
            <p>Customer Email: {{ $email ?? 'Not provided' }} | Submitted: {{ now()->format('F j, Y \a\t g:i A') }}</p>
        </div>
    </div>
</body>
</html>
