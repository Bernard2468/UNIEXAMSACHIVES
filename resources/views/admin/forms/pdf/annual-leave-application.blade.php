{{--
    Annual Leave Application Form — paper-form-faithful PDF.
    Stage data keys (must match AnnualLeaveApplicationForm):
      officer:  name, rank, faculty_department,
                last_leave_from, last_leave_to,
                current_entitlement, accrued_days, total_entitlement,
                proposed_days, proposed_from, proposed_to,
                purpose, resumption_date, deferred_days,
                address, phone, email
      recommender: recommendation
      registrar:   registrar_comments
--}}
@php
    use App\Forms\FormField;

    $officerData     = $submission->sectionData('officer');
    $recommenderData = $submission->sectionData('recommender');
    $registrarData   = $submission->sectionData('registrar');

    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
    $officerSig       = $signaturesByStage->get('officer')?->last();
    $recommenderSig   = $signaturesByStage->get('recommender')?->last();
    $registrarSig     = $signaturesByStage->get('registrar')?->last();

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
    <title>AL - {{ $submission->reference }}</title>
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

        /* ── Numbered items ── */
        /* Real HTML tables — dompdf renders these reliably, unlike display:table CSS. */
        .pf-item { width: 100%; margin: 0 0 10px; border-collapse: collapse; }
        .pf-item td { vertical-align: top; padding: 0; }
        .pf-item__num { width: 24px; font-weight: 700; font-size: 12px; padding-right: 6px !important; }
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

        /* Inline pair: "From: ___ To: ___" */
        .pf-pair__label { font-weight: 600; color: #1f2937; margin-right: 4px; }

        /* ── Signature block (items 13 / 14 / 15) ──
           Layout: label on top, then a box whose bottom border IS the signature
           line (with the image inside the box), then a small caption under it
           with the signer's name + verification badge. Date column mirrors the
           same height so the two columns align cleanly. */
        .pf-sig { width: 100%; margin-top: 6px; border-collapse: collapse; }
        .pf-sig td { vertical-align: top; padding: 0; }
        .pf-sig__left  { width: 60%; padding-right: 18px !important; }
        .pf-sig__right { width: 40%; }
        .pf-sig__label { font-weight: 600; color: #1f2937; display: block; margin-bottom: 4px; }
        .pf-sig__box {
            border-bottom: 1.2px solid #111827;
            min-height: 50px;
            padding: 4px 6px 2px;
            text-align: left;
        }
        .pf-sig__box--center { text-align: center; padding-top: 24px; font-weight: 600; font-size: 12px; }
        .pf-sig-img { max-height: 42px; max-width: 100%; }
        .pf-sig-caption { font-size: 9.5px; color: #6b7280; margin-top: 3px; line-height: 1.4; }
        .pf-sig-caption strong { color: #111827; font-weight: 700; }
        .pf-sig-badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8.5px; font-weight: bold; margin-left: 4px; vertical-align: middle; }
        .pf-sig-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .pf-sig-badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .pf-sig-pending { color: #9ca3af; font-style: italic; font-size: 10px; margin-top: 18px; }

        /* ── Section break for items 14 / 15 ── */
        .pf-section { margin-top: 16px; padding-top: 10px; border-top: 1px solid #d1d5db; }

        /* ── Closing instruction ── */
        .pf-instruction { margin-top: 16px; font-style: italic; font-size: 10.5px; color: #1f2937; padding: 8px 10px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 3px; line-height: 1.5; }
        .pf-instruction strong { font-style: normal; font-weight: 700; }

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
        <h2>ANNUAL LEAVE APPLICATION FORM</h2>
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
            <span class="pf-item__value" style="width: 24%;">{{ $fmtDate($officerData['last_leave_from'] ?? null) }}</span>
            <span class="pf-pair__label" style="margin-left: 8px;">To:</span>
            <span class="pf-item__value" style="width: 24%;">{{ $fmtDate($officerData['last_leave_to'] ?? null) }}</span>
        </td>
    </tr></table>

    {{-- ===== 5. Current Academic Year's Leave Entitlement ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">5.</td>
        <td>
            <span class="pf-item__label">Current Academic Year's Leave Entitlement:</span>
            <span class="pf-item__value" style="width: 48%;">{{ $officerData['current_entitlement'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 6. Accrued/Outstanding Leave Days ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">6.</td>
        <td>
            <span class="pf-item__label">Accrued/Outstanding Leave Days:</span>
            <span class="pf-item__value" style="width: 58%;">{{ $officerData['accrued_days'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 7. Total Leave Entitlement ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">7.</td>
        <td>
            <span class="pf-item__label">Total Leave Entitlement:</span>
            <span class="pf-item__value" style="width: 70%;">{{ $officerData['total_entitlement'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 8. Proposed Leave Days + sub-line From/To ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">8.</td>
        <td>
            <span class="pf-item__label">Proposed Leave Days:</span>
            <span class="pf-item__value" style="width: 72%;">{{ $officerData['proposed_days'] ?? '' }}</span>
        </td>
    </tr></table>
    {{-- Sub-line: From / To — indents under item 8's body column via empty num cell --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">&nbsp;</td>
        <td>
            <span class="pf-pair__label">From:</span>
            <span class="pf-item__value" style="width: 34%;">{{ $fmtDate($officerData['proposed_from'] ?? null) }}</span>
            <span class="pf-pair__label" style="margin-left: 20px;">To:</span>
            <span class="pf-item__value" style="width: 34%;">{{ $fmtDate($officerData['proposed_to'] ?? null) }}</span>
        </td>
    </tr></table>

    {{-- ===== 9. Purpose of taking the Leave ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">9.</td>
        <td>
            <span class="pf-item__label">Purpose of taking the Leave:</span>
            <div class="pf-item__value--grow">{{ $officerData['purpose'] ?? '' }}</div>
        </td>
    </tr></table>

    {{-- ===== 10. Date of Resumption of Duty ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">10.</td>
        <td>
            <span class="pf-item__label">Date of Resumption of Duty:</span>
            <span class="pf-item__value" style="width: 63%;">{{ $fmtDate($officerData['resumption_date'] ?? null) }}</span>
        </td>
    </tr></table>

    {{-- ===== 11. Total Deferred/Outstanding Days ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">11.</td>
        <td>
            <span class="pf-item__label">Total Deferred/Outstanding Days:</span>
            <span class="pf-item__value" style="width: 58%;">{{ $officerData['deferred_days'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 12. Address(es) / Phone / Email — three sub-lines ===== --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">12.</td>
        <td>
            <span class="pf-item__label">Address (es):</span>
            <span class="pf-item__value" style="width: 78%;">{{ $officerData['address'] ?? '' }}</span>
        </td>
    </tr></table>
    {{-- Sub-line: Telephone — indents under item 12's body column --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">&nbsp;</td>
        <td>
            <span class="pf-pair__label">Telephone Number:</span>
            <span class="pf-item__value" style="width: 70%;">{{ $officerData['phone'] ?? '' }}</span>
        </td>
    </tr></table>
    {{-- Sub-line: E-mail --}}
    <table class="pf-item"><tr>
        <td class="pf-item__num">&nbsp;</td>
        <td>
            <span class="pf-pair__label">E-mail Address:</span>
            <span class="pf-item__value" style="width: 72%;">{{ $officerData['email'] ?? '' }}</span>
        </td>
    </tr></table>

    {{-- ===== 13. Signature of Officer / Date ===== --}}
    <table class="pf-item" style="margin-top: 14px;"><tr>
        <td class="pf-item__num">13.</td>
        <td>
            <table class="pf-sig"><tr>
                <td class="pf-sig__left">
                    <span class="pf-sig__label">Signature of Officer:</span>
                    <div class="pf-sig__box">
                        @if($officerSig)
                            @php $img = $sigFsPath($officerSig); @endphp
                            @if($img)
                                <img class="pf-sig-img" src="{{ $img }}" alt="Officer signature">
                            @endif
                        @endif
                    </div>
                    @if($officerSig)
                        @php $check = $officerSig->verifyChain(); @endphp
                        <div class="pf-sig-caption">
                            <strong>{{ $signerName($officerSig) }}</strong>
                            <span class="pf-sig-badge {{ $check['valid'] ? 'pf-sig-badge--ok' : 'pf-sig-badge--bad' }}">
                                {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                            </span>
                        </div>
                    @else
                        <div class="pf-sig-pending">— awaiting officer signature —</div>
                    @endif
                </td>
                <td class="pf-sig__right">
                    <span class="pf-sig__label">Date:</span>
                    <div class="pf-sig__box pf-sig__box--center">
                        {{ $officerSig?->signed_at?->format('d M Y') }}
                    </div>
                </td>
            </tr></table>
        </td>
    </tr></table>

    {{-- ===== 14. Recommendation by Dean/HOD/Director or Office head ===== --}}
    <div class="pf-section">
        <table class="pf-item"><tr>
            <td class="pf-item__num">14.</td>
            <td>
                <span class="pf-item__label">Recommendation/Comment(s) by Dean/Head of Dept./Unit:</span>
                <div class="pf-item__value--grow" style="min-height: 46px;">{{ $recommenderData['recommendation'] ?? '' }}</div>
            </td>
        </tr></table>

        {{-- Name line — kept as a sub-row inside the body column --}}
        <table class="pf-item"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <span class="pf-item__label">Name of Dean/Head of Dept./Unit:</span>
                <span class="pf-item__value" style="width: 60%;">{{ $signerName($recommenderSig) }}</span>
            </td>
        </tr></table>

        {{-- Signature + Date — same indent as the body column --}}
        <table class="pf-item" style="margin-top: 8px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <table class="pf-sig"><tr>
                    <td class="pf-sig__left">
                        <span class="pf-sig__label">Signature:</span>
                        <div class="pf-sig__box">
                            @if($recommenderSig)
                                @php $img = $sigFsPath($recommenderSig); @endphp
                                @if($img)
                                    <img class="pf-sig-img" src="{{ $img }}" alt="Recommender signature">
                                @endif
                            @endif
                        </div>
                        @if($recommenderSig)
                            @php $check = $recommenderSig->verifyChain(); @endphp
                            <div class="pf-sig-caption">
                                <strong>{{ $signerName($recommenderSig) }}</strong>
                                <span class="pf-sig-badge {{ $check['valid'] ? 'pf-sig-badge--ok' : 'pf-sig-badge--bad' }}">
                                    {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                                </span>
                            </div>
                        @else
                            <div class="pf-sig-pending">— awaiting recommender signature —</div>
                        @endif
                    </td>
                    <td class="pf-sig__right">
                        <span class="pf-sig__label">Date:</span>
                        <div class="pf-sig__box pf-sig__box--center">
                            {{ $recommenderSig?->signed_at?->format('d M Y') }}
                        </div>
                    </td>
                </tr></table>
            </td>
        </tr></table>
    </div>

    {{-- ===== 15. Approval by Registrar ===== --}}
    <div class="pf-section">
        <table class="pf-item"><tr>
            <td class="pf-item__num">15.</td>
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

        {{-- Signature + Date for the Registrar — same indent as the body column --}}
        <table class="pf-item" style="margin-top: 8px;"><tr>
            <td class="pf-item__num">&nbsp;</td>
            <td>
                <table class="pf-sig"><tr>
                    <td class="pf-sig__left">
                        <span class="pf-sig__label">Signature:</span>
                        <div class="pf-sig__box">
                            @if($registrarSig)
                                @php $img = $sigFsPath($registrarSig); @endphp
                                @if($img)
                                    <img class="pf-sig-img" src="{{ $img }}" alt="Registrar signature">
                                @endif
                            @endif
                        </div>
                        @if($registrarSig)
                            @php $check = $registrarSig->verifyChain(); @endphp
                            <div class="pf-sig-caption">
                                <strong>{{ $signerName($registrarSig) }}</strong>
                                <span class="pf-sig-badge {{ $check['valid'] ? 'pf-sig-badge--ok' : 'pf-sig-badge--bad' }}">
                                    {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                                </span>
                            </div>
                        @else
                            <div class="pf-sig-pending">— awaiting Registrar's signature —</div>
                        @endif
                    </td>
                    <td class="pf-sig__right">
                        <span class="pf-sig__label">Date:</span>
                        <div class="pf-sig__box pf-sig__box--center">
                            {{ $registrarSig?->signed_at?->format('d M Y') }}
                        </div>
                    </td>
                </tr></table>
            </td>
        </tr></table>
    </div>

    {{-- ===== Closing instruction (mirrors paper form) ===== --}}
    <div class="pf-instruction">
        <strong>Instruction to Head, Human Resource Unit:</strong>
        Please deduct approved days taken or add approved deferred days to his/her outstanding leave days.
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
