@extends("Layouts.content_layout")

@section('title', 'Pengaturan Validasi')
@section('page_title', 'Pengaturan Validasi')
@section('page_subtitle', 'Atur jumlah Koin yang didapatkan user saat melakukan validasi.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex align-items-center">
                        <h4 class="card-title">Daftar Pengaturan koin</h4>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="basic-datatables" class="display table table-striped table-hover" >
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Koin per Validasi</th>
                                    <th>Geofencing</th>
                                    <th>Terakhir Diupdate</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Data dimuat via AJAX DataTables --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Poin --}}
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Edit Koin Validasi
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label>Jumlah Koin <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" name="validation_points" id="edit_points" class="form-control" required min="1">
                            <div class="input-group-append">
                                <span class="input-group-text font-weight-bold">Koin</span>
                            </div>
                        </div>
                        <small class="form-text text-muted">User akan mendapatkan poin ini setiap kali validasi.</small>
                    </div>

                    {{-- Geofence Toggle --}}
                    <div class="form-group">
                        <label>Batasan Lokasi (Geofencing)</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" class="custom-control-input" id="edit_geofence" name="validation_is_geofence_active" value="1">
                            <label class="custom-control-label" for="edit_geofence">Wajib berada di lokasi parkir</label>
                        </div>
                        <small class="form-text text-muted">Jika aktif, user harus berada dalam radius area parkir untuk melakukan validasi.</small>
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
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Init DataTables
        var table = $('#basic-datatables').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.validation.index') }}",
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                { data: 'validation_points', name: 'validation_points' },
                { data: 'geofencing', name: 'geofencing' },
                { data: 'updated_at', name: 'updated_at' },
                { data: 'action', name: 'action', orderable: false, searchable: false }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // Handle Tombol Edit (Populate Modal)
        $('body').on('click', '.btn-edit', function() {
            var id = $(this).data('id');
            var points = $(this).data('points');
            var isGeofence = $(this).data('geofence'); // 0 or 1
            var url = $(this).data('update-url');

            $('#edit_points').val(points);
            $('#edit_geofence').prop('checked', isGeofence == 1); // Set checkbox state
            $('#editForm').attr('action', url); // Set action form ke URL update spesifik ID
            $('#modalEdit').modal('show');
        });
    });
</script>
@endpush