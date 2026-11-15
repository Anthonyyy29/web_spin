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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- CSS Libraries -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.10.0/css/all.min.css" rel="stylesheet">
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
                <li class="breadcrumb-item active">News</li>
            </ul>
        </div>
    </div>
    <!-- Breadcrumb End -->
    @if (isset($top_news))
        <!-- Top News Start-->
        <div class="top-news">
            <div class="container-fluid">
                <div class="row">
                    @foreach ($top_news as $index => $news)
                        @if ($index == 0)
                            <div class="col-md-6 tn-left">
                                <div class="tn-img">
                                    <img src="/assets/uploads/{{ $news['image'] }}" />
                                    <div class="tn-content">
                                        <div class="tn-content-inner">
                                            <a class="tn-date"><i class="far fa-clock"></i> {{ $news['created_at'] }}</a>
                                            <a class="tn-title" href="/news/details/{{ $news['id'] }}">{{ $news['title'] }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @if ($index == 1)
                                <div class="col-md-6 tn-right">
                                    <div class="row">
                                    @endif

                                    <div class="col-md-6">
                                        <div class="tn-img">
                                            <img src="/assets/uploads/{{ $news['image'] }}" />
                                            <div class="tn-content">
                                                <div class="tn-content-inner">
                                                    <a class="tn-date"><i class="far fa-clock"></i> {{ $news['created_at'] }}</a>
                                                    <a class="tn-title" href="/news/details/{{ $news['id'] }}">{{ $news['title'] }}</a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    @if ($index == count($top_news) - 1)
                                    </div>
                                </div>
                            @endif

                        @endif
                    @endforeach
                </div>

            </div>
        </div>
        <!-- Top News End-->
    @endif

    <div class="cat-news">
        <div class="container-fluid">
            <div class="row">
                @foreach ($categorized_news as $category => $newsList)
                    <div class="col-md-6">
                        <h2><i class="fas fa-align-justify"></i>{{ $category }}</h2>
                        <div class="row cn-slider">
                            @foreach ($newsList as $news)
                                <div class="col-md-6">
                                    <div class="cn-img">
                                        <img src="/assets/uploads/{{ $news['image'] }}" />
                                        <div class="cn-content">
                                            <div class="cn-content-inner">
                                                <a class="cn-date"><i class="far fa-clock"></i>{{ $news['created_at'] }}</a>
                                                <a class="cn-title" href="/news/details/{{ $news['id'] }}">{{ $news['title'] }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                            @if ($lastNews = collect($newsList)->last())
                                    <!-- Button View More -->
                                    <div class="col-md-6">
                                        <div class="cn-img view-more">
                                            <img src="/assets/uploads/{{ $lastNews['image'] }}" alt="View More">
                                            <div class="cn-overlay">
                                                <a href="/news/category/{{ $category }}" class="view-more-btn">View More</a>
                                            </div>
                                        </div>
                                    </div>
                            @else
                                <p>No news available.</p>
                            @endif

                        </div>
                    </div>
                @endforeach
                <div class="col-md-6">
                    <div class="sidebar">
                        <div class="sidebar-widget">
                            <h2><i class="fas fa-align-justify"></i>Tags</h2>
                            <div class="tags">
                                <a href="">National</a>
                                <a href="">International</a>
                                <a href="">Economics</a>
                                <a href="">Politics</a>
                                <a href="">Lifestyle</a>
                                <a href="">Technology</a>
                                <a href="">Trades</a>
                                <a href="">National</a>
                                <a href="">International</a>
                                <a href="">Economics</a>
                                <a href="">Politics</a>
                                <a href="">Lifestyle</a>
                                <a href="">Technology</a>
                                <a href="">Trades</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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