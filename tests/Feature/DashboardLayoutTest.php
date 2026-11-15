<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Blade;

uses(RefreshDatabase::class);

function tampilkanLayout(string $navmenu = 'Dashboard'): string
{
    return Blade::render('<x-layouts.dashboard :navmenu="$navmenu">ISI-HALAMAN</x-layouts.dashboard>', ['navmenu' => $navmenu]);
}

test('layout memuat kerangka adminlte dan nama peran', function (string $role, string $nama) {
    $this->actingAs(User::factory()->role($role)->create());
    $html = tampilkanLayout();

    expect($html)->toContain('<title>Admin | Dashboard</title>')
        ->toContain('Spin Radio Admin')
        ->toContain('assets/dashboard/css/adminlte.min.css')
        ->toContain('assets/dashboard/js/adminlte.min.js')
        ->toContain($nama)
        ->toContain('Sign out')
        ->toContain(route('logout'))
        ->toContain('ISI-HALAMAN');
})->with([['admin', 'Admin'], ['hr', 'Hr'], ['panitia', 'Panitia']]);

test('judul mengikuti navmenu', function () {
    $this->actingAs(User::factory()->create());
    expect(tampilkanLayout('History'))->toContain('<title>Admin | History</title>');
});

test('pesan kilat tidak memutus skrip', function () {
    $this->actingAs(User::factory()->create());
    $pesan = '"></script><script>alert(1)</script>';
    session(['success' => $pesan]);

    expect(tampilkanLayout())->not->toContain($pesan)->toContain('alert(1)');
});
