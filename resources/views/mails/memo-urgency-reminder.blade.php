@extends('mails.layouts.cug')

@section('title', 'Urgency alert — ' . $memo->subject)

@section('content')
    <!-- Primary card -->
    <div class="card">
        <div class="eyebrow">{{ $memo->reference ?? 'Memo' }} · Urgency alert</div>
        <span class="status-row is-red">Urgent</span>
        <h1 class="headline">A pending memo needs your attention.</h1>
        <p class="subline">{{ now()->format('M j, Y \a\t g:i A') }}</p>

        <hr class="divider">

        <table class="kv">
            <tr>
                <td class="k">Reference</td>
                <td class="v">{{ $memo->reference ?? '—' }}</td>
            </tr>
            <tr>
                <td class="k">Flagged by</td>
                <td class="v">{{ trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')) }}</td>
            </tr>
            <tr>
                <td class="k">For</td>
                <td class="v">{{ trim(($recipient->first_name ?? '') . ' ' . ($recipient->last_name ?? '')) }}</td>
            </tr>
        </table>
    </div>

    <!-- Action card -->
    <div class="card">
        <h2 class="section-title">Hello {{ $recipient->first_name ?? '' }},</h2>
        <p class="section-sub">
            <strong style="color:#1a1a1a; font-weight:600;">{{ trim(($sender->first_name ?? '') . ' ' . ($sender->last_name ?? '')) }}</strong>
            has flagged this memo as <strong style="color:#b3261e; font-weight:600;">urgent</strong> and awaiting your response.
            Please review and respond as soon as possible.
        </p>

        <div class="quoted">
            <div class="quoted-label">Subject</div>
            <div class="quoted-body">{{ $memo->subject }}</div>
        </div>

        @if(!empty($memo->message))
            <div class="memo-body" style="margin-bottom:14px;">
                {!! $memo->message !!}
            </div>
        @endif

        <div class="cta-wrap" style="margin-top:18px;">
            <a href="{{ route('dashboard.uimms.chat', $memo->id) }}" class="cta is-red">View &amp; respond now &rarr;</a>
        </div>
    </div>
@endsection

@section('footnote')
    You're receiving this urgency alert because a pending memo<br>
    requires your attention in the memo workflow.
@endsection
