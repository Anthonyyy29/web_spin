<?php

namespace App\Http\Controllers\Admin\Lomba;

use App\Http\Controllers\Controller;
use App\Models\PendaftaranLomba;
use App\Models\PengumpulanKarya;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LombaPagesController extends Controller
{
    private const array TEAM_TYPES = ['podcast', 'konten'];

    public function pesertaByJenis(Request $request, string $jenis): View|RedirectResponse|StreamedResponse
    {
        $jenis = strtolower($jenis);
        abort_unless(in_array($jenis, ['poster', 'podcast', 'konten', 'presenter'], true), 404, 'Jenis lomba tidak valid.');

        $pendaftarans = PendaftaranLomba::with('anggota_tim')->where('jenis_lomba', $jenis)->latest('created_at')->get();

        if ($request->query('export') === 'true') {
            return $this->exportPeserta($jenis, $pendaftarans);
        }

        return view("admin.lomba.lomba_{$jenis}", [
            'navmenu' => 'Lomba '.ucfirst($jenis),
            'pendaftarans' => $pendaftarans->map(fn (PendaftaranLomba $p): array => [
                ...$p->toArray(),
                'anggota_tim' => $p->anggota_tim->toArray(),
            ]),
        ]);
    }

    public function karyaByJenis(Request $request, string $jenis): View|RedirectResponse|StreamedResponse
    {
        $jenis = strtolower($jenis);
        abort_unless(in_array($jenis, ['poster', 'podcast', 'konten'], true), 404, 'Jenis lomba tidak valid.');

        $daftarKarya = PengumpulanKarya::where('jenis_lomba', $jenis)->get();

        if ($request->query('export') === 'true') {
            return $this->exportKarya($jenis, $daftarKarya);
        }

        return view("admin.lomba.karya_{$jenis}", ['navmenu' => 'Karya '.ucfirst($jenis), 'daftarKarya' => $daftarKarya]);
    }

    public function hapusPendaftaran(int $id): RedirectResponse
    {
        $pendaftaran = PendaftaranLomba::find($id);

        if ($pendaftaran === null) {
            return back()->with('error', 'Pendaftaran tidak ditemukan.');
        }

        $this->deleteUpload($pendaftaran->bukti_pembayaran, 'lomba/bukti_pembayaran');
        $this->deleteUpload($pendaftaran->kartu_identitas, 'lomba/kartu_identitas');
        $pendaftaran->anggota_tim()->delete();
        $pendaftaran->delete();

        return back()->with('success', 'Pendaftaran berhasil dihapus.');
    }

    /**
     * @param  Collection<int, PendaftaranLomba>  $pendaftarans
     */
    private function exportPeserta(string $jenis, Collection $pendaftarans): StreamedResponse|RedirectResponse
    {
        if ($pendaftarans->isEmpty()) {
            return back()->with('error', 'Data Kosong.');
        }

        $isTeam = in_array($jenis, self::TEAM_TYPES, true);
        $bukti = fn (PendaftaranLomba $p): array => [
            url('assets/uploads/lomba/bukti_pembayaran/'.$p->bukti_pembayaran),
            url('assets/uploads/lomba/kartu_identitas/'.$p->kartu_identitas),
        ];

        $rows = $pendaftarans->map(fn (PendaftaranLomba $p): array => [
            $p->jenis_lomba,
            $isTeam ? $p->nama_tim : $p->nama_lengkap,
            ...($isTeam ? [$p->anggota_tim->pluck('nama_anggota')->implode(', ')] : []),
            $p->instagram, $p->email, $p->no_wa,
            ...$bukti($p),
        ])->all();

        $headers = $isTeam
            ? ['Jenis Lomba', 'Nama Tim', 'Nama Anggota', 'Instagram', 'Email', 'No WA', 'Bukti Pembayaran', 'Kartu Identitas']
            : ['Jenis Lomba', 'Nama Lengkap', 'Instagram', 'Email', 'No WA', 'Bukti Pembayaran', 'Kartu Identitas'];

        return $this->downloadExcel('Pendaftar_'.ucfirst($jenis).'_'.now()->format('Y-m-d_H-i-s'), $headers, $rows, true);
    }

    /**
     * @param  Collection<int, PengumpulanKarya>  $daftarKarya
     */
    private function exportKarya(string $jenis, Collection $daftarKarya): StreamedResponse|RedirectResponse
    {
        if ($daftarKarya->isEmpty()) {
            return back()->with('error', 'Data Kosong.');
        }

        $rows = $daftarKarya->map(fn (PengumpulanKarya $k): array => [
            $k->jenis_lomba, $k->nama, $k->judul_karya, $k->link_gdrive, $k->link_instagram,
        ])->all();

        $headers = ['Jenis Lomba', in_array($jenis, self::TEAM_TYPES, true) ? 'Nama Tim' : 'Nama Peserta', 'Judul Karya', 'Link Gdrive', 'Link Instagram'];

        return $this->downloadExcel('Karya'.ucfirst($jenis).'_'.now()->format('Y-m-d_H-i-s'), $headers, $rows, true);
    }
}
