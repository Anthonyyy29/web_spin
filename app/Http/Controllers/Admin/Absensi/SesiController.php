<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\Content;
use App\Models\Crew;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SesiController extends Controller
{
    private const string LIST_URL = '/dashboard/absensi/sesi';

    public function index(Request $request): View
    {
        $isRiwayat = $request->query('tampil') === 'riwayat';

        $sessions = BroadcastSession::with('content')
            ->whereDate('tanggal', $isRiwayat ? '<' : '>=', today()->toDateString())
            ->orderBy('tanggal', $isRiwayat ? 'desc' : 'asc')
            ->orderBy('jam_mulai', $isRiwayat ? 'desc' : 'asc')
            ->get();

        return view('admin.absensi.sesi', [
            'navmenu' => 'Sesi',
            'sessions' => $sessions,
            'isRiwayat' => $isRiwayat,
            'programs' => Content::orderBy('title')->get(),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $sesi = BroadcastSession::create($this->validated($request));
        $sesi->syncCrews($this->crewIds($request));

        return redirect(self::LIST_URL)->with('success', 'Sesi berhasil ditambahkan');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['id' => ['required', 'exists:broadcast_sessions,id']]);

        $sesi = BroadcastSession::findOrFail($request->input('id'));
        $data = $this->validated($request);

        $tanggalBerubah = $sesi->tanggal->toDateString() !== Carbon::parse($data['tanggal'])->toDateString();

        if ($tanggalBerubah && AbsensiLog::where('session_id', $sesi->id)->exists()) {
            return redirect(self::LIST_URL)->with('error', 'Tanggal sesi tidak bisa diubah karena sudah ada absensi. Tambah sesi baru untuk jadwal yang baru.');
        }

        $sesi->update($data);
        $sesi->syncCrews($this->crewIds($request));

        return redirect(self::LIST_URL)->with('success', 'Sesi berhasil diperbarui');
    }

    public function delete(int $id): RedirectResponse
    {
        BroadcastSession::find($id)?->delete();

        return redirect(self::LIST_URL)->with('success', 'Sesi berhasil dihapus');
    }

    /**
     * @return array{content_id: int, tanggal: string, jam_mulai: string, jam_selesai: string}
     */
    private function validated(Request $request): array
    {
        return Arr::except($request->validate([
            'content_id' => ['required', 'exists:contents,id'],
            'tanggal' => ['required', 'date'],
            'jam_mulai' => ['required', 'date_format:H:i'],
            'jam_selesai' => ['required', 'date_format:H:i', 'after:jam_mulai'],
            'crew_ids' => ['array'],
            'crew_ids.*' => [
                'integer',
                Rule::exists('crews', 'id')->where(fn ($query) => $query
                    ->whereIn('role', Crew::SESSION_ROLES)
                    ->orWhereIn('extra_role', Crew::SESSION_ROLES)),
            ],
        ]), 'crew_ids');
    }

    /**
     * @return array<int, int|string>
     */
    private function crewIds(Request $request): array
    {
        return $request->input('crew_ids', []);
    }
}
