<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Form Pendaftaran Lomba</title>
  <link rel="icon" type="image/png" href="/assets/img/logo-strfm.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  @include('lomba.maincss')
</head>

<body>

  <div class="container py-5">
    <div class="col-12 col-md-6 mx-auto mb-3">
      <button onclick="window.location.href='{{ url('lomba') }}'" class="btn btn-danger d-flex align-items-center gap-2">
        <i class="bi bi-arrow-left fs-5"></i> Kembali
      </button>
    </div>

    <div class="bg-dark p-1 rounded border border-secondary col-12 col-md-6 mx-auto shadow-lg d-flex justify-content-center align-items-center mb-5">
      <h1 class="text-white me-3 mb-0">PENGUMPULAN KARYA</h1>
      <i class="bi bi-clipboard-check-fill text-white fs-1"></i>
    </div>
    <div class="bg-dark p-4 rounded border border-secondary col-12 col-md-6 mx-auto shadow-lg">

      <form method="post" enctype="multipart/form-data" id="formPendaftaran" action="{{ url('lomba/submit-karya') }}">
@csrf
        <div class="mb-4">
          <label for="pilihLomba" class="form-label text-white fw-bold">
            <i class="bi bi-trophy-fill me-2"></i>Pilih Lomba
          </label>
          <select class="form-select border-secondary text-white bg-dark"
            id="pilihLomba" name="jenis_lomba" required>
            <option disabled {{ old('jenis_lomba') ? '' : 'selected' }} value="">
              Pilih jenis lomba
            </option>
            <option value="podcast" {{ old('jenis_lomba') == 'podcast' ? 'selected' : '' }}>
              Lomba Podcast
            </option>
            <option value="konten" {{ old('jenis_lomba') == 'konten' ? 'selected' : '' }}>
              Lomba Konten
            </option>
            <option value="poster" {{ old('jenis_lomba') == 'poster' ? 'selected' : '' }}>
              Lomba Poster
            </option>
          </select>

        </div>

        <div id="fieldNamaPeserta" class="mb-4">
          <label class="form-label text-white fw-bold mb-2">
            <i class="bi bi-person-fill me-2"></i>Nama Peserta
          </label>
          <label class="form-label text-white mb-2">
            <i>(Pastikan tidak ada misspelling)</i>
          </label>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="nama_peserta"
            placeholder="Masukkan Nama Lengkap"
            value="{{ old('nama_peserta') }}" required>
        </div>

        <div id="fieldNamaTim" class="mb-4">
          <label class="form-label text-white fw-bold mb-2">
            <i class="bi bi-people-fill me-2"></i>Nama Tim
          </label>
          <label class="form-label text-white mb-2">
            <i>(Pastikan tidak ada misspelling)</i>
          </label>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="nama_tim"
            placeholder="Masukkan Nama Tim"
            value="{{ old('nama_tim') }}" required>
        </div>

        <div class="mb-4">
          <label class="form-label text-white fw-bold mb-2">
            <i class="bi bi-journal-text me-2"></i>Judul Karya
          </label>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="judul_karya"
            placeholder="Masukkan Judul Karya"
            value="{{ old('judul_karya') }}" required>
        </div>

        <div class="mb-4">
          <label class="form-label text-white fw-bold mb-2">
            <i class="bi bi-cloud-arrow-up me-2"></i>Link Karya Google Drive
          </label>
          <label class="form-label text-white mb-2">
            <i>(Pastikan publik)</i>
          </label>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="link_gdrive"
            placeholder="Masukkan Link Karya"
            value="{{ old('link_gdrive') }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label text-white">
            <i class="bi bi-camera-fill me-2"></i>Link Karya Postingan Instagram
          </label>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="link_instagram" placeholder="https://www.instagram.com/p/xxxxxxxxx" value="{{ old('link_instagram') }}" required>
        </div>

        <div id="fieldSuratPernyataan" class="mb-4" style="display:none;">
          <label class="form-label text-white fw-bold mb-2">
            <i class="bi bi-file-earmark-text-fill me-2"></i>Surat Pernyataan Originalitas Karya (PDF/JPG)
          </label>
          <input type="file" 
                class="form-control border-secondary bg-dark text-white" 
                name="surat_pernyataan" 
                accept=".pdf,.jpg,.jpeg" 
                required>
          <small class="text-white-50">Format file: PDF, JPG, atau JPEG (max 2MB)</small>
        </div>


        <div class="mb-4">
          <label class="form-label text-white fw-bold mb-2">
            <i class="bi bi-shield-lock me-2"></i>Verifikasi Keamanan
          </label>
          @include('components.recaptcha')
        </div>

        <button type="submit"
          class="btn btn-dark border border-secondary text-white w-100 mt-3 py-2 d-flex align-items-center justify-content-center gap-2">
          <i class="bi bi-check2-circle fs-5"></i> Daftar
        </button>
      </form>

    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>
  @if (session('success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: @json(session('success')),
        showConfirmButton: false,
        timer: 4000
      });
    </script>
  @endif
  @if (session('error'))
    <script>
      Swal.fire({
        icon: 'error',
        title: @json(session('error')),
        showConfirmButton: false,
        timer: 4000
      });
    </script>
  @endif

  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const select = document.getElementById('pilihLomba');
      const fieldTim = document.getElementById('fieldNamaTim');
      const fieldPes = document.getElementById('fieldNamaPeserta');
      const inputTim = fieldTim.querySelector('input');
      const inputPes = fieldPes.querySelector('input');
      const fieldSurat = document.getElementById('fieldSuratPernyataan');
      const inputSurat = fieldSurat.querySelector('input');

      function toggleFields() {
        if (select.value === 'poster') {
          fieldPes.style.display = 'block';
          fieldTim.style.display = 'none';
          fieldSurat.style.display = 'block';

          inputPes.setAttribute('required', '');
          inputTim.removeAttribute('required');
          inputSurat.setAttribute('required', '');
        } else {
          fieldTim.style.display = 'block';
          fieldPes.style.display = 'none';
          fieldSurat.style.display = 'none';

          inputTim.setAttribute('required', '');
          inputPes.removeAttribute('required');
          inputSurat.removeAttribute('required');
        }
      }
      

      select.addEventListener('change', toggleFields);
      @if (old('jenis_lomba') != '')
        toggleFields();
      @else
        fieldTim.style.display = 'none';
        fieldPes.style.display = 'none';
      @endif
    });
  </script>
</body>

</html>