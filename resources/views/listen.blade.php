<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>STRFM Esa Unggul</title>
  <link rel="icon" type="image/png" href="/assets/img/logo-strfm.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css'>
  <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body>
  @include('layouts.navbar')

  @if (session('error'))
    <div class="alert alert-danger">
      {{ session('error') }}
    </div>
  @endif

    <!-- Breadcrumb Start -->
  <div class="breadcrumb-wrap">
    <div class="container-fluid">
      <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">Home</a></li>
        <li class="breadcrumb-item active">Listen</li>
      </ul>
    </div>
  </div>
  <!-- Breadcrumb End -->

  <!-- Main Content -->
  <div class="container">
    @if ($search_mode !== 'podcast')
      <!-- Our Podcast -->
      <section class="text-center my-5">
        <h3 class="mb-4 fw-bold text-primary text-uppercase title">Our Segment Podcast</h3>
        <div class="container">
          <div class="row" id="podcast-gallery-listen">
            @foreach ($contents as $content)
              <div class="col-md-3 mb-3">
                <a href="/listen/content/{{ $content['id'] }}">
                  <img src="/assets/uploads/{{ $content['image'] }}" class="img-fluid rounded-4" alt="Podcast 1">
                </a>
              </div>
            @endforeach
          </div>
        </div>
      </section>
    @endif

    <section class="text-center my-5">
      <h3 class="mt-3 mb-2 fw-bold text-primary text-uppercase title">{{ $search_mode !=''? 'Result Podcast' : 'last Podcast' }}</h3>
      <div class="podcast-container">
        @foreach ($last_episodes as $episode)
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
  </div>

  @include('layouts.footer')

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src='https://code.jquery.com/jquery-1.12.0.min.js'></script>
  <script src='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.js'></script>
  <script src="/assets/js/index.js"></script>
  <script src="/assets/js/searchpodcast.js"></script>
</body>

</html>