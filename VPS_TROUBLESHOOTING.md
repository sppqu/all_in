# 🔧 TROUBLESHOOTING DROPDOWN KAS DI VPS

## ❌ Masalah
Dropdown **Kas** tidak muncul di VPS, padahal di lokal sudah muncul.

---

## 🎯 Penyebab Umum

| No | Penyebab | Probabilitas | Solusi |
|---|---|---|---|
| 1 | **Data kas tidak ada di database VPS** | ⭐⭐⭐⭐⭐ | Insert data kas |
| 2 | **Cache Laravel belum di-clear** | ⭐⭐⭐⭐ | Clear cache |
| 3 | **Kode belum ter-update di VPS** | ⭐⭐⭐ | Git pull/upload |
| 4 | **Migration belum dijalankan** | ⭐⭐ | Run migration |
| 5 | **Database berbeda** | ⭐ | Cek config |

---

## ✅ SOLUSI - METODE 1: Otomatis (Mudah)

### Via Browser (PHP):

1. **Upload file** `vps_check_and_fix.php` ke root folder VPS
2. **Akses via browser**:
   ```
   https://domain-anda.com/vps_check_and_fix.php
   ```
3. Script akan otomatis:
   - ✅ Cek tabel kas
   - ✅ Cek data kas
   - ✅ Auto-insert jika kosong
   - ✅ Tampilkan status

4. **Hapus file** setelah selesai (untuk keamanan):
   ```bash
   rm vps_check_and_fix.php
   ```

### Via Terminal (Shell):

1. **Upload** `vps_fix.sh` dan `vps_check_and_fix.php` ke VPS

2. **Jalankan**:
   ```bash
   cd /path/to/project
   chmod +x vps_fix.sh
   bash vps_fix.sh
   ```

3. **Bersihkan**:
   ```bash
   rm vps_check_and_fix.php vps_fix.sh
   ```

---

## ✅ SOLUSI - METODE 2: Manual (Detail)

### Step 1: Login ke VPS via SSH
```bash
ssh user@ip-vps-anda
cd /path/to/project
```

### Step 2: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 3: Cek Migration
```bash
php artisan migrate:status

# Jika migration belum jalan:
php artisan migrate --force
```

### Step 4: Cek Data Kas
```bash
# Masuk ke tinker
php artisan tinker

# Jalankan query
>>> DB::table('kas')->count()
>>> DB::table('kas')->get()

# Jika kosong (0), exit dulu:
>>> exit
```

### Step 5: Insert Data Kas (Jika Kosong)

**Cara 1 - Via Tinker:**
```bash
php artisan tinker
```
```php
DB::table('kas')->insert([
    'nama_kas' => 'Kas Tunai',
    'jenis_kas' => 'cash',
    'deskripsi' => 'Kas tunai sekolah',
    'saldo' => 0,
    'is_active' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

DB::table('kas')->insert([
    'nama_kas' => 'Bank BRI',
    'jenis_kas' => 'bank',
    'deskripsi' => 'Rekening sekolah di Bank BRI',
    'nomor_rekening' => '1234567890',
    'nama_bank' => 'Bank BRI',
    'saldo' => 0,
    'is_active' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

DB::table('kas')->insert([
    'nama_kas' => 'Bank Mandiri',
    'jenis_kas' => 'bank',
    'deskripsi' => 'Rekening sekolah',
    'nomor_rekening' => '9876543210',
    'nama_bank' => 'Bank Mandiri',
    'saldo' => 0,
    'is_active' => 1,
    'created_at' => now(),
    'updated_at' => now()
]);

exit
```

**Cara 2 - Via SQL:**
```bash
# Login ke MySQL
mysql -u username -p database_name
```
```sql
INSERT INTO kas (nama_kas, jenis_kas, deskripsi, saldo, is_active, created_at, updated_at) 
VALUES 
('Kas Tunai', 'cash', 'Kas tunai sekolah', 0, 1, NOW(), NOW()),
('Bank BRI', 'bank', 'Rekening sekolah di Bank BRI', 0, 1, NOW(), NOW()),
('Bank Mandiri', 'bank', 'Rekening sekolah', 0, 1, NOW(), NOW());

SELECT * FROM kas;
exit;
```

### Step 6: Update Kode (Jika Belum)
```bash
# Pull kode terbaru dari Git
git pull origin main

# Atau upload manual file yang diubah:
# - app/Http/Controllers/Accounting/ExpensePosController.php
```

### Step 7: Optimize
```bash
php artisan optimize
```

### Step 8: Test
1. Buka browser
2. Hard refresh (Ctrl + Shift + R)
3. Buka halaman Pos Penerimaan/Pengeluaran
4. Klik "Tambah Transaksi"
5. Cek dropdown Kas

---

## 🔍 DEBUG - Jika Masih Tidak Muncul

### Cek Log Laravel:
```bash
tail -f storage/logs/laravel.log
```

### Cek Controller Ter-update:
```bash
grep -n "kasList" app/Http/Controllers/Accounting/ExpensePosController.php

# Harus ada baris:
# $kasList = DB::table('kas')->where('is_active', 1)->get();
```

### Cek View:
```bash
grep -n "kasList" resources/views/accounting/expense-pos/index.blade.php

# Harus ada:
# @foreach($kasList ?? [] as $kas)
```

### Test Query Langsung:
```bash
php artisan tinker
```
```php
$kasList = DB::table('kas')->where('is_active', 1)->orderBy('nama_kas')->get();
dd($kasList->count()); // Harus > 0
```

---

## 📝 Checklist

- [ ] Migration `create_kas_table` sudah dijalankan di VPS
- [ ] Tabel `kas` ada di database VPS
- [ ] Ada minimal 1 data kas dengan `is_active = 1`
- [ ] File `ExpensePosController.php` sudah ter-update di VPS
- [ ] Cache Laravel sudah di-clear
- [ ] Browser sudah di-hard refresh
- [ ] Tidak ada error di console browser (F12)
- [ ] Tidak ada error di `storage/logs/laravel.log`

---

## 🆘 Bantuan Tambahan

Jika masih bermasalah, cek:

1. **File Permissions** (VPS):
   ```bash
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   chown -R www-data:www-data storage
   chown -R www-data:www-data bootstrap/cache
   ```

2. **PHP Version**:
   ```bash
   php -v
   # Pastikan >= 8.1
   ```

3. **Database Connection**:
   ```bash
   # Cek .env di VPS
   cat .env | grep DB_
   
   # Test connection
   php artisan tinker
   >>> DB::connection()->getPdo()
   ```

4. **Apache/Nginx Config**:
   - Pastikan document root sudah benar
   - Pastikan .htaccess ada (untuk Apache)

---

## 🎉 Setelah Berhasil

Jangan lupa **hapus file debug**:
```bash
rm vps_check_and_fix.php
rm vps_fix.sh
rm VPS_TROUBLESHOOTING.md
```

---

**Created:** 2025-10-28  
**Last Update:** 2025-10-28


