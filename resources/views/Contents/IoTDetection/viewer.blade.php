@extends("Layouts.content_layout")

@section('title', 'Manajemen Konfigurasi Deteksi Parkir oleh IoT')
@section('page_title', 'Manajemen Konfigurasi Deteksi Parkir oleh IoT')
@section('page_subtitle', 'Kelola konfigurasi deteksi parkir otomatis berdasarkan perangkat IoT yang terhubung.')

@section('content')

@push('styles')
<style>
.double-range-slider {
    position: absolute;
    width: 100%;
    height: 10px;
    background: transparent;
    pointer-events: none;
    -webkit-appearance: none;
    appearance: none;
    top: 0;
    margin: 0;
    left: 0;
}

.double-range-slider::-webkit-slider-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #1572e8;
    cursor: pointer;
    pointer-events: auto;
    -webkit-appearance: none;
    appearance: none;
    box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    z-index: 20;
    position: relative;
}

.double-range-slider::-moz-range-thumb {
    height: 20px;
    width: 20px;
    border-radius: 50%;
    background: #ffffff;
    border: 3px solid #1572e8;
    cursor: pointer;
    pointer-events: auto;
    box-shadow: 0 1px 3px rgba(0,0,0,0.4);
    z-index: 20;
    position: relative;
}

/* Custom Premium Badge & Card Styles */
.capture-card {
    border-radius: 8px;
    border: 1px solid #ebedf2 !important;
    transition: all 0.2s ease-in-out;
    cursor: pointer;
}
.capture-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    border-color: #1572e8 !important;
}
.status-pill {
    font-size: 9px !important;
    font-weight: 600;
    min-width: 78px;
    text-align: center;
    padding: 3px 6px !important;
    border-radius: 4px;
    display: inline-block;
    border: 1px solid transparent;
}
.status-pill-banyak {
    background-color: rgba(49, 206, 54, 0.1) !important;
    color: #2bb430 !important;
    border-color: rgba(49, 206, 54, 0.2) !important;
}
.status-pill-terbatas {
    background-color: rgba(255, 173, 70, 0.1) !important;
    color: #ff9a13 !important;
    border-color: rgba(255, 173, 70, 0.2) !important;
}
.status-pill-penuh {
    background-color: rgba(242, 89, 97, 0.1) !important;
    color: #ea3d46 !important;
    border-color: rgba(242, 89, 97, 0.2) !important;
}
.status-pill-pending, .status-pill-belum {
    background-color: rgba(108, 117, 125, 0.08) !important;
    color: #8d949a !important;
    border-color: rgba(108, 117, 125, 0.15) !important;
}
.status-pill-trained {
    background-color: rgba(21, 114, 232, 0.1) !important;
    color: #1572e8 !important;
    border-color: rgba(21, 114, 232, 0.2) !important;
}
.status-pill-new {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    border-color: #e9ecef !important;
}
</style>
@endpush

<div class="page-inner mt--5">
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body py-3 d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        @if(isset($selectedDevice) && $selectedDevice->subarea)
                            <a href="{{ route('admin.park-area.show', $selectedDevice->subarea->park_area_id) }}" class="btn btn-light border btn-sm mr-3">
                                <i class="fas fa-arrow-left mr-1"></i> Kembali
                            </a>
                        @endif
                        <i class="fas fa-microchip fa-2x text-primary mr-3"></i>
                        <div>
                            <h5 class="mb-0 font-weight-bold text-dark">
                                @if(isset($selectedDevice) && $selectedDevice->subarea)
                                    {{ $selectedDevice->subarea->parkArea->park_area_name ?? 'Area tidak diketahui' }}
                                    / {{ $selectedDevice->subarea->park_subarea_name ?? 'Subarea tidak diketahui' }}
                                @else
                                    Perangkat IoT
                                @endif
                            </h5>
                            <span class="text-muted" style="font-size: 13px;">
                                MAC: <strong>{{ $targetMac }}</strong>
                            </span>
                        </div>
                    </div>
                    <div>
                        <div id="status-indicator" class="badge badge-{{ $initialStatus === 'online' ? 'success' : 'danger' }} ml-1" style="font-size: 13px; padding: 8px 12px;">
                            <i class="fas fa-circle mr-1" style="font-size: 10px;"></i> {{ strtoupper($initialStatus) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-2 mb-md-0 text-dark">
                        <i class="fas fa-cogs mr-2"></i> Pengaturan Deteksi & Threshold Subarea
                    </h4>
                    <div class="d-flex flex-wrap align-items-center">
                        <span class="badge badge-success m-1" id="connection-status">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Menghubungkan...
                        </span>
                    </div>
                </div>
                <div class="card-body bg-white" style="border-radius: 0 0 15px 15px;">

                    <div class="form-group p-0 text-dark mb-3">
                        <div class="d-flex flex-wrap align-items-center mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary m-1" id="btn-draw-mode" onclick="toggleDrawMode()">
                                <i class="fas fa-edit"></i> Mode Menggambar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger m-1" onclick="clearAllPolygons()">
                                <i class="fas fa-trash-alt"></i> Hapus Semua
                            </button>
                        </div>
                        <div class="bg-light p-2 text-center text-muted" style="font-size: 13px; border-bottom: 1px solid #ebedf2; border-radius: 4px;">
                            <i class="fas fa-info-circle text-primary mr-1"></i> 
                            <strong>Tips:</strong> Saat Mode Menggambar aktif: klik kiri menaruh titik, klik titik pertama menutup, geser sudut mengubah bentuk, klik-kanan menghapus sudut, dan klik tengah garis menyisipkan sudut. Matikan mode menggambar untuk mengunci poligon.
                        </div>
                    </div>

                    <div class="bg-light d-flex justify-content-center align-items-center mb-4" style="min-height: 400px; border-radius: 8px; border: 1px solid #ebedf2;">
                        <div id="feed-container" class="text-center p-4 w-100 h-100 position-relative d-flex justify-content-center align-items-center">
                            <div id="placeholder-container">
                                <i class="fas fa-image fa-4x text-muted mb-3"></i>
                                <h5 class="text-muted" id="feed-placeholder">Menunggu data masuk dari MAC Address: <strong>{{ $targetMac }}</strong>...</h5>
                            </div>
                            <div id="canvas-container" class="position-relative d-none" style="max-width: 640px; margin: 0 auto;">
                                <img id="live-image" src="" alt="Live Stream" class="img-fluid rounded shadow" style="max-height: 400px; display: block; width: 100%; height: auto;">
                                <canvas id="drawing-canvas" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: default; pointer-events: auto;"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="row text-dark mb-3">
                        <div class="col-md-3">
                            <div class="form-group p-0">
                                <label class="font-weight-bold" for="max-slots">Kapasitas Maksimal (Max Slot)</label>
                                <input type="number" id="max-slots" class="form-control" value="{{ $maxSlots }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <label class="font-weight-bold">Batas Threshold Okupansi</label>
                            <div class="threshold-slider-container">
                                <div class="d-flex justify-content-between text-center mb-2">
                                    <div style="flex: 1;">
                                        <div class="status-pill status-pill-banyak">
                                            <i class="fas fa-check-circle mr-1"></i> Banyak Tersedia
                                        </div>
                                        <div class="mt-1 font-weight-bold" style="color: #31ce36; font-size: 0.85rem;"><span id="label-banyak-range">0% - 30%</span></div>
                                    </div>
                                    <div style="flex: 1;">
                                        <span class="badge text-white font-weight-bold" style="background-color: #ffad46; border-radius: 6px; font-size: 10px; padding: 4px 8px;">
                                            <i class="fas fa-exclamation-circle mr-1"></i> Terbatas
                                        </span>
                                        <div class="mt-1 font-weight-bold" style="color: #ffad46; font-size: 0.85rem;"><span id="label-terbatas-range">30% - 80%</span></div>
                                    </div>
                                    <div style="flex: 1;">
                                        <span class="badge text-white font-weight-bold" style="background-color: #f25961; border-radius: 6px; font-size: 10px; padding: 4px 8px;">
                                            <i class="fas fa-times-circle mr-1"></i> Penuh
                                        </span>
                                        <div class="mt-1 font-weight-bold" style="color: #f25961; font-size: 0.85rem;"><span id="label-penuh-range">80% - 100%</span></div>
                                    </div>
                                </div>

                                <div class="position-relative" style="height: 10px; margin: 15px 10px 10px 10px;">
                                    <div class="slider-track" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: #dee2e6; border-radius: 5px;"></div>
                                    <div id="segment-banyak" style="position: absolute; top: 0; bottom: 0; left: 0; background-color: #31ce36; border-radius: 5px 0 0 5px;"></div>
                                    <div id="segment-terbatas" style="position: absolute; top: 0; bottom: 0; background-color: #ffad46;"></div>
                                    <div id="segment-penuh" style="position: absolute; top: 0; bottom: 0; right: 0; background-color: #f25961; border-radius: 0 5px 5px 0;"></div>

                                    <input type="range" id="input-threshold-banyak" min="5" max="95" value="{{ $thresholdBanyak }}" class="double-range-slider">
                                    <input type="range" id="input-threshold-terbatas" min="5" max="95" value="{{ $thresholdTerbatas }}" class="double-range-slider">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <button class="btn btn-primary btn-block btn-round" onclick="saveDetectionConfig()">
                        <i class="fas fa-save mr-1"></i> Simpan Setelan Deteksi ke Server
                    </button>
                </div>
            </div>

            {{-- Panel Simulasi Validasi & Status Subarea (Baru) --}}
            <div class="card shadow-sm mt-3 border-0">
                <div class="card-header" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-vial mr-2"></i> Simulasi Validasi & Status Subarea
                    </h4>
                </div>
                <div class="card-body bg-white" style="border-radius: 0 0 15px 15px;">
                    <div class="row align-items-center mb-3">
                        <div class="col-12 mb-2">
                            <div class="p-3 mb-3 border rounded bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;"><i class="fas fa-robot mr-1 text-primary"></i> Status AI (CV):</span>
                                    <small class="text-muted font-weight-bold" style="font-size: 11px;" id="slot-availability-text" {!! (!isset($initialStatus) || $initialStatus !== 'online') ? 'style="display: none;"' : '' !!}>
                                        Tersedia: <span id="realtime-available-text">{{ max(0, $maxSlots - ($initialCount ?? 0)) }}</span> | Terisi: <span id="realtime-count-text">{{ $initialCount ?? 0 }}</span>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center mt-2">
                                    <span id="cv-status-text" class="font-weight-bold text-secondary" style="font-size: 16px;">
                                        <i class="fas fa-circle-notch fa-spin mr-1"></i> MENGHITUNG...
                                    </span>
                                </div>
                                <div class="progress mt-2" style="height: 8px;">
                                    <div id="availability-progress-bar" class="progress-bar bg-secondary" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>

                            <div id="val-report-container" class="p-3 border rounded" style="display: none; background-color: #fdfaf3;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="font-weight-bold text-dark" style="font-size: 13px;"><i class="fas fa-user-edit mr-1 text-warning"></i> Laporan Pengguna:</span>
                                    <small class="text-muted text-right" style="font-size: 11px;">
                                        <div class="mb-1"><i class="fas fa-history mr-1"></i> Terakhir: <span id="val-last-time">-</span></div>
                                        <div><i class="fas fa-hourglass-half mr-1 text-danger"></i> Berakhir: <span id="val-expires-time" class="text-danger font-weight-bold">-</span></div>
                                    </small>
                                </div>
                                <div class="d-flex align-items-center mb-2">
                                    <span id="voted-status-text" class="font-weight-bold" style="font-size: 14px; text-transform: uppercase;">-</span>
                                    <span id="validation-badge-tervalidasi" class="badge text-white ml-2" style="background-color: #31ce36; display: none; font-size: 10px; padding: 4px 6px;">
                                        <i class="fas fa-check"></i> Tervalidasi
                                    </span>
                                    <span id="validation-badge-berbeda" class="badge text-white ml-2" style="background-color: #ffad46; display: none; font-size: 10px; padding: 4px 6px;">
                                        <i class="fas fa-exclamation"></i> Laporan Berbeda
                                    </span>
                                </div>
                                <div style="font-size: 11px;" class="text-muted">
                                    Status AI saat laporan dibuat: <span id="anchor-cv-status-text" class="font-weight-bold text-dark" style="text-transform: uppercase;">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <p class="small text-muted mb-2 text-center text-md-left">Simulasi validasi manual untuk menguji respons sistem:</p>
                    <div class="d-flex flex-column align-items-center align-items-md-start" style="max-width: 150px;">
                        <button class="btn btn-sm btn-success mb-2 w-100 text-left" id="btn-val-banyak" onclick="validateStream('banyak')">
                            <i class="fas fa-check-circle mr-1"></i> Banyak Tersedia
                        </button>
                        <button class="btn btn-sm btn-warning text-white mb-2 w-100 text-left" id="btn-val-terbatas" onclick="validateStream('terbatas')">
                            <i class="fas fa-exclamation-circle mr-1"></i> Terbatas
                        </button>
                        <button class="btn btn-sm btn-danger mb-2 w-100 text-left" id="btn-val-penuh" onclick="validateStream('penuh')">
                            <i class="fas fa-times-circle mr-1"></i> Penuh
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-header" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0 text-dark"><i class="fas fa-terminal mr-2"></i> Data Log</h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" id="log-list" style="max-height: 400px; overflow-y: auto; font-family: monospace; font-size: 12px;">
                        <li class="list-group-item text-center text-muted py-3" id="empty-log-msg">
                            Log data akan muncul di sini...
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-md-12 mt-4">
            <div class="card shadow-sm" style="border-radius: 15px; border: none; height: 100%;">
                <div class="card-header d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-images mr-2"></i> Koleksi Snapshot Deteksi (Dataset Training)
                    </h4>
                    <div>
                        <span class="badge badge-info" id="selected-count-badge">0 Terpilih</span>
                    </div>
                </div>
                <div class="card-body bg-white text-dark p-3" style="border-radius: 0 0 15px 15px;">
                    <form id="download-batch-form" action="{{ route('admin.iot.download-batch') }}" method="POST" target="_blank">
                        @csrf
                        <input type="hidden" name="mac_address" value="{{ $targetMac }}">

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="font-weight-bold small mb-1 text-dark">Status Pelatihan:</label>
                                <select class="form-control form-control-sm" name="filter_trained" id="filter-trained" onchange="filterCaptures()">
                                    <option value="all">Semua Data</option>
                                    <option value="no" selected>Belum Dilatih (New)</option>
                                    <option value="yes">Sudah Dilatih (Trained)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="font-weight-bold small mb-1 text-dark">Hasil Deteksi CV:</label>
                                <select class="form-control form-control-sm" name="filter_cv_status" id="filter-cv-status" onchange="filterCaptures()">
                                    <option value="all">Semua Status</option>
                                    <option value="banyak">Banyak Tersedia</option>
                                    <option value="terbatas">Terbatas</option>
                                    <option value="penuh">Penuh</option>
                                </select>
                            </div>
                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check p-0 mb-1">
                                    <label class="form-check-label font-weight-bold small text-dark d-flex align-items-center">
                                        <input class="form-check-input mr-2" type="checkbox" name="mark_as_trained" value="true" checked style="width: 16px; height: 16px; position: static;">
                                        Tandai sudah dilatih setelah unduh
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="btn-group">
                                <button type="button" class="btn btn-xs btn-outline-secondary" onclick="toggleSelectAllCaptures(true)">Pilih Semua</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary ml-1" onclick="toggleSelectAllCaptures(false)">Batal Pilih</button>
                            </div>
                            <div>
                                <button type="button" class="btn btn-xs btn-danger mr-1" onclick="deleteSelectedCaptures()">
                                    <i class="fas fa-trash mr-1"></i> Hapus Terpilih
                                </button>
                                <button type="submit" class="btn btn-xs btn-primary">
                                    <i class="fas fa-download mr-1"></i> Batch Download (ZIP)
                                </button>
                            </div>
                        </div>

                        <div id="captures-grid-container" style="max-height: 280px; overflow-y: auto; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; background-color: #f8f9fa;">
                            @if(session('error'))
                                <div class="alert alert-danger py-2 small mb-2">{{ session('error') }}</div>
                            @endif
                            <div class="row" id="captures-grid-row" style="margin-left: -5px; margin-right: -5px;">
                                @include('Contents.IoTDetection.partials.captures_grid')
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window._pendingLogs = [];
    function safeAddLog(message) {
        if (typeof window.addLog === 'function') {
            window.addLog(message);
        } else {
            window._pendingLogs.push(message);
        }
    }

    let lastValidationTime = @json($lastValidationTime ?? null);
    let validationExpiresAt = @json($validationExpiresAt ?? null);
    let isValidated = @json($isValidated ?? false);
    let hasUserReport = @json($hasUserReport ?? false);

    let votedStatus = @json($votedStatus ?? null);
    let anchorCvStatus = @json($anchorCvStatus ?? null);

    function updateValidationInfoUI() {
        const reportContainer = document.getElementById('val-report-container');
        const badgeTervalidasi = document.getElementById('validation-badge-tervalidasi');
        const badgeBerbeda = document.getElementById('validation-badge-berbeda');
        const timeText = document.getElementById('val-last-time');
        const expiresTimeText = document.getElementById('val-expires-time');
        const votedStatusText = document.getElementById('voted-status-text');
        const anchorCvStatusText = document.getElementById('anchor-cv-status-text');
        
        if (!reportContainer) return;
        if (!lastValidationTime || !validationExpiresAt) {
            reportContainer.style.display = 'none';
            return;
        }
        
        const now = new Date();
        const expiresAt = new Date(validationExpiresAt);
        const startAt = new Date(lastValidationTime);
        
        if (now <= expiresAt) {
            reportContainer.style.display = 'block';
            timeText.innerText = startAt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            if (expiresTimeText) {
                expiresTimeText.innerText = expiresAt.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
            }
            if (votedStatusText) {
                votedStatusText.innerText = votedStatus ? votedStatus.toUpperCase() : '-';
                if (votedStatus === 'banyak') votedStatusText.style.color = '#31ce36';
                else if (votedStatus === 'terbatas') votedStatusText.style.color = '#ffad46';
                else if (votedStatus === 'penuh') votedStatusText.style.color = '#f25961';
                else votedStatusText.style.color = '#6861ce';
            }
            if (anchorCvStatusText) {
                anchorCvStatusText.innerText = anchorCvStatus ? anchorCvStatus.toUpperCase() : '-';
                if (anchorCvStatus === 'banyak') anchorCvStatusText.style.color = '#31ce36';
                else if (anchorCvStatus === 'terbatas') anchorCvStatusText.style.color = '#ffad46';
                else if (anchorCvStatus === 'penuh') anchorCvStatusText.style.color = '#f25961';
                else anchorCvStatusText.style.color = '#6861ce';
            }

            if (isValidated) {
                if (badgeTervalidasi) badgeTervalidasi.style.display = 'inline-block';
                if (badgeBerbeda) badgeBerbeda.style.display = 'none';
            } else if (hasUserReport) {
                if (badgeTervalidasi) badgeTervalidasi.style.display = 'none';
                if (badgeBerbeda) badgeBerbeda.style.display = 'inline-block';
            } else {
                if (badgeTervalidasi) badgeTervalidasi.style.display = 'none';
                if (badgeBerbeda) badgeBerbeda.style.display = 'none';
            }
        } else {
            reportContainer.style.display = 'none';
        }
    }

    setInterval(updateValidationInfoUI, 10000);
    document.addEventListener('DOMContentLoaded', updateValidationInfoUI);

    function switchDevice(macAddress) {
        const url = new URL(window.location.href);
        url.searchParams.set('mac', macAddress);
        window.location.href = url.toString();
    }

    function validateStream(content) {
        const macAddress = "{{ $targetMac }}";
        const btnBanyak = document.getElementById('btn-val-banyak');
        const btnTerbatas = document.getElementById('btn-val-terbatas');
        const btnPenuh = document.getElementById('btn-val-penuh');
        
        // Disable buttons
        btnBanyak.disabled = true;
        btnTerbatas.disabled = true;
        btnPenuh.disabled = true;

        safeAddLog(`Memicu validasi manual [${content.toUpperCase()}] untuk ${macAddress}...`);

        fetch("{{ route('admin.iot.validate') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ 
                mac_address: macAddress,
                validation_content: content
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                safeAddLog(data.message);

                // Optimistically update info waktu validasi (berlaku 5 menit)
                const now = new Date();
                lastValidationTime = now.toISOString();
                validationExpiresAt = new Date(now.getTime() + 5 * 60000).toISOString();
                updateValidationInfoUI();

                // Override sementara badge realtime agar langsung merefleksikan validasi
                const badge = document.getElementById('realtime-availability-badge');
                if (badge) {
                    if (content === 'penuh') {
                        badge.className = "badge text-white mb-1";
                        badge.style.backgroundColor = "#f25961";
                        badge.innerHTML = '<i class="fas fa-times-circle mr-1"></i> Penuh (Validasi)';
                    } else if (content === 'terbatas') {
                        badge.className = "badge text-white mb-1";
                        badge.style.backgroundColor = "#ffad46";
                        badge.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> Terbatas (Validasi)';
                    } else if (content === 'banyak') {
                        badge.className = "badge text-white mb-1";
                        badge.style.backgroundColor = "#31ce36";
                        badge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Banyak Tersedia (Validasi)';
                    }
                }

            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Gagal memicu validasi: ' + data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan jaringan.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        })
        .finally(() => {
            btnBanyak.disabled = false;
            btnTerbatas.disabled = false;
            btnPenuh.disabled = false;
        });
    }
</script>

<script>
    let completedPolygons = @json($detectionPolygon) || [];
    let currentPoints = [];
    let isDrawingMode = false;

    let hoverVertex = null;   // {polyIndex, vertIndex} | null
    let isDragging = false;
    let dragTarget = null;    // {polyIndex, vertIndex}; polyIndex = -1 → currentPoints
    let didDrag = false;      // bedakan klik vs geser

    const VERTEX_HIT = 8;     // threshold piksel canvas untuk menyentuh vertex
    const EDGE_HIT = 6;       // threshold piksel canvas untuk menyentuh garis

    const canvas = document.getElementById('drawing-canvas');
    const ctx = canvas?.getContext('2d');
    const liveImage = document.getElementById('live-image');

    function initCanvas() {
        if (!liveImage || !canvas || liveImage.classList.contains('d-none') || !liveImage.clientWidth) return;

        canvas.width = liveImage.clientWidth;
        canvas.height = liveImage.clientHeight;
        // Poligon selesai selalu bisa diedit (seperti subarea) — terima event permanen.
        canvas.style.pointerEvents = 'auto';

        draw();
    }

    function toNatural(clientX, clientY) {
        const rect = canvas.getBoundingClientRect();
        const sx = liveImage.naturalWidth / rect.width;
        const sy = liveImage.naturalHeight / rect.height;
        return [
            Math.round((clientX - rect.left) * sx),
            Math.round((clientY - rect.top) * sy)
        ];
    }

    function toCanvas(pt) {
        const sx = canvas.width / liveImage.naturalWidth;
        const sy = canvas.height / liveImage.naturalHeight;
        return { x: pt[0] * sx, y: pt[1] * sy };
    }

    function eventToCanvas(e) {
        const rect = canvas.getBoundingClientRect();
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    }

    function distToSegment(px, py, a, b) {
        const dx = b.x - a.x;
        const dy = b.y - a.y;
        const lenSq = dx * dx + dy * dy;
        if (lenSq === 0) return Math.hypot(px - a.x, py - a.y);
        let t = ((px - a.x) * dx + (py - a.y) * dy) / lenSq;
        t = Math.max(0, Math.min(1, t));
        return Math.hypot(px - (a.x + t * dx), py - (a.y + t * dy));
    }

    function hitTestVertex(cx, cy) {
        let best = null;
        let bestDist = VERTEX_HIT;
        completedPolygons.forEach((poly, polyIndex) => {
            poly.forEach((pt, vertIndex) => {
                const c = toCanvas(pt);
                const d = Math.hypot(c.x - cx, c.y - cy);
                if (d <= bestDist) { bestDist = d; best = { polyIndex, vertIndex }; }
            });
        });
        currentPoints.forEach((pt, vertIndex) => {
            const c = toCanvas(pt);
            const d = Math.hypot(c.x - cx, c.y - cy);
            if (d <= bestDist) { bestDist = d; best = { polyIndex: -1, vertIndex }; }
        });
        return best;
    }

    function hitTestEdge(cx, cy) {
        let best = null;
        let bestDist = EDGE_HIT;
        completedPolygons.forEach((poly, polyIndex) => {
            for (let i = 0; i < poly.length; i++) {
                const a = toCanvas(poly[i]);
                const b = toCanvas(poly[(i + 1) % poly.length]);
                const d = distToSegment(cx, cy, a, b);
                if (d <= bestDist) { bestDist = d; best = { polyIndex, edgeIndex: i }; }
            }
        });
        return best;
    }

    if (liveImage) {
        liveImage.addEventListener('load', () => {
            const placeholder = document.getElementById('placeholder-container');
            const container = document.getElementById('canvas-container');
            if (placeholder) placeholder.classList.add('d-none');
            if (container) container.classList.remove('d-none');
            setTimeout(initCanvas, 100);
        });
    }

    window.addEventListener('resize', initCanvas);

    if (canvas) {
        canvas.addEventListener('mousedown', (e) => {
            if (!isDrawingMode) return;
            if (e.button !== 0) return; // hanya tombol kiri
            const { x, y } = eventToCanvas(e);
            didDrag = false;

            const v = hitTestVertex(x, y);
            if (v) {
                isDragging = true;
                dragTarget = v;
                return;
            }

            const edge = hitTestEdge(x, y);
            if (edge) {
                const poly = completedPolygons[edge.polyIndex];
                poly.splice(edge.edgeIndex + 1, 0, toNatural(e.clientX, e.clientY));
                isDragging = true;
                dragTarget = { polyIndex: edge.polyIndex, vertIndex: edge.edgeIndex + 1 };
                draw();
            }
        });

        canvas.addEventListener('mousemove', (e) => {
            if (!isDrawingMode) {
                canvas.style.cursor = 'default';
                return;
            }
            const { x, y } = eventToCanvas(e);

            if (isDragging && dragTarget) {
                const pt = toNatural(e.clientX, e.clientY);
                if (dragTarget.polyIndex === -1) {
                    currentPoints[dragTarget.vertIndex] = pt;
                } else {
                    completedPolygons[dragTarget.polyIndex][dragTarget.vertIndex] = pt;
                }
                didDrag = true;
                draw();
                return;
            }

            // Update hover + cursor afordans.
            const v = hitTestVertex(x, y);
            const edge = v ? null : hitTestEdge(x, y);
            const prevHover = hoverVertex;
            hoverVertex = v;
            if (v) {
                canvas.style.cursor = 'pointer';
            } else if (edge) {
                canvas.style.cursor = 'copy';
            } else {
                canvas.style.cursor = isDrawingMode ? 'crosshair' : 'default';
            }
            // Redraw hanya jika hover berubah agar hemat.
            if (JSON.stringify(prevHover) !== JSON.stringify(hoverVertex)) draw();
        });

        canvas.addEventListener('mouseup', () => {
            isDragging = false;
            dragTarget = null;
        });

        canvas.addEventListener('click', (e) => {
            // Abaikan klik yang sebenarnya hasil dari geser.
            if (didDrag) { didDrag = false; return; }
            if (!isDrawingMode) return;

            if (currentPoints.length >= 3) {
                const first = toCanvas(currentPoints[0]);
                const { x, y } = eventToCanvas(e);
                if (Math.hypot(first.x - x, first.y - y) <= VERTEX_HIT) {
                    closeCurrentPolygon();
                    return;
                }
            }

            currentPoints.push(toNatural(e.clientX, e.clientY));
            draw();
        });

        canvas.addEventListener('contextmenu', (e) => {
            e.preventDefault();
            if (!isDrawingMode) return;
            const { x, y } = eventToCanvas(e);
            const v = hitTestVertex(x, y);
            if (!v) return;

            if (v.polyIndex === -1) {
                currentPoints.splice(v.vertIndex, 1);
                draw();
                return;
            }

            const poly = completedPolygons[v.polyIndex];
            if (poly.length <= 3) {
                Swal.fire({
                    title: 'Hapus zona ini?',
                    text: 'Poligon hanya punya 3 titik. Menghapus sudut akan menghapus seluruh zona deteksi ini.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Ya, hapus zona!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        completedPolygons.splice(v.polyIndex, 1);
                        hoverVertex = null;
                        draw();
                        safeAddLog('Zona deteksi dihapus.');
                    }
                });
                return;
            }

            poly.splice(v.vertIndex, 1);
            hoverVertex = null;
            draw();
        });
    }

    function toggleDrawMode() {
        isDrawingMode = !isDrawingMode;
        const btn = document.getElementById('btn-draw-mode');
        if (!btn || !canvas) return;

        if (isDrawingMode) {
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-primary');
            canvas.style.cursor = 'crosshair';
            safeAddLog('Mode menggambar aktif. Meminta snapshot terbaru dari perangkat IoT...');

            fetch("{{ route('admin.iot.trigger') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ 
                    mac_address: "{{ $targetMac }}",
                    save_image: false
                })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    safeAddLog('Berhasil memicu pengambilan snapshot. Menunggu respons gambar...');
                } else {
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Gagal memicu snapshot: ' + data.message,
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                }
            })
            .catch(err => {
                console.error("Error triggering snapshot:", err);
                safeAddLog('Error memicu snapshot: Terjadi masalah jaringan.');
            });
        } else {
            btn.classList.remove('btn-primary');
            btn.classList.add('btn-outline-primary');
            canvas.style.cursor = 'default';
            currentPoints = [];
            draw();
        }
    }



    function closeCurrentPolygon() {
        if (currentPoints.length < 3) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Minimal harus ada 3 titik untuk membuat polygon.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        completedPolygons.push([...currentPoints]);
        currentPoints = [];
        draw();
        safeAddLog('Zona polygon baru selesai dibuat.');
    }

    function clearAllPolygons() {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: 'Semua polygon deteksi akan dihapus dari layar!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                completedPolygons = [];
                currentPoints = [];
                draw();
                safeAddLog('Semua polygon dihapus dari layar.');
            }
        });
    }

    function draw() {
        if (!ctx || !canvas || !liveImage) return;

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        completedPolygons.forEach((poly, index) => {
            ctx.beginPath();
            poly.forEach((pt, i) => {
                const c = toCanvas(pt);
                if (i === 0) ctx.moveTo(c.x, c.y);
                else ctx.lineTo(c.x, c.y);
            });
            ctx.closePath();
            ctx.fillStyle = 'rgba(49, 206, 54, 0.2)'; // Green transparent
            ctx.fill();
            ctx.strokeStyle = '#31ce36'; // Green stroke
            ctx.lineWidth = 2;
            ctx.stroke();

            // Draw label text
            if (poly.length > 0) {
                const c0 = toCanvas(poly[0]);
                ctx.fillStyle = '#1572e8';
                ctx.font = 'bold 12px sans-serif';
                ctx.fillText('Zona ' + (index + 1), c0.x, c0.y - 8);
            }

            // Handle vertex — lingkaran putih ber-border warna zona (meniru pin subarea).
            poly.forEach((pt, vertIndex) => {
                const c = toCanvas(pt);
                const isHover = hoverVertex && hoverVertex.polyIndex === index && hoverVertex.vertIndex === vertIndex;
                const r = isHover ? 8 : 6;
                ctx.beginPath();
                ctx.arc(c.x, c.y, r, 0, 2 * Math.PI);
                ctx.fillStyle = '#ffffff';
                ctx.fill();
                ctx.lineWidth = isHover ? 3 : 2;
                ctx.strokeStyle = '#31ce36';
                ctx.stroke();
            });
        });

        if (currentPoints.length > 0) {
            ctx.beginPath();
            currentPoints.forEach((pt, i) => {
                const c = toCanvas(pt);
                if (i === 0) ctx.moveTo(c.x, c.y);
                else ctx.lineTo(c.x, c.y);
            });
            ctx.strokeStyle = '#ffad46'; // Orange stroke
            ctx.lineWidth = 2;
            ctx.stroke();

            currentPoints.forEach((pt, i) => {
                const c = toCanvas(pt);
                const isFirst = i === 0;
                const isHover = hoverVertex && hoverVertex.polyIndex === -1 && hoverVertex.vertIndex === i;
                // Titik pertama dibuat menonjol saat ≥3 titik → afordans "klik untuk menutup".
                const prominent = isFirst && currentPoints.length >= 3;
                const r = prominent ? 8 : (isHover ? 6 : 4);
                ctx.beginPath();
                ctx.arc(c.x, c.y, r, 0, 2 * Math.PI);
                if (prominent) {
                    ctx.fillStyle = '#ffffff';
                    ctx.fill();
                    ctx.lineWidth = 3;
                    ctx.strokeStyle = '#ffad46';
                    ctx.stroke();
                } else {
                    ctx.fillStyle = '#ffad46';
                    ctx.fill();
                }
            });
        }
    }

    // Mengirim konfigurasi deteksi ke server
    function saveDetectionConfig() {
        const macAddress = "{{ $targetMac }}";
        const maxSlots = document.getElementById('max-slots').value;
        const thresholdBanyak = document.getElementById('input-threshold-banyak').value;
        const thresholdTerbatas = document.getElementById('input-threshold-terbatas').value;

        if (!maxSlots || maxSlots <= 0) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Kapasitas maksimal harus lebih besar dari 0!',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        safeAddLog('Menyimpan konfigurasi deteksi...');

        fetch("{{ route('admin.iot.save-settings') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                mac_address: macAddress,
                max_slots: parseInt(maxSlots),
                threshold_banyak: parseFloat(thresholdBanyak),
                threshold_terbatas: parseFloat(thresholdTerbatas),
                detection_polygon: completedPolygons
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    title: 'Berhasil!',
                    text: 'Konfigurasi deteksi berhasil disimpan dan disinkronkan ke perangkat!',
                    icon: 'success',
                    confirmButtonText: 'OK'
                });
                safeAddLog('Konfigurasi deteksi berhasil disimpan ke database.');
            } else {
                Swal.fire({
                    title: 'Gagal!',
                    text: 'Gagal menyimpan: ' + data.message,
                    icon: 'error',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
            Swal.fire({
                title: 'Gagal!',
                text: 'Terjadi kesalahan jaringan saat menyimpan konfigurasi.',
                icon: 'error',
                confirmButtonText: 'OK'
            });
        });
    }

    // --- LOGIKA GALERI & BATCH ACTIONS SNAPSHOT DATASET ---
    function filterCaptures() {
        const trainedVal = document.getElementById('filter-trained').value;
        const cvVal = document.getElementById('filter-cv-status').value;
        const cards = document.querySelectorAll('.capture-item-card');
        let visibleCount = 0;

        cards.forEach(card => {
            const isTrained = card.getAttribute('data-trained'); // 'yes' or 'no'
            const cvStatus = card.getAttribute('data-status');   // 'banyak' / 'terbatas' / 'penuh' / 'unknown'

            let matchTrained = (trainedVal === 'all') || (trainedVal === isTrained);
            let matchCv = (cvVal === 'all') || (cvVal === cvStatus);

            if (matchTrained && matchCv) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
                const cb = card.querySelector('.capture-checkbox');
                if (cb) cb.checked = false; // Uncheck hidden elements to avoid accidental batch operations
            }
        });

        updateSelectedCount();

        let noCapPlaceholder = document.getElementById('no-captures-placeholder');
        if (visibleCount === 0) {
            if (!noCapPlaceholder) {
                const gridRow = document.getElementById('captures-grid-row');
                gridRow.insertAdjacentHTML('beforeend', `
                    <div class="col-md-12 text-center text-muted py-4" id="no-captures-placeholder">
                        <i class="fas fa-images fa-2x mb-2 text-muted"></i>
                        <p class="mb-0 small">Tidak ada gambar yang cocok dengan filter.</p>
                    </div>
                `);
            } else {
                noCapPlaceholder.style.display = 'block';
                noCapPlaceholder.querySelector('p').innerText = "Tidak ada gambar yang cocok dengan filter.";
            }
        } else {
            if (noCapPlaceholder) {
                noCapPlaceholder.style.display = 'none';
            }
        }
    }

    function toggleSelectAllCaptures(status) {
        const checkboxes = document.querySelectorAll('.capture-item-card:not([style*="display: none"]) .capture-checkbox');
        checkboxes.forEach(cb => {
            cb.checked = status;
        });
        updateSelectedCount();
    }

    function updateSelectedCount() {
        const checkedCount = document.querySelectorAll('.capture-checkbox:checked').length;
        document.getElementById('selected-count-badge').innerText = `${checkedCount} Terpilih`;
    }

    function deleteSelectedCaptures() {
        const checkedCheckboxes = document.querySelectorAll('.capture-checkbox:checked');
        if (checkedCheckboxes.length === 0) {
            Swal.fire({
                title: 'Perhatian!',
                text: 'Silakan pilih minimal 1 gambar untuk dihapus.',
                icon: 'warning',
                confirmButtonText: 'OK'
            });
            return;
        }

        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: `Apakah Anda yakin ingin menghapus ${checkedCheckboxes.length} gambar terpilih dari server?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                const ids = Array.from(checkedCheckboxes).map(cb => parseInt(cb.value));

                fetch("{{ route('admin.iot.delete-batch') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ capture_ids: ids })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            title: 'Berhasil!',
                            text: data.message,
                            icon: 'success',
                            confirmButtonText: 'OK'
                        });
                        window.refreshCaptures();
                    } else {
                        Swal.fire({
                            title: 'Gagal!',
                            text: 'Gagal menghapus gambar: ' + data.message,
                            icon: 'error',
                            confirmButtonText: 'OK'
                        });
                    }
                })
                .catch(err => {
                    console.error('Error deleting captures:', err);
                    Swal.fire({
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan jaringan saat menghapus.',
                        icon: 'error',
                        confirmButtonText: 'OK'
                    });
                });
            }
        });
    }

    // AJAX Refresh function for dataset captures grid
    window.refreshCaptures = function() {
        const macAddress = "{{ $targetMac }}";
        const url = `{{ route('admin.iot.index') }}?mac=${macAddress}`;

        fetch(url, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.text())
        .then(html => {
            const gridRow = document.getElementById('captures-grid-row');
            if (gridRow) {
                gridRow.innerHTML = html;
                // Run filterCaptures again to apply active filters and update selection count
                filterCaptures();
            }
        })
        .catch(err => {
            console.error('Error refreshing captures:', err);
        });
    };

    // Jalankan filter filterCaptures pertama kali saat dimuat untuk menerapkan default (Belum Dilatih)
    document.addEventListener('DOMContentLoaded', () => {
        setTimeout(filterCaptures, 300);

        // Listen to download form submission to refresh grid after download starts
        const downloadForm = document.getElementById('download-batch-form');
        if (downloadForm) {
            downloadForm.addEventListener('submit', function() {
                setTimeout(() => {
                    if (typeof window.refreshCaptures === 'function') {
                        window.refreshCaptures();
                    }
                }, 1500);
            });
        }

        // Card click event delegation: toggles checkbox status when clicking on card body
        const capturesGrid = document.getElementById('captures-grid-row');
        if (capturesGrid) {
            capturesGrid.addEventListener('click', function(e) {
                const card = e.target.closest('.capture-card');
                if (!card) return;

                // Ignore if clicked on a link, image inside a link, or the checkbox itself
                if (e.target.closest('a') || e.target.closest('.capture-checkbox') || e.target.type === 'checkbox') {
                    return;
                }

                const checkbox = card.querySelector('.capture-checkbox');
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    updateSelectedCount();
                }
            });
        }
    });
</script>


<script type="module">
    const cleanMac = "{{ str_replace(':', '', $targetMac) }}";
    const channelName = `iot.detection.${cleanMac}`;
    const serverInitialStatus = "{{ $initialStatus }}";

    const connectionStatus = document.getElementById('connection-status');
    const logList = document.getElementById('log-list');
    const emptyLogMsg = document.getElementById('empty-log-msg');
    const liveImage = document.getElementById('live-image');
    const feedPlaceholder = document.getElementById('feed-placeholder');
    const feedContainerIcon = document.querySelector('#feed-container i');

    // Bug #6: Pending log queue — tangkap log messages sebelum module ready
    const pendingLogs = [];

    function addLog(message) {
        if (emptyLogMsg) emptyLogMsg.style.display = 'none';
        
        const li = document.createElement('li');
        li.className = 'list-group-item px-3 py-2 border-bottom';
        
        const time = new Date().toLocaleTimeString('id-ID');
        li.innerHTML = `<span class="text-primary">[${time}]</span> <span class="text-dark">${message}</span>`;
        
        logList.prepend(li); // Masukkan ke urutan paling atas
        
        if (logList.children.length > 50) {
            logList.removeChild(logList.lastChild);
        }
    }

    // Bug #5 & #6: Expose addLog ke global scope SEGERA (bukan di akhir)
    // agar regular scripts bisa memakainya tanpa race condition
    window.addLog = addLog;

    // Flush pending logs yang dikumpulkan sebelum module ready
    if (window._pendingLogs && window._pendingLogs.length > 0) {
        window._pendingLogs.forEach(msg => addLog(msg));
        window._pendingLogs = [];
    }

    // Bug #4: Track channel subscriptions untuk cleanup
    let streamChannel = null;
    let presenceChannel = null;
    let statusChannel = null;

    // Menggunakan window.Echo yang telah di-bundle secara internal oleh Vite (app.js/echo.js)
    function initEcho() {
        if (typeof window.Echo !== 'undefined') {
            connectionStatus.className = "badge badge-success";
            connectionStatus.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Terhubung ke Reverb';
            addLog(`Tersambung ke server. Mendengarkan channel: <strong>${channelName}</strong>`);
            
            streamChannel = window.Echo.channel(channelName)
                .listen('.iot.detection.received', (e) => {
                    let frameData = e.frameData;
                    
                    if(frameData.startsWith('data:image')) {
                        liveImage.src = frameData;
                        liveImage.classList.remove('d-none');
                        if (feedPlaceholder) feedPlaceholder.style.display = 'none';
                        if (feedContainerIcon) feedContainerIcon.style.display = 'none';
                        
                        addLog('Menerima frame gambar snapshot.');

                        // Silent refresh the gallery grid ONLY if it is a saved validation snapshot
                        if (e.isSaved && typeof window.refreshCaptures === 'function') {
                            window.refreshCaptures();
                        }
                    } else {
                        liveImage.classList.add('d-none');
                        if (feedPlaceholder) {
                            feedPlaceholder.style.display = 'block';
                            feedPlaceholder.innerHTML = `<span class="text-primary font-weight-bold" style="font-size: 24px;">${frameData}</span>`;
                        }
                        if (feedContainerIcon) {
                            feedContainerIcon.className = "fas fa-comment-dots fa-4x text-info mb-3";
                            feedContainerIcon.style.display = 'inline-block';
                        }
                        
                        addLog(`Pesan teks: ${frameData}`);
                    }
                })
                .listen('.count.updated', (e) => {
                    addLog(`Jumlah kendaraan terdeteksi: <strong>${e.count}</strong>`);
                    const countText = document.getElementById('realtime-count-text');
                    if (countText) {
                        countText.innerText = e.count;
                    }
                    if (typeof window.updateAvailabilityUI === 'function') window.updateAvailabilityUI();
                })
                .listen('.threshold.updated', (e) => {
                    addLog(`Threshold WMA bergeser: Banyak=<strong>${e.thresholdBanyak}%</strong>, Terbatas=<strong>${e.thresholdTerbatas}%</strong>`);
                    
                    const sliderBanyak = document.getElementById('input-threshold-banyak');
                    const sliderTerbatas = document.getElementById('input-threshold-terbatas');
                    
                    if (sliderBanyak && sliderTerbatas) {
                        sliderBanyak.value = Math.round(e.thresholdBanyak);
                        sliderTerbatas.value = Math.round(e.thresholdTerbatas);
                        
                        // Bug #5: Gunakan window.updateSliderUI (exposed ke global scope)
                        if (typeof window.updateSliderUI === 'function') {
                            window.updateSliderUI();
                        }
                    }
                });
        } else {
            console.warn("Menunggu modul mandiri (Echo) dari Vite dimuat...");
            setTimeout(initEcho, 500);
        }
    }

    // Mulai inisiasi
    initEcho();

    // Bug #3: Hydrate initial status dari server segera (sebelum presence channel ready)
    updateStatusUI(serverInitialStatus);

    // Listen untuk Presence Channel (instant status) setelah Echo jalan
    function initStatusEcho() {
        if (typeof window.Echo !== 'undefined') {
            // =====================================================
            // PRESENCE CHANNEL — Instant Online/Offline Detection
            // =====================================================
            const presenceChannelName = `iot.device.${cleanMac}`;
            presenceChannel = window.Echo.join(presenceChannelName)
                .here((members) => {
                    const isDeviceOnline = members.some(m => m.type === 'iot_device');
                    if (isDeviceOnline) {
                        updateStatusUI('online');
                        addLog(`✅ Perangkat IoT terdeteksi ONLINE (via Presence Channel)`);
                    } else {
                        // Bug #7: Jika tidak ada IoT device di presence channel,
                        // JANGAN override status — biarkan serverInitialStatus yang berlaku.
                        // Device MQTT tidak join presence channel, jadi ketiadaan di sini
                        // bukan berarti device offline.
                        addLog(`ℹ️ Presence channel terhubung. Status perangkat: ${serverInitialStatus.toUpperCase()} (dari server)`);
                    }
                })
                .joining((member) => {
                    if (member.type === 'iot_device') {
                        updateStatusUI('online');
                        addLog(`✅ Perangkat IoT baru saja ONLINE (instant)`);
                    }
                })
                .leaving((member) => {
                    if (member.type === 'iot_device') {
                        updateStatusUI('offline');
                        addLog(`❌ Perangkat IoT baru saja OFFLINE (instant)`);
                    }
                });

            // =====================================================
            // FALLBACK: Listen Status dari MQTT/WS broadcast events
            // (Menangkap status changes dari MqttListenerCommand & IotWebhookController)
            // =====================================================
            statusChannel = window.Echo.channel('iot.status')
                .listen('.device.status', (e) => {
                    console.log("📡 Status Received (MQTT/WS):", e);
                    const selectedMac = "{{ $targetMac }}";
                    if (e.macAddress.toLowerCase() === selectedMac.toLowerCase()) {
                        updateStatusUI(e.status);
                        addLog(`📡 Status ${e.status.toUpperCase()} diterima via MQTT/WS`);
                    }
                });

            // =====================================================
            // PARK AREA CHANNEL — Realtime Validation Status
            // =====================================================
            @if(isset($parkAreaId) && $parkAreaId > 0)
            window.Echo.channel('park-area.{{ $parkAreaId }}')
                .listen('.subarea.updated', (e) => {
                    // Cek apakah update ini untuk subarea perangkat yang sedang dilihat
                    if (e.parkSubareaId === {{ $parkSubareaId ?? 0 }}) {
                        isValidated = e.isValidated;
                        hasUserReport = e.hasUserReport;
                        validationExpiresAt = e.validationExpiresAt;
                        lastValidationTime = e.lastValidationTime;
                        votedStatus = e.votedStatus;
                        anchorCvStatus = e.anchorCvStatus;
                        
                        if (typeof window.updateValidationInfoUI === 'function') {
                            window.updateValidationInfoUI();
                        }
                    }
                });
            @endif
        } else {
            setTimeout(initStatusEcho, 500);
        }
    }

    function updateStatusUI(status) {
        const indicator = document.getElementById('status-indicator');
        const slotAvailabilityText = document.getElementById('slot-availability-text');

        if (status === 'online') {
            if (indicator) {
                indicator.className = "badge badge-success ml-2";
                indicator.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> ONLINE';
            }
            if (slotAvailabilityText) slotAvailabilityText.style.display = 'inline';
        } else {
            if (indicator) {
                indicator.className = "badge badge-danger ml-2";
                indicator.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> OFFLINE';
            }
            if (slotAvailabilityText) slotAvailabilityText.style.display = 'none';

            // Reset count badge ke 0 saat device offline
            // Ini memastikan UI konsisten dengan backend yang sudah reset current_count = 0
            const countText = document.getElementById('realtime-count-text');
            if (countText) {
                countText.innerText = '0';
            }
        }
        
        // Panggil updateAvailabilityUI untuk merefresh bar status CV
        if (typeof window.updateAvailabilityUI === 'function') {
            window.updateAvailabilityUI();
        }
    }
    
    initStatusEcho();

    // Bug #4: Cleanup Echo channels saat navigasi away (prevent listener leaks)
    window.addEventListener('beforeunload', () => {
        if (streamChannel) {
            window.Echo.leave(channelName);
        }
        if (presenceChannel) {
            window.Echo.leave(`iot.device.${cleanMac}`);
        }
        if (statusChannel) {
            window.Echo.leave('iot.status');
        }
    });

    // --- LOGIKA RANGE SLIDER GANDA KUSTOM ---
    const sliderBanyak = document.getElementById('input-threshold-banyak');
    const sliderTerbatas = document.getElementById('input-threshold-terbatas');

    const labelBanyakRange = document.getElementById('label-banyak-range');
    const labelTerbatasRange = document.getElementById('label-terbatas-range');
    const labelPenuhRange = document.getElementById('label-penuh-range');

    const segBanyak = document.getElementById('segment-banyak');
    const segTerbatas = document.getElementById('segment-terbatas');
    const segPenuh = document.getElementById('segment-penuh');

    function updateSliderUI() {
        let valBanyak = parseInt(sliderBanyak.value);
        let valTerbatas = parseInt(sliderTerbatas.value);

        // Mencegah handle saling bersilangan (crossover)
        if (valBanyak >= valTerbatas) {
            valBanyak = valTerbatas - 1;
            sliderBanyak.value = valBanyak;
        }

        // Atur lebar & posisi segment berwarna
        segBanyak.style.width = valBanyak + '%';
        
        segTerbatas.style.left = valBanyak + '%';
        segTerbatas.style.width = (valTerbatas - valBanyak) + '%';
        
        segPenuh.style.left = valTerbatas + '%';
        segPenuh.style.width = (100 - valTerbatas) + '%';

        // Hitung slot integer
        const maxSlots = parseInt(document.getElementById('max-slots').value) || 0;
        let slotBanyak = Math.round((valBanyak / 100) * maxSlots);
        let slotTerbatas = Math.round((valTerbatas / 100) * maxSlots);

        // Update label teks
        labelBanyakRange.innerHTML = `<span style="font-size: 0.75rem;">(0 - ${slotBanyak} slot)</span><br>0% - ${valBanyak}%`;
        labelTerbatasRange.innerHTML = `<span style="font-size: 0.75rem;">(${slotBanyak} - ${slotTerbatas} slot)</span><br>${valBanyak}% - ${valTerbatas}%`;
        labelPenuhRange.innerHTML = `<span style="font-size: 0.75rem;">(${slotTerbatas} - ${maxSlots} slot)</span><br>${valTerbatas}% - 100%`;
    }

    // Bug #5: Expose updateSliderUI ke global scope agar event listener bisa akses
    window.updateSliderUI = updateSliderUI;

    if (sliderBanyak && sliderTerbatas) {
        sliderBanyak.addEventListener('input', updateSliderUI);
        sliderTerbatas.addEventListener('input', updateSliderUI);
        // Jalankan inisialisasi awal
        updateSliderUI();
    }
    
    // --- LOGIKA STATUS KETERSEDIAAN REAL-TIME ---
    function updateAvailabilityUI() {
        const statusText = document.getElementById('cv-status-text');
        const progressBar = document.getElementById('availability-progress-bar');
        
        // Cek apakah device sedang offline
        const indicator = document.getElementById('status-indicator');
        const isOffline = indicator && indicator.innerText.includes('OFFLINE');
        
        if (isOffline) {
            if (statusText) {
                statusText.className = "font-weight-bold";
                statusText.style.color = "#8d949a";
                statusText.innerHTML = '<i class="fas fa-power-off mr-1"></i> OFFLINE / NETRAL';
            }
            if (progressBar) {
                progressBar.className = "progress-bar";
                progressBar.style.backgroundColor = "#8d949a";
                progressBar.style.width = '0%';
                progressBar.setAttribute('aria-valuenow', 0);
            }
            return; // Hentikan eksekusi normal
        }

        const count = parseInt(document.getElementById('realtime-count-text')?.innerText) || 0;
        const max = parseInt(document.getElementById('max-slots')?.value) || 1;
        const thBanyak = parseInt(document.getElementById('input-threshold-banyak')?.value) || 30;
        const thTerbatas = parseInt(document.getElementById('input-threshold-terbatas')?.value) || 80;

        const percentage = Math.max(0, Math.min((count / max) * 100, 100));
        const availableText = document.getElementById('realtime-available-text');
        const countText = document.getElementById('realtime-count-text');

        if (availableText) availableText.innerText = Math.max(0, max - count);
        if (countText) countText.innerText = count;

        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
        }

        if (statusText) {
            if (percentage >= thTerbatas) {
                statusText.className = "font-weight-bold";
                statusText.style.color = "#f25961";
                statusText.innerHTML = '<i class="fas fa-times-circle mr-1"></i> PENUH';
                if (progressBar) {
                    progressBar.className = "progress-bar";
                    progressBar.style.backgroundColor = "#f25961";
                }
            } else if (percentage >= thBanyak) {
                statusText.className = "font-weight-bold";
                statusText.style.color = "#ffad46";
                statusText.innerHTML = '<i class="fas fa-exclamation-circle mr-1"></i> TERBATAS';
                if (progressBar) {
                    progressBar.className = "progress-bar";
                    progressBar.style.backgroundColor = "#ffad46";
                }
            } else {
                statusText.className = "font-weight-bold";
                statusText.style.color = "#31ce36";
                statusText.innerHTML = '<i class="fas fa-check-circle mr-1"></i> BANYAK TERSEDIA';
                if (progressBar) {
                    progressBar.className = "progress-bar";
                    progressBar.style.backgroundColor = "#31ce36";
                }
            }
        }
    }

    // Expose ke global dan pasang event listener agar realtime update jalan jika input diubah
    window.updateAvailabilityUI = updateAvailabilityUI;
    const maxSlotsInput = document.getElementById('max-slots');
    if (maxSlotsInput) {
        maxSlotsInput.addEventListener('input', () => {
            updateAvailabilityUI();
            if (typeof window.updateSliderUI === 'function') {
                window.updateSliderUI();
            }
        });
    }
    // Override updateSliderUI untuk memanggil updateAvailabilityUI
    const originalUpdateSliderUI = window.updateSliderUI;
    window.updateSliderUI = function() {
        originalUpdateSliderUI();
        updateAvailabilityUI();
    };
    
    // Jalankan pertama kali saat load
    updateAvailabilityUI();
    
    // override di click save config / dll (optional, jika perlu)
</script>
@endpush