@extends("Layouts.content_layout")

@section('title', 'Feedback Pengguna')
@section('page_title', 'Feedback Pengguna')
@section('page_subtitle', 'Kumpulan saran dan masukan dari pengguna aplikasi.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">

                    {{-- Header --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 font-weight-bold text-dark">Daftar Masukan Pengguna</h2>
                        <span class="badge badge-primary" style="font-size: 0.95rem; padding: 10px 20px; border-radius: 20px;">
                            Total: {{ $feedbacks->total() }} masukan
                        </span>
                    </div>

                    {{-- Tabel Feedback --}}
                    <div class="table-responsive">
                        <table class="table table-hover" style="border-radius: 15px; overflow: hidden;">
                            <thead style="background: linear-gradient(135deg, #1105ed 0%, #14b5eb 100%);">
                                <tr>
                                    <th class="text-white text-center" style="width: 5%; font-size: 1.1rem;">No</th>
                                    <th class="text-white" style="width: 30%; font-size: 1.1rem;">Judul</th>
                                    <th class="text-white text-center" style="width: 15%; font-size: 1.1rem;">Kategori</th>
                                    <th class="text-white text-center" style="width: 15%; font-size: 1.1rem;">Tipe</th>
                                    <th class="text-white text-center" style="width: 20%; font-size: 1.1rem;">Tanggal</th>
                                    <th class="text-white text-center" style="width: 15%; font-size: 1.1rem;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($feedbacks as $index => $fb)
                                    <tr style="font-size: 1.05rem;">
                                        <td class="text-center align-middle">
                                            {{ ($feedbacks->currentPage() - 1) * $feedbacks->perPage() + $index + 1 }}
                                        </td>
                                        <td class="align-middle font-weight-bold">{{ $fb->title }}</td>
                                        <td class="text-center align-middle">
                                            @php
                                                $cat = strtolower($fb->category);
                                                if (str_contains($cat, 'bug')) {
                                                    $badgeClass = 'badge-danger';
                                                } elseif (str_contains($cat, 'fitur')) {
                                                    $badgeClass = 'badge-success';
                                                } else {
                                                    $badgeClass = 'badge-info';
                                                }
                                            @endphp
                                            <span class="badge {{ $badgeClass }}" style="padding: 8px 16px; border-radius: 10px; font-size: 0.95rem;">
                                                {{ $fb->category }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-secondary" style="padding: 8px 16px; border-radius: 10px; font-size: 0.95rem;">
                                                {{ $fb->feedback_type }}
                                            </span>
                                        </td>
                                        <td class="text-center align-middle">
                                            {{ \Carbon\Carbon::parse($fb->created_at)->format('d M Y') }}<br>
                                            <span class="text-muted">{{ \Carbon\Carbon::parse($fb->created_at)->format('H:i') }}</span>
                                        </td>
                                        <td class="text-center align-middle">
                                            <div class="d-flex flex-column align-items-center justify-content-center">
                                                {{-- Tombol Detail --}}
                                                <button class="btn btn-link text-primary mb-2 p-0"
                                                    data-toggle="modal"
                                                    data-target="#detailModal{{ $fb->feedback_id }}"
                                                    title="Lihat Detail"
                                                    style="font-size: 1.6rem;">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                                {{-- Tombol Delete --}}
                                                <button class="btn btn-link text-danger p-0"
                                                    data-toggle="modal"
                                                    data-target="#deleteModal{{ $fb->feedback_id }}"
                                                    title="Hapus"
                                                    style="font-size: 1.6rem;">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- Modal Detail --}}
                                    <div class="modal fade" id="detailModal{{ $fb->feedback_id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-lg modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                                <div class="modal-header" style="background: linear-gradient(135deg, #6c63ff 0%, #5a52d5 100%);">
                                                    <h5 class="modal-title text-white">
                                                        <i class="fa fa-info-circle mr-2"></i>Detail Masukan
                                                    </h5>
                                                    <button type="button" class="close text-white" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <div class="modal-body" style="max-height: 450px; overflow-y: auto; font-size: 1.05rem;">
                                                    <div class="mb-3">
                                                        <label class="text-muted mb-1" style="font-size: 0.95rem;">Judul</label>
                                                        <h4 class="font-weight-bold">{{ $fb->title }}</h4>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Kategori</label>
                                                            <p class="mb-0">
                                                                <span class="badge {{ $badgeClass }}" style="padding: 8px 16px; font-size: 1rem;">{{ $fb->category }}</span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Tipe</label>
                                                            <p class="mb-0">
                                                                <span class="badge badge-secondary" style="padding: 8px 16px; font-size: 1rem;">{{ $fb->feedback_type }}</span>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="text-muted mb-1" style="font-size: 0.95rem;">Deskripsi</label>
                                                        <div class="p-3" style="background: #f8f9fa; border-radius: 10px; max-height: 200px; overflow-y: auto; font-size: 1.05rem; line-height: 1.6;">
                                                            <p class="mb-0">{!! nl2br(e($fb->description)) !!}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <label class="text-muted mb-1" style="font-size: 0.95rem;">Dikirim</label>
                                                            <p class="mb-0" style="font-size: 1.05rem;">{{ \Carbon\Carbon::parse($fb->created_at)->format('d M Y - H:i') }}</p>
                                                        </div>
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
                                    <div class="modal fade" id="deleteModal{{ $fb->feedback_id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content" style="border-radius: 20px; overflow: hidden;">
                                                <form action="{{ route('admin.feedback.destroy', $fb->feedback_id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <div class="modal-header bg-danger">
                                                        <h5 class="modal-title text-white">
                                                            <i class="fa fa-exclamation-triangle mr-2"></i>Hapus Feedback
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal">
                                                            <span>&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body text-center py-4" style="font-size: 1.05rem;">
                                                        <i class="fa fa-trash fa-3x text-danger mb-3"></i>
                                                        <p class="mb-0">Yakin ingin menghapus feedback ini?</p>
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
                                        <td colspan="7" class="text-center text-muted py-5">
                                            <i class="fa fa-inbox fa-3x mb-3 d-block"></i>
                                            <p class="mb-0">Tidak ada feedback saat ini.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Paginasi --}}
                    @if($feedbacks->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $feedbacks->links('pagination::bootstrap-4') }}
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