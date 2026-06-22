{{-- Reusable "authorised by approved memo" seal for any form PDF.

     Self-contained inline styles (dompdf-safe, no flexbox) so it renders the
     same whether the host template uses the shared _layout, the leave layout,
     or one of the standalone per-form templates. Guarded — renders nothing
     unless the form was started from an approved memo. --}}
@if(!empty($submission) && $submission->sourceCampaign)
    @php
        $srcMemo  = $submission->sourceCampaign;
        $approver = $srcMemo->formUnlocker;
        $approverName = $approver
            ? trim(($approver->first_name ?? '') . ' ' . ($approver->last_name ?? '')) ?: ($approver->name ?? null)
            : null;
        $memoRefLabel = $srcMemo->reference ?? ('#' . $srcMemo->id);
        $approvedDate = $srcMemo->form_unlocked_at ? $srcMemo->form_unlocked_at->format('d M Y') : null;
    @endphp
    <div style="margin: 0 0 14px; padding: 7px 12px; background: #ecfdf5; border: 1px solid #a7f3d0; border-left: 4px solid #059669; border-radius: 4px; font-size: 10px; color: #065f46; line-height: 1.5;">
        <strong style="color:#065f46;">&#10003; Authorised by approved memo {{ $memoRefLabel }}</strong>
        <span style="display:inline-block; padding:1px 6px; border-radius:8px; font-size:8px; font-weight:bold; background:#059669; color:#fff; margin-left:4px; letter-spacing:0.4px;">APPROVED</span>
        @if($approverName) &nbsp;&middot;&nbsp; approved by {{ $approverName }} @endif
        @if($approvedDate) &nbsp;&middot;&nbsp; {{ $approvedDate }} @endif
    </div>
@endif
