@extends('auth.hr.layout')

@section('title', 'Sesi')

@section('content')
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Sesi</h1>
            <p class="mt-1 text-sm text-muted">Sesi siaran yang dibuat dari program, ditambah sesi tambahan dari HR. Ubah jadwal atau kru, tunda, atau batalkan per sesi.</p>
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
            @if ($isHistory)
                <input type="hidden" name="waktu" value="riwayat">
            @endif
            <button type="submit" class="rounded-md border border-line bg-white px-4 py-2 text-sm font-medium text-ink hover:border-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Terapkan</button>
            <a href="{{ route('sesi.create', array_filter(['program' => $selectedProgramId])) }}" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Tambah sesi</a>
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
                            <span class="ml-2 text-muted tabular-nums">{{ $session->regular_date ? 'semula '.$session->regular_date->locale('id')->translatedFormat('D, d M Y') : 'sesi tambahan' }}</span>
                        </div>
                        <div class="flex items-center gap-4">
                            <a href="{{ route('sesi.edit', $session) }}" class="font-medium text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Jadwalkan</a>
                            <a href="{{ route('absensi.session', $session) }}" class="text-muted underline-offset-4 hover:text-ink hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Absensi</a>
                            <form method="POST" action="{{ route('sesi.cancel', $session) }}" onsubmit="return confirm(@js('Batalkan sesi '.$session->program->name.($session->regular_date ? ' yang semula '.$session->regular_date->format('d/m/Y') : ' (sesi tambahan)').'?'))">
                                @csrf
                                <button type="submit" class="text-danger underline-offset-4 hover:underline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-danger">Batalkan</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>
        </section>
    @endif

    @php($tabClass = 'border-b-2 px-1 pb-2 text-sm font-medium focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal')
    <nav aria-label="Waktu sesi" class="mb-4 flex gap-6 border-b border-line">
        <a href="{{ route('sesi.index', array_filter(['program' => $selectedProgramId])) }}" @if (! $isHistory) aria-current="page" @endif class="{{ $tabClass }} {{ $isHistory ? 'border-transparent text-muted hover:text-ink' : 'border-signal-strong text-ink' }}">Mendatang</a>
        <a href="{{ route('sesi.index', array_filter(['program' => $selectedProgramId, 'waktu' => 'riwayat'])) }}" @if ($isHistory) aria-current="page" @endif class="{{ $tabClass }} {{ $isHistory ? 'border-signal-strong text-ink' : 'border-transparent text-muted hover:text-ink' }}">Riwayat</a>
    </nav>

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-4 py-3 font-medium">Program</th>
                    <th scope="col" class="px-4 py-3 font-medium">Crewcall</th>
                    <th scope="col" class="px-4 py-3 font-medium">Tapping</th>
                    <th scope="col" class="px-4 py-3 font-medium">Status</th>
                    <th scope="col" class="px-4 py-3 font-medium">Kru</th>
                    <th scope="col" class="px-4 py-3 font-medium"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            {{-- Satu tbody per tanggal; tanggal hari ini ditandai garis oranye --}}
            @forelse ($sessions->groupBy(fn ($session) => $session->session_date->toDateString()) as $daySessions)
                @php($day = $daySessions->first()->session_date)
                <tbody class="divide-y divide-line border-t border-line first-of-type:border-t-0">
                    <tr>
                        <th scope="rowgroup" colspan="6" class="relative bg-canvas/70 px-4 py-2 text-left font-semibold tabular-nums {{ $day->isToday() ? 'before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-signal' : '' }}">
                            {{ $day->locale('id')->translatedFormat('l, d M Y') }}
                            @if ($day->isToday())
                                <span class="ml-2 text-xs font-medium text-signal-strong">Hari ini</span>
                            @endif
                        </th>
                    </tr>
                    @foreach ($daySessions as $session)
                        <tr class="hover:bg-canvas/60">
                            <td class="px-4 py-3 whitespace-nowrap font-medium">{{ $session->program->name }}</td>
                            <td class="px-4 py-3 tabular-nums">{{ substr($session->crewcall_time, 0, 5) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap tabular-nums">{{ substr($session->tapping_start, 0, 5) }}–{{ substr($session->tapping_end, 0, 5) }}</td>
                            <td class="px-4 py-3 whitespace-nowrap {{ $session->status === \App\Enums\SessionStatus::Cancelled ? 'text-danger' : 'text-muted' }}">{{ $session->status->label() }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-muted">{{ $session->crew->where('pivot.position', 'announcer')->count() }} announcer, {{ $session->crew->where('pivot.position', \App\Enums\CrewPosition::So->value)->count() }} SO</td>
                            <td class="px-2 py-1.5 text-right">
                                @php($menuItemClass = 'block w-full px-3 py-2 text-left hover:bg-canvas focus-visible:outline-2 focus-visible:-outline-offset-2 focus-visible:outline-signal')
                                <button type="button" popovertarget="sesi-aksi-{{ $session->id }}" aria-label="Aksi sesi {{ $session->program->name }}, {{ $session->session_date->locale('id')->translatedFormat('d M Y') }}" class="inline-flex size-9 items-center justify-center rounded-md text-muted hover:bg-canvas hover:text-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">
                                    <svg aria-hidden="true" viewBox="0 0 20 20" fill="currentColor" class="size-5"><circle cx="4.5" cy="10" r="1.5"/><circle cx="10" cy="10" r="1.5"/><circle cx="15.5" cy="10" r="1.5"/></svg>
                                </button>
                                <div id="sesi-aksi-{{ $session->id }}" popover data-anchored-popover class="m-0 inset-auto min-w-36 rounded-md border border-line bg-white py-1 text-sm text-ink shadow-lg">
                                    <a href="{{ route('sesi.edit', $session) }}" class="{{ $menuItemClass }}">Ubah</a>
                                    <a href="{{ route('absensi.session', $session) }}" class="{{ $menuItemClass }}">Absensi</a>
                                    @if ($session->status === \App\Enums\SessionStatus::Scheduled)
                                        <div class="my-1 border-t border-line"></div>
                                        <form method="POST" action="{{ route('sesi.postpone', $session) }}" onsubmit="return confirm(@js($session->has_check_ins ? 'Sesi ini sudah punya check-in. Menunda akan menghapus semua absensi sesi ini. Lanjutkan?' : 'Tunda sesi '.$session->program->name.' pada '.$session->session_date->format('d/m/Y').'? Tanggal dan jam dikosongkan sampai dijadwalkan ulang.'))">
                                            @csrf
                                            <button type="submit" class="{{ $menuItemClass }}">Tunda</button>
                                        </form>
                                        <form method="POST" action="{{ route('sesi.cancel', $session) }}" onsubmit="return confirm(@js('Batalkan sesi '.$session->program->name.' pada '.$session->session_date->format('d/m/Y').'?'))">
                                            @csrf
                                            <button type="submit" class="{{ $menuItemClass }} text-danger">Batalkan</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            @empty
                <tbody>
                    <tr>
                        <td colspan="6" class="px-4 py-12 text-center text-muted">{{ $isHistory ? 'Belum ada riwayat sesi.' : 'Belum ada sesi mendatang.' }}</td>
                    </tr>
                </tbody>
            @endforelse
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
@endsection
