<x-public.layout active="about" title="About Us — Spin The Radio FM">
    <x-public.page-header
        title="ABOUT US"
        :breadcrumbs="[['label' => 'Home', 'url' => '/home'], ['label' => 'About Us']]" />

    <div class="spin-container spin-section">
        {{-- Profil Kabinet Sonavara --}}
        <section class="spin-feature-card">
            <div class="spin-feature-card__grid">
                <div class="spin-feature-card__media spin-feature-card__media--pink">
                    <div class="spin-feature-card__img-frame">
                        <img src="/assets/img/cover.png" alt="Kabinet Sonavara — Spin The Radio FM" />
                    </div>
                </div>

                <div class="spin-feature-card__body">
                    <span class="spin-tag spin-tag--yellow">Profil Organisasi</span>

                    <h2 style="margin: 1rem 0 0; font-size: clamp(1.5rem, 2.5vw, 2rem); font-weight: 800; color: var(--spin-maroon);">
                        Kabinet Sonavara
                    </h2>

                    <div class="spin-article-prose" style="margin-top: 1rem;">
                        <p>
                            &ldquo;Sona&rdquo; berasal dari kata <em>sonus</em> bahasa Latin yang berarti suara atau bunyi. Kata ini merepresentasikan identitas utama Spin The Radio FM sebagai media yang lahir dari suara, komunikasi, dan penyampaian cerita.
                        </p>
                        <p>
                            &ldquo;Vara&rdquo; dapat dikaitkan dengan bahasa Sanskerta, yang memiliki makna unggul, terbaik, atau pilihan. Makna ini menggambarkan harapan untuk menghadirkan kualitas terbaik dalam setiap proses, karya, dan kontribusi yang diberikan oleh anggota Spin The Radio FM.
                        </p>
                        <p>
                            Perpaduan <strong>Sonus + Vara</strong> dapat dimaknai sebagai &ldquo;suara yang unggul&rdquo; atau &ldquo;suara yang membawa nilai terbaik.&rdquo; Nama ini menggambarkan semangat untuk menjadikan setiap suara di dalam Spin The Radio FM bukan sekadar terdengar, tetapi mampu berkembang menjadi gagasan, karya, dan kontribusi yang bermakna.
                        </p>
                    </div>

                    {{-- Visi & Misi --}}
                    <div class="spin-visi-misi">
                        <div class="spin-info-box spin-info-box--yellow">
                            <h3>Visi</h3>
                            <p>
                                Menjadikan Spin The Radio FM sebagai media kampus yang inovatif, profesional, inspiratif, dalam perkembangan media digital, serta menjadi wadah kolaborasi yang mampu mengembangkan potensi, kreativitas, dan prestasi mahasiswa melalui karya yang berdampak positif bagi lingkungan kampus.
                            </p>
                        </div>

                        <div class="spin-info-box spin-info-box--sky">
                            <h3>Misi</h3>
                            <ol>
                                <li>Menghadirkan program siaran dan konten digital yang kreatif, informatif, edukatif, serta relevan dengan kebutuhan dan minat mahasiswa.</li>
                                <li>Membangun budaya organisasi yang komunikatif, kolaboratif, inklusif, dan menjunjung tinggi rasa kekeluargaan, sehingga tercipta lingkungan yang nyaman untuk belajar, berkarya, dan berkembang bersama.</li>
                                <li>Mengembangkan kompetensi anggota melalui pelatihan, dan praktik langsung di bidang penyiaran, produksi konten, desain kreatif, videografi, serta pengelolaan media digital.</li>
                                <li>Memperluas kolaborasi dengan organisasi mahasiswa, komunitas, institusi pendidikan, media, untuk meningkatkan eksistensi dan dampak positif Spin The Radio FM.</li>
                                <li>Menjunjung tinggi etika, integritas, disiplin, dan profesionalisme dalam setiap proses kerja, penyelenggaraan program, serta aktivitas penyiaran sebagai wujud komitmen terhadap kualitas dan tanggung jawab organisasi.</li>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Lokasi & Form Kontak (Komponen/Input Papan 5) --}}
        <section class="spin-section--divided">
            <div class="spin-section__header">
                <div>
                    <x-public.sticker text="FIND US & REACH OUT" tone="yellow" size="m" tag="h2" />
                    <p class="spin-section__subtitle">
                        Kunjungi studio kami di Universitas Esa Unggul Kampus Bekasi atau kirim pesan langsung melalui formulir di bawah ini.
                    </p>
                </div>
            </div>

            <div class="spin-contact-grid">
                {{-- Google Maps --}}
                <div class="spin-map-card">
                    <iframe
                        src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.790071888161!2d106.9677595697861!3d-6.158864246696813!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e698b4f86ba93ef%3A0xcf58e590de21713a!2sUniversitas%20Esa%20Unggul%20Kampus%20Bekasi!5e0!3m2!1sen!2sid!4v1743053543870!5m2!1sen!2sid"
                        title="Lokasi Universitas Esa Unggul Kampus Bekasi"
                        allowfullscreen
                        loading="lazy"
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

                {{-- Form Kontak --}}
                <div class="spin-form-card">
                    <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800;">Contact Us</h3>
                    <p style="margin: 0.25rem 0 0; font-size: 0.875rem; font-weight: 500; color: rgba(42, 23, 64, 0.7);">
                        Punya pertanyaan, saran, atau ingin berkolaborasi? Tulis pesanmu di sini.
                    </p>

                    <form action="/aboutus/sendmail" method="POST" class="spin-form-stack">
                        @csrf

                        <div class="spin-field">
                            <label for="name" class="spin-field__label">Full Name</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" placeholder="Nama lengkap"
                                @error('name') aria-invalid="true" @enderror
                                class="spin-field__control {{ $errors->has('name') ? 'is-invalid' : '' }}" />
                            @error('name')
                                <p class="spin-field__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="spin-field">
                            <label for="email" class="spin-field__label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="nama@email.com"
                                @error('email') aria-invalid="true" @enderror
                                class="spin-field__control {{ $errors->has('email') ? 'is-invalid' : '' }}" />
                            @error('email')
                                <p class="spin-field__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="spin-field">
                            <label for="subject" class="spin-field__label">Subject</label>
                            <input type="text" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Topik pesan"
                                @error('subject') aria-invalid="true" @enderror
                                class="spin-field__control {{ $errors->has('subject') ? 'is-invalid' : '' }}" />
                            @error('subject')
                                <p class="spin-field__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="spin-field">
                            <label for="message" class="spin-field__label">Message</label>
                            <textarea id="message" name="message" rows="4" placeholder="Tulis pesanmu di sini..."
                                @error('message') aria-invalid="true" @enderror
                                class="spin-field__control {{ $errors->has('message') ? 'is-invalid' : '' }}">{{ old('message') }}</textarea>
                            @error('message')
                                <p class="spin-field__error">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <x-public.pill type="submit" tone="maroon" size="l" class="spin-pill--full-mobile">
                                Send Message
                            </x-public.pill>
                        </div>
                    </form>
                </div>
            </div>
        </section>

        {{-- Partners / Sponsors --}}
        <section class="spin-section--divided">
            <div style="text-align: center; margin-bottom: 2rem;">
                <x-public.sticker text="OUR SATISFIED PARTNERS" tone="sky" size="m" tag="h2" />
            </div>

            @if ($sponsors->isNotEmpty())
                <div class="spin-sponsors-grid">
                    @foreach ($sponsors as $sponsor)
                        <div class="spin-sponsor-card">
                            <img src="/assets/uploads/{{ $sponsor['image'] }}" alt="Partner logo" loading="lazy" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="spin-empty-card">Belum ada data partner.</div>
            @endif
        </section>
    </div>
</x-public.layout>
