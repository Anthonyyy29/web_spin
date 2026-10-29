// Popover [data-anchored-popover] ditempel ke tombol pemicunya: rata kanan dengan tombol,
// di bawahnya, atau di atasnya kalau tombol ada di bagian bawah layar.
document.addEventListener('beforetoggle', (event) => {
    const popover = event.target;

    if (event.newState !== 'open' || !popover.matches('[data-anchored-popover]')) {
        return;
    }

    const trigger = document.querySelector(`[popovertarget="${popover.id}"]`).getBoundingClientRect();
    const opensUpward = trigger.bottom > window.innerHeight * 0.6;

    popover.style.right = `${document.documentElement.clientWidth - trigger.right}px`;
    popover.style.top = opensUpward ? 'auto' : `${trigger.bottom + 4}px`;
    popover.style.bottom = opensUpward ? `${window.innerHeight - trigger.top + 4}px` : 'auto';
}, true);

// Posisi popover tetap (fixed), jadi tutup saat halaman atau tabel di-scroll dan saat ukuran jendela berubah.
const closeAnchoredPopovers = () => {
    document.querySelectorAll('[data-anchored-popover]:popover-open').forEach((popover) => popover.hidePopover());
};

window.addEventListener('scroll', closeAnchoredPopovers, { capture: true, passive: true });
window.addEventListener('resize', closeAnchoredPopovers);
