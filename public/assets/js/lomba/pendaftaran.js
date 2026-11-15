
function previewFile(inputElement, previewElementId) {
    const preview = document.getElementById(previewElementId);
    preview.innerHTML = '';
    const file = inputElement.files[0];
    if (!file) return;

    const fileType = file.type;
    if (fileType.startsWith('image/')) {
        const img = document.createElement('img');
        img.src = URL.createObjectURL(file);
        img.className = "img-fluid rounded border border-secondary";
        img.style.maxHeight = "180px";
        preview.appendChild(img);
    } else if (fileType === "application/pdf") {
        const embed = document.createElement('embed');
        embed.src = URL.createObjectURL(file);
        embed.type = "application/pdf";
        embed.width = "100%";
        embed.height = "200px";
        embed.className = "border border-secondary rounded";
        preview.appendChild(embed);
    } else {
        preview.textContent = "File tidak dapat dipreview.";
    }
}

document.getElementById('buktiPembayaran').addEventListener('change', function () {
    previewFile(this, 'previewBuktiPembayaran');
});
document.getElementById('kartuIdentitas').addEventListener('change', function () {
    previewFile(this, 'previewKartuIdentitas');
});

document.addEventListener("DOMContentLoaded", function () {
    const anggotaList = document.getElementById('anggota-list');
    const select = document.getElementById('pilihLomba');
    const fieldTim = document.getElementById('fieldTim');
    const fieldNonTim = document.getElementById('fieldNonTim');
    const timSelectedOptions = ['podcast', 'konten'];

    function toggleField() {
        const selectedValue = select.value;

        if (timSelectedOptions.includes(selectedValue)) {
            fieldTim.style.display = 'block';
            fieldNonTim.style.display = 'none';

            const pesInput = fieldNonTim.querySelector('input[name="nama_lengkap"]');
            if (pesInput) {
                pesInput.value = '';
                pesInput.removeAttribute('required');
            }

            const namaTimInput = fieldTim.querySelector('input[name="nama_tim"]');
            if (namaTimInput) namaTimInput.setAttribute('required', true);

            const anggotaInputs = anggotaList.querySelectorAll('input[name="anggota[]"]');
            anggotaInputs.forEach(input => input.setAttribute('required', true));

        } else if (selectedValue) {
            fieldTim.style.display = 'none';
            fieldNonTim.style.display = 'block';

            const namaTimInput = fieldTim.querySelector('input[name="nama_tim"]');
            if (namaTimInput) {
                namaTimInput.value = '';
                namaTimInput.removeAttribute('required');
            }

            anggotaList.querySelectorAll('.anggota-item').forEach(div => div.remove());
            document.querySelectorAll('.anggota-default input').forEach(input => {
                input.value = '';
                input.removeAttribute('required');
            });

            const pesInput = fieldNonTim.querySelector('input[name="nama_lengkap"]');
            if (pesInput) pesInput.setAttribute('required', true);

        } else {
            fieldTim.style.display = 'none';
            fieldNonTim.style.display = 'none';

            const pesInput = fieldNonTim.querySelector('input[name="nama_lengkap"]');
            if (pesInput) pesInput.removeAttribute('required');

            const namaTimInput = fieldTim.querySelector('input[name="nama_tim"]');
            if (namaTimInput) namaTimInput.removeAttribute('required');

            const anggotaInputs = anggotaList.querySelectorAll('input[name="anggota[]"]');
            anggotaInputs.forEach(input => input.removeAttribute('required'));
        }
    }

    select.addEventListener('change', toggleField);
    toggleField();
});

document.addEventListener('DOMContentLoaded', function () {
    const anggotaList = document.getElementById('anggota-list');
    const addAnggotaBtn = document.getElementById('addAnggota');
    if (!anggotaList || !addAnggotaBtn) return;

    const maxAnggota = 3;

    function updateAnggotaCount() {
        const currentCount = anggotaList.querySelectorAll('.anggota-item, .anggota-default').length;
        addAnggotaBtn.disabled = currentCount >= maxAnggota;
    }

    anggotaList.querySelectorAll('.remove-anggota').forEach(function (btn) {
        btn.addEventListener('click', function () {
            btn.closest('.anggota-item').remove();
        });
    });

    addAnggotaBtn.addEventListener('click', function () {
        const div = document.createElement('div');
        div.className = 'input-group mb-2 anggota-item';
        div.innerHTML = `
          <input type="text" class="form-control border-secondary bg-dark text-white" name="anggota[]" placeholder="Masukkan Nama Anggota" required>
          <button type="button" class="btn btn-outline-danger btn-sm ms-2 remove-anggota"><i class="bi bi-x"></i></button>
        `;
        anggotaList.appendChild(div);
        div.querySelector('.remove-anggota').addEventListener('click', function () {
            div.remove();
            updateAnggotaCount();
        });

        updateAnggotaCount();
    });

    updateAnggotaCount();
});


