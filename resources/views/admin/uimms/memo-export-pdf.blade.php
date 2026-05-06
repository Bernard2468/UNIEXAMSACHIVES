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
        .letterhead-band {
            width: 100%;
            margin-bottom: 0;
        }
        .letterhead-img {
            width: 100%;
            max-height: 160px;
            display: block;
        }

        /* ── Page wrapper ── */
        .page-body {
            padding: 24px 40px 40px 40px;
        }

        /* ── Formal header table ── */
        .formal-header {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }
        .formal-header td {
            padding: 6px 8px;
            vertical-align: top;
            font-size: 10.5pt;
            border-bottom: 1px solid #e5e5e5;
        }
        .fh-label {
            font-weight: bold;
            color: #374151;
            width: 90px;
            white-space: nowrap;
        }
        .fh-colon {
            width: 10px;
            color: #374151;
            font-weight: bold;
        }
        .fh-value {
            color: #111827;
        }
        .fh-subject-value {
            color: #111827;
            font-weight: bold;
            font-size: 11pt;
        }

        /* ── Divider ── */
        .formal-divider {
            border: none;
            border-top: 2.5px solid #1e3a5f;
            margin: 18px 0 20px 0;
        }

        /* ── Message body ── */
        .memo-body {
            line-height: 1.7;
            font-size: 11pt;
            color: #111827;
            text-align: justify;
        }
        .memo-body p { margin-bottom: 10px; }

        /* ── Tables inside memo body ── */
        .memo-body table {
            width: 100%;
            border-collapse: collapse;
            margin: 14px 0;
            font-size: 10.5pt;
        }
        .memo-body table th {
            background: #1e3a5f;
            color: #ffffff;
            padding: 8px 10px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #1e3a5f;
        }
        .memo-body table td {
            padding: 7px 10px;
            border: 1px solid #c8d3df;
            vertical-align: top;
        }

        /* ── Attachments ── */
        .attachments-section {
            margin-top: 32px;
            border-top: 1.5px solid #d1d5db;
            padding-top: 16px;
        }
        .attachments-title {
            font-size: 10pt;
            font-weight: bold;
            color: #374151;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 14px;
        }
        .attachment-item {
            margin-bottom: 20px;
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
        .attachment-name {
            font-weight: bold;
            color: #111827;
        }
        .attachment-size {
            color: #6b7280;
            font-size: 8.5pt;
        }
        .attachment-body {
            padding: 10px 12px;
        }
        .attachment-image {
            max-width: 100%;
            max-height: 320px;
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
        }
        .attachment-link-note {
            font-size: 9pt;
            color: #6b7280;
            margin-top: 4px;
        }

        /* ── No-letterhead header banner ── */
        .plain-header-banner {
            background: #1e3a5f;
            color: #fff;
            padding: 18px 40px 14px 40px;
            margin-bottom: 0;
        }
        .plain-header-title {
            font-size: 16pt;
            font-weight: bold;
            letter-spacing: 0.03em;
        }
        .plain-header-sub {
            font-size: 9.5pt;
            opacity: 0.85;
            margin-top: 3px;
        }

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
    // ── Resolve display recipients ──
    // If toRecipients is empty (old memos before migration), fall back to all non-CC recipients
    $displayTo = $toRecipients->isNotEmpty()
        ? $toRecipients
        : $memo->recipients->filter(fn($r) => ($r->recipient_role ?? 'to') !== 'cc');

    $displayCc = $ccRecipients;

    // Creator full name
    $creatorName = trim(($memo->creator->first_name ?? '') . ' ' . ($memo->creator->last_name ?? ''));
    if (!$creatorName) $creatorName = $memo->creator->name ?? 'N/A';

    // Creator title line (position + department)
    $creatorMeta = collect([
        optional($memo->creator->position)->name,
        optional($memo->creator->department)->name,
    ])->filter()->implode(', ');
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
    <div class="memo-body">
        {!! $memo->message ?? '' !!}
    </div>

    {{-- ══ ATTACHMENTS ══ --}}
    @if(!empty($processedAttachments))
    <div class="attachments-section">
        <div class="attachments-title">Attachments ({{ count($processedAttachments) }})</div>

        @foreach($processedAttachments as $att)
        <div class="attachment-item">
            <div class="attachment-header">
                <span class="attachment-name">{{ $att['name'] }}</span>
                @if($att['size'])
                    &nbsp;<span class="attachment-size">({{ $att['size'] }})</span>
                @endif
            </div>

            <div class="attachment-body">
                @if($att['type'] === 'image')
                    <img src="{{ $att['data'] }}" class="attachment-image" alt="{{ $att['name'] }}">

                @elseif($att['type'] === 'text')
                    <div class="attachment-text">{{ $att['text'] }}</div>

                @elseif($att['type'] === 'pdf')
                    <div style="font-size:10pt; color:#1e40af;">
                        &#128196; PDF document &mdash; {{ $att['name'] }}
                    </div>
                    @if(isset($att['url']))
                    <div class="attachment-link-note">
                        View in portal: {{ $att['url'] }}
                    </div>
                    @endif

                @elseif($att['type'] === 'doc')
                    <div style="font-size:10pt; color:#1e40af;">
                        &#128196; Word document &mdash; {{ $att['name'] }}
                    </div>
                    @if(isset($att['url']))
                    <div class="attachment-link-note">
                        Download from portal: {{ $att['url'] }}
                    </div>
                    @endif

                @else
                    <div style="font-size:10pt; color:#6b7280;">
                        &#128196; {{ $att['name'] }}
                        @if(isset($att['url']))
                            <br><span style="font-size:9pt;">Download from portal: {{ $att['url'] }}</span>
                        @else
                            <br><span style="font-size:9pt;">File not available for inline preview.</span>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══ FOOTER ── --}}
    <div class="pdf-footer">
        Generated by UIMMS &bull; {{ now()->format('d M Y, H:i') }} &bull; Confidential
    </div>

</div>
</body>
</html>
