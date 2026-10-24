@extends('auth.announcer.layout')

@section('title', 'Jadwal Siaran')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Jadwal Siaran</h1>
        <p class="mt-1 text-sm text-muted">Jadwal siaran kamu, terbaru di atas.</p>
    </div>

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Tanggal</th>
                    <th scope="col" class="px-5 py-3 font-medium">Jam</th>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($jadwalSiaran as $jadwal)
                    <tr>
                        @include('auth.partials.jadwal-cells', ['jadwal' => $jadwal])
                        <td class="px-5 py-3">{{ $jadwal->program_title }}</td>
                        <td class="px-5 py-3 text-muted">{{ $jadwal->notes ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-12 text-center text-muted">Belum ada jadwal siaran untuk kamu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $jadwalSiaran->links() }}
    </div>
@endsection
