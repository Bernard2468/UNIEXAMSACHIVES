{{--
    Renders every fillable field of a FormStage.
    - $stage       : App\Forms\FormStage
    - $sectionData : array of prior values keyed by field name
    - $readonly    : bool — when true the inputs render as disabled/locked rows.
--}}
@php
    use App\Forms\FormField;
    $sectionData = $sectionData ?? [];
    $readonly = $readonly ?? false;
@endphp

<div class="row form-grid">
    @foreach($stage->fields as $field)
        @php
            $value = old($field->name, $sectionData[$field->name] ?? $field->default);
            $colClass = 'col-md-' . max(1, min(12, $field->col));
            $inputId = 'field_' . $stage->slug . '_' . $field->name;
        @endphp

        @if($field->type === FormField::TYPE_HEADING)
            <div class="col-12">
                <h6 class="form-grid__heading">{{ $field->label }}</h6>
                @if($field->help)<p class="form-grid__help">{{ $field->help }}</p>@endif
            </div>
            @continue
        @endif

        <div class="{{ $colClass }} form-grid__col">
            <label for="{{ $inputId }}" class="form-grid__label">
                {{ $field->label }}
                @if($field->required)<span class="form-grid__required">*</span>@endif
            </label>

            @switch($field->type)
                @case(FormField::TYPE_TEXTAREA)
                    <textarea
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        rows="4"
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
                        @if($readonly) disabled readonly @endif
                    >
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
                            @if($readonly) disabled readonly @endif
                        >
                    </div>
                    @break

                @case(FormField::TYPE_DATE)
                    <input
                        type="date"
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        class="form-control"
                        value="{{ $value }}"
                        @if($readonly) disabled readonly @endif
                    >
                    @break

                @case(FormField::TYPE_SELECT)
                    <select
                        id="{{ $inputId }}"
                        name="{{ $field->name }}"
                        class="form-select"
                        @if($readonly) disabled @endif
                    >
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
                                <input
                                    type="radio"
                                    name="{{ $field->name }}"
                                    value="{{ $optVal }}"
                                    @checked($value === $optVal)
                                    @if($readonly) disabled @endif
                                >
                                <span>{{ $optLabel }}</span>
                            </label>
                        @endforeach
                    </div>
                    @break

                @case(FormField::TYPE_CHECKBOX)
                    <label class="checkbox-pill">
                        <input
                            type="checkbox"
                            name="{{ $field->name }}"
                            value="1"
                            @checked(!empty($value))
                            @if($readonly) disabled @endif
                        >
                        <span>{{ $field->help ?? 'Yes' }}</span>
                    </label>
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
                        @if($field->maxLength) maxlength="{{ $field->maxLength }}" @endif
                    >
            @endswitch

            @if($field->help && !in_array($field->type, [FormField::TYPE_CHECKBOX], true))
                <p class="form-grid__help">{{ $field->help }}</p>
            @endif
        </div>
    @endforeach
</div>
