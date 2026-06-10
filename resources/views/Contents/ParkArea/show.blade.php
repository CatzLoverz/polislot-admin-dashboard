@extends("Layouts.content_layout")

@section('title', 'Detail Area Parkir')
@section('page_title')
    Area: {{ $area->park_area_name }}
@endsection
@section('page_subtitle')
    Kode: <strong>{{ $area->park_area_code }}</strong> | Atur subarea (blok parkir) menggunakan peta interaktif.
@endsection

@push('styles')
<style>
    .subarea-item {
        background-color: #ffffff;
        border-left: 5px solid #1572e8;
        margin: 10px 15px;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        transition: all 0.25s ease-in-out;
    }
    .subarea-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0,0,0,0.08) !important;
        background-color: #fbfbfb;
    }
    .list-group-item.subarea-item {
        border-top: 1px solid #ebedf2 !important;
        border-right: 1px solid #ebedf2 !important;
        border-bottom: 1px solid #ebedf2 !important;
    }
    .subarea-list-container::-webkit-scrollbar {
        width: 6px;
    }
    .subarea-list-container::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    .subarea-list-container::-webkit-scrollbar-thumb {
        background: #dcdcdc;
        border-radius: 3px;
    }
    .subarea-list-container::-webkit-scrollbar-thumb:hover {
        background: #c0c0c0;
    }
    
    #comments_container::-webkit-scrollbar {
        width: 6px;
    }
    #comments_container::-webkit-scrollbar-track {
        background: #f8f9fa;
    }
    #comments_container::-webkit-scrollbar-thumb {
        background: #dcdcdc;
        border-radius: 3px;
    }
    #comments_container::-webkit-scrollbar-thumb:hover {
        background: #c0c0c0;
    }
    
    @keyframes pulse-highlight {
        0% { background-color: rgba(21, 114, 232, 0.15); }
        100% { background-color: #ffffff; }
    }
    .status-update-highlight {
        animation: pulse-highlight 2s ease-out;
    }
</style>
@endpush

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        {{-- Kolom Peta (Kiri) --}}
        <div class="col-md-9">
            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center text-dark" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-map-marked-alt mr-2"></i> Peta Editor (Satelit)
                    </h4>
                    <div class="d-flex align-items-center">
                        <button id="btn-start-drawing" class="btn btn-primary btn-sm btn-round mr-2" onclick="startDrawing()">
                            <i class="fas fa-plus mr-1"></i> Tambah Sub Area
                        </button>
                        <button id="btn-finish-drawing" class="btn btn-success btn-sm btn-round mr-2 d-none" onclick="finishDrawing()">
                            <i class="fas fa-check mr-1"></i> Selesai
                        </button>
                        <button id="btn-cancel-drawing" class="btn btn-danger btn-sm btn-round d-none" onclick="cancelDrawing()">
                            <i class="fas fa-times mr-1"></i> Batal
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 650px; width: 100%; border-radius: 0 0 15px 15px; z-index: 1;"></div>
                </div>
            </div>
        </div>

        {{-- Kolom Daftar Subarea (Kanan) --}}
        <div class="col-md-3">
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Daftar Sub Area</h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush subarea-list-container" style="max-height: 650px; overflow-y: auto; background-color: #f8f9fa;">
                        @forelse($area->parkSubarea as $sub)
                            <li class="list-group-item subarea-item" id="subarea-item-{{ $sub->park_subarea_id }}" data-id="{{ $sub->park_subarea_id }}" style="border-left: 5px solid {{ $sub->status_color }} !important;">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="width: 65%;">
                                        <span class="font-weight-bold d-block text-dark subarea-name" style="font-size: 1.05rem;">{{ $sub->park_subarea_name }}</span>
                                        <small class="d-block mt-1 subarea-status-wrapper" style="font-weight: 600;">
                                            <span class="subarea-status-text" style="color: {{ $sub->status_color }}">
                                                <i class="fas fa-circle" style="font-size: 8px;"></i> 
                                                @if($sub->status_color == '#f25961') Penuh
                                                @elseif($sub->status_color == '#ffad46') Terbatas
                                                @elseif($sub->status_color == '#31ce36') Banyak Tersedia
                                                @else Tidak ada info / Netral @endif
                                            </span>

                                            <span class="subarea-validation-badges">
                                                @if(isset($sub->is_validated) && $sub->is_validated)
                                                    <span class="badge badge-success p-1 ml-1 text-white" style="font-size: 8px; vertical-align: middle;"><i class="fas fa-check-circle"></i> Tervalidasi</span>
                                                @elseif(isset($sub->has_user_report) && $sub->has_user_report)
                                                    <span class="badge badge-warning p-1 ml-1 text-white" style="font-size: 8px; vertical-align: middle;"><i class="fas fa-exclamation-triangle"></i> Laporan Berbeda</span>
                                                @endif
                                            </span>
                                        </small>
                                        
                                        {{-- Last Validation Report Time & Countdown --}}
                                        <small class="d-block mt-1 text-muted subarea-validation-time font-weight-bold" style="font-size: 10px; {{ (isset($sub->validation_expires_at) && $sub->validation_expires_at) ? '' : 'display: none;' }}">
                                            <i class="fas fa-history mr-1"></i> Laporan: <span class="last-validated-time-val">
                                                @if(isset($sub->last_validation_time) && $sub->last_validation_time)
                                                    {{ date('H:i', strtotime($sub->last_validation_time)) }}
                                                @endif
                                            </span>
                                            <span class="validation-countdown-val text-info ml-1"></span>
                                        </small>

                                        {{-- Kapasitas Slot (Dynamic) --}}
                                        <small class="d-block mt-1 text-muted subarea-occupancy font-weight-bold" style="font-size: 11px; {{ ($sub->iotDevice && $sub->max_slots > 0) ? '' : 'display: none;' }}">
                                            <i class="fas fa-car mr-1"></i> Terisi: <span class="current-count-val">{{ $sub->current_count ?? 0 }}</span>/<span class="max-slots-val">{{ $sub->max_slots ?? 0 }}</span> slot
                                        </small>
                                        
                                        {{-- Amenities --}}
                                        <div class="mt-2 subarea-badges">
                                            @if($sub->iotDevice)
                                                @if($sub->iot_status === 'online')
                                                    <span class="badge badge-success text-white mr-1 mb-1 p-1 iot-status-badge" data-mac="{{ $sub->iotDevice->device_mac_address }}" style="font-size: 9px;" data-toggle="tooltip" title="IoT Online">
                                                        <i class="fas fa-signal"></i> IoT Online
                                                    </span>
                                                @else
                                                    <span class="badge badge-danger text-white mr-1 mb-1 p-1 iot-status-badge" data-mac="{{ $sub->iotDevice->device_mac_address }}" style="font-size: 9px;" data-toggle="tooltip" title="IoT Offline">
                                                        <i class="fas fa-signal-slash"></i> IoT Offline
                                                    </span>
                                                @endif
                                            @endif
                                            @forelse($sub->parkAmenity as $amenity)
                                                <span class="badge badge-count text-secondary border border-secondary mr-1 mb-1 p-1" style="font-size: 9px;">
                                                    {{ $amenity->park_amenity_name }}
                                                </span>
                                            @empty
                                                @if(!$sub->iotDevice)
                                                    <small class="text-muted font-italic" style="font-size: 11px;">Tidak ada fasilitas/IoT.</small>
                                                @endif
                                            @endforelse
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center ml-1">
                                        {{-- Tombol Lihat Komentar --}}
                                        <button type="button" class="btn btn-icon btn-round btn-info btn-xs position-relative mr-1 btn-comment-modal" 
                                            onclick="fetchAndOpenCommentModal({{ $sub->park_subarea_id }}, '{{ $sub->park_subarea_name }}')"
                                            data-toggle="tooltip" title="Lihat Komentar">
                                            <i class="fas fa-comments"></i>
                                            @php $cCount = $sub->subareaComment->count(); @endphp
                                            <span class="badge badge-notification badge-danger position-absolute comment-count-badge" 
                                                  style="top: -8px; right: -8px; font-size: 8px; padding: 2px 4px; border-radius: 50%; {{ $cCount > 0 ? '' : 'display: none;' }}">
                                                {{ $cCount }}
                                            </span>
                                        </button>

                                        {{-- Tombol Edit (Existing) --}}
                                        <button type="button" class="btn btn-icon btn-round btn-primary btn-xs mr-1" 
                                            onclick="openEditModal({{ $sub->park_subarea_id }}, '{{ $sub->park_subarea_name }}', {{ json_encode($sub->parkAmenity) }}, {{ json_encode($sub->iotDevice) }})"
                                            data-toggle="tooltip" title="Edit Subarea">
                                            <i class="fa fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('admin.park-subarea.destroy', $sub->park_subarea_id) }}" 
                                              method="POST" 
                                              class="d-inline delete-subarea-form"
                                              data-name="{{ $sub->park_subarea_name }}">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-icon btn-round btn-danger btn-xs" 
                                                data-toggle="tooltip" title="Hapus Subarea">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="list-group-item text-center text-muted py-4">
                                <i class="fas fa-info-circle fa-2x mb-2"></i><br>
                                Belum ada subarea.<br>Gambar di peta untuk menambahkan.
                            </li>
                        @endforelse
                    </ul>
                </div>
                <div class="card-footer bg-light">
                    <a href="{{ route('admin.park-area.index') }}" class="btn btn-secondary btn-block btn-round">
                        <i class="fas fa-arrow-left mr-2"></i> Kembali
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Simpan Subarea Baru --}}
<div class="modal fade" id="modalSubarea" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-draw-polygon mr-2"></i> Simpan Sub Area Baru
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Nama Sub Area / Blok</label>
                    <input type="text" id="subarea_name" class="form-control" placeholder="Contoh: Blok A (Motor)" required>
                </div>
                <input type="hidden" id="polygon_data">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-round" onclick="cancelDrawing()" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-success btn-round" onclick="saveSubarea()">
                    <i class="fas fa-save mr-2"></i> Simpan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Edit Subarea (Nama & Amenities) --}}
<div class="modal fade" id="modalEditSubarea" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-edit mr-2"></i> Edit Sub Area & Fasilitas
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label class="font-weight-bold">Nama Sub Area</label>
                    <input type="text" id="edit_subarea_name" class="form-control" required>
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label class="font-weight-bold">Device IOT (Kamera / Sensor)</label>
                    <div class="mb-2">
                        <small class="text-muted">Isi MAC Address jika subarea ini dipasang perangkat IoT.</small>
                    </div>
                    <label>MAC Address (Opsional)</label>
                    <input type="text" id="edit_device_mac" class="form-control" placeholder="Contoh: 00:1A:2B:3C:4D:5E">
                </div>
                
                <hr>
                
                <div class="form-group">
                    <label class="font-weight-bold">Kelola Fasilitas (Amenities)</label>
                    <div class="input-group mb-3">
                        <input type="text" id="new_amenity_name" class="form-control" placeholder="Nama fasilitas baru...">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="button" onclick="addAmenityLocal()">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>
                    {{-- Temp List Container --}}
                    <div id="amenities_list_container" style="max-height: 200px; overflow-y: auto;"></div>
                </div>
                <input type="hidden" id="edit_subarea_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger btn-round" data-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-round" onclick="saveSubareaChanges()">
                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                </button>
            </div>
        </div>
    </div>
</div>

{{-- Modal Komentar (FIX BOX + SCROLLABLE) --}}
<div class="modal fade" id="modalComments" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="border-radius: 15px;">
            <div class="modal-header bg-dark">
                <h5 class="modal-title text-white font-weight-bold">
                    <i class="fas fa-comments mr-2"></i> Komentar: <span id="comment_subarea_title"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            {{-- [PERUBAHAN] Height Fix + Overflow Auto --}}
            <div class="modal-body bg-light" id="comments_container" style="height: 500px; overflow-y: auto;"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-round" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Google Maps API Async Loader --}}
<script>
  (function(g){var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "{{ $mapsApiKey }}",
    v: "weekly",
  });
</script>

<script>
    let map;
    let centerData = @json($area->park_area_data);
    let center = { lat: parseFloat(centerData.lat), lng: parseFloat(centerData.lng) };
    let existingSubareas = @json($area->parkSubarea);
    let currentPolygonObj = null;
    let polygonObjects = {}; // Menyimpan referensi polygon untuk update WS
    let subareaStates = {}; // Menyimpan state validasi & fallback subarea
    let activeCommentSubareaId = null; // Menyimpan ID subarea yang komentar modallnya sedang terbuka
    let activeCommentSubareaName = null; // Menyimpan nama subarea yang komentar modallnya sedang terbuka

    // State untuk mode menggambar kustom pengganti Drawing Manager
    let isDrawingMode = false;
    let drawingPoints = [];
    let tempPolyline = null;
    let tempMarkers = [];

    // Definisikan Base URL Storage untuk akses gambar
    const storageBaseUrl = "{{ asset('storage') }}";

    async function initMap() {
        // Load Libraries via importLibrary
        await google.maps.importLibrary("maps");
        await google.maps.importLibrary("marker");
        await google.maps.importLibrary("geometry");

        map = new google.maps.Map(document.getElementById("map"), {
            center: center,
            zoom: parseInt(centerData.zoom),
            mapTypeId: 'satellite',
            mapId: 'DEMO_MAP_ID', // Diperlukan oleh AdvancedMarkerElement
            streetViewControl: false,
            mapTypeControl: false
        });

       existingSubareas.forEach(sub => {
            let polygonColor = sub.status_color || '#1572e8';

            let polygon = new google.maps.Polygon({
                paths: sub.park_subarea_polygon, 
                strokeColor: polygonColor,
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: polygonColor,
                fillOpacity: 0.35, 
                editable: true, 
                draggable: false, 
                map: map
            });
            
            polygonObjects[sub.park_subarea_id] = polygon;

            let infoWindow = new google.maps.InfoWindow({
                content: `<b>${sub.park_subarea_name}</b>`
            });
            polygon.addListener("click", (event) => {
                infoWindow.setPosition(event.latLng);
                infoWindow.open(map);
            });

            const saveChanges = function() {
                let path = polygon.getPath();
                let coords = [];
                for (let i = 0; i < path.getLength(); i++) {
                    let xy = path.getAt(i);
                    coords.push({ lat: xy.lat(), lng: xy.lng() });
                }
                updatePolygonDirectly(sub.park_subarea_id, sub.park_subarea_name, JSON.stringify(coords));
            };

            polygon.getPath().addListener('set_at', saveChanges);
            polygon.getPath().addListener('insert_at', saveChanges);
            polygon.getPath().addListener('remove_at', saveChanges);

            polygon.addListener('contextmenu', function(e) {
                if (typeof e.vertex === 'number') {
                    polygon.getPath().removeAt(e.vertex);
                }
            });
        });

        // Click listener pada peta untuk merekam koordinat saat menggambar subarea baru
        map.addListener("click", (event) => {
            if (!isDrawingMode) return;
            
            const latLng = { lat: event.latLng.lat(), lng: event.latLng.lng() };
            addDrawingPoint(latLng);
        });
    }

    // === KUSTOM DRAWING FUNCTIONS ===
    function startDrawing() {
        if (isDrawingMode) return;
        
        isDrawingMode = true;
        drawingPoints = [];
        tempMarkers = [];

        // Ubah kursor map menjadi crosshair agar menyerupai alat menggambar
        map.setOptions({ draggableCursor: 'crosshair' });

        // Buat polyline sementara untuk menghubungkan titik-titik polygon
        tempPolyline = new google.maps.Polyline({
            strokeColor: "#1572e8",
            strokeOpacity: 0.8,
            strokeWeight: 3,
            map: map
        });

        // Atur tombol visibilitas
        $('#btn-start-drawing').addClass('d-none');
        $('#btn-finish-drawing').removeClass('d-none');
        $('#btn-cancel-drawing').removeClass('d-none');
    }

    function addDrawingPoint(latLng) {
        drawingPoints.push(latLng);
        
        // Update garis polyline penunjuk
        tempPolyline.setPath(drawingPoints);

        // Buat DOM Element kustom dengan styling modern untuk penanda titik (AdvancedMarkerElement)
        const pinElement = document.createElement("div");
        pinElement.style.width = "12px";
        pinElement.style.height = "12px";
        pinElement.style.borderRadius = "50%";
        pinElement.style.backgroundColor = "#1572e8";
        pinElement.style.border = "2px solid #ffffff";
        pinElement.style.boxShadow = "0 2px 4px rgba(0,0,0,0.3)";
        pinElement.style.cursor = "pointer";

        // Tambah marker baru ke peta menggunakan AdvancedMarkerElement
        const marker = new google.maps.marker.AdvancedMarkerElement({
            map: map,
            position: latLng,
            content: pinElement,
            title: tempMarkers.length === 0 ? "Klik di sini untuk menyelesaikan area" : `Titik ${tempMarkers.length + 1}`
        });

        // Klik titik pertama untuk menyelesaikan gambar secara otomatis
        if (tempMarkers.length === 0) {
            marker.addListener("click", () => {
                finishDrawing();
            });
        }

        tempMarkers.push(marker);
    }

    function finishDrawing() {
        if (!isDrawingMode) return;

        if (drawingPoints.length < 3) {
            Swal.fire('Perhatian', 'Sub area minimal harus terdiri dari 3 titik koordinat!', 'warning');
            return;
        }

        // Tulis data koordinat ke input tersembunyi
        document.getElementById('polygon_data').value = JSON.stringify(drawingPoints);

        // Bersihkan grafis gambar sementara dari peta
        cleanupDrawingGraphics();

        // Tampilkan visualisasi polygon hasil gambar sementara
        currentPolygonObj = new google.maps.Polygon({
            paths: drawingPoints,
            fillColor: "#1572e8",
            fillOpacity: 0.5,
            strokeWeight: 2,
            editable: false,
            map: map
        });

        $('#subarea_name').val('');
        $('#modalSubarea').modal('show');
    }

    function cancelDrawing() {
        cleanupDrawingGraphics();
        if (currentPolygonObj) {
            currentPolygonObj.setMap(null);
            currentPolygonObj = null;
        }
    }

    function cleanupDrawingGraphics() {
        isDrawingMode = false;
        
        // Kembalikan kursor default peta
        map.setOptions({ draggableCursor: null });

        if (tempPolyline) {
            tempPolyline.setMap(null);
            tempPolyline = null;
        }

        // Hapus marker dari peta
        tempMarkers.forEach(m => {
            m.map = null;
        });
        tempMarkers = [];
        drawingPoints = [];

        // Kembalikan visibilitas tombol
        $('#btn-start-drawing').removeClass('d-none');
        $('#btn-finish-drawing').addClass('d-none');
        $('#btn-cancel-drawing').addClass('d-none');
    }

    // === 2. LOGIKA SUBAREA ===
    function saveSubarea() {
        let name = $('#subarea_name').val();
        let polygon = $('#polygon_data').val();

        if(!name) {
            Swal.fire('Gagal', 'Nama sub area tidak boleh kosong!', 'warning');
            return;
        }

        $.ajax({
            url: "{{ route('admin.park-area.subarea.store', $area->park_area_id) }}",
            type: "POST",
            data: { _token: "{{ csrf_token() }}", name: name, polygon: polygon },
            success: function(res) {
                $('#modalSubarea').modal('hide');
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: 'Subarea dibuat.',
                    timer: 1500, showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function(xhr) {
                $('#modalSubarea').modal('hide');
                if(currentPolygonObj) currentPolygonObj.setMap(null);
                
                let errorMsg = 'Gagal menyimpan subarea.';
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    errorMsg = '<ul class="text-left mt-2" style="list-style-type: disc; padding-left: 20px;">';
                    for (let key in errors) {
                        errorMsg += `<li>${errors[key][0]}</li>`;
                    }
                    errorMsg += '</ul>';
                }

                Swal.fire({
                    icon: 'error', 
                    title: 'Gagal Menyimpan!', 
                    html: errorMsg
                }).then(() => {
                    // Opsional: Buka kembali modal jika user ingin memperbaiki
                    $('#modalSubarea').modal('show');
                });
            }
        });
    }

    function updatePolygonDirectly(id, name, polygonJson) {
        $.ajax({
            url: "{{ url('admin/park-subarea') }}/" + id,
            type: "POST",
            data: { _token: "{{ csrf_token() }}", _method: "PUT", name: name, polygon: polygonJson },
            success: function(res) {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                Toast.fire({ icon: 'success', title: 'Peta tersimpan otomatis!' });
            }
        });
    }

    // === 3. LOGIKA AMENITIES (BATCH SAVE) ===
    let tempAmenities = [];

    function openEditModal(id, name, amenities = [], iotDevice = null) {
        $('#edit_subarea_id').val(id);
        $('#edit_subarea_name').val(name);
        $('#new_amenity_name').val('');
        
        if(iotDevice) {
            $('#edit_device_mac').val(iotDevice.device_mac_address || '');
        } else {
            $('#edit_device_mac').val('');
        }
        
        // Initialize Temp State
        tempAmenities = amenities.map(item => 
            (typeof item === 'object' && item !== null) ? item.park_amenity_name : item
        );
        
        renderAmenitiesListLocal();
        $('#modalEditSubarea').modal('show');
    }

    function renderAmenitiesListLocal() {
        let html = '';
        if (tempAmenities.length === 0) {
            html = '<div class="text-center text-muted p-3 small">Belum ada fasilitas.<br>Silakan tambah baru.</div>';
        } else {
            html = '<ul class="list-group list-group-flush border rounded">';
            tempAmenities.forEach((name, index) => {
                html += `<li class="list-group-item d-flex justify-content-between align-items-center p-2">
                        <span><i class="fas fa-check text-success mr-2"></i>${name}</span>
                        <button class="btn btn-xs btn-danger btn-round" onclick="removeAmenityLocal(${index})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </li>`;
            });
            html += '</ul>';
        }
        $('#amenities_list_container').html(html);
    }

    function addAmenityLocal() {
        let name = $('#new_amenity_name').val().trim();
        if(!name) { Swal.fire('Gagal', 'Nama fasilitas kosong.', 'warning'); return; }
        
        if(tempAmenities.includes(name)) {
             Swal.fire('Info', 'Fasilitas sudah ada.', 'info');
             return;
        }

        tempAmenities.push(name);
        $('#new_amenity_name').val('');
        renderAmenitiesListLocal();
    }

    function removeAmenityLocal(index) {
        tempAmenities.splice(index, 1);
        renderAmenitiesListLocal();
    }

    function saveSubareaChanges() {
        let id = $('#edit_subarea_id').val();
        let name = $('#edit_subarea_name').val();
        let deviceMac = $('#edit_device_mac').val();
        
        if(!name) { Swal.fire('Gagal', 'Nama subarea kosong.', 'warning'); return; }

        $.ajax({
            url: "{{ url('admin/park-subarea') }}/" + id,
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                _method: "PUT", 
                name: name,
                amenities: tempAmenities,
                device_mac_address: deviceMac
            },
            success: function(res) {
                $('#modalEditSubarea').modal('hide');
                Swal.fire({
                    icon: 'success', title: 'Berhasil', text: 'Perubahan disimpan.',
                    timer: 1000, showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function(xhr) { 
                let errorMsg = 'Gagal menyimpan perubahan.';
                
                // Cek apakah error dari validasi Laravel (Status 422)
                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    let errors = xhr.responseJSON.errors;
                    errorMsg = '<ul class="text-left mt-2" style="list-style-type: disc; padding-left: 20px;">';
                    for (let key in errors) {
                        errorMsg += `<li>${errors[key][0]}</li>`; // Menampilkan pesan error pertama dari tiap input
                    }
                    errorMsg += '</ul>';
                }

                Swal.fire({
                    icon: 'error', 
                    title: 'Validasi Gagal!', 
                    html: errorMsg // Menggunakan 'html' bukan 'text' agar tag <ul> terbaca
                }); 
            }
        });
    }

    // [BARU] Fetch komentar dinamis & buka modal
    function fetchAndOpenCommentModal(subId, name) {
        activeCommentSubareaId = subId;
        activeCommentSubareaName = name;
        
        // Tampilkan loading spinner di container
        $('#comment_subarea_title').text(name);
        $('#comments_container').html('<div class="text-center py-5"><i class="fas fa-spinner fa-spin fa-3x text-primary mb-3"></i><p class="text-muted">Memuat komentar terbaru...</p></div>');
        $('#modalComments').modal('show');
        
        $.getJSON(`/admin/park-subarea/${subId}/comments`, function(response) {
            if (response.status === 'success') {
                if (activeCommentSubareaId === subId) {
                    openCommentModal(name, response.comments);
                }
            } else {
                $('#comments_container').html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Gagal memuat komentar.</p></div>');
            }
        }).fail(function() {
            $('#comments_container').html('<div class="text-center py-5 text-danger"><i class="fas fa-exclamation-circle fa-3x mb-3"></i><p>Gagal memuat komentar.</p></div>');
        });
    }

    // [BARU] Refresh komentar secara silent (untuk update real-time via WebSockets)
    function refreshCommentsSilently(subId, name) {
        $.getJSON(`/admin/park-subarea/${subId}/comments`, function(response) {
            if (response.status === 'success' && activeCommentSubareaId === subId) {
                openCommentModal(name, response.comments);
            }
        });
    }

    // [PERBAIKAN] Logika Gambar Komentar & Avatar
    function openCommentModal(name, comments) {
        $('#comment_subarea_title').text(name);
        
        let html = '';
        if (!Array.isArray(comments) || comments.length === 0) {
            html = `<div class="text-center py-5"><i class="fas fa-comment-slash fa-3x text-muted mb-3"></i><p class="text-muted">Belum ada komentar untuk area ini.</p></div>`;
        } else {
            // Sort by ID descending (newest first)
            comments.sort((a, b) => {
                const idA = a.subarea_comment_id || a.id;
                const idB = b.subarea_comment_id || b.id;
                return idB - idA;
            });
            
            html = '<div class="list-group list-group-flush">';
            comments.forEach(c => {
                let userName = c.user ? c.user.name : 'User Terhapus';
                
                // [FIX] Cek Avatar path
                let rawAvatar = c.user && c.user.avatar ? c.user.avatar : null;
                let userAvatar = rawAvatar 
                    ? (rawAvatar.startsWith('http') ? rawAvatar : storageBaseUrl + '/' + rawAvatar) 
                    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userName);
                
                // Parse format tanggal
                let dateStr = c.created_at ? new Date(c.created_at).toLocaleString('id-ID') : '';
                
                // [FIX] Cek Bukti Gambar Komentar
                let commentImageHtml = '';
                if (c.subarea_comment_image) {
                    let rawImage = c.subarea_comment_image;
                    let imageUrl = rawImage.startsWith('http') ? rawImage : storageBaseUrl + '/' + rawImage;
                    commentImageHtml = `<div class="mt-2"><img src="${imageUrl}" class="img-fluid rounded border" style="max-height: 200px;" alt="Bukti Foto"></div>`;
                }

                html += `
                    <div class="list-group-item flex-column align-items-start p-3 mb-2 border rounded shadow-sm bg-white">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center mb-2">
                                <div class="avatar avatar-sm mr-2">
                                    <img src="${userAvatar}" alt="..." class="avatar-img rounded-circle border">
                                </div>
                                <h6 class="mb-1 font-weight-bold text-primary">${userName}</h6>
                            </div>
                            <small class="text-muted">${dateStr}</small>
                        </div>
                        <p class="mb-1 text-dark">${c.subarea_comment_content}</p>
                        ${commentImageHtml}
                    </div>
                `;
            });
            html += '</div>';
        }
        $('#comments_container').html(html);
    }


    // Helper function to update Subarea UI (Polygon and sidebar item) from state
    function updateSubareaUI(subId) {
        const state = subareaStates[subId];
        if (!state) return;

        const color = state.color || '#1572e8';
        const status = state.status || 'netral';
        const isValidated = state.isValidated || false;
        const hasUserReport = state.hasUserReport || false;
        const currentCount = state.currentCount ?? 0;
        const maxSlots = state.maxSlots ?? 0;
        const commentCount = state.commentCount ?? 0;

        // A. Update Google Maps Polygon
        if (polygonObjects[subId]) {
            polygonObjects[subId].setOptions({
                strokeColor: color,
                fillColor: color
            });
        }
        
        // B. Update List Item di Kanan
        const item = document.getElementById(`subarea-item-${subId}`);
        if (item) {
            // Highlight animation
            item.classList.remove('status-update-highlight');
            void item.offsetWidth; // Trigger reflow
            item.classList.add('status-update-highlight');
            
            // Update border color
            item.style.setProperty('border-left', `5px solid ${color}`, 'important');
            
            // Update status text
            let statusText = 'Tidak ada info / Netral';
            if (status === 'penuh') statusText = 'Penuh';
            else if (status === 'terbatas') statusText = 'Terbatas';
            else if (status === 'banyak') statusText = 'Banyak Tersedia';
            
            const statusSpan = item.querySelector('.subarea-status-text');
            if (statusSpan) {
                statusSpan.style.color = color;
                statusSpan.innerHTML = `<i class="fas fa-circle mr-1" style="font-size: 8px; vertical-align: middle;"></i> ${statusText}`;
            }
            
            // Update validation badges
            const badgesSpan = item.querySelector('.subarea-validation-badges');
            if (badgesSpan) {
                let badgeHtml = '';
                if (isValidated) {
                    badgeHtml = `<span class="badge badge-success p-1 ml-1 text-white" style="font-size: 8px; vertical-align: middle;"><i class="fas fa-check-circle"></i> Tervalidasi</span>`;
                } else if (hasUserReport) {
                    badgeHtml = `<span class="badge badge-warning p-1 ml-1 text-white" style="font-size: 8px; vertical-align: middle;"><i class="fas fa-exclamation-triangle"></i> Laporan Berbeda</span>`;
                }
                badgesSpan.innerHTML = badgeHtml;
            }
            
            // Update validation time & countdown visibility
            const timeContainer = item.querySelector('.subarea-validation-time');
            if (timeContainer) {
                if (state.validationExpiresAt) {
                    timeContainer.style.setProperty('display', 'block', 'important');
                    if (state.lastValidationTime) {
                        const dateObj = new Date(state.lastValidationTime);
                        const hours = String(dateObj.getHours()).padStart(2, '0');
                        const minutes = String(dateObj.getMinutes()).padStart(2, '0');
                        
                        const timeSpan = timeContainer.querySelector('.last-validated-time-val');
                        if (timeSpan) timeSpan.innerText = `${hours}:${minutes}`;
                    }
                } else {
                    timeContainer.style.setProperty('display', 'none', 'important');
                    const timeSpan = timeContainer.querySelector('.last-validated-time-val');
                    if (timeSpan) timeSpan.innerText = '';
                    const countdownSpan = timeContainer.querySelector('.validation-countdown-val');
                    if (countdownSpan) countdownSpan.innerText = '';
                }
            }
            
            // Update slot count dynamically
            const occupancySpan = item.querySelector('.subarea-occupancy');
            if (occupancySpan) {
                if (maxSlots > 0) {
                    occupancySpan.style.display = 'block';
                    const countVal = occupancySpan.querySelector('.current-count-val');
                    if (countVal) countVal.innerText = currentCount;
                    const maxVal = occupancySpan.querySelector('.max-slots-val');
                    if (maxVal) maxVal.innerText = maxSlots;
                } else {
                    occupancySpan.style.display = 'none';
                }
            }

            // Update comment count badge dynamically
            const commentBadge = item.querySelector('.comment-count-badge');
            if (commentBadge) {
                commentBadge.innerText = commentCount;
                if (commentCount > 0) {
                    commentBadge.style.display = 'inline-block';
                } else {
                    commentBadge.style.display = 'none';
                }
            }

            // Real-time refresh of comments modal if open for this subarea
            if (activeCommentSubareaId && activeCommentSubareaId == subId) {
                refreshCommentsSilently(subId, activeCommentSubareaName);
            }
        }
    }

    // Check validation expiration client-side
    function checkValidationExpirations() {
        Object.keys(subareaStates).forEach(subId => {
            const state = subareaStates[subId];
            if (state.validationRemainingSeconds > 0) {
                state.validationRemainingSeconds--;
                
                if (state.validationRemainingSeconds <= 0) {
                    console.log(`⏰ Validation expired for subarea ${subId}. Reverting to fallback: ${state.fallbackStatus}`);
                    
                    // Revert local state
                    state.status = state.fallbackStatus;
                    state.color = state.fallbackColor;
                    state.isValidated = false;
                    state.hasUserReport = false;
                    state.validationExpiresAt = null;
                    state.lastValidationTime = null;
                    state.validationRemainingSeconds = 0;
                    
                    // Update UI
                    updateSubareaUI(subId);
                } else {
                    // Update countdown text
                    const item = document.getElementById(`subarea-item-${subId}`);
                    if (item) {
                        const countdownSpan = item.querySelector('.validation-countdown-val');
                        if (countdownSpan) {
                            const diffSec = state.validationRemainingSeconds;
                            const minutes = Math.floor(diffSec / 60);
                            const seconds = diffSec % 60;
                            countdownSpan.innerText = `(Sisa: ${minutes}m ${seconds}s)`;
                        }
                    }
                }
            } else if (state.validationExpiresAt) {
                // Fallback for cases where remaining seconds is 0/falsy but expiresAt is present (e.g. initial load logic correction)
                state.status = state.fallbackStatus;
                state.color = state.fallbackColor;
                state.isValidated = false;
                state.hasUserReport = false;
                state.validationExpiresAt = null;
                state.lastValidationTime = null;
                state.validationRemainingSeconds = 0;
                updateSubareaUI(subId);
            }
        });
    }

    // Inisialisasi Echo listener untuk update real-time
    function initEcho() {
        if (typeof window.Echo !== 'undefined') {
            const areaId = "{{ $area->park_area_id }}";
            
            // 1. Dengar event pembaruan subarea di area parkir ini
            window.Echo.channel(`park-area.${areaId}`)
                .listen('.subarea.updated', (e) => {
                    console.log("📡 Subarea Updated Event Received:", e);
                    
                    const subId = e.parkSubareaId;
                    
                    // Update local state dictionary
                    if (subareaStates[subId]) {
                        subareaStates[subId].status = e.status;
                        subareaStates[subId].color = e.statusColor;
                        subareaStates[subId].isValidated = e.isValidated;
                        subareaStates[subId].hasUserReport = e.hasUserReport;
                        subareaStates[subId].validationExpiresAt = e.validationExpiresAt;
                        subareaStates[subId].lastValidationTime = e.lastValidationTime;
                        subareaStates[subId].validationRemainingSeconds = e.validationRemainingSeconds || 0;
                        subareaStates[subId].fallbackStatus = e.fallbackStatus;
                        subareaStates[subId].fallbackColor = e.fallbackStatusColor;
                        subareaStates[subId].currentCount = e.currentCount;
                        subareaStates[subId].maxSlots = e.maxSlots;
                        subareaStates[subId].commentCount = e.commentCount;
                    }
                    
                    // Trigger UI update
                    updateSubareaUI(subId);
                });
                
            // 2. Dengar status perangkat IoT (online/offline)
            window.Echo.channel('iot.status')
                .listen('.device.status', (e) => {
                    console.log("📡 Device Status Received (MQTT/WS):", e);
                    
                    const mac = e.macAddress;
                    const status = e.status;
                    
                    // Cari semua badge status IoT dengan MAC address ini
                    const badges = document.querySelectorAll(`.iot-status-badge[data-mac="${mac}"]`);
                    badges.forEach(badge => {
                        if (status === 'online') {
                            badge.className = "badge badge-success text-white mr-1 mb-1 p-1 iot-status-badge";
                            badge.innerHTML = '<i class="fas fa-signal"></i> IoT Online';
                            badge.setAttribute('title', 'IoT Online');
                            badge.setAttribute('data-original-title', 'IoT Online');
                        } else {
                            badge.className = "badge badge-danger text-white mr-1 mb-1 p-1 iot-status-badge";
                            badge.innerHTML = '<i class="fas fa-signal-slash"></i> IoT Offline';
                            badge.setAttribute('title', 'IoT Offline');
                            badge.setAttribute('data-original-title', 'IoT Offline');
                        }
                    });
                });
        } else {
            setTimeout(initEcho, 500);
        }
    }

    // Event Listener untuk Konfirmasi Hapus Subarea
    document.addEventListener("DOMContentLoaded", function() {
        initMap();
        $(function () { $('[data-toggle="tooltip"]').tooltip() });
        
        // Populate local subareaStates from the server-injected existingSubareas
        existingSubareas.forEach(sub => {
            let commentCount = sub.subarea_comment ? sub.subarea_comment.length : 0;
            subareaStates[sub.park_subarea_id] = {
                status: sub.status_color ? sub.status : 'netral',
                color: sub.status_color || '#1572e8',
                isValidated: sub.is_validated ? true : false,
                hasUserReport: sub.has_user_report ? true : false,
                validationExpiresAt: sub.validation_expires_at || null,
                lastValidationTime: sub.last_validation_time || null,
                validationRemainingSeconds: sub.validation_remaining_seconds ?? 0,
                fallbackStatus: sub.fallback_status || 'netral',
                fallbackColor: sub.fallback_status_color || '#1572e8',
                currentCount: sub.current_count ?? 0,
                maxSlots: sub.max_slots ?? 0,
                commentCount: commentCount
            };
        });
        
        // Inisialisasi Echo listener
        initEcho();

        // Run expiration check immediately to sync UI on page load
        checkValidationExpirations();

        // Start expiration checking timer (check every 1 second for smooth countdowns)
        setInterval(checkValidationExpirations, 1000);

        // Reset active comments state on modal close
        $('#modalComments').on('hidden.bs.modal', function () {
            activeCommentSubareaId = null;
            activeCommentSubareaName = null;
        });

        // Handler tombol hapus subarea
        $(document).on('submit', '.delete-subarea-form', function(e) {
            e.preventDefault();
            let form = this;
            let name = $(this).data('name');

            Swal.fire({
                title: 'Hapus Subarea?',
                text: "Subarea '" + name + "' akan dihapus permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
</script>
@endpush