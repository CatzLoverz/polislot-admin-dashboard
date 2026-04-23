@extends("Layouts.content_layout")

@section('title', 'Manajemen FAQ')
@section('page_title', 'Manajemen FAQ')
@section('page_subtitle', 'Kelola daftar pertanyaan yang sering diajukan oleh pengguna aplikasi.')

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
                    <h4 class="card-title">Daftar FAQ</h4>
                    <button type="button" class="btn btn-primary btn-round ml-auto" data-toggle="modal" data-target="#modalFaq" id="btn-tambah-faq">
                        <i class="fa fa-plus mr-2"></i> Tambah FAQ
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="faq-table" class="display table table-striped table-hover" style="width:100%">
                            <thead>
                                <tr>
                                    <th class="text-center">No</th>
                                    <th>Pertanyaan</th>
                                    <th>Jawaban</th>
                                    <th class="text-center">Dibuat Oleh</th>
                                    <th class="text-center">Terakhir Diperbarui</th>
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

{{-- ============================================================ --}}
{{-- MODAL: Tambah / Edit FAQ                                      --}}
{{-- ============================================================ --}}
<div class="modal fade" id="modalFaq" tabindex="-1" role="dialog" aria-labelledby="modalFaqLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalFaqLabel">Tambah FAQ</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            {{-- Form digunakan untuk Store maupun Update --}}
            <form id="form-faq" method="POST" action="{{ route('admin.user-faq.store') }}">
                @csrf
                {{-- Field method-spoofing untuk PUT (diisi via JS saat Edit) --}}
                <input type="hidden" name="_method" id="form-method" value="POST">

                <div class="modal-body">

                    {{-- Tampilkan error validasi jika ada (untuk non-AJAX submit) --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <ul class="mb-0 pl-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif

                    <div class="form-group">
                        <label for="faq_question">
                            Pertanyaan <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('faq_question') is-invalid @enderror"
                            id="faq_question"
                            name="faq_question"
                            placeholder="Masukkan pertanyaan FAQ..."
                            maxlength="255"
                            value="{{ old('faq_question') }}"
                            required
                        >
                        @error('faq_question')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">Maksimal 255 karakter.</small>
                    </div>

                    <div class="form-group">
                        <label for="faq_answer">
                            Jawaban <span class="text-danger">*</span>
                        </label>
                        <textarea
                            class="form-control @error('faq_answer') is-invalid @enderror"
                            id="faq_answer"
                            name="faq_answer"
                            rows="6"
                            placeholder="Masukkan jawaban dari pertanyaan di atas..."
                            required
                        >{{ old('faq_answer') }}</textarea>
                        @error('faq_answer')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fa fa-times mr-1"></i> Batal
                    </button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-faq">
                        <i class="fa fa-save mr-1"></i> Simpan
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
{{-- ============================================================ --}}
{{-- AKHIR MODAL                                                   --}}
{{-- ============================================================ --}}

@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/datatables/datatables.min.js') }}"></script>
<script>
    $(document).ready(function () {

        // ----------------------------------------------------------
        // 1. Inisialisasi DataTables
        // ----------------------------------------------------------
        var table = $('#faq-table').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.user-faq.index') }}",
            },
            order: [[4, 'desc']], // Default sort by Updated At
            columns: [
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'faq_question',
                    name: 'faq_question',
                    render: function (data, type, row) {
                        if (!data) return '-';
                        if (type !== 'display' || data.length <= 80) return data;

                        var short = data.substr(0, 80) + '...';
                        return '<span class="faq-short">' + short + '</span>' +
                               '<span class="faq-full" style="display:none;">' + data + '</span>' +
                               '<a href="javascript:void(0);" class="text-primary btn-toggle d-block mt-1" style="font-size:0.82rem;">Lihat Selengkapnya</a>';
                    }
                },
                {
                    data: 'faq_answer',
                    name: 'faq_answer',
                    render: function (data, type, row) {
                        if (!data) return '-';
                        var style = 'white-space: pre-line; word-break: break-word; min-width: 250px; max-width: 450px;';

                        if (type !== 'display' || data.length <= 100) {
                            return '<div style="' + style + '">' + data + '</div>';
                        }

                        var short = data.substr(0, 100) + '...';
                        return '<div class="content-wrapper" style="' + style + '">' +
                                   '<span class="text-short">' + short + '</span>' +
                                   '<span class="text-full" style="display:none;">' + data + '</span>' +
                               '</div>' +
                               '<a href="javascript:void(0);" class="text-primary btn-toggle-text mt-1 d-inline-block" style="font-size:0.82rem;">Lihat Selengkapnya</a>';
                    }
                },
                {
                    data: 'creator_name',
                    name: 'user.name',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'updated_at',
                    name: 'updated_at',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
            ],
            language: { url: "{{ asset('assets/js/plugin/datatables/indonesian.json') }}" }
        });

        // ----------------------------------------------------------
        // 2. Re-init tooltips setelah tabel di-redraw
        // ----------------------------------------------------------
        $('#faq-table').on('draw.dt', function () {
            try {
                $('[data-toggle="tooltip"]').tooltip();
            } catch (e) {
                console.error('Tooltip init error:', e);
            }
        });

        // ----------------------------------------------------------
        // 3. Toggle "Lihat Selengkapnya" – kolom Pertanyaan
        // ----------------------------------------------------------
        $('#faq-table').on('click', '.btn-toggle', function () {
            var $this    = $(this);
            var expanded = $this.hasClass('expanded');

            $this.prev('.faq-full').toggle(!expanded);
            $this.prevAll('.faq-short').toggle(expanded);
            $this.text(expanded ? 'Lihat Selengkapnya' : 'Tutup')
                 .toggleClass('expanded', !expanded);
        });

        // ----------------------------------------------------------
        // 4. Toggle "Lihat Selengkapnya" – kolom Jawaban
        // ----------------------------------------------------------
        $('#faq-table').on('click', '.btn-toggle-text', function () {
            var $wrapper = $(this).prev('.content-wrapper');
            var expanded = $(this).hasClass('expanded');

            $wrapper.find('.text-short').toggle(expanded);
            $wrapper.find('.text-full').toggle(!expanded);
            $(this).text(expanded ? 'Lihat Selengkapnya' : 'Tutup')
                   .toggleClass('expanded', !expanded);
        });

        // ----------------------------------------------------------
        // 5. Reset modal ke mode "Tambah" saat tombol Tambah diklik
        // ----------------------------------------------------------
        $('#btn-tambah-faq').on('click', function () {
            resetModal();
        });

        // ----------------------------------------------------------
        // 6. Isi modal dengan data saat tombol Edit diklik
        // ----------------------------------------------------------
        $('#faq-table').on('click', '.btn-edit', function () {
            var faqId      = $(this).data('id');
            var question   = $(this).data('question');
            var answer     = $(this).data('answer');
            var updateUrl  = $(this).data('update-url');

            // Set judul & action
            $('#modalFaqLabel').text('Edit FAQ');
            $('#form-faq').attr('action', updateUrl);
            $('#form-method').val('PUT');
            $('#btn-submit-faq').html('<i class="fa fa-save mr-1"></i> Perbarui');

            // Isi field
            $('#faq_question').val(question);
            $('#faq_answer').val(answer);

            // Tampilkan modal
            $('#modalFaq').modal('show');
        });

        // ----------------------------------------------------------
        // 7. Reset modal ke kondisi awal (mode Tambah)
        // ----------------------------------------------------------
        function resetModal() {
            $('#modalFaqLabel').text('Tambah FAQ');
            $('#form-faq').attr('action', "{{ route('admin.user-faq.store') }}");
            $('#form-method').val('POST');
            $('#btn-submit-faq').html('<i class="fa fa-save mr-1"></i> Simpan');
            $('#faq_question').val('');
            $('#faq_answer').val('');
        }

        // ----------------------------------------------------------
        // 8. Reset modal saat ditutup
        // ----------------------------------------------------------
        $('#modalFaq').on('hidden.bs.modal', function () {
            resetModal();
        });

        // ----------------------------------------------------------
        // 9. Buka modal otomatis jika ada error validasi (setelah redirect)
        // ----------------------------------------------------------
        @if ($errors->any())
            $('#modalFaq').modal('show');
        @endif

        // ----------------------------------------------------------
        // 10. SweetAlert notifikasi CRUD
        // ----------------------------------------------------------
        @if (session('swal_success_crud'))
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: '{{ session('swal_success_crud') }}',
                timer: 2500,
                showConfirmButton: false
            });
        @endif

        @if (session('swal_error_crud'))
            Swal.fire({
                icon: 'error',
                title: 'Gagal!',
                text: '{{ session('swal_error_crud') }}',
            });
        @endif

    });
</script>
@endpush