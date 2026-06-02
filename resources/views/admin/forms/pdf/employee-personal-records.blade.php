{{--
    Employee Personal Records — paper-form-faithful PDF.

    Stage data keys (must match EmployeePersonalRecordsForm):
      applicant — PART I (items 1–11):
                 full_name,
                 date_of_birth, age, gender,
                 place_of_birth, home_town,
                 contact_address,
                 permanent_address, email, telephone_number,
                 marital_status, spouse_name_and_address,
                 children[], father_name, mother_name,
                 next_of_kin_name, next_of_kin_relation, next_of_kin_address, next_of_kin_telephone,
                 beneficiaries[]
      applicant — PART II:  schools_attended[], qualifications
      applicant — PART III: employment_history[]
      applicant — PART IV (items 14–18):
                 convicted_offence, conviction_details,
                 position_at_cug, cug_office_department,
                 social_security_number,
                 date_of_first_appointment, date_of_assumption_of_duty,
                 declaration_accepted
      hr:        staff_no, hr_placement_confirmed, hr_comments     (PRIMARY copy)
      registrar: appointment_no, registrar_comments                (DUPLICATE copy)

    The passport-size photograph in the top-right of page 1 is sourced from the
    first image attachment uploaded at the applicant stage (any image/* mime
    type). If none was uploaded, a "Affix recent passport size photograph"
    placeholder box is rendered instead.
--}}
@php
    $applicantData = $submission->sectionData('applicant');
    $registrarData = $submission->sectionData('registrar');
    $hrData        = $submission->sectionData('hr');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
    $applicantSig = $signaturesByStage->get('applicant')?->last();
    $registrarSig = $signaturesByStage->get('registrar')?->last();
    $hrSig        = $signaturesByStage->get('hr')?->last();

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

    $maritalStatus  = strtolower((string) ($applicantData['marital_status'] ?? ''));
    $isMarried      = $maritalStatus === 'married';
    $isSingle       = $maritalStatus === 'single';
    $convictionAns  = strtolower((string) ($applicantData['convicted_offence'] ?? ''));
    $genderRaw      = strtolower((string) ($applicantData['gender'] ?? ''));
    $genderLabel    = ['male' => 'Male', 'female' => 'Female'][$genderRaw] ?? '';

    // Passport photograph — find the first image attachment on this submission.
    // The controller prepends the dedicated `passport_photo` upload to the
    // attachments array before storage, so the FIRST image at the applicant
    // stage is reliably the passport photo. We accept any of these proofs of
    // being an image — some shared-hosting environments report no/odd mime
    // types, so a file-extension fallback is essential:
    //   1. mime_type starts with "image/", OR
    //   2. the original filename / stored path ends in a common image extension.
    $passportPhotoFs = null;
    $imageExts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'bmp'];

    $resolveImage = function ($attachment) use ($imageExts) {
        $mime = strtolower((string) ($attachment->mime_type ?? ''));
        $name = (string) ($attachment->name ?? '');
        $path = (string) ($attachment->path ?? '');
        $ext  = strtolower(pathinfo($name !== '' ? $name : $path, PATHINFO_EXTENSION));
        $isImage = ($mime !== '' && str_starts_with($mime, 'image/'))
                || in_array($ext, $imageExts, true);
        if (!$isImage || $path === '') return null;
        $fs = storage_path('app/public/' . ltrim($path, '/'));
        return file_exists($fs) ? $fs : null;
    };

    // Prefer an applicant-stage image (where the passport upload lives).
    foreach ($submission->attachments ?? [] as $a) {
        if (($a->stage_slug ?? '') !== 'applicant') continue;
        if ($fs = $resolveImage($a)) { $passportPhotoFs = $fs; break; }
    }
    // Last-ditch fallback: any image attachment on the submission.
    if (!$passportPhotoFs) {
        foreach ($submission->attachments ?? [] as $a) {
            if ($fs = $resolveImage($a)) { $passportPhotoFs = $fs; break; }
        }
    }

    $children          = is_array($applicantData['children'] ?? null)         ? $applicantData['children']         : [];
    $beneficiaries     = is_array($applicantData['beneficiaries'] ?? null)    ? $applicantData['beneficiaries']    : [];
    $schoolsAttended   = is_array($applicantData['schools_attended'] ?? null) ? $applicantData['schools_attended'] : [];
    $employmentHistory = is_array($applicantData['employment_history'] ?? null) ? $applicantData['employment_history'] : [];

    // Drop fully-blank rows so the PDF doesn't render empty placeholders.
    $stripBlank = function (array $rows): array {
        return array_values(array_filter($rows, function ($row) {
            if (!is_array($row)) return false;
            foreach ($row as $cell) {
                if (!is_null($cell) && trim((string) $cell) !== '') return true;
            }
            return false;
        }));
    };

    $children          = $stripBlank($children);
    $beneficiaries     = $stripBlank($beneficiaries);
    $schoolsAttended   = $stripBlank($schoolsAttended);
    $employmentHistory = $stripBlank($employmentHistory);
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $submission->form_code }} - {{ $submission->reference }}</title>
    <style>
        @page { margin: 13mm 13mm 13mm 13mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #111827; font-size: 11px; line-height: 1.45; }
        h1, h2, h3, h4, h5 { margin: 0; padding: 0; }

        /* ── Header layout (paper-faithful): a 3-column table where the
              left spacer column mirrors the right photo column, keeping
              the centre title block centred on the *page* regardless of
              the photo box on the right. ── */
        .pf-headtbl { width: 100%; border-collapse: collapse; margin-bottom: 8px; }
        .pf-headtbl td { vertical-align: top; padding: 0; }
        .pf-headtbl__sp     { width: 18%; }
        .pf-headtbl__center { width: 64%; text-align: center; }
        .pf-headtbl__right  { width: 18%; text-align: right; }

        .pf-head { text-align: center; }
        .pf-head h1 { font-size: 14.5px; font-weight: 800; letter-spacing: 0.4px; }
        .pf-head .pf-subhead { font-style: italic; font-weight: 600; font-size: 10.5px; margin-top: 2px; }
        .pf-head__logo { display: block; margin: 4px auto 3px; height: 50px; width: auto; }
        .pf-head h2 { font-size: 14px; font-weight: 800; margin-top: 4px; text-decoration: underline; letter-spacing: 0.3px; }
        .pf-head .pf-rubric { font-style: italic; font-size: 9.5px; color: #374151; margin-top: 3px; }

        /* ── Passport-photograph box (paper-faithful, dompdf-safe) ──
           Implemented as a nested <table> so the inner <td> can do
           vertical-align: middle without using display: table-cell on a
           non-cell element — dompdf throws "Frame not found in cellmap"
           when an anonymous table-cell is created inside a real <td>. */
        .pf-photo {
            width: 105px;
            height: 130px;
            margin-left: auto;
            border: 1.2px solid #111827;
            background: #fff;
            border-collapse: collapse;
        }
        .pf-photo--filled { border-color: #15803d; }
        .pf-photo__cell {
            width: 105px;
            height: 130px;
            text-align: center;
            vertical-align: middle;
            padding: 0;
            overflow: hidden;
        }
        .pf-photo__img { display: block; margin: 0 auto; }
        .pf-photo__hint {
            font-style: italic;
            font-size: 9.5px;
            color: #6b7280;
            line-height: 1.4;
            padding: 0 4px;
        }

        /* ── Reference strip ── */
        .pf-meta { margin: 8px 0 12px; padding: 6px 10px; background: #f3f4f6; border-radius: 4px; font-size: 9.5px; color: #374151; }
        .pf-meta span { margin-right: 14px; }
        .pf-meta strong { color: #111827; }

        /* ── Staff-No / Appointment-No header box (matches the right-aligned box on the paper form) ── */
        .pf-staffbox {
            margin: 6px 0 14px;
            padding: 8px 12px;
            border: 1.5px solid #111827;
            border-radius: 4px;
            background: #fff;
        }
        .pf-staffbox table { width: 100%; border-collapse: collapse; }
        .pf-staffbox td { padding: 2px 6px; font-size: 10.5px; }
        .pf-staffbox .pf-staffbox__label { font-weight: 700; color: #1f2937; width: 35%; }
        .pf-staffbox .pf-staffbox__value { color: #0c0c0c; font-weight: 600; }
        .pf-staffbox .pf-staffbox__hint { font-style: italic; font-size: 9.5px; color: #6b7280; }

        /* ── Part header ── */
        .pf-part-head {
            margin: 16px 0 10px;
            padding: 6px 10px;
            background: #0c0c0c;
            color: #fff;
            font-weight: 800;
            font-size: 11.5px;
            letter-spacing: 0.6px;
            text-transform: uppercase;
            border-radius: 3px;
        }
        .pf-section { margin-bottom: 12px; }
        .pf-section__title {
            font-weight: 700;
            font-size: 10.5px;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            margin: 10px 0 6px;
            color: #111827;
            border-bottom: 1px solid #d1d5db;
            padding-bottom: 3px;
        }

        /* ── Two-column key/value grid (matches the paper form's two-column layout) ── */
        .pf-grid { width: 100%; margin: 0 0 8px; border-collapse: collapse; }
        .pf-grid td { vertical-align: top; padding: 3px 4px; width: 50%; }
        .pf-grid .pf-kv__label { font-weight: 600; color: #1f2937; display: inline-block; }
        .pf-grid .pf-kv__value {
            display: inline-block;
            border-bottom: 1px dotted #6b7280;
            padding: 0 4px 1px;
            min-height: 13px;
            font-weight: 500;
            color: #0c0c0c;
        }

        /* ── Single-row labelled value (for full-width fields like Contact Address) ── */
        .pf-row { margin: 4px 0 8px; }
        .pf-row__label { font-weight: 600; color: #1f2937; }
        .pf-row__value {
            display: block;
            margin-top: 4px;
            min-height: 24px;
            padding: 6px 8px;
            background: #fafafa;
            border: 1px solid #e5e7eb;
            border-radius: 3px;
            font-weight: 500;
            color: #0c0c0c;
            white-space: pre-wrap;
        }
        .pf-row__value--inline {
            display: inline-block;
            border-bottom: 1px dotted #6b7280;
            padding: 0 4px 1px;
            min-height: 13px;
            background: transparent;
            border-top: 0;
            border-left: 0;
            border-right: 0;
            border-radius: 0;
        }

        /* ── Marital-status pill row ── */
        .pf-pillrow { margin: 4px 0 8px; }
        .pf-pillrow__label { font-weight: 600; color: #1f2937; margin-right: 8px; }
        .pf-pill {
            display: inline-block;
            padding: 2px 10px;
            margin-right: 6px;
            border: 1px solid #d1d5db;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            background: #fff;
        }
        .pf-pill--on {
            background: #111827;
            border-color: #111827;
            color: #fff;
        }

        /* ── Data tables (children, beneficiaries, schools, employment) ── */
        .pf-table { width: 100%; border-collapse: collapse; margin: 4px 0 10px; }
        .pf-table th, .pf-table td {
            border: 1px solid #d1d5db;
            padding: 5px 7px;
            text-align: left;
            font-size: 10px;
            vertical-align: top;
        }
        .pf-table th {
            background: #f3f4f6;
            font-weight: 700;
            color: #1f2937;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.04em;
        }
        .pf-table td.pf-table__num { text-align: center; width: 26px; font-weight: 700; color: #6b7280; }
        .pf-table tr.pf-table__empty td { font-style: italic; color: #9ca3af; text-align: center; font-size: 10px; }

        /* ── Signature card ── */
        .pf-sigcard {
            margin: 8px 0 4px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background: #fff;
            page-break-inside: avoid;
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
        .pf-sigcard__meta {
            padding: 6px 12px 7px;
            border-top: 1px dashed #9ca3af;
            font-size: 10px;
            color: #4b5563;
            line-height: 1.5;
        }
        .pf-sigcard__meta strong { color: #111827; font-weight: 700; }
        .pf-sigcard__date { color: #111827; font-weight: 600; border-bottom: 1px dotted #9ca3af; padding-bottom: 1px; }
        .pf-sigcard__badge {
            display: inline-block;
            padding: 1px 7px;
            border-radius: 8px;
            font-size: 8.5px;
            font-weight: bold;
            margin-left: 5px;
            vertical-align: middle;
        }
        .pf-sigcard__badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .pf-sigcard__badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        /* ── Declaration block ── */
        .pf-declaration {
            margin: 10px 0;
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 3px solid #6b7280;
            border-radius: 0 3px 3px 0;
            font-size: 10px;
            color: #1f2937;
            line-height: 1.55;
        }

        /* ── Footer ── */
        .pf-doc-footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #d1d5db; font-size: 9px; color: #6b7280; text-align: center; }

        /* Avoid splitting a single signature/section badly across pages */
        .pf-keep { page-break-inside: avoid; }
    </style>
</head>
<body>

    {{-- ===== Header (paper-faithful): 3-column table — empty spacer on the
         left exactly mirrors the photo column on the right so the centre
         title block stays centred on the page. ===== --}}
    <table class="pf-headtbl">
        <tr>
            <td class="pf-headtbl__sp">&nbsp;</td>
            <td class="pf-headtbl__center">
                <div class="pf-head">
                    <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE-SUNYANI</h1>
                    <div class="pf-subhead">[Office of the Registrar, P. O. Box 363, Sunyani – B/R]</div>
                    @if(file_exists($logoFsPath))
                        <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
                    @endif
                    <h2>EMPLOYEE PERSONAL RECORDS</h2>
                    <div class="pf-rubric">(This Form must be completed in Duplicate and forwarded to the Registrar's Office)</div>
                </div>
            </td>
            <td class="pf-headtbl__right">
                <table class="pf-photo {{ $passportPhotoFs ? 'pf-photo--filled' : '' }}" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="pf-photo__cell">
                            @if($passportPhotoFs)
                                <img class="pf-photo__img" src="{{ $passportPhotoFs }}" width="103" height="128" alt="Passport photograph">
                            @else
                                <span class="pf-photo__hint">Affix recent<br>passport size<br>photograph</span>
                            @endif
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    {{-- Reference strip --}}
    <div class="pf-meta">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    {{-- ===== Staff No (HR) / Appointment No (Registrar) — compact strip ===== --}}
    <div class="pf-staffbox">
        <table>
            <tr>
                <td class="pf-staffbox__label">STAFF NO.</td>
                <td class="pf-staffbox__value">{{ $hrData['staff_no'] ?? '' }}
                    @if(empty($hrData['staff_no']))
                        <span class="pf-staffbox__hint">— assigned by HR (Office Use)</span>
                    @endif
                </td>
                <td class="pf-staffbox__label">APPOINTMENT NO.</td>
                <td class="pf-staffbox__value">{{ $registrarData['appointment_no'] ?? '' }}
                    @if(empty($registrarData['appointment_no']))
                        <span class="pf-staffbox__hint">— assigned by Registrar</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PART I — PERSONAL & FAMILY DETAILS
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">PART I — Personal Particulars</div>

    {{-- ===== 1. Name ===== --}}
    <div class="pf-row">
        <span class="pf-row__label">1. Name:</span>
        <span class="pf-row__value pf-row__value--inline" style="min-width: 80%;">{{ strtoupper((string) ($applicantData['full_name'] ?? '')) }}</span>
    </div>

    {{-- ===== 2. Date of Birth + Age + Gender ===== --}}
    <table class="pf-grid">
        <tr>
            <td style="width: 38%;"><span class="pf-kv__label">2. Date of Birth:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $fmtDate($applicantData['date_of_birth'] ?? null) }}</span></td>
            <td style="width: 26%;"><span class="pf-kv__label">Age:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['age'] ?? '' }}</span></td>
            <td style="width: 36%;"><span class="pf-kv__label">Gender:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $genderLabel }}</span></td>
        </tr>
    </table>

    {{-- ===== 3. Place of Birth + Home Town ===== --}}
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">3. Place of Birth:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ $applicantData['place_of_birth'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Home Town:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ $applicantData['home_town'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- ===== 4. Contact Address ===== --}}
    <div class="pf-row">
        <span class="pf-row__label">4. Contact Address:</span>
        <div class="pf-row__value">{{ $applicantData['contact_address'] ?? '' }}</div>
    </div>

    {{-- ===== 5. Permanent Address + Email + Telephone ===== --}}
    <div class="pf-row">
        <span class="pf-row__label">5. Permanent Address:</span>
        <div class="pf-row__value">{{ $applicantData['permanent_address'] ?? '' }}</div>
    </div>
    <table class="pf-grid" style="margin-top: 4px;">
        <tr>
            <td><span class="pf-kv__label">Email:</span>
                <span class="pf-kv__value" style="min-width: 70%;">{{ $applicantData['email'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Telephone Number(s):</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['telephone_number'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- ===== 6. Marital Status ===== --}}
    <div class="pf-pillrow">
        <span class="pf-pillrow__label">6. Marital Status:</span>
        <span class="pf-pill {{ $isSingle ? 'pf-pill--on' : '' }}">Single</span>
        <span class="pf-pill {{ $isMarried ? 'pf-pill--on' : '' }}">Married</span>
    </div>

    {{-- ===== 7. Name and Address of Spouse ===== --}}
    <div class="pf-row">
        <span class="pf-row__label">7. Name and Address of Spouse <em style="font-weight: 400; color: #6b7280;">(with supporting document)</em>:</span>
        <div class="pf-row__value">{{ $applicantData['spouse_name_and_address'] ?? '' }}</div>
    </div>

    {{-- ===== 8. Children ===== --}}
    <div class="pf-section__title">8. Children — Names and Dates of Birth</div>
    <table class="pf-table">
        <thead>
            <tr>
                <th style="width: 26px;">#</th>
                <th>Name</th>
                <th style="width: 25%;">Date of Birth</th>
            </tr>
        </thead>
        <tbody>
            @forelse($children as $i => $child)
                <tr>
                    <td class="pf-table__num">{{ $i + 1 }}</td>
                    <td>{{ $child['child_name'] ?? '' }}</td>
                    <td>{{ $fmtDate($child['date_of_birth'] ?? null) }}</td>
                </tr>
            @empty
                <tr class="pf-table__empty"><td colspan="3">— no children listed —</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ===== 9. Names of Parents ===== --}}
    <div class="pf-section__title">9. Names of Parents</div>
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">Father:</span>
                <span class="pf-kv__value" style="min-width: 70%;">{{ $applicantData['father_name'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Mother:</span>
                <span class="pf-kv__value" style="min-width: 70%;">{{ $applicantData['mother_name'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- ===== 10. Next-of-Kin ===== --}}
    <div class="pf-section__title">10. Next-of-Kin and Address</div>
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">Name:</span>
                <span class="pf-kv__value" style="min-width: 65%;">{{ $applicantData['next_of_kin_name'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Relation:</span>
                <span class="pf-kv__value" style="min-width: 65%;">{{ $applicantData['next_of_kin_relation'] ?? '' }}</span></td>
        </tr>
    </table>
    <div class="pf-row">
        <span class="pf-row__label">Address:</span>
        <div class="pf-row__value">{{ $applicantData['next_of_kin_address'] ?? '' }}</div>
    </div>
    <div class="pf-row">
        <span class="pf-row__label">Telephone:</span>
        <span class="pf-row__value pf-row__value--inline" style="min-width: 60%;">{{ $applicantData['next_of_kin_telephone'] ?? '' }}</span>
    </div>

    {{-- ===== 11. Beneficiaries ===== --}}
    <div class="pf-section__title">11. Beneficiaries</div>
    <table class="pf-table">
        <thead>
            <tr>
                <th style="width: 30px;">S/N</th>
                <th>Name</th>
                <th>Relation</th>
                <th style="width: 16%;">% Allocation</th>
                <th style="width: 32%;">Address / Contact No.</th>
            </tr>
        </thead>
        <tbody>
            @forelse($beneficiaries as $i => $b)
                <tr>
                    <td class="pf-table__num">{{ $i + 1 }}</td>
                    <td>{{ $b['beneficiary_name'] ?? '' }}</td>
                    <td>{{ $b['relation'] ?? '' }}</td>
                    <td>{{ isset($b['percentage']) && $b['percentage'] !== '' ? $b['percentage'] . '%' : '' }}</td>
                    <td>{{ $b['address_contact'] ?? '' }}</td>
                </tr>
            @empty
                <tr class="pf-table__empty"><td colspan="5">— no beneficiaries listed —</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══════════════════════════════════════════════════════
         PART II — DETAILS OF EDUCATION AND QUALIFICATIONS
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">PART II — Details of Education and Qualifications</div>

    <div class="pf-section__title">12. Schools Attended</div>
    <table class="pf-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Name of Institution</th>
                <th style="width: 20%;">From</th>
                <th style="width: 20%;">To</th>
            </tr>
        </thead>
        <tbody>
            @forelse($schoolsAttended as $i => $s)
                <tr>
                    <td class="pf-table__num">{{ $i + 1 }}</td>
                    <td>{{ $s['institution'] ?? '' }}</td>
                    <td>{{ $fmtDate($s['from'] ?? null) }}</td>
                    <td>{{ $fmtDate($s['to'] ?? null) }}</td>
                </tr>
            @empty
                <tr class="pf-table__empty"><td colspan="4">— no institutions listed —</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="pf-section__title">13. Qualifications</div>
    <div class="pf-row">
        <div class="pf-row__value" style="min-height: 50px;">{{ $applicantData['qualifications'] ?? '' }}</div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PART III — DETAILS OF EMPLOYMENT
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">PART III — Details of Employment</div>
    <p style="margin: 0 0 8px; font-style: italic; font-size: 10px; color: #4b5563;">
        Listed below, beginning from the current / most recent employment, are all places worked at — with dates, positions held and reasons for leaving.
    </p>
    <table class="pf-table">
        <thead>
            <tr>
                <th style="width: 30px;">#</th>
                <th>Name of Organization</th>
                <th style="width: 14%;">Date From</th>
                <th style="width: 14%;">Date To</th>
                <th>Position Held</th>
                <th style="width: 22%;">Reasons for Leaving</th>
            </tr>
        </thead>
        <tbody>
            @forelse($employmentHistory as $i => $job)
                <tr>
                    <td class="pf-table__num">{{ $i + 1 }}</td>
                    <td>{{ $job['organization'] ?? '' }}</td>
                    <td>{{ $fmtDate($job['date_from'] ?? null) }}</td>
                    <td>{{ $fmtDate($job['date_to'] ?? null) }}</td>
                    <td>{{ $job['position_held'] ?? '' }}</td>
                    <td>{{ $job['reasons'] ?? '' }}</td>
                </tr>
            @empty
                <tr class="pf-table__empty"><td colspan="6">— no previous employment listed —</td></tr>
            @endforelse
        </tbody>
    </table>

    {{-- ═══════════════════════════════════════════════════════
         PART IV — OTHER INFORMATION
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">PART IV — Other Information</div>

    <div class="pf-section__title">14. Convictions / Criminal &amp; Legal Offences</div>
    <div class="pf-pillrow">
        <span class="pf-pillrow__label">Ever convicted of any criminal or legal offence?</span>
        <span class="pf-pill {{ $convictionAns === 'no'  ? 'pf-pill--on' : '' }}">No</span>
        <span class="pf-pill {{ $convictionAns === 'yes' ? 'pf-pill--on' : '' }}">Yes</span>
    </div>
    @if($convictionAns === 'yes')
        <div class="pf-row">
            <span class="pf-row__label">Details:</span>
            <div class="pf-row__value" style="min-height: 40px;">{{ $applicantData['conviction_details'] ?? '' }}</div>
        </div>
    @endif

    {{-- Items 15 + 16 share a 2-col row (.pf-grid honours width:50% on each td). --}}
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">15. Position given at CUG:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['position_at_cug'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">16. Office / Department:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['cug_office_department'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- Item 17 (full-width) rendered as a standalone block — NOT a colspan
         row inside .pf-grid, because the CSS sets width:50% on every td and
         dompdf's cellmap can't reconcile a colspan-2 cell with a 50%
         per-cell width rule (it throws "Frame not found in cellmap"). --}}
    <div class="pf-row" style="margin-top: 4px;">
        <span class="pf-row__label">17. Social Security Number <em style="font-weight: 400; color: #6b7280;">(if any)</em>:</span>
        <span class="pf-row__value pf-row__value--inline" style="min-width: 70%;">{{ $applicantData['social_security_number'] ?? '' }}</span>
    </div>

    {{-- Item 18 — both dates back to a 2-col row. --}}
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">18. Date of 1<sup>st</sup> Appointment:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $fmtDate($applicantData['date_of_first_appointment'] ?? null) }}</span></td>
            <td><span class="pf-kv__label">Date of Assumption of Duty:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $fmtDate($applicantData['date_of_assumption_of_duty'] ?? null) }}</span></td>
        </tr>
    </table>

    {{-- ===== Declaration ===== --}}
    <div class="pf-declaration">
        <strong>Declaration:</strong> I hereby certify that, to the best of my knowledge, all the details given in this form
        are correct. I understand that any proven falsification or concealment of any material fact in respect of this
        record may lead to disciplinary action and possible dismissal.
        @if(!empty($applicantData['declaration_accepted']))
            <em style="color: #15803d; font-weight: 700;">[Declaration accepted and signed below.]</em>
        @endif
    </div>

    {{-- ===== Employee signature ===== --}}
    <div class="pf-sigcard pf-keep">
        <div class="pf-sigcard__head">Signature of Employee</div>
        <div class="pf-sigcard__body">
            @if($applicantSig)
                @php $img = $sigFsPath($applicantSig); @endphp
                @if($img)
                    <img class="pf-sigcard__img" src="{{ $img }}" alt="Employee signature">
                @endif
            @else
                <div class="pf-sigcard__empty">— awaiting employee signature —</div>
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

    {{-- ═══════════════════════════════════════════════════════
         HUMAN RESOURCE UNIT — Primary Copy
         (Listed first because the form is primarily an HR personnel record;
          the employee files a copy with HR and a duplicate with the Registrar.)
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">For Office Use — Human Resource Unit (Primary Copy)</div>
    <p style="margin: 0 0 6px; font-size: 10px; color: #374151;">
        The Human Resource Unit holds the primary copy of this personnel record. By signing
        below, HR confirms the placement details above and assigns the Staff Number.
    </p>
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">Staff Number (assigned by HR):</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $hrData['staff_no'] ?? '' }}</span></td>
            <td>
                @if(!empty($hrData['hr_placement_confirmed']))
                    <span style="font-size: 10px; color: #15803d; font-weight: 700;">✓ Placement confirmed by HR.</span>
                @endif
            </td>
        </tr>
    </table>
    @if(!empty($hrData['hr_comments']))
        <div class="pf-row">
            <span class="pf-row__label">HR Comments:</span>
            <div class="pf-row__value">{{ $hrData['hr_comments'] }}</div>
        </div>
    @endif

    <div class="pf-sigcard pf-keep">
        <div class="pf-sigcard__head">Signature of Head of Human Resource Unit</div>
        <div class="pf-sigcard__body">
            @if($hrSig)
                @php $img = $sigFsPath($hrSig); @endphp
                @if($img)
                    <img class="pf-sigcard__img" src="{{ $img }}" alt="HR signature">
                @endif
            @else
                <div class="pf-sigcard__empty">— awaiting HR signature —</div>
            @endif
        </div>
        <div class="pf-sigcard__meta">
            @if($hrSig)
                @php $check = $hrSig->verifyChain(); @endphp
                Signed by <strong>{{ $signerName($hrSig) }}</strong>
                on <span class="pf-sigcard__date">{{ $hrSig->signed_at?->format('d M Y, H:i') }}</span>
                <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                    {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                </span>
            @else
                Not yet signed.
            @endif
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         REGISTRAR'S OFFICE — Duplicate Copy
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">For Office Use — Registrar's Office (Duplicate Copy)</div>
    <p style="margin: 0 0 6px; font-size: 10px; color: #374151;">
        The Registrar's Office holds the institutional duplicate of this personnel record and
        assigns the Appointment Number.
    </p>
    {{-- Full-width single field — use the .pf-row block to avoid a
         colspan-2 td inside .pf-grid (the per-td width:50% rule and
         colspan don't co-exist in dompdf's cellmap allocator). --}}
    <div class="pf-row" style="margin-top: 4px;">
        <span class="pf-row__label">Appointment Number (assigned by Registrar):</span>
        <span class="pf-row__value pf-row__value--inline" style="min-width: 60%;">{{ $registrarData['appointment_no'] ?? '' }}</span>
    </div>
    @if(!empty($registrarData['registrar_comments']))
        <div class="pf-row">
            <span class="pf-row__label">Registrar's Comments:</span>
            <div class="pf-row__value">{{ $registrarData['registrar_comments'] }}</div>
        </div>
    @endif

    <div class="pf-sigcard pf-keep">
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

    {{-- ===== Footer ===== --}}
    <div class="pf-doc-footer">
        Generated {{ now()->format('d M Y, H:i') }} · Reference {{ $submission->reference }}
        @if($submission->signatures->isNotEmpty())
            · Tamper-evident audit chain ({{ $submission->signatures->count() }} signatures)
        @endif
        @if($definition->pdfFooterNote())
            <br><em>{{ $definition->pdfFooterNote() }}</em>
        @endif
    </div>
</body>
</html>
