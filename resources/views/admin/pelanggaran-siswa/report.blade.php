@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-bar me-2"></i>Laporan Rekap Pelanggaran Per Siswa
                        </h5>
                        <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Summary Cards -->
                    <div class="row mb-4">
                        <div class="col-md-3">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Siswa</h6>
                                    <h3 class="mb-0">{{ $students->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-danger text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Siswa Bermasalah</h6>
                                    <h3 class="mb-0">{{ $students->where('jumlah_pelanggaran', '>', 0)->count() }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-warning text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Pelanggaran</h6>
                                    <h3 class="mb-0">{{ $students->sum('jumlah_pelanggaran') }}</h3>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card bg-info text-white">
                                <div class="card-body">
                                    <h6 class="card-title">Total Point</h6>
                                    <h3 class="mb-0">{{ number_format($students->sum('total_point')) }}</h3>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Button -->
                    <div class="mb-3">
                        <button onclick="window.print()" class="btn btn-success">
                            <i class="fas fa-print"></i> Cetak Laporan
                        </button>
                        <button onclick="exportToExcel()" class="btn btn-success">
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
@media print {
    .btn, .card-header .btn-group, nav { display: none; }
    .card { border: none; box-shadow: none; }
}

.bg-orange {
    background-color: #ff9800 !important;
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

