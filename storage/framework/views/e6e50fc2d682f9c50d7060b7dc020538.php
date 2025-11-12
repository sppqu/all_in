<?php $__env->startSection('content'); ?>
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="card my-3">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Managemen Pengguna</h4>
                    <a href="<?php echo e(route('manage.users.create')); ?>" class="btn btn-primary"><i class="fa fa-plus me-1"></i> Tambah Pengguna</a>
                </div>
                <div class="card-body">
                    <?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
                    <div class="mb-4">
                        <form method="GET" action="<?php echo e(route('manage.users.index')); ?>" class="d-flex align-items-end gap-2">
                            <div class="flex-grow-1">
                                <label for="school_id" class="form-label text-dark fw-semibold">Sekolah</label>
                                <select name="school_id" id="school_id" class="form-select" onchange="this.form.submit()">
                                    <option value="">Semua Sekolah</option>
                                    <?php $__currentLoopData = $schools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($school->id); ?>" <?php echo e($selectedSchoolId == $school->id ? 'selected' : ''); ?>>
                                            <?php echo e($school->nama_sekolah); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <?php if($selectedSchoolId): ?>
                            <a href="<?php echo e(route('manage.users.index')); ?>" class="btn btn-outline-secondary">
                                <i class="fa fa-times me-1"></i> Reset
                            </a>
                            <?php endif; ?>
                        </form>
                    </div>
                    <?php endif; ?>
                    <table class="table table-bordered">
                        <thead>
                            <tr class="table-primary">
                                <th>#</th>
                                <th>Nama</th>
                                <th>Email</th>
                                <th>No. WhatsApp</th>
                                <th>Role</th>
                                <?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
                                <th>Sekolah</th>
                                <?php endif; ?>
                                <th>Dibuat</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($i+1); ?></td>
                                <td><?php echo e($user->name); ?></td>
                                <td><?php echo e($user->email); ?></td>
                                <td><?php echo e($user->nomor_wa ?? '-'); ?></td>
                                <td>
                                    <span class="badge bg-info"><?php echo e(ucfirst($user->role)); ?></span>
                                </td>
                                <?php if(auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan'): ?>
                                <td>
                                    <?php if(in_array($user->role, ['superadmin', 'admin_yayasan'])): ?>
                                        <span class="badge bg-secondary">Semua Sekolah</span>
                                    <?php else: ?>
                                        <?php
                                            $userSchools = $user->schools()->get();
                                        ?>
                                        <?php if($userSchools->count() > 0): ?>
                                            <div class="d-flex flex-wrap gap-1">
                                                <?php $__currentLoopData = $userSchools; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $school): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <span class="badge bg-success"><?php echo e($school->nama_sekolah); ?></span>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </div>
                                        <?php else: ?>
                                            <span class="text-muted">Belum di-assign</span>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <?php endif; ?>
                                <td><?php echo e($user->created_at->format('d/m/Y')); ?></td>
                                <td>
                                    <a href="<?php echo e(route('manage.users.edit', $user->id)); ?>" class="btn btn-sm btn-outline-primary"><i class="fa fa-edit"></i></a>
                                    <form action="<?php echo e(route('manage.users.destroy', $user->id)); ?>" method="POST" style="display:inline" onsubmit="return confirm('Yakin hapus pengguna ini?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="<?php echo e((auth()->user()->role === 'superadmin' || auth()->user()->role === 'admin_yayasan') ? '8' : '7'); ?>" class="text-center py-4">
                                    <p class="text-muted mb-0">Tidak ada data pengguna</p>
                                </td>
                            </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
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
.btn-outline-primary {
    border-color: #2e7d32;
    color: #2e7d32;
}
.btn-outline-primary.active, .btn-outline-primary:active, .btn-outline-primary:focus, .btn-outline-primary:hover {
    background-color: #2e7d32 !important;
    color: #fff !important;
    border-color: #2e7d32 !important;
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
.table-primary {
    background-color: #e8f5e9 !important;
    color: #2e7d32 !important;
}
</style>
<?php $__env->stopSection(); ?> 
<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/users/index.blade.php ENDPATH**/ ?>