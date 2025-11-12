

<?php $__env->startSection('title', 'Transfer Bank'); ?>

<?php $__env->startSection('content'); ?>
<style>
    .bank-info-card {
        background: linear-gradient(135deg, #198754 0%, #20c997 100%);
        color: white;
        border-radius: 15px;
        border: none;
        box-shadow: 0 4px 20px rgba(25, 135, 84, 0.3);
    }
    
    .bank-detail {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 10px;
        padding: 15px;
        margin-bottom: 15px;
        backdrop-filter: blur(10px);
    }
    
    .bank-detail-label {
        font-size: 0.85rem;
        opacity: 0.8;
        margin-bottom: 5px;
    }
    
    .bank-detail-value {
        font-size: 1.1rem;
        font-weight: 600;
        margin-bottom: 0;
    }
    
    .copy-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 1px solid rgba(255, 255, 255, 0.3);
        color: white;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 0.8rem;
        transition: all 0.3s ease;
    }
    
    .copy-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        color: white;
        border-color: rgba(255, 255, 255, 0.5);
    }
    
    .payment-summary {
        background: white;
        border-radius: 15px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .bill-item {
        border-bottom: 1px solid #f1f3f4;
        padding: 15px 0;
    }
    
    .bill-item:last-child {
        border-bottom: none;
    }
    
    .upload-area {
        border: 2px dashed #dee2e6;
        border-radius: 10px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s ease;
        cursor: pointer;
    }
    
    .upload-area:hover {
        border-color: #198754;
        background-color: #f8f9fa;
    }
    
    .upload-area.dragover {
        border-color: #198754;
        background-color: #e8f5e8;
    }
    
    .file-preview {
        max-width: 200px;
        max-height: 200px;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .instruction-card {
        background: #fff3cd;
        border: 1px solid #ffeaa7;
        border-radius: 10px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .instruction-step {
        display: flex;
        align-items: flex-start;
        margin-bottom: 15px;
    }
    
    .step-number {
        background: #198754;
        color: white;
        width: 25px;
        height: 25px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        font-weight: 600;
        margin-right: 15px;
        flex-shrink: 0;
    }
    
    .step-content {
        flex: 1;
    }
    
    .step-title {
        font-weight: 600;
        margin-bottom: 5px;
        color: #495057;
    }
    
    .step-description {
        color: #6c757d;
        font-size: 0.9rem;
        margin-bottom: 0;
    }
</style>

<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div class="d-flex align-items-center">
            <a href="<?php echo e(route('student.cart')); ?>" class="text-decoration-none me-3">
                <i class="fas fa-arrow-left text-dark" style="font-size: 18px;"></i>
            </a>
            <div>
                <h6 class="mb-0 fw-bold">Transfer Bank</h6>
                <p class="text-muted mb-0">Selesaikan pembayaran melalui transfer bank</p>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Bank Account Information -->
        <div class="col-lg-8">
            <div class="card bank-info-card mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center mb-4">
                        <div class="bg-white bg-opacity-20 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 50px; height: 50px;">
                            <i class="fas fa-university fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="mb-1">Rekening Sekolah</h5>
                            <p class="mb-0 opacity-75">Transfer ke rekening resmi sekolah</p>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="bank-detail">
                                <div class="bank-detail-label">Nama Bank</div>
                                <div class="bank-detail-value"><?php echo e($schoolBank->nama_bank ?? 'BCA'); ?></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="bank-detail">
                                <div class="bank-detail-label">Nomor Rekening</div>
                                <div class="d-flex align-items-center">
                                    <div class="bank-detail-value me-2"><?php echo e($schoolBank->norek_bank ?? '1234567890'); ?></div>
                                    <button class="copy-btn" onclick="copyToClipboard('<?php echo e($schoolBank->norek_bank ?? '1234567890'); ?>')">
                                        <i class="fas fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bank-detail">
                        <div class="bank-detail-label">Atas Nama</div>
                        <div class="bank-detail-value"><?php echo e($schoolBank->nama_rekening ?? 'SMK SPPQU DIGITAL PAYMENT'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Instructions -->
            <div class="instruction-card">
                <h6 class="fw-bold mb-3">
                    <i class="fas fa-info-circle me-2"></i>Instruksi Transfer
                </h6>
                
                <div class="instruction-step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <div class="step-title">Transfer ke Rekening Sekolah</div>
                        <div class="step-description">
                            Transfer sejumlah <strong>Rp <?php echo e(number_format($totalAmount, 0, ',', '.')); ?></strong> ke rekening sekolah yang tertera di atas
                        </div>
                    </div>
                </div>
                
                <div class="instruction-step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <div class="step-title">Upload Bukti Transfer</div>
                        <div class="step-description">
                            Upload bukti transfer di form di bawah ini untuk verifikasi admin
                        </div>
                    </div>
                </div>
                
                <div class="instruction-step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <div class="step-title">Tunggu Persetujuan Admin</div>
                        <div class="step-description">
                            Admin akan memverifikasi pembayaran dalam 1-2 hari kerja
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form -->
            <div class="card payment-summary">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-upload me-2"></i>Upload Bukti Transfer
                    </h6>
                    <p class="text-muted mb-4">Upload bukti transfer yang sudah Anda lakukan ke rekening sekolah</p>
                    
                    <form id="transferForm" method="POST" action="<?php echo e(route('student.bank-transfer.process')); ?>" enctype="multipart/form-data">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="payment_method" value="bank_transfer">
                        <input type="hidden" name="payment_type" value="manual">
                        <input type="hidden" name="total_amount" value="<?php echo e($totalAmount); ?>">
                        
                        <!-- Cart Items as Hidden Inputs -->
                        <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <input type="hidden" name="items[<?php echo e($index); ?>][type]" value="<?php echo e($item['type']); ?>">
                            <input type="hidden" name="items[<?php echo e($index); ?>][id]" value="<?php echo e($item['id']); ?>">
                            <input type="hidden" name="items[<?php echo e($index); ?>][amount]" value="<?php echo e($item['amount']); ?>">
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        

                        
                        <div class="mb-4">
                            <label for="transfer_proof" class="form-label fw-bold">Bukti Transfer</label>
                            <div class="upload-area" id="uploadArea" onclick="document.getElementById('transfer_proof').click()">
                                <div id="uploadContent">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                                    <h6 class="text-muted">Klik untuk upload bukti transfer</h6>
                                    <p class="text-muted mb-0">Format: JPG, JPEG, PNG, PDF (Maks. 2MB)</p>
                                </div>
                                <div id="filePreview" style="display: none;">
                                    <img id="previewImage" class="file-preview mb-2">
                                    <p id="fileName" class="text-success mb-0"></p>
                                </div>
                            </div>
                            <input type="file" id="transfer_proof" name="transfer_proof" accept="image/*,.pdf" style="display: none;" required>
                            <div class="form-text">Upload bukti transfer yang jelas dan lengkap untuk memudahkan verifikasi admin</div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="notes" class="form-label">Catatan (Opsional)</label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Tambahkan catatan jika diperlukan"></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-paper-plane me-2"></i>Kirim Bukti Transfer
                            </button>
                        </div>
                        

                    </form>
                </div>
            </div>
        </div>

        <!-- Payment Summary -->
        <div class="col-lg-4">
            <div class="card payment-summary sticky-top" style="top: 20px;">
                <div class="card-body p-4">
                    <h6 class="fw-bold mb-3">
                        <i class="fas fa-receipt me-2"></i>Ringkasan Pembayaran
                    </h6>
                    
                    <?php $__currentLoopData = $cartItems; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="bill-item">
                            <div class="d-flex justify-content-between align-items-start">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1 fw-bold"><?php echo e($item['name']); ?></h6>
                                    <small class="text-muted"><?php echo e($item['month']); ?></small>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-success"><?php echo e($item['amount']); ?></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    
                    <hr class="my-3">
                    
                    <div class="d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Total Pembayaran</h6>
                        <h5 class="mb-0 fw-bold text-success">Rp <?php echo e(number_format($totalAmount, 0, ',', '.')); ?></h5>
                    </div>
                    
                    <div class="mt-3">
                        <div class="alert alert-info">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                Pembayaran akan diproses setelah admin memverifikasi bukti transfer
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(function() {
            showAlert('Nomor rekening berhasil disalin!', 'success');
        }).catch(function(err) {
            console.error('Could not copy text: ', err);
            showAlert('Gagal menyalin nomor rekening', 'error');
        });
    }
    
    function showAlert(message, type) {
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px; max-width: 400px;';
        alertDiv.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'} me-2"></i>
                <span>${message}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(alertDiv);
        
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 3000);
    }
    
    // File upload handling
    document.getElementById('transfer_proof').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const uploadArea = document.getElementById('uploadArea');
        const uploadContent = document.getElementById('uploadContent');
        const filePreview = document.getElementById('filePreview');
        const previewImage = document.getElementById('previewImage');
        const fileName = document.getElementById('fileName');
        
        if (file) {
            // Validate file size (2MB)
            if (file.size > 2 * 1024 * 1024) {
                showAlert('File terlalu besar. Maksimal 2MB.', 'error');
                this.value = '';
                return;
            }
            
            // Validate file type
            const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
            if (!allowedTypes.includes(file.type)) {
                showAlert('Format file tidak didukung. Gunakan JPG, PNG, atau PDF.', 'error');
                this.value = '';
                return;
            }
            
            fileName.textContent = file.name;
            
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    previewImage.src = e.target.result;
                };
                reader.readAsDataURL(file);
            } else {
                previewImage.src = '/assets/pdf-icon.png'; // You can add a PDF icon
            }
            
            uploadContent.style.display = 'none';
            filePreview.style.display = 'block';
            uploadArea.classList.add('border-success');
        }
    });
    
    // Drag and drop functionality
    const uploadArea = document.getElementById('uploadArea');
    
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    
    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });
    
    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            document.getElementById('transfer_proof').files = files;
            document.getElementById('transfer_proof').dispatchEvent(new Event('change'));
        }
    });
    
    // Simple form validation
    function validateForm() {
        const fileInput = document.getElementById('transfer_proof');
        const submitBtn = document.getElementById('submitBtn');
        const form = document.getElementById('transferForm');
        
        // Check if file is selected
        if (!fileInput.files || fileInput.files.length === 0) {
            showAlert('Pilih file bukti transfer terlebih dahulu.', 'error');
            return false;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
        
        return true;
    }
    
    // Add event listener for form submission
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('transferForm');
        const submitBtn = document.getElementById('submitBtn');
        
        form.addEventListener('submit', function(e) {
            
            const fileInput = document.getElementById('transfer_proof');
            
            if (!fileInput.files || fileInput.files.length === 0) {
                e.preventDefault();
                showAlert('Pilih file bukti transfer terlebih dahulu.', 'error');
                return false;
            }
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';
            
            // Clear cart from localStorage after successful submission
            localStorage.removeItem('studentCart');
            
            // Update cart badge if function exists
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
            
        });
        
        // Check if there's a success message (indicating successful payment)
        const successAlert = document.querySelector('.alert-success');
        if (successAlert) {
            // Clear cart from localStorage if payment was successful
            localStorage.removeItem('studentCart');
            
            // Update cart badge if function exists
            if (typeof updateCartBadge === 'function') {
                updateCartBadge();
            }
        }
    });
    

</script>
<?php $__env->stopPush(); ?> 
<?php echo $__env->make('layouts.student', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sppqu\sppqu_addon\resources\views/student/bank-transfer.blade.php ENDPATH**/ ?>