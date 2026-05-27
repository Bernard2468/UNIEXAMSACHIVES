{{-- Shared PDF layout for any form. Uses dompdf-compatible HTML/CSS. --}}
@php
    use App\Forms\FormField;
    $signaturesByStage = $submission->signatures->groupBy('stage_slug');
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $submission->form_code }} - {{ $submission->reference }}</title>
    <style>
        @page { margin: 16mm 14mm; }
        body { font-family: 'DejaVu Sans', sans-serif; color: #111827; font-size: 11px; line-height: 1.45; }
        h1, h2, h3, h4, h5 { margin: 0; padding: 0; }
        .doc-header { text-align: center; margin-bottom: 18px; padding-bottom: 10px; border-bottom: 2px solid #111827; }
        .doc-header h1 { font-size: 16px; font-weight: 700; letter-spacing: 0.5px; }
        .doc-header h2 { font-size: 13px; font-weight: 700; margin-top: 4px; text-decoration: underline; }
        .doc-header .form-code { float: right; font-style: italic; font-weight: 700; }
        .meta-strip { margin-bottom: 14px; padding: 8px 12px; background: #f3f4f6; border-radius: 4px; font-size: 10.5px; }
        .meta-strip span { margin-right: 18px; }
        .meta-strip strong { color: #111827; }

        .section { border: 1.5px solid #111827; margin-bottom: 12px; page-break-inside: avoid; }
        .section__title { background: #f3f4f6; border-bottom: 1.5px solid #111827; padding: 6px 10px; font-weight: 700; font-size: 11.5px; text-transform: uppercase; letter-spacing: 0.4px; }
        .section__body { padding: 10px 12px; }
        .section--pending .section__body { color: #9ca3af; min-height: 60px; }
        .section--pending .section__body em { font-style: italic; }

        .field { margin-bottom: 6px; display: block; }
        .field__label { font-weight: 600; color: #374151; margin-right: 4px; }
        .field__value { border-bottom: 1px dotted #6b7280; padding: 0 4px; min-height: 14px; display: inline-block; min-width: 80px; }
        .field--block .field__value { display: block; width: 100%; min-height: 32px; margin-top: 4px; padding: 6px 8px; background: #fafafa; border: 1px solid #e5e7eb; border-radius: 3px; }

        .grid-2 { width: 100%; }
        .grid-2 td { padding: 4px 8px; vertical-align: top; width: 50%; }

        .signature-block { margin-top: 8px; padding-top: 6px; border-top: 1px dashed #6b7280; }
        .signature-block img { max-height: 50px; max-width: 220px; }
        .signature-block__meta { font-size: 9px; color: #6b7280; margin-top: 4px; }
        .signature-block__hash { font-family: 'Courier New', monospace; }
        .signature-block__badge { display: inline-block; padding: 1px 6px; border-radius: 8px; font-size: 8px; font-weight: bold; margin-left: 4px; }
        .signature-block__badge--ok  { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .signature-block__badge--bad { background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }

        .doc-footer { margin-top: 20px; padding-top: 8px; border-top: 1px solid #d1d5db; font-size: 9px; color: #6b7280; }
        .audit-chain { font-family: 'Courier New', monospace; font-size: 8.5px; word-break: break-all; }
    </style>
</head>
<body>
    <div class="doc-header">
        <span class="form-code">FORM "{{ $submission->form_code }}"</span>
        <h1>CATHOLIC UNIVERSITY OF GHANA, FIAPRE – SUNYANI</h1>
        <h2>{{ strtoupper($definition->title()) }}</h2>
    </div>

    <div class="meta-strip">
        <span><strong>Reference:</strong> {{ $submission->reference }}</span>
        <span><strong>Status:</strong> {{ ucfirst(str_replace('_', ' ', $submission->status)) }}</span>
        @if($submission->submitted_at)<span><strong>Submitted:</strong> {{ $submission->submitted_at->format('d M Y, H:i') }}</span>@endif
        @if($submission->completed_at)<span><strong>Completed:</strong> {{ $submission->completed_at->format('d M Y, H:i') }}</span>@endif
    </div>

    @foreach($definition->stages() as $stage)
        @php
            $signedSig = $signaturesByStage->get($stage->slug)?->last();
            $isSigned  = (bool) $signedSig;
            $data      = $submission->sectionData($stage->slug);
            $isOptionalSkipped = $stage->optional && !$isSigned;
        @endphp

        @if($isOptionalSkipped) @continue @endif

        <div class="section {{ $isSigned ? 'section--signed' : 'section--pending' }}">
            <div class="section__title">{{ $stage->label }}</div>
            <div class="section__body">
                @if(!$isSigned)
                    <em>This section has not yet been completed.</em>
                @else
                    <table class="grid-2">
                        <tr>
                            @php $col = 0; @endphp
                            @foreach($stage->fields as $field)
                                @if($field->type === FormField::TYPE_HEADING) @continue @endif
                                @php $raw = $data[$field->name] ?? null; @endphp
                                @if($raw === null || $raw === '') @continue @endif

                                @if($col > 0 && $col % 2 === 0)
                                    </tr><tr>
                                @endif

                                <td>
                                    <div class="field {{ in_array($field->type, [FormField::TYPE_TEXTAREA], true) ? 'field--block' : '' }}">
                                        <span class="field__label">{{ $field->label }}:</span>
                                        <span class="field__value">
                                            @switch($field->type)
                                                @case(FormField::TYPE_CHECKBOX)
                                                    {{ !empty($raw) ? '☑ Yes' : '☐ No' }}
                                                    @break
                                                @case(FormField::TYPE_CURRENCY)
                                                    GhS {{ number_format((float) $raw, 2) }}
                                                    @break
                                                @case(FormField::TYPE_RADIO)
                                                @case(FormField::TYPE_SELECT)
                                                    {{ $field->options[$raw] ?? $raw }}
                                                    @break
                                                @default
                                                    {{ $raw }}
                                            @endswitch
                                        </span>
                                    </div>
                                </td>
                                @php
                                    $col++;
                                    if (in_array($field->type, [FormField::TYPE_TEXTAREA], true)) {
                                        $col++;
                                        if ($col % 2 !== 0) {
                                            echo '<td>&nbsp;</td>';
                                            $col++;
                                        }
                                    }
                                @endphp
                            @endforeach
                            @if($col % 2 !== 0)<td>&nbsp;</td>@endif
                        </tr>
                    </table>

                    @if($signedSig)
                        @php
                            $sigCheck = $signedSig->verifyChain();
                            // Read directly from storage/app/public so the PDF works on
                            // shared hosts where the public/storage symlink can't be created.
                            $sigFsPath = $signedSig->signature_image_path
                                ? storage_path('app/public/' . ltrim($signedSig->signature_image_path, '/'))
                                : null;
                        @endphp
                        <div class="signature-block">
                            @if($sigFsPath && file_exists($sigFsPath))
                                <img src="{{ $sigFsPath }}" alt="Signature">
                            @endif
                            <div class="signature-block__meta">
                                Signed by <strong>{{ trim((optional($signedSig->user)->first_name ?? '') . ' ' . (optional($signedSig->user)->last_name ?? '')) }}</strong>
                                on {{ $signedSig->signed_at?->format('d M Y, H:i') }}
                                @if($signedSig->ip_address) · IP {{ $signedSig->ip_address }} @endif
                                @if($sigCheck['valid'])
                                    <span class="signature-block__badge signature-block__badge--ok">VERIFIED</span>
                                @else
                                    <span class="signature-block__badge signature-block__badge--bad">CHAIN MISMATCH ({{ $sigCheck['reason'] }})</span>
                                @endif
                                <br>
                                <span class="signature-block__hash">Chain hash: {{ $signedSig->chain_hash }}</span>
                            </div>
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach

    <div class="doc-footer">
        Generated {{ now()->format('d M Y, H:i') }} · Reference {{ $submission->reference }}
        @if($submission->signatures->isNotEmpty())
            · Tamper-evident audit chain present ({{ $submission->signatures->count() }} signatures)
        @endif
    </div>
</body>
</html>
