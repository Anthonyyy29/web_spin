<?php

namespace App\Http\Controllers\auth\hr;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\auth\hr\SesiRequest;
use App\Models\BroadcastSession;
use App\Models\Program;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SesiController extends Controller
{
    /**
     * Appended to the status flash when moving or postponing a session removed its attendance.
     */
    private const ATTENDANCE_DELETED_NOTICE = ' Absensi sesi ini dihapus.';

    /**
     * Display the upcoming or past dated sessions and the postponed sessions waiting to be rescheduled.
     */
    public function index(Request $request): View
    {
        $programId = $request->integer('program') ?: null;
        $isHistory = $request->query('waktu') === 'riwayat';
        $today = today()->toDateString();

        $sessions = BroadcastSession::query()
            ->with(['program', 'crew'])
            ->withExists(['attendances as has_check_ins' => fn ($query) => $query->whereNotNull('checked_in_at')])
            ->whereNotNull('session_date')
            ->when($programId, fn ($query) => $query->where('program_id', $programId))
            ->when(
                $isHistory,
                fn ($query) => $query->whereDate('session_date', '<', $today)->orderByDesc('session_date')->orderByDesc('crewcall_time'),
                fn ($query) => $query->whereDate('session_date', '>=', $today)->orderBy('session_date')->orderBy('crewcall_time'),
            )
            ->paginate(15)
            ->withQueryString();

        $postponedSessions = BroadcastSession::query()
            ->with(['program', 'crew'])
            ->where('status', SessionStatus::Postponed)
            ->when($programId, fn ($query) => $query->where('program_id', $programId))
            ->orderBy('regular_date')
            ->get();

        return view('auth.hr.sesi.index', [
            'sessions' => $sessions,
            'postponedSessions' => $postponedSessions,
            'programs' => Program::orderBy('name')->get(['id', 'name']),
            'selectedProgramId' => $programId,
            'isHistory' => $isHistory,
        ]);
    }

    /**
     * Show the form for editing the specified session.
     */
    public function edit(BroadcastSession $sesi): View
    {
        $sesi->load(['program', 'crew']);

        return view('auth.hr.sesi.edit', [
            'session' => $sesi,
            'hasCheckIns' => $sesi->hasCheckIns(),
            'selectedAnnouncerIds' => $sesi->crew->where('pivot.position', 'announcer')->pluck('id')->all(),
            'selectedSoIds' => $sesi->crew->where('pivot.position', 'so')->pluck('id')->all(),
            'announcers' => User::where('role', 'announcer')->orderBy('username')->get(),
            'sos' => User::where('role', 'so')->orderBy('username')->get(),
        ]);
    }

    /**
     * Schedule the session on the given date and times with the given crew.
     */
    public function update(SesiRequest $request, BroadcastSession $sesi): RedirectResponse
    {
        $attendanceDeleted = false;

        DB::transaction(function () use ($request, $sesi, &$attendanceDeleted): void {
            $attributes = $request->sessionAttributes();

            if ($sesi->hasCheckIns() && $sesi->isScheduleChangedBy(Arr::only($attributes, ['session_date', 'crewcall_time', 'tapping_start', 'tapping_end']))) {
                $sesi->attendances()->delete();
                $attendanceDeleted = true;
            }

            $sesi->update($attributes);

            $crew = collect($request->crewPositions())
                ->map(fn (string $position): array => ['position' => $position])
                ->all();

            $sesi->crew()->sync($crew);
        });

        return redirect()
            ->route('sesi.index')
            ->with('status', 'Sesi berhasil diperbarui.'.($attendanceDeleted ? self::ATTENDANCE_DELETED_NOTICE : ''));
    }

    /**
     * Postpone the session: clear its date and times until HR reschedules it.
     */
    public function postpone(BroadcastSession $sesi): RedirectResponse
    {
        if ($sesi->status !== SessionStatus::Scheduled) {
            return $this->rejected('Sesi ini tidak bisa ditunda.');
        }

        $attendanceDeleted = false;

        DB::transaction(function () use ($sesi, &$attendanceDeleted): void {
            if ($sesi->hasCheckIns()) {
                $sesi->attendances()->delete();
                $attendanceDeleted = true;
            }

            $sesi->update([
                'session_date' => null,
                'crewcall_time' => null,
                'tapping_start' => null,
                'tapping_end' => null,
                'status' => SessionStatus::Postponed,
                'is_customized' => true,
            ]);
        });

        return redirect()
            ->route('sesi.index')
            ->with('status', 'Sesi ditunda. Jadwalkan ulang dari daftar "Perlu dijadwalkan ulang".'.($attendanceDeleted ? self::ATTENDANCE_DELETED_NOTICE : ''));
    }

    /**
     * Cancel a scheduled or postponed session.
     */
    public function cancel(BroadcastSession $sesi): RedirectResponse
    {
        if (! in_array($sesi->status, [SessionStatus::Scheduled, SessionStatus::Postponed], true)) {
            return $this->rejected('Sesi ini tidak bisa dibatalkan.');
        }

        $sesi->update([
            'status' => SessionStatus::Cancelled,
            'is_customized' => true,
        ]);

        return redirect()
            ->route('sesi.index')
            ->with('status', 'Sesi dibatalkan.');
    }

    /**
     * Redirect to the session list with an error for an action the session status does not allow.
     */
    private function rejected(string $message): RedirectResponse
    {
        return redirect()
            ->route('sesi.index')
            ->withErrors(['sesi' => $message]);
    }
}
