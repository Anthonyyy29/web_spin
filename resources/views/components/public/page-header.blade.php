@props([
    'title',
    'breadcrumbs' => [],
    'tone' => 'sky',
])

<section class="spin-page-header">
    {{-- Gelombang maroon di sisi atas (Papan 4) --}}
    <div class="spin-page-header__wave" aria-hidden="true">
        <svg viewBox="0 0 1440 72" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
            <path d="M0 0H1440V32C1220 72 980 8 720 38C460 68 220 12 0 46V0Z" fill="#9A134B" />
        </svg>
    </div>

    <div class="spin-container spin-page-header__body">
        <x-public.sticker :text="$title" :tone="$tone" size="l" tag="h1" />

        @if (! empty($breadcrumbs))
            <nav aria-label="Breadcrumb" class="spin-breadcrumb">
                <ol class="spin-breadcrumb__list">
                    @foreach ($breadcrumbs as $crumb)
                        @if (! $loop->first)
                            <li aria-hidden="true" class="spin-breadcrumb__sep">&rsaquo;</li>
                        @endif

                        <li>
                            @if (! empty($crumb['url']) && ! $loop->last)
                                <a href="{{ $crumb['url'] }}" class="spin-breadcrumb__link">
                                    {{ $crumb['label'] }}
                                </a>
                            @else
                                <span class="spin-breadcrumb__current" @if ($loop->last) aria-current="page" @endif>
                                    {{ $crumb['label'] }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif
    </div>
</section>
