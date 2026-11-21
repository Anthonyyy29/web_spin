@props(['placeholder' => 'Cari podcast...'])

<div data-search-wrapper class="spin-search">
    <form action="/listen/search" method="GET" class="spin-search__form">
        <span class="spin-search__icon">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" />
            </svg>
        </span>
        <input
            type="search"
            name="query"
            value="{{ request('query') }}"
            data-search-input
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            aria-label="{{ $placeholder }}"
            class="spin-search__input" />
    </form>

    <div data-search-suggestions class="spin-search__suggestions is-hidden"></div>
</div>
