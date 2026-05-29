{{--
    Application for Renewal of Appointment (CUGA FORM 1C) —
    paper-form-faithful PDF.

    Stage data keys (must match RenewalOfAppointmentForm):
      applicant_details: present_position_rank, faculty_centre_dept,
                         name, postal_address, email, telephone_mobile,
                         nationality, home_town, date_of_birth, place_of_birth,
                         secondary_education (pipe-delimited rows),
                         university_education (pipe-delimited rows),
                         graduate_programme,
                         previous_employment (pipe-delimited rows),
                         additional_info
      recommender:       assessor_role, recommender_comments
      declaration:       declaration_accepted (checkbox)
      registrar:         registrar_comments
--}}
@php
    $applicantData    = $submission->sectionData('applicant_details');
    $recommenderData  = $submission->sectionData('recommender');
    $declarationData  = $submission->sectionData('declaration');
    $registrarData    = $submission->sectionData('registrar');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
    // applicant_details stage is unsigned (signatureRequired=false on the
    // definition). The applicant's signature lives on the declaration stage.
    $recommenderSig = $signaturesByStage->get('recommender')?->last();
    $declarationSig = $signaturesByStage->get('declaration')?->last();
    $registrarSig   = $signaturesByStage->get('registrar')?->last();

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

    // Read rows from the TYPE_TABLE fields. Each row is an associative array
    // keyed by column name (see RenewalOfAppointmentForm). Falls back to []
    // when nothing has been entered.
    $rowsOf = function (mixed $raw): array {
        return is_array($raw) ? array_values(array_filter($raw, 'is_array')) : [];
    };
    $secondaryRows  = $rowsOf($applicantData['secondary_education']  ?? null);
    $universityRows = $rowsOf($applicantData['university_education'] ?? null);
    $employmentRows = $rowsOf($applicantData['previous_employment']  ?? null);

    // Roman numerals for the table-row indices on the paper form.
    $romans = ['i.', 'ii.', 'iii.', 'iv.', 'v.', 'vi.', 'vii.', 'viii.'];

    // Label for the assessor's role chosen on stage 2.
    $assessorRoleLabels = [
        'dean'        => 'Dean',
        'director'    => 'Director',
        'hod'         => 'Head of Department',
        'unit_head'   => 'Unit / Sectional Head',
        'supervisor'  => 'Supervisor',
        'office_head' => 'Office Head',
    ];
    $assessorRoleLabel = $assessorRoleLabels[$recommenderData['assessor_role'] ?? ''] ?? 'Dean / Director / HOD / Unit Head';
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

        /* ── Confidential / form-code strip (matches paper form) ── */
        .pf-strip { width: 100%; border-collapse: collapse; margin-bottom: 6px; }
        .pf-strip td { padding: 0; font-size: 10px; }
        .pf-strip__left  { font-weight: 700; letter-spacing: 0.1em; }
        .pf-strip__right { text-align: right; font-style: italic; color: #374151; }

        /* ── Header (centred, matches paper form) ── */
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

        /* ── Top top-of-form fields ──
           Real HTML table — label cell on the left, value cell on the right.
           Guarantees they sit on the same line and the dotted underline fills
           the cell width regardless of label length. */
        .pf-top-fields { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .pf-top-fields td { padding: 3px 0; vertical-align: middle; }
        .pf-top-fields__label-cell { width: 34%; padding-right: 8px !important; }
        .pf-top-fields__label { font-weight: 600; color: #1f2937; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.04em; }
        .pf-top-fields__value-cell { border-bottom: 1px dotted #6b7280; padding-bottom: 2px !important; }
        .pf-top-fields__value { font-weight: 500; color: #0c0c0c; }

        /* ── Numbered sections ──
           Section title rendered as a 2-cell table so the number and the
           title text share the same baseline (inline-block min-width caused
           a small vertical drift in dompdf). */
        .pf-section { margin: 12px 0; }
        .pf-section__title-row { width: auto; border-collapse: collapse; margin-bottom: 6px; }
        .pf-section__title-row td { vertical-align: baseline; padding: 0; }
        .pf-section__num-cell { width: 24px; padding-right: 6px !important; font-weight: 700; font-size: 11.5px; color: #111827; }
        .pf-section__title-cell { font-weight: 700; font-size: 11.5px; letter-spacing: 0.3px; text-transform: uppercase; color: #111827; }
        .pf-section--bordered { padding-top: 10px; border-top: 1px solid #d1d5db; }

        /* ── Field rows inside a section ── */
        .pf-item { width: 100%; margin: 0 0 6px; border-collapse: collapse; }
        .pf-item td { vertical-align: top; padding: 0; }
        .pf-item__label { font-weight: 600; color: #1f2937; }
        .pf-item__value {
            display: inline-block;
            border-bottom: 1px dotted #6b7280;
            padding: 0 4px 1px;
            min-height: 13px;
            font-weight: 500;
            color: #0c0c0c;
        }
        .pf-item__value--grow { display: block; margin-top: 3px; min-height: 28px; padding: 5px 8px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 3px; font-weight: 500; }

        /* ── Education / employment tables ── */
        .pf-table { width: 100%; margin: 6px 0; border-collapse: collapse; font-size: 10.5px; }
        .pf-table th, .pf-table td { border-bottom: 1px dotted #9ca3af; padding: 4px 6px; vertical-align: top; }
        .pf-table th { font-weight: 700; text-align: left; background: #f9fafb; border-bottom: 1px solid #6b7280; }
        .pf-table__index { width: 26px; font-weight: 700; }
        .pf-table__empty td { color: #9ca3af; font-style: italic; }

        /* ── Confirmation statement / declaration block ── */
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
        .pf-statement--declaration { background: #fefce8; border-left-color: #ca8a04; }
        .pf-statement strong { color: #111827; }

        /* ── Signature card ── */
        .pf-sigcard {
            margin: 8px 0 4px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #fff;
        }
        .pf-sigcard__head {
            background: #f9fafb;
            padding: 5px 12px;
            font-size: 9.5px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #374151;
            border-bottom: 1px solid #e5e7eb;
        }
        .pf-sigcard__body { padding: 10px 12px; text-align: center; min-height: 54px; }
        .pf-sigcard__img { max-height: 48px; max-width: 70%; }
        .pf-sigcard__empty { color: #9ca3af; font-style: italic; font-size: 10px; padding: 16px 0; }
        .pf-sigcard__meta { padding: 6px 12px 7px; border-top: 1px dashed #9ca3af; font-size: 10px; color: #4b5563; line-height: 1.5; }
        .pf-sigcard__meta strong { color: #111827; font-weight: 700; }
        .pf-sigcard__date { color: #111827; font-weight: 600; border-bottom: 1px dotted #9ca3af; padding-bottom: 1px; }
        .pf-sigcard__badge { display: inline-block; padding: 1px 7px; border-radius: 8px; font-size: 8.5px; font-weight: bold; margin-left: 5px; vertical-align: middle; }
        .pf-sigcard__badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .pf-sigcard__badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* Page-break helpers */
        .pf-pb { page-break-before: always; }

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

    {{-- ===== Header (matches CUGA FORM 1C) ===== --}}
    <div class="pf-head">
        <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE</h1>
        @if(file_exists($logoFsPath))
            <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
        @endif
        <div class="pf-rubric">[Office of the Registrar, P.O. Box 363, Sunyani – B/A]</div>
        <h2>APPLICATION FORM FOR RENEWAL OF APPOINTMENT FOR<br>SENIOR AND JUNIOR STAFF</h2>
        <div class="pf-instructions">
            This form is to be completed (two copies) and returned to the Registrar, Catholic University of Ghana, P.O. Box 363, Sunyani.
        </div>
    </div>

    {{-- Reference strip --}}
    <div class="pf-meta">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    {{-- ===== Top fields (precede the numbered sections) =====
         Real two-column rows: label cell on the left, value cell with its
         dotted underline on the right — both on a single line. --}}
    <table class="pf-top-fields">
        <tr>
            <td class="pf-top-fields__label-cell"><span class="pf-top-fields__label">Present Position / Rank:</span></td>
            <td class="pf-top-fields__value-cell"><span class="pf-top-fields__value">{{ $applicantData['present_position_rank'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td class="pf-top-fields__label-cell"><span class="pf-top-fields__label">Faculty / Centre / Dept:</span></td>
            <td class="pf-top-fields__value-cell"><span class="pf-top-fields__value">{{ $applicantData['faculty_centre_dept'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- =========================================================
         1. PERSONAL PARTICULARS
         ========================================================= --}}
    <div class="pf-section">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">1.</td>
            <td class="pf-section__title-cell">Personal Particulars</td>
        </tr></table>

        <table class="pf-item"><tr><td>
            <span class="pf-item__label">Name:</span>
            <span class="pf-item__value" style="width: 84%;">{{ $applicantData['name'] ?? '' }}</span>
            <div style="font-size: 9px; font-style: italic; color: #6b7280; margin-left: 38px;">(BLOCK LETTERS)</div>
        </td></tr></table>

        <table class="pf-item"><tr><td>
            <span class="pf-item__label">Postal Address (full):</span>
            <div class="pf-item__value--grow" style="min-height: 32px;">{{ $applicantData['postal_address'] ?? '' }}</div>
        </td></tr></table>

        <table class="pf-item"><tr>
            <td style="width: 50%; padding-right: 10px;">
                <span class="pf-item__label">E-mail:</span>
                <span class="pf-item__value" style="width: 70%;">{{ $applicantData['email'] ?? '' }}</span>
            </td>
            <td style="width: 50%;">
                <span class="pf-item__label">Telephone / Mobile No:</span>
                <span class="pf-item__value" style="width: 56%;">{{ $applicantData['telephone_mobile'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td style="width: 50%; padding-right: 10px;">
                <span class="pf-item__label">Nationality:</span>
                <span class="pf-item__value" style="width: 72%;">{{ $applicantData['nationality'] ?? '' }}</span>
            </td>
            <td style="width: 50%;">
                <span class="pf-item__label">Home Town:</span>
                <span class="pf-item__value" style="width: 72%;">{{ $applicantData['home_town'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td style="width: 50%; padding-right: 10px;">
                <span class="pf-item__label">Date of Birth:</span>
                <span class="pf-item__value" style="width: 68%;">{{ $fmtDate($applicantData['date_of_birth'] ?? null) }}</span>
            </td>
            <td style="width: 50%;">
                <span class="pf-item__label">Place of Birth:</span>
                <span class="pf-item__value" style="width: 68%;">{{ $applicantData['place_of_birth'] ?? '' }}</span>
            </td>
        </tr></table>
    </div>

    {{-- =========================================================
         2. EDUCATION
         ========================================================= --}}
    <div class="pf-section">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">2.</td>
            <td class="pf-section__title-cell">Education</td>
        </tr></table>

        {{-- (a) Secondary Schools / Technical Institutes --}}
        <div style="font-weight: 600; margin: 4px 0 2px;">(a) Where educated — Secondary Schools / Technical Institutes with dates</div>
        <table class="pf-table">
            <thead><tr>
                <th class="pf-table__index">&nbsp;</th>
                <th style="width: 50%;">Schools</th>
                <th style="width: 25%;">Dates</th>
                <th style="width: 25%;">Position Held</th>
            </tr></thead>
            <tbody>
                @forelse($secondaryRows as $idx => $row)
                    <tr>
                        <td class="pf-table__index">{{ $romans[$idx] ?? ($idx + 1) . '.' }}</td>
                        <td>{{ $row['school']   ?? '' }}</td>
                        <td>{{ $row['dates']    ?? '' }}</td>
                        <td>{{ $row['position'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="4">— no entries —</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- (b) University --}}
        <div style="font-weight: 600; margin: 8px 0 2px;">(b) University Particulars of qualifications <span style="font-style: italic; font-weight: 400; color: #6b7280;">(University awards, indicating class of degree, distinction, etc. and date and place of award)</span></div>
        <table class="pf-table">
            <thead><tr>
                <th class="pf-table__index">&nbsp;</th>
                <th style="width: 40%;">University</th>
                <th style="width: 20%;">Dates</th>
                <th style="width: 25%;">Award</th>
                <th style="width: 15%;">Class</th>
            </tr></thead>
            <tbody>
                @forelse($universityRows as $idx => $row)
                    <tr>
                        <td class="pf-table__index">{{ $romans[$idx] ?? ($idx + 1) . '.' }}</td>
                        <td>{{ $row['university'] ?? '' }}</td>
                        <td>{{ $row['dates']      ?? '' }}</td>
                        <td>{{ $row['award']      ?? '' }}</td>
                        <td>{{ $row['class']      ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="5">— no entries —</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- =========================================================
         3. DETAILS OF GRADUATE PROGRAMME
         ========================================================= --}}
    <div class="pf-section">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">3.</td>
            <td class="pf-section__title-cell">Details of Graduate Programme <span style="font-weight: 400; font-style: italic; text-transform: none; letter-spacing: 0; color: #6b7280;">(MA, MSc, MBA etc.)</span></td>
        </tr></table>
        <div class="pf-item__value--grow" style="min-height: 60px;">{{ $applicantData['graduate_programme'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         4. PREVIOUS EMPLOYMENT
         ========================================================= --}}
    <div class="pf-section">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">4.</td>
            <td class="pf-section__title-cell">Previous Employment</td>
        </tr></table>
        <table class="pf-table">
            <thead><tr>
                <th class="pf-table__index">&nbsp;</th>
                <th style="width: 30%;">Institution / Organisation</th>
                <th style="width: 18%;">Dates Worked</th>
                <th style="width: 22%;">Position Held</th>
                <th style="width: 30%;">Reasons for Leaving</th>
            </tr></thead>
            <tbody>
                @forelse($employmentRows as $idx => $row)
                    <tr>
                        <td class="pf-table__index">{{ $romans[$idx] ?? ($idx + 1) . '.' }}</td>
                        <td>{{ $row['institution'] ?? '' }}</td>
                        <td>{{ $row['dates']       ?? '' }}</td>
                        <td>{{ $row['position']    ?? '' }}</td>
                        <td>{{ $row['reasons']     ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="5">— no entries —</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- =========================================================
         5. ADDITIONAL INFORMATION
         ========================================================= --}}
    <div class="pf-section">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">5.</td>
            <td class="pf-section__title-cell">Additional Information</td>
        </tr></table>
        <p style="margin: 0 0 4px; font-style: italic; color: #6b7280; font-size: 10px;">The space below may be used for any additional information you wish to give.</p>
        <div class="pf-item__value--grow" style="min-height: 80px;">{{ $applicantData['additional_info'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         6. ASSESSMENT BY DEAN / DIRECTOR / HOD / UNIT HEAD
         (Merges sections 6 and 7 of the paper form — the applicant
          picks who provides the assessment, so the role is recorded
          on the PDF for clarity.)
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">6.</td>
            <td class="pf-section__title-cell">
                Assessment by {{ $assessorRoleLabel }}
                <span style="font-weight: 400; font-style: italic; text-transform: none; letter-spacing: 0; color: #6b7280;">(replaces Sections 6/7 of the paper form — the applicant chose this role)</span>
            </td>
        </tr></table>

        <div class="pf-statement">
            <strong>Comment(s) by {{ $assessorRoleLabel }}:</strong>
            <div class="pf-item__value--grow" style="margin-top: 4px; min-height: 60px; background: #fff; border-color: #d1d5db;">{{ $recommenderData['recommender_comments'] ?? '' }}</div>
        </div>

        {{-- Recommender signature card --}}
        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of {{ $assessorRoleLabel }}</div>
            <div class="pf-sigcard__body">
                @if($recommenderSig)
                    @php $img = $sigFsPath($recommenderSig); @endphp
                    @if($img)
                        <img class="pf-sigcard__img" src="{{ $img }}" alt="Recommender signature">
                    @endif
                @else
                    <div class="pf-sigcard__empty">— awaiting assessment & signature —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($recommenderSig)
                    @php $check = $recommenderSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($recommenderSig) }}</strong>
                    ({{ $assessorRoleLabel }})
                    on <span class="pf-sigcard__date">{{ $recommenderSig->signed_at?->format('d M Y, H:i') }}</span>
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
         7. DECLARATION (Section 8 on the paper form — renumbered
            because we merged the paper's 6 and 7 into our section 6).
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">7.</td>
            <td class="pf-section__title-cell">Declaration by Applicant</td>
        </tr></table>

        <div class="pf-statement pf-statement--declaration">
            I hereby certify that to the best of my knowledge, all the details given in this form are correct.
            I understand that in the event of my contract being renewed with the University, any proven
            falsification or concealment of any material fact in respect of my application may lead to the
            University withdrawing the contract renewal or initiate disciplinary action and possible dismissal
            if employment has commenced.
            @if(!empty($declarationData['declaration_accepted']))
                <div style="margin-top: 6px; color: #15803d; font-weight: 600;">
                    ☑ Declaration accepted by the applicant.
                </div>
            @else
                <div style="margin-top: 6px; color: #9ca3af; font-style: italic;">
                    ☐ Declaration not yet accepted.
                </div>
            @endif
        </div>

        {{-- Applicant declaration signature card --}}
        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Applicant</div>
            <div class="pf-sigcard__body">
                @if($declarationSig)
                    @php $img = $sigFsPath($declarationSig); @endphp
                    @if($img)
                        <img class="pf-sigcard__img" src="{{ $img }}" alt="Applicant signature">
                    @endif
                @else
                    <div class="pf-sigcard__empty">— awaiting applicant's declaration & signature —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($declarationSig)
                    @php $check = $declarationSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($declarationSig) }}</strong>
                    on <span class="pf-sigcard__date">{{ $declarationSig->signed_at?->format('d M Y, H:i') }}</span>
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
         8. APPROVAL & FILING BY REGISTRAR
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">8.</td>
            <td class="pf-section__title-cell">Approval & Filing by Registrar</td>
        </tr></table>

        @if(!empty($registrarData['registrar_comments']))
            <table class="pf-item"><tr><td>
                <span class="pf-item__label">Registrar's Comments:</span>
                <div class="pf-item__value--grow" style="min-height: 28px;">{{ $registrarData['registrar_comments'] }}</div>
            </td></tr></table>
        @endif

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Registrar</div>
            <div class="pf-sigcard__body">
                @if($registrarSig)
                    @php $img = $sigFsPath($registrarSig); @endphp
                    @if($img)
                        <img class="pf-sigcard__img" src="{{ $img }}" alt="Registrar signature">
                    @endif
                @else
                    <div class="pf-sigcard__empty">— awaiting Registrar's signature —</div>
                @endif
            </div>
            <div class="pf-sigcard__meta">
                @if($registrarSig)
                    @php $check = $registrarSig->verifyChain(); @endphp
                    Signed by <strong>{{ $signerName($registrarSig) }}</strong>
                    on <span class="pf-sigcard__date">{{ $registrarSig->signed_at?->format('d M Y, H:i') }}</span>
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
