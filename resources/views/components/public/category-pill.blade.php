@props([
    'href',
    'active' => false,
])

<a href="{{ $href }}"
    @if ($active) aria-current="page" @endif
    {{ $attributes->merge(['class' => 'spin-cat-pill'.($active ? ' is-active' : '')]) }}>
    {{ $slot }}
</a>
