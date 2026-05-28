{{--
    Leave Resumption Form — paper-form-faithful PDF.
    Stage data keys (must match LeaveResumptionForm):
      officer:     name, rank, faculty_department,
                   last_leave_from, last_leave_to,
                   resumption_date, deferred_leave_days
      recommender: recommender_comment
      hr:          hr_comment
      registrar:   registrar_comments
--}}
@php
    $officerData     = $submission->sectionData('officer');
    $recommenderData = $submission->sectionData('recommender');
    $hrData          = $submission->sectionData('hr');
    $registrarData   = $submission->sectionData('registrar');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
    $officerSig     = $signaturesByStage->get('officer')?->last();
    $recommenderSig = $signaturesByStage->get('recommender')?->last();
    $hrSig          = $signaturesByStage->get('hr')?->last();
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

        /* ── Header (centred, like the paper form) ── */
        .pf-head { text-align: center; margin-bottom: 14px; }
        .pf-head h1 { font-size: 16px; font-weight: 800; letter-spacing: 0.5px; }
        .pf-head .pf-subhead { font-style: italic; font-weight: 700; font-size: 12px; margin-top: 2px; }
        .pf-head__logo { display: block; margin: 6px auto 4px; height: 70px; width: auto; }
        .pf-head h2 { font-size: 15px; font-weight: 800; margin-top: 4px; text-decoration: underline; letter-spacing: 0.3px; }
        .pf-head .pf-rubric { font-style: italic; font-size: 10.5px; color: #374151; margin-top: 4px; }

        /* ── Reference strip ── */
        .pf-meta { margin: 10px 0 14px; padding: 6px 10px; background: #f3f4f6; border-radius: 4px; font-size: 10px; color: #374151; }
        .pf-meta span { margin-right: 14px; }
        .pf-meta strong { color: #111827; }

        /* ── Numbered items (real HTML tables — dompdf-reliable) ── */
        .pf-item { width: 100%; margin: 0 0 10px; border-collapse: collapse; }
        .pf-item td { vertical-align: top; padding: 0; }
        .pf-item__num { width: 26px; font-weight: 700; font-size: 12px; padding-right: 6px !important; }
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
        .pf-pair__label { font-weight: 600; color: #1f2937; margin-right: 4px; }

        /* ── Section break ── */
        .pf-section { margin-top: 16px; padding-top: 10px; border-top: 1px solid #d1d5db; }

        /* ── Signature card (matches the leave forms) ── */
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

    {{-- ===== Header (matches paper form) ===== --}}
    <div class="pf-head">
        <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE - SUNYANI</h1>
        <div class="pf-subhead">[Office of the Registrar]</div>
        @if(file_exists($logoFsPath))
            <img class="pf-head__logo" src="{{ $logoFsPath }}" alt="CUG Logo">
        @endif
        <h2>{{ strtoupper($definition->title()) }}</h2>
        <div class="pf-rubric">[This Form must be completed and submitted to the Registrar's Office]</div>
    </div>

    {{-- Reference strip --}}
    <div class="pf-meta">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    {{-- ===== 1. Name of Officer ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">1.</td>
        <td>
            <span class="pf-item__label">Name of Officer:</span>
            <span class="pf-item__value" style="width: 78%;">{{ $officerData['name'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 2. Rank ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">2.</td>
        <td>
            <span class="pf-item__label">Rank:</span>
            <span class="pf-item__value" style="width: 86%;">{{ $officerData['rank'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 3. Faculty/School/Department/Unit ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">3.</td>
        <td>
            <span class="pf-item__label">Faculty/School/Department/Unit:</span>
            <span class="pf-item__value" style="width: 60%;">{{ $officerData['faculty_department'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 4. Date(s) of Last Leave: From ___ To ___ ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">4.</td>
        <td>
            <span class="pf-item__label">Date(s) of Last Leave:</span>
            <span class="pf-pair__label" style="margin-left: 4px;">From</span>
            <span class="pf-item__value" style="width: 26%;">{{ $fmtDate($officerData['last_leave_from'] ?? null) }}</span>
            <span class="pf-pair__label" style="margin-left: 10px;">To</span>
            <span class="pf-item__value" style="width: 26%;">{{ $fmtDate($officerData['last_leave_to'] ?? null) }}</span>
        </td>
    </tr></table>

    {{-- ===== 5. Date of Resumption of Duty ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">5.</td>
        <td>
            <span class="pf-item__label">Date of Resumption of Duty:</span>
            <span class="pf-item__value" style="width: 63%;">{{ $fmtDate($officerData['resumption_date'] ?? null) }}</span>
        </td>
    </tr></table>

    {{-- ===== 6. Total Deferred/Outstanding Leave Days ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">6.</td>
        <td>
            <span class="pf-item__label">Total Deferred/Outstanding Leave Days:</span>
            <span class="pf-item__value" style="width: 54%;">{{ $officerData['deferred_leave_days'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 7. Signature of Officer ===== --}}
    <table class="pf-item" style="margin-top: 14px;"><tr>
        <td class="pf-item__num">7.</td>
        <td>
            <div class="pf-sigcard">
                <div class="pf-sigcard__head">Signature of Officer</div>
                <div class="pf-sigcard__body">
                    @if($officerSig)
                        @php $img = $sigFsPath($officerSig); @endphp
                        @if($img)
                            <img class="pf-sigcard__img" src="{{ $img }}" alt="Officer signature">
                        @endif
                    @else
                        <div class="pf-sigcard__empty">— awaiting officer signature —</div>
                    @endif
                </div>
                <div class="pf-sigcard__meta">
                    @if($officerSig)
                        @php $check = $officerSig->verifyChain(); @endphp
                        Signed by <strong>{{ $signerName($officerSig) }}</strong>
                        on <span class="pf-sigcard__date">{{ $officerSig->signed_at?->format('d M Y, H:i') }}</span>
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

    {{-- ===== 8. Comment by Dean/HOD/Director/Office head ===== --}}
    <div class="pf-section">
        <table class="pf-item"><tr>
            <td class="pf-item__num">8.</td>
            <td>
                <span class="pf-item__label">Comment(s) by Dean/Head of Department/Supervisor, as to whether the staff reported on the expected resumption date or not:</span>
                <div class="pf-item__value--grow" style="min-height: 50px;">{{ $recommenderData['recommender_comment'] ?? '' }}</div>
            </td>
        </tr></table>

        {{-- Name line — sub-row indented under body --}}
        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Name of Dean/Head of Department/Unit:</span>
                <span class="pf-item__value" style="width: 58%;">{{ $signerName($recommenderSig) }}</span>
            </td>
        </tr></table>

        {{-- Recommender signature card --}}
        <table class="pf-item" style="margin-top: 4px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <div class="pf-sigcard">
                    <div class="pf-sigcard__head">Signature of Dean / HOD / Director / Office Head</div>
                    <div class="pf-sigcard__body">
                        @if($recommenderSig)
                            @php $img = $sigFsPath($recommenderSig); @endphp
                            @if($img)
                                <img class="pf-sigcard__img" src="{{ $img }}" alt="Recommender signature">
                            @endif
                        @else
                            <div class="pf-sigcard__empty">— awaiting recommender signature —</div>
                        @endif
                    </div>
                    <div class="pf-sigcard__meta">
                        @if($recommenderSig)
                            @php $check = $recommenderSig->verifyChain(); @endphp
                            Signed by <strong>{{ $signerName($recommenderSig) }}</strong>
                            on <span class="pf-sigcard__date">{{ $recommenderSig->signed_at?->format('d M Y, H:i') }}</span>
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

    {{-- ===== 9. Head of HR Unit — vetting note ===== --}}
    <div class="pf-section">
        <table class="pf-item"><tr>
            <td class="pf-item__num">9.</td>
            <td>
                <span class="pf-item__label">Head of Human Resource Unit:</span>
                <span style="font-style: italic; color: #4b5563; margin-left: 4px;">
                    Please, vet the resumption date and the number of leave days outstanding for further action.
                </span>
                <div class="pf-item__value--grow" style="min-height: 50px;">{{ $hrData['hr_comment'] ?? '' }}</div>
            </td>
        </tr></table>

        {{-- HR signature card --}}
        <table class="pf-item" style="margin-top: 4px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <div class="pf-sigcard">
                    <div class="pf-sigcard__head">Signature of Head of Human Resource Unit</div>
                    <div class="pf-sigcard__body">
                        @if($hrSig)
                            @php $img = $sigFsPath($hrSig); @endphp
                            @if($img)
                                <img class="pf-sigcard__img" src="{{ $img }}" alt="HR signature">
                            @endif
                        @else
                            <div class="pf-sigcard__empty">— awaiting Head of HR signature —</div>
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
            </td>
        </tr></table>
    </div>

    {{-- ===== 10. Approval by Registrar ===== --}}
    <div class="pf-section">
        <table class="pf-item"><tr>
            <td class="pf-item__num">10.</td>
            <td>
                <span class="pf-item__label">Approved by Registrar:</span>
                <span class="pf-pair__label" style="margin-left: 4px;">Name:</span>
                <span class="pf-item__value" style="width: 58%;">{{ $signerName($registrarSig) }}</span>
            </td>
        </tr></table>

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
