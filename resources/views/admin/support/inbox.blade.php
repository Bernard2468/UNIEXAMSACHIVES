@extends('layout.app')

@section('content')
@include('frontend.header')
@include('frontend.theme_shadow')

<div class="dashboardarea sp_bottom_100">
    <div class="container-fluid full__width__padding">
        <div class="row">
            @include('components.create_section')
        </div>
    </div>
    <div class="dashboard">
        <div class="container-fluid full__width__padding">
            <div class="row">
                @include('components.sidebar')

                <div class="col-xl-9 col-lg-9 col-md-12">
                    <div id="sib"
                         data-list="{{ route('dashboard.support.list') }}"
                         data-counts="{{ route('dashboard.support.counts') }}"
                         data-show="{{ route('dashboard.support.show', ['conversation' => '__CID__']) }}"
                         data-messages="{{ route('dashboard.support.messages', ['conversation' => '__CID__']) }}"
                         data-reply="{{ route('dashboard.support.reply', ['conversation' => '__CID__']) }}"
                         data-claim="{{ route('dashboard.support.claim', ['conversation' => '__CID__']) }}"
                         data-resolve="{{ route('dashboard.support.resolve', ['conversation' => '__CID__']) }}"
                         data-reopen="{{ route('dashboard.support.reopen', ['conversation' => '__CID__']) }}"
                         data-csrf="{{ csrf_token() }}"
                         data-me="{{ auth()->id() }}"
                         data-preselect="{{ $preselect }}"
                         data-online="{{ $online ? '1' : '0' }}"
                         data-hours="{{ $hoursText }}">

                        {{-- Header --}}
                        <div class="sib-top">
                            <div class="sib-top-l">
                                <h2 class="sib-title">Support Inbox</h2>
                                <span class="sib-hours">
                                    <span class="sib-dot {{ $online ? 'on' : 'off' }}"></span>
                                    {{ $online ? 'Online' : 'After hours' }} · {{ $hoursText }}
                                </span>
                            </div>
                            <div class="sib-search">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                <input type="text" id="sibSearch" placeholder="Search name, email or subject…" autocomplete="off">
                            </div>
                        </div>

                        {{-- Filter tabs — inline on desktop, fixed bottom-nav on mobile --}}
                        <div class="sib-tabs" id="sibTabs">
                            <button class="sib-tab active" data-filter="open">
                                <svg class="sib-tab__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-6l-2 3h-4l-2-3H2"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>
                                <span class="sib-tab__label">Open</span>
                                <span class="sib-c" data-c="open">0</span>
                            </button>
                            <button class="sib-tab" data-filter="unassigned">
                                <svg class="sib-tab__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                                <span class="sib-tab__label">Unassigned</span>
                                <span class="sib-c" data-c="unassigned">0</span>
                            </button>
                            <button class="sib-tab" data-filter="mine">
                                <svg class="sib-tab__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                <span class="sib-tab__label">Mine</span>
                                <span class="sib-c" data-c="mine">0</span>
                            </button>
                            <button class="sib-tab" data-filter="resolved">
                                <svg class="sib-tab__ico" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                                <span class="sib-tab__label">Resolved</span>
                            </button>
                        </div>

                        {{-- Two-pane workspace --}}
                        <div class="sib-work">
                            {{-- Conversation rail --}}
                            <div class="sib-rail" id="sibRail">
                                <div class="sib-empty" id="sibListEmpty" style="display:none;">
                                    <p>No conversations here yet.</p>
                                </div>
                                <div id="sibList"></div>
                            </div>

                            {{-- Thread pane --}}
                            <div class="sib-pane" id="sibPane">
                                <div class="sib-pane-empty" id="sibPaneEmpty">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
                                    <p>Select a conversation to start helping.</p>
                                </div>

                                <div class="sib-thread-wrap" id="sibThreadWrap" style="display:none;">
                                    {{-- Thread header --}}
                                    <div class="sib-th-head">
                                        <button class="sib-back" id="sibBack" title="Back">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5m7-7-7 7 7 7"/></svg>
                                        </button>
                                        <div class="sib-th-id">
                                            <div class="sib-th-avatar" id="sibThAvatar"></div>
                                            <div>
                                                <div class="sib-th-name" id="sibThName">—</div>
                                                <div class="sib-th-sub" id="sibThSub"></div>
                                            </div>
                                        </div>
                                        <div class="sib-th-actions" id="sibThActions"></div>
                                    </div>

                                    {{-- What the bot already tried --}}
                                    <div class="sib-context" id="sibContext" style="display:none;">
                                        <button class="sib-context-toggle" id="sibContextToggle">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 18 15 12 9 6"/></svg>
                                            What the assistant already tried
                                        </button>
                                        <div class="sib-context-body" id="sibContextBody" style="display:none;"></div>
                                    </div>

                                    {{-- Messages --}}
                                    <div class="sib-msgs" id="sibMsgs"></div>

                                    {{-- Composer --}}
                                    <div class="sib-composer" id="sibComposer">
                                        <div class="sib-note-toggle">
                                            <label class="sib-switch">
                                                <input type="checkbox" id="sibInternal">
                                                <span>Internal note (only agents see this)</span>
                                            </label>
                                        </div>
                                        <div class="sib-composer-row">
                                            <textarea id="sibInput" rows="1" placeholder="Type your reply…"></textarea>
                                            <button id="sibSend" class="sib-send" disabled>
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@include('admin.support._inbox-styles')
@include('admin.support._inbox-script')
@endsection
