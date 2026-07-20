@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendType' => 'success',
    'bgClass' => 'bg-white'
])

@php
    $trendColors = [
        'success' => 'text-success',
        'danger' => 'text-danger',
        'neutral' => 'text-muted'
    ];
    $trendColor = $trendColors[$trendType] ?? $trendColors['neutral'];
@endphp

<div {{ $attributes->merge(['class' => 'card border-0 shadow-sm h-100 ' . $bgClass]) }}>
    <div class="card-body d-flex align-items-center justify-content-between p-4">
        <div>
            <span class="text-secondary small fw-semibold text-uppercase tracking-wider d-block mb-1">{{ $title }}</span>
            <h3 class="mb-0 fw-bold text-dark">{{ $value }}</h3>
            @if($trend)
                <span class="small {{ $trendColor }} mt-1 d-inline-block fw-medium">
                    {{ $trend }}
                </span>
            @endif
        </div>
        @if($icon)
            <div class="rounded-3 p-3 bg-light d-flex align-items-center justify-content-center text-primary-blue fs-4">
                <i class="{{ $icon }}"></i>
            </div>
        @endif
    </div>
</div>
