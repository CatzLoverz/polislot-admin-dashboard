@extends("Layouts.content_layout")

@section('title', 'Manajemen Area Parkir')
@section('page_title', 'Manajemen Area Parkir')
@section('page_subtitle', 'Kelola lokasi parkir, kode area, dan peta digital.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Area Parkir</h4>
                    <a href="{{ route('admin.park-area.create') }}" class="btn btn-primary btn-round ml-auto">
                        <i class="fa fa-plus mr-2"></i> Tambah Area Baru
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="park-area-table" class="display table table-striped table-hover" style="width:100%">
                            <thead> 
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Nama Area</th>
                                    <th>Kode Area</th>
                                    <th>Titik Koordinat (Pusat)</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- DataTables Server-side --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function() {
        $('#park-area-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('admin.park-area.index') }}",
            order: [[1, 'asc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'park_area_name', name: 'park_area_name', className: 'font-weight-bold' },
                { 
                    data: 'park_area_code', 
                    name: 'park_area_code',
                    render: function(data) {
                        return `<span class="badge badge-secondary px-3 py-2">${data}</span>`;
                    }
                },
                { 
                    data: 'park_area_data', 
                    name: 'park_area_data',
                    className: 'text-center text-muted small',
                    render: function(data) {
                        // Data JSON otomatis di-cast jadi object oleh Eloquent
                        // Jika DataTables mengembalikan object, akses langsung propertinya
                        if(data && data.lat && data.lng) {
                            return `${data.lat}, ${data.lng}`;
                        }
                        return '-';
                    }
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // Re-init tooltip
        $('#park-area-table').on('draw.dt', function () {
            $('[data-toggle="tooltip"]').tooltip();
        });
    });
</script>
@endpush