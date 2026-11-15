$(document).ready(function () {
    $("#crew-slider").owlCarousel({
        items: 4,
        itemsDesktop: [1199, 3],
        itemsDesktopSmall: [980, 2],
        itemsMobile: [600, 1],
        navigation: true,
        navigationText: ["", ""],
        pagination: true,
        autoPlay: true
    });
});

document.addEventListener("DOMContentLoaded", function () {
    const viewMoreBtn = document.getElementById("view-more");
    const hideBtn = document.getElementById("hide-images");
    const podcastGallery = document.getElementById("podcast-gallery");
    
    // Menampilkan lebih banyak podcast
    viewMoreBtn.addEventListener("click", function () {
        let morePodcasts = `
            <div class="col-md-3 mb-3 hidden-podcast">
                <a href="https://example.com/podcast5" target="_blank">
                    <img src="assets/img/k-time.jpg" class="img-fluid rounded-4" alt="Podcast 5">
                </a>
            </div>
            <div class="col-md-3 mb-3 hidden-podcast">
                <a href="https://example.com/podcast6" target="_blank">
                    <img src="assets/img/movie-hunter.png" class="img-fluid rounded-4" alt="Podcast 5">
                </a>
            </div>
            <div class="col-md-3 mb-3 hidden-podcast">
                <a href="https://example.com/podcast7" target="_blank">
                    <img src="assets/img/the-untold-stories.png" class="img-fluid rounded-4" alt="Podcast 5">
                </a>
            </div>
        `;

        podcastGallery.insertAdjacentHTML("beforeend", morePodcasts);

        // Tambahkan efek blur ke podcast yang baru ditambahkan
        document.querySelectorAll(".hidden-podcast").forEach(img => img.classList.add("show-podcast"));

        viewMoreBtn.classList.add("d-none"); // Sembunyikan tombol View More
        hideBtn.classList.remove("d-none"); // Tampilkan tombol Hide
    });
});

function logo_carouselInit() {
    $('.owl-carousel.logo_active').owlCarousel({
        dots: false,
        loop: true,
        margin: 30,
        stagePadding: 2,
        smartSpeed: 1000,
        autoplay: true,
        autoplayTimeout: 1500,
        autoplayHoverPause: true,
        responsive: {
            0: {
                items: 2
            },
            576: {
                items: 3,
            },
            768: {
                items: 4,
            },
            992: {
                items: 5
            }
        }
    });
}
logo_carouselInit();


document.addEventListener("DOMContentLoaded", function() {
    document.querySelectorAll(".podcast-card").forEach(episode => {
        let description = episode.querySelector("p"); // Ambil elemen paragraf dalam podcast-card
        let button = episode.querySelector(".read-more");
        let fullText = description.textContent.trim();
        let maxLength = 200;

        if (fullText.length > maxLength) {
            let truncatedText = fullText.substring(0, maxLength) + "...";
            description.textContent = truncatedText;
            button.style.display = "block";

            button.addEventListener("click", function() {
                if (description.textContent === truncatedText) {
                    description.textContent = fullText;
                    button.textContent = "Tampilkan Lebih Sedikit";
                } else {
                    description.textContent = truncatedText;
                    button.textContent = "Lihat Selengkapnya";
                }
            });
        } else {
            button.style.display = "none";
        }
    });
});


const playButton = document.getElementById("playButton");
const audio = document.getElementById("radioAudio");
const playIcon = document.getElementById("playIcon");
const radioHint = document.getElementById("radioHint");

let isPlaying = false;

playButton.addEventListener("click", function (e) {
  e.preventDefault();

  // Hide the floating hint on first click
  if (!isPlaying && radioHint) {
    radioHint.style.display = "none";
  }

  if (!isPlaying) {
    audio.play();
    playButton.classList.add("expanded");
    playIcon.classList.remove("fa-play");
    playIcon.classList.add("fa-pause");
  } else {
    audio.pause();
    playButton.classList.remove("expanded");
    playIcon.classList.remove("fa-pause");
    playIcon.classList.add("fa-play");
  }

  isPlaying = !isPlaying;
});
