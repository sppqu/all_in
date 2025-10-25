@extends('layouts.coreui')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Kelas: {{ $class->class_name }}</h4>
                    <div>
                        <a href="{{ route('classes.edit', $class) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('classes.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Informasi Kelas</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="30%"><strong>ID Kelas:</strong></td>
                                    <td>{{ $class->class_id }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Kelas:</strong></td>
                                    <td>{{ $class->class_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jumlah Siswa:</strong></td>
                                    <td><span class="badge bg-info">{{ $class->students_count }} Siswa</span></td>
                                </tr>
                                <tr>
                                    <td><strong>Dibuat:</strong></td>
                                    <td>{{ $class->created_at ? $class->created_at->format('d/m/Y H:i') : 'Tidak tersedia' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Terakhir Update:</strong></td>
                                    <td>{{ $class->updated_at ? $class->updated_at->format('d/m/Y H:i') : 'Tidak tersedia' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($class->students->count() > 0)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h5>Daftar Siswa di Kelas Ini ({{ $class->students->count() }} siswa)</h5>
                            <div class="table-responsive">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>NIS</th>
                                            <th>Nama</th>
                                            <th>Jenis Kelamin</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($class->students as $index => $student)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $student->student_nis ?? '-' }}</td>
                                            <td>{{ $student->student_full_name ?? '-' }}</td>
                                            <td>
                                                @if($student->student_gender == 'L')
                                                    <span class="badge bg-info">Laki-laki</span>
                                                @elseif($student->student_gender == 'P')
                                                    <span class="badge bg-pink">Perempuan</span>
                                                @else
                                                    <span class="badge bg-secondary">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($student->student_status)
                                                    <span class="badge bg-success">Aktif</span>
                                                @else
                                                    <span class="badge bg-secondary">Non-Aktif</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @else
                    <hr>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Belum ada siswa yang terdaftar di kelas ini.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.badge.bg-pink {
    background-color: #e83e8c !important;
    color: white !important;
}

.table td {
    vertical-align: middle;
}

.badge {
    font-size: 0.75rem;
    padding: 0.35em 0.65em;
}
</style>
@endsection 