@extends('auth.hr.layout')

@section('title', 'Tambah Jadwal Siaran')

@section('content')
    <a href="{{ route('jadwalSiaran.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Jadwal Siaran</a>
    <h1 class="mt-1 mb-6 text-2xl font-semibold tracking-tight">Tambah jadwal</h1>

    <form method="POST" action="{{ route('jadwalSiaran.store') }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8">
        @csrf
        @include('auth.hr.jadwalSiaran._form', ['submitLabel' => 'Simpan jadwal'])
    </form>
@endsection
