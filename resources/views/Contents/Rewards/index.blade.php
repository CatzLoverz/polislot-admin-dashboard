@extends("Layouts.content_layout")

@section('title', 'Manajemen Rewards')
@section('page_title', 'Manajemen Rewards')
@section('page_subtitle', 'Kelola hadiah dan voucher untuk pengguna.')

@section('content')
<div class="page-inner mt--5">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h4 class="card-title">Daftar Reward</h4>
            <button class="btn btn-primary btn-round ml-auto" data-toggle="modal" data-target="#modalReward" id="btnAdd">
                <i class="fa fa-plus"></i> Tambah Reward
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="tableReward" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center">Gambar</th>
                            <th>Nama Reward</th>
                            <th>Tipe</th>
                            <th>Poin Diperlukan</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- MODAL CREATE/EDIT (Style disesuaikan dengan Mission Index) --}}
<div class="modal fade" id="modalReward" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitle">Form Reward</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formReward" method="POST" enctype="multipart/form-data">
                @csrf
                <div id="methodPut"></div>
                
                <div class="modal-body">
                    <h6 class="font-weight-bold text-primary mb-3">Informasi Reward</h6>
                    
                    <div class="form-group">
                        <label>Nama Reward <span class="text-danger">*</span></label>
                        <input type="text" name="reward_name" class="form-control" placeholder="Contoh: Voucher Belanja 50k" required>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Tipe Reward <span class="text-danger">*</span></label>
                            <select name="reward_type" class="form-control" required>
                                <option value="Voucher">Voucher</option>
                                <option value="Barang">Barang Fisik</option>
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Poin Required <span class="text-danger">*</span></label>
                            <input type="number" name="reward_point_required" class="form-control" min="1" placeholder="0" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Gambar (Opsional)</label>
                        <input type="file" name="reward_image" class="form-control-file">
                        <small class="text-muted">Format: JPG, PNG. Max: 2MB</small>
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
        var table = $('#tableReward').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.rewards.index') }}",
            order: [[2, 'asc']],
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                { data: 'reward_image', className: 'text-center', orderable: false, searchable: false },
                { data: 'reward_name', name: 'reward_name' },
                { data: 'reward_type', className: 'text-center' },
                { data: 'reward_point_required'},
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // Re-init Tooltip saat table draw
        $('#tableReward').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });

        // Tombol Tambah
        $('#btnAdd').click(function() {
            $('#modalTitle').text('Reward Baru');
            $('#formReward')[0].reset();
            $('#formReward').attr('action', "{{ route('admin.rewards.store') }}");
            $('#methodPut').empty();
        });

        // Tombol Edit
        $(document).on('click', '.btn-edit', function() {
            var row = $(this).data('row'); // JSON otomatis diparsing oleh jQuery
            var url = $(this).data('update-url');

            $('#modalTitle').text('Edit Reward');
            $('#formReward').attr('action', url);
            $('#methodPut').html('@method("PUT")');

            $('input[name="reward_name"]').val(row.reward_name);
            $('input[name="reward_point_required"]').val(row.reward_point_required);
            $('select[name="reward_type"]').val(row.reward_type);
            
            $('#modalReward').modal('show');
        });
    });
</script>
@endpush