@extends('auth.hr.layout')

@section('title', 'Ubah Jadwal Siaran')

@section('content')
    <a href="{{ route('jadwalSiaran.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Jadwal Siaran</a>
    <h1 class="mt-1 mb-6 text-2xl font-semibold tracking-tight">Ubah jadwal</h1>

    <form method="POST" action="{{ route('jadwalSiaran.update', $jadwalSiaran) }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8">
        @csrf
        @method('PUT')
        @include('auth.hr.jadwalSiaran._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
