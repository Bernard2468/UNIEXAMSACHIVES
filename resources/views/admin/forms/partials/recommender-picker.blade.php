{{--
    Recommender recipient picker — HOD / Dean / Director / Office.

    Used for stages whose recipientPool is `leadership_or_office`. The user picks
    one of four categories: HOD, Dean, Director (leadership-pool style cards) or
    Office (searchable list of all active offices — the form is routed to the
    head of the chosen office).

    Vars:
      - $leadershipCandidates : array<string, Collection> keyed by category slug
                                ('hod', 'dean', 'director')
      - $allOffices           : Collection<Office> — every active office, eager-
                                loaded with members so the head is shown inline.
      - $fieldName            : name for the user-id input (e.g. 'next_assignee_id')
      - $categoryFieldName    : name for the category input (default 'next_leadership_category')
      - $officeFieldName      : name for the office-id input (default 'next_office_id')
      - $required             : bool
--}}
@php
    use App\Models\Position;

    $categoryFieldName = $categoryFieldName ?? 'next_leadership_category';
    $officeFieldName   = $officeFieldName   ?? 'next_office_id';
    $required          = $required          ?? true;
    $leadershipCandidates = $leadershipCandidates ?? [];
    $allOffices           = $allOffices           ?? collect();

    // Pick the first category that actually has users as the default selection.
    $defaultCategory = null;
    foreach (Position::CATEGORIES as $key => $_label) {
        $pool = $leadershipCandidates[$key] ?? collect();
        if ($pool->isNotEmpty()) {
            $defaultCategory = $key;
            break;
        }
    }
    // If no leadership pools are populated, default to office mode (if any offices exist).
    if (!$defaultCategory && $allOffices->isNotEmpty()) {
        $defaultCategory = 'office';
    }

    $leadershipTotal = 0;
    foreach ($leadershipCandidates as $pool) {
        $leadershipTotal += ($pool ? $pool->count() : 0);
    }

    $pickerId = 'rec-' . uniqid();
@endphp

@if($leadershipTotal === 0 && $allOffices->isEmpty())
    <div class="alert alert-warning" style="margin: 0;">
        No Dean / HOD / Director has been tagged yet, and no active offices are configured.
        Ask an administrator to set this up before the form can be forwarded.
    </div>
@else
    <div class="rec-picker" id="{{ $pickerId }}">

        {{-- Category radio chips: HOD, Dean, Director, Office --}}
        <div class="rec-picker__chips">
            @foreach(Position::CATEGORIES as $key => $label)
                @php
                    $pool      = $leadershipCandidates[$key] ?? collect();
                    $count     = $pool->count();
                    $disabled  = $count === 0;
                    $isDefault = $defaultCategory === $key;
                @endphp
                <label class="rec-chip {{ $disabled ? 'is-disabled' : '' }}">
                    <input type="radio"
                           name="{{ $categoryFieldName }}"
                           value="{{ $key }}"
                           data-rec-chip
                           data-mode="leadership"
                           {{ $disabled ? 'disabled' : '' }}
                           {{ $isDefault ? 'checked' : '' }}
                           {{ $required ? 'required' : '' }}>
                    <span class="rec-chip__icon">
                        @if($key === 'hod')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18M5 21V7l8-4 8 4v14"/><path d="M9 9v.01M9 12v.01M9 15v.01M9 18v.01M13 9v.01M13 12v.01M13 15v.01M13 18v.01"/></svg>
                        @elseif($key === 'dean')
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c0 1.66 4 3 9 3s9-1.34 9-3v-5"/></svg>
                        @else
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="5"/><path d="M3 21v-1a7 7 0 0 1 14 0v1"/></svg>
                        @endif
                    </span>
                    <span class="rec-chip__label">{{ $label }}</span>
                    <span class="rec-chip__count">{{ $count }}</span>
                </label>
            @endforeach

            {{-- Office chip --}}
            @php
                $officeDisabled  = $allOffices->isEmpty();
                $officeIsDefault = $defaultCategory === 'office';
            @endphp
            <label class="rec-chip {{ $officeDisabled ? 'is-disabled' : '' }}">
                <input type="radio"
                       name="{{ $categoryFieldName }}"
                       value="office"
                       data-rec-chip
                       data-mode="office"
                       {{ $officeDisabled ? 'disabled' : '' }}
                       {{ $officeIsDefault ? 'checked' : '' }}
                       {{ $required ? 'required' : '' }}>
                <span class="rec-chip__icon">
                    <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14"/><path d="M9 9h.01M9 13h.01M9 17h.01M15 9h.01M15 13h.01M15 17h.01"/></svg>
                </span>
                <span class="rec-chip__label">Office</span>
                <span class="rec-chip__count">{{ $allOffices->count() }}</span>
            </label>
        </div>

        {{-- Search box (re-used across all modes) --}}
        <div class="rec-picker__search">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="text" class="rec-picker__search-input" data-rec-search placeholder="Search…" autocomplete="off">
        </div>

        {{-- Leadership lists (one per category) --}}
        <div class="rec-picker__lists">
            @foreach(Position::CATEGORIES as $key => $label)
                @php $pool = $leadershipCandidates[$key] ?? collect(); @endphp
                <div class="rec-picker__list"
                     data-rec-list="{{ $key }}"
                     style="display: {{ $defaultCategory === $key ? 'block' : 'none' }};">

                    @if($pool->isEmpty())
                        <div class="rec-picker__empty">
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
                            <label class="rec-card"
                                   data-rec-card
                                   data-mode="leadership"
                                   data-category="{{ $key }}"
                                   data-search="{{ strtolower($fullName . ' ' . $member->email . ' ' . $positionName . ' ' . $deptName) }}">
                                <input type="radio"
                                       name="{{ $fieldName }}"
                                       value="{{ $member->id }}"
                                       class="rec-card__radio"
                                       data-rec-radio
                                       data-mode="leadership"
                                       data-category="{{ $key }}">
                                <div class="rec-card__avatar">
                                    @if(!empty($member->profile_picture))
                                        <img src="{{ asset('profile_pictures/' . $member->profile_picture) }}" alt="{{ $fullName }}">
                                    @else
                                        <span>{{ $initials ?: '?' }}</span>
                                    @endif
                                </div>
                                <div class="rec-card__meta">
                                    <div class="rec-card__name">{{ $fullName }}</div>
                                    <div class="rec-card__sub">
                                        @if($positionName)<span>{{ $positionName }}</span>@endif
                                        @if($positionName && $deptName)<span class="rec-card__dot"></span>@endif
                                        @if($deptName)<span>{{ $deptName }}</span>@endif
                                    </div>
                                </div>
                                <div class="rec-card__check">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                                </div>
                            </label>
                        @endforeach
                        <div class="rec-picker__empty" data-rec-no-match style="display:none;">
                            <p>No match in this list.</p>
                        </div>
                    @endif
                </div>
            @endforeach

            {{-- Office list --}}
            <div class="rec-picker__list"
                 data-rec-list="office"
                 style="display: {{ $defaultCategory === 'office' ? 'block' : 'none' }};">

                @if($allOffices->isEmpty())
                    <div class="rec-picker__empty">
                        <p>No active offices configured.</p>
                        <small>Ask an administrator to create offices and add members.</small>
                    </div>
                @else
                    <div class="rec-picker__office-hint">
                        Pick an office — the form is sent to the <strong>head</strong> of that office (or its first active member if no head has been designated yet).
                    </div>
                    @foreach($allOffices as $office)
                        @php
                            $members = $office->users ?? collect();
                            $designatedHead = $members->where('pivot.is_head', true)->where('pivot.is_active', true)->first();
                            $head = $designatedHead ?: $members->where('pivot.is_active', true)->first();
                            $isRealHead = $designatedHead !== null;
                            $headName = $head ? trim(($head->first_name ?? '') . ' ' . ($head->last_name ?? '')) : null;
                            $memberCount = $members->where('pivot.is_active', true)->count();
                            $disabled = $head === null;
                        @endphp
                        <label class="rec-card rec-card--office {{ $disabled ? 'is-disabled' : '' }}"
                               data-rec-card
                               data-mode="office"
                               data-search="{{ strtolower($office->name . ' ' . ($office->description ?? '') . ' ' . ($headName ?? '')) }}">
                            <input type="radio"
                                   name="{{ $officeFieldName }}"
                                   value="{{ $office->id }}"
                                   class="rec-card__radio"
                                   data-rec-office
                                   data-head-id="{{ $head?->id }}"
                                   {{ $disabled ? 'disabled' : '' }}>
                            <div class="rec-card__avatar rec-card__avatar--office">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v14"/><path d="M9 9h.01M9 13h.01M15 9h.01M15 13h.01"/></svg>
                            </div>
                            <div class="rec-card__meta">
                                <div class="rec-card__name">{{ $office->name }}</div>
                                <div class="rec-card__sub">
                                    @if($headName)
                                        <span>{{ $isRealHead ? 'Head:' : 'Recipient:' }} <strong style="color:#374151;">{{ $headName }}</strong></span>
                                        @if(!$isRealHead)
                                            <span style="color:#b45309; font-size: 0.68rem; font-weight: 600; padding: 1px 6px; background: #fef3c7; border-radius: 99px;">no head set</span>
                                        @endif
                                    @else
                                        <span style="color:#b91c1c;">No active members — pick another office</span>
                                    @endif
                                    @if($headName)<span class="rec-card__dot"></span><span>{{ $memberCount }} {{ $memberCount === 1 ? 'member' : 'members' }}</span>@endif
                                </div>
                            </div>
                            <div class="rec-card__check">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            </div>
                        </label>
                    @endforeach
                    <div class="rec-picker__empty" data-rec-no-match style="display:none;">
                        <p>No office matches that search.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

<style>
.rec-picker { background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 12px; overflow: hidden; font-family: 'Outfit', sans-serif !important; }
.rec-picker *, .rec-picker { box-sizing: border-box; }

/* Category chips */
.rec-picker__chips { display: flex; gap: 8px; padding: 14px 16px 10px; background: #fff; border-bottom: 1.5px solid #ebebeb; flex-wrap: wrap; }
.rec-chip { display: inline-flex; align-items: center; gap: 7px; padding: 7px 14px 7px 11px; border: 1.5px solid #ebebeb; border-radius: 99px; cursor: pointer; transition: background .18s, color .18s, border-color .18s, box-shadow .18s, transform .18s; background: #fff; font-size: 0.82rem; font-weight: 500; color: #374151; margin: 0; user-select: none; }
.rec-chip:hover:not(.is-disabled) { border-color: #1f2937; color: #1f2937; }
.rec-chip.is-disabled { opacity: 0.45; cursor: not-allowed; }
.rec-chip input { display: none; }
.rec-chip__icon { display: inline-flex; color: inherit; }
.rec-chip__count { display: inline-flex; align-items: center; justify-content: center; min-width: 20px; height: 18px; padding: 0 6px; background: #f3f4f6; color: #6b7280; border-radius: 99px; font-size: 0.66rem; font-weight: 700; letter-spacing: 0.02em; transition: background .18s, color .18s; }
.rec-chip:has(input:checked) {
    background: linear-gradient(135deg, #232a36 0%, #1a2230 100%);
    color: #fff;
    border-color: #1a2230;
    box-shadow: 0 1px 2px rgba(15,23,42,.08), 0 6px 16px rgba(15,23,42,.14), inset 0 1px 0 rgba(255,255,255,.06);
}
.rec-chip:has(input:checked):hover:not(.is-disabled) {
    background: linear-gradient(135deg, #2a3342 0%, #1f2937 100%);
    color: #fff;
    border-color: #1f2937;
    box-shadow: 0 2px 4px rgba(15,23,42,.12), 0 10px 22px rgba(15,23,42,.20), inset 0 1px 0 rgba(255,255,255,.08);
    transform: translateY(-1px);
}
.rec-chip:has(input:checked) .rec-chip__count { background: rgba(255,255,255,.18); color: #fff; }

/* Search */
.rec-picker__search { position: relative; padding: 12px 16px 4px; background: #fff; }
.rec-picker__search svg { position: absolute; top: 50%; left: 28px; transform: translateY(-50%); color: #b0b5c0; pointer-events: none; }
.rec-picker__search-input { width: 100%; padding: 10px 14px 10px 38px; background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 9px; font-size: 0.84rem; color: #111827; outline: none; transition: all .15s; font-family: 'Outfit', sans-serif !important; }
.rec-picker__search-input:focus { background: #fff; border-color: #0c0c0c; box-shadow: 0 0 0 3px rgba(12,12,12,.06); }
.rec-picker__search-input::placeholder { color: #b0b5c0; }

/* Lists */
.rec-picker__lists { padding: 8px; max-height: 380px; overflow-y: auto; background: #fff; }
.rec-picker__lists::-webkit-scrollbar { width: 6px; }
.rec-picker__lists::-webkit-scrollbar-thumb { background: #e5e7eb; border-radius: 3px; }

.rec-picker__office-hint { padding: 8px 12px; margin: 4px 4px 8px; background: #f9fafb; border: 1px dashed #e5e7eb; border-radius: 8px; font-size: 0.76rem; color: #6b7280; }

.rec-card { display: flex; align-items: center; gap: 12px; padding: 10px 12px; border-radius: 10px; cursor: pointer; transition: background .12s, border-color .12s; margin: 0 0 4px; border: 1.5px solid transparent; }
.rec-card:hover { background: #fafafa; }
.rec-card.is-selected { background: #f9fafb; border-color: #0c0c0c; }
.rec-card.is-disabled { opacity: 0.55; cursor: not-allowed; }
.rec-card__radio { display: none; }
.rec-card__avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #0c0c0c, #374151); color: #fff; display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.76rem; letter-spacing: 0.4px; flex-shrink: 0; overflow: hidden; }
.rec-card__avatar img { width: 100%; height: 100%; object-fit: cover; }
.rec-card__avatar--office { background: linear-gradient(135deg, #4f46e5, #1e40af); }
.rec-card__meta { flex: 1; min-width: 0; }
.rec-card__name { font-weight: 600; color: #111827; font-size: 0.88rem; line-height: 1.2; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.rec-card__sub { color: #9ca3af; font-size: 0.74rem; margin-top: 3px; display: flex; align-items: center; gap: 6px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; flex-wrap: wrap; }
.rec-card__dot { width: 3px; height: 3px; border-radius: 50%; background: #d4d7de; flex-shrink: 0; }
.rec-card__check { width: 22px; height: 22px; border-radius: 50%; border: 1.5px solid #e5e7eb; display: inline-flex; align-items: center; justify-content: center; color: transparent; flex-shrink: 0; transition: all .15s; }
.rec-card.is-selected .rec-card__check { background: #0c0c0c; border-color: #0c0c0c; color: #fff; }

.rec-picker__empty { padding: 24px 16px; text-align: center; color: #b0b5c0; font-size: 0.82rem; }
.rec-picker__empty p { margin: 0 0 4px; }
.rec-picker__empty small { font-size: 0.74rem; color: #c7cbd6; }

/* Dark mode */
.is_dark .rec-picker { background: #0b1322; border-color: #1e2330; }
.is_dark .rec-picker__chips { background: #111827; border-color: #1e2330; }
.is_dark .rec-chip { background: #0f172a; border-color: #2d3748; color: #d1d5db; }
.is_dark .rec-chip__count { background: rgba(255,255,255,.06); color: #9ca3af; }
.is_dark .rec-chip:has(input:checked) {
    background: linear-gradient(135deg, #f3f4f6 0%, #d1d5db 100%);
    color: #0c0c0c;
    border-color: #e5e7eb;
}
.is_dark .rec-chip:has(input:checked):hover:not(.is-disabled) {
    background: linear-gradient(135deg, #ffffff 0%, #e5e7eb 100%);
    color: #0c0c0c;
    border-color: #f3f4f6;
}
.is_dark .rec-picker__search { background: #111827; }
.is_dark .rec-picker__search-input { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .rec-picker__lists { background: #111827; }
.is_dark .rec-picker__office-hint { background: #0f172a; border-color: #2d3748; color: #9ca3af; }
.is_dark .rec-card:hover { background: #0f172a; }
.is_dark .rec-card.is-selected { background: #0f172a; border-color: #f3f4f6; }
.is_dark .rec-card.is-selected .rec-card__check { background: #f3f4f6; color: #0c0c0c; border-color: #f3f4f6; }
.is_dark .rec-card__name { color: #f3f4f6; }
</style>

<script>
(function () {
    document.querySelectorAll('.rec-picker').forEach(function (picker) {
        var chips  = picker.querySelectorAll('[data-rec-chip]');
        var lists  = picker.querySelectorAll('[data-rec-list]');
        var cards  = picker.querySelectorAll('[data-rec-card]');
        var search = picker.querySelector('[data-rec-search]');

        // Synthetic hidden input that mirrors the chosen leadership user OR the
        // head of the chosen office, so the server sees a single `next_assignee_id`.
        var assigneeInput = picker.querySelector('[data-rec-radio]');
        // For office mode we generate the assignee_id from data-head-id on click.
        // We use a hidden input keyed off the same name to coordinate this.
        var hiddenAssigneeName = null;
        picker.querySelectorAll('[data-rec-radio]').forEach(function (r) {
            hiddenAssigneeName = r.name; // 'next_assignee_id'
        });
        if (!hiddenAssigneeName) {
            // Fall back to the conventional name if no leadership inputs exist
            // (i.e. only office mode is available).
            hiddenAssigneeName = 'next_assignee_id';
        }
        var hiddenAssignee = picker.querySelector('input[type="hidden"][data-rec-hidden-assignee]');
        if (!hiddenAssignee) {
            hiddenAssignee = document.createElement('input');
            hiddenAssignee.type = 'hidden';
            hiddenAssignee.name = hiddenAssigneeName;
            hiddenAssignee.setAttribute('data-rec-hidden-assignee', '1');
            picker.appendChild(hiddenAssignee);
        }

        function clearAssigneeRadios() {
            picker.querySelectorAll('[data-rec-radio]').forEach(function (r) { r.checked = false; });
            picker.querySelectorAll('[data-rec-office]').forEach(function (o) { o.checked = false; });
            cards.forEach(function (c) { c.classList.remove('is-selected'); });
        }

        function selectCard(card) {
            if (!card || card.classList.contains('is-disabled')) return;
            cards.forEach(function (c) { c.classList.remove('is-selected'); });
            card.classList.add('is-selected');

            if (card.dataset.mode === 'leadership') {
                var r = card.querySelector('[data-rec-radio]');
                if (r) {
                    r.checked = true;
                    hiddenAssignee.value = r.value;
                }
                picker.querySelectorAll('[data-rec-office]').forEach(function (o) { o.checked = false; });
            } else if (card.dataset.mode === 'office') {
                var officeRadio = card.querySelector('[data-rec-office]');
                if (officeRadio) {
                    officeRadio.checked = true;
                    hiddenAssignee.value = officeRadio.dataset.headId || '';
                }
                picker.querySelectorAll('[data-rec-radio]').forEach(function (r) { r.checked = false; });
            }
        }

        function autoPickFirstVisible() {
            // Pick the first non-disabled card in whichever list is currently visible.
            for (var i = 0; i < lists.length; i++) {
                if (lists[i].style.display !== 'none') {
                    var first = lists[i].querySelector('[data-rec-card]:not(.is-disabled)');
                    if (first) { selectCard(first); return; }
                }
            }
            // No usable card — make sure the hidden assignee is empty so the
            // server returns a helpful 422 instead of silently misrouting.
            clearAssigneeRadios();
            hiddenAssignee.value = '';
        }

        function showMode(mode, category) {
            // mode === 'leadership' OR 'office'
            // For leadership, category is hod/dean/director.
            lists.forEach(function (l) {
                if (mode === 'leadership') {
                    l.style.display = (l.dataset.recList === category) ? 'block' : 'none';
                } else {
                    l.style.display = (l.dataset.recList === 'office') ? 'block' : 'none';
                }
            });
            clearAssigneeRadios();
            hiddenAssignee.value = '';
            applySearch();
            autoPickFirstVisible();
        }

        function applySearch() {
            var q = search ? search.value.trim().toLowerCase() : '';
            lists.forEach(function (list) {
                if (list.style.display === 'none') return;
                var visibleCount = 0;
                list.querySelectorAll('[data-rec-card]').forEach(function (card) {
                    var match = !q || (card.dataset.search || '').indexOf(q) !== -1;
                    card.style.display = match ? '' : 'none';
                    if (match) visibleCount++;
                });
                var noMatch = list.querySelector('[data-rec-no-match]');
                if (noMatch) noMatch.style.display = (visibleCount === 0 && q.length > 0) ? 'block' : 'none';
            });
        }

        chips.forEach(function (chip) {
            chip.addEventListener('change', function () {
                if (!chip.checked) return;
                if (chip.dataset.mode === 'office') {
                    showMode('office');
                } else {
                    showMode('leadership', chip.value);
                }
            });
        });

        cards.forEach(function (card) {
            card.addEventListener('click', function (e) {
                if (card.classList.contains('is-disabled')) {
                    e.preventDefault();
                    return;
                }
                selectCard(card);
            });
        });

        if (search) search.addEventListener('input', applySearch);

        // Pre-pick the first available card in the currently visible list so
        // the user can submit immediately after picking a chip. If the user
        // wants someone else, clicking another card replaces the selection.
        autoPickFirstVisible();

        // Defensive submit guard — if for some reason no assignee is wired up
        // by the time the form posts, surface a friendly message instead of
        // letting the server return a generic 422 page.
        var form = picker.closest('form');
        if (form) {
            form.addEventListener('submit', function (e) {
                // Only guard "send" actions (skip "draft").
                var actionInput = form.querySelector('input[name="action"]');
                if (actionInput && actionInput.value === 'draft') return;

                if (!hiddenAssignee.value) {
                    e.preventDefault();
                    alert('Please pick who should receive this form: a Dean / HOD / Director, or an Office whose head will recommend.');
                }
            });
        }
    });
})();
</script>
