

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1 fw-bold" style="color: #6f42c1;">
                <i class="fas fa-book me-2"></i>Dashboard E-Jurnal 7KAIH
            </h1>
            <p class="text-muted mb-0">Monitoring & Verifikasi Jurnal Harian Siswa</p>
        </div>
        <div class="d-flex gap-2">
            <div class="dropdown">
                <button class="btn btn-outline-success dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="fas fa-file-alt me-1"></i> Laporan
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="<?php echo e(route('jurnal.guru.laporan-siswa')); ?>">
                        <i class="fas fa-user me-2"></i>Laporan Per Siswa
                    </a></li>
                    <li><a class="dropdown-item" href="<?php echo e(route('jurnal.guru.laporan-kelas')); ?>">
                        <i class="fas fa-users me-2"></i>Laporan Per Kelas
                    </a></li>
                </ul>
            </div>
            <button class="btn btn-outline-secondary" onclick="window.location.reload()">
                <i class="fas fa-sync-alt me-1"></i> Refresh
            </button>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Total Jurnal</p>
                            <h2 class="mb-0 fw-bold"><?php echo e($stats['total_jurnal']); ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="fas fa-book-open fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Pending Verifikasi</p>
                            <h2 class="mb-0 fw-bold"><?php echo e($stats['pending_verifikasi']); ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="fas fa-clock fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Terverifikasi</p>
                            <h2 class="mb-0 fw-bold"><?php echo e($stats['terverifikasi']); ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="fas fa-check-circle fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="card-body text-white">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="mb-1 opacity-75">Draft</p>
                            <h2 class="mb-0 fw-bold"><?php echo e($stats['draft']); ?></h2>
                        </div>
                        <div class="bg-white bg-opacity-25 rounded p-3">
                            <i class="fas fa-edit fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-filter me-2 text-primary"></i>Filter Jurnal
            </h5>
        </div>
        <div class="card-body">
            <form method="GET" action="<?php echo e(route('jurnal.guru.index')); ?>" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-semibold">
                            <i class="fas fa-user me-1"></i>Pilih Siswa
                        </label>
                        <select name="siswa_id" class="form-control" id="siswa_id">
                            <option value="">-- Semua Siswa --</option>
                            <?php $__currentLoopData = $students; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $student): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($student->student_id); ?>" <?php echo e(request('siswa_id') == $student->student_id ? 'selected' : ''); ?>>
                                    <?php echo e($student->student_nis); ?> - <?php echo e($student->student_full_name); ?>

                                    <?php if($student->class): ?>
                                        (<?php echo e($student->class->class_name); ?>)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <small class="text-muted">Total <?php echo e($students->count()); ?> siswa aktif</small>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Status</label>
                        <select name="status" class="form-select">
                            <option value="">Semua Status</option>
                            <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                            <option value="submitted" <?php echo e(request('status') == 'submitted' ? 'selected' : ''); ?>>Pending Verifikasi</option>
                            <option value="verified" <?php echo e(request('status') == 'verified' ? 'selected' : ''); ?>>Terverifikasi</option>
                            <option value="revised" <?php echo e(request('status') == 'revised' ? 'selected' : ''); ?>>Perlu Revisi</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Kelas</label>
                        <select name="kelas_id" class="form-select">
                            <option value="">Semua Kelas</option>
                            <?php $__currentLoopData = $classes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $class): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($class->class_id); ?>" <?php echo e(request('kelas_id') == $class->class_id ? 'selected' : ''); ?>>
                                    <?php echo e($class->class_name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tanggal Dari</label>
                        <input type="date" name="tanggal_dari" class="form-control" value="<?php echo e(request('tanggal_dari')); ?>">
                    </div>

                    <div class="col-md-2">
                        <label class="form-label fw-semibold">Tanggal Sampai</label>
                        <input type="date" name="tanggal_sampai" class="form-control" value="<?php echo e(request('tanggal_sampai')); ?>">
                    </div>
                </div>
                
                <div class="row g-3 mt-2">
                    <div class="col-12 d-flex justify-content-end">
                        <a href="<?php echo e(route('jurnal.guru.index')); ?>" class="btn btn-outline-secondary me-2">
                            <i class="fas fa-redo me-1"></i> Reset
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter me-1"></i> Terapkan Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Jurnal List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0 fw-bold">
                <i class="fas fa-list me-2 text-success"></i>Daftar Jurnal Siswa
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if($jurnals->count() > 0): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Tanggal</th>
                                <th class="py-3">Siswa</th>
                                <th class="py-3">Kelas</th>
                                <th class="py-3 text-center">Jumlah Kegiatan</th>
                                <th class="py-3 text-center">Status</th>
                                <th class="py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $jurnals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jurnal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr>
                                    <td class="px-4 py-3">
                                        <span class="fw-semibold"><?php echo e(\Carbon\Carbon::parse($jurnal->tanggal)->format('d M Y')); ?></span>
                                        <br>
                                        <small class="text-muted"><?php echo e(\Carbon\Carbon::parse($jurnal->tanggal)->isoFormat('dddd')); ?></small>
                                    </td>
                                    <td class="py-3">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm me-2">
                                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <span class="fw-bold"><?php echo e(substr($jurnal->siswa->student_full_name, 0, 1)); ?></span>
                                                </div>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo e($jurnal->siswa->student_full_name); ?></div>
                                                <small class="text-muted">NIS: <?php echo e($jurnal->siswa->student_nis); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-3">
                                        <span class="badge bg-info"><?php echo e($jurnal->siswa->class->class_name ?? '-'); ?></span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <span class="badge bg-secondary"><?php echo e($jurnal->entries->count()); ?> kegiatan</span>
                                    </td>
                                    <td class="py-3 text-center">
                                        <?php if($jurnal->status == 'draft'): ?>
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-edit me-1"></i>Draft
                                            </span>
                                        <?php elseif($jurnal->status == 'submitted'): ?>
                                            <span class="badge bg-warning">
                                                <i class="fas fa-clock me-1"></i>Pending
                                            </span>
                                        <?php elseif($jurnal->status == 'verified'): ?>
                                            <span class="badge bg-success">
                                                <i class="fas fa-check-circle me-1"></i>Verified
                                            </span>
                                        <?php elseif($jurnal->status == 'revised'): ?>
                                            <span class="badge bg-danger">
                                                <i class="fas fa-exclamation-circle me-1"></i>Revisi
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 text-center">
                                        <div class="btn-group" role="group">
                                            <a href="<?php echo e(route('jurnal.guru.show', $jurnal->jurnal_id)); ?>" class="btn btn-sm btn-primary" title="Lihat Detail">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="<?php echo e(route('jurnal.guru.edit', $jurnal->jurnal_id)); ?>" class="btn btn-sm btn-warning" title="Edit Jurnal">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3 border-top">
                    <?php echo e($jurnals->links()); ?>

                </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox text-muted" style="font-size: 4rem;"></i>
                    <p class="text-muted mt-3 mb-0">Tidak ada jurnal ditemukan</p>
                    <small class="text-muted">Coba ubah filter atau tunggu siswa mengisi jurnal</small>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .avatar-sm {
        flex-shrink: 0;
    }
    
    .table > :not(caption) > * > * {
        padding: 0.75rem 1rem;
    }
    
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .badge {
        font-weight: 600;
        padding: 0.35em 0.65em;
    }
    
    .btn-sm {
        padding: 0.25rem 0.75rem;
        font-size: 0.875rem;
    }
</style>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('styles'); ?>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 0.25rem;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 38px;
    color: #495057;
}
.select2-container--default .select2-selection--single .select2-selection__arrow {
    height: 36px;
}
.select2-container {
    width: 100% !important;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->startPush('scripts'); ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
(function() {
    // Wait for page to load
    if (typeof jQuery === 'undefined') {
        console.error('jQuery not loaded!');
        return;
    }

    jQuery(document).ready(function($) {
        console.log('Initializing Select2 for student dropdown...');
        
        // Initialize Select2 for student dropdown
        var $select = $('#siswa_id');
        
        if ($select.length) {
            $select.select2({
                placeholder: '-- Semua Siswa --',
                allowClear: true,
                width: '100%'
            });
            console.log('Select2 initialized successfully');
        } else {
            console.error('Element #siswa_id not found!');
        }
    });
})();
</script>
<?php $__env->stopPush(); ?>


<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/jurnal/guru/index.blade.php ENDPATH**/ ?>