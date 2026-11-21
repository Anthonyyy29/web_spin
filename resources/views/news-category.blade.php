<x-public.layout active="news" :title="$activeCategory.' — Spin The Radio FM'">
    <x-public.page-header
        :title="$activeCategory"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => '/home'],
            ['label' => 'News', 'url' => '/news'],
            ['label' => $activeCategory],
        ]" />

    {{-- Bar Pil Kategori (Papan 6) --}}
    <div class="spin-cat-bar">
        <div class="spin-container">
            <nav aria-label="Kategori berita" class="spin-cat-bar__nav">
                <x-public.category-pill href="/news" :active="false">Semua</x-public.category-pill>
                @foreach ($categoryList as $category)
                    <x-public.category-pill href="/news/category/{{ $category }}" :active="$activeCategory === $category">
                        {{ $category }}
                    </x-public.category-pill>
                @endforeach
            </nav>
        </div>
    </div>

    <section class="spin-container spin-section">
        @if ($news->isNotEmpty())
            <div class="spin-grid-3">
                @foreach ($news as $article)
                    <article class="spin-news-card">
                        <a href="/news/details/{{ $article['id'] }}" class="spin-news-card__thumb">
                            <img src="/assets/uploads/{{ $article['image'] }}" alt="{{ $article['title'] }}" loading="lazy" />
                        </a>

                        <div class="spin-news-card__body">
                            <div class="spin-news-card__meta">
                                <span class="spin-tag spin-tag--yellow">{{ $activeCategory }}</span>
                                <span class="spin-news-card__date">{{ $article['created_at'] }}</span>
                            </div>

                            <h2 class="spin-news-card__title">
                                <a href="/news/details/{{ $article['id'] }}">{{ $article['title'] }}</a>
                            </h2>

                            <div class="spin-news-card__excerpt">
                                @foreach ($article['description_row'] as $description)
                                    @if ($description !== '')
                                        <p>{{ $description }}</p>
                                    @endif
                                @endforeach
                            </div>

                            <div style="margin-top: 1.25rem;">
                                <x-public.pill tone="maroon" size="m" href="/news/details/{{ $article['id'] }}">
                                    Baca Selengkapnya
                                </x-public.pill>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @else
            <div class="spin-empty-card">
                <p style="margin: 0 0 1.25rem;">Belum ada berita pada kategori {{ $activeCategory }}.</p>
                <x-public.pill tone="yellow" size="m" href="/news">
                    &larr; Kembali ke Semua Berita
                </x-public.pill>
            </div>
        @endif
    </section>
</x-public.layout>
