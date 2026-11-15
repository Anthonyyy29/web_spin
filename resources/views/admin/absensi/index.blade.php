<x-layouts.dashboard :navmenu="$navmenu">

<div class="container">

    <!-- ================= TAP KARTU ================= -->
    <section class="tap-card mb-4">
        <div class="card shadow border-0">
            <div class="card-header bg-warning text-dark">
                <h5 class="mb-0 fw-bold">Tap Kartu Absensi</h5>
            </div>
            <div class="card-body text-center">

                <form id="formTap" method="post">
                    @csrf

                    <div class="mb-3">
                        <input type="text"
                            name="rfid_uid"
                            id="rfid_uid"
                            class="form-control form-control-lg text-center"
                            placeholder="Silakan Tap Kartu..."
                            autofocus
                            autocomplete="off">
                    </div>

                    <button type="submit" class="btn btn-dark">
                        Proses Absensi
                    </button>
                </form>

                <div id="tapResult" class="mt-3"></div>

            </div>
        </div>
    </section>
    <!-- ================= DASHBOARD ================= -->
    <section id="dashboard" class="page">
        <h1 class="fw-bold mb-1">Dashboard</h1>
        <p class="text-muted">Tanggal: {{ $tanggal ?? date('d F Y') }}</p>

        <!-- TABLE -->
        <section class="quickaccess mb-4">
            <h5 class="fw-semibold mb-3">Table Akses Harian</h5>

            <div class="table-responsive">
                <table class="tabelhadir table table-striped align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>Waktu Masuk</th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                            <th>Program</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if (!empty($absensi_hari_ini))
                            @foreach ($absensi_hari_ini as $row)
                                <tr>
                                    <td>{{ $row['jam_masuk'] }}</td>
                                    <td>{{ $row['rfid_uid'] }}</td>
                                    <td>{{ $row['nama'] }}</td>
                                    <td>{{ $row['divisi'] }}</td>
                                    <td>{{ $row['program'] }}</td>
                                </tr>
                            @endforeach
                        @else
                            <tr>
                                <td colspan="5" class="text-center">Belum ada absensi hari ini</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </section>

        <!-- TAMU -->
        <section class="quickaccess mb-4">
            <h5 class="fw-semibold mb-3">Tamu Hari Ini</h5>

            <div class="table-responsive">
                <table class="tabelhadir table table-striped align-middle">
                    <thead>
                        <tr class="table-dark">
                            <th>Waktu Masuk</th>
                            <th>ID</th>
                            <th>Nama</th>
                            <th>Divisi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($tamu_hari_ini as $row)
                            <tr>
                                <td>{{ $row['jam_masuk'] }}</td>
                                <td>{{ $row['rfid_uid'] }}</td>
                                <td>{{ $row['nama'] }}</td>
                                <td>{{ $row['divisi'] }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Belum ada tamu hari ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- CHARTS SECTION -->
        <section class="charts">
            <div class="row mb-4">
                <!-- Chart Absensi per Divisi -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-primary text-white">
                            <h6 class="m-0 font-weight-bold">Absensi per Divisi (Bulan Ini)</h6>
                        </div>
                        <div class="card-body">
                            @if (!empty($chartDivisi))
                                <canvas id="chartDivisi" height="250"></canvas>
                            @else
                                <p class="text-center text-muted">Belum ada data absensi bulan ini</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Chart Absensi per Status -->
                <div class="col-md-6 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-success text-white">
                            <h6 class="m-0 font-weight-bold">Status Absensi (Bulan Ini)</h6>
                        </div>
                        <div class="card-body">
                            @if (!empty($chartStatus))
                                <canvas id="chartStatus" height="250"></canvas>
                            @else
                                <p class="text-center text-muted">Belum ada data status bulan ini</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Chart Trend Mingguan -->
            <div class="row">
                <div class="col-12 mb-4">
                    <div class="card shadow">
                        <div class="card-header py-3 bg-info text-white">
                            <h6 class="m-0 font-weight-bold">Trend Absensi (7 Hari Terakhir)</h6>
                        </div>
                        <div class="card-body">
                            @if (!empty($chartTrend))
                                <canvas id="chartTrend" height="100"></canvas>
                            @else
                                <p class="text-center text-muted">Belum ada data trend 7 hari terakhir</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Data dari PHP dengan fallback
    const dataDivisi = @json($chartDivisi ?? []);
    const dataStatus = @json($chartStatus ?? []);
    const dataTrend = @json($chartTrend ?? []);

    console.log('Data Divisi:', dataDivisi);
    console.log('Data Status:', dataStatus);
    console.log('Data Trend:', dataTrend);

    // Chart Divisi
    if (dataDivisi.length > 0) {
        const ctxDivisi = document.getElementById('chartDivisi');
        if (ctxDivisi) {
            new Chart(ctxDivisi.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: dataDivisi.map(item => item.divisi),
                    datasets: [{
                        label: 'Jumlah Absensi',
                        data: dataDivisi.map(item => parseInt(item.total)),
                        backgroundColor: 'rgba(54, 162, 235, 0.6)',
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }

    // Chart Status
    if (dataStatus.length > 0) {
        const ctxStatus = document.getElementById('chartStatus');
        if (ctxStatus) {
            const statusColors = {
                'hadir': 'rgba(75, 192, 192, 0.6)',
                'terlambat': 'rgba(255, 206, 86, 0.6)',
                'tidak hadir': 'rgba(255, 99, 132, 0.6)'
            };

            new Chart(ctxStatus.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: dataStatus.map(item => item.status.charAt(0).toUpperCase() + item.status.slice(1)),
                    datasets: [{
                        data: dataStatus.map(item => parseInt(item.total)),
                        backgroundColor: dataStatus.map(item => statusColors[item.status.toLowerCase()] || 'rgba(201, 203, 207, 0.6)'),
                        borderColor: '#fff',
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    }

    // Chart Trend
    if (dataTrend.length > 0) {
        const ctxTrend = document.getElementById('chartTrend');
        if (ctxTrend) {
            new Chart(ctxTrend.getContext('2d'), {
                type: 'line',
                data: {
                    labels: dataTrend.map(item => {
                        const date = new Date(item.tanggal);
                        return date.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'short'
                        });
                    }),
                    datasets: [{
                        label: 'Jumlah Absensi',
                        data: dataTrend.map(item => parseInt(item.total)),
                        fill: true,
                        backgroundColor: 'rgba(153, 102, 255, 0.2)',
                        borderColor: 'rgba(153, 102, 255, 1)',
                        borderWidth: 2,
                        tension: 0.4,
                        pointRadius: 4,
                        pointBackgroundColor: 'rgba(153, 102, 255, 1)'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                stepSize: 1,
                                precision: 0
                            }
                        }
                    }
                }
            });
        }
    }


    function sendTap(uid) {
        fetch("/dashboard/absensi/tap", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('#formTap [name=_token]').value
                },
                body: JSON.stringify({
                    rfid_uid: uid
                })
            })
            .then(response => response.json())
            .then(data => {
                console.log(data);
                alert(data.message);
            })
            .catch(error => {
                console.error("Error:", error);
            });
    }

    document.getElementById("formTap").addEventListener("submit", function(e) {
        e.preventDefault();

        const uid = document.getElementById("rfid_uid").value;

        fetch("/dashboard/absensi/tap", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": document.querySelector('#formTap [name=_token]').value
                },
                body: JSON.stringify({
                    rfid_uid: uid
                })
            })
            .then(response => response.json())
            .then(data => {
                const alert = document.createElement("div");
                alert.className = `alert alert-${data.status === 'success' ? 'success' : 'danger'}`;
                alert.append(data.message);

                if (data.program) {
                    const program = document.createElement("div");
                    program.className = "small";
                    program.textContent = data.program;
                    alert.append(program);
                }

                if (data.status_absen === 'Terlambat') {
                    const terlambat = document.createElement("strong");
                    terlambat.textContent = "Terlambat";
                    alert.append(terlambat);
                }

                document.getElementById("tapResult").replaceChildren(alert);

                document.getElementById("rfid_uid").value = "";
                document.getElementById("rfid_uid").focus();
            })
            .catch(error => {
                console.error(error);
            });
    });
</script>

</x-layouts.dashboard>