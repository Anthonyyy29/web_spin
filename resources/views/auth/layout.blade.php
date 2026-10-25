<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - Spin The Radio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-canvas font-sans text-ink antialiased">

    {{-- Header mobile: panel menu dibuka lewat tombol Menu --}}
    <header class="sticky top-0 z-20 flex items-center justify-between border-b border-line bg-white py-2 pr-2 pl-4 md:hidden">
        <img src="{{ asset('images/logo.png') }}" alt="Spin The Radio" class="h-8 w-auto">

        <details data-mobile-menu class="group">
            <summary class="flex min-h-11 cursor-pointer list-none items-center gap-2 rounded-md px-3 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal [&::-webkit-details-marker]:hidden">
                <span class="group-open:hidden">Menu</span>
                <span class="hidden group-open:inline">Tutup</span>
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="size-5 group-open:hidden"><path d="M3 5.5h14M3 10h14M3 14.5h14"/></svg>
                <svg aria-hidden="true" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" class="hidden size-5 group-open:block"><path d="M5 5l10 10M15 5 5 15"/></svg>
            </summary>

            <div class="absolute inset-x-0 top-full max-h-[calc(100dvh-100%)] overflow-y-auto border-b border-line bg-white shadow-sm">
                <nav aria-label="Menu utama" class="flex flex-col gap-1 px-3 py-3">
                    @yield('nav')
                </nav>
                <div class="border-t border-line px-4 py-4">
                    @include('auth.partials.account')
                </div>
            </div>
        </details>
    </header>

    {{-- Sidebar desktop --}}
    <aside class="hidden bg-white md:fixed md:inset-y-0 md:left-0 md:flex md:w-60 md:flex-col md:border-r md:border-line">
        <div class="px-5 pt-6">
            <img src="{{ asset('images/logo.png') }}" alt="Spin The Radio" class="h-20 w-auto">
        </div>

        <nav aria-label="Menu utama" class="mt-6 flex flex-col gap-1 px-3">
            @yield('nav')
        </nav>

        <div class="mt-auto border-t border-line px-5 py-4">
            @include('auth.partials.account')
        </div>
    </aside>

    {{-- Isi halaman --}}
    <main class="px-4 py-6 md:ml-60 md:px-10 md:py-10">
        <div class="mx-auto max-w-5xl">
            @yield('content')
        </div>
    </main>

</body>
</html>
