<?php

use App\Http\Requests\auth\hr\ProgramRequest;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-10-24 10:00:00');

    $this->hr = User::factory()->hr()->create();
    $this->announcer = User::factory()->announcer()->create();
    $this->so = User::factory()->so()->create();

    $this->payload = fn (array $overrides = []): array => array_merge([
        'name' => 'Pagi Ceria',
        'day_of_week' => 1,
        'crewcall_time' => '07:30',
        'tapping_start' => '08:00',
        'tapping_end' => '09:30',
        'period_start' => '2026-10-26',
        'period_end' => '2026-11-16',
        'announcer_ids' => [$this->announcer->id],
        'so_ids' => [$this->so->id],
    ], $overrides);
});

it('lets hr create a program and generates its sessions', function () {
    $this->actingAs($this->hr)
        ->post(route('program.store'), ($this->payload)())
        ->assertRedirect(route('program.index'))
        ->assertSessionHas('status');

    $program = Program::where('name', 'Pagi Ceria')->firstOrFail();

    expect($program->crewcall_time)->toBe('07:30:00')
        ->and($program->tapping_start)->toBe('08:00:00')
        ->and($program->tapping_end)->toBe('09:30:00')
        ->and($program->sessions)->toHaveCount(4)
        ->and($program->crew()->wherePivot('position', 'announcer')->pluck('users.id')->all())->toBe([$this->announcer->id])
        ->and($program->crew()->wherePivot('position', 'so')->pluck('users.id')->all())->toBe([$this->so->id]);

    $session = $program->sessions()->orderBy('regular_date')->first();

    expect($session->regular_date->toDateString())->toBe('2026-10-26')
        ->and($session->crew)->toHaveCount(2);
});

it('lets hr create a program without sound operators', function () {
    $this->actingAs($this->hr)
        ->post(route('program.store'), ($this->payload)(['so_ids' => null]))
        ->assertRedirect(route('program.index'));

    expect(Program::firstOrFail()->crew)->toHaveCount(1);
});

it('validates times, period and crew roles with indonesian messages', function () {
    $this->actingAs($this->hr)
        ->post(route('program.store'), ($this->payload)([
            'crewcall_time' => '08:00',
            'tapping_start' => '08:00',
            'tapping_end' => '07:00',
            'period_end' => '2026-10-01',
            'announcer_ids' => [$this->so->id],
            'so_ids' => [$this->announcer->id],
        ]))
        ->assertSessionHasErrors([
            'tapping_start' => 'Tapping mulai harus setelah crewcall.',
            'tapping_end' => 'Tapping selesai harus setelah tapping mulai.',
            'period_end' => 'Periode selesai harus sama dengan atau setelah periode mulai.',
            'announcer_ids.0' => 'Announcer yang dipilih tidak valid.',
            'so_ids.0' => 'Sound operator (SO) yang dipilih tidak valid.',
        ]);

    expect(Program::count())->toBe(0);
});

it('requires a name, a valid weekday and at least one announcer', function () {
    $this->actingAs($this->hr)
        ->post(route('program.store'), ($this->payload)([
            'name' => '',
            'day_of_week' => 8,
            'announcer_ids' => [],
        ]))
        ->assertSessionHasErrors([
            'name' => 'Nama program wajib diisi.',
            'day_of_week' => 'Hari yang dipilih tidak valid.',
            'announcer_ids' => 'Announcer wajib diisi.',
        ]);
});

it('rejects the same person in two positions', function () {
    $request = ProgramRequest::create(route('program.store'), 'POST', [
        'announcer_ids' => [$this->announcer->id],
        'so_ids' => [$this->announcer->id],
    ]);
    $validator = Validator::make([], []);

    foreach ($request->after() as $callback) {
        $callback($validator);
    }

    expect($validator->errors()->get('so_ids'))
        ->toBe(['Satu orang tidak boleh menjadi announcer dan sound operator (SO) sekaligus.']);
});

it('rejects a person whose role does not match the position', function () {
    $this->actingAs($this->hr)
        ->post(route('program.store'), ($this->payload)([
            'announcer_ids' => [$this->announcer->id],
            'so_ids' => [$this->announcer->id],
        ]))
        ->assertSessionHasErrors('so_ids.0');

    expect(Program::count())->toBe(0);
});

it('shows crew validation errors on the form', function () {
    $this->actingAs($this->hr)
        ->followingRedirects()
        ->from(route('program.create'))
        ->post(route('program.store'), ($this->payload)(['announcer_ids' => [], 'so_ids' => [$this->announcer->id]]))
        ->assertOk()
        ->assertSee('Announcer wajib diisi.')
        ->assertSee('Sound operator (SO) yang dipilih tidak valid.');
});

it('updates a program and resyncs upcoming sessions', function () {
    $this->actingAs($this->hr)->post(route('program.store'), ($this->payload)());
    $program = Program::firstOrFail();
    $newAnnouncer = User::factory()->announcer()->create();

    $this->actingAs($this->hr)
        ->put(route('program.update', $program), ($this->payload)([
            'name' => 'Pagi Baru',
            'crewcall_time' => '06:45',
            'tapping_start' => '07:15',
            'announcer_ids' => [$newAnnouncer->id],
            'so_ids' => [],
        ]))
        ->assertRedirect(route('program.index'))
        ->assertSessionHas('status');

    $program->refresh();
    $session = $program->sessions()->orderBy('regular_date')->first();

    expect($program->name)->toBe('Pagi Baru')
        ->and($program->crewcall_time)->toBe('06:45:00')
        ->and($program->crew->pluck('id')->all())->toBe([$newAnnouncer->id])
        ->and($program->sessions)->toHaveCount(4)
        ->and($session->crewcall_time)->toBe('06:45:00')
        ->and($session->tapping_start)->toBe('07:15:00')
        ->and($session->crew->pluck('id')->all())->toBe([$newAnnouncer->id]);
});

it('shows the form prefilled with the program values', function () {
    $this->actingAs($this->hr)->post(route('program.store'), ($this->payload)());
    $program = Program::firstOrFail();

    $this->actingAs($this->hr)
        ->get(route('program.edit', $program))
        ->assertOk()
        ->assertSee('value="07:30"', false)
        ->assertSee('value="2026-10-26"', false);
});

it('lists programs with their crew counts', function () {
    $this->actingAs($this->hr)->post(route('program.store'), ($this->payload)());

    $this->actingAs($this->hr)
        ->get(route('program.index'))
        ->assertOk()
        ->assertSee('Pagi Ceria')
        ->assertSee('Senin');
});

it('refuses to delete a program that has attendance', function () {
    $program = Program::factory()->create();
    $session = BroadcastSession::factory()->for($program)->create();
    Attendance::factory()->for($session, 'session')->for($this->announcer)->create();

    $this->actingAs($this->hr)
        ->from(route('program.index'))
        ->delete(route('program.destroy', $program))
        ->assertRedirect(route('program.index'))
        ->assertSessionHasErrors(['program' => 'Program tidak bisa dihapus karena sudah ada absensi.']);

    expect(Program::count())->toBe(1)
        ->and(BroadcastSession::count())->toBe(1);
});

it('deletes a program without attendance together with its sessions', function () {
    $this->actingAs($this->hr)->post(route('program.store'), ($this->payload)());
    $program = Program::firstOrFail();

    $this->actingAs($this->hr)
        ->delete(route('program.destroy', $program))
        ->assertRedirect(route('program.index'))
        ->assertSessionHas('status');

    expect(Program::count())->toBe(0)
        ->and(BroadcastSession::count())->toBe(0);
});

it('forbids non hr users from program routes', function () {
    $program = Program::factory()->create();

    foreach ([$this->announcer, $this->so] as $user) {
        $this->actingAs($user)->get(route('program.index'))->assertForbidden();
        $this->actingAs($user)->get(route('program.create'))->assertForbidden();
        $this->actingAs($user)->post(route('program.store'), ($this->payload)())->assertForbidden();
        $this->actingAs($user)->get(route('program.edit', $program))->assertForbidden();
        $this->actingAs($user)->put(route('program.update', $program), ($this->payload)())->assertForbidden();
        $this->actingAs($user)->delete(route('program.destroy', $program))->assertForbidden();
    }

    expect(Program::count())->toBe(1);
});

it('redirects guests to the login page', function () {
    $this->get(route('program.index'))->assertRedirect(route('login'));
    $this->post(route('program.store'), ($this->payload)())->assertRedirect(route('login'));
});
