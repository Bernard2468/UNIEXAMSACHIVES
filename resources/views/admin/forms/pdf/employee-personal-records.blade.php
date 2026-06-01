{{--
    Employee Personal Records — paper-form-faithful PDF.

    Stage data keys (must match EmployeePersonalRecordsForm):
      applicant: title, surname, other_names, nationality, country, city_region,
                 home_town, date_of_birth, religion, faculty_admin, department,
                 email, telephone_number, contact_address, permanent_address,
                 date_of_appointment, date_of_assumption,
                 marital_status, name_of_spouse, dependants,
                 vehicle_no, vehicle_make_model, vehicle_chassis_no,
                 provident_fund_number, social_security_fund_number,
                 name_of_bank, branch_name, bank_account_number,
                 children[], father_name, mother_name,
                 next_of_kin_name, next_of_kin_relation, next_of_kin_address, next_of_kin_telephone,
                 beneficiaries[],
                 schools_attended[], qualifications,
                 employment_history[],
                 convicted_offence, conviction_details,
                 position_at_cug, cug_office_department,
                 declaration_accepted
      hr:        staff_no, hr_placement_confirmed, hr_comments     (PRIMARY copy)
      registrar: appointment_no, registrar_comments                (DUPLICATE copy)
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

        /* ── Header (centred, matches paper form) ── */
        .pf-head { text-align: center; margin-bottom: 12px; }
        .pf-head h1 { font-size: 16px; font-weight: 800; letter-spacing: 0.6px; text-decoration: underline; }
        .pf-head .pf-subhead { font-style: italic; font-weight: 700; font-size: 11.5px; margin-top: 3px; }
        .pf-head__logo { display: block; margin: 6px auto 4px; height: 64px; width: auto; }
        .pf-head h2 { font-size: 14.5px; font-weight: 800; margin-top: 4px; text-decoration: underline; letter-spacing: 0.3px; }
        .pf-head .pf-rubric { font-style: italic; font-size: 10px; color: #374151; margin-top: 4px; }

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

    {{-- ===== Header (matches paper form) ===== --}}
    <div class="pf-head">
        <h1>CATHOLIC UNIVERSITY OF GHANA (FIAPRE, SUNYANI)</h1>
        @if(file_exists($logoFsPath))
            <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
        @endif
        <h2>EMPLOYEE PERSONAL RECORDS</h2>
        <div class="pf-rubric">[This form must be completed in duplicate and forwarded to the Registrar's Office]</div>
    </div>

    {{-- Reference strip --}}
    <div class="pf-meta">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    {{-- ===== Staff No (HR) / Appointment No (Registrar) box ===== --}}
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
            <tr>
                <td class="pf-staffbox__label">DATE OF APPOINTMENT</td>
                <td class="pf-staffbox__value">{{ $fmtDate($applicantData['date_of_appointment'] ?? null) }}</td>
                <td class="pf-staffbox__label">DATE OF ASSUMPTION</td>
                <td class="pf-staffbox__value">{{ $fmtDate($applicantData['date_of_assumption'] ?? null) }}</td>
            </tr>
        </table>
    </div>

    {{-- ═══════════════════════════════════════════════════════
         PART I — PERSONAL & FAMILY DETAILS
         ═══════════════════════════════════════════════════════ --}}
    <div class="pf-part-head">PART I — Personal &amp; Family Details</div>

    {{-- Personal particulars: 2-column grid --}}
    <div class="pf-section__title">Personal Particulars</div>
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">Title:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $applicantData['title'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Nationality:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $applicantData['nationality'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">Surname:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ strtoupper((string) ($applicantData['surname'] ?? '')) }}</span></td>
            <td><span class="pf-kv__label">Country:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['country'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">Other Names:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ strtoupper((string) ($applicantData['other_names'] ?? '')) }}</span></td>
            <td><span class="pf-kv__label">City / Region:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $applicantData['city_region'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">Home Town:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['home_town'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Date of Birth:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $fmtDate($applicantData['date_of_birth'] ?? null) }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">Religion:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ $applicantData['religion'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Faculty / Admin:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $applicantData['faculty_admin'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">Department:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['department'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Telephone Number:</span>
                <span class="pf-kv__value" style="min-width: 50%;">{{ $applicantData['telephone_number'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="pf-kv__label">E-mail:</span>
                <span class="pf-kv__value" style="min-width: 70%;">{{ $applicantData['email'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- Addresses --}}
    <div class="pf-section__title">Addresses</div>
    <div class="pf-row">
        <span class="pf-row__label">Contact Address:</span>
        <div class="pf-row__value">{{ $applicantData['contact_address'] ?? '' }}</div>
    </div>
    <div class="pf-row">
        <span class="pf-row__label">Permanent Address:</span>
        <div class="pf-row__value">{{ $applicantData['permanent_address'] ?? '' }}</div>
    </div>

    {{-- Marital status --}}
    <div class="pf-section__title">Marital Status &amp; Dependants</div>
    <div class="pf-pillrow">
        <span class="pf-pillrow__label">Marital Status:</span>
        <span class="pf-pill {{ $isSingle ? 'pf-pill--on' : '' }}">Single</span>
        <span class="pf-pill {{ $isMarried ? 'pf-pill--on' : '' }}">Married</span>
    </div>
    <div class="pf-row">
        <span class="pf-row__label">Name of Spouse:</span>
        <span class="pf-row__value pf-row__value--inline" style="min-width: 70%;">{{ $applicantData['name_of_spouse'] ?? '' }}</span>
    </div>
    <div class="pf-row">
        <span class="pf-row__label">Dependants:</span>
        <div class="pf-row__value">{{ $applicantData['dependants'] ?? '' }}</div>
    </div>

    {{-- Vehicle particulars --}}
    <div class="pf-section__title">Particulars of Vehicle (if owned — attach relevant ownership documents)</div>
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">I. Vehicle No.:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['vehicle_no'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">II. Make / Model:</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $applicantData['vehicle_make_model'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="pf-kv__label">III. Chassis No.:</span>
                <span class="pf-kv__value" style="min-width: 70%;">{{ $applicantData['vehicle_chassis_no'] ?? '' }}</span></td>
        </tr>
    </table>

    {{-- Pension & Banking --}}
    <div class="pf-section__title">Pension &amp; Banking Details</div>
    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">Provident Fund Number (if any):</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['provident_fund_number'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Social Security Fund Number:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['social_security_fund_number'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">Name of Bank:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ $applicantData['name_of_bank'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">Branch Name:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ $applicantData['branch_name'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="pf-kv__label">Bank Account Number:</span>
                <span class="pf-kv__value" style="min-width: 60%;">{{ $applicantData['bank_account_number'] ?? '' }}</span></td>
        </tr>
    </table>

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

    <table class="pf-grid">
        <tr>
            <td><span class="pf-kv__label">15. Position given at CUG:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['position_at_cug'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">16. Office / Department:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['cug_office_department'] ?? '' }}</span></td>
        </tr>
        <tr>
            <td><span class="pf-kv__label">17. Social Security Number:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $applicantData['social_security_fund_number'] ?? '' }}</span></td>
            <td><span class="pf-kv__label">18. Date of 1<sup>st</sup> Appointment:</span>
                <span class="pf-kv__value" style="min-width: 45%;">{{ $fmtDate($applicantData['date_of_appointment'] ?? null) }}</span></td>
        </tr>
        <tr>
            <td colspan="2"><span class="pf-kv__label">&nbsp;&nbsp;&nbsp;&nbsp;Date of Assumption of Duty:</span>
                <span class="pf-kv__value" style="min-width: 30%;">{{ $fmtDate($applicantData['date_of_assumption'] ?? null) }}</span></td>
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
    <table class="pf-grid">
        <tr>
            <td colspan="2"><span class="pf-kv__label">Appointment Number (assigned by Registrar):</span>
                <span class="pf-kv__value" style="min-width: 55%;">{{ $registrarData['appointment_no'] ?? '' }}</span></td>
        </tr>
    </table>
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
