@extends('layouts.coreui')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Detail Peserta Didik: {{ $student->student_full_name }}</h4>
                    <div>
                        <a href="{{ route('students.edit', $student) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                        <a href="{{ route('students.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Data Pribadi -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Data Pribadi</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>NIS:</strong></td>
                                    <td>{{ $student->student_nis }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NISN:</strong></td>
                                    <td>{{ $student->student_nisn ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Lengkap:</strong></td>
                                    <td>{{ $student->student_full_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Jenis Kelamin:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $student->gender_text }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Tempat Lahir:</strong></td>
                                    <td>{{ $student->student_born_place ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Tanggal Lahir:</strong></td>
                                    <td>
                                        {{ $student->student_born_date ? $student->student_born_date->format('d/m/Y') : '-' }}
                                        @if($student->age)
                                            <span class="badge bg-secondary ms-2">{{ $student->age }} tahun</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>No. Telepon:</strong></td>
                                    <td>{{ $student->student_phone ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Hobi:</strong></td>
                                    <td>{{ $student->student_hobby ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Alamat:</strong></td>
                                    <td>{{ $student->student_address ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>

                        <!-- Data Akademik & Orang Tua -->
                        <div class="col-md-6">
                            <h5 class="mb-3">Data Akademik</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>Kelas:</strong></td>
                                    <td>
                                        <span class="badge bg-primary">{{ $student->class->class_name ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Jurusan:</strong></td>
                                    <td>
                                        <span class="badge bg-success">{{ $student->major->majors_name ?? '-' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>Status:</strong></td>
                                    <td>
                                        @if($student->student_status)
                                            <span class="badge bg-success">{{ $student->status_text }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $student->status_text }}</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <h5 class="mb-3 mt-4">Data Orang Tua</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>Nama Ibu:</strong></td>
                                    <td>{{ $student->student_name_of_mother ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Nama Ayah:</strong></td>
                                    <td>{{ $student->student_name_of_father ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>No. Telepon Orang Tua:</strong></td>
                                    <td>{{ $student->student_parent_phone ?? '-' }}</td>
                                </tr>
                            </table>

                            <h5 class="mb-3 mt-4">Informasi Sistem</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="35%"><strong>Tanggal Input:</strong></td>
                                    <td>{{ $student->student_input_date ? $student->student_input_date->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Terakhir Update:</strong></td>
                                    <td>{{ $student->student_last_update ? $student->student_last_update->format('d/m/Y H:i') : '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($student->student_img)
                    <hr>
                    <div class="row">
                        <div class="col-12">
                            <h5>Foto Peserta Didik</h5>
                            <img src="{{ asset('storage/' . $student->student_img) }}" 
                                 alt="Foto {{ $student->student_full_name }}" 
                                 class="img-thumbnail" 
                                 style="max-width: 200px;">
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 