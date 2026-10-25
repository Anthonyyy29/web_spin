{{-- Params: $label, $route (null = belum tersedia), $active (pola nama route) --}}
@if ($route)
    @php($isActive = request()->routeIs($active))
    <a href="{{ route($route) }}"
       @if ($isActive) aria-current="page" @endif
       class="relative flex min-h-11 shrink-0 items-center rounded-md px-3 py-2 text-sm whitespace-nowrap focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal md:min-h-0 {{ $isActive ? 'bg-canvas font-medium text-ink before:absolute before:inset-y-2 before:-left-3 before:w-1 before:rounded-r before:bg-signal' : 'text-muted hover:bg-canvas hover:text-ink' }}">
        {{ $label }}
    </a>
@else
    <span aria-disabled="true" title="Belum tersedia" class="flex min-h-11 shrink-0 items-center px-3 py-2 text-sm whitespace-nowrap text-muted/60 md:min-h-0">
        {{ $label }}
        <span class="ml-2 text-xs md:hidden">belum tersedia</span>
    </span>
@endif
