{{-- Tabel sesi saya. Param: $sessions, $emptyMessage, $user, $now, $routePrefix --}}
<div class="relative overflow-x-auto rounded-lg border border-line bg-white">
    <table class="w-full text-left text-sm max-md:block">
        <thead class="border-b border-line text-muted max-md:hidden">
            <tr>
                <th scope="col" class="px-5 py-3 font-medium">Program</th>
                <th scope="col" class="px-5 py-3 font-medium">Tanggal</th>
                <th scope="col" class="px-5 py-3 font-medium">Crewcall</th>
                <th scope="col" class="px-5 py-3 font-medium">Tapping</th>
                <th scope="col" class="px-5 py-3 font-medium">Kehadiran</th>
                <th scope="col" class="px-5 py-3 font-medium"><span class="sr-only">Aksi</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-line max-md:block">
            @forelse ($sessions as $session)
                @if ($session->status === \App\Enums\SessionStatus::Postponed)
                    <tr class="hover:bg-canvas/60 max-md:grid max-md:grid-cols-2 max-md:gap-x-3 max-md:gap-y-1 max-md:px-4 max-md:py-3">
                        <td class="px-5 py-3 font-medium max-md:col-span-2 max-md:p-0">{{ $session->program->name }}</td>
                        <td colspan="3" class="px-5 py-3 text-muted max-md:p-0">Menunggu jadwal baru</td>
                        <td class="px-5 py-3 text-muted max-md:p-0">Ditunda</td>
                        <td class="px-5 py-3 max-md:hidden"></td>
                    </tr>
                @else
                    @php($attendance = $session->attendances->firstWhere('user_id', $user->id))
                    @php($isToday = $session->session_date->isSameDay($now))
                    @php($canCheckIn = ! $attendance && $session->isCheckInOpen($now))
                    <tr class="hover:bg-canvas/60 max-md:relative max-md:grid max-md:grid-cols-2 max-md:gap-x-3 max-md:gap-y-1 max-md:px-4 max-md:py-3 {{ $isToday ? 'max-md:before:absolute max-md:before:inset-y-0 max-md:before:left-0 max-md:before:w-1 max-md:before:bg-signal' : '' }}">
                        <td class="px-5 py-3 font-medium max-md:col-span-2 max-md:p-0">{{ $session->program->name }}</td>
                        <td class="px-5 py-3 whitespace-nowrap tabular-nums md:relative max-md:col-span-2 max-md:p-0 {{ $isToday ? 'md:before:absolute md:before:inset-y-0 md:before:left-0 md:before:w-1 md:before:bg-signal' : '' }}">
                            {{ $session->session_date->locale('id')->translatedFormat('D, d M Y') }}
                            @if ($isToday)
                                <span class="ml-2 text-xs font-medium text-signal-strong">Hari ini</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 tabular-nums max-md:p-0"><span class="text-muted md:hidden">Crewcall </span>{{ substr($session->crewcall_time, 0, 5) }}</td>
                        <td class="px-5 py-3 whitespace-nowrap tabular-nums max-md:p-0"><span class="text-muted md:hidden">Tapping </span>{{ substr($session->tapping_start, 0, 5) }}–{{ substr($session->tapping_end, 0, 5) }}</td>
                        <td class="px-5 py-3 whitespace-nowrap max-md:col-span-2 max-md:p-0">
                            {{ $session->attendanceStatusFor($user, $now)->label() }}
                            @if ($attendance?->checked_in_at)
                                <span class="ml-1 text-muted tabular-nums">{{ $attendance->checked_in_at->format('H:i') }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 max-md:col-span-2 max-md:p-0 max-md:pt-1 {{ $canCheckIn ? '' : 'max-md:hidden' }}">
                            @if ($canCheckIn)
                                <form method="POST" action="{{ route($routePrefix.'.sesi.checkIn', $session) }}" class="flex justify-end">
                                    @csrf
                                    <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal max-md:min-h-11 max-md:w-full">Check-in</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endif
            @empty
                <tr class="max-md:block">
                    <td colspan="6" class="px-5 py-12 text-center text-muted max-md:block max-md:px-4 max-md:py-10">{{ $emptyMessage }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
