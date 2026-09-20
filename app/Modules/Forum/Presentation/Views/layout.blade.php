@extends('library::layout')

@section('site-section', 'Форум')
@section('body-class', 'forum-screen')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/forum.css') }}?v={{ filemtime(public_path('css/forum.css')) }}">
@endpush

@section('sidebar')
    @include('forum::_sidebar')
@endsection
