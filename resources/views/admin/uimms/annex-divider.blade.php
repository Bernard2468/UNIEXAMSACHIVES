<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif; color: #1f2937; }

        /* Letterhead strip (kept slim so the divider stays airy) */
        .lh { width: 100%; display: block; }

        .top-rule { border-top: 3px solid #16335b; margin: 26px 56px 0; }

        /* Centre block — pushed down so it sits in the upper-middle of the page */
        .wrap { text-align: center; padding-top: 200px; }
        .word {
            font-size: 12pt;
            letter-spacing: 0.55em;
            text-indent: 0.55em;
            color: #16335b;
            text-transform: uppercase;
        }
        .num {
            font-size: 52pt;
            font-weight: bold;
            color: #16335b;
            line-height: 1.05;
            margin: 6px 0 2px;
        }
        .divline { width: 64px; border: none; border-top: 2px solid #c7d0db; margin: 20px auto 18px; }
        .docname { font-size: 16pt; font-weight: bold; color: #111827; padding: 0 60px; line-height: 1.4; }
        .ref { font-size: 9.5pt; color: #6b7280; margin-top: 14px; letter-spacing: 0.04em; }
        .src { font-size: 9pt; color: #9ca3af; margin-top: 3px; }

        .bottom-rule { position: fixed; bottom: 52px; left: 56px; right: 56px; border-top: 1px solid #e5e7eb; }
        .foot {
            position: fixed; bottom: 32px; left: 0; right: 0;
            text-align: center; font-size: 8pt; color: #b0b7c0; letter-spacing: 0.06em;
        }
    </style>
</head>
<body>

@if($hasLetterhead && $letterheadBase64)
    <img src="{{ $letterheadBase64 }}" class="lh" alt="Letterhead">
@endif

<div class="top-rule"></div>

<div class="wrap">
    <div class="word">Annexure</div>
    <div class="num">{{ $number }}</div>
    <hr class="divline">
    <div class="docname">{{ pathinfo($name, PATHINFO_FILENAME) ?: $name }}</div>
    <div class="ref">Enclosed with Memorandum &nbsp;Ref&nbsp; {{ $memoRef }}</div>
    @if(!empty($label))
        <div class="src">{{ $label }}</div>
    @endif
</div>

<div class="bottom-rule"></div>
<div class="foot">{{ $memoRef }} &bull; Annexure {{ $number }} &bull; Confidential</div>

</body>
</html>
