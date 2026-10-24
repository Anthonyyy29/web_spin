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
        $area = $user->area();

        $now = now();
        $withAttendance = ['program', 'attendances' => fn ($query) => $query->where('user_id', $user->id)];

        $upcomingSessions = $user->broadcastSessions()
            ->where('status', SessionStatus::Scheduled)
            ->whereDate('session_date', '>=', $now->toDateString())
            ->with($withAttendance)
            ->orderBy('session_date')
            ->orderBy('crewcall_time')
            ->get()
            ->concat(
                $user->broadcastSessions()
                    ->where('status', SessionStatus::Postponed)
                    ->with($withAttendance)
                    ->orderBy('broadcast_sessions.id')
                    ->get()
            );

        $historySessions = $user->broadcastSessions()
            ->where('status', SessionStatus::Scheduled)
            ->whereDate('session_date', '<', $now->toDateString())
            ->with($withAttendance)
            ->orderByDesc('session_date')
            ->orderByDesc('crewcall_time')
            ->paginate(15)
            ->withQueryString();

        return view('auth.crew.sesi.index', [
            'upcomingSessions' => $upcomingSessions,
            'historySessions' => $historySessions,
            'user' => $user,
            'now' => $now,
            'layout' => $area->layout(),
            'routePrefix' => $area->value,
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
