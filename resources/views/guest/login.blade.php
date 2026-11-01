<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - Spin The Radio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen flex-col items-center justify-center bg-canvas px-4 py-10 font-sans text-ink antialiased">

    <main class="w-full max-w-sm">
        <div class="rounded-lg border border-line bg-white p-6 md:p-8">
            <img src="{{ asset('images/logo.png') }}" alt="Spin The Radio" class="-mt-2 mb-4 h-24 w-auto">
            <h1 class="text-xl font-semibold tracking-tight">Masuk</h1>
            <p class="mt-1 text-sm text-muted">Untuk HR dan announcer Spin The Radio.</p>

            @if ($errors->any())
                <div role="alert" class="mt-5 rounded-md border border-danger/30 bg-danger/5 px-4 py-3 text-sm text-danger">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
                @csrf

                <div>
                    <label for="username" class="mb-1.5 block text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" value="{{ old('username') }}"
                           required autofocus autocomplete="username"
                           class="w-full rounded-md border border-line px-3 py-2 focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30">
                </div>

                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium">Kata sandi</label>
                    <input type="password" id="password" name="password"
                           required autocomplete="current-password"
                           class="w-full rounded-md border border-line px-3 py-2 focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30">
                </div>

                <button type="submit"
                        class="w-full rounded-md bg-signal-strong py-2.5 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">
                    Masuk
                </button>
            </form>
        </div>

        <p class="mt-6 text-center text-sm">
            <a href="{{ url('/') }}" class="text-muted underline underline-offset-4 hover:text-ink">Kembali ke beranda</a>
        </p>
    </main>

</body>
</html>
