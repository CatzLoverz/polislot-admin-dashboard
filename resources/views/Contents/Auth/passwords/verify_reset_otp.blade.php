@extends('Layouts.auth_layout')

@section('title', 'Verifikasi OTP Reset Password')
@section('use_container', true)

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
        /* Hapus panah di input number */
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

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="otp-form-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="card-title">Verifikasi OTP Reset Password</h4>
                    <p class="card-category mb-0">Masukkan 6 digit kode OTP yang dikirim ke email Anda.</p>
                </div>
                <div class="card-body">
                    <form id="otp-form" method="POST" action="{{ route('password.otp.verify') }}">
                        @csrf
                        <div class="form-group">
                            <label for="otp" class="mb-3">Kode OTP</label>
                            <div class="otp-input-container">
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                                <input name="otp_digits[]" type="number" class="form-control otp-input" required>
                            </div>
                        </div>

                        <input type="hidden" name="otp" id="otp-value">

                        <div class="form-group mt-4">
                            <button type="submit" class="btn btn-primary btn-block btn-round">
                                <span class="spinner-border spinner-border-sm loading-indicator" role="status" aria-hidden="true"></span>
                                Verifikasi OTP
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <form method="POST" action="{{ route('resendResetOtp') }}" style="display: inline;">
                            @csrf
                            <p>Tidak menerima kode? 
                                <button type="submit" class="btn btn-link p-0">Kirim Ulang</button>
                            </p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

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
            
            if (otp.length === 6) {
                otpValueInput.value = otp;
                // Tampilkan loading indicator saat submit
                loadingIndicator.style.display = 'inline-block';
                submitButton.disabled = true;
                return true; // Lanjutkan submit
            } else {
                e.preventDefault(); // Hentikan submit jika OTP tidak lengkap
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
