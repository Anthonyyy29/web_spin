<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fitur belum tersedia - Spin The Radio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-canvas px-4 font-sans text-ink antialiased">

    <main class="w-full max-w-sm rounded-lg border border-line bg-white p-8 text-center">
        <img src="{{ asset('images/logo.png') }}" alt="Spin The Radio" class="mx-auto h-20 w-auto">

        <p class="mt-6 text-sm font-medium">{{ Auth::user()->username }}</p>
        <p class="text-sm text-muted">{{ Auth::user()->role->label }}</p>

        <p class="mt-6 text-sm">Fitur untuk jabatanmu belum tersedia. Silakan keluar.</p>

        <form method="POST" action="{{ route('logout') }}" class="mt-6">
            @csrf
            <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Keluar</button>
        </form>
    </main>

</body>
</html>
