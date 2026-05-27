{{--
    Leadership recipient picker — HOD / Dean / Director.
    Used for stages whose recipientPool is `leadership`.

    Vars:
      - $leadershipCandidates : array<string, Collection> keyed by category slug
                                ('hod', 'dean', 'director')
      - $fieldName            : name for the user-id input (e.g. 'next_assignee_id')
      - $categoryFieldName    : name for the category input (e.g. 'next_leadership_category')
      - $required             : bool
--}}
@php
    use App\Models\Position;

    $categoryFieldName = $categoryFieldName ?? 'next_leadership_category';
    $required          = $required ?? true;
    $leadershipCandidates = $leadershipCandidates ?? [];

    // Pick the first category that actually has users as the default selection.
    $defaultCategory = null;
    foreach (Position::CATEGORIES as $key => $_label) {
        $pool = $leadershipCandidates[$key] ?? collect();
        if ($pool->isNotEmpty()) {
            $defaultCategory = $key;
            break;
        }
    }

    $pickerId = 'lead-' . uniqid();
@endphp

@php
    $totalCandidates = 0;
    foreach ($leadershipCandidates as $pool) {
        $totalCandidates += ($pool ? $pool->count() : 0);
    }
@endphp

@if($totalCandidates === 0)
    <div class="alert alert-warning" style="margin: 0;">
        No HOD, Dean or Director has been tagged on a position yet.
        Ask an administrator to open <strong>Positions</strong> and set a "Forms category" on the relevant roles.
    </div>
@else
    <div class="lead-picker" id="{{ $pickerId }}">

        {{-- Category radio chips --}}
        <div class="lead-picker__chips">
            @foreach(Position::CATEGORIES as $key => $label)
                @php
                    $pool  = $leadershipCandidates[$key] ?? collect();
                    $count = $pool->count();
                    $disabled = $count === 0;
                    $isDefault = $defaultCategory === $key;
                @endphp
                <label class="lead-chip {{ $disabled ? 'is-disabled' : '' }}">
                    <input type="radio"
                           name="{{ $categoryFieldName }}"
                           value="{{ $key }}"
                           data-lead-chip
                           {{ $disabled ? 'disabled' : '' }}
                           {{ $isDefault ? 'checked' : '' }}
                           {{ $required ? 'required' : '' }}>
                    <span class="lead-chip__icon">
                        @if($key === 'hod')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"/></svg>
                        @elseif($key === 'dean')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/></svg>
                        @else
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M3 21v-1a7 7 0 0 1 14 0v1"/></svg>
                        @endif
                    </span>
                    <span class="lead-chip__label">{{ $label }}</span>
                    <span class="lead-chip__count">{{ $count }}</span>
                </label>
            @endforeach
        </div>

        {{-- Search --}}
        <div class="lead-picker__search">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="lead-picker__search-input" data-lead-search placeholder="Search by name or email…" autocomplete="off">
        </div>

        {{-- Candidate lists (one per category, only one visible at a time) --}}
        <div class="lead-picker__lists">
            @foreach(Position::CATEGORIES as $key => $label)
                @php $pool = $leadershipCandidates[$key] ?? collect(); @endphp
                <div class="lead-picker__list"
                     data-lead-list="{{ $key }}"
                     style="display: {{ $defaultCategory === $key ? 'block' : 'none' }};">

                    @if($pool->isEmpty())
                        <div class="lead-picker__empty">
                            <p>No users tagged as {{ $label }} yet.</p>
                            <small>Ask an administrator to tag a position with this category.</small>
                        </div>
                    @else
                        @foreach($pool as $member)
                            @php
                                $fullName = trim(($member->first_name ?? '') . ' ' . ($member->last_name ?? ''));
                                $initials = strtoupper(substr($member->first_name ?? '', 0, 1) . substr($member->last_name ?? '', 0, 1));
                                $positionName = optional($member->position)->name;
                                $deptName     = optional($member->department)->name;
                            @endphp
                            <label class="lead-card"
                                   data-lead-card
                                   data-category="{{ $key }}"
                                   data-search="{{ strtolower($fullName . ' ' . $member->email . ' ' . $positionName . ' ' . $deptName) }}">
                                <input type="radio"
                                       name="{{ $fieldName }}"
                                       value="{{ $member->id }}"
                                       class="lead-card__radio"
                                       data-lead-radio
                                       data-category="{{ $key }}">
                                <div class="lead-card__avatar">
                                    @if(!empty($member->profile_picture))
                                        <img src="{{ asset('profile_pictures/' . $member->profile_picture) }}" alt="{{ $fullName }}">
                                    @else
                                        <span>{{ $initials ?: '?' }}</span>
                                    @endif
                                </div>
                                <div class="lead-card__meta">
                                    <div class="lead-card__name">{{ $fullName }}</div>
                                    <div class="lead-card__sub">
                                        @if($positionName)<span>{{ $positionName }}</span>@endif
                                        @if($positionName && $deptName)<span class="lead-card__dot"></span>@endif
                                        @if($deptName)<span>{{ $deptName }}</span>@endif
                                    </div>
                                </div>
                                <div class="lead-card__check">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </label>
                        @endforeach
                        <div class="lead-picker__empty" data-lead-no-match style="display:none;">
                            <p>No match in this list.</p>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
@endif

<style>
.lead-picker { background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 12px; overflow: hidden; font-family: 'Outfit', sans-serif !important; }
.lead-picker *, .lead-picker { box-sizing: border-box; }

/* Category chips */
.lead-picker__chips { display: flex; gap: 8px; padding: 14px 16px 10px; background: #fff; border-bottom: 1.5px solid #ebebeb; flex-wrap: wrap; }
.lead-chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px 7px 11px; border: 1.5px solid #ebebeb; border-radius: 99px; cursor: pointer; transition: all .15s; background: #fff; font-size: 0.82rem; font-weight: 500; color: #374151; margin: 0; user-select: none; }
.lead-chip:hover:not(.is-disabled) { border-color: #0c0c0c; color: #0c0c0c; }
.lead-chip.is-disabled { opacity: 0.45; cursor: not-allowed; }
.lead-chip input { display: none; }
.lead-chip__icon { display: inline-flex; color: inherit; }
.lead-chip__count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 18px; padding: 0 6px; background: #f3f4f6; color: #6b7280; border-radius: 99px; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.02em; }
.lead-chip:has(input:checked) { background: #0c0c0c; color: #fff; border-color: #0c0c0c; }
.lead-chip:has(input:checked) .lead-chip__count { background: rgba(255,255,255,.18); color: #fff; }

/* Search */
.lead-picker__search { position: relative; padding: 12px 16px 4px; background: #fff; }
.lead-picker__search svg { position: absolute; top: 50%; left: 28px; transform: translateY(-50%); color: #b0b5c0; pointer-events: none; }
.lead-picker__search-input { width: 100%; padding: 10px 14px 10px 38px; background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 9px; font-size: 0.84rem; color: #111827; outline: none; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.lead-picker__search-input:focus { background: #fff; border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.lead-picker__search-input::placeholder { color: #b0b5c0; }

/* Lists */
.lead-picker__lists { padding: 8px; max-height: 360px; overflow-y: auto; background: #fff; }
.lead-picker__lists::-webkit-scrollbar { width: 6px; }
.lead-picker__lists::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }

.lead-card { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; cursor: pointer; transition: background .12s, border-color .12s; margin: 0 0 4px; border: 1.5px solid transparent; }
.lead-card:hover { background: #fafafa; }
.lead-card.is-selected { background: #f9fafb; border-color: #0c0c0c; }
.lead-card__radio { display: none; }
.lead-card__avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.76rem; letter-spacing: 0.4px; flex-shrink: 0; overflow: hidden; }
.lead-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
.lead-card__meta { flex: 1; min-width: 0; }
.lead-card__name { font-weight: 600; color: #111827; font-size: 0.88rem; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lead-card__sub { color: #9ca3af; font-size: 0.74rem; margin-top: 3px; display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.lead-card__dot { width: 3px; height: 3px; border-radius: 50%; background: #d4d7de; flex-shrink: 0; }
.lead-card__check { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; color: transparent; flex-shrink: 0; transition: all .15s; }
.lead-card.is-selected .lead-card__check { background: #0c0c0c; border-color: #0c0c0c; color: #fff; }

.lead-picker__empty { padding: 24px 16px; text-align: center; color: #b0b5c0; font-size: 0.82rem; }
.lead-picker__empty p { margin: 0 0 4px; }
.lead-picker__empty small { font-size: 0.74rem; color: #c7cbd6; }

/* Dark mode */
.is_dark .lead-picker { background: #0b1322; border-color: #1e2330; }
.is_dark .lead-picker__chips { background: #111827; border-color: #1e2330; }
.is_dark .lead-chip { background: #0f172a; border-color: #2d3748; color: #d1d5db; }
.is_dark .lead-chip__count { background: rgba(255,255,255,.06); color: #9ca3af; }
.is_dark .lead-chip:has(input:checked) { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
.is_dark .lead-picker__search { background: #111827; }
.is_dark .lead-picker__search-input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .lead-picker__lists { background: #111827; }
.is_dark .lead-card:hover { background: #0f172a; }
.is_dark .lead-card.is-selected { background: #0f172a; border-color: #f3f4f6; }
.is_dark .lead-card.is-selected .lead-card__check { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
.is_dark .lead-card__name { color: #f3f4f6; }
</style>

<script>
(function () {
    document.querySelectorAll('.lead-picker').forEach(function (picker) {
        var chips  = picker.querySelectorAll('[data-lead-chip]');
        var lists  = picker.querySelectorAll('[data-lead-list]');
        var cards  = picker.querySelectorAll('[data-lead-card]');
        var search = picker.querySelector('[data-lead-search]');

        function showCategory(cat) {
            lists.forEach(function (l) {
                l.style.display = (l.dataset.leadList === cat) ? 'block' : 'none';
            });
            // Clear any previously-selected radio when switching category so
            // the user is forced to pick again — prevents accidental cross-pool routing.
            cards.forEach(function (card) {
                if (card.dataset.category !== cat) {
                    card.classList.remove('is-selected');
                    var r = card.querySelector('[data-lead-radio]');
                    if (r) r.checked = false;
                }
            });
            applySearch();
        }

        function applySearch() {
            var q = search ? search.value.trim().toLowerCase() : '';
            lists.forEach(function (list) {
                if (list.style.display === 'none') return;
                var visibleCount = 0;
                list.querySelectorAll('[data-lead-card]').forEach(function (card) {
                    var match = !q || (card.dataset.search || '').indexOf(q) !== -1;
                    card.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });
                var noMatch = list.querySelector('[data-lead-no-match]');
                if (noMatch) noMatch.style.display = (visibleCount === 0 && q.length > 0) ? 'block' : 'none';
            });
        }

        chips.forEach(function (chip) {
            chip.addEventListener('change', function () {
                if (chip.checked) showCategory(chip.value);
            });
        });

        cards.forEach(function (card) {
            card.addEventListener('click', function () {
                cards.forEach(function (c) { c.classList.remove('is-selected'); });
                card.classList.add('is-selected');
                var r = card.querySelector('[data-lead-radio]');
                if (r) r.checked = true;
            });
        });

        if (search) search.addEventListener('input', applySearch);
    });
})();
</script>
