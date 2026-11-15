<?php

use App\Models\AuditLog;
use App\Models\Content;
use App\Models\Crew;
use App\Models\Episode;
use App\Models\News;
use App\Models\Sponsor;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

function masukSebagai(string $role): void
{
    test()->actingAs(User::factory()->role($role)->create());
}

afterEach(function () {
    foreach (glob(public_path('assets/uploads/{,crew/,program/,news/,sponsor/}*_*.png'), GLOB_BRACE) ?: [] as $berkas) {
        if (filemtime($berkas) > time() - 120) {
            @unlink($berkas);
        }
    }
});

test('menu admin hanya untuk admin', function (string $role, int $status) {
    masukSebagai($role);

    foreach (['/dashboard/crew', '/dashboard/podcast', '/dashboard/news', '/dashboard/sponsor'] as $url) {
        $this->get($url)->assertStatus($status);
    }
})->with([['admin', 200], ['hr', 403]]);

test('crew: simpan dengan gambar, ubah, filter, dan hapus tercatat di audit log', function () {
    masukSebagai('admin');

    $this->post('/dashboard/crew/save', ['name' => 'Budi', 'role' => 'Announcer', 'image' => UploadedFile::fake()->image('a.png')])
        ->assertSessionHas('success', 'Data berhasil disimpan.');

    $crew = Crew::firstOrFail();
    expect(public_path('assets/uploads/'.$crew->image))->toBeFile();

    $this->travel(2)->seconds();
    $this->post('/dashboard/crew/save', ['id' => $crew->id, 'name' => 'Budi B', 'role' => 'Announcer'])->assertSessionHas('success');
    expect($crew->fresh()->name)->toBe('Budi B');

    $this->get('/dashboard/crew?filter=Announcer')->assertOk()->assertSee('Budi B');
    $this->get('/dashboard/crew?filter=Bendahara')->assertOk()->assertDontSee('Budi B');
    $this->get('/dashboard/crew/filter/Announcer')->assertRedirect('/dashboard/crew?filter=Announcer');

    $this->travel(2)->seconds();
    $this->get('/dashboard/crew/delete/'.$crew->id)->assertSessionHas('success', 'Data berhasil dihapus.');
    expect(Crew::count())->toBe(0)
        ->and(public_path('assets/uploads/'.$crew->image))->not->toBeFile()
        ->and(AuditLog::pluck('action')->all())->toBe(['CREATE', 'UPDATE', 'DELETE']);
});

test('simpan menolak gambar salah dan jabatan di luar daftar', function () {
    masukSebagai('admin');

    $this->post('/dashboard/crew/save', ['name' => 'Budi', 'role' => 'Announcer', 'image' => UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')])
        ->assertSessionHas('error');
    $this->travel(2)->seconds();
    $this->post('/dashboard/crew/save', ['name' => 'Budi', 'role' => 'Bos Besar', 'image' => UploadedFile::fake()->image('a.png')])
        ->assertSessionHas('error');
    expect(Crew::count())->toBe(0);
});

test('news mengubah tautan reel jadi id dan podcast menyimpan episode dengan embed spotify', function () {
    masukSebagai('admin');

    $this->post('/dashboard/news/save', [
        'title' => 'Judul', 'category' => 'Campus Buzz', 'description' => 'Isi', 'video' => 'https://www.instagram.com/reel/XyZ123/',
        'image' => UploadedFile::fake()->image('n.png'),
    ])->assertSessionHas('success');
    expect(News::firstOrFail()->video)->toBe('XyZ123');

    $content = Content::factory()->create();
    $this->travel(2)->seconds();
    $this->post('/dashboard/podcast/episode/save', [
        'id_content' => $content->id, 'title' => 'Eps', 'description' => 'Deskripsi', 'audio' => 'https://open.spotify.com/episode/ABC123?si=x',
    ])->assertSessionHas('success');
    expect(Episode::firstOrFail()->audio)->toBe('https://open.spotify.com/embed/episode/ABC123');

    $this->get('/dashboard/podcast/episode/open/'.$content->id)->assertOk()->assertSee('Eps');
    $this->get('/dashboard/podcast/open/'.$content->id)->assertRedirect('/dashboard/podcast');
    $this->get('/dashboard/sponsor')->assertOk();
    Sponsor::factory()->create();
});

test('api: unggah episode butuh token, cek episode hari ini', function () {
    config(['services.podcast_api.token' => 'rahasia-api']);
    $content = Content::factory()->create(['title' => 'Segmen Api']);
    $payload = ['content_name' => 'segmen api', 'title' => 'Eps', 'description' => 'D', 'audio' => 'https://open.spotify.com/episode/QWE'];

    $this->postJson('/api/uploadpodcast', $payload)->assertStatus(401);
    $this->postJson('/api/uploadpodcast', $payload, ['Authorization' => 'salah'])->assertStatus(403);
    $this->postJson('/api/uploadpodcast', $payload, ['Authorization' => 'rahasia-api'])->assertOk()->assertJson(['status' => true]);
    $this->postJson('/api/uploadpodcast', [...$payload, 'content_name' => 'tidak ada'], ['Authorization' => 'rahasia-api'])->assertStatus(404);

    $this->getJson('/api/checklatestpodcast/Segmen Api')->assertJson(['status' => true]);
    $this->getJson('/api/checklatestpodcast/Lain')->assertJson(['status' => false]);
    expect($content->episodes()->count())->toBe(1);
});

test('token API kosong di konfigurasi menolak semua permintaan', function () {
    config(['services.podcast_api.token' => null]);
    $this->postJson('/api/uploadpodcast', [], ['Authorization' => ''])->assertStatus(401);
    $this->postJson('/api/uploadpodcast', [], ['Authorization' => 'apa-saja'])->assertStatus(403);
});
