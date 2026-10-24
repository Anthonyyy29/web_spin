@extends('auth.layout')

@section('nav')
    @include('auth.partials.nav-link', ['label' => 'Sesi saya', 'route' => 'so.sesi.index', 'active' => 'so.sesi.*'])
@endsection
