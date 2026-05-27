<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form sent back</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; padding: 32px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 32px;">
        <h2 style="color: #d97706; margin: 0 0 8px;">Your form was sent back for revision</h2>
        <p style="color: #6b7280; margin: 0 0 24px;">Reference: <strong style="color: #111827;">{{ $submission->reference }}</strong> · {{ $submission->form_code }}</p>

        <p>Your form needs revisions before it can continue through the workflow.</p>

        <p style="background: #fef3c7; padding: 12px 16px; border-left: 4px solid #d97706; border-radius: 4px;">
            <strong>Reason:</strong><br>
            {{ $reason }}
        </p>

        <div style="margin: 28px 0;">
            <a href="{{ $showUrl }}"
               style="background: #d97706; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600;">
                Open the form
            </a>
        </div>

        <p style="color: #6b7280; font-size: 13px;">
            Once you have updated the requisitioner section, you can sign and resubmit. The form will be routed through the offices again.
        </p>
    </div>
</body>
</html>
