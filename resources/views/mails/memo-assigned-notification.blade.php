@extends('mails.layouts.cug')

@section('title', 'New memo assignment — ' . $memo->subject)

@section('content')
    <!-- Primary card -->
    <div class="card">
        <div class="eyebrow">{{ $memo->reference ?? 'Memo' }} · Assignment</div>
        <h1 class="headline">A memo was assigned to you.</h1>
        <p class="subline">{{ now()->format('M j, Y') }}</p>

        <hr class="divider">

        <table class="kv">
            <tr>
                <td class="k">Reference</td>
                <td class="v">{{ $memo->reference ?? '—' }}</td>
            </tr>
            <tr>
                <td class="k">Assigned by</td>
                <td class="v">{{ trim(($assigner->first_name ?? '') . ' ' . ($assigner->last_name ?? '')) }}</td>
            </tr>
            <tr>
                <td class="k">Assigned to</td>
                <td class="v">{{ trim(($assignee->first_name ?? '') . ' ' . ($assignee->last_name ?? '')) }}</td>
            </tr>
            @if($office)
                <tr>
                    <td class="k">Office</td>
                    <td class="v">{{ $office }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Action card -->
    <div class="card">
        <h2 class="section-title">Hello {{ $assignee->first_name ?? '' }},</h2>
        <p class="section-sub">
            <strong style="color:#1a1a1a; font-weight:600;">{{ trim(($assigner->first_name ?? '') . ' ' . ($assigner->last_name ?? '')) }}</strong>
            has assigned a memo to you for your review and response. Please open it in the portal to read the full thread and reply.
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
            <a href="{{ route('dashboard.uimms.chat', $memo->id) }}" class="cta">View &amp; respond &rarr;</a>
        </div>
    </div>
@endsection

@section('footnote')
    You're receiving this because a memo was assigned to you<br>
    through the institution's memo workflow.
@endsection
