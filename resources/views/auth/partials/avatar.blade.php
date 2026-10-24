{{-- Params: $user, $size (kelas ukuran Tailwind, mis. size-8) --}}
@if ($user->photo_path)
    <img src="{{ Storage::url($user->photo_path) }}" alt="" class="{{ $size }} shrink-0 rounded-full object-cover">
@else
    <span aria-hidden="true" class="{{ $size }} inline-flex shrink-0 items-center justify-center rounded-full bg-canvas font-medium text-muted ring-1 ring-line">{{ mb_strtoupper(mb_substr($user->username, 0, 1)) }}</span>
@endif
