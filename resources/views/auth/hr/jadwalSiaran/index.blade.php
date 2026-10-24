@extends('auth.hr.layout')

@section('title', 'Jadwal Siaran')

@section('content')
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-semibold">Jadwal Siaran</h1>
        <a href="{{ route('jadwalSiaran.create') }}" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Tambah Jadwal</a>
    </div>

    @if (session('status'))
        <div class="mb-4 px-4 py-3 rounded bg-green-100 text-green-800 text-sm">{{ session('status') }}</div>
    @endif

    <div class="bg-white rounded border overflow-x-auto">
        <table class="w-full text-sm text-left">
            <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Jam</th>
                    <th class="px-4 py-3">Program</th>
                    <th class="px-4 py-3">Announcer</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($jadwalSiaran as $jadwal)
                    <tr class="border-b last:border-b-0">
                        <td class="px-4 py-3">{{ $jadwal->broadcast_date->format('d-m-Y') }}</td>
                        <td class="px-4 py-3">{{ substr($jadwal->start_time, 0, 5) }} - {{ substr($jadwal->end_time, 0, 5) }}</td>
                        <td class="px-4 py-3">{{ $jadwal->program_title }}</td>
                        <td class="px-4 py-3">{{ $jadwal->announcer->username }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                <a href="{{ route('jadwalSiaran.edit', $jadwal) }}" class="text-indigo-600">Edit</a>
                                <form method="POST" action="{{ route('jadwalSiaran.destroy', $jadwal) }}" onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">Belum ada jadwal siaran.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $jadwalSiaran->links() }}
    </div>
@endsection
