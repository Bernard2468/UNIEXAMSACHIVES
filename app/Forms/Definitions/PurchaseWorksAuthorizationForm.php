<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Purchase / Works Authorization Form (Form PWA) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Used to request authorisation for budgeted (or non-budgeted) purchases or
 * works above GH₵5,000.00. When authorised, the form is sent to Finance for
 * Financial Clearance before an LPO is issued.
 *
 * Stages, in order:
 *   1. requisitioner       — Section A
 *   2. hod_dean            — Section A (Dean/HOD co-sign)
 *   3. finance             — Section B (Finance Office: budget confirmation, codes)
 *   4. procurement         — Section C (Procurement Committee decision)
 *   5. audit               — Section D (Internal Audit referrals/comments)
 *   6. registrar           — Section E (Registrar approves / refers to VC)
 *   7. vc                  — Optional VC approval branch
 */
class PurchaseWorksAuthorizationForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'purchase-works-authorization';
    }

    public function code(): string
    {
        return 'PWA';
    }

    public function title(): string
    {
        return 'Purchase / Works Authorization Form';
    }

    public function description(): string
    {
        return 'Request authorisation for purchases or works above GH₵5,000. Routes through Dean/HOD, Finance, Procurement Committee, Internal Audit and the Registrar.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.purchase-works-authorization';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.purchase-works-authorization';
    }

    public function amountFieldName(): ?string
    {
        return 'non_budget_amount';
    }

    public function vcReferralFieldName(): ?string
    {
        return 'referred_for_vc';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'requisitioner',
                label: 'A. Requisitioner',
                officeSlug: null,
                description: 'Describe the purchase or works being requested, then sign.',
                fields: [
                    new FormField('name',                'Name',                FormField::TYPE_TEXT, required: true,  col: 6),
                    new FormField('job_title',           'Job Title',           FormField::TYPE_TEXT, required: true,  col: 6),
                    new FormField('faculty_department', 'Faculty / Department', FormField::TYPE_TEXT, required: true,  col: 6),
                    new FormField('phone',               'Phone #',             FormField::TYPE_TEXT, required: true,  col: 6, maxLength: 30),
                    new FormField('purchase_description', 'Purchase Description', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Please attach relevant documents, invoices, etc.'),
                    new FormField(
                        name: 'budget_status',
                        label: 'Budget Status',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'budgeted'     => 'Budgeted Item',
                            'non_budgeted' => 'Non-Budgeted Item',
                        ],
                    ),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'hod_dean',
                label: 'A. Dean / HOD / Director Co-Sign',
                officeSlug: null,
                description: 'The Dean, Head of Department or Director endorses the requisitioner. Pick the specific person on the next step.',
                fields: [
                    new FormField('hod_remarks', 'Remarks (optional)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP,
            ),

            new FormStage(
                slug: 'finance',
                label: 'B. Finance Office',
                officeSlug: 'finance-office',
                description: 'Finance Office confirms budget status and assigns the budget item code.',
                fields: [
                    new FormField(
                        name: 'budget_confirmation',
                        label: 'Budget Confirmation',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'budgeted_purchase'     => 'Budgeted Purchase / Works',
                            'non_budgeted_purchase' => 'Non-Budgeted Purchase / Works',
                        ],
                    ),
                    new FormField(
                        name: 'within_budget_limit',
                        label: 'If budgeted, is the amount within approved budget limit?',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'yes' => 'Yes',
                            'no'  => 'No',
                        ],
                    ),
                    new FormField('non_budget_amount', 'If No, state budget allocation for the item(s) (GhS)', FormField::TYPE_CURRENCY, required: false, col: 6,
                        help: 'Attach schedule, if any.'),
                    new FormField('budget_item_code',  'Budget Item Code', FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 50),
                    new FormField('fo_comments',       'Finance Office Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'procurement',
                label: 'C. Procurement Committee',
                officeSlug: 'procurement-committee',
                description: 'Procurement Committee records its decision.',
                fields: [
                    new FormField('committee_decision', 'Committee Decision', FormField::TYPE_TEXTAREA, required: true, col: 12),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'audit',
                label: 'D. Internal Audit',
                officeSlug: 'internal-audit',
                description: 'Internal Audit records referrals or comments after vetting.',
                fields: [
                    new FormField('audit_comments', 'Referrals / Comments after vetting', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'E. Registrar',
                officeSlug: 'registrar',
                description: 'Registrar approves, or refers the request to the Vice-Chancellor.',
                branches: ['vc'],
                fields: [
                    new FormField('referred_for_vc', "Refer for VC's Approval", FormField::TYPE_CHECKBOX, required: false, col: 12,
                        help: 'Tick to route this request to the Vice-Chancellor before final authorisation.'),
                    new FormField('registrar_comments', 'Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                    new FormField('approved_by_name', 'Approved By (Name)', FormField::TYPE_TEXT, required: true, col: 8),
                    new FormField('approved_value',   'Approved Value (GhS)', FormField::TYPE_CURRENCY, required: true, col: 4),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'vc',
                label: "VC's Approval",
                officeSlug: 'vc',
                description: 'Vice-Chancellor approves requests referred up by the Registrar.',
                fields: [
                    new FormField('vc_comments', 'VC Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
                optional: true,
            ),
        ];
    }
}
