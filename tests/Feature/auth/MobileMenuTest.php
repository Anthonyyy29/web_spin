<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Get the markup of the mobile menu panel from a rendered page.
 */
function mobileMenu(string $html): string
{
    preg_match('/<details data-mobile-menu.*?<\/details>/s', $html, $matches);

    return $matches[0] ?? '';
}

it('puts every menu item, the account and the logout button in the mobile menu', function (string $role, string $url, array $labels) {
    $user = User::factory()->role($role)->create(['username' => 'Ani Lestari']);

    $menu = mobileMenu($this->actingAs($user)->get($url)->assertOk()->getContent());

    expect($menu)->toContain('Menu')
        ->toContain('Tutup')
        ->toContain('Ani Lestari')
        ->toContain($user->role->label)
        ->toContain('action="'.route('logout').'"')
        ->toContain('Keluar');

    foreach ($labels as $label) {
        expect($menu)->toContain($label);
    }
})->with([
    ['human_resource', '/hr/dashboard', ['Dashboard', 'Program', 'Sesi', 'Absensi', 'Pengaturan', 'Profil']],
    ['announcer', '/announcer/dashboard', ['Dashboard', 'Sesi saya', 'Profil']],
    ['sound_operator', '/so/sesi', ['Sesi saya', 'Profil']],
]);

it('marks the current page in the mobile menu', function () {
    $user = User::factory()->hr()->create();

    $menu = mobileMenu($this->actingAs($user)->get(route('sesi.index'))->getContent());

    expect($menu)->toMatch('/<a href="'.preg_quote(route('sesi.index'), '/').'"\s+aria-current="page"/');
});

it('labels a menu item that is not available yet in the mobile menu', function () {
    $menu = mobileMenu($this->actingAs(User::factory()->hr()->create())->get('/hr/dashboard')->getContent());

    expect($menu)->toContain('belum tersedia');
});

it('no longer shows the logout link at the bottom of the page', function () {
    $user = User::factory()->announcer()->create(['username' => 'Ani Lestari']);

    $this->actingAs($user)->get('/announcer/dashboard')
        ->assertDontSee('Keluar (Ani Lestari)');
});
