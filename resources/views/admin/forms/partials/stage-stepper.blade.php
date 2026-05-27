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
            $isDone   = in_array($stage->slug, $completedStages, true);
            $isCurrent = $currentSlug === $stage->slug;
            $stateClass = $isDone ? 'stage-step--done' : ($isCurrent ? 'stage-step--current' : 'stage-step--pending');
            if ($stage->optional && !$isDone && !$isCurrent) {
                $stateClass .= ' stage-step--optional';
            }
        @endphp
        <div class="stage-step {{ $stateClass }}">
            <div class="stage-step__index">{{ $i + 1 }}</div>
            <div class="stage-step__body">
                <div class="stage-step__label">{{ $stage->label }}</div>
                @if($stage->optional)
                    <div class="stage-step__sub">Optional</div>
                @endif
            </div>
        </div>
        @if(!$loop->last)
            <div class="stage-step__divider {{ $isDone ? 'stage-step__divider--done' : '' }}"></div>
        @endif
    @endforeach
</div>

<style>
.stage-stepper { display: flex; align-items: center; gap: 4px; padding: 14px 18px; background: #fff; border: 1px solid #e5e7eb; border-radius: 12px; margin-bottom: 22px; overflow-x: auto; }
.stage-step { display: flex; align-items: center; gap: 10px; padding: 6px 12px; border-radius: 99px; flex-shrink: 0; }
.stage-step__index { display: inline-flex; align-items: center; justify-content: center; width: 28px; height: 28px; border-radius: 50%; font-weight: 600; font-size: 13px; }
.stage-step--pending .stage-step__index { background: #f3f4f6; color: #6b7280; }
.stage-step--current { background: #eff6ff; }
.stage-step--current .stage-step__index { background: #1d4ed8; color: #fff; box-shadow: 0 0 0 4px rgba(29,78,216,0.15); }
.stage-step--done .stage-step__index { background: #10b981; color: #fff; }
.stage-step--done .stage-step__index::before { content: '✓'; }
.stage-step--done .stage-step__index { font-size: 0; }
.stage-step--done .stage-step__index::before { font-size: 14px; }
.stage-step--optional { opacity: 0.55; }
.stage-step__body { font-size: 12.5px; line-height: 1.2; }
.stage-step__label { font-weight: 600; color: #111827; }
.stage-step__sub { font-size: 10px; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }
.stage-step__divider { flex: 0 0 24px; height: 2px; background: #e5e7eb; }
.stage-step__divider--done { background: #10b981; }
</style>
