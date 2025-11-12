@extends('layouts.coreui')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold mb-0" style="font-size: 1.5rem;">Edit Sekolah</h4>
        <a href="{{ route('manage.foundation.schools.index') }}" class="btn btn-secondary">
            <i class="fa fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-body">
            <h6 class="fw-bold mb-4" style="font-size: 1.1rem;">Profil Sekolah</h6>
            
            <form action="{{ route('manage.foundation.schools.update', $school) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                @php
                    $isFoundationLevel = auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan';
                @endphp

                <!-- Data Utama (diatur oleh Yayasan) -->
                <div class="mb-4">
                    <h6 class="text-muted mb-3" style="font-size: 0.95rem;">Data Utama (diatur oleh Yayasan)</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama_sekolah" class="form-label" style="font-size: 0.9rem;">Nama Sekolah</label>
                            @if($isFoundationLevel)
                                <input type="text" class="form-control @error('nama_sekolah') is-invalid @enderror" 
                                       id="nama_sekolah" name="nama_sekolah" value="{{ old('nama_sekolah', $school->nama_sekolah) }}" required>
                            @else
                                <input type="text" class="form-control bg-light" 
                                       id="nama_sekolah" value="{{ $school->nama_sekolah }}" readonly>
                            @endif
                            @error('nama_sekolah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="kepala_sekolah" class="form-label" style="font-size: 0.9rem;">Kepala Sekolah</label>
                            @if($isFoundationLevel)
                                <input type="text" class="form-control @error('kepala_sekolah') is-invalid @enderror" 
                                       id="kepala_sekolah" name="kepala_sekolah" value="{{ old('kepala_sekolah', $school->kepala_sekolah) }}">
                            @else
                                <input type="text" class="form-control bg-light" 
                                       id="kepala_sekolah" value="{{ $school->kepala_sekolah ?? '-' }}" readonly>
                            @endif
                            @error('kepala_sekolah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact and Identification -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="npsn" class="form-label" style="font-size: 0.9rem;">NPSN</label>
                            <input type="text" class="form-control @error('npsn') is-invalid @enderror" 
                                   id="npsn" name="npsn" value="{{ old('npsn', $school->npsn) }}">
                            @error('npsn')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="no_telp" class="form-label" style="font-size: 0.9rem;">Telepon <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('no_telp') is-invalid @enderror" 
                                   id="no_telp" name="no_telp" value="{{ old('no_telp', $school->no_telp) }}" required>
                            @error('no_telp')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="email" class="form-label" style="font-size: 0.9rem;">Email</label>
                            <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email', $school->email) }}">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Address Information -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-8 mb-3">
                            <label for="alamat_baris_1" class="form-label" style="font-size: 0.9rem;">Alamat Baris 1</label>
                            <textarea class="form-control @error('alamat_baris_1') is-invalid @enderror" 
                                      id="alamat_baris_1" name="alamat_baris_1" rows="3">{{ old('alamat_baris_1', $school->alamat_baris_1 ?? $school->alamat) }}</textarea>
                            @error('alamat_baris_1')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label for="alamat_baris_2" class="form-label" style="font-size: 0.9rem;">Alamat Baris 2 (Kab./Kota)</label>
                            <input type="text" class="form-control @error('alamat_baris_2') is-invalid @enderror" 
                                   id="alamat_baris_2" name="alamat_baris_2" value="{{ old('alamat_baris_2', $school->alamat_baris_2) }}">
                            @error('alamat_baris_2')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                @if($isFoundationLevel)
                <!-- Field tambahan untuk foundation level -->
                <div class="mb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="jenjang" class="form-label" style="font-size: 0.9rem;">Jenjang <span class="text-danger">*</span></label>
                            <select class="form-select @error('jenjang') is-invalid @enderror" id="jenjang" name="jenjang" required>
                                <option value="">Pilih Jenjang</option>
                                <option value="TK" {{ old('jenjang', $school->jenjang) == 'TK' ? 'selected' : '' }}>TK</option>
                                <option value="SD" {{ old('jenjang', $school->jenjang) == 'SD' ? 'selected' : '' }}>SD</option>
                                <option value="SMP" {{ old('jenjang', $school->jenjang) == 'SMP' ? 'selected' : '' }}>SMP</option>
                                <option value="SMA" {{ old('jenjang', $school->jenjang) == 'SMA' ? 'selected' : '' }}>SMA</option>
                                <option value="SMK" {{ old('jenjang', $school->jenjang) == 'SMK' ? 'selected' : '' }}>SMK</option>
                                <option value="MA" {{ old('jenjang', $school->jenjang) == 'MA' ? 'selected' : '' }}>MA</option>
                            </select>
                            @error('jenjang')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="alamat" class="form-label" style="font-size: 0.9rem;">Alamat Lengkap <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('alamat') is-invalid @enderror" 
                                  id="alamat" name="alamat" rows="3" required>{{ old('alamat', $school->alamat) }}</textarea>
                        @error('alamat')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                @endif
                
                <input type="hidden" name="status" value="active">

                <div class="mb-3">
                    <label for="logo_sekolah" class="form-label" style="font-size: 0.9rem;">Logo Sekolah</label>
                    @if($school->logo_sekolah)
                        <div class="mb-2">
                            <img src="{{ Storage::url($school->logo_sekolah) }}" 
                                 alt="{{ $school->nama_sekolah }}" 
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 8px;">
                            <p class="text-muted mt-1 mb-0" style="font-size: 0.85rem;"><small>Logo saat ini</small></p>
                        </div>
                    @endif
                    <input type="file" class="form-control @error('logo_sekolah') is-invalid @enderror" 
                           id="logo_sekolah" name="logo_sekolah" accept="image/jpeg,image/jpg,image/png">
                    <small class="form-text text-muted" style="font-size: 0.85rem;">Format: JPG, PNG (Max: 2MB). Kosongkan jika tidak ingin mengubah logo.</small>
                    @error('logo_sekolah')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <a href="{{ route('manage.foundation.schools.index') }}" class="btn btn-secondary">Batal</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save me-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

