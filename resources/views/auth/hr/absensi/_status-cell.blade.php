{{-- Sel status absensi satu orang di satu sesi. Param: $status (AttendanceStatus atau null kalau bukan kru sesi itu) --}}
@if ($status === null)
    <td class="px-3 py-3 text-muted"><span aria-hidden="true">–</span><span class="sr-only">Bukan kru sesi ini</span></td>
@else
    <td class="px-3 py-3 whitespace-nowrap {{ match ($status) {
        \App\Enums\AttendanceStatus::TidakHadir => 'font-medium text-danger',
        \App\Enums\AttendanceStatus::Belum => 'text-muted',
        default => '',
    } }}">{{ $status->label() }}</td>
@endif
