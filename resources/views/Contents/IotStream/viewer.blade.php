@extends("Layouts.content_layout")

@section('title', 'IoT Stream Viewer')
@section('page_title', 'Live IoT Stream Viewer')
@section('page_subtitle', 'Monitoring real-time data/video dari perangkat IoT yang terdaftar.')

@section('content')
<div class="page-inner mt--5">
    {{-- Pemilihan Device --}}
    <div class="row mb-3">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body py-3 d-flex align-items-center">
                    <i class="fas fa-microchip fa-lg text-primary mr-3"></i>
                    <div class="mr-3">
                        <label class="mb-0 font-weight-bold" for="device-selector">Pilih Perangkat IoT:</label>
                        <div id="status-indicator" class="badge badge-{{ $initialStatus === 'online' ? 'success' : 'danger' }} ml-2">
                            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> {{ strtoupper($initialStatus) }}
                        </div>
                        <span class="badge badge-primary ml-2">
                            <i class="fas fa-car mr-1"></i> Count: <strong id="current-count-badge">0</strong>
                        </span>
                    </div>
                    <select class="form-control" id="device-selector" style="max-width: 450px;" onchange="switchDevice(this.value)">
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
                    <span class="badge badge-info ml-3">
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
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-satellite-dish mr-2"></i> Live Feed
                    </h4>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-success mr-1" id="btn-val-banyak" onclick="validateStream('banyak')">
                            <i class="fas fa-check-circle mr-1"></i> Banyak
                        </button>
                        <button class="btn btn-sm btn-warning text-white mr-1" id="btn-val-terbatas" onclick="validateStream('terbatas')">
                            <i class="fas fa-exclamation-circle mr-1"></i> Terbatas
                        </button>
                        <button class="btn btn-sm btn-danger mr-2" id="btn-val-penuh" onclick="validateStream('penuh')">
                            <i class="fas fa-times-circle mr-1"></i> Penuh
                        </button>
                        <span class="badge badge-success" id="connection-status">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Menghubungkan...
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 bg-light d-flex justify-content-center align-items-center" style="min-height: 400px; border-radius: 0 0 15px 15px;">
                    <div id="feed-container" class="text-center p-4 w-100 h-100 position-relative d-flex justify-content-center align-items-center">
                        <div id="placeholder-container">
                            <i class="fas fa-video-slash fa-4x text-muted mb-3"></i>
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
                <div class="card-header bg-dark text-white" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-cogs mr-2"></i> Pengaturan Deteksi & Threshold Subarea
                    </h4>
                </div>
                <div class="card-body bg-white" style="border-radius: 0 0 15px 15px;">
                    <div class="row text-dark">
                        <div class="col-md-4">
                            <div class="form-group p-0">
                                <label class="font-weight-bold" for="max-slots">Kapasitas Maksimal (Max Slot)</label>
                                <input type="number" id="max-slots" class="form-control" value="{{ $maxSlots }}" min="1">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group p-0">
                                <label class="font-weight-bold" for="threshold-banyak">Batas Status "Banyak" (%)</label>
                                <input type="number" id="threshold-banyak" class="form-control" value="{{ $thresholdBanyak }}" min="5" max="90">
                                <small class="text-muted">Okupansi di bawah ini dianggap "Banyak Kosong"</small>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group p-0">
                                <label class="font-weight-bold" for="threshold-terbatas">Batas Status "Penuh" (%)</label>
                                <input type="number" id="threshold-terbatas" class="form-control" value="{{ $thresholdTerbatas }}" min="10" max="95">
                                <small class="text-muted">Okupansi di atas ini dianggap "Penuh"</small>
                            </div>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="form-group p-0 text-dark">
                        <label class="font-weight-bold d-block">Polygon Bounding Box Deteksi (Multi-Zone)</label>
                        <div class="btn-group mb-2" role="group">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btn-draw-mode" onclick="toggleDrawMode()">
                                <i class="fas fa-edit"></i> Mode Menggambar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-warning" onclick="undoPoint()">
                                <i class="fas fa-undo"></i> Batalkan Titik
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-success" onclick="closeCurrentPolygon()">
                                <i class="fas fa-check"></i> Selesai Polygon
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllPolygons()">
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
                <div class="card-header bg-primary text-white" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0"><i class="fas fa-terminal mr-2"></i> Data Log</h4>
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

        <!-- Kolom Chat PoC -->
        <div class="col-md-4 mt-4">
            <div class="card shadow-sm" style="border-radius: 15px; border: none;">
                <div class="card-header bg-info text-white" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0"><i class="fas fa-comments mr-2"></i> Live Chat (PoC)</h4>
                </div>
                <div class="card-body p-2">
                    <div id="chat-messages" style="height: 300px; overflow-y: auto; background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 4px; padding: 10px; margin-bottom: 10px;">
                        <div class="text-muted text-center small mt-2">Selamat datang di Live Chat.</div>
                    </div>
                    <form id="chat-form" onsubmit="sendChatMessage(event)">
                        <div class="input-group">
                            <input type="text" id="chat-input" class="form-control" placeholder="Ketik pesan..." required>
                            <div class="input-group-append">
                                <button type="submit" class="btn btn-info" id="chat-btn">Kirim</button>
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

        if (typeof addLog === 'function') {
            addLog(`Memicu validasi manual [${content.toUpperCase()}] untuk ${macAddress}...`);
        }

        fetch("{{ route('admin.iot-stream-viewer.validate') }}", {
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
                if (typeof addLog === 'function') {
                    addLog(data.message);
                }
            } else {
                alert("Gagal memicu validasi: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan jaringan.");
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
            if (typeof addLog === 'function') {
                addLog('Mode menggambar aktif. Meminta snapshot terbaru dari perangkat IoT...');
            }

            // Pemicu otomatis snapshot dari device IoT
            fetch("{{ route('admin.iot-stream-viewer.trigger') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ mac_address: "{{ $targetMac }}" })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (typeof addLog === 'function') {
                        addLog('Berhasil memicu pengambilan snapshot. Menunggu respons gambar...');
                    }
                } else {
                    alert("Gagal memicu snapshot: " + data.message);
                }
            })
            .catch(err => {
                console.error("Error triggering snapshot:", err);
                if (typeof addLog === 'function') {
                    addLog('Error memicu snapshot: Terjadi masalah jaringan.');
                }
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
            alert('Minimal harus ada 3 titik untuk membuat polygon.');
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
        if (typeof addLog === 'function') {
            addLog('Zona polygon baru selesai dibuat.');
        }
    }

    function clearAllPolygons() {
        if (confirm('Hapus semua polygon deteksi?')) {
            completedPolygons = [];
            currentPoints = [];
            draw();
            if (typeof addLog === 'function') {
                addLog('Semua polygon dihapus dari layar.');
            }
        }
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
        const thresholdBanyak = document.getElementById('threshold-banyak').value;
        const thresholdTerbatas = document.getElementById('threshold-terbatas').value;

        if (!maxSlots || maxSlots <= 0) {
            alert('Kapasitas maksimal harus lebih besar dari 0!');
            return;
        }

        if (typeof addLog === 'function') {
            addLog('Menyimpan konfigurasi deteksi...');
        }

        fetch("{{ route('admin.iot-stream-viewer.save-settings') }}", {
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
                alert('Konfigurasi deteksi berhasil disimpan dan disinkronkan ke perangkat!');
                if (typeof addLog === 'function') {
                    addLog('Konfigurasi deteksi berhasil disimpan ke database.');
                }
            } else {
                alert('Gagal menyimpan: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan jaringan saat menyimpan konfigurasi.');
        });
    }
</script>

<script type="module">
    // Hapus titik dua dari MAC address sesuai dengan format channel di Event kita
    const cleanMac = "{{ str_replace(':', '', $targetMac) }}";
    const channelName = `iot.stream.${cleanMac}`;

    const connectionStatus = document.getElementById('connection-status');
    const logList = document.getElementById('log-list');
    const emptyLogMsg = document.getElementById('empty-log-msg');
    const liveImage = document.getElementById('live-image');
    const feedPlaceholder = document.getElementById('feed-placeholder');
    const feedContainerIcon = document.querySelector('#feed-container i');

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

    // Menggunakan window.Echo yang telah di-bundle secara internal oleh Vite (app.js/echo.js)
    function initEcho() {
        if (typeof window.Echo !== 'undefined') {
            connectionStatus.className = "badge badge-success";
            connectionStatus.innerHTML = '<i class="fas fa-check-circle mr-1"></i> Terhubung ke Reverb';
            addLog(`Tersambung ke server. Mendengarkan channel: <strong>${channelName}</strong>`);
            
            window.Echo.channel(channelName)
                .listen('.stream.received', (e) => {
                    let frameData = e.frameData;
                    
                    if(frameData.startsWith('data:image')) {
                        liveImage.src = frameData;
                        liveImage.classList.remove('d-none');
                        if (feedPlaceholder) feedPlaceholder.style.display = 'none';
                        if (feedContainerIcon) feedContainerIcon.style.display = 'none';
                        
                        addLog('Menerima frame gambar snapshot.');
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
                });
        } else {
            console.warn("Menunggu modul mandiri (Echo) dari Vite dimuat...");
            setTimeout(initEcho, 500);
        }
    }

    // Fungsi menambah pesan ke chat box
    function addChatMessage(username, message, time) {
        const chatBox = document.getElementById('chat-messages');
        const isSelf = username === "{{ auth()->user()->name ?? 'Admin' }}";
        
        const html = `
            <div class="mb-2 ${isSelf ? 'text-right' : ''}">
                <small class="text-muted" style="font-size: 0.7rem;">${username} • ${time}</small>
                <div class="d-inline-block px-3 py-2 rounded ${isSelf ? 'bg-info text-white' : 'bg-light border'}" style="max-width: 85%; text-align: left; display: inline-block;">
                    ${message}
                </div>
            </div>
        `;
        chatBox.insertAdjacentHTML('beforeend', html);
        chatBox.scrollTop = chatBox.scrollHeight;
    }

    // Kirim pesan chat via AJAX
    window.sendChatMessage = function(e) {
        e.preventDefault();
        const input = document.getElementById('chat-input');
        const btn = document.getElementById('chat-btn');
        const message = input.value.trim();
        
        if (!message) return;
        
        input.disabled = true;
        btn.disabled = true;
 
        fetch("{{ route('admin.iot-stream-viewer.chat') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                mac_address: "{{ $targetMac }}",
                username: "{{ auth()->user()->name ?? 'Admin' }}",
                message: message
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                input.value = '';
            } else {
                addLog('Gagal mengirim pesan chat.', 'danger');
            }
        })
        .finally(() => {
            input.disabled = false;
            btn.disabled = false;
            input.focus();
        });
    }

    // Mulai inisiasi
    initEcho();

    // Listen untuk Live Chat PoC + Presence Channel (instant status) setelah Echo jalan
    function initChatEcho() {
        if (typeof window.Echo !== 'undefined') {
            // Listen Chat
            window.Echo.channel('livechat.demo')
                .listen('.chat.message', (e) => {
                    addChatMessage(e.username, e.message, e.time);
                });

            // =====================================================
            // PRESENCE CHANNEL — Instant Online/Offline Detection
            // =====================================================
            const presenceChannelName = `iot.device.${cleanMac}`;
            window.Echo.join(presenceChannelName)
                .here((members) => {
                    const isDeviceOnline = members.some(m => m.type === 'iot_device');
                    if (isDeviceOnline) {
                        updateStatusUI('online');
                        addLog(`✅ Perangkat IoT terdeteksi ONLINE (via Presence Channel)`);
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
                })
                .listenForWhisper('chat-reply', (e) => {
                    console.log("💬 Chat received via WS:", e);
                    addChatMessage(e.username, e.message, e.time);
                });

            // =====================================================
            // FALLBACK: Listen Status dari MQTT
            // =====================================================
            window.Echo.channel('iot.status')
                .listen('.device.status', (e) => {
                    console.log("📡 Status Received (MQTT):", e);
                    const selectedMac = document.getElementById('device-selector').value;
                    if (e.macAddress.toLowerCase() === selectedMac.toLowerCase()) {
                        updateStatusUI(e.status);
                        addLog(`📡 Status ${e.status.toUpperCase()} diterima via MQTT`);
                    }
                });
        } else {
            setTimeout(initChatEcho, 500);
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
        }
    }
    
    initChatEcho();
    window.addLog = addLog; // Expose globally for other scripts
</script>
@endpush