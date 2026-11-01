@extends('auth.hr.layout')

@section('title', 'Tambah Sesi')

@section('content')
    <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Sesi</a>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">Tambah sesi</h1>
    <p class="mt-1 mb-6 max-w-2xl text-sm text-muted">Sesi tambahan di luar jadwal mingguan program, misalnya siaran khusus. Sesi ini tidak ikut berubah saat programnya diubah.</p>

    @if (! $session->program_id)
        <form method="GET" action="{{ route('sesi.create') }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8">
            <label for="program" class="mb-1.5 block text-sm font-medium">Pilih program</label>
            <p class="mb-3 text-sm text-muted">Jam dan kru akan diisi dari program ini, lalu bisa kamu ubah.</p>
            <div class="flex flex-wrap items-center gap-3">
                <select id="program" name="program" required class="rounded-md border border-line bg-white px-3 py-2 text-sm text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30">
                    <option value="">Pilih program</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}">{{ $program->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Lanjut</button>
            </div>
            @error('program_id')
                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </form>
    @else
        <form method="POST" action="{{ route('sesi.store') }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8">
            @csrf
            <input type="hidden" name="program_id" value="{{ $session->program_id }}">

            <div class="mb-5 flex flex-wrap items-baseline justify-between gap-3 border-b border-line pb-5">
                <p class="text-sm"><span class="text-muted">Program</span> <span class="ml-2 font-medium">{{ $programs->firstWhere('id', $session->program_id)?->name }}</span></p>
                <a href="{{ route('sesi.create') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Ganti program</a>
            </div>
            @error('program_id')
                <p role="alert" class="mb-5 text-sm text-danger">{{ $message }}</p>
            @enderror

            @include('auth.hr.sesi._fields')

            <div class="mt-8 flex items-center gap-5 border-t border-line pt-5">
                <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Simpan sesi</button>
                <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Batal</a>
            </div>
        </form>
    @endif
@endsection
