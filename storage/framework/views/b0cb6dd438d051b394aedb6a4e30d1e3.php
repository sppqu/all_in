<div class="card border-0 shadow-sm h-100 hover-card">
    <a href="<?php echo e(route('library.book.show', $book->id)); ?>" class="text-decoration-none">
        <div class="position-relative">
            <?php if($book->cover_image): ?>
                <img src="<?php echo e(asset('storage/' . $book->cover_image)); ?>" 
                     class="card-img-top" 
                     alt="<?php echo e($book->judul); ?>"
                     style="height: 200px; object-fit: cover;">
            <?php else: ?>
                <div class="card-img-top bg-gradient d-flex align-items-center justify-content-center"
                     style="height: 200px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <i class="fas fa-book fa-3x text-white opacity-50"></i>
                </div>
            <?php endif; ?>
            <?php if($book->is_featured): ?>
                <span class="position-absolute top-0 end-0 m-2 badge bg-warning">
                    <i class="fas fa-star"></i> Unggulan
                </span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <h6 class="card-title mb-2 text-dark" style="font-size: 0.9rem; min-height: 40px;">
                <?php echo e(Str::limit($book->judul, 50)); ?>

            </h6>
            <p class="card-text small text-muted mb-2">
                <i class="fas fa-user me-1"></i><?php echo e(Str::limit($book->pengarang, 30)); ?>

            </p>
            <div class="d-flex justify-content-between align-items-center">
                <span class="badge" style="background-color: <?php echo e($book->category->warna ?? '#3498db'); ?>20; color: <?php echo e($book->category->warna ?? '#3498db'); ?>;">
                    <?php echo e($book->category->nama_kategori ?? 'Umum'); ?>

                </span>
                <small class="text-muted">
                    <i class="fas fa-eye me-1"></i><?php echo e(number_format($book->total_views)); ?>

                </small>
            </div>
        </div>
    </a>
</div>

<style>
.hover-card {
    transition: all 0.3s ease;
}
.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1.5rem rgba(0,0,0,.15)!important;
}
</style>
<?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/library/partials/book-card.blade.php ENDPATH**/ ?>