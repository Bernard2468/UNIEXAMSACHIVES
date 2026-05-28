{{--
    Application for Car/Motorcycle/Bicycle Maintenance Allowance —
    paper-form-faithful PDF.

    Stage data keys (must match VehicleMaintenanceAllowanceForm):
      applicant: name, post_status, department_section_unit,
                 assumption_of_duty_date, vehicle_use_start_date,
                 vehicle_type, registration_number, type_and_make,
                 cubic_capacity
      head:      head_comments
      auditor:   audit_comments
      registrar: effective_from, registrar_comments
--}}
@php
    $applicantData = $submission->sectionData('applicant');
    $headData      = $submission->sectionData('head');
    $auditorData   = $submission->sectionData('auditor');
    $registrarData = $submission->sectionData('registrar');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
    $applicantSig = $signaturesByStage->get('applicant')?->last();
    $headSig      = $signaturesByStage->get('head')?->last();
    $auditorSig   = $signaturesByStage->get('auditor')?->last();
    $registrarSig = $signaturesByStage->get('registrar')?->last();

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

    // Vehicle-type label for display in the embedded confirmation statements.
    $vehicleTypeLabels = ['car' => 'car', 'motorcycle' => 'motorcycle', 'bicycle' => 'bicycle'];
    $vehicleNoun = $vehicleTypeLabels[$applicantData['vehicle_type'] ?? ''] ?? 'car / motorcycle / bicycle';

    $applicantNameForStatement = trim((string) ($applicantData['name'] ?? ''));
    $applicantNameForStatement = $applicantNameForStatement !== '' ? $applicantNameForStatement : '____________';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $submission->form_code }} - {{ $submission->reference }}</title>
    <style>
        @page { margin: 14mm 14mm 14mm 14mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #111827; font-size: 11.5px; line-height: 1.5; }
        h1, h2, h3, h4, h5 { margin: 0; padding: 0; }

        /* ── Header (centred, matches the paper form — no SUNYANI / no Registrar's Office rubric) ── */
        .pf-head { text-align: center; margin-bottom: 14px; padding-bottom: 8px; border-bottom: 1.5px solid #111827; }
        .pf-head h1 { font-size: 16px; font-weight: 800; letter-spacing: 1px; }
        .pf-head__logo { display: block; margin: 6px auto 4px; height: 60px; width: auto; }
        .pf-head h2 { font-size: 14.5px; font-weight: 800; margin-top: 4px; letter-spacing: 0.3px; line-height: 1.3; }

        /* ── Reference strip ── */
        .pf-meta { margin: 10px 0 14px; padding: 6px 10px; background: #f3f4f6; border-radius: 4px; font-size: 10px; color: #374151; }
        .pf-meta span { margin-right: 14px; }
        .pf-meta strong { color: #111827; }

        /* ── Numbered sections ── */
        .pf-section { margin-bottom: 14px; }
        .pf-section__title { font-weight: 700; font-size: 12px; letter-spacing: 0.4px; text-transform: uppercase; margin-bottom: 8px; color: #111827; }
        .pf-section__title span.pf-num { display: inline-block; min-width: 20px; }
        .pf-section--bordered { padding-top: 12px; border-top: 1px solid #d1d5db; margin-top: 16px; }

        /* ── Numbered / sub-lettered items inside a section ── */
        .pf-item { width: 100%; margin: 0 0 8px; border-collapse: collapse; }
        .pf-item td { vertical-align: top; padding: 0; }
        .pf-item__num { width: 28px; font-weight: 700; font-size: 11px; padding-right: 6px !important; padding-left: 6px !important; }
        .pf-item__label { font-weight: 600; color: #1f2937; }
        .pf-item__value {
            display: inline-block;
            border-bottom: 1px dotted #6b7280;
            padding: 0 4px 1px;
            min-height: 13px;
            font-weight: 500;
            color: #0c0c0c;
        }
        .pf-item__value--grow { display: block; margin-top: 4px; min-height: 30px; padding: 6px 8px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 3px; font-weight: 500; }

        /* ── Confirmation statement block (the "I confirm that … uses his/her …" lines) ── */
        .pf-statement {
            margin: 8px 0;
            padding: 8px 12px;
            background: #f9fafb;
            border-left: 3px solid #6b7280;
            border-radius: 0 4px 4px 0;
            font-size: 11px;
            color: #1f2937;
            line-height: 1.6;
        }
        .pf-statement__name { font-weight: 700; color: #111827; border-bottom: 1px dotted #6b7280; padding: 0 4px 1px; }

        /* ── Vehicle-type pill row ── */
        .pf-vehicle-row { margin-bottom: 8px; }
        .pf-vehicle-row__label { font-weight: 600; color: #1f2937; margin-right: 8px; }
        .pf-vehicle-pill {
            display: inline-block;
            padding: 3px 10px;
            margin-right: 6px;
            border: 1px solid #d1d5db;
            border-radius: 99px;
            font-size: 10px;
            font-weight: 600;
            color: #6b7280;
            background: #fff;
        }
        .pf-vehicle-pill--on {
            background: #111827;
            border-color: #111827;
            color: #fff;
        }

        /* ── Signature card (matches the leave / resumption forms) ── */
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
        .pf-sigcard__body {
            padding: 10px 12px;
            text-align: center;
            min-height: 54px;
        }
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

        /* ── Footer ── */
        .pf-doc-footer { margin-top: 14px; padding-top: 6px; border-top: 1px solid #d1d5db; font-size: 9px; color: #6b7280; text-align: center; }
    </style>
</head>
<body>

    {{-- ===== Header (matches paper form: no SUNYANI, no Registrar's Office rubric) ===== --}}
    <div class="pf-head">
        <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE</h1>
        @if(file_exists($logoFsPath))
            <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
        @endif
        <h2>{{ strtoupper($definition->title()) }}</h2>
    </div>

    {{-- Reference strip --}}
    <div class="pf-meta">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    {{-- =========================================================
         1. TO BE COMPLETED BY APPLICANT
         ========================================================= --}}
    <div class="pf-section">
        <div class="pf-section__title"><span class="pf-num">1</span> To be completed by Applicant</div>

        {{-- (a) Personal details --}}
        <table class="pf-item"><tr>
            <td class="pf-item__num">a)</td>
            <td>
                <span class="pf-item__label">Name:</span>
                <span class="pf-item__value" style="width: 80%;">{{ $applicantData['name'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Post / Status:</span>
                <span class="pf-item__value" style="width: 78%;">{{ $applicantData['post_status'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Department / Section / Unit:</span>
                <span class="pf-item__value" style="width: 64%;">{{ $applicantData['department_section_unit'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Date of Assumption of Duty:</span>
                <span class="pf-item__value" style="width: 60%;">{{ $fmtDate($applicantData['assumption_of_duty_date'] ?? null) }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Date on Which Staff Started Using Car / Motorcycle / Bicycle:</span>
                <span class="pf-item__value" style="width: 38%;">{{ $fmtDate($applicantData['vehicle_use_start_date'] ?? null) }}</span>
            </td>
        </tr></table>

        {{-- (b) Vehicle particulars --}}
        <table class="pf-item" style="margin-top: 10px;"><tr>
            <td class="pf-item__num">b)</td>
            <td>
                <span class="pf-item__label">Particulars of Car / Motorcycle / Bicycle:</span>
            </td>
        </tr></table>

        {{-- Vehicle-type pill row --}}
        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <div class="pf-vehicle-row">
                    <span class="pf-vehicle-row__label">Vehicle Type:</span>
                    @foreach(['car' => 'Car', 'motorcycle' => 'Motorcycle', 'bicycle' => 'Bicycle'] as $key => $label)
                        <span class="pf-vehicle-pill {{ ($applicantData['vehicle_type'] ?? '') === $key ? 'pf-vehicle-pill--on' : '' }}">{{ $label }}</span>
                    @endforeach
                </div>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Registration Number:</span>
                <span class="pf-item__value" style="width: 70%;">{{ $applicantData['registration_number'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Type & Make:</span>
                <span class="pf-item__value" style="width: 80%;">{{ $applicantData['type_and_make'] ?? '' }}</span>
            </td>
        </tr></table>

        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Cubic Capacity:</span>
                <span class="pf-item__value" style="width: 78%;">{{ $applicantData['cubic_capacity'] ?? '' }}</span>
            </td>
        </tr></table>

        {{-- Applicant signature card --}}
        <table class="pf-item" style="margin-top: 12px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <div class="pf-sigcard">
                    <div class="pf-sigcard__head">Signature of Applicant</div>
                    <div class="pf-sigcard__body">
                        @if($applicantSig)
                            @php $img = $sigFsPath($applicantSig); @endphp
                            @if($img)
                                <img class="pf-sigcard__img" src="{{ $img }}" alt="Applicant signature">
                            @endif
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
            </td>
        </tr></table>
    </div>

    {{-- =========================================================
         2. ENDORSEMENT BY HEAD OF DEPARTMENT / SECTION / UNIT
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <div class="pf-section__title"><span class="pf-num">2</span> Endorsement by Head of Department / Section / Unit</div>

        <div class="pf-statement">
            I confirm that <span class="pf-statement__name">{{ $applicantNameForStatement }}</span> uses
            his/her <strong>{{ $vehicleNoun }}</strong> for commuting to and from the university.
        </div>

        @if(!empty($headData['head_comments']))
            <table class="pf-item"><tr>
                <td class="pf-item__num">&nbsp;</td>
                <td>
                    <span class="pf-item__label">Remarks:</span>
                    <div class="pf-item__value--grow" style="min-height: 26px;">{{ $headData['head_comments'] }}</div>
                </td>
            </tr></table>
        @endif

        {{-- Head signature card --}}
        <table class="pf-item" style="margin-top: 4px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <div class="pf-sigcard">
                    <div class="pf-sigcard__head">Signature of Head of Department / Section / Unit</div>
                    <div class="pf-sigcard__body">
                        @if($headSig)
                            @php $img = $sigFsPath($headSig); @endphp
                            @if($img)
                                <img class="pf-sigcard__img" src="{{ $img }}" alt="Head signature">
                            @endif
                        @else
                            <div class="pf-sigcard__empty">— awaiting head's signature —</div>
                        @endif
                    </div>
                    <div class="pf-sigcard__meta">
                        @if($headSig)
                            @php $check = $headSig->verifyChain(); @endphp
                            Signed by <strong>{{ $signerName($headSig) }}</strong>
                            on <span class="pf-sigcard__date">{{ $headSig->signed_at?->format('d M Y, H:i') }}</span>
                            <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                                {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                            </span>
                        @else
                            Not yet signed.
                        @endif
                    </div>
                </div>
            </td>
        </tr></table>
    </div>

    {{-- =========================================================
         3. ENDORSEMENT BY INTERNAL AUDITOR
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <div class="pf-section__title"><span class="pf-num">3</span> Endorsement by Internal Auditor</div>

        <div class="pf-statement">
            I have inspected the above named <strong>{{ $vehicleNoun }}</strong> and all relevant documents and confirm that
            the vehicle belongs to the applicant. Payment of maintenance allowance is recommended.
        </div>

        @if(!empty($auditorData['audit_comments']))
            <table class="pf-item"><tr>
                <td class="pf-item__num">&nbsp;</td>
                <td>
                    <span class="pf-item__label">Inspection notes:</span>
                    <div class="pf-item__value--grow" style="min-height: 26px;">{{ $auditorData['audit_comments'] }}</div>
                </td>
            </tr></table>
        @endif

        {{-- Auditor signature card --}}
        <table class="pf-item" style="margin-top: 4px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <div class="pf-sigcard">
                    <div class="pf-sigcard__head">Signature of Internal Auditor</div>
                    <div class="pf-sigcard__body">
                        @if($auditorSig)
                            @php $img = $sigFsPath($auditorSig); @endphp
                            @if($img)
                                <img class="pf-sigcard__img" src="{{ $img }}" alt="Auditor signature">
                            @endif
                        @else
                            <div class="pf-sigcard__empty">— awaiting Internal Auditor's signature —</div>
                        @endif
                    </div>
                    <div class="pf-sigcard__meta">
                        @if($auditorSig)
                            @php $check = $auditorSig->verifyChain(); @endphp
                            Signed by <strong>{{ $signerName($auditorSig) }}</strong>
                            on <span class="pf-sigcard__date">{{ $auditorSig->signed_at?->format('d M Y, H:i') }}</span>
                            <span class="pf-sigcard__badge {{ $check['valid'] ? 'pf-sigcard__badge--ok' : 'pf-sigcard__badge--bad' }}">
                                {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                            </span>
                        @else
                            Not yet signed.
                        @endif
                    </div>
                </div>
            </td>
        </tr></table>
    </div>

    {{-- =========================================================
         4. APPROVAL BY REGISTRAR
         ========================================================= --}}
    <div class="pf-section pf-section--bordered">
        <div class="pf-section__title"><span class="pf-num">4</span> Approval by Registrar</div>

        <div class="pf-statement">
            Based on 2 and 3 above, the payment of <strong>{{ $vehicleNoun }}</strong> maintenance allowance is
            approved to take effect from
            <span class="pf-statement__name">{{ $fmtDate($registrarData['effective_from'] ?? null) ?: '____________' }}</span>.
        </div>

        @if(!empty($registrarData['registrar_comments']))
            <table class="pf-item"><tr>
                <td class="pf-item__num">&nbsp;</td>
                <td>
                    <span class="pf-item__label">Comments:</span>
                    <div class="pf-item__value--grow" style="min-height: 26px;">{{ $registrarData['registrar_comments'] }}</div>
                </td>
            </tr></table>
        @endif

        {{-- Registrar signature card --}}
        <table class="pf-item" style="margin-top: 4px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
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
            </td>
        </tr></table>
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
