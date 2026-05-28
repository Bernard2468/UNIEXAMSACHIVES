<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Application for Car / Motorcycle / Bicycle Maintenance Allowance (Form VMA)
 * — Catholic University of Ghana, Fiapre.
 *
 * Staff who use their own vehicle to commute to and from the university apply
 * for a monthly maintenance allowance. The form moves through:
 *
 *   1. applicant    — Section 1 (a + b): applicant + vehicle particulars
 *   2. head         — Section 2: Head of Department/Section/Unit endorses
 *                                (flexible pool — HOD / Dean / Director /
 *                                 any Office head can sign)
 *   3. auditor      — Section 3: Internal Auditor inspects the vehicle and
 *                                relevant documents, recommends payment
 *   4. registrar    — Section 4: Registrar approves and names the effective
 *                                date from which the allowance applies
 */
class VehicleMaintenanceAllowanceForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'vehicle-maintenance-allowance';
    }

    public function code(): string
    {
        return 'VMA';
    }

    public function title(): string
    {
        return 'Application for Car/Motorcycle/Bicycle Maintenance Allowance';
    }

    public function description(): string
    {
        return 'Apply for maintenance allowance on the car / motorcycle / bicycle you use to commute to and from the university. Routes through your Head of Department / Section / Unit, the Internal Auditor for inspection, and the Registrar for final approval.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.vehicle-maintenance-allowance';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.vehicle-maintenance-allowance';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'applicant',
                label: '1. To be completed by Applicant',
                officeSlug: null,
                description: 'Fill in your details (a) and the particulars of your vehicle (b), then sign.',
                fields: [
                    // ── (a) Personal details ──
                    new FormField('name',                 'Name',                         FormField::TYPE_TEXT, required: true,  col: 8),
                    new FormField('post_status',          'Post / Status',                FormField::TYPE_TEXT, required: true,  col: 4),
                    new FormField('department_section_unit', 'Department / Section / Unit', FormField::TYPE_TEXT, required: true, col: 12),
                    new FormField('assumption_of_duty_date',  'Date of Assumption of Duty', FormField::TYPE_DATE, required: true, col: 6),
                    new FormField('vehicle_use_start_date',   'Date on Which Staff Started Using Car / Motorcycle / Bicycle', FormField::TYPE_DATE, required: true, col: 6),

                    // ── (b) Vehicle particulars ──
                    new FormField(
                        name: 'vehicle_type',
                        label: 'Vehicle Type',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'car'        => 'Car',
                            'motorcycle' => 'Motorcycle',
                            'bicycle'    => 'Bicycle',
                        ],
                    ),
                    new FormField('registration_number', 'Registration Number', FormField::TYPE_TEXT, required: true, col: 6, maxLength: 60),
                    new FormField('type_and_make',       'Type & Make',         FormField::TYPE_TEXT, required: true, col: 6, maxLength: 120,
                        help: 'e.g. Toyota Corolla, Honda CB125, Cannondale CAAD13.'),
                    new FormField('cubic_capacity',      'Cubic Capacity',      FormField::TYPE_TEXT, required: false, col: 6, maxLength: 30,
                        help: "e.g. 1500cc. Leave blank for bicycles."),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'head',
                label: '2. Endorsement by Head of Department / Section / Unit',
                officeSlug: null,
                description: 'Confirm that the applicant uses the named vehicle for commuting to and from the university, then sign. The applicant chooses where this should go (HOD, Dean, Director or Office head).',
                fields: [
                    new FormField('head_comments', 'Remarks (optional)', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: "Your signature endorses the confirmation: \"I confirm that {applicant} uses his/her car / motorcycle / bicycle for commuting to and from the university.\""),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP_OR_OFFICE,
            ),

            new FormStage(
                slug: 'auditor',
                label: '3. Endorsement by Internal Auditor',
                officeSlug: 'internal-audit',
                description: 'After inspecting the vehicle and the relevant documents, confirm that it belongs to the applicant and recommend payment of the maintenance allowance.',
                fields: [
                    new FormField('audit_comments', 'Inspection notes (optional)', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Your signature endorses the confirmation: "I have inspected the above named car / motorcycle / bicycle and all relevant documents and confirm that the vehicle belongs to the applicant. Payment of maintenance allowance is recommended."'),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'registrar',
                label: '4. Approval by Registrar',
                officeSlug: 'registrar',
                description: 'Approve the maintenance allowance and set the date from which it takes effect.',
                fields: [
                    new FormField('effective_from',      'Allowance to Take Effect From', FormField::TYPE_DATE, required: true, col: 6,
                        help: 'The month/date from which the maintenance allowance starts.'),
                    new FormField('registrar_comments',  'Comments (if any)',             FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
