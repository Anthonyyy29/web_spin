<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('public');

    $this->user = User::factory()->announcer()->create(['username' => 'Ani Lestari', 'password' => 'lama']);
});

it('shows the profile page in the area layout with the job title', function (string $role, string $navRoute) {
    $user = User::factory()->role($role)->create();

    $this->actingAs($user)->get(route('profil.edit'))
        ->assertOk()
        ->assertSee($user->role->label)
        ->assertSee(route($navRoute), false);
})->with([
    ['kadiv_human_resource', 'hr.dashboard'],
    ['announcer', 'announcer.sesi.index'],
    ['kadiv_sound_operator', 'so.sesi.index'],
]);

it('forbids the profile page for roles without an area', function () {
    $this->actingAs(User::factory()->role('creative')->create())->get(route('profil.edit'))->assertForbidden();
});

it('updates the username', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari Putri'])
        ->assertRedirect(route('profil.edit'))
        ->assertSessionHas('status', 'Profil disimpan.');

    expect($this->user->fresh()->username)->toBe('Ani Lestari Putri');
});

it('keeps the own username valid when it is unchanged', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari'])
        ->assertSessionHasNoErrors();
});

it('rejects a username that belongs to someone else', function () {
    User::factory()->create(['username' => 'Budi']);

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Budi'])
        ->assertSessionHasErrors(['username' => 'Username ini sudah dipakai.']);
});

it('stores a new photo and deletes the old one', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('a.jpg')]);
    $oldPath = $this->user->fresh()->photo_path;

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('b.png')]);
    $newPath = $this->user->fresh()->photo_path;

    expect($oldPath)->toStartWith('profile-photos/')->and($newPath)->not->toBe($oldPath);
    Storage::disk('public')->assertMissing($oldPath);
    Storage::disk('public')->assertExists($newPath);
});

it('keeps the photo when saving without a new one', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('a.jpg')]);
    $path = $this->user->fresh()->photo_path;

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari Putri']);

    expect($this->user->fresh()->photo_path)->toBe($path);
    Storage::disk('public')->assertExists($path);
});

it('rejects a photo that is not an image', function () {
    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')])
        ->assertSessionHasErrors(['photo' => 'Foto profil harus berupa gambar JPG, PNG, atau WebP.']);
});

it('ignores a role change sent with the profile form', function () {
    $hrRoleId = Role::where('name', 'human_resource')->value('id');

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'role_id' => $hrRoleId]);

    expect($this->user->fresh()->role->name)->toBe('announcer');
});

it('renders the stored photo with a url from the public disk', function () {
    config(['filesystems.default' => 'local', 'filesystems.disks.local.url' => 'http://other.test/files']);

    $this->actingAs($this->user)->put(route('profil.update'), ['username' => 'Ani Lestari', 'photo' => UploadedFile::fake()->image('a.jpg')]);
    $path = $this->user->fresh()->photo_path;

    $this->actingAs($this->user)->get(route('profil.edit'))
        ->assertOk()
        ->assertSee(Storage::disk('public')->url($path), false);
});
