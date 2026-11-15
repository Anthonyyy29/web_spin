<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
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

  <!-- Header -->
  <header class="text-center my-0">
    <div class="d-flex flex-column align-items-center justify-content-center">
      <h1>
        SPIN THE RADIO FM
      </h1>
      <h3>
        "Voice, Inspire, Action!"
      </h3>
    </div>
  </header>

  <!-- Main Content -->
  <div class="container">
    <!-- Our Podcast -->
    <section class="text-center my-5">
      <h3 class="mb-4 fw-bold text-primary text-uppercase">Our Podcast</h3>
      <div class="container">
        <div class="row" id="podcast-gallery">
          @foreach ($content as $podcast)
            <div class="col-md-3 mb-3">
              <a href="/listen/content/{{ $podcast['id'] }}">
                <img src="/assets/uploads/{{ $podcast['image'] }}" class="img-fluid rounded-4" alt="Podcast 1">
              </a>
            </div>
          @endforeach
        </div>
        <a href="/listen" class="btn btn-custom mt-3">View More</a>
      </div>
    </section>

    <!-- Our Crew -->
    <section class="text-center my-5">
      <h3 class="fw-bold text-primary text-uppercase">Our Crew</h3>
      <div class="container-fluid">
        <div class="row">
          <div class="col-md-11">
            <div id="crew-slider" class="owl-carousel">
              @foreach ($crew as $anggota)
                <div class="post-slide">
                  <div class="post-img">
                    <img src="/assets/uploads/{{ $anggota['image'] }}" alt="">
                    <a href="{{ $anggota['instagram_link'] }}" class="over-layer"><i class="fa fa-link"></i></a>
                  </div>
                </div>
              @endforeach
            </div>
          </div>
        </div>
        <a href="/crew" class="btn btn-custom mt-3">View More</a>
    </section>
    <section>
        <!-- Floating hint before click -->
        <div class="radio_hint" id="radioHint">
          <span class="hint-text">Play Radio Now!!!</span>
          <span class="hint-arrow">↓</span>
        </div>

        <!-- Radio Button -->
        <a class="radio_btn" href="#" id="playButton">
          <div class="icon-circle">
            <i class="fa-solid fa-play" id="playIcon"></i>
          </div>
          <span class="radio-label" id="radioLabel">Playing...</span>
          <div class="wave" id="waveAnim">
            <span></span><span></span><span></span><span></span><span></span>
          </div>
        </a>

        <!-- Audio -->
        <audio id="radioAudio" src="https://radio.spintheradiofm.site/listen/radio-strfm/radio.mp3"></audio>
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