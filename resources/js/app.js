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

// Tulisan pengingat di form sesi: muncul kalau isian (hari siaran, jam, kru) beda dari program.
// Teks tulisan tanggal/jam ada di atribut data-program-hint-text; untuk kru teksnya disusun di sini. Tanpa atribut data-program-*, blok ini tidak berbuat apa-apa.
const programHintOf = (field) => document.getElementById(`${field.id}-program`);

// Teks tulisan diisi saat ditampilkan dan dikosongkan saat disembunyikan, supaya pembaca layar mengumumkannya (live region).
const setHintVisible = (hint, visible) => {
    hint.textContent = visible ? hint.dataset.programHintText : '';
    hint.hidden = !visible;
};

const crewNamesOf = (ids, checkboxes) => ids
    .map((id) => checkboxes.find((checkbox) => Number(checkbox.value) === id))
    .filter((checkbox) => checkbox !== undefined)
    .map((checkbox) => checkbox.closest('label').textContent.trim());

const refreshProgramHint = (field) => {
    if (field.matches('fieldset[data-program-crew]')) {
        const hint = field.querySelector('[data-program-hint]');
        const programIds = JSON.parse(field.dataset.programCrew).map(Number);
        const checkboxes = [...field.querySelectorAll('input[type="checkbox"]')];
        const checkedIds = checkboxes.filter((checkbox) => checkbox.checked).map((checkbox) => Number(checkbox.value));
        const removed = crewNamesOf(programIds.filter((id) => !checkedIds.includes(id)), checkboxes);
        const added = crewNamesOf(checkedIds.filter((id) => !programIds.includes(id)), checkboxes);
        const sentences = [];

        if (removed.length > 0) {
            sentences.push(`Dilepas dari kru program: ${removed.join(', ')}.`);
        }

        if (added.length > 0) {
            sentences.push(`Bukan kru program: ${added.join(', ')}.`);
        }

        hint.textContent = sentences.join(' ');
        hint.hidden = sentences.length === 0;

        return;
    }

    const hint = programHintOf(field);

    if (hint === null) {
        return;
    }

    if (field.dataset.programWeekday !== undefined) {
        const weekday = new Date(`${field.value}T00:00`).getDay() || 7;
        setHintVisible(hint, field.value !== '' && weekday !== Number(field.dataset.programWeekday));

        return;
    }

    setHintVisible(hint, field.value !== '' && field.value !== field.dataset.programValue);
};

const programFieldSelector = '[data-program-value], [data-program-weekday], fieldset[data-program-crew]';

document.querySelectorAll(programFieldSelector).forEach(refreshProgramHint);

['input', 'change'].forEach((eventName) => {
    document.addEventListener(eventName, (event) => {
        const field = event.target.closest?.(programFieldSelector);

        if (field) {
            refreshProgramHint(field);
        }
    });
});
