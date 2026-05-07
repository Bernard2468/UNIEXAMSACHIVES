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
    $displayTo = $toRecipients->isNotEmpty()
        ? $toRecipients
        : $memo->recipients->filter(fn($r) => ($r->recipient_role ?? 'to') !== 'cc');
    $displayCc = $ccRecipients;

    $creatorName = trim(($memo->creator->first_name ?? '') . ' ' . ($memo->creator->last_name ?? ''));
    if (!$creatorName) $creatorName = $memo->creator->name ?? 'N/A';

    $creatorMeta = collect([
        optional($memo->creator->position)->name,
        optional($memo->creator->department)->name,
    ])->filter()->implode(', ');

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
            case 'merged_ref':
                $out .= '<div class="pdf-label">&#128196; ' . e($att['name']) . '</div>';
                $out .= '<div class="merged-ref-note">&#10142; Full document content is appended after the chat thread, with original formatting preserved.</div>';
                break;
            case 'doc_html':
                $out .= '<div class="pdf-label">&#128196; Word Document — ' . e($att['name']) . '</div>';
                $out .= '<div class="doc-html-content">' . ($att['html'] ?? '') . '</div>';
                break;
            case 'pdf_text':
                $out .= '<div class="pdf-label">&#128196; PDF Content — ' . e($att['name']) . '</div>';
                $out .= '<div class="attachment-pdf-text">' . ($att['text'] ?? '') . '</div>';
                break;
            case 'doc_text':
                $out .= '<div class="pdf-label">&#128196; Word Document Content — ' . e($att['name']) . '</div>';
                $out .= '<div class="attachment-pdf-text">' . ($att['text'] ?? '') . '</div>';
                break;
            case 'pdf':
                $out .= '<div class="pdf-label">&#128196; PDF Document — ' . e($att['name']) . '</div>';
                $out .= '<div class="attachment-unavailable">PDF content could not be extracted. Verify <code>smalot/pdfparser</code> or <code>setasign/fpdi</code> is installed on the server.</div>';
                break;
            case 'doc':
                $out .= '<div class="pdf-label">&#128196; Word Document — ' . e($att['name']) . '</div>';
                $out .= '<div class="attachment-unavailable">Document content could not be extracted from this file format.</div>';
                break;
            case 'missing':
                $out .= '<div class="attachment-unavailable">&#9888; File not found on server: ' . e($att['name']) . '</div>';
                break;
            default:
                $out .= '<div class="attachment-unavailable">&#128196; ' . e($att['name']) . ' — file type not previewable inline.</div>';
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
                {{ $creatorName }}@if($creatorMeta) &mdash; {{ $creatorMeta }}@endif
            </td>
        </tr>
        <tr>
            <td class="fh-label">To</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">
                @forelse($displayTo as $r)
                    @php
                        $rName = trim(($r->user->first_name ?? '') . ' ' . ($r->user->last_name ?? ''));
                        if (!$rName) $rName = $r->user->name ?? $r->email ?? 'Unknown';
                    @endphp
                    {{ $rName }}@if(!$loop->last); @endif
                @empty
                    All Recipients
                @endforelse
            </td>
        </tr>
        @if($displayCc->isNotEmpty())
        <tr>
            <td class="fh-label">Cc</td>
            <td class="fh-colon">:</td>
            <td class="fh-value">
                @foreach($displayCc as $r)
                    @php
                        $ccName = trim(($r->user->first_name ?? '') . ' ' . ($r->user->last_name ?? ''));
                        if (!$ccName) $ccName = $r->user->name ?? $r->email ?? 'Unknown';
                    @endphp
                    {{ $ccName }}@if(!$loop->last); @endif
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

    {{-- ══ APPENDED DOCUMENTS INDEX ══ --}}
    @if(!empty($pdfMergeQueue))
    <div class="appended-section">
        <div class="appended-section-title">Documents Appended ({{ count($pdfMergeQueue) }})</div>
        <p style="font-size:9.5pt; color:#92400e; margin-bottom:8px;">
            The following documents are appended to this PDF in their original format:
        </p>
        <ol class="appended-list">
            @foreach($pdfMergeQueue as $doc)
                <li>{{ $doc['label'] }}</li>
            @endforeach
        </ol>
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
