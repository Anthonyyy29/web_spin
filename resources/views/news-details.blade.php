<x-public.layout active="news" :title="$news['title'].' — Spin The Radio FM'">
    <x-public.page-header
        title="SPIN NEWS"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => '/home'],
            ['label' => 'News', 'url' => '/news'],
            ['label' => $news['title']],
        ]" />

    {{-- Bar Pil Kategori (Papan 6) --}}
    <div class="spin-cat-bar">
        <div class="spin-container">
            <nav aria-label="Kategori berita" class="spin-cat-bar__nav">
                <x-public.category-pill href="/news" :active="false">Semua</x-public.category-pill>
                @foreach ($categoryList as $category)
                    <x-public.category-pill href="/news/category/{{ $category }}" :active="($news['category'] ?? '') === $category">
                        {{ $category }}
                    </x-public.category-pill>
                @endforeach
            </nav>
        </div>
    </div>

    <article class="spin-container spin-container--narrow spin-section">
        <div class="spin-feature-card">
            <div class="spin-feature-card__grid">
                {{-- Media (Instagram Reel atau Foto Berita) --}}
                <div class="spin-feature-card__media spin-feature-card__media--yellow">
                    @if (! empty($news['video']))
                        <div class="spin-feature-card__img-frame" style="background: var(--spin-white); padding: 0.5rem;">
                            <blockquote
                                class="instagram-media"
                                data-instgrm-permalink="https://www.instagram.com/reel/{{ $news['video'] }}/?utm_source=ig_embed&amp;utm_campaign=loading"
                                data-instgrm-version="14"
                                style="background:#FFF; border:0; border-radius:8px; margin:0 auto; max-width:540px; min-width:260px; padding:0; width:100%;">
                                <div style="padding:16px; text-align:center;">
                                    <a href="https://www.instagram.com/reel/{{ $news['video'] }}/" target="_blank" rel="noopener noreferrer" style="font-size:0.875rem; font-weight:700; color:var(--spin-maroon); text-decoration:underline;">
                                        Lihat Reel di Instagram (@spintheradiofm)
                                    </a>
                                </div>
                            </blockquote>
                            <script async src="//www.instagram.com/embed.js"></script>
                        </div>
                    @else
                        <div class="spin-feature-card__img-frame">
                            <img src="{{ url('assets/uploads/'.$news['image']) }}" alt="{{ $news['title'] }}" />
                        </div>
                    @endif
                </div>

                {{-- Isi Berita --}}
                <div class="spin-feature-card__body" style="justify-content: flex-start;">
                    <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.625rem;">
                        @if (! empty($news['category']))
                            <a href="/news/category/{{ $news['category'] }}" class="spin-tag spin-tag--maroon">
                                {{ $news['category'] }}
                            </a>
                        @endif
                        <span class="spin-tag spin-tag--sky">{{ $news['created_at'] }}</span>
                    </div>

                    <h2 style="margin: 1.25rem 0 0; font-size: clamp(1.5rem, 2.6vw, 2rem); font-weight: 800; line-height: 1.25;">
                        {{ $news['title'] }}
                    </h2>

                    <div class="spin-article-prose">
                        @foreach ($news['description_row'] as $description)
                            @if ($description === '')
                                <div style="height: 0.5rem;"></div>
                            @else
                                <p>{{ $description }}</p>
                            @endif
                        @endforeach
                    </div>

                    <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid rgba(42, 23, 64, 0.15);">
                        <x-public.pill tone="yellow" size="m" href="/news">
                            &larr; Kembali ke Berita
                        </x-public.pill>
                    </div>
                </div>
            </div>
        </div>
    </article>
</x-public.layout>
