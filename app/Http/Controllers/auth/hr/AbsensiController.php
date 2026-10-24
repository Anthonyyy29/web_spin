<?php

namespace App\Http\Controllers\auth\hr;

use App\Enums\AttendanceStatus;
use App\Enums\CrewPosition;
use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\hr\AbsensiStatusRequest;
use App\Models\Attendance;
use App\Models\BroadcastSession;
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
     * Display the attendance summary per person for a date range.
     */
    public function index(Request $request): View
    {
        ['from' => $from, 'to' => $to] = $this->dateRange($request);

        return view('auth.hr.absensi.index', [
            'rows' => $this->summaryRows($from, $to),
            'from' => $from,
            'to' => $to,
        ]);
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
            ->sortBy(fn (array $member): array => [
                match ($member['position']) {
                    'announcer' => 0,
                    'so' => 1,
                    default => 2,
                },
                $member['user']->username,
            ])
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
