{{--
    Picks a specific user inside an Office to receive the next stage.
    Compose-memo style: searchable, single-select user cards with avatars.

    Vars:
      - $office     : App\Models\Office or null
      - $fieldName  : name attribute (e.g. 'next_assignee_id')
      - $required   : bool
      - $selectedId : optional preselect (defaults to office head)
--}}
@php
    $selectedId = $selectedId ?? null;
    $members    = $office ? $office->users->where('pivot.is_active', true)->values() : collect();
    $head       = $members->where('pivot.is_head', true)->first();
    $defaultSelected = $selectedId ?: ($head ? $head->id : null);
    $pickerId   = 'rcpt-' . ($office?->id ?? 'none') . '-' . uniqid();
@endphp

@if(!$office)
    <div class="alert alert-warning" style="margin: 0;">
        No downstream office is configured for this stage. Ask a Super Admin to set one up.
    </div>
@elseif($members->isEmpty())
    <div class="alert alert-warning" style="margin: 0;">
        <strong>{{ $office->name }}</strong> has no active members yet. Ask an administrator to assign people to this office before forwarding.
    </div>
@else
    <div class="rcpt-picker" id="{{ $pickerId }}">
        <div class="rcpt-picker__header">
            <div class="rcpt-picker__office">
                <span class="rcpt-picker__office-dot"></span>
                <strong>{{ $office->name }}</strong>
                <small>{{ $members->count() }} {{ $members->count() === 1 ? 'member' : 'members' }}</small>
            </div>
            <div class="rcpt-picker__stats" data-rcpt-stats>
                <span class="rcpt-picker__stats-text"><strong>0</strong> selected</span>
            </div>
        </div>

        <div class="rcpt-picker__search">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="rcpt-picker__search-input" data-rcpt-search placeholder="Search by name or email…" autocomplete="off">
        </div>

        <div class="rcpt-picker__list" data-rcpt-list>
            @foreach($members as $member)
                @php
                    $isHead = (bool) ($member->pivot->is_head ?? false);
                    $isSelected = (int) $defaultSelected === (int) $member->id;
                    $fullName = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
                    $initials = strtoupper(substr($member->first_name ?? '', 0, 1) . substr($member->last_name ?? '', 0, 1));
                @endphp
                <label class="rcpt-card {{ $isSelected ? 'is-selected' : '' }}"
                       data-rcpt-card
                       data-search="{{ strtolower($fullName . ' ' . $member->email) }}">
                    <input type="radio"
                           name="{{ $fieldName }}"
                           value="{{ $member->id }}"
                           class="rcpt-card__radio"
                           data-rcpt-radio
                           {{ $isSelected ? 'checked' : '' }}
                           {{ $required ? 'required' : '' }}>
                    <div class="rcpt-card__avatar">
                        @if(!empty($member->profile_picture))
                            <img src="{{ asset('profile_pictures/' . $member->profile_picture) }}" alt="{{ $fullName }}">
                        @else
                            <span>{{ $initials ?: '?' }}</span>
                        @endif
                    </div>
                    <div class="rcpt-card__meta">
                        <div class="rcpt-card__name">
                            <span>{{ $fullName }}</span>
                            @if($isHead)
                                <span class="rcpt-card__badge">
                                    <svg width="9" height="9" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Head
                                </span>
                            @endif
                        </div>
                        <div class="rcpt-card__email">{{ $member->email }}</div>
                    </div>
                    <div class="rcpt-card__check">
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                </label>
            @endforeach
            <div class="rcpt-picker__empty" data-rcpt-empty style="display:none;">
                <p>No member matches that search.</p>
            </div>
        </div>
    </div>
@endif

<style>
.rcpt-picker { background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 12px; overflow: hidden; font-family: 'Outfit', sans-serif !important; }
.rcpt-picker *, .rcpt-picker { box-sizing: border-box; }

.rcpt-picker__header { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; background: #fff; border-bottom: 1.5px solid #ebebeb; gap: 12px; flex-wrap: wrap; }
.rcpt-picker__office { display: inline-flex; align-items: center; gap: 7px; font-size: 0.85rem; color: #111827; }
.rcpt-picker__office strong { font-weight: 700; letter-spacing: -0.01em; }
.rcpt-picker__office small { font-size: 0.7rem; color: #9ca3af; font-weight: 500; padding-left: 4px; }
.rcpt-picker__office-dot { width: 7px; height: 7px; border-radius: 50%; background: #0c0c0c; }
.rcpt-picker__stats { font-size: 0.74rem; color: #6b7280; }
.rcpt-picker__stats strong { color: #0c0c0c; font-weight: 700; }

.rcpt-picker__search { position: relative; padding: 12px 16px 4px; background: #fff; }
.rcpt-picker__search svg { position: absolute; top: 50%; left: 28px; transform: translateY(-50%); color: #b0b5c0; pointer-events: none; }
.rcpt-picker__search-input { width: 100%; padding: 10px 14px 10px 38px; background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 9px; font-size: 0.84rem; color: #111827; outline: none; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.rcpt-picker__search-input:focus { background: #fff; border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.rcpt-picker__search-input::placeholder { color: #b0b5c0; }

.rcpt-picker__list { padding: 8px; max-height: 320px; overflow-y: auto; background: #fff; }
.rcpt-picker__list::-webkit-scrollbar { width: 6px; }
.rcpt-picker__list::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }

.rcpt-card { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; cursor: pointer; transition: background .12s, border-color .12s; margin: 0 0 4px; border: 1.5px solid transparent; }
.rcpt-card:hover { background: #fafafa; }
.rcpt-card.is-selected { background: #f9fafb; border-color: #0c0c0c; }
.rcpt-card:last-child { margin-bottom: 0; }
.rcpt-card__radio { display: none; }

.rcpt-card__avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.76rem; letter-spacing: 0.4px; flex-shrink: 0; overflow: hidden; }
.rcpt-card__avatar img { width: 100%; height: 100%; object-fit: cover; }

.rcpt-card__meta { flex: 1; min-width: 0; }
.rcpt-card__name { display: flex; align-items: center; gap: 7px; font-weight: 600; color: #111827; font-size: 0.86rem; line-height: 1.2; }
.rcpt-card__name span:first-child { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rcpt-card__badge { display: inline-flex; align-items: center; gap: 3px; padding: 2px 7px; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 99px; font-size: 0.62rem; font-weight: 700; letter-spacing: 0.04em; text-transform: uppercase; }
.rcpt-card__email { color: #9ca3af; font-size: 0.76rem; margin-top: 2px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

.rcpt-card__check { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; color: transparent; flex-shrink: 0; transition: all .15s; }
.rcpt-card.is-selected .rcpt-card__check { background: #0c0c0c; border-color: #0c0c0c; color: #fff; }

.rcpt-picker__empty { padding: 26px 16px; text-align: center; color: #b0b5c0; font-size: 0.82rem; }
.rcpt-picker__empty p { margin: 0; }

/* Dark mode */
.is_dark .rcpt-picker { background: #0b1322; border-color: #1e2330; }
.is_dark .rcpt-picker__header { background: #111827; border-color: #1e2330; }
.is_dark .rcpt-picker__office { color: #f3f4f6; }
.is_dark .rcpt-picker__office-dot { background: #f3f4f6; }
.is_dark .rcpt-picker__search { background: #111827; }
.is_dark .rcpt-picker__search-input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .rcpt-picker__list { background: #111827; }
.is_dark .rcpt-card:hover { background: #0f172a; }
.is_dark .rcpt-card.is-selected { background: #0f172a; border-color: #f3f4f6; }
.is_dark .rcpt-card.is-selected .rcpt-card__check { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
.is_dark .rcpt-card__name { color: #f3f4f6; }
</style>

<script>
(function () {
    document.querySelectorAll('.rcpt-picker').forEach(function (picker) {
        var search = picker.querySelector('[data-rcpt-search]');
        var list   = picker.querySelector('[data-rcpt-list]');
        var empty  = picker.querySelector('[data-rcpt-empty]');
        var stats  = picker.querySelector('[data-rcpt-stats] .rcpt-picker__stats-text strong');
        var cards  = picker.querySelectorAll('[data-rcpt-card]');

        function refreshStats() {
            var any = picker.querySelector('[data-rcpt-radio]:checked');
            if (stats) stats.textContent = any ? '1' : '0';
        }

        cards.forEach(function (card) {
            var radio = card.querySelector('[data-rcpt-radio]');
            card.addEventListener('click', function () {
                cards.forEach(function (c) { c.classList.remove('is-selected'); });
                card.classList.add('is-selected');
                if (radio) radio.checked = true;
                refreshStats();
            });
        });

        if (search) {
            search.addEventListener('input', function () {
                var q = search.value.trim().toLowerCase();
                var anyVisible = false;
                cards.forEach(function (card) {
                    var matches = !q || (card.dataset.search || '').indexOf(q) !== -1;
                    card.style.display = matches ? '' : 'none';
                    if (matches) anyVisible = true;
                });
                if (empty) empty.style.display = anyVisible ? 'none' : 'block';
            });
        }

        refreshStats();
    });
})();
</script>
