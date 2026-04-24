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
                    </div>
                    <select class="form-control" id="device-selector" style="max-width: 450px;" onchange="switchDevice(this.value)">
                        @forelse($devices as $device)
                            <option value="{{ $device->device_mac_address }}" 
                                {{ $targetMac === $device->device_mac_address ? 'selected' : '' }}>
                                {{ $device->device_mac_address }}
                                @if($device->subarea)
                                    — {{ $device->subarea->park_subarea_name ?? '' }}
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
                    <span class="badge badge-success" id="connection-status">
                        <i class="fas fa-circle-notch fa-spin mr-1"></i> Menghubungkan...
                    </span>
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

    // Mulai inisiasi
    initEcho();
</script>
@endpush