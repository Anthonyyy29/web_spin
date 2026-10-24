@extends('auth.hr.layout')

@section('title', 'Tambah Jadwal Siaran')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Tambah Jadwal Siaran</h1>

    <form method="POST" action="{{ route('jadwalSiaran.store') }}" class="bg-white rounded border p-6 max-w-2xl">
        @csrf
        @include('auth.hr.jadwalSiaran._form')
    </form>
@endsection
