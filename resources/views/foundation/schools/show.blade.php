@extends('layouts.adminty')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0" style="font-size: 1.5rem;">Detail Sekolah</h4>
        <div class="d-flex gap-2">
            <a href="{{ route('manage.foundation.schools.edit', $school) }}" class="btn btn-warning">
                <i class="fa fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('manage.foundation.schools.index') }}" class="btn btn-secondary">
                <i class="fa fa-arrow-left me-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-body text-center">
                    @if($school->logo_sekolah)
                        <img src="{{ Storage::url($school->logo_sekolah) }}" 
                             alt="{{ $school->nama_sekolah }}" 
                             class="img-fluid mb-3" 
                             style="max-width: 200px; border-radius: 10px;">
                    @else
                        <div class="bg-secondary text-white rounded d-flex align-items-center justify-content-center mx-auto mb-3" 
                             style="width: 200px; height: 200px;">
                            <i class="fa fa-school fa-4x"></i>
                        </div>
                    @endif
                    <h5 class="fw-bold" style="font-size: 1.25rem;">{{ $school->nama_sekolah }}</h5>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">{{ $school->jenjang }}</p>
                </div>
            </div>
        </div>

        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 pb-2">
                    <h6 class="fw-bold mb-0" style="font-size: 1.1rem;">Informasi Sekolah</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless" style="font-size: 0.9rem;">
                        <tr>
                            <th width="30%" style="font-size: 0.9rem;">Nama Sekolah</th>
                            <td style="font-size: 0.9rem;">{{ $school->nama_sekolah }}</td>
                        </tr>
                        <tr>
                            <th style="font-size: 0.9rem;">Jenjang</th>
                            <td style="font-size: 0.9rem;">{{ $school->jenjang }}</td>
                        </tr>
                        <tr>
                            <th style="font-size: 0.9rem;">Alamat</th>
                            <td style="font-size: 0.9rem;">{{ $school->alamat }}</td>
                        </tr>
                        <tr>
                            <th style="font-size: 0.9rem;">No. Telepon</th>
                            <td style="font-size: 0.9rem;">{{ $school->no_telp }}</td>
                        </tr>
                        <tr>
                            <th style="font-size: 0.9rem;">Yayasan</th>
                            <td style="font-size: 0.9rem;">{{ $school->foundation->nama_yayasan }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik -->
    <div class="row">
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 text-center" style="border-radius: 15px;">
                <div class="card-body">
                    <i class="fa fa-users fa-2x text-primary mb-3"></i>
                    <h4 class="fw-bold" style="font-size: 1.5rem;">{{ $stats['total_students'] }}</h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Siswa Aktif</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 text-center" style="border-radius: 15px;">
                <div class="card-body">
                    <i class="fa fa-graduation-cap fa-2x text-info mb-3"></i>
                    <h4 class="fw-bold" style="font-size: 1.5rem;">{{ $stats['total_classes'] }}</h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Kelas</p>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card shadow-sm border-0 text-center" style="border-radius: 15px;">
                <div class="card-body">
                    <i class="fa fa-calendar fa-2x text-success mb-3"></i>
                    <h4 class="fw-bold" style="font-size: 1.5rem;">{{ $stats['total_periods'] }}</h4>
                    <p class="text-muted mb-0" style="font-size: 0.9rem;">Total Tahun Ajaran</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

