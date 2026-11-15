<x-layouts.dashboard :navmenu="$navmenu">


<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12">
            <div class="callout callout-info">
                Berikut adalah data pengumpulan karya lomba.
            </div>
        </div>
        <div class="col-12">
            <div class="mb-3">
                <a href="{{ url()->current() . '?export=true' }}" class="btn btn-success">
                    <i class="bi bi-file-earmark-excel-fill me-1"></i> Export Excel
                </a>
            </div>
            <div class="table-responsive">
                <table class="table table-bordered table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Jenis Lomba</th>
                            <th>Nama Peserta</th>
                            <th>Judul Karya</th>
                            <th>Link_Gdrive</th>
                            <th>Link_Instagram</th>
                            <th>Surat Pernyataan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($daftarKarya) && count($daftarKarya) > 0)
                            @foreach ($daftarKarya as $i => $p)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p['jenis_lomba'] }}</td>
                                    <td>{{ $p['nama'] }}</td>
                                    <td>{{ $p['judul_karya'] }}</td>
                                    <td>
                                        <a href="{{ $p['link_gdrive'] }}" target="_blank">
                                            {{ $p['link_gdrive'] }}
                                        </a>
                                    </td>
                                    <td>
                                        <a href="{{ $p['link_instagram'] }}" target="_blank">
                                            {{ $p['link_instagram'] }}
                                        </a>
                                    </td>
                                    <td>
                                        @if (!empty($p['surat_pernyataan']))
                                            <a href="{{ url('assets/uploads/lomba/surat_pernyataan/' . $p['surat_pernyataan']) }}" target="_blank">
                                                Lihat
                                            </a>
                                        @else
                                            <span class="text-muted">Belum ada</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="7" class="text-center">Belum ada data pengumpulan karya.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            @if (isset($pager))
                {{ $pager->links() }}
            @endif
        </div>
    </div>
</div>
</x-layouts.dashboard>