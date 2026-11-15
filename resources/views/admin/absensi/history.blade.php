<x-layouts.dashboard :navmenu="$navmenu">

<div class="container">
  <!-- ================= HISTORY ================= -->
  <section id="history" class="page">

    <section class="filters d-flex align-items-center gap-3 mb-3">
      <label for="dateFilter" class="fw-semibold">Filter Tanggal:</label>
      <input type="date" id="dateFilter" class="form-control w-auto">
      <button id="applyFilter" class="btn btn-outline-dark btn-sm">
        Apply
      </button>
      <a href="{{ url('dashboard/absensi/export-excel') }}"
        class="btn btn-success btn-sm">
        Export Excel
      </a>
    </section>

    <section class="quickaccess">
      <h5 class="fw-semibold mb-3">Table Akses</h5>

      <div class="table-responsive">
        <table class="tabelhadir table table-striped align-middle">
          <thead class="table-dark">
            <tr>
              <th>Tanggal</th>
              <th>Waktu Masuk</th>
              <th>RFID UID</th>
              <th>Nama</th>
              <th>Divisi</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="historyTableBody">
            <tr>
              <td colspan="7" class="text-center">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</div>

<script>
  // Load all history saat halaman dimuat
  document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
  });

  // Fungsi untuk load history
  function loadHistory(date = null) {
    const tbody = document.getElementById('historyTableBody');

    fetch('/dashboard/absensi/history/all')
      .then(response => response.json())
      .then(result => {
        if (result.status === 'success' && result.data.length > 0) {
          let rows = '';

          // Filter by date jika ada
          let filteredData = result.data;
          if (date) {
            filteredData = result.data.filter(item => item.tanggal === date);
          }

          filteredData.forEach(item => {
            rows += `
                        <tr>
                            <td>${item.tanggal}</td>
                            <td>${item.jam_masuk || '-'}</td>
                            <td>${item.rfid_uid}</td>
                            <td>${item.nama}</td>
                            <td>${item.divisi}</td>
                            <td>${item.status}</td>
                        </tr>
                    `;
          });

          tbody.innerHTML = rows;
        } else {
          tbody.innerHTML = '<tr><td colspan="7" class="text-center">Tidak ada data</td></tr>';
        }
      })
      .catch(error => {
        console.error('Error:', error);
        tbody.innerHTML = '<tr><td colspan="7" class="text-center text-danger">Error loading data</td></tr>';
      });
  }

  // Apply filter
  document.getElementById('applyFilter').addEventListener('click', function() {
    const dateValue = document.getElementById('dateFilter').value;
    loadHistory(dateValue);
  });
</script>

</x-layouts.dashboard>