<?php

namespace App\Http\Controllers\auth\hr;

use App\Http\Controllers\Controller;
use App\Http\Requests\auth\hr\JadwalSiaranRequest;
use App\Models\BroadcastSchedule;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JadwalSiaranController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $jadwalSiaran = BroadcastSchedule::with('announcer')
            ->orderByDesc('broadcast_date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('auth.hr.jadwalSiaran.index', compact('jadwalSiaran'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('auth.hr.jadwalSiaran.create', [
            'announcers' => $this->announcers(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(JadwalSiaranRequest $request): RedirectResponse
    {
        BroadcastSchedule::create($request->scheduleAttributes());

        return redirect()
            ->route('jadwalSiaran.index')
            ->with('status', 'Jadwal siaran berhasil ditambahkan.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BroadcastSchedule $jadwalSiaran): View
    {
        return view('auth.hr.jadwalSiaran.edit', [
            'jadwalSiaran' => $jadwalSiaran,
            'announcers' => $this->announcers(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(JadwalSiaranRequest $request, BroadcastSchedule $jadwalSiaran): RedirectResponse
    {
        $jadwalSiaran->update($request->scheduleAttributes());

        return redirect()
            ->route('jadwalSiaran.index')
            ->with('status', 'Jadwal siaran berhasil diperbarui.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BroadcastSchedule $jadwalSiaran): RedirectResponse
    {
        $jadwalSiaran->delete();

        return redirect()
            ->route('jadwalSiaran.index')
            ->with('status', 'Jadwal siaran berhasil dihapus.');
    }

    /**
     * Get the announcers that can be assigned to a schedule.
     *
     * @return Collection<int, User>
     */
    private function announcers(): Collection
    {
        return User::where('role', 'announcer')->orderBy('username')->get();
    }
}
