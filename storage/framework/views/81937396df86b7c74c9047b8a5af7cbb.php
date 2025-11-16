<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card my-3">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Tambah Pengguna</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('manage.users.store')); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e(old('name')); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e(old('email')); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" name="nomor_wa" class="form-control" value="<?php echo e(old('nomor_wa')); ?>">
                        </div>
                        <?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
                        
                        <input type="hidden" name="role" value="admin">
                        <div class="mb-3">
                            <label class="form-label">Pilih Sekolah</label>
                            <small class="text-muted d-block mb-2">User ini akan otomatis menjadi Admin Sekolah di sekolah yang dipilih (bisa pilih lebih dari satu)</small>
                            <select name="school_ids[]" id="school_ids" class="form-control select-default" multiple size="5" required>
                                <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($school->id); ?>" <?php echo e(in_array($school->id, old('school_ids', [])) ? 'selected' : ''); ?>>
                                        <?php echo e($school->nama_sekolah); ?> (<?php echo e($school->jenjang); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted d-block mt-1">Tekan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu sekolah</small>
                        </div>
                        <?php else: ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="role_select" class="form-control select-primary" required>
                                <option value="">- Pilih Role -</option>
                                <option value="admin" <?php echo e(old('role')=='admin' ? 'selected' : ''); ?>>Admin Sekolah</option>
                                <option value="admin_bk" <?php echo e(old('role')=='admin_bk' ? 'selected' : ''); ?>>Admin BK (Hanya Akses BK)</option>
                                <option value="admin_jurnal" <?php echo e(old('role')=='admin_jurnal' ? 'selected' : ''); ?>>Admin Jurnal (Hanya Akses Jurnal)</option>
                                <option value="admin_perpustakaan" <?php echo e(old('role')=='admin_perpustakaan' ? 'selected' : ''); ?>>Admin Perpustakaan (Hanya Akses Perpustakaan)</option>
                                <option value="spmb_admin" <?php echo e(old('role')=='spmb_admin' ? 'selected' : ''); ?>>Admin SPMB (Hanya Akses SPMB)</option>
                                <option value="kasir" <?php echo e(old('role')=='kasir' ? 'selected' : ''); ?>>Kasir/Petugas</option>
                                <option value="bendahara" <?php echo e(old('role')=='bendahara' ? 'selected' : ''); ?>>Bendahara</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Catatan:</strong> Role menentukan akses utama user. 
                                Role khusus (Admin BK, Admin Jurnal, Admin Perpustakaan, Admin SPMB) hanya bisa akses modul tersebut saja.
                                Untuk memberikan akses tambahan ke modul tertentu pada Admin Sekolah, gunakan opsi "Akses Admin BK" atau "Akses Admin SPMB" di bawah.
                            </small>
                        </div>
                        
                        <input type="hidden" name="school_ids[]" id="school_ids" value="<?php echo e(currentSchoolId()); ?>">
                        <?php endif; ?>
                        
                        <input type="hidden" name="is_bk" value="0">
                        <input type="hidden" name="spmb_admin_access" value="0">
                        <div class="d-flex justify-content-between">
                            <a href="<?php echo e(route('manage.users.index')); ?>" class="btn btn-secondary">Batal</a>
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

<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/users/create.blade.php ENDPATH**/ ?>