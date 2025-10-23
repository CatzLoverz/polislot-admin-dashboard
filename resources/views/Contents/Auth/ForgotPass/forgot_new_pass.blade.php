@extends('Layouts.auth_layout')

@section('title', 'Reset Password | PoliSlot')

@push('styles')
<style>
    .reset-password-container {
        max-width: 480px;
        margin: 60px auto;
    }

    .card {
        border-radius: 12px;
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
    }

    .card-header {
        background-color: #fff;
        border-bottom: none;
        text-align: center;
    }

    .card-title {
        font-weight: 700;
        font-size: 1.5rem;
    }

    .form-label {
        font-weight: 500;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-center align-items-center min-vh-100 bg-light">
    <div class="reset-password-container">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Reset Password</h4>
                <p class="text-muted mb-0">Silakan buat password baru Anda.</p>
            </div>

            <div class="card-body">
                <form action="{{ route('reset_pass.attempt') }}" method="POST">
                    @csrf

                    {{-- Pesan Notifikasi --}}
                    @if (session('swal_success_crud'))
                        <div class="alert alert-success">{{ session('swal_success_crud') }}</div>
                    @endif
                    @if (session('swal_error_crud'))
                        <div class="alert alert-danger">{{ session('swal_error_crud') }}</div>
                    @endif

                    {{-- Password Baru --}}
                    <div class="form-group mb-3">
                        <label for="password" class="form-label">Password Baru</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control form-control-lg" 
                               placeholder="Masukkan password baru" 
                               required />
                        @error('password')
                            <div class="text-danger small mt-1">{{ $message }}</div>
                        @enderror
                        <a href="#" class="text-muted small mt-1 d-inline-block" 
                           data-toggle="tooltip" 
                           title="Kata sandi harus minimal 8 karakter, mengandung huruf besar, huruf kecil, angka, dan simbol.">
                            Lihat syarat kata sandi
                        </a>
                    </div>

                    {{-- Konfirmasi Password --}}
                    <div class="form-group mb-4">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               class="form-control form-control-lg" 
                               placeholder="Ulangi password baru" 
                               required />
                    </div>

                    {{-- Tombol Simpan --}}
                    <div class="form-group text-center">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            Simpan Password Baru
                        </button>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('login.form') }}" class="text-danger small">
                            Kembali ke Login
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('swal_success_crud'))
        Swal.fire({
            icon: 'success',
            title: 'Berhasil!',
            text: '{{ session('swal_success_crud') }}',
            timer: 2500,
            showConfirmButton: false
        });
    @endif

    @if (session('swal_error_crud'))
        Swal.fire({
            icon: 'error',
            title: 'Gagal!',
            text: '{{ session('swal_error_crud') }}',
            confirmButtonText: 'OK'
        });
    @endif
});
</script>
@endpush
