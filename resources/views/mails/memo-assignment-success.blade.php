@extends('mails.layouts.cug')

@section('title', 'Memo assignment successful — ' . $memo->subject)

@section('content')
    <!-- Primary card -->
    <div class="card">
        <div class="eyebrow">{{ $memo->reference ?? 'Memo' }} · Confirmation</div>
        <span class="status-row is-green">Assigned</span>
        <h1 class="headline">Your memo was assigned.</h1>
        <p class="subline">{{ now()->format('M j, Y') }}</p>

        <hr class="divider">

        <table class="kv">
            <tr>
                <td class="k">Reference</td>
                <td class="v">{{ $memo->reference ?? '—' }}</td>
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
            <tr>
                <td class="k">Status</td>
                <td class="v">Notified by email</td>
            </tr>
        </table>
    </div>

    <!-- Confirmation card -->
    <div class="card">
        <h2 class="section-title">Hello {{ $assigner->first_name ?? '' }},</h2>
        <p class="section-sub">
            You successfully assigned this memo to
            <strong style="color:#1a1a1a; font-weight:600;">{{ trim(($assignee->first_name ?? '') . ' ' . ($assignee->last_name ?? '')) }}</strong>.
            They have been notified and can now act on it. You can follow progress in the portal.
        </p>

        <div class="quoted">
            <div class="quoted-label">Subject</div>
            <div class="quoted-body">{{ $memo->subject }}</div>
        </div>

        <div class="cta-wrap" style="margin-top:18px;">
            <a href="{{ route('dashboard.uimms.chat', $memo->id) }}" class="cta">Open memo &rarr;</a>
        </div>
    </div>
@endsection

@section('footnote')
    You're receiving this because you assigned a memo<br>
    through the institution's memo workflow.
@endsection
