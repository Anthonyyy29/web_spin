<x-public.layout active="listen" :title="$content['title'].' — Spin The Radio FM'">
    <x-public.page-header
        :title="$content['title']"
        :breadcrumbs="[
            ['label' => 'Home', 'url' => '/home'],
            ['label' => 'Listen', 'url' => '/listen'],
            ['label' => $content['title']],
        ]" />

    <div class="spin-container spin-section">
        {{-- Kartu Cover Segmen --}}
        <section class="spin-feature-card">
            <div class="spin-feature-card__grid">
                <div class="spin-feature-card__media">
                    <div class="spin-feature-card__img-frame spin-feature-card__img-frame--square">
                        <img src="/assets/uploads/{{ $content['image'] }}" alt="{{ $content['title'] }}" />
                    </div>
                </div>

                <div class="spin-feature-card__body">
                    <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                        <span class="spin-tag spin-tag--maroon">Segmen Podcast</span>
                        <span class="spin-tag spin-tag--yellow">{{ $episodes->count() }} Episode</span>
                    </div>

                    <h2 style="margin: 1rem 0 0; font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 800;">
                        {{ $content['title'] }}
                    </h2>

                    <p style="margin: 0.75rem 0 0; font-size: 0.9375rem; font-weight: 500; line-height: 1.7; color: rgba(42, 23, 64, 0.8);">
                        {{ $content['description'] ?: 'Discover deep conversations and insightful stories in our latest podcast series.' }}
                    </p>

                    <div style="margin-top: 1.5rem;">
                        <x-public.pill tone="sky" size="m" href="/listen">
                            &larr; Semua Segmen
                        </x-public.pill>
                    </div>
                </div>
            </div>
        </section>

        {{-- Daftar Episode --}}
        <section class="spin-section--divided">
            <div class="spin-section__header">
                <x-public.sticker text="DAFTAR EPISODE" tone="yellow" size="m" tag="h2" />
            </div>

            @if ($episodes->isNotEmpty())
                <div class="spin-grid-3">
                    @foreach ($episodes as $episode)
                        <article class="spin-episode-card">
                            <div class="spin-episode-card__embed">
                                <iframe
                                    src="{{ $episode['audio'] }}"
                                    loading="lazy"
                                    allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture">
                                </iframe>
                            </div>

                            <div class="spin-episode-card__body">
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
                <div class="spin-empty-card">Belum ada episode untuk segmen ini.</div>
            @endif
        </section>
    </div>
</x-public.layout>
