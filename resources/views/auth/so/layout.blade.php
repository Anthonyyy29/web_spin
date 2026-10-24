@extends('auth.layout')

@section('role', 'Sound operator')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Sesi saya', 'route' => 'so.sesi.index', 'active' => 'so.sesi.*'])
@endsection
