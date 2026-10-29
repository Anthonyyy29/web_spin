{{-- Tanggal, jam, dan kru sesi. Param: $session, $announcers, $sos, $selectedAnnouncerIds, $selectedSoIds, $programReference (opsional: nilai program untuk pengingat) --}}
@php($programReference ??= null)
@php($fieldClass = 'w-full rounded-md border border-line bg-white px-3 py-2 text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')

<div class="space-y-5">
    <div>
        <label for="session_date" class="mb-1.5 block text-sm font-medium">Tanggal sesi</label>
        <input type="date" id="session_date" name="session_date" value="{{ old('session_date', $session->session_date?->format('Y-m-d')) }}" class="{{ $fieldClass }} tabular-nums" @error('session_date') aria-invalid="true" aria-describedby="session_date-error" @enderror @if ($programReference) data-program-weekday="{{ $programReference['dayOfWeek'] }}" @endif>
        @if ($programReference)
            {{-- Teks pengingat; ditampilkan JS saat hari yang dipilih bukan hari siaran program --}}
            <p id="session_date-program" data-program-hint data-program-hint-text="Bukan hari siaran program ({{ $programReference['dayName'] }})" hidden aria-live="polite" class="mt-1.5 text-sm text-signal-strong"></p>
        @endif
        @error('session_date')
            <p id="session_date-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="crewcall_time" class="mb-1.5 block text-sm font-medium">Crewcall</label>
            <input type="time" id="crewcall_time" name="crewcall_time" value="{{ old('crewcall_time', $session->crewcall_time ? substr($session->crewcall_time, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('crewcall_time') aria-invalid="true" aria-describedby="crewcall_time-error" @enderror @if ($programReference) data-program-value="{{ $programReference['crewcall_time'] }}" @endif>
            @if ($programReference)
                <p id="crewcall_time-program" data-program-hint data-program-hint-text="Beda dari program ({{ $programReference['crewcall_time'] }})" hidden aria-live="polite" class="mt-1.5 text-sm text-signal-strong"></p>
            @endif
            @error('crewcall_time')
                <p id="crewcall_time-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="tapping_start" class="mb-1.5 block text-sm font-medium">Tapping mulai</label>
            <input type="time" id="tapping_start" name="tapping_start" value="{{ old('tapping_start', $session->tapping_start ? substr($session->tapping_start, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('tapping_start') aria-invalid="true" aria-describedby="tapping_start-error" @enderror @if ($programReference) data-program-value="{{ $programReference['tapping_start'] }}" @endif>
            @if ($programReference)
                <p id="tapping_start-program" data-program-hint data-program-hint-text="Beda dari program ({{ $programReference['tapping_start'] }})" hidden aria-live="polite" class="mt-1.5 text-sm text-signal-strong"></p>
            @endif
            @error('tapping_start')
                <p id="tapping_start-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="tapping_end" class="mb-1.5 block text-sm font-medium">Tapping selesai</label>
            <input type="time" id="tapping_end" name="tapping_end" value="{{ old('tapping_end', $session->tapping_end ? substr($session->tapping_end, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('tapping_end') aria-invalid="true" aria-describedby="tapping_end-error" @enderror @if ($programReference) data-program-value="{{ $programReference['tapping_end'] }}" @endif>
            @if ($programReference)
                <p id="tapping_end-program" data-program-hint data-program-hint-text="Beda dari program ({{ $programReference['tapping_end'] }})" hidden aria-live="polite" class="mt-1.5 text-sm text-signal-strong"></p>
            @endif
            @error('tapping_end')
                <p id="tapping_end-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    @include('auth.hr.partials.crew-fields')
</div>
