@extends('mails.layouts.cug')

@section('title', 'Now complete — ' . $submission->reference)

@section('content')
    <!-- Primary card -->
    <div class="card">
        <div class="eyebrow">{{ $submission->form_code }} · Complete</div>
        <h1 class="headline">{{ $headline }}</h1>
        <p class="subline">Completed {{ optional($submission->completed_at ?? $submission->updated_at)->format('M j, Y') }}</p>

        <span class="status-row is-green">Completed</span>

        <hr class="divider">

        <table class="kv">
            <tr>
                <td class="k">Reference</td>
                <td class="v">{{ $submission->reference }}</td>
            </tr>
            <tr>
                <td class="k">Form</td>
                <td class="v">{{ $submission->form_code }} — {{ optional($submission->definition())->title() ?? $submission->form_code }}</td>
            </tr>
            @if($submission->requisition_amount)
                <tr>
                    <td class="k">Amount</td>
                    <td class="v">GhS {{ number_format($submission->requisition_amount, 2) }}</td>
                </tr>
            @endif
        </table>
    </div>

    <!-- Message card -->
    <div class="card">
        <h2 class="section-title">No further action needed.</h2>
        <p class="section-sub">{{ $bodyMessage }}</p>

        <div class="cta-wrap">
            <a href="{{ $showUrl }}" class="cta">Open the form &rarr;</a>
        </div>
    </div>
@endsection

@section('footnote')
    You're receiving this email because you signed a stage<br>
    of this form in the institution's forms workflow.
@endsection
