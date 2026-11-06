# Design System — Spin The Radio

Pedoman tampilan aplikasi internal Spin The Radio, dirangkum dari UI yang sudah ada. Dokumen ini mencatat apa yang **sudah dipakai**; halaman baru mengikuti pola di sini supaya terasa satu aplikasi.

- **Pengguna:** HR, announcer, dan SO (sound operator) Spin The Radio.
- **Tugas utama UI:** mengatur program siaran dan sesinya, check-in crewcall, serta merekap absensi.
- **Karakter:** alat kerja yang tenang dan padat data. Putih dan abu-abu dingin sebagai dasar, oranye dari logo hanya sebagai penanda (aksi utama, posisi aktif, hari ini, fokus).

Sumber kebenaran token: `resources/css/app.css` (blok `@theme`, Tailwind v4).

---

## 1. Prinsip

1. **Oranye itu sinyal, bukan dekorasi.** Oranye hanya muncul untuk: tombol aksi utama, penanda menu aktif, penanda "hari ini", tab aktif, ring fokus, dan tulisan pengingat. Tidak dipakai untuk latar besar, ikon hias, atau gradien.
2. **Data lebih dulu.** Hampir setiap halaman adalah judul + tabel. Tabel memakai lebar penuh area konten, teks `text-sm`, dan angka/jam rata dengan `tabular-nums`.
3. **Hirarki lewat warna teks, bukan kotak.** Info sekunder memakai `text-muted`, bukan kartu atau badge tambahan. Satu permukaan putih (`bg-white` + `border-line`) per blok konten.
4. **Status ditulis, bukan diikonkan.** Status absensi dan sesi tampil sebagai kata ("Hadir", "Ditunda"); warna hanya menguatkan yang perlu perhatian (merah untuk "Tidak hadir" dan "Batal").
5. **Tanpa animasi.** Tidak ada transisi atau animasi masuk. Perubahan hanya lewat state hover/fokus dan elemen native (`<details>`, `popover`).
6. **Aksesibel secara default.** Fokus keyboard selalu terlihat, target sentuh 44px di mobile, label untuk setiap isian, dan teks khusus pembaca layar untuk tanda visual.

---

## 2. Warna

### Token dasar

| Token | Hex | Kelas Tailwind | Dipakai untuk |
|---|---|---|---|
| `canvas` | `#f4f5f7` | `bg-canvas` | Latar halaman, hover baris/menu (`hover:bg-canvas`, `hover:bg-canvas/60`), latar header grup tanggal (`bg-canvas/70`), latar avatar inisial |
| `white` | `#ffffff` | `bg-white` | Semua permukaan konten: sidebar, header mobile, tabel, form, alert |
| `ink` | `#1d232b` | `text-ink`, `hover:bg-ink` | Teks utama; warna hover tombol utama |
| `muted` | `#5f6875` | `text-muted` | Teks sekunder, header kolom tabel, deskripsi halaman, link sekunder, empty state |
| `line` | `#e2e5e9` | `border-line`, `divide-line`, `ring-line` | Semua garis: border permukaan, pemisah baris, garis tab |
| `signal` | `#ef7d22` | `bg-signal`, `outline-signal`, `border-signal`, `ring-signal/30` | **Tanda grafis saja**: bar menu aktif, bar "hari ini", ring/outline fokus, border isian saat fokus |
| `signal-strong` | `#b4540c` | `bg-signal-strong`, `text-signal-strong`, `border-signal-strong`, `accent-signal-strong` | Tombol utama, link aksi, garis tab aktif, label "Hari ini", tulisan pengingat, checkbox |
| `danger` | `#b42318` | `text-danger`, `border-danger/40`, `bg-danger` | Error validasi, alert error, aksi hapus/batalkan, status "Tidak hadir" dan "Batal" |
| — | emerald-600 | `bg-emerald-600` | Hanya titik di pesan sukses (`auth.partials.status`) |

### Aturan kontras

- `signal` (`#ef7d22`) kontrasnya ±2,8:1 di atas putih → **jangan dipakai untuk teks**. Untuk teks oranye dan latar tombol bertulisan putih, pakai `signal-strong` (±5:1).
- `muted` di atas putih ±5,6:1 dan di atas `canvas` ±5,2:1, aman untuk teks kecil.
- Teks yang dinonaktifkan memakai `text-muted/60` (contoh: menu "Pengaturan" yang belum tersedia), selalu disertai `aria-disabled="true"`.

### Warna status absensi di Excel

Ekspor .xlsx (`App\Support\AttendanceSpreadsheet`) memakai warna sel (latar / tulisan). Di halaman web, status **tidak** diberi latar; lihat bagian 6.

| Status | Latar | Tulisan |
|---|---|---|
| Hadir | `#DCFCE7` | `#166534` |
| Terlambat | `#FEF3C7` | `#92400E` |
| Izin, Sakit | `#DBEAFE` | `#1E40AF` |
| Tidak hadir | `#FEE2E2` | `#B42318`, tebal |
| Belum | `#F4F5F7` | `#5F6875` |
| Header tabel | `#E2E5E9` | tebal |

---

## 3. Tipografi

Satu keluarga huruf: **Instrument Sans** (`--font-sans`), fallback ke `ui-sans-serif, system-ui`. Body selalu `font-sans antialiased`.

| Peran | Kelas | Contoh |
|---|---|---|
| Judul halaman (h1) | `text-2xl font-semibold tracking-tight` | "Program", "Absensi Siaran Pagi" |
| Judul bagian (h2) | `text-lg font-semibold tracking-tight` | "Sesi mendatang", "Ganti password" |
| Judul blok dalam kartu | `text-sm font-semibold` | "Perlu dijadwalkan ulang" |
| Judul login | `text-xl font-semibold tracking-tight` | "Masuk" |
| Teks isi, tabel, form, tombol | `text-sm` | — |
| Label isian | `text-sm font-medium` | — |
| Penanda kecil | `text-xs font-medium` | "Hari ini" |

Aturan:

- **Berat huruf hanya tiga:** normal (isi), `font-medium` (label, nama di kolom pertama, tombol, item aktif), `font-semibold` (judul).
- **`tabular-nums`** untuk semua tanggal, jam, rentang jam, dan angka rekap supaya kolom rata.
- **Tanpa huruf kapital semua** untuk label atau header kolom. Header kolom tabel ditulis biasa ("Crewcall", "Tapping").
- Deskripsi di bawah judul dibatasi `max-w-3xl` agar baris tidak terlalu panjang.

---

## 4. Layout

### Shell aplikasi (setelah login) — `auth/layout.blade.php`

Setiap area (HR, announcer, SO) punya layout sendiri yang hanya mengisi `@section('nav')`; kerangkanya dipakai bersama.

```
Desktop (≥ md)                         Mobile (< md)
┌──────────┬──────────────────────┐    ┌───────────────────────────┐
│ [logo]   │                      │    │ [logo]            Menu ☰  │ ← sticky, bg-white
│          │  Judul halaman       │    ├───────────────────────────┤
│ ▌Program │  deskripsi    [Aksi] │    │ Judul halaman             │
│  Sesi    │                      │    │ deskripsi                 │
│  Absensi │  ┌────────────────┐  │    │ [Aksi]                    │
│          │  │ tabel          │  │    │ ┌───────────────────────┐ │
│          │  └────────────────┘  │    │ │ tabel (scroll →)      │ │
│──────────│                      │    │ └───────────────────────┘ │
│ (o) nama │   max-w-5xl, rata    │    └───────────────────────────┘
│ Keluar   │   kiri di tengah     │
└──────────┴──────────────────────┘
  w-60, fixed, border-r
```

- **Sidebar desktop:** `md:w-60`, fixed, `bg-white`, `border-r border-line`. Logo `h-20` di atas, navigasi, lalu akun (`auth.partials.account`) di bawah dipisah `border-t`.
- **Header mobile:** sticky, `bg-white border-b`, logo `h-8`, tombol "Menu"/"Tutup" berbasis `<details>` (tanpa JS). Panel menu turun penuh lebar, berisi navigasi + akun.
- **Konten:** `px-4 py-6 md:ml-60 md:px-10 md:py-10`, dibungkus `mx-auto max-w-5xl`. Semua konten **rata kiri**.

### Halaman berdiri sendiri

Login (`guest/login.blade.php`) dan "belum tersedia" memakai satu kartu di tengah layar:

```
          bg-canvas, flex center
        ┌────────────────────┐
        │ [logo h-24]        │  max-w-sm
        │ Masuk              │  rounded-lg border-line bg-white
        │ deskripsi          │  p-6 md:p-8
        │ [isian]            │
        │ [isian]            │
        │ [  Masuk  ]        │  tombol penuh lebar
        └────────────────────┘
          Kembali ke beranda
```

### Lebar konten di dalam shell

| Jenis | Lebar |
|---|---|
| Tabel, daftar | penuh (`max-w-5xl` dari shell) |
| Form tambah/ubah (program, sesi) | `max-w-2xl`, `p-6 md:p-8` |
| Form profil/password | `max-w-md`, `p-5` |
| Teks deskripsi | `max-w-3xl` |

### Jarak

- Antar-isian form: `space-y-5`; label → isian `mb-1.5`; isian → pesan `mt-1.5`.
- Header halaman → konten: `mb-6`. Alert → konten: `mb-4`. Antar-bagian (h2): `mb-8` / `mt-8`.
- Grup isian sejajar (jam crewcall/tapping): `grid gap-5 sm:grid-cols-3`.

---

## 5. Komponen

Kelas di bawah adalah string yang sudah dipakai; salin apa adanya.

### Header halaman

```blade
{{-- Opsional: link kembali ke halaman induk --}}
<a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Sesi</a>

<div class="mt-1 mb-6 flex flex-wrap items-end justify-between gap-4">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight">Judul</h1>
        <p class="mt-1 text-sm text-muted">Deskripsi singkat.</p>
    </div>
    {{-- aksi utama halaman di kanan --}}
</div>
```

Link kembali berisi **nama halaman induk** ("Sesi", "Program"), bukan "Kembali".

### Tombol

| Varian | Kelas | Contoh |
|---|---|---|
| Utama | `rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink` + fokus | "Tambah program", "Simpan sesi", "Check-in" |
| Utama kecil (di dalam tabel) | `px-3 py-1.5`, sisanya sama | "Simpan" di baris absensi |
| Sekunder | `rounded-md border border-line bg-white px-4 py-2 text-sm font-medium text-ink hover:border-ink` + fokus | "Terapkan" di filter |
| Link aksi | `font-medium text-signal-strong underline underline-offset-4` | "Ekspor Excel", "Tambah program pertama" |
| Link netral | `text-muted underline-offset-4 hover:text-ink hover:underline` | "Batal", "Keluar" |
| Link bahaya | `text-danger underline-offset-4 hover:underline` + fokus `outline-danger` | "Hapus", "Batalkan" |

- Kelas fokus standar: `focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal`.
- Satu tombol utama per form/halaman. Pasangan form: tombol utama + link "Batal" di kanannya, dalam `mt-8 flex items-center gap-5 border-t border-line pt-5`.
- Tidak ada tombol merah berlatar; aksi merusak selalu berupa link/menu bahaya + `confirm()`.

### Isian form

```blade
@php($fieldClass = 'w-full rounded-md border border-line bg-white px-3 py-2 text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')

<div>
    <label for="name" class="mb-1.5 block text-sm font-medium">Nama program</label>
    <input type="text" id="name" name="name" class="{{ $fieldClass }}"
           @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
    @error('name')
        <p id="name-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
    @enderror
</div>
```

- Isian filter (di atas tabel) memakai varian tanpa `w-full` dan dengan `text-sm`; isian jam/tanggal tambah `tabular-nums`.
- Isian di dalam baris tabel memakai padding kecil `px-2 py-1.5 text-sm` dan label `sr-only`.
- Teks bantuan: `mt-1.5 text-sm text-muted`, ditautkan lewat `aria-describedby`.
- **Tulisan pengingat** (nilai beda dari program): `mt-1.5 text-sm text-signal-strong`, `hidden` + `aria-live="polite"`, diatur `resources/js/app.js` lewat atribut `data-program-*`.
- **Daftar checkbox** (pilih kru): `<fieldset>` + `<legend class="mb-1.5 block text-sm font-medium">`, kotak `max-h-48 space-y-1 overflow-y-auto rounded-md border border-line p-3`, tiap item `flex items-center gap-2 text-sm`, checkbox `size-4 rounded border-line accent-signal-strong`. Keterangan opsional di legend: `<span class="font-normal text-muted">`.

### Pesan

| Jenis | Kelas | Isi |
|---|---|---|
| Sukses (flash `status`) | `@include('auth.partials.status')` | Titik `bg-emerald-600` + teks, kartu putih `border-line` |
| Error di halaman | `role="alert" class="mb-4 rounded-md border border-danger/40 bg-white px-4 py-3 text-sm text-danger"` | Titik `size-2 rounded-full bg-danger` di depan tiap pesan |
| Error login | `border-danger/30 bg-danger/5` | Satu pesan pertama saja |
| Info netral | `mb-4 text-sm text-muted` (teks polos) | "Status absensi hanya bisa diubah untuk sesi terjadwal." |

### Tabel

```blade
<div class="overflow-x-auto rounded-lg border border-line bg-white">
    <table class="w-full text-left text-sm">
        <thead class="border-b border-line text-muted">
            <tr><th scope="col" class="px-5 py-3 font-medium">Program</th> …</tr>
        </thead>
        <tbody class="divide-y divide-line">
            <tr class="hover:bg-canvas/60">
                <td class="px-5 py-3 font-medium">Nama</td>
                <td class="px-5 py-3 tabular-nums">07:00</td>
            </tr>
        </tbody>
    </table>
</div>
```

- **Kolom pertama** (nama program/orang) `font-medium`; kolom keterangan `text-muted`.
- **Kepadatan:** `px-5` untuk tabel biasa, `px-4` untuk tabel sesi yang punya kolom aksi menu, `px-3` untuk kolom matriks absensi.
- **Angka rekap** rata kanan: `text-right tabular-nums` di `th` dan `td`.
- **Kolom aksi** tanpa judul terlihat: `<span class="sr-only">Aksi</span>`.
- **Empty state:** satu baris `colspan` penuh, `px-5 py-12 text-center text-muted`, kalimat "Belum ada …". Bila ada aksi lanjut, tambah link aksi di bawahnya ("Tambah program pertama").
- **Matriks lebar** (rekap per orang/per program): wrapper tambah `relative`; kolom nama `sticky left-0 bg-white` dengan `scope="row"`; kolom total dipisah `border-l border-line`.
- **Grup per tanggal** (daftar sesi): satu `<tbody>` per tanggal dengan baris judul `scope="rowgroup"` berkelas `bg-canvas/70 px-4 py-2 font-semibold tabular-nums`.
- **Penanda hari ini:** bar kiri `before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-signal` di sel tanggal (`relative`), ditambah `<span class="ml-2 text-xs font-medium text-signal-strong">Hari ini</span>`.
- Paginasi: `{{ $items->links() }}` di dalam `<div class="mt-4">`.

### Kartu daftar

Untuk daftar pendek yang bukan tabel (contoh: sesi yang perlu dijadwalkan ulang):

```blade
<section aria-labelledby="x-heading" class="mb-6 rounded-lg border border-line bg-white">
    <h2 id="x-heading" class="border-b border-line px-5 py-3 text-sm font-semibold">Judul</h2>
    <ul class="divide-y divide-line text-sm">
        <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">…</li>
    </ul>
</section>
```

### Tab

Dipakai untuk berganti tampilan data yang sama ("Mendatang / Riwayat", "Per program / Per orang"). Tab adalah link (`<a>` + `aria-current="page"`), bukan JS.

```blade
@php($tabClass = 'border-b-2 px-1 pb-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal')
<nav aria-label="…" class="mb-4 flex gap-6 border-b border-line">
    <a aria-current="page" class="{{ $tabClass }} border-signal-strong text-ink">Aktif</a>
    <a class="{{ $tabClass }} border-transparent text-muted hover:text-ink">Lainnya</a>
</nav>
```

### Menu aksi baris

Bila satu baris punya lebih dari dua aksi, aksinya dikumpulkan di tombol titik tiga yang membuka `popover` native.

- Tombol: `inline-flex size-9 items-center justify-center rounded-md text-muted hover:bg-canvas hover:text-ink` + fokus, dengan `aria-label` yang menyebut baris ("Aksi sesi Siaran Pagi, 03 Nov 2026").
- Panel: `popover data-anchored-popover class="m-0 inset-auto min-w-36 rounded-md border border-line bg-white py-1 text-sm text-ink shadow-lg"`. Posisi dihitung `resources/js/app.js` (rata kanan tombol, membuka ke atas bila tombol di 40% bawah layar) dan ditutup saat scroll/resize.
- Item: `block w-full px-3 py-2 text-left hover:bg-canvas focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-signal`.
- Aksi merusak diletakkan paling bawah, dipisah `<div class="my-1 border-t border-line">`, item bahaya tambah `text-danger`.
- Ini satu-satunya tempat `shadow-lg` dipakai.

### Navigasi samping — `auth.partials.nav-link`

```blade
@include('auth.partials.nav-link', ['label' => 'Sesi', 'route' => 'sesi.index', 'active' => 'sesi.*'])
```

- Aktif: `bg-canvas font-medium text-ink` + bar oranye 4px di tepi kiri (`before:bg-signal`) + `aria-current="page"`.
- Tidak aktif: `text-muted hover:bg-canvas hover:text-ink`.
- `route => null` menandai menu belum tersedia: teks `text-muted/60`, `aria-disabled`, tooltip "Belum tersedia", dan teks "belum tersedia" di mobile.

### Avatar — `auth.partials.avatar`

Foto bulat `rounded-full object-cover`; tanpa foto, inisial huruf besar di lingkaran `bg-canvas text-muted ring-1 ring-line`. Ukuran lewat param `size` (`size-8` di akun, `size-20` di profil).

### Logo

`public/images/logo.png` (oranye + biru, latar putih). Selalu di atas permukaan putih, `w-auto`, tinggi: `h-8` header mobile, `h-20` sidebar dan halaman "belum tersedia", `h-24` login. `alt="Spin The Radio"`.

---

## 6. Status

### Status absensi (`App\Enums\AttendanceStatus`)

Di web, status ditampilkan sebagai teks label tanpa latar:

| Status | Tampilan |
|---|---|
| Hadir, Terlambat, Izin, Sakit | teks biasa (`text-ink`) |
| Tidak hadir | `font-medium text-danger` |
| Belum | `text-muted` |
| Bukan kru sesi itu | `–` berwarna `text-muted` + `sr-only` "Bukan kru sesi ini" |

Jam check-in ditulis setelah status: `<span class="ml-1 text-muted tabular-nums">07:02</span>`. Total "Tidak hadir" di rekap yang lebih dari nol juga `font-medium text-danger`. Sel status matriks memakai partial `auth.hr.absensi._status-cell`.

### Status sesi (`App\Enums\SessionStatus`)

| Status | Tampilan |
|---|---|
| Terjadwal | `text-muted` |
| Ditunda | `text-muted`; kolom tanggal/jam diganti "Menunggu jadwal baru" |
| Batal | `text-danger` |

---

## 7. Interaksi dan aksesibilitas

- **Fokus:** setiap elemen interaktif memakai `focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal` (bahaya: `outline-danger`). Isian memakai `focus:border-signal focus:ring-2 focus:ring-signal/30`.
- **Target sentuh:** link navigasi, tombol menu mobile, dan tombol "Keluar" `min-h-11` di mobile (`md:min-h-0` di desktop).
- **Konfirmasi:** aksi merusak (hapus program, tunda, batalkan, ubah sesi yang sudah ada check-in) memakai `onsubmit="return confirm(@js('…'))"`. Kalimatnya menyebut objek dan akibatnya, diakhiri pertanyaan.
- **Tanpa JS bila bisa:** menu mobile `<details>`, menu aksi `popover`, tab berupa link, filter berupa form GET. JS (`resources/js/app.js`) hanya untuk posisi popover dan tulisan pengingat.
- **Semantik tabel:** `scope="col"`, `scope="row"`, `scope="rowgroup"`; label `sr-only` untuk isian di dalam baris.
- **Tanda visual punya padanan teks:** bar "hari ini" disertai teks "Hari ini"; tanda `–` disertai `sr-only`; ikon SVG selalu `aria-hidden="true"`.
- **Gerak:** tidak ada animasi atau transisi.

---

## 8. Penulisan

- **Bahasa Indonesia**, kalimat biasa (huruf besar hanya di awal), sapaan "-mu" bila perlu ("Fitur untuk jabatanmu belum tersedia").
- **Istilah siaran dipertahankan:** crewcall, tapping, check-in, announcer, SO.
- **Tombol = kata kerja + objek bila perlu:** "Tambah program", "Simpan sesi", "Simpan profil", "Ganti password", "Ekspor Excel", "Terapkan", "Check-in", "Keluar". Aksi di menu cukup satu kata: "Ubah", "Absensi", "Tunda", "Batalkan", "Hapus". Pembatal form: "Batal".
- **Empty state:** "Belum ada …" ("Belum ada program.", "Belum ada riwayat sesi.").
- **Format tanggal:** locale `id`, `D, d M Y` di tabel ("Sen, 03 Nov 2026"), `l, d M Y` untuk judul grup ("Senin, 03 Nov 2026"), `d/m/Y` di teks konfirmasi.
- **Format jam:** `HH:MM` (24 jam). Rentang memakai en dash tanpa spasi: `07:00–09:00`.
- **Judul tab browser:** `@section('title', 'Nama Halaman')` → "Nama Halaman - Spin The Radio".

---

## 9. Kondisi saat ini

Fakta yang perlu diketahui saat membuat halaman baru:

- **Instrument Sans belum dimuat.** Font hanya disebut di `--font-sans`; tidak ada `<link>` font atau file font di proyek, jadi browser menampilkan fallback `system-ui` kecuali font terpasang di perangkat.
- **Dashboard HR dan announcer masih placeholder** (`text-gray-500`, judul tanpa `tracking-tight`), belum mengikuti token di dokumen ini.
- **Beranda publik (`main.blade.php`) masih placeholder**, belum punya tampilan.
- **Menu "Pengaturan" (HR)** sudah terdaftar tapi belum tersedia.
- **Istilah kata sandi belum seragam:** login memakai "Kata sandi", profil memakai "password".
