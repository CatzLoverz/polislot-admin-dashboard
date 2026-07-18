@extends('Layouts.content_layout')

@section('title', 'Dashboard')
@section('page_title', 'Dashboard')
@section('page_subtitle', 'Selamat Datang ' . Auth::user()->name ?? 'Pengguna')

@section('content')
<div class="page-inner mt--5">
    <!-- Row 1: Summary Cards -->
    <div class="row row-card-no-pd">
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-users text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats d-flex align-items-center justify-content-center">
                            <div class="numbers">
                                <p class="card-category">Pengguna</p>
                                <h4 class="card-title">{{ number_format($totalUsers) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-parking text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats d-flex align-items-center justify-content-center">
                            <div class="numbers">
                                <p class="card-category">Area Parkir</p>
                                <h4 class="card-title">{{ number_format($totalParkAreas) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-map-signs text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats d-flex align-items-center justify-content-center">
                            <div class="numbers">
                                <p class="card-category">Subarea</p>
                                <h4 class="card-title">{{ number_format($totalSubareas) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-md-3">
            <div class="card card-stats card-round">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-gift text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7 col-stats d-flex align-items-center justify-content-center">
                            <div class="numbers">
                                <p class="card-category">Klaim Hadiah</p>
                                <h4 class="card-title">{{ number_format($pendingRewards) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Row 1.5: Automatic Detection Parking Availability Statistics -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Statistik Ketersediaan Parkir Deteksi Otomatis</div>
                        <div class="card-tools">
                            <!-- Consolidated Calendar Filter Dropdown for Detection -->
                            <div class="dropdown d-inline-block" id="detectionChartFilterDropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="detectionDropdownMenuButton" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-calendar-alt mr-1"></i> <span id="detectionFilterDropdownLabel">Filter Tanggal</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right p-3" aria-labelledby="detectionDropdownMenuButton" style="min-width: 280px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); border-radius: 6px; transform: none !important; right: 0 !important; left: auto !important; top: 100% !important;">
                                    <div class="form-group p-0 mb-2">
                                        <label class="small font-weight-bold mb-1">Area Parkir</label>
                                        <select id="detectionChartAreaFilter" class="form-control form-control-sm">
                                            <option value="all">Semua Area</option>
                                            @foreach($parkAreas as $area)
                                                <option value="{{ $area->park_area_id }}">{{ $area->park_area_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group p-0 mb-2">
                                        <label class="small font-weight-bold mb-1">Tipe Filter</label>
                                        <select id="detectionFilterType" class="form-control form-control-sm">
                                            <option value="tanggal">Tanggal</option>
                                            <option value="bulan">Bulan</option>
                                            <option value="tahun">Tahun</option>
                                        </select>
                                    </div>

                                    <div class="form-check p-0 mb-3">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="detectionRangeToggle">
                                            <span class="form-check-sign small">Rentang Waktu</span>
                                        </label>
                                    </div>

                                    <div class="form-group p-0 mb-3">
                                        <label class="small font-weight-bold mb-1">Pilih Waktu</label>

                                        <!-- Tanggal Inputs -->
                                        <div class="filter-inputs-group" id="detectionGroupTanggal">
                                            <input type="date" class="form-control form-control-sm mb-1" id="detectionDateFrom">
                                            <input type="date" class="form-control form-control-sm d-none" id="detectionDateTo">
                                        </div>

                                        <!-- Bulan Inputs -->
                                        <div class="filter-inputs-group d-none" id="detectionGroupBulan">
                                            <input type="month" class="form-control form-control-sm mb-1" id="detectionMonthFrom">
                                            <input type="month" class="form-control form-control-sm d-none" id="detectionMonthTo">
                                        </div>

                                        <!-- Tahun Inputs -->
                                        <div class="filter-inputs-group d-none" id="detectionGroupTahun">
                                            <input type="number" class="form-control form-control-sm mb-1" id="detectionYearFrom" value="{{ date('Y') }}" placeholder="Tahun Mulai">
                                            <input type="number" class="form-control form-control-sm d-none" id="detectionYearTo" value="{{ date('Y') }}" placeholder="Tahun Akhir">
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-xs btn-primary btn-block" id="applyDetectionChartFilter">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="detectionChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Row 2: Validation Chart -->
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Statistik Validasi Pengguna</div>
                        <div class="card-tools">
                            <!-- Consolidated Calendar Filter Dropdown -->
                            <div class="dropdown d-inline-block" id="chartFilterDropdown">
                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-calendar-alt mr-1"></i> <span id="filterDropdownLabel">Filter Tanggal</span>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right p-3" aria-labelledby="dropdownMenuButton" style="min-width: 280px; box-shadow: 0 4px 10px rgba(0,0,0,0.15); border-radius: 6px; transform: none !important; right: 0 !important; left: auto !important; top: 100% !important;">
                                    <div class="form-group p-0 mb-2">
                                        <label class="small font-weight-bold mb-1">Area Parkir</label>
                                        <select id="chartAreaFilter" class="form-control form-control-sm">
                                            <option value="all">Semua Area</option>
                                            @foreach($parkAreas as $area)
                                                <option value="{{ $area->park_area_id }}">{{ $area->park_area_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group p-0 mb-2">
                                        <label class="small font-weight-bold mb-1">Tipe Filter</label>
                                        <select id="filterType" class="form-control form-control-sm">
                                            <option value="tanggal">Tanggal</option>
                                            <option value="bulan">Bulan</option>
                                            <option value="tahun">Tahun</option>
                                        </select>
                                    </div>

                                    <div class="form-check p-0 mb-3">
                                        <label class="form-check-label">
                                            <input class="form-check-input" type="checkbox" id="rangeToggle">
                                            <span class="form-check-sign small">Rentang Waktu</span>
                                        </label>
                                    </div>

                                    <div class="form-group p-0 mb-3">
                                        <label class="small font-weight-bold mb-1">Pilih Waktu</label>

                                        <!-- Tanggal Inputs -->
                                        <div class="filter-inputs-group" id="groupTanggal">
                                            <input type="date" class="form-control form-control-sm mb-1" id="dateFrom">
                                            <input type="date" class="form-control form-control-sm d-none" id="dateTo">
                                        </div>

                                        <!-- Bulan Inputs -->
                                        <div class="filter-inputs-group d-none" id="groupBulan">
                                            <input type="month" class="form-control form-control-sm mb-1" id="monthFrom">
                                            <input type="month" class="form-control form-control-sm d-none" id="monthTo">
                                        </div>

                                        <!-- Tahun Inputs -->
                                        <div class="filter-inputs-group d-none" id="groupTahun">
                                            <input type="number" class="form-control form-control-sm mb-1" id="yearFrom" value="{{ date('Y') }}" placeholder="Tahun Mulai">
                                            <input type="number" class="form-control form-control-sm d-none" id="yearTo" value="{{ date('Y') }}" placeholder="Tahun Akhir">
                                        </div>
                                    </div>

                                    <button type="button" class="btn btn-xs btn-primary btn-block" id="applyChartFilter">Terapkan</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container" style="min-height: 375px">
                        <canvas id="validationChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Row 3: Leaderboard & Realtime Report -->
    <div class="row">
        <!-- Leaderboard -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-title">Top Pengguna (Leaderboard)</div>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="table-responsive">
                        <table class="table table-hover" id="leaderboardTable">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Pengguna</th>
                                    <th class="text-right">Koin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="3" class="text-center">Memuat...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Realtime Validation Report -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-head-row">
                        <div class="card-title">Validasi Realtime</div>
                        <div class="card-tools">
                            <select id="realtimeAreaFilter" class="form-control form-control-sm">
                                <option value="all">Semua Area</option>
                                @foreach($parkAreas as $area)
                                    <option value="{{ $area->park_area_id }}">{{ $area->park_area_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body" style="max-height: 400px; overflow-y: auto;">
                    <ul class="list-group list-group-flush" id="realtimeList">
                        <li class="list-group-item text-center">Menunggu pembaruan...</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    $(document).ready(function () {
        // --- 1. Validation Chart ---
        const ctx = document.getElementById('validationChart').getContext('2d');
        let validationChart = null;

        function loadChart(filterType, params = {}) {
            let requestData = Object.assign({ filter_type: filterType }, params);
            // Tambah area_id dari filter
            let areaId = $('#chartAreaFilter').val();
            if (areaId) requestData.area_id = areaId;

            $.get("{{ route('dashboard.chart') }}", requestData, function (response) {
                if (validationChart) {
                    validationChart.destroy();
                }

                validationChart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: response.labels,
                        datasets: response.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { position: 'bottom' }
                        },
                        scales: {
                            x: {
                                stacked: true
                            },
                            y: {
                                stacked: true,
                                beginAtZero: true
                            }
                        },
                        layout: {
                            padding: { left: 15, right: 15, top: 15, bottom: 15 }
                        }
                    }
                });
            });
        }

        // --- 1.5. Detection Chart ---
        const ctxDetection = document.getElementById('detectionChart').getContext('2d');
        let detectionChart = null;

        function loadDetectionChart(filterType, params = {}) {
            let requestData = Object.assign({ filter_type: filterType }, params);
            let areaId = $('#detectionChartAreaFilter').val();
            if (areaId) requestData.area_id = areaId;

            $.get("{{ route('dashboard.detection_chart') }}", requestData, function (response) {
                if (detectionChart) {
                    detectionChart.destroy();
                }

                detectionChart = new Chart(ctxDetection, {
                    type: 'bar',
                    data: {
                        labels: response.labels,
                        datasets: response.datasets
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                title: {
                                    display: true,
                                    text: 'Rata-rata Slot Tersedia'
                                }
                            },
                            x: {
                                title: {
                                    display: true,
                                    text: 'Jam'
                                }
                            }
                        },
                        layout: {
                            padding: { left: 15, right: 15, top: 15, bottom: 15 }
                        }
                    }
                });
            });
        }

        // --- Consolidated Calendar Filter Dropdown for Validation Chart ---
        // Keep dropdown open when interacting inside
        $('#chartFilterDropdown .dropdown-menu').on('click', function (e) {
            e.stopPropagation();
        });

        function updateInputsVisibility() {
            const type = $('#filterType').val();
            const isRange = $('#rangeToggle').is(':checked');

            // Sembunyikan semua input "hingga" terlebih dahulu
            $('#dateTo, #monthTo, #yearTo').addClass('d-none');

            // Tampilkan input "hingga" hanya jika range toggle aktif
            if (isRange) {
                const idMap = { tanggal: '#dateTo', bulan: '#monthTo', tahun: '#yearTo' };
                $(idMap[type]).removeClass('d-none');
            }
        }

        // Mode switcher
        $('#filterType').change(function () {
            const type = $(this).val();
            $('.filter-inputs-group').addClass('d-none');
            $('#group' + type.charAt(0).toUpperCase() + type.slice(1)).removeClass('d-none');

            // Perbarui visibilitas input "hingga"
            updateInputsVisibility();

            const labelMap = { tanggal: 'Filter Tanggal', bulan: 'Filter Bulan', tahun: 'Filter Tahun' };
            $('#filterDropdownLabel').text(labelMap[type] || 'Filter');
        });

        // Range toggle
        $('#rangeToggle').change(function () {
            updateInputsVisibility();
        });

        // Apply Filter
        $('#applyChartFilter').click(function () {
            const type = $('#filterType').val();
            let params = {};
            const isRange = $('#rangeToggle').is(':checked');

            if (type === 'tanggal') {
                params.date_from = $('#dateFrom').val();
                if (isRange) params.date_to = $('#dateTo').val();
            } else if (type === 'bulan') {
                params.month_from = $('#monthFrom').val();
                if (isRange) params.month_to = $('#monthTo').val();
            } else if (type === 'tahun') {
                params.year_from = $('#yearFrom').val();
                if (isRange) params.year_to = $('#yearTo').val();
            }

            if (!params.date_from && !params.month_from && !params.year_from) {
                return;
            }

            // Dapatkan nama area parkir yang dipilih
            const areaName = $('#chartAreaFilter option:selected').text();

            // Update dropdown button label to show area and range
            const labelMap = { tanggal: 'Tanggal', bulan: 'Bulan', tahun: 'Tahun' };
            let rangeLabel = areaName + ' | ' + labelMap[type] + ': ';
            if (isRange) {
                const fromId = { tanggal: '#dateFrom', bulan: '#monthFrom', tahun: '#yearFrom' };
                const toId = { tanggal: '#dateTo', bulan: '#monthTo', rangeToggle: '#yearTo' }; // yearTo selector fix
                const toIdReal = { tanggal: '#dateTo', bulan: '#monthTo', tahun: '#yearTo' };
                const fromVal = $(fromId[type]).val();
                const toVal = $(toIdReal[type]).val();
                rangeLabel += (fromVal || '-') + ' s/d ' + (toVal || '-');
            } else {
                const singleId = { tanggal: '#dateFrom', bulan: '#monthFrom', tahun: '#yearFrom' };
                rangeLabel += $(singleId[type]).val() || '-';
            }
            $('#filterDropdownLabel').text(rangeLabel);

            loadChart(type, params);

            // Close dropdown
            $('#chartFilterDropdown').removeClass('show');
            $('#chartFilterDropdown .dropdown-menu').removeClass('show');
        });

        // --- Consolidated Calendar Filter Dropdown for Detection Chart ---
        $('#detectionChartFilterDropdown .dropdown-menu').on('click', function (e) {
            e.stopPropagation();
        });

        function updateDetectionInputsVisibility() {
            const type = $('#detectionFilterType').val();
            const isRange = $('#detectionRangeToggle').is(':checked');

            $('#detectionDateTo, #detectionMonthTo, #detectionYearTo').addClass('d-none');

            if (isRange) {
                const idMap = { tanggal: '#detectionDateTo', bulan: '#detectionMonthTo', tahun: '#detectionYearTo' };
                $(idMap[type]).removeClass('d-none');
            }
        }

        // Mode switcher for detection
        $('#detectionFilterType').change(function () {
            const type = $(this).val();
            $('#detectionChartFilterDropdown .filter-inputs-group').addClass('d-none');
            $('#detectionGroup' + type.charAt(0).toUpperCase() + type.slice(1)).removeClass('d-none');

            updateDetectionInputsVisibility();

            const labelMap = { tanggal: 'Filter Tanggal', bulan: 'Filter Bulan', tahun: 'Filter Tahun' };
            $('#detectionFilterDropdownLabel').text(labelMap[type] || 'Filter');
        });

        // Range toggle for detection
        $('#detectionRangeToggle').change(function () {
            updateDetectionInputsVisibility();
        });

        // Apply Filter for detection
        $('#applyDetectionChartFilter').click(function () {
            const type = $('#detectionFilterType').val();
            let params = {};
            const isRange = $('#detectionRangeToggle').is(':checked');

            if (type === 'tanggal') {
                params.date_from = $('#detectionDateFrom').val();
                if (isRange) params.date_to = $('#detectionDateTo').val();
            } else if (type === 'bulan') {
                params.month_from = $('#detectionMonthFrom').val();
                if (isRange) params.month_to = $('#detectionMonthTo').val();
            } else if (type === 'tahun') {
                params.year_from = $('#detectionYearFrom').val();
                if (isRange) params.year_to = $('#detectionYearTo').val();
            }

            if (!params.date_from && !params.month_from && !params.year_from) {
                return;
            }

            const areaName = $('#detectionChartAreaFilter option:selected').text();

            const labelMap = { tanggal: 'Tanggal', bulan: 'Bulan', tahun: 'Tahun' };
            let rangeLabel = areaName + ' | ' + labelMap[type] + ': ';
            if (isRange) {
                const fromId = { tanggal: '#detectionDateFrom', bulan: '#detectionMonthFrom', tahun: '#detectionYearFrom' };
                const toIdReal = { tanggal: '#detectionDateTo', bulan: '#detectionMonthTo', tahun: '#detectionYearTo' };
                const fromVal = $(fromId[type]).val();
                const toVal = $(toIdReal[type]).val();
                rangeLabel += (fromVal || '-') + ' s/d ' + (toVal || '-');
            } else {
                const singleId = { tanggal: '#detectionDateFrom', bulan: '#detectionMonthFrom', tahun: '#detectionYearFrom' };
                rangeLabel += $(singleId[type]).val() || '-';
            }
            $('#detectionFilterDropdownLabel').text(rangeLabel);

            loadDetectionChart(type, params);

            // Close dropdown
            $('#detectionChartFilterDropdown').removeClass('show');
            $('#detectionChartFilterDropdown .dropdown-menu').removeClass('show');
        });

        // Initial Load: Tanggal Range (Last 30 Days)
        const today = new Date();
        const thirtyDaysAgo = new Date();
        thirtyDaysAgo.setDate(today.getDate() - 30);

        const formatDate = (date) => {
            const y = date.getFullYear();
            const m = String(date.getMonth() + 1).padStart(2, '0');
            const d = String(date.getDate()).padStart(2, '0');
            return `${y}-${m}-${d}`;
        };

        const initFrom = formatDate(thirtyDaysAgo);
        const initTo = formatDate(today);

        // Validation Chart Default Values
        $('#dateFrom').val(initFrom);
        $('#dateTo').val(initTo);
        $('#rangeToggle').prop('checked', true);

        // Detection Chart Default Values
        $('#detectionDateFrom').val(initFrom);
        $('#detectionDateTo').val(initTo);
        $('#detectionRangeToggle').prop('checked', true);

        // Panggil visibility manager
        updateInputsVisibility();
        updateDetectionInputsVisibility();

        // Label awal dengan info Area default "Semua Area"
        const defaultAreaName = $('#chartAreaFilter option:selected').text();
        $('#filterDropdownLabel').text(defaultAreaName + ' | Tanggal: ' + initFrom + ' s/d ' + initTo);

        const defaultDetectionAreaName = $('#detectionChartAreaFilter option:selected').text();
        $('#detectionFilterDropdownLabel').text(defaultDetectionAreaName + ' | Tanggal: ' + initFrom + ' s/d ' + initTo);

        loadChart('tanggal', {
            date_from: initFrom,
            date_to: initTo
        });
        loadDetectionChart('tanggal', {
            date_from: initFrom,
            date_to: initTo
        });

        // Re-load chart when area filter changes
        $('#chartAreaFilter').change(function () {
            $('#applyChartFilter').click();
        });

        $('#detectionChartAreaFilter').change(function () {
            $('#applyDetectionChartFilter').click();
        });


        // --- 2. Leaderboard ---
        function loadLeaderboard() {
            $.get("{{ route('dashboard.leaderboard') }}", function (data) {
                let rows = '';
                if (data.length === 0) {
                    rows = '<tr><td colspan="3" class="text-center">Tidak Ada Data</td></tr>';
                } else {
                    data.forEach((user, index) => {
                        let avatar = user.avatar ? user.avatar : `https://ui-avatars.com/api/?name=${user.name}&background=random`;
                        rows += `
                            <tr>
                                <td>${index + 1}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm mr-2">
                                            <img src="${avatar}" alt="..." class="avatar-img rounded-circle">
                                        </div>
                                        <span>${user.name}</span>
                                    </div>
                                </td>
                                <td class="text-right font-weight-bold text-success">${user.lifetime_points}</td>
                            </tr>
                        `;
                    });
                }
                $('#leaderboardTable tbody').html(rows);
            });
        }
        loadLeaderboard(); // Load once


        // --- 3. Realtime Validations ---
        let realtimeInterval;

        function loadRealtime(areaId) {
            $.get("{{ route('dashboard.realtime') }}", { area_id: areaId }, function (data) {
                let html = '';
                if (data.length === 0) {
                    html = '<li class="list-group-item text-center">Belum ada aktivitas</li>';
                } else {
                    data.forEach(item => {
                        let badgeColor = item.status === 'banyak' ? 'success' : (item.status === 'terbatas' ? 'warning' : 'danger');
                        let avatar = item.avatar ? item.avatar : `https://ui-avatars.com/api/?name=${item.username}&background=random`;

                        html += `
                            <li class="list-group-item px-3 py-3">
                                <div class="row w-100 align-items-center m-0">
                                    <!-- Column 1: Identity (Left) -->
                                    <div class="col-6 p-0 d-flex align-items-center">
                                        <div class="avatar avatar-sm mr-3">
                                            <img src="${avatar}" class="avatar-img rounded-circle">
                                        </div>
                                        <div>
                                            <h6 class="mb-1 fw-bold">${item.username}</h6>
                                            <small class="text-muted d-block" style="line-height:1.2">Area: ${item.area}</small>
                                            <small class="text-muted d-block" style="line-height:1.2">Sub: ${item.subarea}</small>
                                        </div>
                                    </div>

                                    <!-- Column 2: Timestamp (Center) -->
                                    <div class="col-3 p-0 text-center">
                                        <small class="text-muted font-weight-bold">${item.timestamp}</small>
                                    </div>

                                    <!-- Column 3: Status (Right) -->
                                    <div class="col-3 p-0 text-center">
                                        <span class="badge badge-${badgeColor} badge-pill">${item.status}</span>
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                }
                $('#realtimeList').html(html);
            });
        }

        // Initial Load & Polling (Every 5 seconds)
        loadRealtime('all');

        realtimeInterval = setInterval(() => {
            loadRealtime($('#realtimeAreaFilter').val());
        }, 5000);

        $('#realtimeAreaFilter').change(function () {
            loadRealtime($(this).val());
        });
    });
</script>
@endpush

@push('styles')
<style>
    /* CSS Override rigid untuk dropdown filter agar posisinya stabil di kanan dan tidak offside */
    #chartFilterDropdown .dropdown-menu {
        position: absolute !important;
        left: auto !important;
        right: 0 !important;
        transform: none !important;
        top: 100% !important;
    }
</style>
@endpush