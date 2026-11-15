<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class HistoryController extends Controller
{
    public function index(): View
    {
        return view('admin.absensi.history', ['navmenu' => 'History', 'title' => 'History Absensi']);
    }

    public function byUid(string $rfid): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->history(30, $rfid)]);
    }

    public function all(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->history()]);
    }

    public function exportExcel(): StreamedResponse
    {
        $rows = $this->history()->map(fn (AbsensiLog $log): array => [
            $log->tanggal, $log->jam_masuk, $log->rfid_uid, $log->nama, $log->divisi, $log->status,
        ])->all();

        return $this->downloadExcel(
            'History_Absensi_'.now()->format('Ymd_His'),
            ['Tanggal', 'Jam Masuk', 'RFID UID', 'Nama', 'Divisi', 'Status'],
            $rows,
        );
    }

    /**
     * @return Collection<int, AbsensiLog>
     */
    private function history(int $limit = 200, ?string $rfid = null): Collection
    {
        return AbsensiLog::withCrew()
            ->when($rfid !== null, fn ($query) => $query->where('absensi_logs.rfid_uid', $rfid))
            ->orderByDesc('absensi_logs.tanggal')
            ->orderByDesc('absensi_logs.jam_masuk')
            ->limit($limit)
            ->get();
    }
}
