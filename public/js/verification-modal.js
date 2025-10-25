/**
 * Verification Modal & Toast System
 * Modern, responsive verification modals with beautiful animations
 */

class VerificationSystem {
    constructor() {
        this.init();
        console.log('VerificationSystem initialized');
    }

    init() {
        this.createToastContainer();
        this.bindEvents();
        console.log('VerificationSystem init completed');
    }

    createToastContainer() {
        if (!document.querySelector('.toast-container')) {
            const toastContainer = document.createElement('div');
            toastContainer.className = 'toast-container';
            document.body.appendChild(toastContainer);
            console.log('Toast container created');
        } else {
            console.log('Toast container already exists');
        }
    }

    bindEvents() {
        // Bind approve buttons
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="approve"]')) {
                e.preventDefault();
                this.showApproveModal(e.target.dataset.paymentId);
            }
            
            if (e.target.matches('[data-action="reject"]')) {
                e.preventDefault();
                this.showRejectModal(e.target.dataset.paymentId);
            }
        });

        // Close modals when clicking outside
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('verification-modal') || 
                e.target.classList.contains('rejection-modal')) {
                this.hideAllModals();
            }
        });

        // Close modals with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                this.hideAllModals();
            }
        });
    }

    showApproveModal(paymentId) {
        const modal = this.createApproveModal(paymentId);
        document.body.appendChild(modal);
        
        // Show modal with animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    showRejectModal(paymentId) {
        const modal = this.createRejectModal(paymentId);
        document.body.appendChild(modal);
        
        // Show modal with animation
        setTimeout(() => {
            modal.classList.add('show');
        }, 10);
    }

    createApproveModal(paymentId) {
        const modal = document.createElement('div');
        modal.className = 'verification-modal';
        modal.innerHTML = `
            <div class="verification-modal-content">
                <div class="verification-modal-header">
                    <h3 class="verification-modal-title">
                        <i class="fas fa-check-circle me-2"></i>
                        Verifikasi Pembayaran
                    </h3>
                </div>
                <div class="verification-modal-body">
                    <div class="verification-modal-message">
                        <p><strong>Apakah Anda yakin ingin memverifikasi pembayaran ini?</strong></p>
                        <p class="mb-0">Setelah diverifikasi, pembayaran akan diproses dan status tagihan akan berubah menjadi lunas.</p>
                    </div>
                    <div class="verification-modal-actions">
                        <button class="verification-btn approve" onclick="verificationSystem.approvePayment(${paymentId})">
                            <i class="fas fa-check me-2"></i>Verifikasi
                        </button>
                        <button class="verification-btn cancel" onclick="verificationSystem.hideAllModals()">
                            <i class="fas fa-times me-2"></i>Batal
                        </button>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }

    createRejectModal(paymentId) {
        const modal = document.createElement('div');
        modal.className = 'rejection-modal';
        modal.innerHTML = `
            <div class="verification-modal-content">
                <div class="verification-modal-header">
                    <h3 class="verification-modal-title">
                        <i class="fas fa-times-circle me-2"></i>
                        Tolak Pembayaran
                    </h3>
                </div>
                <div class="verification-modal-body">
                    <div class="verification-modal-message">
                        <p><strong>Apakah Anda yakin ingin menolak pembayaran ini?</strong></p>
                        <p class="mb-0">Setelah ditolak, pembayaran akan dibatalkan dan status tagihan akan kembali aktif.</p>
                    </div>
                    <div class="verification-modal-actions">
                        <button class="verification-btn reject" onclick="verificationSystem.rejectPayment(${paymentId})">
                            <i class="fas fa-times me-2"></i>Tolak
                        </button>
                        <button class="verification-btn cancel" onclick="verificationSystem.hideAllModals()">
                            <i class="fas fa-arrow-left me-2"></i>Kembali
                        </button>
                    </div>
                </div>
            </div>
        `;
        return modal;
    }

    async approvePayment(paymentId) {
        const approveBtn = document.querySelector(`[onclick="verificationSystem.approvePayment(${paymentId})"]`);
        const originalText = approveBtn.innerHTML;
        
        // Set loading state
        approveBtn.classList.add('loading');
        approveBtn.disabled = true;
        approveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

        try {
            const response = await fetch(`/online-payments/${paymentId}/approve`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', 'Berhasil!', data.message);
                this.hideAllModals();
                
                // Refresh page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showToast('error', 'Gagal!', data.message);
            }

        } catch (error) {
            console.error('Error approving payment:', error);
            this.showToast('error', 'Error!', 'Terjadi kesalahan saat memverifikasi pembayaran');
        } finally {
            // Reset button state
            approveBtn.classList.remove('loading');
            approveBtn.disabled = false;
            approveBtn.innerHTML = originalText;
        }
    }

    async rejectPayment(paymentId) {
        const rejectBtn = document.querySelector(`[onclick="verificationSystem.rejectPayment(${paymentId})"]`);
        const originalText = rejectBtn.innerHTML;
        
        // Set loading state
        rejectBtn.classList.add('loading');
        rejectBtn.disabled = true;
        rejectBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Memproses...';

        try {
            const response = await fetch(`/online-payments/${paymentId}/reject`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json'
                }
            });

            const data = await response.json();

            if (data.success) {
                this.showToast('success', 'Berhasil!', data.message);
                this.hideAllModals();
                
                // Refresh page after 2 seconds
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            } else {
                this.showToast('error', 'Gagal!', data.message);
            }

        } catch (error) {
            console.error('Error rejecting payment:', error);
            this.showToast('error', 'Error!', 'Terjadi kesalahan saat menolak pembayaran');
        } finally {
            // Reset button state
            rejectBtn.classList.remove('loading');
            rejectBtn.disabled = false;
            rejectBtn.innerHTML = originalText;
        }
    }

    hideAllModals() {
        const modals = document.querySelectorAll('.verification-modal, .rejection-modal');
        modals.forEach(modal => {
            modal.classList.remove('show');
            setTimeout(() => {
                if (modal.parentNode) {
                    modal.parentNode.removeChild(modal);
                }
            }, 300);
        });
    }

    showToast(type, title, message) {
        console.log(`Showing toast: ${type} - ${title} - ${message}`);
        
        const toastContainer = document.querySelector('.toast-container');
        if (!toastContainer) {
            console.error('Toast container not found, creating one...');
            this.createToastContainer();
        }
        
        const toast = document.createElement('div');
        
        const icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-exclamation-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };

        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <div class="toast-header">
                <h6 class="toast-title">
                    <i class="toast-icon ${icons[type]}"></i>
                    ${title}
                </h6>
                <button class="toast-close" onclick="this.parentElement.parentElement.remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="toast-body">
                ${message}
            </div>
        `;

        toastContainer.appendChild(toast);
        console.log('Toast added to container');

        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.style.animation = 'slideOutRight 0.4s ease-out';
                setTimeout(() => {
                    if (toast.parentNode) {
                        toast.parentNode.removeChild(toast);
                    }
                }, 400);
            }
        }, 5000);
    }

    // Utility method to show toast from anywhere
    static showToast(type, title, message) {
        console.log(`Static showToast called: ${type} - ${title} - ${message}`);
        if (window.verificationSystem) {
            window.verificationSystem.showToast(type, title, message);
        } else {
            console.error('VerificationSystem not initialized');
        }
    }
}

// Initialize verification system when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    console.log('DOM loaded, initializing VerificationSystem...');
    window.verificationSystem = new VerificationSystem();
    console.log('VerificationSystem assigned to window.verificationSystem');
});

// Global function for external use
window.showVerificationToast = (type, title, message) => {
    console.log(`Global showVerificationToast called: ${type} - ${title} - ${message}`);
    if (window.verificationSystem) {
        window.verificationSystem.showToast(type, title, message);
    } else {
        console.error('VerificationSystem not available');
    }
};

// Test function to verify toast system
window.testToast = () => {
    console.log('Testing toast system...');
    if (window.verificationSystem) {
        window.verificationSystem.showToast('success', 'Test Berhasil!', 'Sistem toast berfungsi dengan baik!');
    } else {
        console.error('VerificationSystem not available for testing');
    }
};
