@extends('auth.hr.layout')

@section('title', 'Absensi per Orang')

@section('content')
    @include('auth.hr.absensi._tabs', ['active' => 'people'])

    <p class="mb-6 max-w-3xl text-sm text-muted">Status tiap announcer dan SO per sesi dari semua program. Total di kanan hanya menghitung sesi yang check-in-nya sudah ditutup. Klik nama program untuk membuka absensi sesi itu.</p>

    <form method="GET" action="{{ route('absensi.people') }}" class="mb-6 flex flex-wrap items-start gap-3">
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
            <a href="{{ route('absensi.export', ['from' => $from, 'to' => $to]) }}" class="text-sm font-medium text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ekspor Excel</a>
        </div>
    </form>

    @php($dateGroups = $sessions->groupBy(fn ($session) => $session->session_date->toDateString()))
    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" rowspan="2" class="sticky left-0 bg-white px-5 py-3 font-medium">Username</th>
                    <th scope="col" rowspan="2" class="px-5 py-3 font-medium">Role</th>
                    @foreach ($dateGroups as $group)
                        <th scope="colgroup" colspan="{{ $group->count() }}" class="px-3 pt-3 pb-1 font-medium whitespace-nowrap">{{ $group->first()->session_date->locale('id')->translatedFormat('d M') }}</th>
                    @endforeach
                    <th scope="col" rowspan="2" class="border-l border-line px-3 py-3 text-right font-medium">Hadir</th>
                    <th scope="col" rowspan="2" class="px-3 py-3 text-right font-medium">Terlambat</th>
                    <th scope="col" rowspan="2" class="px-3 py-3 text-right font-medium">Izin</th>
                    <th scope="col" rowspan="2" class="px-3 py-3 text-right font-medium">Sakit</th>
                    <th scope="col" rowspan="2" class="px-3 py-3 text-right font-medium">Tidak hadir</th>
                </tr>
                <tr>
                    @foreach ($sessions as $session)
                        <th scope="col" class="px-3 pt-1 pb-3 font-medium whitespace-nowrap">
                            <a href="{{ route('absensi.session', $session) }}" class="underline-offset-4 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">{{ $session->program->name }}</a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($rows as $row)
                    <tr class="hover:bg-canvas/60">
                        <th scope="row" class="sticky left-0 bg-white px-5 py-3 font-medium whitespace-nowrap">{{ $row['user']->username }}</th>
                        <td class="px-5 py-3 text-muted">{{ $row['position'] === null ? '-' : \App\Enums\CrewPosition::from($row['position'])->label() }}</td>
                        @foreach ($row['cells'] as $status)
                            @include('auth.hr.absensi._status-cell', ['status' => $status])
                        @endforeach
                        <td class="border-l border-line px-3 py-3 text-right tabular-nums">{{ $row['totals']['hadir'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $row['totals']['terlambat'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $row['totals']['izin'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums">{{ $row['totals']['sakit'] }}</td>
                        <td class="px-3 py-3 text-right tabular-nums {{ $row['totals']['tidak_hadir'] > 0 ? 'font-medium text-danger' : '' }}">{{ $row['totals']['tidak_hadir'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $sessions->count() + 7 }}" class="px-5 py-12 text-center text-muted">Belum ada announcer atau SO.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($sessions->isEmpty())
        <p class="mt-4 text-sm text-muted">Belum ada sesi terjadwal di rentang tanggal ini.</p>
    @endif
@endsection
