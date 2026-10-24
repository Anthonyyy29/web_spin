<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Spin The Radio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">

    {{-- Sidebar --}}
    <aside class="border-b border-line bg-white md:fixed md:inset-y-0 md:left-0 md:flex md:w-60 md:flex-col md:border-r md:border-b-0">
        <div class="flex items-center justify-between px-5 pt-4 md:block md:pt-6">
            <img src="{{ asset('images/logo.png') }}" alt="Spin The Radio" class="h-12 w-auto md:h-20">
        </div>

        <nav aria-label="Menu utama" class="flex gap-1 overflow-x-auto px-3 py-3 md:mt-6 md:flex-col md:overflow-visible md:py-0">
            @yield('nav')
        </nav>

        <div class="hidden border-t border-line px-5 py-4 md:mt-auto md:block">
            <p class="text-sm font-medium">{{ Auth::user()->username }}</p>
            <p class="text-sm text-muted">@yield('role')</p>
            <form method="POST" action="{{ route('logout') }}" class="mt-3">
                @csrf
                <button type="submit" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Isi halaman --}}
    <main class="px-4 py-6 md:ml-60 md:px-10 md:py-10">
        <div class="mx-auto max-w-5xl">
            @yield('content')
        </div>

        <form method="POST" action="{{ route('logout') }}" class="mt-10 md:hidden">
            @csrf
            <button type="submit" class="text-sm text-muted underline underline-offset-4">Keluar ({{ Auth::user()->username }})</button>
        </form>
    </main>

</body>
</html>
