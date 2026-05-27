<?php

namespace App\Forms;

/**
 * A single stage in a form's signing journey.
 *
 * Examples:
 *   - 'requisitioner' (no office, requisitioner fills + signs)
 *   - 'hod_dean' (Dean/HOD co-sign, auto-routed)
 *   - 'finance' (Finance Office signs)
 *   - 'audit' (Internal Audit vets)
 *   - 'registrar' (Registrar approves; may branch to 'vc')
 *   - 'vc' (optional — only entered when Registrar refers up)
 *   - 'finance_director' (Director of Finance authorises Accountant/Cashier)
 */
class FormStage
{
    /**
     * @param  string                $slug             Stable identifier (used as JSON key in section_data).
     * @param  string                $label            Human label, e.g. "Finance Processing".
     * @param  ?string               $officeSlug       Slug of the Office that handles this stage. Null = requisitioner.
     * @param  array<int, FormField> $fields           Fields the office fills at this stage.
     * @param  bool                  $signatureRequired Show signature pad + persist FormSignature when submitted.
     * @param  array<int, string>    $branches         Possible alternate next-stage slugs (e.g. ['vc']).
     * @param  bool                  $optional         True if this stage may be skipped (e.g. VC stage).
     * @param  ?string               $description      Short helper text shown on the stage UI.
     */
    public function __construct(
        public readonly string $slug,
        public readonly string $label,
        public readonly ?string $officeSlug = null,
        public readonly array $fields = [],
        public readonly bool $signatureRequired = true,
        public readonly array $branches = [],
        public readonly bool $optional = false,
        public readonly ?string $description = null,
    ) {
    }

    public function isRequisitionerStage(): bool
    {
        return $this->officeSlug === null;
    }

    /**
     * Combined Laravel validation rules for every fillable field in this stage.
     */
    public function validationRules(): array
    {
        $rules = [];
        foreach ($this->fields as $field) {
            if ($field->isDisplayOnly()) {
                continue;
            }
            $rules = array_merge($rules, $field->validationRule());
        }

        return $rules;
    }

    /**
     * Field names (excluding display-only) — used to extract section data
     * from request input safely.
     */
    public function fieldNames(): array
    {
        $names = [];
        foreach ($this->fields as $field) {
            if (!$field->isDisplayOnly()) {
                $names[] = $field->name;
            }
        }

        return $names;
    }
}
