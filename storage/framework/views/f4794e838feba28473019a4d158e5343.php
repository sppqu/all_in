

<?php $__env->startSection('title', 'Pencarian Buku - E-Perpustakaan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h4 class="mb-3 fw-bold">🔍 Pencarian Buku</h4>
        
        <!-- Search Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <form action="<?php echo e(route('library.search')); ?>" method="GET">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <input type="text" name="q" class="form-control" 
                                   placeholder="Cari judul, pengarang, penerbit..." 
                                   value="<?php echo e(request('q')); ?>">
                        </div>
                        <div class="col-md-3">
                            <select name="category" class="form-control select-primary">
                                <option value="">Semua Kategori</option>
                                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category') == $cat->id ? 'selected' : ''); ?>>
                                    <?php echo e($cat->nama_kategori); ?>

                                </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <select name="sort" class="form-control select-primary">
                                <option value="latest" <?php echo e(request('sort') == 'latest' ? 'selected' : ''); ?>>Terbaru</option>
                                <option value="popular" <?php echo e(request('sort') == 'popular' ? 'selected' : ''); ?>>Terpopuler</option>
                                <option value="title" <?php echo e(request('sort') == 'title' ? 'selected' : ''); ?>>Judul A-Z</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-2"></i>Cari
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Results -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">
            <?php if(request('q')): ?>
                Hasil pencarian "<?php echo e(request('q')); ?>" 
            <?php else: ?>
                Semua Buku
            <?php endif; ?>
            <span class="badge bg-primary"><?php echo e($books->total()); ?></span>
        </h5>
    </div>

    <div class="row g-3">
        <?php $__empty_1 = true; $__currentLoopData = $books; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="col-md-2">
            <?php echo $__env->make('library.partials.book-card', ['book' => $book], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="fas fa-search fa-4x text-muted mb-3"></i>
                    <h5>Buku tidak ditemukan</h5>
                    <p class="text-muted">Coba gunakan kata kunci lain atau ubah filter pencarian</p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if($books->hasPages()): ?>
    <div class="mt-4">
        <?php echo e($books->appends(request()->query())->links()); ?>

    </div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.adminty', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/library/search.blade.php ENDPATH**/ ?>