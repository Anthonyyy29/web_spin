<?php

namespace App\Http\Controllers\auth\crew;

use App\Enums\SessionStatus;
use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\BroadcastSession;
use Illuminate\Contracts\View\View;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;

class SesiSayaController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $role = $user->role;

        $sessions = $user->broadcastSessions()
            ->whereIn('status', [SessionStatus::Scheduled, SessionStatus::Postponed])
            ->with(['program', 'attendances' => fn ($query) => $query->where('user_id', $user->id)])
            ->orderByRaw('session_date is null')
            ->orderBy('session_date')
            ->orderBy('crewcall_time')
            ->paginate(15);

        return view('auth.crew.sesi.index', [
            'sessions' => $sessions,
            'user' => $user,
            'layout' => 'auth.'.$role.'.layout',
            'routePrefix' => $role,
        ]);
    }

    public function checkIn(BroadcastSession $sesi): RedirectResponse
    {
        $user = auth()->user();
        $now = now();

        abort_unless($sesi->crew()->whereKey($user->id)->exists(), 403);

        if ($sesi->status !== SessionStatus::Scheduled) {
            return $this->checkInError('Sesi ini tidak bisa di-check-in.');
        }

        if (! $sesi->isCheckInOpen($now)) {
            return $this->checkInError('Check-in hanya bisa dari 15 menit sebelum crewcall sampai tapping dimulai.');
        }

        if ($sesi->attendances()->where('user_id', $user->id)->exists()) {
            return $this->checkInError('Kamu sudah check-in di sesi ini.');
        }

        $status = $sesi->statusForCheckIn($now);

        try {
            Attendance::create([
                'broadcast_session_id' => $sesi->id,
                'user_id' => $user->id,
                'checked_in_at' => $now,
                'status' => $status,
            ]);
        } catch (UniqueConstraintViolationException) {
            return $this->checkInError('Kamu sudah check-in di sesi ini.');
        }

        return back()->with('status', 'Check-in tercatat: '.$status->label().' '.$now->format('H:i').'.');
    }

    private function checkInError(string $message): RedirectResponse
    {
        return back()->withErrors(['checkIn' => $message]);
    }
}
