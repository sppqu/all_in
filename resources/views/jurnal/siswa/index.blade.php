@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-book me-2 text-primary"></i>E-Jurnal Harian 7KAIH
            </h2>
            <p class="text-muted mb-0">7 Kebiasaan Anak Indonesia Hebat</p>
        </div>
        <div>
            <a href="{{ route('jurnal.siswa.create') }}" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Isi Jurnal Hari Ini
            </a>
            <a href="{{ route('jurnal.siswa.rekap-bulanan') }}" class="btn btn-info">
                <i class="fas fa-chart-line me-2"></i>Rekap Bulanan
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Total Jurnal</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['total_jurnal'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-book-open"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Bulan Ini</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['jurnal_bulan_ini'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Terverifikasi</h6>
                            <h2 class="mb-0 fw-bold">{{ $stats['jurnal_terverifikasi'] }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1">Rata-rata Nilai</h6>
                            <h2 class="mb-0 fw-bold">{{ number_format($stats['rata_rata_nilai'], 1) }}</h2>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Jurnals -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-history me-2"></i>Jurnal Terbaru</h5>
        </div>
        <div class="card-body">
            @if($recentJurnals->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Tanggal</th>
                                <th>Jumlah Kegiatan</th>
                                <th>Total Nilai</th>
                                <th>Rata-rata</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentJurnals as $jurnal)
                            <tr>
                                <td>
                                    <strong>{{ $jurnal->tanggal->format('d M Y') }}</strong><br>
                                    <small class="text-muted">{{ $jurnal->tanggal->format('l') }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $jurnal->entries->count() }} kegiatan</span>
                                </td>
                                <td>
                                    <strong>{{ $jurnal->total_nilai }}</strong>
                                </td>
                                <td>
                                    @php
                                        $avg = $jurnal->rata_rata_nilai;
                                        $badgeClass = $avg >= 8 ? 'success' : ($avg >= 6 ? 'info' : ($avg >= 4 ? 'warning' : 'danger'));
                                    @endphp
                                    <span class="badge bg-{{ $badgeClass }}">{{ number_format($avg, 1) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $jurnal->status_badge }}">
                                        {{ ucfirst($jurnal->status) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('jurnal.siswa.show', $jurnal->jurnal_id) }}" 
                                           class="btn btn-info" title="Lihat">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($jurnal->status != 'verified')
                                        <a href="{{ route('jurnal.siswa.edit', $jurnal->jurnal_id) }}" 
                                           class="btn btn-warning" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endif
                                        @if($jurnal->status == 'draft')
                                        <form action="{{ route('jurnal.siswa.destroy', $jurnal->jurnal_id) }}" 
                                              method="POST" 
                                              onsubmit="return confirm('Yakin ingin menghapus jurnal ini?');"
                                              class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-book fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Belum ada jurnal. Mulai isi jurnal hari ini!</p>
                    <a href="{{ route('jurnal.siswa.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus-circle me-2"></i>Isi Jurnal Sekarang
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
</style>
@endsection

