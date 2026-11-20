<x-layouts.dashboard :navmenu="$navmenu">

<div class="container">
    <section class="quickaccess">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-semibold mb-0">RFID Crew</h5>

            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                + Tambah RFID
            </button>
        </div>

        <div class="table-responsive">
            <table class="table table-light table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>RFID UID</th>
                        <th>Divisi</th>
                        <th width="140">Action</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($rfid_crews as $r)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $r['nama_anggota'] }}</td>
                                <td>{{ $r['rfid_uid'] }}</td>
                                <td>{{ $r['divisi'] }}</td>

                                <td>
                                    <button class="btn btn-sm btn-warning btn-edit"
                                        data-rfid="{{ $r['rfid_uid'] }}"
                                        data-crew="{{ $r['crew_id'] }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modalEdit">
                                        Edit
                                    </button>

                                    <button class="btn btn-sm btn-danger btn-delete"
                                        data-rfid="{{ $r['rfid_uid'] }}">
                                        Delete
                                    </button>
                                </td>

                            </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">Belum ada data</td>
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

            <form action="{{ url('dashboard/absensi/crew/simpan') }}" method="post">
@csrf

                <div class="modal-header">
                    <h5 class="modal-title">Tambah RFID Crew</h5>
                </div>

                <div class="modal-body">

                    <!-- RFID -->
                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">RFID UID</label>
                        <div class="border rounded p-3 bg-light">
                            <h6 class="text-muted">Tempelkan kartu RFID</h6>
                            <input type="text" id="rfid_uid" name="rfid_uid"
                                class="form-control text-center mt-2"
                                placeholder="Menunggu tap..." readonly required>
                        </div>
                    </div>

                    <!-- pilih crew -->
                    <div class="mb-3">
                        <label>Pilih Crew</label>
                        <input type="search" class="form-control mb-2 crew-search" data-target="tambah_crew" placeholder="Cari nama crew..." autocomplete="off">
                        <select name="crew_id" id="tambah_crew" class="form-control" required>
                            <option value="">-- Pilih Crew --</option>
                            @foreach ($crew_list as $c)
                                <option value="{{ $c['id'] }}">
                                    {{ $c['name'] }} - {{ $c['role'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
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

            <form action="{{ url('dashboard/absensi/crew/update') }}" method="post">
@csrf

                <div class="modal-header">
                    <h5 class="modal-title">Edit RFID</h5>
                </div>

                <div class="modal-body">

                    <input type="hidden" name="rfid_lama" id="edit_rfid_lama">

                    <div class="mb-3 text-center">
                        <label class="form-label fw-bold">RFID Baru (opsional)</label>
                        <div class="border rounded p-3 bg-light">
                            <input type="text" name="rfid_uid" id="edit_rfid_baru"
                                class="form-control text-center"
                                placeholder="Tap kartu baru" readonly>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label>Pilih Crew</label>
                        <select name="crew_id" id="edit_crew" class="form-control" required>
                            @foreach ($crew_list as $c)
                                <option value="{{ $c['id'] }}">
                                    {{ $c['name'] }} - {{ $c['role'] }}
                                </option>
                            @endforeach
                        </select>
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
    // auto fokus RFID saat modal buka
    var modalTambah = document.getElementById('modalTambah');
    modalTambah.addEventListener('shown.bs.modal', () => {
        document.getElementById("rfid_uid").focus();
    });

    // tangkap input RFID (keyboard emulation)
    document.addEventListener('keypress', function(e) {
        let input = document.getElementById("rfid_uid");
        if (document.activeElement === input) {
            if (e.key === "Enter") {
                e.preventDefault();
            } else {
                input.value += e.key;
            }
        }
    });

    // cari crew: saring opsi select sambil mengetik, pilihan aktif tetap dipertahankan
    function isiOpsiCrew(select, kata) {
        let terpilih = select.value;
        select.innerHTML = "";
        select._semuaOpsi.forEach(opsi => {
            if (opsi.value === "" || opsi.value === terpilih || opsi.text.toLowerCase().includes(kata)) {
                select.appendChild(opsi);
            }
        });
        select.value = terpilih;
    }

    document.querySelectorAll(".crew-search").forEach(input => {
        let select = document.getElementById(input.dataset.target);
        select._semuaOpsi = Array.from(select.options);
        input.addEventListener("input", () => isiOpsiCrew(select, input.value.trim().toLowerCase()));
        input.closest(".modal").addEventListener("hidden.bs.modal", () => {
            input.value = "";
            isiOpsiCrew(select, "");
        });
    });

    // tombol edit
    document.querySelectorAll(".btn-edit").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("edit_rfid_lama").value = this.dataset.rfid;
            document.getElementById("edit_crew").value = this.dataset.crew;
            document.getElementById("edit_rfid_baru").value = "";
            document.getElementById("edit_rfid_baru").focus();
        });
    });

    // tap edit
    document.addEventListener('keypress', function(e) {
        let input = document.getElementById("edit_rfid_baru");
        if (document.activeElement === input) {
            if (e.key === "Enter") {
                e.preventDefault();
            } else {
                input.value += e.key;
            }
        }
    });

    // delete
    document.querySelectorAll(".btn-delete").forEach(btn => {
        btn.addEventListener("click", function() {
            let rfid = this.dataset.rfid;
            if (confirm("Hapus RFID ini?")) {
                window.location = "/dashboard/absensi/crew/delete/" + rfid;
            }
        });
    });
</script>

</x-layouts.dashboard>