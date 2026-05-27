<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your form was sent back — {{ $submission->reference }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Helvetica, Arial, sans-serif;
            background-color: #f5f4ed;
            color: #1a1a1a;
            line-height: 1.5;
            -webkit-text-size-adjust: 100%;
            -webkit-font-smoothing: antialiased;
        }
        .wrap { width: 100%; background-color: #f5f4ed; padding: 28px 16px; }
        .container { max-width: 560px; margin: 0 auto; }

        .brand { text-align: center; padding: 4px 0 22px; }
        .brand-logo { height: 46px; width: auto; max-width: 240px; display: inline-block; }
        .brand-tagline { font-size: 12px; color: #8a8780; font-weight: 500; margin-top: 8px; letter-spacing: 0.01em; }

        .card { background: #ffffff; border-radius: 14px; padding: 26px 26px 24px; margin-bottom: 14px; }

        .eyebrow { font-size: 12.5px; color: #8a8780; font-weight: 400; margin-bottom: 8px; }
        .headline { font-size: 28px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.02em; line-height: 1.15; margin: 0 0 4px; }
        .subline { font-size: 13px; color: #8a8780; font-weight: 400; margin: 0 0 18px; }

        .divider { border: none; border-top: 1px solid #ebeae3; margin: 18px 0; }

        .links { padding: 0; margin: 4px 0 0; font-size: 13.5px; }
        .links a { color: #1a1a1a; text-decoration: none; font-weight: 500; margin-right: 22px; }
        .links a:hover { text-decoration: underline; }

        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: 7px 0; font-size: 13.5px; vertical-align: top; }
        .kv td.k { color: #8a8780; font-weight: 400; width: 42%; }
        .kv td.v { color: #1a1a1a; font-weight: 500; text-align: right; }

        .section-title { font-size: 16px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.01em; margin: 0 0 14px; }
        .section-sub { font-size: 13.5px; color: #4b4844; font-weight: 400; line-height: 1.55; margin: 0 0 14px; }

        .pill-row { display: inline-flex; align-items: center; gap: 6px; padding: 4px 11px; border-radius: 99px; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; background: #fdf4e8; color: #92400e; margin-bottom: 14px; }
        .pill-dot { width: 6px; height: 6px; border-radius: 50%; background: #d97706; display: inline-block; }

        .reason-box { background: #faf9f5; border-radius: 10px; padding: 16px 18px; margin: 4px 0 14px; }
        .reason-label { font-size: 11.5px; color: #8a8780; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px; }
        .reason-body { font-size: 14px; color: #1a1a1a; font-weight: 500; line-height: 1.55; white-space: pre-wrap; }

        .cta-wrap { padding-top: 6px; }
        .cta { display: inline-block; background: #1a1a1a; color: #ffffff !important; padding: 11px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .cta:hover { background: #2d2d2d; }

        .foot { text-align: center; padding: 10px 14px 0; font-size: 12px; color: #8a8780; font-weight: 400; line-height: 1.5; }

        @media (max-width: 600px) {
            .wrap { padding: 20px 10px; }
            .card { padding: 22px 20px; border-radius: 12px; }
            .headline { font-size: 24px; }
            .kv td { font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="container">

            <!-- Brand bar (centered logo + tagline) -->
            <div class="brand">
                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1761222538/cug_logo_new_e9d6v9.jpg"
                     alt="Catholic University of Ghana"
                     class="brand-logo">
                <div class="brand-tagline">Home of Academic, Technical &amp; Moral Excellence</div>
            </div>

            <!-- Primary card -->
            <div class="card">
                <div class="eyebrow">{{ $submission->form_code }} · Sent back for revision</div>
                <h1 class="headline">Your form needs revisions.</h1>
                <p class="subline">Sent back {{ optional($submission->rejected_at ?? $submission->updated_at)->format('M j, Y') }}</p>

                <div class="pill-row">
                    <span class="pill-dot"></span>
                    Action required
                </div>

                <hr class="divider">

                <table class="kv">
                    <tr>
                        <td class="k">Reference</td>
                        <td class="v">{{ $submission->reference }}</td>
                    </tr>
                    <tr>
                        <td class="k">Form</td>
                        <td class="v">{{ $submission->form_code }} — {{ optional($submission->definition())->title() ?? $submission->form_code }}</td>
                    </tr>
                    @if($submission->requisition_amount)
                        <tr>
                            <td class="k">Amount</td>
                            <td class="v">GhS {{ number_format($submission->requisition_amount, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <!-- Reason card -->
            <div class="card">
                <h2 class="section-title">Reason for revision</h2>
                <div class="reason-box">
                    <div class="reason-label">Comment</div>
                    <div class="reason-body">{{ $reason }}</div>
                </div>

                <p class="section-sub">
                    Once you have updated the relevant section, sign the form again and it will route through the offices.
                </p>

                <div class="cta-wrap">
                    <a href="{{ $showUrl }}" class="cta">Open the form &rarr;</a>
                </div>
            </div>

            <!-- Footer -->
            <div class="foot">
                You're receiving this email because you submitted this form<br>
                through the institution's forms workflow.
            </div>

        </div>
    </div>
</body>
</html>
