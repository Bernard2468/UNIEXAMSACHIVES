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

    public function isDisplayOnly(): bool
    {
        return $this->type === self::TYPE_HEADING;
    }
}
