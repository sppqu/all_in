@extends('layouts.adminty')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card my-3">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tambah Pengguna</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manage.users.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" name="nomor_wa" class="form-control" value="{{ old('nomor_wa') }}">
                        </div>
                        @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                        {{-- Yayasan hanya bisa membuat Admin Sekolah --}}
                        <input type="hidden" name="role" value="admin">
                        <div class="mb-3">
                            <label class="form-label">Pilih Sekolah</label>
                            <small class="text-muted d-block mb-2">User ini akan otomatis menjadi Admin Sekolah di sekolah yang dipilih (bisa pilih lebih dari satu)</small>
                            <select name="school_ids[]" id="school_ids" class="form-control select-default" multiple size="5" required>
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ in_array($school->id, old('school_ids', [])) ? 'selected' : '' }}>
                                        {{ $school->nama_sekolah }} ({{ $school->jenjang }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Tekan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu sekolah</small>
                        </div>
                        @else
                        {{-- Untuk role lain (jika ada akses dari level lain) --}}
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="role_select" class="form-control select-primary" required>
                                <option value="">- Pilih Role -</option>
                                <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin Sekolah</option>
                                <option value="admin_bk" {{ old('role')=='admin_bk' ? 'selected' : '' }}>Admin BK (Hanya Akses BK)</option>
                                <option value="admin_jurnal" {{ old('role')=='admin_jurnal' ? 'selected' : '' }}>Admin Jurnal (Hanya Akses Jurnal)</option>
                                <option value="admin_perpustakaan" {{ old('role')=='admin_perpustakaan' ? 'selected' : '' }}>Admin Perpustakaan (Hanya Akses Perpustakaan)</option>
                                <option value="spmb_admin" {{ old('role')=='spmb_admin' ? 'selected' : '' }}>Admin SPMB (Hanya Akses SPMB)</option>
                                <option value="kasir" {{ old('role')=='kasir' ? 'selected' : '' }}>Kasir/Petugas</option>
                                <option value="bendahara" {{ old('role')=='bendahara' ? 'selected' : '' }}>Bendahara</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Catatan:</strong> Role menentukan akses utama user. 
                                Role khusus (Admin BK, Admin Jurnal, Admin Perpustakaan, Admin SPMB) hanya bisa akses modul tersebut saja.
                                Untuk memberikan akses tambahan ke modul tertentu pada Admin Sekolah, gunakan opsi "Akses Admin BK" atau "Akses Admin SPMB" di bawah.
                            </small>
                        </div>
                        {{-- School selection dihilangkan, otomatis menggunakan school_id dari admin yang membuat --}}
                        <input type="hidden" name="school_ids[]" id="school_ids" value="{{ currentSchoolId() }}">
                        @endif
                        {{-- Hidden fields: Admin dengan addon aktif sudah bisa akses semua menu addon --}}
                        <input type="hidden" name="is_bk" value="0">
                        <input type="hidden" name="spmb_admin_access" value="0">
                        <div class="d-flex justify-content-between">
                            <a href="{{ route('manage.users.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.card-header.bg-primary, .card-header {
    background-color: #2e7d32 !important;
    color: #fff !important;
}
.btn-primary {
    background-color: #2e7d32 !important;
    border-color: #2e7d32 !important;
    color: #fff !important;
}
.btn-primary:active, .btn-primary:focus, .btn-primary:hover {
    background-color: #256026 !important;
    border-color: #256026 !important;
    color: #fff !important;
}
</style>
{{-- School selection sudah otomatis berdasarkan admin yang membuat --}}
@endsection 