@push('scripts')
<script>
(function(){
    var root = document.getElementById('sib');
    if(!root) return;

    var CFG = {
        list: root.dataset.list, counts: root.dataset.counts,
        show: root.dataset.show, messages: root.dataset.messages, reply: root.dataset.reply,
        claim: root.dataset.claim, resolve: root.dataset.resolve, reopen: root.dataset.reopen,
        csrf: root.dataset.csrf, me: parseInt(root.dataset.me, 10) || 0,
        preselect: parseInt(root.dataset.preselect, 10) || 0
    };
    function u(tpl, id){ return tpl.replace('__CID__', id); }
    function hdrs(){ return {'Content-Type':'application/json','X-CSRF-TOKEN':CFG.csrf,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}; }
    function esc(s){ return String(s==null?'':s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function initials(name){ return String(name||'?').trim().split(/\s+/).map(function(p){return p.charAt(0);}).slice(0,2).join('').toUpperCase() || '?'; }
    function uid(){ return 'a' + Date.now() + Math.random().toString(36).slice(2,8); }

    var el = {
        list: document.getElementById('sibList'), listEmpty: document.getElementById('sibListEmpty'),
        pane: document.getElementById('sibPane'), paneEmpty: document.getElementById('sibPaneEmpty'),
        wrap: document.getElementById('sibThreadWrap'), msgs: document.getElementById('sibMsgs'),
        thName: document.getElementById('sibThName'), thSub: document.getElementById('sibThSub'),
        thAvatar: document.getElementById('sibThAvatar'), thActions: document.getElementById('sibThActions'),
        context: document.getElementById('sibContext'), contextToggle: document.getElementById('sibContextToggle'),
        contextBody: document.getElementById('sibContextBody'),
        input: document.getElementById('sibInput'), send: document.getElementById('sibSend'),
        internal: document.getElementById('sibInternal'), composer: document.getElementById('sibComposer'),
        search: document.getElementById('sibSearch'), tabs: document.getElementById('sibTabs'),
        back: document.getElementById('sibBack')
    };

    var state = { filter:'open', q:'', activeId:0, activeConv:null, lastMsgId:0, sending:false, listTimer:null, msgTimer:null };

    // ---------- list ----------
    function fetchList(){
        var url = CFG.list + '?filter=' + encodeURIComponent(state.filter) + '&q=' + encodeURIComponent(state.q);
        fetch(url, {headers:hdrs()}).then(function(r){return r.json();}).then(function(d){
            renderList(d.conversations || []);
            updateCounts(d.counts || {});
        }).catch(function(){});
    }
    function updateCounts(c){
        ['open','unassigned','mine'].forEach(function(k){
            var n = root.querySelector('.sib-c[data-c="'+k+'"]');
            if(n) n.textContent = (c[k]!=null? c[k] : 0);
        });
    }
    function renderList(rows){
        el.list.innerHTML = '';
        el.listEmpty.style.display = rows.length ? 'none' : 'block';
        rows.forEach(function(c){
            var card = document.createElement('div');
            card.className = 'sib-card' + (c.id===state.activeId ? ' active' : '');
            card.setAttribute('data-id', c.id);
            var badge = c.resolved ? '<span class="sib-badge resolved">Resolved</span>'
                       : (c.status==='queued' ? '<span class="sib-badge queued">Queued</span>' : '<span class="sib-badge active">Active</span>');
            var cat = c.category ? '<span class="sib-badge cat">'+esc(c.category)+'</span>' : '';
            var wait = (c.waiting_min!=null && c.waiting_min>0) ? '<span class="sib-wait">'+c.waiting_min+'m waiting</span>' : '';
            var av = c.user_avatar ? '<img src="'+esc(c.user_avatar)+'" alt="" onerror="this.remove()">' : '';
            var unread = c.unread>0 ? '<span class="sib-unread">'+(c.unread>9?'9+':c.unread)+'</span>' : '';
            card.innerHTML =
                '<div class="sib-av">'+av+esc(initials(c.user_name))+'</div>'+
                '<div class="sib-card-main">'+
                    '<div class="sib-card-top"><span class="sib-card-name">'+esc(c.user_name)+'</span><span class="sib-card-time">'+esc(c.time_h||'')+'</span></div>'+
                    '<div class="sib-card-subj">'+esc(c.subject)+'</div>'+
                    '<div class="sib-card-snip">'+esc(c.snippet||'')+'</div>'+
                    '<div class="sib-card-meta">'+badge+cat+wait+'</div>'+
                '</div>'+ unread;
            card.addEventListener('click', function(){ openConversation(c.id); });
            el.list.appendChild(card);
        });
    }

    // ---------- thread ----------
    function openConversation(id){
        state.activeId = id; state.lastMsgId = 0;
        highlightActive();
        el.paneEmpty.style.display = 'none';
        el.wrap.style.display = 'flex';
        root.classList.add('show-thread');
        el.msgs.innerHTML = '';
        fetch(u(CFG.show, id), {headers:hdrs()}).then(function(r){return r.json();}).then(function(d){
            renderThread(d, false);
            startThreadPoll();
        }).catch(function(){});
    }
    function highlightActive(){
        root.querySelectorAll('.sib-card').forEach(function(c){
            c.classList.toggle('active', parseInt(c.getAttribute('data-id'),10)===state.activeId);
        });
    }
    function renderThread(d, append){
        var conv = d.conversation || {};
        state.activeConv = conv;
        if(!append){
            el.thName.textContent = (conv.user && conv.user.name) || 'User';
            el.thSub.textContent = (conv.subject || '') + (conv.category ? ' · '+conv.category : '');
            var av = conv.user && conv.user.avatar;
            el.thAvatar.innerHTML = (av ? '<img src="'+esc(av)+'" alt="" onerror="this.remove()">' : '') + esc(initials(conv.user && conv.user.name));
            renderActions(conv);
            renderContext(conv.context || []);
            el.msgs.innerHTML = '';
        } else {
            renderActions(conv); // keep action buttons in sync with status changes
        }
        (d.messages || []).forEach(function(m){ appendMessage(m); });
        if(d.messages && d.messages.length){ state.lastMsgId = d.messages[d.messages.length-1].id; }
        el.msgs.scrollTop = el.msgs.scrollHeight;
    }
    function renderContext(turns){
        if(!turns.length){ el.context.style.display='none'; return; }
        el.context.style.display='block';
        el.contextBody.innerHTML = turns.map(function(t){
            return '<div class="sib-ctx-turn"><b>'+(t.role==='assistant'?'Assistant':'User')+':</b> '+esc(t.content)+'</div>';
        }).join('');
    }
    function renderActions(conv){
        var html = '';
        if(conv.resolved){
            html = '<button class="sib-btn" data-act="reopen">Reopen</button>';
        } else {
            if(!conv.assigned_agent_id){ html += '<button class="sib-btn primary" data-act="claim">Claim</button>'; }
            html += '<button class="sib-btn green" data-act="resolve">Resolve</button>';
        }
        el.thActions.innerHTML = html;
        el.thActions.querySelectorAll('[data-act]').forEach(function(b){
            b.addEventListener('click', function(){ doAction(b.getAttribute('data-act')); });
        });
    }
    function appendMessage(m){
        if(m.sender==='system'){
            var s = document.createElement('div'); s.className='sib-row system';
            s.innerHTML = '<div class="sib-sys">'+esc(m.body)+'</div>';
            el.msgs.appendChild(s); return;
        }
        var isAgent = (m.sender==='agent');
        var row = document.createElement('div');
        row.className = 'sib-row ' + (isAgent?'agent':'user') + (m.is_internal?' internal':'');
        var avatar = m.avatar ? '<img src="'+esc(m.avatar)+'" alt="" onerror="this.remove()">' : '';
        row.innerHTML =
            '<div class="sib-mav">'+avatar+esc(initials(m.name||(isAgent?'A':'U')))+'</div>'+
            '<div><div class="sib-bubble">'+(m.is_internal?'📝 ':'')+esc(m.body)+'</div>'+
            '<span class="sib-time">'+esc(m.time_h||'')+(m.is_internal?' · internal note':'')+'</span></div>';
        el.msgs.appendChild(row);
    }

    function startThreadPoll(){
        stopThreadPoll();
        state.msgTimer = setInterval(function(){
            if(document.hidden || !state.activeId) return;
            fetch(u(CFG.messages, state.activeId)+'?after='+state.lastMsgId, {headers:hdrs()})
                .then(function(r){return r.json();})
                .then(function(d){
                    if(d.messages && d.messages.length){
                        var atBottom = (el.msgs.scrollHeight - el.msgs.scrollTop - el.msgs.clientHeight) < 60;
                        d.messages.forEach(function(m){ appendMessage(m); });
                        state.lastMsgId = d.messages[d.messages.length-1].id;
                        if(atBottom) el.msgs.scrollTop = el.msgs.scrollHeight;
                    }
                    if(d.conversation){ state.activeConv = d.conversation; renderActions(d.conversation); }
                }).catch(function(){});
        }, 4000);
    }
    function stopThreadPoll(){ if(state.msgTimer){ clearInterval(state.msgTimer); state.msgTimer=null; } }

    // ---------- send ----------
    function sendReply(){
        var body = el.input.value.trim();
        if(!body || state.sending || !state.activeId) return;
        state.sending = true; el.send.disabled = true;
        var internal = el.internal.checked;
        var cid = uid();
        fetch(u(CFG.reply, state.activeId), {method:'POST', headers:hdrs(), body:JSON.stringify({body:body, internal:internal, client_id:cid})})
            .then(function(r){return r.json();})
            .then(function(d){
                state.sending=false;
                if(d && d.ok && d.message){
                    appendMessage(d.message);
                    state.lastMsgId = Math.max(state.lastMsgId, d.message.id);
                    el.msgs.scrollTop = el.msgs.scrollHeight;
                    el.input.value=''; autosize(); refreshSend();
                    fetchList();
                } else { el.send.disabled=false; }
            })
            .catch(function(){ state.sending=false; el.send.disabled=false; });
    }
    function doAction(act){
        if(!state.activeId) return;
        var url = act==='claim'?CFG.claim : act==='resolve'?CFG.resolve : CFG.reopen;
        fetch(u(url, state.activeId), {method:'POST', headers:hdrs(), body:'{}'})
            .then(function(r){return r.json();})
            .then(function(){ // refresh thread + list
                fetch(u(CFG.messages, state.activeId)+'?after='+state.lastMsgId, {headers:hdrs()})
                    .then(function(r){return r.json();}).then(function(d){ renderThread(d, true); }).catch(function(){});
                fetchList();
            }).catch(function(){});
    }

    // ---------- composer helpers ----------
    function autosize(){ el.input.style.height='auto'; el.input.style.height=Math.min(140, el.input.scrollHeight)+'px'; }
    function refreshSend(){ el.send.disabled = !el.input.value.trim() || state.sending; }

    el.input.addEventListener('input', function(){ autosize(); refreshSend(); });
    el.input.addEventListener('keydown', function(e){ if(e.key==='Enter' && !e.shiftKey){ e.preventDefault(); sendReply(); } });
    el.send.addEventListener('click', sendReply);
    el.internal.addEventListener('change', function(){
        el.composer.classList.toggle('note-on', el.internal.checked);
        el.input.setAttribute('placeholder', el.internal.checked ? 'Write an internal note…' : 'Type your reply…');
    });
    el.back.addEventListener('click', function(){ root.classList.remove('show-thread'); state.activeId=0; stopThreadPoll(); highlightActive(); });
    el.contextToggle.addEventListener('click', function(){
        var open = el.contextBody.style.display!=='none';
        el.contextBody.style.display = open ? 'none' : 'block';
        el.contextToggle.classList.toggle('open', !open);
    });

    // ---------- tabs + search ----------
    el.tabs.querySelectorAll('.sib-tab').forEach(function(t){
        t.addEventListener('click', function(){
            el.tabs.querySelectorAll('.sib-tab').forEach(function(x){ x.classList.remove('active'); });
            t.classList.add('active');
            state.filter = t.getAttribute('data-filter');
            fetchList();
        });
    });
    var searchTimer=null;
    el.search.addEventListener('input', function(){
        clearTimeout(searchTimer);
        searchTimer = setTimeout(function(){ state.q = el.search.value.trim(); fetchList(); }, 300);
    });

    // ---------- boot ----------
    fetchList();
    state.listTimer = setInterval(function(){ if(!document.hidden) fetchList(); }, 12000);
    if(CFG.preselect){ setTimeout(function(){ openConversation(CFG.preselect); }, 400); }

    document.addEventListener('visibilitychange', function(){ if(!document.hidden){ fetchList(); } });
})();
</script>
@endpush
