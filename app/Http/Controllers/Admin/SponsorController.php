<?php

namespace App\Http\Controllers\Admin;

use App\Models\Sponsor;
use Illuminate\View\View;

class SponsorController extends ImageCrudController
{
    protected function modelClass(): string
    {
        return Sponsor::class;
    }

    protected function auditName(): string
    {
        return 'Sponsor';
    }

    public function index(): View
    {
        $sponsors = Sponsor::paginate(4, pageName: 'sponsorpage');

        return view('admin.sponsor', ['navmenu' => 'Sponsor', 'sponsors' => $sponsors, 'pager' => $sponsors]);
    }
}
