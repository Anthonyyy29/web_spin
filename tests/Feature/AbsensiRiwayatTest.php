<?php

use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\RfidCrew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function siapkanLogSesiDanTamu(): BroadcastSession
{
    $sesi = BroadcastSession::factory()->create([
        'content_id' => Content::factory()->create(['title' => 'Pagi Ceria'])->id,
        'jam_mulai' => '10:00:00',
    ]);

    $crewSesi = Crew::factory()->create(['name' => 'Sari Sesi']);
    RfidCrew::factory()->create(['rfid_uid' => 'S1', 'crew_id' => $crewSesi->id]);
    AbsensiLog::factory()->create(['rfid_uid' => 'S1', 'session_id' => $sesi->id, 'jam_masuk' => '09:58:00']);

    $crewTamu = Crew::factory()->create(['name' => 'Tono Tamu']);
    RfidCrew::factory()->create(['rfid_uid' => 'T1', 'crew_id' => $crewTamu->id]);
    AbsensiLog::factory()->create(['rfid_uid' => 'T1', 'session_id' => null, 'jam_masuk' => '14:00:00']);

    return $sesi;
}

beforeEach(function () {
    $this->actingAs(User::factory()->role('hr')->create());
});

test('history/all memisahkan sesi dan tamu, default sesi', function () {
    siapkanLogSesiDanTamu();

    $this->getJson('/dashboard/absensi/history/all?jenis=sesi')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nama', 'Sari Sesi')
        ->assertJsonPath('data.0.program', 'Pagi Ceria')
        ->assertJsonPath('data.0.jam_sesi', '10:00:00');

    $this->getJson('/dashboard/absensi/history/all?jenis=tamu')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nama', 'Tono Tamu')
        ->assertJsonPath('data.0.program', null)
        ->assertJsonPath('data.0.jam_sesi', null);

    $this->getJson('/dashboard/absensi/history/all')
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.nama', 'Sari Sesi');
});

test('ekspor mengikuti jenis', function () {
    siapkanLogSesiDanTamu();

    $this->get('/dashboard/absensi/export-excel?jenis=sesi')->assertOk()->assertDownload();
    $this->get('/dashboard/absensi/export-excel?jenis=tamu')->assertOk()->assertDownload();
});

test('dashboard memisahkan tamu dan hanya sesi di grafik status dan tren', function () {
    siapkanLogSesiDanTamu();

    $response = $this->get('/dashboard')->assertOk()->assertSee('Tamu Hari Ini')->assertSee('Tono Tamu');

    expect($response->viewData('chartStatus')->sum('total'))->toBe(1)
        ->and($response->viewData('chartTrend')->sum('total'))->toBe(1)
        ->and($response->viewData('absensi_hari_ini'))->toHaveCount(1)
        ->and($response->viewData('tamu_hari_ini'))->toHaveCount(1);
});

test('log tanpa sesi setelah sesi dihapus tidak merusak riwayat', function () {
    $sesi = siapkanLogSesiDanTamu();
    $sesi->delete();

    $this->getJson('/dashboard/absensi/history/all?jenis=tamu')->assertJsonCount(2, 'data');
    $this->get('/dashboard')->assertOk();
});
