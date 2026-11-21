@props([
    'content',
    'index' => 0,
    'label' => 'PODCAST',
])

@php
    $tones = ['pink', 'lavender', 'green', 'orange'];
    $tone = $tones[$index % count($tones)];
    $letters = mb_str_split($label);
    $episodeCount = $content['episodes_count'] ?? null;
@endphp

<a href="/listen/content/{{ $content['id'] }}" {{ $attributes->merge(['class' => 'spin-program-card']) }}>
    <div class="spin-program-card__body">
        {{-- Spine vertikal maroon di kiri (Papan 2) --}}
        <div class="spin-program-card__spine">
            @foreach ($letters as $char)
                <span>{{ $char }}</span>
            @endforeach
        </div>

        {{-- Area gambar + info --}}
        <div class="spin-program-card__content">
            <div class="spin-program-card__thumb spin-program-card__thumb--{{ $tone }}">
                <img
                    src="/assets/uploads/{{ $content['image'] }}"
                    alt="{{ $content['title'] }}"
                    loading="lazy" />
            </div>

            <div class="spin-program-card__meta">
                <div class="spin-program-card__text">
                    <h3 class="spin-program-card__title">{{ $content['title'] }}</h3>
                    <p class="spin-program-card__sub">
                        @if ($episodeCount !== null)
                            Episode terbaru &middot; {{ $episodeCount }} episode
                        @else
                            {{ \Illuminate\Support\Str::limit($content['description'] ?? 'Segmen Podcast STRFM', 36) }}
                        @endif
                    </p>
                </div>

                <span class="spin-program-card__badge">{{ $index + 1 }}</span>
            </div>
        </div>
    </div>
</a>
