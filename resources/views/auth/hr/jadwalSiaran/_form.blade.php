@php($jadwal = $jadwalSiaran ?? null)

<div class="mb-4">
    <label for="user_id" class="block text-sm font-medium mb-1">Announcer</label>
    <select id="user_id" name="user_id" class="w-full border rounded px-3 py-2">
        <option value="">Pilih announcer</option>
        @foreach ($announcers as $announcer)
            <option value="{{ $announcer->id }}" @selected(old('user_id', $jadwal?->user_id) == $announcer->id)>{{ $announcer->username }}</option>
        @endforeach
    </select>
    @error('user_id')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="program_title" class="block text-sm font-medium mb-1">Judul Program</label>
    <input type="text" id="program_title" name="program_title" value="{{ old('program_title', $jadwal?->program_title) }}" class="w-full border rounded px-3 py-2">
    @error('program_title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="mb-4">
    <label for="broadcast_date" class="block text-sm font-medium mb-1">Tanggal Siaran</label>
    <input type="date" id="broadcast_date" name="broadcast_date" value="{{ old('broadcast_date', $jadwal?->broadcast_date->format('Y-m-d')) }}" class="w-full border rounded px-3 py-2">
    @error('broadcast_date')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    <div>
        <label for="start_time" class="block text-sm font-medium mb-1">Jam Mulai</label>
        <input type="time" id="start_time" name="start_time" value="{{ old('start_time', $jadwal ? substr($jadwal->start_time, 0, 5) : null) }}" class="w-full border rounded px-3 py-2">
        @error('start_time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
    <div>
        <label for="end_time" class="block text-sm font-medium mb-1">Jam Selesai</label>
        <input type="time" id="end_time" name="end_time" value="{{ old('end_time', $jadwal ? substr($jadwal->end_time, 0, 5) : null) }}" class="w-full border rounded px-3 py-2">
        @error('end_time')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mb-6">
    <label for="notes" class="block text-sm font-medium mb-1">Catatan</label>
    <textarea id="notes" name="notes" rows="3" class="w-full border rounded px-3 py-2">{{ old('notes', $jadwal?->notes) }}</textarea>
    @error('notes')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
    @enderror
</div>

<div class="flex items-center gap-3">
    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded hover:bg-indigo-700">Simpan</button>
    <a href="{{ route('jadwalSiaran.index') }}" class="text-sm text-gray-600">Batal</a>
</div>
