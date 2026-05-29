<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Application Form for Renewal of Appointment for Senior Members (Academic)
 * (CUG FORM 1A) — Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Filed by academic senior members and returned to the Office of the
 * Registrar, P.O. Box 363, Sunyani.
 *
 * Workflow (4 stages):
 *   1. applicant_details — Sections 1-8: personal particulars, education,
 *                          graduate programme, teaching experience,
 *                          publications, service delivery, significant
 *                          contribution, additional information. (No
 *                          signature yet — the applicant signs the formal
 *                          declaration in stage 3.)
 *   2. recommender       — Section 9: comments by Dean / Director / HOD.
 *                          POOL_LEADERSHIP (no "Office" option — academic
 *                          renewals route through faculty leadership only).
 *   3. declaration       — Section 10: form returns to the applicant via
 *                          POOL_CREATOR for the formal declaration.
 *   4. registrar         — Final filing with the Office of the Registrar.
 *
 * Distinct from the Senior & Junior Staff renewal (CUGA FORM 1C) because the
 * academic form gathers significantly more research-related information
 * (publications, teaching experience, graduate qualifications).
 */
class AcademicRenewalOfAppointmentForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'renewal-of-appointment-academic';
    }

    public function code(): string
    {
        return 'CUG-1A';
    }

    public function title(): string
    {
        return 'Application for Renewal of Appointment (Senior Members – Academic)';
    }

    public function description(): string
    {
        return 'Academic senior members apply for renewal of appointment. Captures personal details, education, graduate programme, teaching experience, publications and service. Routes through your Dean / HOD / Director, returns to you for the declaration, then on to the Registrar.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.renewal-of-appointment-academic';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.renewal-of-appointment-academic';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'applicant_details',
                label: 'Applicant — Personal & Academic Details',
                officeSlug: null,
                description: 'Complete sections 1 through 8. Your signature will be captured at the end of the workflow when you sign the declaration.',
                fields: [
                    // ── Top fields (precede the numbered sections) ──
                    new FormField('current_position_rank',      'Current Position / Rank',        FormField::TYPE_TEXT, required: true,  col: 6),
                    new FormField('faculty_school_department',  'Faculty / School / Department',  FormField::TYPE_TEXT, required: true,  col: 6),

                    // ── 1. Personal Particulars ──
                    new FormField(name: 'section_1_heading', label: '1. Personal Particulars (BLOCK LETTERS)', type: FormField::TYPE_HEADING),
                    new FormField('surname',          'Surname',                           FormField::TYPE_TEXT,     required: true,  col: 12),
                    new FormField('first_names',      'First Name(s)',                     FormField::TYPE_TEXT,     required: true,  col: 6),
                    new FormField('middle_name',      'Middle Name',                       FormField::TYPE_TEXT,     required: false, col: 6),
                    new FormField('postal_address',   'Postal Address (full)',             FormField::TYPE_TEXTAREA, required: true,  col: 12),
                    new FormField('email',            'E-mail',                             FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 150),
                    new FormField('telephone_mobile', 'Telephone / Mobile No.',             FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 60),
                    new FormField('nationality_at_birth', 'Nationality at Birth (if different)', FormField::TYPE_TEXT, required: false, col: 6,
                        help: 'Leave blank if same as current nationality.'),
                    new FormField('home_town',        'Home Town',                          FormField::TYPE_TEXT,     required: true,  col: 6),
                    new FormField('date_of_birth',    'Date of Birth',                      FormField::TYPE_DATE,     required: true,  col: 4,
                        calculatesAgeTarget: 'age'),
                    new FormField('age',              'Age (years)',                        FormField::TYPE_NUMBER,   required: true,  col: 4,
                        help: 'Auto-calculated from Date of Birth. You can edit it if needed.'),
                    new FormField('place_of_birth',   'Place of Birth',                     FormField::TYPE_TEXT,     required: true,  col: 4),

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
                            ['name' => 'dates',    'label' => 'Dates',         'col' => 3, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. 2004-2007'],
                            ['name' => 'position', 'label' => 'Position Held', 'col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. School Prefect'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 8,
                        addRowLabel: 'Add another school',
                    ),
                    new FormField(
                        name: 'university_education',
                        label: '(b) University Particulars of Qualifications',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'University awards — class of degree, distinction, etc. and dates and places awarded.',
                        tableColumns: [
                            ['name' => 'university', 'label' => 'University',    'col' => 5, 'required' => false, 'max' => 250, 'placeholder' => 'e.g. KNUST'],
                            ['name' => 'dates',      'label' => 'Dates',         'col' => 3, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. 2008-2012'],
                            ['name' => 'position',   'label' => 'Position Held', 'col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. BSc Computer Science, First Class'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 8,
                        addRowLabel: 'Add another university',
                    ),

                    // ── 3. Details of Graduate Programme — Master's ──
                    new FormField(name: 'section_3_heading', label: '3. Details of Graduate Programme (MPhil, MSc (Research), PhD/DBA)', type: FormField::TYPE_HEADING,
                        help: 'Kindly indicate the type of postgraduate qualification you have by selecting one or more of the programmes specified below and indicating whether it is by course work or research.'),

                    new FormField(name: 'masters_heading', label: 'Master\'s Programme', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'masters_qualification',
                        label: 'Qualification',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'ma'       => 'MA',
                            'mphil'    => 'MPhil',
                            'msc'      => 'MSc',
                            'other'    => 'Other (specify below)',
                            'none'     => 'None / Not applicable',
                        ],
                    ),
                    new FormField('masters_qualification_other', 'If other, please specify', FormField::TYPE_TEXT, required: false, col: 8, maxLength: 200),
                    new FormField(
                        name: 'masters_format',
                        label: 'Format',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'course_work'      => 'Course Work',
                            'research_masters' => 'Research Masters',
                        ],
                    ),

                    new FormField(name: 'terminal_heading', label: 'Terminal Degree', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'terminal_qualification',
                        label: 'Qualification',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'phd'     => 'PhD',
                            'dba'     => 'DBA',
                            'other'   => 'Other (specify below)',
                            'none'    => 'None / Not applicable',
                        ],
                    ),
                    new FormField('terminal_qualification_other', 'If other, please specify', FormField::TYPE_TEXT, required: false, col: 8, maxLength: 200),
                    new FormField(
                        name: 'terminal_format',
                        label: 'Format',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'course_work'      => 'Course Work',
                            'research_masters' => 'Research Masters',
                        ],
                    ),

                    // ── 4. Teaching / Professional Experience ──
                    new FormField(name: 'section_4_heading', label: '4. Teaching / Professional Experience', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'teaching_experience',
                        label: 'Experience',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Add one row per institution / role.',
                        tableColumns: [
                            ['name' => 'institution',  'label' => 'Institution',         'col' => 3, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. KNUST'],
                            ['name' => 'dates',        'label' => 'Date(s)',             'col' => 2, 'required' => false, 'max' => 80,  'placeholder' => 'e.g. 2018-2022'],
                            ['name' => 'fpt',          'label' => 'Full / Part-Time',    'col' => 2, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. Full-time'],
                            ['name' => 'subjects',     'label' => 'Subjects taught & level (UG/PG)', 'col' => 5, 'required' => false, 'max' => 400, 'placeholder' => 'e.g. Calculus I (UG), Real Analysis (PG)'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 12,
                        addRowLabel: 'Add another teaching role',
                    ),

                    // ── 5. Publications ──
                    new FormField(name: 'section_5_heading', label: '5. List of Publications for the Last Three (3) Years', type: FormField::TYPE_HEADING,
                        help: 'All references cited must be exact and complete.'),
                    new FormField(
                        name: 'publications_published',
                        label: 'Published references',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Add one row per publication. Use the + button to add more.',
                        tableColumns: [
                            ['name' => 'reference', 'label' => 'Reference', 'col' => 12, 'required' => false, 'max' => 1000, 'placeholder' => 'Author(s), Title, Journal, Volume(Issue), Year, Pages.'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 30,
                        addRowLabel: 'Add another publication',
                    ),
                    new FormField(
                        name: 'publications_unpublished',
                        label: 'Others — unpublished papers accepted for publication in referred journals (last 3 years)',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Evidence of acceptance must be provided. Add one row per entry.',
                        tableColumns: [
                            ['name' => 'reference', 'label' => 'Reference', 'col' => 12, 'required' => false, 'max' => 1000, 'placeholder' => 'Author(s), Title, Journal (accepted), Date of acceptance.'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 30,
                        addRowLabel: 'Add another unpublished entry',
                    ),

                    // ── 6. Service Delivery ──
                    new FormField(name: 'section_6_heading', label: '6. Service Delivery (University, National, International)', type: FormField::TYPE_HEADING),
                    new FormField('service_delivery', 'Service Delivery', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    // ── 7. Significant Contribution ──
                    new FormField(name: 'section_7_heading', label: '7. Significant Contribution to the Work of the University — Last 3 Years', type: FormField::TYPE_HEADING),
                    new FormField('significant_contribution', 'Significant Contribution', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    // ── 8. Additional Information ──
                    new FormField(name: 'section_8_heading', label: '8. Any Additional Information You May Wish to Provide', type: FormField::TYPE_HEADING),
                    new FormField('additional_information', 'Additional Information', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: false,
            ),

            new FormStage(
                slug: 'recommender',
                label: 'Comments by Dean / HOD / Director',
                officeSlug: null,
                description: 'Section 9 — Comments by the Dean, Head of Department or Director on the applicant\'s suitability for renewal.',
                fields: [
                    new FormField(
                        name: 'assessor_role',
                        label: 'Your role for this assessment',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'dean'     => 'Dean',
                            'director' => 'Director',
                            'hod'      => 'Head of Department',
                        ],
                        help: 'Recorded next to your name on the PDF so the assessment is unambiguous.',
                    ),
                    new FormField('recommender_comments', 'Comments by Dean (Head of Department)', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Provide your assessment of the applicant\'s suitability for renewal of appointment.'),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP,
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
