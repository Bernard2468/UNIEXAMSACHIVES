<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Memo – {{ $memo->reference ?? $memo->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'DejaVu Sans', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #1f2937;
            background: #fff;
        }

        /* ── Letterhead ── */
        .letterhead-band { width: 100%; margin-bottom: 0; }
        .letterhead-img  { width: 100%; height: auto; display: block; }

        /* ── Page wrapper ── */
        .page-body { padding: 20px 46px 40px 46px; }

        /* ── Masthead ── */
        .masthead { text-align: center; padding: 14px 0 4px; }
        .masthead .title {
            font-size: 13pt;
            font-weight: bold;
            color: #16335b;
            letter-spacing: 0.42em;
            text-indent: 0.42em; /* re-centre after trailing letter-spacing */
        }
        .rule-strong { border: none; border-top: 2px solid #16335b; margin: 10px 0 1px; }
        .rule-hair   { border: none; border-top: 0.6px solid #c7d0db; margin: 0; }

        /* ── Meta block (Ref / Date / From / To …) ── */
        .meta { width: 100%; border-collapse: collapse; margin-top: 14px; }
        .meta td { padding: 4px 0; vertical-align: top; }
        .meta .k {
            width: 92px;
            text-transform: uppercase;
            font-size: 8pt;
            letter-spacing: 0.11em;
            color: #6b7280;
            font-weight: bold;
            padding-top: 3px;
            white-space: nowrap;
        }
        .meta .v       { color: #111827; font-size: 10.5pt; line-height: 1.5; }
        .meta .subject { font-weight: bold; color: #16335b; }
        .meta .muted   { color: #9ca3af; font-size: 8.5pt; }

        /* ── Section heading (consistent everywhere) ── */
        .sec {
            font-size: 8.5pt;
            font-weight: bold;
            color: #16335b;
            text-transform: uppercase;
            letter-spacing: 0.13em;
            border-bottom: 1px solid #16335b;
            padding-bottom: 4px;
            margin: 26px 0 14px;
        }

        /* ── Memo body ── */
        .body          { font-size: 11pt; line-height: 1.75; color: #1f2937; text-align: justify; }
        .body p        { margin-bottom: 10px; }
        .body table    { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 10.5pt; }
        .body table th { background: #16335b; color: #fff; padding: 8px 10px; text-align: left; font-weight: bold; border: 1px solid #16335b; }
        .body table td { padding: 7px 10px; border: 1px solid #c8d3df; vertical-align: top; }

        /* ── Minutes (people who minuted on the memo) ── */
        .msg { margin-bottom: 15px; padding-left: 13px; border-left: 2px solid #d8dee7; }
        .msg .who  { font-weight: bold; font-size: 10pt; color: #16335b; }
        .msg .when { font-size: 8.5pt; color: #9ca3af; margin-left: 8px; }
        .msg .text { font-size: 10.5pt; line-height: 1.6; color: #1f2937; margin-top: 3px; }
        .msg .text p { margin-bottom: 6px; }
        .empty-note { font-size: 10pt; color: #9ca3af; font-style: italic; }

        /* ── Inline attachments (compact, no boxes) ── */
        .att-image { text-align: center; margin: 12px 0; }
        .att-image img { max-width: 88%; max-height: 360px; border: 1px solid #e5e7eb; }
        .att-cap { font-size: 8pt; color: #9ca3af; margin-top: 4px; }
        .att-text {
            font-family: 'DejaVu Sans Mono', 'Courier New', monospace;
            font-size: 8.5pt;
            color: #374151;
            background: #f8fafc;
            border-left: 2px solid #d8dee7;
            padding: 9px 11px;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.55;
            margin: 9px 0;
        }
        .clip { font-size: 9pt; color: #6b7280; margin: 4px 0; line-height: 1.5; }
        .clip .nm  { color: #374151; font-weight: bold; }
        .clip .sz  { color: #9ca3af; font-size: 8.5pt; }
        .clip .tag { color: #16335b; font-weight: bold; }

        /* ── Enclosures list ── */
        .encl { width: 100%; border-collapse: collapse; margin-top: 4px; }
        .encl td { padding: 7px 0; border-bottom: 1px solid #eef1f4; font-size: 10pt; vertical-align: middle; }
        .encl .no { width: 30px; color: #9ca3af; font-weight: bold; font-size: 9pt; }
        .encl .nm { color: #111827; }
        .encl .sz { color: #9ca3af; font-size: 8.5pt; }
        .encl .ax { width: 96px; text-align: right; color: #16335b; font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.07em; }

        /* ── No-letterhead masthead banner ── */
        .plain-banner { padding: 6px 0 0; text-align: center; }
        .plain-banner .org { font-size: 9pt; color: #6b7280; letter-spacing: 0.18em; text-transform: uppercase; }

        /* ── Footer ── */
        .foot {
            margin-top: 34px;
            border-top: 1px solid #e5e7eb;
            padding-top: 9px;
            font-size: 8pt;
            color: #b0b7c0;
            text-align: center;
            letter-spacing: 0.05em;
        }
    </style>
</head>
<body>

@php
    // Name helper (handles null user / missing names).
    $personName = function ($u) {
        if (!$u) return 'Unknown';
        $n = trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? ''));
        return $n !== '' ? $n : ($u->name ?? $u->email ?? 'Unknown');
    };

    // While a Through memo is awaiting forward, the real To/Cc rows don't exist yet —
    // fall back to the addressed (held) lists so the printout still reads correctly.
    $throughToHeld = ($memo->hasThrough() && $toRecipients->isEmpty() && !empty($memo->selected_users))
        ? \App\Models\User::whereIn('id', $memo->selected_users)->get()
        : collect();
    $throughCcHeld = ($memo->hasThrough() && $ccRecipients->isEmpty() && !empty($memo->cc_users))
        ? \App\Models\User::whereIn('id', $memo->cc_users)->get()
        : collect();

    if ($throughToHeld->isNotEmpty()) {
        $displayToNames = $throughToHeld->map($personName);
    } elseif ($toRecipients->isNotEmpty()) {
        $displayToNames = $toRecipients->map(fn($r) => $personName($r->user));
    } else {
        $displayToNames = $memo->recipients
            ->filter(fn($r) => !in_array($r->recipient_role ?? 'to', ['cc', 'through'], true))
            ->map(fn($r) => $personName($r->user));
    }

    $displayCcNames = $throughCcHeld->isNotEmpty()
        ? $throughCcHeld->map($personName)
        : $ccRecipients->map(fn($r) => $personName($r->user));

    $throughName = $memo->hasThrough()
        ? ($memo->throughUser ? $personName($memo->throughUser) : '—')
        : null;

    $creatorName = trim(($memo->creator->first_name ?? '') . ' ' . ($memo->creator->last_name ?? ''));
    if (!$creatorName) $creatorName = $memo->creator->name ?? 'N/A';
    $creatorPosition = optional($memo->creator->position)->name;

    $memoRef = $memo->reference ?? ('MEMO/' . str_pad($memo->id, 4, '0', STR_PAD_LEFT));

    // Inline renderer. Documents converted to annexes are NOT shown here — they are
    // enumerated once in the Enclosures list and appended at the back. Only genuine
    // inline content (images, short text) renders inline; a non-converted or missing
    // file gets a one-line note so it is never silently dropped.
    $renderInline = function (array $att) {
        switch ($att['type'] ?? '') {
            case 'image':
                return '<div class="att-image"><img src="' . $att['data'] . '" alt="' . e($att['name']) . '"></div>';
            case 'text':
                return '<div class="att-text">' . $att['text'] . '</div>';
            case 'annex':
                return ''; // listed in Enclosures and appended as a full annex
            case 'missing':
                return '<div class="clip">&#9888; <span class="nm">' . e($att['name']) . '</span> &mdash; file not found on server</div>';
            default: // 'file' — a document that could not be converted/appended
                return '<div class="clip">&#128206; <span class="nm">' . e($att['name']) . '</span>'
                     . ($att['size'] ? ' <span class="sz">(' . e($att['size']) . ')</span>' : '')
                     . ' <span class="sz">&mdash; available in the system</span></div>';
        }
    };
@endphp

{{-- ══ LETTERHEAD BAND ══ --}}
@if($hasLetterhead && $letterheadBase64)
    <div class="letterhead-band">
        <img src="{{ $letterheadBase64 }}" class="letterhead-img" alt="Letterhead">
    </div>
@endif

<div class="page-body">

    {{-- ══ MASTHEAD ══ --}}
    <div class="masthead">
        @unless($hasLetterhead && $letterheadBase64)
            <div class="plain-banner"><div class="org">University Internal Memo Management System</div></div>
        @endunless
        <div class="title">INTERNAL MEMORANDUM</div>
    </div>
    <hr class="rule-strong">
    <hr class="rule-hair">

    {{-- ══ META BLOCK ══ --}}
    <table class="meta">
        <tr>
            <td class="k">Ref</td>
            <td class="v">{{ $memoRef }}</td>
        </tr>
        <tr>
            <td class="k">Date</td>
            <td class="v">{{ $memo->created_at ? $memo->created_at->format('d F Y') : date('d F Y') }}</td>
        </tr>
        <tr>
            <td class="k">From</td>
            <td class="v">@if($creatorPosition){{ $creatorPosition }} &mdash; @endif{{ $creatorName }}</td>
        </tr>
        <tr>
            <td class="k">To</td>
            <td class="v">@forelse($displayToNames as $name){{ $name }}@if(!$loop->last); @endif @empty All Recipients @endforelse</td>
        </tr>
        @if($throughName)
        <tr>
            <td class="k">Through</td>
            <td class="v">{{ $throughName }} <span class="muted">@if($memo->isThroughPending())(awaiting forward)@else(forwarded)@endif</span></td>
        </tr>
        @endif
        @if($displayCcNames->isNotEmpty())
        <tr>
            <td class="k">Cc</td>
            <td class="v">@foreach($displayCcNames as $name){{ $name }}@if(!$loop->last); @endif @endforeach</td>
        </tr>
        @endif
        <tr>
            <td class="k">Subject</td>
            <td class="v subject">{{ strtoupper($memo->subject ?? '') }}</td>
        </tr>
    </table>

    <hr class="rule-hair" style="margin-top:14px;">

    {{-- ══ MEMO BODY ══ --}}
    <div class="sec">Memorandum</div>
    <div class="body">
        {!! $memo->message ?? '' !!}
    </div>

    {{-- ══ MEMO-LEVEL ATTACHMENTS (inline content only; documents live under Enclosures) ══ --}}
    @php
        $memoInline = array_filter($processedAttachments, fn($a) => ($a['type'] ?? '') !== 'annex');
    @endphp
    @if(!empty($memoInline))
        <div class="sec">Attachments</div>
        @foreach($memoInline as $att){!! $renderInline($att) !!}@endforeach
    @endif

    {{-- ══ MINUTES (officials who minuted on this memo — text only; files are in Enclosures) ══ --}}
    <div class="sec">Minutes</div>
    @if(empty($processedReplies))
        <p class="empty-note">No minutes have been recorded on this memo.</p>
    @else
        @foreach($processedReplies as $item)
        <div class="msg">
            <div><span class="who">{{ $item['sender'] }}</span><span class="when">{{ $item['sent_at'] }}</span></div>
            @if($item['message'])
                <div class="text">{!! $item['message'] !!}</div>
            @endif
            @php $minInline = array_filter($item['attachments'] ?? [], fn($a) => ($a['type'] ?? '') !== 'annex'); @endphp
            @foreach($minInline as $att){!! $renderInline($att) !!}@endforeach
        </div>
        @endforeach
    @endif

    {{-- ══ ENCLOSURES (formal index of every appended document) ══ --}}
    @if(!empty($annexes))
    <div class="sec">Enclosures</div>
    <table class="encl">
        @foreach($annexes as $annex)
        <tr>
            <td class="no">{{ str_pad($annex['number'], 2, '0', STR_PAD_LEFT) }}</td>
            <td class="nm">{{ $annex['name'] }}@if(!empty($annex['label']))<span class="sz"> &mdash; {{ $annex['label'] }}</span>@endif</td>
            <td class="ax">Annexure {{ $annex['number'] }}</td>
        </tr>
        @endforeach
    </table>
    @endif

    {{-- ══ FOOTER ══ --}}
    <div class="foot">
        {{ $memoRef }} &bull; Generated {{ now()->format('d M Y, H:i') }} &bull; Confidential
    </div>

</div>
</body>
</html>
