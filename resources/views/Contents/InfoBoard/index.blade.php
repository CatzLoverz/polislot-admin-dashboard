@extends("Layouts.content_layout")

@section('title', 'Kelola Info Board')

@section('page_title', 'Kelola Info Board')
@section('page_subtitle', 'Sesuaikan dan Atur informasi yang ditampilkan di Info Board.')

@section('content')
<div class="page-inner mt--5">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                        <div class="card-body text-center" style="padding: 2rem;">
                            <img src="{{ asset('assets/img/myinternship-soon.gif') }}" alt="Coming Soon"
                                style="max-width: 100%; width: min(750px, 90%);" />
                            <div style="margin-top: 1.5rem;">
                                <span class="h1" style="font-weight: 600; color: #333;">COMING SOON</span>
                                <p class="text-muted mt-2">Fitur spesifik untuk peran Anda
                                    {{ strtoupper(Auth::user()->role ?? 'Pengguna') }}
                                    sedang kami persiapkan. Terima kasih atas kesabaran Anda!</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
</div>
@endsection