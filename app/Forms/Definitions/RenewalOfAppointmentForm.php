<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Application Form for Renewal of Appointment for Senior and Junior Staff
 * (CUGA FORM 1C) — Catholic University of Ghana, Fiapre.
 *
 * Returned to the Office of the Registrar, P.O. Box 363, Sunyani.
 *
 * Workflow (4 stages):
 *   1. applicant_details — Sections 1-5: personal particulars, education,
 *                          graduate programme, previous employment, additional
 *                          info (no signature yet — applicant signs the
 *                          declaration in stage 3 after the recommender's
 *                          assessment is added).
 *   2. recommender       — Sections 6/7 merged: Dean / Unit Head / Director /
 *                          HOD / Office head comments. The applicant picks
 *                          where the form should go via the flexible pool —
 *                          this avoids the paper-form ambiguity where two
 *                          competing assessment stages were listed.
 *   3. declaration       — Section 8: form returns to the applicant for the
 *                          formal declaration ("I hereby certify…"). Uses
 *                          POOL_CREATOR — no recipient picker, auto-routed
 *                          back to the original applicant.
 *   4. registrar         — Final filing with the Registrar.
 */
class RenewalOfAppointmentForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'renewal-of-appointment';
    }

    public function code(): string
    {
        return 'CUGA-1C';
    }

    public function title(): string
    {
        return 'Application for Renewal of Appointment (Senior & Junior Staff)';
    }

    public function description(): string
    {
        return 'Apply for renewal of your appointment. Routes through your Dean / Director / HOD / Office head for assessment, returns to you for the formal declaration, then on to the Registrar.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.renewal-of-appointment';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.renewal-of-appointment';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'applicant_details',
                label: 'Applicant — Personal & Employment Details',
                officeSlug: null,
                description: 'Complete your personal particulars, education history, graduate programme details, previous employment and any additional information you wish to provide. You will sign the formal declaration at the end of the workflow.',
                fields: [
                    // ── Top fields (precede the numbered sections) ──
                    new FormField('present_position_rank',  'Present Position / Rank',     FormField::TYPE_TEXT, required: true,  col: 6),
                    new FormField('faculty_centre_dept',    'Faculty / Centre / Dept',     FormField::TYPE_TEXT, required: true,  col: 6),

                    // ── 1. Personal Particulars ──
                    new FormField(name: 'section_1_heading', label: '1. Personal Particulars', type: FormField::TYPE_HEADING),
                    new FormField('name',                    'Name (in BLOCK LETTERS)',     FormField::TYPE_TEXT,     required: true,  col: 12),
                    new FormField('postal_address',          'Postal Address (full)',       FormField::TYPE_TEXTAREA, required: true,  col: 12),
                    new FormField('email',                   'E-mail',                       FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 150),
                    new FormField('telephone_mobile',        'Telephone / Mobile No.',       FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 60),
                    new FormField('nationality',             'Nationality',                  FormField::TYPE_TEXT,     required: true,  col: 6),
                    new FormField('home_town',               'Home Town',                    FormField::TYPE_TEXT,     required: true,  col: 6),
                    new FormField('date_of_birth',           'Date of Birth',                FormField::TYPE_DATE,     required: true,  col: 6),
                    new FormField('place_of_birth',          'Place of Birth',               FormField::TYPE_TEXT,     required: true,  col: 6),

                    // ── 2. Education ──
                    new FormField(name: 'section_2_heading', label: '2. Education', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'secondary_education',
                        label: '(a) Secondary Schools / Technical Institutes — with dates',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Use the + button to add another school. Each row holds one institution.',
                        tableColumns: [
                            ['name' => 'school',   'label' => 'Schools',       'col' => 5, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. St. James SHS'],
                            ['name' => 'dates',    'label' => 'Dates',         'col' => 3, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. 2010-2013'],
                            ['name' => 'position', 'label' => 'Position Held', 'col' => 4, 'required' => false, 'max' => 150, 'placeholder' => 'e.g. Class Prefect'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 8,
                        addRowLabel: 'Add another school',
                    ),
                    new FormField(
                        name: 'university_education',
                        label: '(b) University Qualifications',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'University awards — indicate class of degree, distinction, etc. and the date and place of award.',
                        tableColumns: [
                            ['name' => 'university', 'label' => 'University', 'col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. University of Ghana'],
                            ['name' => 'dates',      'label' => 'Dates',      'col' => 2, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. 2014-2018'],
                            ['name' => 'award',      'label' => 'Award',      'col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. BSc Mathematics'],
                            ['name' => 'class',      'label' => 'Class',      'col' => 2, 'required' => false, 'max' => 80,  'placeholder' => 'e.g. First Class'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 8,
                        addRowLabel: 'Add another university',
                    ),

                    // ── 3. Graduate Programme ──
                    new FormField(name: 'section_3_heading', label: '3. Details of Graduate Programme (MA, MSc, MBA etc.)', type: FormField::TYPE_HEADING),
                    new FormField('graduate_programme',      'Programme Details',            FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Leave blank if you have no graduate programme to report.'),

                    // ── 4. Previous Employment ──
                    new FormField(name: 'section_4_heading', label: '4. Previous Employment', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'previous_employment',
                        label: 'Employment History',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Add one row per role. Use the + button to add more rows.',
                        tableColumns: [
                            ['name' => 'institution', 'label' => 'Institution / Organisation', 'col' => 3, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. KNUST'],
                            ['name' => 'dates',       'label' => 'Dates Worked',               'col' => 2, 'required' => false, 'max' => 80,  'placeholder' => 'e.g. 2018-2022'],
                            ['name' => 'position',    'label' => 'Position Held',              'col' => 3, 'required' => false, 'max' => 150, 'placeholder' => 'e.g. Tutor'],
                            ['name' => 'reasons',     'label' => 'Reasons for Leaving',        'col' => 4, 'required' => false, 'max' => 300, 'placeholder' => 'e.g. Contract ended'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 8,
                        addRowLabel: 'Add another role',
                    ),

                    // ── 5. Additional Information ──
                    new FormField(name: 'section_5_heading', label: '5. Additional Information', type: FormField::TYPE_HEADING),
                    new FormField('additional_info',         'Any additional information you wish to give', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: false,
            ),

            new FormStage(
                slug: 'recommender',
                label: 'Assessment — Dean / Director / HOD / Office Head',
                officeSlug: null,
                description: 'Comment(s) by the Dean, Director, Head of Department, Unit Head or Supervisor on the applicant\'s suitability for renewal. The applicant chooses who should provide the assessment so that Sections 6 and 7 of the paper form are handled here in one place.',
                fields: [
                    new FormField('assessor_role',
                        label: 'Your role for this assessment',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'dean'          => 'Dean',
                            'director'      => 'Director',
                            'hod'           => 'Head of Department',
                            'unit_head'     => 'Unit / Sectional Head',
                            'supervisor'    => 'Supervisor',
                            'office_head'   => 'Office Head',
                        ],
                        help: 'Records which role you are signing in — printed next to your name on the PDF so the assessment is unambiguous.',
                    ),
                    new FormField('recommender_comments', 'Comment(s) on the applicant', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Provide your assessment of the applicant\'s suitability for renewal of appointment.'),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP_OR_OFFICE,
            ),

            new FormStage(
                slug: 'declaration',
                label: 'Applicant — Declaration',
                officeSlug: null,
                description: 'I hereby certify that to the best of my knowledge, all the details given in this form are correct. I understand that in the event of my contract being renewed with the University, any proven falsification or concealment of any material fact in respect of my application may lead to the University withdrawing the contract renewal or initiate disciplinary action and possible dismissal if employment has commenced.',
                fields: [
                    new FormField(
                        name: 'declaration_accepted',
                        label: 'I have read and accept the declaration above',
                        type: FormField::TYPE_CHECKBOX,
                        required: true,
                        col: 12,
                        help: 'You must tick this box and sign before the form can be forwarded to the Registrar.',
                    ),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_CREATOR,
            ),

            new FormStage(
                slug: 'registrar',
                label: 'Approval & Filing by Registrar',
                officeSlug: 'registrar',
                description: 'The Registrar receives and files the application.',
                fields: [
                    new FormField('registrar_comments', 'Comments (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
