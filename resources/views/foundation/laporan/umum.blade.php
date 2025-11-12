@extends('layouts.coreui')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="fw-bold mb-0">Generate Laporan Keuangan</h2>
    </div>

    <!-- Pilih Kriteria Laporan -->
    <div class="card mb-4">
        <div class="card-body">
            <h5 class="fw-bold mb-4">Pilih Kriteria Laporan</h5>
            <form method="GET" action="{{ route('manage.foundation.laporan.umum') }}" class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">1. Pilih Sekolah</label>
                    <select name="school_id" id="school_id" class="form-select" onchange="this.form.submit()">
                        <option value="">-- Pilih Sekolah --</option>
                        @foreach($schools as $school)
                            <option value="{{ $school->id }}" {{ $selectedSchoolId == $school->id ? 'selected' : '' }}>
                                {{ $school->nama_sekolah }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label">2. Pilih Kelas</label>
                    <select name="class_id" id="class_id" class="form-select" onchange="this.form.submit()" {{ !$selectedSchoolId ? 'disabled' : '' }}>
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($classes as $class)
                            <option value="{{ $class->class_id }}" {{ $selectedClassId == $class->class_id ? 'selected' : '' }}>
                                {{ $class->class_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    @if($selectedSchoolId && $selectedClassId && $reportData->count() > 0)
    <!-- Preview Laporan -->
    <div class="card">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Preview Laporan Kelas: {{ $selectedClass->class_name }}</h5>
            <button class="btn btn-primary btn-sm">TAMPILAN LEBAR (A3)</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa / NIS</th>
                            <th>Jenis Biaya</th>
                            <th class="text-end">Total Tagihan</th>
                            <th class="text-end">Total Dibayar</th>
                            <th class="text-end">Tunggakan</th>
                            <th>Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $grandTotalTagihan = 0;
                            $grandTotalDibayar = 0;
                            $grandTotalTunggakan = 0;
                            $no = 1;
                        @endphp
                        @foreach($reportData as $studentData)
                            @foreach($studentData['data'] as $index => $data)
                            <tr>
                                @if($index === 0)
                                    <td rowspan="{{ $studentData['data']->count() }}">{{ $no++ }}</td>
                                    <td rowspan="{{ $studentData['data']->count() }}">
                                        <strong>{{ $studentData['student_name'] }}</strong><br>
                                        <small class="text-muted">NIS: {{ $studentData['student_nis'] }}</small>
                                    </td>
                                @endif
                                <td>{{ $data['jenis_biaya'] }}</td>
                                <td class="text-end">{{ number_format($data['total_tagihan'], 0, ',', '.') }}</td>
                                <td class="text-end">{{ number_format($data['total_dibayar'], 0, ',', '.') }}</td>
                                <td class="text-end text-danger fw-bold">{{ number_format($data['tunggakan'], 0, ',', '.') }}</td>
                                <td>
                                    @if($data['last_payment'])
                                        Bayar Akhir: {{ $data['last_payment'] }}
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                            <tr class="table-light">
                                <td colspan="3" class="fw-bold">Subtotal</td>
                                <td class="text-end fw-bold">{{ number_format($studentData['total_tagihan'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold">{{ number_format($studentData['total_dibayar'], 0, ',', '.') }}</td>
                                <td class="text-end fw-bold text-danger">{{ number_format($studentData['total_tunggakan'], 0, ',', '.') }}</td>
                                <td></td>
                            </tr>
                            @php
                                $grandTotalTagihan += $studentData['total_tagihan'];
                                $grandTotalDibayar += $studentData['total_dibayar'];
                                $grandTotalTunggakan += $studentData['total_tunggakan'];
                            @endphp
                        @endforeach
                    </tbody>
                    <tfoot class="table-secondary">
                        <tr>
                            <td colspan="3" class="fw-bold">GRAND TOTAL</td>
                            <td class="text-end fw-bold">{{ number_format($grandTotalTagihan, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold">{{ number_format($grandTotalDibayar, 0, ',', '.') }}</td>
                            <td class="text-end fw-bold text-danger">{{ number_format($grandTotalTunggakan, 0, ',', '.') }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
    @elseif($selectedSchoolId && $selectedClassId && $reportData->count() == 0)
    <div class="alert alert-info">
        <i class="fa fa-info-circle me-2"></i>Tidak ada data laporan untuk kelas yang dipilih.
    </div>
    @endif
</div>

<script>
    // Auto-submit form when school is selected to load classes
    document.getElementById('school_id')?.addEventListener('change', function() {
        if (this.value) {
            this.form.submit();
        }
    });
</script>
@endsection





