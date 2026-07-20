@props([
    'type' => 'secondary',
    'class' => ''
])

@php
    $typeMap = [
        'primary' => 'bg-primary-subtle text-primary border border-primary-subtle',
        'success' => 'bg-success-subtle text-success border border-success-subtle',
        'danger' => 'bg-danger-subtle text-danger border border-danger-subtle',
        'warning' => 'bg-warning-subtle text-warning border border-warning-subtle',
        'info' => 'bg-info-subtle text-info border border-info-subtle',
        'secondary' => 'bg-secondary-subtle text-secondary border border-secondary-subtle'
    ];
    $badgeStyle = $typeMap[$type] ?? $typeMap['secondary'];
@endphp

<span {{ $attributes->merge(['class' => 'badge px-2.5 py-1.5 rounded-pill font-weight-medium ' . $badgeStyle . ' ' . $class]) }}>
    {{ $slot }}
</span>
