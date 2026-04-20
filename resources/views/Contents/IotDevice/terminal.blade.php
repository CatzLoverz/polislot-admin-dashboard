@extends("Layouts.content_layout")

@section('title', 'Terminal SSH - ' . $device->device_name)
@section('page_title', 'Terminal Perangkat IoT')
@section('page_subtitle', 'Remote SSH akses untuk perangkat: ' . $device->device_name)

@section('content')
<div class="page-inner mt--5">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title">Terminal: {{ $device->device_name }}</h4>
                    <div>
                        <a href="{{ route('admin.iot-devices.index') }}" class="btn btn-secondary btn-round mr-2">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                        <a href="{{ $device->ssh_url }}" target="_blank" class="btn btn-info btn-round">
                            <i class="fas fa-external-link-alt"></i> Buka di Tab Baru
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    {{-- Alert jika iframe diblokir oleh header X-Frame-Options dari Cloudflare --}}
                    <div class="alert alert-warning m-3" role="alert">
                        <i class="fas fa-exclamation-triangle"></i> Jika halaman terminal di bawah ini kosong/putih, kemungkinan fitur keamanan Cloudflare memblokir akses iframe. 
                        Silakan gunakan tombol <strong>"Buka di Tab Baru"</strong> di atas.
                    </div>

                    <div class="embed-responsive embed-responsive-16by9" style="height: 70vh;">
                        <iframe class="embed-responsive-item" src="{{ $device->ssh_url }}" allowfullscreen></iframe>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
