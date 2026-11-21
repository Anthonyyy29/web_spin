@props(['dots' => 5])

<div class="spin-carousel__controls">
    <div class="spin-carousel__row">
        <button type="button" data-carousel-prev aria-label="Sebelumnya" class="spin-carousel__arrow">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M19 12H5M12 19l-7-7 7-7" />
            </svg>
        </button>

        <div class="spin-carousel__dots">
            @for ($i = 0; $i < $dots; $i++)
                <button type="button" data-carousel-dot aria-label="Slide {{ $i + 1 }}"
                    class="spin-carousel__dot {{ $i === 0 ? 'is-active' : '' }}">
                </button>
            @endfor
        </div>

        <button type="button" data-carousel-next aria-label="Berikutnya" class="spin-carousel__arrow">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M5 12h14M12 5l7 7-7 7" />
            </svg>
        </button>
    </div>

    <p class="spin-carousel__hint">Geser untuk lihat lainnya &rsaquo;</p>
</div>
