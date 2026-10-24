@extends('auth.hr.layout')

@section('title', 'Absensi')

@section('content')
    @include('auth.hr.absensi._tabs', ['active' => 'program'])

    <p class="mb-6 max-w-3xl text-sm text-muted">Total kehadiran kru per program dari sesi terjadwal yang check-in-nya sudah ditutup. Buka program untuk melihat status tiap kru per tanggal.</p>

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Hari</th>
                    <th scope="col" class="px-5 py-3 font-medium">Periode</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Sesi selesai</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Hadir</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Terlambat</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Izin</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Sakit</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Tidak hadir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($programs as $row)
                    <tr class="hover:bg-canvas/60">
                        <td class="px-5 py-3 font-medium">
                            <a href="{{ route('absensi.program', $row['program']) }}" class="text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">{{ $row['program']->name }}</a>
                        </td>
                        <td class="px-5 py-3">{{ \App\Http\Controllers\auth\hr\ProgramController::DAY_NAMES[$row['program']->day_of_week] ?? '-' }}</td>
                        <td class="px-5 py-3 text-muted tabular-nums">{{ $row['program']->period_start->locale('id')->translatedFormat('d M Y') }} – {{ $row['program']->period_end->locale('id')->translatedFormat('d M Y') }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['closed_sessions'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['totals']['hadir'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['totals']['terlambat'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['totals']['izin'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums">{{ $row['totals']['sakit'] }}</td>
                        <td class="px-5 py-3 text-right tabular-nums {{ $row['totals']['tidak_hadir'] > 0 ? 'font-medium text-danger' : '' }}">{{ $row['totals']['tidak_hadir'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-muted">Belum ada program. Buat program dulu di menu Program.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
