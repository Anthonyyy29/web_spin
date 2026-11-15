<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>STRFM Esa Unggul</title>
  <link rel="icon" type="image/png" href="/assets/img/logo-strfm.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <!--<link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css'>-->
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css'>
  <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="/assets/lib/slick/slick.css" rel="stylesheet">
  <link href="/assets/lib/slick/slick-theme.css" rel="stylesheet">
  <link rel="stylesheet" href="/assets/css/news.css">

</head>

<body>
  @include('layouts.navbar')

  @include('layouts.news-header')

  <!-- Breadcrumb Start -->
  <div class="breadcrumb-wrap">
    <div class="container-fluid">
      <ul class="breadcrumb">
        <li class="breadcrumb-item"><a href="/home">Home</a></li>
        <li class="breadcrumb-item"><a href="/news">News</a></li>
        <li class="breadcrumb-item active">{{ $activeCategory }}</li>
      </ul>
    </div>
  </div>
  <!-- Breadcrumb End -->

  <main>
    <div class="container mt-4">
        <h1 class="text-center">{{ $activeCategory }}</h1>
        <hr>
      </div>

      <!-- News List -->
      <div class="container">
        <div class="row">
          @foreach ($news as $article)
            <div class="col-md-4 mb-4">
              <div class="card h-100">
                <img src="/assets/uploads/{{ $article['image'] }}" class="card-img-top" alt="News Image">
                <div class="card-body">
                  <h5 class="card-title">{{ $article['title'] }}</h5>
                  @foreach ($article['description_row'] as $description)
                    <p class="card-text" {!! ($description == '') ? "style='margin:15px;'" : "style='margin:0;'" !!}>{{ $description }}</p>
                  @endforeach
                  <a href="/news/details/{{ $article['id'] }}" class="btn btn-primary mt-2">Read More</a>
                  <p class="text-muted mt-2"><i class="far fa-clock"></i> {{ $article['created_at'] }}</p>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
  </main>
  

  @include('layouts.footer')

  <!-- JavaScript Libraries -->
  <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/lib/easing/easing.min.js"></script>
  <script src="/assets/lib/slick/slick.min.js"></script>


  <!-- Template Javascript -->
  <script src="/assets/js/main.js"></script>
  <script src="/assets/js/searchpodcast.js"></script>
</body>

</html>