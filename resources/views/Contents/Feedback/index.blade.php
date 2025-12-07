@extends("Layouts.content_layout")

@section('title', 'Feedback Pengguna')
@section('page_title', 'Feedback Pengguna')
@section('page_subtitle', 'Kumpulan saran dan masukan dari pengguna aplikasi.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title">Daftar Feedback</h4>
                    <a href="{{ route('admin.feedback-category.index') }}" class="btn btn-primary btn-round ml-auto text-white" style="text-decoration: none;">
                        <i class="fa fa-list mr-2"></i> Kelola Kategori 
                    </a>
                </div>
                <div class="card-body">
                    
                    {{-- === BAGIAN FILTER === --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_filter">Filter Berdasarkan Kategori</label>
                                <select class="form-control" id="category_filter" name="category_filter">
                                    <option value="">-- Semua Kategori --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->fbk_category_id }}">{{ $cat->fbk_category_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6 mt-4 d-flex align-items-end justify-content-end">
                            <div class="form-group">
                                <button id="apply_filter" class="btn btn-primary">Terapkan Filter</button>
                                <button id="reset_filter" class="btn btn-danger ml-2">Batal</button>
                            </div>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="feedback-table" class="display table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Judul</th>
                                    <th>Deskripsi Masukan</th>
                                    <th>Kategori Masukan</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                {{-- Diisi oleh DataTables AJAX --}}
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
        // Inisialisasi DataTables
        var table = $('#feedback-table').DataTable({
            processing: true,
            serverSide: true,
            // Update AJAX untuk mengirim filter
            ajax: {
                url: "{{ route('admin.feedback.index') }}",
                data: function(d) {
                    d.category_filter = $('#category_filter').val(); // Kirim value dropdown
                }
            },
            order: [[4, 'desc']], // Default sort by Created At
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'feedback_title', name: 'feedback_title' },
                { 
                    data: 'feedback_description', 
                    name: 'feedback_description',
                    render: function(data, type, row) {
                        if (!data) return '-';
                        var style = 'white-space: pre-line; word-break: break-word; min-width: 300px; max-width: 500px;';

                        if (type !== 'display' || data.length <= 50) {
                            return `<div style="${style}">${data}</div>`;
                        }

                        var shortText = data.substr(0, 50) + '...';
                        var fullText = data; 

                        return `<div class="content-wrapper" style="${style}">` +
                                    `<span class="text-short">${shortText}</span>` +
                                    `<span class="text-full" style="display: none;">${fullText}</span>` +
                                `</div>` +
                                `<a href="javascript:void(0);" class="text-primary btn-toggle-text mt-1 d-inline-block" style="font-size: 0.85rem;">` +
                                    `Lihat Selengkapnya` +
                                `</a>`;
                    }
                },
                { data: 'category_name', name: 'feedbackCategory.fbk_category_name', orderable: false },
                { data: 'created_at', name: 'created_at', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // === EVENT LISTENER FILTER ===
        $('#apply_filter').click(function() {
            table.draw(); // Refresh tabel dengan parameter filter baru
        });

        $('#reset_filter').click(function() {
            $('#category_filter').val(''); // Reset dropdown
            table.draw(); // Refresh tabel
        });

        // === EVENT LISTENER TOMBOL "LIHAT SELENGKAPNYA" ===
        $('#feedback-table').on('click', '.btn-toggle-text', function() {
            var wrapper = $(this).prev('.content-wrapper');
            var isExpanded = $(this).hasClass('expanded');

            if (isExpanded) {
                wrapper.find('.text-short').show();
                wrapper.find('.text-full').hide();
                $(this).text('Lihat Selengkapnya').removeClass('expanded');
            } else {
                wrapper.find('.text-short').hide();
                wrapper.find('.text-full').show(); 
                $(this).text('Tutup').addClass('expanded');
            }
        });

        // Re-init tooltips saat tabel di-redraw
        $('#feedback-table').on('draw.dt', function () {
            try {
                $('[data-toggle="tooltip"]').tooltip();
            } catch (e) {
                console.error('Tooltip init error:', e);
            }
        });
    });
</script>
@endpush