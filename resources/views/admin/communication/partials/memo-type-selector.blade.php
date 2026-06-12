{{--
    Compact, optional "Memo Type" selector — shared by both compose screens
    (normal user: admin.communication.create, admin: admin.communication-admin.create).

    Each option carries a hover/focus popover that explains what it means and
    which form it unlocks once approved. Optionally pass $memoCategory (e.g. on an
    edit screen) to pre-select a value; old() input always wins on redirects.
--}}
@php
    $mtSelected = old('memo_category', $memoCategory ?? '');
    $mtOptions = [
        '' => [
            'label'  => 'General',
            'tag'    => 'General',
            'title'  => 'General Memo',
            'desc'   => 'Ordinary communication or announcement. This does not lead to any form — just write your message and send.',
            'accent' => '#94a3b8',
        ],
        'promotion' => [
            'label'  => 'Promotion',
            'tag'    => 'Promotion',
            'title'  => 'Promotion Memo',
            'desc'   => 'Request a promotion or renewal of appointment. Once approved, you can proceed to fill the Promotion / Renewal of Appointment form.',
            'accent' => '#2563eb',
        ],
        'procurement' => [
            'label'  => 'Procurement',
            'tag'    => 'Procurement',
            'title'  => 'Procurement Memo',
            'desc'   => 'Request a purchase or payment. Once approved, you can proceed to fill the Payment Requisition or Purchase & Works Authorization form.',
            'accent' => '#16a34a',
        ],
        'leave' => [
            'label'  => 'Leave',
            'tag'    => 'Leave',
            'title'  => 'Leave Memo',
            'desc'   => 'Request time off. Once approved, you can proceed to fill the Annual, Casual or Leave Resumption form.',
            'accent' => '#d97706',
        ],
        'other' => [
            'label'  => 'Other',
            'tag'    => 'Other',
            'title'  => 'Other Request',
            'desc'   => 'A specific request that does not map to a standard form. Approval simply records the decision on the memo.',
            'accent' => '#64748b',
        ],
    ];
@endphp

<div class="form-group">
    <label class="form-label" style="margin-bottom:2px;">
        <i class="icofont-tag"></i> Memo Type
        <span style="font-weight:500;color:#94a3b8;font-size:12px;">(optional)</span>
    </label>
    <p class="form-help" style="margin-bottom:8px;">
        Pick a type only if this memo is a request that leads to a form once approved.
        <span style="color:#94a3b8;">Hover an option to see what it does.</span>
    </p>
    <div class="mtype-radios">
        @foreach($mtOptions as $val => $opt)
            <span class="mtype-wrap">
                <label class="mtype-radio">
                    <input type="radio" name="memo_category" value="{{ $val }}" {{ $mtSelected === $val ? 'checked' : '' }}>
                    <span class="mtype-text">{{ $opt['label'] }}</span>
                </label>
                <span class="mtype-pop" role="tooltip">
                    <span class="mtype-pop-tag" style="background:{{ $opt['accent'] }};">{{ $opt['tag'] }}</span>
                    <span class="mtype-pop-title">{{ $opt['title'] }}</span>
                    <span class="mtype-pop-desc">{{ $opt['desc'] }}</span>
                    <span class="mtype-pop-arrow"></span>
                </span>
            </span>
        @endforeach
    </div>
</div>

<style>
    .mtype-radios { display:flex; flex-wrap:wrap; gap:10px 22px; align-items:center; padding:2px 0; }

    /* anchor for each option's popover */
    .mtype-wrap { position:relative; display:inline-flex; }

    .mtype-radio { display:inline-flex; align-items:center; gap:7px; cursor:pointer; margin:0; user-select:none; }
    .mtype-radio input {
        appearance:none; -webkit-appearance:none;
        width:16px; height:16px; margin:0; border:2px solid #cbd5e1; border-radius:50%;
        position:relative; cursor:pointer; flex:0 0 auto;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .mtype-radio input:hover { border-color:#1a4a9b; }
    .mtype-radio input:checked { border-color:#1a4a9b; }
    .mtype-radio input:checked::after { content:""; position:absolute; inset:2px; border-radius:50%; background:#1a4a9b; }
    .mtype-radio input:focus-visible { box-shadow:0 0 0 3px rgba(26,74,155,.18); outline:none; }

    .mtype-text {
        font-size:13.5px; font-weight:600; color:#475569;
        border-bottom:1px dashed #cbd5e1; line-height:1.3;
        transition:color .15s ease, border-color .15s ease;
    }
    .mtype-radio input:checked + .mtype-text { color:#1a4a9b; border-bottom-color:#1a4a9b; }
    .mtype-wrap:hover .mtype-text { color:#1a4a9b; border-bottom-color:#94a3b8; }

    /* ── hover/focus popover (matches the reference card) ── */
    .mtype-pop {
        position:absolute; bottom:calc(100% + 12px); left:50%; transform:translateX(-50%);
        width:262px; max-width:80vw;
        background:#1f2430; color:#e7eaf0; border-radius:14px; padding:14px 16px;
        box-shadow:0 18px 42px rgba(15,23,42,.34), 0 4px 12px rgba(15,23,42,.24);
        text-align:left; z-index:60;
        opacity:0; visibility:hidden; pointer-events:none;
        transition:opacity .16s ease, visibility .16s;
    }
    .mtype-wrap:hover .mtype-pop,
    .mtype-wrap:focus-within .mtype-pop { opacity:1; visibility:visible; pointer-events:auto; }

    .mtype-pop-tag {
        display:inline-block; font-size:10.5px; font-weight:800; letter-spacing:.6px;
        text-transform:uppercase; color:#fff; padding:3px 11px; border-radius:20px; margin-bottom:9px;
    }
    .mtype-pop-title { display:block; font-size:14px; font-weight:800; color:#fff; margin-bottom:5px; }
    .mtype-pop-desc  { display:block; font-size:12px; line-height:1.55; color:#aeb6c6; }

    .mtype-pop-arrow {
        position:absolute; top:100%; left:50%; transform:translateX(-50%);
        width:0; height:0;
        border-left:8px solid transparent; border-right:8px solid transparent;
        border-top:9px solid #1f2430;
    }

    /* keep the first/last popovers inside the panel instead of centering */
    .mtype-wrap:first-child .mtype-pop { left:0; right:auto; transform:none; }
    .mtype-wrap:first-child .mtype-pop-arrow { left:22px; transform:none; }
    .mtype-wrap:last-child  .mtype-pop { left:auto; right:0; transform:none; }
    .mtype-wrap:last-child  .mtype-pop-arrow { left:auto; right:22px; transform:none; }

    @media (max-width:600px) {
        .mtype-pop { width:230px; }
    }
</style>
