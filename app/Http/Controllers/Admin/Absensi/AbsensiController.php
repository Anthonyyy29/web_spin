<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
use App\Models\BroadcastSession;
use App\Models\RfidCrew;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    /**
     * Catat kehadiran dari tap kartu RFID (UID bisa lewat query, form, atau JSON).
     */
    public function tap(Request $request): JsonResponse
    {
        $uid = trim((string) $request->input('rfid_uid'));

        if ($uid === '') {
            return response()->json(['status' => 'error', 'message' => 'UID tidak terbaca']);
        }

        $kartu = RfidCrew::with('crew')->find($uid);

        if ($kartu === null) {
            return response()->json(['status' => 'error', 'message' => 'Kartu tidak terdaftar']);
        }

        $sekarang = now()->startOfSecond();
        $sesi = BroadcastSession::findForTap($kartu->crew, $sekarang);

        $sudahAbsen = AbsensiLog::where('rfid_uid', $uid)
            ->when($sesi, fn ($query) => $query->where('session_id', $sesi->id))
            ->unless($sesi, fn ($query) => $query->whereNull('session_id')->whereDate('tanggal', $sekarang->toDateString()))
            ->exists();

        if ($sudahAbsen) {
            $pesan = $sesi ? 'Anda sudah absen di sesi ini' : 'Anda sudah absen hari ini';

            return response()->json(['status' => 'warning', 'message' => $pesan]);
        }

        $jam = $sekarang->format('H:i:s');
        $statusAbsen = $sesi?->statusAt($sekarang) ?? 'Hadir';

        AbsensiLog::create([
            'rfid_uid' => $uid,
            'session_id' => $sesi?->id,
            'tanggal' => $sekarang->toDateString(),
            'jam_masuk' => $jam,
            'status' => $statusAbsen,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => $sesi ? 'Absen berhasil' : 'Dicatat sebagai tamu',
            'nama' => $kartu->crew?->name,
            'jam' => $jam,
            'jenis' => $sesi ? 'sesi' : 'tamu',
            'program' => $sesi?->content->title,
            'status_absen' => $statusAbsen,
        ]);
    }
}
