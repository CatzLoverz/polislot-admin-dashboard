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

@push('scripts')
{{-- Google Maps API --}}
<script src="https://maps.googleapis.com/maps/api/js?key={{$mapsApiKey}}"></script>
<script>
    let map;
    let marker;

    function initMap() {
        // Lokasi Default (Batam)
        let defaultLoc = { lat: 1.118902, lng: 104.048494 }; 
        
        map = new google.maps.Map(document.getElementById("map"), {
            center: defaultLoc,
            zoom: 16,
            mapTypeId: 'satellite',
            streetViewControl: false,
            mapTypeControl: false
        });

        // Set nilai awal input hidden
        updateInput(defaultLoc, 16);

        // Tambahkan Marker Visual di tengah peta (Statis)
        marker = new google.maps.Marker({
            position: defaultLoc,
            map: map,
            title: "Pusat Area",
            // Icon default Google Maps sudah merah
        });

        // Event Listener: Saat peta digeser/zoom
        map.addListener("center_changed", () => {
            let center = map.getCenter();
            // Konversi ke object {lat, lng}
            let pos = { lat: center.lat(), lng: center.lng() };
            marker.setPosition(pos); 
            updateInput(pos, map.getZoom());
        });
        
        map.addListener("zoom_changed", () => {
            let center = map.getCenter();
            let pos = { lat: center.lat(), lng: center.lng() };
            updateInput(pos, map.getZoom());
        });
    }

    function updateInput(latLng, zoom) {
        // Pastikan latLng adalah object atau method
        let lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
        let lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
        
        // Update Hidden Input
        document.getElementById("lat").value = lat.toFixed(6);
        document.getElementById("lng").value = lng.toFixed(6);
        document.getElementById("zoom").value = zoom;

        // Update Display Text
        document.getElementById("display-lat").innerText = lat.toFixed(6);
        document.getElementById("display-lng").innerText = lng.toFixed(6);
        document.getElementById("display-zoom").innerText = zoom;
    }

    // Load Map via window onload agar aman
    window.onload = initMap;
</script>
@endpush