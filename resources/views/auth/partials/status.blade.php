@if (session('status'))
    <div role="status" class="mb-4 rounded-md border border-line bg-white px-4 py-3 text-sm">
        <span class="mr-2 inline-block size-2 rounded-full bg-emerald-600 align-middle" aria-hidden="true"></span>{{ session('status') }}
    </div>
@endif
