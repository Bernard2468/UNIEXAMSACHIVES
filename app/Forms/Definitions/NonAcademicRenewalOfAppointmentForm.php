<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Application Form for Renewal of Appointment for Senior Members
 * (Non-Academic / Professionals) — CUGA FORM 1B.
 * Catholic University of Ghana, Fiapre-Sunyani.
 *
 * Filed by non-academic senior members and returned to the Office of the
 * Registrar, P.O. Box 363, Sunyani.
 *
 * Workflow (4 stages):
 *   1. applicant_details — Sections 1–7: personal particulars, education,
 *                          previous employment, working experience, projects /
 *                          publications, areas of professional interest,
 *                          general (associations, extra-curricular, additional
 *                          info). No signature yet — applicant signs the
 *                          declaration in stage 3.
 *   2. recommender       — Sections 8 + 9 of the paper form are combined here.
 *                          The paper form lists Section 8 (Unit Head / Supervisor)
 *                          AND Section 9 (HOD, if Section 8 was by a Sectional /
 *                          Unit Head) — both are "recommendation by a senior",
 *                          so they collapse to one digital stage with a flexible
 *                          recipient pool (POOL_LEADERSHIP_OR_OFFICE) — applicant
 *                          picks Dean / HOD / Director / Office head (Unit Head,
 *                          Supervisor, Sectional Head all fall under "Office").
 *                          Same pattern as CUGA-1C.
 *   3. declaration       — Section 10: form returns to the applicant for the
 *                          formal declaration ("I hereby certify…"). Uses
 *                          POOL_CREATOR.
 *   4. registrar         — Final filing with the Office of the Registrar.
 *
 * Sits alongside CUG-1A (academic senior members) and CUGA-1C (senior &
 * junior staff). Distinct because non-academic senior members fill different
 * professional sections: a "Working Experience" table covering managerial /
 * leadership posts, projects + publications, and special-interest areas.
 */
class NonAcademicRenewalOfAppointmentForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'renewal-of-appointment-non-academic';
    }

    public function code(): string
    {
        return 'CUGA-1B';
    }

    public function title(): string
    {
        return 'Application for Renewal of Appointment (Senior Members — Non-Academic / Professionals)';
    }

    public function description(): string
    {
        return 'Non-academic / professional senior members apply for renewal of appointment. Captures personal details, education, previous employment, working / leadership experience, projects + publications, and areas of professional interest. Routes through your Unit Head / Supervisor / HOD / Director / Office head for recommendation, returns to you for the declaration, then on to the Registrar.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.renewal-of-appointment-non-academic';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.renewal-of-appointment-non-academic';
    }

    public function stages(): array
    {
        return [
            new FormStage(
                slug: 'applicant_details',
                label: 'Applicant — Personal & Professional Details',
                officeSlug: null,
                description: 'Complete sections 1 through 7. Your signature will be captured at the end of the workflow when you sign the declaration.',
                fields: [
                    // ── Top fields (precede the numbered sections) ──
                    new FormField('current_position_rank',     'Current Position / Rank',        FormField::TYPE_TEXT, required: true, col: 6),
                    new FormField('faculty_school_department', 'Faculty / School / Department',  FormField::TYPE_TEXT, required: true, col: 6),

                    // ══════════════════════════════════════════════════════
                    // 1. Personal Particulars
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_1_heading', label: '1. Personal Particulars (BLOCK LETTERS)', type: FormField::TYPE_HEADING),
                    new FormField('surname',           'Surname',                             FormField::TYPE_TEXT,     required: true,  col: 12),
                    new FormField('first_names',       'First Name(s)',                       FormField::TYPE_TEXT,     required: true,  col: 6),
                    new FormField('middle_names',      'Middle Name(s)',                      FormField::TYPE_TEXT,     required: false, col: 6),
                    new FormField('postal_address',    'Postal Address (in full)',            FormField::TYPE_TEXTAREA, required: true,  col: 12),
                    new FormField('email',             'E-mail',                              FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 150),
                    new FormField('telephone_mobile',  'Telephone / Mobile No.',              FormField::TYPE_TEXT,     required: true,  col: 6, maxLength: 60),
                    new FormField('nationality_at_birth', 'Nationality at Birth (if different)', FormField::TYPE_TEXT, required: false, col: 6,
                        help: 'Leave blank if same as current nationality.'),
                    new FormField('home_town',         'Home Town',                           FormField::TYPE_TEXT,     required: true,  col: 6),
                    new FormField('date_of_birth',     'Date of Birth',                       FormField::TYPE_DATE,     required: true,  col: 4,
                        calculatesAgeTarget: 'age'),
                    new FormField('age',               'Age (years)',                         FormField::TYPE_NUMBER,   required: true,  col: 4,
                        help: 'Auto-calculated from Date of Birth. You can edit it if needed.'),
                    new FormField('place_of_birth',    'Place of Birth',                      FormField::TYPE_TEXT,     required: true,  col: 4),

                    // ══════════════════════════════════════════════════════
                    // 2. Education
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_2_heading', label: '2. Education', type: FormField::TYPE_HEADING),

                    new FormField(
                        name: 'secondary_education',
                        label: '(a) Secondary Schools / Technical Institutes — with dates',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'List the schools you attended. Add one row per institution.',
                        tableColumns: [
                            ['name' => 'school',   'label' => 'Schools',       'col' => 5, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. St. James SHS'],
                            ['name' => 'dates',    'label' => 'Dates',         'col' => 3, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. 2004-2007'],
                            ['name' => 'position', 'label' => 'Position Held', 'col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. School Prefect'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 8,
                        addRowLabel: 'Add another school',
                    ),

                    new FormField(name: 'sub_university_heading', label: '(b) University Particulars of Qualifications', type: FormField::TYPE_HEADING,
                        help: 'University awards — indicating class of degree, distinction, etc. and date and place of award.'),

                    // ── Undergraduate Programme ──
                    new FormField(name: 'sub_undergrad_heading', label: 'i. Details of Undergraduate Programme', type: FormField::TYPE_HEADING,
                        help: 'e.g. Bachelor of Arts, BSc. in Computer Science, etc.'),
                    new FormField(
                        name: 'undergraduate_programmes',
                        label: 'Undergraduate Programmes',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Add one row per programme.',
                        tableColumns: [
                            ['name' => 'programme',    'label' => 'Programme',           'col' => 4, 'required' => false, 'max' => 250, 'placeholder' => 'e.g. BSc Accounting'],
                            ['name' => 'dates',        'label' => 'Date(s)',             'col' => 2, 'required' => false, 'max' => 60,  'placeholder' => 'e.g. 2014-2018'],
                            ['name' => 'class',        'label' => 'Class',               'col' => 2, 'required' => false, 'max' => 80,  'placeholder' => 'e.g. First Class'],
                            ['name' => 'institution',  'label' => 'Awarding Institution','col' => 4, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. KNUST'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 6,
                        addRowLabel: 'Add another undergraduate programme',
                    ),

                    // ── Graduate Programme (Master's + Terminal) ──
                    new FormField(name: 'sub_graduate_heading', label: 'ii. Details of Graduate Programme (PhD, DBA, MPhil, MBA, MA, MSc. (Research), etc.)', type: FormField::TYPE_HEADING,
                        help: 'Kindly indicate the type of postgraduate qualification you have by selecting one or more of the programmes below and indicating whether it is by course work or research.'),

                    new FormField(name: 'sub_masters_heading', label: 'Master\'s Programme', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'masters_qualification',
                        label: 'Qualification',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'mphil' => 'MPhil',
                            'ma'    => 'MA',
                            'msc'   => 'MSc',
                            'mba'   => 'MBA',
                            'other' => 'Other (specify below)',
                            'none'  => 'None / Not applicable',
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

                    new FormField(name: 'sub_terminal_heading', label: 'Terminal Degree', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'terminal_qualification',
                        label: 'Qualification',
                        type: FormField::TYPE_RADIO,
                        required: false,
                        col: 12,
                        options: [
                            'phd'   => 'PhD',
                            'dba'   => 'DBA',
                            'other' => 'Other (specify below)',
                            'none'  => 'None / Not applicable',
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
                            'course_work' => 'Course Work',
                            'by_research' => 'By Research',
                        ],
                    ),

                    new FormField('research_area', 'Research Area', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Use additional sheets if necessary — attach them via the Attachments panel below.'),

                    // ══════════════════════════════════════════════════════
                    // 3. Previous Employment
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_3_heading', label: '3. Previous Employment', type: FormField::TYPE_HEADING),
                    new FormField(
                        name: 'previous_employment',
                        label: 'Employment History',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Add one row per role.',
                        tableColumns: [
                            ['name' => 'institution', 'label' => 'Institution / Organisation', 'col' => 3, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. Bank of Ghana'],
                            ['name' => 'dates',       'label' => 'Dates Worked',               'col' => 2, 'required' => false, 'max' => 80,  'placeholder' => 'e.g. 2018-2022'],
                            ['name' => 'position',    'label' => 'Position Held',              'col' => 3, 'required' => false, 'max' => 150, 'placeholder' => 'e.g. Finance Officer'],
                            ['name' => 'reasons',     'label' => 'Reasons for Leaving',        'col' => 4, 'required' => false, 'max' => 300, 'placeholder' => 'e.g. Contract ended'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 10,
                        addRowLabel: 'Add another role',
                    ),
                    new FormField('previous_employment_extra', 'Additional information about your previous employment', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'Optional — use this space for any additional context the table above couldn\'t capture.'),

                    // ══════════════════════════════════════════════════════
                    // 4. Working Experience (managerial / leadership)
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_4_heading', label: '4. Working Experience', type: FormField::TYPE_HEADING,
                        help: 'These may include managerial and administrative leadership positions held. You may attach additional sheets if your history is long.'),
                    new FormField(
                        name: 'working_experience',
                        label: 'Working Experience',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'List each role; add additional rows if needed.',
                        tableColumns: [
                            ['name' => 'institution',           'label' => 'Institution / Organisation',  'col' => 3, 'required' => false, 'max' => 200, 'placeholder' => 'e.g. CUG, Fiapre'],
                            ['name' => 'dates',                 'label' => 'Date(s)',                     'col' => 2, 'required' => false, 'max' => 80,  'placeholder' => 'e.g. 2019-2024'],
                            ['name' => 'effective_appointment', 'label' => 'Effective Date(s) of Appointment', 'col' => 3, 'required' => false, 'max' => 120, 'placeholder' => 'e.g. 01 Jan 2020'],
                            ['name' => 'responsibilities',      'label' => 'Responsibilities',            'col' => 4, 'required' => false, 'max' => 400, 'placeholder' => 'Key duties and leadership responsibilities'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 15,
                        addRowLabel: 'Add another role',
                    ),

                    // ══════════════════════════════════════════════════════
                    // 5. Major Administrative / Professional Projects + Publications
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_5_heading', label: '5. Details of Major Administrative / Professional Projects Undertaken — including Reports, Memoranda and Publications', type: FormField::TYPE_HEADING,
                        help: 'All references cited must be exact and complete.'),
                    new FormField(
                        name: 'projects_publications',
                        label: 'Projects, Reports, Memoranda and Publications',
                        type: FormField::TYPE_TABLE,
                        required: false,
                        col: 12,
                        help: 'Add one row per reference.',
                        tableColumns: [
                            ['name' => 'reference', 'label' => 'Reference', 'col' => 12, 'required' => false, 'max' => 1000, 'placeholder' => 'Author(s), Title, Publisher / Recipient, Date, Pages.'],
                        ],
                        minTableRows: 1,
                        maxTableRows: 25,
                        addRowLabel: 'Add another entry',
                    ),

                    // ══════════════════════════════════════════════════════
                    // 6. Brief Statement — Areas of Special Interest
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_6_heading', label: '6. A Brief Statement of Areas of Special Administrative / Professional Interest', type: FormField::TYPE_HEADING),
                    new FormField('areas_of_interest', 'Areas of Special Interest', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    // ══════════════════════════════════════════════════════
                    // 7. General
                    // ══════════════════════════════════════════════════════
                    new FormField(name: 'section_7_heading', label: '7. General', type: FormField::TYPE_HEADING),
                    new FormField('professional_associations', 'i. Name(s) of professional associations of which the candidate is a member', FormField::TYPE_TEXTAREA, required: false, col: 12),
                    new FormField('extracurricular_activities', 'ii. Extra-curricular activities undertaken in the last 3 years', FormField::TYPE_TEXTAREA, required: false, col: 12),
                    new FormField('additional_information', 'iii. Any additional information you may wish to provide', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: false,
            ),

            // ══════════════════════════════════════════════════════════════
            // Stage 2 — Recommender (Sections 8 + 9 combined)
            // ══════════════════════════════════════════════════════════════
            new FormStage(
                slug: 'recommender',
                label: 'Recommendation — Unit Head / Supervisor / HOD / Director / Office Head',
                officeSlug: null,
                description: 'Sections 8 and 9 of the paper form are handled here. The applicant chooses where the recommendation should go — to a Dean, HOD, Director, or an Office head (e.g. Unit Head / Sectional Head / Supervisor). The named recommender writes their comment and signs.',
                fields: [
                    new FormField(
                        name: 'assessor_role',
                        label: 'Your role for this recommendation',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'dean'           => 'Dean',
                            'director'       => 'Director',
                            'hod'            => 'Head of Department',
                            'unit_head'      => 'Unit Head',
                            'sectional_head' => 'Sectional Head',
                            'supervisor'     => 'Supervisor',
                            'office_head'    => 'Office Head',
                        ],
                        help: 'Records which role you are signing in — printed next to your name on the PDF so the recommendation is unambiguous.',
                    ),
                    new FormField('recommender_comments', 'Recommendation / Comment(s) on the applicant', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Provide your assessment of the applicant\'s suitability for renewal of appointment.'),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP_OR_OFFICE,
            ),

            // ══════════════════════════════════════════════════════════════
            // Stage 3 — Applicant Declaration
            // ══════════════════════════════════════════════════════════════
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

            // ══════════════════════════════════════════════════════════════
            // Stage 4 — Registrar
            // ══════════════════════════════════════════════════════════════
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
