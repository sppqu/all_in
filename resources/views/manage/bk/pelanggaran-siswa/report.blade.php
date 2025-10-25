@extends('layouts.coreui')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Rekap Pelanggaran Per Kelas
                        </h5>
                        <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Summary Cards - Modern Design -->
                    <div class="row g-4 mb-4">
                        <div class="col-md-3">
                            <div class="modern-card modern-card-blue">
                                <div class="modern-card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="card-label mb-2">Total Siswa</p>
                                            <h2 class="card-value mb-0">{{ $students->count() }}</h2>
                                            <small class="card-subtitle">Terdaftar</small>
                                        </div>
                                        <div class="icon-wrapper icon-blue">
                                            <i class="fas fa-users"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="modern-card modern-card-red">
                                <div class="modern-card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="card-label mb-2">Siswa Bermasalah</p>
                                            <h2 class="card-value mb-0">{{ $students->where('jumlah_pelanggaran', '>', 0)->count() }}</h2>
                                            <small class="card-subtitle">Siswa</small>
                                        </div>
                                        <div class="icon-wrapper icon-red">
                                            <i class="fas fa-exclamation-triangle"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="modern-card modern-card-orange">
                                <div class="modern-card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="card-label mb-2">Total Pelanggaran</p>
                                            <h2 class="card-value mb-0">{{ $students->sum('jumlah_pelanggaran') }}</h2>
                                            <small class="card-subtitle">Kasus</small>
                                        </div>
                                        <div class="icon-wrapper icon-orange">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-3">
                            <div class="modern-card modern-card-purple">
                                <div class="modern-card-body">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <p class="card-label mb-2">Total Point</p>
                                            <h2 class="card-value mb-0">{{ number_format($students->sum('total_point')) }}</h2>
                                            <small class="card-subtitle">Poin</small>
                                        </div>
                                        <div class="icon-wrapper icon-purple">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Form -->
                    <div class="card mb-4 border-info">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0"><i class="fas fa-filter me-2"></i>Filter Laporan</h6>
                        </div>
                        <div class="card-body">
                            <form method="GET" action="{{ route('manage.bk.pelanggaran-siswa.report') }}" id="filterForm">
                                <div class="row">
                                    <div class="col-md-4">
                                        <label class="form-label">Filter Kelas</label>
                                        <select name="kelas_id" class="form-select" onchange="document.getElementById('filterForm').submit()">
                                            <option value="">-- Semua Kelas --</option>
                                            @foreach($classes as $class)
                                                <option value="{{ $class->class_id }}" {{ $kelasId == $class->class_id ? 'selected' : '' }}>
                                                    {{ $class->class_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-8 d-flex align-items-end">
                                        <button type="submit" class="btn btn-primary me-2">
                                            <i class="fas fa-search"></i> Tampilkan
                                        </button>
                                        <a href="{{ route('manage.bk.pelanggaran-siswa.report') }}" class="btn btn-secondary">
                                            <i class="fas fa-redo"></i> Reset Filter
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div class="mb-3">
                        <a href="{{ route('manage.bk.pelanggaran-siswa.report.export-pdf', ['kelas_id' => $kelasId]) }}" 
                           class="btn btn-outline-danger btn-sm" target="_blank">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                        <button onclick="exportToExcel()" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </button>
                    </div>

                    <!-- Table -->
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="reportTable">
                            <thead class="table-light">
                                <tr>
                                    <th width="5%" class="text-center">Rank</th>
                                    <th width="10%">NIS</th>
                                    <th width="25%">Nama Siswa</th>
                                    <th width="15%">Kelas</th>
                                    <th width="15%" class="text-center">Jumlah Pelanggaran</th>
                                    <th width="15%" class="text-center">Total Point</th>
                                    <th width="15%" class="text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $index => $student)
                                <tr>
                                    <td class="text-center">
                                        @if($index == 0 && $student->total_point > 0)
                                            <i class="fas fa-crown text-warning fs-5"></i>
                                        @else
                                            {{ $index + 1 }}
                                        @endif
                                    </td>
                                    <td>{{ $student->student_nis }}</td>
                                    <td>
                                        <strong>{{ $student->student_full_name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $student->student_phone ?? '-' }}</small>
                                    </td>
                                    <td>
                                        @if($student->class)
                                            {{ $student->class->class_name }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($student->jumlah_pelanggaran > 0)
                                            <span class="badge bg-warning fs-6">{{ $student->jumlah_pelanggaran }}</span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($student->total_point > 0)
                                            <span class="badge bg-danger fs-5">{{ $student->total_point }}</span>
                                        @else
                                            <span class="text-success"><i class="fas fa-check-circle"></i> 0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($student->total_point == 0)
                                            <span class="badge bg-success">Baik</span>
                                        @elseif($student->total_point < 50)
                                            <span class="badge bg-warning">Perlu Perhatian</span>
                                        @elseif($student->total_point < 100)
                                            <span class="badge bg-orange">Bermasalah</span>
                                        @else
                                            <span class="badge bg-danger">Sangat Bermasalah</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                        <p class="text-muted">Belum ada data</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Modern Card Styles */
.modern-card {
    border: none;
    border-radius: 16px;
    overflow: hidden;
    transition: all 0.3s ease;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
    height: 100%;
}

.modern-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.modern-card-body {
    padding: 1.5rem;
}

/* Card Variants with Gradients */
.modern-card-blue {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
}

.modern-card-red {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.modern-card-orange {
    background: linear-gradient(135deg, #ffa751 0%, #ffe259 100%);
    color: white;
}

.modern-card-purple {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    color: white;
}

/* Card Content */
.card-label {
    font-size: 0.875rem;
    font-weight: 500;
    opacity: 0.9;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.card-value {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1;
    margin-bottom: 0.25rem;
}

.card-subtitle {
    font-size: 0.75rem;
    opacity: 0.8;
    font-weight: 400;
}

/* Icon Wrapper */
.icon-wrapper {
    width: 56px;
    height: 56px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
}

.modern-card:hover .icon-wrapper {
    transform: scale(1.1) rotate(5deg);
}

/* Responsive */
@media (max-width: 768px) {
    .card-value {
        font-size: 2rem;
    }
    
    .icon-wrapper {
        width: 48px;
        height: 48px;
        font-size: 20px;
    }
}

@media print {
    .btn, .card-header .btn-group, nav, #filterForm, .border-info { display: none !important; }
    .card { border: none; box-shadow: none; }
    body { print-color-adjust: exact; -webkit-print-color-adjust: exact; }
    .modern-card {
        box-shadow: none !important;
        page-break-inside: avoid;
    }
}

.bg-orange {
    background-color: #ff9800 !important;
}

.border-info {
    border-color: #17a2b8 !important;
}

.bg-info {
    background-color: #17a2b8 !important;
}
</style>

<script>
function exportToExcel() {
    const table = document.getElementById('reportTable');
    const ws = XLSX.utils.table_to_sheet(table);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, 'Rekap Pelanggaran');
    XLSX.writeFile(wb, 'Rekap_Pelanggaran_Siswa_' + new Date().toISOString().slice(0,10) + '.xlsx');
}
</script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@endsection

