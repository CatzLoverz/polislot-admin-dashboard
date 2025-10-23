@extends("Layouts.content_layout")

@section('title', 'Tier Management')
@section('page_title', 'Tier Management')
@section('page_subtitle', 'Kelola tingkatan tier dan reward untuk sistem loyalitas pelanggan.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">

                    {{-- Tombol tambah --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 font-weight-bold text-dark">Sistem Tier Loyalitas</h2>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal"
                            style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fa fa-plus mr-2"></i> Tier Baru
                        </button>
                    </div>
                    <hr>

                    {{-- Daftar Tiers --}}
                    <div class="row">
                        @forelse($tiers as $tier)
                            <div class="col-md-12 mb-4">
                                @php
                                    // Ambil warna dari database atau default
                                    $colorTheme = $tier->color_theme ?? 'blue';
                                    $iconName = $tier->icon ?? 'fa-star';
                                    
                                    // Definisi gradient berdasarkan color_theme
                                    $colorGradients = [
                                        'blue' => [
                                            'bg' => 'linear-gradient(135deg, #eef3ff 0%, #d8e0ff 100%)',
                                            'icon' => '#6c63ff'
                                        ],
                                        'gold' => [
                                            'bg' => 'linear-gradient(135deg, #fff4e6 0%, #ffe0b2 100%)',
                                            'icon' => '#FFD700'
                                        ],
                                        'silver' => [
                                            'bg' => 'linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%)',
                                            'icon' => '#C0C0C0'
                                        ],
                                        'bronze' => [
                                            'bg' => 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)',
                                            'icon' => '#CD7F32'
                                        ],
                                        'red' => [
                                            'bg' => 'linear-gradient(135deg, #ffe5e5 0%, #ffcccc 100%)',
                                            'icon' => '#e74c3c'
                                        ],
                                        'purple' => [
                                            'bg' => 'linear-gradient(135deg, #f3e5ff 0%, #e1ccff 100%)',
                                            'icon' => '#9b59b6'
                                        ],
                                        'green' => [
                                            'bg' => 'linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%)',
                                            'icon' => '#27ae60'
                                        ],
                                        'orange' => [
                                            'bg' => 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)',
                                            'icon' => '#f39c12'
                                        ],
                                        'pink' => [
                                            'bg' => 'linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%)',
                                            'icon' => '#e91e63'
                                        ],
                                        'cyan' => [
                                            'bg' => 'linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%)',
                                            'icon' => '#00bcd4'
                                        ],
                                        'indigo' => [
                                            'bg' => 'linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%)',
                                            'icon' => '#3f51b5'
                                        ],
                                        'teal' => [
                                            'bg' => 'linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%)',
                                            'icon' => '#009688'
                                        ],
                                        'lime' => [
                                            'bg' => 'linear-gradient(135deg, #f9fbe7 0%, #f0f4c3 100%)',
                                            'icon' => '#cddc39'
                                        ],
                                        'amber' => [
                                            'bg' => 'linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%)',
                                            'icon' => '#ffc107'
                                        ],
                                        'rainbow' => [
                                            'bg' => 'linear-gradient(135deg, #ffd89b 0%, #19547b 100%)',
                                            'icon' => '#FFD700'
                                        ],
                                    ];

                                    $colors = $colorGradients[$colorTheme] ?? $colorGradients['blue'];
                                    $bgGradient = $colors['bg'];
                                    $iconColor = $colors['icon'];
                                @endphp

                                <div class="p-4 d-flex align-items-start justify-content-between tier-card"
                                    style="background: {{ $bgGradient }};
                                           border-radius: 25px;
                                           box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
                                           transition: all 0.25s ease;">
                                    
                                    {{-- Icon Tier --}}
                                    <div class="mr-4">
                                        <i class="fa {{ $iconName }} fa-4x" style="color: {{ $iconColor }};"></i>
                                    </div>

                                    {{-- Konten Tier --}}
                                    <div class="flex-fill">
                                        <h5 class="font-weight-bold mb-2">{{ $tier->tier_name }}</h5>
                                        <p class="text-dark mb-2" style="font-size: 0.95rem;">
                                            <strong>Minimum Points:</strong> 
                                            <span class="badge badge-primary px-3 py-2" style="font-size: 1rem; border-radius: 10px;">
                                                {{ number_format($tier->min_points, 0, ',', '.') }} Poin
                                            </span>
                                        </p>
                                        <small class="text-muted">
                                            <i class="fa fa-calendar mr-1"></i> Dibuat: {{ \Carbon\Carbon::parse($tier->created_at)->format('d-M-Y H:i') }}
                                            @if($tier->updated_at != $tier->created_at)
                                                <br><i class="fa fa-edit mr-1"></i> Terakhir Update: {{ \Carbon\Carbon::parse($tier->updated_at)->format('d-M-Y H:i') }}
                                            @endif
                                        </small>
                                    </div>

                                    {{-- Tombol Edit dan Delete --}}
                                    <div class="ml-4 d-flex flex-column align-items-center justify-content-center">
                                        <button class="btn btn-link text-primary mb-3 p-0"
                                            data-toggle="modal"
                                            data-target="#editModal{{ $tier->tier_id }}"
                                            style="font-size: 1.8rem;">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-0"
                                            data-toggle="modal"
                                            data-target="#deleteModal{{ $tier->tier_id }}"
                                            style="font-size: 1.8rem;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="editModal{{ $tier->tier_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tiers.update', $tier->tier_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Tier</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Nama Tier <span class="text-danger">*</span></label>
                                                    <input type="text" name="tier_name" class="form-control" 
                                                           value="{{ $tier->tier_name }}" 
                                                           placeholder="Contoh: Gold, Silver, Bronze" required>
                                                </div>

                                                <div class="form-group">
                                                    <label>Minimum Points <span class="text-danger">*</span></label>
                                                    <input type="number" name="min_points" class="form-control" 
                                                           value="{{ $tier->min_points }}" 
                                                           min="0" placeholder="Contoh: 100" required>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Tema Warna <span class="text-danger">*</span></label>
                                                            <select name="color_theme" class="form-control color-selector" required>
                                                                <option value="blue" {{ ($tier->color_theme ?? 'blue') == 'blue' ? 'selected' : '' }}>🔵 Blue (Default)</option>
                                                                <option value="gold" {{ ($tier->color_theme ?? '') == 'gold' ? 'selected' : '' }}>🥇 Gold</option>
                                                                <option value="silver" {{ ($tier->color_theme ?? '') == 'silver' ? 'selected' : '' }}>🥈 Silver</option>
                                                                <option value="bronze" {{ ($tier->color_theme ?? '') == 'bronze' ? 'selected' : '' }}>🥉 Bronze</option>
                                                                <option value="red" {{ ($tier->color_theme ?? '') == 'red' ? 'selected' : '' }}>🔴 Red (Legend)</option>
                                                                <option value="purple" {{ ($tier->color_theme ?? '') == 'purple' ? 'selected' : '' }}>🟣 Purple (Master)</option>
                                                                <option value="green" {{ ($tier->color_theme ?? '') == 'green' ? 'selected' : '' }}>🟢 Green (Pro)</option>
                                                                <option value="orange" {{ ($tier->color_theme ?? '') == 'orange' ? 'selected' : '' }}>🟠 Orange (Elite)</option>
                                                                <option value="pink" {{ ($tier->color_theme ?? '') == 'pink' ? 'selected' : '' }}>🩷 Pink (VIP)</option>
                                                                <option value="cyan" {{ ($tier->color_theme ?? '') == 'cyan' ? 'selected' : '' }}>🔵 Cyan (Diamond)</option>
                                                                <option value="indigo" {{ ($tier->color_theme ?? '') == 'indigo' ? 'selected' : '' }}>🔷 Indigo (Platinum)</option>
                                                                <option value="teal" {{ ($tier->color_theme ?? '') == 'teal' ? 'selected' : '' }}>💎 Teal (Emerald)</option>
                                                                <option value="lime" {{ ($tier->color_theme ?? '') == 'lime' ? 'selected' : '' }}>🟢 Lime (Fresh)</option>
                                                                <option value="amber" {{ ($tier->color_theme ?? '') == 'amber' ? 'selected' : '' }}>🟡 Amber (Supreme)</option>
                                                                <option value="rainbow" {{ ($tier->color_theme ?? '') == 'rainbow' ? 'selected' : '' }}>🌈 Rainbow (Champion)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Icon <span class="text-danger">*</span></label>
                                                            <select name="icon" class="form-control icon-selector" required>
                                                                <option value="fa-star" {{ ($tier->icon ?? 'fa-star') == 'fa-star' ? 'selected' : '' }}>⭐ Star</option>
                                                                <option value="fa-trophy" {{ ($tier->icon ?? '') == 'fa-trophy' ? 'selected' : '' }}>🏆 Trophy</option>
                                                                <option value="fa-crown" {{ ($tier->icon ?? '') == 'fa-crown' ? 'selected' : '' }}>👑 Crown</option>
                                                                <option value="fa-medal" {{ ($tier->icon ?? '') == 'fa-medal' ? 'selected' : '' }}>🏅 Medal</option>
                                                                <option value="fa-gem" {{ ($tier->icon ?? '') == 'fa-gem' ? 'selected' : '' }}>💎 Gem</option>
                                                                <option value="fa-certificate" {{ ($tier->icon ?? '') == 'fa-certificate' ? 'selected' : '' }}>🎖️ Certificate</option>
                                                                <option value="fa-award" {{ ($tier->icon ?? '') == 'fa-award' ? 'selected' : '' }}>🏆 Award</option>
                                                                <option value="fa-fire" {{ ($tier->icon ?? '') == 'fa-fire' ? 'selected' : '' }}>🔥 Fire</option>
                                                                <option value="fa-bolt" {{ ($tier->icon ?? '') == 'fa-bolt' ? 'selected' : '' }}>⚡ Bolt</option>
                                                                <option value="fa-shield-alt" {{ ($tier->icon ?? '') == 'fa-shield-alt' ? 'selected' : '' }}>🛡️ Shield</option>
                                                                <option value="fa-heart" {{ ($tier->icon ?? '') == 'fa-heart' ? 'selected' : '' }}>❤️ Heart</option>
                                                                <option value="fa-rocket" {{ ($tier->icon ?? '') == 'fa-rocket' ? 'selected' : '' }}>🚀 Rocket</option>
                                                                <option value="fa-flag" {{ ($tier->icon ?? '') == 'fa-flag' ? 'selected' : '' }}>🚩 Flag</option>
                                                                <option value="fa-magic" {{ ($tier->icon ?? '') == 'fa-magic' ? 'selected' : '' }}>✨ Magic</option>
                                                                <option value="fa-diamond" {{ ($tier->icon ?? '') == 'fa-diamond' ? 'selected' : '' }}>💠 Diamond</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Preview --}}
                                                <div class="form-group">
                                                    <label>Preview:</label>
                                                    <div id="editPreview{{ $tier->tier_id }}" class="p-3 d-flex align-items-center" 
                                                         style="border-radius: 15px; background: linear-gradient(135deg, #eef3ff 0%, #d8e0ff 100%);">
                                                        <i class="fa fa-star fa-3x mr-3" style="color: #6c63ff;"></i>
                                                        <div>
                                                            <h6 class="mb-0 font-weight-bold">Preview Tier</h6>
                                                            <small class="text-muted">Pilih warna dan icon untuk melihat preview</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary"
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                    <i class="fa fa-save mr-2"></i> Simpan Perubahan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Hapus --}}
                            <div class="modal fade" id="deleteModal{{ $tier->tier_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.tiers.destroy', $tier->tier_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title"><i class="fa fa-exclamation-triangle mr-2"></i> Hapus Tier</h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center py-4">
                                                <i class="fa fa-trash fa-3x text-danger mb-3"></i>
                                                <p class="mb-2">Apakah Anda yakin ingin menghapus tier:</p>
                                                <h5 class="font-weight-bold text-dark">{{ $tier->tier_name }}</h5>
                                                <p class="text-muted mt-2" style="font-size: 0.9rem;">Tindakan ini tidak dapat dibatalkan!</p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                                                    <i class="fa fa-times mr-2"></i> Batal
                                                </button>
                                                <button type="submit" class="btn btn-danger">
                                                    <i class="fa fa-trash mr-2"></i> Ya, Hapus
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <div class="col-md-12">
                                <div class="text-center py-5">
                                    <i class="fa fa-inbox fa-5x text-muted mb-3"></i>
                                    <h5 class="text-muted">Belum Ada Tier</h5>
                                    <p class="text-muted">Mulai tambahkan tier untuk sistem loyalitas Anda</p>
                                    <button class="btn btn-primary mt-2" data-toggle="modal" data-target="#createModal"
                                        style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                        <i class="fa fa-plus mr-2"></i> Tambah Tier Pertama
                                    </button>
                                </div>
                            </div>
                        @endforelse
                    </div>

                    {{-- Info Box --}}
                    @if($tiers->count() > 0)
                    <div class="alert alert-info mt-4" style="border-radius: 15px; border-left: 5px solid #2196F3;">
                        <h6 class="alert-heading"><i class="fa fa-info-circle mr-2"></i> Informasi Tier</h6>
                        <small>
                            • Total Tier: <strong>{{ $tiers->count() }}</strong> <br>
                            • Tier Tertinggi: <strong>{{ $tiers->first()->tier_name }}</strong> ({{ number_format($tiers->first()->min_points, 0, ',', '.') }} poin)<br>
                            • Tier Terendah: <strong>{{ $tiers->last()->tier_name }}</strong> ({{ number_format($tiers->last()->min_points, 0, ',', '.') }} poin)
                        </small>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.tiers.store') }}" method="POST">
                @csrf
                <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <h5 class="modal-title text-white"><i class="fa fa-plus-circle mr-2"></i> Tambah Tier Baru</h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning" style="border-radius: 10px;">
                        <small><i class="fa fa-lightbulb mr-2"></i> <strong>Tips:</strong> 
                        Pilih warna dan icon yang sesuai dengan tingkatan tier. Misalnya Red + Trophy untuk Legend, Purple + Crown untuk Master, dll.</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Tier <span class="text-danger">*</span></label>
                        <input type="text" name="tier_name" class="form-control" 
                               placeholder="Contoh: Bronze, Silver, Gold, Legend" required>
                    </div>

                    <div class="form-group">
                        <label>Minimum Points <span class="text-danger">*</span></label>
                        <input type="number" name="min_points" class="form-control" 
                               min="0" placeholder="Contoh: 100" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tema Warna <span class="text-danger">*</span></label>
                                <select name="color_theme" id="colorThemeCreate" class="form-control" required>
                                    <option value="blue">🔵 Blue (Default)</option>
                                    <option value="gold">🥇 Gold</option>
                                    <option value="silver">🥈 Silver</option>
                                    <option value="bronze">🥉 Bronze</option>
                                    <option value="red">🔴 Red (Legend)</option>
                                    <option value="purple">🟣 Purple (Master)</option>
                                    <option value="green">🟢 Green (Pro)</option>
                                    <option value="orange">🟠 Orange (Elite)</option>
                                    <option value="pink">🩷 Pink (VIP)</option>
                                    <option value="cyan">🔵 Cyan (Diamond)</option>
                                    <option value="indigo">🔷 Indigo (Platinum)</option>
                                    <option value="teal">💎 Teal (Emerald)</option>
                                    <option value="lime">🟢 Lime (Fresh)</option>
                                    <option value="amber">🟡 Amber (Supreme)</option>
                                    <option value="rainbow">🌈 Rainbow (Champion)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Icon <span class="text-danger">*</span></label>
                                <select name="icon" id="iconCreate" class="form-control" required>
                                    <option value="fa-star">⭐ Star</option>
                                    <option value="fa-trophy">🏆 Trophy</option>
                                    <option value="fa-crown">👑 Crown</option>
                                    <option value="fa-medal">🏅 Medal</option>
                                    <option value="fa-gem">💎 Gem</option>
                                    <option value="fa-certificate">🎖️ Certificate</option>
                                    <option value="fa-award">🏆 Award</option>
                                    <option value="fa-fire">🔥 Fire</option>
                                    <option value="fa-bolt">⚡ Bolt</option>
                                    <option value="fa-shield-alt">🛡️ Shield</option>
                                    <option value="fa-heart">❤️ Heart</option>
                                    <option value="fa-rocket">🚀 Rocket</option>
                                    <option value="fa-flag">🚩 Flag</option>
                                    <option value="fa-magic">✨ Magic</option>
                                    <option value="fa-diamond">💠 Diamond</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Preview --}}
                    <div class="form-group">
                        <label>Preview:</label>
                        <div id="previewCard" class="p-3 d-flex align-items-center" 
                             style="border-radius: 15px; background: linear-gradient(135deg, #eef3ff 0%, #d8e0ff 100%); transition: all 0.3s ease;">
                            <i id="previewIcon" class="fa fa-star fa-3x mr-3" style="color: #6c63ff;"></i>
                            <div>
                                <h6 class="mb-0 font-weight-bold">Preview Tier</h6>
                                <small class="text-muted">Pilih warna dan icon untuk melihat preview</small>
                            </div>
                        </div>
                    </div>

                    {{-- Contoh Tier --}}
                    <div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
                        <h6 class="font-weight-bold mb-2"><i class="fa fa-list mr-2"></i> Contoh Konfigurasi:</h6>
                        <small class="d-block mb-1"><strong>Tier Basic:</strong></small>
                        <small class="d-block ml-3">• Bronze (0-100) → Bronze + Award</small>
                        <small class="d-block ml-3">• Silver (101-500) → Silver + Certificate</small>
                        <small class="d-block ml-3">• Gold (501-1000) → Gold + Gem</small>
                        <small class="d-block mb-1 mt-2"><strong>Tier Special:</strong></small>
                        <small class="d-block ml-3">• Expert (1001+) → Orange + Fire</small>
                        <small class="d-block ml-3">• Master (1501+) → Purple + Crown</small>
                        <small class="d-block ml-3">• Legend (2001+) → Red + Trophy</small>
                        <small class="d-block ml-3">• Champion (5000+) → Rainbow + Trophy</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-2"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        <i class="fa fa-save mr-2"></i> Simpan Tier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        console.log('Tier Management Script Loaded');

        // Definisi warna gradient
        const colorGradients = {
            'blue': { bg: 'linear-gradient(135deg, #eef3ff 0%, #d8e0ff 100%)', icon: '#6c63ff' },
            'gold': { bg: 'linear-gradient(135deg, #fff4e6 0%, #ffe0b2 100%)', icon: '#FFD700' },
            'silver': { bg: 'linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%)', icon: '#C0C0C0' },
            'bronze': { bg: 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)', icon: '#CD7F32' },
            'red': { bg: 'linear-gradient(135deg, #ffe5e5 0%, #ffcccc 100%)', icon: '#e74c3c' },
            'purple': { bg: 'linear-gradient(135deg, #f3e5ff 0%, #e1ccff 100%)', icon: '#9b59b6' },
            'green': { bg: 'linear-gradient(135deg, #e8f5e9 0%, #c8e6c9 100%)', icon: '#27ae60' },
            'orange': { bg: 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)', icon: '#f39c12' },
            'pink': { bg: 'linear-gradient(135deg, #fce4ec 0%, #f8bbd0 100%)', icon: '#e91e63' },
            'cyan': { bg: 'linear-gradient(135deg, #e0f7fa 0%, #b2ebf2 100%)', icon: '#00bcd4' },
            'indigo': { bg: 'linear-gradient(135deg, #e8eaf6 0%, #c5cae9 100%)', icon: '#3f51b5' },
            'teal': { bg: 'linear-gradient(135deg, #e0f2f1 0%, #b2dfdb 100%)', icon: '#009688' },
            'lime': { bg: 'linear-gradient(135deg, #f9fbe7 0%, #f0f4c3 100%)', icon: '#cddc39' },
            'amber': { bg: 'linear-gradient(135deg, #fff8e1 0%, #ffecb3 100%)', icon: '#ffc107' },
            'rainbow': { bg: 'linear-gradient(135deg, #ffd89b 0%, #19547b 100%)', icon: '#FFD700' }
        };

        // ========== PREVIEW MODAL CREATE ==========
        function updateCreatePreview() {
            const colorSelect = $('#colorThemeCreate');
            const iconSelect = $('#iconCreate');
            const previewCard = $('#previewCard');
            const previewIcon = $('#previewIcon');

            if (colorSelect.length && iconSelect.length && previewCard.length && previewIcon.length) {
                const selectedColor = colorSelect.val();
                const selectedIcon = iconSelect.val();
                const colors = colorGradients[selectedColor] || colorGradients['blue'];

                previewCard.css('background', colors.bg);
                previewIcon.attr('class', `fa ${selectedIcon} fa-3x mr-3`);
                previewIcon.css('color', colors.icon);
                
                console.log('Create Preview Updated:', selectedColor, selectedIcon);
            }
        }

        // Event listener untuk CREATE modal
        $('#colorThemeCreate, #iconCreate').on('change', function() {
            updateCreatePreview();
        });

        // Update preview saat modal CREATE dibuka
        $('#createModal').on('shown.bs.modal', function() {
            updateCreatePreview();
            $(this).find('input[name="tier_name"]').focus();
        });

        // ========== PREVIEW MODAL EDIT ==========
        $('[id^="editModal"]').each(function() {
            const modal = $(this);
            const modalId = modal.attr('id').replace('editModal', '');
            
            modal.on('shown.bs.modal', function() {
                const colorSelector = modal.find('.color-selector');
                const iconSelector = modal.find('.icon-selector');
                const editPreview = $('#editPreview' + modalId);
                const editIcon = editPreview.find('i');

                function updateEditPreview() {
                    if (colorSelector.length && iconSelector.length && editPreview.length && editIcon.length) {
                        const selectedColor = colorSelector.val();
                        const selectedIcon = iconSelector.val();
                        const colors = colorGradients[selectedColor] || colorGradients['blue'];

                        editPreview.css('background', colors.bg);
                        editIcon.attr('class', `fa ${selectedIcon} fa-3x mr-3`);
                        editIcon.css('color', colors.icon);
                        
                        console.log('Edit Preview Updated:', selectedColor, selectedIcon);
                    }
                }

                // Update preview pertama kali modal dibuka
                updateEditPreview();

                // Event listener untuk perubahan
                colorSelector.off('change').on('change', updateEditPreview);
                iconSelector.off('change').on('change', updateEditPreview);

                // Focus ke input nama
                modal.find('input[name="tier_name"]').focus();
            });
        });

        // ========== HOVER ANIMATION UNTUK TIER CARDS ==========
        $('.tier-card').hover(
            function() {
                $(this).css({
                    'transform': 'translateY(-5px)',
                    'box-shadow': '0 12px 24px rgba(0, 0, 0, 0.15)'
                });
            },
            function() {
                $(this).css({
                    'transform': 'translateY(0)',
                    'box-shadow': '0 6px 16px rgba(0, 0, 0, 0.08)'
                });
            }
        );

        // ========== KONFIRMASI HAPUS ==========
        // Jangan pakai preventDefault di form submit, biarkan form action bekerja
        $('[id^="deleteModal"]').on('show.bs.modal', function(e) {
            const button = $(e.relatedTarget); // Tombol yang memicu modal
            const tierName = button.closest('.tier-card').find('h5.font-weight-bold').text().trim();
            console.log('Delete modal opened for:', tierName);
        });

        console.log('All event listeners attached successfully');
    });
</script>
@endpush
