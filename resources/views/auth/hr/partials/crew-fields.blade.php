{{-- Fieldset announcer dan SO. Param: $announcers, $sos, $selectedAnnouncerIds, $selectedSoIds --}}
@php($checkedAnnouncerIds = array_map('intval', (array) old('announcer_ids', $selectedAnnouncerIds ?? [])))
@php($checkedSoIds = array_map('intval', (array) old('so_ids', $selectedSoIds ?? [])))
@php($announcerErrors = array_values(array_unique(array_merge($errors->get('announcer_ids'), ...array_values($errors->get('announcer_ids.*'))))))
@php($soErrors = array_values(array_unique(array_merge($errors->get('so_ids'), ...array_values($errors->get('so_ids.*'))))))

<fieldset @if ($announcerErrors) aria-describedby="announcer_ids-error" @endif>
    <legend class="mb-1.5 block text-sm font-medium">Announcer</legend>
    <div class="max-h-48 space-y-1 overflow-y-auto rounded-md border border-line p-3">
        @forelse ($announcers as $announcer)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="announcer_ids[]" value="{{ $announcer->id }}" @checked(in_array($announcer->id, $checkedAnnouncerIds, true)) class="size-4 rounded border-line accent-signal-strong">
                {{ $announcer->username }}
            </label>
        @empty
            <p class="text-sm text-muted">Belum ada pengguna dengan peran announcer.</p>
        @endforelse
    </div>
    @foreach ($announcerErrors as $message)
        <p @if ($loop->first) id="announcer_ids-error" @endif class="mt-1.5 text-sm text-danger">{{ $message }}</p>
    @endforeach
</fieldset>

<fieldset @if ($soErrors) aria-describedby="so_ids-error" @endif>
    <legend class="mb-1.5 block text-sm font-medium">Sound operator (SO) <span class="font-normal text-muted">(opsional)</span></legend>
    <div class="max-h-48 space-y-1 overflow-y-auto rounded-md border border-line p-3">
        @forelse ($sos as $so)
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="so_ids[]" value="{{ $so->id }}" @checked(in_array($so->id, $checkedSoIds, true)) class="size-4 rounded border-line accent-signal-strong">
                {{ $so->username }}
            </label>
        @empty
            <p class="text-sm text-muted">Belum ada pengguna dengan peran SO.</p>
        @endforelse
    </div>
    @foreach ($soErrors as $message)
        <p @if ($loop->first) id="so_ids-error" @endif class="mt-1.5 text-sm text-danger">{{ $message }}</p>
    @endforeach
</fieldset>
