@extends("Layouts.content_layout")

@section('title', 'Rewards')
@section('page_title', 'Manajemen Rewards')
@section('page_subtitle', 'Kelola hadiah dan voucher untuk pengguna setia.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">

                    {{-- Tombol tambah --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 font-weight-bold text-dark">Daftar Rewards</h2>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal"
                            style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fa fa-plus mr-2"></i> Reward Baru
                        </button>
                    </div>
                    <hr>

                    {{-- Daftar rewards --}}
                    <div class="row">
                        @forelse($rewards as $reward)
                            <div class="col-md-6 mb-4">
                                <div class="p-4 d-flex align-items-start"
                                    style="background: linear-gradient(135deg, #fff5e6 0%, #ffe0b3 100%);
                                           border-radius: 25px;
                                           box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
                                           transition: all 0.25s ease;
                                           min-height: 200px;">
                                    
                                    {{-- Gambar Reward --}}
                                    <div class="mr-4 d-flex align-items-center justify-content-center" style="min-width: 100px; min-height: 100px;">
                                        @if($reward->reward_image)
                                            <img src="{{ asset('storage/' . $reward->reward_image) }}" 
                                                 alt="{{ $reward->reward_name }}"
                                                 style="width: 100px; height: 100px; object-fit: contain; border-radius: 15px;">
                                        @else
                                            @php
                                                $iconClass = $reward->reward_type === 'voucher' ? 'fa-ticket-alt' : 'fa-gift';
                                                $iconColor = $reward->reward_type === 'voucher' ? '#ff6b6b' : '#ffa726';
                                            @endphp
                                            <i class="fa {{ $iconClass }} fa-4x" style="color: {{ $iconColor }};"></i>
                                        @endif
                                    </div>

                                    {{-- Konten Reward --}}
                                    <div class="flex-fill">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h5 class="font-weight-bold mb-0">{{ $reward->reward_name }}</h5>
                                            <span class="badge badge-{{ $reward->reward_type === 'voucher' ? 'danger' : 'warning' }} ml-2"
                                                  style="font-size: 0.75rem; padding: 0.35rem 0.75rem; border-radius: 12px;">
                                                {{ ucfirst($reward->reward_type) }}
                                            </span>
                                        </div>
                                        
                                        @php
                                            $shortDesc = $reward->description && strlen($reward->description) > 80 
                                                ? substr($reward->description, 0, 80) . '...' 
                                                : $reward->description;
                                        @endphp
                                        
                                        <p class="text-dark mb-2" style="font-size: 0.9rem;">
                                            {{ $shortDesc ?? 'Tidak ada deskripsi.' }}
                                        </p>
                                        
                                        @if($reward->description && strlen($reward->description) > 80)
                                            <a href="#" data-toggle="modal" data-target="#detailModal{{ $reward->reward_id }}" 
                                               class="text-primary" style="font-size: 0.85rem;">Lihat selengkapnya</a>
                                            <br>
                                        @endif
                                        
                                        <div class="mt-2">
                                            <span class="badge badge-info" style="font-size: 0.85rem; padding: 0.4rem 0.8rem; border-radius: 12px;">
                                                <i class="fa fa-coins mr-1"></i> {{ number_format($reward->points_required, 0, ',', '.') }} Poin
                                            </span>
                                        </div>
                                        
                                        <small class="text-muted d-block mt-2">
                                            <strong>Dibuat:</strong> {{ \Carbon\Carbon::parse($reward->created_at)->format('d-M-Y H:i') }}
                                        </small>
                                    </div>

                                    {{-- Tombol Edit dan Delete --}}
                                    <div class="ml-3 d-flex flex-column align-items-center justify-content-center">
                                        <button class="btn btn-link text-primary mb-3 p-0"
                                            data-toggle="modal"
                                            data-target="#editModal{{ $reward->reward_id }}"
                                            style="font-size: 1.8rem;">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-0"
                                            data-toggle="modal"
                                            data-target="#deleteModal{{ $reward->reward_id }}"
                                            style="font-size: 1.8rem;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Detail --}}
                            <div class="modal fade" id="detailModal{{ $reward->reward_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $reward->reward_name }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            @if($reward->reward_image)
                                                <div class="text-center mb-3">
                                                    <img src="{{ asset('storage/' . $reward->reward_image) }}" 
                                                         alt="{{ $reward->reward_name }}"
                                                         style="max-width: 200px; max-height: 200px; object-fit: contain; border-radius: 15px;">
                                                </div>
                                            @endif
                                            <p><strong>Tipe:</strong> <span class="badge badge-{{ $reward->reward_type === 'voucher' ? 'danger' : 'warning' }}">{{ ucfirst($reward->reward_type) }}</span></p>
                                            <p><strong>Poin Dibutuhkan:</strong> {{ number_format($reward->points_required, 0, ',', '.') }} Poin</p>
                                            <p><strong>Deskripsi:</strong></p>
                                            <p>{{ $reward->description ?? 'Tidak ada deskripsi.' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="editModal{{ $reward->reward_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.rewards.update', $reward->reward_id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Reward</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Nama Reward</label>
                                                    <input type="text" name="reward_name" class="form-control" value="{{ $reward->reward_name }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Deskripsi</label>
                                                    <textarea name="description" rows="3" class="form-control">{{ $reward->description }}</textarea>
                                                </div>
                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Poin Dibutuhkan</label>
                                                            <input type="number" name="points_required" class="form-control" value="{{ $reward->points_required }}" required min="0">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="form-group">
                                                            <label>Tipe Reward</label>
                                                            <select name="reward_type" class="form-control" required>
                                                                <option value="merchandise" {{ $reward->reward_type === 'merchandise' ? 'selected' : '' }}>Merchandise</option>
                                                                <option value="voucher" {{ $reward->reward_type === 'voucher' ? 'selected' : '' }}>Voucher</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group">
                                                    <label>Gambar Reward</label>
                                                    @if($reward->reward_image)
                                                        <div class="mb-2">
                                                            <img src="{{ asset('storage/' . $reward->reward_image) }}" 
                                                                 alt="Current" 
                                                                 style="max-width: 150px; max-height: 150px; object-fit: contain; border-radius: 10px;">
                                                            <p class="text-muted small mt-1">Gambar saat ini</p>
                                                        </div>
                                                    @endif
                                                    <input type="file" name="reward_image" class="form-control-file" accept="image/*">
                                                    <small class="form-text text-muted">Format: JPG, PNG, SVG (Max: 2MB)</small>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-primary"
                                                    style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                                                    Simpan
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Hapus --}}
                            <div class="modal fade" id="deleteModal{{ $reward->reward_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.rewards.destroy', $reward->reward_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Hapus Reward</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p>Apakah kamu yakin ingin menghapus <strong>{{ $reward->reward_name }}</strong>?</p>
                                                <p class="text-danger small">Tindakan ini tidak dapat dibatalkan.</p>
                                            </div>
                                            <div class="modal-footer justify-content-center">
                                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                <button type="submit" class="btn btn-danger">Hapus</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        @empty
                            <div class="col-md-12 text-center text-muted mt-3">
                                <i class="fa fa-gift fa-3x mb-3" style="color: #ddd;"></i>
                                <p>Belum ada reward tersedia saat ini.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginasi --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $rewards->links('pagination::bootstrap-4') }}
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah --}}
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('admin.rewards.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Reward Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Reward</label>
                        <input type="text" name="reward_name" class="form-control" placeholder="Contoh: Kaos Eksklusif" required>
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="description" rows="3" class="form-control" placeholder="Deskripsi reward..."></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Poin Dibutuhkan</label>
                                <input type="number" name="points_required" class="form-control" placeholder="1000" required min="0">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tipe Reward</label>
                                <select name="reward_type" class="form-control" required>
                                    <option value="">-- Pilih Tipe --</option>
                                    <option value="merchandise">Merchandise</option>
                                    <option value="voucher">Voucher</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Gambar Reward</label>
                        <input type="file" name="reward_image" class="form-control-file" accept="image/*">
                        <small class="form-text text-muted">Format: JPG, PNG, SVG (Max: 2MB)</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Smooth scroll ke atas saat ganti halaman
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        });
    });
</script>
@endsection
@endsection