

<?php $__env->startSection('title', $book->judul . ' - E-Perpustakaan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4" style="height: 100vh; display: flex; flex-direction: column;">
    <!-- Header -->
    <div class="mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <a href="<?php echo e(session('is_student') ? route('student.library') : route('library.index')); ?>" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Kembali
                </a>
            </div>
            <div>
                <h5 class="mb-0 fw-bold"><?php echo e($book->judul); ?></h5>
                <small class="text-muted"><?php echo e($book->pengarang); ?></small>
            </div>
            <div>
                <a href="<?php echo e(session('is_student') ? route('student.library.download', $book->id) : route('library.download', $book->id)); ?>" class="btn btn-primary" target="_blank">
                    <i class="fas fa-download me-2"></i>Download PDF
                </a>
            </div>
        </div>
    </div>

    <!-- PDF Viewer -->
    <div class="flex-grow-1" style="min-height: 0;">
        <iframe 
            src="<?php echo e($pdfUrl); ?>" 
            style="width: 100%; height: 100%; border: none; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);"
            title="PDF Viewer">
        </iframe>
    </div>
</div>

<?php $__env->startPush('styles'); ?>
<style>
body {
    overflow: hidden;
}

.pcoded-content, .main-body, .page-wrapper, .page-body {
    height: 100% !important;
}

.container-fluid {
    height: 100%;
    display: flex;
    flex-direction: column;
}
</style>
<?php $__env->stopPush(); ?>

<?php $__env->stopSection(); ?>


<?php echo $__env->make(session('is_student') ? 'layouts.student' : 'layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/library/reader/read.blade.php ENDPATH**/ ?>