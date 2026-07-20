@props([
    'title',
    'description',
    'icon' => 'bi-database-exclamation',
    'actionText' => null,
    'actionUrl' => null
])

<div {{ $attributes->merge(['class' => 'text-center py-5 px-4 bg-white rounded-3 shadow-sm']) }}>
    <div class="mb-4 text-secondary opacity-50">
        <i class="{{ $icon }}" style="font-size: 4rem;"></i>
    </div>
    <h4 class="fw-bold text-dark mb-2">{{ $title }}</h4>
    <p class="text-secondary mx-auto mb-4" style="max-width: 460px;">{{ $description }}</p>
    @if($actionText && $actionUrl)
        <a href="{{ $actionUrl }}" class="btn btn-primary px-4 py-2">
            {{ $actionText }}
        </a>
    @endif
</div>
