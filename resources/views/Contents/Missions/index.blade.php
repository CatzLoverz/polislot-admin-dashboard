@extends("Layouts.content_layout")

@section('title', 'Manajemen Misi')
@section('page_title', 'Manajemen Misi')
@section('page_subtitle', 'Kelola daftar misi, aturan target, dan koin reward.')

@push('styles')
<style>
/* === Radio Cards === */
.radio-card {
    border: 2px solid #ebeeef;
    border-radius: 8px;
    padding: 14px 8px;
    cursor: pointer;
    transition: border-color 0.18s, background 0.18s;
    background: #fff;
    /* Tidak ada height: 100% — biarkan konten menentukan tinggi */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: flex-start;
    height: 100%;
    box-sizing: border-box;
}
.radio-card:hover {
    border-color: #b3d4fc;
}
.radio-input:checked + .radio-card {
    border-color: #1572E8;
    background-color: #f1f7ff;
}
.radio-input {
    display: none;
}
.radio-card .icon {
    font-size: 22px;
    margin-bottom: 6px;
    color: #1572E8;
    display: block;
    flex-shrink: 0;
}
.radio-card .title {
    font-weight: 700;
    font-size: 12px;
    margin-bottom: 3px;
    color: #333;
    word-break: break-word;
    text-align: center;
}
.radio-card .desc {
    font-size: 10px;
    color: #777;
    line-height: 1.4;
    word-break: break-word;
    overflow-wrap: break-word;
    text-align: center;
    white-space: normal;
}

/* === Card column containers — stretch agar semua kartu dalam satu baris sama tinggi === */
.type-card-col,
.cycle-card-col {
    flex: 0 0 auto;
    display: flex;       
    flex-direction: column;
}

/* Label harus mengisi penuh tinggi kolom */
.type-card-col > label,
.cycle-card-col > label {
    display: flex;
    flex: 1;
}

/* Baris kartu menggunakan align-items: stretch agar semua kolom sama tinggi */
#typeCardsRow,
#cycleCardsRow {
    align-items: stretch;
}
</style>
@endpush

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

                    {{-- 1. Informasi Dasar --}}
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-info-circle"></i> 1. Informasi Dasar</h6>
                    <div class="row">
                        <div class="col-md-8 form-group">
                            <label>Judul Misi <span class="text-danger">*</span></label>
                            <input type="text" name="mission_title" id="inputTitle" class="form-control" placeholder="Masukkan Judul Misi" required>
                        </div>
                        <div class="col-md-4 form-group">
                            <label>Reward Koin <span class="text-danger">*</span></label>
                            <input type="number" name="mission_points" class="form-control" placeholder="0" min="0" required>
                        </div>
                        <div class="col-md-12 form-group">
                            <label>Deskripsi Singkat</label>
                            <textarea name="mission_description" class="form-control" rows="2" placeholder="Masukkan Deskripsi Misi"></textarea>
                        </div>
                    </div>

                    <hr>

                    {{-- 2. Event Pemicu --}}
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-cogs"></i> 2. Kondisi Misi</h6>

                    <div class="form-group pt-0">
                        <label class="d-block mb-2">A. Event Pemicu (Metric) <span class="text-danger">*</span></label>
                        <div class="row">
                            @php
                                $metricDefs = [
                                    'VALIDATION_ACTION' => ['icon' => 'fas fa-car',         'label' => 'Validasi Parkir',       'desc' => 'Memvalidasi kendaraan di area parkir'],
                                    'LOGIN_ACTION'      => ['icon' => 'fas fa-sign-in-alt',  'label' => 'Login Aplikasi',        'desc' => 'Membuka aplikasi mobile (1x per hari)'],
                                    'PROFILE_UPDATE'    => ['icon' => 'fas fa-user-edit',    'label' => 'Perbarui Profil',       'desc' => 'Mengubah data / avatar profil'],
                                ];
                            @endphp
                            @foreach($metrics as $code => $label)
                            <div class="col-md-4 mb-2">
                                <label class="w-100 h-100 mb-0">
                                    <input type="radio" name="mission_metric_code" value="{{ $code }}" class="radio-input" id="metric_{{ $code }}" required>
                                    <div class="radio-card text-center">
                                        <i class="{{ $metricDefs[$code]['icon'] ?? 'fas fa-star' }} icon"></i>
                                        <div class="title">{{ $metricDefs[$code]['label'] ?? $label }}</div>
                                        <div class="desc">{{ $metricDefs[$code]['desc'] ?? '' }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- B. Tipe Perhitungan — kondisional per metric --}}
                    <div class="form-group pt-0" id="sectionType">
                        <label class="d-block mb-2">B. Tipe Perhitungan <span class="text-danger">*</span></label>
                        <div class="row justify-content-center" id="typeCardsRow">
                            {{-- TARGET --}}
                            <div class="mb-2 type-card-col" id="colTypeTarget">
                                <label class="w-100 h-100 mb-0">
                                    <input type="radio" name="mission_type" value="TARGET" class="radio-input" id="typeTarget" required>
                                    <div class="radio-card text-center p-2">
                                        <i class="fas fa-bullseye icon mb-1" style="font-size: 20px;"></i>
                                        <div class="title">TARGET</div>
                                        <div class="desc">Akumulasi jumlah aksi, bebas urutan hari</div>
                                    </div>
                                </label>
                            </div>
                            {{-- SEQUENCE --}}
                            <div class="mb-2 type-card-col" id="colTypeSequence">
                                <label class="w-100 h-100 mb-0">
                                    <input type="radio" name="mission_type" value="SEQUENCE" class="radio-input" id="typeSequence" required>
                                    <div class="radio-card text-center p-2">
                                        <i class="fas fa-layer-group icon mb-1" style="font-size: 20px;"></i>
                                        <div class="title">SEQUENCE</div>
                                        <div class="desc">Progres per-hari, tidak harus berturut-turut</div>
                                    </div>
                                </label>
                            </div>
                            {{-- SEQUENCE_STREAK --}}
                            <div class="mb-2 type-card-col" id="colTypeStreak">
                                <label class="w-100 h-100 mb-0">
                                    <input type="radio" name="mission_type" value="SEQUENCE_STREAK" class="radio-input" id="typeStreak" required>
                                    <div class="radio-card text-center p-2">
                                        <i class="fas fa-fire icon mb-1" style="font-size: 20px; color: #e74c3c;"></i>
                                        <div class="title" style="color: #e74c3c;">STREAK</div>
                                        <div class="desc">Progres per-hari, HARUS berturut-turut</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- C. Siklus Reset — kondisional per metric --}}
                    <div class="form-group pt-0" id="sectionCycle">
                        <label class="d-block mb-2">C. Siklus Reset <span class="text-danger">*</span></label>
                        <div class="row justify-content-center" id="cycleCardsRow">
                            @php
                                $cycleDefs = [
                                    'NONE'    => ['icon' => 'fas fa-infinity',     'label' => 'Tidak Reset',  'desc' => 'Misi hanya bisa diselesaikan sekali seumur hidup'],
                                    'DAILY'   => ['icon' => 'fas fa-sun',          'label' => 'Harian',       'desc' => 'Progress direset setiap tengah malam (00:00)'],
                                    'WEEKLY'  => ['icon' => 'fas fa-calendar',     'label' => 'Mingguan',     'desc' => 'Progress direset setiap hari Senin pagi'],
                                    'MONTHLY' => ['icon' => 'fas fa-calendar-alt', 'label' => 'Bulanan',      'desc' => 'Progress direset setiap tanggal 1 bulan baru'],
                                ];
                            @endphp
                            @foreach($cycles as $code => $label)
                            <div class="mb-2 cycle-card-col" id="colCycle{{ $code }}">
                                <label class="w-100 h-100 mb-0">
                                    <input type="radio" name="mission_reset_cycle" value="{{ $code }}" class="radio-input" id="cycle{{ $code }}" required>
                                    <div class="radio-card text-center p-2">
                                        <i class="{{ $cycleDefs[$code]['icon'] ?? 'fas fa-clock' }} icon mb-1" style="font-size: 18px;"></i>
                                        <div class="title" style="font-size: 11px;">{{ $cycleDefs[$code]['label'] ?? $label }}</div>
                                        <div class="desc">{{ $cycleDefs[$code]['desc'] ?? '' }}</div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <hr>

                    {{-- 3. Target Pencapaian --}}
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-flag-checkered"></i> 3. Target Pencapaian</h6>
                    <div class="row">
                        <div class="col-md-12 form-group">
                            <label id="labelThreshold">Target Jumlah (Kali) <span class="text-danger">*</span></label>
                            <input type="number" name="mission_threshold" id="inputThreshold" class="form-control" placeholder="Misal: 10" min="1" required>
                            <small id="hintThreshold" class="text-muted"></small>
                        </div>
                    </div>

                    <hr>

                    {{-- 4. Status Misi --}}
                    <h6 class="font-weight-bold text-primary mb-3"><i class="fas fa-toggle-on"></i> 4. Status Misi</h6>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="w-100 h-100 mb-0">
                                <input type="radio" name="mission_is_active" value="1" class="radio-input" id="statusActive" required>
                                <div class="radio-card text-center p-2">
                                    <i class="fas fa-check-circle icon mb-1" style="font-size: 20px; color: #28a745;"></i>
                                    <div class="title" style="color: #28a745;">Aktif</div>
                                    <div class="desc">Misi akan ditampilkan ke pengguna</div>
                                </div>
                            </label>
                        </div>
                        <div class="col-6 mb-2">
                            <label class="w-100 h-100 mb-0">
                                <input type="radio" name="mission_is_active" value="0" class="radio-input" id="statusInactive" required>
                                <div class="radio-card text-center p-2">
                                    <i class="fas fa-pause-circle icon mb-1" style="font-size: 20px; color: #dc3545;"></i>
                                    <div class="title" style="color: #dc3545;">Non-Aktif</div>
                                    <div class="desc">Misi disembunyikan dari pengguna</div>
                                </div>
                            </label>
                        </div>
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

    /* ================================================================
     * DataTable
     * ================================================================ */
    var table = $('#tableMission').DataTable({
        processing: true,
        serverSide: true,
        ajax: "{{ route('admin.missions.index') }}",
        columns: [
            { data: 'DT_RowIndex',      className: 'text-center', orderable: false, searchable: false },
            { data: 'mission_title',    name: 'mission_title' },
            { data: 'mission_type',     className: 'text-center' },
            { data: 'rules_detail',     orderable: false, searchable: false },
            { data: 'cycle_info',       name: 'mission_reset_cycle' },
            { data: 'mission_points',   className: 'text-right' },
            { data: 'mission_is_active',className: 'text-center' },
            { data: 'action',           className: 'text-center', orderable: false, searchable: false }
        ],
        language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
    });

    /* ================================================================
     * Aturan logika per metric
     *
     * VALIDATION_ACTION : TARGET | SEQUENCE | SEQUENCE_STREAK  — cycle: ALL
     * LOGIN_ACTION       : SEQUENCE | SEQUENCE_STREAK           — cycle: ALL
     * PROFILE_UPDATE     : TARGET (only, threshold locked=1)    — cycle: NONE (only)
     * ================================================================ */
    var RULES = {
        VALIDATION_ACTION: {
            types:  ['TARGET', 'SEQUENCE', 'SEQUENCE_STREAK'],
            cycles: ['NONE', 'DAILY', 'WEEKLY', 'MONTHLY'],
            lockThreshold: false,
        },
        LOGIN_ACTION: {
            types:  ['SEQUENCE', 'SEQUENCE_STREAK'],
            cycles: ['NONE', 'DAILY', 'WEEKLY', 'MONTHLY'],
            lockThreshold: false,
        },
        PROFILE_UPDATE: {
            types:  ['TARGET'],
            cycles: ['NONE'],
            lockThreshold: true,
        }
    };

    /* ---- Compute equal card widths based on visible count ---- */
    function setEqualWidth(selector, count) {
        var pct = count > 0 ? Math.floor(100 / count) + '%' : '0%';
        $(selector + ':visible').css('width', pct);
    }

    /* ================================================================
     * Main adjust function — triggered on any radio change
     * ================================================================ */
    function adjustMissionOptions() {
        var metric = $('input[name="mission_metric_code"]:checked').val();
        if (!metric) return; // nothing chosen yet

        var rule        = RULES[metric];
        var currentType = $('input[name="mission_type"]:checked').val();

        /* --- B. Show / hide type cards --- */
        var allTypes = ['TARGET', 'SEQUENCE', 'SEQUENCE_STREAK'];
        allTypes.forEach(function(t) {
            var $col = $('#colType' + t.charAt(0).toUpperCase() + t.slice(1).replace('_STREAK','Streak').replace('SEQUENCE','Sequence'));
            // map value to column id
        });
        // Use explicit mapping for clarity
        var typeColMap = {
            TARGET:          '#colTypeTarget',
            SEQUENCE:        '#colTypeSequence',
            SEQUENCE_STREAK: '#colTypeStreak'
        };
        var visibleTypeCount = 0;
        $.each(typeColMap, function(val, colId) {
            if (rule.types.indexOf(val) !== -1) {
                $(colId).show();
                visibleTypeCount++;
            } else {
                $(colId).hide();
                // If currently selected type is now hidden, uncheck it
                if (currentType === val) {
                    $('input[name="mission_type"][value="' + val + '"]').prop('checked', false);
                    currentType = null;
                }
            }
        });
        // If previously selected type is no longer visible, auto-select first available
        if (!currentType || rule.types.indexOf(currentType) === -1) {
            $('input[name="mission_type"][value="' + rule.types[0] + '"]').prop('checked', true);
            currentType = rule.types[0];
        }
        // Set equal width for visible type cards
        var typeColWidth = visibleTypeCount > 0 ? (100 / visibleTypeCount).toFixed(4) + '%' : '0%';
        $.each(typeColMap, function(val, colId) {
            if (rule.types.indexOf(val) !== -1) {
                $(colId).css('width', typeColWidth);
            }
        });

        /* --- C. Show / hide cycle cards --- */
        var allCycles = ['NONE', 'DAILY', 'WEEKLY', 'MONTHLY'];
        var currentCycle = $('input[name="mission_reset_cycle"]:checked').val();
        var visibleCycleCount = 0;
        allCycles.forEach(function(c) {
            if (rule.cycles.indexOf(c) !== -1) {
                $('#colCycle' + c).show();
                visibleCycleCount++;
            } else {
                $('#colCycle' + c).hide();
                if (currentCycle === c) {
                    $('input[name="mission_reset_cycle"][value="' + c + '"]').prop('checked', false);
                    currentCycle = null;
                }
            }
        });
        // Auto-select first available cycle if none selected or current was hidden
        if (!currentCycle || rule.cycles.indexOf(currentCycle) === -1) {
            $('input[name="mission_reset_cycle"][value="' + rule.cycles[0] + '"]').prop('checked', true);
        }
        // Equal width for visible cycle cards
        var cycleColWidth = visibleCycleCount > 0 ? (100 / visibleCycleCount).toFixed(4) + '%' : '0%';
        allCycles.forEach(function(c) {
            if (rule.cycles.indexOf(c) !== -1) {
                $('#colCycle' + c).css('width', cycleColWidth);
            }
        });

        /* --- Threshold label & lock --- */
        if (currentType === 'TARGET') {
            $('#labelThreshold').html('Target Jumlah (Kali) <span class="text-danger">*</span>');
            $('#hintThreshold').text('Berapa kali aksi harus dilakukan secara total.');
        } else {
            $('#labelThreshold').html('Durasi Hari <span class="text-danger">*</span>');
            $('#hintThreshold').text('Berapa hari harus dicapai.');
        }

        if (rule.lockThreshold) {
            $('#inputThreshold').val(1).prop('readonly', true);
            $('#hintThreshold').text('Event ini hanya membutuhkan 1 kali aksi dan tidak bisa diubah.');
        } else {
            $('#inputThreshold').prop('readonly', false);
            if ($('#inputThreshold').val() == 1 && metric !== 'PROFILE_UPDATE') {
                $('#inputThreshold').val(''); // clear placeholder-like locked value
            }
        }
    }

    /* Listen on all radio changes */
    $(document).on('change', 'input[name="mission_metric_code"], input[name="mission_type"]', function() {
        adjustMissionOptions();
    });

    /* ================================================================
     * Reset modal to initial "create" state
     * ================================================================ */
    function resetModal() {
        $('#formMission')[0].reset();

        // Unchecked all radios first
        $('input[name="mission_metric_code"]').prop('checked', false);
        $('input[name="mission_type"]').prop('checked', false);
        $('input[name="mission_reset_cycle"]').prop('checked', false);
        $('input[name="mission_is_active"]').prop('checked', false);

        // Default selections
        $('input[name="mission_metric_code"][value="VALIDATION_ACTION"]').prop('checked', true);
        $('input[name="mission_type"][value="TARGET"]').prop('checked', true);
        $('input[name="mission_reset_cycle"][value="NONE"]').prop('checked', true);
        $('input[name="mission_is_active"][value="1"]').prop('checked', true);
        $('#inputThreshold').prop('readonly', false);

        adjustMissionOptions();
    }

    /* ================================================================
     * Tombol Tambah
     * ================================================================ */
    $('#btnAdd').click(function() {
        $('#modalTitle').text('Buat Misi Baru');
        $('#formMission').attr('action', "{{ route('admin.missions.store') }}");
        $('#methodPut').empty();
        resetModal();
        $('#modalMission').modal('show');
    });

    /* ================================================================
     * Tombol Edit
     * ================================================================ */
    $(document).on('click', '.btn-edit', function() {
        var row = $(this).data('row');
        var url = $(this).data('update-url');

        $('#modalTitle').text('Edit Misi');
        $('#formMission').attr('action', url);
        $('#methodPut').html('@method("PUT")');

        // Reset then fill
        resetModal();

        $('input[name="mission_title"]').val(row.mission_title);
        $('input[name="mission_points"]').val(row.mission_points);
        $('textarea[name="mission_description"]').val(row.mission_description);
        $('input[name="mission_threshold"]').val(row.mission_threshold);

        // Radios
        $('input[name="mission_metric_code"][value="' + row.mission_metric_code + '"]').prop('checked', true);
        $('input[name="mission_type"][value="'        + row.mission_type         + '"]').prop('checked', true);
        $('input[name="mission_reset_cycle"][value="' + row.mission_reset_cycle  + '"]').prop('checked', true);
        $('input[name="mission_is_active"][value="'   + (row.mission_is_active ? '1' : '0') + '"]').prop('checked', true);

        adjustMissionOptions();

        $('#modalMission').modal('show');
    });

    /* ================================================================
     * DataTable tooltip refresh
     * ================================================================ */
    $('#tableMission').on('draw.dt', function () {
        $('[data-toggle="tooltip"]').tooltip();
    });

    /* Initial adjust on page load (nothing selected yet, just ensure layout) */
    adjustMissionOptions();
});
</script>
@endpush