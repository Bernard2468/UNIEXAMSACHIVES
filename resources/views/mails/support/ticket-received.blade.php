@extends('mails.layouts.cug')

@section('title', 'New support request')

@section('content')
    @php
        $who = trim(($requester->first_name ?? '') . ' ' . ($requester->last_name ?? '')) ?: 'A user';
        $lastMsg = $conversation->messages()
            ->where('sender_type', 'user')->where('is_internal', false)
            ->latest('id')->first();
    @endphp

    <div class="card">
        <div class="eyebrow">UDTS Support Desk</div>
        <h1 class="headline">{{ $online ? 'New support chat' : 'New support ticket' }}</h1>
        <p class="subline">{{ $who }} has asked to speak with an administrator.</p>

        <span class="status-row {{ $online ? 'is-green' : 'is-amber' }}">
            {{ $online ? 'Live — user is waiting' : 'Offline — reply by email / next shift' }}
        </span>

        <table class="kv">
            <tr><td class="k">From</td><td class="v">{{ $who }}</td></tr>
            @if($requester->email)
                <tr><td class="k">Email</td><td class="v">{{ $requester->email }}</td></tr>
            @endif
            @if($conversation->category)
                <tr><td class="k">Topic</td><td class="v">{{ ucfirst($conversation->category) }}</td></tr>
            @endif
            <tr><td class="k">Reference</td><td class="v">#{{ $conversation->id }}</td></tr>
        </table>

        @if($lastMsg)
            <div class="quoted">
                <div class="quoted-label">Their message</div>
                <div class="quoted-body">{{ \Illuminate\Support\Str::limit($lastMsg->content, 400) }}</div>
            </div>
        @endif

        <div class="cta-wrap">
            <a href="{{ $inboxUrl }}" class="cta">Open in Support Inbox →</a>
        </div>
    </div>
@endsection

@section('footnote')
    You are receiving this because you are a support agent for the University Digital Transformation Suite.
@endsection
