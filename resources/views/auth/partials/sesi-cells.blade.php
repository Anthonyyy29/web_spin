{{-- Sel tanggal, crewcall, tapping, dan status sesi; sesi hari ini ditandai garis oranye. Param: $session (bertanggal) --}}
@php($isToday = $session->session_date->isToday())
<td class="relative px-5 py-3 whitespace-nowrap tabular-nums {{ $isToday ? 'before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-signal' : '' }}">
    {{ $session->session_date->locale('id')->translatedFormat('D, d M Y') }}
    @if ($isToday)
        <span class="ml-2 text-xs font-medium text-signal-strong">Hari ini</span>
    @endif
</td>
<td class="px-5 py-3 tabular-nums">{{ substr($session->crewcall_time, 0, 5) }}</td>
<td class="px-5 py-3 whitespace-nowrap tabular-nums">{{ substr($session->tapping_start, 0, 5) }}–{{ substr($session->tapping_end, 0, 5) }}</td>
<td class="px-5 py-3 whitespace-nowrap {{ $session->status === \App\Enums\SessionStatus::Cancelled ? 'text-danger' : 'text-muted' }}">{{ $session->status->label() }}</td>
