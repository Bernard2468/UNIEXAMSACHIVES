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
     * Does this form request a passport-style photo on the applicant's
     * compose page? When true, the compose view renders a click-to-upload
     * square box in the top-right of the first panel and the controller
     * prepends the chosen file to the attachments array (so the PDF's
     * "first image at applicant stage" detection picks it up as the
     * passport photograph).
     *
     * Default: false — most forms don't need one.
     */
    public function requiresPassportPhoto(): bool
    {
        return false;
    }

    /**
     * Optional list of field NAMES (declared on the given stage) that should
     * be visually relocated from the main field grid into the Attachments
     * panel body. Useful for "I confirm I've attached X" checkboxes that
     * read more naturally next to the file uploader than buried at the end
     * of the question list.
     *
     * The fields stay in the stage definition (so they validate and persist
     * exactly as before) — only their *rendering location* changes. The
     * compose and show pages exclude these names from the main pass and
     * re-render them inside the Attachments panel.
     *
     * Default: empty (no relocation — current behaviour).
     *
     * @param  string $stageSlug
     * @return array<int, string>
     */
    public function attachmentsPanelFieldNames(string $stageSlug): array
    {
        return [];
    }

    /**
     * Optional multi-step "wizard" configuration for the requisitioner /
     * applicant compose page. When non-null, the first stage's fields are
     * grouped into the named steps (driven by `startAt` field-name
     * boundaries) and rendered with a stepper UI + Next/Previous controls
     * so long forms don't force endless scrolling.
     *
     * Each entry is an associative array:
     *   - 'key'     : stable step identifier (used only for DOM ids)
     *   - 'label'   : short label shown in the stepper chip
     *   - 'startAt' : name of the FIRST field belonging to this step
     *                 (typically a HEADING field). All subsequent fields,
     *                 up to but not including the next step's startAt,
     *                 are rendered inside this step.
     *   - 'icon'    : optional SVG markup or character shown on the chip
     *
     * Return null = single-step compose page (current default behaviour).
     *
     * @return array<int, array{key:string,label:string,startAt:string,icon?:string,description?:string}>|null
     */
    public function composeWizardSteps(): ?array
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
