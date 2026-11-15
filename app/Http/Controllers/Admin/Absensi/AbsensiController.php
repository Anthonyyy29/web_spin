<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\AbsensiLog;
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

        $sudahAbsen = AbsensiLog::where('rfid_uid', $uid)->whereDate('tanggal', today())->exists();

        if ($sudahAbsen) {
            return response()->json(['status' => 'warning', 'message' => 'Anda sudah absen hari ini']);
        }

        $jam = now()->format('H:i:s');
        AbsensiLog::create(['rfid_uid' => $uid, 'tanggal' => today()->toDateString(), 'jam_masuk' => $jam, 'status' => 'Hadir']);

        return response()->json(['status' => 'success', 'message' => 'Absen berhasil', 'nama' => $kartu->crew?->name, 'jam' => $jam]);
    }
}
