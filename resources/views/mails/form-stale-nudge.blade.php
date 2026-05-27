<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $escalated ? 'Escalation' : 'Reminder' }} — {{ $submission->reference }}</title>
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
        .eyebrow--warn   { color: #92400e; font-weight: 600; }
        .eyebrow--danger { color: #b91c1c; font-weight: 600; }

        .headline { font-size: 26px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.02em; line-height: 1.2; margin: 0 0 4px; }
        .subline { font-size: 13px; color: #8a8780; font-weight: 400; margin: 0 0 18px; }

        .stale-banner { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-radius: 12px; margin: 8px 0 18px; }
        .stale-banner--warn   { background: #fef3c7; border: 1.5px solid #fde68a; }
        .stale-banner--danger { background: #fef2f2; border: 1.5px solid #fecaca; }
        .stale-banner__icon { width: 32px; height: 32px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-weight: 700; font-size: 13px; flex-shrink: 0; }
        .stale-banner--warn   .stale-banner__icon { background: #f59e0b; color: #fff; }
        .stale-banner--danger .stale-banner__icon { background: #dc2626; color: #fff; }
        .stale-banner__copy { font-size: 13.5px; line-height: 1.45; }
        .stale-banner--warn   .stale-banner__copy { color: #78350f; }
        .stale-banner--danger .stale-banner__copy { color: #7f1d1d; }
        .stale-banner__copy strong { font-weight: 700; }

        .divider { border: none; border-top: 1px solid #ebeae3; margin: 18px 0; }

        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: 7px 0; font-size: 13.5px; vertical-align: top; }
        .kv td.k { color: #8a8780; font-weight: 400; width: 42%; }
        .kv td.v { color: #1a1a1a; font-weight: 500; text-align: right; }

        .section-title { font-size: 16px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.01em; margin: 0 0 14px; }
        .section-sub { font-size: 13px; color: #6b6862; font-weight: 400; line-height: 1.55; margin: 0 0 14px; }

        .cta-wrap { padding-top: 6px; }
        .cta { display: inline-block; background: #1a1a1a; color: #ffffff !important; padding: 11px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; }
        .cta:hover { background: #2d2d2d; }

        .hint { font-size: 12.5px; color: #8a8780; padding: 14px 0 0; line-height: 1.5; }
        .hint strong { color: #1a1a1a; font-weight: 600; }

        .foot { text-align: center; padding: 10px 14px 0; font-size: 12px; color: #8a8780; font-weight: 400; line-height: 1.5; }

        @media (max-width: 600px) {
            .wrap { padding: 20px 10px; }
            .card { padding: 22px 20px; border-radius: 12px; }
            .headline { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <div class="container">

            <div class="brand">
                <img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1761222538/cug_logo_new_e9d6v9.jpg"
                     alt="Catholic University of Ghana"
                     class="brand-logo">
                <div class="brand-tagline">Home of Academic, Technical &amp; Moral Excellence</div>
            </div>

            <div class="card">
                <div class="eyebrow {{ $escalated ? 'eyebrow--danger' : 'eyebrow--warn' }}">
                    {{ $submission->form_code }} · {{ $escalated ? 'Escalation' : 'Reminder' }}
                </div>
                <h1 class="headline">
                    @if($escalated)
                        This form has been waiting {{ $staleDays }} days.
                    @else
                        Friendly nudge — this form is waiting on you.
                    @endif
                </h1>
                <p class="subline">Reference {{ $submission->reference }}</p>

                <div class="stale-banner {{ $escalated ? 'stale-banner--danger' : 'stale-banner--warn' }}">
                    <span class="stale-banner__icon">{{ $staleDays }}d</span>
                    <div class="stale-banner__copy">
                        @if($escalated)
                            No action has been recorded on this form in <strong>{{ $staleDays }} days</strong>.
                            The office head has been copied on this reminder.
                        @else
                            This form has been waiting on your action for <strong>{{ $staleDays }} days</strong>.
                            A quick sign-off or send-back keeps the workflow moving.
                        @endif
                    </div>
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
                    <tr>
                        <td class="k">Requisitioner</td>
                        <td class="v">{{ trim((optional($submission->creator)->first_name ?? '') . ' ' . (optional($submission->creator)->last_name ?? '')) }}</td>
                    </tr>
                    @if($submission->currentOffice)
                        <tr>
                            <td class="k">Held at</td>
                            <td class="v">{{ $submission->currentOffice->name }}</td>
                        </tr>
                    @endif
                    @if($submission->requisition_amount)
                        <tr>
                            <td class="k">Amount</td>
                            <td class="v">GhS {{ number_format($submission->requisition_amount, 2) }}</td>
                        </tr>
                    @endif
                </table>
            </div>

            <div class="card">
                <h2 class="section-title">Hello {{ $recipient->first_name ?? '' }},</h2>
                <p class="section-sub">
                    Please open the form below, complete your section, and either <strong style="color:#1a1a1a; font-weight:600;">Sign &amp; Forward</strong>
                    to the next office or <strong style="color:#1a1a1a; font-weight:600;">Send Back</strong> with a reason if it cannot proceed.
                </p>

                <div class="cta-wrap">
                    <a href="{{ $showUrl }}" class="cta">Open the form &rarr;</a>
                </div>

                <p class="hint">
                    <strong>On leave?</strong> Ask the head of your office to reassign this form to another active member —
                    they'll see a Reassign button on the form page.
                </p>
            </div>

            <div class="foot">
                You're receiving this reminder because this form is currently<br>
                assigned to you in the institution's forms workflow.
            </div>

        </div>
    </div>
</body>
</html>
