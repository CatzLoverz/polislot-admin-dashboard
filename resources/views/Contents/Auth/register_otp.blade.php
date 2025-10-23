@extends('Layouts.auth_layout')

{{-- Judul Halaman --}}
@section('title', 'Verifikasi OTP')

{{-- Menggunakan class .container pada content-wrap --}}
@section('use_container', true)

{{-- CSS Khusus untuk Halaman OTP --}}
@push('styles')
<style>
    .otp-form-container {
        max-width: 500px;
        margin: 40px auto;
    }
    .otp-input-container {
        display: flex;
        justify-content: space-between;
        gap: 10px;
    }
    .otp-input {
        width: 50px;
        height: 60px;
        text-align: center;
        font-size: 1.75rem;
        font-weight: 600;
        border: 1px solid #ddd;
        border-radius: 8px;
        background-color: #f8f9fa;
        transition: all 0.2s ease-in-out;
        -moz-appearance: textfield;
    }
    .otp-input::-webkit-inner-spin-button,
    .otp-input::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .otp-input:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        outline: none;
    }
    .card-title {
        font-weight: 700;
    }
</style>
@endpush


{{-- Konten Utama Halaman --}}
@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="otp-form-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="card-title">Verifikasi Kode OTP Registrasi</h4>
                    <p class="card-category mb-0">Masukkan 6 digit kode yang telah kami kirimkan ke Email Anda.</p>
                </div>
                <div class="card-body">
                    {{-- Ganti action dengan route yang sesuai di proyek Anda --}}
                    <form id="otp-form" method="POST" action="{{ route('register_otp.verify') }}">
                        @csrf
                        {{-- Tambahkan input lain jika diperlukan, contoh: email atau no. hp --}}
                        {{-- <input type="hidden" name="email" value="{{ $email }}"> --}}
                        
                        <div class="form-group">
                            <label for="otp" class="mb-3">Kode Verifikasi</label>
                            <div class="otp-input-container">
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                            </div>
                        </div>

                        {{-- Hidden input untuk menampung kode OTP gabungan --}}
                        <input type="hidden" name="otp" id="otp-value">

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block btn-round">
                                <span class="spinner-border spinner-border-sm loading-indicator" role="status" aria-hidden="true"></span>
                                Verifikasi
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                         {{-- Ganti action dengan route yang sesuai di proyek Anda --}}
                        <form method="POST" action="{{ route('register_otp.resend') }}" style="display: inline;">
                            @csrf
                            {{-- <input type="hidden" name="email" value="{{ $email }}"> --}}
                            <p>Tidak menerima kode? <button type="submit" class="btn btn-link p-0">Kirim Ulang</button></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection


{{-- JavaScript Khusus untuk Halaman OTP --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('otp-form');
        const inputs = [...form.querySelectorAll('.otp-input')];
        const otpValueInput = document.getElementById('otp-value');
        const submitButton = form.querySelector('button[type="submit"]');
        const loadingIndicator = form.querySelector('.loading-indicator');

        inputs.forEach((input, index) => {
            // Batasi input hanya 1 digit
            input.addEventListener('input', () => {
                if (input.value.length > 1) {
                    input.value = input.value.slice(0, 1);
                }
                // Jika sudah ada isinya, pindah ke input selanjutnya
                if (input.value.length >= 1 && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                // Jika menekan backspace di input yang kosong, pindah ke input sebelumnya
                if (e.key === 'Backspace' && input.value.length === 0 && index > 0) {
                    inputs[index - 1].focus();
                }
            });

             // Memungkinkan paste kode OTP
             input.addEventListener('paste', (e) => {
                e.preventDefault();
                const pasteData = e.clipboardData.getData('text').trim();
                if (/^\d{6}$/.test(pasteData)) {
                    inputs.forEach((box, i) => {
                        box.value = pasteData[i];
                    });
                    inputs[5].focus(); // Fokus ke input terakhir
                }
            });
        });

        form.addEventListener('submit', function(e) {
            // Gabungkan semua digit sebelum form disubmit
            const otp = inputs.map(input => input.value).join('');
            
            // Memastikan semua 6 digit telah diisi
            if (otp.length === 6) {
                otpValueInput.value = otp;
                loadingIndicator.style.display = 'inline-block';
                submitButton.disabled = true;
                return true;
            } else {
                //Jika tidak lengkap menampilkan alert
                e.preventDefault(); 
                Swal.fire({
                    title: 'Gagal!',
                    text: "Silakan masukkan 6 digit kode OTP dengan lengkap.",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        });
    });
</script>
@endpush