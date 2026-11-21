@props([
    'text',
    'size' => 'l',
    'tone' => 'yellow',
    'tag' => 'h2',
])

@php
    $sizeClass = match ($size) {
        'xl' => 'spin-sticker--xl',
        'm' => 'spin-sticker--m',
        's' => 'spin-sticker--s',
        default => 'spin-sticker--l',
    };

    $toneClass = $tone === 'sky' ? 'spin-sticker--sky' : 'spin-sticker--yellow';
@endphp

<{{ $tag }} {{ $attributes->merge(['class' => "spin-sticker {$sizeClass} {$toneClass}"]) }} data-text="{{ $text }}">
    {{ $text }}
</{{ $tag }}>
