document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initPodcastSearch();
    initCarousels();
    initRadioPlayer();
    initToasts();
    initReadMoreToggles();
});

/**
 * Toggle menu & pencarian mobile pada Navbar.
 */
function initMobileNav() {
    const toggleBtn = document.querySelector('[data-nav-toggle]');
    const searchBtn = document.querySelector('[data-nav-search-toggle]');
    const drawer = document.querySelector('[data-nav-drawer]');
    const iconOpen = document.querySelector('[data-nav-icon-open]');
    const iconClose = document.querySelector('[data-nav-icon-close]');

    if (!toggleBtn || !drawer) {
        return;
    }

    const setOpen = (isOpen) => {
        drawer.classList.toggle('is-hidden', !isOpen);
        toggleBtn.setAttribute('aria-expanded', String(isOpen));

        if (iconOpen && iconClose) {
            iconOpen.classList.toggle('is-hidden', isOpen);
            iconClose.classList.toggle('is-hidden', !isOpen);
        }

        if (searchBtn) {
            searchBtn.classList.toggle('is-hidden', isOpen);
        }
    };

    toggleBtn.addEventListener('click', () => {
        const willOpen = drawer.classList.contains('is-hidden');
        setOpen(willOpen);
    });

    if (searchBtn) {
        searchBtn.addEventListener('click', () => {
            setOpen(true);
            const mobileInput = drawer.querySelector('input[type="search"]');
            if (mobileInput) {
                mobileInput.focus();
            }
        });
    }
}

/**
 * Pencarian podcast dengan saran langsung dari /api/podcastlist.
 */
function initPodcastSearch() {
    const wrappers = document.querySelectorAll('[data-search-wrapper]');
    if (wrappers.length === 0) {
        return;
    }

    let cachedData = null;

    const loadPodcasts = async () => {
        if (cachedData !== null) {
            return cachedData;
        }

        try {
            const response = await fetch('/api/podcastlist', {
                headers: { Accept: 'application/json' },
            });
            if (!response.ok) {
                return {};
            }
            cachedData = await response.json();
            return cachedData;
        } catch {
            return {};
        }
    };

    wrappers.forEach((wrapper) => {
        const input = wrapper.querySelector('[data-search-input]');
        const box = wrapper.querySelector('[data-search-suggestions]');

        if (!input || !box) {
            return;
        }

        const renderSuggestions = async () => {
            const keyword = input.value.trim().toLowerCase();
            if (keyword === '') {
                box.classList.add('is-hidden');
                box.innerHTML = '';
                return;
            }

            const grouped = await loadPodcasts();
            box.innerHTML = '';
            let matchCount = 0;

            Object.entries(grouped).forEach(([segment, items]) => {
                if (!Array.isArray(items)) {
                    return;
                }

                const filtered = items.filter((item) =>
                    String(item.label || '').toLowerCase().includes(keyword),
                );

                if (filtered.length === 0) {
                    return;
                }

                const heading = document.createElement('div');
                heading.className = 'spin-search__group-label';
                heading.textContent = segment || 'PODCAST';
                box.appendChild(heading);

                filtered.slice(0, 5).forEach((item) => {
                    matchCount++;
                    const link = document.createElement('a');
                    link.href = '/' + String(item.url || '').replace(/^\/+/, '');
                    link.className = 'spin-search__item';
                    link.textContent = item.label || '';
                    box.appendChild(link);
                });
            });

            if (matchCount === 0) {
                const empty = document.createElement('div');
                empty.className = 'spin-search__empty';
                empty.textContent = 'Podcast tidak ditemukan.';
                box.appendChild(empty);
            }

            box.classList.remove('is-hidden');
        };

        input.addEventListener('input', renderSuggestions);
        input.addEventListener('focus', () => {
            if (input.value.trim() !== '') {
                renderSuggestions();
            }
        });

        document.addEventListener('click', (event) => {
            if (!wrapper.contains(event.target)) {
                box.classList.add('is-hidden');
            }
        });
    });
}

/**
 * Kontrol carousel horizontal dengan tombol panah dan indikator titik (Papan 5).
 */
function initCarousels() {
    document.querySelectorAll('[data-carousel]').forEach((root) => {
        const track = root.querySelector('[data-carousel-track]');
        const prevBtn = root.querySelector('[data-carousel-prev]');
        const nextBtn = root.querySelector('[data-carousel-next]');
        const dots = Array.from(root.querySelectorAll('[data-carousel-dot]'));

        if (!track) {
            return;
        }

        const step = () => Math.max(track.clientWidth * 0.75, 260);

        const updateDots = () => {
            if (dots.length === 0) {
                return;
            }

            const maxScroll = Math.max(track.scrollWidth - track.clientWidth, 1);
            const ratio = Math.min(Math.max(track.scrollLeft / maxScroll, 0), 1);
            const activeIndex = Math.round(ratio * (dots.length - 1));

            dots.forEach((dot, idx) => {
                dot.classList.toggle('is-active', idx === activeIndex);
            });
        };

        if (prevBtn) {
            prevBtn.addEventListener('click', () => {
                track.scrollBy({ left: -step(), behavior: 'smooth' });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => {
                track.scrollBy({ left: step(), behavior: 'smooth' });
            });
        }

        dots.forEach((dot, idx) => {
            dot.addEventListener('click', () => {
                const maxScroll = Math.max(track.scrollWidth - track.clientWidth, 0);
                const target = dots.length > 1 ? (idx / (dots.length - 1)) * maxScroll : 0;
                track.scrollTo({ left: target, behavior: 'smooth' });
            });
        });

        track.addEventListener('scroll', updateDots, { passive: true });
        updateDots();
    });
}

/**
 * Pemutar radio streaming langsung pada halaman Home.
 */
function initRadioPlayer() {
    const btn = document.getElementById('playButton');
    const audio = document.getElementById('radioAudio');
    const label = document.getElementById('radioLabel');
    const iconPlay = document.getElementById('playIcon');
    const iconPause = document.getElementById('pauseIcon');
    const wave = document.getElementById('waveAnim');
    const hint = document.getElementById('radioHint');

    if (!btn || !audio) {
        return;
    }

    const setPlaying = (playing) => {
        if (iconPlay) {
            iconPlay.classList.toggle('is-hidden', playing);
        }
        if (iconPause) {
            iconPause.classList.toggle('is-hidden', !playing);
        }
        if (wave) {
            wave.classList.toggle('is-hidden', !playing);
        }
        if (label) {
            label.textContent = playing ? 'On Air · Playing' : 'Dengarkan Live Radio';
        }
        if (hint) {
            hint.classList.toggle('is-hidden', playing);
        }
    };

    btn.addEventListener('click', (event) => {
        event.preventDefault();
        if (audio.paused) {
            audio.play().then(() => setPlaying(true)).catch(() => setPlaying(false));
        } else {
            audio.pause();
            setPlaying(false);
        }
    });

    audio.addEventListener('ended', () => setPlaying(false));
    audio.addEventListener('pause', () => setPlaying(false));
}

/**
 * Tutup otomatis notifikasi Toast (Papan 5).
 */
function initToasts() {
    document.querySelectorAll('[data-toast]').forEach((toast) => {
        const closeBtn = toast.querySelector('[data-toast-close]');
        const dismiss = () => {
            toast.classList.add('is-dismissed');
            setTimeout(() => toast.remove(), 250);
        };

        if (closeBtn) {
            closeBtn.addEventListener('click', dismiss);
        }

        setTimeout(dismiss, 4500);
    });
}

/**
 * Toggle deskripsi panjang pada kartu episode podcast.
 */
function initReadMoreToggles() {
    document.querySelectorAll('[data-read-more-btn]').forEach((btn) => {
        const target = btn.parentElement?.querySelector('[data-read-more-text]');
        if (!target) {
            return;
        }

        btn.addEventListener('click', () => {
            const clamped = target.classList.toggle('spin-clamp-3');
            btn.textContent = clamped ? 'Lihat Selengkapnya' : 'Sembunyikan';
        });
    });
}
