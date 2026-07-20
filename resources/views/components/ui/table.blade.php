@props([
    'headers' => [],
    'class' => 'table-hover',
    'id' => null
])

<div class="table-responsive">
    <table @if($id) id="{{ $id }}" @endif {{ $attributes->merge(['class' => 'table align-middle ' . $class]) }}>
        @if(!empty($headers))
            <thead class="table-light text-secondary">
                <tr>
                    @foreach($headers as $header)
                        <th class="fw-semibold">{{ $header }}</th>
                    @endforeach
                </tr>
            </thead>
        @endif
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
