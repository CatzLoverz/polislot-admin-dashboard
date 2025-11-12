@extends('Layouts.auth_layout')

@section('title', 'Reset Password | PoliSlot')

@push('styles')
    <style>
        .password-criteria-list {
            list-style-type: none;
            padding-left: 0;
            margin-top: 10px;
        }

        .criteria-item {
            color: #6c757d;
            /* Warna default (abu-abu) */
            font-size: 0.875rem;
            transition: color 0.3s ease;
            margin-bottom: 4px;
            /* Jarak antar item */
        }

        .criteria-item i {
            margin-right: 8px;
            width: 16px;
            /* Menyamakan lebar ikon */
            text-align: center;
            transition: color 0.3s ease;
        }
        
        .criteria-item.text-success {
            color: #28a745 !important;
            /* Warna valid (hijau) */
            font-weight: 500;
        }

        .criteria-item.text-muted {
            color: #6c757d !important;
        }

        .form-label {
            font-weight: 500;
        }
    </style>
@endpush

@section('content')
    <div class="container mt-5 mb-5">
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="card shadow-lg">
                    <div class="card-header card-header-custom">
                        <h4 class="card-title mb-0">Reset Password</h4>
                    </div>

                    <div class="card-body p-4">
                        <p class="text-muted mb-3">Silakan buat password baru Anda.</p>

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

                        @if (session('swal_success_crud'))
                            <div class="alert alert-success">{{ session('swal_success_crud') }}</div>
                        @endif
                        @if (session('swal_error_crud'))
                            <div class="alert alert-danger">{{ session('swal_error_crud') }}</div>
                        @endif


                        <form action="{{ route('reset_pass.attempt') }}" method="POST">
                            @csrf
                            <div class="form-group mb-3">
                                <label for="password" class="form-label">Password Baru</label>
                                <input type="password" id="password" name="password"
                                    class="form-control form-control-lg @error('password') is-invalid @enderror"
                                    placeholder="Masukkan password baru" required autocomplete="new-password" />
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group" style="margin-top: -10px; margin-bottom: 15px;">
                                <div class="row">
                                    <div class="col-6 pr-1">
                                        <ul class="password-criteria-list">
                                            <li class="criteria-item" id="rule-length">
                                                <i class="fas fa-times-circle"></i> Minimal 8 karakter
                                            </li>
                                            <li class="criteria-item" id="rule-uppercase">
                                                <i class="fas fa-times-circle"></i> Mengandung Huruf besar (A-Z)
                                            </li>
                                            <li class="criteria-item" id="rule-lowercase">
                                                <i class="fas fa-times-circle"></i> Mengandung Huruf kecil (a-z)
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-6 pr-1">
                                        <ul class="password-criteria-list">
                                            <li class="criteria-item" id="rule-number">
                                                <i class="fas fa-times-circle"></i> Mengandung Angka (0-9)
                                            </li>
                                            <li class="criteria-item" id="rule-symbol">
                                                <i class="fas fa-times-circle"></i> Mengandung Simbol (!@#$...)
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                <input type="password" id="password_confirmation" name="password_confirmation"
                                    class="form-control form-control-lg" placeholder="Ulangi password baru" required
                                    autocomplete="new-password" />
                                <div id="confirmation-feedback" class="form-text mt-1"></div>
                            </div>

                            <div class="form-group text-center mt-4">
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
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {

            /**
             * FITUR 1: INDIKATOR KRITERIA PASSWORD
             * Diambil dari index.blade.php (Fitur 3)
             * Disesuaikan untuk forgot_new_pass.blade.php
             */
            function setupPasswordStrength() {
                // ID disesuaikan dari 'new_password' -> 'password'
                const newPasswordInput = document.getElementById('password');
                if (!newPasswordInput) return;

                // ID disesuaikan dengan HTML di atas
                const rules = {
                    length: document.getElementById('rule-length'),
                    uppercase: document.getElementById('rule-uppercase'),
                    lowercase: document.getElementById('rule-lowercase'),
                    number: document.getElementById('rule-number'),
                    symbol: document.getElementById('rule-symbol'),
                    // 'previous' dan 'current_password' dihapus karena tidak relevan
                };

                // Fungsi helper privat (diambil langsung dari index.blade.php)
                // Disesuaikan untuk menggunakan base class 'criteria-item'
                function validateRule(element, isValid) {
                    if (!element) return;
                    if (isValid) {
                        element.className = 'criteria-item text-success';
                        element.querySelector('i').className = 'fas fa-check-circle mr-1';
                    } else {
                        element.className = 'criteria-item text-muted';
                        element.querySelector('i').className = 'fas fa-times-circle mr-1';
                    }
                }

                const validateAllPasswordRules = () => {
                    const password = newPasswordInput.value;

                    validateRule(rules.length, password.length >= 8);
                    validateRule(rules.uppercase, /[A-Z]/.test(password));
                    validateRule(rules.lowercase, /[a-z]/.test(password));
                    validateRule(rules.number, /\d/.test(password));
                    validateRule(rules.symbol, /[\W_]/.test(password));
                };

                newPasswordInput.addEventListener('keyup', validateAllPasswordRules);
            }

            function setupRealtimePasswordConfirmation() {
                // ID disesuaikan
                const newPasswordInput = document.getElementById('password');
                const newPasswordConfirmationInput = document.getElementById('password_confirmation');
                
                if (!newPasswordInput || !newPasswordConfirmationInput) return;

                const confirmationFeedback = document.getElementById('confirmation-feedback');
                const checkPasswordMatch = () => {
                    if (!confirmationFeedback) return;
                    if (newPasswordConfirmationInput.value.length === 0 && newPasswordInput.value.length === 0) {
                        confirmationFeedback.innerHTML = ''; return;
                    }
                    if (newPasswordInput.value === newPasswordConfirmationInput.value) {
                        confirmationFeedback.innerHTML = '<i class="fas fa-check-circle"></i> Password cocok.';
                        confirmationFeedback.className = 'form-text text-success mt-1';
                    } else {
                        confirmationFeedback.innerHTML = '<i class="fas fa-times-circle"></i> Password tidak cocok.';
                        confirmationFeedback.className = 'form-text text-danger mt-1';
                    }
                };

                newPasswordInput.addEventListener('keyup', checkPasswordMatch);
                newPasswordConfirmationInput.addEventListener('keyup', checkPasswordMatch);
            }

            setupPasswordStrength();
            setupRealtimePasswordConfirmation();

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