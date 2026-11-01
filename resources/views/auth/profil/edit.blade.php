@extends($layout)

@section('title', 'Profil')

@section('content')
    <h1 class="mb-6 text-2xl font-semibold tracking-tight">Profil</h1>

    @include('auth.partials.status')

    @php($fieldClass = 'w-full rounded-md border border-line bg-white px-3 py-2 text-sm text-ink focus:border-signal focus:outline-none focus:ring-2 focus:ring-signal/30 aria-[invalid=true]:border-danger')

    <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="max-w-md space-y-5 rounded-lg border border-line bg-white p-5">
        @csrf
        @method('PUT')

        <div class="flex items-center gap-4">
            @include('auth.partials.avatar', ['user' => $user, 'size' => 'size-20'])
            <div class="min-w-0 flex-1">
                <label for="photo" class="mb-1.5 block text-sm font-medium">Foto profil</label>
                <input type="file" id="photo" name="photo" accept="image/jpeg,image/png,image/webp" class="{{ $fieldClass }}" aria-describedby="photo-help @error('photo') photo-error @enderror" @error('photo') aria-invalid="true" @enderror>
                <p id="photo-help" class="mt-1.5 text-sm text-muted">JPG, PNG, atau WebP. Maksimal 2 MB.</p>
                @error('photo')
                    <p id="photo-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div>
            <label for="username" class="mb-1.5 block text-sm font-medium">Username</label>
            <input type="text" id="username" name="username" value="{{ old('username', $user->username) }}" required class="{{ $fieldClass }}" aria-describedby="username-help @error('username') username-error @enderror" @error('username') aria-invalid="true" @enderror>
            <p id="username-help" class="mt-1.5 text-sm text-muted">Nama ini dipakai untuk login.</p>
            @error('username')
                <p id="username-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <p class="mb-1.5 text-sm font-medium">Jabatan</p>
            <p class="text-sm">{{ $user->role->label }}</p>
        </div>

        <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Simpan profil</button>
    </form>

    <h2 class="mb-3 mt-8 text-lg font-semibold tracking-tight">Ganti password</h2>

    <form method="POST" action="{{ route('profil.password') }}" class="max-w-md space-y-5 rounded-lg border border-line bg-white p-5">
        @csrf
        @method('PUT')

        <div>
            <label for="current_password" class="mb-1.5 block text-sm font-medium">Password saat ini</label>
            <input type="password" id="current_password" name="current_password" required autocomplete="current-password" class="{{ $fieldClass }}" @error('current_password', 'password') aria-invalid="true" aria-describedby="current_password-error" @enderror>
            @error('current_password', 'password')
                <p id="current_password-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password" class="mb-1.5 block text-sm font-medium">Password baru</label>
            <input type="password" id="password" name="password" required autocomplete="new-password" class="{{ $fieldClass }}" @error('password', 'password') aria-invalid="true" aria-describedby="password-error" @enderror>
            @error('password', 'password')
                <p id="password-error" class="mt-1.5 text-sm text-danger">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium">Konfirmasi password baru</label>
            <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="{{ $fieldClass }}">
        </div>

        <button type="submit" class="rounded-md bg-signal-strong px-4 py-2 text-sm font-medium text-white hover:bg-ink focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-signal">Ganti password</button>
    </form>
@endsection
