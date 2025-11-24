@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
  <h1>Bienvenido al panel administrativo</h1>
  <p>Usuario: {{ Auth::user()->name }}</p>
@endsection
