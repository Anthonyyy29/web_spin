<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

class ProgramPodcastSeeder extends Seeder
{
    /**
     * Isi program podcast ke tabel contents. Gambar sudah ada di
     * public/assets/uploads.
     */
    public function run(): void
    {
        $program = [
            [
                'title' => 'CAKHI',
                'description' => "Tempat ngobrol santai yang penuh cerita, mulai dari pengalaman hidup, kenangan masa lalu, sampai obrolan random yang nggak ada habisnya. CAKHI hadir jadi teman berbagi cerita dan tempat buat kamu ngobrol tanpa harus banyak basa-basi.\n\nYuk, dengerin obrolan serunya di Spotify Spin The Radio FM! 🎙️✨",
                'image' => 'cakhi.png',
            ],
            [
                'title' => 'Bisik Bernada',
                'description' => "Bisik Bernada hadir lagi dengan cerita-cerita menarik di balik lagu favoritmu! 🎶🤫\n\nKarena setiap lagu punya kisah yang nggak selalu terdengar dari melodinya. Mulai dari arti lirik, cerita di balik proses pembuatannya, hingga alasan sebuah lagu bisa begitu melekat di telinga. Semuanya bakal kita bahas dengan santai dan pastinya bikin kamu makin menikmati setiap nada.\n\nPenasaran sama cerita di balik lagu berikutnya? Stay tuned and catch the whispers! 😉\n\nDengarkan sekarang, exclusively on Spotify Spin The Radio FM! 🎧",
                'image' => 'bisik-bernada.png',
            ],
            [
                'title' => 'Attenthings',
                'description' => "Attenthings is back to bring more love stories and butterflies to your day! 💖🦋\n\nMulai dari cerita tentang gebetan, momen manis bareng pasangan, pengalaman flirting yang berujung gagal, sampai kisah cinta yang bikin senyum-senyum sendiri. Semua hal tentang cinta bakal jadi bahan obrolan seru di sini!\n\nYuk, gabung di obrolan penuh warna tentang percintaan yang siap bikin harimu makin berbunga-bunga.\n\nJangan sampai ketinggalan! Dengerin Attenthings hanya di Spotify Spin The Radio FM! 🎧✨",
                'image' => 'attenthings.png',
            ],
            [
                'title' => 'Movie Hunter',
                'description' => "From the latest releases to hidden cinematic gems, everything is worth exploring with Movie Hunter! 🎬🍿\n\nBingung mau nonton apa? Atau lagi cari film dengan alur cerita yang nggak ketebak? Movie Hunter siap nemenin kamu membahas berbagai film, mulai dari jalan cerita, kesan dan suasana film, hingga review dan rating yang pastinya bisa jadi bahan pertimbangan sebelum nonton.\n\nBuat kamu para movie enthusiast yang selalu haus rekomendasi tontonan, this is the place to be!\n\nTemukan tontonan favorit berikutnya bareng Movie Hunter di Spotify Spin The Radio FM. Happy hunting! 🎥✨",
                'image' => 'movie-hunter.png',
            ],
            [
                'title' => 'K-TIME',
                'description' => "Annyeong, chingudeul! 🇰🇷👋🏻 K-TIME hadir lagi buat ngajak kamu mengenal lebih dekat serunya kehidupan di Korea Selatan!\n\nMulai dari drama Korea yang lagi ramai dibicarakan, dunia K-Pop, jajanan street food yang menggugah selera, sampai budaya unik yang menarik untuk dikulik. Semuanya bakal kita bahas dengan santai dan pastinya menambah wawasan kamu tentang Korea!\n\nYuk, luangkan waktumu dan ikut menjelajahi keseruan Korea bareng K-TIME di Spotify Spin The Radio FM!\n\nSampai jumpa, chingudeul! Jangan sampai ketinggalan, ya! 💗🎧",
                'image' => 'k-time.png',
            ],
            [
                'title' => 'ERROR (ERA HORROR)',
                'description' => "Pernah nggak sih, dengar suara aneh yang nggak tahu asalnya dari mana? Atau merasa ada sesuatu yang mengawasimu saat sedang sendirian? 👀\n\nERROR hadir buat menemani kamu menyelami berbagai kisah misteri, mulai dari pengalaman horor yang dialami langsung, urban legend yang bikin penasaran, hingga kejadian-kejadian di luar nalar. Setiap cerita punya misterinya sendiri, dan siap-siap dibuat merinding saat mendengarkannya!\n\nBuat kamu yang suka hal-hal berbau mistis dan penuh teka-teki, jangan sampai kelewatan! ERA HORROR bakal hadir eksklusif di Spotify Spin The Radio FM. 🎧👻\n\nSelamat datang di ERA HORROR. Ingat, jangan dengarkan sendirian! ‼️",
                'image' => 'error.png',
            ],
            [
                'title' => 'The Untold Stories',
                'description' => "Ada banyak cerita yang tersimpan di balik hari-hari yang kita jalani. Mulai dari hal sederhana, perasaan yang sulit dijelaskan, sampai pengalaman yang mungkin belum pernah kita bagikan kepada siapa pun. The Untold Stories hadir untuk menemani setiap cerita itu. 🫶🏻\n\nLewat obrolan yang santai dan penuh makna, kita akan berbagi sudut pandang, membahas kehidupan, dan mendengarkan kisah yang mungkin terasa dekat dengan diri kita sendiri.\n\nTemukan obrolan hangat bersama The Untold di Spotify Spin The Radio FM! 🎧🤍",
                'image' => 'the-untold-stories.jpg',
            ],
            [
                'title' => 'SPINVERSE',
                'description' => 'SPINVERSE',
                'image' => 'spinverse.png',
            ],
        ];

        foreach ($program as $data) {
            Content::updateOrCreate(['title' => $data['title']], $data);
        }
    }
}
