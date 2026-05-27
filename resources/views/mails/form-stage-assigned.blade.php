<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form awaiting your action</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; padding: 32px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 32px; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
        <h2 style="color: #1d4ed8; margin: 0 0 8px;">Form Awaiting Your Action</h2>
        <p style="color: #6b7280; margin: 0 0 24px;">Reference: <strong style="color: #111827;">{{ $submission->reference }}</strong> · {{ $submission->form_code }}</p>

        <p>Hello {{ trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')) }},</p>

        <p><strong>{{ trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')) }}</strong> has forwarded a <strong>{{ $submission->form_code }}</strong> form to you for action.</p>

        @if($submission->title)
            <p style="background: #f9fafb; padding: 12px 16px; border-left: 4px solid #1d4ed8; border-radius: 4px;">
                <strong>Subject:</strong> {{ $submission->title }}
            </p>
        @endif

        <div style="margin: 28px 0;">
            <a href="{{ $showUrl }}"
               style="background: #1d4ed8; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600;">
                Open form
            </a>
        </div>

        <p style="color: #6b7280; font-size: 13px;">
            You're receiving this email because the form was routed to you through the institution's forms workflow.
        </p>
    </div>
</body>
</html>
