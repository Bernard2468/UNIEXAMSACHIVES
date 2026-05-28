<?php

namespace App\Forms;

/**
 * Abstract base every form type extends.
 *
 * A FormDefinition encapsulates everything about ONE form type:
 *   - Identity (slug, code, title)
 *   - The list of stages (in order) it passes through
 *   - Which Blade template renders it
 *   - Where the amount field lives (so we can populate `requisition_amount`
 *     for indexing/filtering without hardcoding per-form logic)
 *
 * Adding a new form to the system = create one subclass + register it in
 * `App\Providers\AppServiceProvider`. No DB, route, or controller changes
 * are needed.
 */
abstract class BaseFormDefinition
{
    /** Stable form identifier; used in URLs and form_submissions.form_slug. */
    abstract public function slug(): string;

    /** Short code printed on the paper form, e.g. 'PR' or 'PWA'. */
    abstract public function code(): string;

    /** Human title shown in the gallery and in the page header. */
    abstract public function title(): string;

    /** One-line description shown in the "All Forms" gallery. */
    abstract public function description(): string;

    /**
     * Ordered list of stages. Index 0 must be the requisitioner stage.
     *
     * @return array<int, FormStage>
     */
    abstract public function stages(): array;

    /**
     * Path to the Blade view that renders this form's full layout
     * (e.g. 'admin.forms.templates.payment-requisition').
     */
    abstract public function templateView(): string;

    /**
     * Path to the Blade view that renders the PDF version.
     */
    abstract public function pdfView(): string;

    /**
     * Name of the field (inside the requisitioner stage) that holds the
     * monetary amount, if any. Used to populate FormSubmission.requisition_amount
     * for portfolio-wide filtering. Return null for forms with no amount.
     */
    public function amountFieldName(): ?string
    {
        return null;
    }

    /**
     * Name of the field (inside any stage) that, when checked, flips the
     * form into VC referral mode. Null = form does not support VC referral.
     */
    public function vcReferralFieldName(): ?string
    {
        return null;
    }

    /**
     * Optional standing instruction printed at the bottom of the PDF —
     * mirrors any "Instruction to ..." note on the paper form.
     */
    public function pdfFooterNote(): ?string
    {
        return null;
    }

    /**
     * Convenience lookup of a stage by slug.
     */
    public function stage(string $slug): ?FormStage
    {
        foreach ($this->stages() as $stage) {
            if ($stage->slug === $slug) {
                return $stage;
            }
        }

        return null;
    }

    /**
     * Compute the natural next stage after the given slug.
     *
     * - Skips optional stages by default; the caller asks for them explicitly
     *   when a branch is triggered (e.g. VC referral).
     * - Returns null when there is no next stage (the form is complete).
     */
    public function nextStageAfter(string $slug, bool $includeOptional = false): ?FormStage
    {
        $found = false;
        foreach ($this->stages() as $stage) {
            if ($found) {
                if ($stage->optional && !$includeOptional) {
                    continue;
                }
                return $stage;
            }
            if ($stage->slug === $slug) {
                $found = true;
            }
        }

        return null;
    }

    public function firstStage(): FormStage
    {
        return $this->stages()[0];
    }

    public function lastStage(): FormStage
    {
        $stages = $this->stages();
        return $stages[count($stages) - 1];
    }
}
