<?php

namespace App\Http\Controllers\auth\hr;

use App\Enums\AttendanceStatus;
use App\Enums\CrewPosition;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\hr\AbsensiStatusRequest;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AbsensiController extends Controller
{
    /**
     * Display every program with its attendance totals from closed, scheduled sessions.
     */
    public function index(): View
    {
        $now = now();

        $programs = Program::orderBy('name')->get()->map(function (Program $program) use ($now): array {
            ['sessions' => $sessions, 'rows' => $rows] = $this->programMatrix($program, null, null);
            $totals = $this->emptyTotals();

            foreach ($rows as $row) {
                foreach ($row['totals'] as $status => $count) {
                    $totals[$status] += $count;
                }
            }

            return [
                'program' => $program,
                'closed_sessions' => $sessions->filter(fn (BroadcastSession $session): bool => $session->isCheckInClosed($now))->count(),
                'totals' => $totals,
            ];
        });

        return view('auth.hr.absensi.index', ['programs' => $programs]);
    }

    /**
     * Display the attendance summary per person for a date range.
     */
    public function people(Request $request): View
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($request);

        return view('auth.hr.absensi.people', [
            'rows' => $this->summaryRows($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Display one program's attendance as a crew by session date matrix.
     */
    public function program(Request $request, Program $program): View
    {
        ['from' => $from, 'to' => $to] = $this->optionalDateRange($request);

        return view('auth.hr.absensi.program', [
            ...$this->programMatrix($program, $from, $to),
            'program' => $program,
            'from' => $from,
            'to' => $to,
        ]);
    }

    /**
     * Download one program's attendance matrix as CSV.
     */
    public function programExport(Request $request, Program $program): StreamedResponse
    {
        ['from' => $from, 'to' => $to] = $this->optionalDateRange($request);
        ['sessions' => $sessions, 'rows' => $rows] = $this->programMatrix($program, $from, $to);
        $filename = 'absensi-program-'.Str::slug($program->name).($from || $to ? '-'.($from ?? 'awal').'-'.($to ?? 'akhir') : '').'.csv';

        return $this->csvDownload($filename, function ($output) use ($sessions, $rows): void {
            $this->writeCsvRow($output, [
                'Username',
                'Posisi',
                ...$sessions->map(fn (BroadcastSession $session): string => $session->session_date->format('d/m/Y'))->all(),
                'Hadir', 'Terlambat', 'Tidak hadir', 'Izin', 'Sakit',
            ]);

            foreach ($rows as $row) {
                $this->writeCsvRow($output, [
                    $row['user']->username,
                    $row['position'] === null ? '-' : CrewPosition::from($row['position'])->label(),
                    ...array_map(fn (?AttendanceStatus $status): string => $status?->label() ?? '', array_values($row['cells'])),
                    ...array_values($row['totals']),
                ]);
            }
        });
    }

    /**
     * Download the attendance summary per person as CSV.
     */
    public function export(Request $request): StreamedResponse
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($request);
        $rows = $this->summaryRows($from, $to);

        return $this->csvDownload("rekap-absensi-{$from}-{$to}.csv", function ($output) use ($rows): void {
            $this->writeCsvRow($output, ['Username', 'Role', 'Hadir', 'Terlambat', 'Tidak hadir', 'Izin', 'Sakit']);

            foreach ($rows as $row) {
                $this->writeCsvRow($output, [
                    $row['user']->username,
                    CrewPosition::from($row['user']->role)->label(),
                    $row['hadir'],
                    $row['terlambat'],
                    $row['tidak_hadir'],
                    $row['izin'],
                    $row['sakit'],
                ]);
            }
        });
    }

    /**
     * Display the attendance of every crew member of one session.
     */
    public function session(BroadcastSession $sesi): View
    {
        return view('auth.hr.absensi.session', [
            'session' => $sesi->load('program'),
            'members' => $this->sessionMembers($sesi, now()),
        ]);
    }

    /**
     * Download the attendance of one session as CSV.
     */
    public function sessionExport(BroadcastSession $sesi): StreamedResponse
    {
        $sesi->load('program');
        $members = $this->sessionMembers($sesi, now());
        $date = $sesi->session_date?->toDateString();
        $crewcall = $sesi->crewcall_time ? substr($sesi->crewcall_time, 0, 5) : '';

        return $this->csvDownload('absensi-'.Str::slug($sesi->program->name).'-'.($date ?? 'ditunda').'.csv', function ($output) use ($sesi, $members, $date, $crewcall): void {
            $this->writeCsvRow($output, ['Program', 'Tanggal', 'Crewcall', 'Username', 'Posisi', 'Status', 'Jam check-in', 'Catatan']);

            foreach ($members as $member) {
                $this->writeCsvRow($output, [
                    $sesi->program->name,
                    $date ?? '',
                    $crewcall,
                    $member['user']->username,
                    $member['position'] === null ? '-' : CrewPosition::from($member['position'])->label(),
                    $member['status']->label(),
                    $member['attendance']?->checked_in_at?->format('H:i') ?? '',
                    $member['attendance']?->note ?? '',
                ]);
            }
        });
    }

    /**
     * Override a member's attendance status (izin or sakit) or restore the status earned by checking in.
     */
    public function update(AbsensiStatusRequest $request, BroadcastSession $sesi, User $user): RedirectResponse
    {
        $attendance = $sesi->attendances()->where('user_id', $user->id)->first();

        abort_unless($attendance !== null || $sesi->crew()->whereKey($user->id)->exists(), 404);

        if ($sesi->status !== SessionStatus::Scheduled) {
            return redirect()
                ->route('absensi.session', $sesi)
                ->withErrors(['absensi' => 'Status absensi hanya bisa diubah untuk sesi terjadwal.']);
        }

        ['status' => $status, 'note' => $note] = $request->statusChange() + ['note' => null];

        if ($status === 'reset') {
            $this->restoreCheckInStatus($sesi, $attendance);
        } else {
            Attendance::updateOrCreate(
                ['broadcast_session_id' => $sesi->id, 'user_id' => $user->id],
                ['status' => AttendanceStatus::from($status), 'note' => $note],
            );
        }

        return redirect()
            ->route('absensi.session', $sesi)
            ->with('status', 'Status absensi '.$user->username.' diperbarui.');
    }

    /**
     * Drop an override: restore the status earned by the check-in, or delete an attendance that never checked in.
     */
    private function restoreCheckInStatus(BroadcastSession $sesi, ?Attendance $attendance): void
    {
        if ($attendance === null) {
            return;
        }

        if ($attendance->checked_in_at === null) {
            $attendance->delete();

            return;
        }

        $attendance->update([
            'status' => $sesi->statusForCheckIn($attendance->checked_in_at),
            'note' => null,
        ]);
    }

    /**
     * Get the validated date range as Y-m-d strings, defaulting to the first of this month through today.
     *
     * @return array{from: string, to: string}
     */
    private function dateRange(Request $request): array
    {
        $today = now();

        /** @var array{from: string, to: string} */
        return Validator::make(
            [
                'from' => $request->query('from') ?: $today->copy()->startOfMonth()->toDateString(),
                'to' => $request->query('to') ?: $today->toDateString(),
            ],
            [
                'from' => ['date_format:Y-m-d'],
                'to' => ['date_format:Y-m-d', 'after_or_equal:from'],
            ],
            [
                'date_format' => ':Attribute harus berformat tahun-bulan-tanggal (Y-m-d).',
                'to.after_or_equal' => ':Attribute tidak boleh sebelum tanggal mulai.',
            ],
            [
                'from' => 'tanggal mulai',
                'to' => 'tanggal akhir',
            ],
        )->validate();
    }

    /**
     * Get an optional validated date range as Y-m-d strings; a missing bound leaves that side open.
     *
     * @return array{from: string|null, to: string|null}
     */
    private function optionalDateRange(Request $request): array
    {
        $range = [
            'from' => $request->query('from') ?: null,
            'to' => $request->query('to') ?: null,
        ];

        Validator::make(
            $range,
            [
                'from' => ['nullable', 'date_format:Y-m-d'],
                'to' => ['nullable', 'date_format:Y-m-d', ...($range['from'] ? ['after_or_equal:from'] : [])],
            ],
            [
                'date_format' => ':Attribute harus berformat tahun-bulan-tanggal (Y-m-d).',
                'to.after_or_equal' => ':Attribute tidak boleh sebelum tanggal mulai.',
            ],
            [
                'from' => 'tanggal mulai',
                'to' => 'tanggal akhir',
            ],
        )->validate();

        return $range;
    }

    /**
     * Build a program's attendance matrix: its dated, scheduled sessions in the range as columns and one row per member.
     * Members are the program crew, every session's crew and anyone with an attendance row; a cell is null when the
     * person was not part of that session. Totals count only sessions whose check-in has closed.
     *
     * @return array{sessions: Collection<int, BroadcastSession>, rows: Collection<int, array{user: User, position: string|null, cells: array<int, AttendanceStatus|null>, totals: array{hadir: int, terlambat: int, tidak_hadir: int, izin: int, sakit: int}}>}
     */
    private function programMatrix(Program $program, ?string $from, ?string $to): array
    {
        $now = now();

        $sessions = $program->sessions()
            ->with(['crew', 'attendances'])
            ->where('status', SessionStatus::Scheduled)
            ->whereNotNull('session_date')
            ->when($from, fn ($query) => $query->whereDate('session_date', '>=', $from))
            ->when($to, fn ($query) => $query->whereDate('session_date', '<=', $to))
            ->orderBy('session_date')
            ->orderBy('crewcall_time')
            ->get();

        /** @var array<int, User> $users */
        $users = [];
        /** @var array<int, string|null> $positions */
        $positions = [];

        foreach ([$program->crew, ...$sessions->pluck('crew')] as $crew) {
            foreach ($crew as $user) {
                $users[$user->id] ??= $user;
                $positions[$user->id] ??= $user->pivot->position;
            }
        }

        $attendeeIds = $sessions->flatMap->attendances->pluck('user_id')->unique()->diff(array_keys($users));
        foreach (User::whereIn('id', $attendeeIds->all())->get() as $user) {
            $users[$user->id] = $user;
            $positions[$user->id] = null;
        }

        $rows = collect($users)
            ->map(function (User $user) use ($sessions, $positions, $now): array {
                $totals = $this->emptyTotals();
                $cells = [];

                foreach ($sessions as $session) {
                    $isMember = $session->crew->contains('id', $user->id) || $session->attendances->contains('user_id', $user->id);
                    $cells[$session->id] = $isMember ? $session->attendanceStatusFor($user, $now) : null;

                    if ($isMember && $session->isCheckInClosed($now)) {
                        $totals[$cells[$session->id]->value]++;
                    }
                }

                return [
                    'user' => $user,
                    'position' => $positions[$user->id],
                    'cells' => $cells,
                    'totals' => $totals,
                ];
            })
            ->sortBy(fn (array $row): array => [$this->positionOrder($row['position']), $row['user']->username])
            ->values();

        return ['sessions' => $sessions, 'rows' => $rows];
    }

    /**
     * Zeroed attendance totals keyed by status value, in the column order used by the recaps.
     *
     * @return array{hadir: int, terlambat: int, tidak_hadir: int, izin: int, sakit: int}
     */
    private function emptyTotals(): array
    {
        return [
            AttendanceStatus::Hadir->value => 0,
            AttendanceStatus::Terlambat->value => 0,
            AttendanceStatus::TidakHadir->value => 0,
            AttendanceStatus::Izin->value => 0,
            AttendanceStatus::Sakit->value => 0,
        ];
    }

    /**
     * Sort key that lists announcers first, then sound operators, then members without a position.
     */
    private function positionOrder(?string $position): int
    {
        return match ($position) {
            'announcer' => 0,
            'so' => 1,
            default => 2,
        };
    }

    /**
     * Tally the closed, scheduled sessions in the range per announcer and sound operator.
     * A session's crew is its assigned crew plus anyone with an attendance row on it.
     *
     * @return Collection<int, array{user: User, hadir: int, terlambat: int, izin: int, sakit: int, tidak_hadir: int}>
     */
    private function summaryRows(string $from, string $to): Collection
    {
        $now = now();

        $users = User::query()
            ->whereIn('role', ['announcer', 'so'])
            ->orderBy('role')
            ->orderBy('username')
            ->get();

        $sessions = BroadcastSession::query()
            ->with(['crew', 'attendances'])
            ->where('status', SessionStatus::Scheduled)
            ->whereDate('session_date', '>=', $from)
            ->whereDate('session_date', '<=', $to)
            ->get()
            ->filter(fn (BroadcastSession $session): bool => $session->isCheckInClosed($now));

        $counts = $users->mapWithKeys(fn (User $user): array => [$user->id => [
            AttendanceStatus::Hadir->value => 0,
            AttendanceStatus::Terlambat->value => 0,
            AttendanceStatus::Izin->value => 0,
            AttendanceStatus::Sakit->value => 0,
            AttendanceStatus::TidakHadir->value => 0,
        ]])->all();

        foreach ($sessions as $session) {
            $memberIds = $session->crew->pluck('id')->merge($session->attendances->pluck('user_id'))->unique();

            foreach ($users->whereIn('id', $memberIds->all()) as $user) {
                $counts[$user->id][$session->attendanceStatusFor($user, $now)->value]++;
            }
        }

        return $users->map(fn (User $user): array => [
            'user' => $user,
            'hadir' => $counts[$user->id][AttendanceStatus::Hadir->value],
            'terlambat' => $counts[$user->id][AttendanceStatus::Terlambat->value],
            'izin' => $counts[$user->id][AttendanceStatus::Izin->value],
            'sakit' => $counts[$user->id][AttendanceStatus::Sakit->value],
            'tidak_hadir' => $counts[$user->id][AttendanceStatus::TidakHadir->value],
        ]);
    }

    /**
     * Get every member of a session with position, attendance and effective status.
     * Members are the assigned crew plus anyone with an attendance row, so a member removed after checking in stays listed.
     *
     * @return Collection<int, array{user: User, position: string|null, attendance: Attendance|null, status: AttendanceStatus}>
     */
    private function sessionMembers(BroadcastSession $sesi, CarbonInterface $now): Collection
    {
        $sesi->load(['crew', 'attendances.user']);

        $assigned = $sesi->crew->map(fn (User $user): array => [
            'user' => $user,
            'position' => $user->pivot->position,
        ]);

        $removed = $sesi->attendances
            ->reject(fn (Attendance $attendance): bool => $sesi->crew->contains('id', $attendance->user_id))
            ->map(fn (Attendance $attendance): array => [
                'user' => $attendance->user,
                'position' => null,
            ]);

        return $assigned
            ->concat($removed)
            ->map(fn (array $member): array => [
                ...$member,
                'attendance' => $sesi->attendances->firstWhere('user_id', $member['user']->id),
                'status' => $sesi->attendanceStatusFor($member['user'], $now),
            ])
            ->sortBy(fn (array $member): array => [$this->positionOrder($member['position']), $member['user']->username])
            ->values();
    }

    /**
     * Stream a CSV download without a byte order mark.
     *
     * @param  callable(resource): void  $writer
     */
    private function csvDownload(string $filename, callable $writer): StreamedResponse
    {
        return response()->streamDownload(function () use ($writer): void {
            $output = fopen('php://output', 'w');
            $writer($output);
            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * Write one CSV row.
     *
     * @param  resource  $output
     * @param  array<int, string|int|null>  $row
     */
    private function writeCsvRow($output, array $row): void
    {
        fputcsv($output, $row, escape: '');
    }
}
