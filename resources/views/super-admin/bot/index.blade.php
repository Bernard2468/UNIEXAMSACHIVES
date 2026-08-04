@extends('super-admin.layout')

@section('title', 'Bot Control Center')

@push('styles')
<style>
    .bot-hero{ background:linear-gradient(135deg,#0f172a,#1e293b); color:#fff; border-radius:16px; padding:26px 28px; margin-bottom:26px; }
    .bot-hero h2{ font-size:22px; font-weight:700; margin-bottom:4px; }
    .bot-hero p{ opacity:.75; margin:0; font-size:14px; }
    .master-switch{ display:flex; align-items:center; gap:14px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.15);
        border-radius:14px; padding:16px 20px; margin-top:18px; }
    .master-switch .state{ font-weight:700; font-size:16px; }
    .master-switch .state.on{ color:#4ade80; } .master-switch .state.off{ color:#f87171; }
    .master-switch .sub{ font-size:12.5px; opacity:.7; }
    .toggle{ position:relative; width:58px; height:32px; flex-shrink:0; }
    .toggle input{ opacity:0; width:0; height:0; }
    .toggle label{ position:absolute; inset:0; cursor:pointer; background:#475569; border-radius:999px; transition:.25s; }
    .toggle label::after{ content:''; position:absolute; top:3px; left:3px; width:26px; height:26px; border-radius:50%; background:#fff; transition:.25s; }
    .toggle input:checked + label{ background:#22c55e; }
    .toggle input:checked + label::after{ transform:translateX(26px); }

    .stat-grid{ display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:16px; }
    .stat{ background:#fff; border:1px solid #eef0f3; border-radius:14px; padding:16px 18px; }
    .stat .n{ font-size:26px; font-weight:700; color:#0f172a; line-height:1.1; }
    .stat .l{ font-size:12px; color:#64748b; margin-top:4px; }
    .stat .s{ font-size:11px; color:#94a3b8; margin-top:2px; }
    .badge-soft{ display:inline-block; padding:2px 9px; border-radius:999px; font-size:11px; font-weight:600; }
    .b-green{ background:#dcfce7; color:#166534; } .b-blue{ background:#dbeafe; color:#1e40af; }
    .b-amber{ background:#fef3c7; color:#92400e; } .b-grey{ background:#f1f5f9; color:#475569; }
    .key-row{ display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 14px; border:1px solid #eef0f3; border-radius:12px; margin-bottom:10px; }
    .key-mask{ font-family:monospace; font-size:14px; color:#334155; }
    .mini-form{ display:inline; }
    .privacy-note{ background:#fffbeb; border:1px solid #fde68a; color:#92400e; border-radius:10px; padding:10px 14px; font-size:13px; }
    .kb-item{ border:1px solid #eef0f3; border-radius:12px; padding:12px 14px; margin-bottom:10px; }
    .kb-item h6{ margin:0; font-size:14px; font-weight:600; }
    .kb-kw{ font-size:12px; color:#94a3b8; }
    label.form-label{ font-weight:600; font-size:13px; color:#334155; }
    .section-title{ font-size:16px; font-weight:700; color:#0f172a; margin:0; }
</style>
@endpush

@section('content')
<div class="d-flex align-items-center justify-content-between mb-2">
    <h1 style="font-size:26px;font-weight:700;color:#0f172a;">🤖 Bot Control Center</h1>
</div>
<p style="color:#64748b;margin-bottom:24px;">Monitor and fully control the UDTS Assistant — the AI helper that appears on every user page.</p>

{{-- ── Master switch ──────────────────────────────────────────────────── --}}
<div class="bot-hero">
    <h2>UDTS Assistant</h2>
    <p>One switch controls the bot for the entire system. When off, it disappears from every page instantly.</p>
    <form method="POST" action="{{ route('super-admin.bot.toggle') }}" id="masterForm">
        @csrf
        <div class="master-switch">
            <div class="toggle">
                <input type="checkbox" id="masterToggle" name="enabled" value="1"
                       {{ $settings['bot_enabled'] ? 'checked' : '' }}
                       onchange="document.getElementById('masterForm').submit()">
                <label for="masterToggle"></label>
            </div>
            <div>
                @if($settings['bot_enabled'])
                    <div class="state on">● LIVE on every user page</div>
                    <div class="sub">Signed-in users can chat with the assistant right now.</div>
                @else
                    <div class="state off">● OFF everywhere</div>
                    <div class="sub">The bot is hidden across the whole system until you switch it back on.</div>
                @endif
            </div>
        </div>
    </form>
</div>

{{-- ── Analytics ──────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="section-title">Usage & analytics</span>
        <span class="badge-soft b-grey">Privacy-safe · counts only, no message content</span>
    </div>
    <div class="card-body">
        @php $t = $analytics['today']; $w = $analytics['week']; $m = $analytics['month']; @endphp

        <div class="stat-grid mb-4">
            <div class="stat">
                <div class="n">{{ number_format($m['messages']) }}</div>
                <div class="l">Messages (30 days)</div>
                <div class="s">{{ number_format($t['messages']) }} today · {{ number_format($w['messages']) }} this week</div>
            </div>
            <div class="stat">
                <div class="n">{{ $m['pct_local'] }}%</div>
                <div class="l">Answered without the API</div>
                <div class="s">{{ number_format($m['local_answers']) }} local vs {{ number_format($m['api_calls']) }} Gemini calls</div>
            </div>
            <div class="stat">
                <div class="n">{{ number_format($m['api_calls']) }}</div>
                <div class="l">Gemini calls (30 days)</div>
                <div class="s">≈ ${{ number_format($m['est_cost'], 2) }} estimated</div>
            </div>
            <div class="stat">
                <div class="n">{{ number_format($m['active_users']) }}</div>
                <div class="l">Active users (30 days)</div>
                <div class="s">avg {{ $m['avg_latency'] }}ms response</div>
            </div>
            <div class="stat">
                <div class="n">{{ $feedback['up'] }} / {{ $feedback['down'] }}</div>
                <div class="l">👍 helpful / 👎 not</div>
                <div class="s">{{ $feedback['total'] }} ratings total</div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 mb-3">
                <canvas id="botChart" height="120"></canvas>
            </div>
            <div class="col-lg-4 mb-3">
                <div style="font-weight:600;font-size:13px;color:#334155;margin-bottom:10px;">Answer sources (30 days)</div>
                @php
                    $src = [
                        ['Live data', $m['live_answers'], 'b-green'],
                        ['Knowledge base', $m['kb_answers'], 'b-blue'],
                        ['Cached', $m['cache_hits'], 'b-grey'],
                        ['Gemini (API)', $m['api_calls'], 'b-amber'],
                    ];
                @endphp
                @foreach($src as $s)
                    <div class="d-flex align-items-center justify-content-between mb-2" style="font-size:13px;">
                        <span>{{ $s[0] }}</span>
                        <span class="badge-soft {{ $s[2] }}">{{ number_format($s[1]) }}</span>
                    </div>
                @endforeach

                @if($flagged->count())
                    <div style="font-weight:600;font-size:13px;color:#334155;margin:16px 0 8px;">👎 Topics to improve</div>
                    @foreach($flagged as $f)
                        <div class="d-flex align-items-center justify-content-between mb-1" style="font-size:12.5px;color:#64748b;">
                            <span>{{ $f->matched_key }}</span>
                            <span class="badge-soft b-amber">{{ $f->c }}</span>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ── API key vault ──────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header"><span class="section-title">AI provider keys — secure vault</span></div>
    <div class="card-body">
        <p style="color:#64748b;font-size:13px;">
            Keys are <strong>encrypted at rest</strong> and never shown in full. Add several Gemini keys to form a rotation
            pool — the bot spreads calls across them so no single key's daily limit is exhausted quickly.
        </p>

        @forelse($keys as $key)
            <div class="key-row">
                <div>
                    <span class="badge-soft {{ $key->provider === 'gemini' ? 'b-blue' : 'b-grey' }}">{{ ucfirst($key->provider) }}</span>
                    <span style="font-weight:600;margin-left:6px;">{{ $key->label }}</span>
                    <span class="key-mask ms-2">{{ $key->maskedKey() }}</span>
                    <div class="s" style="font-size:11.5px;color:#94a3b8;margin-top:4px;">
                        {{ $key->request_count }} calls · {{ $key->failure_count }} failures ·
                        {{ $key->last_used_at ? 'used '.$key->last_used_at->diffForHumans() : 'never used' }}
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge-soft {{ $key->is_active ? 'b-green' : 'b-grey' }}">{{ $key->is_active ? 'Active' : 'Inactive' }}</span>
                    @if($key->provider === 'gemini')
                        <form class="mini-form" method="POST" action="{{ route('super-admin.bot.keys.test', $key->id) }}">
                            @csrf
                            <button class="btn btn-sm btn-outline-primary">Test</button>
                        </form>
                    @endif
                    <form class="mini-form" method="POST" action="{{ route('super-admin.bot.keys.toggle', $key->id) }}">
                        @csrf
                        <button class="btn btn-sm btn-outline-secondary">{{ $key->is_active ? 'Disable' : 'Enable' }}</button>
                    </form>
                    <form class="mini-form" method="POST" action="{{ route('super-admin.bot.keys.destroy', $key->id) }}"
                          onsubmit="return confirm('Remove this key permanently?');">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <div class="privacy-note mb-3">
                No API keys yet. The bot still works using its built-in knowledge base and your live data — add a Gemini key
                below to unlock free-form AI answers. Get a free key at <strong>aistudio.google.com/app/apikey</strong>.
            </div>
        @endforelse

        <hr class="my-3">
        <form method="POST" action="{{ route('super-admin.bot.keys.store') }}" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-2">
                <label class="form-label">Provider</label>
                <select name="provider" class="form-select form-select-sm">
                    <option value="gemini">Gemini</option>
                    <option value="deepseek">DeepSeek</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Label (optional)</label>
                <input type="text" name="label" class="form-control form-control-sm" placeholder="e.g. Primary key">
            </div>
            <div class="col-md-5">
                <label class="form-label">API key</label>
                <input type="password" name="api_key" class="form-control form-control-sm" placeholder="Paste the key — it will be encrypted" required autocomplete="off">
            </div>
            <div class="col-md-2">
                <button class="btn btn-sm btn-primary w-100">Add key</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Settings ───────────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header"><span class="section-title">Behaviour & limits</span></div>
    <div class="card-body">
        <form method="POST" action="{{ route('super-admin.bot.settings') }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Daily AI messages per user</label>
                    <input type="number" name="bot_daily_user_cap" min="0" max="100000" class="form-control"
                           value="{{ $settings['bot_daily_user_cap'] }}">
                    <div class="s" style="font-size:12px;color:#94a3b8;margin-top:4px;">
                        Only Gemini calls count against this. Knowledge-base and live-data answers are always free. <strong>0 = unlimited.</strong>
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Answer creativity (temperature)</label>
                    <input type="number" name="bot_temperature" step="0.1" min="0" max="1" class="form-control"
                           value="{{ $settings['bot_temperature'] }}">
                    <div class="s" style="font-size:12px;color:#94a3b8;margin-top:4px;">0 = precise & factual, 1 = more creative.</div>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Gemini model cascade</label>
                    <textarea name="bot_model_cascade" rows="2" class="form-control" placeholder="one model per line">{{ $settings['bot_model_cascade'] }}</textarea>
                    <div class="s" style="font-size:12px;color:#94a3b8;margin-top:4px;">Tried top → bottom until one succeeds.</div>
                </div>

                <div class="col-12">
                    <div class="privacy-note" style="background:#eff6ff;border-color:#bfdbfe;color:#1e40af;">
                        The bot's opening line is now <strong>generated automatically</strong> — it greets each person by
                        name and time of day, and surfaces their live pending items (forms awaiting them, unread memos).
                        No manual greeting to maintain.
                    </div>
                </div>

                <div class="col-12">
                    <div class="privacy-note">
                        <label class="d-flex align-items-center gap-2" style="cursor:pointer;margin:0;">
                            <input type="checkbox" name="bot_store_transcripts" value="1" {{ $settings['bot_store_transcripts'] ? 'checked' : '' }}>
                            <span><strong>Store full conversation transcripts</strong> — records exactly what every user types and the bot replies.
                            Leave this <strong>OFF</strong> (recommended) to respect user privacy; analytics above work either way.</span>
                        </label>
                    </div>
                </div>
            </div>
            <div class="mt-3">
                <button class="btn btn-primary">Save settings</button>
            </div>
        </form>
    </div>
</div>

{{-- ── Knowledge base ─────────────────────────────────────────────────── --}}
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <span class="section-title">Knowledge base — feed the bot</span>
        <form method="POST" action="{{ route('super-admin.bot.kb.seed') }}" class="mini-form"
              onsubmit="return confirm('Load the comprehensive starter knowledge pack? This only adds entries that are missing and never overwrites your edits.');">
            @csrf
            <button class="btn btn-sm btn-outline-primary"><i class="fas fa-magic"></i> Load starter knowledge pack</button>
        </form>
    </div>
    <div class="card-body">
        <p style="color:#64748b;font-size:13px;">
            The bot already ships with a full built-in guide to UDTS. Add institution-specific answers here to extend it —
            these are matched against user questions <strong>before</strong> any API call, so a well-fed knowledge base keeps the
            bot smart and free. Keywords drive matching; links are optional deep-links. Use <strong>Load starter knowledge pack</strong>
            for a comprehensive, ready-to-edit set covering forms, memos, folders and more (safe to click anytime — it never overwrites your edits).
        </p>

        @forelse($kbEntries as $entry)
            <div class="kb-item">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <h6>{{ $entry->title }}
                            <span class="badge-soft {{ $entry->is_active ? 'b-green' : 'b-grey' }} ms-1">{{ $entry->is_active ? 'Active' : 'Off' }}</span>
                            <span class="badge-soft b-grey ms-1">{{ $entry->category }}</span>
                        </h6>
                        <div class="kb-kw mt-1">🔑 {{ \Illuminate\Support\Str::limit($entry->keywords, 90) }}</div>
                    </div>
                    <div class="d-flex gap-2">
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#kb{{ $entry->id }}">Edit</button>
                        <form method="POST" action="{{ route('super-admin.bot.kb.destroy', $entry->id) }}" onsubmit="return confirm('Delete this entry?');">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger">Delete</button>
                        </form>
                    </div>
                </div>
                <div class="collapse mt-3" id="kb{{ $entry->id }}">
                    @include('super-admin.bot._entry-form', ['entry' => $entry])
                </div>
            </div>
        @empty
            <div class="privacy-note mb-3">No custom entries yet — the bot already ships with a full built-in guide to UDTS. Add entries below to extend it.</div>
        @endforelse

        <hr class="my-3">
        <h6 style="font-weight:700;font-size:14px;margin-bottom:12px;">Add a new entry</h6>
        @include('super-admin.bot._entry-form', ['entry' => null])
    </div>
</div>

{{-- ── Transcripts (only when explicitly enabled) ─────────────────────── --}}
@if($settings['bot_store_transcripts'])
<div class="card">
    <div class="card-header"><span class="section-title">Recent conversations</span></div>
    <div class="card-body">
        <div class="privacy-note mb-3">Transcript storage is <strong>ON</strong>. You are recording user message content. Turn it off above to stop.</div>
        <table class="table table-sm">
            <thead><tr><th>User</th><th>Page</th><th>Messages</th><th>When</th></tr></thead>
            <tbody>
                @forelse($transcripts as $c)
                    <tr>
                        <td>{{ optional($c->user)->first_name }} {{ optional($c->user)->last_name }}</td>
                        <td>{{ $c->page }}</td>
                        <td>{{ $c->messages()->count() }}</td>
                        <td>{{ $c->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="text-center text-muted">No conversations recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    (function(){
        var el = document.getElementById('botChart');
        if(!el || typeof Chart === 'undefined') return;
        var series = @json($series);
        new Chart(el, {
            type: 'line',
            data: {
                labels: series.labels,
                datasets: [
                    { label:'Messages', data: series.messages, borderColor:'#01b2ac', backgroundColor:'rgba(1,178,172,.12)', fill:true, tension:.35, borderWidth:2, pointRadius:2 },
                    { label:'Gemini calls', data: series.api, borderColor:'#f59e0b', backgroundColor:'rgba(245,158,11,.10)', fill:true, tension:.35, borderWidth:2, pointRadius:2 }
                ]
            },
            options: {
                responsive:true,
                plugins:{ legend:{ position:'bottom' } },
                scales:{ y:{ beginAtZero:true, ticks:{ precision:0 } } }
            }
        });
    })();
</script>
@endpush
