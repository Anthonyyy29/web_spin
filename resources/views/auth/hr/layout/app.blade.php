<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'Laravel') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- Jika memakai Vite, ganti script CDN di atas dengan: @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
</head>
<body class="bg-gray-100 text-gray-800" x-data="{ sidebarOpen: false, profileOpen: false }">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    @include('auth.hr.layout.topbar')
    
    @include('auth.hr.layout.sidebar')

    {{-- Konten --}}
    <main class="pt-16 lg:pl-64 min-h-screen">
        <div class="p-6">
            @yield('content')
        </div>
    </main>

    <style>[x-cloak]{display:none !important;}</style>
</body>
</html>
