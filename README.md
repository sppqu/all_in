# 📚 SPPQU - Sistem Pembayaran SPP berbasis QU (QRIS & Universal)

![SPPQU](public/images/logo-sppqu.svg)

**SPPQU** adalah sistem manajemen sekolah terintegrasi yang mencakup pembayaran SPP, SPMB (Sistem Penerimaan Mahasiswa Baru), bimbingan konseling, perpustakaan digital, dan berbagai modul pendukung administrasi sekolah/kampus.

---

## 📋 Daftar Isi

- [Tentang SPPQU](#tentang-sppqu)
- [Fitur Utama](#fitur-utama)
- [Teknologi](#teknologi)
- [Persyaratan Sistem](#persyaratan-sistem)
- [Instalasi](#instalasi)
- [Konfigurasi](#konfigurasi)
- [Modul-Modul](#modul-modul)
- [Payment Gateway](#payment-gateway)
- [API Documentation](#api-documentation)
- [Deployment](#deployment)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)
- [Support](#support)

---

## 🎯 Tentang SPPQU

**SPPQU** (SPP QU) adalah platform manajemen sekolah/kampus modern yang dirancang untuk:

- ✅ Mempermudah pembayaran SPP dengan QRIS & Virtual Account
- ✅ Mengelola penerimaan mahasiswa/siswa baru (SPMB)
- ✅ Mencatat pelanggaran dan bimbingan konseling siswa
- ✅ Mengelola perpustakaan digital
- ✅ Membuat jurnal harian kelas
- ✅ Manajemen keuangan terintegrasi
- ✅ Notifikasi otomatis via WhatsApp
- ✅ Laporan & analitik real-time

**Target Pengguna:**
- Sekolah (SD, SMP, SMA/SMK)
- Perguruan Tinggi
- Pesantren
- Lembaga Pendidikan lainnya

**Dikembangkan dengan ❤️ oleh Tim SPPQU**

---

## ⭐ Fitur Utama

### 🎓 **Akademik**
- Manajemen siswa/mahasiswa
- Manajemen kelas & jurusan
- Manajemen periode akademik
- Absensi siswa

### 💰 **Keuangan**
- Pembayaran SPP dengan QRIS & VA
- Payment Gateway terintegrasi (iPaymu, Midtrans, Tripay)
- Pembayaran bulanan otomatis
- Pembayaran bebas (custom amount)
- Laporan keuangan lengkap
- Jurnal akuntansi

### 🎯 **SPMB (Penerimaan Mahasiswa Baru)**
- Form pendaftaran online
- Upload dokumen digital
- Pembayaran biaya pendaftaran
- Seleksi gelombang (wave system)
- Notifikasi real-time
- Dashboard tracking 5 tahap

### 📚 **Perpustakaan Digital**
- Katalog buku digital
- Sistem peminjaman online
- QR Code untuk buku
- Riwayat peminjaman
- Notifikasi jatuh tempo
- Statistik pembaca

### 👥 **Bimbingan Konseling**
- Pencatatan pelanggaran siswa
- Kategori pelanggaran
- Poin pelanggaran
- Riwayat konseling
- Laporan per siswa
- Dashboard monitoring

### 📝 **E-Jurnal Harian**
- Jurnal kelas harian
- Absensi terintegrasi
- Catatan pembelajaran
- Upload materi
- Timeline aktivitas

### 🔔 **Notifikasi**
- WhatsApp Gateway terintegrasi
- Notifikasi pembayaran
- Reminder jatuh tempo
- Notifikasi pendaftaran SPMB
- Custom broadcast

### 📊 **Laporan & Analitik**
- Dashboard real-time
- Laporan pembayaran
- Laporan tunggakan
- Analisis target
- Export Excel/PDF
- Grafik & chart

### 🛡️ **Keamanan**
- Multi-role authentication (Superadmin, Admin, Operator, Guru, Siswa)
- Permission-based access control
- Activity logging
- OTP verification
- Session management
- CSRF protection

### 📱 **Progressive Web App (PWA)**
- Install as app
- Offline support
- Push notifications
- Mobile responsive
- Fast loading

---

## 🛠️ Teknologi

### **Backend:**
- **Framework:** Laravel 11.x
- **PHP:** 8.2+
- **Database:** MySQL 8.0+
- **Queue:** Redis (optional)
- **Cache:** Redis/File

### **Frontend:**
- **UI Framework:** Bootstrap 5.3
- **Admin Template:** CoreUI
- **Icons:** Font Awesome 6.0
- **Charts:** Chart.js
- **DataTables:** jQuery DataTables

### **Payment Gateway:**
- **iPaymu** (QRIS, Virtual Account)
- **Midtrans** (Multiple payment methods)
- **Tripay** (Payment aggregator)

### **Third-Party Services:**
- **WhatsApp Gateway:** Fonnte
- **Storage:** Local/Cloud
- **PDF Generation:** DomPDF
- **Excel:** PhpSpreadsheet

### **DevOps:**
- **Web Server:** Nginx/Apache
- **Process Manager:** PHP-FPM
- **Version Control:** Git
- **CI/CD:** GitHub Actions (optional)

---

## 💻 Persyaratan Sistem

### **Minimum Requirements:**

| Komponen | Spesifikasi |
|----------|-------------|
| **PHP** | 8.2 atau lebih tinggi |
| **MySQL** | 8.0 atau lebih tinggi |
| **Web Server** | Nginx 1.18+ atau Apache 2.4+ |
| **RAM** | 2 GB (4 GB recommended) |
| **Storage** | 10 GB (20 GB recommended) |
| **SSL Certificate** | Required untuk production |

### **PHP Extensions Required:**
```
- BCMath
- Ctype
- JSON
- Mbstring
- OpenSSL
- PDO
- Tokenizer
- XML
- GD
- Zip
- cURL
```

### **Composer:**
```bash
composer 2.x
```

### **Node.js (untuk build assets):**
```bash
Node.js 18.x atau lebih tinggi
npm 9.x atau lebih tinggi
```

---

## 🚀 Instalasi

### **1. Clone Repository**

```bash
git clone https://github.com/sppqu/all_in.git
cd all_in
```

### **2. Install Dependencies**

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### **3. Setup Environment**

```bash
# Copy .env example
cp .env.example .env

# Generate application key
php artisan key:generate
```

### **4. Configure Database**

Edit file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sppqu_db
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

### **5. Create Database**

```bash
mysql -u root -p
```

```sql
CREATE DATABASE sppqu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
EXIT;
```

### **6. Run Migrations**

```bash
php artisan migrate --seed
```

### **7. Build Assets**

```bash
# Development
npm run dev

# Production
npm run build
```

### **8. Setup Storage**

```bash
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### **9. Start Development Server**

```bash
php artisan serve
```

Akses aplikasi di: `http://localhost:8000`

### **10. Default Login**

```
Email: admin@sppqu.com
Password: password
```

**⚠️ Segera ganti password default setelah login!**

---

## ⚙️ Konfigurasi

### **1. Payment Gateway**

#### **iPaymu Configuration:**

```env
IPAYMU_API_KEY=your_ipaymu_api_key
IPAYMU_VA=your_ipaymu_va
IPAYMU_SANDBOX=false
```

**Cara dapat credentials:**
1. Register di https://ipaymu.com
2. Login ke dashboard
3. Menu Settings → API Key
4. Copy API Key & VA

#### **Midtrans Configuration:**

```env
MIDTRANS_SERVER_KEY=your_server_key
MIDTRANS_CLIENT_KEY=your_client_key
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true
```

#### **Tripay Configuration:**

```env
TRIPAY_API_KEY=your_api_key
TRIPAY_PRIVATE_KEY=your_private_key
TRIPAY_MERCHANT_CODE=your_merchant_code
TRIPAY_SANDBOX=false
```

### **2. WhatsApp Gateway (Fonnte)**

```env
FONNTE_TOKEN=your_fonnte_token
```

**Setup Fonnte:**
1. Register di https://fonnte.com
2. Connect WhatsApp number
3. Copy token dari dashboard

### **3. Mail Configuration**

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@sppqu.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### **4. Queue Configuration (Optional)**

```env
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

**Start queue worker:**
```bash
php artisan queue:work --daemon
```

### **5. Session & Cache**

```env
SESSION_DRIVER=file
SESSION_LIFETIME=120

CACHE_DRIVER=file
```

**For production:**
```env
SESSION_DRIVER=redis
CACHE_DRIVER=redis
```

---

## 📦 Modul-Modul

### **1. SPMB (Sistem Penerimaan Mahasiswa Baru)**

**Path:** `/spmb`

**Fitur:**
- Form pendaftaran 5 tahap
- Upload dokumen (KTP, KK, Ijazah, Rapor, Foto)
- Pembayaran biaya pendaftaran
- Pilihan jurusan & gelombang
- Dashboard tracking progress
- Notifikasi real-time
- Print formulir pendaftaran

**Role akses:**
- Admin: Kelola semua pendaftar
- Calon siswa: Isi form & bayar

**Files terkait:**
- Controller: `app/Http/Controllers/SPMBController.php`
- Models: `app/Models/SPMB*.php`
- Views: `resources/views/spmb/`
- Routes: `routes/spmb.php`

---

### **2. Bimbingan Konseling (BK)**

**Path:** `/manage/bk`

**Fitur:**
- Pencatatan pelanggaran siswa
- Kategori pelanggaran dengan poin
- Riwayat konseling
- Laporan per siswa
- Dashboard monitoring
- Export laporan

**Role akses:**
- Admin BK: Full access
- Guru: View & add pelanggaran
- Siswa: View own history

**Files terkait:**
- Models: `app/Models/Pelanggaran*.php`
- Views: `resources/views/bk/`
- Routes: `routes/pelanggaran.php`

---

### **3. E-Perpustakaan Digital**

**Path:** `/manage/library`

**Fitur:**
- Katalog buku dengan kategori
- Sistem peminjaman online
- QR Code untuk setiap buku
- Riwayat peminjaman
- Notifikasi jatuh tempo
- Statistik pembaca
- Denda otomatis

**Role akses:**
- Admin Perpustakaan: Kelola buku
- Siswa/Guru: Pinjam buku

**Files terkait:**
- Models: `app/Models/Book*.php`, `app/Models/ReadingHistory.php`
- Views: `resources/views/library/`
- Routes: `routes/library.php`

---

### **4. E-Jurnal Harian**

**Path:** `/manage/jurnal`

**Fitur:**
- Jurnal per kelas per hari
- Absensi siswa
- Materi pembelajaran
- Catatan guru
- Upload file materi
- Kategori jurnal
- Timeline aktivitas

**Role akses:**
- Guru: Create & edit jurnal
- Admin: View all jurnal
- Siswa: View jurnal kelas sendiri

**Files terkait:**
- Models: `app/Models/JurnalHarian.php`, `app/Models/JurnalKategori.php`
- Views: `resources/views/jurnal/`
- Routes: `routes/jurnal.php`

---

### **5. Payment Gateway**

**Path:** `/manage/payments`

**Fitur:**
- Multiple payment methods
- QRIS (scan & pay)
- Virtual Account (BRI, BCA, Mandiri, BNI)
- Pembayaran bulanan
- Pembayaran bebas
- Riwayat transaksi
- Auto-verify payment
- Webhook handler

**Supported Gateways:**
- ✅ iPaymu (Primary)
- ✅ Midtrans
- ✅ Tripay

**Files terkait:**
- Service: `app/Services/IpaymuService.php`
- Models: `app/Models/Payment.php`, `app/Models/OnlinePayment.php`
- Callback: `routes/callback.php`

---

### **6. Addon System**

**Path:** `/manage/addons`

**Fitur:**
- Buy addons (one-time purchase)
- Addon activation per user
- Payment via QRIS
- Auto-activation after payment
- Addon permissions

**Available Addons:**
- SPMB
- Bimbingan Konseling
- E-Jurnal Harian
- E-Perpustakaan
- Payment Gateway
- WhatsApp Gateway
- Analisis Target
- Inventaris

**Files terkait:**
- Models: `app/Models/Addon.php`, `app/Models/UserAddon.php`
- Views: `resources/views/addons/`

---

### **7. Subscription System**

**Path:** `/manage/subscription`

**Fitur:**
- Monthly/yearly subscription
- Payment via iPaymu
- Auto-renewal
- Subscription reminder (30 days before expiry)
- Grace period
- Payment history

**Files terkait:**
- Models: `app/Models/Subscription.php`, `app/Models/SubscriptionInvoice.php`
- Views: `resources/views/subscription/`

---

## 💳 Payment Gateway

### **iPaymu Integration**

**Flow pembayaran:**

```
1. User pilih pembayaran
2. System create payment request ke iPaymu
3. iPaymu return QRIS/VA
4. User bayar via e-wallet/bank
5. iPaymu send callback
6. System verify & update payment status
7. Auto-activate addon/subscription
```

**Callback Handler:**
- URL: `/api/manage/ipaymu/callback`
- Method: POST
- Verification: Signature check

**Testing:**

```bash
# Test iPaymu API
php test_ipaymu_api.php

# Test SPMB Payment
php test_spmb_payment.php

# Toggle Sandbox Mode
php toggle_ipaymu_sandbox.php
```

### **Payment Methods:**

| Method | Code | Description |
|--------|------|-------------|
| **QRIS** | `QRIS` | Scan QR dengan e-wallet |
| **BRI VA** | `BRIVA` | Virtual Account BRI |
| **BCA VA** | `BCAVA` | Virtual Account BCA |
| **Mandiri VA** | `MANDIRIVA` | Virtual Account Mandiri |
| **BNI VA** | `BNIVA` | Virtual Account BNI |

---

## 📡 API Documentation

### **Authentication**

```http
POST /api/login
Content-Type: application/json

{
  "email": "user@example.com",
  "password": "password"
}
```

**Response:**
```json
{
  "success": true,
  "token": "Bearer token_here",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "user@example.com",
    "role": "admin"
  }
}
```

### **Payment Endpoints**

#### **Create Payment**

```http
POST /api/payments/create
Authorization: Bearer {token}
Content-Type: application/json

{
  "student_id": 123,
  "amount": 500000,
  "type": "monthly",
  "payment_method": "QRIS"
}
```

#### **Check Payment Status**

```http
GET /api/payments/{reference_id}/status
Authorization: Bearer {token}
```

#### **Payment Callback**

```http
POST /api/manage/ipaymu/callback
Content-Type: application/json

{
  "trx_id": "...",
  "status": "success",
  "amount": 500000,
  ...
}
```

### **SPMB Endpoints**

#### **Register**

```http
POST /api/spmb/register
Content-Type: application/json

{
  "name": "John Doe",
  "phone": "08123456789",
  "password": "password"
}
```

#### **Submit Data**

```http
POST /api/spmb/submit
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "step": 1,
  "data": {
    "nik": "1234567890123456",
    "birth_place": "Jakarta",
    ...
  }
}
```

---

## 🚀 Deployment

### **1. Server Requirements**

**Recommended VPS:**
- CPU: 2 cores
- RAM: 4 GB
- Storage: 20 GB SSD
- OS: Ubuntu 22.04 LTS

### **2. Install Stack (LEMP)**

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring \
  php8.2-xml php8.2-bcmath php8.2-curl php8.2-gd php8.2-zip -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install nodejs -y
```

### **3. Deploy Application**

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/sppqu/all_in.git sppqu
cd sppqu

# Set permissions
sudo chown -R www-data:www-data /var/www/sppqu
sudo chmod -R 755 /var/www/sppqu

# Install dependencies
composer install --no-dev --optimize-autoloader
npm install
npm run build

# Setup environment
cp .env.example .env
php artisan key:generate

# Configure database
nano .env
# Edit DB_* values

# Run migrations
php artisan migrate --force

# Setup storage
php artisan storage:link
chmod -R 775 storage bootstrap/cache

# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **4. Configure Nginx**

```bash
sudo nano /etc/nginx/sites-available/sppqu
```

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/sppqu/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/sppqu /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### **5. Setup SSL (Let's Encrypt)**

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com
```

### **6. Setup Cron Jobs**

```bash
sudo crontab -e
```

Add:
```
* * * * * cd /var/www/sppqu && php artisan schedule:run >> /dev/null 2>&1
```

### **7. Setup Supervisor (Queue Worker)**

```bash
sudo apt install supervisor -y
sudo nano /etc/supervisor/conf.d/sppqu-worker.conf
```

```ini
[program:sppqu-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sppqu/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sppqu/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sppqu-worker:*
```

---

## 🔧 Troubleshooting

### **1. Database Connection Error**

```bash
# Run fix script
php vps_fix_database.php
```

### **2. Migration Error**

```bash
# Fix migration error
php vps_fix_migration_error.php
php artisan migrate --force
```

### **3. View Path Not Found**

```bash
# Fix view path
php vps_fix_view_path.php
```

### **4. Permission Issues**

```bash
# Fix permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### **5. Clear Cache**

```bash
# Safe cache clear
bash vps_clear_cache_safe.sh

# Or manual
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
```

### **6. Check Logs**

```bash
# Laravel logs
tail -f storage/logs/laravel.log

# Nginx logs
sudo tail -f /var/log/nginx/error.log

# PHP-FPM logs
sudo tail -f /var/log/php8.2-fpm.log
```

---

## 🤝 Contributing

Kami menerima kontribusi! Silakan ikuti langkah berikut:

1. Fork repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

**Coding Standards:**
- Follow PSR-12
- Write tests untuk fitur baru
- Update documentation

---

## 📄 License

SPPQU adalah software proprietary. 

**© 2024 SPPQU. All rights reserved.**

Untuk lisensi komersial, hubungi: [support@sppqu.com](mailto:support@sppqu.com)

---

## 📞 Support

**Website:** https://sppqu.com  
**Email:** support@sppqu.com  
**WhatsApp:** +62 812-3456-7890  
**Documentation:** https://docs.sppqu.com  
**GitHub:** https://github.com/sppqu/all_in

---

## 🙏 Credits

**Developed by:**
- Tim SPPQU Development Team

**Built with:**
- Laravel Framework
- Bootstrap
- CoreUI
- Font Awesome
- Chart.js

**Special Thanks:**
- Semua pengguna SPPQU
- Contributors & testers
- Open source community

---

**Made with ❤️ in Indonesia 🇮🇩**

**Version:** 2.0.0  
**Last Updated:** October 2024

---

## 📊 Statistics

![GitHub stars](https://img.shields.io/github/stars/sppqu/all_in)
![GitHub forks](https://img.shields.io/github/forks/sppqu/all_in)
![GitHub issues](https://img.shields.io/github/issues/sppqu/all_in)
![License](https://img.shields.io/badge/license-Proprietary-red)

---

**🚀 Start using SPPQU today and transform your school management!**

