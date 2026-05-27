{{--
    Picks a specific user inside an Office to receive the next stage.
    - $office     : App\Models\Office or null
    - $fieldName  : name attribute (e.g. 'next_assignee_id')
    - $required   : bool
    - $selectedId : optional preselect
--}}
@php
    $selectedId = $selectedId ?? null;
    $members    = $office ? $office->users->where('pivot.is_active', true) : collect();
    $head       = $members->where('pivot.is_head', true)->first();
@endphp

@if(!$office)
    <div class="alert alert-warning" style="margin: 0;">
        No downstream office is configured for this stage. Ask a Super Admin to set one up.
    </div>
@elseif($members->isEmpty())
    <div class="alert alert-warning" style="margin: 0;">
        <strong>{{ $office->name }}</strong> has no active members yet. Ask a Super Admin to assign people to this office before forwarding.
    </div>
@else
    <div class="recipient-picker">
        <div class="recipient-picker__label">
            <strong>{{ $office->name }}</strong> — choose who in this office receives the form.
        </div>
        <select name="{{ $fieldName }}" class="form-select" {{ $required ? 'required' : '' }}>
            <option value="">— Select a recipient —</option>
            @foreach($members as $member)
                @php
                    $isHead = (bool) ($member->pivot->is_head ?? false);
                    $isSelected = $selectedId
                        ? (int) $selectedId === (int) $member->id
                        : ($head ? (int) $head->id === (int) $member->id : false);
                @endphp
                <option value="{{ $member->id }}" @selected($isSelected)>
                    {{ trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? '')) }}
                    @if($isHead) (Head) @endif
                </option>
            @endforeach
        </select>
    </div>
@endif

<style>
.recipient-picker__label { font-size: 13px; color: #4b5563; margin-bottom: 8px; }
</style>
