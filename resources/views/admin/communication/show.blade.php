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
                    <div class="dashboard__content__wraper">

                        @php
                            $statusMap = [
                                'draft'     => ['label' => 'Draft',     'icon' => 'icofont-edit',          'cls' => 'is-draft'],
                                'scheduled' => ['label' => 'Scheduled', 'icon' => 'icofont-clock-time',    'cls' => 'is-scheduled'],
                                'sending'   => ['label' => 'Sending',   'icon' => 'icofont-spinner',       'cls' => 'is-sending'],
                                'sent'      => ['label' => 'Sent',      'icon' => 'icofont-check-circled', 'cls' => 'is-sent'],
                                'failed'    => ['label' => 'Failed',    'icon' => 'icofont-close-circled', 'cls' => 'is-failed'],
                            ];
                            $cur = $statusMap[$campaign->status] ?? ['label' => ucfirst($campaign->status), 'icon' => 'icofont-info-circle', 'cls' => 'is-draft'];

                            $initials = function ($u) {
                                if (!$u) return '—';
                                return strtoupper(mb_substr($u->first_name ?? '', 0, 1) . mb_substr($u->last_name ?? '', 0, 1)) ?: '–';
                            };

                            $recipients  = $campaign->recipients;
                            $roleOf      = fn ($r) => $r->recipient_role ?: 'to';
                            $toList      = $recipients->filter(fn ($r) => $roleOf($r) === 'to');
                            $ccList      = $recipients->filter(fn ($r) => $roleOf($r) === 'cc');
                            $throughUser = $campaign->throughUser;

                            $rate = rtrim(rtrim(number_format($campaign->success_rate, 1), '0'), '.');
                            $prog = round($campaign->progress_percentage);
                        @endphp

                        <div class="memo-view">

                            {{-- ============ TOP BAR ============ --}}
                            <div class="mv-topbar">
                                <nav class="mv-crumb">
                                    <a href="{{ route('admin.communication.index') }}">Memos</a>
                                    <span class="sep">/</span>
                                    <span class="cur">Details</span>
                                </nav>
                                <div class="mv-actions">
                                    <a href="{{ route('admin.communication.index') }}" class="mv-btn ghost">
                                        <i class="icofont-simple-left"></i><span>Back</span>
                                    </a>
                                    @if($campaign->status === 'draft')
                                        <a href="{{ route('admin.communication.edit', $campaign) }}" class="mv-btn ghost">
                                            <i class="icofont-ui-edit"></i><span>Edit</span>
                                        </a>
                                    @endif
                                    @if(in_array($campaign->status, ['draft', 'scheduled']))
                                        <form method="POST" action="{{ route('admin.communication.send', $campaign) }}"
                                              onsubmit="return confirm('Send this memo now?')">
                                            @csrf
                                            <button type="submit" class="mv-btn solid">
                                                <i class="icofont-paper-plane"></i><span>Send now</span>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>

                            {{-- ============ HERO ============ --}}
                            <header class="mv-hero">
                                <div class="mv-hero-grid">
                                    <div class="mv-hero-main">
                                        <div class="mv-eyebrow">
                                            <span class="mv-ref">{{ $campaign->reference ?? 'MEMO' }}</span>
                                            @if($campaign->memo_category)
                                                <span class="mv-tag">{{ ucfirst($campaign->memo_category) }}</span>
                                            @endif
                                        </div>

                                        <h1 class="mv-title">{{ $campaign->subject }}</h1>

                                        <div class="mv-chips">
                                            <span class="mv-status {{ $cur['cls'] }}">
                                                <span class="dot"></span>{{ $cur['label'] }}
                                            </span>
                                            @if($toList->count())
                                                <span class="mv-aud"><i class="icofont-users-alt-4"></i>{{ $toList->count() }} recipient{{ $toList->count() > 1 ? 's' : '' }}</span>
                                            @endif
                                            @if($ccList->count())
                                                <span class="mv-aud cc"><span class="rolepill cc">Cc</span>{{ $ccList->count() }}</span>
                                            @endif
                                            @if($campaign->through_user_id)
                                                <span class="mv-aud through"><i class="icofont-exchange"></i>Routed</span>
                                            @endif
                                        </div>
                                    </div>

                                    <div class="mv-hero-meta">
                                        <div class="mv-meta">
                                            <span class="k">From</span>
                                            <span class="v">
                                                <span class="ava sm">{{ $initials($campaign->creator) }}</span>
                                                {{ optional($campaign->creator)->first_name }} {{ optional($campaign->creator)->last_name }}
                                            </span>
                                        </div>
                                        <div class="mv-meta">
                                            <span class="k">Created</span>
                                            <span class="v mono">{{ $campaign->created_at->format('M j, Y · g:i A') }}</span>
                                        </div>
                                        @if($campaign->scheduled_at)
                                            <div class="mv-meta">
                                                <span class="k">Scheduled</span>
                                                <span class="v mono">{{ $campaign->scheduled_at->format('M j, Y · g:i A') }}</span>
                                            </div>
                                        @endif
                                        @if($campaign->sent_at)
                                            <div class="mv-meta">
                                                <span class="k">Sent</span>
                                                <span class="v mono">{{ $campaign->sent_at->format('M j, Y · g:i A') }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- routing strip (Through) --}}
                                @if($campaign->through_user_id)
                                    <div class="mv-route">
                                        <div class="node">
                                            <span class="ava">{{ $initials($campaign->creator) }}</span>
                                            <div class="lbl">
                                                <span class="r">Sender</span>
                                                <span class="n">{{ optional($campaign->creator)->first_name }} {{ optional($campaign->creator)->last_name }}</span>
                                            </div>
                                        </div>
                                        <div class="arrow"><i class="icofont-long-arrow-right"></i></div>
                                        <div class="node through">
                                            <span class="ava">{{ $initials($throughUser) }}</span>
                                            <div class="lbl">
                                                <span class="r">Through · {{ ($campaign->through_status === 'pending') ? 'awaiting forward' : 'forwarded' }}</span>
                                                <span class="n">{{ $throughUser ? $throughUser->first_name . ' ' . $throughUser->last_name : 'Intermediary' }}</span>
                                            </div>
                                        </div>
                                        <div class="arrow"><i class="icofont-long-arrow-right"></i></div>
                                        <div class="node">
                                            <span class="ava ghostava"><i class="icofont-users-alt-4"></i></span>
                                            <div class="lbl">
                                                <span class="r">Recipients</span>
                                                <span class="n">{{ $campaign->total_recipients }} {{ \Illuminate\Support\Str::plural('person', $campaign->total_recipients) }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </header>

                            {{-- ============ BODY ============ --}}
                            <div class="mv-body">
                                <div class="mv-col-main">

                                    {{-- message --}}
                                    <section class="mv-card">
                                        <div class="mv-card-head"><h2><i class="icofont-paper"></i> Message</h2></div>
                                        <div class="mv-prose">{!! $campaign->message !!}</div>
                                    </section>

                                    {{-- attachments --}}
                                    @if($campaign->attachments && count($campaign->attachments) > 0)
                                        <section class="mv-card">
                                            <div class="mv-card-head">
                                                <h2><i class="icofont-clip"></i> Attachments <span class="count">{{ count($campaign->attachments) }}</span></h2>
                                            </div>
                                            <div class="mv-files">
                                                @foreach($campaign->attachments as $index => $attachment)
                                                    <div class="mv-file">
                                                        <span class="ic"><i class="icofont-file-document"></i></span>
                                                        <div class="meta">
                                                            <span class="fn">{{ $attachment['name'] }}</span>
                                                            <span class="fs mono">{{ number_format(($attachment['size'] ?? 0) / 1024, 1) }} KB</span>
                                                        </div>
                                                        <a class="dl" href="{{ route('admin.communication.download-attachment', [$campaign, $index]) }}" title="Download">
                                                            <i class="icofont-download"></i>
                                                        </a>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </section>
                                    @endif

                                    {{-- recipients --}}
                                    @if($recipients->isNotEmpty())
                                        <section class="mv-card">
                                            <div class="mv-card-head between">
                                                <h2><i class="icofont-users-alt-4"></i> Recipients</h2>
                                                <div class="mv-filters">
                                                    <button class="chip active" onclick="mvFilter(this,'all')">All <b>{{ $recipients->count() }}</b></button>
                                                    @if($ccList->count())
                                                        <button class="chip" onclick="mvFilter(this,'role:cc')">Cc <b>{{ $ccList->count() }}</b></button>
                                                    @endif
                                                    <button class="chip" onclick="mvFilter(this,'sent')">Sent <b>{{ $recipients->where('status','sent')->count() }}</b></button>
                                                    <button class="chip" onclick="mvFilter(this,'pending')">Pending <b>{{ $recipients->where('status','pending')->count() }}</b></button>
                                                    <button class="chip" onclick="mvFilter(this,'failed')">Failed <b>{{ $recipients->where('status','failed')->count() }}</b></button>
                                                </div>
                                            </div>

                                            <div class="mv-rlist">
                                                @foreach($recipients as $recipient)
                                                    @php $role = $recipient->recipient_role ?: 'to'; @endphp
                                                    <div class="mv-rrow" data-status="{{ $recipient->status }}" data-role="{{ $role }}">
                                                        <span class="ava">{{ $initials($recipient->user) }}</span>
                                                        <div class="who">
                                                            <span class="nm">
                                                                {{ optional($recipient->user)->first_name }} {{ optional($recipient->user)->last_name ?: (optional($recipient->user)->first_name ? '' : 'Unknown user') }}
                                                                @if($role === 'cc')
                                                                    <span class="rolepill cc">Cc</span>
                                                                @elseif($role === 'through')
                                                                    <span class="rolepill through">Through</span>
                                                                @endif
                                                                @if($recipient->is_read)
                                                                    <span class="readtick" title="Read{{ $recipient->read_at ? ' · ' . $recipient->read_at->format('M j, g:i A') : '' }}"><i class="icofont-check"></i> read</span>
                                                                @endif
                                                            </span>
                                                            <span class="em mono">{{ optional($recipient->user)->email }}</span>
                                                        </div>
                                                        <div class="rstat">
                                                            <span class="sdot {{ $recipient->status }}"></span>{{ ucfirst($recipient->status) }}
                                                        </div>
                                                        <div class="rwhen mono">{{ $recipient->sent_at ? $recipient->sent_at->format('M j · g:i A') : '—' }}</div>
                                                        <div class="rerr">
                                                            @if($recipient->error_message)
                                                                <i class="icofont-warning-alt" title="{{ $recipient->error_message }}"></i>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </section>
                                    @else
                                        <section class="mv-card">
                                            <div class="mv-empty">
                                                <i class="icofont-users-alt-4"></i>
                                                <p>Recipients will appear here once this memo is
                                                    {{ $campaign->through_user_id ? 'forwarded by the intermediary' : ($campaign->status === 'scheduled' ? 'sent at the scheduled time' : 'sent') }}.</p>
                                            </div>
                                        </section>
                                    @endif
                                </div>

                                {{-- ============ SIDEBAR ============ --}}
                                <aside class="mv-col-side">
                                    <section class="mv-card">
                                        <div class="mv-card-head"><h2><i class="icofont-chart-histogram"></i> Overview</h2></div>
                                        <div class="mv-stat-grid">
                                            <div class="st">
                                                <span class="n">{{ $campaign->total_recipients }}</span>
                                                <span class="l">Recipients</span>
                                            </div>
                                            <div class="st ok">
                                                <span class="n">{{ $campaign->sent_count }}</span>
                                                <span class="l">Delivered</span>
                                            </div>
                                            <div class="st bad">
                                                <span class="n">{{ $campaign->failed_count }}</span>
                                                <span class="l">Failed</span>
                                            </div>
                                            @if($campaign->status === 'sent')
                                                <div class="st info">
                                                    <span class="n">{{ $rate }}%</span>
                                                    <span class="l">Success</span>
                                                </div>
                                            @endif
                                        </div>

                                        @if(in_array($campaign->status, ['sending', 'sent']))
                                            <div class="mv-progress">
                                                <div class="ptop">
                                                    <span class="lbl">Delivery progress</span>
                                                    <span class="pct mono">{{ $prog }}%</span>
                                                </div>
                                                <div class="pbar"><span style="width: {{ $campaign->progress_percentage }}%"></span></div>
                                                <span class="pmeta mono">{{ $campaign->sent_count }} of {{ $campaign->total_recipients }} delivered</span>
                                            </div>
                                        @endif
                                    </section>

                                    <section class="mv-card">
                                        <div class="mv-card-head"><h2><i class="icofont-tag"></i> Properties</h2></div>
                                        <div class="mv-props">
                                            @if($campaign->memo_category)
                                                <div class="pr"><span class="k">Category</span><span class="v">{{ ucfirst($campaign->memo_category) }}</span></div>
                                            @endif
                                            @if($campaign->letterhead)
                                                <div class="pr"><span class="k">Letterhead</span><span class="v">{{ \Illuminate\Support\Str::headline($campaign->letterhead) }}</span></div>
                                            @endif
                                            <div class="pr"><span class="k">Reference</span><span class="v mono">{{ $campaign->reference }}</span></div>
                                            <div class="pr"><span class="k">Audience</span><span class="v">{{ \Illuminate\Support\Str::headline($campaign->recipient_type) }}</span></div>
                                        </div>
                                    </section>

                                    @if(in_array($campaign->status, ['draft', 'scheduled']))
                                        <section class="mv-card accent">
                                            <h3 class="mv-mini-title">Ready to deliver?</h3>
                                            <p class="muted">Send this memo to all listed recipients right away.</p>
                                            <form method="POST" action="{{ route('admin.communication.send', $campaign) }}"
                                                  onsubmit="return confirm('Send this memo now?')">
                                                @csrf
                                                <button type="submit" class="mv-btn solid block">
                                                    <i class="icofont-paper-plane"></i><span>Send memo now</span>
                                                </button>
                                            </form>
                                        </section>
                                    @endif

                                    @if($campaign->status !== 'sending')
                                        <section class="mv-card danger">
                                            <div class="mv-card-head"><h2><i class="icofont-trash"></i> Danger zone</h2></div>
                                            <p class="muted">Deleting a memo is permanent and cannot be undone.</p>
                                            <form method="POST" action="{{ route('admin.communication.destroy', $campaign) }}"
                                                  onsubmit="return confirm('Delete this memo permanently? This cannot be undone.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="mv-btn danger block">
                                                    <i class="icofont-trash"></i><span>Delete memo</span>
                                                </button>
                                            </form>
                                        </section>
                                    @endif
                                </aside>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&family=Roboto+Mono:wght@400;500&display=swap" rel="stylesheet">

<style>
/* ============================================================
   MEMO DETAILS — scoped redesign (calm, cool, no gradients)
   ============================================================ */
.memo-view {
    --ink:    #1b2230;
    --ink-2:  #5a657b;
    --ink-3:  #8b95a8;
    --line:   #e9edf4;
    --line-2: #f1f4f9;
    --surface:#ffffff;
    --surf-2: #f7f9fc;
    --surf-3: #eef2f8;

    --accent:     #4a63d6;
    --accent-ink: #3a4ea8;
    --accent-bg:  #eef1fd;

    --ok:#2f8f63;   --ok-bg:#e8f4ee;
    --warn:#9a7327; --warn-bg:#f7f0e1;
    --bad:#c1556c;  --bad-bg:#fbecef;
    --info:#3a7fa6; --info-bg:#e9f3f8;
    --violet:#6f57c4; --violet-bg:#efebfb;
    --teal:#2f8b8b;   --teal-bg:#e5f3f3;

    --radius: 18px;
    --radius-sm: 12px;
    --shadow: 0 1px 2px rgba(20,30,55,.04), 0 8px 24px rgba(20,30,55,.05);

    font-family: 'DM Sans', system-ui, sans-serif;
    color: var(--ink);
    line-height: 1.55;
}
.memo-view *,
.memo-view *::before,
.memo-view *::after { box-sizing: border-box; }

.memo-view .mono { font-family: 'Roboto Mono', ui-monospace, monospace; font-size: .82em; letter-spacing: -.2px; }

/* ---------- top bar ---------- */
.memo-view .mv-topbar {
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; flex-wrap: wrap; margin-bottom: 22px;
}
.memo-view .mv-crumb { font-family: 'Outfit', sans-serif; font-size: 13.5px; color: var(--ink-3); display: flex; align-items: center; gap: 8px; }
.memo-view .mv-crumb a { color: var(--ink-2); text-decoration: none; font-weight: 500; transition: color .2s; }
.memo-view .mv-crumb a:hover { color: var(--accent); }
.memo-view .mv-crumb .sep { color: var(--ink-3); opacity: .6; }
.memo-view .mv-crumb .cur { color: var(--ink); font-weight: 600; }
.memo-view .mv-actions { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

/* ---------- buttons ---------- */
.memo-view .mv-btn {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 14px;
    padding: 10px 16px; border-radius: 12px; border: 1px solid transparent;
    cursor: pointer; text-decoration: none; white-space: nowrap; line-height: 1;
    transition: transform .15s ease, background .2s, border-color .2s, box-shadow .2s;
}
.memo-view .mv-btn i { font-size: 15px; }
.memo-view .mv-btn:active { transform: translateY(1px); }
.memo-view .mv-btn.ghost { background: var(--surface); color: var(--ink-2); border-color: var(--line); }
.memo-view .mv-btn.ghost:hover { color: var(--ink); border-color: #d8dee9; background: var(--surf-2); }
.memo-view .mv-btn.solid { background: var(--accent); color: #fff; box-shadow: 0 6px 16px rgba(74,99,214,.22); }
.memo-view .mv-btn.solid:hover { background: var(--accent-ink); }
.memo-view .mv-btn.danger { background: var(--bad-bg); color: var(--bad); border-color: #f2d6dd; }
.memo-view .mv-btn.danger:hover { background: #f7dfe5; }
.memo-view .mv-btn.block { width: 100%; justify-content: center; padding: 12px 16px; }

/* ---------- hero ---------- */
.memo-view .mv-hero {
    background: var(--surface); border: 1px solid var(--line);
    border-radius: var(--radius); padding: 28px; margin-bottom: 22px;
    box-shadow: var(--shadow); position: relative; overflow: hidden;
}
.memo-view .mv-hero::before {
    content: ""; position: absolute; inset: 0 0 auto 0; height: 3px;
    background: var(--accent); opacity: .85;
}
.memo-view .mv-hero-grid { display: grid; grid-template-columns: 1fr minmax(230px, 300px); gap: 32px; align-items: start; }
.memo-view .mv-hero-main { min-width: 0; }

.memo-view .mv-eyebrow { display: flex; align-items: center; gap: 10px; margin-bottom: 14px; flex-wrap: wrap; }
.memo-view .mv-ref {
    font-family: 'Roboto Mono', monospace; font-size: 12px; font-weight: 500;
    color: var(--accent-ink); background: var(--accent-bg);
    padding: 4px 10px; border-radius: 7px; letter-spacing: .3px;
}
.memo-view .mv-tag {
    font-family: 'Outfit', sans-serif; font-size: 11.5px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .6px; color: var(--ink-2);
    background: var(--surf-3); padding: 4px 10px; border-radius: 7px;
}
.memo-view .mv-title {
    font-family: 'Sora', sans-serif; font-weight: 700; font-size: 27px;
    line-height: 1.25; letter-spacing: -.6px; color: var(--ink);
    margin: 0 0 16px; word-break: break-word;
}

.memo-view .mv-chips { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.memo-view .mv-status {
    display: inline-flex; align-items: center; gap: 8px;
    font-family: 'Outfit', sans-serif; font-weight: 600; font-size: 13px;
    padding: 6px 13px 6px 11px; border-radius: 999px; border: 1px solid transparent;
}
.memo-view .mv-status .dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; box-shadow: 0 0 0 4px rgba(0,0,0,.05); }
.memo-view .mv-status.is-draft     { color: var(--ink-2); background: var(--surf-3); border-color: var(--line); }
.memo-view .mv-status.is-scheduled { color: var(--accent-ink); background: var(--accent-bg); border-color: #dfe5fb; }
.memo-view .mv-status.is-sending   { color: var(--teal); background: var(--teal-bg); border-color: #d3e9e9; }
.memo-view .mv-status.is-sent      { color: var(--ok); background: var(--ok-bg); border-color: #d4ebdf; }
.memo-view .mv-status.is-failed    { color: var(--bad); background: var(--bad-bg); border-color: #f2d6dd; }

.memo-view .mv-aud {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 500; color: var(--ink-2);
    background: var(--surf-2); border: 1px solid var(--line);
    padding: 5px 12px; border-radius: 999px;
}
.memo-view .mv-aud i { font-size: 14px; color: var(--ink-3); }
.memo-view .mv-aud.through i { color: var(--teal); }

/* role pills (Cc / Through) */
.memo-view .rolepill {
    display: inline-flex; align-items: center; font-family: 'Outfit', sans-serif;
    font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: .5px;
    padding: 2px 7px; border-radius: 6px; line-height: 1.4;
}
.memo-view .rolepill.cc { color: var(--violet); background: var(--violet-bg); }
.memo-view .rolepill.through { color: var(--teal); background: var(--teal-bg); }
.memo-view .mv-aud.cc { gap: 7px; }

/* hero meta column */
.memo-view .mv-hero-meta {
    display: flex; flex-direction: column; gap: 14px;
    background: var(--surf-2); border: 1px solid var(--line);
    border-radius: var(--radius-sm); padding: 18px;
}
.memo-view .mv-meta { display: flex; flex-direction: column; gap: 4px; }
.memo-view .mv-meta .k {
    font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .7px; color: var(--ink-3);
}
.memo-view .mv-meta .v { font-size: 14px; font-weight: 500; color: var(--ink); display: flex; align-items: center; gap: 8px; }

/* avatars */
.memo-view .ava {
    flex-shrink: 0; width: 40px; height: 40px; border-radius: 12px;
    display: inline-flex; align-items: center; justify-content: center;
    font-family: 'Sora', sans-serif; font-weight: 600; font-size: 13.5px;
    background: var(--accent-bg); color: var(--accent-ink); letter-spacing: .3px;
}
.memo-view .ava.sm { width: 26px; height: 26px; border-radius: 8px; font-size: 10.5px; }
.memo-view .ava.ghostava { background: var(--surf-3); color: var(--ink-3); }

/* routing strip */
.memo-view .mv-route {
    margin-top: 24px; padding-top: 22px; border-top: 1px dashed var(--line);
    display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.memo-view .mv-route .node {
    display: flex; align-items: center; gap: 11px;
    background: var(--surf-2); border: 1px solid var(--line);
    padding: 10px 16px 10px 10px; border-radius: 14px; flex: 1; min-width: 180px;
}
.memo-view .mv-route .node.through { background: var(--teal-bg); border-color: #d3e9e9; }
.memo-view .mv-route .lbl { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.memo-view .mv-route .lbl .r {
    font-family: 'Outfit', sans-serif; font-size: 10.5px; font-weight: 600;
    text-transform: uppercase; letter-spacing: .5px; color: var(--ink-3);
}
.memo-view .mv-route .node.through .lbl .r { color: var(--teal); }
.memo-view .mv-route .lbl .n { font-size: 13.5px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.memo-view .mv-route .arrow { color: var(--ink-3); font-size: 18px; flex-shrink: 0; }

/* ---------- body grid ---------- */
.memo-view .mv-body { display: grid; grid-template-columns: 1fr 340px; gap: 22px; align-items: start; }
.memo-view .mv-col-main { display: flex; flex-direction: column; gap: 22px; min-width: 0; }
.memo-view .mv-col-side { display: flex; flex-direction: column; gap: 22px; position: sticky; top: 20px; }

/* cards */
.memo-view .mv-card {
    background: var(--surface); border: 1px solid var(--line);
    border-radius: var(--radius); padding: 24px; box-shadow: var(--shadow);
}
.memo-view .mv-card.accent { background: var(--accent-bg); border-color: #dfe5fb; }
.memo-view .mv-card.danger { border-color: #f2dde2; }
.memo-view .mv-card-head { display: flex; align-items: center; margin-bottom: 18px; }
.memo-view .mv-card-head.between { justify-content: space-between; gap: 14px; flex-wrap: wrap; }
.memo-view .mv-card-head h2 {
    font-family: 'Sora', sans-serif; font-size: 15px; font-weight: 600;
    color: var(--ink); margin: 0; display: flex; align-items: center; gap: 9px; letter-spacing: -.2px;
}
.memo-view .mv-card-head h2 i { color: var(--accent); font-size: 16px; }
.memo-view .mv-card.danger .mv-card-head h2 i { color: var(--bad); }
.memo-view .mv-card-head h2 .count {
    font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600;
    color: var(--ink-2); background: var(--surf-3); padding: 1px 9px; border-radius: 999px;
}
.memo-view .muted { color: var(--ink-2); font-size: 13.5px; margin: 0 0 14px; }
.memo-view .mv-mini-title { font-family: 'Sora', sans-serif; font-size: 16px; font-weight: 600; margin: 0 0 6px; }

/* message prose */
.memo-view .mv-prose { font-size: 15px; color: #28303f; line-height: 1.72; word-break: break-word; }
.memo-view .mv-prose p { margin: 0 0 14px; }
.memo-view .mv-prose p:last-child { margin-bottom: 0; }
.memo-view .mv-prose ul, .memo-view .mv-prose ol { margin: 14px 0; padding-left: 22px; }
.memo-view .mv-prose li { margin: 6px 0; }
.memo-view .mv-prose h1, .memo-view .mv-prose h2, .memo-view .mv-prose h3 { font-family: 'Sora', sans-serif; margin: 22px 0 10px; line-height: 1.3; }
.memo-view .mv-prose a { color: var(--accent); }
.memo-view .mv-prose img { max-width: 100%; height: auto; border-radius: 10px; }
.memo-view .mv-prose blockquote { margin: 14px 0; padding: 4px 16px; border-left: 3px solid var(--line); color: var(--ink-2); }
.memo-view .mv-prose table { width: 100%; border-collapse: collapse; margin: 14px 0; }
.memo-view .mv-prose th, .memo-view .mv-prose td { border: 1px solid var(--line); padding: 8px 10px; text-align: left; }

/* attachments */
.memo-view .mv-files { display: grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap: 12px; }
.memo-view .mv-file {
    display: flex; align-items: center; gap: 12px;
    background: var(--surf-2); border: 1px solid var(--line);
    border-radius: var(--radius-sm); padding: 12px 14px; transition: border-color .2s, background .2s;
}
.memo-view .mv-file:hover { border-color: #d8dee9; background: #fff; }
.memo-view .mv-file .ic {
    width: 38px; height: 38px; border-radius: 10px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    background: var(--accent-bg); color: var(--accent); font-size: 18px;
}
.memo-view .mv-file .meta { display: flex; flex-direction: column; gap: 2px; min-width: 0; flex: 1; }
.memo-view .mv-file .fn { font-size: 13.5px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.memo-view .mv-file .fs { color: var(--ink-3); }
.memo-view .mv-file .dl {
    width: 34px; height: 34px; border-radius: 9px; flex-shrink: 0;
    display: inline-flex; align-items: center; justify-content: center;
    color: var(--ink-2); background: var(--surface); border: 1px solid var(--line);
    text-decoration: none; transition: all .2s;
}
.memo-view .mv-file .dl:hover { color: #fff; background: var(--accent); border-color: var(--accent); }

/* recipient filters */
.memo-view .mv-filters { display: flex; gap: 8px; flex-wrap: wrap; }
.memo-view .mv-filters .chip {
    font-family: 'Outfit', sans-serif; font-size: 12.5px; font-weight: 500;
    color: var(--ink-2); background: var(--surf-2); border: 1px solid var(--line);
    padding: 6px 12px; border-radius: 999px; cursor: pointer; transition: all .18s; line-height: 1.3;
}
.memo-view .mv-filters .chip b { font-weight: 700; color: var(--ink-3); margin-left: 3px; }
.memo-view .mv-filters .chip:hover { border-color: #d3dae7; color: var(--ink); }
.memo-view .mv-filters .chip.active { background: var(--ink); color: #fff; border-color: var(--ink); }
.memo-view .mv-filters .chip.active b { color: rgba(255,255,255,.7); }

/* recipient rows */
.memo-view .mv-rlist { display: flex; flex-direction: column; }
.memo-view .mv-rrow {
    display: grid; grid-template-columns: 40px minmax(0,1fr) 116px 120px 22px;
    align-items: center; gap: 14px; padding: 13px 6px;
    border-bottom: 1px solid var(--line-2);
}
.memo-view .mv-rrow:last-child { border-bottom: none; }
.memo-view .mv-rrow:hover { background: var(--surf-2); border-radius: 10px; }
.memo-view .mv-rrow .who { display: flex; flex-direction: column; gap: 2px; min-width: 0; }
.memo-view .mv-rrow .nm { font-size: 14px; font-weight: 600; color: var(--ink); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.memo-view .mv-rrow .em { color: var(--ink-3); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.memo-view .readtick { font-family:'Outfit',sans-serif; font-size: 10.5px; font-weight: 600; color: var(--ok); background: var(--ok-bg); padding: 1px 7px; border-radius: 6px; }
.memo-view .rstat { display: flex; align-items: center; gap: 8px; font-size: 13px; font-weight: 500; color: var(--ink-2); }
.memo-view .sdot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; background: var(--ink-3); }
.memo-view .sdot.sent { background: var(--ok); }
.memo-view .sdot.pending { background: var(--warn); }
.memo-view .sdot.failed { background: var(--bad); }
.memo-view .rwhen { color: var(--ink-3); text-align: right; }
.memo-view .rerr { text-align: center; color: var(--bad); font-size: 15px; cursor: help; }

/* empty state */
.memo-view .mv-empty { text-align: center; padding: 30px 16px; color: var(--ink-3); }
.memo-view .mv-empty i { font-size: 34px; opacity: .55; }
.memo-view .mv-empty p { margin: 12px auto 0; max-width: 360px; font-size: 13.5px; }

/* stats */
.memo-view .mv-stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.memo-view .st {
    display: flex; flex-direction: column; gap: 3px;
    background: var(--surf-2); border: 1px solid var(--line);
    border-radius: var(--radius-sm); padding: 16px;
}
.memo-view .st .n { font-family: 'Sora', sans-serif; font-size: 25px; font-weight: 700; color: var(--ink); letter-spacing: -.5px; line-height: 1.1; }
.memo-view .st .l { font-family: 'Outfit', sans-serif; font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: .6px; color: var(--ink-3); }
.memo-view .st.ok .n { color: var(--ok); }
.memo-view .st.bad .n { color: var(--bad); }
.memo-view .st.info .n { color: var(--info); }

/* progress */
.memo-view .mv-progress { margin-top: 18px; padding-top: 18px; border-top: 1px solid var(--line); }
.memo-view .mv-progress .ptop { display: flex; justify-content: space-between; align-items: center; margin-bottom: 9px; }
.memo-view .mv-progress .lbl { font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600; color: var(--ink-2); }
.memo-view .mv-progress .pct { color: var(--accent-ink); font-weight: 500; }
.memo-view .pbar { height: 8px; border-radius: 999px; background: var(--surf-3); overflow: hidden; }
.memo-view .pbar span { display: block; height: 100%; border-radius: 999px; background: var(--accent); transition: width .6s ease; }
.memo-view .pmeta { display: block; margin-top: 8px; color: var(--ink-3); }

/* properties */
.memo-view .mv-props { display: flex; flex-direction: column; }
.memo-view .mv-props .pr { display: flex; justify-content: space-between; align-items: center; gap: 14px; padding: 11px 0; border-bottom: 1px solid var(--line-2); }
.memo-view .mv-props .pr:last-child { border-bottom: none; padding-bottom: 0; }
.memo-view .mv-props .pr:first-child { padding-top: 0; }
.memo-view .mv-props .k { font-family: 'Outfit', sans-serif; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: .5px; color: var(--ink-3); }
.memo-view .mv-props .v { font-size: 13.5px; font-weight: 500; color: var(--ink); text-align: right; }

/* spinner */
.memo-view .is-sending .dot { animation: mvpulse 1.1s ease-in-out infinite; }
@keyframes mvpulse { 0%,100% { opacity: 1; } 50% { opacity: .3; } }

/* ---------- responsive ---------- */
@media (max-width: 1199px) {
    .memo-view .mv-body { grid-template-columns: 1fr; }
    .memo-view .mv-col-side { position: static; }
    .memo-view .mv-stat-grid { grid-template-columns: repeat(4, 1fr); }
}
@media (max-width: 860px) {
    .memo-view .mv-hero-grid { grid-template-columns: 1fr; gap: 22px; }
    .memo-view .mv-route .arrow { transform: rotate(90deg); width: 100%; text-align: center; }
    .memo-view .mv-route { flex-direction: column; align-items: stretch; }
    .memo-view .mv-route .node { min-width: 0; }
}
@media (max-width: 600px) {
    .memo-view .mv-hero, .memo-view .mv-card { padding: 18px; }
    .memo-view .mv-title { font-size: 22px; }
    .memo-view .mv-stat-grid { grid-template-columns: 1fr 1fr; }
    .memo-view .mv-rrow { grid-template-columns: 36px minmax(0,1fr) auto; row-gap: 4px; }
    .memo-view .mv-rrow .ava { width: 36px; height: 36px; }
    .memo-view .mv-rrow .rwhen { display: none; }
    .memo-view .mv-rrow .rerr { grid-column: 3; }
    .memo-view .mv-actions { width: 100%; }
    .memo-view .mv-topbar { gap: 12px; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Auto-refresh while a memo is actively sending
    @if($campaign->status === 'sending')
        setTimeout(function () { location.reload(); }, 10000);
    @endif

    // Auto-dismiss flash alerts (keeps parity with the rest of the dashboard)
    setTimeout(function () {
        if (window.jQuery) { jQuery('.alert').fadeOut('slow'); }
    }, 5000);
});

function mvFilter(btn, filter) {
    document.querySelectorAll('.memo-view .mv-filters .chip').forEach(function (c) { c.classList.remove('active'); });
    btn.classList.add('active');

    document.querySelectorAll('.memo-view .mv-rrow').forEach(function (row) {
        var show;
        if (filter === 'all') {
            show = true;
        } else if (filter.indexOf('role:') === 0) {
            show = row.dataset.role === filter.split(':')[1];
        } else {
            show = row.dataset.status === filter;
        }
        row.style.display = show ? '' : 'none';
    });
}
</script>
@endsection
