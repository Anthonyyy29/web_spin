<x-layouts.dashboard :navmenu="$navmenu">

<div class="container">
    <section class="quickaccess">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-semibold mb-0">Sesi Siaran {{ $isRiwayat ? '(Riwayat)' : '(Mendatang)' }}</h5>

            <div>
                @if ($isRiwayat)
                    <a href="{{ url('dashboard/absensi/sesi') }}" class="btn btn-outline-secondary">Mendatang</a>
                @else
                    <a href="{{ url('dashboard/absensi/sesi?tampil=riwayat') }}" class="btn btn-outline-secondary">Riwayat</a>
                @endif

                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                    + Tambah Sesi
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-light table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Program</th>
                        <th>Tanggal</th>
                        <th>Jam</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($sessions as $session)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $session->content?->title }}</td>
                            <td>{{ $session->tanggal->format('d-m-Y') }}</td>
                            <td>{{ substr($session->jam_mulai, 0, 5) }} - {{ substr($session->jam_selesai, 0, 5) }}</td>
                            <td>
                                <button class="btn btn-sm btn-warning btn-edit"
                                    data-id="{{ $session->id }}"
                                    data-content="{{ $session->content_id }}"
                                    data-tanggal="{{ $session->tanggal->toDateString() }}"
                                    data-mulai="{{ substr($session->jam_mulai, 0, 5) }}"
                                    data-selesai="{{ substr($session->jam_selesai, 0, 5) }}"
                                    data-bs-toggle="modal"
                                    data-bs-target="#modalEdit">
                                    Edit
                                </button>

                                <a href="{{ url('dashboard/absensi/sesi/delete/'.$session->id) }}"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Hapus sesi ini? Absensinya akan menjadi tamu.')">
                                    Delete
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada sesi</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<!-- ================= MODAL TAMBAH ================= -->
<div class="modal fade" id="modalTambah">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('dashboard/absensi/sesi/simpan') }}" method="post">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tambah Sesi</h5>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Program</label>
                        <select name="content_id" class="form-control" required>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Mulai</label>
                        <input type="time" name="jam_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Selesai</label>
                        <input type="time" name="jam_selesai" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================= MODAL EDIT ================= -->
<div class="modal fade" id="modalEdit">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('dashboard/absensi/sesi/update') }}" method="post">
                @csrf
                <input type="hidden" name="id" id="edit_id">

                <div class="modal-header">
                    <h5 class="modal-title">Edit Sesi</h5>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label>Program</label>
                        <select name="content_id" id="edit_content" class="form-control" required>
                            @foreach ($programs as $program)
                                <option value="{{ $program->id }}">{{ $program->title }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Tanggal</label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Mulai</label>
                        <input type="time" name="jam_mulai" id="edit_mulai" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Jam Selesai</label>
                        <input type="time" name="jam_selesai" id="edit_selesai" class="form-control" required>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll(".btn-edit").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("edit_id").value = this.dataset.id;
            document.getElementById("edit_content").value = this.dataset.content;
            document.getElementById("edit_tanggal").value = this.dataset.tanggal;
            document.getElementById("edit_mulai").value = this.dataset.mulai;
            document.getElementById("edit_selesai").value = this.dataset.selesai;
        });
    });
</script>

</x-layouts.dashboard>
