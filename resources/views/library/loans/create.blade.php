@extends('layouts.adminty')

@section('title', 'Tambah Peminjaman - E-Perpustakaan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <a href="{{ route('manage.library.loans.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-plus me-2"></i>Tambah Peminjaman Buku</h5>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <form action="{{ route('manage.library.loans.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pilih Siswa <span class="text-danger">*</span></label>
                                <input type="text" id="student-search" class="form-control mb-2" 
                                       placeholder="Ketik NIS atau Nama Siswa..." autocomplete="off">
                                <select name="student_id" id="student_id" class="form-select" required style="display:none;">
                                    <option value="">Pilih Siswa</option>
                                </select>
                                <div id="student-results" class="list-group"></div>
                                @error('student_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Pilih Buku <span class="text-danger">*</span></label>
                                <select name="book_id" id="book_id" class="form-control select-primary" required>
                                    <option value="">Pilih Buku</option>
                                    @foreach($books as $book)
                                    <option value="{{ $book->id }}" {{ old('book_id') == $book->id ? 'selected' : '' }}>
                                        {{ $book->judul }} - {{ $book->pengarang }}
                                    </option>
                                    @endforeach
                                </select>
                                @error('book_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Tanggal Pinjam <span class="text-danger">*</span></label>
                                <input type="date" name="tanggal_pinjam" class="form-control @error('tanggal_pinjam') is-invalid @enderror" 
                                       value="{{ old('tanggal_pinjam', date('Y-m-d')) }}" required>
                                @error('tanggal_pinjam')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Durasi Peminjaman <span class="text-danger">*</span></label>
                                <select name="durasi_hari" class="form-control select-primary @error('durasi_hari') is-invalid @enderror" required>
                                    <option value="">Pilih Durasi</option>
                                    <option value="3" {{ old('durasi_hari') == 3 ? 'selected' : '' }}>3 Hari</option>
                                    <option value="7" {{ old('durasi_hari') == 7 ? 'selected' : '' }}>7 Hari (1 Minggu)</option>
                                    <option value="14" {{ old('durasi_hari') == 14 ? 'selected' : '' }}>14 Hari (2 Minggu)</option>
                                </select>
                                @error('durasi_hari')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                <small class="text-muted">Maksimal peminjaman 14 hari</small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan</label>
                            <textarea name="catatan" rows="3" class="form-control @error('catatan') is-invalid @enderror" 
                                      placeholder="Catatan tambahan (opsional)">{{ old('catatan') }}</textarea>
                            @error('catatan')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <!-- Info Box -->
                        <div class="alert alert-info">
                            <h6 class="mb-2"><i class="fas fa-info-circle me-2"></i>Informasi Penting:</h6>
                            <ul class="mb-0 small">
                                <li>Maksimal peminjaman: <strong>3 buku</strong> per siswa</li>
                                <li>Durasi maksimal: <strong>14 hari</strong></li>
                                <li>Denda keterlambatan: <strong>Rp 1.000/hari</strong></li>
                                <li>Pastikan buku dalam kondisi <strong>tersedia</strong></li>
                            </ul>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-save me-2"></i>Simpan Peminjaman
                            </button>
                            <a href="{{ route('manage.library.loans.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-2"></i>Batal
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#student-results {
    position: absolute;
    z-index: 1000;
    max-height: 300px;
    overflow-y: auto;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
}
#student-results .list-group-item {
    cursor: pointer;
}
#student-results .list-group-item:hover {
    background-color: #f8f9fa;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('student-search');
    const resultsDiv = document.getElementById('student-results');
    const studentSelect = document.getElementById('student_id');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        const query = this.value.trim();
        
        if (query.length < 2) {
            resultsDiv.innerHTML = '';
            return;
        }

        searchTimeout = setTimeout(() => {
            fetch('/manage/library/students/search?q=' + encodeURIComponent(query))
                .then(response => response.json())
                .then(data => {
                    resultsDiv.innerHTML = '';
                    
                    if (data.length === 0) {
                        resultsDiv.innerHTML = '<div class="list-group-item">Tidak ada hasil</div>';
                        return;
                    }

                    data.forEach(student => {
                        const item = document.createElement('a');
                        item.className = 'list-group-item list-group-item-action';
                        item.textContent = student.student_nis + ' - ' + student.student_full_name + ' (' + (student.class_name || '-') + ')';
                        item.dataset.id = student.student_id;
                        item.dataset.text = item.textContent;
                        
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            selectStudent(this.dataset.id, this.dataset.text);
                        });
                        
                        resultsDiv.appendChild(item);
                    });
                })
                .catch(error => {
                    console.error('Error:', error);
                    resultsDiv.innerHTML = '<div class="list-group-item text-danger">Error loading data</div>';
                });
        }, 300);
    });

    function selectStudent(id, text) {
        searchInput.value = text;
        studentSelect.value = id;
        resultsDiv.innerHTML = '';
        
        // Check loan limit
        fetch('/manage/library/loans/check-student/' + id)
            .then(res => res.json())
            .then(data => {
                if (data.active_loans >= 3) {
                    alert('Perhatian: Siswa ini sudah memiliki ' + data.active_loans + ' peminjaman aktif. Maksimal 3 buku!');
                }
            })
            .catch(err => console.error(err));
    }

    // Hide results when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target !== searchInput && !resultsDiv.contains(e.target)) {
            resultsDiv.innerHTML = '';
        }
    });
});
</script>
@endsection
