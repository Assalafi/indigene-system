<div class="wizard-steps" aria-label="Application progress">
    @foreach (\App\Http\Controllers\ApplicationWizardController::STEP_NAMES as $number => $name)
        @php
            $stateClass = '';
            if ($number < $step) { $stateClass = 'complete'; }
            if ($number === (int) $step) { $stateClass = 'current'; }
        @endphp
        <a class="wizard-step {{ $stateClass }}" href="{{ route('applications.wizard', ['application' => $application, 'step' => $number]) }}">
            <span class="step-num">{{ $number }}</span>
            <span class="d-none d-md-inline">{{ $name }}</span>
            <span class="d-md-none">{{ $number }}</span>
        </a>
    @endforeach
</div>
