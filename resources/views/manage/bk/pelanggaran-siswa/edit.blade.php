@extends('layouts.adminty')

@section('content')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container {
    width: 100% !important;
}
.select2-container .select2-selection--single {
    height: 38px;
    padding: 4px 12px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 28px;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
</style>

<div class="container-fluid">
    <div class="mb-4">
        <h4 class="mb-1"><i class="fas fa-edit me-2"></i>Edit Pelanggaran Siswa</h4>
        <p class="text-muted mb-0">Update data pelanggaran siswa</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="{{ route('manage.bk.pelanggaran-siswa.update', $pelanggaranSiswa->id) }}" method="POST">
                        @csrf
                        @method('PUT')

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
                                <select name="siswa_id" id="siswaSelect" class="form-control @error('siswa_id') is-invalid @enderror" required>
                                    <option value="">Pilih Peserta Didik</option>
                                    @foreach($students as $student)
                                        <option value="{{ $student->student_id }}" 
                                            {{ old('siswa_id', $pelanggaranSiswa->siswa_id) == $student->student_id ? 'selected' : '' }}>
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
                                <input type="date" name="tanggal_pelanggaran" 
                                       class="form-control @error('tanggal_pelanggaran') is-invalid @enderror" 
                                       value="{{ old('tanggal_pelanggaran', $pelanggaranSiswa->tanggal_pelanggaran->format('Y-m-d')) }}" required>
                                @error('tanggal_pelanggaran')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Jenis Pelanggaran -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Jenis Pelanggaran <span class="text-danger">*</span></label>
                                <select name="pelanggaran_id" class="form-control @error('pelanggaran_id') is-invalid @enderror" 
                                        required id="pelanggaranSelect">
                                    <option value="">Pilih Jenis Pelanggaran</option>
                                    @foreach($pelanggaran as $kategoriName => $items)
                                        <optgroup label="{{ $kategoriName }}">
                                            @foreach($items as $item)
                                                <option value="{{ $item->id }}" data-point="{{ $item->point }}" 
                                                    {{ old('pelanggaran_id', $pelanggaranSiswa->pelanggaran_id) == $item->id ? 'selected' : '' }}>
                                                    [{{ $item->kode }}] {{ $item->nama }} ({{ $item->point }} Point)
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('pelanggaran_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div id="pointInfo" class="mt-2" style="{{ $pelanggaranSiswa->pelanggaran ? '' : 'display: none;' }}">
                                    <div class="alert alert-info">
                                        Point pelanggaran: <strong id="pointValue" class="fs-5">{{ $pelanggaranSiswa->pelanggaran->point ?? 0 }} Point</strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Pelapor -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pelapor <span class="text-danger">*</span></label>
                                <input type="text" name="pelapor" class="form-control @error('pelapor') is-invalid @enderror" 
                                       value="{{ old('pelapor', $pelanggaranSiswa->pelapor) }}" required 
                                       placeholder="Nama guru/staff yang melaporkan">
                                @error('pelapor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Tempat -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tempat Kejadian</label>
                                <input type="text" name="tempat" class="form-control @error('tempat') is-invalid @enderror" 
                                       value="{{ old('tempat', $pelanggaranSiswa->tempat) }}" 
                                       placeholder="Contoh: Kelas, Kantin, Lapangan">
                                @error('tempat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Keterangan -->
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Keterangan/Kronologi</label>
                                <textarea name="keterangan" class="form-control @error('keterangan') is-invalid @enderror" 
                                          rows="4" placeholder="Detail kronologi kejadian...">{{ old('keterangan', $pelanggaranSiswa->keterangan) }}</textarea>
                                @error('keterangan')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select-primary @error('status') is-invalid @enderror" required>
                                    <option value="pending" {{ old('status', $pelanggaranSiswa->status) == 'pending' ? 'selected' : '' }}>
                                        Pending (Perlu Review)
                                    </option>
                                    <option value="approved" {{ old('status', $pelanggaranSiswa->status) == 'approved' ? 'selected' : '' }}>
                                        Disetujui
                                    </option>
                                    <option value="rejected" {{ old('status', $pelanggaranSiswa->status) == 'rejected' ? 'selected' : '' }}>
                                        Ditolak
                                    </option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>Update
                            </button>
                            <a href="{{ route('manage.bk.pelanggaran-siswa.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>Kembali
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Informasi</h6>
                    <table class="table table-sm mb-0">
                        <tr>
                            <td><strong>Dibuat:</strong></td>
                            <td>{{ $pelanggaranSiswa->created_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        <tr>
                            <td><strong>Terakhir Update:</strong></td>
                            <td>{{ $pelanggaranSiswa->updated_at->format('d/m/Y H:i') }}</td>
                        </tr>
                        @if($pelanggaranSiswa->creator)
                        <tr>
                            <td><strong>Dicatat Oleh:</strong></td>
                            <td>{{ $pelanggaranSiswa->creator->name }}</td>
                        </tr>
                        @endif
                    </table>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-3">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-exclamation-triangle me-2 text-warning"></i>Perhatian</h6>
                    <ul class="small mb-0">
                        <li>Perubahan data akan mempengaruhi total point siswa</li>
                        <li>Status "Disetujui" akan menambah point ke siswa</li>
                        <li>Status "Ditolak" tidak menambah point</li>
                        <li>Pastikan data yang diinput sudah benar</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    // Initialize Select2 for Siswa
    $('#siswaSelect').select2({
        placeholder: 'Ketik untuk mencari siswa...',
        allowClear: true,
        width: '100%',
        language: {
            noResults: function() {
                return "Tidak ada hasil ditemukan";
            }
        }
    });

    // Show point info when pelanggaran is selected
    $('#pelanggaranSelect').on('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const point = selectedOption.getAttribute('data-point');
        
        if (point) {
            $('#pointValue').text(point + ' Point');
            $('#pointInfo').show();
        } else {
            $('#pointInfo').hide();
        }
    });
});
</script>
@endsection

