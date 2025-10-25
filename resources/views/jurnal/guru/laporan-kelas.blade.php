@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('jurnal.guru.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <h1 class="h3 mb-1 fw-bold" style="color: #6f42c1;">
            <i class="fas fa-file-alt me-2"></i>Laporan Jurnal Per Kelas
        </h1>
        <p class="text-muted mb-0">Laporan jurnal harian berdasarkan kelas dan range tanggal</p>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Laporan
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('jurnal.guru.laporan-kelas') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-users me-1"></i>Pilih Kelas <span class="text-danger">*</span>
                        </label>
                        <select name="kelas_id" class="form-select" required>
                            <option value="">-- Pilih Kelas --</option>
                            @foreach($classes as $class)
                                <option value="{{ $class->class_id }}" {{ request('kelas_id') == $class->class_id ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> Tampilkan
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($laporan !== null)
        <!-- Class Info -->
        @if($kelas)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">Kelas: {{ $kelas->class_name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-users me-1"></i>Total {{ count($laporan) }} siswa
                        </p>
                    </div>
                    <div class="col-md-4 text-end no-print">
                        <a href="{{ route('jurnal.guru.laporan-kelas-pdf', ['kelas_id' => request('kelas_id'), 'tanggal_dari' => request('tanggal_dari'), 'tanggal_sampai' => request('tanggal_sampai')]) }}" 
                           class="btn btn-outline-danger" target="_blank">
                            <i class="fas fa-file-pdf me-1"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Summary Statistics -->
        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #667eea !important;">
                    <div class="card-body">
                        <small class="text-muted">Total Siswa</small>
                        <h3 class="fw-bold mb-0">{{ count($laporan) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                    <div class="card-body">
                        <small class="text-muted">Total Jurnal</small>
                        <h3 class="fw-bold mb-0">{{ array_sum(array_column($laporan, 'total_jurnal')) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745 !important;">
                    <div class="card-body">
                        <small class="text-muted">Terverifikasi</small>
                        <h3 class="fw-bold mb-0">{{ array_sum(array_column($laporan, 'verified')) }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
                    <div class="card-body">
                        <small class="text-muted">Pending</small>
                        <h3 class="fw-bold mb-0">{{ array_sum(array_column($laporan, 'pending')) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Rekap Per Siswa -->
        @if(count($laporan) > 0)
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-table me-2 text-primary"></i>Rekap Jurnal 7 Kebiasaan Per Siswa
                </h5>
                <small class="text-muted">Jumlah entri per kategori untuk setiap siswa</small>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th width="3%" class="text-center align-middle" rowspan="2">No</th>
                                <th width="20%" class="align-middle" rowspan="2">Nama Siswa</th>
                                <th class="text-center" colspan="7">7 Kebiasaan Anak Indonesia Hebat</th>
                                <th width="10%" class="text-center align-middle" rowspan="2">Total<br>Jurnal</th>
                            </tr>
                            <tr>
                                @foreach($kategori as $kat)
                                <th class="text-center" style="background: {{ $kat->warna }}20; color: {{ $kat->warna }}; min-width: 80px;">
                                    <i class="{{ $kat->icon }} d-block mb-1"></i>
                                    <small>{{ $kat->nama_kategori }}</small>
                                </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($laporan as $index => $item)
                            <tr>
                                <td class="text-center align-middle">{{ $index + 1 }}</td>
                                <td class="align-middle">
                                    <strong>{{ $item['siswa']->student_full_name }}</strong>
                                    <br>
                                    <small class="text-muted">NIS: {{ $item['siswa']->student_nis }}</small>
                                </td>
                                @foreach($kategori as $kat)
                                <td class="text-center align-middle" style="background: {{ $kat->warna }}10;">
                                    @if(isset($item['count_per_kategori'][$kat->kategori_id]) && $item['count_per_kategori'][$kat->kategori_id] > 0)
                                        <span class="badge" style="background: {{ $kat->warna }};">
                                            {{ $item['count_per_kategori'][$kat->kategori_id] }}x
                                        </span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @endforeach
                                <td class="text-center align-middle">
                                    <strong class="text-primary">{{ $item['total_jurnal'] }}</strong>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-secondary">
                            <tr>
                                <th colspan="2" class="text-end align-middle">TOTAL:</th>
                                @php
                                    $totalPerKategori = [];
                                    foreach ($kategori as $kat) {
                                        $totalPerKategori[$kat->kategori_id] = 0;
                                    }
                                    foreach ($laporan as $item) {
                                        foreach ($item['count_per_kategori'] as $katId => $count) {
                                            if (isset($totalPerKategori[$katId])) {
                                                $totalPerKategori[$katId] += $count;
                                            }
                                        }
                                    }
                                @endphp
                                @foreach($kategori as $kat)
                                <th class="text-center align-middle" style="background: {{ $kat->warna }}20;">
                                    <strong>{{ $totalPerKategori[$kat->kategori_id] ?? 0 }}</strong>
                                </th>
                                @endforeach
                                <th class="text-center align-middle">
                                    <strong class="text-primary">{{ array_sum(array_column($laporan, 'total_jurnal')) }}</strong>
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">Tidak ada jurnal ditemukan untuk kelas ini</p>
                </div>
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3 mb-0">Pilih kelas dan klik "Tampilkan" untuk melihat laporan</p>
            </div>
        </div>
    @endif
</div>

<style>
@media print {
    .btn, nav, .sidebar, .no-print, a[href*="kembali"] {
        display: none !important;
    }
    .card {
        box-shadow: none !important;
        border: 1px solid #000 !important;
        page-break-inside: avoid;
        margin-bottom: 20px !important;
    }
    .card-header {
        background: #f8f9fa !important;
        border-bottom: 2px solid #000 !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .table {
        font-size: 9pt;
    }
    .table th, .table td {
        border: 1px solid #000 !important;
        padding: 6px !important;
    }
    .table thead th {
        background: #f8f9fa !important;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
    .badge {
        border: 1px solid #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endsection

