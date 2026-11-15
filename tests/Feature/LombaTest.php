<?php

use App\Models\AnggotaTim;
use App\Models\PendaftaranLomba;
use App\Models\PengumpulanKarya;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

afterEach(function () {
    foreach (glob(public_path('assets/uploads/lomba/*/*_*')) ?: [] as $berkas) {
        if (filemtime($berkas) > time() - 120) {
            @unlink($berkas);
        }
    }
});

function recaptchaLolos(): void
{
    config(['services.recaptcha.secret' => 'rahasia']);
    Http::fake(['www.google.com/*' => Http::response(['success' => true])]);
}

function dataDaftar(array $ubah = []): array
{
    return [
        'pilih_lomba' => 'poster', 'nama_lengkap' => 'Ani', 'instagram' => '@ani', 'email' => 'ani@contoh.id', 'no_wa' => '0812',
        'bukti_pembayaran' => UploadedFile::fake()->create('b.pdf', 10, 'application/pdf'),
        'kartu_identitas' => UploadedFile::fake()->create('k.pdf', 10, 'application/pdf'),
        'g-recaptcha-response' => 'ok',
        ...$ubah,
    ];
}

test('pendaftaran ditolak tanpa reCAPTCHA yang valid', function () {
    $this->post('/lomba/daftar', dataDaftar(['g-recaptcha-response' => '']))->assertSessionHas('error', 'reCAPTCHA verification failed.');

    config(['services.recaptcha.secret' => 'rahasia']);
    Http::fake(['www.google.com/*' => Http::response(['success' => false])]);
    $this->post('/lomba/daftar', dataDaftar())->assertSessionHas('error', 'reCAPTCHA verification failed.');
    expect(PendaftaranLomba::count())->toBe(0);
});

test('mendaftar perorangan memasang cookie lalu tidak bisa daftar lagi sebelum reset', function () {
    recaptchaLolos();

    $this->post('/lomba/daftar', dataDaftar())->assertRedirect('/lomba/whatsapp-group')->assertCookie('sudahDaftar');
    expect(PendaftaranLomba::firstOrFail()->nama_lengkap)->toBe('Ani');

    $sudah = ['sudahDaftar' => 'true'];
    $this->withCookies($sudah)->get('/lomba/pendaftaran')->assertRedirect('/lomba/whatsapp-group');
    $this->withCookies($sudah)->get('/lomba/whatsapp-group')->assertOk();
    $this->withCookies($sudah)->get('/lomba/reset-pendaftaran')->assertRedirect('/lomba/pendaftaran');
});

test('whatsapp group menolak yang belum mendaftar', function () {
    $this->get('/lomba/whatsapp-group')->assertRedirect('/lomba');
});

test('tim: maksimal tiga anggota dan nama tim unik per lomba', function () {
    recaptchaLolos();
    $tim = fn (array $ubah = []) => dataDaftar(['pilih_lomba' => 'podcast', 'nama_tim' => 'Alfa', 'anggota' => ['A', 'B'], ...$ubah]);

    $this->post('/lomba/daftar', $tim())->assertRedirect('/lomba/whatsapp-group');
    expect(PendaftaranLomba::first()->nama_tim)->toBe('Tim Alfa')->and(AnggotaTim::count())->toBe(2);

    $this->post('/lomba/daftar', $tim())->assertSessionHas('error', fn ($pesan) => str_contains($pesan, 'Nama tim sudah terdaftar'));
    $this->post('/lomba/daftar', $tim(['nama_tim' => 'Beta', 'anggota' => ['1', '2', '3', '4']]))
        ->assertSessionHas('error', 'Jumlah anggota tim tidak boleh lebih dari 3 orang.');
    expect(PendaftaranLomba::count())->toBe(1);
});

test('mengumpulkan karya: tim harus terdaftar dan hanya sekali', function () {
    recaptchaLolos();
    PendaftaranLomba::factory()->create(['jenis_lomba' => 'konten', 'nama_tim' => 'Tim Alfa']);
    $karya = ['jenis_lomba' => 'konten', 'nama_tim' => 'Alfa', 'judul_karya' => 'Karya Satu', 'link_gdrive' => 'https://drive.google.com/a', 'link_instagram' => 'https://instagram.com/p/a', 'g-recaptcha-response' => 'ok'];

    $this->post('/lomba/submit-karya', [...$karya, 'nama_tim' => 'Zzz'])->assertSessionHas('error', 'Nama Tim tidak ditemukan.');
    $this->post('/lomba/submit-karya', $karya)->assertSessionHas('success', 'Pengumpulan karya berhasil!');
    $this->post('/lomba/submit-karya', $karya)->assertSessionHas('error', 'Karya untuk tim ini sudah pernah dikumpulkan.');
    $this->post('/lomba/submit-karya', [...$karya, 'link_gdrive' => 'bukan url'])->assertSessionHas('error', fn ($p) => str_contains($p, 'Format URL Google Drive tidak valid.'));
    expect(PengumpulanKarya::count())->toBe(1);
});

test('poster wajib surat pernyataan', function () {
    recaptchaLolos();
    $karya = ['jenis_lomba' => 'poster', 'nama_peserta' => 'Ani', 'judul_karya' => 'Poster', 'link_gdrive' => 'https://d.com/a', 'link_instagram' => 'https://i.com/a', 'g-recaptcha-response' => 'ok'];

    $this->post('/lomba/submit-karya', $karya)->assertSessionHas('error', fn ($p) => str_contains($p, 'Surat Pernyataan wajib diunggah.'));
    $this->post('/lomba/submit-karya', [...$karya, 'surat_pernyataan' => UploadedFile::fake()->create('s.pdf', 10, 'application/pdf')])->assertSessionHas('success');
    expect(PengumpulanKarya::firstOrFail()->surat_pernyataan)->not->toBeNull();
});

test('panitia: dashboard, daftar peserta, ekspor, hapus; jenis tak dikenal 404', function () {
    $peserta = PendaftaranLomba::factory()->create(['jenis_lomba' => 'poster', 'nama_lengkap' => 'Ani Peserta']);
    PengumpulanKarya::factory()->create(['jenis_lomba' => 'poster', 'judul_karya' => 'Poster Ani']);
    $this->actingAs(User::factory()->role('panitia')->create());

    $this->get('/dashboard')->assertOk();
    $this->get('/dashboard/lomba/jenis/poster')->assertOk()->assertSee('Ani Peserta');
    $this->get('/dashboard/lomba/jenis/poster?export=true')->assertOk()->assertDownload();
    $this->get('/dashboard/lomba/karya/poster')->assertOk()->assertSee('Poster Ani');
    $this->get('/dashboard/lomba/jenis/lain')->assertNotFound();
    $this->get('/dashboard/lomba/karya/presenter')->assertNotFound();
    $this->get('/dashboard/lomba/jenis/podcast?export=true')->assertSessionHas('error', 'Data Kosong.');

    $this->get('/dashboard/lomba/hapus/'.$peserta->id)->assertSessionHas('success');
    expect(PendaftaranLomba::count())->toBe(0);
});

test('lomba hanya untuk panitia', function () {
    $this->actingAs(User::factory()->role('admin')->create())->get('/dashboard/lomba/jenis/poster')->assertForbidden();
});
