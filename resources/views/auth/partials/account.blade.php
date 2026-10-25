{{-- Foto, nama, jabatan, dan tombol Keluar pengguna yang login; dipakai di menu mobile dan sidebar desktop --}}
<div class="flex items-center gap-3">
    @include('auth.partials.avatar', ['user' => Auth::user(), 'size' => 'size-8'])
    <div class="min-w-0">
        <p class="truncate text-sm font-medium">{{ Auth::user()->username }}</p>
        <p class="truncate text-sm text-muted">{{ Auth::user()->role->label }}</p>
    </div>
</div>
<form method="POST" action="{{ route('logout') }}" class="mt-2 md:mt-3">
    @csrf
    <button type="submit" class="inline-flex min-h-11 items-center text-sm text-muted underline-offset-4 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal md:min-h-0">Keluar</button>
</form>
