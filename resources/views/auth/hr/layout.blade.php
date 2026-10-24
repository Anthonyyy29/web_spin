@extends('auth.layout')

@section('role', 'HR')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Dashboard', 'route' => 'hr.dashboard', 'active' => 'hr.dashboard'])
    @include('auth.partials.nav-link', ['label' => 'Jadwal Siaran', 'route' => 'jadwalSiaran.index', 'active' => 'jadwalSiaran.*'])
    @include('auth.partials.nav-link', ['label' => 'Absensi', 'route' => null, 'active' => null])
    @include('auth.partials.nav-link', ['label' => 'Pengaturan', 'route' => null, 'active' => null])
@endsection
