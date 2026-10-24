{{-- Sel tanggal dan jam; jadwal hari ini ditandai garis oranye --}}
@php($isToday = $jadwal->broadcast_date->isToday())
<td class="relative px-5 py-3 whitespace-nowrap tabular-nums {{ $isToday ? 'before:absolute before:inset-y-0 before:left-0 before:w-1 before:bg-signal' : '' }}">
    {{ $jadwal->broadcast_date->locale('id')->translatedFormat('D, d M Y') }}
    @if ($isToday)
        <span class="ml-2 text-xs font-medium text-signal-strong">Hari ini</span>
    @endif
</td>
<td class="px-5 py-3 whitespace-nowrap tabular-nums">{{ substr($jadwal->start_time, 0, 5) }}–{{ substr($jadwal->end_time, 0, 5) }}</td>
