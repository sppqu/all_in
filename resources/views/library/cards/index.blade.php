@extends('layouts.adminty')

@section('title', 'Cetak Kartu Perpustakaan - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📇 Cetak Kartu Perpustakaan</h4>
            <p class="text-muted mb-0">Cetak kartu perpustakaan per kelas (Format A4 - 8 kartu/halaman)</p>
        </div>
        <div>
            <a href="{{ route('library.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="card-body text-white p-4">
            <div class="row align-items-center">
                <div class="col-md-2 text-center">
                    <i class="fas fa-id-card fa-4x opacity-75"></i>
                </div>
                <div class="col-md-10">
                    <h5 class="mb-2"><i class="fas fa-info-circle me-2"></i>Informasi Cetak Kartu</h5>
                    <ul class="mb-0 small">
                        <li>Ukuran kartu: <strong>85.6mm × 54mm</strong> (Standar ATM/Credit Card)</li>
                        <li>Format kertas: <strong>A4 Portrait</strong></li>
                        <li>Layout: <strong>2 kolom (kanan-kiri)</strong> - Hemat kertas!</li>
                        <li>Jumlah kartu per halaman: <strong>10 kartu</strong> (2 kolom × 5 baris)</li>
                        <li>Sudah termasuk: Nama, NIS, Kelas, Library ID, Validity Period</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Class List -->
    <div class="row g-3">
        @foreach($classes as $class)
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100 hover-card">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <div class="bg-primary bg-gradient text-white rounded-circle d-flex align-items-center justify-content-center"
                                 style="width: 50px; height: 50px;">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h5 class="mb-0">{{ $class->class_name }}</h5>
                            <small class="text-muted">
                                {{ $class->students->where('student_status', 1)->count() }} siswa aktif
                            </small>
                        </div>
                    </div>
                    
                    <hr>
                    
                    <div class="d-grid">
                        <a href="{{ route('manage.library.cards.print-class', $class->class_id) }}" 
                           class="btn btn-primary" target="_blank">
                            <i class="fas fa-print me-2"></i>Cetak Kartu PDF
                        </a>
                    </div>
                    
                    <div class="mt-2 text-center">
                        <small class="text-muted">
                            <i class="fas fa-file-pdf me-1"></i>
                            Estimasi {{ ceil($class->students->where('student_status', 1)->count() / 10) }} halaman
                        </small>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    @if($classes->count() == 0)
    <div class="text-center py-5">
        <i class="fas fa-folder-open fa-4x text-muted mb-3"></i>
        <h5>Belum Ada Data Kelas</h5>
        <p class="text-muted">Silakan tambahkan kelas terlebih dahulu</p>
    </div>
    @endif
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important;
}
</style>
@endsection

