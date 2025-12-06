@extends("Layouts.content_layout")

@section('title', 'Kategori Feedback')
@section('page_title', 'Kategori Feedback')
@section('page_subtitle', 'Kelola kategori untuk pengelompokan masukan pengguna.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title mb-2 mb-md-0">Daftar Kategori Feedback</h4>
                    <div class="form-group mb-0 d-flex align-items-center">
                        <a href="{{ route('admin.feedback.index') }}" class="btn btn-primary btn-round ml-auto text-white" style="text-decoration: none;">
                            Kembali
                        </a>
                        <button class="btn btn-secondary btn-round ml-2" data-toggle="modal" data-target="#createModal">
                            <i class="fa fa-plus"></i>
                            Tambah Kategori
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="category-table" class="display table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Kategori</th>
                                    <th>Tanggal Dibuat</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh DataTables AJAX --}}
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
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Tambah Kategori Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.feedback-category.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="fbk_category_name" class="form-control" placeholder="Masukkan nama kategori" required>
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
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Edit Kategori</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Kategori <span class="text-danger">*</span></label>
                        <input type="text" name="fbk_category_name" id="edit_name" class="form-control" required>
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
        var table = $('#category-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.feedback-category.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'fbk_category_name', name: 'fbk_category_name' },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // Event Listener Tombol Edit
        $(document).on('click', '.btn-edit', function() {
            var name = $(this).data('name');
            var url = $(this).data('update-url');

            // Isi Form di Modal
            $('#edit_name').val(name);
            $('#editForm').attr('action', url);

            // Tampilkan Modal
            $('#editModal').modal('show');
        });

        // Re-init tooltips saat tabel di-redraw
        $('#category-table').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });
</script>
@endpush