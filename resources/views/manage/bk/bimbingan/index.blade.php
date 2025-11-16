@extends('layouts.adminty')

@section('content')
<div class="container-fluid px-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 fw-bold text-dark">
                <i class="fas fa-comments me-2 text-info"></i>Bimbingan Konseling
            </h2>
            <p class="text-muted mb-0">Kelola data bimbingan konseling siswa</p>
        </div>
        <div>
            <a href="{{ route('manage.bk.bimbingan-konseling') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahBimbingan">
                <i class="fas fa-plus-circle me-2"></i>Tambah Bimbingan
            </button>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('manage.bk.bimbingan.index') }}">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Cari Siswa</label>
                        <input type="text" name="search" class="form-control" 
                               placeholder="Nama atau NIS siswa..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Status</label>
                        <select name="status" class="form-control select-primary">
                            <option value="">Semua Status</option>
                            <option value="dijadwalkan" {{ request('status') == 'dijadwalkan' ? 'selected' : '' }}>Dijadwalkan</option>
                            <option value="berlangsung" {{ request('status') == 'berlangsung' ? 'selected' : '' }}>Berlangsung</option>
                            <option value="selesai" {{ request('status') == 'selesai' ? 'selected' : '' }}>Selesai</option>
                            <option value="ditunda" {{ request('status') == 'ditunda' ? 'selected' : '' }}>Ditunda</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-semibold">Jenis Bimbingan</label>
                        <select name="jenis" class="form-control select-primary">
                            <option value="">Semua Jenis</option>
                            <option value="akademik" {{ request('jenis') == 'akademik' ? 'selected' : '' }}>Akademik</option>
                            <option value="pribadi" {{ request('jenis') == 'pribadi' ? 'selected' : '' }}>Pribadi</option>
                            <option value="sosial" {{ request('jenis') == 'sosial' ? 'selected' : '' }}>Sosial</option>
                            <option value="karir" {{ request('jenis') == 'karir' ? 'selected' : '' }}>Karir</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">No</th>
                            <th width="10%">Tanggal</th>
                            <th width="15%">NIS/Nama Siswa</th>
                            <th width="10%">Kelas</th>
                            <th width="12%">Jenis</th>
                            <th width="10%">Kategori</th>
                            <th width="8%">Sesi</th>
                            <th width="10%">Status</th>
                            <th width="10%">Guru BK</th>
                            <th width="10%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bimbingan as $index => $item)
                        <tr>
                            <td>{{ $bimbingan->firstItem() + $index }}</td>
                            <td>{{ $item->tanggal_bimbingan->format('d M Y') }}</td>
                            <td>
                                <div class="fw-semibold">{{ $item->siswa->student_full_name }}</div>
                                <small class="text-muted">{{ $item->siswa->student_nis }}</small>
                            </td>
                            <td>
                                @if($item->siswa->class)
                                    <span class="badge bg-secondary">{{ $item->siswa->class->class_name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-info">
                                    {{ ucfirst($item->jenis_bimbingan) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->kategori_badge }}">
                                    {{ ucfirst($item->kategori) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-primary">Sesi #{{ $item->sesi_ke }}</span>
                            </td>
                            <td>
                                <span class="badge bg-{{ $item->status_badge }}">
                                    {{ ucfirst($item->status) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $item->guruBK->name ?? '-' }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('manage.bk.bimbingan.show', $item->id) }}" 
                                       class="btn btn-info" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('manage.bk.bimbingan.edit', $item->id) }}" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('manage.bk.bimbingan.destroy', $item->id) }}" 
                                          method="POST" class="d-inline"
                                          onsubmit="return confirm('Yakin ingin menghapus data ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <i class="fas fa-inbox fa-3x text-muted mb-3 d-block"></i>
                                <p class="text-muted">Belum ada data bimbingan konseling</p>
                                <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTambahBimbingan">
                                    <i class="fas fa-plus-circle me-2"></i>Tambah Bimbingan Pertama
                                </button>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($bimbingan->hasPages())
        <div class="card-footer bg-white">
            {{ $bimbingan->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Bimbingan -->
<div class="modal fade" id="modalTambahBimbingan" tabindex="-1" role="dialog" aria-labelledby="modalTambahBimbinganLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="modalTambahBimbinganLabel">
                    <i class="fas fa-plus-circle me-2"></i>Tambah Bimbingan Konseling
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form id="formTambahBimbingan" method="POST" action="{{ route('manage.bk.bimbingan.store') }}">
                @csrf
                <div class="modal-body">
                    <!-- Informasi Dasar -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-info-circle me-2"></i>Informasi Dasar
                        </h6>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                                <select name="siswa_id" id="modal_siswa_id" class="form-control" required>
                                    <option value="">-- Pilih Siswa --</option>
                                    @forelse($students ?? [] as $student)
                                        <option value="{{ $student->student_id }}">
                                            {{ $student->student_nis }} - {{ $student->student_full_name }}
                                            @if($student->class)
                                                ({{ $student->class->class_name }})
                                            @endif
                                        </option>
                                    @empty
                                        <option value="" disabled>Tidak ada data siswa</option>
                                    @endforelse
                                </select>
                                <small class="text-muted">Total {{ ($students ?? collect())->count() }} siswa</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Bimbingan <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_bimbingan" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                        </div>
                    </div>

                    <!-- Detail Bimbingan -->
                    <div class="mb-4">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-clipboard-list me-2"></i>Detail Bimbingan
                        </h6>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Jenis Bimbingan <span class="text-danger">*</span></label>
                                <select name="jenis_bimbingan" class="form-control select-primary" required>
                                    <option value="">-- Pilih Jenis --</option>
                                    <option value="akademik">Akademik</option>
                                    <option value="pribadi">Pribadi</option>
                                    <option value="sosial">Sosial</option>
                                    <option value="karir">Karir</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Kategori <span class="text-danger">*</span></label>
                                <select name="kategori" class="form-control select-primary" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    <option value="ringan">Ringan</option>
                                    <option value="sedang">Sedang</option>
                                    <option value="berat">Berat</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Sesi Ke <span class="text-danger">*</span></label>
                                <input type="number" name="sesi_ke" class="form-control" value="1" min="1" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status <span class="text-danger">*</span></label>
                                <select name="status" class="form-control select-primary" required>
                                    <option value="dijadwalkan">Dijadwalkan</option>
                                    <option value="berlangsung">Berlangsung</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="ditunda">Ditunda</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Catatan Bimbingan -->
                    <div class="mb-3">
                        <h6 class="text-primary mb-3">
                            <i class="fas fa-file-alt me-2"></i>Catatan Bimbingan
                        </h6>
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Permasalahan <span class="text-danger">*</span></label>
                                <textarea name="permasalahan" rows="4" class="form-control" placeholder="Deskripsikan permasalahan yang dihadapi siswa..." required></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Analisis</label>
                                <textarea name="analisis" rows="3" class="form-control" placeholder="Analisis terhadap permasalahan (opsional)..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Tindakan/Solusi</label>
                                <textarea name="tindakan" rows="3" class="form-control" placeholder="Tindakan atau solusi yang diberikan (opsional)..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Hasil</label>
                                <textarea name="hasil" rows="3" class="form-control" placeholder="Hasil dari bimbingan (opsional)..."></textarea>
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label">Catatan Tambahan</label>
                                <textarea name="catatan" rows="2" class="form-control" placeholder="Catatan tambahan (opsional)..."></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times me-1"></i>Batal
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Simpan Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.25rem;
        background-color: #fff !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff;
        padding: 1px 30px 8px 20px;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
    }
    #modal_siswa_id {
        background-color: #fff !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    // Initialize Select2 for siswa dropdown in modal
    $('#modal_siswa_id').select2({
        placeholder: '-- Pilih Siswa --',
        allowClear: true,
        width: '100%',
        dropdownParent: $('#modalTambahBimbingan')
    });

    // Handle form submission
    $('#formTambahBimbingan').on('submit', function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = new FormData(this);
        var submitBtn = form.find('button[type="submit"]');
        var originalText = submitBtn.html();
        
        // Disable submit button
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i>Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                // Show success message
                if (typeof showToast === 'function') {
                    showToast('success', 'Berhasil', 'Data bimbingan konseling berhasil ditambahkan.');
                } else {
                    alert('Data bimbingan konseling berhasil ditambahkan.');
                }
                
                // Close modal
                $('#modalTambahBimbingan').modal('hide');
                
                // Reset form
                form[0].reset();
                $('#modal_siswa_id').val(null).trigger('change');
                
                // Reload page after 1 second
                setTimeout(function() {
                    window.location.reload();
                }, 1000);
            },
            error: function(xhr) {
                // Re-enable submit button
                submitBtn.prop('disabled', false).html(originalText);
                
                // Show error message
                var errorMessage = 'Terjadi kesalahan saat menyimpan data.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                    var errors = xhr.responseJSON.errors;
                    var errorList = Object.values(errors).flat().join('<br>');
                    errorMessage = errorList;
                }
                
                if (typeof showToast === 'function') {
                    showToast('error', 'Error', errorMessage);
                } else {
                    alert(errorMessage);
                }
            }
        });
    });
    
    // Reset form when modal is closed
    $('#modalTambahBimbingan').on('hidden.bs.modal', function() {
        $('#formTambahBimbingan')[0].reset();
        $('#modal_siswa_id').val(null).trigger('change');
        $('#formTambahBimbingan').find('button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save me-1"></i>Simpan Data');
    });
});
</script>
@endpush

