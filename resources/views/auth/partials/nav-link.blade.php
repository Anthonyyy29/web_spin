{{-- Params: $label, $route (null = belum tersedia), $active (pola nama route) --}}
@if ($route)
    @php($isActive = request()->routeIs($active))
    <a href="{{ route($route) }}"
       @if ($isActive) aria-current="page" @endif
       class="relative shrink-0 rounded-md px-3 py-2 text-sm whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal {{ $isActive ? 'bg-canvas font-medium text-ink before:absolute before:inset-y-2 before:-left-3 before:hidden before:w-1 before:rounded-r before:bg-signal md:before:block' : 'text-muted hover:bg-canvas hover:text-ink' }}">
        {{ $label }}
    </a>
@else
    <span aria-disabled="true" title="Belum tersedia" class="shrink-0 px-3 py-2 text-sm whitespace-nowrap text-muted/60">{{ $label }}</span>
@endif
