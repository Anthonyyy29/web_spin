@extends('auth.hr.layout')

@section('title', 'Ubah Program')

@section('content')
    <a href="{{ route('program.index') }}" class="text-sm text-muted underline-offset-4 hover:text-ink hover:underline">Program</a>
    <h1 class="mt-1 mb-6 text-2xl font-semibold tracking-tight">Ubah program</h1>

    <form method="POST" action="{{ route('program.update', $program) }}" class="max-w-2xl rounded-lg border border-line bg-white p-6 md:p-8">
        @csrf
        @method('PUT')
        @include('auth.hr.program._form', ['submitLabel' => 'Simpan perubahan'])
    </form>
@endsection
