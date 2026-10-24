@extends('auth.hr.layout')

@section('title', 'Absensi Sesi')

@section('content')
    @php($isScheduled = $session->status === \App\Enums\SessionStatus::Scheduled)
    <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Sesi</a>
    <div class="mt-1 mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Absensi {{ $session->program->name }}</h1>
            <p class="mt-1 text-sm text-muted tabular-nums">
                @if ($session->session_date)
                    {{ $session->session_date->locale('id')->translatedFormat('D, d M Y') }}, crewcall {{ substr($session->crewcall_time, 0, 5) }}, tapping {{ substr($session->tapping_start, 0, 5) }}–{{ substr($session->tapping_end, 0, 5) }}
                @else
                    Ditunda
                @endif
                <span class="ml-2 font-sans">{{ $session->status->label() }}</span>
            </p>
        </div>
        <a href="{{ route('absensi.session.export', $session) }}" class="text-sm font-medium text-signal-strong underline underline-offset-4 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ekspor CSV</a>
    </div>

    @include('auth.partials.status')

    @if ($errors->any())
        <div role="alert" class="mb-4 rounded-md border border-danger/40 bg-white px-4 py-3 text-sm text-danger">
            @foreach ($errors->all() as $message)
                <p><span class="mr-2 inline-block size-2 rounded-full bg-danger align-middle" aria-hidden="true"></span>{{ $message }}</p>
            @endforeach
        </div>
    @endif

    @unless ($isScheduled)
        <p class="mb-4 text-sm text-muted">Status absensi hanya bisa diubah untuk sesi terjadwal.</p>
    @endunless

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Username</th>
                    <th scope="col" class="px-5 py-3 font-medium">Posisi</th>
                    <th scope="col" class="px-5 py-3 font-medium">Status</th>
                    <th scope="col" class="px-5 py-3 font-medium">Jam check-in</th>
                    <th scope="col" class="px-5 py-3 font-medium">Catatan</th>
                    @if ($isScheduled)
                        <th scope="col" class="px-5 py-3 font-medium">Ubah status</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($members as $member)
                    @php($user = $member['user'])
                    <tr class="hover:bg-canvas/60">
                        <td class="px-5 py-3 font-medium">{{ $user->username }}</td>
                        <td class="px-5 py-3 text-muted">{{ $member['position'] === null ? '-' : ($member['position'] === 'so' ? 'SO' : 'Announcer') }}</td>
                        <td class="px-5 py-3 whitespace-nowrap {{ $member['status'] === \App\Enums\AttendanceStatus::TidakHadir ? 'font-medium text-danger' : '' }}">{{ $member['status']->label() }}</td>
                        <td class="px-5 py-3 tabular-nums">{{ $member['attendance']?->checked_in_at?->format('H:i') ?? '-' }}</td>
                        <td class="px-5 py-3 text-muted">{{ $member['attendance']?->note ?? '-' }}</td>
                        @if ($isScheduled)
                            <td class="px-5 py-3">
                                <form method="POST" action="{{ route('absensi.update', [$session, $user]) }}" class="flex flex-wrap items-center gap-2">
                                    @csrf
                                    @method('PUT')
                                    <label for="status-{{ $user->id }}" class="sr-only">Status {{ $user->username }}</label>
                                    <select id="status-{{ $user->id }}" name="status" class="rounded-md border border-line bg-white px-2 py-1.5 text-sm text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30">
                                        <option value="izin">Izin</option>
                                        <option value="sakit">Sakit</option>
                                        <option value="reset">Kembalikan</option>
                                    </select>
                                    <label for="note-{{ $user->id }}" class="sr-only">Catatan {{ $user->username }}</label>
                                    <input type="text" id="note-{{ $user->id }}" name="note" maxlength="255" placeholder="Catatan" value="{{ $member['attendance']?->note }}" class="w-40 rounded-md border border-line bg-white px-2 py-1.5 text-sm text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30">
                                    <button type="submit" class="rounded-md bg-signal-strong px-3 py-1.5 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Simpan</button>
                                </form>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ $isScheduled ? 6 : 5 }}" class="px-5 py-12 text-center text-muted">Sesi ini belum punya kru.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
