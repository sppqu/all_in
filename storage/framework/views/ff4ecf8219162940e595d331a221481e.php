

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-list-alt me-2"></i>Master Pelanggaran</h4>
            <p class="text-muted mb-0">Kelola jenis-jenis pelanggaran siswa</p>
        </div>
        <div class="btn-group">
            <a href="<?php echo e(route('manage.bk.pelanggaran.template')); ?>" class="btn btn-outline-success">
                <i class="fas fa-file-excel me-2"></i>Download Template
            </a>
            <button type="button" class="btn btn-outline-info" data-bs-toggle="modal" data-bs-target="#importModal">
                <i class="fas fa-file-import me-2"></i>Import Excel
            </button>
            <a href="<?php echo e(route('manage.bk.pelanggaran.create')); ?>" class="btn btn-outline-primary">
                <i class="fas fa-plus me-2"></i>Tambah Pelanggaran
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(session('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?php echo e(session('error')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('manage.bk.pelanggaran.index')); ?>">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label small">Kategori</label>
                        <select name="kategori_id" class="form-select">
                            <option value="">Semua Kategori</option>
                            <?php $__currentLoopData = $kategoris; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kategori): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($kategori->id); ?>" <?php echo e(request('kategori_id') == $kategori->id ? 'selected' : ''); ?>>
                                    <?php echo e($kategori->nama); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select name="is_active" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="1" <?php echo e(request('is_active') === '1' ? 'selected' : ''); ?>>Aktif</option>
                            <option value="0" <?php echo e(request('is_active') === '0' ? 'selected' : ''); ?>>Tidak Aktif</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Cari</label>
                        <input type="text" name="search" class="form-control" placeholder="Kode atau Nama..." value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Table -->
    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">#</th>
                            <th width="10%">Kode</th>
                            <th width="25%">Nama Pelanggaran</th>
                            <th width="15%">Kategori</th>
                            <th width="10%" class="text-center">Point</th>
                            <th width="10%" class="text-center">Status</th>
                            <th width="15%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $pelanggaran; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($pelanggaran->firstItem() + $index); ?></td>
                            <td><code><?php echo e($item->kode); ?></code></td>
                            <td>
                                <strong><?php echo e($item->nama); ?></strong>
                                <?php if($item->keterangan): ?>
                                    <br><small class="text-muted"><?php echo e(Str::limit($item->keterangan, 50)); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo e($item->kategori->nama == 'Pelanggaran Ringan' ? 'warning' : ($item->kategori->nama == 'Pelanggaran Sedang' ? 'orange' : 'danger')); ?>">
                                    <?php echo e($item->kategori->nama); ?>

                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger fs-6"><?php echo e($item->point); ?></span>
                            </td>
                            <td class="text-center">
                                <?php if($item->is_active): ?>
                                    <span class="badge bg-success">Aktif</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Tidak Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="<?php echo e(route('manage.bk.pelanggaran.edit', $item->id)); ?>" class="btn btn-outline-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="<?php echo e(route('manage.bk.pelanggaran.destroy', $item->id)); ?>" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus?')">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="btn btn-outline-danger" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <p>Tidak ada data pelanggaran</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php if($pelanggaran->hasPages()): ?>
        <div class="card-footer bg-white py-2">
            <?php echo e($pelanggaran->onEachSide(1)->links('pagination::bootstrap-4')); ?>

        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Import Modal -->
<div class="modal fade" id="importModal" tabindex="-1" aria-labelledby="importModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="<?php echo e(route('manage.bk.pelanggaran.import')); ?>" method="POST" enctype="multipart/form-data">
                <?php echo csrf_field(); ?>
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="importModalLabel">
                        <i class="fas fa-file-import me-2"></i>Import Data Master Pelanggaran
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Petunjuk:</strong>
                        <ul class="mb-0 mt-2">
                            <li>Download template Excel terlebih dahulu</li>
                            <li>Isi data sesuai format template</li>
                            <li>Upload file Excel yang sudah diisi</li>
                            <li>Format file: <strong>.xlsx</strong> atau <strong>.xls</strong></li>
                        </ul>
                    </div>
                    
                    <div class="mb-3">
                        <label for="file" class="form-label">File Excel <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file" name="file" accept=".xlsx,.xls" required>
                        <small class="text-muted">Max: 2MB</small>
                    </div>

                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="replace_existing" name="replace_existing">
                        <label class="form-check-label" for="replace_existing">
                            <strong>Hapus semua data lama</strong> (Data master pelanggaran akan di-reset)
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-upload me-2"></i>Upload & Import
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.bg-orange {
    background-color: #ff9800 !important;
}

/* Pagination - Smaller & Cleaner */
.pagination {
    margin-bottom: 0;
}

.page-link {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.page-item:first-child .page-link,
.page-item:last-child .page-link {
    border-radius: 0.25rem;
}
</style>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/manage/bk/pelanggaran/index.blade.php ENDPATH**/ ?>