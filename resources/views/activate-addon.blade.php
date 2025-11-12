<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aktivasi Addon - SPPQU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .btn-activate {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-activate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn-deactivate {
            background: linear-gradient(45deg, #dc3545, #e74c3c);
            border: none;
            border-radius: 10px;
            padding: 12px 30px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-deactivate:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(220, 53, 69, 0.4);
        }
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 16px;
            border-radius: 20px;
        }
        .loading {
            display: none;
        }
        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 10px;
        }
        .result.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .result.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card">
                    <div class="card-header bg-primary text-white text-center">
                        <h4 class="mb-0">
                            <i class="fas fa-cog me-2"></i>
                            Aktivasi Addon SPPQU
                        </h4>
                    </div>
                    <div class="card-body p-4">
                        <form id="activationForm">
                            <div class="mb-3">
                                <label for="userId" class="form-label">
                                    <i class="fas fa-user me-2"></i>User ID
                                </label>
                                <input type="number" class="form-control" id="userId" name="userId" 
                                       placeholder="Masukkan User ID" required>
                                <div class="form-text">
                                    <i class="fas fa-info-circle me-1"></i>
                                    ID user yang akan diaktifkan addon-nya
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="addonSlug" class="form-label">
                                    <i class="fas fa-puzzle-piece me-2"></i>Addon
                                </label>
                                <select class="form-select" id="addonSlug" name="addonSlug" required>
                                    <option value="">Pilih Addon</option>
                                    <option value="spmb">SPMB (Sistem Penerimaan Mahasiswa Baru)</option>
                                    <option value="bk">Bimbingan Konseling (BK) - Pencatatan Pelanggaran Siswa</option>
                                    <option value="ejurnal-7kaih">E-Jurnal Harian 7KAIH</option>
                                    <option value="library">E-Perpustakaan Digital</option>
                                    <option value="payment-gateway">Payment Gateway</option>
                                    <option value="whatsapp-gateway">WhatsApp Gateway</option>
                                    <option value="analisis-target">Menu Analisis Target</option>
                                    <option value="inventaris">Sistem Inventaris</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="action" class="form-label">
                                    <i class="fas fa-play me-2"></i>Aksi
                                </label>
                                <select class="form-select" id="action" name="action" required>
                                    <option value="activate">Aktifkan Addon</option>
                                    <option value="deactivate">Nonaktifkan Addon</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-activate text-white" id="submitBtn">
                                    <i class="fas fa-check me-2"></i>
                                    <span class="btn-text">Aktifkan Addon</span>
                                </button>
                            </div>
                        </form>
                        
                        <div class="loading text-center mt-3">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="mt-2">Memproses aktivasi...</p>
                        </div>
                        
                        <div class="result" id="result"></div>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-header bg-info text-white">
                        <h6 class="mb-0">
                            <i class="fas fa-link me-2"></i>Link Cepat
                        </h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-2">
                            <strong>Format URL:</strong><br>
                            <code>https://yourdomain.com/activate/{userId}/{addonSlug}</code>
                        </p>
                        <p class="mb-2">
                            <strong>Contoh:</strong><br>
                            <code>https://yourdomain.com/activate/1/spmb</code>
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Ganti <code>yourdomain.com</code> dengan domain Anda
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('activationForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const userId = document.getElementById('userId').value;
            const addonSlug = document.getElementById('addonSlug').value;
            const action = document.getElementById('action').value;
            const submitBtn = document.getElementById('submitBtn');
            const loading = document.querySelector('.loading');
            const result = document.getElementById('result');
            
            if (!userId || !addonSlug) {
                showResult('error', 'Mohon lengkapi semua field!');
                return;
            }
            
            // Show loading
            submitBtn.disabled = true;
            loading.style.display = 'block';
            result.style.display = 'none';
            
            // Update button text based on action
            const btnText = action === 'activate' ? 'Aktifkan Addon' : 'Nonaktifkan Addon';
            document.querySelector('.btn-text').textContent = btnText;
            
            // Update button class based on action
            if (action === 'activate') {
                submitBtn.className = 'btn btn-activate text-white';
            } else {
                submitBtn.className = 'btn btn-deactivate text-white';
            }
            
            // Make request
            const url = action === 'activate' 
                ? `/activate/${userId}/${addonSlug}`
                : `/deactivate/${userId}/${addonSlug}`;
                
            fetch(url, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin'
            })
                .then(response => {
                    // Check if response is ok
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    // Check if response is JSON
                    const contentType = response.headers.get('content-type');
                    if (!contentType || !contentType.includes('application/json')) {
                        throw new Error('Response bukan JSON! Kemungkinan terjadi redirect atau error.');
                    }
                    
                    return response.json();
                })
                .then(data => {
                    loading.style.display = 'none';
                    submitBtn.disabled = false;
                    
                    if (data.success) {
                        showResult('success', data.message);
                    } else {
                        showResult('error', data.error || 'Terjadi kesalahan!');
                    }
                })
                .catch(error => {
                    loading.style.display = 'none';
                    submitBtn.disabled = false;
                    showResult('error', 'Terjadi kesalahan: ' + error.message);
                });
        });
        
        function showResult(type, message) {
            const result = document.getElementById('result');
            result.className = `result ${type}`;
            result.innerHTML = `
                <div class="d-flex align-items-center">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-triangle'} me-2"></i>
                    <span>${message}</span>
                </div>
            `;
            result.style.display = 'block';
        }
        
        // Update button text when action changes
        document.getElementById('action').addEventListener('change', function() {
            const action = this.value;
            const btnText = document.querySelector('.btn-text');
            const submitBtn = document.getElementById('submitBtn');
            
            if (action === 'activate') {
                btnText.textContent = 'Aktifkan Addon';
                submitBtn.className = 'btn btn-activate text-white';
            } else {
                btnText.textContent = 'Nonaktifkan Addon';
                submitBtn.className = 'btn btn-deactivate text-white';
            }
        });
    </script>
</body>
</html>
