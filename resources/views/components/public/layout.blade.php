@props([
    'title' => 'Spin The Radio FM — Universitas Esa Unggul',
    'active' => '',
])
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $title }}</title>
    <link rel="icon" type="image/png" href="{{ asset('assets/ui/logo-spin.png') }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,400;0,500;0,600;0,700;0,800;0,900;1,800;1,900&family=Shrikhand&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/css/public-ui.css') }}" />
</head>

<body>
    <x-public.navbar :active="$active" />

    <main class="spin-main">
        {{ $slot }}
    </main>

    <x-public.footer />
    <x-public.toast />

    <script src="{{ asset('assets/js/public-ui.js') }}" defer></script>
</body>

</html>
