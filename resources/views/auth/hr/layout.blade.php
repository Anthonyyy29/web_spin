<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">

    {{-- Topbar --}}
    <header class="fixed top-0 left-0 right-0 h-16 bg-white border-b flex items-center justify-between px-6">
        <h1 class="text-xl font-bold text-indigo-600">Dashboard HR</h1>

        <div class="flex items-center gap-4">
            <span class="text-sm">{{ Auth::user()->username }}</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-red-600">Keluar</button>
            </form>
        </div>
    </header>

    {{-- Sidebar --}}
    <aside class="fixed top-16 left-0 bottom-0 w-64 bg-white border-r p-4">
        <a href="{{ route('hr.dashboard') }}" class="block px-3 py-2 rounded hover:bg-gray-100">Dashboard</a>
        <a href="{{ route('jadwalSiaran.index') }}" class="block px-3 py-2 rounded hover:bg-gray-100">Jadwal Siaran</a>
        <a href="#" class="block px-3 py-2 rounded hover:bg-gray-100">Absensi</a>
        <a href="#" class="block px-3 py-2 rounded hover:bg-gray-100">Pengaturan</a>
    </aside>

    {{-- Isi halaman --}}
    <main class="ml-64 pt-16 p-6">
        @yield('content')
    </main>

</body>
</html>
