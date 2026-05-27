<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Payment Requisition Form (Form PR) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Stages, in order:
 *   1. requisitioner       — Section A (the staff member raising the request)
 *   2. hod_dean            — Section A (Director / Dean / HOD co-signs)
 *   3. finance             — Section B (Finance Processing: codes + accountant + DF)
 *   4. audit               — Section C (Internal Audit vetting comments)
 *   5. registrar           — Section D (Registrar approves, may refer to VC)
 *   6. vc                  — VC approval (optional, only when Registrar refers up)
 *   7. finance_director    — Final authorisation to Accountant or Cashier
 */
class PaymentRequisitionForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'payment-requisition';
    }

    public function code(): string
    {
        return 'PR';
    }

    public function title(): string
    {
        return 'Payment Requisition Form';
    }

    public function description(): string
    {
        return 'Request payment for services, expenses or reimbursements. Routes through Director/Dean, Finance, Internal Audit, Registrar and the Director of Finance.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.payment-requisition';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.payment-requisition';
    }

    public function amountFieldName(): ?string
    {
        return 'amount';
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
                description: 'Fill in your details and the payment request, then sign.',
                fields: [
                    new FormField('name',                'Name',                FormField::TYPE_TEXT,     required: true,  col: 8),
                    new FormField('faculty_department', 'Faculty / Department', FormField::TYPE_TEXT,     required: true,  col: 4),
                    new FormField('job_title',           'Job Title',           FormField::TYPE_TEXT,     required: true,  col: 8),
                    new FormField('phone',               'Phone #',             FormField::TYPE_TEXT,     required: true,  col: 4, maxLength: 30),
                    new FormField('brief_payment_request','Brief Payment Request', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Please attach original supporting documents, invoice, receipts, etc.'),
                    new FormField('amount',              'Amount (GhS)',        FormField::TYPE_CURRENCY, required: true,  col: 4),
                    new FormField('payee_name',          "Payee's Name",        FormField::TYPE_TEXT,     required: true,  col: 8),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'hod_dean',
                label: 'A. Director / Dean / HOD Co-Sign',
                officeSlug: 'dean-hod',
                description: 'Director, Dean or Head of Department endorses the requisitioner.',
                fields: [
                    new FormField('hod_remarks', 'Remarks (optional)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'finance',
                label: 'B. Finance Processing',
                officeSlug: 'finance-office',
                description: 'Finance Office assigns budget codes and the Director of Finance comments.',
                fields: [
                    new FormField('expense_code',     'Expense Code',     FormField::TYPE_TEXT, required: true,  col: 4, maxLength: 50),
                    new FormField('cost_centre_code', 'Cost Centre Code', FormField::TYPE_TEXT, required: true,  col: 4, maxLength: 50),
                    new FormField('accrent_code',     'Accrent Code',     FormField::TYPE_TEXT, required: false, col: 4, maxLength: 50),
                    new FormField('df_comments',      "Director of Finance Comments (if any)", FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'audit',
                label: 'C. Internal Audit',
                officeSlug: 'internal-audit',
                description: 'Internal Audit records its vetting comments.',
                fields: [
                    new FormField('audit_comments', 'Comments after vetting (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'D. Registrar',
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

            new FormStage(
                slug: 'finance_director',
                label: 'E. Director of Finance — Payment Authorisation',
                officeSlug: 'director-of-finance',
                description: 'Final authorisation. Choose whether payment goes to the Accountant or the Cashier.',
                fields: [
                    new FormField(
                        name: 'payment_authorisation_to',
                        label: 'Payment Authorisation To',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'accountant' => 'Accountant',
                            'cashier'    => 'Cashier',
                        ],
                    ),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
