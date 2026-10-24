@extends($layout)

@section('title', 'Sesi saya')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Sesi saya</h1>
        <p class="mt-1 text-sm text-muted">Sesi siaran tempat kamu bertugas. Check-in dibuka 15 menit sebelum crewcall dan ditutup saat tapping dimulai.</p>
    </div>

    @include('auth.partials.status')

    @error('checkIn')
        <div role="alert" class="mb-4 rounded-md border border-danger/40 bg-white px-4 py-3 text-sm text-danger">
            <span class="mr-2 inline-block size-2 rounded-full bg-danger align-middle" aria-hidden="true"></span>{{ $message }}
        </div>
    @enderror

    <div class="overflow-x-auto rounded-lg border border-line bg-white">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-line text-muted">
                <tr>
                    <th scope="col" class="px-5 py-3 font-medium">Program</th>
                    <th scope="col" class="px-5 py-3 font-medium">Tanggal</th>
                    <th scope="col" class="px-5 py-3 font-medium">Crewcall</th>
                    <th scope="col" class="px-5 py-3 font-medium">Tapping</th>
                    <th scope="col" class="px-5 py-3 font-medium">Kehadiran</th>
                    <th scope="col" class="px-5 py-3 font-medium"><span class="sr-only">Aksi</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-line">
                @forelse ($sessions as $session)
                    @if ($session->status === \App\Enums\SessionStatus::Postponed)
                        <tr class="hover:bg-canvas/60">
                            <td class="px-5 py-3 font-medium">{{ $session->program->name }}</td>
                            <td colspan="3" class="px-5 py-3 text-muted">Menunggu jadwal baru</td>
                            <td class="px-5 py-3 text-muted">Ditunda</td>
                            <td class="px-5 py-3"></td>
                        </tr>
                    @else
                        @php($attendance = $session->attendances->firstWhere('user_id', $user->id))
                        @php($isToday = $session->session_date->isToday())
                        <tr class="hover:bg-canvas/60">
                            <td class="px-5 py-3 font-medium">{{ $session->program->name }}</td>
                            <td class="relative px-5 py-3 whitespace-nowrap tabular-nums {{ $isToday ? 'before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-signal' : '' }}">
                                {{ $session->session_date->locale('id')->translatedFormat('D, d M Y') }}
                                @if ($isToday)
                                    <span class="ml-2 text-xs font-medium text-signal-strong">Hari ini</span>
                                @endif
                            </td>
                            <td class="px-5 py-3 tabular-nums">{{ substr($session->crewcall_time, 0, 5) }}</td>
                            <td class="px-5 py-3 whitespace-nowrap tabular-nums">{{ substr($session->tapping_start, 0, 5) }}–{{ substr($session->tapping_end, 0, 5) }}</td>
                            <td class="px-5 py-3 whitespace-nowrap">
                                {{ $session->attendanceStatusFor($user, now())->label() }}
                                @if ($attendance)
                                    <span class="ml-1 text-muted tabular-nums">{{ $attendance->checked_in_at->format('H:i') }}</span>
                                @endif
                            </td>
                            <td class="px-5 py-3">
                                @if (! $attendance && $session->isCheckInOpen(now()))
                                    <form method="POST" action="{{ route($routePrefix.'.sesi.checkIn', $session) }}" class="flex justify-end">
                                        @csrf
                                        <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Check-in</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @endif
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-muted">Belum ada sesi untuk kamu.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
@endsection
