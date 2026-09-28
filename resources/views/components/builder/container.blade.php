@props([
    'id' => 'container',
    'layout' => [],
    'slots' => [],
    'slot' => '',
    'website' => null,
])

@php
    $columns = (int) ($layout['columns'] ?? 1);
    $colClass = match($columns) {
        2 => 'grid grid-cols-1 md:grid-cols-2 gap-6',
        3 => 'grid grid-cols-1 md:grid-cols-3 gap-6',
        4 => 'grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6',
        default => 'w-full space-y-6',
    };
@endphp

<div id="{{ e($id) }}" class="builder-container-component {{ $colClass }} py-4 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    {!! $slot ?? '' !!}
</div>
