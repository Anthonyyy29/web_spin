@props(['text' => 'ON AIR · PODCAST · NEWS · CREW'])

<div class="spin-film-strip" aria-hidden="true">
    <div class="spin-film-strip__track">
        @for ($i = 0; $i < 12; $i++)
            <span class="spin-film-strip__item">{{ $text }}</span>
        @endfor
    </div>
</div>
