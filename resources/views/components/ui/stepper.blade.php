@props([
    'steps' => [],
    'currentStep' => 1
])

<div class="stepper-wrapper mb-4">
    @foreach($steps as $index => $stepTitle)
        @php
            $stepNum = $index + 1;
            $isActive = $stepNum === (int) $currentStep;
            $isCompleted = $stepNum < (int) $currentStep;
            $statusClass = $isCompleted ? 'completed' : ($isActive ? 'active' : '');
        @endphp
        
        <div class="stepper-item {{ $statusClass }}">
            <div class="step-number">
                @if($isCompleted)
                    <i class="bi bi-check-lg"></i>
                @else
                    {{ $stepNum }}
                @endif
            </div>
            <div class="step-title d-none d-md-block">{{ $stepTitle }}</div>
        </div>

        @if(!$loop->last)
            <div class="stepper-line {{ $isCompleted ? 'completed' : '' }}"></div>
        @endif
    @endforeach
</div>
