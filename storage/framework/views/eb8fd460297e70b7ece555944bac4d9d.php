<?php $__env->startSection('content'); ?>
<?php if(session('success') || session('error')): ?>
    <div id="session-messages" style="display: none;">
        <?php if(session('success')): ?>
            <div data-type="success" data-message="<?php echo e(session('success')); ?>"></div>
        <?php endif; ?>
        <?php if(session('error')): ?>
            <div data-type="error" data-message="<?php echo e(session('error')); ?>"></div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if($errors->any()): ?>
    <div id="validation-errors" style="display: none;">
        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div data-type="error" data-message="<?php echo e($error); ?>"></div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-6">
            <div class="card my-3">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Edit Pengguna</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="<?php echo e(route('manage.users.update', $user->id)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="mb-3">
                            <label class="form-label">Nama</label>
                            <input type="text" name="name" class="form-control" value="<?php echo e(old('name', $user->name)); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?php echo e(old('email', $user->email)); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password <small>(Kosongkan jika tidak ingin mengubah)</small></label>
                            <input type="password" name="password" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">No. WhatsApp</label>
                            <input type="text" name="nomor_wa" class="form-control" value="<?php echo e(old('nomor_wa', $user->nomor_wa)); ?>">
                        </div>
                        <?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select name="role" id="role_select" class="form-control select-primary" required>
                                <option value="">- Pilih Role -</option>
                                <option value="superadmin" <?php echo e(old('role', $user->role)=='superadmin' ? 'selected' : ''); ?>>Superadmin</option>
                                <option value="admin_yayasan" <?php echo e(old('role', $user->role)=='admin_yayasan' ? 'selected' : ''); ?>>Admin Yayasan</option>
                                <option value="admin" <?php echo e(old('role', $user->role)=='admin' ? 'selected' : ''); ?>>Admin Sekolah</option>
                                <option value="admin_bk" <?php echo e(old('role', $user->role)=='admin_bk' ? 'selected' : ''); ?>>Admin BK (Hanya Akses BK)</option>
                                <option value="admin_jurnal" <?php echo e(old('role', $user->role)=='admin_jurnal' ? 'selected' : ''); ?>>Admin Jurnal (Hanya Akses Jurnal)</option>
                                <option value="admin_perpustakaan" <?php echo e(old('role', $user->role)=='admin_perpustakaan' ? 'selected' : ''); ?>>Admin Perpustakaan (Hanya Akses Perpustakaan)</option>
                                <option value="spmb_admin" <?php echo e(old('role', $user->role)=='spmb_admin' ? 'selected' : ''); ?>>Admin SPMB (Hanya Akses SPMB)</option>
                                <option value="kasir" <?php echo e(old('role', $user->role)=='kasir' ? 'selected' : ''); ?>>Kasir/Petugas</option>
                                <option value="bendahara" <?php echo e(old('role', $user->role)=='bendahara' ? 'selected' : ''); ?>>Bendahara</option>
                            </select>
                            <small class="form-text text-muted">
                                <strong>Catatan:</strong> Role menentukan akses utama user. 
                                Role khusus (Admin BK, Admin Jurnal, Admin Perpustakaan, Admin SPMB) hanya bisa akses modul tersebut saja.
                                Untuk memberikan akses tambahan ke modul tertentu pada Admin Sekolah, gunakan opsi "Akses Admin BK" atau "Akses Admin SPMB" di bawah.
                            </small>
                        </div>
                        <?php else: ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <input type="text" class="form-control" value="<?php echo e(ucfirst($user->role)); ?>" readonly>
                            <small class="text-muted">Role tidak dapat diubah</small>
                            
                            <input type="hidden" name="role" value="<?php echo e($user->role); ?>">
                        </div>
                        <?php endif; ?>
                        
                        <?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
                        
                        <div class="mb-3" id="school_selection" style="display: none;">
                            <label class="form-label">Pilih Sekolah</label>
                            <small class="text-muted d-block mb-2">User ini akan di-assign sebagai admin sekolah yang dipilih (bisa pilih lebih dari satu)</small>
                            <select name="school_ids[]" id="school_ids" class="form-control select-default" multiple size="5">
                                <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($school->id); ?>" <?php echo e(in_array($school->id, old('school_ids', $userSchoolIds ?? [])) ? 'selected' : ''); ?>>
                                        <?php echo e($school->nama_sekolah); ?> (<?php echo e($school->jenjang); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <small class="text-muted d-block mt-1">Tekan Ctrl (Windows) atau Cmd (Mac) untuk memilih lebih dari satu sekolah</small>
                        </div>
                        <?php else: ?>
                        
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
    background-color: #01a9ac !important;
    color: #fff !important;
}
.btn-primary {
    background-color: #01a9ac !important;
    border-color: #01a9ac !important;
    color: #fff !important;
}
.btn-primary:active, .btn-primary:focus, .btn-primary:hover {
    background-color: #018a8c !important;
    border-color: #018a8c !important;
    color: #fff !important;
}
</style>
<?php $__env->startPush('scripts'); ?>
<script>
$(document).ready(function() {
    // Handle session messages with global toast
    const sessionMessages = $('#session-messages');
    if (sessionMessages.length) {
        sessionMessages.find('div').each(function() {
            const type = $(this).data('type');
            const message = $(this).data('message');
            
            if (typeof showToast === 'function') {
                showToast(type === 'success' ? 'success' : 'error', type === 'success' ? 'Berhasil' : 'Error', message);
            }
        });
    }
    
    // Handle validation errors with global toast
    const validationErrors = $('#validation-errors');
    if (validationErrors.length) {
        validationErrors.find('div').each(function() {
            const message = $(this).data('message');
            
            if (typeof showToast === 'function') {
                showToast('error', 'Validasi Error', message);
            }
        });
    }
});

<?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
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
<?php endif; ?>
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/users/edit.blade.php ENDPATH**/ ?>