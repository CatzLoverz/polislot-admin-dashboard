@extends("Layouts.content_layout")

@section('title', 'Detail Area Parkir')
@section('page_title')
    Area: {{ $area->park_area_name }}
@endsection
@section('page_subtitle')
    Kode: <strong>{{ $area->park_area_code }}</strong> | Atur subarea (blok parkir) menggunakan peta interaktif.
@endsection

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        {{-- Kolom Peta (Kiri) --}}
        <div class="col-md-9">
            <div class="card shadow-sm" style="border-radius: 15px;">
                <div class="card-header d-flex justify-content-between align-items-center text-dark" style="border-radius: 15px 15px 0 0;">
                    <h4 class="card-title font-weight-bold mb-0">
                        <i class="fas fa-map-marked-alt mr-2"></i> Peta Editor (Satelit)
                    </h4>
                    <small class="text-dark op-8">
                        <i class="fas fa-info-circle mr-1"></i> Klik Kanan titik untuk hapus &bull; Geser untuk edit
                    </small>
                </div>
                <div class="card-body p-0">
                    <div id="map" style="height: 650px; width: 100%; border-radius: 0 0 15px 15px; z-index: 1;"></div>
                </div>
            </div>
        </div>

        {{-- Kolom Daftar Subarea (Kanan) --}}
        <div class="col-md-3">
            <div class="card shadow-sm" style="border-radius: 15px; height: 100%;">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Daftar Sub Area</h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($area->parkSubarea as $sub)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    {{-- Info Nama & Amenities --}}
                                    <div style="width: 75%;">
                                        <span class="font-weight-bold d-block text-dark" style="font-size: 1rem;">
                                            {{ $sub->park_subarea_name }}
                                        </span>
                                        
                                        {{-- Loop Amenities Di Sini (Dibawah Nama) --}}
                                        <div class="mt-2">
                                            @forelse($sub->parkAmenity as $amenity)
                                                <span class="badge badge-count text-secondary border border-secondary mr-1 mb-1 p-1" style="font-size: 10px; background: #f8f9fa;">
                                                    <i class="fas fa-check text-success mr-1"></i>{{ $amenity->park_amenity_name }}
                                                </span>
                                            @empty
                                                <small class="text-muted font-italic" style="font-size: 11px;">Tidak ada fasilitas.</small>
                                            @endforelse
                                        </div>
                                    </div>
                                    
                                    {{-- Action Buttons --}}
                                    <div class="d-flex align-items-center ml-2">
                                        {{-- Tombol Edit --}}
                                        <button type="button" class="btn btn-icon btn-round btn-primary btn-xs mr-1" 
                                            onclick="openEditModal({{ $sub->park_subarea_id }}, '{{ $sub->park_subarea_name }}', {{ json_encode($sub->parkAmenity) }})"
                                            data-toggle="tooltip" title="Ubah & Fasilitas">
                                            <i class="fa fa-edit"></i>
                                        </button>

                                        {{-- Tombol Hapus --}}
                                        <form action="{{ route('admin.park-subarea.destroy', $sub->park_subarea_id) }}" 
                                              method="POST" 
                                              class="delete-form d-inline"
                                              data-entity-name="Subarea: {{ $sub->park_subarea_name }}">
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
                {{-- Form Ganti Nama Subarea --}}
                <div class="form-group">
                    <label class="font-weight-bold">Nama Sub Area</label>
                    <div class="input-group mb-3">
                        <input type="text" id="edit_subarea_name" class="form-control" required>
                        <div class="input-group-append">
                            <button type="button" class="btn-primary " onclick="updateSubareaName()">
                                <i class="fas fa-save mr-1"></i> Simpan
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">Klik "Simpan Nama" untuk memperbarui nama area ini.</small>
                </div>
                
                <hr>
                
                {{-- Form Amenities (Langsung CRUD) --}}
                <div class="form-group">
                    <label class="font-weight-bold">Kelola Fasilitas (Amenities)</label>
                    
                    {{-- Input Tambah Baru --}}
                    <div class="input-group mb-3">
                        <input type="text" id="new_amenity_name" class="form-control" placeholder="Nama fasilitas baru...">
                        <div class="input-group-append">
                            <button class="btn btn-success" type="button" onclick="addAmenity()">
                                <i class="fas fa-plus"></i> Tambah
                            </button>
                        </div>
                    </div>

                    {{-- List Fasilitas Existing --}}
                    <label class="small text-muted mb-2">Daftar Fasilitas:</label>
                    <div id="amenities_list_container" style="max-height: 200px; overflow-y: auto;">
                        {{-- List akan di-render lewat JS --}}
                    </div>
                </div>

                <input type="hidden" id="edit_subarea_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-round" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<link rel="stylesheet" href="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.2/dist/leaflet-geoman.css" />
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
<script src="https://unpkg.com/@geoman-io/leaflet-geoman-free@2.14.2/dist/leaflet-geoman.min.js"></script>

<script>
    let map;
    // Data dari Controller
    let centerData = @json($area->park_area_data);
    let center = [parseFloat(centerData.lat), parseFloat(centerData.lng)];
    
    // Data Subarea & Relasi Amenities
    let existingSubareas = @json($area->parkSubarea);
    
    let currentLayer = null; 

    // === 1. INISIALISASI PETA ===
    function initMap() {
        map = L.map('map').setView(center, parseInt(centerData.zoom));

        // Tile Layer Satelit (Esri)
        L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
            attribution: 'Tiles &copy; Esri',
            maxZoom: 19
        }).addTo(map);

        // Render Subarea yang ada
        existingSubareas.forEach(sub => {
            let polygon = L.polygon(sub.park_subarea_polygon, {
                color: '#31ce36', 
                fillColor: '#31ce36',
                fillOpacity: 0.35,
                weight: 2
            }).addTo(map);

            polygon.subareaId = sub.park_subarea_id;
            polygon.subareaName = sub.park_subarea_name;

            polygon.bindPopup(`<b>${sub.park_subarea_name}</b>`);

            // Event Listener: Auto-Update saat Polygon Diedit di Peta
            const saveChanges = function(e) {
                let rawCoords = e.layer.getLatLngs()[0];
                let coords = rawCoords.map(p => ({ lat: p.lat, lng: p.lng }));
                updatePolygonDirectly(polygon.subareaId, polygon.subareaName, JSON.stringify(coords));
            };

            polygon.on('pm:edit', saveChanges);
            polygon.on('pm:vertexremoved', saveChanges); 
            polygon.on('pm:vertexadded', saveChanges);
            polygon.on('pm:dragend', saveChanges);
        });

        // Setup Toolbar Geoman
        map.pm.addControls({
            position: 'topleft',
            drawPolygon: true,
            drawCircleMarker: false,
            drawMarker: false,
            drawPolyline: false,
            drawRectangle: false,
            drawCircle: false,
            drawText: false,
            editMode: true,      
            dragMode: false,
            cutPolygon: false,
            removalMode: true,   
        });
        map.pm.setLang('id');

        // Event: Selesai Gambar Polygon Baru -> Buka Modal
        map.on('pm:create', function(e) {
            currentLayer = e.layer;
            let rawCoords = e.layer.getLatLngs()[0]; 
            let coords = rawCoords.map(p => ({ lat: p.lat, lng: p.lng }));

            document.getElementById('polygon_data').value = JSON.stringify(coords);
            $('#subarea_name').val('');
            $('#modalSubarea').modal('show');
        });
    }

    // === 2. LOGIKA SUBAREA (CREATE, UPDATE POLYGON, UPDATE NAME) ===

    // Simpan Subarea Baru
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
            data: { 
                _token: "{{ csrf_token() }}", 
                name: name, 
                polygon: polygon 
            },
            success: function(res) {
                $('#modalSubarea').modal('hide');
                Swal.fire({
                    icon: 'success', title: 'Berhasil!', text: 'Subarea dibuat.',
                    timer: 1500, showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function(xhr) {
                $('#modalSubarea').modal('hide');
                Swal.fire('Error', 'Gagal menyimpan.', 'error');
                if(currentLayer) map.removeLayer(currentLayer); 
            }
        });
    }

    // Update Polygon Langsung (saat geser peta)
    function updatePolygonDirectly(id, name, polygonJson) {
        let updateUrl = "{{ url('admin/park-subarea') }}/" + id;
        $.ajax({
            url: updateUrl,
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                _method: "PUT", 
                name: name, 
                polygon: polygonJson 
            },
            success: function(res) {
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                Toast.fire({ icon: 'success', title: 'Peta tersimpan otomatis!' });
            }
        });
    }

    // Update Nama Subarea (dari Modal Edit)
    function updateSubareaName() {
        let id = $('#edit_subarea_id').val();
        let name = $('#edit_subarea_name').val();

        if(!name) { Swal.fire('Gagal', 'Nama kosong.', 'warning'); return; }

        $.ajax({
            url: "{{ url('admin/park-subarea') }}/" + id,
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                _method: "PUT", 
                name: name 
            },
            success: function(res) {
                Swal.fire({
                    icon: 'success', title: 'Berhasil', text: 'Nama subarea diperbarui.',
                    timer: 1000, showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function(err) { Swal.fire('Error', 'Gagal update nama.', 'error'); }
        });
    }

    // === 3. LOGIKA AMENITIES (SIMPLE CRUD RESOURCE) ===

    // Buka Modal & Render List
    function openEditModal(id, name, amenities = []) {
        $('#edit_subarea_id').val(id);
        $('#edit_subarea_name').val(name);
        $('#new_amenity_name').val(''); // Reset input tambah
        
        renderAmenitiesList(amenities); // Tampilkan list yang ada
        
        $('#modalEditSubarea').modal('show');
    }

    // Helper: Render HTML List Amenities
    function renderAmenitiesList(amenities) {
        let html = '';
        if (!Array.isArray(amenities) || amenities.length === 0) {
            html = '<div class="text-center text-muted p-3 small">Belum ada fasilitas.<br>Silakan tambah baru.</div>';
        } else {
            html = '<ul class="list-group list-group-flush border rounded">';
            amenities.forEach(function(item) {
                let amenityName = (typeof item === 'object' && item !== null) ? item.park_amenity_name : item;
                let amenityId = (typeof item === 'object' && item !== null) ? item.park_amenity_id : null;

                // Jika ID null (karena data string lama), tombol hapus tidak bisa berfungsi normal
                let deleteButton = '';
                if(amenityId) {
                    deleteButton = `
                        <button class="btn btn-xs btn-danger btn-round" onclick="deleteAmenity(${amenityId})">
                            <i class="fas fa-trash"></i>
                        </button>`;
                }

                html += `
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2" id="amenity_item_${amenityId}">
                        <span><i class="fas fa-check text-success mr-2"></i>${amenityName}</span>
                        ${deleteButton}
                    </li>
                `;
            });
            html += '</ul>';
        }
        $('#amenities_list_container').html(html);
    }

    // Tambah Amenity (POST ke Controller)
    function addAmenity() {
        let subareaId = $('#edit_subarea_id').val();
        let name = $('#new_amenity_name').val();

        if(!name) { Swal.fire('Gagal', 'Nama fasilitas kosong.', 'warning'); return; }

        $.ajax({
            url: "{{ route('admin.park-amenity.store') }}",
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                park_subarea_id: subareaId, 
                park_amenity_name: name 
            },
            success: function(res) {
                // Tambahkan elemen baru ke list UI secara manual (agar instan)
                let newItem = res.data;
                
                // Jika sebelumnya kosong, buat wrapper ul baru
                if ($('#amenities_list_container ul').length === 0) {
                    $('#amenities_list_container').html('<ul class="list-group list-group-flush border rounded"></ul>');
                }

                let newHtml = `
                    <li class="list-group-item d-flex justify-content-between align-items-center p-2" id="amenity_item_${newItem.park_amenity_id}">
                        <span><i class="fas fa-check text-success mr-2"></i>${newItem.park_amenity_name}</span>
                        <button class="btn btn-xs btn-danger btn-round" onclick="deleteAmenity(${newItem.park_amenity_id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </li>
                `;
                $('#amenities_list_container ul').append(newHtml);
                
                $('#new_amenity_name').val(''); // Reset input
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                Toast.fire({ icon: 'success', title: 'Fasilitas ditambahkan' });
            },
            error: function(err) { Swal.fire('Error', 'Gagal menyimpan fasilitas.', 'error'); }
        });
    }

    // Hapus Amenity (DELETE ke Controller)
    function deleteAmenity(id) {
        // Konfirmasi Hapus
        Swal.fire({
            title: 'Hapus Fasilitas?',
            text: "Data akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "{{ url('admin/park-amenity') }}/" + id,
                    type: "POST",
                    data: { 
                        _token: "{{ csrf_token() }}", 
                        _method: "DELETE" 
                    },
                    success: function(res) {
                        $('#amenity_item_' + id).remove(); // Hapus dari UI
                        
                        // Cek jika habis, tampilkan pesan kosong
                        if ($('#amenities_list_container li').length === 0) {
                             $('#amenities_list_container').html('<div class="text-center text-muted p-3 small">Belum ada fasilitas.<br>Silakan tambah baru.</div>');
                        }

                        const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                        Toast.fire({ icon: 'success', title: 'Fasilitas dihapus' });
                    },
                    error: function(err) { Swal.fire('Error', 'Gagal menghapus.', 'error'); }
                });
            }
        });
    }

    function cancelDrawing() {
        if(currentLayer) map.removeLayer(currentLayer);
    }

    // Init saat halaman siap
    document.addEventListener("DOMContentLoaded", function() {
        initMap();
        $(function () { $('[data-toggle="tooltip"]').tooltip() });
    });
</script>
@endpush