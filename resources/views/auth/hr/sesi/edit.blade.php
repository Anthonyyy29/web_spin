@extends('auth.hr.layout')

@section('title', 'Ubah Sesi')

@section('content')
    <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Sesi</a>
    <h1 class="mt-1 text-2xl font-semibold tracking-tight">{{ $session->status === \App\Enums\SessionStatus::Postponed ? 'Jadwalkan ulang sesi' : 'Ubah sesi' }}</h1>
    <p class="mt-1 mb-6 text-sm text-muted">{{ $session->program->name }} <span class="tabular-nums">(semula {{ $session->regular_date->locale('id')->translatedFormat('D, d M Y') }})</span></p>

    <form method="POST" action="{{ route('sesi.update', $session) }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8">
        @csrf
        @method('PUT')
        @php($fieldClass = 'w-full rounded-md border border-line bg-white px-3 py-2 text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')
        @php($checkedAnnouncerIds = array_map('intval', (array) old('announcer_ids', $selectedAnnouncerIds ?? [])))
        @php($checkedSoIds = array_map('intval', (array) old('so_ids', $selectedSoIds ?? [])))
        @php($announcerErrors = array_values(array_unique(array_merge($errors->get('announcer_ids'), ...array_values($errors->get('announcer_ids.*'))))))
        @php($soErrors = array_values(array_unique(array_merge($errors->get('so_ids'), ...array_values($errors->get('so_ids.*'))))))

        <div class="space-y-5">
            <div>
                <label for="session_date" class="mb-1.5 block text-sm font-medium">Tanggal sesi</label>
                <input type="date" id="session_date" name="session_date" value="{{ old('session_date', $session->session_date?->format('Y-m-d')) }}" class="{{ $fieldClass }} tabular-nums" @error('session_date') aria-invalid="true" aria-describedby="session_date-error" @enderror>
                @error('session_date')
                    <p id="session_date-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <label for="crewcall_time" class="mb-1.5 block text-sm font-medium">Crewcall</label>
                    <input type="time" id="crewcall_time" name="crewcall_time" value="{{ old('crewcall_time', $session->crewcall_time ? substr($session->crewcall_time, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('crewcall_time') aria-invalid="true" aria-describedby="crewcall_time-error" @enderror>
                    @error('crewcall_time')
                        <p id="crewcall_time-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tapping_start" class="mb-1.5 block text-sm font-medium">Tapping mulai</label>
                    <input type="time" id="tapping_start" name="tapping_start" value="{{ old('tapping_start', $session->tapping_start ? substr($session->tapping_start, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('tapping_start') aria-invalid="true" aria-describedby="tapping_start-error" @enderror>
                    @error('tapping_start')
                        <p id="tapping_start-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="tapping_end" class="mb-1.5 block text-sm font-medium">Tapping selesai</label>
                    <input type="time" id="tapping_end" name="tapping_end" value="{{ old('tapping_end', $session->tapping_end ? substr($session->tapping_end, 0, 5) : null) }}" class="{{ $fieldClass }} tabular-nums" @error('tapping_end') aria-invalid="true" aria-describedby="tapping_end-error" @enderror>
                    @error('tapping_end')
                        <p id="tapping_end-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                    @enderror
                </div>
            </div>

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
        </div>

        <div class="mt-8 flex items-center gap-5 border-t border-line pt-5">
            <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Simpan sesi</button>
            <a href="{{ route('sesi.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Batal</a>
        </div>
    </form>
@endsection
