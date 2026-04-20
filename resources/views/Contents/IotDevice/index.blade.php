@extends("Layouts.content_layout")

@section('title', 'Manajemen Perangkat IoT')
@section('page_title', 'Manajemen Perangkat IoT')
@section('page_subtitle', 'Kelola daftar alamat terminal SSH perangkat IoT dari Cloudflare Tunnel.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Perangkat IoT</h4>
                    <button class="btn btn-primary btn-round ml-auto" data-toggle="modal" data-target="#createModal">
                        <i class="fa fa-plus"></i>
                        Tambah Perangkat
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="iotdevice-table" class="display table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Perangkat</th>
                                    <th>URL SSH (Cloudflare)</th>
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
                <h5 class="modal-title text-white">Tambah Perangkat Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.iot-devices.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Perangkat <span class="text-danger">*</span></label>
                        <input type="text" name="device_name" class="form-control" placeholder="Masukkan nama perangkat..." required>
                    </div>
                    <div class="form-group">
                        <label>URL Akses SSH <span class="text-danger">*</span></label>
                        <input type="url" name="ssh_url" class="form-control" placeholder="https://ssh.namadomain.com" required>
                        <small class="form-text text-muted">Aplikasi Cloudflare Access yang mengatur Web SSH Terminal.</small>
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
                <h5 class="modal-title text-white">Edit Perangkat</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama Perangkat <span class="text-danger">*</span></label>
                        <input type="text" name="device_name" id="edit_device_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>URL Akses SSH <span class="text-danger">*</span></label>
                        <input type="url" name="ssh_url" id="edit_ssh_url" class="form-control" required>
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
        var table = $('#iotdevice-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.iot-devices.index') }}",
            order: [[1, 'asc']], 

            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'device_name', name: 'device_name' },
                { 
                    data: 'ssh_url', 
                    name: 'ssh_url',
                    render: function(data, type, row) {
                        return '<a href="' + data + '" target="_blank">' + data + '</a>';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // Event Listener untuk Tombol Edit di dalam Tabel
        $(document).on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var name = $(this).data('name');
            var url = $(this).data('url');
            var updateUrl = $(this).data('update-url');

            // Isi nilai ke dalam form modal
            $('#edit_device_name').val(name);
            $('#edit_ssh_url').val(url);
            $('#editForm').attr('action', updateUrl);

            // Tampilkan modal
            $('#editModal').modal('show');
        });

        // Re-init tooltip jika ada
        $('#iotdevice-table').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });
</script>
@endpush
