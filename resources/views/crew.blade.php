<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>STRFM Esa Unggul</title>
    <link rel="icon" type="image/png" href="assets/img/logo-strfm.jpg">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/index.css">
</head>

<body>
    @include('layouts.navbar')


    <!-- Our Team Section -->
    <div class="wrapper">
        <div class="title">
            <h4>Our Crew</h4>
        </div>
        <div class="card_Container">
            <!-- Team Member Cards -->
            @foreach ($crew as $anggota)
            <div class="card">
                <div class="imbBx">
                    <img src="/assets/uploads/{{ $anggota['image'] }}" alt="">
                </div>
                <div class="content">
                    <div class="contentBx">
                        <h3>{{ $anggota['name'] }}</h3>
                    </div>
                    <ul class="sci">
                        <li>
                            <a href="{{ $anggota['instagram_link'] }}">
                                <i class="fa-brands fa-instagram"></i>
                            </a>
                        </li>
                        <li>
                            <a href="{{ $anggota['linkedin_link'] }}">
                                <i class="fa-brands fa-linkedin-in"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white text-center py-10">
        <div class="container">
            <div class="row">
                <!-- Logo & Deskripsi -->
                <div class="col-md-3 mt-3">
                    <img src="/assets/img/logo-strfm.jpg" alt="STRFM Logo" width="80" class="mb-2" />
                    <p class="small">
                        🎧 <strong>STRFM - Suara Mahasiswa, Suara Perubahan</strong><br />
                        Dengarkan, berpartisipasi, dan jadilah bagian dari gelombang inspirasi.
                    </p>
                </div>

                <!-- Produk -->
                <div class="col-md-3 mt-3">
                    <h5>Product</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white text-decoration-none d-block link-opacity-75-hover mb-2">Listen</a></li>
                        <li><a href="#" class="text-white text-decoration-none d-block link-opacity-75-hover mb-2">News</a></li>
                        <li><a href="#" class="text-white text-decoration-none d-block link-opacity-75-hover mb-2">About Us</a></li>
                    </ul>
                </div>

                <!-- Social Media -->
                <div class="col-md-3 text-center mt-3">
                    <h5>Social Media</h5>
                    <ul class="list-unstyled">
                        <li class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <img src="assets/img/spotify.png" alt="Spotify" width="30" height="30">
                            <a href="#" class="text-white text-decoration-none link-opacity-75-hover">Spin The Radio FM</a>
                        </li>
                        <li class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <img src="assets/img/ig.png" alt="Instagram" width="30" height="30">
                            <a href="#" class="text-white text-decoration-none link-opacity-75-hover">@spintheradiofm</a>
                        </li>
                        <li class="d-flex justify-content-center align-items-center gap-2 mb-2">
                            <img src="assets/img/tiktok.png" alt="TikTok" width="30" height="30">
                            <a href="#" class="text-white text-decoration-none link-opacity-75-hover">@spintheradiofm</a>
                        </li>
                        <li class="d-flex justify-content-center align-items-center gap-2">
                            <img src="assets/img/yt.png" alt="YouTube" width="30" height="30">
                            <a href="#" class="text-white text-decoration-none link-opacity-75-hover">@spintheradiofm</a>
                        </li>
                    </ul>
                </div>

                <!-- Kontak -->
                <div class="col-md-3 mt-3">
                    <h5>Contact</h5>
                    <p class="mb-1"><i class="fa-solid fa-phone me-2"></i> <strong>08128239233</strong></p>
                    <p class="mb-1"><i class="fa-solid fa-envelope me-2"></i> <strong>strfm@gmail.com</strong></p>
                    <p><i class="fa-solid fa-location-dot me-2"></i> Universitas Esa Unggul Kampus Bekasi</p>
                </div>
            </div>
        </div>

        <!-- Copyright Section -->
        <div class="footerCopy bg-primary text-white py-2 mt-2">
            <p class="mb-0 fs-6">Copyright © 2025 Spin The Radio FM Universitas Esa Unggul Kampus Bekasi. All Rights Reserved.</p>
        </div>
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.5.1/dist/jquery.slim.min.js"></script>
    <script src="/assets/js/searchpodcast.js"></script>
</body>

</html>