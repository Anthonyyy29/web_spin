@php($jadwal = $jadwalSiaran ?? null)
@php($fieldClass = 'w-full rounded-md border border-line bg-white px-3 py-2 text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')

<div class="space-y-5">
    <div>
        <label for="user_id" class="mb-1.5 block text-sm font-medium">Announcer</label>
        <select id="user_id" name="user_id" class="{{ $fieldClass }}" @error('user_id') aria-invalid="true" aria-describedby="user_id-error" @enderror>
            <option value="">Pilih announcer</option>
            @foreach ($announcers as $announcer)
                <option value="{{ $announcer->id }}" @selected(old('user_id', $jadwal?->user_id) == $announcer->id)>{{ $announcer->username }}</option>
            @endforeach
        </select>
        @error('user_id')
            <p id="user_id-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="program_title" class="mb-1.5 block text-sm font-medium">Judul program</label>
        <input type="text" id="program_title" name="program_title" value="{{ old('program_title', $jadwal?->program_title) }}" class="{{ $fieldClass }}" @error('program_title') aria-invalid="true" aria-describedby="program_title-error" @enderror>
        @error('program_title')
            <p id="program_title-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-3">
        <div>
            <label for="broadcast_date" class="mb-1.5 block text-sm font-medium">Tanggal siaran</label>
            <input type="date" id="broadcast_date" name="broadcast_date" value="{{ old('broadcast_date', $jadwal?->broadcast_date->format('Y-m-d')) }}" class="{{ $fieldClass }} tabular-nums" @error('broadcast_date') aria-invalid="true" aria-describedby="broadcast_date-error" @enderror>
            @error('broadcast_date')
                <p id="broadcast_date-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="start_time" class="mb-1.5 block text-sm font-medium">Jam mulai</label>
            <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $jadwal ? substr($jadwal->start_time, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('start_time') aria-invalid="true" aria-describedby="start_time-error" @enderror>
            @error('start_time')
                <p id="start_time-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="end_time" class="mb-1.5 block text-sm font-medium">Jam selesai</label>
            <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $jadwal ? substr($jadwal->end_time, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" aria-describedby="end_time-hint @error('end_time') end_time-error @enderror" @error('end_time') aria-invalid="true" @enderror>
            <p id="end_time-hint" class="mt-1.5 text-sm text-muted">Isi 00:00 untuk tengah malam.</p>
            @error('end_time')
                <p id="end_time-error" class="mt-1 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>
    </div>

    <div>
        <label for="notes" class="mb-1.5 block text-sm font-medium">Catatan <span class="font-normal text-muted">(opsional)</span></label>
        <textarea id="notes" name="notes" rows="3" class="{{ $fieldClass }}" @error('notes') aria-invalid="true" aria-describedby="notes-error" @enderror>{{ old('notes', $jadwal?->notes) }}</textarea>
        @error('notes')
            <p id="notes-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex items-center gap-5 border-t border-line pt-5">
    <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">{{ $submitLabel }}</button>
    <a href="{{ route('jadwalSiaran.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Batal</a>
</div>
