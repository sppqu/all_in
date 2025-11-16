

<?php $__env->startSection('title', 'Kelola Peminjaman - E-Perpustakaan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📋 Kelola Peminjaman Buku</h4>
            <p class="text-muted mb-0">Manajemen peminjaman dan pengembalian</p>
        </div>
        <div>
            <a href="<?php echo e(route('library.index')); ?>" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-2"></i>Kembali
            </a>
            <a href="<?php echo e(route('manage.library.loans.create')); ?>" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Tambah Peminjaman
            </a>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="fas fa-check-circle me-2"></i><?php echo e(session('success')); ?>

        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($stats['active'])); ?></h3>
                        <p class="mb-0">Sedang Dipinjam</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-book-open fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($stats['overdue'])); ?></h3>
                        <p class="mb-0">Terlambat</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-exclamation-triangle fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0"><?php echo e(number_format($stats['returned_today'])); ?></h3>
                        <p class="mb-0">Dikembalikan Hari Ini</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-check-circle fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white" style="position: relative; padding-right: 90px !important;">
                    <div>
                        <h3 class="mb-0">Rp <?php echo e(number_format($stats['total_fines'])); ?></h3>
                        <p class="mb-0">Total Denda Bulan Ini</p>
                    </div>
                    <div style="position: absolute; top: 15px; right: 10px; width: 64px; height: 64px; display: flex; align-items: center; justify-content: center; border-radius: 8px; background-color: rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-coins fa-2x text-white"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="<?php echo e(route('manage.library.loans.index')); ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-3">
                        <select name="status" class="form-control select-primary">
                            <option value="">Semua Status</option>
                            <option value="dipinjam" <?php echo e(request('status') == 'dipinjam' ? 'selected' : ''); ?>>Dipinjam</option>
                            <option value="dikembalikan" <?php echo e(request('status') == 'dikembalikan' ? 'selected' : ''); ?>>Dikembalikan</option>
                            <option value="hilang" <?php echo e(request('status') == 'hilang' ? 'selected' : ''); ?>>Hilang/Rusak</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select name="overdue" class="form-control select-primary">
                            <option value="">Semua</option>
                            <option value="1" <?php echo e(request('overdue') == '1' ? 'selected' : ''); ?>>Hanya Terlambat</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <input type="text" name="search" class="form-control" 
                               placeholder="Cari nama peminjam atau judul buku..." 
                               value="<?php echo e(request('search')); ?>">
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i>Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Loans Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th>Peminjam</th>
                            <th>Buku</th>
                            <th>Tgl Pinjam</th>
                            <th>Harus Kembali</th>
                            <th>Tgl Kembali</th>
                            <th>Status</th>
                            <th>Denda</th>
                            <th width="15%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $loans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($loans->firstItem() + $index); ?></td>
                            <td>
                                <strong><?php echo e($loan->user->name); ?></strong><br>
                                <small class="text-muted"><?php echo e($loan->user->email); ?></small>
                            </td>
                            <td>
                                <?php echo e(Str::limit($loan->book->judul, 40)); ?><br>
                                <small class="text-muted"><?php echo e($loan->book->pengarang); ?></small>
                            </td>
                            <td><?php echo e(\Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y')); ?></td>
                            <td>
                                <?php echo e(\Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y')); ?>

                                <?php if($loan->isOverdue() && $loan->status == 'dipinjam'): ?>
                                <br><span class="badge bg-danger"><?php echo e($loan->daysOverdue()); ?> hari</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo e($loan->tanggal_kembali_aktual ? \Carbon\Carbon::parse($loan->tanggal_kembali_aktual)->format('d M Y') : '-'); ?></td>
                            <td>
                                <?php if($loan->status == 'dipinjam'): ?>
                                    <?php if($loan->isOverdue()): ?>
                                    <span class="badge bg-danger">Terlambat</span>
                                    <?php else: ?>
                                    <span class="badge bg-primary">Dipinjam</span>
                                    <?php endif; ?>
                                <?php elseif($loan->status == 'dikembalikan'): ?>
                                <span class="badge bg-success">Dikembalikan</span>
                                <?php elseif($loan->status == 'hilang'): ?>
                                <span class="badge bg-dark">Hilang/Rusak</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($loan->denda > 0): ?>
                                <span class="text-danger fw-bold">Rp <?php echo e(number_format($loan->denda)); ?></span>
                                <?php elseif($loan->isOverdue() && $loan->status == 'dipinjam'): ?>
                                <span class="text-warning">Rp <?php echo e(number_format($loan->calculateFine())); ?></span>
                                <?php else: ?>
                                <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($loan->status == 'dipinjam'): ?>
                                <div class="btn-group btn-group-sm">
                                    <button type="button" class="btn btn-success" 
                                            onclick="returnBook(<?php echo e($loan->id); ?>)" title="Kembalikan">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                    <a href="<?php echo e(route('manage.library.loans.edit', $loan->id)); ?>" 
                                       class="btn btn-warning" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </div>
                                <?php else: ?>
                                <a href="<?php echo e(route('manage.library.loans.show', $loan->id)); ?>" 
                                   class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="fas fa-book fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Belum ada data peminjaman</p>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($loans->hasPages()): ?>
            <div class="mt-3">
                <?php echo e($loans->appends(request()->query())->links()); ?>

            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Return Modal -->
<div class="modal fade" id="returnModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="returnForm" method="POST">
                <?php echo csrf_field(); ?>
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title">Kembalikan Buku</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah buku sudah dikembalikan?</p>
                    <div class="mb-3">
                        <label class="form-label">Kondisi Buku</label>
                        <select name="kondisi" class="form-control select-primary" required>
                            <option value="baik">Baik</option>
                            <option value="rusak_ringan">Rusak Ringan</option>
                            <option value="rusak_berat">Rusak Berat</option>
                            <option value="hilang">Hilang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Catatan</label>
                        <textarea name="catatan" class="form-control" rows="3"></textarea>
                    </div>
                    <div id="fineInfo" class="alert alert-warning" style="display:none;">
                        <strong>Denda keterlambatan:</strong> <span id="fineAmount"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Kembalikan Buku</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function returnBook(loanId) {
    const form = document.getElementById('returnForm');
    form.action = `/manage/library/loans/${loanId}/return`;
    
    // Fetch loan details to show fine if any
    fetch(`/manage/library/loans/${loanId}/fine`)
        .then(res => res.json())
        .then(data => {
            if (data.fine > 0) {
                document.getElementById('fineInfo').style.display = 'block';
                document.getElementById('fineAmount').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.fine);
            } else {
                document.getElementById('fineInfo').style.display = 'none';
            }
        });
    
    const modal = new bootstrap.Modal(document.getElementById('returnModal'));
    modal.show();
}
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/library/loans/index.blade.php ENDPATH**/ ?>