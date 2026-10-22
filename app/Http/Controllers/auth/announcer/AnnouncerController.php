<?php

namespace App\Http\Controllers\auth\announcer;

use Illuminate\Http\Request;

class AnnouncerController extends Controller
{
    public function dashboard()
    {
        return view('auth.announcer.dashboard.index');
    }

}
