<!DOCTYPE html>
<html lang="en">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'PoliSlot')</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('assets/img/PoliSlot Pin.png') }}" type="image/x-icon" />
    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"> </script>
    <script>
        WebFont.load({
            google: { families: ["Lato:300,400,700,900"] },
            custom: { families: ["Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular", "Font Awesome 5 Brands", "simple-line-icons"], urls: ["{{ asset('assets/css/fonts.min.css') }}"] },
            active: function () {
                sessionStorage.fonts = true;
            },
        });
    </script>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/atlantis.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" />
    <style>
        html, body {
            height: 100%;
        }
        .main-section-wrapper {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .content-wrap {
            flex: 1 0 auto;
            padding-top: 20px; /* Padding atas */
            padding-bottom: 20px; /* Padding bawah */
        }
        .footer-section {
            flex-shrink: 0;
        }

        /* Styles from register_layout specific to registration form structure */
        .role-fields {
            margin-top: 20px;
            padding: 15px;
            border: 1px solid #eee;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .card-header-custom {
            background-color: #007bff;
            color: white;
            padding: 0.75rem 1.25rem;
            margin-bottom: 0;
            border-bottom: 1px solid rgba(0,0,0,.125);
            border-radius: calc(.25rem - 1px) calc(.25rem - 1px) 0 0;
            text-align: center;
        }
        .form-section-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #333;
            border-bottom: 1px solid #ddd;
            padding-bottom: 0.5rem;
        }
        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
            border-width: .2em;
        }
        .loading-indicator {
            display: none; /* Hidden by default */
            margin-left: 10px;
        }

        /* Styles from login_layout specific to login form structure */
        .divider:after,
        .divider:before {
            content: "";
            flex: 1;
            height: 1px;
            background: #eee;
        }
        .h-custom { /* specific to login page design */
            height: calc(100% - 73px);
        }
        @media (max-width: 450px) {
            .h-custom {
                height: 100%;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <div class="main-section-wrapper">
        {{-- Wrapper .container bisa ditambahkan di sini jika umum, atau di yield content jika spesifik per halaman --}}
        <div class="content-wrap @hasSection('use_container') container @endif">
             @yield('content')
        </div>

        <footer class="footer-section d-flex flex-column flex-md-row text-center text-md-start justify-content-between py-4 px-4 px-xl-5 bg-dark">
            <div class="text-white copyright ml-auto">made with <i class="fa fa-heart heart text-danger"></i> by PBL-TRPL 303</div>
        </footer>
    </div>

    <script src="{{ asset('assets/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/atlantis.min.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('swal_success_crud'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('swal_success_crud') }}",
                    icon: 'success',
                    timer: 2000,
                    showConfirmButton: false, 
                    timerProgressBar: true
                });
            @endif
            @if (session('swal_error_crud')) 
                Swal.fire({
                    title: 'Gagal!',
                    text: "{{ session('swal_error_crud') }}",
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            @endif

            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const toggleIcon = document.getElementById('togglePasswordIcon');

            if (togglePassword && passwordInput && toggleIcon) {
                togglePassword.addEventListener('click', function () {
                    const isPassword = passwordInput.type === 'password';
                    passwordInput.type = isPassword ? 'text' : 'password';
                    if (passwordConfirmationInput) {
                        passwordConfirmationInput.type = isPassword ? 'text' : 'password';
                    }
                    toggleIcon.classList.toggle('fa-eye');
                    toggleIcon.classList.toggle('fa-eye-slash');
                });
            }
        });
    </script>
    @stack('scripts')
</body>
</html>