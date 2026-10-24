@extends('auth.hr.layout')

@section('title', 'Jadwal Siaran')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Jadwal Siaran</h1>
            <p class="mt-1 text-sm text-muted">Semua jadwal announcer, terbaru di atas.</p>
        </div>
        <a href="{{ route('jadwalSiaran.create') }}" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Tambah jadwal</a>
    </div>

    @include('auth.partials.status')

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Tanggal</th>
                    <th scope="col" class="px-5 py-3 font-medium">Jam</th>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Announcer</th>
                    <th scope="col" class="px-5 py-3 font-medium"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($jadwalSiaran as $jadwal)
                    <tr class="hover:bg-canvas/60">
                        @include('auth.partials.jadwal-cells', ['jadwal' => $jadwal])
                        <td class="px-5 py-3">{{ $jadwal->program_title }}</td>
                        <td class="px-5 py-3 text-muted">{{ $jadwal->announcer->username }}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('jadwalSiaran.edit', $jadwal) }}" class="font-medium text-ink underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ubah</a>
                                <form method="POST" action="{{ route('jadwalSiaran.destroy', $jadwal) }}" onsubmit="return confirm(@js('Hapus jadwal '.$jadwal->program_title.'?'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-12 text-center">
                            <p class="text-muted">Belum ada jadwal siaran.</p>
                            <a href="{{ route('jadwalSiaran.create') }}" class="mt-2 inline-block font-medium text-signal-strong underline underline-offset-4">Tambah jadwal pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $jadwalSiaran->links() }}
    </div>
@endsection
