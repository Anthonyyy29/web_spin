<?php

use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

afterEach(function () {
    foreach (glob(public_path('assets/uploads/{,crew/,program/,news/,sponsor/}*_*.png'), GLOB_BRACE) ?: [] as $berkas) {
        if (filemtime($berkas) > time() - 120) {
            @unlink($berkas);
        }
    }
});

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

test('program yang punya sesi tidak bisa dihapus', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();
    $sesi = BroadcastSession::factory()->create(['content_id' => $content->id]);
    AbsensiLog::factory()->create(['session_id' => $sesi->id]);

    $this->get('/dashboard/podcast/delete/'.$content->id)
        ->assertSessionHas('error', 'Program masih punya sesi siaran. Hapus sesinya dulu di menu Sesi.');

    expect(Content::count())->toBe(1)
        ->and(BroadcastSession::count())->toBe(1)
        ->and(AbsensiLog::whereNotNull('session_id')->count())->toBe(1);
});

test('program tanpa sesi bisa dihapus', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();

    $this->get('/dashboard/podcast/delete/'.$content->id)->assertSessionHas('success');

    expect(Content::count())->toBe(0);
});

test('form program tidak lagi memuat bagian crew', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    Crew::factory()->create(['role' => 'Announcer']);
    Content::factory()->create();

    $this->get('/dashboard/podcast')
        ->assertOk()
        ->assertDontSee('name="crew_ids[]"', false)
        ->assertDontSee('crew ditugaskan')
        ->assertDontSee('data-crews', false);
});

test('simpan program dengan crew_ids tidak error dan crew_ids diabaikan', function () {
    $this->actingAs(User::factory()->role('hr')->create());

    $this->post('/dashboard/podcast/save', [
        'title' => 'Program Baru',
        'description' => 'Deskripsi',
        'image' => UploadedFile::fake()->image('a.png'),
        'crew_ids' => [9999],
    ])->assertSessionHas('success', 'Data berhasil disimpan.');

    expect(Content::where('title', 'Program Baru')->count())->toBe(1);
});

test('program menyimpan jam default dan mengosongkannya', function () {
    $this->actingAs(User::factory()->role('hr')->create());

    $this->post('/dashboard/podcast/save', [
        'title' => 'Program Jam', 'description' => 'Deskripsi', 'image' => UploadedFile::fake()->image('a.png'),
        'jam_mulai_default' => '10:00', 'jam_selesai_default' => '11:00',
    ])->assertSessionHas('success');

    $content = Content::firstOrFail();
    expect(substr($content->jam_mulai_default, 0, 5))->toBe('10:00')
        ->and(substr($content->jam_selesai_default, 0, 5))->toBe('11:00');

    $this->travel(2)->seconds();
    $this->post('/dashboard/podcast/save', [
        'id' => $content->id, 'title' => 'Program Jam', 'description' => 'Deskripsi',
        'jam_mulai_default' => '', 'jam_selesai_default' => '',
    ])->assertSessionHas('success');

    $content->refresh();
    expect($content->jam_mulai_default)->toBeNull()->and($content->jam_selesai_default)->toBeNull();
});

test('jam default ditolak bila hanya satu terisi, selesai tidak setelah mulai, atau format salah', function (string $mulai, string $selesai) {
    $this->actingAs(User::factory()->role('hr')->create());

    $this->post('/dashboard/podcast/save', [
        'title' => 'Program Jam', 'description' => 'Deskripsi', 'image' => UploadedFile::fake()->image('a.png'),
        'jam_mulai_default' => $mulai, 'jam_selesai_default' => $selesai,
    ])->assertSessionHas('error');

    expect(Content::count())->toBe(0);
})->with([
    ['10:00', ''],
    ['', '11:00'],
    ['11:00', '10:00'],
    ['10:00', '10:00'],
    ['25:99', '11:00'],
    ['10:00', 'jam'],
]);

test('mengubah program tanpa isian jam tidak menghapus jam default', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create(['jam_mulai_default' => '10:00', 'jam_selesai_default' => '11:00']);

    $this->post('/dashboard/podcast/save', [
        'id' => $content->id, 'title' => 'Judul Baru', 'description' => 'Deskripsi',
    ])->assertSessionHas('success');

    $content->refresh();
    expect(substr($content->jam_mulai_default, 0, 5))->toBe('10:00')
        ->and(substr($content->jam_selesai_default, 0, 5))->toBe('11:00');
});

test('modal program membawa jam default di tombol Edit', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    Content::factory()->create(['jam_mulai_default' => '10:00:00', 'jam_selesai_default' => '11:00:00']);

    $this->get('/dashboard/podcast')
        ->assertSee('data-jam-mulai="10:00"', false)
        ->assertSee('data-jam-selesai="11:00"', false);
});
