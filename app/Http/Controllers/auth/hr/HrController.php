<?php

namespace App\Http\Controllers\auth\hr;

use App\Http\Controllers\Controller;

class HrController extends Controller
{
    public function dashboard()
    {
        return view('auth.hr.dashboard.index');
    }

    
}
