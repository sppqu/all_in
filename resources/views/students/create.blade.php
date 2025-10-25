@extends('layouts.coreui')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Tambah Peserta Didik</h4>
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    <form action="{{ route('students.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <!-- Data Pribadi -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Data Pribadi</h5>
                                
                                <div class="mb-3">
                                    <label for="student_nis" class="form-label">NIS <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('student_nis') is-invalid @enderror" 
                                           id="student_nis" 
                                           name="student_nis" 
                                           value="{{ old('student_nis') }}" 
                                           maxlength="45"
                                           required>
                                    @error('student_nis')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="student_full_name" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('student_full_name') is-invalid @enderror" 
                                           id="student_full_name" 
                                           name="student_full_name" 
                                           value="{{ old('student_full_name') }}" 
                                           maxlength="255"
                                           required>
                                    @error('student_full_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="student_gender" class="form-label">Jenis Kelamin <span class="text-danger">*</span></label>
                                    <select class="form-select @error('student_gender') is-invalid @enderror" 
                                            id="student_gender" 
                                            name="student_gender" 
                                            required>
                                        <option value="">Pilih Jenis Kelamin</option>
                                        <option value="L" {{ old('student_gender') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                                        <option value="P" {{ old('student_gender') == 'P' ? 'selected' : '' }}>Perempuan</option>
                                    </select>
                                    @error('student_gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="student_born_place" class="form-label">Tempat Lahir <span class="text-danger">*</span></label>
                                            <input type="text" 
                                                   class="form-control @error('student_born_place') is-invalid @enderror" 
                                                   id="student_born_place" 
                                                   name="student_born_place" 
                                                   value="{{ old('student_born_place') }}" 
                                                   maxlength="45"
                                                   required>
                                            @error('student_born_place')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="student_born_date" class="form-label">Tanggal Lahir <span class="text-danger">*</span></label>
                                            <input type="date" 
                                                   class="form-control @error('student_born_date') is-invalid @enderror" 
                                                   id="student_born_date" 
                                                   name="student_born_date" 
                                                   value="{{ old('student_born_date') }}"
                                                   required>
                                            @error('student_born_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Akademik & Orang Tua -->
                            <div class="col-md-6">
                                <h5 class="mb-3">Data Akademik</h5>
                                
                                <div class="mb-3">
                                    <label for="class_class_id" class="form-label">Kelas <span class="text-danger">*</span></label>
                                    <select class="form-select @error('class_class_id') is-invalid @enderror" 
                                            id="class_class_id" 
                                            name="class_class_id" 
                                            required>
                                        <option value="">Pilih Kelas</option>
                                        @foreach($classes as $class)
                                            <option value="{{ $class->class_id }}" {{ old('class_class_id') == $class->class_id ? 'selected' : '' }}>
                                                {{ $class->class_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('class_class_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="student_password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" 
                                           class="form-control @error('student_password') is-invalid @enderror" 
                                           id="student_password" 
                                           name="student_password" 
                                           maxlength="100"
                                           required>
                                    @error('student_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <h5 class="mb-3 mt-4">Data Orang Tua</h5>
                                
                                <div class="mb-3">
                                    <label for="student_parent_phone" class="form-label">No. Telepon Orang Tua <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control @error('student_parent_phone') is-invalid @enderror" 
                                           id="student_parent_phone" 
                                           name="student_parent_phone" 
                                           value="{{ old('student_parent_phone') }}" 
                                           maxlength="45"
                                           required>
                                    @error('student_parent_phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" 
                                               type="checkbox" 
                                               id="student_status" 
                                               name="student_status" 
                                               value="1" 
                                               {{ old('student_status', 1) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="student_status">
                                            Status Aktif
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection 