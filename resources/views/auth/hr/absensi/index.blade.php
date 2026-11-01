@extends('auth.hr.layout')

@section('title', 'Absensi')

@section('content')
    @include('auth.hr.absensi._tabs', ['active' => 'program'])

    <p class="mb-6 max-w-3xl text-sm text-muted">Total kehadiran kru per program dari sesi terjadwal yang check-in-nya sudah ditutup. Buka program untuk melihat status tiap kru per tanggal.</p>

    <div class="relative overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm max-md:block">
            <thead class="border-b border-line text-muted max-md:hidden">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Hari</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Sesi selesai</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Hadir</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Terlambat</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Izin</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Sakit</th>
                    <th scope="col" class="px-5 py-3 text-right font-medium">Tidak hadir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line max-md:block">
                @forelse ($programs as $row)
                    @php($countClass = 'px-5 py-3 text-right tabular-nums max-md:p-0 max-md:text-left')
                    @php($countLabelClass = 'block text-xs text-muted md:hidden')
                    <tr class="hover:bg-canvas/60 max-md:grid max-md:grid-cols-3 max-md:gap-x-3 max-md:gap-y-2 max-md:px-4 max-md:py-3">
                        <td class="px-5 py-3 font-medium max-md:col-span-2 max-md:p-0">
                            <a href="{{ route('absensi.program', $row['program']) }}" class="text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">{{ $row['program']->name }}</a>
                        </td>
                        <td class="px-5 py-3 max-md:p-0 max-md:text-right max-md:text-muted">{{ \App\Models\Program::DAY_NAMES[$row['program']->day_of_week] ?? '-' }}</td>
                        <td class="{{ $countClass }}"><span class="{{ $countLabelClass }}">Sesi selesai</span>{{ $row['closed_sessions'] }}</td>
                        <td class="{{ $countClass }}"><span class="{{ $countLabelClass }}">Hadir</span>{{ $row['totals']['hadir'] }}</td>
                        <td class="{{ $countClass }}"><span class="{{ $countLabelClass }}">Terlambat</span>{{ $row['totals']['terlambat'] }}</td>
                        <td class="{{ $countClass }}"><span class="{{ $countLabelClass }}">Izin</span>{{ $row['totals']['izin'] }}</td>
                        <td class="{{ $countClass }}"><span class="{{ $countLabelClass }}">Sakit</span>{{ $row['totals']['sakit'] }}</td>
                        <td class="{{ $countClass }} {{ $row['totals']['tidak_hadir'] > 0 ? 'font-medium text-danger' : '' }}"><span class="{{ $countLabelClass }} font-normal">Tidak hadir</span>{{ $row['totals']['tidak_hadir'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-muted max-md:block max-md:px-4 max-md:py-10">Belum ada program. Buat program dulu di menu Program.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
