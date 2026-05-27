<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form awaiting your action — {{ $submission->reference }}</title>
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

        /* Brand bar — centered logo + tagline above the cards */
        .brand { text-align: center; padding: 4px 0 22px; }
        .brand-logo { height: 46px; width: auto; max-width: 240px; display: inline-block; }
        .brand-tagline { font-size: 12px; color: #8a8780; font-weight: 500; margin-top: 8px; letter-spacing: 0.01em; }

        /* Card */
        .card { background: #ffffff; border-radius: 14px; padding: 26px 26px 24px; margin-bottom: 14px; }

        .eyebrow { font-size: 12.5px; color: #8a8780; font-weight: 400; margin-bottom: 8px; letter-spacing: 0; }
        .headline { font-size: 28px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.02em; line-height: 1.15; margin: 0 0 4px; }
        .subline { font-size: 13px; color: #8a8780; font-weight: 400; margin: 0 0 18px; }

        .status-row { display: inline-block; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; background: #f5f4ed; color: #6b6862; margin-bottom: 16px; }

        .divider { border: none; border-top: 1px solid #ebeae3; margin: 18px 0; }
        .divider--tight { margin: 14px 0; }

        /* Quick links row */
        .links { display: block; padding: 0; margin: 4px 0 0; font-size: 13.5px; }
        .links a { color: #1a1a1a; text-decoration: none; font-weight: 500; margin-right: 22px; }
        .links a:hover { text-decoration: underline; }

        /* Two-column key/value table */
        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: 7px 0; font-size: 13.5px; vertical-align: top; }
        .kv td.k { color: #8a8780; font-weight: 400; width: 42%; }
        .kv td.v { color: #1a1a1a; font-weight: 500; text-align: right; }

        /* Section heading inside card */
        .section-title { font-size: 16px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.01em; margin: 0 0 14px; }
        .section-sub { font-size: 13px; color: #6b6862; font-weight: 400; line-height: 1.5; margin: 0 0 14px; }

        /* Subject / quoted block */
        .quoted { background: #faf9f5; border-radius: 10px; padding: 14px 16px; margin: 8px 0 14px; }
        .quoted-label { font-size: 11.5px; color: #8a8780; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .quoted-body { font-size: 14px; color: #1a1a1a; font-weight: 500; line-height: 1.45; }

        /* CTA */
        .cta-wrap { padding-top: 6px; }
        .cta { display: inline-block; background: #1a1a1a; color: #ffffff !important; padding: 11px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: -0.005em; }
        .cta:hover { background: #2d2d2d; }

        /* Footer */
        .foot { text-align: center; padding: 10px 14px 0; font-size: 12px; color: #8a8780; font-weight: 400; line-height: 1.5; }
        .foot a { color: #6b6862; text-decoration: none; }

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
                <div class="eyebrow">{{ $submission->form_code }} · Awaiting your action</div>
                <h1 class="headline">A form needs your signature.</h1>
                <p class="subline">Forwarded {{ now()->format('M j, Y') }}</p>

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
                    <tr>
                        <td class="k">Forwarded by</td>
                        <td class="v">{{ trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')) }}</td>
                    </tr>
                    <tr>
                        <td class="k">Assigned to</td>
                        <td class="v">{{ trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')) }}</td>
                    </tr>
                    @if($submission->requisition_amount)
                        <tr>
                            <td class="k">Amount</td>
                            <td class="v">GhS {{ number_format($submission->requisition_amount, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <!-- Action card -->
            <div class="card">
                <h2 class="section-title">Hello {{ $recipient->first_name ?? '' }},</h2>
                <p class="section-sub">
                    <strong style="color:#1a1a1a; font-weight:600;">{{ trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')) }}</strong>
                    has forwarded a <strong style="color:#1a1a1a; font-weight:600;">{{ $submission->form_code }}</strong> form
                    to you. Please review your section, sign it, and forward it to the next office.
                </p>

                @if(!empty($submission->title))
                    <div class="quoted">
                        <div class="quoted-label">Subject</div>
                        <div class="quoted-body">{{ $submission->title }}</div>
                    </div>
                @endif

                <div class="cta-wrap">
                    <a href="{{ $showUrl }}" class="cta">Open the form &rarr;</a>
                </div>
            </div>

            <!-- Footer -->
            <div class="foot">
                You're receiving this email because the form was routed to you<br>
                through the institution's forms workflow.
            </div>

        </div>
    </div>
</body>
</html>
