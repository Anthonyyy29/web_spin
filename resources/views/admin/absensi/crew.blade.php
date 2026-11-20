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
                        <div class="dropdown" id="tambah_crew_dropdown">
                            <input type="hidden" name="crew_id" id="tambah_crew">
                            <button type="button" class="form-select text-start" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <span class="crew-label text-secondary">-- Pilih Crew --</span>
                            </button>
                            <div class="dropdown-menu w-100 p-2">
                                <input type="search" class="form-control mb-2 crew-search" placeholder="Cari nama crew..." autocomplete="off">
                                <div style="max-height: 220px; overflow-y: auto;">
                                    @foreach ($crew_list as $c)
                                        <button type="button" class="dropdown-item crew-item" data-id="{{ $c['id'] }}">{{ $c['name'] }} - {{ $c['role'] }}</button>
                                    @endforeach
                                    <span class="crew-kosong text-secondary small px-2 d-none">Crew tidak ditemukan.</span>
                                </div>
                            </div>
                            <div class="text-danger small mt-1 d-none" id="tambah_crew_error">Pilih crew dulu.</div>
                        </div>
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

    // dropdown crew di modal tambah: cari di dalam menu, pilihan disimpan di input tersembunyi
    (function() {
        let dropdown = document.getElementById("tambah_crew_dropdown");
        let tombol = dropdown.querySelector("button[data-bs-toggle]");
        let label = dropdown.querySelector(".crew-label");
        let cari = dropdown.querySelector(".crew-search");
        let kosong = dropdown.querySelector(".crew-kosong");
        let pilihan = document.getElementById("tambah_crew");
        let error = document.getElementById("tambah_crew_error");
        let items = dropdown.querySelectorAll(".crew-item");

        function reset() {
            pilihan.value = "";
            label.textContent = "-- Pilih Crew --";
            label.classList.add("text-secondary");
            cari.value = "";
            items.forEach(item => item.classList.remove("d-none"));
            kosong.classList.add("d-none");
            error.classList.add("d-none");
        }

        cari.addEventListener("input", () => {
            let kata = cari.value.trim().toLowerCase();
            let ada = false;
            items.forEach(item => {
                let cocok = item.textContent.toLowerCase().includes(kata);
                item.classList.toggle("d-none", !cocok);
                ada = ada || cocok;
            });
            kosong.classList.toggle("d-none", ada);
        });

        dropdown.addEventListener("shown.bs.dropdown", () => cari.focus());

        items.forEach(item => {
            item.addEventListener("click", () => {
                pilihan.value = item.dataset.id;
                label.textContent = item.textContent;
                label.classList.remove("text-secondary");
                error.classList.add("d-none");
                bootstrap.Dropdown.getOrCreateInstance(tombol).hide();
            });
        });

        dropdown.closest("form").addEventListener("submit", e => {
            if (!pilihan.value) {
                e.preventDefault();
                error.classList.remove("d-none");
            }
        });

        modalTambah.addEventListener("hidden.bs.modal", reset);
    })();

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