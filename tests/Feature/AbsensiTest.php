<?php

use App\Models\AbsensiLog;
use App\Models\Crew;
use App\Models\RfidCrew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('tap lewat alat: kartu tak dikenal, berhasil, lalu sudah absen', function () {
    $crew = Crew::factory()->create(['name' => 'Siti']);
    RfidCrew::create(['rfid_uid' => 'AB12', 'crew_id' => $crew->id]);

    $this->postJson('/tap-absen', ['rfid_uid' => ''])->assertJson(['status' => 'error', 'message' => 'UID tidak terbaca']);
    $this->postJson('/tap-absen', ['rfid_uid' => 'ZZ99'])->assertJson(['status' => 'error', 'message' => 'Kartu tidak terdaftar']);
    $this->get('/tap-absen?rfid_uid=AB12')->assertJson(['status' => 'success', 'nama' => 'Siti']);
    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])->assertJson(['status' => 'warning', 'message' => 'Anda sudah absen hari ini']);

    expect(AbsensiLog::count())->toBe(1);
});

test('hr melihat tabel hari ini, riwayat, dan ekspor excel; peran lain ditolak', function () {
    $crew = Crew::factory()->create(['name' => 'Siti', 'role' => 'Announcer']);
    RfidCrew::create(['rfid_uid' => 'AB12', 'crew_id' => $crew->id]);
    AbsensiLog::create(['rfid_uid' => 'AB12', 'tanggal' => today()->toDateString(), 'jam_masuk' => '08:01:00', 'status' => 'Hadir']);

    $this->actingAs(User::factory()->role('hr')->create());
    $this->get('/dashboard')->assertOk()->assertSee('Siti')->assertSee('08:01:00');
    $this->get('/dashboard/absensi/history')->assertOk();
    $this->getJson('/dashboard/absensi/history/all')->assertJsonPath('data.0.nama', 'Siti')->assertJsonPath('data.0.divisi', 'Announcer');
    $this->getJson('/dashboard/absensi/history/byUid/AB12')->assertJsonCount(1, 'data');
    $this->get('/dashboard/absensi/export-excel')->assertOk()->assertDownload();

    $this->actingAs(User::factory()->role('panitia')->create())->get('/dashboard/absensi/history')->assertForbidden();
});

test('kartu crew: tambah, tolak duplikat, ubah, hapus', function () {
    $crew = Crew::factory()->create();
    $lain = Crew::factory()->create();
    $this->actingAs(User::factory()->role('hr')->create());

    $this->post('/dashboard/absensi/crew/simpan', ['rfid_uid' => 'AB12', 'crew_id' => $crew->id])->assertSessionHas('success');
    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/crew/simpan', ['rfid_uid' => 'AB12', 'crew_id' => $lain->id])->assertSessionHas('error', 'RFID sudah terdaftar!');
    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/crew/update', ['rfid_lama' => 'AB12', 'rfid_uid' => 'CD34', 'crew_id' => $lain->id])->assertSessionHas('success');
    expect(RfidCrew::find('CD34')->crew_id)->toBe($lain->id)->and(RfidCrew::find('AB12'))->toBeNull();

    $this->get('/dashboard/absensi/crew')->assertOk()->assertSee('CD34');
    $this->get('/dashboard/absensi/crew/delete/CD34')->assertSessionHas('success');
    expect(RfidCrew::count())->toBe(0);
});
