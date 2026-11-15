<?php

use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
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
