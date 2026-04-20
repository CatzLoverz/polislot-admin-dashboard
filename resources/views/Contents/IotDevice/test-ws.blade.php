@extends("Layouts.content_layout")

@section('title', 'Testing WebSocket (Reverb)')
@section('page_title', 'Testing WebSocket')
@section('page_subtitle', 'Alat pengujian komunikasi real-time menggunakan Laravel Reverb.')

@section('content')
<div class="row mt--2">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <div class="card-title">Kirim Pesan ke Semua Klien</div>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Pesan yang dikirim di sini akan disiarkan serentak oleh *Reverb* dan ditangkap langsung oleh kotak "Log Penerimaan" (baik di laptop ini maupun perangkat lain yang secara bersamaan membuka halaman ini).</p>
                <div class="form-group px-0">
                    <label>Pesan Anda</label>
                    <div class="input-group">
                        <input type="text" id="ws-message" class="form-control" placeholder="Halo WebSocket!">
                        <div class="input-group-append">
                            <button id="btn-send-ws" class="btn btn-primary" type="button">
                                <i class="fa fa-paper-plane mr-2"></i> Kirim
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card full-height">
            <div class="card-header">
                <div class="card-title d-flex justify-content-between align-items-center">
                    <span>Log Penerimaan (Realtime)</span>
                    <span id="ws-status" class="badge badge-warning"><i class="fa fa-spinner fa-spin mr-1"></i> Menyambungkan...</span>
                </div>
            </div>
            <div class="card-body">
                <div class="list-group list-group-messages list-group-flush" id="messages-container" style="max-height: 400px; overflow-y: auto;">
                    {{-- Pesan akan masuk ke sini via Javascript Echo --}}
                    <div class="text-center text-muted py-5" id="empty-state">
                        <i class="fa fa-inbox fa-3x mb-3"></i>
                        <br>Belum ada pesan WebSocket yang diterima.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
{{-- Hanya memuat vite dan echo JS jika modul instalasi broadcasting sudah selesai --}}
@vite(['resources/js/app.js'])

<script>
    $(document).ready(function() {
        const $btnSend = $('#btn-send-ws');
        const $inputMsg = $('#ws-message');
        const $container = $('#messages-container');
        const $emptyState = $('#empty-state');
        const $statusBadge = $('#ws-status');

        // Pastikan pustaka Echo (Reverb) sukses dimuat
        setTimeout(function() {
            if (typeof window.Echo !== 'undefined') {
                $statusBadge.removeClass('badge-warning').addClass('badge-success').html('<i class="fa fa-wifi mr-1"></i> Terhubung (Reverb)');
                
                // Mendengarkan public channel bernama 'iot-channel'
                // Karena kita menggunakan event App\Events\TestWebSocket
                window.Echo.channel('iot-channel')
                    .listen('.test.message', (e) => {
                        console.log('Message Received:', e);
                        
                        // Sembunyikan elemen kosong
                        $emptyState.hide();

                        // Tambahkan baris pesan baru
                        const newMsg = `
                            <div class="list-group-item px-0" style="background-color: #f8f9fa; animation: fadeIn 0.5s;">
                                <div class="d-flex w-100 justify-content-between">
                                    <h5 class="mb-1 text-primary"><i class="fa fa-envelope-open mr-2"></i>Pesan Masuk Baru!</h5>
                                    <small class="text-muted">${e.timestamp}</small>
                                </div>
                                <p class="mb-1 font-weight-bold" style="font-size: 16px;">"${e.message}"</p>
                            </div>
                        `;
                        
                        $container.prepend(newMsg);
                    });
            } else {
                $statusBadge.removeClass('badge-warning').addClass('badge-danger').html('<i class="fa fa-times-circle mr-1"></i> Tidak Terhubung');
                console.error("Laravel Echo library is not loaded. Make sure you ran 'php artisan install:broadcasting' and 'npm run build'.");
            }
        }, 1000);

        // Menangani aksi klik tombol Kirim Pesan via POST API/Route
        $btnSend.on('click', function() {
            const msgObj = $inputMsg.val().trim();
            if(!msgObj) return;

            // Kunci tombol sejenak
            $btnSend.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i>');

            $.ajax({
                url: "{{ route('admin.test-ws.push') }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    message: msgObj
                },
                success: function(response) {
                    $inputMsg.val('');
                    // Membuka kunci tombol
                    $btnSend.prop('disabled', false).html('<i class="fa fa-paper-plane mr-2"></i> Kirim');
                },
                error: function(xhr) {
                    alert('Gagal trigger pesan log. Lakukan periksa Log Laravel.');
                    $btnSend.prop('disabled', false).html('<i class="fa fa-paper-plane mr-2"></i> Kirim');
                }
            });
        });

        // Trigger kirim dengan menekan Enter
        $inputMsg.on('keypress', function(e) {
            if (e.which == 13) {
                $btnSend.click();
            }
        });
    });
</script>
<style>
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>
@endpush
