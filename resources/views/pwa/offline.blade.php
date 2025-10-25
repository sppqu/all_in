<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPPQU - Offline</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .offline-container {
            background: white;
            border-radius: 20px;
            padding: 3rem;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        .offline-icon {
            font-size: 5rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        .btn-retry {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-retry:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(40, 167, 69, 0.3);
            color: white;
        }
    </style>
</head>
<body>
    <div class="offline-container">
        <div class="offline-icon">
            <i class="fas fa-wifi-slash"></i>
        </div>
        
        <h2 class="mb-3">Tidak Ada Koneksi Internet</h2>
        
        <p class="text-muted mb-4">
            Maaf, Anda sedang offline. Beberapa fitur SPPQU memerlukan koneksi internet untuk berfungsi dengan baik.
        </p>
        
        <div class="row mb-4">
            <div class="col-6">
                <div class="text-center">
                    <i class="fas fa-check-circle text-success mb-2" style="font-size: 2rem;"></i>
                    <h6>Fitur Tersedia</h6>
                    <small class="text-muted">Dashboard, Riwayat Pembayaran</small>
                </div>
            </div>
            <div class="col-6">
                <div class="text-center">
                    <i class="fas fa-times-circle text-danger mb-2" style="font-size: 2rem;"></i>
                    <h6>Fitur Terbatas</h6>
                    <small class="text-muted">Pembayaran Online, Notifikasi</small>
                </div>
            </div>
        </div>
        
        <button class="btn btn-retry" onclick="window.location.reload()">
            <i class="fas fa-redo me-2"></i>Coba Lagi
        </button>
        
        <div class="mt-4">
            <small class="text-muted">
                <i class="fas fa-info-circle me-1"></i>
                SPPQU akan otomatis tersinkronisasi ketika koneksi internet tersedia
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
