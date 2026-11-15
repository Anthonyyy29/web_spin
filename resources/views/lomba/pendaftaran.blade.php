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
      <h1 class="text-white me-3 mb-0">PENDAFTARAN LOMBA</h1>
      <i class="bi bi-clipboard-check-fill text-white fs-1"></i>
    </div>

    <div class="bg-dark p-4 rounded border border-secondary col-12 col-md-6 mx-auto shadow-lg">

      <form method="post" enctype="multipart/form-data" id="formPendaftaran" action="{{ url('lomba/daftar') }}">
@csrf
        <div class="mb-4">
          <label for="pilihLomba" class="form-label text-white fw-bold">
            <i class="bi bi-trophy-fill me-2"></i>Pilih Lomba Yang Diinginkan
          </label>
          <select class="form-select border-secondary text-white bg-dark" id="pilihLomba" name="pilih_lomba" required>
            <option disabled {{ old('pilih_lomba') ? '' : 'selected' }} value="">Pilih jenis lomba</option>
            <option value="poster" {{ old('pilih_lomba') == 'poster' ? 'selected' : '' }}>Lomba Poster</option>
            <option value="podcast" {{ old('pilih_lomba') == 'podcast' ? 'selected' : '' }}>Lomba Podcast</option>
            <option value="konten" {{ old('pilih_lomba') == 'konten' ? 'selected' : '' }}>Lomba Konten</option>
            <option value="presenter" {{ old('pilih_lomba') == 'presenter' ? 'selected' : '' }}>Lomba Presenter</option>
          </select>

        </div>

        <div class="mb-4" id="fieldTim" style="display: none;">
          <div class="form-label text-white fw-bold mb-2">
            <i class="bi bi-mic-fill me-2"></i>Pendaftaran Tim
          </div>

          <input type="text" class="form-control border-secondary bg-dark text-white mb-2" name="nama_tim"
            placeholder="Masukkan Nama Tim" value="{{ old('nama_tim') }}">
          <small class="text-secondary">Jumlah maksimal anggota tim adalah 3 orang.</small>
          <div id="anggota-list">
            @php($anggotaOld = old('anggota'))
            @if (is_array($anggotaOld) && count($anggotaOld) > 0)
              @foreach ($anggotaOld as $i => $nama)
                @if ($i == 0)
                  <div class="input-group mb-2 anggota-default">
                    <input type="text" class="form-control border-secondary bg-dark text-white"
                      name="anggota[]" placeholder="Masukkan Nama Anggota"
                      value="{{ $nama }}" required>
                  </div>
                @else
                  <div class="input-group mb-2 anggota-item">
                    <input type="text" class="form-control border-secondary bg-dark text-white"
                      name="anggota[]" placeholder="Masukkan Nama Anggota"
                      value="{{ $nama }}" required>
                    <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-anggota"><i class="bi bi-x"></i></button>
                  </div>
              @endif
              @endforeach
            @else
              <div class="input-group mb-2 anggota-default">
                <input type="text" class="form-control border-secondary bg-dark text-white"
                  name="anggota[]" placeholder="Masukkan Nama Anggota" value="" required>
              </div>
            @endif
          </div>

          <button type="button" id="addAnggota" class="btn btn-outline-light btn-sm">
            <i class="bi bi-plus-lg"></i> Tambah Anggota
          </button>

        </div>

        <div class="mb-4" id="fieldNonTim" style="display: none;">
          <div class="form-label text-white fw-bold mb-2">
            <i class="bi bi-person-fill me-2"></i>Nama
          </div>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="nama_lengkap"
            placeholder="Masukkan Nama Lengkap"
            value="{{ old('nama_lengkap') }}">
        </div>

        <div class="mb-3">
          <label class="form-label text-white"><i class="bi bi-instagram me-2"></i>Masukkan Akun Instagram</label>
          <input type="text" class="form-control border-secondary bg-dark text-white"
            name="instagram" placeholder="@username" value="{{ old('instagram') }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label text-white"><i class="bi bi-envelope-fill me-2"></i>Masukkan Email Aktif</label>
          <input type="email" class="form-control border-secondary bg-dark text-white"
            name="email" placeholder="email@example.com" value="{{ old('email') }}" required>
        </div>
        <div class="mb-3">
          <label class="form-label text-white"><i class="bi bi-whatsapp me-2"></i>Masukkan Nomor WA Aktif</label>
          <input type="tel" class="form-control border-secondary bg-dark text-white"
            name="no_wa" placeholder="08xxxxxxxxxx" value="{{ old('no_wa') }}" required>
        </div>

        <div class="mb-3">
          <label class="form-label text-white">
            <i class="bi bi-file-earmark-arrow-up-fill me-2"></i>Bukti Pembayaran
          </label>
          <div class="d-flex flex-column form-control border-secondary bg-dark text-white p-3 rounded mb-2 gap-3">

            <div class="d-flex align-items-center">
              <img src="https://assets.jabarekspres.com/main/2023/02/shopeepay-2.jpg" alt="ShopeePay" style="height: 32px;" class="me-2">
              <span class="fw-semibold small">ShopeePay : 081809971330 (a.n. Maura Riani)</span>
            </div>

            <div class="d-flex align-items-center">
              <img src="https://cdn.antaranews.com/cache/1200x800/2022/04/25/dana.jpg.webp" alt="Dana" style="height: 32px;" class="me-2">
              <span class="fw-semibold small">Dana : 081809971330 (a.n. Maura Riani)</span>
            </div>
          </div>

          <input class="form-control border-secondary bg-dark text-white"
            id="buktiPembayaran" name="bukti_pembayaran" type="file" accept=".jpg,.jpeg,.pdf" required>
          <small class="text-secondary">File berupa .jpg / .jpeg / .pdf, maksimal ukuran 2MB.</small>
          <div id="previewBuktiPembayaran" class="mt-2"></div>
        </div>

        <div class="mb-3">
          <label class="form-label text-white">
            <i class="bi bi-file-earmark-person-fill me-2"></i>Kartu Tanda Mahasiswa / Kartu Pelajar / NISN
          </label>
          <input class="form-control border-secondary bg-dark text-white"
            id="kartuIdentitas" name="kartu_identitas" type="file" accept=".jpg,.jpeg,.pdf" required>
          <small class="text-secondary">File berupa .jpg / .jpeg / .pdf, maksimal ukuran 2MB.</small>
          <div id="previewKartuIdentitas" class="mt-2"></div>
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
  <script src="{{ url('assets/js/lomba/pendaftaran.min.js') }}"></script>
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

</body>

</html>