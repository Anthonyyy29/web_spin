<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>STRFM Esa Unggul</title>
  <link rel="icon" type="image/png" href="assets/img/logo-strfm.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css'>
  <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body>
  @include('layouts.navbar')
  <!-- Breadcrumb Start -->
  <div class="breadcrumb-wrap">
    <div class="container-fluid">
      <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">Home</a></li>
        <li class="breadcrumb-item"><a href="/listen">Listen</a></li>
        <li class="breadcrumb-item active">{{ $content['title'] }}</li>
      </ul>
    </div>
  </div>
  <!-- Breadcrumb End -->

  <!-- Main Content -->
  <section class="podcast-section">
    <!-- Cover Segment -->
    <div class="segment-cover">
      <img src="/assets/uploads/{{ $content['image'] }}" alt="Podcast Cover">
      <div class="segment-info">
        <h2>🎙️ {{ $content['title'] }}</h2>
        <p>Discover deep conversations and insightful stories in our latest podcast series.</p>
      </div>
    </div>

    <!-- Episode List -->
    <div class="podcast-container">
      @foreach ($episodes as $episode)
        <div class="podcast-card">
          <div class="podcast-content">
            <iframe src="{{ $episode['audio'] }}" allow="autoplay; clipboard-write; encrypted-media; fullscreen; picture-in-picture"></iframe>
            <h3>Deskripsi</h3>
            <p>{{ $episode['description'] }}</p>
            <button class="read-more">Lihat Selengkapnya</button>
          </div>
        </div>
      @endforeach
    </div>
  </section>

  @include('layouts.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src='https://code.jquery.com/jquery-1.12.0.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js'></script>
  <script src="/assets/js/searchpodcast.js"></script>
  <script src="/assets/js/index.js"></script>
</body>

</html>