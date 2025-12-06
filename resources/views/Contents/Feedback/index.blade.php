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
                        Kelola Kategori 
                    </a>
                </div>
                <div class="card-body">
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
            ajax: "{{ route('admin.feedback.index') }}",
            order: [[4, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'feedback_title', name: 'feedback_title' },
                { 
                    data: 'feedback_description', 
                    name: 'feedback_description',
                    render: function(data, type, row) {
                        // Pastikan data tidak null
                        if (!data) return '-';

                        // Style agar enter terbaca (pre-line) dan text turun (break-word)
                        var style = 'white-space: pre-line; word-break: break-word; min-width: 300px; max-width: 500px;';

                        // Jika teks pendek, tampilkan langsung
                        if (type !== 'display' || data.length <= 50) {
                            return `<div style="${style}">${data}</div>`;
                        }

                        // Jika teks panjang (> 50 karakter), potong dan tambahkan tombol
                        var shortText = data.substr(0, 50) + '...';
                        var fullText = data; 

                        // Render HTML toggle
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

        // === EVENT LISTENER TOMBOL "LIHAT SELENGKAPNYA" ===
        $('#feedback-table').on('click', '.btn-toggle-text', function() {
            var wrapper = $(this).prev('.content-wrapper');
            var isExpanded = $(this).hasClass('expanded');

            if (isExpanded) {
                // Tutup
                wrapper.find('.text-short').show();
                wrapper.find('.text-full').hide();
                $(this).text('Lihat Selengkapnya').removeClass('expanded');
            } else {
                // Buka
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