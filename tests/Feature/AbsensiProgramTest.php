<?php

use App\Models\Content;
use App\Models\Crew;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('hr melihat program dan menyimpan crew-nya', function () {
    $content = Content::factory()->create(['title' => 'Pagi Ceria']);
    $siti = Crew::factory()->create(['name' => 'Siti']);
    $budi = Crew::factory()->create(['name' => 'Budi']);
    $this->actingAs(User::factory()->role('hr')->create());

    $this->get('/dashboard/absensi/program')->assertOk()->assertSee('Pagi Ceria')->assertSee('Siti');

    $this->post('/dashboard/absensi/program/update', ['content_id' => $content->id, 'crew_ids' => [$siti->id, $budi->id]])
        ->assertRedirect('/dashboard/absensi/program')
        ->assertSessionHas('success', 'Crew program diperbarui');
    expect($content->crews()->pluck('crews.id')->all())->toEqualCanonicalizing([$siti->id, $budi->id]);

    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/program/update', ['content_id' => $content->id, 'crew_ids' => [$siti->id]]);
    expect($content->crews()->pluck('crews.id')->all())->toBe([$siti->id]);

    $this->travel(2)->seconds();
    $this->post('/dashboard/absensi/program/update', ['content_id' => $content->id]);
    expect($content->crews()->count())->toBe(0);
});

test('update menolak crew tak ada dan peran lain', function () {
    $content = Content::factory()->create();
    $this->actingAs(User::factory()->role('hr')->create());

    $this->post('/dashboard/absensi/program/update', ['content_id' => $content->id, 'crew_ids' => [9999]])
        ->assertSessionHasErrors('crew_ids.0');

    $this->actingAs(User::factory()->role('admin')->create())->get('/dashboard/absensi/program')->assertForbidden();
});
