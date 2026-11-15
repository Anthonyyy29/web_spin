<?php

namespace App\Http\Controllers\Lomba;

use App\Http\Controllers\Controller;
use App\Models\AnggotaTim;
use App\Models\PendaftaranLomba;
use App\Models\PengumpulanKarya;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class LombaController extends Controller
{
    private const array TEAM_TYPES = ['podcast', 'konten'];

    private const string FOLDER = 'lomba';

    public function guidebook(): View
    {
        return view('lomba.guidebook');
    }

    public function pendaftaranView(Request $request): View|RedirectResponse
    {
        return $request->cookie('sudahDaftar') === 'true'
            ? redirect('/lomba/whatsapp-group')
            : view('lomba.pendaftaran');
    }

    public function resetPendaftaran(Request $request): RedirectResponse
    {
        if ($request->cookie('sudahDaftar') !== 'true') {
            return back()->with('error', 'Anda belum mendaftar. Tidak ada yang perlu direset.')->withInput();
        }

        return redirect('/lomba/pendaftaran')
            ->with('success', 'Pendaftaran telah direset. Silakan daftar ulang.')
            ->withoutCookie('sudahDaftar');
    }

    public function pengumpulanView(): View
    {
        return view('lomba.pengumpulan_karya');
    }

    public function whatsappPage(Request $request): View|RedirectResponse
    {
        return $request->cookie('sudahDaftar') === 'true'
            ? view('lomba.whatsapp_group')
            : redirect('/lomba');
    }

    public function daftar(Request $request): RedirectResponse
    {
        $anggota = (array) $request->input('anggota', []);

        if (count($anggota) > 3) {
            return $this->failRegistration('Jumlah anggota tim tidak boleh lebih dari 3 orang.');
        }

        $jenis = (string) $request->input('pilih_lomba');
        $isTeam = in_array($jenis, self::TEAM_TYPES, true);

        $rules = [
            'pilih_lomba' => 'required|in:poster,podcast,konten,presenter',
            'instagram' => 'required',
            'email' => 'required|email',
            'no_wa' => 'required',
            'bukti_pembayaran' => 'required|file|max:2048|extensions:jpg,jpeg,pdf',
            'kartu_identitas' => 'required|file|max:2048|extensions:jpg,jpeg,pdf',
            ...($isTeam ? ['nama_tim' => 'required', 'anggota' => 'required'] : ['nama_lengkap' => 'required']),
        ];

        if ($isTeam && PendaftaranLomba::where('jenis_lomba', $jenis)->where('nama_tim', 'Tim '.$request->input('nama_tim'))->exists()) {
            return $this->failRegistration('Nama tim sudah terdaftar untuk lomba ini. Silakan gunakan nama tim yang berbeda.');
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return $this->failRegistration(implode('<br>', $validator->errors()->all()));
        }

        $pendaftaran = PendaftaranLomba::create([
            'jenis_lomba' => $jenis,
            'nama_tim' => $request->filled('nama_tim') ? 'Tim '.$request->input('nama_tim') : null,
            'nama_lengkap' => $request->input('nama_lengkap'),
            'instagram' => $request->input('instagram'),
            'email' => $request->input('email'),
            'no_wa' => $request->input('no_wa'),
            'bukti_pembayaran' => $this->storeUpload($request->file('bukti_pembayaran'), self::FOLDER.'/bukti_pembayaran'),
            'kartu_identitas' => $this->storeUpload($request->file('kartu_identitas'), self::FOLDER.'/kartu_identitas'),
        ]);

        if ($isTeam) {
            foreach (array_filter($anggota, fn ($nama): bool => trim((string) $nama) !== '') as $nama) {
                AnggotaTim::create(['pendaftaran_id' => $pendaftaran->id, 'nama_anggota' => $nama]);
            }
        }

        return redirect('/lomba/whatsapp-group')
            ->with('success', 'Pendaftaran berhasil!')
            ->cookie('sudahDaftar', 'true', 1440);
    }

    public function submitKarya(Request $request): RedirectResponse
    {
        $jenis = (string) $request->input('jenis_lomba');
        $isTeam = in_array($jenis, self::TEAM_TYPES, true);

        $rules = [
            'jenis_lomba' => 'required|in:podcast,konten,poster',
            'judul_karya' => 'required|min:3|max:255',
            'link_gdrive' => 'required|url',
            'link_instagram' => 'required|url',
            ...($isTeam
                ? ['nama_tim' => 'required|min:3|max:100']
                : ['nama_peserta' => 'required|min:3|max:100']),
            ...($jenis === 'poster' ? ['surat_pernyataan' => 'required|file|max:2048|extensions:jpg,jpeg,pdf'] : []),
        ];

        $messages = [
            'jenis_lomba.required' => 'Pilihan lomba wajib diisi.',
            'jenis_lomba.in' => 'Jenis lomba tidak valid.',
            'judul_karya.required' => 'Judul karya wajib diisi.',
            'judul_karya.max' => 'Judul karya maksimal 255 karakter.',
            'link_gdrive.required' => 'Link Google Drive wajib diisi.',
            'link_gdrive.url' => 'Format URL Google Drive tidak valid.',
            'link_instagram.required' => 'Link Instagram wajib diisi.',
            'link_instagram.url' => 'Format URL Instagram tidak valid.',
            'nama_tim.required' => 'Nama tim wajib diisi.',
            'nama_tim.min' => 'Nama tim terlalu pendek.',
            'nama_peserta.required' => 'Nama peserta wajib diisi.',
            'nama_peserta.min' => 'Nama peserta terlalu pendek.',
            'surat_pernyataan.required' => 'Surat Pernyataan wajib diunggah.',
            'surat_pernyataan.max' => 'Ukuran file maksimal 2MB.',
            'surat_pernyataan.extensions' => 'Format file harus JPG, JPEG, atau PDF.',
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withInput()->with('error', implode('<br>', $validator->errors()->all()));
        }

        if ($isTeam) {
            $nama = 'Tim '.$request->input('nama_tim');

            if (! PendaftaranLomba::where('jenis_lomba', $jenis)->where('nama_tim', $nama)->exists()) {
                return back()->with('error', 'Nama Tim tidak ditemukan.')->withInput();
            }

            if (PengumpulanKarya::where('jenis_lomba', $jenis)->where('nama', $nama)->exists()) {
                return back()->with('error', 'Karya untuk tim ini sudah pernah dikumpulkan.')->withInput();
            }
        } else {
            $nama = (string) $request->input('nama_peserta');
        }

        PengumpulanKarya::create([
            'jenis_lomba' => $jenis,
            'nama' => $nama,
            'judul_karya' => $request->input('judul_karya'),
            'link_gdrive' => $request->input('link_gdrive'),
            'link_instagram' => $request->input('link_instagram'),
            'surat_pernyataan' => $jenis === 'poster'
                ? $this->storeUpload($request->file('surat_pernyataan'), self::FOLDER.'/surat_pernyataan')
                : null,
        ]);

        return back()->with('success', 'Pengumpulan karya berhasil!');
    }

    private function failRegistration(string $message): RedirectResponse
    {
        return redirect('/lomba/pendaftaran')->with('error', $message)->withInput();
    }
}
