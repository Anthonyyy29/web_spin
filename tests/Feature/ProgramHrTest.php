<?php

use App\Models\Content;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('admin dan hr membuka menu podcast beserta episodenya', function (string $role) {
    $this->actingAs(User::factory()->role($role)->create());
    $content = Content::factory()->create();

    $this->get('/dashboard/podcast')->assertOk();
    $this->get('/dashboard/podcast/episode/open/'.$content->id)->assertOk();
})->with(['admin', 'hr']);

test('hr tetap ditolak di menu admin lain', function () {
    $this->actingAs(User::factory()->role('hr')->create());

    foreach (['/dashboard/crew', '/dashboard/news', '/dashboard/sponsor'] as $url) {
        $this->get($url)->assertForbidden();
    }
});

test('tamu diarahkan ke login', function () {
    $this->get('/dashboard/podcast')->assertRedirect('/login');
});

test('halaman program lama sudah tidak ada', function () {
    $this->actingAs(User::factory()->role('hr')->create());

    $this->get('/dashboard/absensi/program')->assertNotFound();
    $this->post('/dashboard/absensi/program/update')->assertNotFound();
});

test('menu samping hr memuat Program menuju podcast', function () {
    $this->actingAs(User::factory()->role('hr')->create());

    $this->get('/dashboard/podcast')
        ->assertSee('href="/dashboard/podcast"', false)
        ->assertDontSee('/dashboard/absensi/program');
});
