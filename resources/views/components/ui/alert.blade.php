@props([
    'type' => 'info',
    'dismissible' => false,
    'icon' => null
])

@php
    $typeMap = [
        'primary' => 'alert-primary',
        'success' => 'alert-success',
        'danger' => 'alert-danger',
        'warning' => 'alert-warning',
        'info' => 'alert-info'
    ];
    $alertClass = $typeMap[$type] ?? $typeMap['info'];
@endphp

<div {{ $attributes->merge(['class' => 'alert d-flex align-items-center ' . $alertClass . ($dismissible ? ' alert-dismissible fade show' : '')]) }} role="alert">
    @if($icon)
        <span class="me-2"><i class="{{ $icon }} fs-5"></i></span>
    @endif
    <div>
        {{ $slot }}
    </div>
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
