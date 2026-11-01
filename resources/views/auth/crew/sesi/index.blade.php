@extends($layout)

@section('title', 'Sesi saya')

@section('content')
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">Sesi saya</h1>
        <p class="mt-1 text-sm text-muted">Sesi siaran tempat kamu bertugas. Check-in dibuka 15 menit sebelum crewcall dan ditutup saat tapping dimulai.</p>
    </div>

    @include('auth.partials.status')

    @error('checkIn')
        <div role="alert" class="mb-4 rounded-md border border-danger/40 bg-white px-4 py-3 text-sm text-danger">
            <span class="mr-2 inline-block size-2 rounded-full bg-danger align-middle" aria-hidden="true"></span>{{ $message }}
        </div>
    @enderror

    <section aria-labelledby="upcoming-heading" class="mb-8">
        <h2 id="upcoming-heading" class="mb-3 text-lg font-semibold tracking-tight">Mendatang</h2>
        @include('auth.crew.sesi.table', ['sessions' => $upcomingSessions, 'emptyMessage' => 'Belum ada sesi mendatang.'])
    </section>

    <section aria-labelledby="history-heading">
        <h2 id="history-heading" class="mb-3 text-lg font-semibold tracking-tight">Riwayat</h2>
        @include('auth.crew.sesi.table', ['sessions' => $historySessions, 'emptyMessage' => 'Belum ada riwayat sesi.'])

        <div class="mt-4">
            {{ $historySessions->links() }}
        </div>
    </section>
@endsection
