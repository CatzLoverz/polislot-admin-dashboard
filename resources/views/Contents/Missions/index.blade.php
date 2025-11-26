@extends("Layouts.content_layout")

@section('title', 'Manajemen Mission')
@section('page_title', 'Manajemen Mission')
@section('page_subtitle', 'Kelola misi dan tantangan untuk pengguna aplikasi.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 font-weight-bold text-dark">Daftar Mission</h2>
                        <div class="d-flex align-items-center">
                            <a href="{{ route('admin.missions.create') }}" class="btn btn-primary" style="border-radius: 15px; padding: 10px 25px;">
                                <i class="fa fa-plus mr-2"></i>Tambah Mission
                            </a>
                        </div>
                    </div>

                    {{-- Tabel Mission --}}
                    <div class="table-responsive">
                        <table class="table table-hover" style="border-radius: 15px; overflow: hidden;">
                            <thead style="background: linear-gradient(135deg, #1105ed 0%, #14b5eb 100%);">
                                <tr>
                                    <th class="text-white text-center" style="width: 5%; font-size: 1.1rem;">No</th>
                                    <th class="text-white" style="width: 20%; font-size: 1.1rem;">Nama Mission</th>
                                    <th class="text-white text-center" style="width: 15%; font-size: 1.1rem;">Tipe</th>
                                    <th class="text-white text-center" style="width: 10%; font-size: 1.1rem;">Target</th>
                                    <th class="text-white text-center" style="width: 10%; font-size: 1.1rem;">Reward</th>
                                    <th class="text-white text-center" style="width: 10%; font-size: 1.1rem;">Periode</th>
                                    <th class="text-white text-center" style="width: 10%; font-size: 1.1rem;">Status</th>
                                    <th class="text-white text-center" style="width: 10%; font-size: 1.1rem;">Detail</th>
                                    <th class="text-white text-center" style="width: 10%; font-size: 1.1rem;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($missions as $index => $mission)
                                    <tr style="font-size: 1.05rem;">
                                        <td class="text-center align-middle">
                                            {{ ($missions->currentPage() - 1) * $missions->perPage() + $index + 1 }}
                                        </td>
                                        <td class="align-middle font-weight-bold">{{ $mission->mission_name }}</td>
                                        <td class="text-center align-middle">
                                            @php
                                                $type = strtoupper($mission->mission_type);
                                                if (str_contains($type, 'LOGIN')) {
                                                    $badgeClass = 'badge-info';
                                                } elseif (str_contains($type, 'FEEDBACK')) {
                                                    $badgeClass = 'badge-success';
                                                } elseif (str_contains($type, 'REPORT')) {
                                                    $badgeClass = 'badge-warning';
                                                } else {
                                                    $badgeClass = 'badge-secondary';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" style="padding: 8px 16px; border-radius: 10px; font-size: 0.9rem;">
                                                {{ $mission->mission_type }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="font-weight-bold">{{ $mission->target_value }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-primary" style="padding: 8px 16px; border-radius: 10px; font-size: 0.95rem;">
                                                <i class="fa fa-star mr-1"></i>{{ $mission->reward_points }} pts
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @php
                                                $periodBadge = match($mission->period_type) {
                                                    'daily' => 'badge-info',
                                                    'weekly' => 'badge-warning',
                                                    'one_time' => 'badge-secondary',
                                                    default => 'badge-secondary'
                                                };
                                            @endphp
                                            <span class="badge {{ $periodBadge }}" style="padding: 8px 16px; border-radius: 10px; font-size: 0.9rem;">
                                                {{ ucfirst($mission->period_type) }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            @if($mission->is_active)
                                                <span class="badge badge-success" style="padding: 8px 16px; border-radius: 10px; font-size: 0.9rem;">
                                                    <i class="fa fa-check-circle mr-1"></i>Aktif
                                                </span>
                                            @else
                                                <span class="badge badge-danger" style="padding: 8px 16px; border-radius: 10px; font-size: 0.9rem;">
                                                    <i class="fa fa-times-circle mr-1"></i>Nonaktif
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center align-middle">
                                            {{-- Tombol Detail --}}
                                            <button class="btn btn-link text-primary p-0"
                                                data-toggle="modal"
                                                data-target="#detailModal{{ $mission->mission_id }}"
                                                title="Lihat Detail"
                                                style="font-size: 1.6rem;">
                                                <i class="fa fa-eye"></i>
                                            </button>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex justify-content-center align-items-center">
                                                {{-- Tombol Edit --}}
                                                <a href="{{ route('admin.missions.edit', $mission->mission_id) }}" 
                                                   class="btn btn-link text-warning p-0 mr-2"
                                                   title="Edit"
                                                   style="font-size: 1.6rem;">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                {{-- Tombol Delete --}}
                                                <button class="btn btn-link text-danger p-0"
                                                    data-toggle="modal"
                                                    data-target="#deleteModal{{ $mission->mission_id }}"
                                                    title="Hapus"
                                                    style="font-size: 1.6rem;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Modal Detail --}}
                                    <div class="modal fade" id="detailModal{{ $mission->mission_id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                                <div class="modal-header" style="background: linear-gradient(135deg, #6c63ff 0%, #5a52d5 100%);">
                                                    <h5 class="modal-title text-white">
                                                        <i class="fa fa-info-circle mr-2"></i>Detail Mission
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="max-height: 450px; overflow-y: auto; font-size: 1.05rem;">
                                                    <div class="mb-3">
                                                        <label class="text-muted mb-1" style="font-size: 0.95rem;">Nama Mission</label>
                                                        <h4 class="font-weight-bold">{{ $mission->mission_name }}</h4>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-muted mb-1" style="font-size: 0.95rem;">Deskripsi</label>
                                                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px; font-size: 1.05rem; line-height: 1.6;">
                                                            <p class="mb-0">{!! nl2br(e($mission->description ?? 'Tidak ada deskripsi')) !!}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Tipe Mission</label>
                                                            <p class="mb-0">
                                                                <span class="badge {{ $badgeClass }}" style="padding: 8px 16px; font-size: 1rem;">{{ $mission->mission_type }}</span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Periode</label>
                                                            <p class="mb-0">
                                                                <span class="badge {{ $periodBadge }}" style="padding: 8px 16px; font-size: 1rem;">{{ ucfirst($mission->period_type) }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Target</label>
                                                            <p class="mb-0 font-weight-bold" style="font-size: 1.1rem;">{{ $mission->target_value }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Reward Poin</label>
                                                            <p class="mb-0">
                                                                <span class="badge badge-primary" style="padding: 8px 16px; font-size: 1rem;">
                                                                    <i class="fa fa-star mr-1"></i>{{ $mission->reward_points }} poin
                                                                </span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-4">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Waktu Reset</label>
                                                            <p class="mb-0" style="font-size: 1.05rem;">{{ $mission->reset_time }}</p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Mulai</label>
                                                            <p class="mb-0" style="font-size: 1.05rem;">{{ \Carbon\Carbon::parse($mission->start_date)->format('d M Y') }}</p>
                                                        </div>
                                                        <div class="col-md-4">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Berakhir</label>
                                                            <p class="mb-0" style="font-size: 1.05rem;">{{ \Carbon\Carbon::parse($mission->end_date)->format('d M Y') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-muted mb-1" style="font-size: 0.95rem;">Status</label>
                                                        <p class="mb-0">
                                                            @if($mission->is_active)
                                                                <span class="badge badge-success" style="padding: 8px 16px; font-size: 1rem;">
                                                                    <i class="fa fa-check-circle mr-1"></i>Aktif
                                                                </span>
                                                            @else
                                                                <span class="badge badge-danger" style="padding: 8px 16px; font-size: 1rem;">
                                                                    <i class="fa fa-times-circle mr-1"></i>Nonaktif
                                                                </span>
                                                            @endif
                                                        </p>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px;">
                                                        Tutup
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Modal Hapus --}}
                                    <div class="modal fade" id="deleteModal{{ $mission->mission_id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                                <form action="{{ route('admin.missions.destroy', $mission->mission_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header bg-danger">
                                                        <h5 class="modal-title text-white">
                                                            <i class="fa fa-exclamation-triangle mr-2"></i>Hapus Mission
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center py-4" style="font-size: 1.05rem;">
                                                        <i class="fa fa-trash fa-3x text-danger mb-3"></i>
                                                        <p class="mb-0 font-weight-bold">Yakin ingin menghapus mission ini?</p>
                                                        <p class="text-muted mb-0">{{ $mission->mission_name }}</p>
                                                        <small class="text-muted">Tindakan ini tidak dapat dibatalkan.</small>
                                                    </div>
                                                    <div class="modal-footer justify-content-center">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal" style="border-radius: 10px;">
                                                            Batal
                                                        </button>
                                                        <button type="submit" class="btn btn-danger" style="border-radius: 10px;">
                                                            <i class="fa fa-trash mr-1"></i> Hapus
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-5">
                                            <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                                            <p class="mb-0">Tidak ada mission saat ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginasi --}}
                    @if($missions->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $missions->links('pagination::bootstrap-4') }}
                        </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        });
    });
</script>
@endsection

@endsection