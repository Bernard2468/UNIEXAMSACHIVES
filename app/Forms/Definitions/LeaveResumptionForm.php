<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Leave Resumption Form (Form LR) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Filed by an officer after returning from leave to record the actual resumption
 * date, outstanding leave days, and have it endorsed up the chain.
 *
 * Stages, in order:
 *   1. officer       — Sections 1-7 (Officer fills the resumption details, signs)
 *   2. recommender   — Section 8 (Dean/HOD/Director OR Head of any Office)
 *   3. hr            — Section 9 (Head of Human Resource Unit vets the dates)
 *   4. registrar     — Section 10 (Registrar approves and signs)
 *
 * Distinct from the leave-application family because the HR Unit stage sits
 * between the recommender and the Registrar — they vet the resumption date
 * and outstanding leave-day balance before the Registrar signs off.
 */
class LeaveResumptionForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'leave-resumption';
    }

    public function code(): string
    {
        return 'LR';
    }

    public function title(): string
    {
        return 'Leave Resumption Form';
    }

    public function description(): string
    {
        return 'File your resumption after a leave. Routes through your Dean / HOD / Director / Office head, then the Head of the Human Resource Unit, then the Registrar for approval.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.leave-resumption';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.leave-resumption';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'officer',
                label: 'Officer — Resumption Details',
                officeSlug: null,
                description: 'Fill in your particulars, the dates of your last leave and the date you resumed duty, then sign.',
                fields: [
                    new FormField('name',                'Name of Officer',                FormField::TYPE_TEXT,     required: true,  col: 8),
                    new FormField('rank',                'Rank',                            FormField::TYPE_TEXT,     required: true,  col: 4),
                    new FormField('faculty_department',  'Faculty / School / Department / Unit', FormField::TYPE_TEXT, required: true, col: 12),

                    new FormField('last_leave_from',     'Last Leave — From',               FormField::TYPE_DATE,     required: true,  col: 6),
                    new FormField('last_leave_to',       'Last Leave — To',                 FormField::TYPE_DATE,     required: true,  col: 6),

                    new FormField('resumption_date',     'Date of Resumption of Duty',      FormField::TYPE_DATE,     required: true,  col: 6),
                    new FormField('deferred_leave_days', 'Total Deferred / Outstanding Leave Days', FormField::TYPE_NUMBER, required: false, col: 6),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'recommender',
                label: 'Comment by Dean / HOD / Director / Office',
                officeSlug: null,
                description: 'Comment(s) by the Dean, Head of Department, Director or Head of an Office as to whether the staff reported on the expected resumption date or not. The officer chooses where this should go.',
                fields: [
                    new FormField('recommender_comment', "Comment(s) — did the staff report on the expected resumption date?", FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Confirm whether the staff reported on the expected resumption date, or note the actual date and reason for any deviation.'),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP_OR_OFFICE,
            ),

            new FormStage(
                slug: 'hr',
                label: 'Head of Human Resource Unit — Vetting',
                officeSlug: 'human-resource-unit',
                description: 'Vet the resumption date and the number of leave days outstanding for further action.',
                fields: [
                    new FormField('hr_comment', 'Vetting note — resumption date & outstanding leave days', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Confirm the resumption date and the running balance of leave days, and note any further action required.'),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'Approval by Registrar',
                officeSlug: 'registrar',
                description: 'Registrar approves the resumption record.',
                fields: [
                    new FormField('registrar_comments', 'Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
