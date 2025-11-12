

<?php $__env->startSection('title', 'E-Perpustakaan'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid px-4 py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold">📚 E-Perpustakaan Digital</h4>
            <p class="text-muted mb-0">Koleksi buku digital lengkap untuk pembelajaran</p>
        </div>
        <div>
            <?php if(auth()->user()->role == 'superadmin' || auth()->user()->role == 'admin'): ?>
            <a href="<?php echo e(route('manage.library.cards.index')); ?>" class="btn btn-outline-success me-2">
                <i class="fas fa-id-card me-2"></i>Cetak Kartu
            </a>
            <a href="<?php echo e(route('manage.library.loans.index')); ?>" class="btn btn-outline-info me-2">
                <i class="fas fa-list-alt me-2"></i>Kelola Peminjaman
            </a>
            <a href="<?php echo e(route('manage.library.books.index')); ?>" class="btn btn-primary">
                <i class="fas fa-cog me-2"></i>Kelola Buku
            </a>
            <?php else: ?>
            <a href="<?php echo e(route('library.card')); ?>" class="btn btn-outline-primary me-2">
                <i class="fas fa-id-card me-2"></i>Kartu Perpustakaan
            </a>
            <a href="<?php echo e(route('library.my-loans')); ?>" class="btn btn-primary">
                <i class="fas fa-book me-2"></i>Peminjaman Saya
            </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-books fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0"><?php echo e(number_format($totalBooks)); ?></h2>
                            <p class="mb-0">Total Buku</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-layer-group fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0"><?php echo e(number_format($totalCategories)); ?></h2>
                            <p class="mb-0">Kategori</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-book-reader fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0"><?php echo e(number_format($activeLoans)); ?></h2>
                            <p class="mb-0">Dipinjam</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="card-body text-white">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-eye fa-3x opacity-50"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h2 class="mb-0"><?php echo e(number_format($totalReads)); ?></h2>
                            <p class="mb-0">Pembacaan</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <form action="<?php echo e(route('library.search')); ?>" method="GET">
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" name="q" class="form-control border-start-0" 
                                   placeholder="Cari judul, pengarang, atau penerbit..." 
                                   value="<?php echo e(request('q')); ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select form-select-lg">
                            <option value="">Semua Kategori</option>
                            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e(request('category') == $cat->id ? 'selected' : ''); ?>>
                                <?php echo e($cat->nama_kategori); ?>

                            </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary btn-lg w-100">
                            <i class="fas fa-search me-2"></i>Cari
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Categories -->
    <div class="mb-4">
        <h5 class="mb-3 fw-bold">📑 Kategori Buku</h5>
        <div class="row g-3">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-2 col-6">
                <a href="<?php echo e(route('library.search', ['category' => $category->id])); ?>" 
                   class="text-decoration-none">
                    <div class="card border-0 shadow-sm h-100 hover-shadow">
                        <div class="card-body text-center">
                            <div class="mb-2" style="color: <?php echo e($category->warna); ?>;">
                                <i class="<?php echo e($category->icon); ?> fa-2x"></i>
                            </div>
                            <h6 class="mb-1" style="font-size: 0.85rem;"><?php echo e($category->nama_kategori); ?></h6>
                            <small class="text-muted"><?php echo e($category->books_count); ?> buku</small>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <?php if($featuredBooks->count() > 0): ?>
    <!-- Featured Books -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">⭐ Buku Unggulan</h5>
            <a href="<?php echo e(route('library.search')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="row g-3">
            <?php $__currentLoopData = $featuredBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-3">
                <?php echo $__env->make('library.partials.book-card', ['book' => $book], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- My Loans Section (User) -->
    <?php if(auth()->user()->role !== 'superadmin' && auth()->user()->role !== 'admin'): ?>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">📖 Peminjaman Saya</h5>
            <a href="<?php echo e(route('library.my-loans')); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <?php
            $myLoans = \App\Models\BookLoan::with('book')
                ->where('user_id', auth()->id())
                ->where('status', 'dipinjam')
                ->latest()
                ->take(3)
                ->get();
        ?>
        
        <?php if($myLoans->count() > 0): ?>
        <div class="row g-3">
            <?php $__currentLoopData = $myLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex">
                            <?php if($loan->book->cover_image): ?>
                            <img src="<?php echo e(asset('storage/' . $loan->book->cover_image)); ?>" 
                                 style="width: 60px; height: 80px; object-fit: cover;" 
                                 class="rounded me-3">
                            <?php else: ?>
                            <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center text-white"
                                 style="width: 60px; height: 80px;">
                                <i class="fas fa-book"></i>
                            </div>
                            <?php endif; ?>
                            <div class="flex-grow-1">
                                <h6 class="mb-1"><?php echo e(Str::limit($loan->book->judul, 40)); ?></h6>
                                <small class="text-muted d-block mb-2"><?php echo e($loan->book->pengarang); ?></small>
                                <div class="d-flex justify-content-between align-items-center">
                                    <small class="text-muted">
                                        Kembali: <?php echo e(\Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y')); ?>

                                    </small>
                                    <?php if($loan->isOverdue()): ?>
                                    <span class="badge bg-danger">
                                        Terlambat <?php echo e($loan->daysOverdue()); ?> hari
                                    </span>
                                    <?php else: ?>
                                    <span class="badge bg-success">Aktif</span>
                                    <?php endif; ?>
                                </div>
                                <?php if($loan->isOverdue()): ?>
                                <div class="mt-2 alert alert-danger py-1 px-2 mb-0">
                                    <small><strong>Denda:</strong> Rp <?php echo e(number_format($loan->calculateFine())); ?></small>
                                </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-4">
                <i class="fas fa-book-reader fa-3x text-muted mb-3"></i>
                <p class="text-muted mb-0">Anda belum memiliki peminjaman aktif</p>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Loans Management (Admin) -->
    <?php if(auth()->user()->role == 'superadmin' || auth()->user()->role == 'admin'): ?>
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">📋 Peminjaman Terbaru</h5>
            <a href="<?php echo e(route('manage.library.loans.index')); ?>" class="btn btn-sm btn-outline-primary">Kelola Semua</a>
        </div>
        <?php
            $recentLoans = \App\Models\BookLoan::with(['book', 'user'])
                ->latest()
                ->take(5)
                ->get();
        ?>
        
        <?php if($recentLoans->count() > 0): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Peminjam</th>
                                <th>Buku</th>
                                <th>Tanggal Pinjam</th>
                                <th>Kembali</th>
                                <th>Status</th>
                                <th>Denda</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $recentLoans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $loan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td><?php echo e($loan->user->name); ?></td>
                                <td><?php echo e(Str::limit($loan->book->judul, 40)); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($loan->tanggal_pinjam)->format('d M Y')); ?></td>
                                <td><?php echo e(\Carbon\Carbon::parse($loan->tanggal_kembali_rencana)->format('d M Y')); ?></td>
                                <td>
                                    <?php if($loan->status == 'dipinjam'): ?>
                                        <?php if($loan->isOverdue()): ?>
                                        <span class="badge bg-danger">Terlambat</span>
                                        <?php else: ?>
                                        <span class="badge bg-primary">Dipinjam</span>
                                        <?php endif; ?>
                                    <?php elseif($loan->status == 'dikembalikan'): ?>
                                    <span class="badge bg-success">Dikembalikan</span>
                                    <?php else: ?>
                                    <span class="badge bg-secondary"><?php echo e(ucfirst($loan->status)); ?></span>
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
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Recent Books -->
    <div class="mb-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0 fw-bold">🆕 Buku Terbaru</h5>
            <a href="<?php echo e(route('library.search', ['sort' => 'latest'])); ?>" class="btn btn-sm btn-outline-primary">Lihat Semua</a>
        </div>
        <div class="row g-3">
            <?php $__currentLoopData = $recentBooks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $book): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="col-md-2">
                <?php echo $__env->make('library.partials.book-card', ['book' => $book], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}
.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.15)!important;
}
</style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.coreui', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/library/dashboard.blade.php ENDPATH**/ ?>