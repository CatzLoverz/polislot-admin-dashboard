@extends("Layouts.content_layout")

@section('title', 'Tambah Mission')
@section('page_title', 'Tambah Mission')
@section('page_subtitle', 'Buat mission baru untuk pengguna aplikasi.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2.5rem;">

                    {{-- Header --}}
                    <div class="mb-4">
                        <a href="{{ route('admin.missions.index') }}" class="btn btn-secondary mb-3" style="border-radius: 15px; padding: 10px 25px;">
                            <i class="fa fa-arrow-left mr-2"></i>Kembali
                        </a>
                        <h2 class="mb-2 font-weight-bold text-dark">
                            <i class="fa fa-plus-circle mr-2 text-primary"></i>Tambah Mission Baru
                        </h2>
                        <p class="text-muted mb-0">Isi formulir di bawah ini untuk membuat mission baru</p>
                    </div>

                    {{-- Form --}}
                    <form action="{{ route('admin.missions.store') }}" method="POST">
                        @csrf

                        <div class="row">
                            {{-- Nama Mission --}}
                            <div class="col-md-6 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-tag mr-1 text-primary"></i>Nama Mission <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="mission_name" 
                                       class="form-control @error('mission_name') is-invalid @enderror" 
                                       placeholder="Contoh: Login Harian"
                                       value="{{ old('mission_name') }}"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                @error('mission_name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Mission Type --}}
                            <div class="col-md-6 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-list-alt mr-1 text-primary"></i>Tipe Mission <span class="text-danger">*</span>
                                </label>
                                <input type="text" 
                                       name="mission_type" 
                                       class="form-control @error('mission_type') is-invalid @enderror" 
                                       placeholder="Contoh: DAILY_LOGIN"
                                       value="{{ old('mission_type') }}"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                <small class="text-muted">Gunakan format UPPERCASE dengan underscore (DAILY_LOGIN, FEEDBACK_SUBMIT, dll)</small>
                                @error('mission_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Deskripsi --}}
                        <div class="mb-4">
                            <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                <i class="fa fa-align-left mr-1 text-primary"></i>Deskripsi
                            </label>
                            <textarea name="description" 
                                      class="form-control @error('description') is-invalid @enderror" 
                                      rows="4" 
                                      placeholder="Jelaskan tentang mission ini..."
                                      style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            {{-- Target Value --}}
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-bullseye mr-1 text-primary"></i>Target <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="target_value" 
                                       class="form-control @error('target_value') is-invalid @enderror" 
                                       placeholder="Contoh: 7"
                                       value="{{ old('target_value') }}"
                                       min="0"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                <small class="text-muted">Jumlah yang harus dicapai</small>
                                @error('target_value')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Reward Points --}}
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-star mr-1 text-warning"></i>Reward Poin <span class="text-danger">*</span>
                                </label>
                                <input type="number" 
                                       name="reward_points" 
                                       class="form-control @error('reward_points') is-invalid @enderror" 
                                       placeholder="Contoh: 100"
                                       value="{{ old('reward_points') }}"
                                       min="0"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                @error('reward_points')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Period Type --}}
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-calendar mr-1 text-primary"></i>Periode <span class="text-danger">*</span>
                                </label>
                                <select name="period_type" 
                                        class="form-control @error('period_type') is-invalid @enderror"
                                        style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                        required>
                                    <option value="">-- Pilih Periode --</option>
                                    <option value="daily" {{ old('period_type') == 'daily' ? 'selected' : '' }}>Daily (Harian)</option>
                                    <option value="weekly" {{ old('period_type') == 'weekly' ? 'selected' : '' }}>Weekly (Mingguan)</option>
                                    <option value="one_time" {{ old('period_type') == 'one_time' ? 'selected' : '' }}>One Time (Sekali)</option>
                                </select>
                                @error('period_type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row">
                            {{-- Reset Time --}}
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-clock mr-1 text-primary"></i>Waktu Reset <span class="text-danger">*</span>
                                </label>
                                <input type="time" 
                                       name="reset_time" 
                                       class="form-control @error('reset_time') is-invalid @enderror" 
                                       value="{{ old('reset_time', '00:00') }}"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                <small class="text-muted">Jam reset progress harian/mingguan</small>
                                @error('reset_time')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Start Date --}}
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-calendar-check mr-1 text-success"></i>Tanggal Mulai <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="start_date" 
                                       class="form-control @error('start_date') is-invalid @enderror" 
                                       value="{{ old('start_date', date('Y-m-d')) }}"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                @error('start_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- End Date --}}
                            <div class="col-md-4 mb-4">
                                <label class="font-weight-bold mb-2" style="font-size: 1.05rem;">
                                    <i class="fa fa-calendar-times mr-1 text-danger"></i>Tanggal Berakhir <span class="text-danger">*</span>
                                </label>
                                <input type="date" 
                                       name="end_date" 
                                       class="form-control @error('end_date') is-invalid @enderror" 
                                       value="{{ old('end_date') }}"
                                       style="border-radius: 10px; padding: 12px 15px; font-size: 1.05rem;"
                                       required>
                                @error('end_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Status Aktif --}}
                        <div class="mb-4">
                            <div class="custom-control custom-switch" style="padding-left: 2.5rem;">
                                <input type="checkbox" 
                                       class="custom-control-input" 
                                       id="is_active" 
                                       name="is_active"
                                       value="1"
                                       {{ old('is_active', true) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="is_active" style="font-size: 1.05rem;">
                                    <i class="fa fa-toggle-on mr-1 text-success"></i>Mission Aktif
                                </label>
                                <small class="d-block text-muted ml-4">Mission akan langsung bisa diakses user</small>
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="d-flex justify-content-end mt-5 pt-4 border-top">
                            <a href="{{ route('admin.missions.index') }}" class="btn btn-secondary mr-3" style="border-radius: 15px; padding: 12px 30px; font-size: 1.05rem;">
                                <i class="fa fa-times mr-2"></i>Batal
                            </a>
                            <button type="submit" class="btn btn-primary" style="border-radius: 15px; padding: 12px 30px; font-size: 1.05rem;">
                                <i class="fa fa-save mr-2"></i>Simpan Mission
                            </button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection