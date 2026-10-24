<?php

namespace App\Http\Controllers\auth\announcer;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class AnnouncerController extends Controller
{
    public function dashboard()
    {
        return view('auth.announcer.dashboard.index');
    }

    /**
     * Tampilkan jadwal siaran milik announcer yang sedang login.
     */
    public function jadwalSiaran(): View
    {
        $jadwalSiaran = auth()->user()
            ->broadcastSchedules()
            ->orderByDesc('broadcast_date')
            ->orderBy('start_time')
            ->paginate(15);

        return view('auth.announcer.jadwalSiaran.index', compact('jadwalSiaran'));
    }
}
