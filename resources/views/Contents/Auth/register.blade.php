@extends('Layouts.auth_layout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg">
                <div class="card-header card-header-custom">
                    <h4 class="card-title mb-0">Buat Akun Baru</h4>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <strong class="font-weight-bold">Terjadi Kesalahan! Periksa kembali:</strong>
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('register.attempt') }}" method="POST">
                        @csrf
                    {{-- Common Fields --}}
                        <div class="form-group">
                            <label for="name">Nama Lengkap</label>
                            <input type="text" id="name" name="name" class="form-control form-control-lg" placeholder="Masukkan Nama Lengkap" value="{{ old('name') }}" required>
                        </div>

                        <div class="form-group">
                            <label for="email">E-mail</label>
                            <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder="Masukkan E-mail Aktif" value="{{ old('email') }}" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password">Kata Sandi</label>
                                    <div class="input-group">
                                        <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Masukkan Kata Sandi" required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                            <i class="fa fa-eye" id="togglePasswordIcon"></i>
                                        </button>
                                    </div>
                                    <a href="#" class="text-muted small mt-1" data-toggle="tooltip" title="Kata sandi harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.">Lihat syarat kata sandi</a>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="password_confirmation">Konfirmasi Kata Sandi</label>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control form-control-lg" placeholder="Ulangi Kata Sandi" required>
                                </div>
                            </div>
                        </div>
                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-dark btn-lg btn-block">Daftar</button>
                        </div>
                        <div class="text-center mt-3">
                            <p>Sudah punya akun? <a href="{{ route('login.form') }}">Masuk disini</a></p>
                            <p><a href="{{ url('/') }}">Kembali ke Beranda</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection