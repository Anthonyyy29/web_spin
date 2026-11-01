{{-- Judul halaman Absensi dan tab Per program / Per orang. Param: $active ('program' atau 'people') --}}
<h1 class="text-2xl font-semibold tracking-tight">Absensi</h1>
@php($tabClass = 'border-b-2 px-1 pb-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal')
<nav aria-label="Tampilan absensi" class="mt-4 mb-4 flex gap-6 border-b border-line">
    <a href="{{ route('absensi.index') }}" @if ($active === 'program') aria-current="page" @endif class="{{ $tabClass }} {{ $active === 'program' ? 'border-signal-strong text-ink' : 'border-transparent text-muted hover:text-ink' }}">Per program</a>
    <a href="{{ route('absensi.people') }}" @if ($active === 'people') aria-current="page" @endif class="{{ $tabClass }} {{ $active === 'people' ? 'border-signal-strong text-ink' : 'border-transparent text-muted hover:text-ink' }}">Per orang</a>
</nav>
