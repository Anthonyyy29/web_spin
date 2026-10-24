@extends('auth.layout')

@section('role', 'Announcer')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Profile', 'route' => 'announcer.dashboard', 'active' => 'announcer.dashboard'])
    @include('auth.partials.nav-link', ['label' => 'Sesi saya', 'route' => null, 'active' => null])
@endsection
