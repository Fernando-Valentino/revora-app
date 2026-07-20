@props([
    'title' => null,
    'subtitle' => null,
    'class' => ''
])

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm ' . $class]) }}>
    @if($title || isset($headerActions))
        <div class="card-header bg-transparent border-0 d-flex justify-content-between align-items-center py-3">
            <div>
                @if($title)
                    <h5 class="card-title mb-1 fw-semibold text-dark">{{ $title }}</h5>
                @endif
                @if($subtitle)
                    <p class="text-muted small mb-0">{{ $subtitle }}</p>
                @endif
            </div>
            @if(isset($headerActions))
                <div>
                    {{ $headerActions }}
                </div>
            @endif
        </div>
    @endif
    <div class="card-body">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card-footer bg-transparent border-top py-3">
            {{ $footer }}
        </div>
    @endif
</div>
