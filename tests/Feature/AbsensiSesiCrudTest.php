<?php

use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\RfidCrew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('hr menambah, melihat, mengubah tanggalnya (diundur berminggu), dan menghapus sesi', function () {
    $content = Content::factory()->create(['title' => 'Pagi Ceria']);
    $this->actingAs(User::factory()->role('hr')->create());
    $tanggal = today()->addDay()->toDateString();

    $this->post('/dashboard/absensi/sesi/simpan', [
        'content_id' => $content->id, 'tanggal' => $tanggal, 'jam_mulai' => '10:00', 'jam_selesai' => '11:00',
    ])->assertRedirect('/dashboard/absensi/sesi')->assertSessionHas('success');

    $sesi = BroadcastSession::firstOrFail();
    expect($sesi->content_id)->toBe($content->id);

    $this->get('/dashboard/absensi/sesi')->assertOk()->assertSee('Pagi Ceria');

    $this->travel(2)->seconds();
    $baru = today()->addDays(21)->toDateString();
    $this->post('/dashboard/absensi/sesi/update', [
        'id' => $sesi->id, 'content_id' => $content->id, 'tanggal' => $baru, 'jam_mulai' => '10:00', 'jam_selesai' => '11:00',
    ])->assertRedirect('/dashboard/absensi/sesi')->assertSessionHas('success');
    expect($sesi->fresh()->tanggal->toDateString())->toBe($baru);

    BroadcastSession::factory()->create(['content_id' => Content::factory()->create(['title' => 'Malam Lalu']), 'tanggal' => today()->subDay()->toDateString()]);
    $this->get('/dashboard/absensi/sesi?tampil=riwayat')->assertOk()->assertSee(today()->subDay()->format('d-m-Y'))->assertDontSee(today()->addDays(21)->format('d-m-Y'));

    $this->get("/dashboard/absensi/sesi/delete/{$sesi->id}")->assertRedirect('/dashboard/absensi/sesi');
    expect(BroadcastSession::find($sesi->id))->toBeNull();
});

test('validasi sesi', function () {
    $content = Content::factory()->create();
    $this->actingAs(User::factory()->role('hr')->create());
    $valid = ['content_id' => $content->id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00'];

    $this->post('/dashboard/absensi/sesi/simpan', [...$valid, 'jam_selesai' => '10:00'])->assertSessionHasErrors('jam_selesai');

    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/sesi/simpan', [...$valid, 'jam_selesai' => '09:00'])->assertSessionHasErrors('jam_selesai');

    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/sesi/simpan', [...$valid, 'content_id' => 9999])->assertSessionHasErrors('content_id');

    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/sesi/simpan', [...$valid, 'tanggal' => 'bukan tanggal'])->assertSessionHasErrors('tanggal');

    expect(BroadcastSession::count())->toBe(0);
});

test('hapus sesi menjadikan absensinya tamu', function () {
    $sesi = BroadcastSession::factory()->create();
    $log = AbsensiLog::factory()->create(['session_id' => $sesi->id]);
    $this->actingAs(User::factory()->role('hr')->create());

    $this->get("/dashboard/absensi/sesi/delete/{$sesi->id}");

    expect($log->fresh())->not->toBeNull()->and($log->fresh()->session_id)->toBeNull();
});

test('peran lain ditolak', function () {
    $this->actingAs(User::factory()->role('admin')->create())->get('/dashboard/absensi/sesi')->assertForbidden();
});

test('tanggal sesi tidak bisa diubah bila sudah ada absensi', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $sesi = BroadcastSession::factory()->create(['tanggal' => today()->toDateString(), 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00']);
    AbsensiLog::factory()->create(['session_id' => $sesi->id]);

    $this->post('/dashboard/absensi/sesi/update', [
        'id' => $sesi->id, 'content_id' => $sesi->content_id, 'tanggal' => today()->addWeek()->toDateString(), 'jam_mulai' => '09:00', 'jam_selesai' => '11:00',
    ])->assertRedirect('/dashboard/absensi/sesi')->assertSessionHas('error');

    $sesi->refresh();
    expect($sesi->tanggal->toDateString())->toBe(today()->toDateString())
        ->and($sesi->jam_mulai)->toBe('10:00:00');
});

test('sesi yang sudah ada absensi tetap bisa diubah jamnya bila tanggal sama', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $sesi = BroadcastSession::factory()->create(['tanggal' => today()->toDateString(), 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00']);
    AbsensiLog::factory()->create(['session_id' => $sesi->id]);

    $this->post('/dashboard/absensi/sesi/update', [
        'id' => $sesi->id, 'content_id' => $sesi->content_id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '12:00',
    ])->assertRedirect('/dashboard/absensi/sesi')->assertSessionHas('success');

    expect(substr($sesi->fresh()->jam_selesai, 0, 5))->toBe('12:00');
});

test('halaman sesi memuat jam default tiap program dan tombol apply', function () {
    Content::factory()->create(['title' => 'Program Berjam', 'jam_mulai_default' => '10:00', 'jam_selesai_default' => '11:00']);
    Content::factory()->create(['title' => 'Program Polos', 'jam_mulai_default' => null, 'jam_selesai_default' => null]);
    $this->actingAs(User::factory()->role('hr')->create());

    $berjam = Content::where('title', 'Program Berjam')->first();

    $respons = $this->get('/dashboard/absensi/sesi')->assertOk();

    expect($respons->getContent())
        ->toMatch('/<option value="'.$berjam->id.'"\s+data-mulai="10:00"/');

    $respons
        ->assertSee('data-mulai="10:00"', false)
        ->assertSee('data-mulai=""', false)
        ->assertSee('data-selesai="11:00"', false)
        ->assertSee('Apply waktu default')
        ->assertSee('Program ini belum punya jam default');
});

test('simpan sesi dengan crew_ids menyimpan crew tiga jabatan', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();
    $ids = collect(['Announcer', 'Sound Operator', 'Human Resource'])
        ->map(fn (string $role) => Crew::factory()->create(['role' => $role])->id)->all();

    $this->post('/dashboard/absensi/sesi/simpan', [
        'content_id' => $content->id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'crew_ids' => $ids,
    ])->assertSessionHas('success');

    expect(BroadcastSession::firstOrFail()->crews()->pluck('crews.id')->all())->toEqualCanonicalizing($ids);
});

test('update sesi menambah dan melepas crew', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $sesi = BroadcastSession::factory()->create(['tanggal' => today()->toDateString(), 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00']);
    $lama = Crew::factory()->create(['role' => 'Announcer']);
    $baru = Crew::factory()->create(['role' => 'Sound Operator']);
    $sesi->crews()->attach($lama);

    $this->post('/dashboard/absensi/sesi/update', [
        'id' => $sesi->id, 'content_id' => $sesi->content_id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'crew_ids' => [$baru->id],
    ])->assertSessionHas('success');

    expect($sesi->crews()->pluck('crews.id')->all())->toBe([$baru->id]);
});

test('update sesi tanpa crew_ids melepas crew berhak dan mempertahankan crew tak berhak', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $sesi = BroadcastSession::factory()->create(['tanggal' => today()->toDateString(), 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00']);
    $berhak = Crew::factory()->create(['role' => 'Announcer']);
    $tidakBerhak = Crew::factory()->create(['role' => 'Web Development', 'extra_role' => null]);
    $sesi->crews()->attach([$berhak->id, $tidakBerhak->id]);

    $this->post('/dashboard/absensi/sesi/update', [
        'id' => $sesi->id, 'content_id' => $sesi->content_id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00',
    ])->assertSessionHas('success');

    expect($sesi->crews()->pluck('crews.id')->all())->toBe([$tidakBerhak->id]);
});

test('crew_ids tidak valid ditolak', function (string $kasus, string $kunci) {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();

    $crewIds = match ($kasus) {
        'jabatan lain' => [Crew::factory()->create(['role' => 'Web Development'])->id],
        'tidak ada' => [9999],
        'bukan array' => 'abc',
        'array bersarang' => [[Crew::factory()->create(['role' => 'Announcer'])->id]],
    };

    $this->post('/dashboard/absensi/sesi/simpan', [
        'content_id' => $content->id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'crew_ids' => $crewIds,
    ])->assertSessionHasErrors($kunci);

    expect(BroadcastSession::count())->toBe(0);
})->with([
    ['jabatan lain', 'crew_ids.0'],
    ['tidak ada', 'crew_ids.0'],
    ['bukan array', 'crew_ids'],
    ['array bersarang', 'crew_ids.0'],
]);

test('crew jabatan utama lain dengan extra_role berhak diterima', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();
    $crew = Crew::factory()->create(['role' => 'Bendahara', 'extra_role' => 'Announcer']);

    $this->post('/dashboard/absensi/sesi/simpan', [
        'content_id' => $content->id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'crew_ids' => [$crew->id],
    ])->assertSessionHas('success');

    expect(BroadcastSession::firstOrFail()->crews()->pluck('crews.id')->all())->toBe([$crew->id]);
});

test('tanggal sesi yang punya absensi tetap terkunci saat crew_ids dikirim', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $sesi = BroadcastSession::factory()->create(['tanggal' => today()->toDateString(), 'jam_mulai' => '10:00:00', 'jam_selesai' => '11:00:00']);
    AbsensiLog::factory()->create(['session_id' => $sesi->id]);
    $crew = Crew::factory()->create(['role' => 'Announcer']);

    $this->post('/dashboard/absensi/sesi/update', [
        'id' => $sesi->id, 'content_id' => $sesi->content_id, 'tanggal' => today()->addWeek()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'crew_ids' => [$crew->id],
    ])->assertSessionHas('error');

    expect($sesi->fresh()->tanggal->toDateString())->toBe(today()->toDateString());
});

test('sesi dibuat lewat form dengan crew_ids dipakai tap', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $content = Content::factory()->create();
    $announcer = Crew::factory()->create(['role' => 'Announcer']);
    RfidCrew::factory()->create(['rfid_uid' => 'AB12', 'crew_id' => $announcer->id]);

    $this->post('/dashboard/absensi/sesi/simpan', [
        'content_id' => $content->id, 'tanggal' => today()->toDateString(), 'jam_mulai' => '10:00', 'jam_selesai' => '11:00', 'crew_ids' => [$announcer->id],
    ])->assertSessionHas('success');

    $this->travelTo(today()->setTime(10, 30));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'success', 'jenis' => 'sesi']);
});

test('halaman sesi mengirim crewByRole terkelompok per jabatan sesi dan terurut nama', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    Crew::factory()->create(['name' => 'Web Dev', 'role' => 'Web Development']);
    $bendahara = Crew::factory()->create(['name' => 'Bendi', 'role' => 'Bendahara', 'extra_role' => 'Sound Operator']);
    $ganda = Crew::factory()->create(['name' => 'Anto', 'role' => 'Announcer', 'extra_role' => 'Sound Operator']);

    $response = $this->get('/dashboard/absensi/sesi')->assertOk();
    $crewByRole = $response->viewData('crewByRole');

    expect($crewByRole->keys()->all())->toBe(Crew::SESSION_ROLES);
    expect($crewByRole['Announcer']->pluck('id')->all())->toBe([$ganda->id]);
    expect($crewByRole['Sound Operator']->pluck('id')->all())->toBe([$ganda->id, $bendahara->id]);
    expect($crewByRole['Human Resource'])->toBeEmpty();
    $response->assertDontSee('Web Dev');
});

test('tabel sesi menampilkan kolom crew, data-crews, dan teks kosong', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $a = Crew::factory()->create(['name' => 'Alfa Crew', 'role' => 'Announcer']);
    $b = Crew::factory()->create(['name' => 'Beta Crew', 'role' => 'Sound Operator']);
    $sesi = BroadcastSession::factory()->create(['tanggal' => today()->addDay()->toDateString()]);
    $sesi->crews()->attach([$a->id, $b->id]);

    $this->get('/dashboard/absensi/sesi')->assertOk()
        ->assertSee('<th>Crew</th>', false)
        ->assertSee('Alfa Crew, Beta Crew')
        ->assertSee('data-crews="'.e($sesi->crews->pluck('id')->toJson()).'"', false);

    $sesi->crews()->detach();
    $this->get('/dashboard/absensi/sesi')->assertOk()->assertSee('Belum ada crew');
});

test('modal sesi memuat dropdown crew dengan id kotak centang unik per modal', function () {
    $this->actingAs(User::factory()->role('hr')->create());
    $crew = Crew::factory()->create(['role' => 'Announcer']);

    $this->get('/dashboard/absensi/sesi')->assertOk()
        ->assertSee('name="crew_ids[]"', false)
        ->assertSee("crew-tambah-announcer-{$crew->id}", false)
        ->assertSee("crew-edit-announcer-{$crew->id}", false);
});
