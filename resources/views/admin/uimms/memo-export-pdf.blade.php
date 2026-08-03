<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Memo – {{ $memo->reference ?? $memo->id }}</title>
    <style>
        /* dompdf implements @page margins as the root <html> frame's own
           margins, merged BEFORE author rules — so a universal reset
           `* { margin: 0 }` matches <html> and silently wipes them to zero
           (dompdf 3.1.4 Stylesheet.php, "@page … applied to the very first
           root frame before processing other styles"). Keep the margin reset
           OFF the root element: box-sizing may stay universal, margins may not. */
        * { box-sizing: border-box; }
        body, div, span, p, h1, h2, h3, h4, h5, h6,
        table, tr, td, th, img, hr, ul, ol, li, blockquote { margin: 0; padding: 0; }

        /* Word-like page margins: 48pt (⅔") top so continuation pages don't
           start at the paper's edge, and a bottom margin so content never hugs
           the foot of the page. Sides stay 0 — .page-body carries the side
           padding and the letterhead band stays full-bleed. Units on every
           value, matching the proven forms-PDF @page rules. */
        @page { margin: 48pt 0pt 56pt 0pt; }

        body {
            /* Times core font (what MS Word memos use). It only covers cp1252 —
               no emoji/₵ glyphs — so decorative entities must stay out of markup. */
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            color: #111827;
            background: #fff;
        }

        /* ── Letterhead ── */
        /* -48pt cancels the @page top margin on page 1 exactly, so the band
           prints at the physical top of the sheet — same position as before
           the page margins were introduced. Must stay equal-and-opposite to
           the @page margin-top above. */
        .letterhead-band { width: 100%; margin: -48pt 0pt 0pt 0pt; }
        .letterhead-img  { width: 100%; height: auto; display: block; }

        /* ── Page wrapper ── */
        .page-body { padding: 8px 46px 24px 46px; }

        .rule-strong { border: none; border-top: 2px solid #16335b; margin: 8px 0 10px; }
        .rule-hair   { border: none; border-top: 0.6px solid #c7d0db; margin: 0; }

        /* ── Meta block (Ref / Date / From / To …) ── */
        .meta { width: 100%; border-collapse: collapse; margin-top: 6px; }
        /* dompdf lines a label up with its value ONLY when they are the SAME font-size:
           with equal metrics a plain top-aligned label cell and value cell share one
           baseline, so "To" sits exactly on its recipient's line. dompdf CANNOT
           baseline-align two cells of different sizes, and it mis-baselines an
           inline-block label (it uses the box's bottom edge) — both make a small label
           drift upward. So the label matches the value's 12pt and stays distinct by
           being bold, like the label column of a Word-typed memo. Multi-recipient
           To/Cc print one row per name; continuation rows leave the label cell empty so
           the names line up under the value column. */
        .meta td { padding: 2px 0; line-height: 1.4; vertical-align: top; }
        .meta .k {
            width: 96px;
            font-size: 12pt;
            color: #111827;
            font-weight: bold;
            white-space: nowrap;
        }
        .meta .v       { color: #111827; font-size: 12pt; word-wrap: break-word; overflow-wrap: break-word; }
        .meta .subject { font-weight: bold; color: #16335b; }
        .meta .muted   { color: #9ca3af; font-size: 9pt; }
        /* Ref stands alone, right-aligned; the &nbsp;s hold a gap before its value. */
        .meta td.ref    { text-align: right; }

        /* ── Section heading (consistent everywhere) ── */
        .sec {
            font-size: 9.5pt;
            font-weight: bold;
            color: #16335b;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            border-bottom: 1px solid #16335b;
            padding-bottom: 3px;
            margin: 12px 0 8px;
        }

        /* ── Memo body ── */
        /* word-wrap/overflow-wrap break very long unbroken strings (pasted URLs,
           no-space text) so they stay inside the page instead of running off it. */
        .body          { font-size: 12pt; line-height: 1.45; color: #111827; text-align: justify; word-wrap: break-word; overflow-wrap: break-word; }
        .body p        { margin-bottom: 8px; word-wrap: break-word; overflow-wrap: break-word; }
        .body table    { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 11pt; }
        .body table th { background: #16335b; color: #fff; padding: 8px 10px; text-align: left; font-weight: bold; border: 1px solid #16335b; }
        .body table td { padding: 7px 10px; border: 1px solid #c8d3df; vertical-align: top; word-wrap: break-word; overflow-wrap: break-word; }

        /* ── Minutes (official, signed Minute-To actions) ──
           Modelled on how officers minute a paper memo: each minute takes the
           next free space under the body — "To" line, remark, signature, name
           and date — and moves to the next page when it can't fit WHOLE.
           page-break-inside: avoid keeps a minute (and, for the first block,
           the MINUTES heading too) from being split or stranded at a page's
           foot; a block taller than a page still breaks rather than vanish. */
        .minute-block { page-break-inside: avoid; }
        .sec--minutes { margin-top: 26px; }
        .minute-tbl { width: 100%; border-collapse: collapse; }
        .minute-tbl td { vertical-align: top; padding: 5px 0 9px; }
        .minute-tbl .mno { width: 28px; font-weight: bold; }
        .minute-tbl .mlab { font-weight: bold; }
        .minute-tbl .mrem { margin-top: 1px; }
        .minute-tbl .msig { height: 42px; margin: 3px 0 0; display: block; }
        .minute-tbl .mwho { font-weight: bold; }
        .minute-tbl .mwhen { font-size: 10pt; color: #6b7280; }

        /* ── Discussion (informal chat thread — printed plainly, on its own page) ── */
        .page-break { page-break-before: always; }
        .msg { margin-bottom: 10px; page-break-inside: avoid; }
        .msg .who  { font-weight: bold; font-size: 11pt; color: #111827; }
        .msg .when { font-size: 9pt; color: #6b7280; margin-left: 8px; }
        .msg .text { font-size: 11pt; line-height: 1.45; color: #111827; margin-top: 2px; word-wrap: break-word; overflow-wrap: break-word; }
        .msg .text p { margin-bottom: 5px; }

        /* ── Inline attachments (compact, no boxes) ── */
        .att-image { text-align: center; margin: 10px 0; }
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
        .clip { font-size: 10pt; color: #6b7280; margin: 4px 0; line-height: 1.4; }
        .clip .nm  { color: #374151; font-weight: bold; }
        .clip .sz  { color: #9ca3af; font-size: 8.5pt; }
        .clip .tag { color: #16335b; font-weight: bold; }

        /* ── Enclosures list ── */
        .encl { width: 100%; border-collapse: collapse; margin-top: 2px; }
        .encl td { padding: 4px 0; border-bottom: 1px solid #eef1f4; font-size: 10.5pt; vertical-align: middle; }
        .encl .no { width: 30px; color: #9ca3af; font-weight: bold; font-size: 9pt; }
        .encl .nm { color: #111827; }
        .encl .sz { color: #9ca3af; font-size: 8.5pt; }
        .encl .ax { width: 96px; text-align: right; color: #16335b; font-size: 8pt; font-weight: bold; text-transform: uppercase; letter-spacing: 0.07em; }

        /* ── No-letterhead masthead banner ── */
        .plain-banner { padding: 6px 0 0; text-align: center; }
        .plain-banner .org { font-size: 9pt; color: #6b7280; letter-spacing: 0.18em; text-transform: uppercase; }

        /* ── Footer ── */
        .foot {
            margin-top: 16px;
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

    // Bulk audiences (all staff, a staff category, a leadership pool, one or more
    // departments, or a committee) collapse the "To" line to a single label instead
    // of listing everyone; individually addressed ("selected"/Through) memos keep names.
    $audienceLabel = $memo->audienceLabel();
    $toLine = $audienceLabel
        ?? ($displayToNames->isNotEmpty() ? $displayToNames->implode('; ') : 'All Recipients');

    $displayCcNames = $throughCcHeld->isNotEmpty()
        ? $throughCcHeld->map($personName)
        : $ccRecipients->map(fn($r) => $personName($r->user));

    // Each To/Cc recipient prints on its own meta row (first row carries the label,
    // the rest an empty label-width spacer), so build the lists as plain line arrays.
    $toLines = $audienceLabel
        ? [$audienceLabel]
        : ($displayToNames->isNotEmpty() ? $displayToNames->values()->all() : ['All Recipients']);
    $ccLines = $displayCcNames->values()->all();

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
                return '<div class="clip"><span class="nm">' . e($att['name']) . '</span> &mdash; file not found on server</div>';
            default: // 'file' — a document that could not be converted/appended
                return '<div class="clip">Attachment: <span class="nm">' . e($att['name']) . '</span>'
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

    {{-- No masthead: the letterhead already carries the "Internal Memorandum" title.
         Without a letterhead, a slim system label stands in so the memo isn't headerless. --}}
    @unless($hasLetterhead && $letterheadBase64)
        <div class="plain-banner"><div class="org">University Internal Memo Management System</div></div>
        <hr class="rule-hair">
    @endunless

    {{-- ══ META BLOCK ══ --}}
    <table class="meta">
        <tr>
            <td class="ref" colspan="2"><span class="k">Ref&nbsp;&nbsp;</span><span class="v">{{ $memoRef }}</span></td>
        </tr>
        @foreach($toLines as $line)
        <tr>
            <td class="k">{{ $loop->first ? 'To' : '' }}</td>
            <td class="v">{{ $line }}</td>
        </tr>
        @endforeach
        <tr>
            <td class="k">From</td>
            <td class="v">@if($creatorPosition){{ $creatorPosition }} &mdash; @endif{{ $creatorName }}</td>
        </tr>
        @if($throughName)
        <tr>
            <td class="k">Through</td>
            <td class="v">{{ $throughName }} <span class="muted">@if($memo->isThroughPending())(awaiting forward)@else(forwarded)@endif</span></td>
        </tr>
        @endif
        @if(!empty($ccLines))
        @foreach($ccLines as $line)
        <tr>
            <td class="k">{{ $loop->first ? 'Cc' : '' }}</td>
            <td class="v">{{ $line }}</td>
        </tr>
        @endforeach
        @endif
        <tr>
            <td class="k">Date</td>
            <td class="v">{{ $memo->created_at ? $memo->created_at->format('d F Y') : date('d F Y') }}</td>
        </tr>
        <tr>
            <td class="k">Subject</td>
            <td class="v subject">{{ strtoupper($memo->subject ?? '') }}</td>
        </tr>
    </table>

    <hr class="rule-strong">

    {{-- ══ MEMO BODY ══ --}}
    <div class="body">
        {!! $memoBodyHtml ?? ($memo->message ?? '') !!}
    </div>

    {{-- ══ MEMO-LEVEL ATTACHMENTS (inline content only; documents live under Enclosures) ══ --}}
    @php
        $memoInline = array_filter($processedAttachments, fn($a) => ($a['type'] ?? '') !== 'annex');
    @endphp
    @if(!empty($memoInline))
        <div class="sec">Attachments</div>
        @foreach($memoInline as $att){!! $renderInline($att) !!}@endforeach
    @endif

    {{-- ══ MINUTES (official signed Minute-To actions) ══ --}}
    {{-- Each minute is its own unbreakable block; the heading rides inside the
         first block so it can never be left alone at the bottom of a page. --}}
    @if(!empty($processedMinutes))
        @foreach($processedMinutes as $m)
        <div class="minute-block">
            @if($loop->first)
                <div class="sec sec--minutes">Minutes</div>
            @endif
            <table class="minute-tbl">
                <tr>
                    <td class="mno">{{ $m['number'] }}.</td>
                    <td>
                        <div><span class="mlab">To:</span> {{ $m['to'] !== '' ? $m['to'] : '—' }}</div>
                        @if($m['remark'] !== '')
                            <div class="mrem">{{ $m['remark'] }}</div>
                        @endif
                        @if($m['signature'])
                            <img class="msig" src="{{ $m['signature'] }}" alt="Signature of {{ $m['signer'] }}">
                        @endif
                        <div class="mwho">{{ $m['signer'] }}@if($m['position']) &mdash; {{ $m['position'] }}@endif</div>
                        <div class="mwhen">{{ $m['when'] }}</div>
                    </td>
                </tr>
            </table>
        </div>
        @endforeach
    @endif

    {{-- ══ ENCLOSURES (formal index of every appended document) ══ --}}
    {{-- Wrapped so the heading + index list stay together instead of the
         heading being stranded at the foot of a page (list is always short). --}}
    @if(!empty($annexes))
    <div style="page-break-inside: avoid;">
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
    </div>
    @endif

    {{-- ══ DISCUSSION (informal chat thread — starts on its own page so the
         official memo + minutes stand alone, like the paper original) ══ --}}
    @php
        // Print a discussion entry only if it carries an actual remark or
        // inline content. An entry that was just a document upload (now listed
        // under Enclosures) would otherwise render as an empty, name-only row.
        $hasRemark = fn ($item) => trim(strip_tags($item['message'] ?? '')) !== '';
        $hasInline = fn ($item) => !empty(array_filter($item['attachments'] ?? [], fn($a) => ($a['type'] ?? '') !== 'annex'));
        $renderableDiscussion = array_values(array_filter($processedReplies, fn ($item) => $hasRemark($item) || $hasInline($item)));
    @endphp
    @if(!empty($renderableDiscussion))
        <div class="page-break"></div>
        <div class="sec">Discussion</div>
        @foreach($renderableDiscussion as $item)
        <div class="msg">
            <div><span class="who">{{ $item['sender'] }}</span><span class="when">{{ $item['sent_at'] }}</span></div>
            @if($hasRemark($item))
                {{-- Message is pre-sanitised for print (icons/pills stripped to clean
                     text); nl2br restores the meaningful line breaks. --}}
                <div class="text">{!! nl2br($item['message']) !!}</div>
            @endif
            @foreach(array_filter($item['attachments'] ?? [], fn($a) => ($a['type'] ?? '') !== 'annex') as $att){!! $renderInline($att) !!}@endforeach
        </div>
        @endforeach
    @endif

    {{-- ══ FOOTER ══ --}}
    <div class="foot">
        {{ $memoRef }} &bull; Generated {{ now()->format('d M Y, H:i') }} &bull; Confidential
    </div>

</div>
</body>
</html>
