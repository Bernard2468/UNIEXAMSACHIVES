<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Form completed</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f3f4f6; padding: 32px;">
    <div style="max-width: 600px; margin: 0 auto; background: #fff; border-radius: 10px; padding: 32px;">
        <h2 style="color: #10b981; margin: 0 0 8px;">Your form is fully approved</h2>
        <p style="color: #6b7280; margin: 0 0 24px;">Reference: <strong style="color: #111827;">{{ $submission->reference }}</strong> · {{ $submission->form_code }}</p>

        <p>Good news — all offices have signed your form.</p>

        @if($submission->title)
            <p style="background: #f9fafb; padding: 12px 16px; border-left: 4px solid #10b981; border-radius: 4px;">
                <strong>Subject:</strong> {{ $submission->title }}
            </p>
        @endif

        <div style="margin: 28px 0;">
            <a href="{{ $showUrl }}"
               style="background: #10b981; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block; font-weight: 600;">
                View signed form
            </a>
        </div>

        <p style="color: #6b7280; font-size: 13px;">
            You can download the complete signed PDF from the form page.
        </p>
    </div>
</body>
</html>
