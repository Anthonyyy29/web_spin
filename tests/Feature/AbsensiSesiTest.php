<?php

use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\RfidCrew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function siapkanSesiHariIni(): BroadcastSession
{
    $crew = Crew::factory()->create();
    RfidCrew::factory()->create(['rfid_uid' => 'AB12', 'crew_id' => $crew->id]);
    $content = Content::factory()->create();
    $content->crews()->attach($crew);

    return BroadcastSession::factory()->create([
        'content_id' => $content->id,
        'tanggal' => today()->toDateString(),
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:00:00',
    ]);
}

test('tap di jendela sesi mencatat absen sesi', function () {
    $sesi = siapkanSesiHariIni();
    $this->travelTo(today()->setTime(10, 30));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson([
            'status' => 'success',
            'message' => 'Absen berhasil',
            'jenis' => 'sesi',
            'program' => $sesi->content->title,
            'status_absen' => 'Hadir',
        ]);

    expect(AbsensiLog::first()->session_id)->toBe($sesi->id);
});

test('tap setelah jam selesai dicatat Terlambat', function () {
    siapkanSesiHariIni();
    $this->travelTo(today()->setTime(11, 5));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'success', 'status_absen' => 'Terlambat']);

    expect(AbsensiLog::first()->status)->toBe('Terlambat');
});

test('tap sebelum jam mulai menjadi tamu', function () {
    siapkanSesiHariIni();
    $this->travelTo(today()->setTime(9, 55));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'success', 'jenis' => 'tamu']);

    expect(AbsensiLog::first()->session_id)->toBeNull();
});

test('tap kedua di sesi yang sama ditolak', function () {
    siapkanSesiHariIni();
    $this->travelTo(today()->setTime(10, 5));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'success']);
    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'warning', 'message' => 'Anda sudah absen di sesi ini']);

    expect(AbsensiLog::count())->toBe(1);
});

test('tap di luar sesi menjadi tamu, satu kali sehari', function () {
    siapkanSesiHariIni();
    $this->travelTo(today()->setTime(14, 0));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'success', 'message' => 'Dicatat sebagai tamu', 'jenis' => 'tamu']);
    expect(AbsensiLog::first()->session_id)->toBeNull();

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['status' => 'warning', 'message' => 'Anda sudah absen hari ini']);

    expect(AbsensiLog::count())->toBe(1);
});

test('kartu tanpa crew tetap tamu tanpa error', function () {
    RfidCrew::create(['rfid_uid' => 'ZZ', 'crew_id' => null]);

    $this->postJson('/tap-absen', ['rfid_uid' => 'ZZ'])
        ->assertJson(['status' => 'success', 'jenis' => 'tamu', 'nama' => null]);
});

test('tamu tidak menghalangi absen sesi kemudian', function () {
    $sesi = siapkanSesiHariIni();

    $this->travelTo(today()->setTime(8, 0));
    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])->assertJson(['jenis' => 'tamu']);

    $this->travelTo(today()->setTime(10, 30));
    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])->assertJson(['jenis' => 'sesi']);

    expect(AbsensiLog::count())->toBe(2)
        ->and(AbsensiLog::whereNotNull('session_id')->first()->session_id)->toBe($sesi->id);
});

test('judul program dikirim apa adanya sebagai data JSON', function () {
    $sesi = siapkanSesiHariIni();
    $sesi->content->update(['title' => '<img src=x onerror=alert(1)>']);
    $this->travelTo(today()->setTime(10, 30));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['program' => '<img src=x onerror=alert(1)>']);
});

test('view absensi tidak memasukkan data hasil tap ke innerHTML', function () {
    $this->actingAs(User::factory()->role('hr')->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('${data.program}', false)
        ->assertSee('program.textContent = data.program', false);
});

test('tap sepersekian detik setelah jam selesai tetap dicatat di sesi', function () {
    siapkanSesiHariIni();
    $this->travelTo(today()->setTime(11, 0, 0, 400000));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])->assertJson(['jenis' => 'sesi']);
});

test('tap sepersekian detik setelah jam mulai tidak dianggap terlambat', function () {
    siapkanSesiHariIni();
    $this->travelTo(today()->setTime(10, 0, 0, 300000));

    $this->postJson('/tap-absen', ['rfid_uid' => 'AB12'])
        ->assertJson(['jenis' => 'sesi', 'status_absen' => 'Hadir', 'jam' => '10:00:00']);
});
