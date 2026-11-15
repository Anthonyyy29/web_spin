<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="description" content="">
  <meta name="keywords" content="app, landing, corporate, Creative, Html Template, Template">
  <meta name="author" content="web-themes">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- title -->
  <title>STRFM Esa Unggul</title>
  <link rel="icon" type="image/png" href="assets/img/logo-strfm.jpg">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" />
  <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/owl-carousel/1.3.3/owl.carousel.min.css'>
  <link rel='stylesheet' href='https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css'>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="/assets/css/owl.carousel.min.css" rel="stylesheet" type="text/css" />
  <link href="/assets/css/index.css" rel="stylesheet" type="text/css" />
</head>

<body>
  @include('layouts.navbar')

  <!-- About Us Section -->
  <section class="container py-5">
    <div class="card shadow-lg border-0 p-4">
      <div class="row align-items-center">
        <!-- Gambar -->
        <div class="col-lg-4 text-center mb-4">
          <img src="/assets/img/cover.png" class="img-fluid rounded-2 img-hover" alt="cover">
        </div>
        <!-- Konten -->
        <div class="col-lg-8">
          <h1 class="fw-bold text-primary">Kabinet Sonavara</h1>
          <p class="text-justify">
            "Sona" berasal dari kata sonus bahasa Latin yang berarti suara atau bunyi. Kata ini merepresentasikan identitas utama Spin The Radio FM sebagai media yang lahir dari suara, komunikasi, dan penyampaian cerita.
          </p>
          <p class="text-justify">
            "Vara" dapat dikaitkan dengan bahasa Sanskerta, yang memiliki makna unggul, terbaik, atau pilihan. Makna ini menggambarkan harapan untuk menghadirkan kualitas terbaik dalam setiap proses, karya, dan kontribusi yang diberikan oleh anggota Spin The Radio FM.
          </p>
          <p class="text-justify">
            Perpaduan Sonus + Vara dapat dimaknai sebagai “suara yang unggul” atau “suara yang membawa nilai terbaik.” Nama ini menggambarkan semangat untuk menjadikan setiap suara yang ada di dalam Spin The Radio FM bukan sekadar terdengar, tetapi mampu berkembang menjadi gagasan, karya, dan kontribusi yang bermakna.
          </p>

          <!-- Visi -->
          <h3 class="mt-4 text-secondary">Visi</h3>
          <ol class="list-group list-group-flush">
            <li class="list-group-item">Menjadikan Spin The Radio FM sebagai media kampus yang inovatif, profesional, inspiratif, dalam perkembangan media digital, serta menjadi wadah kolaborasi yang mampu mengembangkan potensi, kreativitas, dan prestasi mahasiswa melalui karya yang berdampak positif bagi lingkungan kampus.
            </li>
          </ol>

          <!-- Misi -->
          <h3 class="mt-4 text-secondary">Misi</h3>
          <ol class="list-group list-group-flush">
            <li class="list-group-item">1. Menghadirkan program siaran dan konten digital yang kreatif, informatif, edukatif, serta relevan dengan kebutuhan dan minat mahasiswa.
            </li>
            <li class="list-group-item">2. Membangun budaya organisasi yang komunikatif, kolaboratif, inklusif, dan menjunjung tinggi rasa kekeluargaan, sehingga tercipta lingkungan yang nyaman untuk belajar, berkarya, dan berkembang bersama.
            </li>
            <li class="list-group-item">3. Mengembangkan kompetensi anggota melalui pelatihan, dan praktik langsung di bidang penyiaran, produksi konten, desain kreatif, videografi, serta pengelolaan media digital.
            </li>
            <li class="list-group-item">4. Memperluas kolaborasi dengan organisasi mahasiswa, komunitas, institusi pendidikan, media, untuk meningkatkan eksistensi dan dampak positif Spin The Radio FM.
            </li>
            <li class="list-group-item">5. Menjunjung tinggi etika, integritas, disiplin, dan profesionalisme dalam setiap proses kerja, penyelenggaraan program, serta aktivitas penyiaran sebagai wujud komitmen terhadap kualitas dan tanggung jawab organisasi.
            </li>
          </ol>
        </div>
      </div>
    </div>
  </section>

  <section class="organization_detail_section">
    <div class="location_card">
      <h2>Find Us & Reach Out</h2>
      <p></p>

      <div class="contact_map_container">
        <!-- Google Map -->
        <div class="location_map">
          <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3966.790071888161!2d106.9677595697861!3d-6.158864246696813!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2e698b4f86ba93ef%3A0xcf58e590de21713a!2sUniversitas%20Esa%20Unggul%20Kampus%20Bekasi!5e0!3m2!1sen!2sid!4v1743053543870!5m2!1sen!2sid"
            allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>

        <!-- Contact Form -->
        <div class="contact_form">
          <h3>Contact Us</h3>
          <form action="/aboutus/sendmail" method="post">
@csrf
            <label for="nama">Full Name</label>
            <input type="text" id="name" name="name" placeholder="Name">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="Email">
            <label for="subject">Subject</label>
            <input type="text" id="subject" name="subject" placeholder="Subject">
            <label for="message">Message</label>
            <textarea name="message" id="message" placeholder="Message"></textarea>
            <button type="submit">Send</button>
          </form>
        </div>
      </div>
    </div>
  </section>

  <section class="logo_wrapper">
    <div class="logo_card">
      <h2>Our Satisfied Partners</h2>
      <div class="owl-carousel logo_active">
        @foreach ($sponsors as $sponsor)
          <img class="img-fluid" src="/assets/uploads/{{ $sponsor['image'] }}" alt="logo">
        @endforeach
      </div>
    </div>
  </section>

  @include('layouts.footer')

  <script src='https://code.jquery.com/jquery-1.12.0.min.js'></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/assets/js/owl.carousel.min.js"></script>
  <script src="/assets/js/index.js"></script>
  <script src="/assets/js/searchpodcast.js"></script>
</body>

</html>