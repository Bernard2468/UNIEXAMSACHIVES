@extends('mails.layouts.cug')

@php
    // Tailor the framing to the kind of memo, driven by the subject prefix the
    // controllers set ("[Cc] ...", "[Through – Action Required] ...").
    $isCc      = str_starts_with($subject, '[Cc]');
    $isThrough = str_starts_with($subject, '[Through');

    $headline = $isThrough ? 'A memo needs your review.'
              : ($isCc     ? 'You have been copied on a memo.'
                           : 'You have a new memo.');

    $eyebrowAction = $isThrough ? 'Action required'
                   : ($isCc     ? 'For your information'
                                : 'New memo');

    $intro = $isThrough
        ? 'A memo has been routed through you. Please review it and forward it to the intended recipient(s) from the portal.'
        : ($isCc
            ? 'You have been copied on the following memo for your information.'
            : 'You have received a memo through the University communication system. The details are below.');

    $senderName = $campaign->creator
        ? trim(($campaign->creator->first_name ?? '') . ' ' . ($campaign->creator->last_name ?? ''))
        : 'University Communication';
    $attachmentCount = (is_array($campaign->attachments ?? null)) ? count($campaign->attachments) : 0;
@endphp

@section('title', $subject)

@section('content')
    <!-- Primary card -->
    <div class="card">
        <div class="eyebrow">{{ $campaign->reference ?? 'Memo' }} · {{ $eyebrowAction }}</div>
        <h1 class="headline">{{ $headline }}</h1>
        <p class="subline">{{ optional($campaign->created_at)->format('M j, Y') ?? now()->format('M j, Y') }}</p>

        <hr class="divider">

        <table class="kv">
            <tr>
                <td class="k">Reference</td>
                <td class="v">{{ $campaign->reference ?? '—' }}</td>
            </tr>
            <tr>
                <td class="k">From</td>
                <td class="v">{{ $senderName ?: 'University Communication' }}</td>
            </tr>
            <tr>
                <td class="k">To</td>
                <td class="v">{{ trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')) ?: ($user->email ?? '—') }}</td>
            </tr>
            @if($attachmentCount > 0)
                <tr>
                    <td class="k">Attachments</td>
                    <td class="v">{{ $attachmentCount }} file{{ $attachmentCount > 1 ? 's' : '' }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Content card -->
    <div class="card">
        <h2 class="section-title">Hello {{ $user->first_name ?? 'there' }},</h2>
        <p class="section-sub">{{ $intro }}</p>

        <div class="quoted">
            <div class="quoted-label">Subject</div>
            <div class="quoted-body">{{ $subject }}</div>
        </div>

        <div class="memo-body">
            {!! $message !!}
        </div>

        @if($attachmentCount > 0)
            <div class="attach">
                <div class="attach-title">📎 {{ $attachmentCount }} Attachment{{ $attachmentCount > 1 ? 's' : '' }}</div>
                @foreach($campaign->attachments as $attachment)
                    <div class="attach-item">
                        {{ $attachment['name'] ?? 'Attachment' }}
                        @if(isset($attachment['size']))<span class="sz"> · {{ number_format($attachment['size'] / 1024, 1) }} KB</span>@endif
                    </div>
                @endforeach
            </div>
        @endif

        @if(!empty($campaign->id))
            <div class="cta-wrap" style="margin-top:18px;">
                <a href="{{ route('dashboard.uimms.chat', $campaign->id) }}" class="cta">Open memo &rarr;</a>
            </div>
        @endif
    </div>
@endsection

@section('footnote')
    You're receiving this email because a memo was sent to you<br>
    through the University's communication system.
@endsection
