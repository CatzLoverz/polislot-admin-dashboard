@extends('Layouts.content_layout')

@section('title') 
    Dashboard - {{ strtoupper($user->role ?? 'Pengguna') }}
@endsection

@section('page_title')
    Selamat Datang {{ $user->name ?? 'Pengguna' }}!
@endsection

