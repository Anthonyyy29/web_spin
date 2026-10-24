@php($program = $program ?? null)
@php($fieldClass = 'w-full rounded-md border border-line bg-white px-3 py-2 text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')

<div class="space-y-5">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium">Nama program</label>
        <input type="text" id="name" name="name" value="{{ old('name', $program?->name) }}" class="{{ $fieldClass }}" @error('name') aria-invalid="true" aria-describedby="name-error" @enderror>
        @error('name')
            <p id="name-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="day_of_week" class="mb-1.5 block text-sm font-medium">Hari siaran</label>
        <select id="day_of_week" name="day_of_week" class="{{ $fieldClass }}" @error('day_of_week') aria-invalid="true" aria-describedby="day_of_week-error" @enderror>
            <option value="">Pilih hari</option>
            @foreach ($dayNames as $number => $dayName)
                <option value="{{ $number }}" @selected((int) old('day_of_week', $program?->day_of_week) === $number)>{{ $dayName }}</option>
            @endforeach
        </select>
        @error('day_of_week')
            <p id="day_of_week-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="crewcall_time" class="mb-1.5 block text-sm font-medium">Crewcall</label>
            <input type="time" id="crewcall_time" name="crewcall_time" value="{{ old('crewcall_time', $program ? substr($program->crewcall_time, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('crewcall_time') aria-invalid="true" aria-describedby="crewcall_time-error" @enderror>
            @error('crewcall_time')
                <p id="crewcall_time-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="tapping_start" class="mb-1.5 block text-sm font-medium">Tapping mulai</label>
            <input type="time" id="tapping_start" name="tapping_start" value="{{ old('tapping_start', $program ? substr($program->tapping_start, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('tapping_start') aria-invalid="true" aria-describedby="tapping_start-error" @enderror>
            @error('tapping_start')
                <p id="tapping_start-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="tapping_end" class="mb-1.5 block text-sm font-medium">Tapping selesai</label>
            <input type="time" id="tapping_end" name="tapping_end" value="{{ old('tapping_end', $program ? substr($program->tapping_end, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('tapping_end') aria-invalid="true" aria-describedby="tapping_end-error" @enderror>
            @error('tapping_end')
                <p id="tapping_end-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="period_start" class="mb-1.5 block text-sm font-medium">Periode mulai</label>
            <input type="date" id="period_start" name="period_start" value="{{ old('period_start', $program?->period_start->format('Y-m-d')) }}" class="{{ $fieldClass }} tabular-nums" @error('period_start') aria-invalid="true" aria-describedby="period_start-error" @enderror>
            @error('period_start')
                <p id="period_start-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="period_end" class="mb-1.5 block text-sm font-medium">Periode selesai</label>
            <input type="date" id="period_end" name="period_end" value="{{ old('period_end', $program?->period_end->format('Y-m-d')) }}" class="{{ $fieldClass }} tabular-nums" @error('period_end') aria-invalid="true" aria-describedby="period_end-error" @enderror>
            @error('period_end')
                <p id="period_end-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    @include('auth.hr.partials.crew-fields', ['announcers' => $announcers, 'sos' => $sos, 'selectedAnnouncerIds' => $selectedAnnouncerIds ?? [], 'selectedSoIds' => $selectedSoIds ?? []])
</div>

<div class="mt-8 flex items-center gap-5 border-t border-line pt-5">
    <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">{{ $submitLabel }}</button>
    <a href="{{ route('program.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Batal</a>
</div>
