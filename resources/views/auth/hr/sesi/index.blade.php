@extends('auth.hr.layout')

@section('title', 'Sesi')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Sesi</h1>
            <p class="mt-1 text-sm text-muted">Sesi siaran yang dibuat dari program. Ubah jadwal atau kru, tunda, atau batalkan per sesi.</p>
        </div>
        <form method="GET" action="{{ route('sesi.index') }}" class="flex items-end gap-2">
            <div>
                <label for="program" class="mb-1.5 block text-sm font-medium">Program</label>
                <select id="program" name="program" class="rounded-md border border-line bg-white px-3 py-2 text-sm text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30">
                    <option value="">Semua program</option>
                    @foreach ($programs as $program)
                        <option value="{{ $program->id }}" @selected($selectedProgramId === $program->id)>{{ $program->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Terapkan</button>
        </form>
    </div>

    @include('auth.partials.status')

    @error('sesi')
        <div role="alert" class="mb-4 rounded-md border border-danger/40 bg-white px-4 py-3 text-sm text-danger">
            <span class="mr-2 inline-block size-2 rounded-full bg-danger align-middle" aria-hidden="true"></span>{{ $message }}
        </div>
    @enderror

    @if ($postponedSessions->isNotEmpty())
        <section aria-labelledby="reschedule-heading" class="mb-6 rounded-lg border border-line bg-white">
            <h2 id="reschedule-heading" class="border-b border-line px-5 py-3 text-sm font-semibold">Perlu dijadwalkan ulang</h2>
            <ul class="divide-y divide-line text-sm">
                @foreach ($postponedSessions as $session)
                    <li class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div>
                            <span class="font-medium">{{ $session->program->name }}</span>
                            <span class="ml-2 text-muted tabular-nums">semula {{ $session->regular_date->locale('id')->translatedFormat('D, d M Y') }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('sesi.edit', $session) }}" class="font-medium text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Jadwalkan</a>
                            <form method="POST" action="{{ route('sesi.cancel', $session) }}" onsubmit="return confirm(@js('Batalkan sesi '.$session->program->name.' yang semula '.$session->regular_date->format('d/m/Y').'?'))">
                                @csrf
                                <button type="submit" class="text-danger underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger">Batalkan</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Tanggal</th>
                    <th scope="col" class="px-5 py-3 font-medium">Crewcall</th>
                    <th scope="col" class="px-5 py-3 font-medium">Tapping</th>
                    <th scope="col" class="px-5 py-3 font-medium">Status</th>
                    <th scope="col" class="px-5 py-3 font-medium">Kru</th>
                    <th scope="col" class="px-5 py-3 font-medium"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($sessions as $session)
                    <tr class="hover:bg-canvas/60">
                        <td class="px-5 py-3 font-medium">{{ $session->program->name }}</td>
                        @include('auth.partials.sesi-cells', ['session' => $session])
                        <td class="px-5 py-3 whitespace-nowrap text-muted">{{ $session->crew->where('pivot.position', 'announcer')->count() }} announcer, {{ $session->crew->where('pivot.position', 'so')->count() }} SO</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('sesi.edit', $session) }}" class="font-medium text-ink underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ubah</a>
                                @if ($session->status === \App\Enums\SessionStatus::Scheduled)
                                    <form method="POST" action="{{ route('sesi.postpone', $session) }}" onsubmit="return confirm(@js('Tunda sesi '.$session->program->name.' pada '.$session->session_date->format('d/m/Y').'? Tanggal dan jam dikosongkan sampai dijadwalkan ulang.'))">
                                        @csrf
                                        <button type="submit" class="text-muted underline-offset-4 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Tunda</button>
                                    </form>
                                    <form method="POST" action="{{ route('sesi.cancel', $session) }}" onsubmit="return confirm(@js('Batalkan sesi '.$session->program->name.' pada '.$session->session_date->format('d/m/Y').'?'))">
                                        @csrf
                                        <button type="submit" class="text-danger underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger">Batalkan</button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-muted">Belum ada sesi bertanggal.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
@endsection
