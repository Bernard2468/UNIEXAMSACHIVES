<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <title>@yield('title', 'Catholic University of Ghana')</title>
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

        .eyebrow { font-size: 12.5px; color: #8a8780; font-weight: 400; margin-bottom: 8px; }
        .headline { font-size: 28px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.02em; line-height: 1.15; margin: 0 0 4px; }
        .subline { font-size: 13px; color: #8a8780; font-weight: 400; margin: 0 0 18px; }

        /* Status pill */
        .status-row { display: inline-block; padding: 4px 10px; border-radius: 99px; font-size: 11px; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; background: #f5f4ed; color: #6b6862; margin-bottom: 16px; }
        .status-row.is-amber { background: #fdf1d8; color: #946200; }
        .status-row.is-green { background: #e4f3e6; color: #1d7a32; }
        .status-row.is-red   { background: #fbe6e6; color: #b3261e; }

        .divider { border: none; border-top: 1px solid #ebeae3; margin: 18px 0; }

        /* Two-column key/value table */
        .kv { width: 100%; border-collapse: collapse; }
        .kv td { padding: 7px 0; font-size: 13.5px; vertical-align: top; }
        .kv td.k { color: #8a8780; font-weight: 400; width: 40%; }
        .kv td.v { color: #1a1a1a; font-weight: 500; text-align: right; }

        /* Section heading inside card */
        .section-title { font-size: 16px; font-weight: 700; color: #1a1a1a; letter-spacing: -0.01em; margin: 0 0 14px; }
        .section-sub { font-size: 13.5px; color: #6b6862; font-weight: 400; line-height: 1.55; margin: 0 0 14px; }

        /* Quoted / subject block */
        .quoted { background: #faf9f5; border-radius: 10px; padding: 14px 16px; margin: 8px 0 14px; }
        .quoted-label { font-size: 11.5px; color: #8a8780; font-weight: 500; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px; }
        .quoted-body { font-size: 14px; color: #1a1a1a; font-weight: 500; line-height: 1.45; }

        /* Rich memo body (user-authored HTML) */
        .memo-body { font-size: 14.5px; color: #2a2a2a; line-height: 1.65; }
        .memo-body p { margin: 0 0 12px; }
        .memo-body ul, .memo-body ol { margin: 12px 0; padding-left: 22px; }
        .memo-body li { margin: 4px 0; }
        .memo-body h1, .memo-body h2, .memo-body h3 { margin: 18px 0 8px; font-weight: 700; letter-spacing: -0.01em; line-height: 1.25; color: #1a1a1a; }
        .memo-body h1 { font-size: 19px; }
        .memo-body h2 { font-size: 17px; }
        .memo-body h3 { font-size: 15px; }
        .memo-body strong, .memo-body b { font-weight: 700; color: #1a1a1a; }
        .memo-body em, .memo-body i { font-style: italic; }
        .memo-body a { color: #1a4a9b; text-decoration: underline; }
        .memo-body img { max-width: 100%; height: auto; border-radius: 8px; }

        /* Attachments */
        .attach { background: #faf9f5; border-radius: 10px; padding: 14px 16px; margin: 16px 0 0; }
        .attach-title { font-size: 11.5px; color: #8a8780; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px; }
        .attach-item { font-size: 13.5px; color: #1a1a1a; font-weight: 500; padding: 4px 0; }
        .attach-item .sz { color: #8a8780; font-weight: 400; font-size: 12px; }

        /* CTA */
        .cta-wrap { padding-top: 6px; }
        .cta { display: inline-block; background: #1a1a1a; color: #ffffff !important; padding: 11px 22px; border-radius: 10px; text-decoration: none; font-size: 14px; font-weight: 600; letter-spacing: -0.005em; }
        .cta:hover { background: #2d2d2d; }
        .cta.is-red { background: #b3261e; }
        .cta.is-red:hover { background: #951f18; }

        /* Footer */
        .foot { text-align: center; padding: 10px 14px 0; font-size: 12px; color: #8a8780; font-weight: 400; line-height: 1.5; }
        .foot a { color: #6b6862; text-decoration: none; }

        @media (max-width: 600px) {
            .wrap { padding: 20px 10px; }
            .card { padding: 22px 20px; border-radius: 12px; }
            .headline { font-size: 24px; }
            .kv td { font-size: 13px; }
            .cta { display: block; text-align: center; }
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

            @yield('content')

            <!-- Footer -->
            <div class="foot">
                @hasSection('footnote')
                    @yield('footnote')
                @else
                    This is an automated message from the University Digital Transformation Suite.<br>
                    Please do not reply to this email.
                @endif
            </div>

        </div>
    </div>
</body>
</html>
