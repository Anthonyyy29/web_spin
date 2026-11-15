<x-layouts.dashboard :navmenu="$navmenu">


<div class="container-fluid">
    <div class="row g-4">
        <div class="col-12">
            <div class="callout callout-info">
                Berikut adalah data pendaftaran lomba.
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
                            <th>Nama Tim</th>
                            <th>Anggota Tim</th>
                            <th>Instagram</th>
                            <th>Email</th>
                            <th>No. WA</th>
                            <th>Bukti Pembayaran</th>
                            <th>Kartu Identitas</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (isset($pendaftarans) && count($pendaftarans) > 0)
                            @foreach ($pendaftarans as $i => $p)
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td>{{ $p['jenis_lomba'] }}</td>
                                    <td>
                                        {{ $p['nama_tim'] }}
                                    </td>
                                    <td>
                                        @if (!empty($p['anggota_tim']))
                                            <ul class="mb-0">
                                                @foreach ($p['anggota_tim'] as $agt)
                                                    <li>{{ $agt['nama_anggota'] }}</li>
                                                @endforeach
                                            </ul>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $p['instagram'] }}</td>
                                    <td>{{ $p['email'] }}</td>
                                    <td>{{ $p['no_wa'] }}</td>
                                    <td>
                                        @if (!empty($p['bukti_pembayaran']))
                                            <a href="{{ url('assets/uploads/lomba/bukti_pembayaran/' . $p['bukti_pembayaran']) }}" target="_blank">Lihat</a>
                                        @endif
                                    </td>
                                    <td>
                                        @if (!empty($p['kartu_identitas']))
                                            <a href="{{ url('assets/uploads/lomba/kartu_identitas/' . $p['kartu_identitas']) }}" target="_blank">Lihat</a>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ url('dashboard/lomba/hapus/' . $p['id']) }}" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus data ini?')">Delete</a>
                                    </td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data pendaftaran.</td>
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