<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Employee Personal Records (Form EPR) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * The employee's personal-records form. Conceptually the employee is filing
 * *two copies* of the same record — one with the Human Resource Unit
 * (primary holder of staff personnel files) and one with the Registrar's
 * Office (institutional record). It is the EMPLOYEE who is initiating both
 * filings — neither office is gate-keeping the other.
 *
 * Workflow (3 stages):
 *   1. applicant — Parts I-IV: the employee fills personal & family details,
 *                  education, employment history, other information, then
 *                  signs the declaration. The compose page renders as a
 *                  four-step wizard (see composeWizardSteps()) so the
 *                  employee isn't scrolling through one giant page.
 *   2. hr        — Human Resource Unit (the primary recipient): files its
 *                  copy, assigns the Staff Number, confirms placement and
 *                  signs.
 *   3. registrar — Registrar's Office (the second recipient): files its
 *                  copy, assigns the Appointment Number and signs.
 *
 * Both office stages exist so each office formally acknowledges receipt of
 * its own copy — matching the paper rubric "forms must be completed in
 * duplicate and forwarded to the Registrar's Office", with HR taking the
 * lead because the form is primarily an HR personnel record.
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
        return 'Your personal records form. Capture your personal & family details, education, employment history and other information, then file a copy with the Human Resource Unit (who will assign your Staff No.) and a copy with the Registrar\'s Office (who will assign your Appointment No.). Filled across four short steps — you won\'t be scrolling forever.';
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

    public function requiresPassportPhoto(): bool
    {
        return true;
    }

    /**
     * Four-step wizard for the applicant stage so the employee doesn't
     * have to scroll through Part I → IV on a single page. Each entry's
     * `startAt` names the FIRST field in that step — the renderer
     * groups every following field into the step until it sees the
     * `startAt` of the next step.
     */
    public function composeWizardSteps(): ?array
    {
        return [
            [
                'key'         => 'part1',
                'label'       => 'Personal Particulars',
                'startAt'     => 'part_1_heading',
                'description' => 'Items 1–7 (name, date of birth, place of birth, addresses, marital status, spouse) and items 8–11 (children, parents, next-of-kin, beneficiaries).',
            ],
            [
                'key'         => 'part2',
                'label'       => 'Education',
                'startAt'     => 'part_2_heading',
                'description' => 'Schools attended and your qualifications. Attach copies of your certificates at the final step.',
            ],
            [
                'key'         => 'part3',
                'label'       => 'Employment',
                'startAt'     => 'part_3_heading',
                'description' => 'Every place you have worked at, starting from your current / most recent role.',
            ],
            [
                'key'         => 'part4',
                'label'       => 'Other Info & Sign',
                'startAt'     => 'part_4_heading',
                'description' => 'Convictions disclosure, position at CUG, SSNIT number, appointment dates, declaration. Upload your passport photograph + supporting documents and sign here.',
            ],
        ];
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'applicant',
                label: 'Employee — Personal Records (Parts I–IV)',
                officeSlug: null,
                description: 'Fill in every field accurately, upload your passport photo, then sign. Your copy goes to HR (who assigns your Staff No.) and to the Registrar (who assigns your Appointment No.).',
                fields: [
                    // ═══════════════════════════════════════════════════════
                    // PART I — PERSONAL PARTICULARS
                    // ═══════════════════════════════════════════════════════
                    new FormField(name: 'part_1_heading', label: 'PART I — Personal Particulars', type: FormField::TYPE_HEADING,
                        help: 'Items 1–7 are personal particulars. Items 8–11 cover children, parents, next-of-kin and beneficiaries.'),

                    // ── 1. Name ──
                    new FormField('full_name', '1. Name (Full Name in BLOCK LETTERS)', FormField::TYPE_TEXT, required: true, col: 12, maxLength: 250,
                        help: 'Surname followed by other names, exactly as on your national ID. Use BLOCK LETTERS.'),

                    // ── 2. Date of Birth + Age + Gender ──
                    new FormField('date_of_birth', '2. Date of Birth', FormField::TYPE_DATE, required: true, col: 4,
                        calculatesAgeTarget: 'age'),
                    new FormField('age', 'Age (years)', FormField::TYPE_NUMBER, required: true, col: 4,
                        help: 'Auto-calculated from your Date of Birth. You can override it if needed.'),
                    new FormField(
                        name: 'gender',
                        label: 'Gender',
                        type: FormField::TYPE_SELECT,
                        required: true,
                        col: 4,
                        options: [
                            'male'   => 'Male',
                            'female' => 'Female',
                        ],
                    ),

                    // ── 3. Place of Birth + Home Town ──
                    new FormField('place_of_birth', '3. Place of Birth', FormField::TYPE_TEXT, required: true, col: 6, maxLength: 150),
                    new FormField('home_town',      'Home Town',         FormField::TYPE_TEXT, required: true, col: 6, maxLength: 150),

                    // ── 4. Contact Address ──
                    new FormField('contact_address', '4. Contact Address', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Your current postal / residential address (the address at which you can be reached during the working week).'),

                    // ── 5. Permanent Address + Email + Telephone ──
                    new FormField('permanent_address', '5. Permanent Address', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Your permanent home address.'),
                    new FormField('email',            'Email',              FormField::TYPE_TEXT, required: true, col: 6, maxLength: 150),
                    new FormField('telephone_number', 'Telephone Number(s)', FormField::TYPE_TEXT, required: true, col: 6, maxLength: 100,
                        help: 'Separate multiple numbers with a comma.'),

                    // ── 6. Marital Status ──
                    new FormField(
                        name: 'marital_status',
                        label: '6. Marital Status',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'single'  => 'Single',
                            'married' => 'Married',
                        ],
                    ),

                    // ── 7. Name and Address of Spouse (with supporting document) ──
                    new FormField('spouse_name_and_address', '7. Name and Address of Spouse', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Required only if married. Include your spouse\'s full name and address. Attach a supporting document (marriage certificate) via the Attachments panel at the final step.'),

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
                        help: 'The CUG office or department you are joining.'),

                    new FormField('social_security_number', '17. Social Security Number (if any)', FormField::TYPE_TEXT, required: false, col: 12, maxLength: 80,
                        help: 'Your SSNIT number. Leave blank if you have not yet been registered.'),

                    new FormField('date_of_first_appointment', '18. Date of 1st Appointment', FormField::TYPE_DATE, required: true, col: 6,
                        help: 'The effective date of your appointment, as stated in your appointment letter.'),
                    new FormField('date_of_assumption_of_duty', 'Date of Assumption of Duty', FormField::TYPE_DATE, required: true, col: 6,
                        help: 'The date on which you actually reported for duty.'),

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
                slug: 'hr',
                label: 'Human Resource Unit — Primary Copy',
                officeSlug: 'human-resource-unit',
                description: 'The Human Resource Unit holds the primary copy of this record. Assign the Staff Number, confirm the placement details (faculty / department / position) and sign to file the HR copy. The form then moves on to the Registrar\'s Office for the second copy.',
                fields: [
                    new FormField('staff_no',                'Staff Number',         FormField::TYPE_TEXT, required: true, col: 6, maxLength: 60,
                        help: 'The Staff Number assigned by HR for this new employee. Marked "Office Use" on the paper form.'),
                    new FormField('hr_placement_confirmed',  'Placement confirmed',  FormField::TYPE_CHECKBOX, required: true, col: 6,
                        help: 'I confirm that the staff member\'s faculty / department / position as stated above match the HR placement and the personal-records copy has been filed with the Human Resource Unit.'),
                    new FormField('hr_comments',             'HR comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Optional remarks — induction notes, missing documents requested, etc.'),
                ],
                signatureRequired: true,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'Registrar\'s Office — Duplicate Copy',
                officeSlug: 'registrar',
                description: 'The Registrar\'s Office holds the duplicate (institutional) copy of this record. Assign the Appointment Number and sign to file the Registrar\'s copy. This completes the workflow.',
                fields: [
                    new FormField('appointment_no',     'Appointment Number',   FormField::TYPE_TEXT, required: true, col: 12, maxLength: 60,
                        help: 'The Appointment Reference Number issued by the Registrar\'s Office for this employee.'),
                    new FormField('registrar_comments', 'Registrar comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Optional remarks on filing — e.g. confirmation of identity documents sighted.'),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
