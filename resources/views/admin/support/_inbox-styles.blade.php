@push('styles')
<style>
/* ===== Support Inbox — scoped (prefix: sib-) ============================ */
@import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&display=swap');

#sib{
    --sib-text:#0f172a; --sib-text2:#64748b; --sib-text3:#94a3b8;
    --sib-border:#e8ecf3; --sib-border2:#eef1f6; --sib-bg:#ffffff; --sib-bg2:#f8fafc;
    --sib-brand:#1a4a9b; --sib-brand2:#eef3ff;
    --sib-green:#16a34a; --sib-amber:#f59e0b; --sib-red:#dc2626;
    font-family:'Outfit',-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    color:var(--sib-text);
    background:var(--sib-bg); border:1px solid var(--sib-border); border-radius:18px;
    box-shadow:0 10px 30px rgba(16,24,40,.05); overflow:hidden;
}
#sib *{ box-sizing:border-box; font-family:inherit; }

.sib-top{ display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px 22px 14px; border-bottom:1px solid var(--sib-border2); flex-wrap:wrap; }
.sib-title{ margin:0; font-size:19px; font-weight:800; letter-spacing:-.02em; color:var(--sib-text); }
.sib-hours{ display:flex; align-items:center; gap:7px; font-size:12.5px; color:var(--sib-text2); margin-top:3px; }
.sib-dot{ width:8px; height:8px; border-radius:50%; display:inline-block; }
.sib-dot.on{ background:var(--sib-green); box-shadow:0 0 0 3px rgba(22,163,74,.15); }
.sib-dot.off{ background:var(--sib-text3); }
.sib-search{ position:relative; flex:1 1 260px; max-width:360px; }
.sib-search svg{ position:absolute; left:13px; top:50%; transform:translateY(-50%); width:15px; height:15px; color:var(--sib-text3); }
.sib-search input{ width:100%; border:1.5px solid var(--sib-border); border-radius:11px; padding:9px 14px 9px 36px; font-size:13.5px; outline:none; color:var(--sib-text); transition:border-color .15s, box-shadow .15s; }
.sib-search input:focus{ border-color:var(--sib-brand); box-shadow:0 0 0 3px rgba(26,74,155,.10); }

.sib-tabs{ display:flex; gap:4px; padding:10px 20px 0; border-bottom:1px solid var(--sib-border2); overflow-x:auto; }
.sib-tab{ background:none; border:none; border-bottom:2.5px solid transparent; color:var(--sib-text2); font-size:13.5px; font-weight:600; padding:9px 10px 12px; cursor:pointer; white-space:nowrap; display:inline-flex; align-items:center; gap:7px; transition:color .15s, border-color .15s; }
.sib-tab:hover{ color:var(--sib-text); }
.sib-tab.active{ color:var(--sib-brand); border-bottom-color:var(--sib-brand); }
.sib-c{ background:var(--sib-bg2); color:var(--sib-text2); font-size:11px; font-weight:700; border-radius:99px; padding:1px 7px; min-width:18px; text-align:center; }
.sib-tab.active .sib-c{ background:var(--sib-brand2); color:var(--sib-brand); }

.sib-work{ display:flex; height:min(68vh, 720px); min-height:440px; }
.sib-rail{ width:340px; flex:0 0 340px; border-right:1px solid var(--sib-border2); overflow-y:auto; background:var(--sib-bg2); }
.sib-pane{ flex:1 1 auto; min-width:0; display:flex; flex-direction:column; }

/* rail cards */
.sib-card{ display:flex; gap:11px; padding:13px 16px; border-bottom:1px solid var(--sib-border2); cursor:pointer; transition:background .12s; position:relative; }
.sib-card:hover{ background:#fff; }
.sib-card.active{ background:#fff; box-shadow:inset 3px 0 0 var(--sib-brand); }
.sib-av{ flex:0 0 auto; width:40px; height:40px; border-radius:50%; background:linear-gradient(135deg,var(--sib-brand),#3b6fd4); color:#fff; display:flex; align-items:center; justify-content:center; font-size:14px; font-weight:700; overflow:hidden; position:relative; }
.sib-av img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sib-card-main{ flex:1 1 auto; min-width:0; }
.sib-card-top{ display:flex; align-items:center; justify-content:space-between; gap:8px; }
.sib-card-name{ font-size:14px; font-weight:700; color:var(--sib-text); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sib-card-time{ font-size:11px; color:var(--sib-text3); flex:0 0 auto; }
.sib-card-subj{ font-size:12.5px; color:var(--sib-text); font-weight:500; margin:1px 0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sib-card-snip{ font-size:12px; color:var(--sib-text2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.sib-card-meta{ display:flex; align-items:center; gap:6px; margin-top:5px; }
.sib-badge{ font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.04em; padding:2px 7px; border-radius:20px; }
.sib-badge.queued{ background:#fef3c7; color:#92400e; }
.sib-badge.active{ background:#dcfce7; color:#166534; }
.sib-badge.resolved{ background:#e2e8f0; color:#475569; }
.sib-badge.cat{ background:var(--sib-brand2); color:var(--sib-brand); text-transform:none; letter-spacing:0; }
.sib-wait{ font-size:11px; color:var(--sib-red); font-weight:600; }
.sib-unread{ position:absolute; top:14px; right:14px; min-width:18px; height:18px; padding:0 5px; border-radius:99px; background:var(--sib-brand); color:#fff; font-size:10.5px; font-weight:700; display:flex; align-items:center; justify-content:center; }

.sib-empty, .sib-pane-empty{ text-align:center; color:var(--sib-text3); padding:40px 20px; }
.sib-pane-empty{ margin:auto; display:flex; flex-direction:column; align-items:center; gap:12px; }
.sib-pane-empty svg{ width:44px; height:44px; color:#cbd5e1; }
.sib-pane-empty p{ margin:0; font-size:14px; }

/* thread */
.sib-thread-wrap{ display:flex; flex-direction:column; height:100%; }
.sib-th-head{ display:flex; align-items:center; gap:12px; padding:13px 18px; border-bottom:1px solid var(--sib-border2); }
.sib-back{ display:none; background:none; border:none; cursor:pointer; color:var(--sib-text2); padding:4px; }
.sib-back svg{ width:20px; height:20px; }
.sib-th-id{ display:flex; align-items:center; gap:11px; flex:1 1 auto; min-width:0; }
.sib-th-avatar{ flex:0 0 auto; width:38px; height:38px; border-radius:50%; background:linear-gradient(135deg,var(--sib-brand),#3b6fd4); color:#fff; display:flex; align-items:center; justify-content:center; font-size:13px; font-weight:700; overflow:hidden; position:relative; }
.sib-th-avatar img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sib-th-name{ font-size:15px; font-weight:700; color:var(--sib-text); }
.sib-th-sub{ font-size:12px; color:var(--sib-text2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap; max-width:260px; }
.sib-th-actions{ display:flex; gap:8px; flex:0 0 auto; }
.sib-btn{ display:inline-flex; align-items:center; gap:6px; border-radius:9px; font-size:12.5px; font-weight:600; padding:8px 13px; cursor:pointer; border:1.5px solid var(--sib-border); background:#fff; color:var(--sib-text2); transition:all .15s; }
.sib-btn:hover{ border-color:#cbd5e1; color:var(--sib-text); }
.sib-btn.primary{ background:var(--sib-brand); border-color:var(--sib-brand); color:#fff; }
.sib-btn.primary:hover{ background:#143a7c; color:#fff; }
.sib-btn.green{ background:var(--sib-green); border-color:var(--sib-green); color:#fff; }
.sib-btn.green:hover{ background:#15803d; color:#fff; }

/* bot context */
.sib-context{ border-bottom:1px solid var(--sib-border2); background:#fffdf7; }
.sib-context-toggle{ width:100%; text-align:left; background:none; border:none; cursor:pointer; padding:10px 18px; font-size:12.5px; font-weight:600; color:#92400e; display:flex; align-items:center; gap:8px; }
.sib-context-toggle svg{ width:13px; height:13px; transition:transform .18s; }
.sib-context-toggle.open svg{ transform:rotate(90deg); }
.sib-context-body{ padding:2px 18px 14px; }
.sib-ctx-turn{ font-size:12.5px; line-height:1.5; margin:6px 0; padding-left:10px; border-left:2px solid #fde68a; color:#57534e; }
.sib-ctx-turn b{ color:#78716c; }

/* messages */
.sib-msgs{ flex:1 1 auto; overflow-y:auto; padding:18px; display:flex; flex-direction:column; gap:12px; background:var(--sib-bg2); }
.sib-row{ display:flex; gap:9px; max-width:82%; }
.sib-row.user{ align-self:flex-start; }
.sib-row.agent{ align-self:flex-end; flex-direction:row-reverse; }
.sib-row.system{ align-self:center; max-width:90%; }
.sib-mav{ flex:0 0 auto; width:28px; height:28px; border-radius:50%; background:#cbd5e1; color:#fff; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; overflow:hidden; position:relative; }
.sib-mav img{ position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
.sib-bubble{ padding:9px 13px; border-radius:14px; font-size:13.5px; line-height:1.5; white-space:pre-wrap; word-wrap:break-word; }
.sib-row.user .sib-bubble{ background:#fff; border:1px solid var(--sib-border); border-bottom-left-radius:4px; color:var(--sib-text); }
.sib-row.agent .sib-bubble{ background:var(--sib-brand); color:#fff; border-bottom-right-radius:4px; }
.sib-row.agent.internal .sib-bubble{ background:#fffbeb; border:1px dashed var(--sib-amber); color:#92400e; }
.sib-time{ font-size:10px; color:var(--sib-text3); margin-top:3px; display:block; }
.sib-row.agent .sib-time{ text-align:right; }
.sib-sys{ font-size:11.5px; color:var(--sib-text2); background:#fff; border:1px solid var(--sib-border2); border-radius:20px; padding:5px 12px; text-align:center; }

/* composer */
.sib-composer{ border-top:1px solid var(--sib-border2); padding:12px 16px 14px; background:#fff; }
.sib-note-toggle{ margin-bottom:8px; }
.sib-switch{ display:inline-flex; align-items:center; gap:7px; font-size:12px; color:var(--sib-text2); cursor:pointer; }
.sib-switch input{ accent-color:var(--sib-amber); }
.sib-composer-row{ display:flex; gap:9px; align-items:flex-end; }
.sib-composer.note-on .sib-composer-row textarea{ background:#fffbeb; border-color:var(--sib-amber); }
.sib-composer textarea{ flex:1 1 auto; resize:none; max-height:140px; border:1.5px solid var(--sib-border); border-radius:12px; padding:10px 14px; font-size:13.5px; line-height:1.4; outline:none; color:var(--sib-text); transition:border-color .15s, box-shadow .15s; }
.sib-composer textarea:focus{ border-color:var(--sib-brand); box-shadow:0 0 0 3px rgba(26,74,155,.10); }
.sib-send{ flex:0 0 auto; width:42px; height:42px; border-radius:12px; border:none; background:var(--sib-brand); color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .15s, opacity .15s; }
.sib-send svg{ width:17px; height:17px; }
.sib-send:disabled{ opacity:.4; cursor:not-allowed; }
.sib-composer.note-on .sib-send{ background:var(--sib-amber); }

.sib-rail::-webkit-scrollbar,.sib-msgs::-webkit-scrollbar{ width:7px; }
.sib-rail::-webkit-scrollbar-thumb,.sib-msgs::-webkit-scrollbar-thumb{ background:rgba(100,116,139,.25); border-radius:99px; }

/* ── Mobile: single column, rail ↔ thread swap ── */
@media (max-width:767px){
    .sib-work{ height:auto; min-height:0; }
    .sib-rail{ width:100%; flex-basis:auto; max-height:none; border-right:none; }
    .sib-pane{ display:none; }
    #sib.show-thread .sib-rail{ display:none; }
    #sib.show-thread .sib-pane{ display:flex; }
    .sib-work{ display:block; }
    #sib.show-thread .sib-work{ display:block; }
    .sib-msgs{ height:56vh; }
    .sib-back{ display:inline-flex; }
    .sib-th-sub{ max-width:150px; }
}
</style>
@endpush
