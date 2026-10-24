@extends('auth.hr.layout')

@section('title', 'Ubah Sesi')

@section('content')
    <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Sesi</a>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ $session->status === \App\Enums\SessionStatus::Postponed ? 'Jadwalkan ulang sesi' : 'Ubah sesi' }}</h1>
    <p class="mt-1 mb-6 text-sm text-muted">{{ $session->program->name }} <span class="tabular-nums">({{ $session->regular_date ? 'semula '.$session->regular_date->locale('id')->translatedFormat('D, d M Y') : 'sesi tambahan' }})</span></p>

    @if ($session->status === \App\Enums\SessionStatus::Cancelled)
        <p class="mb-6 max-w-2xl rounded-md border border-line bg-white px-4 py-3 text-sm text-muted">Menyimpan akan mengaktifkan kembali sesi ini.</p>
    @endif

    <form method="POST" action="{{ route('sesi.update', $session) }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8" @if ($hasCheckIns) onsubmit="return confirm(@js('Sesi ini sudah punya check-in. Jika tanggal atau jam diubah, semua absensi sesi ini akan dihapus. Lanjutkan?'))" @endif>
        @csrf
        @method('PUT')
        @include('auth.hr.sesi._fields')

        @if ($hasCheckIns)
            <p class="mt-6 text-sm text-danger">Sesi ini sudah punya check-in. Mengubah tanggal atau jam akan menghapus semua absensi sesi ini.</p>
        @endif

        <div class="mt-8 flex items-center gap-5 border-t border-line pt-5">
            <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Simpan sesi</button>
            <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Batal</a>
        </div>
    </form>
@endsection
