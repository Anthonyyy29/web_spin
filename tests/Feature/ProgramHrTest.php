<?php

use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\RfidCrew;
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

test('hr membuat program beserta crew tiga jabatan', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $ids = collect(['Announcer', 'Sound Operator', 'Human Resource'])
        ->map(fn (string $role): int => Crew::factory()->create(['role' => $role])->id)
        ->all();

    $this->post('/dashboard/podcast/save', [
        'title' => 'Program Baru',
        'description' => 'Deskripsi',
        'image' => UploadedFile::fake()->image('a.png'),
        'crew_ids' => $ids,
    ])->assertSessionHas('success', 'Data berhasil disimpan.');

    expect(Content::firstOrFail()->crews()->pluck('crews.id')->all())->toEqualCanonicalizing($ids);
});

test('ubah program menyinkronkan tiga jabatan dan menjaga jabatan lain', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();
    $webdev = Crew::factory()->create(['role' => 'Web Development']);
    $lama = Crew::factory()->create(['role' => 'Announcer']);
    $operator = Crew::factory()->create(['role' => 'Sound Operator']);
    $baru = Crew::factory()->create(['role' => 'Announcer']);
    $content->crews()->attach([$webdev->id, $lama->id, $operator->id]);

    $this->post('/dashboard/podcast/save', [
        'id' => $content->id, 'title' => 'Judul', 'description' => 'Deskripsi', 'crew_ids' => [$baru->id],
    ])->assertSessionHas('success');

    expect($content->crews()->pluck('crews.id')->all())->toEqualCanonicalizing([$webdev->id, $baru->id]);

    $this->travel(2)->seconds();
    $this->post('/dashboard/podcast/save', [
        'id' => $content->id, 'title' => 'Judul', 'description' => 'Deskripsi',
    ])->assertSessionHas('success');

    expect($content->crews()->pluck('crews.id')->all())->toEqualCanonicalizing([$webdev->id]);
});

test('crew di luar tiga jabatan, tak ada, atau bukan array ditolak', function (string $kasus) {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();

    $crewIds = match ($kasus) {
        'jabatan lain' => [Crew::factory()->create(['role' => 'Web Development'])->id],
        'tidak ada' => [9999],
        'bukan array' => 'abc',
    };

    $this->post('/dashboard/podcast/save', [
        'id' => $content->id, 'title' => 'Judul', 'description' => 'Deskripsi', 'crew_ids' => $crewIds,
    ])->assertSessionHas('error');

    expect($content->crews()->count())->toBe(0);
})->with(['jabatan lain', 'tidak ada', 'bukan array']);

test('tap memakai crew yang ditugaskan lewat form', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $announcer = Crew::factory()->create(['role' => 'Announcer']);
    RfidCrew::factory()->create(['rfid_uid' => 'AB12', 'crew_id' => $announcer->id]);

    $this->post('/dashboard/podcast/save', [
        'title' => 'Program Baru',
        'description' => 'Deskripsi',
        'image' => UploadedFile::fake()->image('a.png'),
        'crew_ids' => [$announcer->id],
    ])->assertSessionHas('success');

    $content = Content::firstOrFail();
    BroadcastSession::factory()->create([
        'content_id' => $content->id,
        'tanggal' => today()->toDateString(),
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:00:00',
    ]);

    $this->travelTo(today()->setTime(9, 55));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'success', 'jenis' => 'sesi']);
});
