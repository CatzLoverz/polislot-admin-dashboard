@extends("Layouts.content_layout")

@section('title', 'Info Board')
@section('page_title', 'Info Board')
@section('page_subtitle', 'Perbarui dan publikasikan pengumuman penting untuk semua pengguna.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">

                    {{-- Tombol tambah --}}
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h2 class="mb-0 font-weight-bold text-dark">Pengumuman Penting</h2>
                        <button class="btn btn-primary" data-toggle="modal" data-target="#createModal"
                            style="border-radius: 10px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                            <i class="fa fa-plus mr-2"></i> Pengumuman Baru
                        </button>
                    </div>
                    <hr>
                    {{-- Daftar pengumuman --}}
                    <div class="row">
                        @forelse($infoBoards as $info)
                            <div class="col-md-12 mb-4">
                                <div class="p-4 d-flex align-items-start justify-content-between"
                                    style="background: linear-gradient(135deg, #eef3ff 0%, #d8e0ff 100%);
                                           border-radius: 25px;
                                           box-shadow: 0 6px 16px rgba(0, 0, 0, 0.08);
                                           transition: all 0.25s ease;">
                                    
                                    {{-- Icon Berdasarkan Judul --}}
                                    <div class="mr-4">
                                        @php
                                            $title = strtolower($info->title);
                                            if (str_contains($title, 'mobil')) {
                                                $icon = 'fa-car';
                                                $color = '#6c63ff';
                                            } elseif (str_contains($title, 'motor')) {
                                                $icon = 'fa-motorcycle';
                                                $color = '#6c63ff';
                                            } else {
                                                $icon = 'fa-bullhorn';
                                                $color = '#6c63ff';
                                            }

                                            $shortContent = strlen($info->info_content) > 100 
                                                ? substr($info->info_content, 0, 100) . '...' 
                                                : $info->info_content;
                                        @endphp
                                        <i class="fa {{ $icon }} fa-4x" style="color: {{ $color }};"></i>
                                    </div>

                                    {{-- Konten Pengumuman --}}
                                    <div class="flex-fill">
                                        <h5 class="font-weight-bold mb-2">{{ $info->info_title }}</h5>
                                        <p class="text-dark mb-2" style="font-size: 0.95rem; max-width: 95%;">
                                            "{!! nl2br(e($shortContent)) !!}"
                                        </p>
                                        @if(strlen($info->info_content) > 100)
                                            <a href="#" data-toggle="modal" data-target="#detailModal{{ $info->info_id }}" class="text-primary" style="font-size: 0.9rem;">Lihat selengkapnya</a>
                                        @endif
                                        <br>
                                        <small class="text-muted">
                                            <strong>Time:</strong> {{ ($info->created_at)->format('H:i') }} <br>
                                            <strong>Update:</strong> {{ ($info->updated_at)->format('d-M-Y') }}
                                        </small>
                                    </div>

                                    {{-- Tombol Edit dan Delete --}}
                                    <div class="ml-4 d-flex flex-column align-items-center justify-content-center">
                                        <button class="btn btn-link text-primary mb-3 p-0"
                                            data-toggle="modal"
                                            data-target="#editModal{{ $info->info_id }}"
                                            style="font-size: 1.8rem;">
                                            <i class="fa fa-edit"></i>
                                        </button>
                                        <button class="btn btn-link text-danger p-0"
                                            data-toggle="modal"
                                            data-target="#deleteModal{{ $info->info_id }}"
                                            style="font-size: 1.8rem;">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Detail --}}
                            <div class="modal fade" id="detailModal{{ $info->info_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">{{ $info->info_title }}</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>{!! nl2br(e($info->info_content)) !!}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Modal Edit --}}
                            <div class="modal fade" id="editModal{{ $info->info_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-lg modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.info-board.update', $info->info_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Pengumuman</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group">
                                                    <label>Judul Pengumuman</label>
                                                    <input type="text" name="info_title" class="form-control" value="{{ $info->info_title }}" required>
                                                </div>
                                                <div class="form-group">
                                                    <label>Isi Pengumuman</label>
                                                    <textarea name="info_content" rows="5" class="form-control" required>{{ $info->info_content }}</textarea>
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
                            <div class="modal fade" id="deleteModal{{ $info->info_id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.info-board.destroy', $info->info_id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <div class="modal-header">
                                                <h5 class="modal-title">Hapus Pengumuman</h5>
                                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                    <span>&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body text-center">
                                                <p>Apakah kamu yakin ingin menghapus <strong>{{ $info->info_title }}</strong>?</p>
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
                                <p>Tidak ada pengumuman saat ini.</p>
                            </div>
                        @endforelse
                    </div>

                    {{-- Paginasi --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $infoBoards->links('pagination::bootstrap-4') }}
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
            <form action="{{ route('admin.info-board.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Pengumuman Baru</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul Pengumuman</label>
                        <input type="text" name="info_title" class="form-control" placeholder="Masukkan judul..." required>
                    </div>
                    <div class="form-group">
                        <label>Isi Pengumuman</label>
                        <textarea name="info_content" rows="5" class="form-control" placeholder="Masukkan isi pengumuman..." required></textarea>
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
        // Tambahkan smooth scroll ke atas saat ganti halaman
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        });
    });
</script>
@endsection
@endsection
