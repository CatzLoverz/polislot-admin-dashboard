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
    {{-- Pemilihan Device --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body py-3 d-flex flex-column flex-md-row align-items-start align-items-md-center">
                    <div class="d-flex align-items-center mb-2 mb-md-0">
                        <i class="fas fa-microchip fa-lg text-primary mr-3"></i>
                        <div class="mr-3">
                            <label class="mb-0 font-weight-bold" for="device-selector">Pilih Perangkat IoT:</label>
                            <div id="status-indicator" class="badge badge-{{ $initialStatus === 'online' ? 'success' : 'danger' }} ml-1">
                                <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> {{ strtoupper($initialStatus) }}
                            </div>
                            <span class="badge badge-primary ml-1">
                                <i class="fas fa-car mr-1"></i> Count: <strong id="current-count-badge">{{ $initialCount ?? 0 }}</strong>
                            </span>
                        </div>
                    </div>
                    <select class="form-control mb-2 mb-md-0 w-100" id="device-selector" style="max-width: 450px;" onchange="switchDevice(this.value)">
                        @forelse($devices as $device)
                            <option value="{{ $device->device_mac_address }}" 
                                {{ $targetMac === $device->device_mac_address ? 'selected' : '' }}>
                                {{ $device->device_mac_address }}
                                @if($device->subarea)
                                    — {{ $device->subarea->parkArea->park_area_name ?? 'Area tidak diketahui' }}
                                    / {{ $device->subarea->park_subarea_name ?? 'Subarea tidak diketahui' }}
                                @endif
                            </option>
                        @empty
                            <option value="" disabled selected>Tidak ada perangkat terdaftar</option>
                        @endforelse
                    </select>
                    <span class="badge badge-info ml-md-3 mt-2 mt-md-0">
                        <i class="fas fa-hdd mr-1"></i> {{ $devices->count() }} Perangkat
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        {{-- Panel Kiri: Live Stream / Text Viewer --}}
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-md-center" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-2 mb-md-0 text-dark">
                        <i class="fas fa-satellite-dish mr-2"></i> Live Feed
                    </h4>
                    <div class="d-flex flex-wrap align-items-center">
                        <button class="btn btn-sm btn-success m-1" id="btn-val-banyak" onclick="validateStream('banyak')">
                            <i class="fas fa-check-circle mr-1"></i> Banyak
                        </button>
                        <button class="btn btn-sm btn-warning text-white m-1" id="btn-val-terbatas" onclick="validateStream('terbatas')">
                            <i class="fas fa-exclamation-circle mr-1"></i> Terbatas
                        </button>
                        <button class="btn btn-sm btn-danger m-1" id="btn-val-penuh" onclick="validateStream('penuh')">
                            <i class="fas fa-times-circle mr-1"></i> Penuh
                        </button>
                        <span class="badge badge-success m-1" id="connection-status">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Menghubungkan...
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 bg-light d-flex justify-content-center align-items-center" style="min-height: 400px; border-radius: 0 0 15px 15px;">
                    <div id="feed-container" class="text-center p-4 w-100 h-100 position-relative d-flex justify-content-center align-items-center">
                        <div id="placeholder-container">
                            <i class="fas fa-image fa-4x text-muted mb-3"></i>
                            <h5 class="text-muted" id="feed-placeholder">Menunggu data masuk dari MAC Address: <strong>{{ $targetMac }}</strong>...</h5>
                        </div>
                        <div id="canvas-container" class="position-relative d-none" style="max-width: 640px; margin: 0 auto;">
                            <img id="live-image" src="" alt="Live Stream" class="img-fluid rounded shadow" style="max-height: 400px; display: block; width: 100%; height: auto;">
                            <canvas id="drawing-canvas" class="position-absolute" style="top: 0; left: 0; width: 100%; height: 100%; z-index: 10; cursor: crosshair; pointer-events: none;"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Panel Config Deteksi Subarea --}}
            <div class="card shadow-sm mt-3 border-0">
                <div class="card-header" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0 text-dark">
                        <i class="fas fa-cogs mr-2"></i> Pengaturan Deteksi & Threshold Subarea
                    </h4>
                </div>
                <div class="card-body bg-white" style="border-radius: 0 0 15px 15px;">
                    <div class="row text-dark">
                        <div class="col-md-3">
                            <div class="form-group p-0">
                                <label class="font-weight-bold" for="max-slots">Kapasitas Maksimal (Max Slot)</label>
                                <input type="number" id="max-slots" class="form-control" value="{{ $maxSlots }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-9">
                            <label class="font-weight-bold">Batas Threshold Okupansi</label>
                            <div class="threshold-slider-container">
                                <!-- Visual labels similar to standard PoliSlot colors -->
                                <div class="d-flex justify-content-between text-center mb-2">
                                    <div style="flex: 1;">
                                        <span class="badge text-white font-weight-bold" style="background-color: #31ce36; border-radius: 6px; font-size: 10px; padding: 4px 8px;">
                                            <i class="fas fa-check-circle mr-1"></i> Banyak
                                        </span>
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

                                <!-- The Double Range Slider Bar -->
                                <div class="position-relative" style="height: 10px; margin: 15px 10px 10px 10px;">
                                    <div class="slider-track" style="position: absolute; top: 0; bottom: 0; left: 0; right: 0; background: #dee2e6; border-radius: 5px;"></div>
                                    <!-- Colored segments representing the 3 regions -->
                                    <div id="segment-banyak" style="position: absolute; top: 0; bottom: 0; left: 0; background-color: #31ce36; border-radius: 5px 0 0 5px;"></div>
                                    <div id="segment-terbatas" style="position: absolute; top: 0; bottom: 0; background-color: #ffad46;"></div>
                                    <div id="segment-penuh" style="position: absolute; top: 0; bottom: 0; right: 0; background-color: #f25961; border-radius: 0 5px 5px 0;"></div>
                                    
                                    <!-- Standard HTML5 input ranges overlayed -->
                                    <input type="range" id="input-threshold-banyak" min="5" max="95" value="{{ $thresholdBanyak }}" class="double-range-slider">
                                    <input type="range" id="input-threshold-terbatas" min="5" max="95" value="{{ $thresholdTerbatas }}" class="double-range-slider">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group p-0 text-dark">
                        <label class="font-weight-bold d-block">Polygon Bounding Box Deteksi (Multi-Zone)</label>
                        <div class="d-flex flex-wrap mb-2">
                            <button type="button" class="btn btn-sm btn-outline-primary m-1" id="btn-draw-mode" onclick="toggleDrawMode()">
                                <i class="fas fa-edit"></i> Mode Menggambar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning m-1" onclick="undoPoint()">
                                <i class="fas fa-undo"></i> Batalkan Titik
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success m-1" onclick="closeCurrentPolygon()">
                                <i class="fas fa-check"></i> Selesai Polygon
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger m-1" onclick="clearAllPolygons()">
                                <i class="fas fa-trash-alt"></i> Hapus Semua
                            </button>
                        </div>
                        <small class="text-muted d-block mb-3">
                            <i class="fas fa-info-circle mr-1"></i> Klik kiri pada gambar untuk menaruh titik-titik (min 3 titik) kemudian klik <strong>Selesai Polygon</strong>. Anda dapat menggambar lebih dari satu zona deteksi.
                        </small>
                    </div>
                    
                    <button class="btn btn-primary btn-block btn-round" onclick="saveDetectionConfig()">
                        <i class="fas fa-save mr-1"></i> Simpan Setelan Deteksi ke Server
                    </button>
                </div>
            </div>
        </div>

        {{-- Panel Kanan: Log Data Real-time --}}
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

        <!-- Panel Snapshot Gallery (Dataset Training) -->
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
                    <!-- Filter & Batch Action Form -->
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

                        <!-- Scrollable Capture Grid -->
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
    // Bug #6: Pending log queue — tangkap log messages sebelum module script ready
    // Module scripts execute deferred, jadi window.addLog belum tersedia saat regular scripts jalan
    window._pendingLogs = [];
    function safeAddLog(message) {
        if (typeof window.addLog === 'function') {
            window.addLog(message);
        } else {
            window._pendingLogs.push(message);
        }
    }

    // Navigasi ke device lain tanpa reload penuh
    function switchDevice(macAddress) {
        const url = new URL(window.location.href);
        url.searchParams.set('mac', macAddress);
        window.location.href = url.toString();
    }

    // Fungsi untuk mengirim manual validation dan meminta snapshot dari device
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
    // --- LOGIKA CANVAS DRAWING OVERLAY ---
    let completedPolygons = @json($detectionPolygon) || [];
    let currentPoints = [];
    let isDrawingMode = false;

    const canvas = document.getElementById('drawing-canvas');
    const ctx = canvas?.getContext('2d');
    const liveImage = document.getElementById('live-image');

    function initCanvas() {
        if (!liveImage || !canvas || liveImage.classList.contains('d-none') || !liveImage.clientWidth) return;
        
        canvas.width = liveImage.clientWidth;
        canvas.height = liveImage.clientHeight;
        
        draw();
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
        canvas.addEventListener('click', (e) => {
            if (!isDrawingMode) return;
            
            const rect = canvas.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            currentPoints.push([x, y]);
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
            canvas.style.pointerEvents = 'auto';
            safeAddLog('Mode menggambar aktif. Meminta snapshot terbaru dari perangkat IoT...');

            // Pemicu otomatis snapshot dari device IoT
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
            canvas.style.pointerEvents = 'none';
            currentPoints = [];
            draw();
        }
    }

    function undoPoint() {
        if (currentPoints.length > 0) {
            currentPoints.pop();
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
        
        // Konversi koordinat canvas ke koordinat natural image
        const scaleX = liveImage.naturalWidth / liveImage.clientWidth;
        const scaleY = liveImage.naturalHeight / liveImage.clientHeight;
        
        const scaledPoints = currentPoints.map(pt => [
            Math.round(pt[0] * scaleX),
            Math.round(pt[1] * scaleY)
        ]);
        
        completedPolygons.push(scaledPoints);
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
        
        const scaleX = canvas.width / liveImage.naturalWidth;
        const scaleY = canvas.height / liveImage.naturalHeight;
        
        // Draw completed polygons
        completedPolygons.forEach((poly, index) => {
            ctx.beginPath();
            poly.forEach((pt, i) => {
                const cx = pt[0] * scaleX;
                const cy = pt[1] * scaleY;
                if (i === 0) ctx.moveTo(cx, cy);
                else ctx.lineTo(cx, cy);
            });
            ctx.closePath();
            ctx.fillStyle = 'rgba(49, 206, 54, 0.2)'; // Green transparent
            ctx.fill();
            ctx.strokeStyle = '#31ce36'; // Green stroke
            ctx.lineWidth = 2;
            ctx.stroke();
            
            // Draw label text
            if (poly.length > 0) {
                ctx.fillStyle = '#1572e8';
                ctx.font = 'bold 12px sans-serif';
                ctx.fillText('Zona ' + (index + 1), poly[0][0] * scaleX, poly[0][1] * scaleY - 5);
            }
        });
        
        // Draw current drawing points
        if (currentPoints.length > 0) {
            ctx.beginPath();
            currentPoints.forEach((pt, i) => {
                if (i === 0) ctx.moveTo(pt[0], pt[1]);
                else ctx.lineTo(pt[0], pt[1]);
            });
            ctx.strokeStyle = '#ffad46'; // Orange stroke
            ctx.lineWidth = 2;
            ctx.stroke();
            
            currentPoints.forEach(pt => {
                ctx.beginPath();
                ctx.arc(pt[0], pt[1], 4, 0, 2 * Math.PI);
                ctx.fillStyle = '#ffad46';
                ctx.fill();
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
    // Hapus titik dua dari MAC address sesuai dengan format channel di Event kita
    const cleanMac = "{{ str_replace(':', '', $targetMac) }}";
    const channelName = `iot.detection.${cleanMac}`;
    const serverInitialStatus = "{{ $initialStatus }}"; // Bug #3: Hydrate dari server

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
                    const countBadge = document.getElementById('current-count-badge');
                    if (countBadge) {
                        countBadge.innerText = e.count;
                    }
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
                    const selectedMac = document.getElementById('device-selector').value;
                    if (e.macAddress.toLowerCase() === selectedMac.toLowerCase()) {
                        updateStatusUI(e.status);
                        addLog(`📡 Status ${e.status.toUpperCase()} diterima via MQTT/WS`);
                    }
                });
        } else {
            setTimeout(initStatusEcho, 500);
        }
    }

    function updateStatusUI(status) {
        const indicator = document.getElementById('status-indicator');
        if (!indicator) return;
        if (status === 'online') {
            indicator.className = "badge badge-success ml-2";
            indicator.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> ONLINE';
        } else {
            indicator.className = "badge badge-danger ml-2";
            indicator.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> OFFLINE';

            // Reset count badge ke 0 saat device offline
            // Ini memastikan UI konsisten dengan backend yang sudah reset current_count = 0
            const countBadge = document.getElementById('current-count-badge');
            if (countBadge) {
                countBadge.innerText = '0';
            }
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

        // Update label teks
        labelBanyakRange.innerText = `0% - ${valBanyak}%`;
        labelTerbatasRange.innerText = `${valBanyak}% - ${valTerbatas}%`;
        labelPenuhRange.innerText = `${valTerbatas}% - 100%`;
    }

    // Bug #5: Expose updateSliderUI ke global scope agar event listener bisa akses
    window.updateSliderUI = updateSliderUI;

    if (sliderBanyak && sliderTerbatas) {
        sliderBanyak.addEventListener('input', updateSliderUI);
        sliderTerbatas.addEventListener('input', updateSliderUI);
        // Jalankan inisialisasi awal
        updateSliderUI();
    }
</script>
@endpush