@extends("Layouts.content_layout")

@section('title', 'Verifikasi Reward')
@section('page_title', 'Verifikasi Kode Reward')
@section('page_subtitle', 'Verifikasi dan ubah status kode voucher pengguna.')

@section('content')
<div class="page-inner mt--5">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title">Antrian Klaim Reward</h4>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_status">Filter Status</label>
                        <select id="filter_status" class="form-control">
                            <option value="">Semua Status</option>
                            <option value="pending" selected>Pending (Default)</option>
                            <option value="accepted">Accepted</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="filter_type">Filter Tipe Reward</label>
                        <select id="filter_type" class="form-control">
                            <option value="">Semua Tipe Reward</option>
                            <option value="Voucher">Voucher</option>
                            <option value="Barang">Barang</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4 d-flex align-items-end justify-content-end">
                    <div class="form-group">
                        <label style="visibility: hidden;">Aksi</label><br>
                        <button id="apply_filter" class="btn btn-primary">Terapkan Filter</button>
                        <button id="reset_filter" class="btn btn-danger">Batal</button>
                    </div>
                </div>
            </div>
            <div class="table-responsive">
                <table id="tableVerify" class="table table-striped table-hover" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>User</th>
                            <th>Reward Info</th>
                            <th class="text-center">Kode Tiket</th>
                            <th>Tgl Request</th>
                            <th>Tgl Proses</th> {{-- Kolom Baru --}}
                            <th class="text-center">Status</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        var table = $('#tableVerify').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.rewards.verify.index') }}",
                data: function(d) {
                    d.filter_status = $('#filter_status').val();
                    d.filter_type = $('#filter_type').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                { data: 'user_name', name: 'user.name' },
                { data: 'reward_info', name: 'reward.reward_name' },
                { data: 'user_reward_code', className: 'text-center' },
                { data: 'created_at', name: 'created_at' },
                { data: 'updated_at', name: 'updated_at' }, // Kolom Baru
                { data: 'user_reward_status', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ],
            order: [], 
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        $('#apply_filter').click(function() {
            table.draw();
        });

        $('#reset_filter').click(function() {
            $('#filter_status').val('pending');
            $('#filter_type').val('');
            table.draw();
        });

        // Draw Callback
        $('#tableVerify').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
            
            // SweetAlert KHUSUS REJECT
            $('.reject-form').on('submit', function(e) {
                e.preventDefault();
                var form = this;
                var msg = $(this).data('msg') || "Anda yakin ingin menolak klaim ini?";
                
                Swal.fire({
                    title: 'Konfirmasi Penolakan',
                    html: msg, 
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) { 
                        form.submit(); 
                    }
                });
            });
        });
    });
</script>
@endpush