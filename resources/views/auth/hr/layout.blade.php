@extends('auth.layout')

@section('role', 'HR')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Dashboard', 'route' => 'hr.dashboard', 'active' => 'hr.dashboard'])
    @include('auth.partials.nav-link', ['label' => 'Program', 'route' => 'program.index', 'active' => 'program.*'])
    @include('auth.partials.nav-link', ['label' => 'Sesi', 'route' => 'sesi.index', 'active' => 'sesi.*'])
    @include('auth.partials.nav-link', ['label' => 'Absensi', 'route' => 'absensi.index', 'active' => 'absensi.*'])
    @include('auth.partials.nav-link', ['label' => 'Pengaturan', 'route' => null, 'active' => null])
@endsection
