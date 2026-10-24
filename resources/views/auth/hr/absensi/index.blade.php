@extends('auth.hr.layout')

@section('title', 'Absensi')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Absensi</h1>
        <p class="mt-1 text-sm text-muted">Rekap kehadiran announcer dan SO dari sesi terjadwal yang check-in-nya sudah ditutup. Buka daftar sesi untuk melihat atau mengubah absensi per sesi.</p>
    </div>

    <form method="GET" action="{{ route('absensi.index') }}" class="mb-6 flex flex-wrap items-start gap-3">
        @php($fieldClass = 'rounded-md border border-line bg-white px-3 py-2 text-sm text-ink tabular-nums focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')
        <div>
            <label for="from" class="mb-1.5 block text-sm font-medium">Dari tanggal</label>
            <input type="date" id="from" name="from" value="{{ $from }}" class="{{ $fieldClass }}" @error('from') aria-invalid="true" aria-describedby="from-error" @enderror>
            @error('from')
                <p id="from-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="to" class="mb-1.5 block text-sm font-medium">Sampai tanggal</label>
            <input type="date" id="to" name="to" value="{{ $to }}" class="{{ $fieldClass }}" @error('to') aria-invalid="true" aria-describedby="to-error" @enderror>
            @error('to')
                <p id="to-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="flex items-center gap-4 pt-7">
            <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Terapkan</button>
            <a href="{{ route('absensi.export', ['from' => $from, 'to' => $to]) }}" class="text-sm font-medium text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ekspor CSV</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Username</th>
                    <th scope="col" class="px-5 py-3 font-medium">Role</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Hadir</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Terlambat</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Izin</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Sakit</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Tidak hadir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $row)
                    <tr class="hover:bg-canvas/60">
                        <td class="px-5 py-3 font-medium">{{ $row['user']->username }}</td>
                        <td class="px-5 py-3 text-muted">{{ $row['user']->role === 'so' ? 'SO' : 'Announcer' }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['hadir'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['terlambat'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['izin'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['sakit'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums {{ $row['tidak_hadir'] > 0 ? 'font-medium text-danger' : '' }}">{{ $row['tidak_hadir'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-muted">Belum ada announcer atau SO.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
