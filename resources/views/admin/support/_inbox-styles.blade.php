@push('styles')
<style>
/* ===== Support Inbox — scoped (prefix: sib-) ============================
   Chat-first design (WhatsApp / Telegram / Messenger feel) on every device.
   Font pairing (scoped to #sib only — the rest of the app keeps Outfit):
     · Inter            → messages & UI text (crisp at small sizes, chat-native)
     · Plus Jakarta Sans → names, titles, tab labels (distinctive, premium)
   Mobile (≤767) becomes a real app: edge-to-edge, fixed bottom-nav tabs,
   full-screen conversation, 16px inputs (no iOS focus-zoom), pinch disabled. */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap');

#sib{
    --sib-text:#0f172a; --sib-text2:#64748b; --sib-text3:#94a3b8;
    --sib-border:#e8ecf3; --sib-border2:#eef1f6; --sib-bg:#ffffff; --sib-bg2:#f8fafc;
    --sib-brand:#1a4a9b; --sib-brand2:#eef3ff;
    --sib-green:#16a34a; --sib-amber:#f59e0b; --sib-red:#dc2626;
    --sib-nav-h:66px;
    font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    color:var(--sib-text);
    background:var(--sib-bg); border:1px solid var(--sib-border); border-radius:18px;
    box-shadow:0 10px 30px rgba(16,24,40,.05); overflow:hidden;
    -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale;
    touch-action:manipulation; /* kill double-tap zoom for an app-like feel */
}
#sib *{ box-sizing:border-box; }
#sib .sib-title, #sib .sib-card-name, #sib .sib-th-name, #sib .sib-tab__label,
#sib .sib-agent-name, #sib .sib-badge{ font-family:'Plus Jakarta Sans','Inter',sans-serif; }

.sib-top{ display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px 22px 14px; border-bottom:1px solid var(--sib-border2); flex-wrap:wrap; }
.sib-title{ margin:0; font-size:20px; font-weight:800; letter-spacing:-.02em; color:var(--sib-text); }
.sib-hours{ display:flex; align-items:center; gap:7px; font-size:12.5px; color:var(--sib-text2); margin-top:3px; }
.sib-dot{ width:8px; height:8px; border-radius:50%; display:inline-block; }
.sib-dot.on{ background:var(--sib-green); box-shadow:0 0 0 3px rgba(22,163,74,.15); }
.sib-dot.off{ background:var(--sib-text3); }
.sib-search{ position:relative; flex:1 1 260px; max-width:360px; }
.sib-search svg{ position:absolute; left:13px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:var(--sib-text3); }
.sib-search input{ width:100%; border:1.5px solid var(--sib-border); border-radius:12px; padding:10px 14px 10px 36px; font-size:14px; font-family:inherit; outline:none; color:var(--sib-text); transition:border-color .15s, box-shadow .15s; }
.sib-search input:focus{ border-color:var(--sib-brand); box-shadow:0 0 0 3px rgba(26,74,155,.10); }

/* Tabs — inline segmented on desktop */
.sib-tabs{ display:flex; gap:4px; padding:10px 20px 0; border-bottom:1px solid var(--sib-border2); overflow-x:auto; }
.sib-tab{ background:none; border:none; border-bottom:2.5px solid transparent; color:var(--sib-text2); font-size:13.5px; font-weight:600; font-family:inherit; padding:9px 12px 12px; cursor:pointer; white-space:nowrap; display:inline-flex; align-items:center; gap:7px; transition:color .15s, border-color .15s; }
.sib-tab:hover{ color:var(--sib-text); }
.sib-tab.active{ color:var(--sib-brand); border-bottom-color:var(--sib-brand); }
.sib-tab__ico{ display:none; width:22px; height:22px; }
.sib-c{ background:var(--sib-bg2); color:var(--sib-text2); font-size:11px; font-weight:700; border-radius:99px; padding:1px 7px; min-width:18px; text-align:center; }
.sib-tab.active .sib-c{ background:var(--sib-brand2); color:var(--sib-brand); }
.sib-c--zero{ display:none; }

.sib-work{ display:flex; height:min(68vh, 720px); min-height:440px; }
.sib-rail{ width:340px; flex:0 0 340px; border-right:1px solid var(--sib-border2); overflow-y:auto; background:var(--sib-bg2); }
.sib-pane{ flex:1 1 auto; min-width:0; display:flex; flex-direction:column; }

/* rail cards — WhatsApp/Gmail row feel */
.sib-card{ display:flex; gap:11px; padding:13px 16px; border-bottom:1px solid var(--sib-border2); cursor:pointer; transition:background .12s; position:relative; }
.sib-card:hover{ background:#fff; }
.sib-card.active{ background:#fff; box-shadow:inset 3px 0 0 var(--sib-brand); }
.sib-av{ flex:0 0 auto; width:46px; height:46px; border-radius:50%; background:linear-gradient(135deg,var(--sib-brand),#3b6fd4); color:#fff; display:flex; align-items:center; justify-content:center; font-size:15px; font-weight:700; overflow:hidden; position:relative; font-family:'Plus Jakarta Sans',sans-serif; }
.sib-av img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sib-card-main{ flex:1 1 auto; min-width:0; }
.sib-card-top{ display:flex; align-items:center; justify-content:space-between; gap:8px; }
.sib-card-name{ font-size:14.5px; font-weight:700; color:var(--sib-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sib-card-time{ font-size:11px; color:var(--sib-text3); flex:0 0 auto; }
.sib-card-subj{ font-size:12.5px; color:var(--sib-text); font-weight:500; margin:1px 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sib-card-snip{ font-size:12.5px; color:var(--sib-text2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sib-card-meta{ display:flex; align-items:center; gap:6px; margin-top:6px; }
.sib-badge{ font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:2px 8px; border-radius:20px; }
.sib-badge.queued{ background:#fef3c7; color:#92400e; }
.sib-badge.active{ background:#dcfce7; color:#166534; }
.sib-badge.resolved{ background:#e2e8f0; color:#475569; }
.sib-badge.cat{ background:var(--sib-brand2); color:var(--sib-brand); text-transform:none; letter-spacing:0; }
.sib-wait{ font-size:11px; color:var(--sib-red); font-weight:600; }
.sib-unread{ position:absolute; top:14px; right:14px; min-width:19px; height:19px; padding:0 5px; border-radius:99px; background:var(--sib-green); color:#fff; font-size:10.5px; font-weight:700; display:flex; align-items:center; justify-content:center; }

.sib-empty, .sib-pane-empty{ text-align:center; color:var(--sib-text3); padding:40px 20px; }
.sib-pane-empty{ margin:auto; display:flex; flex-direction:column; align-items:center; gap:12px; }
.sib-pane-empty svg{ width:44px; height:44px; color:#cbd5e1; }
.sib-pane-empty p{ margin:0; font-size:14px; }

/* thread — app-bar header */
.sib-thread-wrap{ display:flex; flex-direction:column; height:100%; }
.sib-th-head{ display:flex; align-items:center; gap:12px; padding:12px 16px; border-bottom:1px solid var(--sib-border2); background:#fff; }
.sib-back{ display:none; background:none; border:none; cursor:pointer; color:var(--sib-text2); padding:4px; }
.sib-back svg{ width:22px; height:22px; }
.sib-th-id{ display:flex; align-items:center; gap:11px; flex:1 1 auto; min-width:0; }
.sib-th-avatar{ flex:0 0 auto; width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--sib-brand),#3b6fd4); color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; overflow:hidden; position:relative; font-family:'Plus Jakarta Sans',sans-serif; }
.sib-th-avatar img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sib-th-name{ font-size:15.5px; font-weight:700; color:var(--sib-text); }
.sib-th-sub{ font-size:12px; color:var(--sib-text2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:260px; }
.sib-th-actions{ display:flex; gap:8px; flex:0 0 auto; }
.sib-btn{ display:inline-flex; align-items:center; gap:6px; border-radius:9px; font-size:12.5px; font-weight:600; font-family:inherit; padding:8px 13px; cursor:pointer; border:1.5px solid var(--sib-border); background:#fff; color:var(--sib-text2); transition:all .15s; }
.sib-btn:hover{ border-color:#cbd5e1; color:var(--sib-text); }
.sib-btn.primary{ background:var(--sib-brand); border-color:var(--sib-brand); color:#fff; }
.sib-btn.primary:hover{ background:#143a7c; color:#fff; }
.sib-btn.green{ background:var(--sib-green); border-color:var(--sib-green); color:#fff; }
.sib-btn.green:hover{ background:#15803d; color:#fff; }

/* bot context */
.sib-context{ border-bottom:1px solid var(--sib-border2); background:#fffdf7; }
.sib-context-toggle{ width:100%; text-align:left; background:none; border:none; cursor:pointer; padding:10px 18px; font-size:12.5px; font-weight:600; font-family:inherit; color:#92400e; display:flex; align-items:center; gap:8px; }
.sib-context-toggle svg{ width:13px; height:13px; transition:transform .18s; }
.sib-context-toggle.open svg{ transform:rotate(90deg); }
.sib-context-body{ padding:2px 18px 14px; }
.sib-ctx-turn{ font-size:12.5px; line-height:1.5; margin:6px 0; padding-left:10px; border-left:2px solid #fde68a; color:#57534e; }
.sib-ctx-turn b{ color:#78716c; }

/* messages — chat wallpaper + bubbles with tails */
.sib-msgs{ flex:1 1 auto; overflow-y:auto; padding:18px 16px; display:flex; flex-direction:column; gap:10px;
    background-color:#eef1f6;
    background-image:radial-gradient(rgba(15,23,42,.04) 1px, transparent 1px);
    background-size:22px 22px; }
.sib-row{ display:flex; gap:8px; max-width:82%; animation:sib-rise .16s ease; }
.sib-row.user{ align-self:flex-start; }
.sib-row.agent{ align-self:flex-end; flex-direction:row-reverse; }
.sib-row.system{ align-self:center; max-width:92%; }
@keyframes sib-rise{ from{opacity:0; transform:translateY(6px);} to{opacity:1; transform:translateY(0);} }
.sib-mav{ flex:0 0 auto; width:28px; height:28px; border-radius:50%; background:#cbd5e1; color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; overflow:hidden; position:relative; align-self:flex-end; }
.sib-mav img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sib-bubble{ position:relative; padding:9px 13px; border-radius:16px; font-size:14px; line-height:1.5; white-space:pre-wrap; word-wrap:break-word; box-shadow:0 1px 1.5px rgba(15,23,42,.08); }
.sib-row.user .sib-bubble{ background:#fff; color:var(--sib-text); border-bottom-left-radius:5px; }
.sib-row.agent .sib-bubble{ background:var(--sib-brand); color:#fff; border-bottom-right-radius:5px; }
.sib-row.agent.internal .sib-bubble{ background:#fffbeb; border:1px dashed var(--sib-amber); color:#92400e; box-shadow:none; }
/* little tails */
.sib-row.user .sib-bubble::before{ content:''; position:absolute; left:-5px; bottom:0; width:12px; height:12px; background:#fff; border-bottom-right-radius:12px; clip-path:polygon(100% 0,100% 100%,0 100%); }
.sib-row.agent:not(.internal) .sib-bubble::before{ content:''; position:absolute; right:-5px; bottom:0; width:12px; height:12px; background:var(--sib-brand); border-bottom-left-radius:12px; clip-path:polygon(0 0,100% 100%,0 100%); }
.sib-agent-name{ display:block; font-size:11px; font-weight:700; color:#dbe4f5; margin-bottom:2px; }
.sib-row.agent.internal .sib-agent-name{ color:#b45309; }
.sib-time{ font-size:10px; color:var(--sib-text3); margin-top:3px; display:block; }
.sib-row.agent .sib-time{ text-align:right; }
.sib-sys{ font-size:11.5px; color:#475569; background:rgba(255,255,255,.85); border-radius:20px; padding:5px 14px; text-align:center; box-shadow:0 1px 2px rgba(15,23,42,.06); }

/* composer */
.sib-composer{ border-top:1px solid var(--sib-border2); padding:12px 16px 14px; background:#fff; }
.sib-note-toggle{ margin-bottom:8px; }
.sib-switch{ display:inline-flex; align-items:center; gap:7px; font-size:12px; color:var(--sib-text2); cursor:pointer; }
.sib-switch input{ accent-color:var(--sib-amber); }
.sib-composer-row{ display:flex; gap:9px; align-items:flex-end; }
.sib-composer.note-on .sib-composer-row textarea{ background:#fffbeb; border-color:var(--sib-amber); }
.sib-composer textarea{ flex:1 1 auto; resize:none; max-height:140px; border:1.5px solid var(--sib-border); border-radius:22px; padding:11px 16px; font-size:14px; font-family:inherit; line-height:1.4; outline:none; color:var(--sib-text); transition:border-color .15s, box-shadow .15s; }
.sib-composer textarea:focus{ border-color:var(--sib-brand); box-shadow:0 0 0 3px rgba(26,74,155,.10); }
.sib-send{ flex:0 0 auto; width:44px; height:44px; border-radius:50%; border:none; background:var(--sib-brand); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s, opacity .15s, transform .1s; }
.sib-send svg{ width:18px; height:18px; }
.sib-send:hover{ background:#143a7c; }
.sib-send:active{ transform:scale(.92); }
.sib-send:disabled{ opacity:.4; cursor:not-allowed; }
.sib-composer.note-on .sib-send{ background:var(--sib-amber); }

/* chat wallpaper (Super-Admin set) — painted on the non-scrolling pane (clipped),
   NOT the scrolling message list, so it fits exactly and never adds a scrollbar */
.sib-pane.sib-has-wall{ position:relative; }
/* Clip the scaled blur on desktop (pane sits beside the rail). On mobile the
   full-width pane is already clipped by #sib, and no overflow context is added
   so the sticky composer keeps working. */
@media (min-width:768px){ .sib-pane.sib-has-wall{ overflow:hidden; } }
.sib-pane.sib-has-wall::before{ content:''; position:absolute; inset:0; background-image:var(--sib-wall); background-size:cover; background-position:center; filter:blur(11px); transform:scale(1.1); opacity:.45; z-index:0; pointer-events:none; }
.sib-pane.sib-has-wall::after{ content:''; position:absolute; inset:0; background:rgba(238,241,246,.6); z-index:0; pointer-events:none; }
.sib-pane.sib-has-wall > *{ position:relative; z-index:1; }
.sib-pane.sib-has-wall .sib-msgs{ background-color:transparent; background-image:none; }

/* typing indicator (the user is typing) */
.sib-typing-row{ display:flex; gap:8px; align-self:flex-start; max-width:82%; }
.sib-typing-dots{ display:flex; gap:5px; align-items:center; padding:11px 14px; border-radius:16px; background:#fff; border:1px solid var(--sib-border); box-shadow:0 1px 1.5px rgba(15,23,42,.08); }
.sib-typing-dots i{ width:7px; height:7px; border-radius:50%; background:var(--sib-text3); display:inline-block; animation:sib-bounce .65s infinite ease-in-out; }
.sib-typing-dots i:nth-child(2){ animation-delay:.16s; } .sib-typing-dots i:nth-child(3){ animation-delay:.32s; }
@keyframes sib-bounce{ 0%,100%{ transform:translateY(0); opacity:.5; } 50%{ transform:translateY(-5px); opacity:1; } }

/* CSAT satisfaction badge in the thread header */
.sib-csat{ display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:700; padding:2px 9px; border-radius:20px; margin-left:8px; }
.sib-csat.up{ background:#dcfce7; color:#166534; }
.sib-csat.down{ background:#fee2e2; color:#991b1b; }

/* read-only lock (another agent owns the chat) */
.sib-lock{ display:flex; align-items:center; gap:10px; padding:14px 18px; border-top:1px solid var(--sib-border2); background:#f8fafc; color:var(--sib-text2); font-size:13px; }
.sib-lock svg{ width:18px; height:18px; flex:0 0 auto; color:var(--sib-text3); }
.sib-lock b{ color:var(--sib-text); font-weight:700; }

.sib-rail::-webkit-scrollbar,.sib-msgs::-webkit-scrollbar{ width:7px; }
.sib-rail::-webkit-scrollbar-thumb,.sib-msgs::-webkit-scrollbar-thumb{ background:rgba(100,116,139,.25); border-radius:99px; }

/* ════════════════════════════════════════════════════════════════════════
   MOBILE (≤767) — real app: edge-to-edge, bottom-nav tabs, full-screen chat.
   De-squish trio is safe here (sidebar is an off-canvas drawer below 992px).
   ════════════════════════════════════════════════════════════════════════ */
@media (max-width:767px){
    /* De-squish trio — this pushed stylesheet only loads on the inbox page */
    .dashboard > .container-fluid > .row{ margin-left:0 !important; margin-right:0 !important; }
    .dashboard .container-fluid.full__width__padding{ padding-left:0 !important; padding-right:0 !important; }
    .dashboard .row > .col-xl-9{ padding-left:0 !important; padding-right:0 !important; }

    /* Edge-to-edge card */
    #sib{ border:none; border-radius:0; box-shadow:none; }

    .sib-top{ padding:14px 16px 12px; }
    .sib-title{ font-size:19px; }
    .sib-search{ flex-basis:100%; max-width:none; order:3; }
    .sib-search input{ font-size:16px; } /* no iOS focus-zoom */

    /* Tabs → fixed bottom navigation (icon over label), settings-page idiom */
    .sib-tabs{
        position:fixed; left:0; right:0; bottom:0; z-index:1040;
        padding:6px 6px calc(6px + env(safe-area-inset-bottom));
        gap:0; border-top:1px solid var(--sib-border2); border-bottom:none;
        background:rgba(255,255,255,.94); backdrop-filter:saturate(180%) blur(12px);
        box-shadow:0 -6px 20px rgba(15,23,42,.06); overflow:visible;
    }
    .sib-tab{
        flex:1 1 0; flex-direction:column; gap:3px; padding:8px 2px 6px;
        border-bottom:none; position:relative; color:var(--sib-text3); font-size:.7rem; font-weight:600;
    }
    .sib-tab__ico{ display:block; width:23px; height:23px; }
    .sib-tab__label{ font-size:.66rem; line-height:1; }
    .sib-tab.active{ color:var(--sib-brand); border-bottom:none; }
    .sib-tab.active::before{ content:''; position:absolute; top:-6px; left:50%; transform:translateX(-50%); width:26px; height:3px; border-radius:0 0 3px 3px; background:var(--sib-brand); }
    /* count → red corner badge on the icon */
    .sib-tab .sib-c{ position:absolute; top:2px; left:calc(50% + 8px); min-width:16px; height:16px; padding:0 4px; background:var(--sib-red); color:#fff; font-size:9.5px; line-height:16px; }
    .sib-tab.active .sib-c{ background:var(--sib-red); color:#fff; }

    /* Rail = full-width chat list; leave room for the bottom nav */
    .sib-work{ display:block; height:auto; min-height:0; }
    .sib-rail{ width:100%; flex-basis:auto; border-right:none; background:#fff; padding-bottom:calc(var(--sib-nav-h) + 8px); }
    .sib-pane{ display:none; }
    .sib-card{ padding:12px 16px; }

    /* Open a conversation = full-screen chat, bottom nav + list header gone */
    #sib.show-thread .sib-top{ display:none; }
    #sib.show-thread .sib-tabs{ display:none; }
    #sib.show-thread .sib-rail{ display:none; }
    #sib.show-thread .sib-pane{ display:flex; }
    .sib-back{ display:inline-flex; }
    .sib-th-sub{ max-width:52vw; }
    .sib-th-actions .sib-btn{ padding:8px 10px; }
    /* fixed internal-scroll messages (memo-chat idiom) so header + composer
       stay put without fighting the global sticky app-bar */
    .sib-msgs{ height:60vh; min-height:0; }
    .sib-composer{ position:sticky; bottom:0; padding-bottom:calc(14px + env(safe-area-inset-bottom)); }
    .sib-composer textarea{ font-size:16px; } /* no iOS focus-zoom */
    .sib-bubble{ font-size:15px; }
    .sib-row{ max-width:86%; }
}
</style>
@endpush
