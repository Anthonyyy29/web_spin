<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Spin The Radio</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 relative flex items-center justify-center p-4">

    {{-- Tombol Back (pojok kiri atas) --}}
    <a href="{{ url()->previous() }}"
       class="absolute top-5 left-5 inline-flex items-center gap-2 rounded-lg bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow hover:bg-gray-100 transition">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
        </svg>
        Back
    </a>

    {{-- Card --}}
    <div class="w-full max-w-5xl rounded-2xl bg-white shadow-lg px-8 py-12 md:px-16">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-10 items-center">

            {{-- Logo --}}
            <div class="flex justify-center">
                <img src="{{ asset('images/logo.png') }}" alt="Spin The Radio" class="w-full max-w-md">
            </div>

            {{-- Form --}}
            <div>
                <h1 class="text-center text-4xl font-medium text-gray-900 mb-10">LOGIN</h1>

                @if ($errors->any())
                    <div class="mb-4 rounded-lg bg-red-50 px-4 py-3 text-sm text-red-600">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}" class="space-y-5">
                    @csrf

                    <div>
                        <label for="username" class="block mb-2 text-lg text-gray-900">Username</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                               placeholder="Your Username" required autofocus
                               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-700 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <div>
                        <label for="password" class="block mb-2 text-lg text-gray-900">Password</label>
                        <input type="password" id="password" name="password"
                               placeholder="Your Password" required
                               class="w-full rounded-lg border border-gray-300 px-4 py-3 text-gray-700 placeholder-gray-500 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-blue-600 py-3 text-lg font-medium text-white hover:bg-blue-700 transition">
                        Login
                    </button>
                </form>

                <p class="mt-6 text-center text-lg text-gray-900">
                    Return Home
                    <a href="{{ url('/') }}" class="text-blue-600 underline hover:text-blue-800">Here</a>
                </p>
            </div>
        </div>
    </div>

</body>
</html>