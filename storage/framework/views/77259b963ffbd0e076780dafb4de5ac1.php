<?php $__env->startSection('title','Hak Akses Menu'); ?>

<?php $__env->startSection('content'); ?>
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h6 class="mb-0">Pengaturan Hak Akses Menu per Role</h6>
    <a href="<?php echo e(url()->previous()); ?>" class="btn btn-sm btn-secondary">Kembali</a>
  </div>
  <div class="card-body">
    <div class="alert alert-info mb-3">
      <i class="fa fa-info-circle me-2"></i>
      <strong>Admin</strong> dan <strong>Superadmin</strong> memiliki akses penuh ke semua menu secara default.
    </div>
  </div>
  <form method="post" action="<?php echo e(route('manage.users.role-menu.save')); ?>" id="roleMenuForm">
    <?php echo csrf_field(); ?>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered align-middle">
          <thead>
            <tr>
              <th>Menu</th>
                              <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if(!in_array($role, ['admin', 'superadmin'])): ?>
                    <th class="text-center text-capitalize">
                      <?php if($role === 'kasir'): ?>
                        Kasir
                      <?php elseif($role === 'bendahara'): ?>
                        Bendahara
                      <?php elseif($role === 'spmb_admin'): ?>
                        Admin SPMB
                      <?php elseif($role === 'admin_perpustakaan'): ?>
                        Admin Perpustakaan
                      <?php elseif($role === 'admin_bk'): ?>
                        Admin BK
                      <?php elseif($role === 'admin_jurnal'): ?>
                        Admin Jurnal
                      <?php else: ?>
                        <?php echo e(ucfirst(str_replace('_', ' ', $role))); ?>

                      <?php endif; ?>
                    </th>
                  <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
          </thead>
          <tbody>
            <?php $labels = config('menus'); ?>
            <?php $__currentLoopData = $menuKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
              <tr>
                <td><?php echo e($labels[$key] ?? $key); ?></td>
                <?php $__currentLoopData = $roles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $role): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                  <?php if(!in_array($role, ['admin', 'superadmin'])): ?>
                    <?php
                      $allowed = optional($permissions[$role] ?? collect())->firstWhere('menu_key',$key)->allowed ?? null;
                    ?>
                    <td class="text-center">
                      <input type="checkbox" name="perm[<?php echo e($role); ?>][<?php echo e($key); ?>]" <?php echo e($allowed ? 'checked' : ''); ?>>
                    </td>
                  <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
              </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
          </tbody>
        </table>
      </div>
      <div class="mt-3">
        <button type="submit" class="btn btn-primary">
          <i class="fa fa-save me-2"></i>Simpan
        </button>
      </div>
    </div>
  </form>
</div>


<?php $__env->stopSection(); ?>

<?php $__env->startSection('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form submission with loading state
    const form = document.getElementById('roleMenuForm');
    const submitBtn = form.querySelector('button[type="submit"]');
    
    form.addEventListener('submit', function() {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Menyimpan...';
    });
});
</script>
<?php $__env->stopSection(); ?>



<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/users/role-menu.blade.php ENDPATH**/ ?>