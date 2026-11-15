<x-layouts.dashboard :navmenu="$navmenu">

<div class="container">
    <section class="quickaccess">
        <h5 class="fw-semibold mb-3">Crew per Program</h5>

        @forelse ($programs as $program)
            <form action="{{ url('dashboard/absensi/program/update') }}" method="post" class="card mb-3">
                @csrf
                <input type="hidden" name="content_id" value="{{ $program->id }}">

                <div class="card-header fw-bold">{{ $program->title }}</div>

                <div class="card-body">
                    <div class="row">
                        @foreach ($crew_list as $crew)
                            <div class="col-md-4 col-sm-6">
                                <label class="form-check">
                                    <input type="checkbox" class="form-check-input" name="crew_ids[]"
                                        value="{{ $crew->id }}"
                                        @checked($program->crews->contains($crew->id))>
                                    {{ $crew->name }} - {{ $crew->role }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card-footer text-end">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        @empty
            <p class="text-center">Belum ada program</p>
        @endforelse
    </section>
</div>

</x-layouts.dashboard>
