<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use Illuminate\View\View;

class CrewController extends Controller
{
    public function index(): View
    {
        return view('crew', ['crew' => Crew::sortedByRole()]);
    }
}
