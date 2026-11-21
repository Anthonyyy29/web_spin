<x-public.layout active="news" title="News — Spin The Radio FM">
    <x-public.page-header
        title="SPIN NEWS"
        :breadcrumbs="[['label' => 'Home', 'url' => '/home'], ['label' => 'News']]" />

    {{-- Bar Pil Kategori (Papan 6) --}}
    <div class="spin-cat-bar">
        <div class="spin-container">
            <nav aria-label="Kategori berita" class="spin-cat-bar__nav">
                <x-public.category-pill href="/news" :active="true">Semua</x-public.category-pill>
                @foreach ($categoryList as $category)
                    <x-public.category-pill href="/news/category/{{ $category }}" :active="false">
                        {{ $category }}
                    </x-public.category-pill>
                @endforeach
            </nav>
        </div>
    </div>

    <div class="spin-container spin-section">
        {{-- Top News --}}
        @if (isset($top_news) && $top_news->isNotEmpty())
            <section>
                <div class="spin-section__header">
                    <x-public.sticker text="TOP NEWS" tone="yellow" size="m" tag="h2" />
                </div>

                @php
                    $featured = $top_news->first();
                    $secondary = $top_news->slice(1, 4);
                @endphp

                <div class="spin-news-hero">
                    {{-- Berita Utama --}}
                    <a href="/news/details/{{ $featured['id'] }}" class="spin-news-featured">
                        <img src="/assets/uploads/{{ $featured['image'] }}" alt="{{ $featured['title'] }}" class="spin-news-featured__img" />
                        <div class="spin-news-overlay"></div>

                        <div class="spin-news-featured__content">
                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.5rem;">
                                <span class="spin-tag spin-tag--yellow">{{ $featured['category'] ?? 'News' }}</span>
                                <span style="padding: 0.15rem 0.75rem; border-radius: 9999px; background: rgba(42, 23, 64, 0.85); color: var(--spin-cream); font-size: 0.75rem; font-weight: 600;">
                                    {{ $featured['created_at'] }}
                                </span>
                            </div>
                            <h3 class="spin-news-featured__title">{{ $featured['title'] }}</h3>
                        </div>
                    </a>

                    {{-- 4 Berita Sekunder --}}
                    @if ($secondary->isNotEmpty())
                        <div class="spin-news-secondary">
                            @foreach ($secondary as $item)
                                <a href="/news/details/{{ $item['id'] }}" class="spin-news-mini">
                                    <img src="/assets/uploads/{{ $item['image'] }}" alt="{{ $item['title'] }}" loading="lazy" class="spin-news-mini__img" />
                                    <div class="spin-news-overlay"></div>

                                    <div class="spin-news-mini__content">
                                        <span class="spin-news-mini__date">{{ $item['created_at'] }}</span>
                                        <h3 class="spin-news-mini__title">{{ $item['title'] }}</h3>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>
        @else
            <div class="spin-empty-card">Belum ada berita yang dipublikasikan.</div>
        @endif

        {{-- Berita Per Kategori --}}
        @foreach ($categorized_news as $category => $articles)
            @if ($articles->isNotEmpty())
                <section class="spin-section--divided">
                    <div class="spin-section__header">
                        <div class="spin-cat-heading">
                            <span class="spin-cat-heading__bar spin-cat-heading__bar--{{ $loop->index % 4 }}"></span>
                            <h2 style="margin: 0; font-size: clamp(1.25rem, 2.2vw, 1.5rem); font-weight: 800;">{{ $category }}</h2>
                        </div>

                        <x-public.pill tone="yellow" size="m" href="/news/category/{{ $category }}">
                            Lihat Semua &rarr;
                        </x-public.pill>
                    </div>

                    <div class="spin-grid-3">
                        @foreach ($articles as $article)
                            <article class="spin-news-card">
                                <a href="/news/details/{{ $article['id'] }}" class="spin-news-card__thumb">
                                    <img src="/assets/uploads/{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" />
                                </a>

                                <div class="spin-news-card__body">
                                    <div class="spin-news-card__meta">
                                        <span>{{ $category }}</span>
                                        <span class="spin-news-card__date">{{ $article['created_at'] }}</span>
                                    </div>

                                    <h3 class="spin-news-card__title">
                                        <a href="/news/details/{{ $article['id'] }}">{{ $article['title'] }}</a>
                                    </h3>

                                    <a href="/news/details/{{ $article['id'] }}" class="spin-text-link" style="margin-top: 1rem;">
                                        Baca Selengkapnya &rarr;
                                    </a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif
        @endforeach
    </div>
</x-public.layout>
