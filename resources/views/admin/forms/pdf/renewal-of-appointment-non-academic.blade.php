{{--
    Application for Renewal of Appointment for Senior Members
    (Non-Academic / Professionals) — CUGA FORM 1B — paper-faithful PDF.

    Stage data keys (must match NonAcademicRenewalOfAppointmentForm):
      applicant_details: current_position_rank, faculty_school_department,
                         surname, first_names, middle_names, postal_address,
                         email, telephone_mobile, nationality_at_birth,
                         home_town, date_of_birth, age, place_of_birth,
                         secondary_education (TABLE rows),
                         undergraduate_programmes (TABLE rows),
                         masters_qualification, masters_qualification_other, masters_format,
                         terminal_qualification, terminal_qualification_other, terminal_format,
                         research_area,
                         previous_employment (TABLE rows), previous_employment_extra,
                         working_experience (TABLE rows),
                         projects_publications (TABLE rows),
                         areas_of_interest,
                         professional_associations, extracurricular_activities, additional_information
      recommender:       assessor_role, recommender_comments
      declaration:       declaration_accepted (checkbox)
      registrar:         registrar_comments
--}}
@php
    $applicantData   = $submission->sectionData('applicant_details');
    $recommenderData = $submission->sectionData('recommender');
    $declarationData = $submission->sectionData('declaration');
    $registrarData   = $submission->sectionData('registrar');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
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

    $rowsOf = function (mixed $raw): array {
        if (!is_array($raw)) return [];
        return array_values(array_filter($raw, function ($row) {
            if (!is_array($row)) return false;
            foreach ($row as $cell) {
                if (!is_null($cell) && trim((string) $cell) !== '') return true;
            }
            return false;
        }));
    };

    $secondaryRows      = $rowsOf($applicantData['secondary_education']      ?? null);
    $undergradRows      = $rowsOf($applicantData['undergraduate_programmes'] ?? null);
    $previousRows       = $rowsOf($applicantData['previous_employment']      ?? null);
    $workingRows        = $rowsOf($applicantData['working_experience']       ?? null);
    $projectsRows       = $rowsOf($applicantData['projects_publications']    ?? null);

    $romans = ['i.', 'ii.', 'iii.', 'iv.', 'v.', 'vi.', 'vii.', 'viii.', 'ix.', 'x.', 'xi.', 'xii.', 'xiii.', 'xiv.', 'xv.', 'xvi.', 'xvii.', 'xviii.', 'xix.', 'xx.', 'xxi.', 'xxii.', 'xxiii.', 'xxiv.', 'xxv.', 'xxvi.', 'xxvii.', 'xxviii.', 'xxix.', 'xxx.'];

    // Qualification labels for the embedded radio displays.
    $mastersQualLabels  = ['mphil' => 'MPhil', 'ma' => 'MA', 'msc' => 'MSc', 'mba' => 'MBA', 'other' => 'Other', 'none' => 'None'];
    $terminalQualLabels = ['phd' => 'PhD', 'dba' => 'DBA', 'other' => 'Other', 'none' => 'None'];
    $mastersFmtLabels   = ['course_work' => 'Course Work', 'research_masters' => 'Research Masters'];
    $terminalFmtLabels  = ['course_work' => 'Course Work', 'by_research'      => 'By Research'];

    // Recommender role label — printed next to the signature so the assessment is unambiguous.
    $assessorRoleLabels = [
        'dean'           => 'Dean',
        'director'       => 'Director',
        'hod'            => 'Head of Department',
        'unit_head'      => 'Unit Head',
        'sectional_head' => 'Sectional Head',
        'supervisor'     => 'Supervisor',
        'office_head'   => 'Office Head',
    ];
    $assessorRoleLabel  = $assessorRoleLabels[$recommenderData['assessor_role'] ?? ''] ?? 'Unit Head / Supervisor';
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

        /* ── Top fields (label sits flush against value) ── */
        .pf-top-fields { width: 100%; border-collapse: collapse; margin-bottom: 4px; }
        .pf-top-fields td { padding: 3px 0; vertical-align: middle; }
        .pf-top-fields__label-cell { width: 1%; white-space: nowrap; padding-right: 8px !important; }
        .pf-top-fields__label { font-weight: 600; color: #1f2937; text-transform: uppercase; font-size: 10.5px; letter-spacing: 0.04em; }
        .pf-top-fields__value-cell { border-bottom: 1px dotted #6b7280; padding-bottom: 2px !important; }
        .pf-top-fields__value { font-weight: 500; color: #0c0c0c; }

        /* ── Numbered sections ── */
        .pf-section { margin: 12px 0; }
        .pf-section__title-row { width: auto; border-collapse: collapse; margin-bottom: 6px; }
        .pf-section__title-row td { vertical-align: baseline; padding: 0; }
        .pf-section__num-cell { width: 26px; padding-right: 6px !important; font-weight: 700; font-size: 11.5px; color: #111827; }
        .pf-section__title-cell { font-weight: 700; font-size: 11.5px; letter-spacing: 0.3px; text-transform: uppercase; color: #111827; }
        .pf-section--bordered { padding-top: 10px; border-top: 1px solid #d1d5db; }

        .pf-subsection__title { font-weight: 700; font-size: 11px; color: #1f2937; margin: 8px 0 4px; }

        /* ── Field rows ── */
        .pf-item { width: 100%; margin: 0 0 6px; border-collapse: collapse; }
        .pf-item td { vertical-align: top; padding: 0; }
        .pf-item__label-cell { width: 1%; white-space: nowrap; padding-right: 6px !important; vertical-align: middle; }
        .pf-item__label { font-weight: 600; color: #1f2937; }
        .pf-item__value-cell { border-bottom: 1px dotted #6b7280; padding-bottom: 1px !important; vertical-align: bottom; }
        .pf-item__value { font-weight: 500; color: #0c0c0c; }
        .pf-item__block { display: block; margin-top: 3px; min-height: 28px; padding: 5px 8px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 3px; font-weight: 500; color: #0c0c0c; white-space: pre-wrap; }

        /* Side-by-side paired fields */
        .pf-pair { width: 100%; border-collapse: collapse; margin: 0 0 6px; }
        .pf-pair td { vertical-align: middle; padding: 0; }
        .pf-pair__left  { width: 50%; padding-right: 10px !important; }
        .pf-pair__right { width: 50%; }

        /* ── Data tables ── */
        .pf-table { width: 100%; margin: 6px 0 8px; border-collapse: collapse; font-size: 10.5px; }
        .pf-table th, .pf-table td { border-bottom: 1px dotted #9ca3af; padding: 4px 6px; vertical-align: top; }
        .pf-table th { font-weight: 700; text-align: left; background: #f9fafb; border-bottom: 1px solid #6b7280; }
        .pf-table__index { width: 28px; font-weight: 700; }
        .pf-table__empty td { color: #9ca3af; font-style: italic; }

        /* ── Section 2(b)ii — Master's | Terminal Degree side-by-side ── */
        .pf-grad { width: 100%; border-collapse: collapse; margin: 6px 0; }
        .pf-grad > tbody > tr > td { vertical-align: top; padding: 8px 12px; border: 1px solid #d1d5db; width: 50%; }
        .pf-grad__heading { font-weight: 700; font-size: 11.5px; letter-spacing: 0.05em; text-align: center; padding: 6px 0 !important; background: #f9fafb !important; border-bottom: 1px solid #d1d5db !important; }
        .pf-grad__line { margin: 4px 0; }
        .pf-grad__line-label { display: inline-block; min-width: 18px; font-weight: 700; }
        .pf-grad__inline-value { display: inline-block; min-width: 60%; border-bottom: 1px dotted #6b7280; padding: 0 4px 1px; font-weight: 500; color: #0c0c0c; }
        .pf-grad__checkbox { font-size: 11px; margin-right: 12px; color: #1f2937; }
        .pf-grad__checkbox--on { color: #111827; font-weight: 700; }
        .pf-grad__checkbox--off { color: #9ca3af; }

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
        <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE-SUNYANI</h1>
        @if(file_exists($logoFsPath))
            <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
        @endif
        <div class="pf-rubric">[Office of the Registrar, P.O. Box 363, Sunyani – B/A]</div>
        <h2>APPLICATION FORM FOR RENEWAL OF APPOINTMENT FOR<br>SENIOR MEMBERS (NON-ACADEMIC / PROFESSIONALS)</h2>
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

    {{-- ===== Top fields ===== --}}
    <table class="pf-top-fields">
        <tr>
            <td class="pf-top-fields__label-cell"><span class="pf-top-fields__label">Current Position / Rank:</span></td>
            <td class="pf-top-fields__value-cell"><span class="pf-top-fields__value">{{ $applicantData['current_position_rank'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td class="pf-top-fields__label-cell"><span class="pf-top-fields__label">Faculty / School / Department:</span></td>
            <td class="pf-top-fields__value-cell"><span class="pf-top-fields__value">{{ $applicantData['faculty_school_department'] ?? '' }}</span></td>
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

        {{-- Surname --}}
        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">Surname:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['surname'] ?? '' }}</span></td>
        </tr></table>

        {{-- First Names + Middle Names --}}
        <table class="pf-pair">
            <tr>
                <td class="pf-pair__left">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">First Name(s):</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['first_names'] ?? '' }}</span></td>
                    </tr></table>
                </td>
                <td class="pf-pair__right">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Middle Name(s):</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['middle_names'] ?? '' }}</span></td>
                    </tr></table>
                </td>
            </tr>
        </table>
        <div style="font-size: 9px; font-style: italic; color: #6b7280; text-align: center; margin: -4px 0 6px;">(BLOCK LETTERS)</div>

        {{-- Postal Address --}}
        <table class="pf-item"><tr>
            <td>
                <span class="pf-item__label">Postal Address (in full):</span>
                <div class="pf-item__block" style="min-height: 32px;">{{ $applicantData['postal_address'] ?? '' }}</div>
            </td>
        </tr></table>

        {{-- Email + Telephone --}}
        <table class="pf-pair">
            <tr>
                <td class="pf-pair__left">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">E-mail:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['email'] ?? '' }}</span></td>
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

        {{-- Nationality at Birth + Home Town --}}
        <table class="pf-pair">
            <tr>
                <td class="pf-pair__left">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Nationality at Birth <em style="font-weight: 400; color: #6b7280;">(if different)</em>:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['nationality_at_birth'] ?? '' }}</span></td>
                    </tr></table>
                </td>
                <td class="pf-pair__right">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Home Town:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['home_town'] ?? '' }}</span></td>
                    </tr></table>
                </td>
            </tr>
        </table>

        {{-- Date of Birth + Age + Place of Birth --}}
        <table class="pf-pair">
            <tr>
                <td class="pf-pair__left">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Date of Birth:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $fmtDate($applicantData['date_of_birth'] ?? null) }}</span></td>
                    </tr></table>
                </td>
                <td class="pf-pair__right">
                    <table class="pf-item"><tr>
                        <td class="pf-item__label-cell"><span class="pf-item__label">Age:</span></td>
                        <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['age'] ?? '' }} {{ !empty($applicantData['age']) ? 'years' : '' }}</span></td>
                    </tr></table>
                </td>
            </tr>
        </table>
        <table class="pf-item"><tr>
            <td class="pf-item__label-cell"><span class="pf-item__label">Place of Birth:</span></td>
            <td class="pf-item__value-cell"><span class="pf-item__value">{{ $applicantData['place_of_birth'] ?? '' }}</span></td>
        </tr></table>
    </div>

    {{-- =========================================================
         2. EDUCATION
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">2.</td>
            <td class="pf-section__title-cell">Education</td>
        </tr></table>

        {{-- (a) Secondary schools --}}
        <div class="pf-subsection__title">(a) Where Educated — Secondary Schools / Technical Institutes with Dates</div>
        <table class="pf-table">
            <thead>
                <tr>
                    <th class="pf-table__index">#</th>
                    <th>Schools</th>
                    <th style="width: 24%;">Dates</th>
                    <th style="width: 28%;">Position Held</th>
                </tr>
            </thead>
            <tbody>
                @forelse($secondaryRows as $i => $row)
                    <tr>
                        <td class="pf-table__index">{{ $romans[$i] ?? ($i + 1) . '.' }}</td>
                        <td>{{ $row['school'] ?? '' }}</td>
                        <td>{{ $row['dates'] ?? '' }}</td>
                        <td>{{ $row['position'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="4">— no secondary institutions listed —</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- (b) University --}}
        <div class="pf-subsection__title">(b) University — Particulars of Qualifications</div>
        <p style="margin: 0 0 6px; font-size: 10.5px; color: #4b5563; font-style: italic;">
            University awards, indicating class of degree, distinction, etc. and date and place of award.
        </p>

        {{-- (b) i. Undergraduate Programme --}}
        <div class="pf-subsection__title" style="margin-top: 6px;">i. Details of Undergraduate Programme</div>
        <table class="pf-table">
            <thead>
                <tr>
                    <th class="pf-table__index">#</th>
                    <th>Programme</th>
                    <th style="width: 16%;">Date(s)</th>
                    <th style="width: 16%;">Class</th>
                    <th style="width: 28%;">Awarding Institution</th>
                </tr>
            </thead>
            <tbody>
                @forelse($undergradRows as $i => $row)
                    <tr>
                        <td class="pf-table__index">{{ ($i + 1) . '.' }}</td>
                        <td>{{ $row['programme'] ?? '' }}</td>
                        <td>{{ $row['dates'] ?? '' }}</td>
                        <td>{{ $row['class'] ?? '' }}</td>
                        <td>{{ $row['institution'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="5">— no undergraduate programmes listed —</td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- (b) ii. Graduate Programme (Master's | Terminal) --}}
        <div class="pf-subsection__title" style="margin-top: 8px;">ii. Details of Graduate Programme <em style="font-weight: 400; color: #6b7280;">(PhD, DBA, MPhil, MBA, MA, MSc. (Research), etc.)</em></div>

        <table class="pf-grad">
            <tr>
                <td class="pf-grad__heading">Master's Programme</td>
                <td class="pf-grad__heading">Terminal Degree</td>
            </tr>
            <tr>
                <td>
                    <div class="pf-grad__line">
                        <span class="pf-grad__line-label">i.</span>
                        @php $sel = $applicantData['masters_qualification'] ?? null; @endphp
                        @foreach($mastersQualLabels as $key => $label)
                            <span class="pf-grad__checkbox {{ $sel === $key ? 'pf-grad__checkbox--on' : 'pf-grad__checkbox--off' }}">
                                {{ $sel === $key ? '☑' : '☐' }} {{ $label }}
                            </span>
                        @endforeach
                    </div>
                    <div class="pf-grad__line">
                        <span class="pf-grad__line-label">ii.</span>
                        <span style="font-weight: 600;">If others, please specify:</span>
                        <span class="pf-grad__inline-value">{{ $applicantData['masters_qualification_other'] ?? '' }}</span>
                    </div>
                    <div class="pf-grad__line" style="margin-top: 6px;">
                        @php $fmt = $applicantData['masters_format'] ?? null; @endphp
                        @foreach($mastersFmtLabels as $key => $label)
                            <span class="pf-grad__checkbox {{ $fmt === $key ? 'pf-grad__checkbox--on' : 'pf-grad__checkbox--off' }}">
                                {{ $fmt === $key ? '☑' : '☐' }} {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </td>
                <td>
                    <div class="pf-grad__line">
                        <span class="pf-grad__line-label">i.</span>
                        @php $sel = $applicantData['terminal_qualification'] ?? null; @endphp
                        @foreach($terminalQualLabels as $key => $label)
                            <span class="pf-grad__checkbox {{ $sel === $key ? 'pf-grad__checkbox--on' : 'pf-grad__checkbox--off' }}">
                                {{ $sel === $key ? '☑' : '☐' }} {{ $label }}
                            </span>
                        @endforeach
                    </div>
                    <div class="pf-grad__line">
                        <span class="pf-grad__line-label">ii.</span>
                        <span style="font-weight: 600;">If others, please specify:</span>
                        <span class="pf-grad__inline-value">{{ $applicantData['terminal_qualification_other'] ?? '' }}</span>
                    </div>
                    <div class="pf-grad__line" style="margin-top: 6px;">
                        @php $fmt = $applicantData['terminal_format'] ?? null; @endphp
                        @foreach($terminalFmtLabels as $key => $label)
                            <span class="pf-grad__checkbox {{ $fmt === $key ? 'pf-grad__checkbox--on' : 'pf-grad__checkbox--off' }}">
                                {{ $fmt === $key ? '☑' : '☐' }} {{ $label }}
                            </span>
                        @endforeach
                    </div>
                </td>
            </tr>
        </table>

        {{-- Research Area --}}
        <table class="pf-item" style="margin-top: 6px;"><tr>
            <td>
                <span class="pf-item__label">Research Area:</span>
                <div class="pf-item__block" style="min-height: 36px;">{{ $applicantData['research_area'] ?? '' }}</div>
                <div style="font-size: 9px; font-style: italic; color: #6b7280; text-align: center; margin-top: 2px;">(use additional sheet if necessary)</div>
            </td>
        </tr></table>
    </div>

    {{-- =========================================================
         3. PREVIOUS EMPLOYMENT
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">3.</td>
            <td class="pf-section__title-cell">Previous Employment</td>
        </tr></table>

        <table class="pf-table">
            <thead>
                <tr>
                    <th class="pf-table__index">#</th>
                    <th>Institution / Organisation</th>
                    <th style="width: 16%;">Dates Worked</th>
                    <th style="width: 22%;">Position Held</th>
                    <th style="width: 26%;">Reasons for Leaving</th>
                </tr>
            </thead>
            <tbody>
                @forelse($previousRows as $i => $row)
                    <tr>
                        <td class="pf-table__index">{{ $romans[$i] ?? ($i + 1) . '.' }}</td>
                        <td>{{ $row['institution'] ?? '' }}</td>
                        <td>{{ $row['dates'] ?? '' }}</td>
                        <td>{{ $row['position'] ?? '' }}</td>
                        <td>{{ $row['reasons'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="5">— no previous employment listed —</td></tr>
                @endforelse
            </tbody>
        </table>

        @if(!empty($applicantData['previous_employment_extra']))
            <table class="pf-item" style="margin-top: 6px;"><tr>
                <td>
                    <span class="pf-item__label">Additional information:</span>
                    <div class="pf-item__block">{{ $applicantData['previous_employment_extra'] }}</div>
                </td>
            </tr></table>
        @endif
    </div>

    {{-- =========================================================
         4. WORKING EXPERIENCE
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">4.</td>
            <td class="pf-section__title-cell">Working Experience</td>
        </tr></table>
        <p style="margin: 0 0 6px; font-size: 10.5px; color: #4b5563; font-style: italic;">
            (These may include managerial and administrative leadership positions held.)
        </p>

        <table class="pf-table">
            <thead>
                <tr>
                    <th>Institution / Organisation</th>
                    <th style="width: 16%;">Date(s)</th>
                    <th style="width: 22%;">Effective Date(s) of Appointment</th>
                    <th style="width: 34%;">Responsibilities</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workingRows as $i => $row)
                    <tr>
                        <td>{{ $row['institution'] ?? '' }}</td>
                        <td>{{ $row['dates'] ?? '' }}</td>
                        <td>{{ $row['effective_appointment'] ?? '' }}</td>
                        <td>{{ $row['responsibilities'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="4">— no working experience listed —</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="font-size: 9px; font-style: italic; color: #6b7280; text-align: center; margin-top: -2px;">(You may add additional sheets if necessary)</div>
    </div>

    {{-- =========================================================
         5. MAJOR PROJECTS / PUBLICATIONS
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">5.</td>
            <td class="pf-section__title-cell">Details of Major Administrative / Professional Projects Undertaken — Reports, Memoranda &amp; Publications</td>
        </tr></table>
        <p style="margin: 0 0 6px; font-size: 10.5px; color: #4b5563; font-style: italic;">
            (All references cited must be exact and complete.)
        </p>

        <table class="pf-table">
            <thead>
                <tr>
                    <th class="pf-table__index">#</th>
                    <th>Reference</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projectsRows as $i => $row)
                    <tr>
                        <td class="pf-table__index">{{ $romans[$i] ?? ($i + 1) . '.' }}</td>
                        <td>{{ $row['reference'] ?? '' }}</td>
                    </tr>
                @empty
                    <tr class="pf-table__empty"><td colspan="2">— no projects or publications listed —</td></tr>
                @endforelse
            </tbody>
        </table>
        <div style="font-size: 9px; font-style: italic; color: #6b7280; text-align: center; margin-top: -2px;">(use extra sheet if necessary)</div>
    </div>

    {{-- =========================================================
         6. BRIEF STATEMENT OF AREAS OF SPECIAL INTEREST
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">6.</td>
            <td class="pf-section__title-cell">A Brief Statement of Areas of Special Administrative / Professional Interest</td>
        </tr></table>
        <div class="pf-item__block" style="min-height: 70px;">{{ $applicantData['areas_of_interest'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         7. GENERAL
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">7.</td>
            <td class="pf-section__title-cell">General</td>
        </tr></table>

        <div class="pf-subsection__title">i. Name(s) of professional associations of which the candidate is a member</div>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['professional_associations'] ?? '' }}</div>

        <div class="pf-subsection__title" style="margin-top: 8px;">ii. Extra-curricular activities undertaken in the last 3 years</div>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['extracurricular_activities'] ?? '' }}</div>

        <div class="pf-subsection__title" style="margin-top: 8px;">iii. Any additional information you may wish to provide</div>
        <div class="pf-item__block" style="min-height: 50px;">{{ $applicantData['additional_information'] ?? '' }}</div>
    </div>

    {{-- =========================================================
         8 + 9. RECOMMENDATION (combined into a single stage)
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">8.</td>
            <td class="pf-section__title-cell">Recommendation / Comment(s) by {{ $assessorRoleLabel }}</td>
        </tr></table>
        <p style="margin: 0 0 6px; font-size: 9.5px; color: #6b7280; font-style: italic;">
            (Section 9 of the paper form — "Comment(s) by Head of Department, if the above recommendation is by Sectional / Unit Head" — is fulfilled here too: the applicant routes the form to the appropriate recommender, who is recorded by role below.)
        </p>

        <div class="pf-statement">
            <strong>Comment(s):</strong>
            <div class="pf-item__block" style="margin-top: 4px; min-height: 70px; background: #fff; border-color: #d1d5db;">{{ $recommenderData['recommender_comments'] ?? '' }}</div>
        </div>

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of {{ $assessorRoleLabel }}</div>
            <div class="pf-sigcard__body">
                @if($recommenderSig)
                    @php $img = $sigFsPath($recommenderSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="Recommender signature">@endif
                @else
                    <div class="pf-sigcard__empty">— awaiting recommendation &amp; signature —</div>
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
         10. DECLARATION
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">9.</td>
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

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Applicant</div>
            <div class="pf-sigcard__body">
                @if($declarationSig)
                    @php $img = $sigFsPath($declarationSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="Applicant signature">@endif
                @else
                    <div class="pf-sigcard__empty">— awaiting applicant's declaration &amp; signature —</div>
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
         APPROVAL & FILING BY REGISTRAR (extra workflow stage)
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <table class="pf-section__title-row"><tr>
            <td class="pf-section__num-cell">10.</td>
            <td class="pf-section__title-cell">Approval &amp; Filing by Registrar</td>
        </tr></table>

        @if(!empty($registrarData['registrar_comments']))
            <div class="pf-item__block" style="min-height: 28px;">
                <strong>Registrar's Comments:</strong>
                <div style="margin-top: 4px;">{{ $registrarData['registrar_comments'] }}</div>
            </div>
        @endif

        <div class="pf-sigcard">
            <div class="pf-sigcard__head">Signature of Registrar</div>
            <div class="pf-sigcard__body">
                @if($registrarSig)
                    @php $img = $sigFsPath($registrarSig); @endphp
                    @if($img)<img class="pf-sigcard__img" src="{{ $img }}" alt="Registrar signature">@endif
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
