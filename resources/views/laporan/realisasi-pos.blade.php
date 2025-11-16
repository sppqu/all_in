@extends('layouts.adminty')

@section('title', 'Realisasi POS Pembayaran')

@section('content')
<!-- Inline Script Test -->
<script>
console.log('=== REALISASI POS SCRIPT TEST ===');
console.log('Testing function availability...');
console.log('filterData:', typeof filterData);
console.log('exportExcel:', typeof exportExcel);
console.log('exportPdf:', typeof exportPdf);

// Test function creation
if (typeof filterData !== 'function') {
    console.log('Creating fallback filterData function...');
    window.filterData = function() {
        const periodId = document.getElementById('period_id').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const classId = document.getElementById('class_id').value;
        console.log('Fallback filterData called with:', { periodId, startDate, endDate, classId });
        let params = `period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
        if (classId) {
            params += `&class_id=${classId}`;
        }
        window.location.href = `{{ route('manage.laporan.realisasi-pos') }}?${params}`;
    };
}

if (typeof exportExcel !== 'function') {
    console.log('Creating fallback exportExcel function...');
    window.exportExcel = function() {
        const periodId = document.getElementById('period_id').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const classId = document.getElementById('class_id').value;
        console.log('Fallback exportExcel called with:', { periodId, startDate, endDate, classId });
        let params = `period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
        if (classId) {
            params += `&class_id=${classId}`;
        }
        window.location.href = `{{ route('manage.laporan.realisasi-pos.export-excel') }}?${params}`;
    };
}

if (typeof exportPdf !== 'function') {
    console.log('Creating fallback exportPdf function...');
    window.exportPdf = function() {
        const periodId = document.getElementById('period_id').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const classId = document.getElementById('class_id').value;
        console.log('Fallback exportPdf called with:', { periodId, startDate, endDate, classId });
        let params = `period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
        if (classId) {
            params += `&class_id=${classId}`;
        }
        window.location.href = `{{ route('manage.laporan.realisasi-pos.export-pdf') }}?${params}`;
    };
}

console.log('Final function availability:');
console.log('filterData:', typeof filterData);
console.log('exportExcel:', typeof exportExcel);
console.log('exportPdf:', typeof exportPdf);
console.log('=== END TEST ===');
</script>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">Realisasi POS Pembayaran</h4>
                </div>
                <div class="card-body">

                    
                    <!-- Error Message -->
                    @if(isset($error))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ $error }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif
                    
                    <!-- Filter Section -->
                    <div class="row mb-4">
                        <div class="col-md-2">
                            <label for="period_id" class="form-label">Tahun Pelajaran</label>
                            <select class="form-select" id="period_id" name="period_id">
                                @if($periods && $periods->count() > 0)
                                    @foreach($periods as $period)
                                        <option value="{{ $period->period_id }}" {{ $selectedPeriod == $period->period_id ? 'selected' : '' }}>
                                            {{ $period->period_start }}/{{ $period->period_end }}
                                        </option>
                                    @endforeach
                                @else
                                    <option value="">Tidak ada data periode</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="class_id" class="form-label">Kelas</label>
                            <select class="form-select" id="class_id" name="class_id">
                                <option value="">Semua Kelas</option>
                                @if($classes && $classes->count() > 0)
                                    @foreach($classes as $class)
                                        <option value="{{ $class->class_id }}" {{ ($selectedClass ?? '') == $class->class_id ? 'selected' : '' }}>
                                            {{ $class->class_name }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="start_date" class="form-label">Tanggal Mulai</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" value="{{ $startDate ?? date('Y-m-01') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="end_date" class="form-label">Tanggal Akhir</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" value="{{ $endDate ?? date('Y-m-d') }}">
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="button" class="btn btn-primary" onclick="if(typeof filterData === 'function') { filterData(); } else { console.error('filterData function not found'); alert('Filter function not loaded. Please refresh the page.'); }">
                                <i class="fas fa-filter me-2"></i>Filter
                            </button>
                        </div>
                    </div>

                    <!-- Table Section -->
                    <div class="table-responsive">
                        <table class="table table-bordered" style="width: 100%; border-collapse: collapse;">
                            <thead>
                                <tr style="background-color: #f8f9fa;">
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">No.</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">Kelas</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: left;">POS Pembayaran</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Target</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Terbayar</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Belum Terbayar</th>
                                    <th style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">Pencapaian</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($realisasiData as $index => $data)
                                    @if(isset($data['is_total']) && $data['is_total'])
                                        <!-- Total Row -->
                                        <tr style="background-color: #e8f5e8; font-weight: bold;">
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px;">{{ $selectedClass ? ($classes->where('class_id', $selectedClass)->first()->class_name ?? '') : 'Semua Kelas' }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px;">{{ $data['pos_name'] }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Rp {{ number_format($data['target'] ?? $data['tagihan'], 0, ',', '.') }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Rp {{ number_format($data['terbayar'], 0, ',', '.') }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Rp {{ number_format($data['belum_terbayar'], 0, ',', '.') }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">
                                                <!-- Progress Bar Total dengan Warna -->
                                                <div class="progress-container" style="width: 100%;">
                                                    <div class="progress-bar" 
                                                         style="width: {{ $data['pencapaian'] }}%; 
                                                                height: 20px; 
                                                                background: {{ $data['pencapaian'] >= 80 ? '#28a745' : ($data['pencapaian'] >= 50 ? '#ffc107' : '#dc3545') }};
                                                                border-radius: 4px;
                                                                position: relative;
                                                                min-width: 30px;
                                                                border: 2px solid #198754;">
                                                        <span style="position: absolute; 
                                                                   top: 50%; 
                                                                   left: 50%; 
                                                                   transform: translate(-50%, -50%);
                                                                   color: {{ $data['pencapaian'] >= 80 ? 'white' : ($data['pencapaian'] >= 50 ? 'black' : 'white') }};
                                                                   font-weight: bold;
                                                                   font-size: 12px;
                                                                   text-shadow: 1px 1px 1px rgba(0,0,0,0.5);">
                                                            {{ $data['pencapaian'] }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <!-- Data Row -->
                                        <tr>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">{{ $index + 1 }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px;">{{ $selectedClass ? ($classes->where('class_id', $selectedClass)->first()->class_name ?? '') : 'Semua Kelas' }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px;">{{ $data['pos_name'] }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Rp {{ number_format($data['target'] ?? $data['tagihan'], 0, ',', '.') }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Rp {{ number_format($data['terbayar'], 0, ',', '.') }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: right;">Rp {{ number_format($data['belum_terbayar'], 0, ',', '.') }}</td>
                                            <td style="border: 1px solid #dee2e6; padding: 8px; text-align: center;">
                                                <!-- Progress Bar dengan Warna -->
                                                <div class="progress-container" style="width: 100%;">
                                                    <div class="progress-bar" 
                                                         style="width: {{ $data['pencapaian'] }}%; 
                                                                height: 20px; 
                                                                background: {{ $data['pencapaian'] >= 80 ? '#28a745' : ($data['pencapaian'] >= 50 ? '#ffc107' : '#dc3545') }};
                                                                border-radius: 4px;
                                                                position: relative;
                                                                min-width: 30px;
                                                                border: 2px solid #198754;">
                                                        <span style="position: absolute; 
                                                                   top: 50%; 
                                                                   left: 50%; 
                                                                   transform: translate(-50%, -50%);
                                                                   color: {{ $data['pencapaian'] >= 80 ? 'white' : ($data['pencapaian'] >= 50 ? 'black' : 'white') }};
                                                                   font-weight: bold;
                                                                   font-size: 12px;
                                                                   text-shadow: 1px 1px 1px rgba(0,0,0,0.5);">
                                                            {{ $data['pencapaian'] }}%
                                                        </span>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Legend Warna Pencapaian -->
                    <div class="achievement-legend">
                        <h6 class="mb-3"><i class="fas fa-info-circle me-2"></i>Keterangan Warna Pencapaian:</h6>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #28a745;"></span>
                            <span class="legend-text">🟢 Excellent (≥80%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #ffc107;"></span>
                            <span class="legend-text">🟡 Good (50-79%)</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-color" style="background-color: #dc3545;"></span>
                            <span class="legend-text">🔴 Need Improvement (<50%)</span>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="row mt-4">
                        <div class="col-12">
                            <button type="button" class="btn btn-success text-white me-2" onclick="if(typeof exportExcel === 'function') { exportExcel(); } else { console.error('exportExcel function not found'); alert('Export Excel function not loaded. Please refresh the page.'); }">
                                <i class="fas fa-file-excel me-2"></i>Export Excel
                            </button>
                            <button type="button" class="btn btn-danger text-white" onclick="if(typeof exportPdf === 'function') { exportPdf(); } else { console.error('exportPdf function not found'); alert('Export PDF function not loaded. Please refresh the page.'); }">
                                <i class="fas fa-file-pdf me-2"></i>Export PDF
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Loading Modal -->
<div class="modal fade" id="loadingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-body text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2 mb-0">Memproses data...</p>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Pastikan fungsi tersedia secara global dan di-load dengan benar
(function() {
    'use strict';
    
    // Fungsi Filter Data
    function filterData() {
        const periodId = document.getElementById('period_id').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const classId = document.getElementById('class_id').value;
        
        console.log('Filtering data with:', { periodId, startDate, endDate, classId });
        
        // Build query parameters
        let params = `period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
        if (classId) {
            params += `&class_id=${classId}`;
        }
        
        // Redirect dengan parameter
        window.location.href = `{{ route('manage.laporan.realisasi-pos') }}?${params}`;
    }
    
    // Fungsi Toggle Class Filter - Always show class filter
    function toggleClassFilter() {
        // Class filter is always visible now
        console.log('Class filter is always visible');
    }
    
    // Fungsi Export Excel
    function exportExcel() {
        const periodId = document.getElementById('period_id').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const classId = document.getElementById('class_id').value;
        
        console.log('Exporting Excel with:', { periodId, startDate, endDate, classId });
        
        // Show loading
        try {
            if (typeof $ !== 'undefined' && $('#loadingModal').length) {
                $('#loadingModal').modal('show');
            }
        } catch (e) {
            console.log('Modal loading error:', e);
        }
        
        // Build query parameters
        let params = `period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
        if (classId) {
            params += `&class_id=${classId}`;
        }
        
        // Redirect ke export Excel
        window.location.href = `{{ route('manage.laporan.realisasi-pos.export-excel') }}?${params}`;
    }
    
    // Fungsi Export PDF
    function exportPdf() {
        const periodId = document.getElementById('period_id').value;
        const startDate = document.getElementById('start_date').value;
        const endDate = document.getElementById('end_date').value;
        const classId = document.getElementById('class_id').value;
        
        console.log('Exporting PDF with:', { periodId, startDate, endDate, classId });
        
        // Show loading
        try {
            if (typeof $ !== 'undefined' && $('#loadingModal').length) {
                $('#loadingModal').modal('show');
            }
        } catch (e) {
            console.log('Modal loading error:', e);
        }
        
        // Build query parameters
        let params = `period_id=${periodId}&start_date=${startDate}&end_date=${endDate}`;
        if (classId) {
            params += `&class_id=${classId}`;
        }
        
        // Redirect ke export PDF
        window.location.href = `{{ route('manage.laporan.realisasi-pos.export-pdf') }}?${params}`;
    }
    
    // Expose functions to global scope
    window.filterData = filterData;
    window.exportExcel = exportExcel;
    window.exportPdf = exportPdf;
    
    // Auto-hide loading modal after page load
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, functions available:', {
            filterData: typeof window.filterData,
            exportExcel: typeof window.exportExcel,
            exportPdf: typeof window.exportPdf
        });
        
        // Class filter is always visible now
        console.log('Class filter visibility initialized');
        
        try {
            if (typeof $ !== 'undefined' && $('#loadingModal').length) {
                $('#loadingModal').modal('hide');
            }
        } catch (e) {
            console.log('Modal hide error:', e);
        }
    });
    
    // Fallback jika jQuery tidak tersedia
    if (typeof $ === 'undefined') {
        console.log('jQuery not available, using vanilla JS');
    }
    
    console.log('Realisasi POS JavaScript loaded successfully');
})();
</script>
@endpush

@push('styles')
<link rel="stylesheet" href="{{ asset('css/realisasi-pos.css') }}">
<style>
/* Progress Bar Styling */
.progress-container {
    background-color: #f8f9fa;
    border-radius: 4px;
    overflow: hidden;
    box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
}

.progress-bar {
    transition: width 0.6s ease;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
}

.progress-bar:hover {
    transform: scale(1.02);
    transition: transform 0.2s ease;
}

/* Legend untuk warna */
.achievement-legend {
    margin-top: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.legend-item {
    display: inline-block;
    margin-right: 20px;
    margin-bottom: 10px;
}

.legend-color {
    display: inline-block;
    width: 20px;
    height: 20px;
    border-radius: 4px;
    margin-right: 8px;
    vertical-align: middle;
}

.legend-text {
    font-size: 14px;
    color: #495057;
}
</style>
@endpush
