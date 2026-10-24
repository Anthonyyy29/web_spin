@extends('auth.layout')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Profile', 'route' => 'announcer.dashboard', 'active' => 'announcer.dashboard'])
    @include('auth.partials.nav-link', ['label' => 'Sesi saya', 'route' => 'announcer.sesi.index', 'active' => 'announcer.sesi.*'])
@endsection
