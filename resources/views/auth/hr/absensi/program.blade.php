@extends('auth.hr.layout')

@section('title', 'Absensi '.$program->name)

@section('content')
    <a href="{{ route('absensi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Absensi</a>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ $program->name }}</h1>
    <p class="mt-1 mb-6 max-w-3xl text-sm text-muted">Status tiap kru per tanggal sesi. Total di kanan hanya menghitung sesi yang check-in-nya sudah ditutup. Klik tanggal untuk membuka absensi sesi itu dan menandai Izin atau Sakit.</p>

    <form method="GET" action="{{ route('absensi.program', $program) }}" class="mb-6 flex flex-wrap items-start gap-3">
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
            @if ($from || $to)
                <a href="{{ route('absensi.program', $program) }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Semua tanggal</a>
            @endif
            <a href="{{ route('absensi.program.export', [$program, ...array_filter(['from' => $from, 'to' => $to])]) }}" class="text-sm font-medium text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ekspor CSV</a>
        </div>
    </form>

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="sticky left-0 bg-white px-5 py-3 font-medium">Kru</th>
                    <th scope="col" class="px-5 py-3 font-medium">Posisi</th>
                    @foreach ($sessions as $session)
                        <th scope="col" class="px-3 py-3 font-medium whitespace-nowrap">
                            <a href="{{ route('absensi.session', $session) }}" class="tabular-nums underline-offset-4 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">{{ $session->session_date->locale('id')->translatedFormat('d M') }}</a>
                            @if ($session->regular_date === null)
                                <span class="block text-xs font-normal">tambahan</span>
                            @endif
                        </th>
                    @endforeach
                    <th scope="col" class="border-l border-line px-3 py-3 text-right font-medium">Hadir</th>
                    <th scope="col" class="px-3 py-3 text-right font-medium">Terlambat</th>
                    <th scope="col" class="px-3 py-3 text-right font-medium">Izin</th>
                    <th scope="col" class="px-3 py-3 text-right font-medium">Sakit</th>
                    <th scope="col" class="px-3 py-3 text-right font-medium">Tidak hadir</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $row)
                    <tr class="hover:bg-canvas/60">
                        <th scope="row" class="sticky left-0 bg-white px-5 py-3 font-medium whitespace-nowrap">{{ $row['user']->username }}</th>
                        <td class="px-5 py-3 text-muted">{{ $row['position'] === null ? '-' : \App\Enums\CrewPosition::from($row['position'])->label() }}</td>
                        @foreach ($row['cells'] as $status)
                            @if ($status === null)
                                <td class="px-3 py-3 text-muted"><span aria-hidden="true">–</span><span class="sr-only">Bukan kru sesi ini</span></td>
                            @else
                                <td class="px-3 py-3 whitespace-nowrap {{ match ($status) {
                                    \App\Enums\AttendanceStatus::TidakHadir => 'font-medium text-danger',
                                    \App\Enums\AttendanceStatus::Belum => 'text-muted',
                                    default => '',
                                } }}">{{ $status->label() }}</td>
                            @endif
                        @endforeach
                        <td class="border-l border-line px-3 py-3 text-right tabular-nums">{{ $row['totals']['hadir'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $row['totals']['terlambat'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $row['totals']['izin'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $row['totals']['sakit'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums {{ $row['totals']['tidak_hadir'] > 0 ? 'font-medium text-danger' : '' }}">{{ $row['totals']['tidak_hadir'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $sessions->count() + 7 }}" class="px-5 py-12 text-center text-muted">Program ini belum punya kru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($sessions->isEmpty())
        <p class="mt-4 text-sm text-muted">Belum ada sesi terjadwal{{ $from || $to ? ' di rentang tanggal ini' : '' }}.</p>
    @endif
@endsection
