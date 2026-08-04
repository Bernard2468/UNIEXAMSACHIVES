@extends('mails.layouts.cug')

@section('title', 'Reply from support')

@section('content')
    @php
        $agentName = trim(($agent->first_name ?? '') . ' ' . ($agent->last_name ?? '')) ?: 'Support';
        $firstName = trim($requester->first_name ?? '') ?: 'there';
        $lastMsg = $conversation->messages()
            ->where('sender_type', 'agent')->where('is_internal', false)
            ->latest('id')->first();
    @endphp

    <div class="card">
        <div class="eyebrow">UDTS Support Desk</div>
        <h1 class="headline">You have a reply</h1>
        <p class="subline">Hi {{ $firstName }}, {{ $agentName }} responded to your support request.</p>

        @if($lastMsg)
            <div class="quoted">
                <div class="quoted-label">{{ $agentName }} wrote</div>
                <div class="quoted-body">{{ \Illuminate\Support\Str::limit($lastMsg->content, 500) }}</div>
            </div>
        @endif

        <p class="section-sub">Continue the conversation right inside the system — your full chat history is saved.</p>

        <div class="cta-wrap">
            <a href="{{ $openUrl }}" class="cta">Open the conversation →</a>
        </div>
    </div>
@endsection
