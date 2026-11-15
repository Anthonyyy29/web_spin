<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Guidebook Lomba</title>
  <link rel="icon" type="image/png" href="/assets/img/logo-strfm.jpg">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

  @include('lomba.maincss')
</head>

<body>

  <div class="container py-5">
    <!-- <div class="d-flex justify-content-center align-items-center mb-5">
      <img src="{{ url('assets/img/lomba/title_spinvibes.png') }}" class="img-fluid" style="max-height: 200px;" alt="Spinvibes Logo">
    </div> -->

    <div class="d-grid gap-3 col-12 col-md-6 mx-auto">
      <a href="{{ url("assets/uploads/lomba/guidebook-spinvibes.pdf") }}" target="_blank" class="btn btn-dark border border-secondary text-white d-flex align-items-center justify-content-start gap-3 py-3 px-4 fs-5" style="margin-top: 110px;">
        <i class="bi file-earmark-text-fill fs-3 text-white"></i>
        GuideBook Lomba
      </a>

      <a href="{{ url('lomba/pendaftaran') }}" class="btn btn-dark border border-secondary text-white d-flex align-items-center justify-content-start gap-3 py-3 px-4 fs-5">
        <i class="bi bi-link-45deg fs-3 text-white"></i>
        Link Pendaftaran Lomba
      </a>

      <a href="{{ url('lomba/pengumpulan-karya') }}" class="btn btn-dark border border-secondary text-white d-flex align-items-center justify-content-start gap-3 py-3 px-4 fs-5">
        <i class="bi bi-link-45deg fs-3 text-white"></i>
        Link Pengumpulan Karya
      </a>

      <a href="{{ url("assets/uploads/lomba/Surat-Pernyataan-Orisinalitas-Karya.docx") }}" target="_blank" class="btn btn-dark border border-secondary text-white d-flex align-items-center justify-content-start gap-3 py-3 px-4 fs-5">
        <i class="bi file-earmark-text-fill fs-3 text-white"></i>
        Surat Pernyataan Orisinalitas Karya
      </a>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>