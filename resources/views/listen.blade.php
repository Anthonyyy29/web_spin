<x-public.layout active="listen" title="Listen — Spin The Radio FM">
    <x-public.page-header
        title="OUR PODCAST"
        :breadcrumbs="[['label' => 'Home', 'url' => '/home'], ['label' => 'Listen']]" />

    <div class="spin-container spin-section">
        @if ($search_mode !== 'podcast')
            {{-- Segmen Podcast --}}
            <section>
                <div class="spin-section__header">
                    <div>
                        <x-public.sticker text="OUR SEGMENT PODCAST" tone="yellow" size="m" tag="h2" />
                        <p class="spin-section__subtitle">Pilih segmen favoritmu untuk melihat seluruh episode yang tersedia.</p>
                    </div>
                </div>

                @if ($contents->isNotEmpty())
                    <div id="podcast-gallery-listen" class="spin-grid-programs">
                        @foreach ($contents as $content)
                            <x-public.program-card :content="$content" :index="$loop->index" />
                        @endforeach
                    </div>
                @else
                    <div class="spin-empty-card">Belum ada segmen podcast.</div>
                @endif
            </section>
        @endif

        {{-- Episode Terbaru / Hasil Pencarian --}}
        <section class="{{ $search_mode !== 'podcast' ? 'spin-section--divided' : '' }}">
            <div class="spin-section__header">
                <div>
                    <x-public.sticker
                        :text="$search_mode !== '' ? 'Result Podcast' : 'LATEST PODCAST'"
                        tone="sky"
                        size="m"
                        tag="h2" />
                    @if ($search_mode !== '')
                        <p class="spin-section__subtitle">
                            Menampilkan hasil pencarian untuk <strong style="color: var(--spin-maroon);">&ldquo;{{ request('query') }}&rdquo;</strong>.
                        </p>
                    @endif
                </div>

                @if ($search_mode !== '')
                    <x-public.pill tone="yellow" size="m" href="/listen">
                        &larr; Kembali ke Semua Podcast
                    </x-public.pill>
                @endif
            </div>

            @if ($last_episodes->isNotEmpty())
                <div class="spin-grid-3">
                    @foreach ($last_episodes as $episode)
                        <article class="spin-episode-card">
                            <div class="spin-episode-card__embed">
                                <iframe
                                    src="{{ $episode['audio'] }}"
                                    loading="lazy"
                                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture">
                                </iframe>
                            </div>

                            <div class="spin-episode-card__body">
                                <span class="spin-tag spin-tag--yellow">Episode</span>
                                <h3 class="spin-episode-card__title">{{ $episode['title'] }}</h3>
                                <p data-read-more-text class="spin-episode-card__desc spin-clamp-3">
                                    {{ $episode['description'] }}
                                </p>
                                <button type="button" data-read-more-btn class="spin-text-link">
                                    Lihat Selengkapnya
                                </button>
                            </div>
                        </article>
                    @endforeach
                </div>
            @else
                <div class="spin-empty-card">Belum ada episode podcast yang tersedia.</div>
            @endif
        </section>
    </div>
</x-public.layout>
