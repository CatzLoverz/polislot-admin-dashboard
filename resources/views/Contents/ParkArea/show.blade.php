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
            <div class="card shadow-sm">
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
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-header">
                    <h4 class="card-title font-weight-bold">Daftar Sub Area</h4>
                </div>
                <div class="card-body p-0">
                    <ul class="list-group list-group-flush" style="max-height: 500px; overflow-y: auto;">
                        @forelse($area->parkSubarea as $sub)
                            <li class="list-group-item">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div style="width: 65%;">
                                        <span class="font-weight-bold d-block text-dark">{{ $sub->park_subarea_name }}</span>
                                        {{-- Indikator Status Teks Kecil --}}
                                        <small class="d-block mt-1" style="color: {{ $sub->status_color }}">
                                            <i class="fas fa-circle" style="font-size: 8px;"></i> 
                                            @if($sub->status_color == '#f25961') Penuh
                                            @elseif($sub->status_color == '#ffad46') Terbatas
                                            @elseif($sub->status_color == '#31ce36') Banyak Kosong
                                            @else Tidak ada info @endif
                                        </small>
                                        
                                        {{-- Amenities --}}
                                        <div class="mt-2">
                                            @forelse($sub->parkAmenity as $amenity)
                                                <span class="badge badge-count text-secondary border border-secondary mr-1 mb-1 p-1" style="font-size: 9px;">
                                                    {{ $amenity->park_amenity_name }}
                                                </span>
                                            @empty
                                                <small class="text-muted font-italic" style="font-size: 11px;">Tidak ada fasilitas.</small>
                                            @endforelse
                                        </div>
                                    </div>
                                    
                                    <div class="d-flex align-items-center ml-1">
                                        {{-- Tombol Lihat Komentar --}}
                                        <button type="button" class="btn btn-icon btn-round btn-info btn-xs mr-1" 
                                            onclick="openCommentModal('{{ $sub->park_subarea_name }}', {{ json_encode($sub->subareaComment) }})"
                                            data-toggle="tooltip" title="Lihat Komentar">
                                            <i class="fas fa-comments"></i>
                                        </button>

                                        {{-- Tombol Edit (Existing) --}}
                                        <button type="button" class="btn btn-icon btn-round btn-primary btn-xs mr-1" 
                                            onclick="openEditModal({{ $sub->park_subarea_id }}, '{{ $sub->park_subarea_name }}', {{ json_encode($sub->parkAmenity) }})"
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
    let drawingManager;
    let currentPolygonObj = null;

    // Definisikan Base URL Storage untuk akses gambar
    const storageBaseUrl = "{{ asset('storage') }}";

    async function initMap() {
        await google.maps.importLibrary("maps");
        await google.maps.importLibrary("drawing");
        await google.maps.importLibrary("geometry");

        map = new google.maps.Map(document.getElementById("map"), {
            center: center,
            zoom: parseInt(centerData.zoom),
            mapTypeId: 'satellite',
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

        drawingManager = new google.maps.drawing.DrawingManager({
            drawingMode: google.maps.drawing.OverlayType.POLYGON,
            drawingControl: true,
            drawingControlOptions: {
                position: google.maps.ControlPosition.TOP_LEFT,
                drawingModes: ['polygon'],
            },
            polygonOptions: {
                fillColor: "#1572e8",
                fillOpacity: 0.5,
                strokeWeight: 2,
                editable: true,
                zIndex: 1
            }
        });
        drawingManager.setMap(map);

        google.maps.event.addListener(drawingManager, 'overlaycomplete', function(event) {
            currentPolygonObj = event.overlay;
            let paths = event.overlay.getPath().getArray();
            let coords = paths.map(p => ({ lat: p.lat(), lng: p.lng() }));
            document.getElementById('polygon_data').value = JSON.stringify(coords);
            $('#subarea_name').val('');
            $('#modalSubarea').modal('show');
            drawingManager.setDrawingMode(null);
        });
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
                Swal.fire('Error', 'Gagal menyimpan.', 'error');
                if(currentPolygonObj) currentPolygonObj.setMap(null);
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

    function openEditModal(id, name, amenities = []) {
        $('#edit_subarea_id').val(id);
        $('#edit_subarea_name').val(name);
        $('#new_amenity_name').val('');
        
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
        
        if(!name) { Swal.fire('Gagal', 'Nama subarea kosong.', 'warning'); return; }

        $.ajax({
            url: "{{ url('admin/park-subarea') }}/" + id,
            type: "POST",
            data: { 
                _token: "{{ csrf_token() }}", 
                _method: "PUT", 
                name: name,
                amenities: tempAmenities // Kirim array amenities baru
            },
            success: function(res) {
                $('#modalEditSubarea').modal('hide');
                Swal.fire({
                    icon: 'success', title: 'Berhasil', text: 'Perubahan disimpan.',
                    timer: 1000, showConfirmButton: false
                }).then(() => { location.reload(); });
            },
            error: function(err) { Swal.fire('Error', 'Gagal menyimpan perubahan.', 'error'); }
        });
    }

    // [PERBAIKAN] Logika Gambar Komentar & Avatar
    function openCommentModal(name, comments) {
        $('#comment_subarea_title').text(name);
        
        let html = '';
        if (!Array.isArray(comments) || comments.length === 0) {
            html = `<div class="text-center py-5"><i class="fas fa-comment-slash fa-3x text-muted mb-3"></i><p class="text-muted">Belum ada komentar untuk area ini.</p></div>`;
        } else {
            comments.sort((a, b) => b.subarea_comment_id - a.subarea_comment_id);
            
            html = '<div class="list-group list-group-flush">';
            comments.forEach(c => {
                let userName = c.user ? c.user.name : 'User Terhapus';
                
                // [FIX] Cek Avatar path
                let rawAvatar = c.user && c.user.avatar ? c.user.avatar : null;
                let userAvatar = rawAvatar 
                    ? (rawAvatar.startsWith('http') ? rawAvatar : storageBaseUrl + '/' + rawAvatar) 
                    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(userName);
                
                let date = new Date(c.created_at).toLocaleString('id-ID');

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
                            <small class="text-muted">${date}</small>
                        </div>
                        <p class="mb-1 text-dark">${c.subarea_comment_content}</p>
                        ${commentImageHtml}
                    </div>
                `;
            });
            html += '</div>';
        }
        $('#comments_container').html(html);
        $('#modalComments').modal('show');
    }

    function cancelDrawing() {
        if(currentPolygonObj) {
            currentPolygonObj.setMap(null);
            drawingManager.setDrawingMode(null);
        }
    }

    // Event Listener untuk Konfirmasi Hapus Subarea
    document.addEventListener("DOMContentLoaded", function() {
        initMap();
        $(function () { $('[data-toggle="tooltip"]').tooltip() });

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