@extends('layouts.adminty')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Pelanggaran</h4>
        <p class="text-muted mb-0">Update data pelanggaran siswa</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('manage.bk.pelanggaran.update', $pelanggaran->id) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Kategori Pelanggaran <span class="text-danger">*</span></label>
                            <select name="kategori_id" class="form-control select-primary @error('kategori_id') is-invalid @enderror" required>
                                <option value="">-- Pilih Kategori --</option>
                                @foreach($kategoris as $kategori)
                                    <option value="{{ $kategori->id }}" 
                                        {{ (old('kategori_id', $pelanggaran->kategori_id) == $kategori->id) ? 'selected' : '' }}>
                                        {{ $kategori->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kategori_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label class="form-label">Kode <span class="text-danger">*</span></label>
                                    <input type="text" name="kode" class="form-control @error('kode') is-invalid @enderror" 
                                           value="{{ old('kode', $pelanggaran->kode) }}" placeholder="Misal: R01, S01, B01" required>
                                    @error('kode')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">Kode unik untuk pelanggaran</small>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <div class="mb-3">
                                    <label class="form-label">Nama Pelanggaran <span class="text-danger">*</span></label>
                                    <input type="text" name="nama" class="form-control @error('nama') is-invalid @enderror" 
                                           value="{{ old('nama', $pelanggaran->nama) }}" placeholder="Misal: Terlambat masuk kelas" required>
                                    @error('nama')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Point <span class="text-danger">*</span></label>
                            <input type="number" name="point" class="form-control @error('point') is-invalid @enderror" 
                                   value="{{ old('point', $pelanggaran->point) }}" placeholder="Misal: 5" min="1" required>
                            @error('point')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Nilai point yang akan ditambahkan jika siswa melakukan pelanggaran ini</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                      rows="3" placeholder="Deskripsi atau penjelasan tambahan...">{{ old('keterangan', $pelanggaran->keterangan) }}</textarea>
                            @error('keterangan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="active" value="1" 
                                       {{ old('is_active', $pelanggaran->is_active) == '1' ? 'checked' : '' }} required>
                                <label class="form-check-label" for="active">
                                    <i class="fas fa-check-circle text-success me-1"></i> Aktif
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="is_active" id="inactive" value="0" 
                                       {{ old('is_active', $pelanggaran->is_active) == '0' ? 'checked' : '' }}>
                                <label class="form-check-label" for="inactive">
                                    <i class="fas fa-times-circle text-secondary me-1"></i> Tidak Aktif
                                </label>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update
                            </button>
                            <a href="{{ route('manage.bk.pelanggaran.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $pelanggaran->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $pelanggaran->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Total Digunakan:</strong></td>
                            <td>
                                <span class="badge bg-primary">
                                    {{ $pelanggaran->pelanggaranSiswa->count() }} kali
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="card mt-3 bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Perhatian</h6>
                    <ul class="small mb-0">
                        <li>Perubahan kode tidak mempengaruhi data lama</li>
                        <li>Perubahan point tidak berlaku retroaktif</li>
                        <li>Nonaktifkan jika tidak ingin digunakan lagi</li>
                        <li>Data tidak dapat dihapus jika sudah digunakan</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

