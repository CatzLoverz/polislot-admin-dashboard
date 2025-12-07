@extends("Layouts.content_layout")

@section('title', 'Manajemen Misi')
@section('page_title', 'Manajemen Misi')
@section('page_subtitle', 'Kelola daftar misi, aturan target, dan poin reward.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Misi Tersedia</h4>
                    <button class="btn btn-primary btn-round ml-auto" data-toggle="modal" data-target="#createModal">
                        <i class="fa fa-plus"></i>
                        Tambah Misi Baru
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        {{-- Perhatikan ID table disesuaikan dengan script --}}
                        <table id="mission-table" class="display table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Judul Misi</th>
                                    <th>Reward</th>
                                    <th>Tipe</th>
                                    <th>Detail Aturan</th>
                                    <th>Status</th>
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

{{-- ======================================================================================= --}}
{{-- MODAL CREATE (TAMBAH) --}}
{{-- ======================================================================================= --}}
<div class="modal fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Buat Misi Baru</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- Pastikan route sesuai dengan Controller (plural: missions) --}}
            <form action="{{ route('admin.missions.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    
                    {{-- 1. Informasi Dasar & Metric --}}
                    <h6 class="text-uppercase font-weight-bold text-primary mb-3">Informasi Dasar</h6>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Judul Misi <span class="text-danger">*</span></label>
                                <input type="text" name="mission_title" class="form-control" placeholder="Contoh: Raja Validasi" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Reward Poin <span class="text-danger">*</span></label>
                                <input type="number" name="mission_points" class="form-control" placeholder="0" min="0" required>
                            </div>
                        </div>
                    </div>

                    {{-- UPDATE: Metric Code Pindah ke Sini (Global) --}}
                    <div class="form-group">
                        <label>Event Pemicu (Metric) <span class="text-danger">*</span></label>
                        <select name="mission_metric_code" id="create_mission_metric_code" class="form-control" required disabled>
                            <option value="" selected disabled>-- Pilih Tipe Misi Terlebih Dahulu --</option>
                        </select>
                        <small class="text-muted">Pilih aksi apa yang dilakukan user untuk menaikkan progress misi ini.</small>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="mission_description" rows="2" class="form-control" placeholder="Penjelasan singkat misi..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="datetime-local" name="mission_start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Berakhir</label>
                                <input type="datetime-local" name="mission_end_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-check mt-2">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="mission_is_active" value="1" checked>
                            <span class="form-check-sign font-weight-bold">Aktifkan Misi Segera?</span>
                        </label>
                    </div>

                    <hr>

                    {{-- 2. Konfigurasi Tipe Misi --}}
                    <h6 class="text-uppercase font-weight-bold text-warning mb-3">Aturan Main</h6>
                    
                    <div class="form-group">
                        <label>Pilih Tipe Perhitungan <span class="text-danger">*</span></label>
                        <select name="mission_type" id="create_mission_type" class="form-control" required>
                            <option value="" selected disabled>-- Pilih Tipe --</option>
                            <option value="TARGET">TARGET (Akumulasi Total)</option>
                            <option value="SEQUENCE">SEQUENCE (Trigger Harian)</option>
                        </select>
                    </div>

                    {{-- Dynamic Field: TARGET --}}
                    <div id="create_target_fields" style="display: none; background: #fff3cd; padding: 15px; border-radius: 5px;">
                        <div class="form-group">
                            <label>Target Jumlah (Amount) <span class="text-danger">*</span></label>
                            <input type="number" name="mission_target_amount" class="form-control" placeholder="Misal: 50">
                            <small class="text-muted">Berapa total kali user harus melakukan aksi di atas?</small>
                        </div>
                    </div>

                    {{-- Dynamic Field: SEQUENCE --}}
                    <div id="create_sequence_fields" style="display: none; background: #fff3cd; padding: 15px; border-radius: 5px;">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Durasi Hari (Days Required) <span class="text-danger">*</span></label>
                                    <input type="number" name="mission_days_required" class="form-control" placeholder="Misal: 7">
                                    <small class="text-muted">Berapa hari user harus melakukan aksi ini?</small>
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center">
                                {{-- UPDATE: Checkbox Consecutive --}}
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" name="mission_is_consecutive" value="1" checked>
                                        <span class="form-check-sign font-weight-bold text-dark">Harus Berurut?</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted pl-2">Jika "Harus Berurut" dicentang, progress akan reset ke 0 jika user bolos sehari.</small>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan Misi</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ======================================================================================= --}}
{{-- MODAL EDIT (UPDATE) --}}
{{-- ======================================================================================= --}}
<div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white">Edit Misi</h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    {{-- Informasi Umum --}}
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>Judul Misi <span class="text-danger">*</span></label>
                                <input type="text" name="mission_title" id="edit_mission_title" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>Reward Poin <span class="text-danger">*</span></label>
                                <input type="number" name="mission_points" id="edit_mission_points" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    {{-- UPDATE: Dropdown Metric Code di Edit --}}
                    <div class="form-group">
                        <label>Event Pemicu (Metric) <span class="text-danger">*</span></label>
                        <select name="mission_metric_code" id="edit_mission_metric_code" class="form-control" required>
                            <option value="" disabled>-- Pilih Aksi User --</option>
                            @foreach($metrics as $code => $label)
                                <option value="{{ $code }}">{{ $label }} ({{ $code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="mission_description" id="edit_mission_description" rows="2" class="form-control"></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Mulai</label>
                                <input type="datetime-local" name="mission_start_date" id="edit_mission_start_date" class="form-control">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>Tanggal Berakhir</label>
                                <input type="datetime-local" name="mission_end_date" id="edit_mission_end_date" class="form-control">
                            </div>
                        </div>
                    </div>

                    <div class="form-check mt-2">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="mission_is_active" id="edit_mission_is_active" value="1">
                            <span class="form-check-sign font-weight-bold">Status Aktif</span>
                        </label>
                    </div>

                    <hr>

                    {{-- Tipe Misi --}}
                    <div class="form-group">
                        <label>Tipe Misi (Read Only)</label>
                        <input type="text" id="edit_mission_type_display" class="form-control" readonly>
                    </div>

                    {{-- Dynamic Field: TARGET --}}
                    <div id="edit_target_fields" style="display: none; background: #fff3cd; padding: 15px; border-radius: 5px;">
                        <div class="form-group">
                            <label>Target Jumlah (Amount)</label>
                            <input type="number" name="mission_target_amount" id="edit_mission_target_amount" class="form-control">
                        </div>
                    </div>

                    {{-- Dynamic Field: SEQUENCE --}}
                    <div id="edit_sequence_fields" style="display: none; background: #fff3cd; padding: 15px; border-radius: 5px;">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label>Durasi Hari (Days Required)</label>
                                    <input type="number" name="mission_days_required" id="edit_mission_days_required" class="form-control">
                                </div>
                            </div>
                            <div class="col-md-4 d-flex align-items-center">
                                {{-- UPDATE: Checkbox Consecutive di Edit --}}
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" name="mission_is_consecutive" id="edit_mission_is_consecutive" value="1">
                                        <span class="form-check-sign font-weight-bold text-dark">Harus Berurut?</span>
                                    </label>
                                </div>
                            </div>
                        </div>
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
    // 0. Siapkan Data dari Controller ke JS
    const ALL_METRICS = @json($metrics);      // {CODE: Label}
    const METRIC_GROUPS = @json($metricTypes); // {TARGET: [CODE, CODE], SEQUENCE: [...]}

    // Helper Function: Update isi Dropdown Metric
    function updateMetricOptions(type, selector, selectedValue = null) {
        var $dropdown = $(selector);
        $dropdown.empty(); // Kosongkan dulu

        if (!type || !METRIC_GROUPS[type]) {
            $dropdown.append('<option value="" selected disabled>-- Pilih Tipe Misi Dulu --</option>');
            $dropdown.prop('disabled', true);
            return;
        }

        // Enable dropdown
        $dropdown.prop('disabled', false);
        $dropdown.append('<option value="" selected disabled>-- Pilih Aksi User --</option>');

        // Loop group yang sesuai
        METRIC_GROUPS[type].forEach(function(code) {
            var label = ALL_METRICS[code];
            var isSelected = (selectedValue === code) ? 'selected' : '';
            $dropdown.append(`<option value="${code}" ${isSelected}>${label} (${code})</option>`);
        });
    }

    $(document).ready(function() {
        // 1. Inisialisasi DataTables
        var table = $('#mission-table').DataTable({
            processing: true,
            serverSide: true,
            
            ajax: "{{ route('admin.missions.index') }}", // Pastikan route plural 'missions'
            order: [[0, 'desc']],

            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'mission_title', name: 'mission_title' },
                { data: 'mission_points', name: 'mission_points', className: 'text-right' },
                { data: 'mission_type', name: 'mission_type', className: 'text-center' },
                { data: 'rule_detail', name: 'rule_detail', orderable: false, searchable: false }, // Menggunakan rule_detail dari controller baru
                { data: 'mission_is_active', name: 'mission_is_active', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // 2. Logic Toggle Form pada Modal CREATE
        $('#create_mission_type').change(function() {
            var type = $(this).val();
            
            // 1. Update Dropdown Metric sesuai Tipe
            updateMetricOptions(type, '#create_mission_metric_code');

            // 2. Toggle Form Fields (Code Lama Anda)
            if (type === 'TARGET') {
                $('#create_target_fields').slideDown();
                $('#create_sequence_fields').hide();
                $('input[name="mission_target_amount"]').prop('required', true);
                $('input[name="mission_days_required"]').prop('required', false);
            } else if (type === 'SEQUENCE') {
                $('#create_target_fields').hide();
                $('#create_sequence_fields').slideDown();
                $('input[name="mission_target_amount"]').prop('required', false);
                $('input[name="mission_days_required"]').prop('required', true);
            } else {
                $('#create_target_fields').hide();
                $('#create_sequence_fields').hide();
            }
        });

        // ==========================================
        // LOGIC MODAL EDIT
        // ==========================================
        $(document).on('click', '.btn-edit', function() {
            var d = $(this).data();

            // Isi Form Dasar
            $('#edit_mission_title').val(d.title);
            $('#edit_mission_points').val(d.points);
            $('#edit_mission_description').val(d.description);
            $('#edit_mission_start_date').val(d.start);
            $('#edit_mission_end_date').val(d.end);
            $('#edit_mission_is_active').prop('checked', d.active == 1);
            $('#editForm').attr('action', d.updateUrl);
            $('#edit_mission_type_display').val(d.type);

            // LOGIC BARU: Populate Dropdown Metric Edit sesuai Type
            // Kita panggil fungsi helper, lalu set value-nya ke d.metric
            updateMetricOptions(d.type, '#edit_mission_metric_code', d.metric);

            // Toggle Tampilan
            if (d.type === 'TARGET') {
                $('#edit_target_fields').show();
                $('#edit_sequence_fields').hide();
                $('#edit_mission_target_amount').val(d.targetAmount);
            } else if (d.type === 'SEQUENCE') {
                $('#edit_target_fields').hide();
                $('#edit_sequence_fields').show();
                $('#edit_mission_days_required').val(d.daysRequired);
                $('#edit_mission_is_consecutive').prop('checked', d.isConsecutive == 1);
            }

            $('#editModal').modal('show');
        });
    });
</script>
@endpush