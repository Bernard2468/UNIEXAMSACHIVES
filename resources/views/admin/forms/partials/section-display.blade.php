{{--
    Read-only display of a signed/locked section.
    - $stage       : App\Forms\FormStage
    - $sectionData : array of saved values keyed by field name
    - $signature   : App\Models\FormSignature|null
    - $signer      : App\Models\User|null
--}}
@php
    use App\Forms\FormField;
    $sectionData = $sectionData ?? [];
@endphp

<div class="form-panel form-panel--locked">
    <div class="form-panel__head">
        <div style="display: flex; align-items: flex-start; gap: 14px;">
            <span class="form-panel__lockicon">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
            </span>
            <div>
                <h2 class="form-panel__title">{{ $stage->label }}<span class="form-panel__title-bar"></span></h2>
                @if($signer)
                    <p class="form-panel__desc">
                        Signed by <strong>{{ trim(($signer->first_name ?? '') . ' ' . ($signer->last_name ?? '')) }}</strong>
                        @if(isset($signature) && $signature)
                            on {{ optional($signature->signed_at)->format('d M Y, H:i') }}
                        @endif
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="form-panel__body">
        <dl class="locked-fields">
            @foreach($stage->fields as $field)
                @if($field->type === FormField::TYPE_HEADING) @continue @endif
                @php $raw = $sectionData[$field->name] ?? null; @endphp
                @if($raw === null || $raw === '') @continue @endif

                <div class="locked-fields__row">
                    <dt>{{ $field->label }}</dt>
                    <dd>
                        @switch($field->type)
                            @case(FormField::TYPE_CHECKBOX)
                                {{ !empty($raw) ? 'Yes' : 'No' }}
                                @break
                            @case(FormField::TYPE_CURRENCY)
                                GhS {{ number_format((float) $raw, 2) }}
                                @break
                            @case(FormField::TYPE_RADIO)
                            @case(FormField::TYPE_SELECT)
                                {{ $field->options[$raw] ?? $raw }}
                                @break
                            @default
                                {!! nl2br(e($raw)) !!}
                        @endswitch
                    </dd>
                </div>
            @endforeach
        </dl>

        @if(isset($signature) && $signature)
            @php $check = $signature->verifyChain(); @endphp
            <div class="locked-signature">
                <div class="locked-signature__label">
                    Signature
                    @if($check['valid'])
                        <span class="locked-signature__badge locked-signature__badge--ok" title="Hash chain reconstructs from stored data.">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                            Verified
                        </span>
                    @else
                        <span class="locked-signature__badge locked-signature__badge--bad" title="{{ $check['reason'] }}">
                            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="8" x2="12" y2="13"/><line x1="12" y1="16" x2="12.01" y2="16"/><circle cx="12" cy="12" r="10"/></svg>
                            Chain mismatch
                        </span>
                    @endif
                </div>
                @if($signature->image_url)
                    <img src="{{ $signature->image_url }}" alt="Signature" class="locked-signature__img"
                         onerror="this.outerHTML='<div class=&quot;locked-signature__broken&quot;>Signature image unavailable. Run <code>php artisan storage:link</code> on the server.</div>';">
                @else
                    <div class="locked-signature__broken">No signature image was captured for this stage.</div>
                @endif
                <div class="locked-signature__meta">
                    SHA-256: <code>{{ substr($signature->chain_hash, 0, 12) }}…</code>
                    · {{ $signature->provider ?? 'in_app' }}
                    @if($signature->ip_address) · IP {{ $signature->ip_address }} @endif
                </div>
            </div>
        @endif
    </div>
</div>
