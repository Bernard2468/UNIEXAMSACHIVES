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
        .pf-item { display: table; width: 100%; margin: 0 0 6px; padding: 0; table-layout: fixed; }
        .pf-item__num { display: table-cell; width: 22px; padding-right: 4px; font-weight: 700; vertical-align: top; font-size: 11.5px; }
        .pf-item__body { display: table-cell; vertical-align: top; }
        .pf-item__label { font-weight: 600; color: #1f2937; }
        .pf-item__value {
            display: inline-block;
            border-bottom: 1px dotted #6b7280;
            padding: 0 4px 1px;
            min-height: 13px;
            font-weight: 500;
            color: #0c0c0c;
        }
        .pf-item__value--grow { display: block; margin-top: 2px; min-height: 28px; padding: 4px 6px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 3px; font-weight: 500; }

        /* Inline pair: "From: ___ To: ___" */
        .pf-pair { margin-top: 1px; }
        .pf-pair__label { font-weight: 600; color: #1f2937; margin-right: 4px; }
        .pf-pair__label--indent { margin-left: 28px; }

        /* Sub-line under item 8 (From / To) — indented under the numbered item */
        .pf-subline { margin: 2px 0 6px 26px; }

        /* ── Signature ── */
        .pf-sig-row { display: table; width: 100%; margin-top: 6px; }
        .pf-sig-cell { display: table-cell; vertical-align: bottom; padding-right: 14px; }
        .pf-sig-cell--right { padding-right: 0; }
        .pf-sig-img { max-height: 44px; max-width: 220px; display: block; }
        .pf-sig-line { border-bottom: 1px solid #111827; min-height: 28px; padding-bottom: 1px; }
        .pf-sig-caption { font-size: 9.5px; color: #6b7280; margin-top: 2px; }
        .pf-sig-caption strong { color: #111827; }
        .pf-sig-badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8.5px; font-weight: bold; margin-left: 4px; }
        .pf-sig-badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .pf-sig-badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .pf-sig-pending { color: #9ca3af; font-style: italic; font-size: 10px; }

        /* ── Section break for items 14 / 15 ── */
        .pf-section { margin-top: 10px; padding-top: 8px; border-top: 1px solid #d1d5db; }

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
    <div class="pf-item">
        <div class="pf-item__num">1.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Name of Officer:</span>
            <span class="pf-item__value" style="min-width: 70%;">{{ $officerData['name'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 2. Rank ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">2.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Rank:</span>
            <span class="pf-item__value" style="min-width: 75%;">{{ $officerData['rank'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 3. Faculty/School/Department/Unit ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">3.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Faculty/School/Department/Unit:</span>
            <span class="pf-item__value" style="min-width: 55%;">{{ $officerData['faculty_department'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 4. Date(s) of Last Leave: From ___ To ___ ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">4.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Date(s) of Last Leave:</span>
            <span class="pf-pair__label">From</span>
            <span class="pf-item__value" style="min-width: 22%;">{{ $fmtDate($officerData['last_leave_from'] ?? null) }}</span>
            <span class="pf-pair__label" style="margin-left: 8px;">To:</span>
            <span class="pf-item__value" style="min-width: 22%;">{{ $fmtDate($officerData['last_leave_to'] ?? null) }}</span>
        </div>
    </div>

    {{-- ===== 5. Current Academic Year's Leave Entitlement ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">5.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Current Academic Year's Leave Entitlement:</span>
            <span class="pf-item__value" style="min-width: 45%;">{{ $officerData['current_entitlement'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 6. Accrued/Outstanding Leave Days ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">6.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Accrued/Outstanding Leave Days:</span>
            <span class="pf-item__value" style="min-width: 55%;">{{ $officerData['accrued_days'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 7. Total Leave Entitlement ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">7.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Total Leave Entitlement:</span>
            <span class="pf-item__value" style="min-width: 65%;">{{ $officerData['total_entitlement'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 8. Proposed Leave Days + sub-line From/To ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">8.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Proposed Leave Days:</span>
            <span class="pf-item__value" style="min-width: 65%;">{{ $officerData['proposed_days'] ?? '' }}</span>
        </div>
    </div>
    <div class="pf-subline">
        <span class="pf-pair__label">From:</span>
        <span class="pf-item__value" style="min-width: 30%;">{{ $fmtDate($officerData['proposed_from'] ?? null) }}</span>
        <span class="pf-pair__label" style="margin-left: 14px;">To:</span>
        <span class="pf-item__value" style="min-width: 30%;">{{ $fmtDate($officerData['proposed_to'] ?? null) }}</span>
    </div>

    {{-- ===== 9. Purpose of taking the Leave ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">9.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Purpose of taking the Leave:</span>
            <div class="pf-item__value--grow">{{ $officerData['purpose'] ?? '' }}</div>
        </div>
    </div>

    {{-- ===== 10. Date of Resumption of Duty ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">10.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Date of Resumption of Duty:</span>
            <span class="pf-item__value" style="min-width: 60%;">{{ $fmtDate($officerData['resumption_date'] ?? null) }}</span>
        </div>
    </div>

    {{-- ===== 11. Total Deferred/Outstanding Days ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">11.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Total Deferred/Outstanding Days:</span>
            <span class="pf-item__value" style="min-width: 55%;">{{ $officerData['deferred_days'] ?? '' }}</span>
        </div>
    </div>

    {{-- ===== 12. Address(es) / Phone / Email — three sub-lines ===== --}}
    <div class="pf-item">
        <div class="pf-item__num">12.</div>
        <div class="pf-item__body">
            <span class="pf-item__label">Address (es):</span>
            <span class="pf-item__value" style="min-width: 70%;">{{ $officerData['address'] ?? '' }}</span>
        </div>
    </div>
    <div class="pf-subline">
        <span class="pf-pair__label">Telephone Number:</span>
        <span class="pf-item__value" style="min-width: 60%;">{{ $officerData['phone'] ?? '' }}</span>
    </div>
    <div class="pf-subline">
        <span class="pf-pair__label">E-mail Address:</span>
        <span class="pf-item__value" style="min-width: 60%;">{{ $officerData['email'] ?? '' }}</span>
    </div>

    {{-- ===== 13. Signature of Officer / Date ===== --}}
    <div class="pf-item" style="margin-top: 10px;">
        <div class="pf-item__num">13.</div>
        <div class="pf-item__body">
            <div class="pf-sig-row">
                <div class="pf-sig-cell" style="width: 60%;">
                    <span class="pf-item__label">Signature of Officer:</span>
                    @if($officerSig)
                        @php $img = $sigFsPath($officerSig); @endphp
                        @if($img)
                            <img class="pf-sig-img" src="{{ $img }}" alt="Officer signature">
                        @endif
                        <div class="pf-sig-line"></div>
                        <div class="pf-sig-caption">
                            <strong>{{ $signerName($officerSig) }}</strong>
                            @php $check = $officerSig->verifyChain(); @endphp
                            <span class="pf-sig-badge {{ $check['valid'] ? 'pf-sig-badge--ok' : 'pf-sig-badge--bad' }}">
                                {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                            </span>
                        </div>
                    @else
                        <div class="pf-sig-line"></div>
                        <div class="pf-sig-pending">— awaiting officer signature —</div>
                    @endif
                </div>
                <div class="pf-sig-cell pf-sig-cell--right" style="width: 40%;">
                    <span class="pf-item__label">Date:</span>
                    <div class="pf-sig-line" style="text-align: center; font-weight: 600;">
                        {{ $officerSig?->signed_at?->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== 14. Recommendation by Dean/HOD/Director or Office head ===== --}}
    <div class="pf-section">
        <div class="pf-item">
            <div class="pf-item__num">14.</div>
            <div class="pf-item__body">
                <span class="pf-item__label">Recommendation/Comment(s) by Dean/Head of Dept./Unit:</span>
                <div class="pf-item__value--grow" style="min-height: 46px;">{{ $recommenderData['recommendation'] ?? '' }}</div>
            </div>
        </div>

        <div style="margin-left: 26px; margin-top: 4px;">
            <div style="margin-bottom: 4px;">
                <span class="pf-item__label">Name of Dean/Head of Dept./Unit:</span>
                <span class="pf-item__value" style="min-width: 55%;">{{ $signerName($recommenderSig) }}</span>
            </div>
            <div class="pf-sig-row">
                <div class="pf-sig-cell" style="width: 60%;">
                    <span class="pf-item__label">Signature:</span>
                    @if($recommenderSig)
                        @php $img = $sigFsPath($recommenderSig); @endphp
                        @if($img)
                            <img class="pf-sig-img" src="{{ $img }}" alt="Recommender signature">
                        @endif
                        <div class="pf-sig-line"></div>
                        <div class="pf-sig-caption">
                            <strong>{{ $signerName($recommenderSig) }}</strong>
                            @php $check = $recommenderSig->verifyChain(); @endphp
                            <span class="pf-sig-badge {{ $check['valid'] ? 'pf-sig-badge--ok' : 'pf-sig-badge--bad' }}">
                                {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                            </span>
                        </div>
                    @else
                        <div class="pf-sig-line"></div>
                        <div class="pf-sig-pending">— awaiting recommender signature —</div>
                    @endif
                </div>
                <div class="pf-sig-cell pf-sig-cell--right" style="width: 40%;">
                    <span class="pf-item__label">Date:</span>
                    <div class="pf-sig-line" style="text-align: center; font-weight: 600;">
                        {{ $recommenderSig?->signed_at?->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== 15. Approval by Registrar ===== --}}
    <div class="pf-section">
        <div class="pf-item">
            <div class="pf-item__num">15.</div>
            <div class="pf-item__body">
                <span class="pf-item__label">Approved by Registrar:</span>
                <span class="pf-pair__label" style="margin-left: 4px;">Name:</span>
                <span class="pf-item__value" style="min-width: 55%;">{{ $signerName($registrarSig) }}</span>
            </div>
        </div>

        @if(!empty($registrarData['registrar_comments']))
            <div style="margin: 4px 0 6px 26px;">
                <span class="pf-item__label">Comments:</span>
                <div class="pf-item__value--grow" style="min-height: 24px;">{{ $registrarData['registrar_comments'] }}</div>
            </div>
        @endif

        <div style="margin-left: 26px;">
            <div class="pf-sig-row">
                <div class="pf-sig-cell" style="width: 60%;">
                    <span class="pf-item__label">Signature:</span>
                    @if($registrarSig)
                        @php $img = $sigFsPath($registrarSig); @endphp
                        @if($img)
                            <img class="pf-sig-img" src="{{ $img }}" alt="Registrar signature">
                        @endif
                        <div class="pf-sig-line"></div>
                        <div class="pf-sig-caption">
                            <strong>{{ $signerName($registrarSig) }}</strong>
                            @php $check = $registrarSig->verifyChain(); @endphp
                            <span class="pf-sig-badge {{ $check['valid'] ? 'pf-sig-badge--ok' : 'pf-sig-badge--bad' }}">
                                {{ $check['valid'] ? 'VERIFIED' : 'CHAIN MISMATCH' }}
                            </span>
                        </div>
                    @else
                        <div class="pf-sig-line"></div>
                        <div class="pf-sig-pending">— awaiting Registrar's signature —</div>
                    @endif
                </div>
                <div class="pf-sig-cell pf-sig-cell--right" style="width: 40%;">
                    <span class="pf-item__label">Date:</span>
                    <div class="pf-sig-line" style="text-align: center; font-weight: 600;">
                        {{ $registrarSig?->signed_at?->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
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
