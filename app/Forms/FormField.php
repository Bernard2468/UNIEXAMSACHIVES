<?php

namespace App\Forms;

/**
 * A single input on a form stage.
 *
 * A FormField does three things at once:
 *  - Describes how it renders (type, layout width, placeholder, options).
 *  - Provides its own Laravel validation rule (so the controller can build
 *    a rule array from the stage definition without duplication).
 *  - Carries metadata used by the PDF renderer (label, helper text).
 */
class FormField
{
    public const TYPE_TEXT      = 'text';
    public const TYPE_TEXTAREA  = 'textarea';
    public const TYPE_NUMBER    = 'number';
    public const TYPE_CURRENCY  = 'currency';
    public const TYPE_DATE      = 'date';
    public const TYPE_SELECT    = 'select';
    public const TYPE_RADIO     = 'radio';
    public const TYPE_CHECKBOX  = 'checkbox';
    public const TYPE_HIDDEN    = 'hidden';
    public const TYPE_HEADING   = 'heading';
    /**
     * Repeating-row table — for fields like education history, employment
     * history, etc. Each row is a record with the columns described by
     * $tableColumns. Stored as an array of associative arrays in
     * section_data. Renders as a stacked-row table with an "Add row" button.
     */
    public const TYPE_TABLE     = 'table';

    /**
     * @param  array<int, array{name:string,label:string,type?:string,col?:int,required?:bool,max?:int,placeholder?:string,options?:array<string,string>}> $tableColumns
     *         Column descriptors for TYPE_TABLE fields. Each column is rendered
     *         as a cell in every row. The "col" value (1-12) controls how wide
     *         the cell is relative to the others in the row.
     */
    public function __construct(
        public readonly string $name,
        public readonly string $label,
        public readonly string $type = self::TYPE_TEXT,
        public readonly bool $required = false,
        public readonly ?string $placeholder = null,
        public readonly ?string $help = null,
        public readonly array $options = [],
        public readonly int $col = 12,
        public readonly mixed $default = null,
        public readonly ?string $rule = null,
        public readonly ?int $maxLength = null,
        public readonly array $tableColumns = [],
        public readonly int $minTableRows = 1,
        public readonly int $maxTableRows = 10,
        public readonly string $addRowLabel = 'Add another row',
    ) {
    }

    /**
     * Laravel validation rule for this field, derived from its type unless
     * an explicit rule was supplied to the constructor.
     */
    public function validationRule(): array
    {
        if ($this->rule) {
            return [$this->name => $this->rule];
        }

        // TYPE_TABLE returns multi-key rules covering the array + each column.
        if ($this->type === self::TYPE_TABLE) {
            return $this->tableValidationRules();
        }

        $rules = [];
        $rules[] = $this->required ? 'required' : 'nullable';

        switch ($this->type) {
            case self::TYPE_TEXTAREA:
            case self::TYPE_TEXT:
            case self::TYPE_HIDDEN:
            case self::TYPE_HEADING:
                $rules[] = 'string';
                if ($this->maxLength) {
                    $rules[] = 'max:' . $this->maxLength;
                } else {
                    $rules[] = 'max:5000';
                }
                break;

            case self::TYPE_NUMBER:
                $rules[] = 'integer';
                break;

            case self::TYPE_CURRENCY:
                $rules[] = 'numeric';
                $rules[] = 'min:0';
                break;

            case self::TYPE_DATE:
                $rules[] = 'date';
                break;

            case self::TYPE_SELECT:
            case self::TYPE_RADIO:
                if (!empty($this->options)) {
                    $rules[] = 'in:' . implode(',', array_keys($this->options));
                }
                break;

            case self::TYPE_CHECKBOX:
                // Replace nullable/required with boolean equivalents.
                $rules = ['boolean'];
                if (!$this->required) {
                    array_unshift($rules, 'nullable');
                } else {
                    array_unshift($rules, 'accepted');
                }
                break;
        }

        return [$this->name => $rules];
    }

    /**
     * Validation rules for TYPE_TABLE: the field itself is an array, and each
     * declared column maps to a "name.*.column" rule key. After empty-row
     * filtering in the controller, every remaining row has at least one
     * non-empty cell — column-level rules are applied to those rows.
     *
     * @return array<string, array<int, string>>
     */
    protected function tableValidationRules(): array
    {
        $outer = [$this->required ? 'required' : 'nullable', 'array', 'max:' . $this->maxTableRows];
        if ($this->required) {
            $outer[] = 'min:' . max(1, $this->minTableRows);
        }
        $rules = [$this->name => $outer];

        foreach ($this->tableColumns as $col) {
            $colName = $col['name'] ?? null;
            if (!$colName) continue;

            $colRules = [($col['required'] ?? false) ? 'required' : 'nullable'];
            $colType  = $col['type'] ?? self::TYPE_TEXT;

            switch ($colType) {
                case self::TYPE_DATE:
                    $colRules[] = 'date';
                    break;
                case self::TYPE_NUMBER:
                    $colRules[] = 'integer';
                    break;
                case self::TYPE_CURRENCY:
                    $colRules[] = 'numeric';
                    $colRules[] = 'min:0';
                    break;
                case self::TYPE_SELECT:
                case self::TYPE_RADIO:
                    if (!empty($col['options'])) {
                        $colRules[] = 'in:' . implode(',', array_keys($col['options']));
                    }
                    break;
                default:
                    $colRules[] = 'string';
                    $colRules[] = 'max:' . ($col['max'] ?? 500);
                    break;
            }

            $rules[$this->name . '.*.' . $colName] = $colRules;
        }

        return $rules;
    }

    public function isDisplayOnly(): bool
    {
        return $this->type === self::TYPE_HEADING;
    }
}
