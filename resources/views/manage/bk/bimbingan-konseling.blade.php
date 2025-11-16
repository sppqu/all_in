@extends('layouts.adminty')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1 fw-bold text-dark">
            <i class="fas fa-user-friends me-2 text-info"></i>Bimbingan Konseling
        </h2>
        <p class="text-muted mb-0">Daftar siswa yang memerlukan bimbingan konseling berdasarkan pelanggaran</p>
    </div>

    <!-- Info Card -->
    <div class="row g-4 mb-4">
        <div class="col-md-12">
            <div class="alert alert-info border-0 shadow-sm">
                <div class="d-flex align-items-center">
                    <i class="fas fa-info-circle fa-2x me-3"></i>
                    <div>
                        <h6 class="mb-1 fw-semibold">Kriteria Siswa Perlu Bimbingan</h6>
                        <p class="mb-0 small">Siswa dengan <strong>total poin pelanggaran ≥ 25</strong> memerlukan perhatian khusus dan bimbingan konseling.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body position-relative" style="padding-right: 90px;">
                    <div>
                        <p class="text-muted mb-1 small">Total Siswa Perlu Bimbingan</p>
                        <h3 class="mb-0 fw-bold text-danger">{{ $siswaPerluBimbingan->count() }}</h3>
                    </div>
                    <div class="stat-icon" style="background-color: #dc3545; border-radius: 8px;">
                        <i class="fas fa-user-friends fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body position-relative" style="padding-right: 90px;">
                    <div>
                        <p class="text-muted mb-1 small">Rata-rata Point</p>
                        <h3 class="mb-0 fw-bold text-warning">
                            {{ $siswaPerluBimbingan->count() > 0 ? round($siswaPerluBimbingan->avg('total_point')) : 0 }}
                        </h3>
                    </div>
                    <div class="stat-icon" style="background-color: #ffc107; border-radius: 8px;">
                        <i class="fas fa-chart-line fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body position-relative" style="padding-right: 90px;">
                    <div>
                        <p class="text-muted mb-1 small">Point Tertinggi</p>
                        <h3 class="mb-0 fw-bold text-danger">
                            {{ $siswaPerluBimbingan->count() > 0 ? $siswaPerluBimbingan->max('total_point') : 0 }}
                        </h3>
                    </div>
                    <div class="stat-icon" style="background-color: #dc3545; border-radius: 8px;">
                        <i class="fas fa-fire fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="row g-3 mb-4">
        <div class="col-md-12">
            <a href="{{ route('manage.bk.bimbingan.index') }}" class="btn btn-outline-info btn-lg">
                <i class="fas fa-list me-2"></i>Lihat Semua Bimbingan
            </a>
        </div>
    </div>

    <!-- List Siswa -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="mb-0 fw-semibold">
                <i class="fas fa-users me-2 text-info"></i>Daftar Siswa Perlu Bimbingan Konseling
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%" class="text-center">#</th>
                            <th width="10%">NIS</th>
                            <th width="25%">Nama Siswa</th>
                            <th width="15%">Kelas</th>
                            <th width="15%" class="text-center">Total Pelanggaran</th>
                            <th width="10%" class="text-center">Total Point</th>
                            <th width="10%" class="text-center">Level</th>
                            <th width="10%" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($siswaPerluBimbingan as $index => $siswa)
                        @php
                            if($siswa->total_point >= 75) {
                                $level = 'Sangat Tinggi';
                                $badgeClass = 'danger';
                                $icon = 'fas fa-fire';
                            } elseif($siswa->total_point >= 50) {
                                $level = 'Tinggi';
                                $badgeClass = 'warning';
                                $icon = 'fas fa-exclamation-triangle';
                            } else {
                                $level = 'Sedang';
                                $badgeClass = 'info';
                                $icon = 'fas fa-exclamation-circle';
                            }
                        @endphp
                        <tr>
                            <td class="text-center">
                                @if($index == 0)
                                    <i class="fas fa-crown text-warning fs-5"></i>
                                @else
                                    {{ $index + 1 }}
                                @endif
                            </td>
                            <td><strong>{{ $siswa->student_nis }}</strong></td>
                            <td>
                                <div class="fw-semibold">{{ $siswa->student_full_name }}</div>
                            </td>
                            <td>
                                @if($siswa->class)
                                    <span class="badge bg-secondary">{{ $siswa->class->class_name }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">{{ $siswa->total_pelanggaran }}x</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6">{{ $siswa->total_point }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $badgeClass }}">
                                    <i class="{{ $icon }} me-1"></i>{{ $level }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('manage.bk.pelanggaran-siswa.index', ['siswa_id' => $siswa->student_id]) }}" 
                                       class="btn btn-info" title="Lihat Pelanggaran">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="https://wa.me/{{ $siswa->student_phone ?? '' }}?text=Assalamualaikum, kami dari Bimbingan Konseling perlu berbicara dengan Anda mengenai catatan pelanggaran." 
                                       class="btn btn-success" title="Chat WhatsApp" target="_blank">
                                        <i class="fab fa-whatsapp"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="fas fa-smile fa-3x text-success mb-3 d-block"></i>
                                <h5 class="text-muted">Tidak Ada Siswa yang Perlu Bimbingan</h5>
                                <p class="text-muted mb-0">Semua siswa dalam kondisi baik</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Keterangan Level -->
    <div class="row g-4 mt-4">
        <div class="col-md-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Keterangan Level Prioritas</h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-info me-2"><i class="fas fa-exclamation-circle"></i> Sedang</span>
                                <span class="small">Point: 25 - 49</span>
                            </div>
                            <p class="small text-muted mb-0">Perlu pantauan dan teguran</p>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-warning me-2"><i class="fas fa-exclamation-triangle"></i> Tinggi</span>
                                <span class="small">Point: 50 - 74</span>
                            </div>
                            <p class="small text-muted mb-0">Perlu bimbingan intensif</p>
                        </div>
                        <div class="col-md-4">
                            <div class="d-flex align-items-center mb-2">
                                <span class="badge bg-danger me-2"><i class="fas fa-fire"></i> Sangat Tinggi</span>
                                <span class="small">Point: ≥ 75</span>
                            </div>
                            <p class="small text-muted mb-0">Perlu penanganan segera & panggilan orang tua</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hover-lift {
    transition: transform 0.2s ease;
}

.hover-lift:hover {
    transform: translateY(-2px);
}

.stat-icon {
    width: 64px;
    height: 64px;
    display: flex;
    align-items: center;
    justify-content: center;
    position: absolute;
    top: 15px;
    right: 15px;
}
</style>
@endsection

