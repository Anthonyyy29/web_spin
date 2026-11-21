<x-public.layout active="home" title="Spin The Radio FM — Universitas Esa Unggul">
    {{-- Hero Section --}}
    <section class="spin-hero">
        <div class="spin-container spin-hero__inner">
            <div class="spin-hero__grid">
                <div class="spin-hero__copy">
                    <span class="spin-hero__eyebrow">
                        <span class="spin-hero__dot"></span>
                        The Best Radio Campus &middot; UEU Bekasi
                    </span>

                    <div class="spin-hero__title">
                        <x-public.sticker text="SPIN THE RADIO FM" tone="yellow" size="xl" tag="h1" />
                    </div>

                    <p class="spin-hero__tagline">&ldquo;Voice, Inspire, Action!&rdquo;</p>

                    <p class="spin-hero__desc">
                        Dengarkan siaran langsung, podcast kampus pilihan, berita hangat mahasiswa, dan kenal lebih dekat kru kreatif Spin The Radio FM Universitas Esa Unggul Kampus Bekasi.
                    </p>

                    <div class="spin-hero__actions">
                        <x-public.pill tone="yellow" size="l" href="/listen">
                            Dengarkan Podcast
                        </x-public.pill>
                        <x-public.pill tone="pink" size="l" href="/news">
                            Baca Berita
                        </x-public.pill>
                    </div>
                </div>

                {{-- Kartu Visual Logo & Live On Air --}}
                <div class="spin-hero__visual">
                    <div class="spin-onair-card">
                        <div class="spin-onair-card__top">
                            <span class="spin-onair-badge">
                                <span class="spin-onair-badge__dot"></span>
                                ON AIR
                            </span>
                            <span style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.06em; color: var(--spin-maroon);">STRFM</span>
                        </div>

                        <div class="spin-onair-card__logo">
                            <img src="{{ asset('assets/ui/logo-spin.png') }}" alt="Logo Spin The Radio FM" />
                        </div>

                        <p class="spin-onair-card__caption">Good Looking Tidak Perlu, Suara Nomor Satu!</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Gelombang transisi ke krem --}}
        <div class="spin-hero__wave" aria-hidden="true">
            <svg viewBox="0 0 1440 72" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
                <path d="M0 36C240 0 480 72 720 36C960 0 1200 72 1440 28V72H0V36Z" fill="#FFF8D6" />
            </svg>
        </div>
    </section>

    {{-- Film Strip Divider (Papan 1) --}}
    <x-public.film-strip />

    {{-- Section: Our Podcast --}}
    <section class="spin-section">
        <div class="spin-container">
            <div class="spin-section__header">
                <div>
                    <x-public.sticker text="OUR PODCAST" tone="yellow" size="l" tag="h2" />
                    <p class="spin-section__subtitle">Segmen podcast pilihan dari studio Spin The Radio FM.</p>
                </div>
                <x-public.pill tone="maroon" size="m" href="/listen">
                    Lihat Semua &rarr;
                </x-public.pill>
            </div>

            @if ($content->isNotEmpty())
                <div id="podcast-gallery" class="spin-grid-programs">
                    @foreach ($content as $podcast)
                        <x-public.program-card :content="$podcast" :index="$loop->index" />
                    @endforeach
                </div>
            @else
                <div class="spin-empty-card">Belum ada segmen podcast yang tersedia.</div>
            @endif
        </div>
    </section>

    {{-- Section: Our Crew --}}
    <section class="spin-section spin-section--tinted">
        <div class="spin-container">
            <div class="spin-section__header">
                <div>
                    <x-public.sticker text="OUR CREW" tone="sky" size="l" tag="h2" />
                    <p class="spin-section__subtitle">Orang-orang di balik suara dan karya Spin The Radio FM.</p>
                </div>
                <x-public.pill tone="yellow" size="m" href="/crew">
                    Lihat Semua Crew &rarr;
                </x-public.pill>
            </div>

            @if ($crew->isNotEmpty())
                <div data-carousel>
                    <div id="crew-slider" data-carousel-track class="spin-carousel__track">
                        @foreach ($crew as $anggota)
                            <div class="spin-carousel__slide">
                                <x-public.crew-card :crew="$anggota" :index="$loop->index" />
                            </div>
                        @endforeach
                    </div>

                    <x-public.carousel-controls :dots="min(max($crew->count(), 1), 5)" />
                </div>
            @else
                <div class="spin-empty-card">Belum ada data crew.</div>
            @endif
        </div>
    </section>

    {{-- Floating Live Radio Player --}}
    <div class="spin-radio-float">
        <div id="radioHint" class="spin-radio-float__hint">Play Radio Now! &darr;</div>

        <button type="button" id="playButton" class="spin-radio-float__btn">
            <span class="spin-radio-float__circle">
                <svg id="playIcon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z" />
                </svg>
                <svg id="pauseIcon" class="is-hidden" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M6 5h4v14H6zm8 0h4v14h-4z" />
                </svg>
            </span>

            <span id="radioLabel">Dengarkan Live Radio</span>

            <span id="waveAnim" class="spin-radio-wave is-hidden" aria-hidden="true">
                <span></span><span></span><span></span><span></span>
            </span>
        </button>

        <audio id="radioAudio" src="https://radio.spintheradiofm.site/listen/radio-strfm/radio.mp3" preload="none"></audio>
    </div>
</x-public.layout>
