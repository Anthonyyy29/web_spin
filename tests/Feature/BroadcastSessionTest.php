<?php

use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use App\Models\RfidCrew;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

function sessionFor(Crew $crew, string $mulai = '10:00:00', string $selesai = '11:00:00', ?Carbon $tanggal = null): BroadcastSession
{
    $content = Content::factory()->create();
    $content->crews()->attach($crew);

    return BroadcastSession::factory()->create([
        'content_id' => $content->id,
        'tanggal' => ($tanggal ?? today())->toDateString(),
        'jam_mulai' => $mulai,
        'jam_selesai' => $selesai,
    ]);
}

test('findForTap: cocok di jendela, batas inklusif', function () {
    $crew = Crew::factory()->create();
    $session = sessionFor($crew);

    expect(BroadcastSession::findForTap($crew, today()->setTime(9, 0, 0))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(8, 59, 59)))->toBeNull()
        ->and(BroadcastSession::findForTap($crew, today()->setTime(11, 0, 0))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(11, 0, 1)))->toBeNull();
});

test('findForTap: tamu bila crew null atau di luar program atau hari lain', function () {
    $crew = Crew::factory()->create();
    $at = today()->setTime(10, 0, 0);
    sessionFor($crew);
    sessionFor(Crew::factory()->create(), '10:00:00', '11:00:00', today());
    $other = Crew::factory()->create();
    sessionFor($other, '10:00:00', '11:00:00', today()->addDay());

    expect(BroadcastSession::findForTap(null, $at))->toBeNull()
        ->and(BroadcastSession::findForTap(Crew::factory()->create(), $at))->toBeNull()
        ->and(BroadcastSession::findForTap($other, $at))->toBeNull();
});

test('findForTap: dua sesi cocok, pilih jam mulai terdekat', function () {
    $crew = Crew::factory()->create();
    $pertama = sessionFor($crew, '10:00:00', '11:00:00');
    $kedua = sessionFor($crew, '10:30:00', '11:30:00');

    expect(BroadcastSession::findForTap($crew, today()->setTime(10, 20, 0))?->id)->toBe($kedua->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(10, 10, 0))?->id)->toBe($pertama->id);
});

test('statusAt: Hadir sampai jam mulai, Terlambat sesudahnya', function () {
    $session = sessionFor(Crew::factory()->create());

    expect($session->statusAt(today()->setTime(10, 0, 0)))->toBe('Hadir')
        ->and($session->statusAt(today()->setTime(10, 0, 1)))->toBe('Terlambat')
        ->and($session->statusAt(today()->setTime(9, 30, 0)))->toBe('Hadir');
});

test('hapus sesi menjadikan log tamu', function () {
    $crew = Crew::factory()->create();
    $rfid = RfidCrew::factory()->create(['crew_id' => $crew->id]);
    $session = sessionFor($crew);
    $log = AbsensiLog::create([
        'rfid_uid' => $rfid->rfid_uid,
        'session_id' => $session->id,
        'tanggal' => today()->toDateString(),
        'jam_masuk' => '10:00:00',
        'status' => 'Hadir',
    ]);

    $session->delete();

    expect($log->fresh())->not->toBeNull()
        ->and($log->fresh()->session_id)->toBeNull();
});

test('findForTap: tap bermikrodetik tetap memilih sesi terdekat', function () {
    $crew = Crew::factory()->create();
    $pertama = sessionFor($crew, '10:00:00', '11:00:00');
    $kedua = sessionFor($crew, '10:00:01', '11:00:00');

    expect(BroadcastSession::findForTap($crew, today()->setTime(10, 0, 0, 345678))?->id)->toBe($pertama->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(10, 0, 0, 600000))?->id)->toBe($kedua->id);
});
