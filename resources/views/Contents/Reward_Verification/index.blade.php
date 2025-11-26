@extends("Layouts.content_layout")

@section('title', 'Verifikasi Reward')
@section('page_title', 'Verifikasi Kode Reward')
@section('page_subtitle', 'Verifikasi dan ubah status kode voucher pengguna.')

@section('content')
<div class="page-inner mt--5">
    {{-- Form Pencarian Kode --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body" style="padding: 2rem 2.5rem;">
                    <h3 class="text-white mb-3">
                        <i class="fa fa-search mr-2"></i>Cari Kode Voucher
                    </h3>
                    <form action="{{ route('admin.reward_verification.search') }}" method="POST">
                        @csrf
                        <div class="input-group input-group-lg">
                            <input type="text" 
                                   name="voucher_code" 
                                   class="form-control" 
                                   placeholder="Masukkan kode voucher (contoh: RWD-ABC123)"
                                   style="border-radius: 15px 0 0 15px; border: none; padding: 1rem 1.5rem;"
                                   required>
                            <div class="input-group-append">
                                <button class="btn btn-light" type="submit" style="border-radius: 0 15px 15px 0; padding: 0 2rem; font-weight: bold;">
                                    <i class="fa fa-search mr-2"></i>Cari
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- Hasil Pencarian --}}
    @if(session('search_result'))
        @php $result = session('search_result'); @endphp
        <div class="row justify-content-center mb-4">
            <div class="col-md-12">
                <div class="card shadow-sm" style="border-radius: 20px; border: 3px solid #667eea;">
                    <div class="card-body" style="padding: 2rem 2.5rem;">
                        <h4 class="mb-3 font-weight-bold">
                            <i class="fa fa-info-circle text-primary mr-2"></i>Hasil Pencarian
                        </h4>
                        <div class="row align-items-center">
                            <div class="col-md-2 text-center">
                                @if($result->reward_image)
                                    <img src="{{ asset('storage/' . $result->reward_image) }}" 
                                         alt="{{ $result->reward_name }}"
                                         style="width: 100px; height: 100px; object-fit: contain;">
                                @else
                                    <i class="fa fa-gift fa-4x text-warning"></i>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <h5 class="font-weight-bold mb-2">{{ $result->reward_name }}</h5>
                                <p class="mb-1"><strong>Kode Voucher:</strong> 
                                    <span class="badge badge-dark px-3 py-2" style="font-size: 1rem;">{{ $result->voucher_code }}</span>
                                </p>
                                <p class="mb-1"><strong>User:</strong> {{ $result->user_name }} ({{ $result->user_email }})</p>
                                <p class="mb-1"><strong>Tipe:</strong> 
                                    <span class="badge badge-{{ $result->reward_type === 'voucher' ? 'danger' : 'warning' }}">
                                        {{ ucfirst($result->reward_type) }}
                                    </span>
                                </p>
                                <p class="mb-0"><strong>Ditukar:</strong> {{ \Carbon\Carbon::parse($result->created_at)->format('d M Y, H:i') }}</p>
                            </div>
                            <div class="col-md-3 text-right">
                                @if($result->redeemed_status === 'belum dipakai')
                                    <form action="{{ route('admin.reward_verification.verify', $result->user_reward_id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-lg" style="border-radius: 12px;">
                                            <i class="fa fa-check-circle mr-2"></i>Verifikasi & Tandai Terpakai
                                        </button>
                                    </form>
                                @else
                                    <span class="badge badge-success py-3 px-4" style="font-size: 1.1rem;">
                                        <i class="fa fa-check-circle mr-2"></i>Sudah Terpakai
                                    </span>
                                    <p class="text-muted small mt-2">{{ \Carbon\Carbon::parse($result->updated_at)->format('d M Y, H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Daftar Reward Pending --}}
    <div class="row justify-content-center mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">
                    <h2 class="mb-4 font-weight-bold text-dark">
                        <i class="fa fa-clock mr-2 text-warning"></i>Menunggu Verifikasi
                    </h2>
                    <hr>

                    @forelse($pendingRewards as $pending)
                        <div class="mb-3 p-4" style="background: linear-gradient(135deg, #fff9e6 0%, #ffe6b3 100%); border-radius: 15px; border-left: 5px solid #ffc107;">
                            <div class="row align-items-center">
                                <div class="col-md-1 text-center">
                                    @if($pending->reward_image)
                                        <img src="{{ asset('storage/' . $pending->reward_image) }}" 
                                             alt="{{ $pending->reward_name }}"
                                             style="width: 60px; height: 60px; object-fit: contain;">
                                    @else
                                        <i class="fa fa-gift fa-3x text-warning"></i>
                                    @endif
                                </div>
                                <div class="col-md-7">
                                    <h5 class="mb-1 font-weight-bold">{{ $pending->reward_name }}</h5>
                                    <p class="mb-1">
                                        <strong>Kode:</strong> 
                                        <span class="badge badge-dark px-3 py-1" style="font-size: 0.95rem; letter-spacing: 1px;">
                                            {{ $pending->voucher_code }}
                                        </span>
                                    </p>
                                    <p class="mb-0 text-muted small">
                                        <i class="fa fa-user mr-1"></i> {{ $pending->user_name }} | 
                                        <i class="fa fa-envelope mr-1"></i> {{ $pending->user_email }}
                                    </p>
                                    <small class="text-muted">
                                        Ditukar: {{ \Carbon\Carbon::parse($pending->created_at)->format('d M Y, H:i') }}
                                    </small>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="badge badge-info py-2 px-3" style="font-size: 0.85rem;">
                                        <i class="fa fa-coins mr-1"></i>{{ number_format($pending->points_required, 0, ',', '.') }} Poin
                                    </span>
                                </div>
                                <div class="col-md-2 text-right">
                                    <button class="btn btn-success" 
                                            data-toggle="modal" 
                                            data-target="#verifyModal{{ $pending->user_reward_id }}"
                                            style="border-radius: 10px;">
                                        <i class="fa fa-check mr-1"></i> Verifikasi
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Verifikasi --}}
                        <div class="modal fade" id="verifyModal{{ $pending->user_reward_id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content">
                                    <form action="{{ route('admin.reward_verification.verify', $pending->user_reward_id) }}" method="POST">
                                        @csrf
                                        <div class="modal-header">
                                            <h5 class="modal-title">Konfirmasi Verifikasi</h5>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span>&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body text-center">
                                            <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
                                            <h5>{{ $pending->reward_name }}</h5>
                                            <p class="mb-2">
                                                <strong>Kode:</strong> 
                                                <span class="badge badge-dark px-3 py-2" style="font-size: 1rem;">{{ $pending->voucher_code }}</span>
                                            </p>
                                            <p class="mb-2"><strong>User:</strong> {{ $pending->user_name }}</p>
                                            <p class="text-muted">Apakah kamu yakin ingin memverifikasi kode ini dan menandainya sebagai <strong>TERPAKAI</strong>?</p>
                                        </div>
                                        <div class="modal-footer justify-content-center">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-success">
                                                <i class="fa fa-check mr-1"></i> Ya, Verifikasi
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fa fa-check-double fa-3x mb-3"></i>
                            <p>Semua reward sudah diverifikasi.</p>
                        </div>
                    @endforelse

                    {{-- Pagination Pending --}}
                    @if($pendingRewards->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $pendingRewards->appends(['used' => request('used')])->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Riwayat Terverifikasi --}}
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card shadow-sm" style="border-radius: 20px;">
                <div class="card-body" style="padding: 2rem 2.5rem;">
                    <h2 class="mb-4 font-weight-bold text-dark">
                        <i class="fa fa-check-circle mr-2 text-success"></i>Riwayat Terverifikasi
                    </h2>
                    <hr>

                    @forelse($usedRewards as $used)
                        <div class="mb-3 p-3" style="background: #f8f9fa; border-radius: 12px; border-left: 4px solid #28a745;">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h6 class="mb-1 font-weight-bold">{{ $used->reward_name }}</h6>
                                    <p class="mb-1 small">
                                        <span class="badge badge-dark">{{ $used->voucher_code }}</span>
                                        <span class="mx-2">|</span>
                                        <i class="fa fa-user"></i> {{ $used->user_name }}
                                    </p>
                                    <small class="text-muted">
                                        Diverifikasi: {{ \Carbon\Carbon::parse($used->updated_at)->format('d M Y, H:i') }}
                                    </small>
                                </div>
                                <div class="col-md-4 text-right">
                                    <span class="badge badge-success py-2 px-3">
                                        <i class="fa fa-check-circle mr-1"></i>Terpakai
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted">
                            <i class="fa fa-inbox fa-3x mb-3"></i>
                            <p>Belum ada riwayat verifikasi.</p>
                        </div>
                    @endforelse

                    {{-- Pagination Used --}}
                    @if($usedRewards->hasPages())
                        <div class="d-flex justify-content-center mt-4">
                            {{ $usedRewards->appends(['pending' => request('pending')])->links('pagination::bootstrap-4') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Smooth scroll untuk pagination
        const paginationLinks = document.querySelectorAll('.pagination a');
        paginationLinks.forEach(link => {
            link.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        });
    });
</script>
@endsection
@endsection