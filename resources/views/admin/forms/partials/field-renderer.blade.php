{{--
    Renders every fillable field of a FormStage on a clean 12-col grid.
    - $stage       : App\Forms\FormStage
    - $sectionData : array of prior values keyed by field name
    - $readonly    : bool — when true the inputs render as disabled/locked rows.
--}}
@php
    use App\Forms\FormField;
    $sectionData = $sectionData ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="form-grid">
    @foreach($stage->fields as $field)
        @php
            $value   = old($field->name, $sectionData[$field->name] ?? $field->default);
            $col     = max(1, min(12, $field->col));
            $inputId = 'field_' . $stage->slug . '_' . $field->name;
        @endphp

        @if($field->type === FormField::TYPE_HEADING)
            <div class="form-grid__heading" data-field-name="{{ $field->name }}">{{ $field->label }}</div>
            @if($field->help)<div class="form-field--col-12 form-field" data-field-name="{{ $field->name }}__help"><p class="form-field__help" style="margin: -10px 0 0;">{{ $field->help }}</p></div>@endif
            @continue
        @endif

        <div class="form-field form-field--col-{{ $col }}" data-field-name="{{ $field->name }}" @if($field->required) data-required="1" data-field-type="{{ $field->type }}" data-field-label="{{ $field->label }}" @endif>
            <label for="{{ $inputId }}" class="form-field__label">
                {{ $field->label }}
                @if($field->required)<span class="form-field__required">*</span>@endif
            </label>

            @switch($field->type)
                @case(FormField::TYPE_TEXTAREA)
                    <textarea
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        rows="3"
                        class="form-control"
                        placeholder="{{ $field->placeholder }}"
                        @if($readonly) disabled readonly @endif
                        @if($field->maxLength) maxlength="{{ $field->maxLength }}" @endif
                    >{{ $value }}</textarea>
                    @break

                @case(FormField::TYPE_NUMBER)
                    <input
                        type="number"
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        class="form-control"
                        value="{{ $value }}"
                        placeholder="{{ $field->placeholder }}"
                        @if($readonly) disabled readonly @endif>
                    @break

                @case(FormField::TYPE_CURRENCY)
                    <div class="input-group">
                        <span class="input-group-text">GhS</span>
                        <input
                            type="number"
                            step="0.01"
                            min="0"
                            id="{{ $inputId }}"
                            name="{{ $field->name }}"
                            class="form-control"
                            value="{{ $value }}"
                            placeholder="0.00"
                            @if($readonly) disabled readonly @endif>
                    </div>
                    @break

                @case(FormField::TYPE_DATE)
                    <input
                        type="date"
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        class="form-control"
                        value="{{ $value }}"
                        @if($field->calculatesAgeTarget)
                            data-calc-age-target="{{ $field->calculatesAgeTarget }}"
                            {{-- Inline event handlers in addition to the addEventListener
                                 wiring at the bottom of this partial — this is a
                                 belt-and-braces guarantee that the age field auto-fills
                                 the moment the user picks (or types) a complete DOB. --}}
                            oninput="if(window.cugFillAgeFrom)window.cugFillAgeFrom(this)"
                            onchange="if(window.cugFillAgeFrom)window.cugFillAgeFrom(this)"
                            onblur="if(window.cugFillAgeFrom)window.cugFillAgeFrom(this)"
                        @endif
                        @if($readonly) disabled readonly @endif>
                    @break

                @case(FormField::TYPE_SELECT)
                    <select
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        class="form-select"
                        @if($readonly) disabled @endif>
                        <option value="">— Select —</option>
                        @foreach($field->options as $optVal => $optLabel)
                            <option value="{{ $optVal }}" @selected($value === $optVal)>{{ $optLabel }}</option>
                        @endforeach
                    </select>
                    @break

                @case(FormField::TYPE_RADIO)
                    <div class="radio-group">
                        @foreach($field->options as $optVal => $optLabel)
                            <label class="radio-pill">
                                <input type="radio" name="{{ $field->name }}" value="{{ $optVal }}" @checked($value === $optVal) @if($readonly) disabled @endif>
                                <span>{{ $optLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    @break

                @case(FormField::TYPE_CHECKBOX)
                    <label class="checkbox-pill">
                        <input type="checkbox" name="{{ $field->name }}" value="1" @checked(!empty($value)) @if($readonly) disabled @endif>
                        <span>{{ $field->help ?? 'Yes' }}</span>
                    </label>
                    @break

                @case(FormField::TYPE_TABLE)
                    @php
                        // Rows currently saved on the submission (array of associative arrays),
                        // or one blank row to start. Posted-back ("old") values take priority
                        // so server-side validation errors don't wipe what the user typed.
                        $rows = old($field->name, is_array($value) ? $value : null);
                        if (!is_array($rows) || empty($rows)) {
                            $rows = [array_fill_keys(array_column($field->tableColumns, 'name'), '')];
                        }
                        $romans = ['i.', 'ii.', 'iii.', 'iv.', 'v.', 'vi.', 'vii.', 'viii.', 'ix.', 'x.'];
                    @endphp
                    <div class="form-table"
                         data-form-table
                         data-table-name="{{ $field->name }}"
                         data-table-min-rows="{{ $field->minTableRows }}"
                         data-table-max-rows="{{ $field->maxTableRows }}">
                        {{-- Header row --}}
                        <div class="form-table__row form-table__row--header">
                            <div class="form-table__cell form-table__cell--index">#</div>
                            @foreach($field->tableColumns as $col)
                                <div class="form-table__cell" style="flex: {{ $col['col'] ?? 4 }};">
                                    {{ $col['label'] ?? $col['name'] }}
                                    @if($col['required'] ?? false)<span class="form-field__required">*</span>@endif
                                </div>
                            @endforeach
                            <div class="form-table__cell form-table__cell--remove">&nbsp;</div>
                        </div>

                        {{-- Data rows --}}
                        <div class="form-table__rows" data-table-rows>
                            @foreach($rows as $rowIdx => $row)
                                <div class="form-table__row" data-table-row>
                                    <div class="form-table__cell form-table__cell--index" data-table-index>{{ $romans[$rowIdx] ?? ($rowIdx + 1) . '.' }}</div>
                                    @foreach($field->tableColumns as $col)
                                        @php
                                            $colName  = $col['name'];
                                            $colType  = $col['type'] ?? FormField::TYPE_TEXT;
                                            $colPh    = $col['placeholder'] ?? '';
                                            $colMax   = $col['max'] ?? null;
                                            $cellVal  = $row[$colName] ?? '';
                                            $inName   = $field->name . '[' . $rowIdx . '][' . $colName . ']';
                                        @endphp
                                        <div class="form-table__cell" style="flex: {{ $col['col'] ?? 4 }};">
                                            @switch($colType)
                                                @case(FormField::TYPE_DATE)
                                                    <input type="date" name="{{ $inName }}" value="{{ $cellVal }}" class="form-control" @if($readonly) disabled readonly @endif>
                                                    @break
                                                @case(FormField::TYPE_NUMBER)
                                                    <input type="number" name="{{ $inName }}" value="{{ $cellVal }}" class="form-control" placeholder="{{ $colPh }}" @if($readonly) disabled readonly @endif>
                                                    @break
                                                @case(FormField::TYPE_SELECT)
                                                    <select name="{{ $inName }}" class="form-select" @if($readonly) disabled @endif>
                                                        <option value="">— Select —</option>
                                                        @foreach(($col['options'] ?? []) as $optVal => $optLabel)
                                                            <option value="{{ $optVal }}" @selected($cellVal === $optVal)>{{ $optLabel }}</option>
                                                        @endforeach
                                                    </select>
                                                    @break
                                                @default
                                                    <input type="text" name="{{ $inName }}" value="{{ $cellVal }}" class="form-control" placeholder="{{ $colPh }}" @if($readonly) disabled readonly @endif @if($colMax) maxlength="{{ $colMax }}" @endif>
                                            @endswitch
                                        </div>
                                    @endforeach
                                    <div class="form-table__cell form-table__cell--remove">
                                        @if(!$readonly)
                                            <button type="button" class="form-table__remove-btn" data-table-remove title="Remove this row" aria-label="Remove row">
                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        {{-- Add-row button --}}
                        @if(!$readonly)
                            <button type="button" class="form-table__add-btn" data-table-add>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                                <span>{{ $field->addRowLabel }}</span>
                            </button>
                        @endif
                    </div>
                    @break

                @default
                    <input
                        type="text"
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        class="form-control"
                        value="{{ $value }}"
                        placeholder="{{ $field->placeholder }}"
                        @if($readonly) disabled readonly @endif
                        @if($field->maxLength) maxlength="{{ $field->maxLength }}" @endif>
            @endswitch

            @if($field->help && !in_array($field->type, [FormField::TYPE_CHECKBOX], true))
                <p class="form-field__help">{{ $field->help }}</p>
            @endif
        </div>
    @endforeach
</div>

{{-- ─────────────────────────────────────────────────────────────
     Repeating-row table — styles + JS (loaded once per page).
     ───────────────────────────────────────────────────────────── --}}
@once
<style>
.form-table { background: #fafafa; border: 1.5px solid #ebebeb; border-radius: 12px; padding: 10px; font-family: 'Outfit', sans-serif !important; }
.form-table * { box-sizing: border-box; }

.form-table__row { display: flex; align-items: center; gap: 8px; padding: 6px 8px; border-radius: 8px; margin-bottom: 6px; }
.form-table__row:last-child { margin-bottom: 0; }
.form-table__row--header { background: transparent; padding: 4px 8px 6px; margin-bottom: 4px; border-bottom: 1.5px solid #ebebeb; }
.form-table__row--header .form-table__cell { font-size: 0.72rem; font-weight: 700; color: #374151; text-transform: uppercase; letter-spacing: 0.06em; }

.form-table__cell { flex: 1; min-width: 0; }
.form-table__cell--index {
    flex: 0 0 30px;
    text-align: center;
    font-weight: 700;
    font-size: 0.78rem;
    color: #6b7280;
    font-family: 'JetBrains Mono', monospace, sans-serif;
}
.form-table__cell--remove { flex: 0 0 32px; text-align: center; }

.form-table__cell .form-control,
.form-table__cell .form-select {
    width: 100%;
    padding: 8px 10px;
    font-size: 0.84rem;
    background: #fff;
    border: 1.5px solid #ebebeb;
    border-radius: 8px;
    color: #111827;
    transition: border-color .15s, box-shadow .15s;
}
.form-table__cell .form-control:focus,
.form-table__cell .form-select:focus {
    outline: none;
    border-color: #0c0c0c;
    box-shadow: 0 0 0 3px rgba(12,12,12,.06);
}

.form-table__remove-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 26px; height: 26px;
    background: #fff; border: 1.5px solid #ebebeb;
    border-radius: 7px; color: #9ca3af;
    cursor: pointer;
    transition: all .15s;
}
.form-table__remove-btn:hover { background: #fef2f2; border-color: #fca5a5; color: #b91c1c; transform: scale(1.05); }
.form-table__remove-btn:disabled { opacity: 0.35; cursor: not-allowed; pointer-events: none; }

.form-table__add-btn {
    margin-top: 4px;
    display: inline-flex; align-items: center; gap: 7px;
    padding: 9px 16px;
    background: linear-gradient(135deg, #111827 0%, #0c0c0c 100%);
    color: #fff;
    border: none;
    border-radius: 99px;
    font-size: 0.82rem; font-weight: 600;
    cursor: pointer;
    transition: all .18s;
    font-family: 'Outfit', sans-serif !important;
    box-shadow: 0 2px 6px rgba(12,12,12,.12);
}
.form-table__add-btn:hover { transform: translateY(-1px); box-shadow: 0 6px 14px rgba(12,12,12,.18); }
.form-table__add-btn:disabled { opacity: 0.5; cursor: not-allowed; transform: none; box-shadow: none; }

/* Stack columns on narrow screens for legibility */
@media (max-width: 720px) {
    .form-table__row { flex-wrap: wrap; }
    .form-table__row--header { display: none; }
    .form-table__cell { flex: 1 1 100%; }
    .form-table__cell--index { flex: 0 0 auto; padding-right: 4px; }
    .form-table__cell--remove { flex: 0 0 auto; position: absolute; right: 14px; }
    .form-table__row { position: relative; background: #fff; border: 1px solid #ebebeb; padding: 12px 12px 8px; }
}

/* Dark mode */
.is_dark .form-table { background: #0b1322; border-color: #1e2330; }
.is_dark .form-table__row--header { border-color: #1e2330; }
.is_dark .form-table__row--header .form-table__cell { color: #d1d5db; }
.is_dark .form-table__cell--index { color: #9ca3af; }
.is_dark .form-table__cell .form-control,
.is_dark .form-table__cell .form-select { background: #0f172a; border-color: #2d3748; color: #f3f4f6; }
.is_dark .form-table__cell .form-control:focus,
.is_dark .form-table__cell .form-select:focus { border-color: #f3f4f6; box-shadow: 0 0 0 3px rgba(243,244,246,.08); }
.is_dark .form-table__remove-btn { background: #111827; border-color: #2d3748; color: #6b7280; }
.is_dark .form-table__remove-btn:hover { background: #7f1d1d; border-color: #b91c1c; color: #fee2e2; }
.is_dark .form-table__add-btn { background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%); color: #0c0c0c; }
</style>

<script>
(function () {
    const ROMANS = ['i.', 'ii.', 'iii.', 'iv.', 'v.', 'vi.', 'vii.', 'viii.', 'ix.', 'x.'];

    function initFormTable(tableEl) {
        if (tableEl.dataset.initialized === '1') return;
        tableEl.dataset.initialized = '1';

        const rowsContainer = tableEl.querySelector('[data-table-rows]');
        const addBtn        = tableEl.querySelector('[data-table-add]');
        const tableName     = tableEl.dataset.tableName;
        const maxRows       = parseInt(tableEl.dataset.tableMaxRows || '10', 10);
        const minRows       = parseInt(tableEl.dataset.tableMinRows || '1', 10);

        function reindex() {
            const rows = rowsContainer.querySelectorAll('[data-table-row]');
            const nameRe = new RegExp('^(' + tableName.replace(/[\[\]]/g, '\\$&') + ')\\[\\d+\\]\\[(.+)\\]$');
            rows.forEach((row, i) => {
                const idxEl = row.querySelector('[data-table-index]');
                if (idxEl) idxEl.textContent = ROMANS[i] || ((i + 1) + '.');
                row.querySelectorAll('input, textarea, select').forEach((input) => {
                    const m = input.name.match(nameRe);
                    if (m) input.name = `${m[1]}[${i}][${m[2]}]`;
                });
                const removeBtn = row.querySelector('[data-table-remove]');
                if (removeBtn) removeBtn.disabled = rows.length <= Math.max(1, minRows);
            });
            if (addBtn) addBtn.disabled = rows.length >= maxRows;
        }

        function removeRow(row) {
            const rows = rowsContainer.querySelectorAll('[data-table-row]');
            if (rows.length <= Math.max(1, minRows)) return;
            row.remove();
            reindex();
        }

        function bindRow(row) {
            const btn = row.querySelector('[data-table-remove]');
            if (btn) btn.addEventListener('click', () => removeRow(row));
        }

        rowsContainer.querySelectorAll('[data-table-row]').forEach(bindRow);

        if (addBtn) {
            addBtn.addEventListener('click', () => {
                const rows = rowsContainer.querySelectorAll('[data-table-row]');
                if (rows.length >= maxRows) return;
                const newRow = rows[0].cloneNode(true);
                newRow.querySelectorAll('input, textarea').forEach((i) => { i.value = ''; });
                newRow.querySelectorAll('select').forEach((s) => { s.selectedIndex = 0; });
                rowsContainer.appendChild(newRow);
                bindRow(newRow);
                reindex();
                // Focus the first input of the new row for fast entry.
                const firstInput = newRow.querySelector('input, textarea, select');
                if (firstInput) firstInput.focus();
            });
        }

        reindex();
    }

    document.querySelectorAll('[data-form-table]').forEach(initFormTable);
})();
</script>

{{-- ─────────────────────────────────────────────────────────────
     Auto-calculate age from a date-of-birth field.
     Any date input carrying  data-calc-age-target="age_field_name"
     will update that target field in years whenever its value
     changes. Listens on both `input` AND `change` so the age fills
     in the moment the user picks (or types) a complete date —
     not only when they tab away. Re-runs on DOMContentLoaded as
     a safety net for any inputs added later in the page lifecycle.
     ───────────────────────────────────────────────────────────── --}}
<script>
(function () {
    function computeAge(dobValue) {
        if (!dobValue) return null;
        // Parse "YYYY-MM-DD" explicitly so timezone juggling doesn't push
        // the date over a day boundary on certain locales (e.g. type=date
        // returns "1990-01-15" and `new Date("1990-01-15")` is UTC midnight).
        var parts = String(dobValue).split('-');
        var dob;
        if (parts.length === 3) {
            var y = parseInt(parts[0], 10);
            var mo = parseInt(parts[1], 10) - 1;
            var d = parseInt(parts[2], 10);
            if (!isNaN(y) && !isNaN(mo) && !isNaN(d)) {
                dob = new Date(y, mo, d);
            }
        }
        if (!dob) dob = new Date(dobValue);
        if (isNaN(dob.getTime())) return null;
        var today = new Date();
        var age = today.getFullYear() - dob.getFullYear();
        var m = today.getMonth() - dob.getMonth();
        if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
        return (age >= 0 && age <= 150) ? age : null;
    }

    // ──────────────────────────────────────────────────────────────
    // Public auto-fill entry point — also bound directly via inline
    // oninput / onchange / onblur attributes on the date input itself
    // (see field-renderer @case TYPE_DATE). Either path lands here.
    // ──────────────────────────────────────────────────────────────
    window.cugFillAgeFrom = function (dateEl) {
        if (!dateEl) return;
        var targetName = dateEl.dataset.calcAgeTarget;
        if (!targetName) return;
        var form = dateEl.closest('form') || document;
        var target = form.querySelector('[name="' + targetName + '"]');
        if (!target) return;
        var age = computeAge(dateEl.value);
        if (age === null) return;
        target.value = age;
        // Notify any other listeners (validation, dependent fields, etc.)
        try {
            target.dispatchEvent(new Event('input',  { bubbles: true }));
            target.dispatchEvent(new Event('change', { bubbles: true }));
        } catch (e) { /* ignore — value is set either way */ }
    };

    function wire(dateEl) {
        if (dateEl.dataset.ageWired === '1') return; // idempotent
        dateEl.dataset.ageWired = '1';

        var run = function () { window.cugFillAgeFrom(dateEl); };

        // `input` fires on every keystroke / picker change — fastest path
        // to a populated age. `change` and `blur` are belt-and-braces for
        // browsers that suppress `input` on type=date.
        dateEl.addEventListener('input',  run);
        dateEl.addEventListener('change', run);
        dateEl.addEventListener('blur',   run);

        // Derive on load if the date is already populated (editing a saved
        // submission) AND the age field is still empty — never clobber a
        // value the user has manually typed.
        var targetName = dateEl.dataset.calcAgeTarget;
        var form = dateEl.closest('form') || document;
        var target = form.querySelector('[name="' + targetName + '"]');
        if (dateEl.value && target && !target.value) run();
    }

    function wireAll() {
        document.querySelectorAll('input[type="date"][data-calc-age-target]').forEach(wire);
    }

    // Run immediately (inline-script timing — the relevant inputs precede
    // this <script> in document order, so they are guaranteed parsed) AND
    // again on DOMContentLoaded in case any are added later.
    wireAll();
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', wireAll);
    }
})();
</script>
@endonce
