<!DOCTYPE html>
<html lang="en">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <title>@yield('title', 'Dashboard') | PoliSlot</title>
    <meta content="width=device-width, initial-scale=1.0, shrink-to-fit=no" name="viewport" />
    <link rel="icon" href="{{ asset('assets/img/Polibatam.png') }}" type="image/x-icon" />

    <script src="{{ asset('assets/js/plugin/webfont/webfont.min.js') }}"></script>
    <script>
        WebFont.load({
            google: {
                families: ["Lato:300,400,700,900"]
            },
            custom: {
                families: [
                    "Flaticon", "Font Awesome 5 Solid", "Font Awesome 5 Regular",
                    "Font Awesome 5 Brands", "simple-line-icons"
                ],
                urls: ["{{ asset('assets/css/fonts.min.css') }}"]
            },
            active: function() {
                sessionStorage.fonts = true;
            }
        });
    </script>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/atlantis.min.css') }}" />
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}" /> --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/zxcvbn/4.4.2/zxcvbn.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @stack('styles')
</head>

<body>
    <div class="wrapper">
        {{-- Header --}}
        <div class="main-header">
            <div class="logo-header" data-background-color="dark">
                <a href="#" class="logo"> <img src="{{ asset('assets/img/PoliSlot.png') }}" alt="Polibatam Logo"
                        class="navbar-brand" style="width: 195px; height: 40px;" />
                </a>
                <button class="navbar-toggler sidenav-toggler ml-auto" type="button" data-toggle="collapse"
                    data-target="collapse" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"><i class="icon-menu"></i></span>
                </button>
            </div>
            {{-- Navbar --}}
            <nav class="navbar navbar-header navbar-expand-lg" data-background-color="dark">
                <div class="container-fluid">
                    <ul class="navbar-nav topbar-nav ml-md-auto align-items-center">
                        <li class="nav-item dropdown hidden-caret">
                            <a class="dropdown-toggle profile-pic" data-toggle="dropdown" href="#" aria-expanded="false">
                                <div class="avatar-sm">
                                    <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/default_avatar.jpg') }}"
                                        alt="User Avatar" class="avatar-img rounded-circle" />
                                </div>
                            </a>
                            <ul class="dropdown-menu dropdown-user animated fadeIn">
                                <div class="dropdown-user-scroll scrollbar-outer">
                                    <li>
                                        <div class="user-box">
                                            <div class="avatar-lg">
                                                <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/default_avatar.jpg') }}"
                                                    alt="Profile Avatar" class="avatar-img rounded" />
                                            </div>
                                            <div class="u-text">
                                                <h4>{{ Auth::user()->name ?? 'Nama Pengguna' }}</h4>
                                                <p class="text-muted">
                                                    {{ strtoupper(Auth::user()->role ?? 'Pengguna') }}
                                                </p>
                                                <a href="{{ route('profile.edit', Auth::user()->id) }}" class="btn btn-xs btn-dark btn-sm">Lihat Profil</a> </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="{{ route('dashboard') }}">Dashboard</a> 
                                        <div class="dropdown-divider"></div>
                                            <a class="dropdown-item" href="#"
                                            onclick="event.preventDefault(); document.getElementById('logout-form-dropdown').submit();">
                                            Keluar
                                        </a>
                                        <form id="logout-form-dropdown" action="{{ route('logout') }}" method="POST"
                                            style="display: none;"> @csrf
                                        </form>
                                    </li>
                                </div>
                            </ul>
                        </li>
                    </ul>
                </div>
            </nav>
        </div>
        {{-- Akhir Header --}}

        {{-- Sidebar --}}
        <div class="sidebar sidebar-style-2">
            <div class="sidebar-wrapper scrollbar scrollbar-inner">
                <div class="sidebar-content">
                    <div class="user">
                        <div class="avatar-sm float-left mr-2">
                            <img src="{{ Auth::user()->avatar ? asset('storage/' . Auth::user()->avatar) : asset('assets/img/default_avatar.jpg') }}"
                                alt="User Sidebar Avatar" class="avatar-img rounded-circle" />
                        </div>
                        <div class="info">
                            <a href="{{ route('profile.edit', Auth::user()->id) }}" aria-expanded="true">
                                <span>
                                    {{ Auth::user()->name ?? 'Nama Pengguna' }}
                                    <span class="user-level">
                                        {{ strtoupper(Auth::user()->role ?? 'PENGGUNA') }}
                                    </span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <ul class="nav nav-primary">
                        <li class="nav-item {{ Route::is('dashboard') ? 'active' : '' }}">
                            <a href="{{ route('dashboard') }}"> <i class="fas fa-home"></i>
                                <p>Dashboard</p>
                            </a>
                        </li>

                        {{-- Menu khusus Admin --}}
                        @can('access-admin-features')
                            <li class="nav-section">
                                <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
                                <h4 class="text-section">Kelola</h4>
                            </li>
                            <li class="nav-item {{ Route::is('admin.park.*') ? 'active' : '' }}">
                                <a href="{{ Route('admin.park.index') }}"> <i class="fas fa-id-badge"></i>
                                    <p>Manajemen Area Parkir</p>
                                </a>
                            </li>
                            <li class="nav-item {{ Route::is('admin.info_board.*') ? 'active' : '' }}">
                                <a href="{{ Route('admin.info_board.index') }}"> <i class="fas fa-bullhorn"></i>
                                    <p>Manajemen Info Board</p>
                                </a>
                            </li>
                            <li class="nav-item {{ Request::is('admin/daily*') || Request::is('admin/weekly*') || Request::is('admin/points*') || Request::is('admin/rewards*') || Request::is('admin/winner*') ? 'active submenu' : '' }}">
                                <a data-toggle="collapse" href="#menuDropdown" aria-expanded="true">
                                    <i class="fas fa-gamepad"></i><p>Manajemen Gamifikasi</p><span class="caret"></span>
                                </a>
                                <div class="collapse show" id="menuDropdown">
                                    <ul class="nav nav-collapse">
                                        <li class="{{ Route::is('admin.daily.*') ? 'active' : '' }}">
                                            <a href="#"> <span class="sub-item">Misi Harian</span>
                                            </a>
                                        </li>
                                        <li class="{{ Route::is('admin.points.*') ? 'active' : '' }}">
                                            <a href="#"> <span class="sub-item">Ketentuan Poin per-Aktivitas</span>
                                            </a>
                                        </li>
                                        <li class="{{ Route::is('admin.rewards.*') ? 'active' : '' }}">
                                            <a href="#"> <span class="sub-item">Manajemen Hadiah</span>
                                            </a>
                                        </li>
                                        <li class="{{ Route::is('admin.winner.*') ? 'active' : '' }}">
                                            <a href="#"> <span class="sub-item">Penerima Hadiah</span>
                                            </a>
                                        </li>
                                        <li class="{{ Route::is('admin.tiers.*') ? 'active' : '' }}">
                                            <a href="{{ route('admin.tiers.index') }}">
                                                <span class="sub-item">Penentuan Tiers</span>
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </li>
                            <li class="nav-item {{ Route::is('admin.suggestion.*') ? 'active' : '' }}">
                                <a href="#"> <i class="fas fa-comments"></i>
                                    <p>Kritik & Saran</p>
                                </a>
                            </li>
                        @endcan
                        {{-- Akhir menu khusus Admin --}}

                        <li class="nav-item">
                            <a href="#"
                                onclick="event.preventDefault(); document.getElementById('logout-form-sidebar').submit();">
                                <i class="fas fa-door-open text-danger"></i>
                                <p class="text-danger">Keluar</p>
                            </a>
                            <form id="logout-form-sidebar" action="{{ route('logout') }}" method="POST"
                                class="d-none"> @csrf
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        {{-- Akhir Sidebar --}}

        {{-- Main Panel --}}
        <div class="main-panel">
            <div class="content">
                <div class="panel-header bg-dark-gradient">
                    <div class="page-inner py-5">
                        <div class="d-flex align-items-left align-items-md-center flex-column flex-md-row">
                            <div>
                                <h2 class="text-white pb-2 fw-bold">@yield('page_title', 'Dashboard')</h2>
                                <h5 class="text-white op-7 mb-2">
                                    @yield('page_subtitle', 'Selamat datang di dashboard Anda.')
                                </h5>
                            </div>
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>
            <footer class="footer">
                <div class="container-fluid" >
                    <div class="copyright ml-auto">
                        Made with <i class="fa fa-heart heart text-danger"></i> by
                        <a href="https://www.instagram.com/pbl303.trpl/" target="_blank">
                            PBL-TRPL 303
                        </a>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <script src="{{ asset('assets/js/core/jquery.3.2.1.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-ui-1.12.1.custom/jquery-ui.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-ui-touch-punch/jquery.ui.touch-punch.min.js') }}"></script>
    <script src="{{ asset('assets/js/plugin/jquery-scrollbar/jquery.scrollbar.min.js') }}"></script>
    <script src="{{ asset('assets/js/atlantis.min.js') }}"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            @if (session('swal_success_login'))
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: "{{ session('swal_success_login') }}",
                    showConfirmButton: false,
                    timer: 2500,
                    timerProgressBar: true
                });
            @endif

            @if (session('swal_success_crud'))
                Swal.fire({
                    title: 'Berhasil!',
                    text: "{{ session('swal_success_crud') }}",
                    icon: 'success',
                    timer: 2500,
                    showConfirmButton: false
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

            @if(session('swal_warning'))
                Swal.fire({
                    title: 'Perhatian!',
                    text: '{{ session("swal_warning") }}',
                    icon: 'warning',
                    confirmButtonColor: '#3085d6',
                    confirmButtonText: 'Baik, Saya Mengerti'
                });
            @endif

            $(document).on('submit', '.delete-form', function(event) {
                const form = this;  
                const entityName = $(form).data('entity-name') || 'data ini';

                Swal.fire({
                    title: 'Apakah Anda yakin?',
                    html: `Data <strong>${entityName}</strong> akan dihapus dan tindakan ini tidak bisa dibatalkan!`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, hapus data!',
                    cancelButtonText: 'Batal',
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            // === LOGIKA UNTUK PREVIEW GAMBAR UPLOAD ===
            const imageInput = document.getElementById('avatar');
            const imagePreview = document.getElementById('preview-avatar');

            if (imageInput && imagePreview) {
                imageInput.addEventListener('change', function(event) {
                    if (event.target.files && event.target.files[0]) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            imagePreview.src = e.target.result;
                        }
                        reader.readAsDataURL(event.target.files[0]);
                    }
                });
            }

            const formWithReset = document.querySelector('form button[type="reset"]');
            if (formWithReset) {
                formWithReset.closest('form').addEventListener('reset', function() {
                    const previews = this.querySelectorAll('img[id^="preview-"]');
                    previews.forEach(preview => {
                        preview.src = '#';
                        preview.style.display = 'none';
                    });
                });
            }
        });
    </script>
    @stack('scripts')
</body>

</html>