<?php

namespace App\Http\Controllers\Admin;

use App\Models\Crew;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CrewController extends ImageCrudController
{
    protected function modelClass(): string
    {
        return Crew::class;
    }

    protected function auditName(): string
    {
        return 'Crew';
    }

    protected function fieldRules(): array
    {
        return [
            'name' => ['required', 'max:30'],
            'role' => ['required', Rule::in(array_keys(Crew::ROLES))],
            'about' => ['nullable'],
            'instagram_link' => ['nullable'],
            'linkedin_link' => ['nullable'],
        ];
    }

    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'All');

        $crews = Crew::query()
            ->when($filter !== '' && $filter !== 'All', fn ($query) => $query->where('role', $filter))
            ->latest()
            ->paginate(12, pageName: 'crewPage')
            ->withQueryString();

        return view('admin.crew', [
            'navmenu' => 'Crew',
            'crews' => $crews,
            'pager' => $crews,
            'roleList' => array_keys(Crew::ROLES),
            'activefilter' => $filter === '' ? 'All' : $filter,
        ]);
    }

    public function filter(string $role): RedirectResponse
    {
        return redirect('/dashboard/crew?filter='.urlencode($role));
    }
}
