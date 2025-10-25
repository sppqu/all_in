@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-plus-circle me-2"></i>Catat Pelanggaran Siswa Baru
                    </h5>
                </div>
                
                <form action="{{ route('manage.bk.pelanggaran-siswa.store') }}" method="POST">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <strong>Error!</strong>
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <!-- Siswa -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Siswa <span class="text-danger">*</span></label>
                                <select name="siswa_id" class="form-select @error('siswa_id') is-invalid @enderror" required>
                                    <option value="">Pilih Siswa</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->student_id }}" {{ old('siswa_id') == $student->student_id ? 'selected' : '' }}>
                                            {{ $student->student_nis }} - {{ $student->student_full_name }}
                                            @if($student->class)
                                                ({{ $student->class->class_name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('siswa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tanggal Pelanggaran -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pelanggaran <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pelanggaran" class="form-control @error('tanggal_pelanggaran') is-invalid @enderror" 
                                       value="{{ old('tanggal_pelanggaran', date('Y-m-d')) }}" required>
                                @error('tanggal_pelanggaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Jenis Pelanggaran (Grouped by Kategori) -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Jenis Pelanggaran <span class="text-danger">*</span></label>
                                <select name="pelanggaran_id" class="form-select @error('pelanggaran_id') is-invalid @enderror" required id="pelanggaranSelect">
                                    <option value="">Pilih Jenis Pelanggaran</option>
                                    @foreach($pelanggaran as $kategoriName => $items)
                                        <optgroup label="{{ $kategoriName }}">
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" data-point="{{ $item->point }}" {{ old('pelanggaran_id') == $item->id ? 'selected' : '' }}>
                                                    [{{ $item->kode }}] {{ $item->nama }} ({{ $item->point }} Point)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('pelanggaran_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="pointInfo" class="mt-2" style="display: none;">
                                    <div class="alert alert-info">
                                        Point pelanggaran: <strong id="pointValue" class="fs-5"></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Pelapor -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pelapor <span class="text-danger">*</span></label>
                                <input type="text" name="pelapor" class="form-control @error('pelapor') is-invalid @enderror" 
                                       value="{{ old('pelapor', Auth::user()->name ?? '') }}" required placeholder="Nama guru/staff yang melaporkan">
                                @error('pelapor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tempat -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Kejadian</label>
                                <input type="text" name="tempat" class="form-control @error('tempat') is-invalid @enderror" 
                                       value="{{ old('tempat') }}" placeholder="Contoh: Kelas, Kantin, Lapangan">
                                @error('tempat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan/Kronologi</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" rows="4" 
                                          placeholder="Detail kronologi kejadian...">{{ old('keterangan') }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-select @error('status') is-invalid @enderror" required>
                                    <option value="pending" {{ old('status', 'pending') == 'pending' ? 'selected' : '' }}>Pending (Perlu Review)</option>
                                    <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Langsung Disetujui</option>
                                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Pilih "Pending" jika perlu review lebih lanjut</small>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Pelanggaran
                        </button>
                        <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('pelanggaranSelect').addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const point = selectedOption.getAttribute('data-point');
    
    if (point) {
        document.getElementById('pointValue').textContent = point + ' Point';
        document.getElementById('pointInfo').style.display = 'block';
    } else {
        document.getElementById('pointInfo').style.display = 'none';
    }
});
</script>
@endsection

