@props([
    'crew',
    'index' => 0,
])

@php
    $roleTones = ['yellow', 'green', 'sky', 'orange'];
    $roleTone = $roleTones[$index % count($roleTones)];
    $instagram = $crew['instagram_link'] ?? null;
    $linkedin = $crew['linkedin_link'] ?? null;
@endphp

<article {{ $attributes->merge(['class' => 'spin-crew-card']) }}>
    <div class="spin-crew-card__media">
        <img
            src="/assets/uploads/{{ $crew['image'] }}"
            alt="{{ $crew['name'] }}"
            loading="lazy" />

        <div class="spin-crew-card__role-wrap">
            <span class="spin-crew-card__role spin-crew-card__role--{{ $roleTone }}">
                {{ $crew['role'] }}
            </span>
        </div>
    </div>

    <div class="spin-crew-card__footer">
        <div class="spin-crew-card__info">
            <h3 class="spin-crew-card__name">{{ $crew['name'] }}</h3>
            @if (! empty($crew['about']))
                <p class="spin-crew-card__about">{{ $crew['about'] }}</p>
            @endif
        </div>

        <div class="spin-crew-card__socials">
            @if (! empty($instagram))
                <a href="{{ $instagram }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram {{ $crew['name'] }}" class="spin-crew-card__social-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <rect width="20" height="20" x="2" y="2" rx="5" ry="5" />
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                        <line x1="17.5" x2="17.51" y1="6.5" y2="6.5" />
                    </svg>
                </a>
            @endif

            @if (! empty($linkedin))
                <a href="{{ $linkedin }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn {{ $crew['name'] }}" class="spin-crew-card__social-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z" />
                        <rect width="4" height="12" x="2" y="9" />
                        <circle cx="4" cy="4" r="2" />
                    </svg>
                </a>
            @endif
        </div>
    </div>
</article>
