@extends("Layouts.content_layout")

@section('title', 'Tambah Area Parkir')
@section('page_title', 'Tambah Area Parkir')
@section('page_subtitle', 'Tentukan nama, kode, dan lokasi pusat peta area parkir.')

@section('content')
<div class="page-inner mt--5">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold text-dark">Formulir Area Parkir</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.park-area.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            {{-- Kolom Input --}}
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label for="park_area_name" class="placeholder font-weight-bold">Nama Area Parkir <span class="text-danger">*</span></label>
                                    <input id="park_area_name" name="park_area_name" type="text" class="form-control input-border-bottom" required placeholder="Contoh: Gedung Utama">
                                    <small class="form-text text-muted">Nama yang mudah dikenali pengguna.</small>
                                </div>
                                
                                <div class="form-group mt-3">
                                    <label for="park_area_code" class="placeholder font-weight-bold">Kode Area (Unik) <span class="text-danger">*</span></label>
                                    <input id="park_area_code" name="park_area_code" type="text" class="form-control input-border-bottom" required placeholder="Contoh: AREA-A">
                                    <small class="form-text text-muted">Kode unik untuk identifikasi sistem.</small>
                                </div>

                                <div class="alert alert-info mt-4" role="alert">
                                    <i class="fas fa-info-circle mr-2"></i>
                                    <strong>Panduan Peta:</strong>
                                    <ul class="mb-0 pl-3 mt-1 small">
                                        <li>Geser peta di samping ke lokasi parkir.</li>
                                        <li>Marker merah akan selalu berada di tengah peta.</li>
                                        <li>Sesuaikan level Zoom agar area terlihat jelas.</li>
                                    </ul>
                                </div>

                                <div class="form-group mt-4">
                                    <button type="submit" class="btn btn-success btn-round btn-lg btn-block">
                                        <i class="fas fa-save mr-2"></i> Simpan Area Parkir
                                    </button>
                                    <a href="{{ route('admin.park-area.index') }}" class="btn btn-danger btn-link btn-block">Batal</a>
                                </div>
                            </div>

                            {{-- Kolom Peta --}}
                            <div class="col-md-7">
                                <div class="card border">
                                    <div class="card-body p-0">
                                        <div id="map" style="height: 450px; width: 100%; border-radius: 5px; z-index: 1;"></div>
                                    </div>
                                    <div class="card-footer bg-light text-center small text-muted">
                                        Titik Pusat: <span id="display-lat">-</span>, <span id="display-lng">-</span> | Zoom: <span id="display-zoom">-</span>
                                    </div>
                                </div>
                                
                                {{-- Hidden Inputs --}}
                                <input type="hidden" name="center_lat" id="lat">
                                <input type="hidden" name="center_lng" id="lng">
                                <input type="hidden" name="zoom_level" id="zoom">
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
{{-- Leaflet CSS --}}
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
@endpush

@push('scripts')
{{-- Leaflet JS --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script>
    let map;
    let marker;

    function initMap() {
        // Lokasi Default (Batam)
        let defaultLoc = [1.118902, 104.048494]; 
        
        // Inisialisasi Map
        map = L.map('map').setView(defaultLoc, 16);

        // Tambahkan Tile Layer (Esri World Imagery - Satelit)
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community',
            maxZoom: 19
        }).addTo(map);

        // Tambahkan Marker di tengah
        var redIcon = new L.Icon({
            iconUrl: 'https://raw.githubusercontent.com/pointhi/leaflet-color-markers/master/img/marker-icon-2x-red.png',
            shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/0.7.7/images/marker-shadow.png',
            iconSize: [25, 41],
            iconAnchor: [12, 41],
            popupAnchor: [1, -34],
            shadowSize: [41, 41]
        });

        marker = L.marker(defaultLoc, {icon: redIcon}).addTo(map);

        // Update input awal
        updateInput(defaultLoc[0], defaultLoc[1], 16);

        // Event Listener: Saat peta digeser
        map.on('move', function() {
            let center = map.getCenter();
            marker.setLatLng(center); // Marker ikut ke tengah
            updateInput(center.lat, center.lng, map.getZoom());
        });

        // Event Listener: Saat zoom berubah
        map.on('zoomend', function() {
            let center = map.getCenter();
            updateInput(center.lat, center.lng, map.getZoom());
        });
    }

    function updateInput(lat, lng, zoom) {
        // Update Hidden Input
        document.getElementById("lat").value = lat.toFixed(6);
        document.getElementById("lng").value = lng.toFixed(6);
        document.getElementById("zoom").value = zoom;

        // Update Display Text
        document.getElementById("display-lat").innerText = lat.toFixed(6);
        document.getElementById("display-lng").innerText = lng.toFixed(6);
        document.getElementById("display-zoom").innerText = zoom;
    }

    // Load Map
    document.addEventListener("DOMContentLoaded", function() {
        initMap();
    });
</script>
@endpush