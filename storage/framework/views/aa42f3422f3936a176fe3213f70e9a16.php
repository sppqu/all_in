<div class="row">
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Informasi Pembayaran</h6>
        <table class="table table-borderless">
            <tr>
                <td width="140"><strong>Nomor Pembayaran:</strong></td>
                <td><?php echo e($transfer->reference); ?></td>
            </tr>
            <tr>
                <td><strong>Status:</strong></td>
                <td>
                    <span class="badge <?php echo e(\App\Helpers\TransferStatusHelper::getTransferStatusBadge($transfer->status)); ?>">
                        <?php echo e(\App\Helpers\TransferStatusHelper::getTransferStatusText($transfer->status)); ?>

                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Metode Pembayaran:</strong></td>
                <td>Transfer Bank Manual</td>
            </tr>

            <?php if($transfer->confirm_photo): ?>
                <tr>
                    <td><strong>Bukti Transfer:</strong></td>
                    <td>
                        <?php
                            $imagePath = 'storage/' . $transfer->confirm_photo;
                            $fullPath = public_path($imagePath);
                            $imageExists = file_exists($fullPath);
                            
                            // Check for double extension issue (e.g., .jpg.jpg)
                            if (!$imageExists && $transfer->confirm_photo) {
                                $pathInfo = pathinfo($transfer->confirm_photo);
                                $doubleExtensionPath = 'storage/' . $pathInfo['dirname'] . '/' . $pathInfo['filename'] . '.' . $pathInfo['extension'] . '.' . $pathInfo['extension'];
                                $doubleExtensionFullPath = public_path($doubleExtensionPath);
                                if (file_exists($doubleExtensionFullPath)) {
                                    $imagePath = $doubleExtensionPath;
                                    $fullPath = $doubleExtensionFullPath;
                                    $imageExists = true;
                                }
                            }
                        ?>
                        
                        <?php if($imageExists): ?>
                            <button type="button" class="btn btn-sm btn-outline-primary proof-image-btn" 
                                    data-image-url="<?php echo e(asset($imagePath)); ?>"
                                    onclick="viewProofImage('<?php echo e(asset($imagePath)); ?>', <?php echo e($transfer->transfer_id); ?>)">
                                <i class="fas fa-eye me-1"></i>Lihat Bukti
                            </button>
                            <a href="<?php echo e(asset($imagePath)); ?>" 
                               target="_blank" class="btn btn-sm btn-outline-secondary ms-1">
                                <i class="fas fa-download me-1"></i>Download
                            </a>
                        <?php else: ?>
                            <span class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                File bukti transfer tidak ditemukan
                            </span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if($transfer->detail): ?>
                <tr>
                    <td><strong>Catatan:</strong></td>
                    <td><?php echo e($transfer->detail); ?></td>
                </tr>
            <?php endif; ?>

            <tr>
                <td><strong>Tanggal Dibuat:</strong></td>
                <td><?php echo e(\Carbon\Carbon::parse($transfer->created_at)->format('d/m/Y H:i')); ?></td>
            </tr>
            <?php if($transfer->verif_date): ?>
                <tr>
                    <td><strong>Tanggal Verifikasi:</strong></td>
                    <td><?php echo e(\Carbon\Carbon::parse($transfer->verif_date)->format('d/m/Y H:i')); ?></td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    
    <div class="col-md-6">
        <h6 class="text-primary mb-3">Informasi Siswa</h6>
        <table class="table table-borderless">
            <tr>
                <td width="140"><strong>NIS:</strong></td>
                <td><?php echo e($transfer->student_nis); ?></td>
            </tr>
            <tr>
                <td><strong>Nama:</strong></td>
                <td><?php echo e($transfer->student_full_name); ?></td>
            </tr>
            <tr>
                <td><strong>Kelas:</strong></td>
                <td><?php echo e($transfer->class_name ?? 'Kelas tidak ditemukan'); ?></td>
            </tr>
        </table>
    </div>
</div>

<?php if($transferDetails->count() > 0): ?>
    <div class="row mt-3">
        <div class="col-12">
            <h6 class="text-primary mb-3">Detail Tagihan</h6>
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Jenis</th>
                            <th>Nama Tagihan</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__currentLoopData = $transferDetails; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $detail): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td>
                                    <?php if($detail->payment_type == 1): ?>
                                        <span class="badge bg-primary">Bulanan</span>
                                    <?php elseif($detail->payment_type == 2): ?>
                                        <span class="badge bg-info">Bebas</span>
                                    <?php elseif($detail->payment_type == 3 && $detail->is_tabungan == 1): ?>
                                        <span class="badge bg-success">Tabungan</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Lainnya</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if($detail->payment_type == 1): ?>
                                        <?php echo e($detail->pos_name ?? 'N/A'); ?> - <?php echo e($detail->month_name ?? 'N/A'); ?>

                                    <?php elseif($detail->payment_type == 3 && $detail->is_tabungan == 1): ?>
                                        <?php echo e($detail->desc ?? 'Setor Tabungan'); ?>

                                    <?php else: ?>
                                        <?php echo e($detail->pos_name ?? $detail->desc ?? 'N/A'); ?>

                                    <?php endif; ?>
                                </td>
                                <td>
                                    
                                    <span class="badge <?php echo e(\App\Helpers\TransferStatusHelper::getDetailStatusBadge($detail->payment_type, $detail->is_tabungan ?? 0, $transfer->status)); ?>">
                                        <?php echo e(\App\Helpers\TransferStatusHelper::getDetailStatusText($detail->payment_type, $detail->is_tabungan ?? 0, $transfer->status)); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="row mt-4">
    <div class="col-12 text-center">
        <?php if($transfer->status == 1): ?>
            <button type="button" class="btn btn-success me-2" onclick="downloadReceipt(<?php echo e($transfer->transfer_id); ?>)" style="color: white;">
                <i class="fas fa-download me-2" style="color: white;"></i>Cetak Kuitansi
            </button>
        <?php endif; ?>
        
        <?php if($transfer->status == 0): ?>
            <button type="button" class="btn btn-success me-2" data-action="approve" data-payment-id="<?php echo e($transfer->transfer_id); ?>" style="color: white;">
                <i class="fas fa-check me-2" style="color: white;"></i>Verifikasi
            </button>
            <button type="button" class="btn btn-danger me-2" data-action="reject" data-payment-id="<?php echo e($transfer->transfer_id); ?>" style="color: white;">
                <i class="fas fa-times me-2" style="color: white;"></i>Tolak
            </button>
        <?php elseif($transfer->status == 2): ?>
            <span class="badge bg-danger">Pembayaran Ditolak</span>
        <?php elseif($transfer->status == 3): ?>
            <span class="badge bg-secondary">Pembayaran Dibatalkan</span>
        <?php elseif($transfer->status == 4): ?>
            <span class="badge bg-dark">Pembayaran Expired</span>
        <?php endif; ?>
        

    </div>
</div> <?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/online-payment/detail.blade.php ENDPATH**/ ?>