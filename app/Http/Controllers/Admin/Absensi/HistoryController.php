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
        return response()->json(['status' => 'success', 'data' => $this->history(30, rfid: $rfid)]);
    }

    public function all(): JsonResponse
    {
        return response()->json(['status' => 'success', 'data' => $this->history(jenis: $this->jenis())]);
    }

    public function exportExcel(): StreamedResponse
    {
        $rows = $this->history(jenis: $this->jenis())->map(fn (AbsensiLog $log): array => [
            $log->tanggal, $log->jam_masuk, $log->rfid_uid, $log->nama, $log->divisi,
            $log->program ?? '-', $log->jam_sesi ?? '-', $log->status,
        ])->all();

        return $this->downloadExcel(
            'History_Absensi_'.now()->format('Ymd_His'),
            ['Tanggal', 'Jam Masuk', 'RFID UID', 'Nama', 'Divisi', 'Program', 'Jam Sesi', 'Status'],
            $rows,
        );
    }

    private function jenis(): string
    {
        return request('jenis') === 'tamu' ? 'tamu' : 'sesi';
    }

    /**
     * Riwayat absensi; $jenis null memuat sesi dan tamu sekaligus.
     *
     * @return Collection<int, AbsensiLog>
     */
    private function history(int $limit = 200, ?string $rfid = null, ?string $jenis = null): Collection
    {
        return AbsensiLog::withCrew()
            ->withSession()
            ->when($jenis === 'sesi', fn ($query) => $query->whereNotNull('absensi_logs.session_id'))
            ->when($jenis === 'tamu', fn ($query) => $query->whereNull('absensi_logs.session_id'))
            ->when($rfid !== null, fn ($query) => $query->where('absensi_logs.rfid_uid', $rfid))
            ->orderByDesc('absensi_logs.tanggal')
            ->orderByDesc('absensi_logs.jam_masuk')
            ->limit($limit)
            ->get();
    }
}
