{{--
    UDTS Assistant — floating AI bot widget.

    A faithful port of the GNRS chatbot to vanilla JS + CSS: same robot, same
    floating/rotating idle animation, same pulsing ring, same chat-window design,
    tokens and Outfit font. Motion is reproduced with CSS keyframes; the token
    "stream" is reproduced with a client-side typewriter reveal (reliable on shared
    hosting). All dynamic values are passed as data-attributes so the CSS/JS block
    stays isolated from Blade. Rendered once in layout.app for signed-in users.

    NOTE: never write Blade directive words (with a leading at-sign) inside this
    comment — Blade extracts verbatim blocks before stripping comments, so a stray
    directive keyword here would swallow the rest of the file into a verbatim block.
--}}
@php
    $rn = optional(request()->route())->getName() ?? '';
    $labelMap = [
        'admin.forms.'            => 'Forms Portal',
        'dashboard.uimms.'        => 'Memos',
        'dashboard.memo'          => 'Memos',
        'dashboard.all.files'     => 'Files',
        'dashboard.all.exams'     => 'Exams',
        'dashboard.folders.'      => 'Folders',
        'dashboard.notifications' => 'Notifications',
        'dashboard.settings'      => 'Settings',
        'dashboard.profile'       => 'Profile',
        'offices.'                => 'Offices',
        'committees.'             => 'Committees',
        'search.'                 => 'Search',
        'dashboard'               => 'Dashboard',
    ];
    $botPageLabel = 'UDTS';
    foreach ($labelMap as $prefix => $lbl) {
        if ($rn !== '' && str_starts_with($rn, $prefix)) { $botPageLabel = $lbl; break; }
    }
    // ── Smart, personalised opening greeting ──────────────────────────────
    // Uses the person's name + time of day, and surfaces a live, actionable nudge
    // (forms awaiting them / unread memos) with jump links. Brief and professional,
    // never the same vague canned line. Falls back cleanly when nothing is pending.
    $botUser  = auth()->user();
    $botFirst = trim((string) ($botUser->first_name ?? '')) ?: 'there';
    $botHour  = (int) now()->format('G');
    $botTod   = $botHour < 12 ? 'Good morning' : ($botHour < 17 ? 'Good afternoon' : 'Good evening');

    $botAwaiting = (int) ($awaitingFormsCount ?? 0);
    $botUnread   = (int) ($unreadMemosCount ?? 0);

    $botSafeRoute = function (string $route) {
        try { return route($route, [], false); } catch (\Throwable $e) { return '/'; }
    };
    $botFormsUrl = $botSafeRoute('admin.forms.portal');
    $botMemoUrl  = $botSafeRoute('dashboard.uimms.portal');

    // Always generate a fresh greeting — no stored/canned value is ever read, so the
    // old line can never reappear. A variant is chosen at random each load, so it
    // feels alive and never repeats the same words.
    $botNudges = [];
    $botJumps  = [];
    if ($botAwaiting > 0) {
        $botNudges[] = "**{$botAwaiting} form" . ($botAwaiting === 1 ? '' : 's') . "** awaiting your action";
        $botJumps[]  = "[Forms Portal]({$botFormsUrl})";
    }
    if ($botUnread > 0) {
        $botNudges[] = "**{$botUnread} unread memo" . ($botUnread === 1 ? '' : 's') . "**";
        $botJumps[]  = "[Memos]({$botMemoUrl})";
    }

    if (!empty($botNudges)) {
        $botOpeners = [
            "{$botTod}, {$botFirst} — MetaGuide here.",
            "Hi {$botFirst}, MetaGuide here.",
            "{$botTod}, {$botFirst}.",
            "Welcome back, {$botFirst} — MetaGuide here.",
        ];
        $botGreeting = $botOpeners[array_rand($botOpeners)]
            . " You have " . implode(' and ', $botNudges)
            . ". Jump to " . implode(' or ', $botJumps) . ", or ask me anything.";
    } else {
        $botVariants = [
            "{$botTod}, {$botFirst} — I'm MetaGuide. What can I help you with across forms, memos and the archive?",
            "Hi {$botFirst}, MetaGuide here. Ask me anything about the system, or just tell me what you need and I'll take you straight there.",
            "{$botTod}, {$botFirst}. I'm MetaGuide — need a form raised, a memo found or a file located? Just ask.",
            "Welcome back, {$botFirst}. I'm MetaGuide, ready to help with forms, memos, files or anything else here.",
            "{$botTod}, {$botFirst} — MetaGuide at your service. Where would you like to start?",
        ];
        $botGreeting = $botVariants[array_rand($botVariants)];
    }

    $botCap = (int) \App\Models\SystemSetting::get('bot_daily_user_cap', 40);

    // ── Support Chat (bot → human handoff) config for the widget ──
    // Resolved via the service so hours / online state stay in one place. Wrapped
    // so a not-yet-migrated support layer can never break the widget or the page.
    $botSupportEnabled = false; $botSupportOnline = false; $botSupportHours = ''; $botWallpaper = '';
    try {
        $supportSvc = app(\App\Services\Support\SupportChatService::class);
        $botSupportEnabled = $supportSvc->isEnabled();
        $botSupportOnline  = $supportSvc->isSupportOnline();
        $botSupportHours   = $supportSvc->hoursText();
        $botWallpaper      = $supportSvc->wallpaper() ?? '';
    } catch (\Throwable $e) {
        $botSupportEnabled = false;
    }
    $botSupportSafe = function (string $route, array $params = []) {
        try { return route($route, $params, false); } catch (\Throwable $e) { return ''; }
    };
@endphp

<div id="udtsbot-root"
     data-ask-url="{{ route('bot.ask') }}"
     data-feedback-url="{{ route('bot.feedback') }}"
     data-csrf="{{ csrf_token() }}"
     data-page="{{ $botPageLabel }}"
     data-remaining="{{ $botRemaining ?? '' }}"
     data-limit="{{ $botCap > 0 ? $botCap : '' }}"
     data-greeting="{{ $botGreeting }}"
     data-robot="https://res.cloudinary.com/dsypclqxk/image/upload/v1777022316/Pngtree_friendly_ai_assistant_robot_icon_21334634_lyqp0d.png"
     data-support-enabled="{{ $botSupportEnabled ? '1' : '0' }}"
     data-support-online="{{ $botSupportOnline ? '1' : '0' }}"
     data-support-hours="{{ $botSupportHours }}"
     data-support-escalate="{{ $botSupportSafe('bot.support.escalate') }}"
     data-support-active="{{ $botSupportSafe('bot.support.active') }}"
     data-support-history="{{ $botSupportSafe('bot.support.history') }}"
     data-support-thread="{{ $botSupportSafe('bot.support.thread', ['conversation' => '__CID__']) }}"
     data-support-message="{{ $botSupportSafe('bot.support.message', ['conversation' => '__CID__']) }}"
     data-support-typing="{{ $botSupportSafe('bot.support.typing', ['conversation' => '__CID__']) }}"
     data-support-csat="{{ $botSupportSafe('bot.support.csat', ['conversation' => '__CID__']) }}"
     data-support-resolve="{{ $botSupportSafe('bot.support.resolve', ['conversation' => '__CID__']) }}"
     data-support-destroy="{{ $botSupportSafe('bot.support.destroy', ['conversation' => '__CID__']) }}"
     data-wallpaper="{{ $botWallpaper }}"></div>

@verbatim
<style>
/* ===== UDTS Assistant — scoped styles (prefix: ub-) ===================== */
/* Same font stack + weights as the folders page drawer (.exp-root) so the bot
   reads as part of the same product. */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

#udtsbot-root, #udtsbot-root * { box-sizing: border-box; }
#udtsbot-root {
    --ub-text:#111827; --ub-text2:#6b7280; --ub-text3:#9ca3af;
    --ub-border:#e5e7eb; --ub-border-light:#f3f4f6;
    --ub-surface:#ffffff; --ub-surface-alt:#fafafa;
    --ub-icon:#a1a1aa; --ub-icon-hover:#71717a;
    --ub-user:#18181b; --ub-charcoal:#3f3f46; --ub-link:#52525b;
    --ub-chip-border:#e4e4e7; --ub-chip-text:#52525b; --ub-chip-hover:#f4f4f5; --ub-chip-hover-bdr:#d4d4d8;
    --ub-fill:#3f3f46; --ub-warn:#f59e0b; --ub-over:#ef4444;
    font-family:'Outfit', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
}
/* Force EVERY text element inside the widget to inherit Outfit. The theme +
   Bootstrap set font-family directly on p/a/div/button/etc, and inheritance is
   weak, so those win inside the bot unless we override them. These ID-scoped
   selectors (specificity beats the global element/class rules) fix that — the
   same technique the folders drawer (.exp-root) uses. `code` is left alone so it
   keeps its monospace face. */
#udtsbot-root p, #udtsbot-root div, #udtsbot-root span, #udtsbot-root a,
#udtsbot-root strong, #udtsbot-root em, #udtsbot-root li, #udtsbot-root ul,
#udtsbot-root ol, #udtsbot-root h1, #udtsbot-root h2, #udtsbot-root h3,
#udtsbot-root h4, #udtsbot-root label, #udtsbot-root input, #udtsbot-root button,
#udtsbot-root textarea, #udtsbot-root select { font-family: inherit; }

/* ---- Floating launcher -------------------------------------------------
   Stacked ABOVE the app's scroll-to-top button (#scrollUp: right:20px,
   bottom:60px, 50px tall → its top edge is at 110px). Same right edge (20px)
   and a bottom of 126px keeps a clean ~16px gap so the two never collide.
   The GNRS bounce combines translateY (button hop) + rotate (icon wobble) +
   hover scale — three transforms. CSS can't animate two transforms on one
   element, so they live on nested elements: .ub-btn (scale) → .ub-floater
   (hop) → img (wobble). The pulsing ring is box-shadow on .ub-ring. */
.ub-launcher{ position:fixed; bottom:126px; right:20px; z-index:2147483000; }
.ub-ring{ border-radius:50%; display:inline-flex; animation:ub-ring 2.6s ease-in-out infinite; }
@keyframes ub-ring{
    0%,100%{ box-shadow:0 0 0 2.5px rgba(139,92,246,.75),0 0 0 5px rgba(139,92,246,.18),0 0 18px rgba(139,92,246,.18); }
    50%    { box-shadow:0 0 0 2.5px rgba(139,92,246,.75),0 0 0 11px rgba(139,92,246,.04),0 0 32px rgba(139,92,246,.08); }
}
.ub-btn{ background:none; border:none; cursor:pointer; padding:0; display:flex; align-items:center; justify-content:center;
    width:64px; height:64px; transition:transform .15s; }
.ub-btn:hover{ transform:scale(1.1); }
.ub-btn:active{ transform:scale(.94); }
.ub-floater{ display:flex; animation:ub-float 8.8s linear infinite; }
.ub-floater img{ width:64px; height:64px; object-fit:contain; display:block; animation:ub-wobble 8.8s ease-in-out infinite; }
/* GNRS uses framer's alternating cubic-beziers per segment (snappy rise,
   accelerating fall) — replicated here with per-keyframe timing functions so
   the bounce feels identical, then rests flat during the 5s repeat delay. */
@keyframes ub-float{
    0%    {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    4.75% {transform:translateY(-40px);animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    9.5%  {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    12.96%{transform:translateY(-32px);animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    16.4% {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    19%   {transform:translateY(-24px);animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    21.6% {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    24.2% {transform:translateY(-16px);animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    26.4% {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    28.1% {transform:translateY(-10px);animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    29.8% {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    31.1% {transform:translateY(-5px); animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    32.4% {transform:translateY(0);    animation-timing-function:cubic-bezier(.25,.46,.45,.94)}
    33.7% {transform:translateY(-2px); animation-timing-function:cubic-bezier(.55,.06,.68,.19)}
    35%   {transform:translateY(0);    animation-timing-function:linear}
    100%  {transform:translateY(0)}
}
@keyframes ub-wobble{
    0%{transform:rotate(0)} 4.75%{transform:rotate(-18deg)} 9.5%{transform:rotate(18deg)}
    12.96%{transform:rotate(-14deg)} 16.4%{transform:rotate(14deg)} 19%{transform:rotate(-10deg)}
    21.6%{transform:rotate(10deg)} 24.2%{transform:rotate(-6deg)} 26.4%{transform:rotate(6deg)}
    28.1%{transform:rotate(-3deg)} 29.8%{transform:rotate(3deg)} 32.4%{transform:rotate(0)} 100%{transform:rotate(0)}
}
@media (min-width:1600px){ .ub-btn,.ub-floater img{ width:72px; height:72px; } .ub-launcher{ bottom:128px; } }
@media (max-width:1199px){ .ub-btn,.ub-floater img{ width:58px; height:58px; } .ub-launcher{ bottom:124px; } }
@media (max-width:767px){  .ub-btn,.ub-floater img{ width:52px; height:52px; } .ub-launcher{ right:16px; bottom:120px; } }

.ub-tooltip{ position:absolute; bottom:100%; right:0; margin-bottom:10px; white-space:nowrap; border-radius:10px;
    background:var(--ub-user); padding:8px 12px; box-shadow:0 8px 24px rgba(0,0,0,.18);
    opacity:0; transform:translateX(10px) scale(.92); transition:opacity .18s, transform .18s; pointer-events:none; }
.ub-launcher:hover .ub-tooltip{ opacity:1; transform:translateX(0) scale(1); }
.ub-tooltip b{ color:#fff; font-weight:600; font-size:12px; display:block; }
.ub-tooltip span{ color:var(--ub-icon); font-size:11px; }
.ub-tooltip::after{ content:''; position:absolute; bottom:0; right:14px; width:8px; height:8px;
    background:var(--ub-user); transform:rotate(45deg) translateY(50%); }

/* ---- Overlay + window -------------------------------------------------- */
.ub-overlay{ position:fixed; inset:0; z-index:2147483001; background:rgba(0,0,0,.35); backdrop-filter:blur(4px);
    opacity:0; transition:opacity .2s; }
.ub-open .ub-overlay{ opacity:1; }

.ub-window{ position:fixed; bottom:126px; right:20px; z-index:2147483002; width:440px; max-width:calc(100vw - 40px);
    height:680px; max-height:calc(100vh - 146px); display:flex; flex-direction:column; background:var(--ub-surface);
    border-radius:20px; border:1px solid var(--ub-border); overflow:hidden;
    box-shadow:0 24px 64px rgba(0,0,0,.12),0 8px 24px rgba(0,0,0,.08),0 1px 4px rgba(0,0,0,.06);
    opacity:0; transform:translateY(20px) scale(.94); transition:opacity .22s, transform .28s cubic-bezier(.2,.9,.3,1.2);
    color:var(--ub-text); }
.ub-open .ub-window{ opacity:1; transform:translateY(0) scale(1); }
@media (max-width:767px){ .ub-window{ right:12px; left:12px; bottom:12px; width:auto; height:88vh; max-height:88vh; } }
/* Larger desktops: give it more room. */
@media (min-width:1600px){ .ub-window{ width:480px; height:760px; } }
/* Short viewports (typical laptops): sit lower + use nearly the full height so it
   never looks like a small box. */
@media (min-width:768px) and (max-height:820px){ .ub-window{ bottom:92px; height:auto; top:20px; max-height:none; } }

.ub-header{ display:flex; align-items:center; justify-content:space-between; padding:14px 16px 13px;
    border-bottom:1px solid var(--ub-border-light); flex-shrink:0; }
.ub-id{ display:flex; align-items:center; gap:10px; }
.ub-id img{ width:34px; height:34px; object-fit:contain; display:block; }
.ub-id .ub-name{ margin:0; font-weight:600; font-size:14px; letter-spacing:-.01em; color:var(--ub-text); }
.ub-status{ display:flex; align-items:center; gap:5px; margin-top:1px; }
.ub-dot{ width:6px; height:6px; border-radius:50%; background:#22c55e; display:inline-block; }
.ub-status span{ font-size:11px; color:var(--ub-text3); }
.ub-tools{ display:flex; align-items:center; gap:6px; }
.ub-pill{ display:flex; align-items:center; gap:4px; border-radius:999px; padding:3px 9px;
    border:1px solid var(--ub-chip-border); background:var(--ub-surface-alt); font-size:11px; font-weight:500; color:var(--ub-text2); }
.ub-pill.warn{ color:var(--ub-warn); } .ub-pill.over{ color:var(--ub-over); }
.ub-iconbtn{ background:none; border:none; cursor:pointer; padding:5px; border-radius:8px; color:var(--ub-icon);
    display:flex; align-items:center; transition:color .15s; }
.ub-iconbtn:hover{ color:var(--ub-icon-hover); }

.ub-progwrap{ padding:8px 16px 6px; flex-shrink:0; border-bottom:1px solid var(--ub-border-light); }
.ub-progtop{ display:flex; justify-content:space-between; margin-bottom:5px; }
.ub-progtop span{ font-size:10px; color:var(--ub-text3); font-weight:500; }
.ub-progtop .u{ text-transform:uppercase; letter-spacing:.06em; }
.ub-progbar{ height:3px; border-radius:99px; background:var(--ub-border-light); overflow:hidden; }
.ub-progfill{ height:100%; border-radius:99px; background:var(--ub-fill); width:0%; transition:width .5s ease; }

.ub-msgs{ flex:1; overflow-y:auto; padding:16px 16px 8px; display:flex; flex-direction:column; gap:12px; background:var(--ub-surface); }
.ub-msgs::-webkit-scrollbar{ width:6px; } .ub-msgs::-webkit-scrollbar-thumb{ background:rgba(100,116,139,.25); border-radius:999px; }

.ub-row{ display:flex; align-items:flex-end; gap:8px; animation:ub-rise .18s ease; }
.ub-row.user{ justify-content:flex-end; }
@keyframes ub-rise{ from{opacity:0; transform:translateY(8px);} to{opacity:1; transform:translateY(0);} }
.ub-av{ width:26px; height:26px; object-fit:contain; display:block; flex-shrink:0; }
.ub-bubble{ max-width:78%; padding:10px 14px; border-radius:18px 18px 18px 4px; background:var(--ub-surface-alt);
    border:1px solid var(--ub-border-light); box-shadow:0 1px 3px rgba(0,0,0,.04); font-size:14px; line-height:1.55; color:var(--ub-text); }
.ub-row.user .ub-bubble{ border-radius:18px 18px 4px 18px; background:var(--ub-user); border:none; color:#fff;
    box-shadow:0 2px 8px rgba(0,0,0,.12); }
.ub-bubble p{ margin:2px 0; line-height:1.6; }
.ub-bubble ul,.ub-bubble ol{ margin:5px 0 5px 16px; padding:0; }
.ub-bubble li{ margin-bottom:3px; line-height:1.55; }
.ub-bubble a{ color:var(--ub-link); text-decoration:underline; text-underline-offset:2px; font-weight:500; }
.ub-bubble code{ background:#f4f4f5; color:var(--ub-charcoal); padding:1px 5px; border-radius:4px;
    font-family:'Geist Mono','Fira Code',monospace; font-size:.8em; }
.ub-bubble strong{ font-weight:600; }
.ub-bubble h2{ font-weight:700; font-size:.85rem; margin:9px 0 3px; }
.ub-bubble h3{ font-weight:600; font-size:.72rem; text-transform:uppercase; letter-spacing:.06em; color:var(--ub-text2); margin:9px 0 3px; }
.ub-time{ margin:5px 0 0; font-size:10px; color:var(--ub-text3); }
.ub-row.user .ub-time{ color:rgba(255,255,255,.4); }
.ub-cursor{ display:inline-block; width:2px; height:12px; background:var(--ub-link); margin-left:2px;
    vertical-align:middle; border-radius:1px; animation:ub-blink .72s ease-in-out infinite; }
@keyframes ub-blink{ 0%,100%{opacity:1;} 50%{opacity:0;} }

/* feedback */
.ub-fb{ display:flex; gap:6px; margin-top:7px; }
.ub-fb button{ background:none; border:1px solid var(--ub-border-light); border-radius:8px; cursor:pointer;
    padding:2px 7px; font-size:12px; color:var(--ub-text3); transition:.15s; }
.ub-fb button:hover{ color:var(--ub-icon-hover); border-color:var(--ub-chip-hover-bdr); }
.ub-fb button.done{ color:var(--ub-charcoal); border-color:var(--ub-chip-hover-bdr); background:var(--ub-chip-hover); }

/* typing dots */
.ub-typing{ display:flex; gap:5px; align-items:center; padding:12px 16px; border-radius:18px 18px 18px 4px;
    background:var(--ub-surface-alt); border:1px solid var(--ub-border-light); box-shadow:0 1px 3px rgba(0,0,0,.04); }
.ub-typing i{ width:7px; height:7px; border-radius:50%; background:var(--ub-icon); display:inline-block; animation:ub-bounce 0.65s infinite ease-in-out; }
.ub-typing i:nth-child(2){ animation-delay:.16s; } .ub-typing i:nth-child(3){ animation-delay:.32s; }
@keyframes ub-bounce{ 0%,100%{transform:translateY(0); opacity:.5;} 50%{transform:translateY(-5px); opacity:1;} }

/* suggestion chips */
.ub-chips{ display:flex; flex-wrap:wrap; gap:7px; padding-left:34px; padding-top:2px; }
.ub-chip{ display:flex; align-items:center; gap:6px; border-radius:999px; padding:6px 11px; background:#fff;
    border:1px solid var(--ub-chip-border); cursor:pointer; transition:.15s; font-family:inherit; }
.ub-chip:hover{ background:var(--ub-chip-hover); border-color:var(--ub-chip-hover-bdr); }
.ub-chip span{ font-size:12px; font-weight:500; color:var(--ub-chip-text); white-space:nowrap; }
.ub-chip svg{ width:13px; height:13px; color:var(--ub-icon); flex-shrink:0; }

/* limit card */
.ub-limit{ margin-left:34px; max-width:90%; border-radius:16px; padding:12px 14px; background:var(--ub-surface-alt);
    border:1px solid var(--ub-chip-border); }
.ub-limit b{ font-size:12px; color:var(--ub-text); display:flex; align-items:center; gap:6px; margin-bottom:5px; }
.ub-limit p{ margin:0; font-size:12px; color:var(--ub-text2); line-height:1.5; }

/* input */
.ub-foot{ border-top:1px solid var(--ub-border-light); padding:12px 12px 10px; background:var(--ub-surface); flex-shrink:0; }
.ub-inputrow{ display:flex; align-items:center; gap:8px; border-radius:13px; padding:9px 10px 9px 14px;
    background:var(--ub-surface-alt); border:1px solid var(--ub-border); transition:border-color .18s, box-shadow .18s; }
.ub-inputrow.focus{ border-color:var(--ub-charcoal); box-shadow:0 0 0 3px rgba(63,63,70,.08); }
.ub-inputrow input{ flex:1; background:transparent; border:none; outline:none; font-size:14px; color:var(--ub-text);
    font-family:inherit; letter-spacing:-.01em; }
.ub-send{ width:32px; height:32px; border-radius:9px; flex-shrink:0; background:var(--ub-border-light); border:none;
    cursor:not-allowed; display:flex; align-items:center; justify-content:center; transition:background .18s, transform .1s; }
.ub-send.active{ background:var(--ub-user); cursor:pointer; }
.ub-send.active:hover{ background:#27272a; }
.ub-send svg{ width:14px; height:14px; color:var(--ub-icon); }
.ub-send.active svg{ color:#fff; }
.ub-retry{ background:none; border:none; cursor:pointer; padding:4px; display:flex; align-items:center; color:var(--ub-icon);
    opacity:.4; transition:color .15s; }
.ub-retry.on{ opacity:1; } .ub-retry.on:hover{ color:var(--ub-icon-hover); }
.ub-footnote{ margin:8px 0 0; text-align:center; font-size:10px; color:var(--ub-text3); letter-spacing:.02em; }

/* ---- Support (human handoff) layer — additive, does not touch bot styles ---- */
/* The "Talk to a person" icon is a colour image and a key call-to-action, so it
   is deliberately larger than the utility (new/close) icons and scales up on
   bigger screens for clear visibility. */
.ub-human{ padding:2px; }
.ub-human img{ width:30px; height:30px; object-fit:contain; display:block; }
@media (min-width:1600px){ .ub-human img{ width:34px; height:34px; } }
@media (max-width:1199px){ .ub-human img{ width:28px; height:28px; } }
@media (max-width:767px){  .ub-human img{ width:27px; height:27px; } }
.ub-handoff{ margin-left:34px; max-width:92%; border-radius:14px; padding:12px 14px; background:#eef3ff; border:1px solid #dbe4f5; }
.ub-handoff-t{ font-size:12.5px; color:var(--ub-charcoal); line-height:1.5; margin-bottom:9px; }
.ub-handoff-b{ background:var(--ub-user); color:#fff; border:none; border-radius:9px; font-size:12.5px; font-weight:600; padding:8px 14px; cursor:pointer; font-family:inherit; transition:background .15s; }
.ub-handoff-b:hover{ background:#27272a; }
.ub-suphead{ display:flex; align-items:center; justify-content:space-between; gap:10px; background:var(--ub-surface-alt); border:1px solid var(--ub-border-light); border-radius:12px; padding:9px 12px; margin-bottom:4px; }
.ub-suphead-i{ display:flex; flex-direction:column; min-width:0; }
.ub-suphead-i b{ font-size:12.5px; color:var(--ub-text); }
.ub-suphead-i span{ font-size:11px; color:var(--ub-text2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ub-supback{ flex:0 0 auto; background:none; border:1px solid var(--ub-chip-border); border-radius:8px; font-size:11.5px; color:var(--ub-chip-text); padding:5px 9px; cursor:pointer; font-family:inherit; }
.ub-supback:hover{ background:var(--ub-chip-hover); }
.ub-sup-sys{ align-self:center; text-align:center; font-size:11px; color:var(--ub-text3); background:var(--ub-surface-alt); border:1px solid var(--ub-border-light); border-radius:20px; padding:4px 12px; margin:2px auto; max-width:90%; }
.ub-agent-name{ display:block; font-size:10.5px; font-weight:700; color:#1a4a9b; margin-bottom:2px; }
.ub-history svg{ width:17px; height:17px; }
/* history list (the user's past support chats) */
.ub-hist{ display:flex; align-items:stretch; gap:6px; margin:0 0 8px; transition:opacity .18s; }
.ub-hist-main{ flex:1 1 auto; min-width:0; text-align:left; background:var(--ub-surface-alt); border:1px solid var(--ub-border-light); border-radius:12px; padding:10px 12px; cursor:pointer; font-family:inherit; transition:background .15s, border-color .15s; }
.ub-hist-main:hover{ background:var(--ub-chip-hover); border-color:var(--ub-chip-hover-bdr); }
.ub-hist-top{ display:flex; align-items:center; justify-content:space-between; gap:8px; }
.ub-hist-subj{ font-size:13px; font-weight:600; color:var(--ub-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ub-hist-time{ font-size:10.5px; color:var(--ub-text3); flex:0 0 auto; }
.ub-hist-snip{ font-size:12px; color:var(--ub-text2); margin-top:2px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ub-hist-status{ font-size:10.5px; font-weight:600; margin-top:5px; }
.ub-hist-status.act{ color:#16a34a; }
.ub-hist-status.res{ color:var(--ub-text3); }
.ub-hist-del{ flex:0 0 auto; width:42px; border:1px solid var(--ub-border-light); border-radius:12px; background:#fff; color:var(--ub-text3); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:color .15s, border-color .15s, background .15s; }
.ub-hist-del:hover{ color:var(--ub-over); border-color:#fecaca; background:#fef2f2; }
.ub-hist-del svg{ width:15px; height:15px; }
.ub-hist-empty{ text-align:center; color:var(--ub-text3); font-size:12.5px; padding:24px 12px; }
/* Chat wallpaper (Super-Admin set) — softly blurred behind the bubbles, WhatsApp/Telegram style */
.ub-msgs.ub-has-wall{ position:relative; }
.ub-msgs.ub-has-wall::before{ content:''; position:absolute; inset:-16px; background-image:var(--ub-wall); background-size:cover; background-position:center; filter:blur(9px); opacity:.5; z-index:0; pointer-events:none; }
.ub-msgs.ub-has-wall::after{ content:''; position:absolute; inset:0; background:rgba(255,255,255,.62); z-index:0; pointer-events:none; }
.ub-msgs.ub-has-wall > *{ position:relative; z-index:1; }
/* Typing indicator (peer is typing) */
.ub-typewrap{ display:flex; align-items:flex-end; gap:8px; }
/* resolved / new-chat bar */
.ub-resolved{ margin:6px 0 2px; border-radius:14px; padding:12px 14px; background:var(--ub-surface-alt); border:1px solid var(--ub-border-light); text-align:center; }
.ub-resolved-t{ font-size:12.5px; color:var(--ub-text2); line-height:1.5; margin-bottom:10px; }
.ub-resolved-new{ background:var(--ub-user); color:#fff; border:none; border-radius:9px; font-size:12.5px; font-weight:600; padding:9px 16px; cursor:pointer; font-family:inherit; transition:background .15s; }
.ub-resolved-new:hover{ background:#27272a; }
/* CSAT rating */
.ub-csat{ margin-top:10px; padding-top:10px; border-top:1px dashed var(--ub-border); }
.ub-csat-q{ font-size:12px; color:var(--ub-text2); margin-bottom:7px; }
.ub-csat-btns{ display:flex; gap:8px; justify-content:center; }
.ub-csat-btns button{ width:40px; height:36px; border:1px solid var(--ub-chip-border); border-radius:10px; background:#fff; cursor:pointer; font-size:16px; transition:.15s; }
.ub-csat-btns button:hover{ background:var(--ub-chip-hover); border-color:var(--ub-chip-hover-bdr); }
.ub-csat-done{ font-size:12px; color:var(--ub-charcoal); text-align:center; }
</style>

<script>
(function(){
    var root = document.getElementById('udtsbot-root');
    if(!root || root.dataset.inited) return;
    root.dataset.inited = '1';

    var CFG = {
        askUrl: root.dataset.askUrl,
        feedbackUrl: root.dataset.feedbackUrl,
        csrf: root.dataset.csrf,
        page: root.dataset.page || 'UDTS',
        robot: root.dataset.robot,
        greeting: root.dataset.greeting || "Hi! I'm MetaGuide.",
        remaining: root.dataset.remaining === '' ? null : parseInt(root.dataset.remaining,10),
        limit: root.dataset.limit === '' ? null : parseInt(root.dataset.limit,10),
    };
    CFG.support = {
        enabled:  root.dataset.supportEnabled === '1',
        escalate: root.dataset.supportEscalate,
        active:   root.dataset.supportActive,
        history:  root.dataset.supportHistory,
        thread:   root.dataset.supportThread,
        message:  root.dataset.supportMessage,
        typing:   root.dataset.supportTyping,
        csat:     root.dataset.supportCsat,
        resolve:  root.dataset.supportResolve,
        destroy:  root.dataset.supportDestroy,
        hours:    root.dataset.supportHours || '',
        online:   root.dataset.supportOnline === '1'
    };
    CFG.wallpaper = root.dataset.wallpaper || '';

    var SUGGESTIONS = [
        {label:'What can I do here?',       icon:'compass'},
        {label:'How do I submit a form?',   icon:'file'},
        {label:'How many forms await me?',  icon:'inbox'},
        {label:'How do I sign a form?',     icon:'card'},
        {label:'Where are my files?',       icon:'folder'},
        {label:'How do I send a memo?',     icon:'mail'},
    ];

    var ICONS = {
        send:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>',
        x:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>',
        rotate:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 1 0 2.13-9.36L1 10"/></svg>',
        zap:'<svg viewBox="0 0 24 24" fill="currentColor" stroke="none"><polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/></svg>',
        lock:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>',
        compass:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/></svg>',
        file:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
        inbox:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 16 12 14 15 10 15 8 12 2 12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/></svg>',
        card:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>',
        folder:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
        mail:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/></svg>',
        chevron:'<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="opacity:.7"><polyline points="9 18 15 12 9 6"/></svg>',
    };

    // ---- state ----
    var state = { open:false, loading:false, history:[], remaining:CFG.remaining, limit:(CFG.limit||CFG.remaining), unlimited:CFG.remaining===null, greeted:false,
                  mode:'bot', convId:0, supportLastId:0, supportTimer:null, supportSending:false, pendingContext:[],
                  supportResolved:false, supportAgentAvatar:null };

    // ---- helpers ----
    function esc(s){ return s.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function nowTime(){ return new Date().toLocaleTimeString('en-US',{hour:'numeric',minute:'2-digit',hour12:true}); }

    function inline(text){
        var out=''; var rem=text; var guard=0;
        var pats=[
            {re:/\[([^\]]+)\]\(([^)]+)\)/, t:'link'},
            {re:/\*\*([^*]+)\*\*/, t:'bold'},
            {re:/\*([^*\n]+)\*/, t:'em'},
            {re:/`([^`]+)`/, t:'code'}
        ];
        while(rem.length && guard++<500){
            var idx=Infinity, m=null, type='';
            for(var i=0;i<pats.length;i++){ var mm=pats[i].re.exec(rem); if(mm && mm.index<idx){ idx=mm.index; m=mm; type=pats[i].t; } }
            if(!m){ out+=esc(rem); break; }
            if(idx>0) out+=esc(rem.slice(0,idx));
            if(type==='link'){
                var label=esc(m[1]); var href=m[2].trim();
                var internal = href.charAt(0)==='/';
                // Only allow safe schemes — never render javascript:/data: from model output.
                var safe = internal || /^https?:\/\//i.test(href) || /^mailto:/i.test(href);
                if(!safe){ out+=label; }
                else{
                    var attrs = internal ? 'href="'+encodeURI(href)+'"' : 'href="'+encodeURI(href)+'" target="_blank" rel="noopener noreferrer"';
                    out+='<a '+attrs+'>'+label+'</a>';
                }
            } else if(type==='bold'){ out+='<strong>'+esc(m[1])+'</strong>'; }
            else if(type==='em'){ out+='<em>'+esc(m[1])+'</em>'; }
            else if(type==='code'){ out+='<code>'+esc(m[1])+'</code>'; }
            rem=rem.slice(idx+m[0].length);
        }
        return out;
    }

    function markdown(text){
        var lines=String(text).split('\n'); var html=''; var buf=[]; var ordered=false;
        function flush(){ if(!buf.length) return; html+='<'+(ordered?'ol':'ul')+'>'+buf.join('')+'</'+(ordered?'ol':'ul')+'>'; buf=[]; }
        for(var i=0;i<lines.length;i++){
            var t=lines[i].trim();
            if(!t){ flush(); continue; }
            var bullet=/^[-*•·]\s/.test(t), num=/^\d+\.\s/.test(t);
            if(bullet||num){ if(!buf.length) ordered=num; buf.push('<li>'+inline(t.replace(/^[-*•·]\s|^\d+\.\s/,''))+'</li>'); continue; }
            flush();
            if(t.indexOf('### ')===0) html+='<h3>'+inline(t.slice(4))+'</h3>';
            else if(t.indexOf('## ')===0) html+='<h2>'+inline(t.slice(3))+'</h2>';
            else html+='<p>'+inline(t)+'</p>';
        }
        flush();
        return html;
    }

    // ---- build DOM ----
    var launcher=document.createElement('div'); launcher.className='ub-launcher';
    launcher.innerHTML =
        '<div class="ub-tooltip"><b>Need help?</b><span>Ask me anything about UDTS</span></div>'+
        '<div class="ub-ring"><button class="ub-btn" aria-label="Open MetaGuide"><span class="ub-floater"><img src="'+CFG.robot+'" alt="MetaGuide"></span></button></div>';
    root.appendChild(launcher);

    var shell=document.createElement('div'); shell.style.display='none'; root.appendChild(shell);
    shell.innerHTML =
        '<div class="ub-overlay"></div>'+
        '<div class="ub-window">'+
          '<div class="ub-header">'+
            '<div class="ub-id"><img src="'+CFG.robot+'" alt=""><div>'+
              '<p class="ub-name">MetaGuide</p>'+
              '<div class="ub-status"><span class="ub-dot"></span><span>Online · Powered by Metascholar</span></div>'+
            '</div></div>'+
            '<div class="ub-tools">'+
              '<button class="ub-iconbtn ub-human" data-human title="Talk to a person"><img src="https://res.cloudinary.com/dsypclqxk/image/upload/v1785866774/1dee6f70-14e1-4867-8cbb-7a8df642750d.png" alt="Talk to a person"></button>'+
              '<button class="ub-iconbtn ub-history" data-history title="Your conversations"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3v5h5"/><path d="M3.05 13A9 9 0 1 0 6 5.3L3 8"/><path d="M12 7v5l4 2"/></svg></button>'+
              '<button class="ub-iconbtn" data-new title="New conversation">'+ICONS.rotate+'</button>'+
              '<button class="ub-iconbtn" data-close title="Close">'+ICONS.x+'</button>'+
            '</div>'+
          '</div>'+
          '<div class="ub-progwrap" data-progwrap>'+
            '<div class="ub-progtop"><span class="u">Daily AI messages</span><span data-progtext></span></div>'+
            '<div class="ub-progbar"><div class="ub-progfill" data-progfill></div></div>'+
          '</div>'+
          '<div class="ub-msgs" data-msgs></div>'+
          '<div class="ub-foot">'+
            '<div class="ub-inputrow" data-inputrow>'+
              '<input type="text" placeholder="Ask me anything about UDTS…" data-input>'+
              '<button class="ub-retry" data-retry title="Retry last">'+ICONS.rotate+'</button>'+
              '<button class="ub-send" data-send>'+ICONS.send+'</button>'+
            '</div>'+
            '<p class="ub-footnote">MetaGuide · Ask · Navigate · Get unstuck</p>'+
          '</div>'+
        '</div>';

    var $=function(s){ return shell.querySelector(s); };
    var msgs=$('[data-msgs]'), input=$('[data-input]'), sendBtn=$('[data-send]'), retryBtn=$('[data-retry]'),
        inputRow=$('[data-inputrow]'),
        progWrap=$('[data-progwrap]'), progText=$('[data-progtext]'), progFill=$('[data-progfill]');

    // ---- pill / progress ----
    function refreshUsage(){
        if(state.unlimited || state.remaining===null){
            progWrap.style.display='none'; return;
        }
        var lim = state.limit || (state.remaining||0);
        var used = Math.max(0, lim - state.remaining);
        progWrap.style.display='';
        progText.textContent = used+'/'+lim;
        var pct = lim>0 ? Math.round(used/lim*100) : 0;
        progFill.style.width = pct+'%';
        progFill.style.background = state.remaining<=0 ? 'var(--ub-over)' : (state.remaining<=1 ? 'var(--ub-warn)' : 'var(--ub-fill)');
    }

    // ---- rendering messages ----
    function scrollDown(){ msgs.scrollTop = msgs.scrollHeight; }

    function addUser(text){
        var row=document.createElement('div'); row.className='ub-row user';
        row.innerHTML='<div class="ub-bubble"><p></p><p class="ub-time">'+nowTime()+'</p></div>';
        row.querySelector('p').textContent=text;
        msgs.appendChild(row); scrollDown();
    }

    function addAssistantShell(){
        var row=document.createElement('div'); row.className='ub-row';
        row.innerHTML='<img class="ub-av" src="'+CFG.robot+'" alt=""><div class="ub-bubble"><div data-body></div><p class="ub-time">'+nowTime()+'</p></div>';
        msgs.appendChild(row); scrollDown();
        return row;
    }

    function typing(){
        var row=document.createElement('div'); row.className='ub-row'; row.dataset.typing='1';
        row.innerHTML='<img class="ub-av" src="'+CFG.robot+'" alt=""><div class="ub-typing"><i></i><i></i><i></i></div>';
        msgs.appendChild(row); scrollDown(); return row;
    }

    function reveal(bodyEl, full, meta){
        var i=0; var n=full.length;
        function step(){
            i=Math.min(n, i + Math.max(2, Math.round(n/120)) );
            bodyEl.innerHTML = markdown(full.slice(0,i)) + (i<n?'<span class="ub-cursor"></span>':'');
            scrollDown();
            if(i<n){ setTimeout(step, 14); }
            else { attachFeedback(bodyEl, meta); }
        }
        step();
    }

    function attachFeedback(bodyEl, meta){
        if(!meta || meta.source==='fallback') return;
        var fb=document.createElement('div'); fb.className='ub-fb';
        fb.innerHTML='<button data-up title="Helpful">👍</button><button data-down title="Not helpful">👎</button>';
        bodyEl.parentNode.appendChild(fb);
        fb.querySelector('[data-up]').addEventListener('click', function(){ sendFeedback('up',meta,this,fb); });
        fb.querySelector('[data-down]').addEventListener('click', function(){ sendFeedback('down',meta,this,fb); });
    }

    function sendFeedback(rating, meta, btn, fb){
        Array.prototype.forEach.call(fb.querySelectorAll('button'), function(b){ b.classList.remove('done'); });
        btn.classList.add('done');
        fetch(CFG.feedbackUrl,{ method:'POST', headers:hdrs(), body:JSON.stringify({
            rating:rating, source:meta.source, matched_key:meta.matched_key, question:meta.question, answer:meta.answer
        })}).catch(function(){});
    }

    function hdrs(){ return {'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}; }

    // ---- suggestions ----
    function showSuggestions(){
        var wrap=document.createElement('div'); wrap.className='ub-chips'; wrap.dataset.chips='1';
        SUGGESTIONS.forEach(function(s){
            var b=document.createElement('button'); b.className='ub-chip';
            b.innerHTML=(ICONS[s.icon]||ICONS.compass)+'<span>'+s.label+'</span>'+ICONS.chevron;
            b.addEventListener('click', function(){ send(s.label); });
            wrap.appendChild(b);
        });
        msgs.appendChild(wrap); scrollDown();
    }
    function clearSuggestions(){ var c=msgs.querySelector('[data-chips]'); if(c) c.remove(); }

    // ---- greet / reset ----
    function greet(){
        restoreBotChrome();
        msgs.innerHTML='';
        var row=addAssistantShell();
        row.querySelector('[data-body]').innerHTML=markdown(CFG.greeting);
        showSuggestions();
        state.history=[];
        state.greeted=true;
        refreshUsage();
    }

    // ---- send ----
    function send(text){
        text=(text||input.value).trim();
        if(state.mode==='history'){ input.value=''; setActive(); return; }
        if(!text) return;
        if(state.mode==='support'){ input.value=''; setActive(); return sendSupportMessage(text); }
        if(state.mode==='support_intake'){ input.value=''; setActive(); return submitSupportIntake(text); }
        if(state.loading) return;
        clearSuggestions();
        input.value=''; setActive();
        addUser(text);
        state.loading=true;
        var tRow=typing();

        fetch(CFG.askUrl,{ method:'POST', headers:hdrs(), body:JSON.stringify({
            message:text, page:CFG.page, history:state.history.slice(-8)
        })})
        .then(function(r){ return r.json().then(function(j){ return {ok:r.ok, j:j}; }); })
        .then(function(res){
            if(tRow) tRow.remove();
            state.loading=false;
            var j=res.j||{};
            if(!res.ok){
                var row=addAssistantShell();
                row.querySelector('[data-body]').innerHTML=markdown(j.answer || "I'm having trouble right now. Please try again in a moment.");
                return;
            }
            if(typeof j.remaining!=='undefined'){ state.remaining=j.remaining; state.limit=j.limit; state.unlimited=!!j.unlimited; refreshUsage(); }
            state.history.push({role:'user',content:text});
            state.history.push({role:'assistant',content:j.answer});
            var row=addAssistantShell();
            reveal(row.querySelector('[data-body]'), j.answer, {source:j.source, matched_key:j.matched_key, question:text, answer:j.answer});
            if(j.source==='fallback' && CFG.support && CFG.support.enabled){ showHandoffChip(); }
        })
        .catch(function(){
            if(tRow) tRow.remove(); state.loading=false;
            var row=addAssistantShell();
            row.querySelector('[data-body]').innerHTML=markdown('Network error — please check your connection and try again.');
        });
    }

    function setActive(){
        var on = input.value.trim() && !state.loading;
        sendBtn.classList.toggle('active', !!on);
        var last = state.history.length>0;
        retryBtn.classList.toggle('on', last && !state.loading);
    }

    // ---- open / close ----
    function open(){ if(state.open) return; state.open=true; shell.style.display=''; requestAnimationFrame(function(){ root.classList.add('ub-open'); }); if(!state.greeted) greet(); resumeSupportIfAny(); setTimeout(function(){ input.focus(); },120); }
    function close(){ state.open=false; root.classList.remove('ub-open'); setTimeout(function(){ if(!state.open) shell.style.display='none'; },260); }

    // ---- wire events ----
    launcher.querySelector('.ub-btn').addEventListener('click', open);
    $('[data-close]').addEventListener('click', close);
    $('.ub-overlay').addEventListener('click', close);
    $('[data-new]').addEventListener('click', function(){ greet(); });
    sendBtn.addEventListener('click', function(){ if(sendBtn.classList.contains('active')) send(); });
    retryBtn.addEventListener('click', function(){
        if(state.loading) return;
        var lastUser=null;
        for(var i=state.history.length-1;i>=0;i--){ if(state.history[i].role==='user'){ lastUser=state.history[i].content; break; } }
        if(lastUser){ state.history=state.history.slice(0, Math.max(0,state.history.length-2)); send(lastUser); }
    });
    input.addEventListener('input', setActive);
    input.addEventListener('focus', function(){ inputRow.classList.add('focus'); });
    input.addEventListener('blur', function(){ inputRow.classList.remove('focus'); });
    input.addEventListener('keydown', function(e){ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); send(); } });
    document.addEventListener('keydown', function(e){ if(e.key==='Escape' && state.open) close(); });

    // ===== Support (human handoff) — additive layer; the bot flow above is untouched =====
    var nameEl = shell.querySelector('.ub-name');
    var statusText = shell.querySelector('.ub-status span:last-of-type');
    var origName = nameEl ? nameEl.textContent : 'MetaGuide';
    var origStatus = statusText ? statusText.textContent : 'Online';
    var origPlaceholder = input ? input.getAttribute('placeholder') : '';

    var humanBtn = $('[data-human]');
    if(humanBtn){
        if(!CFG.support.enabled){ humanBtn.style.display='none'; }
        else { humanBtn.addEventListener('click', function(){ startSupport(); }); }
    }
    var historyBtn = $('[data-history]');
    if(historyBtn){
        if(!CFG.support.enabled){ historyBtn.style.display='none'; }
        else { historyBtn.addEventListener('click', function(){ openHistory(); }); }
    }

    // Chat wallpaper (Super-Admin set) — softly blurred behind the bubbles.
    if(CFG.wallpaper){
        try{ msgs.classList.add('ub-has-wall'); msgs.style.setProperty('--ub-wall', 'url("'+CFG.wallpaper.replace(/["\\;]/g,'')+'")'); }catch(e){}
    }

    // Typing ping: while in an active support chat, tell the agent we're typing (throttled).
    var lastTypingPing = 0;
    input.addEventListener('input', function(){
        if(state.mode!=='support' || !state.convId || state.supportResolved) return;
        var t = Date.now();
        if(t - lastTypingPing > 2500){ lastTypingPing = t; try{ fetch(supUrl(CFG.support.typing, state.convId), {method:'POST', headers:hdrs(), body:'{}'}).catch(function(){}); }catch(e){} }
    });

    function supUrl(tpl, id){ return String(tpl||'').replace('__CID__', id); }
    function supCid(p){ return (p||'u') + Date.now() + Math.random().toString(36).slice(2,7); }

    function restoreBotChrome(){
        state.mode='bot'; stopSupportPoll();
        state.supportResolved=false;
        if(nameEl) nameEl.textContent = origName;
        if(statusText) statusText.textContent = origStatus;
        if(input){ input.disabled=false; input.setAttribute('placeholder', origPlaceholder); }
        if(retryBtn) retryBtn.style.display='';
        refreshUsage();
    }

    function showHandoffChip(){
        if(state.mode==='support' || !CFG.support.enabled) return;
        var wrap=document.createElement('div'); wrap.className='ub-handoff';
        wrap.innerHTML='<div class="ub-handoff-t">Prefer a person? Talk to a support admin — I’ll pass along what we’ve covered.</div><button class="ub-handoff-b" type="button">Talk to a person</button>';
        wrap.querySelector('button').addEventListener('click', function(){ startSupport(); });
        msgs.appendChild(wrap); scrollDown();
    }

    // "Talk to a person": resume an existing open chat, or — if none — ask the
    // user to describe their concern FIRST. No ticket is created (and no agent is
    // alerted) until they actually send that first message.
    function startSupport(){
        if(!CFG.support.enabled) return;
        var ctx=[];
        for(var i=0;i<state.history.length;i++){ ctx.push({role:state.history[i].role, content:state.history[i].content}); }
        state.pendingContext = ctx.slice(-12);
        var tRow=typing();
        fetch(CFG.support.escalate,{method:'POST',headers:hdrs(),body:JSON.stringify({page:CFG.page, bot_context:state.pendingContext})})
            .then(function(r){return r.json();})
            .then(function(d){
                if(tRow) tRow.remove();
                if(d && d.conversation){ enterSupportUI(d); }   // resumed an open chat
                else { enterSupportIntake(); }                  // collect the concern first
            })
            .catch(function(){ if(tRow) tRow.remove(); enterSupportIntake(); });
    }

    // Pre-chat: prompt the user for their first message before we connect them.
    function enterSupportIntake(){
        state.mode='support_intake';
        stopSupportPoll();
        clearSuggestions();
        if(progWrap) progWrap.style.display='none';
        if(retryBtn) retryBtn.style.display='none';
        if(nameEl) nameEl.textContent = 'Contact support';
        if(statusText) statusText.textContent = CFG.support.online ? 'Online' : 'Away';
        input.setAttribute('placeholder','Describe your issue…');
        msgs.innerHTML='';
        var head=document.createElement('div'); head.className='ub-suphead';
        head.innerHTML='<div class="ub-suphead-i"><b>Contact support</b><span>'+(CFG.support.online ? 'We’re online' : 'We’ll reply by email')+'</span></div><button class="ub-supback" type="button">← Assistant</button>';
        head.querySelector('.ub-supback').addEventListener('click', function(){ greet(); });
        msgs.appendChild(head);
        var row=addAssistantShell();
        row.querySelector('[data-body]').innerHTML=markdown("Please describe your issue or question in a message below, and I'll connect you to a support administrator.");
        scrollDown();
        setTimeout(function(){ input.focus(); }, 60);
    }

    // Submit the concern → NOW create the conversation and alert agents.
    function submitSupportIntake(text){
        addUser(text);
        var tRow=typing();
        fetch(CFG.support.escalate,{method:'POST',headers:hdrs(),body:JSON.stringify({message:text, page:CFG.page, bot_context: state.pendingContext||[]})})
            .then(function(r){return r.json();})
            .then(function(d){
                if(tRow) tRow.remove();
                if(d && d.conversation){ enterSupportUI(d); }
                else {
                    var row=addAssistantShell();
                    row.querySelector('[data-body]').innerHTML=markdown("Sorry — I couldn't connect you just now. Please try again in a moment.");
                }
            })
            .catch(function(){ if(tRow) tRow.remove(); var row=addAssistantShell(); row.querySelector('[data-body]').innerHTML=markdown('Network error — please try again.'); });
    }

    // ---- History (the user's own past support chats) ----
    function openHistory(){
        if(!CFG.support.enabled) return;
        state.mode='history';
        stopSupportPoll();
        clearSuggestions();
        if(progWrap) progWrap.style.display='none';
        if(retryBtn) retryBtn.style.display='none';
        if(nameEl) nameEl.textContent = 'Your conversations';
        if(statusText) statusText.textContent = 'History';
        input.setAttribute('placeholder','Open a conversation above…');
        msgs.innerHTML = '';
        var tRow=typing();
        fetch(CFG.support.history,{headers:hdrs()})
            .then(function(r){return r.json();})
            .then(function(d){ if(tRow) tRow.remove(); renderHistory((d && d.conversations) || []); })
            .catch(function(){ if(tRow) tRow.remove(); renderHistory([]); });
    }

    function renderHistory(rows){
        msgs.innerHTML='';
        var head=document.createElement('div'); head.className='ub-suphead';
        head.innerHTML='<div class="ub-suphead-i"><b>Your conversations</b><span>'+rows.length+' chat'+(rows.length===1?'':'s')+'</span></div><button class="ub-supback" type="button">← Assistant</button>';
        head.querySelector('.ub-supback').addEventListener('click', function(){ greet(); });
        msgs.appendChild(head);

        if(!rows.length){
            var e=document.createElement('div'); e.className='ub-hist-empty';
            e.textContent="You haven't spoken with support yet.";
            msgs.appendChild(e); return;
        }

        var trash='<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
        rows.forEach(function(c){
            var status = c.resolved ? 'Resolved' : (c.status==='queued' ? 'Waiting' : 'Active');
            var item=document.createElement('div'); item.className='ub-hist';
            item.innerHTML=
                '<button class="ub-hist-main" type="button">'+
                    '<div class="ub-hist-top"><span class="ub-hist-subj"></span><span class="ub-hist-time">'+esc(c.time_h||'')+'</span></div>'+
                    '<div class="ub-hist-snip"></div>'+
                    '<div class="ub-hist-status '+(c.resolved?'res':'act')+'">'+status+(c.unread>0?(' · '+c.unread+' new'):'')+'</div>'+
                '</button>'+
                '<button class="ub-hist-del" type="button" title="Delete conversation">'+trash+'</button>';
            item.querySelector('.ub-hist-subj').textContent = c.subject || 'Support request';
            item.querySelector('.ub-hist-snip').textContent = c.snippet || '';
            item.querySelector('.ub-hist-main').addEventListener('click', function(){ openSupportConversation(c.id); });
            item.querySelector('.ub-hist-del').addEventListener('click', function(ev){ ev.stopPropagation(); deleteSupportConversation(c.id, item); });
            msgs.appendChild(item);
        });
        scrollDown();
    }

    function openSupportConversation(id){
        var tRow=typing();
        fetch(supUrl(CFG.support.thread, id)+'?after=0',{headers:hdrs()})
            .then(function(r){return r.json();})
            .then(function(d){ if(tRow) tRow.remove(); if(d && d.conversation){ enterSupportUI(d); } })
            .catch(function(){ if(tRow) tRow.remove(); });
    }

    function deleteSupportConversation(id, itemEl){
        if(!window.confirm('Delete this conversation from your history? Support will keep a record.')) return;
        fetch(supUrl(CFG.support.destroy, id),{method:'DELETE',headers:hdrs()})
            .then(function(r){return r.json();})
            .then(function(d){ if(d && d.ok && itemEl){ itemEl.style.opacity='0'; setTimeout(function(){ if(itemEl.parentNode) itemEl.parentNode.removeChild(itemEl); },180); } })
            .catch(function(){});
    }

    function enterSupportUI(payload){
        state.mode='support';
        state.convId = payload.conversation.id;
        state.supportLastId = 0;
        clearSuggestions();
        if(progWrap) progWrap.style.display='none';
        if(retryBtn) retryBtn.style.display='none';
        if(nameEl) nameEl.textContent = 'Support Team';
        input.setAttribute('placeholder','Message the support team…');
        renderSupportThread(payload, false);
        startSupportPoll();
        setTimeout(function(){ input.focus(); }, 60);
    }

    function renderSupportThread(payload, append){
        if(!append){ msgs.innerHTML=''; addSupportBanner(payload.conversation); }
        (payload.messages||[]).forEach(addSupportBubble);
        if(payload.messages && payload.messages.length){ state.supportLastId = payload.messages[payload.messages.length-1].id; }
        updateSupportStatus(payload.conversation);
        scrollDown();
    }

    function addSupportBanner(conv){
        var b=document.createElement('div'); b.className='ub-suphead';
        b.innerHTML='<div class="ub-suphead-i"><b>Support desk</b><span data-sup-status></span></div><button class="ub-supback" type="button">← Assistant</button>';
        b.querySelector('.ub-supback').addEventListener('click', function(){ greet(); });
        msgs.appendChild(b);
    }

    function updateSupportStatus(conv){
        if(!conv) return;
        state.supportAgentAvatar = conv.agent_avatar || null;
        if(statusText){ statusText.textContent = conv.agent_name ? ('with '+conv.agent_name) : (conv.resolved ? 'Resolved' : (conv.online ? 'Online' : 'Away')); }
        var s = msgs.querySelector('[data-sup-status]');
        if(s){
            if(conv.resolved){ s.textContent = 'This conversation is resolved'; }
            else if(conv.agent_name){ s.textContent = 'You’re chatting with '+conv.agent_name; }
            else { s.textContent = conv.online ? ('We’re online · typically reply in a few minutes') : ('We’re away right now · we’ll email you · '+(conv.hours||'')); }
        }
        applySupportResolvedState(conv);
    }

    // Resolved chats are terminal for the user (X/Google style): the composer is
    // locked and they get a "Start a new chat" CTA + a satisfaction rating.
    function applySupportResolvedState(conv){
        var bar = msgs.querySelector('[data-resolved-bar]');
        if(conv.resolved){
            state.supportResolved = true;
            if(input){ input.disabled=true; input.setAttribute('placeholder','This chat is resolved'); }
            if(sendBtn) sendBtn.classList.remove('active');
            hidePeerTyping();
            if(!bar){
                bar=document.createElement('div'); bar.className='ub-resolved'; bar.dataset.resolvedBar='1';
                var csatHtml = conv.csat_done ? '' :
                    '<div class="ub-csat"><div class="ub-csat-q">Was this resolved to your satisfaction?</div><div class="ub-csat-btns"><button type="button" data-csat="up" title="Yes">👍</button><button type="button" data-csat="down" title="No">👎</button></div></div>';
                bar.innerHTML='<div class="ub-resolved-t">This conversation was marked resolved. Need more help?</div><button class="ub-resolved-new" type="button">Start a new chat</button>'+csatHtml;
                bar.querySelector('.ub-resolved-new').addEventListener('click', function(){ startNewSupportChat(); });
                var up=bar.querySelector('[data-csat="up"]'); var down=bar.querySelector('[data-csat="down"]');
                if(up) up.addEventListener('click', function(){ sendCsat('up', bar); });
                if(down) down.addEventListener('click', function(){ sendCsat('down', bar); });
                msgs.appendChild(bar);
            } else {
                msgs.appendChild(bar); // keep pinned to the bottom
            }
            scrollDown();
        } else {
            state.supportResolved = false;
            if(input){ input.disabled=false; if(input.getAttribute('placeholder')==='This chat is resolved') input.setAttribute('placeholder','Message the support team…'); }
            if(bar) bar.remove();
        }
    }

    function startNewSupportChat(){
        state.convId=0; state.supportResolved=false; stopSupportPoll();
        if(input){ input.disabled=false; }
        startSupport();
    }

    function sendCsat(rating, bar){
        if(!state.convId) return;
        fetch(supUrl(CFG.support.csat, state.convId),{method:'POST',headers:hdrs(),body:JSON.stringify({rating:rating})})
            .then(function(r){return r.json();})
            .then(function(){ var c=bar.querySelector('.ub-csat'); if(c) c.innerHTML='<div class="ub-csat-done">Thanks for your feedback! 🙏</div>'; })
            .catch(function(){});
    }

    function showPeerTyping(){
        if(msgs.querySelector('[data-peer-typing]')) return;
        var row=document.createElement('div'); row.className='ub-row'; row.dataset.peerTyping='1';
        var av = state.supportAgentAvatar ? esc(state.supportAgentAvatar) : CFG.robot;
        row.innerHTML='<img class="ub-av" src="'+av+'" alt=""><div class="ub-typing"><i></i><i></i><i></i></div>';
        msgs.appendChild(row); scrollDown();
    }
    function hidePeerTyping(){ var t=msgs.querySelector('[data-peer-typing]'); if(t) t.remove(); }

    function addSupportBubble(m){
        if(m.sender==='system'){
            var sys=document.createElement('div'); sys.className='ub-sup-sys'; sys.textContent=m.body;
            msgs.appendChild(sys); return;
        }
        var isUser = (m.sender==='user');
        var row=document.createElement('div'); row.className='ub-row'+(isUser?' user':'');
        if(isUser){
            row.innerHTML='<div class="ub-bubble"><p></p><p class="ub-time">'+esc(m.time_h||'')+'</p></div>';
            row.querySelector('p').textContent=m.body;
        } else {
            var src = m.avatar ? esc(m.avatar) : CFG.robot;
            row.innerHTML='<img class="ub-av" src="'+src+'" alt=""><div class="ub-bubble"><span class="ub-agent-name">'+esc(m.name||'Support')+'</span><p></p><p class="ub-time">'+esc(m.time_h||'')+'</p></div>';
            row.querySelectorAll('p')[0].textContent=m.body;
        }
        msgs.appendChild(row);
    }

    function sendSupportMessage(text){
        if(state.supportSending || !state.convId || state.supportResolved) return;
        addSupportBubble({sender:'user', body:text, time_h:nowTime()}); scrollDown();
        state.supportSending=true;
        var cid=supCid('u');
        fetch(supUrl(CFG.support.message, state.convId),{method:'POST',headers:hdrs(),body:JSON.stringify({body:text, client_id:cid})})
            .then(function(r){return r.json();})
            .then(function(d){ if(d && d.message && d.message.id){ state.supportLastId=Math.max(state.supportLastId, d.message.id); } state.supportSending=false; })
            .catch(function(){ state.supportSending=false; });
    }

    function startSupportPoll(){
        stopSupportPoll();
        state.supportTimer=setInterval(function(){
            if(document.hidden || state.mode!=='support' || !state.convId || state.supportSending) return;
            fetch(supUrl(CFG.support.thread, state.convId)+'?after='+state.supportLastId,{headers:hdrs()})
                .then(function(r){return r.json();})
                .then(function(d){
                    if(d.messages && d.messages.length){
                        var atBottom=(msgs.scrollHeight - msgs.scrollTop - msgs.clientHeight) < 80;
                        hidePeerTyping();
                        d.messages.forEach(addSupportBubble);
                        state.supportLastId=d.messages[d.messages.length-1].id;
                        if(atBottom) scrollDown();
                    }
                    if(d.conversation) updateSupportStatus(d.conversation);
                    if(d.peer_typing && !state.supportResolved){ showPeerTyping(); } else { hidePeerTyping(); }
                }).catch(function(){});
        }, 4000);
    }
    function stopSupportPoll(){ if(state.supportTimer){ clearInterval(state.supportTimer); state.supportTimer=null; } }

    function resumeSupportIfAny(){
        // Only auto-resume from the plain bot view — never override an in-progress
        // intake, an open support thread, or the history list.
        if(!CFG.support.enabled || state.mode!=='bot') return;
        fetch(CFG.support.active,{headers:hdrs()})
            .then(function(r){return r.json();})
            .then(function(d){ if(d && d.conversation && !d.conversation.resolved){ enterSupportUI(d); } })
            .catch(function(){});
    }

    refreshUsage();
})();
</script>
@endverbatim
