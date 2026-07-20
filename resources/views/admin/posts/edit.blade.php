@extends('layouts.admin')

@section('title', 'Sửa bài viết')

@section('content')
    <h2 class="text-2xl font-bold text-gray-900">
        Sửa bài viết: {{ $post->title }}
    </h2>
@endsection