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
                                    $tierLower = strtolower($tier->tier_name);
                                    
                                    // Special Tiers (Top Leaderboard)
                                    if (str_contains($tierLower, 'champion') || str_contains($tierLower, 'legend')) {
                                        $bgGradient = 'linear-gradient(135deg, #ffd89b 0%, #19547b 100%)';
                                        $icon = 'fa-trophy';
                                        $iconColor = '#FFD700';
                                    } elseif (str_contains($tierLower, 'master') || str_contains($tierLower, 'elite')) {
                                        $bgGradient = 'linear-gradient(135deg, #e0e0e0 0%, #757575 100%)';
                                        $icon = 'fa-crown';
                                        $iconColor = '#C0C0C0';
                                    } elseif (str_contains($tierLower, 'expert') || str_contains($tierLower, 'hero')) {
                                        $bgGradient = 'linear-gradient(135deg, #cd7f32 0%, #8b4513 100%)';
                                        $icon = 'fa-medal';
                                        $iconColor = '#CD7F32';
                                    }
                                    // Regular Tiers
                                    elseif (str_contains($tierLower, 'gold') || str_contains($tierLower, 'emas')) {
                                        $bgGradient = 'linear-gradient(135deg, #fff4e6 0%, #ffe0b2 100%)';
                                        $icon = 'fa-gem';
                                        $iconColor = '#FFD700';
                                    } elseif (str_contains($tierLower, 'silver') || str_contains($tierLower, 'perak')) {
                                        $bgGradient = 'linear-gradient(135deg, #f5f5f5 0%, #e0e0e0 100%)';
                                        $icon = 'fa-certificate';
                                        $iconColor = '#C0C0C0';
                                    } elseif (str_contains($tierLower, 'bronze') || str_contains($tierLower, 'perunggu')) {
                                        $bgGradient = 'linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%)';
                                        $icon = 'fa-award';
                                        $iconColor = '#CD7F32';
                                    } else {
                                        $bgGradient = 'linear-gradient(135deg, #eef3ff 0%, #d8e0ff 100%)';
                                        $icon = 'fa-star';
                                        $iconColor = '#6c63ff';
                                    }
                                @endphp

                                <div class="p-4 d-flex align-items-start justify-content-between"
                                    style="background: {{ $bgGradient }};
                                           border-radius: 25px;
                                           box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
                                           transition: all 0.25s ease;">
                                    
                                    {{-- Icon Tier --}}
                                    <div class="mr-4">
                                        <i class="fa {{ $icon }} fa-4x" style="color: {{ $iconColor }};"></i>
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
                                                    <small class="form-text text-muted">Masukkan nama tier yang unik dan menarik</small>
                                                </div>
                                                <div class="form-group">
                                                    <label>Minimum Points <span class="text-danger">*</span></label>
                                                    <input type="number" name="min_points" class="form-control" 
                                                           value="{{ $tier->min_points }}" 
                                                           min="0" placeholder="Contoh: 100" required>
                                                    <small class="form-text text-muted">Jumlah minimum poin yang diperlukan untuk mencapai tier ini</small>
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
                            • Tier Terendah: <strong>{{ $tiers->first()->tier_name }}</strong> ({{ number_format($tiers->first()->min_points, 0, ',', '.') }} poin)<br>
                            • Tier Tertinggi: <strong>{{ $tiers->last()->tier_name }}</strong> ({{ number_format($tiers->last()->min_points, 0, ',', '.') }} poin)
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
                        Gunakan nama tier yang menarik seperti Bronze (0-100), Silver (101-500), Gold (501-1000), 
                        atau tier khusus seperti Champion, Master, Expert untuk top leaderboard.</small>
                    </div>

                    <div class="form-group">
                        <label>Nama Tier <span class="text-danger">*</span></label>
                        <input type="text" name="tier_name" class="form-control" 
                               placeholder="Contoh: Bronze, Silver, Gold, Champion" required>
                        <small class="form-text text-muted">Masukkan nama tier yang unik dan mudah diingat</small>
                    </div>
                    <div class="form-group">
                        <label>Minimum Points <span class="text-danger">*</span></label>
                        <input type="number" name="min_points" class="form-control" 
                               min="0" placeholder="Contoh: 100" required>
                        <small class="form-text text-muted">Jumlah minimum poin yang diperlukan untuk mencapai tier ini</small>
                    </div>

                    {{-- Contoh Tier --}}
                    <div class="mt-4 p-3" style="background: #f8f9fa; border-radius: 10px;">
                        <h6 class="font-weight-bold mb-2"><i class="fa fa-list mr-2"></i>Struktur Tier:</h6>
                        <small class="d-block mb-1"><strong>Regular Tiers:</strong></small>
                        <small class="d-block ml-3">• Bronze: 0-100 poin</small>
                        <small class="d-block ml-3">• Silver: 101-500 poin</small>
                        <small class="d-block ml-3">• Gold: 501-1000 poin</small>
                        <small class="d-block mb-1 mt-2"><strong>Special Tiers (Top Leaderboard):</strong></small>
                        <small class="d-block ml-3">• Expert/Hero: Top 3 (1001+ poin)</small>
                        <small class="d-block ml-3">• Master/Elite: Top 2 (1501+ poin)</small>
                        <small class="d-block ml-3">• Champion/Legend: Top 1 (2001+ poin)</small>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Smooth scroll animation
        const cards = document.querySelectorAll('[style*="transition"]');
        cards.forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 12px 24px rgba(0, 0, 0, 0.15)';
            });
            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 6px 16px rgba(0, 0, 0, 0.08)';
            });
        });

        // Auto focus pada input pertama saat modal dibuka
        $('#createModal, [id^="editModal"]').on('shown.bs.modal', function() {
            $(this).find('input[name="tier_name"]').focus();
        });

        // Konfirmasi sebelum submit form hapus
        $('form[method="POST"]').on('submit', function(e) {
            if ($(this).find('input[name="_method"][value="DELETE"]').length) {
                const tierName = $(this).closest('.modal-content').find('h5.font-weight-bold').text();
                if (!confirm(`Yakin ingin menghapus tier "${tierName}"?`)) {
                    e.preventDefault();
                }
            }
        });
    });
</script>
@endsection
@endsection