@extends('Layouts.auth_layout')

@section('title', 'Lupa Password | PoliSlot')

@section('content')
<section class="vh-100">
    <div class="container-fluid h-custom">
        <div class="row d-flex justify-content-center align-items-center h-100">
            <div class="col-md-9 col-lg-6 col-xl-5">
                <img src="{{ asset('assets/img/3d-render-secure-login-password-illustration.jpg') }}" class="img-fluid" alt="Forgot Password Illustration" />
            </div>
            <div class="col-md-8 col-lg-6 col-xl-4 offset-xl-1">

                <form action="{{ route('password.send.otp') }}" method="POST">
                    @csrf
                    <div class="d-flex flex-row align-items-center justify-content-center justify-content-lg-start">
                        <p class="lead fw-normal mb-1 mt-5 me-3">Lupa Password</p>
                    </div>

                    <div class="divider d-flex align-items-center my-4"></div>

                    @if (session('swal_success_crud'))
                        <div class="alert alert-success">{{ session('swal_success_crud') }}</div>
                    @endif
                    @if (session('swal_error_crud'))
                        <div class="alert alert-danger">{{ session('swal_error_crud') }}</div>
                    @endif

                    <div class="form-outline mb-4">
                        <label class="form-label" for="email">Masukkan E-mail Anda</label>
                        <input type="email" id="email" name="email" class="form-control form-control-lg" placeholder="Alamat e-mail terdaftar" required autofocus />
                        @error('email')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="text-left text-lg-start mt-3 pt-2">
                        <button type="submit" class="btn btn-dark form-control btn-lg">
                            Kirim Kode OTP ke Email
                        </button>
                        <p class="small fw-bold mt-4 pt-3 mb-2">
                            Sudah ingat password? <a href="{{ route('login.show') }}" class="link-danger">Masuk di sini</a>
                        </p>
                        <p class="small fw-bold mt-3 pt-1 mb-0">
                            <a href="{{ url('/') }}" class="link-danger">Kembali ke Beranda</a>
                        </p>
                    </div>
                </form>

            </div>
        </div>
    </div>
</section>
@endsection
