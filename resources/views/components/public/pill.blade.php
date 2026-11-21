@props([
    'tone' => 'yellow',
    'size' => 'm',
    'href' => null,
    'type' => 'button',
])

@php
    $toneClass = match ($tone) {
        'maroon' => 'spin-pill--maroon',
        'pink' => 'spin-pill--pink',
        'sky' => 'spin-pill--sky',
        default => 'spin-pill--yellow',
    };

    $sizeClass = $size === 'l' ? 'spin-pill--l' : 'spin-pill--m';
    $baseClass = "spin-pill {$toneClass} {$sizeClass}";
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $baseClass]) }}>
        {{ $slot }}
    </button>
@endif
