@extends('auth.layout')

@section('role', 'Announcer')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Profile', 'route' => 'announcer.dashboard', 'active' => 'announcer.dashboard'])
    @include('auth.partials.nav-link', ['label' => 'Jadwal Siaran', 'route' => 'announcer.jadwalSiaran', 'active' => 'announcer.jadwalSiaran'])
    @include('auth.partials.nav-link', ['label' => 'Absensi', 'route' => null, 'active' => null])
@endsection
