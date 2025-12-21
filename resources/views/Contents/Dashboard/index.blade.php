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


        <!-- Row 2: Validation Chart -->
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-head-row">
                            <div class="card-title">Statistik Validasi Pengguna</div>
                            <div class="card-tools">

                                <select id="chartPeriodFilter" class="form-control form-control-sm d-inline-block"
                                    style="width: 100px;">
                                    <option value="day">Harian</option>
                                    <option value="week">Mingguan</option>
                                    <option value="month">Bulanan</option>
                                </select>
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

            function loadChart(period) {
                $.get("{{ route('dashboard.chart') }}", { period: period }, function (response) {
                    if (validationChart) {
                        validationChart.destroy();
                    }

                    validationChart = new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: response.labels,
                            datasets: response.datasets
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            legend: { position: 'bottom' },
                            tooltips: {
                                bodySpacing: 4,
                                mode: "nearest",
                                intersect: 0,
                                position: "nearest",
                                xPadding: 10,
                                yPadding: 10,
                                caretPadding: 10
                            },
                            layout: {
                                padding: { left: 15, right: 15, top: 15, bottom: 15 }
                            }
                        }
                    });
                });
            }

            // Initial Load
            loadChart('day');

            // Filter Events
            $('#chartPeriodFilter').change(function () {
                loadChart($(this).val());
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