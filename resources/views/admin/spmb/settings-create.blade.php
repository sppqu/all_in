<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pengaturan SPMB - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fa;
        }
        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .form-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
        }
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        .form-label {
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('manage.spmb.settings') }}">
                <i class="fas fa-plus me-2"></i>Tambah Pengaturan SPMB
            </a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="{{ route('manage.spmb.settings') }}">
                    <i class="fas fa-arrow-left me-1"></i>Kembali ke Pengaturan
                </a>
            </div>
        </div>
    </nav>

    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-card">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h4 class="mb-0">
                                <i class="fas fa-plus me-2"></i>Tambah Pengaturan SPMB
                            </h4>
                            <span class="badge bg-success">Baru</span>
                        </div>

                        <form method="POST" action="{{ route('manage.spmb.settings.store') }}">
                            @csrf
                            
                            <div class="row">
                                <!-- Tahun Pelajaran -->
                                <div class="col-md-6 mb-3">
                                    <label for="tahun_pelajaran" class="form-label">
                                        <i class="fas fa-calendar me-1"></i>Tahun Pelajaran *
                                    </label>
                                    <input type="text" 
                                           class="form-control @error('tahun_pelajaran') is-invalid @enderror" 
                                           id="tahun_pelajaran" 
                                           name="tahun_pelajaran" 
                                           value="{{ old('tahun_pelajaran') }}"
                                           placeholder="Contoh: 2024/2025"
                                           required>
                                    @error('tahun_pelajaran')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Status Pendaftaran -->
                                <div class="col-md-6 mb-3">
                                    <label for="pendaftaran_dibuka" class="form-label">
                                        <i class="fas fa-toggle-on me-1"></i>Status Pendaftaran
                                    </label>
                                    <select class="form-select @error('pendaftaran_dibuka') is-invalid @enderror" 
                                            id="pendaftaran_dibuka" 
                                            name="pendaftaran_dibuka">
                                        <option value="1" {{ old('pendaftaran_dibuka', '1') == '1' ? 'selected' : '' }}>
                                            Dibuka
                                        </option>
                                        <option value="0" {{ old('pendaftaran_dibuka') == '0' ? 'selected' : '' }}>
                                            Ditutup
                                        </option>
                                    </select>
                                    @error('pendaftaran_dibuka')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Tanggal Buka -->
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_buka" class="form-label">
                                        <i class="fas fa-calendar-plus me-1"></i>Tanggal Buka
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('tanggal_buka') is-invalid @enderror" 
                                           id="tanggal_buka" 
                                           name="tanggal_buka" 
                                           value="{{ old('tanggal_buka') }}">
                                    @error('tanggal_buka')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Tanggal Tutup -->
                                <div class="col-md-6 mb-3">
                                    <label for="tanggal_tutup" class="form-label">
                                        <i class="fas fa-calendar-minus me-1"></i>Tanggal Tutup
                                    </label>
                                    <input type="date" 
                                           class="form-control @error('tanggal_tutup') is-invalid @enderror" 
                                           id="tanggal_tutup" 
                                           name="tanggal_tutup" 
                                           value="{{ old('tanggal_tutup') }}">
                                    @error('tanggal_tutup')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row">
                                <!-- Biaya Pendaftaran -->
                                <div class="col-md-6 mb-3">
                                    <label for="biaya_pendaftaran" class="form-label">
                                        <i class="fas fa-money-bill me-1"></i>Biaya Pendaftaran *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               class="form-control @error('biaya_pendaftaran') is-invalid @enderror" 
                                               id="biaya_pendaftaran" 
                                               name="biaya_pendaftaran" 
                                               value="{{ old('biaya_pendaftaran', 50000) }}"
                                               min="0" 
                                               step="1"
                                               required>
                                        @error('biaya_pendaftaran')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <!-- Biaya SPMB -->
                                <div class="col-md-6 mb-3">
                                    <label for="biaya_spmb" class="form-label">
                                        <i class="fas fa-money-bill-wave me-1"></i>Biaya SPMB *
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text">Rp</span>
                                        <input type="number" 
                                               class="form-control @error('biaya_spmb') is-invalid @enderror" 
                                               id="biaya_spmb" 
                                               name="biaya_spmb" 
                                               value="{{ old('biaya_spmb', 100000) }}"
                                               min="0" 
                                               step="1"
                                               required>
                                        @error('biaya_spmb')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <!-- Deskripsi -->
                            <div class="mb-4">
                                <label for="deskripsi" class="form-label">
                                    <i class="fas fa-align-left me-1"></i>Deskripsi
                                </label>
                                <textarea class="form-control @error('deskripsi') is-invalid @enderror" 
                                          id="deskripsi" 
                                          name="deskripsi" 
                                          rows="4" 
                                          placeholder="Deskripsi pengaturan SPMB...">{{ old('deskripsi') }}</textarea>
                                @error('deskripsi')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('manage.spmb.settings') }}" class="btn btn-secondary">
                                    <i class="fas fa-times me-1"></i>Batal
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-1"></i>Simpan Pengaturan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="form-card mt-4">
                    <div class="p-4">
                        <h6 class="mb-3">
                            <i class="fas fa-info-circle me-2"></i>Informasi Pengaturan
                        </h6>
                        <div class="row">
                            <div class="col-md-12">
                                <small class="text-muted">
                                    <strong>Catatan:</strong> Pengaturan SPMB akan mempengaruhi proses pendaftaran siswa. 
                                    Pastikan semua data sudah benar sebelum menyimpan.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Auto format currency
        document.getElementById('biaya_pendaftaran').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        document.getElementById('biaya_spmb').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            e.target.value = value;
        });

        // Date validation
        document.getElementById('tanggal_buka').addEventListener('change', function() {
            const tanggalBuka = new Date(this.value);
            const tanggalTutup = document.getElementById('tanggal_tutup');
            
            if (tanggalTutup.value) {
                const tanggalTutupDate = new Date(tanggalTutup.value);
                if (tanggalBuka > tanggalTutupDate) {
                    alert('Tanggal buka tidak boleh lebih besar dari tanggal tutup!');
                    this.value = '';
                }
            }
        });

        document.getElementById('tanggal_tutup').addEventListener('change', function() {
            const tanggalTutup = new Date(this.value);
            const tanggalBuka = document.getElementById('tanggal_buka');
            
            if (tanggalBuka.value) {
                const tanggalBukaDate = new Date(tanggalBuka.value);
                if (tanggalBukaDate > tanggalTutup) {
                    alert('Tanggal tutup tidak boleh lebih kecil dari tanggal buka!');
                    this.value = '';
                }
            }
        });

        // Set default dates
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date();
            const nextMonth = new Date(today.getFullYear(), today.getMonth() + 1, today.getDate());
            const nextYear = new Date(today.getFullYear() + 1, today.getMonth(), today.getDate());
            
            if (!document.getElementById('tanggal_buka').value) {
                document.getElementById('tanggal_buka').value = today.toISOString().split('T')[0];
            }
            if (!document.getElementById('tanggal_tutup').value) {
                document.getElementById('tanggal_tutup').value = nextYear.toISOString().split('T')[0];
            }
        });
    </script>
</body>
</html>






