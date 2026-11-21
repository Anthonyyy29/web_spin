<footer class="spin-footer">
    <div class="spin-container spin-footer__inner">
        <div class="spin-footer__grid">
            {{-- Logo & Deskripsi --}}
            <div>
                <a href="/home" class="spin-footer__brand">
                    <img src="{{ asset('assets/ui/logo-spin.png') }}" alt="Spin The Radio FM" />
                </a>
                <p class="spin-footer__tagline">STRFM - The Best Radio Campus</p>
                <p class="spin-footer__sub">Good Looking Tidak Perlu, Suara Nomor Satu!</p>
            </div>

            {{-- Product --}}
            <div>
                <h2 class="spin-footer__heading">Product</h2>
                <ul class="spin-footer__list">
                    <li><a href="/listen">Listen</a></li>
                    <li><a href="/news">News</a></li>
                    <li><a href="/crew">Crew</a></li>
                    <li><a href="/aboutus">About Us</a></li>
                </ul>
            </div>

            {{-- Social Media --}}
            <div>
                <h2 class="spin-footer__heading">Social Media</h2>
                <ul class="spin-footer__list">
                    <li>
                        <a href="https://open.spotify.com" target="_blank" rel="noopener noreferrer" class="spin-footer__social-link">
                            <img src="{{ asset('assets/ui/spotify.png') }}" alt="Spotify" />
                            <span>Spotify &middot; Spin The Radio FM</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://instagram.com/spintheradiofm" target="_blank" rel="noopener noreferrer" class="spin-footer__social-link">
                            <img src="{{ asset('assets/ui/instagram.png') }}" alt="Instagram" />
                            <span>Instagram &middot; @spintheradiofm</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://tiktok.com/@spintheradiofm" target="_blank" rel="noopener noreferrer" class="spin-footer__social-link">
                            <img src="{{ asset('assets/ui/tiktok.png') }}" alt="TikTok" />
                            <span>TikTok &middot; @spintheradiofm</span>
                        </a>
                    </li>
                    <li>
                        <a href="https://youtube.com/@spintheradiofm" target="_blank" rel="noopener noreferrer" class="spin-footer__social-link">
                            <img src="{{ asset('assets/ui/youtube.png') }}" alt="YouTube" />
                            <span>YouTube &middot; @spintheradiofm</span>
                        </a>
                    </li>
                </ul>
            </div>

            {{-- Contact --}}
            <div>
                <h2 class="spin-footer__heading">Contact</h2>
                <ul class="spin-footer__list">
                    <li>+62 812-1975-2441</li>
                    <li>
                        <a href="mailto:publicrelations.strfm@gmail.com">publicrelations.strfm@gmail.com</a>
                    </li>
                    <li>Universitas Esa Unggul Kampus Bekasi</li>
                </ul>
            </div>
        </div>

        <div class="spin-footer__copy">
            Copyright &copy; {{ date('Y') }} Spin The Radio FM Universitas Esa Unggul Kampus Bekasi. All Rights Reserved.
        </div>
    </div>
</footer>
