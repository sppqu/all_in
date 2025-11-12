@extends('layouts.coreui')
@section('content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card my-3">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Pengguna</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('manage.users.update', $user->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <small>(Kosongkan jika tidak ingin mengubah)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" name="nomor_wa" class="form-control" value="{{ old('nomor_wa', $user->nomor_wa) }}">
                        </div>
                        @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="role_select" class="form-select" required>
                                <option value="">- Pilih Role -</option>
                                <option value="superadmin" {{ old('role', $user->role)=='superadmin' ? 'selected' : '' }}>Superadmin</option>
                                <option value="admin_yayasan" {{ old('role', $user->role)=='admin_yayasan' ? 'selected' : '' }}>Admin Yayasan</option>
                                <option value="admin" {{ old('role', $user->role)=='admin' ? 'selected' : '' }}>Admin Sekolah</option>
                                <option value="admin_bk" {{ old('role', $user->role)=='admin_bk' ? 'selected' : '' }}>Admin BK (Hanya Akses BK)</option>
                                <option value="admin_jurnal" {{ old('role', $user->role)=='admin_jurnal' ? 'selected' : '' }}>Admin Jurnal (Hanya Akses Jurnal)</option>
                                <option value="admin_perpustakaan" {{ old('role', $user->role)=='admin_perpustakaan' ? 'selected' : '' }}>Admin Perpustakaan (Hanya Akses Perpustakaan)</option>
                                <option value="spmb_admin" {{ old('role', $user->role)=='spmb_admin' ? 'selected' : '' }}>Admin SPMB (Hanya Akses SPMB)</option>
                                <option value="kasir" {{ old('role', $user->role)=='kasir' ? 'selected' : '' }}>Kasir/Petugas</option>
                                <option value="bendahara" {{ old('role', $user->role)=='bendahara' ? 'selected' : '' }}>Bendahara</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Catatan:</strong> Role menentukan akses utama user. 
                                Role khusus (Admin BK, Admin Jurnal, Admin Perpustakaan, Admin SPMB) hanya bisa akses modul tersebut saja.
                                Untuk memberikan akses tambahan ke modul tertentu pada Admin Sekolah, gunakan opsi "Akses Admin BK" atau "Akses Admin SPMB" di bawah.
                            </small>
                        </div>
                        @else
                        {{-- User sekolah tidak bisa mengubah role --}}
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="{{ ucfirst($user->role) }}" readonly>
                            <small class="text-muted">Role tidak dapat diubah</small>
                        </div>
                        @endif
                        {{-- School selection dihilangkan, otomatis menggunakan school_id dari admin yang membuat --}}
                        @if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
                        {{-- Untuk superadmin/admin_yayasan, tetap bisa pilih sekolah --}}
                        <div class="mb-3" id="school_selection" style="display: none;">
                            <label class="form-label">Pilih Sekolah</label>
                            <small class="text-muted d-block mb-2">User ini akan di-assign sebagai admin sekolah yang dipilih (bisa pilih lebih dari satu)</small>
                            <select name="school_ids[]" id="school_ids" class="form-select" multiple size="5">
                                @foreach($schools as $school)
                                    <option value="{{ $school->id }}" {{ in_array($school->id, old('school_ids', $userSchoolIds ?? [])) ? 'selected' : '' }}>
                                        {{ $school->nama_sekolah }} ({{ $school->jenjang }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted d-block mt-1">Tekan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu sekolah</small>
                        </div>
                        @else
                        {{-- Untuk admin sekolah, otomatis menggunakan school_id dari admin yang sedang login --}}
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
@if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const roleSelect = document.getElementById('role_select');
    const schoolSelection = document.getElementById('school_selection');
    const schoolIds = document.getElementById('school_ids');
    
    function toggleSchoolSelection() {
        if (!roleSelect || !schoolSelection || !schoolIds) return;
        
        const selectedRole = roleSelect.value;
        const foundationRoles = ['superadmin', 'admin_yayasan'];
        
        if (foundationRoles.includes(selectedRole)) {
            schoolSelection.style.display = 'none';
            schoolIds.removeAttribute('required');
            // Clear selection
            Array.from(schoolIds.options).forEach(option => {
                option.selected = false;
            });
        } else {
            schoolSelection.style.display = 'block';
            schoolIds.setAttribute('required', 'required');
        }
    }
    
    // Check on page load
    toggleSchoolSelection();
    
    // Check on role change
    if (roleSelect) {
        roleSelect.addEventListener('change', toggleSchoolSelection);
    }
});
</script>
@endif
@endsection 