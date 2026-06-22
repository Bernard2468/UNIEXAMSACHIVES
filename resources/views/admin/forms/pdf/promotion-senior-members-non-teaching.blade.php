{{--
    Criteria for Promotion — Senior Members (Non-Teaching)
    Catholic University of Ghana, Fiapre — Human Resource Development Unit.

    Stage data keys (must match PromotionSeniorMembersNonTeachingForm):
      applicant:     applicant_name, date_of_birth, telephone_mobile, email,
                     current_rank, first_appointment_date, last_promotion_date,
                     years_on_current_rank, position_sought, department_unit,
                     major_duties, service_contributions,
                     publications_list, publications_external_assessment,
                     graduate_qualifications, professional_qualifications,
                     eval_admin_procedures … eval_quality_reports (14 numbers),
                     applicant_attachments_confirmed
      supervisor:    assessor_role, supervisor_department_unit, confidential_report,
                     supervisor_recommends, confidential_report_attached
      forward_to_hr: forward_confirmed
      hrd_unit:      appraisal_reports_attached, hrd_decision, hrd_comments
--}}
@php
    $applicantData   = $submission->sectionData('applicant');
    $supervisorData  = $submission->sectionData('supervisor');
    $forwardData     = $submission->sectionData('forward_to_hr');
    $hrdData         = $submission->sectionData('hrd_unit');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
    $applicantSig  = $signaturesByStage->get('applicant')?->last();
    $supervisorSig = $signaturesByStage->get('supervisor')?->last();
    $forwardSig    = $signaturesByStage->get('forward_to_hr')?->last();
    $hrdSig        = $signaturesByStage->get('hrd_unit')?->last();

    $logoFsPath = public_path('img/cug_logo_update.jpeg');

    $fmtDate = function ($value) {
        if (!$value) return '';
        try { return \Illuminate\Support\Carbon::parse($value)->format('d M Y'); }
        catch (\Throwable $e) { return $value; }
    };

    $sigFsPath = function ($sig) {
        if (!$sig || !$sig->signature_image_path) return null;
        $path = storage_path('app/public/' . ltrim($sig->signature_image_path, '/'));
        return file_exists($path) ? $path : null;
    };

    $signerName = function ($sig) {
        if (!$sig) return '';
        $u = $sig->user;
        return $u ? trim(($u->first_name ?? '') . ' ' . ($u->last_name ?? '')) : '';
    };

    // ── Self-evaluation: 14 indicators in paper-form order ──
    $evalRows = [
        ['key' => 'eval_admin_procedures',   'label' => 'Knowledge of relevant administrative procedures'],
        ['key' => 'eval_work_independently', 'label' => 'Ability to work independently'],
        ['key' => 'eval_meet_deadlines',     'label' => 'Ability to meet deadlines'],
        ['key' => 'eval_additional_work',    'label' => 'Readiness to do additional work'],
        ['key' => 'eval_legit_instructions', 'label' => 'Ability / willingness to carry out legitimate instructions'],
        ['key' => 'eval_ict_new_techniques', 'label' => 'Ability to use ICT / learn new techniques'],
        ['key' => 'eval_regularity',         'label' => 'Regularity at work'],
        ['key' => 'eval_punctuality',        'label' => 'Punctuality to work'],
        ['key' => 'eval_initiative',         'label' => 'Initiative and resourcefulness'],
        ['key' => 'eval_supervision',        'label' => 'Effective supervision of subordinates (where applicable)'],
        ['key' => 'eval_peer_relations',     'label' => 'Peer relations and approachability'],
        ['key' => 'eval_appearance',         'label' => 'Appearance / general comportment'],
        ['key' => 'eval_quality_duties',     'label' => 'Quality of duties performed'],
        ['key' => 'eval_quality_reports',    'label' => 'Quality of reports / memos / letters'],
    ];

    $evalTotal = 0;
    $evalFilled = 0;
    foreach ($evalRows as $row) {
        $v = $applicantData[$row['key']] ?? null;
        if (is_numeric($v)) {
            $evalTotal += (int) $v;
            $evalFilled++;
        }
    }
    $evalPercentage = $evalFilled > 0 ? round(($evalTotal / 140) * 100, 1) : null;

    // Assessor role label (printed next to supervisor signature).
    $assessorRoleLabels = [
        'supervisor'  => 'Supervisor',
        'hod'         => 'Head of Department',
        'dean'        => 'Dean',
        'director'    => 'Director',
        'office_head' => 'Office Head',
    ];
    $assessorRoleLabel = $assessorRoleLabels[$supervisorData['assessor_role'] ?? ''] ?? 'Supervisor';

    $supervisorRecommendLabels = [
        'recommend'         => 'Recommended for promotion',
        'recommend_reserve' => 'Recommended with reservations',
        'do_not_recommend'  => 'Not recommended at this time',
    ];
    $supervisorRecommend = $supervisorRecommendLabels[$supervisorData['supervisor_recommends'] ?? ''] ?? '';

    $hrdDecisionLabels = [
        'forward_to_committee'  => 'Forwarded to the Promotions / Appointments Committee',
        'returned_to_applicant' => 'Returned to applicant — incomplete / further information required',
        'filed_no_action'       => 'Filed — no further action at this stage',
    ];
    $hrdDecision = $hrdDecisionLabels[$hrdData['hrd_decision'] ?? ''] ?? '';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $submission->form_code }} - {{ $submission->reference }}</title>
    <style>
        @page { margin: 14mm 14mm 14mm 14mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #111827; font-size: 11px; line-height: 1.5; }
        h1, h2, h3, h4, h5 { margin: 0; padding: 0; }

        /* ── Confidential / form-code strip ── */
        .pf-strip { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .pf-strip td { padding: 0; font-size: 10px; }
        .pf-strip__left  { font-weight: 700; letter-spacing: 0.1em; }
        .pf-strip__right { text-align: right; font-style: italic; color: #374151; }

        /* ── Header ── */
        .pf-head { text-align: center; margin-bottom: 12px; }
        .pf-head h1 { font-size: 16px; font-weight: 800; letter-spacing: 1px; }
        .pf-head__logo { display: block; margin: 4px auto; height: 50px; width: auto; }
        .pf-head .pf-rubric { font-style: italic; font-size: 10.5px; color: #374151; margin-top: 2px; }
        .pf-head h2 { font-size: 13.5px; font-weight: 800; margin-top: 8px; letter-spacing: 0.3px; line-height: 1.35; }
        .pf-head .pf-instructions { margin-top: 8px; padding-bottom: 8px; border-bottom: 2px solid #111827; font-style: italic; font-size: 10.5px; color: #1f2937; }

        /* ── Reference strip ── */
        .pf-meta { margin: 8px 0 12px; padding: 5px 10px; background: #f3f4f6; border-radius: 4px; font-size: 9.5px; color: #374151; }
        .pf-meta span { margin-right: 14px; }
        .pf-meta strong { color: #111827; }

        /* ── Numbered sections ── */
        .pf-section { margin: 10px 0; }
        .pf-section__title-row { width: auto; border-collapse: collapse; margin-bottom: 6px; }
        .pf-section__title-row td { vertical-align: baseline; padding: 0; }
        .pf-section__num-cell { width: 26px; padding-right: 6px !important; font-weight: 700; font-size: 11.5px; color: #111827; }
        .pf-section__title-cell { font-weight: 700; font-size: 11.5px; letter-spacing: 0.3px; text-transform: uppercase; color: #111827; }
        .pf-section--bordered { padding-top: 10px; border-top: 1px solid #d1d5db; }

        /* ── Field rows ── */
        .pf-item { width: 100%; margin: 0 0 6px; border-collapse: collapse; }
        .pf-item td { vertical-align: top; padding: 0; }
        .pf-item__label-cell { width: 1%; white-space: nowrap; padding-right: 6px !important; vertical-align: middle; }
        .pf-item__label { font-weight: 600; color: #1f2937; }
        .pf-item__value-cell { border-bottom: 1px dotted #6b7280; padding-bottom: 1px !important; vertical-align: bottom; }
        .pf-item__value { font-weight: 500; color: #0c0c0c; }
        .pf-item__block { display: block; margin-top: 3px; min-height: 28px; padding: 5px 8px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 3px; font-weight: 500; color: #0c0c0c; white-space: pre-wrap; }

        .pf-pair { width: 100%; border-collapse: collapse; margin: 0 0 6px; }
        .pf-pair td { vertical-align: middle; padding: 0; }
        .pf-pair__left  { width: 50%; padding-right: 10px !important; }
        .pf-pair__right { width: 50%; }

        /* ── Self-evaluation table ── */
        .pf-eval { width: 100%; margin: 6px 0 8px; border-collapse: collapse; font-size: 10.5px; }
        .pf-eval th, .pf-eval td { border: 1px solid #6b7280; padding: 4px 6px; vertical-align: middle; }
        .pf-eval th { font-weight: 700; text-align: left; background: #f3f4f6; }
        .pf-eval__idx { width: 24px; text-align: center; font-weight: 700; }
        .pf-eval__score { width: 110px; text-align: center; font-weight: 700; }
        .pf-eval__pct { width: 90px; text-align: center; }
        .pf-eval__totalrow td { background: #f9fafb; font-weight: 700; }
        .pf-eval__totalrow .pf-eval__score, .pf-eval__totalrow .pf-eval__pct { color: #111827; font-size: 11px; }
        .pf-eval__notice {
            margin: 4px 0 6px;
            padding: 6px 10px;
            background: #fef3c7;
            border-left: 4px solid #ca8a04;
            border-radius: 0 4px 4px 0;
            font-size: 10.5px;
            color: #713f12;
            font-weight: 600;
        }
        .pf-eval__formula {
            margin-top: 4px;
            padding: 4px 8px;
            background: #f3f4f6;
            border-radius: 3px;
            font-size: 10px;
            color: #374151;
            text-align: right;
        }
        .pf-eval__formula strong { color: #111827; }

        /* ── Statement / declaration blocks ── */
        .pf-statement {
            margin: 6px 0 10px;
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 3px solid #6b7280;
            border-radius: 0 4px 4px 0;
            font-size: 10.5px;
            color: #1f2937;
            line-height: 1.6;
        }
        .pf-statement--official { background: #fef2f2; border-left-color: #b91c1c; }
        .pf-statement--declaration { background: #fefce8; border-left-color: #ca8a04; }
        .pf-statement strong { color: #111827; }

        /* ── Signature card ── */
        .pf-sigcard {
            margin: 8px 0 4px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #fff;
            page-break-inside: avoid;
        }
        .pf-sigcard__head { background: #f9fafb; padding: 5px 12px; font-size: 9.5px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #374151; border-bottom: 1px solid #e5e7eb; }
        .pf-sigcard__body { padding: 10px 12px; text-align: center; min-height: 54px; }
        .pf-sigcard__img { max-height: 48px; max-width: 70%; }
        .pf-sigcard__empty { color: #9ca3af; font-style: italic; font-size: 10px; padding: 16px 0; }
        .pf-sigcard__meta { padding: 6px 12px 7px; border-top: 1px dashed #9ca3af; font-size: 10px; color: #4b5563; line-height: 1.5; }
        .pf-sigcard__meta strong { color: #111827; font-weight: 700; }
        .pf-sigcard__date { color: #111827; font-weight: 600; border-bottom: 1px dotted #9ca3af; padding-bottom: 1px; }
        .pf-sigcard__badge { display: inline-block; padding: 1px 7px; border-radius: 8px; font-size: 8.5px; font-weight: bold; margin-left: 5px; vertical-align: middle; }
        .pf-sigcard__badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .pf-sigcard__badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* ── Footer ── */
        .pf-doc-footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #d1d5db; font-size: 9px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>

    {{-- ===== CONFIDENTIAL / form-code strip ===== --}}
    <table class="pf-strip"><tr>
        <td class="pf-strip__left">CONFIDENTIAL</td>
        <td class="pf-strip__right">{{ $submission->form_code }}</td>
    </tr></table>

    {{-- ===== Header ===== --}}
    <div class="pf-head">
        <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE</h1>
        @if(file_exists($logoFsPath))
            <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
        @endif
        <div class="pf-rubric">P. O. Box 363, Sunyani — [ HUMAN RESOURCE DEVELOPMENT UNIT ]</div>
        <h2>CRITERIA FOR PROMOTION — SENIOR MEMBERS (NON-TEACHING)</h2>
        <div class="pf-instructions">
            Please fill the following and attach all necessary documents as requested.
        </div>
    </div>

    {{-- Reference strip --}}
    <div class="pf-meta">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    @include('admin.forms.pdf._auth-seal', ['submission' => $submission])

    {{-- =========================================================
         1-9. APPLICANT PARTICULARS & POSITION
         ========================================================= --}}
    <div class="pf-section">
        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">1. Name of Applicant (Block Letters):</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['applicant_name'] ?? '' }}</span></td>
        </tr></table>

        <table class="pf-pair">
            <tr>
                <td class="pf-pair__left">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">2. Date of Birth:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $fmtDate($applicantData['date_of_birth'] ?? null) }}</span></td>
                    </tr></table>
                </td>
                <td class="pf-pair__right">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Telephone / Mobile No:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['telephone_mobile'] ?? '' }}</span></td>
                    </tr></table>
                </td>
            </tr>
        </table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">3. E-mail:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['email'] ?? '' }}</span></td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">4. Current Rank:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['current_rank'] ?? '' }}</span></td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">5. Effective Date of First Appointment in the CUG:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $fmtDate($applicantData['first_appointment_date'] ?? null) }}</span></td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">6. Effective Date of Last Promotion:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $fmtDate($applicantData['last_promotion_date'] ?? null) }}</span></td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">7. Number of years served on current rank:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['years_on_current_rank'] ?? '' }}</span></td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">8. Position Being Sought:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['position_sought'] ?? '' }}</span></td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">9. Department / Unit / Faculty / School:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['department_unit'] ?? '' }}</span></td>
        </tr></table>
    </div>

    {{-- =========================================================
         10. MAJOR DUTIES
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">10.</td>
            <td class="pf-section__title-cell">List all your major duties</td>
        </tr></table>
        <div class="pf-item__block" style="min-height: 70px;">{{ $applicantData['major_duties'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         11. PUBLICATIONS
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">11.</td>
            <td class="pf-section__title-cell">Publications since last appointment / promotion</td>
        </tr></table>
        <p style="margin: 0 0 4px; font-size: 10px; color: #4b5563; font-style: italic;">
            (Reports, memos, proposals, working documents, monographs, books etc., intended to impact positively on
            management policy on any functional area of the University Administration / promotion of knowledge in the University.)
        </p>
        <div class="pf-item__block" style="min-height: 60px;">{{ $applicantData['publications_list'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         12. SELECTED PUBLICATIONS FOR EXTERNAL ASSESSMENT
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">12.</td>
            <td class="pf-section__title-cell">Selected publications / papers for external assessment (where applicable)</td>
        </tr></table>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['publications_external_assessment'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         13. SERVICE TO UNIVERSITY / NATIONAL / INTERNATIONAL COMMUNITY
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">13.</td>
            <td class="pf-section__title-cell">Service to the University Community / National / International Community</td>
        </tr></table>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['service_contributions'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         14. QUALIFICATIONS — GRADUATE PROGRAMME
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">14.</td>
            <td class="pf-section__title-cell">Qualifications: details of graduate programme (MA, MSc, MBA, MPhil, DBA, PhD, etc.)</td>
        </tr></table>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['graduate_qualifications'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         15. PROFESSIONAL QUALIFICATIONS
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">15.</td>
            <td class="pf-section__title-cell">Professional Qualifications (if any)</td>
        </tr></table>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['professional_qualifications'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         16. SELF-EVALUATION TABLE
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">16.</td>
            <td class="pf-section__title-cell">Self-Evaluation of Job Performance by the Applicant</td>
        </tr></table>
        <div class="pf-eval__notice">
            ⚠ Score: MAXIMUM 10 POINTS for each indicator. Final percentage = (Total Score ÷ 140) × 100.
        </div>

        <table class="pf-eval">
            <thead>
                <tr>
                    <th class="pf-eval__idx">#</th>
                    <th>Description</th>
                    <th class="pf-eval__score">Score<br>(10 points each)</th>
                    <th class="pf-eval__pct">Percentage (%)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evalRows as $i => $row)
                    @php $v = $applicantData[$row['key']] ?? null; @endphp
                    <tr>
                        <td class="pf-eval__idx">{{ $i + 1 }}.</td>
                        <td>{{ $row['label'] }}</td>
                        <td class="pf-eval__score">{{ is_numeric($v) ? (int) $v : '' }}</td>
                        <td class="pf-eval__pct">&mdash;</td>
                    </tr>
                @endforeach
                <tr class="pf-eval__totalrow">
                    <td colspan="2" style="text-align: right;">Total Score</td>
                    <td class="pf-eval__score">{{ $evalFilled > 0 ? $evalTotal . ' / 140' : '— / 140' }}</td>
                    <td class="pf-eval__pct">{{ $evalPercentage !== null ? $evalPercentage . '%' : '' }}</td>
                </tr>
            </tbody>
        </table>
        <div class="pf-eval__formula">
            Formula: <strong>(Total Score ÷ 140) × 100</strong>
            @if($evalFilled > 0)
                &nbsp;=&nbsp; <strong>({{ $evalTotal }} ÷ 140) × 100 = {{ $evalPercentage }}%</strong>
            @endif
        </div>
    </div>

    {{-- =========================================================
         17. APPLICATION LETTER / CV / CERTIFICATES + APPLICANT SIGNATURE
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">17.</td>
            <td class="pf-section__title-cell">Application Letter, Curriculum Vitae &amp; Certificates</td>
        </tr></table>

        <div class="pf-statement">
            The applicant has written an application letter and attached a copy of their current Curriculum Vitae and
            Certificates (attached via the system's Attachments panel — see Audit Trail / Attachments at the end of this document).
            @if(!empty($applicantData['applicant_attachments_confirmed']))
                <div style="margin-top: 6px; color: #15803d; font-weight: 600;">
                    ☑ Confirmed by applicant: application letter, CV and certificates are attached.
                </div>
            @else
                <div style="margin-top: 6px; color: #9ca3af; font-style: italic;">
                    ☐ Applicant has not yet confirmed attachments.
                </div>
            @endif
        </div>

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Staff</div>
            <div class="pf-sigcard__body">
                @if($applicantSig)
                    @php $img = $sigFsPath($applicantSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="Applicant signature">@endif
                @else
                    <div class="pf-sigcard__empty">— awaiting applicant signature —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($applicantSig)
                    @php $check = $applicantSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($applicantSig) }}</strong>
                    on <span class="pf-sigcard__date">{{ $applicantSig->signed_at?->format('d M Y, H:i') }}</span>
                    <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                        {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                    </span>
                @else
                    Not yet signed.
                @endif
            </div>
        </div>
    </div>

    {{-- =========================================================
         18-21. SUPERVISOR / HOD CONFIDENTIAL REPORT + SIGNATURE
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">18.</td>
            <td class="pf-section__title-cell">Confidential Report by {{ $assessorRoleLabel }}</td>
        </tr></table>
        <p style="margin: 0 0 6px; font-size: 9.5px; color: #6b7280; font-style: italic;">
            (Item 19 of the paper form — onward submission to the Registrar / HRD — is fulfilled by the digital workflow:
            the application is forwarded automatically through the chain.)
        </p>

        <div class="pf-statement">
            <strong>Confidential comments on the applicant's job performance:</strong>
            <div class="pf-item__block" style="margin-top: 4px; min-height: 70px; background: #fff; border-color: #d1d5db;">{{ $supervisorData['confidential_report'] ?? '' }}</div>

            @if(!empty($supervisorRecommend))
                <div style="margin-top: 6px;">
                    <strong>Overall recommendation:</strong> {{ $supervisorRecommend }}.
                </div>
            @endif

            @if(!empty($supervisorData['confidential_report_attached']))
                <div style="margin-top: 4px; color: #15803d; font-weight: 600;">
                    ☑ A separate written Confidential Report has been attached.
                </div>
            @endif
        </div>

        <table class="pf-pair" style="margin-top: 6px;">
            <tr>
                <td class="pf-pair__left">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">20. Name of Supervisor:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $signerName($supervisorSig) }}</span></td>
                    </tr></table>
                </td>
                <td class="pf-pair__right">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Department / Unit:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $supervisorData['supervisor_department_unit'] ?? '' }}</span></td>
                    </tr></table>
                </td>
            </tr>
        </table>

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">21. Signature of {{ $assessorRoleLabel }}</div>
            <div class="pf-sigcard__body">
                @if($supervisorSig)
                    @php $img = $sigFsPath($supervisorSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="Supervisor signature">@endif
                @else
                    <div class="pf-sigcard__empty">— awaiting supervisor's confidential report &amp; signature —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($supervisorSig)
                    @php $check = $supervisorSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($supervisorSig) }}</strong>
                    ({{ $assessorRoleLabel }})
                    on <span class="pf-sigcard__date">{{ $supervisorSig->signed_at?->format('d M Y, H:i') }}</span>
                    <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                        {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                    </span>
                @else
                    Not yet signed.
                @endif
            </div>
        </div>
    </div>

    {{-- =========================================================
         APPLICANT FORWARDS BUNDLE TO HRD
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">→</td>
            <td class="pf-section__title-cell">Applicant — Forward to HRD Unit</td>
        </tr></table>

        <div class="pf-statement pf-statement--declaration">
            On receiving the supervisor's confidential report, I forward this application — together with my application
            letter, current CV, copies of my certificates and the supervisor's report — to the Head of the Human Resource
            Development Unit for further action.
            @if(!empty($forwardData['forward_confirmed']))
                <div style="margin-top: 6px; color: #15803d; font-weight: 600;">
                    ☑ Confirmed and forwarded by the applicant.
                </div>
            @else
                <div style="margin-top: 6px; color: #9ca3af; font-style: italic;">
                    ☐ Awaiting applicant's confirmation to forward.
                </div>
            @endif
        </div>

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Applicant (on forwarding to HRD)</div>
            <div class="pf-sigcard__body">
                @if($forwardSig)
                    @php $img = $sigFsPath($forwardSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="Applicant forward signature">@endif
                @else
                    <div class="pf-sigcard__empty">— awaiting applicant's signature on forwarding —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($forwardSig)
                    @php $check = $forwardSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($forwardSig) }}</strong>
                    on <span class="pf-sigcard__date">{{ $forwardSig->signed_at?->format('d M Y, H:i') }}</span>
                    <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                        {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                    </span>
                @else
                    Not yet signed.
                @endif
            </div>
        </div>
    </div>

    {{-- =========================================================
         22. OFFICIAL USE — HRD UNIT
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">22.</td>
            <td class="pf-section__title-cell">Official Use — Human Resource Development Unit</td>
        </tr></table>

        <div class="pf-statement pf-statement--official">
            <em>Head, HRD Unit: kindly attach summary annual appraisal reports of the applicant for the last three (3)
            academic years for further action.</em>
            @if(!empty($hrdData['appraisal_reports_attached']))
                <div style="margin-top: 6px; color: #15803d; font-weight: 600;">
                    ☑ Three-year summary appraisal reports attached by HRD.
                </div>
            @else
                <div style="margin-top: 6px; color: #9ca3af; font-style: italic;">
                    ☐ HRD has not yet attached the three-year summary appraisal reports.
                </div>
            @endif
        </div>

        @if(!empty($hrdDecision))
            <table class="pf-item"><tr>
                <td class="pf-item__label-cell"><span class="pf-item__label">HRD Decision:</span></td>
                <td class="pf-item__value-cell"><span class="pf-item__value">{{ $hrdDecision }}</span></td>
            </tr></table>
        @endif

        @if(!empty($hrdData['hrd_comments']))
            <div class="pf-item__block" style="min-height: 28px;">
                <strong>HRD Comments:</strong>
                <div style="margin-top: 4px;">{{ $hrdData['hrd_comments'] }}</div>
            </div>
        @endif

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Head, HRD Unit</div>
            <div class="pf-sigcard__body">
                @if($hrdSig)
                    @php $img = $sigFsPath($hrdSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="HRD Unit signature">@endif
                @else
                    <div class="pf-sigcard__empty">— awaiting Head, HRD Unit signature —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($hrdSig)
                    @php $check = $hrdSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($hrdSig) }}</strong>
                    on <span class="pf-sigcard__date">{{ $hrdSig->signed_at?->format('d M Y, H:i') }}</span>
                    <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                        {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                    </span>
                @else
                    Not yet signed.
                @endif
            </div>
        </div>
    </div>

    {{-- ===== Footer ===== --}}
    <div class="pf-doc-footer">
        Generated {{ now()->format('d M Y, H:i') }} · Reference {{ $submission->reference }}
        @if($submission->signatures->isNotEmpty())
            · Tamper-evident audit chain ({{ $submission->signatures->count() }} signatures)
        @endif
    </div>
</body>
</html>
