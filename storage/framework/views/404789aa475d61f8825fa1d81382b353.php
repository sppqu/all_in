

<?php $__env->startSection('title', 'Profil Pengguna'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header fw-bold">Profil Pengguna</div>
                <div class="card-body">
                    <?php if(session('success')): ?>
                        <div class="alert alert-success"><?php echo e(session('success')); ?></div>
                    <?php endif; ?>
                    <form method="POST" action="<?php echo e(route('manage.profile.update')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Nama</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="name" value="<?php echo e(old('name', $user->name)); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Email</label>
                            <div class="col-sm-9">
                                <input type="email" class="form-control" name="email" value="<?php echo e(old('email', $user->email)); ?>" required>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">No. WA</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="nomor_wa" value="<?php echo e(old('nomor_wa', $user->nomor_wa)); ?>">
                            </div>
                        </div>
                        <div class="row mb-3">
                            <label class="col-sm-3 col-form-label">Foto</label>
                            <div class="col-sm-9 d-flex align-items-center" style="gap:12px;">
                                <input type="file" class="form-control" name="avatar" accept="image/*">
                                <?php if($user->avatar_path): ?>
                                    <img src="<?php echo e(asset('storage/'.$user->avatar_path)); ?>" alt="avatar" style="width:48px;height:48px;border-radius:50%;object-fit:cover;">
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/user/profile.blade.php ENDPATH**/ ?>