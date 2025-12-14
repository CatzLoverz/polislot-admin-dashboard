@extends("Layouts.content_layout")

@section('title', 'Manajemen Misi')
@section('page_title', 'Manajemen Misi')
@section('page_subtitle', 'Kelola daftar misi, aturan target, dan koin reward.')

@section('content')
<div class="page-inner mt--5">
    <div class="card">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h4 class="card-title">Daftar Misi Tersedia</h4>
            <button class="btn btn-primary btn-round" data-toggle="modal" data-target="#modalMission" id="btnAdd">
                <i class="fa fa-plus"></i> Tambah Misi
            </button>
        </div>
        <div class="card-body">
            <table id="tableMission" class="table table-striped" style="width:100%">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>Judul</th>
                        <th>Tipe</th>
                        <th>Detail Aturan</th>
                        <th>Siklus Reset</th>
                        <th>Reward</th>
                        <th>Status</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

{{-- Modal untuk Create dan Edit --}}
<div class="modal fade" id="modalMission" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalTitle">Form Misi</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <form id="formMission" method="POST">
                @csrf
                <div id="methodPut"></div> {{-- Tempat inject @method('PUT') --}}
                
                <div class="modal-body">
                    {{-- Judul & Poin --}}
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Judul Misi <span class="text-danger">*</span></label>
                            <input type="text" name="mission_title" class="form-control" placeholder="Masukkan Judul Misi" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Reward Koin <span class="text-danger">*</span></label>
                            <input type="number" name="mission_points" class="form-control" placeholder="0" min="0" required>
                        </div>
                    </div>

                    {{-- Metric & Cycle --}}
                    <div class="row">
                        <div class="col-md-6 form-group">
                            <label>Event Pemicu (Metric) <span class="text-danger">*</span></label>
                            <select name="mission_metric_code" class="form-control" required>
                                @foreach($metrics as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 form-group">
                            <label>Siklus Reset <span class="text-danger">*</span></label>
                            <select name="mission_reset_cycle" class="form-control" required>
                                @foreach($cycles as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    
                    <hr>
                    <h6 class="font-weight-bold text-primary">Konfigurasi Aturan</h6>

                    {{-- Tipe Misi --}}
                    <div class="form-group">
                        <label>Tipe Perhitungan <span class="text-danger">*</span></label>
                        <select name="mission_type" id="inputType" class="form-control" required>
                            <option value="TARGET">TARGET (Akumulasi Biasa)</option>
                            <option value="SEQUENCE">SEQUENCE (Progress +1 per-hari sampai durasi yang ditentukan)</option>
                        </select>
                    </div>

                    {{-- Threshold (Label berubah dinamis) --}}
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label id="labelThreshold">Target Jumlah (Kali)<span class="text-danger">*</span></label> 
                            <input type="number" name="mission_threshold" class="form-control" placeholder="Misal: 50" required>
                        </div>
                        
                        {{-- Checkbox Consecutive (Hanya muncul jika Sequence) --}}
                        <div class="col-md-4" id="divConsecutive" style="display:none;">
                            <div class="d-flex align-items-center h-100">
                                <div class="form-check">
                                    <label class="form-check-label">
                                        <input class="form-check-input" type="checkbox" name="mission_is_consecutive" value="1">
                                        <span class="form-check-sign font-weight-bold">Harus Berurut?</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea name="mission_description" class="form-control" rows="2" placeholder="Masukkan Deskripsi Misi"></textarea>
                    </div>

                    <div class="form-check">
                        <label class="form-check-label">
                            <input class="form-check-input" type="checkbox" name="mission_is_active" value="1" checked>
                            <span class="form-check-sign">Aktifkan Misi</span>
                        </label>
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
        var table = $('#tableMission').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.missions.index') }}",
            columns: [
                { data: 'DT_RowIndex', className: 'text-center', orderable: false, searchable: false },
                { data: 'mission_title', name: 'mission_title' },
                { data: 'mission_type', className: 'text-center' },
                { data: 'rules_detail', orderable: false, searchable: false },
                { data: 'cycle_info', name: 'mission_reset_cycle' },
                { data: 'mission_points', className: 'text-right' },
                { data: 'mission_is_active', className: 'text-center' },
                { data: 'action', className: 'text-center', orderable: false, searchable: false }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // 1. Logic Tampilan Dinamis (Create & Edit)
        $('#inputType').change(function() {
            var type = $(this).val();
            if(type === 'SEQUENCE') {
                $('#labelThreshold').text('Durasi Hari (Days Required)');
                $('#divConsecutive').show();
            } else {
                $('#labelThreshold').text('Target Jumlah (Total Amount)');
                $('#divConsecutive').hide();
                $('input[name="mission_is_consecutive"]').prop('checked', false);
            }
        });

        // 2. Tombol Tambah
        $('#btnAdd').click(function() {
            $('#modalTitle').text('Buat Misi Baru');
            $('#formMission')[0].reset();
            $('#formMission').attr('action', "{{ route('admin.missions.store') }}");
            $('#methodPut').empty(); // Hapus method PUT
            $('#inputType').trigger('change'); // Reset UI
        });

        // 3. Tombol Edit
        $(document).on('click', '.btn-edit', function() {
            var row = $(this).data('row'); // Ambil JSON dari button
            var url = $(this).data('update-url');

            $('#modalTitle').text('Edit Misi');
            $('#formMission').attr('action', url);
            $('#methodPut').html('@method("PUT")'); // Inject PUT

            // Isi Value
            $('input[name="mission_title"]').val(row.mission_title);
            $('input[name="mission_points"]').val(row.mission_points);
            $('textarea[name="mission_description"]').val(row.mission_description);
            $('select[name="mission_metric_code"]').val(row.mission_metric_code);
            $('select[name="mission_reset_cycle"]').val(row.mission_reset_cycle);
            $('select[name="mission_type"]').val(row.mission_type);
            $('input[name="mission_threshold"]').val(row.mission_threshold);
            
            // Checkbox
            $('input[name="mission_is_active"]').prop('checked', row.mission_is_active == 1);
            $('input[name="mission_is_consecutive"]').prop('checked', row.mission_is_consecutive == 1);

            // Trigger Change agar label threshold menyesuaikan
            $('#inputType').trigger('change');

            $('#modalMission').modal('show');
        });

        $('#tableMission').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });
</script>
@endpush