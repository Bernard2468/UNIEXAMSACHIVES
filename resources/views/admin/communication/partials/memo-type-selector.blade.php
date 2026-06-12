{{--
    Compact, optional "Memo Type" selector — shared by both compose screens
    (normal user: admin.communication.create, admin: admin.communication-admin.create).

    Optionally pass $memoCategory (e.g. on an edit screen) to pre-select a value;
    old() input always wins on validation redirects.
--}}
@php
    $mtSelected = old('memo_category', $memoCategory ?? '');
    $mtOptions = [
        ''            => 'General',
        'promotion'   => 'Promotion',
        'procurement' => 'Procurement',
        'leave'       => 'Leave',
        'other'       => 'Other',
    ];
@endphp

<div class="form-group">
    <label class="form-label" style="margin-bottom:2px;">
        <i class="icofont-tag"></i> Memo Type
        <span style="font-weight:500;color:#94a3b8;font-size:12px;">(optional)</span>
    </label>
    <p class="form-help" style="margin-bottom:8px;">
        Pick a type only if this memo is a request that leads to a form once approved
        (promotion, procurement, leave). Leave it as <strong>General</strong> for an ordinary memo.
    </p>
    <div class="mtype-radios">
        @foreach($mtOptions as $val => $label)
            <label class="mtype-radio">
                <input type="radio" name="memo_category" value="{{ $val }}" {{ $mtSelected === $val ? 'checked' : '' }}>
                <span>{{ $label }}</span>
            </label>
        @endforeach
    </div>
</div>

<style>
    .mtype-radios { display:flex; flex-wrap:wrap; gap:9px 20px; align-items:center; padding:2px 0; }
    .mtype-radio { display:inline-flex; align-items:center; gap:7px; cursor:pointer; margin:0; user-select:none; }
    .mtype-radio input {
        appearance:none; -webkit-appearance:none;
        width:16px; height:16px; margin:0; border:2px solid #cbd5e1; border-radius:50%;
        position:relative; cursor:pointer; flex:0 0 auto;
        transition:border-color .15s ease, box-shadow .15s ease;
    }
    .mtype-radio input:hover { border-color:#1a4a9b; }
    .mtype-radio input:checked { border-color:#1a4a9b; }
    .mtype-radio input:checked::after {
        content:""; position:absolute; inset:2px; border-radius:50%; background:#1a4a9b;
    }
    .mtype-radio input:focus-visible { box-shadow:0 0 0 3px rgba(26,74,155,.18); outline:none; }
    .mtype-radio span { font-size:13.5px; font-weight:600; color:#475569; transition:color .15s ease; }
    .mtype-radio input:checked + span { color:#1a4a9b; }
</style>
