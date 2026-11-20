<div class="mb-3">
    <label class="form-label fw-semibold">Crew sesi ini</label>
    <p class="text-secondary" style="font-size: smaller">Dipakai mencocokkan tap RFID ke sesi. Hanya jabatan (utama atau tambahan) Announcer, Sound Operator, dan Human Resource.</p>
    @foreach ($crewByRole as $role => $crews)
        <div class="mb-2">
            <label class="form-label mb-1">{{ $role }}</label>
            <div class="dropdown crew-dropdown" data-placeholder="Pilih {{ strtolower($role) }}">
                <button type="button" class="form-select text-start d-flex flex-wrap align-items-center gap-1" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                    <span class="crew-chips d-flex flex-wrap gap-1"></span>
                </button>
                <div class="dropdown-menu w-100 p-2" style="max-height: 220px; overflow-y: auto;">
                    @forelse ($crews as $crew)
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input crew-check" id="crew-{{ $prefix }}-{{ Str::slug($role) }}-{{ $crew->id }}" name="crew_ids[]" value="{{ $crew->id }}" data-name="{{ $crew->name }}">
                            <label class="form-check-label w-100" for="crew-{{ $prefix }}-{{ Str::slug($role) }}-{{ $crew->id }}">{{ $crew->name }}</label>
                        </div>
                    @empty
                        <span class="text-secondary small">Belum ada crew {{ $role }}.</span>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>
