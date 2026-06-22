<?php

namespace App\Forms;

/**
 * A single stage in a form's signing journey.
 *
 * Examples:
 *   - 'requisitioner' (no office, requisitioner fills + signs)
 *   - 'hod_dean'      (leadership pool — Dean/HOD/Director picked dynamically)
 *   - 'finance'       (Finance Office signs)
 *   - 'audit'         (Internal Audit vets)
 *   - 'registrar'     (Registrar approves; may branch to 'vc')
 *   - 'vc'            (optional — only entered when Registrar refers up)
 *   - 'finance_director' (Director of Finance authorises Accountant/Cashier)
 */
class FormStage
{
    /**
     * Default recipient pool — pick a user from the named Office.
     */
    public const POOL_OFFICE = 'office';

    /**
     * Leadership pool — the requisitioner/forwarder picks HOD vs Dean vs
     * Director (radio chips on the form), then chooses a specific user
     * whose Position is tagged with that category. There is no single
     * "head" because every department / faculty has its own.
     */
    public const POOL_LEADERSHIP = 'leadership';

    /**
     * Leadership-OR-office pool — the requisitioner picks one of four
     * options: HOD, Dean, Director or "an Office". For HOD/Dean/Director
     * the leadership-style picker is used. For "Office" the user picks
     * any active Office from a searchable list and the form is routed
     * to that office's head. Used by Annual Leave Application.
     */
    public const POOL_LEADERSHIP_OR_OFFICE = 'leadership_or_office';

    /**
     * Creator pool — the form returns to the user who originally submitted
     * it. Used for "applicant re-confirms after endorsement" patterns such
     * as the Renewal of Appointment form's declaration stage. No recipient
     * picker is shown; the workflow service resolves to FormSubmission.created_by.
     */
    public const POOL_CREATOR = 'creator';

    public const POOLS = [
        self::POOL_OFFICE,
        self::POOL_LEADERSHIP,
        self::POOL_LEADERSHIP_OR_OFFICE,
        self::POOL_CREATOR,
    ];

    /**
     * @param  string                $slug             Stable identifier (used as JSON key in section_data).
     * @param  string                $label            Human label, e.g. "Finance Processing".
     * @param  ?string               $officeSlug       Slug of the Office that handles this stage. Null = requisitioner OR leadership pool.
     * @param  array<int, FormField> $fields           Fields the office fills at this stage.
     * @param  bool                  $signatureRequired Show signature pad + persist FormSignature when submitted.
     * @param  array<int, string>    $branches         Possible alternate next-stage slugs (e.g. ['vc']).
     * @param  bool                  $optional         True if this stage may be skipped (e.g. VC stage).
     * @param  ?string               $description      Short helper text shown on the stage UI.
     * @param  string                $recipientPool    Either POOL_OFFICE (default) or POOL_LEADERSHIP.
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
        public readonly string $recipientPool = self::POOL_OFFICE,
    ) {
    }

    public function isRequisitionerStage(): bool
    {
        return $this->officeSlug === null && $this->recipientPool === self::POOL_OFFICE;
    }

    public function isLeadershipPool(): bool
    {
        return $this->recipientPool === self::POOL_LEADERSHIP;
    }

    public function isLeadershipOrOfficePool(): bool
    {
        return $this->recipientPool === self::POOL_LEADERSHIP_OR_OFFICE;
    }

    public function isCreatorPool(): bool
    {
        return $this->recipientPool === self::POOL_CREATOR;
    }

    /**
     * Internal Audit stage. Universities conventionally use green ink for
     * audit, so this stage's recorded comment and signature render in green
     * "audit ink" both on screen and in the PDF. Identified by the office it
     * routes to, since the stage slug varies across forms ('audit' / 'auditor').
     */
    public function isInternalAudit(): bool
    {
        return $this->officeSlug === 'internal-audit';
    }

    /**
     * Stages where the requisitioner/forwarder picks the recipient at the
     * point of forwarding (no fixed downstream office configured on the
     * stage itself). Creator-pool stages don't qualify — the recipient is
     * always the original applicant, no UI choice needed.
     */
    public function hasDynamicRecipient(): bool
    {
        return $this->isLeadershipPool() || $this->isLeadershipOrOfficePool();
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
