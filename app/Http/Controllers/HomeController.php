<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Crew;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(): View
    {
        return view('home', [
            'crew' => Crew::sortedByRole(10),
            'content' => Content::limit(4)->get(),
        ]);
    }
}
