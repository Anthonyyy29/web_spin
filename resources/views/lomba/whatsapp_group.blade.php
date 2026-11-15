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
      <h1 class="text-white me-3 mb-0">WHATSAPP GROUP</h1>
      <i class="bi bi-whatsapp text-white fs-1"></i>
    </div>
    <div class="bg-dark p-4 rounded border border-secondary col-12 col-md-6 mx-auto shadow-lg">

      <div class="mb-4">
        <p class="text-white mb-0">
          <i class="bi bi-info-circle-fill text-warning me-2"></i>
          Bergabunglah ke grup WhatsApp sesuai kategori lomba yang kamu ikuti untuk mendapatkan informasi, pengumuman, dan koordinasi lebih lanjut dari panitia.
        </p>
      </div>
      <div class="mb-4">
        <div class="p-3 bg-secondary bg-opacity-10 rounded border border-secondary">
          <h5 class="text-white fw-bold mb-3 d-flex align-items-center">
            <i class="bi bi-whatsapp text-success fs-4 me-2"></i> Grup WhatsApp Lomba
          </h5>
          <div class="d-grid gap-2">
            <a href="https://chat.whatsapp.com/CNAiYPO3Hjh7FTWGCKAdIS" target="_blank" class="btn btn-outline-success text-start d-flex align-items-center">
              <i class="bi bi-person-video3 me-2 fs-5"></i> Presenter
            </a>
            <a href="https://chat.whatsapp.com/IbOK8QuhuJD2qC7fW3plqL" target="_blank" class="btn btn-outline-success text-start d-flex align-items-center">
              <i class="bi bi-camera-reels-fill me-2 fs-5"></i> Konten
            </a>
            <a href="https://chat.whatsapp.com/FPZ0c5jNsXrHZV1Ins3Os6" target="_blank" class="btn btn-outline-success text-start d-flex align-items-center">
              <i class="bi bi-mic-fill me-2 fs-5"></i> Podcast
            </a>
            <a href="https://chat.whatsapp.com/LL29jVfWfGS58ulQR891vr" target="_blank" class="btn btn-outline-success text-start d-flex align-items-center">
              <i class="bi bi-image-fill me-2 fs-5"></i> Lomba Poster
            </a>
          </div>
        </div>
      </div>
      <button onclick="window.location.href='{{ url('lomba/reset-pendaftaran') }}'"
        class="btn btn-dark border border-secondary text-white w-100 mt-3 py-2 d-flex align-items-center justify-content-center gap-2">
        <i class="bi bi-check2-circle fs-5"></i> Daftar Lagi?
      </button>
    </div>
  </div>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://www.google.com/recaptcha/api.js" async defer></script>

  <script>
    @if (session('success'))
      Swal.fire({
        icon: 'success',
        title: @json(session('success')),
        showConfirmButton: false,
        timer: 4000
      });

    @endif
  </script>

</body>

</html>