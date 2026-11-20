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
    $session = BroadcastSession::factory()->create([
        'content_id' => Content::factory()->create()->id,
        'tanggal' => ($tanggal ?? today())->toDateString(),
        'jam_mulai' => $mulai,
        'jam_selesai' => $selesai,
    ]);
    $session->crews()->attach($crew);

    return $session;
}

test('findForTap: cocok dari jam mulai sampai satu jam setelah selesai, batas inklusif', function () {
    $crew = Crew::factory()->create();
    $session = sessionFor($crew);

    expect(BroadcastSession::findForTap($crew, today()->setTime(9, 59, 59)))->toBeNull()
        ->and(BroadcastSession::findForTap($crew, today()->setTime(10, 0, 0))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(11, 0, 0))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(11, 0, 1))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(12, 0, 0))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(12, 0, 1)))->toBeNull();
});

test('findForTap: tamu bila crew null atau di luar sesi atau hari lain', function () {
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

test('findForTap: dua sesi, pilih yang sedang berjalan atau jam mulai terdekat', function () {
    $crew = Crew::factory()->create();
    $pertama = sessionFor($crew, '10:00:00', '11:00:00');
    $kedua = sessionFor($crew, '10:30:00', '11:30:00');

    expect(BroadcastSession::findForTap($crew, today()->setTime(10, 20, 0))?->id)->toBe($pertama->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(10, 40, 0))?->id)->toBe($kedua->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(11, 15, 0))?->id)->toBe($kedua->id);
});

test('findForTap: sesi yang sedang berjalan mengalahkan sesi yang akan Terlambat', function () {
    $crew = Crew::factory()->create();
    $panjang = sessionFor($crew, '10:00:00', '12:00:00');
    sessionFor($crew, '10:30:00', '10:45:00');

    expect(BroadcastSession::findForTap($crew, today()->setTime(10, 50, 0))?->id)->toBe($panjang->id);
});

test('findForTap: dua sesi sama-sama Terlambat, pilih jam mulai terdekat', function () {
    $crew = Crew::factory()->create();
    sessionFor($crew, '10:00:00', '11:00:00');
    $pendek = sessionFor($crew, '10:30:00', '10:45:00');

    expect(BroadcastSession::findForTap($crew, today()->setTime(11, 30, 0))?->id)->toBe($pendek->id);
});

test('statusAt: Hadir sampai jam selesai, Terlambat sesudahnya', function () {
    $session = sessionFor(Crew::factory()->create());

    expect($session->statusAt(today()->setTime(10, 0, 0)))->toBe('Hadir')
        ->and($session->statusAt(today()->setTime(11, 0, 0)))->toBe('Hadir')
        ->and($session->statusAt(today()->setTime(11, 0, 1)))->toBe('Terlambat');
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

test('findForTap: tap bermikrodetik memilih sesi yang sudah mulai', function () {
    $crew = Crew::factory()->create();
    $pertama = sessionFor($crew, '10:00:00', '11:00:00');
    sessionFor($crew, '10:00:01', '11:00:00');

    expect(BroadcastSession::findForTap($crew, today()->setTime(10, 0, 0, 345678))?->id)->toBe($pertama->id)
        ->and(BroadcastSession::findForTap($crew, today()->setTime(10, 0, 0, 600000))?->id)->toBe($pertama->id);
});

test('findForTap: crew program yang sama tetapi tidak ditugaskan ke sesi tetap tamu', function () {
    $a = Crew::factory()->create();
    $b = Crew::factory()->create();
    $content = Content::factory()->create();
    $content->crews()->attach([$a->id, $b->id]);
    $session = BroadcastSession::factory()->create([
        'content_id' => $content->id,
        'tanggal' => today()->toDateString(),
        'jam_mulai' => '10:00:00',
        'jam_selesai' => '11:00:00',
    ]);
    $session->crews()->attach($a);

    expect(BroadcastSession::findForTap($a, today()->setTime(10, 30))?->id)->toBe($session->id)
        ->and(BroadcastSession::findForTap($b, today()->setTime(10, 30)))->toBeNull();
});

test('syncCrews menyimpan id terpilih dan menggantikan yang lama', function () {
    $session = BroadcastSession::factory()->create();
    $lama = Crew::factory()->create(['role' => 'Announcer']);
    $baru = Crew::factory()->create(['role' => 'Sound Operator']);
    $session->crews()->attach($lama);

    $session->syncCrews([$baru->id]);

    expect($session->crews()->pluck('crews.id')->all())->toBe([$baru->id]);
});

test('syncCrews menyimpan id ganda satu baris', function () {
    $session = BroadcastSession::factory()->create();
    $a = Crew::factory()->create(['role' => 'Announcer']);

    $session->syncCrews([$a->id, $a->id]);

    expect($session->crews()->pluck('crews.id')->all())->toBe([$a->id]);
});

test('syncCrews mempertahankan crew yang tak lagi berhak', function () {
    $session = BroadcastSession::factory()->create();
    $webdev = Crew::factory()->create(['role' => 'Web Development', 'extra_role' => null]);
    $session->crews()->attach($webdev);

    $session->syncCrews([]);

    expect($session->crews()->pluck('crews.id')->all())->toBe([$webdev->id]);
});

test('syncCrews melepas crew berhak yang tidak dipilih', function () {
    $session = BroadcastSession::factory()->create();
    $announcer = Crew::factory()->create(['role' => 'Announcer']);
    $session->crews()->attach($announcer);

    $session->syncCrews([]);

    expect($session->crews()->count())->toBe(0);
});
