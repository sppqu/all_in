@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="mb-4">
        <a href="{{ route('jurnal.guru.index') }}" class="btn btn-outline-secondary btn-sm mb-2">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <h1 class="h3 mb-1 fw-bold" style="color: #6f42c1;">
            <i class="fas fa-file-alt me-2"></i>Laporan Jurnal Per Siswa
        </h1>
        <p class="text-muted mb-0">Laporan jurnal harian berdasarkan siswa dan range tanggal</p>
    </div>

    <!-- Filter Form -->
    <div class="card border-0 shadow-sm mb-4 no-print">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Laporan
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('jurnal.guru.laporan-siswa') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-1"></i>Pilih Siswa <span class="text-danger">*</span>
                        </label>
                        <select name="siswa_id" class="form-control" id="siswa_id" required>
                            <option value="">-- Pilih Siswa --</option>
                            @foreach($students as $student)
                                <option value="{{ $student->student_id }}" {{ request('siswa_id') == $student->student_id ? 'selected' : '' }}>
                                    {{ $student->student_nis }} - {{ $student->student_full_name }}
                                    @if($student->class)
                                        ({{ $student->class->class_name }})
                                    @endif
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
        <!-- Student Info -->
        @if($siswa)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="fw-bold mb-1">{{ $siswa->student_full_name }}</h5>
                        <p class="text-muted mb-0">
                            <i class="fas fa-id-card me-1"></i>NIS: {{ $siswa->student_nis }} | 
                            <i class="fas fa-school me-1"></i>Kelas: {{ $siswa->class->class_name ?? '-' }}
                        </p>
                    </div>
                    <div class="col-md-4 text-end no-print">
                        <a href="{{ route('jurnal.guru.laporan-siswa-pdf', ['siswa_id' => request('siswa_id'), 'tanggal_dari' => request('tanggal_dari'), 'tanggal_sampai' => request('tanggal_sampai')]) }}" 
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
                        <small class="text-muted">Total Jurnal</small>
                        <h3 class="fw-bold mb-0">{{ $laporan->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #28a745 !important;">
                    <div class="card-body">
                        <small class="text-muted">Terverifikasi</small>
                        <h3 class="fw-bold mb-0">{{ $laporan->where('status', 'verified')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #ffc107 !important;">
                    <div class="card-body">
                        <small class="text-muted">Pending</small>
                        <h3 class="fw-bold mb-0">{{ $laporan->where('status', 'submitted')->count() }}</h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm" style="border-left: 4px solid #6f42c1 !important;">
                    <div class="card-body">
                        <small class="text-muted">Total Kegiatan</small>
                        <h3 class="fw-bold mb-0">{{ $laporan->sum(function($j) { return $j->entries->count(); }) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Jurnal Tables per Kategori -->
        @if($laporan->count() > 0)
            @php
                // Group entries by kategori across all jurnals
                $kategoriList = \App\Models\JurnalKategori::where('is_active', true)->orderBy('urutan')->get();
                
                $dataPerKategori = [];
                foreach ($kategoriList as $kat) {
                    $dataPerKategori[$kat->kategori_id] = [];
                }
                
                foreach ($laporan as $jurnal) {
                    foreach ($jurnal->entries as $entry) {
                        $dataPerKategori[$entry->kategori_id][] = [
                            'tanggal' => $jurnal->tanggal,
                            'entry' => $entry,
                            'jurnal' => $jurnal
                        ];
                    }
                }
            @endphp

            @foreach($kategoriList as $kategori)
                @if(isset($dataPerKategori[$kategori->kategori_id]) && count($dataPerKategori[$kategori->kategori_id]) > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3" style="background: {{ $kategori->warna }}20; border-left: 5px solid {{ $kategori->warna }};">
                        <h5 class="mb-0 fw-bold" style="color: {{ $kategori->warna }};">
                            <i class="{{ $kategori->icon }} me-2"></i>{{ $kategori->nama_kategori }}
                        </h5>
                        <small class="text-muted">{{ $kategori->deskripsi }}</small>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th width="5%" class="text-center">No</th>
                                        <th width="15%">Tanggal</th>
                                        @if(in_array($kategori->kode, ['BANGUN', 'OLAHRAGA', 'TIDUR']))
                                            <th width="10%">Jam</th>
                                        @endif
                                        <th>Keterangan</th>
                                        <th width="15%" class="text-center">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataPerKategori[$kategori->kategori_id] as $index => $item)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>{{ \Carbon\Carbon::parse($item['tanggal'])->isoFormat('DD MMM YYYY') }}</td>
                                        
                                        @if(in_array($kategori->kode, ['BANGUN', 'OLAHRAGA', 'TIDUR']))
                                            <td>{{ $item['entry']->jam ?? '-' }}</td>
                                        @endif
                                        
                                        <td>
                                            @php
                                                $entry = $item['entry'];
                                                $checklistData = $entry->checklist_data ? json_decode($entry->checklist_data, true) : [];
                                            @endphp
                                            
                                            @if($kategori->kode == 'IBADAH')
                                                @php
                                                    $sholatList = [];
                                                    if(isset($checklistData['subuh'])) $sholatList[] = 'Subuh';
                                                    if(isset($checklistData['dzuhur'])) $sholatList[] = 'Dzuhur';
                                                    if(isset($checklistData['asar'])) $sholatList[] = 'Asar';
                                                    if(isset($checklistData['magrib'])) $sholatList[] = 'Magrib';
                                                    if(isset($checklistData['isya'])) $sholatList[] = 'Isya';
                                                @endphp
                                                <div class="mb-1">
                                                    <strong>Sholat:</strong> {{ count($sholatList) > 0 ? implode(', ', $sholatList) : '-' }}
                                                </div>
                                            @elseif($kategori->kode == 'OLAHRAGA')
                                                @if(isset($checklistData['berolahraga']))
                                                    <span class="badge bg-success mb-1">
                                                        <i class="fas fa-check me-1"></i>Berolahraga
                                                    </span>
                                                @endif
                                            @elseif($kategori->kode == 'MAKAN')
                                                @php
                                                    $makanList = [];
                                                    if(isset($checklistData['pagi'])) $makanList[] = 'Pagi';
                                                    if(isset($checklistData['siang'])) $makanList[] = 'Siang';
                                                    if(isset($checklistData['malam'])) $makanList[] = 'Malam';
                                                @endphp
                                                <div class="mb-1">
                                                    <strong>Makan:</strong> {{ count($makanList) > 0 ? implode(', ', $makanList) : '-' }}
                                                </div>
                                            @elseif($kategori->kode == 'MEMBACA')
                                                @if(isset($checklistData['belajar']))
                                                    <span class="badge bg-info mb-1">
                                                        <i class="fas fa-check me-1"></i>Belajar/Membaca
                                                    </span>
                                                @endif
                                            @elseif($kategori->kode == 'SOSIAL')
                                                @php
                                                    $sosialList = [];
                                                    if(isset($checklistData['keluarga'])) $sosialList[] = 'Keluarga';
                                                    if(isset($checklistData['teman'])) $sosialList[] = 'Teman';
                                                    if(isset($checklistData['tetangga'])) $sosialList[] = 'Tetangga';
                                                @endphp
                                                @if(count($sosialList) > 0)
                                                <div class="mb-1">
                                                    <strong>Dengan:</strong> {{ implode(', ', $sosialList) }}
                                                </div>
                                                @endif
                                            @endif
                                            
                                            @if($entry->keterangan)
                                                <div class="text-muted small">{{ $entry->keterangan }}</div>
                                            @endif
                                        </td>
                                        
                                        <td class="text-center">
                                            @if($item['jurnal']->status == 'verified')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Terverifikasi
                                                </span>
                                            @elseif($item['jurnal']->status == 'submitted')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            @elseif($item['jurnal']->status == 'revised')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-exclamation-circle me-1"></i>Revisi
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-edit me-1"></i>Draft
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                @endif
            @endforeach
        @else
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">Tidak ada jurnal ditemukan untuk periode ini</p>
                </div>
            </div>
        @endif
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="fas fa-search text-muted" style="font-size: 4rem;"></i>
                <p class="text-muted mt-3 mb-0">Pilih siswa dan klik "Tampilkan" untuk melihat laporan</p>
            </div>
        </div>
    @endif
</div>

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}

@media print {
    .btn, nav, .sidebar, .no-print, a[href*="kembali"], .alert-info {
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
        font-size: 10pt;
    }
    .table th, .table td {
        border: 1px solid #000 !important;
        padding: 8px !important;
    }
    .badge {
        border: 1px solid #000;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
    }
}
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function() {
    if (typeof jQuery === 'undefined') {
        console.error('jQuery not loaded!');
        return;
    }

    jQuery(document).ready(function($) {
        var $select = $('#siswa_id');
        
        if ($select.length) {
            $select.select2({
                placeholder: '-- Pilih Siswa --',
                allowClear: true,
                width: '100%'
            });
        }
    });
})();
</script>
@endpush
@endsection

