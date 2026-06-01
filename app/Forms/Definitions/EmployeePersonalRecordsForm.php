<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Employee Personal Records (Form EPR) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Captured at the point of hire so the Registrar's office and the Human
 * Resource Unit each hold a complete record of the new staff member.
 *
 * The paper form carries the rubric "The forms must be completed in duplicate
 * and forwarded to the Registrar's office." In the digital workflow the same
 * record is the canonical source of truth — both the Registrar and the HR
 * Unit countersign it so each office has formally acknowledged its copy.
 *
 * Workflow (3 stages):
 *   1. applicant — Parts I-IV: the employee fills personal & family details,
 *                  education, employment history, other information, then
 *                  signs the declaration.
 *   2. registrar — The Registrar's Office assigns the Staff Number and
 *                  Appointment Number, files the record, and signs.
 *   3. hr        — The Human Resource Unit takes its copy, confirms the
 *                  placement (faculty / department / position), and signs.
 *
 * Final-state of "fully filed" is signalled by completion of both office
 * stages — exactly the "duplicate forwarded to Registrar AND held by HR"
 * pattern described on the paper form.
 */
class EmployeePersonalRecordsForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'employee-personal-records';
    }

    public function code(): string
    {
        return 'EPR';
    }

    public function title(): string
    {
        return 'Employee Personal Records';
    }

    public function description(): string
    {
        return 'New-hire personal records form. Capture your personal & family details, education, employment history and other information, then route to the Registrar (who assigns your Staff No. and Appointment No.) and the Human Resource Unit, both of whom hold a copy on file.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.employee-personal-records';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.employee-personal-records';
    }

    public function pdfFooterNote(): ?string
    {
        return 'This form must be completed in duplicate and forwarded to the Registrar\'s Office.';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'applicant',
                label: 'Employee — Personal Records (Parts I–IV)',
                officeSlug: null,
                description: 'Fill in every field carefully and accurately. You will sign the declaration at the bottom. Once submitted, the form moves to the Registrar\'s Office (who assigns your Staff Number and Appointment Number) and finally to the Human Resource Unit for filing.',
                fields: [
                    // ═══════════════════════════════════════════════════════
                    // PART I — PERSONAL & FAMILY DETAILS
                    // ═══════════════════════════════════════════════════════
                    new FormField(name: 'part_1_heading', label: 'PART I — Personal & Family Details', type: FormField::TYPE_HEADING,
                        help: 'Please use BLOCK LETTERS. Items marked * are required.'),

                    // ── Personal Particulars ──
                    new FormField(name: 'sub_personal_heading', label: 'Personal Particulars', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'title',
                        label: 'Title',
                        type: FormField::TYPE_SELECT,
                        required: true,
                        col: 3,
                        options: [
                            'Mr'   => 'Mr',
                            'Mrs'  => 'Mrs',
                            'Miss' => 'Miss',
                            'Ms'   => 'Ms',
                            'Dr'   => 'Dr',
                            'Prof' => 'Prof',
                            'Rev'  => 'Rev',
                            'Fr'   => 'Fr',
                            'Sr'   => 'Sr',
                        ],
                    ),
                    new FormField('surname',      'Surname',                 FormField::TYPE_TEXT, required: true, col: 4, maxLength: 100,
                        help: 'In BLOCK LETTERS.'),
                    new FormField('other_names',  'Other Names (First / Middle)', FormField::TYPE_TEXT, required: true, col: 5, maxLength: 150,
                        help: 'First name(s) and middle name. The paper form has only "Surname", but capturing the full name here ensures unambiguous identification.'),

                    new FormField('nationality',  'Nationality',             FormField::TYPE_TEXT, required: true, col: 4, maxLength: 80),
                    new FormField('country',      'Country',                 FormField::TYPE_TEXT, required: true, col: 4, maxLength: 80),
                    new FormField('city_region',  'City / Region',           FormField::TYPE_TEXT, required: true, col: 4, maxLength: 120),

                    new FormField('home_town',    'Home Town',               FormField::TYPE_TEXT, required: true, col: 4, maxLength: 120),
                    new FormField('date_of_birth','Date of Birth',           FormField::TYPE_DATE, required: true, col: 4),
                    new FormField('religion',     'Religion',                FormField::TYPE_TEXT, required: false, col: 4, maxLength: 80),

                    new FormField('faculty_admin','Faculty / Admin',         FormField::TYPE_TEXT, required: true, col: 6, maxLength: 150,
                        help: 'The Faculty (e.g. "Faculty of Science") or "Administration" if you are non-academic staff.'),
                    new FormField('department',   'Department',              FormField::TYPE_TEXT, required: true, col: 6, maxLength: 150),

                    new FormField('email',           'E-mail',               FormField::TYPE_TEXT, required: true, col: 6, maxLength: 150),
                    new FormField('telephone_number','Telephone Number',     FormField::TYPE_TEXT, required: true, col: 6, maxLength: 60),

                    // ── Addresses ──
                    new FormField(name: 'sub_address_heading', label: 'Addresses', type: FormField::TYPE_HEADING),
                    new FormField('contact_address',   'Contact Address',    FormField::TYPE_TEXTAREA, required: true, col: 6,
                        help: 'Your current postal / residential address (the address at which you can be reached during the working week).'),
                    new FormField('permanent_address', 'Permanent Address',  FormField::TYPE_TEXTAREA, required: true, col: 6,
                        help: 'Your permanent home address.'),

                    // ── Appointment dates (employee fills; Registrar assigns Staff/Appointment numbers later) ──
                    new FormField(name: 'sub_appointment_heading', label: 'Appointment Dates', type: FormField::TYPE_HEADING,
                        help: 'Your Staff Number and Appointment Number will be assigned by the Registrar\'s Office at the next stage.'),
                    new FormField('date_of_appointment', 'Date of Appointment',     FormField::TYPE_DATE, required: true, col: 6,
                        help: 'The effective date of your appointment, as stated in your appointment letter.'),
                    new FormField('date_of_assumption',  'Date of Assumption of Duty', FormField::TYPE_DATE, required: true, col: 6,
                        help: 'The date on which you actually reported for duty.'),

                    // ── Marital Status & Dependants ──
                    new FormField(name: 'sub_marital_heading', label: 'Marital Status & Dependants', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'marital_status',
                        label: 'Marital Status',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 6,
                        options: [
                            'single'  => 'Single',
                            'married' => 'Married',
                        ],
                    ),
                    new FormField('name_of_spouse', 'Name of Spouse', FormField::TYPE_TEXT, required: false, col: 6, maxLength: 200,
                        help: 'Required only if married. Leave blank otherwise.'),
                    new FormField('dependants', 'Dependants', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'List the people (other than your spouse and children, who are captured separately below) who depend on you for support. Include the relationship.'),

                    // ── Vehicle Particulars (optional) ──
                    new FormField(name: 'sub_vehicle_heading', label: 'Particulars of Vehicle (if you own one)', type: FormField::TYPE_HEADING,
                        help: 'Optional. Attach relevant ownership documents (insurance, registration, log-book) as supporting attachments below.'),
                    new FormField('vehicle_no',          'Vehicle No.',     FormField::TYPE_TEXT, required: false, col: 4, maxLength: 60),
                    new FormField('vehicle_make_model',  'Make / Model',    FormField::TYPE_TEXT, required: false, col: 4, maxLength: 120),
                    new FormField('vehicle_chassis_no',  'Chassis No.',     FormField::TYPE_TEXT, required: false, col: 4, maxLength: 100),

                    // ── Pension & Banking ──
                    new FormField(name: 'sub_banking_heading', label: 'Pension & Banking Details', type: FormField::TYPE_HEADING),
                    new FormField('provident_fund_number',       'Provident Fund Number (if any)', FormField::TYPE_TEXT, required: false, col: 6, maxLength: 80),
                    new FormField('social_security_fund_number', 'Social Security Fund Number',    FormField::TYPE_TEXT, required: true,  col: 6, maxLength: 80,
                        help: 'Your SSNIT number.'),
                    new FormField('name_of_bank',                'Name of Bank',                   FormField::TYPE_TEXT, required: true,  col: 4, maxLength: 150),
                    new FormField('branch_name',                 'Branch Name',                    FormField::TYPE_TEXT, required: true,  col: 4, maxLength: 150),
                    new FormField('bank_account_number',         'Bank Account Number',            FormField::TYPE_TEXT, required: true,  col: 4, maxLength: 60),

                    // ── 8. Children ──
                    new FormField(name: 'sub_children_heading', label: '8. Children — Names and Dates of Birth', type: FormField::TYPE_HEADING,
                        help: 'Supporting documents (birth certificates, etc.) should be attached below. Add one row per child.'),
                    new FormField(
                        name: 'children',
                        label: 'Children',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        tableColumns: [
                            ['name' => 'child_name',   'label' => 'Name',          'col' => 8, 'required' => false, 'max' => 200, 'placeholder' => 'Full name of child'],
                            ['name' => 'date_of_birth','label' => 'Date of Birth', 'col' => 4, 'type' => FormField::TYPE_DATE,    'required' => false],
                        ],
                        minTableRows: 1,
                        maxTableRows: 10,
                        addRowLabel: 'Add another child',
                    ),

                    // ── 9. Names of Parents ──
                    new FormField(name: 'sub_parents_heading', label: '9. Names of Parents', type: FormField::TYPE_HEADING),
                    new FormField('father_name', 'Father — Name', FormField::TYPE_TEXT, required: true, col: 6, maxLength: 200),
                    new FormField('mother_name', 'Mother — Name', FormField::TYPE_TEXT, required: true, col: 6, maxLength: 200),

                    // ── 10. Next-of-Kin ──
                    new FormField(name: 'sub_kin_heading', label: '10. Next-of-Kin', type: FormField::TYPE_HEADING),
                    new FormField('next_of_kin_name',      'Name of Next-of-Kin',  FormField::TYPE_TEXT,     required: true, col: 6, maxLength: 200),
                    new FormField('next_of_kin_relation',  'Relation',             FormField::TYPE_TEXT,     required: true, col: 6, maxLength: 100,
                        help: 'e.g. Spouse, Father, Sister, etc.'),
                    new FormField('next_of_kin_address',   'Address',              FormField::TYPE_TEXTAREA, required: true, col: 8),
                    new FormField('next_of_kin_telephone', 'Telephone',            FormField::TYPE_TEXT,     required: true, col: 4, maxLength: 60),

                    // ── 11. Beneficiaries ──
                    new FormField(name: 'sub_beneficiaries_heading', label: '11. Beneficiaries', type: FormField::TYPE_HEADING,
                        help: 'List each beneficiary, their relation to you, the percentage of allocation, and their contact details. The total of all percentages should equal 100%.'),
                    new FormField(
                        name: 'beneficiaries',
                        label: 'Beneficiaries',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        tableColumns: [
                            ['name' => 'beneficiary_name',     'label' => 'Name',                            'col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'Full name'],
                            ['name' => 'relation',             'label' => 'Relation',                        'col' => 2, 'required' => false, 'max' => 100, 'placeholder' => 'e.g. Spouse'],
                            ['name' => 'percentage',           'label' => '% Allocation',                    'col' => 2, 'type' => FormField::TYPE_NUMBER, 'required' => false, 'placeholder' => 'e.g. 50'],
                            ['name' => 'address_contact',      'label' => 'Address / Contact Number',        'col' => 4, 'required' => false, 'max' => 300, 'placeholder' => 'Postal address and phone'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 10,
                        addRowLabel: 'Add another beneficiary',
                    ),

                    // ═══════════════════════════════════════════════════════
                    // PART II — DETAILS OF EDUCATION AND QUALIFICATIONS
                    // ═══════════════════════════════════════════════════════
                    new FormField(name: 'part_2_heading', label: 'PART II — Details of Education and Qualifications', type: FormField::TYPE_HEADING),

                    new FormField(name: 'sub_schools_heading', label: '12. Schools Attended', type: FormField::TYPE_HEADING,
                        help: 'List the institutions you have attended, with the dates you attended each. Add one row per institution.'),
                    new FormField(
                        name: 'schools_attended',
                        label: 'Schools Attended',
                        type: FormField::TYPE_TABLE,
                        required: true,
                        col: 12,
                        tableColumns: [
                            ['name' => 'institution', 'label' => 'Name of Institution', 'col' => 6, 'required' => false, 'max' => 250, 'placeholder' => 'e.g. University of Cape Coast'],
                            ['name' => 'from',        'label' => 'From',                'col' => 3, 'type' => FormField::TYPE_DATE,   'required' => false],
                            ['name' => 'to',          'label' => 'To',                  'col' => 3, 'type' => FormField::TYPE_DATE,   'required' => false],
                        ],
                        minTableRows: 1,
                        maxTableRows: 10,
                        addRowLabel: 'Add another institution',
                    ),

                    new FormField(name: 'sub_qualifications_heading', label: '13. Qualifications', type: FormField::TYPE_HEADING,
                        help: 'List your qualifications (degrees, diplomas, professional certifications, etc.). Attach photocopies of certificates / diplomas as supporting attachments below.'),
                    new FormField('qualifications', 'Qualifications', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'One per line — e.g. "BSc Computer Science, KNUST, 2015". Photocopies of certificates should be uploaded below.'),

                    // ═══════════════════════════════════════════════════════
                    // PART III — DETAILS OF EMPLOYMENT
                    // ═══════════════════════════════════════════════════════
                    new FormField(name: 'part_3_heading', label: 'PART III — Details of Employment', type: FormField::TYPE_HEADING,
                        help: 'List, beginning from your current / most recent employment, all places you have worked at — stating dates, position(s) held and reason for leaving.'),
                    new FormField(
                        name: 'employment_history',
                        label: 'Employment History',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        tableColumns: [
                            ['name' => 'organization',   'label' => 'Name of Organization', 'col' => 4, 'required' => false, 'max' => 250, 'placeholder' => 'e.g. KNUST'],
                            ['name' => 'date_from',      'label' => 'Date From',            'col' => 2, 'type' => FormField::TYPE_DATE,   'required' => false],
                            ['name' => 'date_to',        'label' => 'Date To',              'col' => 2, 'type' => FormField::TYPE_DATE,   'required' => false],
                            ['name' => 'position_held',  'label' => 'Position Held',        'col' => 2, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. Lecturer'],
                            ['name' => 'reasons',        'label' => 'Reasons for Leaving',  'col' => 2, 'required' => false, 'max' => 300, 'placeholder' => 'e.g. Contract ended'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 12,
                        addRowLabel: 'Add another role',
                    ),

                    // ═══════════════════════════════════════════════════════
                    // PART IV — OTHER INFORMATION
                    // ═══════════════════════════════════════════════════════
                    new FormField(name: 'part_4_heading', label: 'PART IV — Other Information', type: FormField::TYPE_HEADING),

                    new FormField(
                        name: 'convicted_offence',
                        label: '14. Have you ever been convicted of any criminal or legal offence?',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'no'  => 'No',
                            'yes' => 'Yes (give full details below)',
                        ],
                    ),
                    new FormField('conviction_details', 'If yes, please give full details', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Required only if you answered "Yes" above. Note the offence, date, court, and outcome.'),

                    new FormField('position_at_cug',     '15. Position given at CUG',   FormField::TYPE_TEXT, required: true,  col: 6, maxLength: 200),
                    new FormField('cug_office_department','16. Office / Department',    FormField::TYPE_TEXT, required: true,  col: 6, maxLength: 200,
                        help: 'The CUG office or department you are joining. May match "Department" above if you are joining a teaching department.'),

                    new FormField(
                        name: 'declaration_accepted',
                        label: 'Declaration',
                        type: FormField::TYPE_CHECKBOX,
                        required: true,
                        col: 12,
                        help: 'I hereby certify that, to the best of my knowledge, all the details given in this form are correct. I understand that any proven falsification or concealment of any material fact in respect of this record may lead to disciplinary action and possible dismissal.',
                    ),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'Registrar — Assign Staff Number & File',
                officeSlug: 'registrar',
                description: 'Assign the new staff member their Staff Number and Appointment Number, then sign to file the Registrar\'s copy. The form will then move to the Human Resource Unit for the duplicate copy.',
                fields: [
                    new FormField('staff_no',        'Staff Number',         FormField::TYPE_TEXT, required: true, col: 6, maxLength: 60,
                        help: 'The Staff Number assigned by the Registrar\'s Office. Marked "Office Use" on the paper form.'),
                    new FormField('appointment_no',  'Appointment Number',   FormField::TYPE_TEXT, required: true, col: 6, maxLength: 60,
                        help: 'The Appointment Reference Number issued for this employee.'),
                    new FormField('registrar_comments', 'Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Optional remarks on filing — e.g. confirmation of identity documents sighted.'),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'hr',
                label: 'Human Resource Unit — Final Filing',
                officeSlug: 'human-resource-unit',
                description: 'Take the duplicate copy of the record for the Human Resource Unit\'s files. Confirm the placement details and sign to complete the workflow.',
                fields: [
                    new FormField('hr_placement_confirmed', 'Placement confirmed', FormField::TYPE_CHECKBOX, required: true, col: 12,
                        help: 'I confirm that the staff member\'s faculty / department / position as stated above match the HR records, and the personal-records copy has been filed with the Human Resource Unit.'),
                    new FormField('hr_comments', 'Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Optional remarks — induction notes, missing documents requested, etc.'),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
