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
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SesiController extends Controller
{
    /**
     * Display the dated sessions and the postponed sessions waiting to be rescheduled.
     */
    public function index(Request $request): View
    {
        $programId = $request->integer('program') ?: null;

        $sessions = BroadcastSession::query()
            ->with(['program', 'crew'])
            ->whereNotNull('session_date')
            ->when($programId, fn ($query) => $query->where('program_id', $programId))
            ->orderByDesc('session_date')
            ->orderBy('crewcall_time')
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
        DB::transaction(function () use ($request, $sesi): void {
            $sesi->update($request->sessionAttributes());

            $crew = collect($request->crewPositions())
                ->map(fn (string $position): array => ['position' => $position])
                ->all();

            $sesi->crew()->sync($crew);
        });

        return redirect()
            ->route('sesi.index')
            ->with('status', 'Sesi berhasil diperbarui.');
    }

    /**
     * Postpone the session: clear its date and times until HR reschedules it.
     */
    public function postpone(BroadcastSession $sesi): RedirectResponse
    {
        $sesi->update([
            'session_date' => null,
            'crewcall_time' => null,
            'tapping_start' => null,
            'tapping_end' => null,
            'status' => SessionStatus::Postponed,
            'is_customized' => true,
        ]);

        return redirect()
            ->route('sesi.index')
            ->with('status', 'Sesi ditunda. Jadwalkan ulang dari daftar "Perlu dijadwalkan ulang".');
    }

    /**
     * Cancel the session.
     */
    public function cancel(BroadcastSession $sesi): RedirectResponse
    {
        $sesi->update([
            'status' => SessionStatus::Cancelled,
            'is_customized' => true,
        ]);

        return redirect()
            ->route('sesi.index')
            ->with('status', 'Sesi dibatalkan.');
    }
}
