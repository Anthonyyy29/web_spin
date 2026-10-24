@extends('auth.hr.layout')

@section('title', 'Edit Jadwal Siaran')

@section('content')
    <h1 class="text-2xl font-semibold mb-4">Edit Jadwal Siaran</h1>

    <form method="POST" action="{{ route('jadwalSiaran.update', $jadwalSiaran) }}" class="bg-white rounded border p-6 max-w-2xl">
        @csrf
        @method('PUT')
        @include('auth.hr.jadwalSiaran._form')
    </form>
@endsection
