<x-public.layout active="crew" title="Our Crew — Spin The Radio FM">
    <x-public.page-header
        title="OUR CREW"
        :breadcrumbs="[['label' => 'Home', 'url' => '/home'], ['label' => 'Crew']]" />

    <section class="spin-container spin-section">
        <div class="spin-section__header">
            <div>
                <h2 style="margin: 0; font-size: clamp(1.25rem, 2.2vw, 1.625rem); font-weight: 800;">
                    Kabinet Sonavara &middot; Spin The Radio FM
                </h2>
                <p class="spin-section__subtitle">
                    Tim kreatif, penyiar, produser, dan penggerak di balik setiap siaran kami.
                </p>
            </div>
            <span class="spin-tag spin-tag--sky" style="padding: 0.35rem 1rem; font-size: 0.75rem;">
                {{ $crew->count() }} Anggota
            </span>
        </div>

        @if ($crew->isNotEmpty())
            <div class="spin-grid-crew">
                @foreach ($crew as $anggota)
                    <x-public.crew-card :crew="$anggota" :index="$loop->index" />
                @endforeach
            </div>
        @else
            <div class="spin-empty-card">Belum ada data crew yang ditampilkan.</div>
        @endif
    </section>
</x-public.layout>
