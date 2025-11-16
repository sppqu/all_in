@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1" style="font-size: 1.5rem;">
                <i class="feather icon-list me-2"></i>Lihat Jurnal
            </h4>
            <p class="text-muted mb-0" style="font-size: 0.9rem;">Daftar semua jurnal harian siswa</p>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="feather icon-filter"></i> Filter Jurnal
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('jurnal.guru.lihat') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="feather icon-user"></i> Pilih Siswa
                        </label>
                        <select name="siswa_id" class="form-control select-primary" id="siswa_id">
                            <option value="">-- Semua Siswa --</option>
                            @foreach($students ?? [] as $student)
                                <option value="{{ $student->student_id }}" {{ request('siswa_id') == $student->student_id ? 'selected' : '' }}>
                                    {{ $student->student_nis }} - {{ $student->student_full_name }}
                                    @if($student->class)
                                        ({{ $student->class->class_name }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-control select-primary">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Pending Verifikasi</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Terverifikasi</option>
                            <option value="revised" {{ request('status') == 'revised' ? 'selected' : '' }}>Perlu Revisi</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Kelas</label>
                        <select name="kelas_id" class="form-control select-primary">
                            <option value="">Semua Kelas</option>
                            @foreach($classes ?? [] as $class)
                                <option value="{{ $class->class_id }}" {{ request('kelas_id') == $class->class_id ? 'selected' : '' }}>
                                    {{ $class->class_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="{{ request('tanggal_dari') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="{{ request('tanggal_sampai') }}">
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12 d-flex justify-content-end">
                        <a href="{{ route('jurnal.guru.lihat') }}" class="btn btn-outline-secondary mr-2" style="border-color: #dee2e6;">
                            <i class="feather icon-refresh-cw"></i> Reset
                        </a>
                        <button type="submit" class="btn" style="background-color: #01a9ac; border-color: #01a9ac; color: #ffffff;">
                            <i class="feather icon-filter"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table Section -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="feather icon-list"></i> Daftar Jurnal
                </h5>
                <div class="btn-group">
                    <button type="button" class="btn btn-danger btn-sm" onclick="exportPdf()">
                        <i class="feather icon-file-text"></i> Unduh Laporan PDF
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            @if($jurnals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Siswa</th>
                                <th>Kelas</th>
                                <th>Status</th>
                                <th>Jumlah Entry</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jurnals as $index => $jurnal)
                                <tr>
                                    <td>{{ ($jurnals->currentPage() - 1) * $jurnals->perPage() + $index + 1 }}</td>
                                    <td>{{ \Carbon\Carbon::parse($jurnal->tanggal)->format('d/m/Y') }}</td>
                                    <td>
                                        <strong>{{ $jurnal->siswa->student_full_name ?? '-' }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $jurnal->siswa->student_nis ?? '-' }}</small>
                                    </td>
                                    <td>{{ $jurnal->siswa->class->class_name ?? '-' }}</td>
                                    <td>
                                        @if($jurnal->status == 'verified')
                                            <span class="badge badge-success">Terverifikasi</span>
                                        @elseif($jurnal->status == 'submitted')
                                            <span class="badge badge-warning">Pending Verifikasi</span>
                                        @elseif($jurnal->status == 'revised')
                                            <span class="badge badge-danger">Perlu Revisi</span>
                                        @else
                                            <span class="badge badge-secondary">Draft</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $jurnal->entries->count() ?? 0 }} Entry</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('jurnal.guru.show', $jurnal->id) }}" class="btn btn-sm btn-primary">
                                            <i class="feather icon-eye"></i> Detail
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $jurnals->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="feather icon-inbox" style="font-size: 4rem; color: #dee2e6;"></i>
                    <p class="text-muted mt-3">Tidak ada data jurnal ditemukan</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    /* Select2 height */
    .select2-container .select2-selection--single {
        height: 35px;
    }
    
    /* Remove background color from Select2 rendered */
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        background-color: transparent !important;
        padding: 1px 30px 8px 20px;
    }
</style>
@endpush

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
function exportPdf() {
    // Get current filter parameters
    var params = new URLSearchParams(window.location.search);
    var siswaId = params.get('siswa_id');
    
    // Check if siswa is selected
    if (!siswaId || siswaId === '') {
        if (typeof showToast === 'function') {
            showToast('warning', 'Peringatan', 'Silakan pilih siswa terlebih dahulu untuk mengunduh laporan PDF.');
        } else {
            alert('Silakan pilih siswa terlebih dahulu untuk mengunduh laporan PDF.');
        }
        return;
    }
    
    // Build export URL
    var exportUrl = '{{ route("jurnal.guru.laporan-siswa-pdf") }}?' + params.toString();
    
    // Open in new window
    window.open(exportUrl, '_blank');
}

$(document).ready(function() {
    // Wait a bit to ensure all template scripts are loaded
    setTimeout(function() {
        // Initialize Select2 for student dropdown
        if (typeof $.fn.select2 !== 'undefined') {
            $('#siswa_id').select2({
                placeholder: '-- Semua Siswa --',
                allowClear: true,
                width: '100%'
            });
        }
    }, 100);
});
</script>
@endsection

