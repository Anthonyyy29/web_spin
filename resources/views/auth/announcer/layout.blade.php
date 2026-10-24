@extends('auth.layout')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Dashboard', 'route' => 'announcer.dashboard', 'active' => 'announcer.dashboard'])
    @include('auth.partials.nav-link', ['label' => 'Sesi saya', 'route' => 'announcer.sesi.index', 'active' => 'announcer.sesi.*'])
    @include('auth.partials.nav-link', ['label' => 'Profil', 'route' => 'profil.edit', 'active' => 'profil.*'])
@endsection
