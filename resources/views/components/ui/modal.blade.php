@props([
    'id',
    'title',
    'size' => ''
])

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Label" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog {{ $size }} modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-semibold text-dark" id="{{ $id }}Label">{{ $title }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-3">
                {{ $slot }}
            </div>
            @if(isset($footer))
                <div class="modal-footer border-0 pt-0">
                    {{ $footer }}
                </div>
            @endif
        </div>
    </div>
</div>
