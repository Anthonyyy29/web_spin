<?php

namespace App\Http\Controllers\Admin\Absensi;

use App\Http\Controllers\Controller;
use App\Models\Crew;
use App\Models\RfidCrew;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CrewController extends Controller
{
    private const string LIST_URL = '/dashboard/absensi/crew';

    public function index(): View
    {
        return view('admin.absensi.crew', [
            'navmenu' => 'Crew RFID',
            'rfid_crews' => RfidCrew::with('crew')->get()->map(fn (RfidCrew $kartu): array => [
                'rfid_uid' => $kartu->rfid_uid,
                'crew_id' => $kartu->crew_id,
                'nama_anggota' => $kartu->crew?->name,
                'divisi' => $kartu->crew?->role,
            ]),
            'crew_list' => Crew::all(),
        ]);
    }

    public function simpan(Request $request): RedirectResponse
    {
        $data = $request->validate(['rfid_uid' => ['required', 'string'], 'crew_id' => ['required', 'exists:crews,id']]);

        if (RfidCrew::find(trim($data['rfid_uid']))) {
            return redirect(self::LIST_URL)->with('error', 'RFID sudah terdaftar!');
        }

        RfidCrew::create(['rfid_uid' => trim($data['rfid_uid']), 'crew_id' => $data['crew_id']]);

        return redirect(self::LIST_URL)->with('success', 'RFID Crew berhasil ditambahkan!');
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate(['rfid_lama' => ['required'], 'crew_id' => ['required', 'exists:crews,id']]);

        $lama = (string) $request->input('rfid_lama');
        $baru = trim((string) $request->input('rfid_uid'));
        $kartu = RfidCrew::find($lama);

        if ($kartu === null) {
            return redirect(self::LIST_URL)->with('error', 'Data tidak ditemukan.');
        }

        if ($baru !== '' && $baru !== $lama && RfidCrew::find($baru)) {
            return redirect(self::LIST_URL)->with('error', 'RFID sudah digunakan!');
        }

        $kartu->crew_id = $request->input('crew_id');

        if ($baru !== '') {
            $kartu->rfid_uid = $baru;
        }

        $kartu->save();

        return redirect(self::LIST_URL)->with('success', 'Data berhasil diupdate');
    }

    public function delete(string $rfid): RedirectResponse
    {
        RfidCrew::find($rfid)?->delete();

        return redirect(self::LIST_URL)->with('success', 'RFID berhasil dihapus');
    }
}
