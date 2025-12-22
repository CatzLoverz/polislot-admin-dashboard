@extends("Layouts.content_layout")

@section('title', 'Manajemen Info Board')
@section('page_title', 'Manajemen Info Board')
@section('page_subtitle', 'Kelola daftar pengumuman penting untuk pengguna.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Pengumuman</h4>
                    <button class="btn btn-primary btn-round ml-auto" data-toggle="modal" data-target="#createModal">
                        <i class="fa fa-plus"></i>
                        Tambah Pengumuman
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="infoboard-table" class="display table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Judul</th>
                                    <th>Isi Pengumuman</th>
                                    <th>Dibuat Oleh</th>
                                    <th>Dibuat Pada</th>
                                    <th>Terakhir Update</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables Server-side --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Tambah (Create) --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Tambah Pengumuman Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.info-board.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" name="info_title" class="form-control" placeholder="Masukkan judul..." required>
                    </div>
                    <div class="form-group">
                        <label>Isi Pengumuman <span class="text-danger">*</span></label>
                        <textarea name="info_content" rows="5" class="form-control" placeholder="Masukkan isi pengumuman..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Edit (Global) --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Edit Pengumuman</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Judul Pengumuman <span class="text-danger">*</span></label>
                        <input type="text" name="info_title" id="edit_info_title" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Isi Konten <span class="text-danger">*</span></label>
                        <textarea name="info_content" id="edit_info_content" rows="5" class="form-control" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTables
        var table = $('#infoboard-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.info-board.index') }}",
            order: [[4, 'desc']], 

            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'info_title', name: 'info_title' },
                { 
                    data: 'info_content', 
                    name: 'info_content',
                    render: function(data, type, row) {
                        if (!data) return '';

                        // Style agar enter terbaca (pre-line) dan text turun (break-word)
                        var style = 'white-space: pre-line; word-break: break-word; min-width: 300px; max-width: 500px;';

                        // Jika teks pendek (< 50 karakter), tampilkan langsung
                        if (type !== 'display' || data.length <= 50) {
                            return `<div style="${style}">${data}</div>`;
                        }

                        // Jika teks panjang (> 50 karakter), potong dan tambahkan tombol
                        var shortText = data.substr(0, 50) + '...';
                        var fullText = data; 

                        return `<div class="content-wrapper" style="${style}">` +
                                    `<span class="text-short">${shortText}</span>` +
                                    `<span class="text-full" style="display: none;">${fullText}</span>` +
                                `</div>` +
                                `<a href="javascript:void(0);" class="text-primary btn-toggle-text mt-1 d-inline-block" style="font-size: 0.85rem;">` +
                                    `Lihat Selengkapnya` +
                                `</a>`;
                    }
                },
                { data: 'creator_name', name: 'user.name', orderable: false },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // Event Listener untuk Tombol Lihat Selengkapnya / Tutup
        $('#infoboard-table').on('click', '.btn-toggle-text', function() {
            var wrapper = $(this).prev('.content-wrapper');
            var isExpanded = $(this).hasClass('expanded');

            if (isExpanded) {
                // Tutup
                wrapper.find('.text-short').show();
                wrapper.find('.text-full').hide();
                $(this).text('Lihat Selengkapnya').removeClass('expanded');
            } else {
                // Buka
                wrapper.find('.text-short').hide();
                wrapper.find('.text-full').show();
                $(this).text('Tutup').addClass('expanded');
            }
        });

        // Event Listener untuk Tombol Edit di dalam Tabel
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var title = $(this).data('title');
            var content = $(this).data('content');
            var url = $(this).data('update-url');

            // Isi nilai ke dalam form modal
            $('#edit_info_title').val(title);
            $('#edit_info_content').val(content);
            $('#editForm').attr('action', url);

            // Tampilkan modal
            $('#editModal').modal('show');
        });

        // Re-init tooltip jika ada
        $('#infoboard-table').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });
</script>
@endpush