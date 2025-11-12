@extends('layouts.coreui')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-0">Laporan Tunggakan Siswa</h2>
    </div>

    <!-- Total Tunggakan Kumulatif Banner -->
    <div class="alert alert-danger mb-4" role="alert">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h4 class="alert-heading mb-1">Total Tunggakan Kumulatif (Semua Tahun)</h4>
                <h2 class="mb-0 fw-bold">Rp {{ number_format($totalTunggakanKumulatif, 0, ',', '.') }}</h2>
            </div>
        </div>
    </div>

    <!-- Rincian Tunggakan per Kelas -->
    <div class="card">
        <div class="card-header bg-white">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="mb-0">Rincian Tunggakan per Kelas</h5>
                <a href="{{ route('manage.foundation.laporan.tunggakan', ['student_status' => 0]) }}" class="text-decoration-none">
                    Lihat Tunggakan Alumni / Non-Aktif →
                </a>
            </div>
            
            <!-- Filters -->
            <form method="GET" action="{{ route('manage.foundation.laporan.tunggakan') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="school_id" class="form-label">Pilih Sekolah</label>
                    <select name="school_id" id="school_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Sekolah</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>
                                {{ $school->nama_sekolah }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="class_id" class="form-label">Filter Kelas</label>
                    <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()">
                        <option value="">Semua Kelas</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->class_id }}" {{ $selectedClassId == $class->class_id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <input type="hidden" name="student_status" value="{{ $studentStatus }}">
            </form>
        </div>
        <div class="card-body">
            @forelse($tunggakanByClass as $className => $students)
            <div class="mb-4">
                <h5 class="mb-3">{{ $className }}</h5>
                <div class="mb-2">
                    <a href="#" class="text-primary text-decoration-none small">Detail per Jenis Biaya</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Nama Siswa</th>
                                <th>NIS</th>
                                <th>Status di T.A. Ini</th>
                                <th class="text-end">Total Tunggakan (Akumulasi)</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students as $student)
                            <tr>
                                <td>{{ $student->student_full_name }}</td>
                                <td>{{ $student->student_nis }}</td>
                                <td>
                                    @if($student->student_status == 1)
                                        <span class="badge bg-success">AKTIF</span>
                                    @else
                                        <span class="badge bg-secondary">NON-AKTIF</span>
                                    @endif
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    Rp {{ number_format($student->total_tunggakan, 0, ',', '.') }}
                                </td>
                                <td>
                                    <a href="{{ route('manage.laporan.tunggakan-siswa', ['student_id' => $student->student_id]) }}" class="text-primary text-decoration-none">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @empty
            <div class="text-center py-4">
                <p class="text-muted mb-0">Tidak ada data tunggakan untuk filter yang dipilih.</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
@endsection





