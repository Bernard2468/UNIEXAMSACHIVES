{{--
    Visual stage stepper.
    - $definition       : App\Forms\BaseFormDefinition
    - $currentStage     : current FormStage (null if completed)
    - $completedStages  : array of stage slugs already signed
--}}
@php
    $completedStages = $completedStages ?? [];
    $currentSlug = $currentStage->slug ?? null;
@endphp

<div class="stage-stepper">
    @foreach($definition->stages() as $i => $stage)
        @php
            $isDone    = in_array($stage->slug, $completedStages, true);
            $isCurrent = $currentSlug === $stage->slug;
            $cls = 'stage-step';
            if ($isDone)    $cls .= ' stage-step--done';
            if ($isCurrent) $cls .= ' stage-step--active';
            if ($stage->optional && !$isDone && !$isCurrent) {
                $cls .= ' stage-step--optional';
            }
        @endphp
        <div class="{{ $cls }}">
            <span class="stage-step__dot">
                @if($isDone)
                    <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                @else
                    {{ $i + 1 }}
                @endif
            </span>
            <span class="stage-step__label">
                {{ $stage->label }}
                @if($stage->optional)<small style="color:#b0b5c0; font-weight:500; margin-left:4px;">(opt.)</small>@endif
            </span>
        </div>
    @endforeach
</div>
