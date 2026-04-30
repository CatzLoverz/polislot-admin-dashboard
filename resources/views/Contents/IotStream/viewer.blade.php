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
                        <div id="status-indicator" class="badge badge-secondary ml-2">
                            <i class="fas fa-circle mr-1" style="font-size: 8px;"></i> Memuat status...
                        </div>
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
                    <div>
                        <button class="btn btn-sm btn-primary mr-2" id="btn-request-snapshot" onclick="requestSnapshot()">
                            <i class="fas fa-camera mr-1"></i> Ambil Snapshot
                        </button>
                        <span class="badge badge-success" id="connection-status">
                            <i class="fas fa-circle-notch fa-spin mr-1"></i> Menghubungkan...
                        </span>
                    </div>
                </div>
                <div class="card-body p-0 bg-light d-flex justify-content-center align-items-center" style="min-height: 400px; border-radius: 0 0 15px 15px;">
                    <div id="feed-container" class="text-center p-4">
                        <i class="fas fa-video-slash fa-4x text-muted mb-3"></i>
                        <h5 class="text-muted" id="feed-placeholder">Menunggu data masuk dari MAC Address: <strong>{{ $targetMac }}</strong>...</h5>
                        <img id="live-image" src="" alt="Live Stream" class="img-fluid rounded shadow d-none" style="max-height: 400px;">
                    </div>
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

    // Fungsi untuk meminta snapshot ke server
    function requestSnapshot() {
        const macAddress = "{{ $targetMac }}";
        const btn = document.getElementById('btn-request-snapshot');
        const originalText = btn.innerHTML;
        
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Meminta...';
        btn.disabled = true;

        fetch("{{ route('admin.iot-stream-viewer.trigger') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ mac_address: macAddress })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Tampilkan notifikasi atau log
                if (typeof addLog === 'function') {
                    addLog(`Perintah snapshot dikirim ke ${macAddress}`);
                }
            } else {
                alert("Gagal: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert("Terjadi kesalahan jaringan.");
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
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
                        
                        addLog('Menerima frame gambar.');
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
                });
        } else {
            // Polling sampai file app.js termuat sepenuhnya tanpa harus mem-blok peramban
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

    // Listen untuk Live Chat PoC setelah Echo jalan
    function initChatEcho() {
        if (typeof window.Echo !== 'undefined') {
            // Listen Chat
            window.Echo.channel('livechat.demo')
                .listen('.chat.message', (e) => {
                    addChatMessage(e.username, e.message, e.time);
                });

            // Listen Status Perangkat (Online/Offline)
            window.Echo.channel('iot.status')
                .listen('.device.status', (e) => {
                    const selectedMac = document.getElementById('device-selector').value;
                    if (e.macAddress === selectedMac) {
                        updateStatusUI(e.status);
                    }
                });
        } else {
            setTimeout(initChatEcho, 500);
        }
    }

    function updateStatusUI(status) {
        const indicator = document.getElementById('status-indicator');
        if (status === 'online') {
            indicator.className = "badge badge-success ml-2";
            indicator.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> ONLINE';
        } else {
            indicator.className = "badge badge-danger ml-2";
            indicator.innerHTML = '<i class="fas fa-circle mr-1" style="font-size: 8px;"></i> OFFLINE';
        }
    }
    
    initChatEcho();
</script>
@endpush