<?php

use App\Models\Content;
use App\Models\Crew;
use App\Models\Episode;
use App\Models\News;
use App\Models\Sponsor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

uses(RefreshDatabase::class);

test('halaman publik terbuka dan root mengarah ke home', function (string $url) {
    $this->get($url)->assertOk();
})->with(['/home', '/listen', '/news', '/aboutus', '/crew']);

test('root dialihkan ke home', function () {
    $this->get('/')->assertRedirect('/home');
});

test('home menampilkan podcast dan crew terurut jabatan', function () {
    $content = Content::factory()->create(['title' => 'Segmen Satu']);
    Crew::factory()->create(['name' => 'Bendahara Budi', 'role' => 'Bendahara']);
    Crew::factory()->create(['name' => 'Manajer Mira', 'role' => 'General Manager']);

    $this->get('/home')
        ->assertOk()
        ->assertSeeInOrder(['listen/content/', 'crew-slider']);

    expect(Crew::sortedByRole()->pluck('name')->all())->toBe(['Manajer Mira', 'Bendahara Budi']);
});

test('segmen dan episode', function () {
    $content = Content::factory()->create(['title' => 'Segmen Satu']);
    $episode = Episode::factory()->create(['id_content' => $content->id, 'description' => 'Isi episode unik']);

    $this->get('/listen/content/'.$content->id)->assertOk()->assertSee('Segmen Satu')->assertSee('Isi episode unik');
    $this->get('/listen/content/999999')->assertNotFound();
    $this->get('/listen/search?query='.urlencode($episode->title))->assertOk()->assertSee('Result Podcast');
    $this->get('/listen/search?query=tidakada')->assertRedirect('/listen')->assertSessionHas('error', 'Podcast not found');
    $this->getJson('/api/podcastlist')->assertOk()->assertJsonPath('Segmen Satu.0.label', $episode->title);
});

test('berita: daftar, kategori, dan detail', function () {
    $news = News::factory()->create(['title' => 'Judul Berita Unik', 'category' => 'Campus Buzz']);

    $this->get('/news')->assertOk()->assertSee('Judul Berita Unik');
    $this->get('/news/category/Campus Buzz')->assertOk()->assertSee('Judul Berita Unik');
    $this->get('/news/details/'.$news->id)->assertOk()->assertSee('Judul Berita Unik');
    $this->get('/news/details/999999')->assertNotFound();
});

test('deskripsi berita dipotong per jumlah kata', function () {
    $news = News::factory()->make(['description' => "satu dua tiga\nempat lima enam"]);

    expect($news->descriptionRows(4))->toBe(['satu dua tiga', 'empat ...'])
        ->and(News::extractInstagramReelId('https://www.instagram.com/reel/AbC_1-x/?igsh=1'))->toBe('AbC_1-x')
        ->and(News::extractInstagramReelId('https://contoh.com'))->toBeNull();
});

test('about us menampilkan sponsor dan mengirim pesan kontak', function () {
    Sponsor::factory()->create(['image' => 'logo-sponsor.png']);
    $this->get('/aboutus')->assertSee('logo-sponsor.png');

    Mail::fake();
    $this->post('/aboutus/sendmail', ['name' => 'Budi', 'email' => 'budi@contoh.id', 'subject' => 'Halo', 'message' => 'Pesan uji'])
        ->assertRedirect('/aboutus')->assertSessionHas('success');
    $this->post('/aboutus/sendmail', ['name' => 'B'])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);
});
