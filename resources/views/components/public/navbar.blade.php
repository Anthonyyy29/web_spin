@props(['active' => ''])

@php
    $links = [
        ['key' => 'home', 'label' => 'HOME', 'href' => '/home'],
        ['key' => 'listen', 'label' => 'LISTEN', 'href' => '/listen'],
        ['key' => 'news', 'label' => 'NEWS', 'href' => '/news'],
        ['key' => 'crew', 'label' => 'CREW', 'href' => '/crew'],
        ['key' => 'about', 'label' => 'ABOUT', 'href' => '/aboutus'],
    ];
@endphp

<header class="spin-navbar">
    <div class="spin-container spin-navbar__inner">
        {{-- Logo SPIN --}}
        <a href="/home" class="spin-navbar__brand">
            <img src="{{ asset('assets/ui/logo-spin.png') }}" alt="Spin The Radio FM" />
        </a>

        {{-- Navigasi Desktop --}}
        <nav aria-label="Navigasi utama" class="spin-navbar__links">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}"
                    @if ($active === $link['key']) aria-current="page" @endif
                    class="spin-navbar__link {{ $active === $link['key'] ? 'is-active' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>

        {{-- Pencarian Desktop --}}
        <div class="spin-navbar__search-desktop">
            <x-public.search />
        </div>

        {{-- Tombol Mobile (Cari + Menu / Tutup) --}}
        <div class="spin-navbar__mobile-actions">
            <button type="button" data-nav-search-toggle aria-label="Buka pencarian" class="spin-icon-btn">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path d="m20 20-3.5-3.5" />
                </svg>
            </button>

            <button type="button" data-nav-toggle aria-expanded="false" aria-label="Buka menu navigasi" class="spin-icon-btn">
                <svg data-nav-icon-open width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
                <svg data-nav-icon-close class="is-hidden" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true">
                    <path d="M6 6l12 12M18 6 6 18" />
                </svg>
            </button>
        </div>
    </div>

    {{-- Drawer Mobile --}}
    <div data-nav-drawer class="spin-navbar__drawer is-hidden">
        <div class="spin-navbar__drawer-search">
            <x-public.search />
        </div>

        <nav aria-label="Navigasi mobile" class="spin-navbar__drawer-nav">
            @foreach ($links as $link)
                <a href="{{ $link['href'] }}"
                    @if ($active === $link['key']) aria-current="page" @endif
                    class="spin-navbar__link {{ $active === $link['key'] ? 'is-active' : '' }}">
                    {{ $link['label'] }}
                </a>
            @endforeach
        </nav>
    </div>
</header>
