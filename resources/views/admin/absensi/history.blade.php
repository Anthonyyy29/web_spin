<x-layouts.dashboard :navmenu="$navmenu">

<div class="container">
  <!-- ================= HISTORY ================= -->
  <section id="history" class="page">

    <div class="btn-group mb-3" role="group">
      <button type="button" id="tabSesi" class="btn btn-dark btn-sm" data-jenis="sesi">Sesi</button>
      <button type="button" id="tabTamu" class="btn btn-outline-dark btn-sm" data-jenis="tamu">Tamu</button>
    </div>

    <section class="filters d-flex align-items-center gap-3 mb-3">
      <label for="dateFilter" class="fw-semibold">Filter Tanggal:</label>
      <input type="date" id="dateFilter" class="form-control w-auto">
      <button id="applyFilter" class="btn btn-outline-dark btn-sm">
        Apply
      </button>
      <a id="exportLink" href="{{ url('dashboard/absensi/export-excel') }}?jenis=sesi"
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
              <th>Program</th>
              <th>Jam Sesi</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody id="historyTableBody">
            <tr>
              <td colspan="8" class="text-center">Loading...</td>
            </tr>
          </tbody>
        </table>
      </div>
    </section>
  </section>
</div>

<script>
  const exportUrl = @json(url('dashboard/absensi/export-excel'));
  let jenisAktif = 'sesi';

  document.addEventListener('DOMContentLoaded', function() {
    loadHistory();
  });

  function messageRow(text, className = '') {
    const tr = document.createElement('tr');
    const td = document.createElement('td');
    td.colSpan = 8;
    td.className = 'text-center ' + className;
    td.textContent = text;
    tr.append(td);
    return tr;
  }

  function loadHistory(date = null) {
    const tbody = document.getElementById('historyTableBody');

    fetch('/dashboard/absensi/history/all?jenis=' + jenisAktif)
      .then(response => response.json())
      .then(result => {
        const data = date ? result.data.filter(item => item.tanggal === date) : result.data;

        if (result.status !== 'success' || data.length === 0) {
          tbody.replaceChildren(messageRow('Tidak ada data'));
          return;
        }

        const rows = data.map(item => {
          const tr = document.createElement('tr');
          [item.tanggal, item.jam_masuk, item.rfid_uid, item.nama, item.divisi, item.program, item.jam_sesi, item.status]
            .forEach(value => {
              const td = document.createElement('td');
              td.textContent = value || '-';
              tr.append(td);
            });
          return tr;
        });

        tbody.replaceChildren(...rows);
      })
      .catch(error => {
        console.error('Error:', error);
        tbody.replaceChildren(messageRow('Error loading data', 'text-danger'));
      });
  }

  document.getElementById('applyFilter').addEventListener('click', function() {
    loadHistory(document.getElementById('dateFilter').value);
  });

  document.querySelectorAll('[data-jenis]').forEach(button => {
    button.addEventListener('click', function() {
      jenisAktif = button.dataset.jenis;
      document.querySelectorAll('[data-jenis]').forEach(other => {
        other.className = 'btn btn-sm ' + (other === button ? 'btn-dark' : 'btn-outline-dark');
      });
      document.getElementById('exportLink').href = exportUrl + '?jenis=' + jenisAktif;
      loadHistory(document.getElementById('dateFilter').value || null);
    });
  });
</script>

</x-layouts.dashboard>