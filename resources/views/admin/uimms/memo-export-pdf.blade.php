<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Memo – {{ $memo->reference ?? $memo->id }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 11pt;
            color: #1a1a1a;
            background: #fff;
        }

        /* ── Letterhead ── */
        .letterhead-band { width: 100%; margin-bottom: 0; }
        .letterhead-img  { width: 100%; height: auto; display: block; }

        /* ── Page wrapper ── */
        .page-body { padding: 24px 40px 40px 40px; }

        /* ── Formal header table ── */
        .formal-header { width: 100%; border-collapse: collapse; margin-bottom: 18px; }
        .formal-header td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10.5pt;
            border-bottom: 1px solid #e5e5e5;
        }
        .fh-label         { font-weight: bold; color: #374151; width: 90px; white-space: nowrap; }
        .fh-colon         { width: 10px; color: #374151; font-weight: bold; }
        .fh-value         { color: #111827; }
        .fh-subject-value { color: #111827; font-weight: bold; font-size: 11pt; }

        /* ── Divider ── */
        .formal-divider { border: none; border-top: 2.5px solid #1e3a5f; margin: 18px 0 20px 0; }
        .section-divider { border: none; border-top: 1.5px solid #d1d5db; margin: 28px 0 20px 0; }

        /* ── Memo body ── */
        .memo-body          { line-height: 1.7; font-size: 11pt; color: #111827; text-align: justify; }
        .memo-body p        { margin-bottom: 10px; }
        .memo-body table    { width: 100%; border-collapse: collapse; margin: 14px 0; font-size: 10.5pt; }
        .memo-body table th { background: #1e3a5f; color: #fff; padding: 8px 10px; text-align: left; font-weight: bold; border: 1px solid #1e3a5f; }
        .memo-body table td { padding: 7px 10px; border: 1px solid #c8d3df; vertical-align: top; }

        /* ── Section heading ── */
        .section-heading {
            font-size: 9.5pt;
            font-weight: bold;
            color: #1e3a5f;
            text-transform: uppercase;
            letter-spacing: 0.07em;
            margin-bottom: 14px;
            padding-bottom: 5px;
            border-bottom: 2px solid #1e3a5f;
        }

        /* ── Attachments ── */
        .attachments-section { margin-top: 28px; }
        .attachment-item {
            margin-bottom: 18px;
            border: 1px solid #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        .attachment-header {
            background: #f3f4f6;
            padding: 7px 12px;
            font-size: 9.5pt;
            color: #374151;
        }
        .attachment-name { font-weight: bold; color: #111827; }
        .attachment-size { color: #6b7280; font-size: 8.5pt; }
        .attachment-body { padding: 10px 12px; }
        .attachment-image {
            max-width: 100%;
            max-height: 400px;
            display: block;
        }
        .attachment-text {
            font-family: DejaVu Sans Mono, Courier New, monospace;
            font-size: 9pt;
            color: #1f2937;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.5;
            background: #f9fafb;
            padding: 8px;
            border: 1px solid #e5e7eb;
        }
        .attachment-pdf-text {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 9.5pt;
            color: #1f2937;
            white-space: pre-wrap;
            word-break: break-word;
            line-height: 1.65;
            background: #fffdf5;
            padding: 10px 12px;
            border: 1px solid #fde68a;
        }
        .attachment-unavailable {
            font-size: 9.5pt;
            color: #6b7280;
            font-style: italic;
            padding: 4px 0;
        }
        .merged-ref-note {
            font-size: 9.5pt;
            color: #166534;
            background: #f0fdf4;
            border-left: 3px solid #22c55e;
            padding: 6px 10px;
            margin-top: 4px;
        }
        .appended-section {
            margin-top: 32px;
            padding: 14px 18px;
            background: #fffbeb;
            border: 1px solid #fde68a;
            border-radius: 4px;
        }
        .appended-section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #92400e;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }
        .appended-list {
            font-size: 9.5pt;
            color: #1f2937;
            margin: 0;
            padding-left: 20px;
        }
        .appended-list li { margin-bottom: 4px; }
        .pdf-label {
            font-size: 9.5pt;
            color: #1e40af;
            font-weight: bold;
            margin-bottom: 6px;
        }
        /* ── Formatted Word / HTML document content ── */
        .doc-html-content {
            font-family: DejaVu Sans, Arial, sans-serif;
            font-size: 10.5pt;
            line-height: 1.7;
            color: #111827;
            padding: 8px 4px;
        }
        .doc-html-content p     { margin-bottom: 8px; }
        .doc-html-content h1    { font-size: 15pt; font-weight: bold; margin: 10px 0 6px; color: #1e3a5f; }
        .doc-html-content h2    { font-size: 13pt; font-weight: bold; margin: 10px 0 6px; color: #1e3a5f; }
        .doc-html-content h3    { font-size: 11.5pt; font-weight: bold; margin: 8px 0 4px; color: #374151; }
        .doc-html-content h4,
        .doc-html-content h5,
        .doc-html-content h6    { font-size: 10.5pt; font-weight: bold; margin: 6px 0 4px; }
        .doc-html-content b,
        .doc-html-content strong { font-weight: bold; }
        .doc-html-content i,
        .doc-html-content em     { font-style: italic; }
        .doc-html-content u      { text-decoration: underline; }
        .doc-html-content ul,
        .doc-html-content ol     { margin: 6px 0 6px 20px; }
        .doc-html-content li     { margin-bottom: 3px; }
        .doc-html-content table  { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 10pt; }
        .doc-html-content th     { background: #1e3a5f; color: #fff; padding: 6px 8px; font-weight: bold; border: 1px solid #1e3a5f; text-align: left; }
        .doc-html-content td     { padding: 5px 8px; border: 1px solid #c8d3df; vertical-align: top; }
        .doc-html-content tr:nth-child(even) td { background: #f8fafc; }

        /* ── Chat thread ── */
        .chat-section { margin-top: 32px; }
        .chat-message {
            margin-bottom: 20px;
            border: 1px solid #dbeafe;
            border-radius: 6px;
            overflow: hidden;
        }
        .chat-message-header {
            background: #eff6ff;
            padding: 8px 14px;
            border-bottom: 1px solid #dbeafe;
        }
        .chat-sender { font-weight: bold; font-size: 10pt; color: #1e3a5f; }
        .chat-time   { font-size: 8.5pt; color: #6b7280; margin-left: 10px; }
        .chat-message-body {
            padding: 10px 14px;
            font-size: 10.5pt;
            line-height: 1.65;
            color: #111827;
        }
        .chat-message-body p { margin-bottom: 8px; }
        .chat-attachments    { padding: 0 14px 12px 14px; }
        .chat-attach-heading { font-size: 8.5pt; font-weight: bold; color: #6b7280; text-transform: uppercase; margin-bottom: 8px; letter-spacing: 0.04em; }

        /* ── No-letterhead header banner ── */
        .plain-header-banner  { background: #1e3a5f; color: #fff; padding: 18px 40px 14px 40px; margin-bottom: 0; }
        .plain-header-title   { font-size: 16pt; font-weight: bold; letter-spacing: 0.03em; }
        .plain-header-sub     { font-size: 9.5pt; opacity: 0.85; margin-top: 3px; }

        /* ── Footer ── */
        .pdf-footer {
            margin-top: 40px;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
            font-size: 8.5pt;
            color: #9ca3af;
            text-align: center;
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
        // Exclude the intermediary ('through') and Cc rows from the "To" line.
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

    // Formal memo "From": lead with the sender's office/position, then name.
    $creatorPosition = optional($memo->creator->position)->name;

    // Helper: render a single processed attachment entry
    $renderAttachment = function(array $att) {
        $out = '';
        $out .= '<div class="attachment-item">';
        $out .= '<div class="attachment-header">';
        $out .= '<span class="attachment-name">' . e($att['name']) . '</span>';
        if ($att['size']) $out .= '&nbsp;<span class="attachment-size">(' . e($att['size']) . ')</span>';
        $out .= '</div>';
        $out .= '<div class="attachment-body">';

        switch ($att['type'] ?? '') {
            case 'image':
                $out .= '<img src="' . $att['data'] . '" class="attachment-image" alt="' . e($att['name']) . '">';
                break;
            case 'text':
                $out .= '<div class="attachment-text">' . $att['text'] . '</div>';
                break;
            case 'missing':
                $out .= '<div class="attachment-unavailable">&#9888; File not found on server: ' . e($att['name']) . '</div>';
                break;
            case 'annex':
                // Document was converted to PDF and appended at the end of this export.
                $out .= '<div class="merged-ref-note">&#128206; Full document appended as <strong>Annex ' . (int) ($att['annex_number'] ?? 0) . '</strong> at the end of this export.</div>';
                break;
            default:
                // PDF, Word and any other document that could not be converted: listed by
                // name only (header above shows the filename).
                $out .= '<div class="attachment-unavailable">&#128206; Document attached &mdash; open the original file in the system to view its contents.</div>';
        }

        $out .= '</div></div>';
        return $out;
    };
@endphp

{{-- ══ LETTERHEAD BAND ══ --}}
@if($hasLetterhead && $letterheadBase64)
    <div class="letterhead-band">
        <img src="{{ $letterheadBase64 }}" class="letterhead-img" alt="Letterhead">
    </div>
@else
    <div class="plain-header-banner">
        <div class="plain-header-title">INTERNAL MEMO</div>
        <div class="plain-header-sub">University Internal Memo Management System (UIMMS)</div>
    </div>
@endif

<div class="page-body">

    {{-- ══ FORMAL HEADER TABLE ══ --}}
    <table class="formal-header">
        <tr>
            <td class="fh-label">Ref</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">{{ $memo->reference ?? ('MEMO/' . str_pad($memo->id, 4, '0', STR_PAD_LEFT)) }}</td>
        </tr>
        <tr>
            <td class="fh-label">Date</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">{{ $memo->created_at ? $memo->created_at->format('d F Y') : date('d F Y') }}</td>
        </tr>
        <tr>
            <td class="fh-label">From</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">
                @if($creatorPosition){{ $creatorPosition }} &mdash; @endif{{ $creatorName }}
            </td>
        </tr>
        <tr>
            <td class="fh-label">To</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">
                @forelse($displayToNames as $name)
                    {{ $name }}@if(!$loop->last); @endif
                @empty
                    All Recipients
                @endforelse
            </td>
        </tr>
        @if($throughName)
        <tr>
            <td class="fh-label">Through</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">
                {{ $throughName }}@if($memo->isThroughPending()) &nbsp;(awaiting forward)@else &nbsp;(forwarded)@endif
            </td>
        </tr>
        @endif
        @if($displayCcNames->isNotEmpty())
        <tr>
            <td class="fh-label">Cc</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">
                @foreach($displayCcNames as $name)
                    {{ $name }}@if(!$loop->last); @endif
                @endforeach
            </td>
        </tr>
        @endif
        <tr>
            <td class="fh-label">Subject</td>
            <td class="fh-colon">:</td>
            <td class="fh-subject-value">{{ strtoupper($memo->subject ?? '') }}</td>
        </tr>
    </table>

    <hr class="formal-divider">

    {{-- ══ MEMO BODY ══ --}}
    <div class="section-heading">Memo Content</div>
    <div class="memo-body">
        {!! $memo->message ?? '' !!}
    </div>

    {{-- ══ MEMO-LEVEL ATTACHMENTS ══ --}}
    @if(!empty($processedAttachments))
    <div class="attachments-section">
        <div class="section-heading" style="margin-top:22px;">
            Memo Attachments ({{ count($processedAttachments) }})
        </div>
        @foreach($processedAttachments as $att)
            {!! $renderAttachment($att) !!}
        @endforeach
    </div>
    @endif

    {{-- ══ CHAT THREAD ══ --}}
    <hr class="section-divider">
    <div class="chat-section">
        <div class="section-heading">
            Chat Thread &mdash; {{ count($processedReplies) }} {{ count($processedReplies) === 1 ? 'Message' : 'Messages' }}
        </div>

        @if(empty($processedReplies))
            <p style="font-size:10pt; color:#6b7280; font-style:italic;">No chat messages have been sent in this memo thread.</p>
        @else
            @foreach($processedReplies as $item)
            <div class="chat-message">
                <div class="chat-message-header">
                    <span class="chat-sender">{{ $item['sender'] }}</span>
                    <span class="chat-time">{{ $item['sent_at'] }}</span>
                </div>

                @if($item['message'])
                <div class="chat-message-body">
                    {!! $item['message'] !!}
                </div>
                @endif

                @if(!empty($item['attachments']))
                <div class="chat-attachments">
                    <div class="chat-attach-heading">
                        Attachments ({{ count($item['attachments']) }})
                    </div>
                    @foreach($item['attachments'] as $att)
                        {!! $renderAttachment($att) !!}
                    @endforeach
                </div>
                @endif
            </div>
            @endforeach
        @endif
    </div>

    {{-- ══ ANNEXES INDEX (documents merged in after this page) ══ --}}
    @if(!empty($annexes))
    <div class="appended-section">
        <div class="appended-section-title">Annexes &mdash; Appended Documents</div>
        <ol class="appended-list">
            @foreach($annexes as $annex)
                <li>
                    <strong>Annex {{ $annex['number'] }}:</strong> {{ $annex['name'] }}
                    @if(!empty($annex['label']))
                        <span style="color:#6b7280;">&mdash; {{ $annex['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
        <div style="font-size:8.5pt; color:#92400e; margin-top:8px;">
            The full content of each document listed above is attached on the following pages, in order.
        </div>
    </div>
    @endif

    {{-- ══ FOOTER ══ --}}
    <div class="pdf-footer">
        Generated by UIMMS &bull; {{ now()->format('d M Y, H:i') }} &bull; Confidential
        &bull; Memo Ref: {{ $memo->reference ?? $memo->id }}
    </div>

</div>
</body>
</html>
