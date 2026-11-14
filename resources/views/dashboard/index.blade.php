<x-layouts.dashboard navmenu="Dashboard">
    <p>Selamat datang, {{ ucfirst(auth()->user()->getRawOriginal('role')) }}.</p>
</x-layouts.dashboard>
