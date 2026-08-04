@extends('mails.layouts.cug')

@section('title', 'Procurement memo approved — ' . $memo->subject)

@section('content')
    <!-- Primary card -->
    <div class="card">
        <div class="eyebrow">{{ $memo->reference ?? 'Procurement Memo' }} · Approved</div>
        <h1 class="headline">A procurement memo was approved.</h1>
        <p class="subline">{{ now()->format('M j, Y') }}</p>

        <span class="status-row is-green">Approved</span>

        <hr class="divider">

        <table class="kv">
            <tr>
                <td class="k">Reference</td>
                <td class="v">{{ $memo->reference ?? '—' }}</td>
            </tr>
            <tr>
                <td class="k">Approved by</td>
                <td class="v">{{ trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) ?: '—' }}</td>
            </tr>
            <tr>
                <td class="k">Requested by</td>
                <td class="v">{{ trim((optional($memo->creator)->first_name ?? '') . ' ' . (optional($memo->creator)->last_name ?? '')) ?: '—' }}</td>
            </tr>
            <tr>
                <td class="k">Linked form</td>
                <td class="v">{{ $formLabel }}</td>
            </tr>
        </table>
    </div>

    <!-- Action card -->
    <div class="card">
        <h2 class="section-title">Hello {{ $recipient->first_name ?? '' }},</h2>
        <p class="section-sub">
            A procurement memo has just been approved and unlocked by
            <strong style="color:#1a1a1a; font-weight:600;">{{ trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) }}</strong>.
            As the Director of Finance, please take note — the request will proceed to the
            {{ $formLabel }} and reach your office for processing.
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
            <a href="{{ route('dashboard.uimms.chat', $memo->id) }}" class="cta">View memo &rarr;</a>
        </div>
    </div>
@endsection

@section('footnote')
    You're receiving this as a member of the Director of Finance office<br>
    so Finance can take note of approved procurement requests.
@endsection
