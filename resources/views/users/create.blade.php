@extends('layouts.coreui')
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
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" class="form-select" required>
                                <option value="">- Pilih Role -</option>
                                <option value="superadmin" {{ old('role')=='superadmin' ? 'selected' : '' }}>Superadmin</option>
                                <option value="admin" {{ old('role')=='admin' ? 'selected' : '' }}>Admin</option>
                                <option value="admin_bk" {{ old('role')=='admin_bk' ? 'selected' : '' }}>Admin BK</option>
                                <option value="admin_jurnal" {{ old('role')=='admin_jurnal' ? 'selected' : '' }}>Admin Jurnal</option>
                                <option value="spmb_admin" {{ old('role')=='spmb_admin' ? 'selected' : '' }}>Admin SPMB</option>
                                <option value="kasir" {{ old('role')=='kasir' ? 'selected' : '' }}>Kasir/Petugas</option>
                                <option value="bendahara" {{ old('role')=='bendahara' ? 'selected' : '' }}>Bendahara</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Akses Admin BK</label>
                            <select name="is_bk" class="form-select" id="is_bk_select">
                                <option value="0" {{ old('is_bk')=='0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('is_bk')=='1' ? 'selected' : '' }}>Ya</option>
                            </select>
                            <small class="form-text text-muted">Memberikan akses khusus ke modul Bimbingan Konseling (HANYA menu BK)</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Akses Admin SPMB</label>
                            <select name="spmb_admin_access" class="form-select">
                                <option value="0" {{ old('spmb_admin_access')=='0' ? 'selected' : '' }}>Tidak</option>
                                <option value="1" {{ old('spmb_admin_access')=='1' ? 'selected' : '' }}>Ya</option>
                            </select>
                            <small class="form-text text-muted">Memberikan akses khusus ke dashboard SPMB</small>
                        </div>
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
@endsection 