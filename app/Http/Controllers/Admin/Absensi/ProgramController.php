<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Models\Crew;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProgramController extends Controller
{
    private const string LIST_URL = '/dashboard/absensi/program';

    public function index(): View
    {
        return view('admin.absensi.program', [
            'navmenu' => 'Program',
            'programs' => Content::with('crews')->get(),
            'crew_list' => Crew::sortedByRole(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'content_id' => ['required', 'exists:contents,id'],
            'crew_ids' => ['array'],
            'crew_ids.*' => ['exists:crews,id'],
        ]);

        Content::findOrFail($data['content_id'])->crews()->sync($data['crew_ids'] ?? []);

        return redirect(self::LIST_URL)->with('success', 'Crew program diperbarui');
    }
}
