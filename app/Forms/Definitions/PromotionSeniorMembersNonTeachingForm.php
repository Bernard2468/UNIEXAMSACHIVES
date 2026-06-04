<?php

namespace App\Forms\Definitions;

use App\Forms\BaseFormDefinition;
use App\Forms\FormField;
use App\Forms\FormStage;

/**
 * Criteria for Promotion — Senior Members (Non-Teaching)
 * Catholic University of Ghana, Fiapre-Sunyani — Human Resource Development Unit.
 *
 * Filed by a senior member (non-teaching) seeking promotion. The paper form
 * collects applicant particulars + a 14-indicator self-evaluation, requires
 * the applicant to attach an application letter, CV and certificates, then
 * routes through their Supervisor / HOD / Dean / Director for a confidential
 * report, comes back to the applicant to confirm and forward to the Human
 * Resource Development Unit, who attach the three-year summary appraisal
 * report and file the application.
 *
 * Workflow (4 stages):
 *   1. applicant     — Items 1-17 of the paper form: bio + position + duties +
 *                      publications + qualifications + 14-indicator
 *                      self-evaluation, then sign at item 17 and attach the
 *                      application letter, CV and certificates via the
 *                      standard attachments panel.
 *   2. supervisor    — Items 18 + 20-21: Supervisor / HOD / Dean / Director
 *                      provides a confidential report (textarea + Confidential
 *                      Report file attachment) and signs. POOL_LEADERSHIP_OR_OFFICE
 *                      so the applicant can pick any of the four recipient
 *                      shapes from the same picker as Renewal-of-Appointment.
 *   3. forward_to_hr — Form returns to the applicant (POOL_CREATOR) so they
 *                      can confirm the supervisor's confidential report is in
 *                      and forward the bundle to HRD. Tamper-evident: their
 *                      signature here acknowledges everything signed above.
 *   4. hrd_unit      — Item 22 ("Official Use"): Head, HRD Unit attaches the
 *                      summary annual appraisal reports for the last three (3)
 *                      academic years, records the decision and signs.
 *
 * Distinct from the Renewal-of-Appointment forms: that flow is about contract
 * renewal at current rank, whereas this is about advancement to a higher rank
 * and runs entirely through HRD (not the Registrar).
 */
class PromotionSeniorMembersNonTeachingForm extends BaseFormDefinition
{
    public function slug(): string
    {
        return 'promotion-senior-members-non-teaching';
    }

    public function code(): string
    {
        return 'CUG-PROM-SMNT';
    }

    public function title(): string
    {
        return 'Criteria for Promotion — Senior Members (Non-Teaching)';
    }

    public function description(): string
    {
        return 'Senior members (non-teaching) apply for promotion. Capture your particulars, current rank and years served, major duties, publications, service contributions, qualifications and a 14-indicator self-evaluation, then attach your application letter, current CV and certificates. The form routes through your Supervisor / HOD / Dean / Director for a confidential report, returns to you to forward, then lands with the Head of the Human Resource Development Unit who attaches your last three years of summary appraisals.';
    }

    public function templateView(): string
    {
        return 'admin.forms.templates.promotion-senior-members-non-teaching';
    }

    public function pdfView(): string
    {
        return 'admin.forms.pdf.promotion-senior-members-non-teaching';
    }

    public function pdfFooterNote(): ?string
    {
        return 'Head, HRD Unit: kindly attach summary annual appraisal reports of the applicant for the last three (3) academic years for further action.';
    }

    /**
     * Five-step wizard for the applicant stage so a 17-item paper form
     * doesn't render as one long scroll.
     */
    public function composeWizardSteps(): ?array
    {
        return [
            [
                'key'         => 'bio',
                'label'       => 'Bio & Position',
                'startAt'     => 'section_bio_heading',
                'description' => 'Items 1–9: your name, contact details, current rank, dates of appointment and the position you are seeking.',
            ],
            [
                'key'         => 'duties',
                'label'       => 'Duties & Service',
                'startAt'     => 'section_duties_heading',
                'description' => 'Items 10 + 13: your major duties and your service to the University, national or international community.',
            ],
            [
                'key'         => 'publications',
                'label'       => 'Publications',
                'startAt'     => 'section_publications_heading',
                'description' => 'Items 11 + 12: all publications since your last promotion, and those selected for external assessment.',
            ],
            [
                'key'         => 'qualifications',
                'label'       => 'Qualifications',
                'startAt'     => 'section_qualifications_heading',
                'description' => 'Items 14 + 15: graduate-programme qualifications and professional qualifications.',
            ],
            [
                'key'         => 'self_eval',
                'label'       => 'Self-Evaluation & Sign',
                'startAt'     => 'section_self_eval_heading',
                'description' => 'Item 16: score each of the 14 performance indicators out of 10. Item 17: sign the form. Attach your application letter, CV and certificates via the Attachments panel before forwarding.',
            ],
        ];
    }

    public function stages(): array
    {
        return [
            // ══════════════════════════════════════════════════════════════
            // Stage 1 — Applicant (items 1-17)
            // ══════════════════════════════════════════════════════════════
            new FormStage(
                slug: 'applicant',
                label: 'Applicant — Particulars, Duties & Self-Evaluation',
                officeSlug: null,
                description: 'Complete items 1 through 17. Sign at the end and attach your application letter, current CV and copies of your certificates via the Attachments panel before forwarding to your supervisor.',
                fields: [
                    // ── Bio & Position (items 1-9) ──
                    new FormField(name: 'section_bio_heading', label: '1–9. Applicant Particulars & Current Position', type: FormField::TYPE_HEADING),

                    new FormField('applicant_name',  'Name of Applicant (BLOCK LETTERS)', FormField::TYPE_TEXT, required: true, col: 12),
                    new FormField('date_of_birth',   'Date of Birth',                     FormField::TYPE_DATE, required: true, col: 6),
                    new FormField('telephone_mobile','Telephone / Mobile No.',            FormField::TYPE_TEXT, required: true, col: 6, maxLength: 60),
                    new FormField('email',           'E-mail',                            FormField::TYPE_TEXT, required: true, col: 12, maxLength: 150),
                    new FormField('current_rank',    'Current Rank',                      FormField::TYPE_TEXT, required: true, col: 12),
                    new FormField('first_appointment_date', 'Effective Date of First Appointment in the CUG', FormField::TYPE_DATE, required: true, col: 6),
                    new FormField('last_promotion_date',    'Effective Date of Last Promotion',                FormField::TYPE_DATE, required: false, col: 6,
                        help: 'Leave blank if you have not yet been promoted.'),
                    new FormField('years_on_current_rank', 'Number of Years Served on Current Rank', FormField::TYPE_NUMBER, required: true, col: 6,
                        rule: 'required|integer|min:0|max:60'),
                    new FormField('position_sought', 'Position Being Sought',             FormField::TYPE_TEXT, required: true, col: 6),
                    new FormField('department_unit', 'Department / Unit / Faculty / School', FormField::TYPE_TEXT, required: true, col: 12),

                    // ── Duties & Service (items 10 + 13) ──
                    new FormField(name: 'section_duties_heading', label: '10 & 13. Major Duties and Service Contributions', type: FormField::TYPE_HEADING),

                    new FormField('major_duties', '10. List all your major duties', FormField::TYPE_TEXTAREA, required: true, col: 12,
                        help: 'Use additional sheets if necessary — attach them via the Attachments panel below.'),

                    new FormField('service_contributions', '13. Service to the University Community / National / International Community', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    // ── Publications (items 11 + 12) ──
                    new FormField(name: 'section_publications_heading', label: '11 & 12. Publications since Last Appointment / Promotion', type: FormField::TYPE_HEADING),

                    new FormField('publications_list', '11. All publications with dates (reports, memos, proposals, working documents, monographs, books, etc.) since your last appointment / promotion intended to impact positively on management policy or the University Administration / promotion of knowledge in the University', FormField::TYPE_TEXTAREA, required: false, col: 12,
                        help: 'All references must be exact and complete.'),

                    new FormField('publications_external_assessment', '12. Selected publications / papers for external assessment (where applicable)', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    // ── Qualifications (items 14 + 15) ──
                    new FormField(name: 'section_qualifications_heading', label: '14 & 15. Qualifications', type: FormField::TYPE_HEADING),

                    new FormField('graduate_qualifications', '14. Qualifications — details of graduate programme (MA, MSc, MBA, MPhil, DBA, PhD, etc.)', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    new FormField('professional_qualifications', '15. Professional Qualifications (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),

                    // ── Self-Evaluation (item 16) ──
                    new FormField(name: 'section_self_eval_heading', label: '16. Self-Evaluation of Job Performance', type: FormField::TYPE_HEADING,
                        help: 'Score yourself on each of the 14 indicators below — maximum 10 points each. Your final percentage is (total score × 100) ÷ 140 and is computed automatically on the PDF.'),

                    new FormField('eval_admin_procedures',   '1. Knowledge of relevant administrative procedures', FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_work_independently', '2. Ability to work independently',                   FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_meet_deadlines',     '3. Ability to meet deadlines',                       FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_additional_work',    '4. Readiness to do additional work',                 FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_legit_instructions', '5. Ability / willingness to carry out legitimate instructions', FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_ict_new_techniques', '6. Ability to use ICT / learn new techniques',       FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_regularity',         '7. Regularity at work',                              FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_punctuality',        '8. Punctuality to work',                             FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_initiative',         '9. Initiative and resourcefulness',                  FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_supervision',       '10. Effective supervision of subordinates (where applicable)', FormField::TYPE_NUMBER, required: false, col: 12, rule: 'nullable|integer|min:0|max:10', help: 'Out of 10. Leave blank if not applicable.'),
                    new FormField('eval_peer_relations',    '11. Peer relations and approachability',              FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_appearance',        '12. Appearance / general comportment',                FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_quality_duties',    '13. Quality of duties performed',                     FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),
                    new FormField('eval_quality_reports',   '14. Quality of reports / memos / letters',            FormField::TYPE_NUMBER, required: true, col: 12, rule: 'required|integer|min:0|max:10', help: 'Out of 10.'),

                    // ── Sign / item 17 ──
                    new FormField(name: 'section_sign_heading', label: '17. Application Letter, CV & Certificates', type: FormField::TYPE_HEADING,
                        help: 'Tick the confirmation below, sign at the bottom of this stage and attach: (a) your application letter, (b) a copy of your current CV, and (c) copies of your certificates — using the Attachments panel.'),
                    new FormField(
                        name: 'applicant_attachments_confirmed',
                        label: 'I confirm that my application letter, current CV and copies of my certificates are attached.',
                        type: FormField::TYPE_CHECKBOX,
                        required: true,
                        col: 12,
                        help: 'Required — your supervisor cannot complete their confidential report without these documents.',
                    ),
                ],
                signatureRequired: true,
            ),

            // ══════════════════════════════════════════════════════════════
            // Stage 2 — Supervisor / HOD / Dean / Director / Office Head
            // ══════════════════════════════════════════════════════════════
            new FormStage(
                slug: 'supervisor',
                label: 'Confidential Report by Supervisor / HOD / Dean / Director',
                officeSlug: null,
                description: 'Items 18, 20 & 21 of the paper form. Provide a confidential report on the applicant\'s job performance using the same 14 evaluation indicators they scored themselves on. Upload your written Confidential Report as an attachment, then sign below.',
                fields: [
                    new FormField(
                        name: 'assessor_role',
                        label: 'Your role for this confidential report',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'supervisor'  => 'Supervisor',
                            'hod'         => 'Head of Department',
                            'dean'        => 'Dean',
                            'director'    => 'Director',
                            'office_head' => 'Office Head',
                        ],
                        help: 'Recorded next to your name on the PDF so the report is unambiguous.',
                    ),
                    new FormField('supervisor_department_unit', '20. Department / Unit', FormField::TYPE_TEXT, required: true, col: 12,
                        help: 'Printed alongside your name on the supervisor signature block.'),
                    new FormField(
                        name: 'confidential_report',
                        label: '18. Confidential Report on the Applicant\'s Job Performance',
                        type: FormField::TYPE_TEXTAREA,
                        required: true,
                        col: 12,
                        help: 'Comment on the applicant against the 14 performance indicators they self-scored. Attach your written confidential report as a file via the Attachments panel below.',
                    ),
                    new FormField(
                        name: 'supervisor_recommends',
                        label: 'Overall recommendation',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'recommend'           => 'I recommend the applicant for promotion to the position sought.',
                            'recommend_reserve'   => 'I recommend with reservations (see comments above).',
                            'do_not_recommend'    => 'I do not recommend the applicant for promotion at this time.',
                        ],
                    ),
                    new FormField(
                        name: 'confidential_report_attached',
                        label: 'I have attached my written Confidential Report (if applicable).',
                        type: FormField::TYPE_CHECKBOX,
                        required: false,
                        col: 12,
                        help: 'Optional — leave unticked if your typed comment above is sufficient.',
                    ),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_LEADERSHIP_OR_OFFICE,
            ),

            // ══════════════════════════════════════════════════════════════
            // Stage 3 — Applicant forwards bundle to HRD
            // ══════════════════════════════════════════════════════════════
            new FormStage(
                slug: 'forward_to_hr',
                label: 'Applicant — Forward to HRD Unit',
                officeSlug: null,
                description: 'Your supervisor has completed their confidential report. Review their comments, tick the confirmation below and sign to forward the complete bundle to the Head of the Human Resource Development Unit.',
                fields: [
                    new FormField(
                        name: 'forward_confirmed',
                        label: 'I have reviewed the supervisor\'s confidential report and forward this application — together with my letter, CV, certificates and the supervisor\'s report — to the Head of the HRD Unit for further action.',
                        type: FormField::TYPE_CHECKBOX,
                        required: true,
                        col: 12,
                        help: 'You must tick this box and re-sign before the application can be forwarded.',
                    ),
                ],
                signatureRequired: true,
                recipientPool: FormStage::POOL_CREATOR,
            ),

            // ══════════════════════════════════════════════════════════════
            // Stage 4 — Human Resource Development Unit (item 22, Official Use)
            // ══════════════════════════════════════════════════════════════
            new FormStage(
                slug: 'hrd_unit',
                label: 'Official Use — Human Resource Development Unit',
                officeSlug: 'human-resource-unit',
                description: 'Item 22 of the paper form. Attach the summary annual appraisal reports for the applicant for the last three (3) academic years, record any decision or note, and sign to file the application.',
                fields: [
                    new FormField(
                        name: 'appraisal_reports_attached',
                        label: 'I confirm that the summary annual appraisal reports for the last three (3) academic years are attached to this application.',
                        type: FormField::TYPE_CHECKBOX,
                        required: true,
                        col: 12,
                        help: 'Attach the three (3) summary appraisal reports via the Attachments panel before signing.',
                    ),
                    new FormField(
                        name: 'hrd_decision',
                        label: 'HRD recommendation / decision',
                        type: FormField::TYPE_RADIO,
                        required: true,
                        col: 12,
                        options: [
                            'forward_to_committee' => 'Forward to the Promotions / Appointments Committee for further action.',
                            'returned_to_applicant' => 'Return to applicant — application incomplete or further information required.',
                            'filed_no_action'      => 'Filed — no further action at this stage.',
                        ],
                    ),
                    new FormField('hrd_comments', 'Comments / Note (if any)', FormField::TYPE_TEXTAREA, required: false, col: 12),
                ],
                signatureRequired: true,
            ),
        ];
    }
}
