<?php

namespace App\Http\Controllers\auth\hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\hr\ProgramRequest;
use App\Models\Program;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProgramController extends Controller
{
    /**
     * Day of week names (ISO 8601, 1 = Monday).
     *
     * @var array<int, string>
     */
    public const DAY_NAMES = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $programs = Program::query()
            ->withCount([
                'crew as announcers_count' => fn ($query) => $query->where('program_crew.position', 'announcer'),
                'crew as sos_count' => fn ($query) => $query->where('program_crew.position', 'so'),
            ])
            ->orderBy('day_of_week')
            ->orderBy('crewcall_time')
            ->paginate(15);

        return view('auth.hr.program.index', [
            'programs' => $programs,
            'dayNames' => self::DAY_NAMES,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('auth.hr.program.create', $this->formData());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProgramRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $program = Program::create($request->programAttributes());
            $this->syncCrewAndSessions($program, $request);
        });

        return redirect()
            ->route('program.index')
            ->with('status', 'Program berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Program $program): View
    {
        $program->load('crew');

        return view('auth.hr.program.edit', [
            'program' => $program,
            'selectedAnnouncerIds' => $program->crew->where('pivot.position', 'announcer')->pluck('id')->all(),
            'selectedSoIds' => $program->crew->where('pivot.position', 'so')->pluck('id')->all(),
            ...$this->formData(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProgramRequest $request, Program $program): RedirectResponse
    {
        DB::transaction(function () use ($request, $program): void {
            $program->update($request->programAttributes());
            $this->syncCrewAndSessions($program, $request);
        });

        return redirect()
            ->route('program.index')
            ->with('status', 'Program berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Program $program): RedirectResponse
    {
        $hasAttendance = $program->sessions()->whereHas('attendances')->exists();

        if ($hasAttendance) {
            return back()->withErrors(['program' => 'Program tidak bisa dihapus karena sudah ada absensi.']);
        }

        $program->delete();

        return redirect()
            ->route('program.index')
            ->with('status', 'Program berhasil dihapus.');
    }

    /**
     * Save the crew of the program and regenerate its sessions.
     */
    private function syncCrewAndSessions(Program $program, ProgramRequest $request): void
    {
        $crew = collect($request->crewPositions())
            ->map(fn (string $position): array => ['position' => $position])
            ->all();

        $program->crew()->sync($crew);
        $program->syncSessions(today());
    }

    /**
     * Get the data shared by the create and edit forms.
     *
     * @return array{announcers: Collection<int, User>, sos: Collection<int, User>, dayNames: array<int, string>}
     */
    private function formData(): array
    {
        return [
            'announcers' => User::where('role', 'announcer')->orderBy('username')->get(),
            'sos' => User::where('role', 'so')->orderBy('username')->get(),
            'dayNames' => self::DAY_NAMES,
        ];
    }
}
