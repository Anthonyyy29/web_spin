<footer class="bg-dark text-white text-center py-10">
    <div class="container">
        <div class="row">
            <!-- Logo & Deskripsi -->
            <div class="col-md-3 mt-3">
                <img src="/assets/img/logo-strfm.jpg" alt="STRFM Logo" width="80" class="mb-2" />
                <p class="small">
                    🎧 <strong>STRFM - The Best Radio Campus</strong><br />
                    Good Looking Tidak Perlu, Suara Nomor Satu!
                </p>
            </div>

            <!-- Produk -->
            <div class="col-md-3 mt-3">
                <h5>Product</h5>
                <ul class="list-unstyled">
                    <li><a href="/listen" class="text-white text-decoration-none d-block link-opacity-75-hover mb-2">Listen</a></li>
                    <li><a href="/news" class="text-white text-decoration-none d-block link-opacity-75-hover mb-2">News</a></li>
                    <li><a href="/aboutus" class="text-white text-decoration-none d-block link-opacity-75-hover mb-2">About Us</a></li>
                </ul>
            </div>

            <!-- Social Media -->
            <div class="col-md-3 text-center mt-3">
                <h5>Social Media</h5>
                <ul class="list-unstyled">
                    <li class="d-flex justify-content-center align-items-center gap-2 mb-2">
                        <img src="/assets/img/spotify.png" alt="Spotify" width="30" height="30">
                        <a href="#" class="text-white text-decoration-none link-opacity-75-hover">Spin The Radio FM</a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center gap-2 mb-2">
                        <img src="/assets/img/ig.png" alt="Instagram" width="30" height="30">
                        <a href="#" class="text-white text-decoration-none link-opacity-75-hover">@spintheradiofm</a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center gap-2 mb-2">
                        <img src="/assets/img/tiktok.png" alt="TikTok" width="30" height="30">
                        <a href="#" class="text-white text-decoration-none link-opacity-75-hover">@spintheradiofm</a>
                    </li>
                    <li class="d-flex justify-content-center align-items-center gap-2">
                        <img src="/assets/img/yt.png" alt="YouTube" width="30" height="30">
                        <a href="#" class="text-white text-decoration-none link-opacity-75-hover">@spintheradiofm</a>
                    </li>
                </ul>
            </div>

            <!-- Kontak -->
            <div class="col-md-3 mt-3">
                <h5>Contact</h5>
                <p class="mb-1"><i class="fa-solid fa-phone me-2"></i> <strong>+62 812-1975-2441</strong></p>
                <p class="mb-1"><i class="fa-solid fa-envelope me-2"></i> <strong>publicrelations.strfm@gmail.com</strong></p>
                <p><i class="fa-solid fa-location-dot me-2"></i> Universitas Esa Unggul Kampus Bekasi</p>
            </div>
        </div>
    </div>

    <!-- Copyright Section -->
    <div class="footerCopy bg-primary text-white py-2 mt-2">
        <p class="mb-0 fs-6">Copyright © {{ date('Y') }} Spin The Radio FM Universitas Esa Unggul Kampus Bekasi. All Rights Reserved.</p>
    </div>

</footer>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            Swal.fire({
                icon: "success",
                title: "Success!",
                text: @json(session('success')),
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif
    @if (session('error'))
        <script>
            Swal.fire({
                icon: "error",
                title: "Error!",
                text: @json(session('error')),
                showConfirmButton: false,
                timer: 2000
            });
        </script>
    @endif