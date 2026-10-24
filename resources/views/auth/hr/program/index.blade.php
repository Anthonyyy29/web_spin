@extends('auth.hr.layout')

@section('title', 'Program')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Program</h1>
            <p class="mt-1 text-sm text-muted">Template mingguan program siaran. Sesi dibuat otomatis dari program.</p>
        </div>
        <a href="{{ route('program.create') }}" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Tambah program</a>
    </div>

    @include('auth.partials.status')

    @error('program')
        <div role="alert" class="mb-4 rounded-md border border-danger/40 bg-white px-4 py-3 text-sm text-danger">
            <span class="mr-2 inline-block size-2 rounded-full bg-danger align-middle" aria-hidden="true"></span>{{ $message }}
        </div>
    @enderror

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Hari</th>
                    <th scope="col" class="px-5 py-3 font-medium">Crewcall</th>
                    <th scope="col" class="px-5 py-3 font-medium">Tapping</th>
                    <th scope="col" class="px-5 py-3 font-medium">Periode</th>
                    <th scope="col" class="px-5 py-3 font-medium">Kru</th>
                    <th scope="col" class="px-5 py-3 font-medium"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($programs as $program)
                    <tr class="hover:bg-canvas/60">
                        <td class="px-5 py-3 font-medium">{{ $program->name }}</td>
                        <td class="px-5 py-3">{{ $dayNames[$program->day_of_week] ?? '-' }}</td>
                        <td class="px-5 py-3 tabular-nums">{{ substr($program->crewcall_time, 0, 5) }}</td>
                        <td class="px-5 py-3 tabular-nums">{{ substr($program->tapping_start, 0, 5) }} - {{ substr($program->tapping_end, 0, 5) }}</td>
                        <td class="px-5 py-3 whitespace-nowrap text-muted tabular-nums">{{ $program->period_start->format('d/m/Y') }} - {{ $program->period_end->format('d/m/Y') }}</td>
                        <td class="px-5 py-3 whitespace-nowrap text-muted">{{ $program->announcers_count }} announcer, {{ $program->sos_count }} SO</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('program.edit', $program) }}" class="font-medium text-ink underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ubah</a>
                                <form method="POST" action="{{ route('program.destroy', $program) }}" onsubmit="return confirm(@js('Hapus program '.$program->name.' beserta seluruh sesinya?'))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-danger underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center">
                            <p class="text-muted">Belum ada program.</p>
                            <a href="{{ route('program.create') }}" class="mt-2 inline-block font-medium text-signal-strong underline underline-offset-4">Tambah program pertama</a>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $programs->links() }}
    </div>
@endsection
