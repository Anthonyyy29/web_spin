<?php

use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->travelTo('2026-10-26 07:20:00');
    $this->hr = User::factory()->hr()->create();
    $this->announcer = User::factory()->announcer()->create();
    $this->program = Program::factory()->create();
    $this->program->crew()->attach($this->announcer->id, ['position' => 'announcer']);
    $this->session = BroadcastSession::factory()->for($this->program)->create();
    $this->session->crew()->attach($this->announcer->id, ['position' => 'announcer']);
});

it('keeps every horizontal scroll area as the containing block of the labels inside it', function (string $who, Closure $url) {
    $html = $this->actingAs($this->{$who})->get($url->call($this))->assertOk()->getContent();

    preg_match_all('/class="([^"]*\boverflow-x-auto\b[^"]*)"/', $html, $matches);

    expect($matches[1])->not->toBeEmpty();
    foreach ($matches[1] as $classes) {
        expect($classes)->toMatch('/(^|\s)relative(\s|$)/');
    }
})->with([
    'program' => ['hr', fn () => route('program.index')],
    'sesi' => ['hr', fn () => route('sesi.index')],
    'absensi per program' => ['hr', fn () => route('absensi.index')],
    'absensi per orang' => ['hr', fn () => route('absensi.people', ['from' => '2026-10-01', 'to' => '2026-10-31'])],
    'absensi satu program' => ['hr', fn () => route('absensi.program', $this->program)],
    'absensi sesi' => ['hr', fn () => route('absensi.session', $this->session)],
    'sesi saya' => ['announcer', fn () => route('announcer.sesi.index')],
]);
