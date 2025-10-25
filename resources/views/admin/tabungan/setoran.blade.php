@extends('layouts.coreui')

@section('title', 'Setoran Tabungan')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Setoran Tabungan</h4>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h5>Informasi Siswa</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td width="150"><strong>Nama Siswa:</strong></td>
                                    <td>{{ $tabungan->student_full_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>NIS:</strong></td>
                                    <td>{{ $tabungan->student_nis }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Kelas:</strong></td>
                                    <td>{{ $tabungan->class_name }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Saldo Saat Ini:</strong></td>
                                    <td><span class="badge bg-success">Rp {{ number_format($tabungan->saldo, 0, ',', '.') }}</span></td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <form action="{{ route('manage.tabungan.store-setoran', $tabungan->tabungan_id) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="jumlah" class="form-label">Jumlah Setoran <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="text" class="form-control @error('jumlah') is-invalid @enderror" 
                                               id="jumlah" name="jumlah" value="{{ old('jumlah') }}" 
                                               placeholder="Masukkan jumlah setoran" required>
                                    </div>
                                    @error('jumlah')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <textarea class="form-control @error('keterangan') is-invalid @enderror" 
                                              id="keterangan" name="keterangan" rows="3" 
                                              placeholder="Keterangan setoran (opsional)">{{ old('keterangan') }}</textarea>
                                    @error('keterangan')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Simpan Setoran
                                    </button>
                                    <a href="{{ route('manage.tabungan.index') }}" class="btn btn-secondary">
                                        <i class="fas fa-arrow-left"></i> Kembali
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Format input jumlah dengan separator ribuan
    document.getElementById('jumlah').addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
            e.target.value = value;
        }
    });
</script>
@endpush 