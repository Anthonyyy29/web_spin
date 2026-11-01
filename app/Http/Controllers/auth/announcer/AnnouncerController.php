<?php

namespace App\Http\Controllers\auth\announcer;

use App\Http\Controllers\Controller;

class AnnouncerController extends Controller
{
    public function dashboard()
    {
        return view('auth.announcer.dashboard.index');
    }
}
