@extends('Layouts.content_layout')

@section('title', 'Kelola Profil Saya')

@section('page_title', 'Pengaturan Profil Pengguna')
@section('page_subtitle', 'Perbarui informasi pribadi dan keamanan akun Anda.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card" id="personal-info-card">
                    <div class="card-header">
                        <div class="d-flex align-items-center">
                            <div class="card-title">Informasi Pribadi</div>
                            <a href="{{ route('dashboard') }}" class="btn btn-md btn-round btn-primary ml-auto">
                                <i class="fas fa-arrow-left"></i> Kembali
                            </a>
                        </div>
                    </div>
                    <div class="card-body">                       
                        {{-- Menampilkan pesan error validasi umum --}}
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

                        <div class="row">
                            <div class="col-md-3 text-center align-self-start">
                                <div class="form-group">
                                    <label for="avatar" class="form-label">Foto Profil</label>
                                    <div>
                                        <img id="preview-avatar" 
                                             src="{{ $user->avatar ? asset('storage/'. $user->avatar) : asset('assets/img/default_avatar.jpg') }}" 
                                             alt="Foto Profil {{ $user->name }}" 
                                             class="img-fluid rounded-circle mb-2" 
                                             style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #eee;">
                                    </div>
                                    <label for="avatar" class="btn btn-sm text-white btn-primary mt-2">Pilih Foto Baru</label>
                                    <input type="file" name="avatar" class="form-control-file @error('avatar') is-invalid @enderror" id="avatar" style="display: none;" accept=".png, .jpg, .jpeg">
                                    <small class="form-text text-muted d-block">Maks. 2MB (PNG, JPG, JPEG)</small>
                                    @error('avatar') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                                </div>
                            </div>
                            <div class="col-md-9">
                                <div class="form-group">
                                    <label for="name">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" id="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label for="email">Alamat Email <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" id="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="form-group">
                                    <label>Peran</label>
                                    <input type="text" class="form-control" value="{{strtoupper ($user->role) }}" readonly disabled>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Card untuk Ubah Password --}}
                <div class="card mt-4">
                    <div class="card-header">
                        <div class="card-title">Ubah Password</div>
                        <div class="card-category">Kosongkan jika Anda tidak ingin mengubah password.</div>
                        <div class="card-category">(Password harus mengandung 8 karakter dengan setidaknya satu huruf besar, satu huruf kecil, satu angka dan satu simbol).</div>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label for="current_password">Password Saat Ini</label>
                            <div class="input-group">
                                <input type="password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" id="current_password">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" id="toggle-all-passwords" tabindex="-1"><i class="fa fa-eye"></i></button>
                                </div>
                                @error('current_password') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password">Password Baru</label>
                            <input type="password" name="new_password" class="form-control @error('new_password') is-invalid @enderror" id="new_password">
                            @error('new_password') <div class="invalid-feedback">{{ $message }}</div> @enderror

                            <div class="mt-2">
                                <div class="progress" style="height: 5px;">
                                    <div id="password-strength-bar" class="progress-bar" role="progressbar" style="width: 0%;"></div>
                                </div>
                                <small id="password-strength-text" class="form-text text-muted"></small>
                            </div>
                            <div id="password-rules" class="mt-2">
                                <div class="row">
                                    <div class="col-6 pr-1">
                                        <ul class="list-unstyled" style="font-size: 0.85rem;">
                                            <li id="rule-length" class="text-muted">
                                                <i class="fas fa-times-circle mr-1"></i> Minimal 8 karakter
                                            </li>
                                            <li id="rule-uppercase" class="text-muted">
                                                <i class="fas fa-times-circle mr-1"></i> Mengandung huruf besar (A-Z)
                                            </li>
                                            <li id="rule-lowercase" class="text-muted">
                                                <i class="fas fa-times-circle mr-1"></i> Mengandung huruf kecil (a-z)
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <ul class="list-unstyled" style="font-size: 0.85rem;">
                                            <li id="rule-number" class="text-muted">
                                                <i class="fas fa-times-circle mr-1"></i> Mengandung angka (0-9)
                                            </li>
                                            <li id="rule-symbol" class="text-muted">
                                                <i class="fas fa-times-circle mr-1"></i> Mengandung simbol (!@#$...)
                                            </li>
                                            <li id="rule-previous" class="text-muted">
                                                <i class="fas fa-times-circle mr-1"></i> Berbeda dari password lama
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="new_password_confirmation">Konfirmasi Password Baru</label>
                            <input type="password" name="new_password_confirmation" class="form-control" id="new_password_confirmation">
                            @error('new_password_confirmation') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div id="confirmation-feedback" class="form-text mt-1"></div>
                    </div>
                </div>

                <div class="card-action text-right mt-3">
                    <button type="submit" class="btn btn-success"><i class="fas fa-save mr-1"></i> Simpan Perubahan</button>
                    <button type="reset" class="btn btn-danger ml-2"><i class="fas fa-times mr-1"></i> Batal</button>
                </div>
            </form>
        </div>

        <div class="col-md-12 mt-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Informasi Akun</div>
                </div>
                <div class="card-body">
                    <p><strong>Akun Dibuat:</strong> {{ $user->created_at ? $user->created_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i T') : '-' }}</p>
                    <p><strong>Profil Diperbarui:</strong> {{ $user->updated_at ? $user->updated_at->setTimezone('Asia/Jakarta')->translatedFormat('d F Y, H:i T') : '-' }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Style khusus untuk halaman profil --}}
<style>
    /* Mengatur tampilan form yang terkunci/readonly */
    /* .form-group-locked .form-control[readonly] { background-color: #f0f1f5 !important; cursor: not-allowed; }
    .form-group-locked label[for="image"] { cursor: not-allowed; opacity: 0.65; pointer-events: none; } */

    /* Style untuk checklist password */
    #password-rules ul { padding-left: 0; }
    #password-rules li { transition: all 0.2s ease-in-out; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    /**
     * FITUR 1: PREVIEW GAMBAR AVATAR
     * Menangani preview gambar saat file baru dipilih.
     */
    function setupAvatarPreview() {
        const imageInput = document.getElementById('image');
        const imagePreview = document.getElementById('preview-image');

        if (!imageInput || !imagePreview) return;

        imageInput.addEventListener('change', function(event) {
            // Hentikan script lain (dari layout) agar tidak berjalan pada elemen ini
            event.stopImmediatePropagation(); 
            
            const file = event.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = e => imagePreview.src = e.target.result;
                reader.readAsDataURL(file);
            }
        });
    }

    /**
     * FITUR 2: VISIBILITAS PASSWORD (SHOW/HIDE)
     * Meng-handle tombol mata untuk melihat semua field password.
     */
    function setupPasswordVisibilityToggle() {
        const toggleButton = document.getElementById('toggle-all-passwords');
        if (!toggleButton) return;
        
        const icon = toggleButton.querySelector('i');
        const currentPasswordInput = document.getElementById('current_password');
        const newPasswordInput = document.getElementById('new_password');
        const newPasswordConfirmationInput = document.getElementById('new_password_confirmation');

        toggleButton.addEventListener('click', function() {
            const isPassword = currentPasswordInput.type === 'password';
            const newType = isPassword ? 'text' : 'password';
            currentPasswordInput.type = newType;
            newPasswordInput.type = newType;
            newPasswordConfirmationInput.type = newType;
            icon.classList.toggle('fa-eye');
            icon.classList.toggle('fa-eye-slash');
        });
    }

    /**
     * FITUR 3: INDIKATOR KEKUATAN & KRITERIA PASSWORD
     * Memberikan feedback real-time saat pengguna mengetik password baru.
     */
    function setupPasswordStrengthMeter() {
        const newPasswordInput = document.getElementById('new_password');
        if (!newPasswordInput) return;

        // Deklarasi elemen khusus untuk fitur ini
        const strengthBar = document.getElementById('password-strength-bar');
        const strengthText = document.getElementById('password-strength-text');
        const rules = {
            length: document.getElementById('rule-length'), uppercase: document.getElementById('rule-uppercase'),
            lowercase: document.getElementById('rule-lowercase'), number: document.getElementById('rule-number'),
            symbol: document.getElementById('rule-symbol'), previous: document.getElementById('rule-previous'),
            strength: document.getElementById('rule-strength'),
        };
        const currentPasswordInput = document.getElementById('current_password');

        // Fungsi helper privat untuk fitur ini
        function validateRule(element, isValid) {
            if (!element) return;
            if (isValid) {
                element.className = 'text-success';
                element.querySelector('i').className = 'fas fa-check-circle mr-1';
            } else {
                element.className = 'text-muted';
                element.querySelector('i').className = 'fas fa-times-circle mr-1';
            }
        }

        const validateAllPasswordRules = () => {
            const password = newPasswordInput.value;
            const currentPassword = currentPasswordInput ? currentPasswordInput.value : '';
            
            validateRule(rules.length, password.length >= 8);
            validateRule(rules.uppercase, /[A-Z]/.test(password));
            validateRule(rules.lowercase, /[a-z]/.test(password));
            validateRule(rules.number, /\d/.test(password));
            validateRule(rules.symbol, /[\W_]/.test(password));
            validateRule(rules.previous, password.length > 0 && password !== currentPassword);

            if (password.length === 0) {
                if (strengthBar) strengthBar.style.width = '0%';
                if (strengthText) strengthText.textContent = '';
                validateRule(rules.strength, false);
                return;
            }
            const result = zxcvbn(password);
            const score = result.score;
            const strengthLevels = { 0: { text: 'Sangat Lemah', class: 'bg-danger', width: '10%' }, 1: { text: 'Lemah', class: 'bg-danger', width: '25%' }, 2: { text: 'Sedang', class: 'bg-warning', width: '50%' }, 3: { text: 'Kuat', class: 'bg-info', width: '75%' }, 4: { text: 'Sangat Kuat', class: 'bg-success', width: '100%' } };
            const level = strengthLevels[score];

            if (strengthBar) {
                strengthBar.style.width = level.width;
                strengthBar.className = 'progress-bar ' + level.class;
            }
            if (strengthText) strengthText.textContent = 'Kekuatan: ' + level.text;
            validateRule(rules.strength, score >= 3);
        };
        
        newPasswordInput.addEventListener('keyup', validateAllPasswordRules);
        if (currentPasswordInput) currentPasswordInput.addEventListener('keyup', validateAllPasswordRules);
    }

    /**
     * FITUR 4: KONFIRMASI PASSWORD REAL-TIME
     * Memeriksa apakah password konfirmasi cocok dengan password baru.
     */
    function setupRealtimePasswordConfirmation() {
        const newPasswordInput = document.getElementById('new_password');
        const newPasswordConfirmationInput = document.getElementById('new_password_confirmation');
        if (!newPasswordInput || !newPasswordConfirmationInput) return;
        
        const confirmationFeedback = document.getElementById('confirmation-feedback');

        const checkPasswordMatch = () => {
            if (newPasswordConfirmationInput.value.length === 0) {
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

    /**
     * FITUR 5: PENGUNCIAN FORM JIKA BELUM GANTI PASSWORD
     * Menonaktifkan form informasi pribadi.
     */
    // function setupFormLocking() {
    //     // Fungsi ini hanya dijalankan jika kondisi terpenuhi
    //     @if(!Auth::user()->pass_change)
    //         const personalInfoCard = document.querySelector('#personal-info-card');
    //         if (personalInfoCard) {
    //             personalInfoCard.querySelectorAll('.form-group').forEach(fg => fg.classList.add('form-group-locked'));
    //             personalInfoCard.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]').forEach(input => input.readOnly = true);
    //             const avatarInput = document.getElementById('image');
    //             if(avatarInput) avatarInput.disabled = true;
    //         }
    //     @endif
    // }


    // =================================================================
    // EKSEKUSI SEMUA FUNGSI SETUP
    // =================================================================
    setupAvatarPreview();
    setupPasswordVisibilityToggle();
    setupPasswordStrengthMeter();
    setupRealtimePasswordConfirmation();
    // setupFormLocking();

});
</script>
@endpush