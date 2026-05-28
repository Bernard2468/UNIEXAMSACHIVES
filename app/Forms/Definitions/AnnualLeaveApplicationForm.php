<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Annual Leave Application Form (Form AL) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Submitted to the Office of the Registrar.
 *
 * Stages, in order:
 *   1. officer       — Section 1-13 (Officer fills the application and signs)
 *   2. recommender   — Section 14 (Dean/HOD/Director OR Head of any Office recommends)
 *   3. registrar     — Section 15 (Registrar approves and signs)
 */
class AnnualLeaveApplicationForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'annual-leave-application';
    }

    public function code(): string
    {
        return 'AL';
    }

    public function title(): string
    {
        return 'Annual Leave Application Form';
    }

    public function description(): string
    {
        return 'Apply for annual leave. Routes to a Dean / HOD / Director or any Office head for recommendation, then on to the Registrar for approval.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.annual-leave-application';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.annual-leave-application';
    }

    public function pdfFooterNote(): ?string
    {
        return 'Instruction to Head, Human Resource Unit: Please deduct approved days taken or add approved deferred days to his/her outstanding leave days.';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'officer',
                label: 'Officer — Application Details',
                officeSlug: null,
                description: 'Fill in your particulars and the leave you are requesting, then sign.',
                fields: [
                    new FormField('name',                'Name of Officer',                FormField::TYPE_TEXT,     required: true,  col: 8),
                    new FormField('rank',                'Rank',                            FormField::TYPE_TEXT,     required: true,  col: 4),
                    new FormField('faculty_department',  'Faculty / School / Department / Unit', FormField::TYPE_TEXT, required: true, col: 12),

                    new FormField('last_leave_from',     'Last Leave — From',               FormField::TYPE_DATE,     required: false, col: 6,
                        help: 'Leave blank if you have not taken any leave yet.'),
                    new FormField('last_leave_to',       'Last Leave — To',                 FormField::TYPE_DATE,     required: false, col: 6),

                    new FormField('current_entitlement', "Current Academic Year's Leave Entitlement (days)", FormField::TYPE_NUMBER, required: true, col: 4),
                    new FormField('accrued_days',        'Accrued / Outstanding Leave Days', FormField::TYPE_NUMBER,   required: false, col: 4),
                    new FormField('total_entitlement',   'Total Leave Entitlement (days)',   FormField::TYPE_NUMBER,   required: true,  col: 4),

                    new FormField('proposed_days',       'Proposed Leave Days',             FormField::TYPE_NUMBER,   required: true,  col: 4),
                    new FormField('proposed_from',       'Proposed Leave — From',           FormField::TYPE_DATE,     required: true,  col: 4),
                    new FormField('proposed_to',         'Proposed Leave — To',             FormField::TYPE_DATE,     required: true,  col: 4),

                    new FormField('purpose',             'Purpose of Taking the Leave',     FormField::TYPE_TEXTAREA, required: true,  col: 12),

                    new FormField('resumption_date',     'Date of Resumption of Duty',      FormField::TYPE_DATE,     required: true,  col: 6),
                    new FormField('deferred_days',       'Total Deferred / Outstanding Days', FormField::TYPE_NUMBER, required: false, col: 6),

                    new FormField('address',             'Address(es)',                     FormField::TYPE_TEXTAREA, required: true,  col: 12),
                    new FormField('phone',               'Telephone Number',                FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 30),
                    new FormField('email',               'E-mail Address',                  FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 150),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'recommender',
                label: 'Recommendation by Dean / HOD / Director / Office',
                officeSlug: null,
                description: "Recommendation / Comment(s) by the Dean, Head of Department, Director or the Head of an Office. The officer chooses where this should go.",
                fields: [
                    new FormField('recommendation', 'Recommendation / Comment(s)', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Endorse, comment on, or decline the application.'),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP_OR_OFFICE,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'Approval by Registrar',
                officeSlug: 'registrar',
                description: 'Registrar approves the application. The Head of Human Resource Unit will deduct approved days taken or add approved deferred days to the officer\'s outstanding leave days.',
                fields: [
                    new FormField('registrar_comments', 'Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
