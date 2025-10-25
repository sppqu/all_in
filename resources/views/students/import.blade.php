@extends('layouts.coreui')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Import Data Peserta Didik</h4>
                    <a href="{{ route('students.index') }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle"></i> {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="card border-primary">
                                <div class="card-header bg-primary text-white">
                                    <h6 class="mb-0"><i class="fas fa-download"></i> Download Template</h6>
                                </div>
                                <div class="card-body">
                                    <p class="card-text">Download template Excel untuk mengisi data peserta didik dengan format yang benar.</p>
                                    <a href="{{ url('/students-download-template') }}" class="btn btn-primary">
                                        <i class="fas fa-download"></i> Download Template Excel
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="card border-success">
                                <div class="card-header bg-success text-white">
                                    <h6 class="mb-0"><i class="fas fa-upload"></i> Upload File</h6>
                                </div>
                                <div class="card-body">
                                    <form action="{{ url('/students-import') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="file" class="form-label">Pilih File Excel/CSV</label>
                                            <input type="file" 
                                                   class="form-control @error('file') is-invalid @enderror" 
                                                   id="file" 
                                                   name="file" 
                                                   accept=".xlsx,.xls,.csv"
                                                   required>
                                            @error('file')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text text-muted">
                                                Format yang didukung: .xlsx, .xls, .csv (Maksimal 2MB)
                                            </small>
                                        </div>
                                        <button type="submit" class="btn btn-success">
                                            <i class="fas fa-upload"></i> Import Data
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-info">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Panduan Import</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h6 class="text-primary">Format Data yang Diperlukan:</h6>
                                            <ul class="list-unstyled">
                                                <li><strong>NIS:</strong> Nomor Induk Siswa (Wajib, Unik)</li>
                                                <li><strong>NISN:</strong> Nomor Induk Siswa Nasional (Opsional)</li>
                                                <li><strong>Password:</strong> Password untuk login (Wajib)</li>
                                                <li><strong>Nama Lengkap:</strong> Nama lengkap siswa (Wajib)</li>
                                                <li><strong>Jenis Kelamin:</strong> L (Laki-laki) atau P (Perempuan)</li>
                                                <li><strong>Tempat Lahir:</strong> Tempat kelahiran (Wajib)</li>
                                                <li><strong>Tanggal Lahir:</strong> Format dd/mm/yyyy (Wajib)</li>
                                                <li><strong>Kelas:</strong> Nama kelas yang ada di sistem (Wajib)</li>
                                                <li><strong>No. Telp Orang Tua:</strong> Nomor telepon orang tua (Wajib)</li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-warning">Ketentuan Import:</h6>
                                            <ul class="list-unstyled">
                                                <li>✓ NIS harus unik dan tidak boleh kosong</li>
                                                <li>✓ NISN bersifat opsional</li>
                                                <li>✓ Jenis kelamin harus L atau P</li>
                                                <li>✓ Tanggal lahir format dd/mm/yyyy</li>
                                                <li>✓ Kelas harus sesuai dengan data di sistem</li>
                                                <li>✓ Password akan di-hash otomatis</li>
                                                <li>✓ Data yang gagal import akan dilaporkan</li>
                                                <li>✓ Import dapat diulang untuk data yang gagal</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="card border-warning">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0"><i class="fas fa-exclamation-triangle"></i> Daftar Kelas yang Tersedia</h6>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        @php
                                            $classes = \App\Models\ClassModel::orderBy('class_name')->get();
                                        @endphp
                                        @foreach($classes as $class)
                                            <div class="col-md-3 mb-2">
                                                <span class="badge bg-primary">{{ $class->class_name }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                    @if($classes->isEmpty())
                                        <p class="text-muted mb-0">Belum ada data kelas. Silakan tambahkan kelas terlebih dahulu.</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
    border-radius: 0.5rem;
}

.card-header {
    border-radius: 0.5rem 0.5rem 0 0 !important;
}

.badge {
    font-size: 0.875rem;
    padding: 0.5em 0.75em;
}

.list-unstyled li {
    margin-bottom: 0.5rem;
    padding-left: 1rem;
    position: relative;
}

.list-unstyled li:before {
    content: "•";
    position: absolute;
    left: 0;
    color: #6c757d;
}

.form-control:focus {
    border-color: #80bdff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

.btn {
    border-radius: 0.375rem;
    font-weight: 500;
}

.btn:hover {
    transform: translateY(-1px);
    transition: transform 0.2s ease;
}
</style>
@endsection 