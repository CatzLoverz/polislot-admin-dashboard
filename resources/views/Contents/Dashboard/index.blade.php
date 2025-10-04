@extends('Layouts.content_layout')

@section('title') Dashboard
    @if (Auth::check())
        - {{ Auth::user()->role ?? 'Pengguna'}}
    @endif
@endsection

@section('page_title')
    Selamat Datang {{ Auth::user()->name ?? 'Pengguna' }}!
@endsection

