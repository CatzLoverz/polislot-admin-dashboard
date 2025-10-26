@extends('Layouts.auth_layout') {{-- Sesuaikan path jika layout Anda berbeda --}}

@section('title', 'Masuk | PoliSlot')

@section('content')
<section class="vh-100">
    <div class="container-fluid h-custom">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="{{ asset("assets/img/3d-render-secure-login-password-illustration.jpg") }}" class="img-fluid" alt="Sample image" />
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">
                <form action="{{ url('/login-attempt') }}" method="POST">
                    @csrf
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <p class="lead fw-normal mb-1 mt-5 me-3">Masuk ke Akun Anda</p>
                    </div>

                    <div class="divider d-flex align-items-center my-4"></div>

                    {{-- Pesan Error Global --}}
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                        </div>
                    @endif
                    {{-- Digunakan untuk menampilkan error 'auth.failed' --}}
                    @if ($errors->has('auth_error')) 
                        <div class="alert alert-danger">
                            {{ $errors->first('auth_error') }}
                        </div>
                    @endif

                    <div class="form-outline mb-4">
                        <label class="form-label" for="email">E-mail</label>
                        <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder="Masukkan e-mail" value="{{ old('email') }}" required autofocus/>
                        @error('email') {{-- Error spesifik untuk email jika validasi Laravel gagal --}}
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-outline mb-3">
                        <label class="form-label" for="password">Password</label>
                        <div class="input-group">
                            <input type="password" id="password" name="password" class="form-control form-control-lg" placeholder="Masukkan Password" required/>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword" tabindex="-1">
                                <i class="fa fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror

                        {{-- 🔹 Tambahan tombol "Lupa Password?" --}}
                        <div class="text-end mt-2">
                            <a href="{{ route('forgot.form') }}" class="small fw-bold mt-3 pt-1 mb-0 link-danger">Lupa Password?</a>
                        </div>
                    </div>

                    <div class="text-left text-lg-start mt-3 pt-2">
                        <button type="submit" class="btn btn-dark form-control btn-lg" style="padding-left: 2.5rem; padding-right: 2.5rem;">Masuk</button>
                        <p class="small fw-bold mt-4 pt-3 mb-2">Tidak punya akun? <a href="{{ route('register.form') }}" class="link-danger">Klik disini</a></p>
                        <p class="small fw-bold mt-3 pt-1 mb-0"> <a href="{{ url('/') }}" class="link-danger">Kembali ke Beranda</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection