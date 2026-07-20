@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="text-3xl font-bold">

    Xin chào {{ auth()->user()->name }}

</div>

@endsection