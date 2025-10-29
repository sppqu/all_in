# 🔧 VPS Database Connection Fix Guide

## 🔴 Error yang Terjadi:
```
SQLSTATE[HY000] [1045] Access denied for user 'root'@'localhost' (using password: NO)
```

**Artinya:** Database tidak terhubung atau kredensial salah.

---

## ✅ SOLUSI CEPAT (3 Steps):

### **Step 1: Check Kredensial Database**

Di VPS, jalankan:
```bash
cd /www/wwwroot/srx.sppqu/all_in
cat .env | grep DB_
```

Jika kosong atau salah, lanjut ke Step 2.

---

### **Step 2: Fix Database Config**

**Opsi A - Pakai Script PHP (Recommended):**
```bash
php vps_fix_database.php
```

Script akan:
- ✅ Tanya kredensial database
- ✅ Update file .env
- ✅ Test koneksi
- ✅ Jalankan migration (optional)

**Opsi B - Pakai Script Bash:**
```bash
chmod +x vps_fix_database.sh
./vps_fix_database.sh
```

**Opsi C - Manual Edit:**
```bash
nano .env
```

Update bagian ini:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

Simpan: `Ctrl+X` → `Y` → `Enter`

---

### **Step 3: Clear Cache & Migrate**

```bash
php artisan config:clear
php artisan cache:clear
php artisan migrate --force
```

---

## 📋 JIKA DATABASE BELUM ADA:

### **1. Login ke MySQL:**
```bash
mysql -u root -p
```

### **2. Buat Database:**
```sql
CREATE DATABASE sppqu_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### **3. Buat User & Berikan Akses:**
```sql
CREATE USER 'sppqu_user'@'localhost' IDENTIFIED BY 'your_strong_password';
GRANT ALL PRIVILEGES ON sppqu_db.* TO 'sppqu_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### **4. Update .env:**
```env
DB_DATABASE=sppqu_db
DB_USERNAME=sppqu_user
DB_PASSWORD=your_strong_password
```

---

## 🔍 TROUBLESHOOTING:

### **Problem 1: MySQL Service Not Running**
```bash
# Check status
systemctl status mysql

# Start MySQL
systemctl start mysql

# Enable auto-start
systemctl enable mysql
```

### **Problem 2: Permission Denied**
```bash
# Check user permissions
mysql -u root -p
```
```sql
SHOW GRANTS FOR 'your_username'@'localhost';
```

### **Problem 3: Database Doesn't Exist**
```bash
# List all databases
mysql -u root -p -e "SHOW DATABASES;"

# Create if not exists
mysql -u root -p -e "CREATE DATABASE your_database_name;"
```

### **Problem 4: Wrong Password**
```bash
# Reset password
mysql -u root -p
```
```sql
ALTER USER 'your_username'@'localhost' IDENTIFIED BY 'new_password';
FLUSH PRIVILEGES;
```

---

## 🧪 TEST CONNECTION:

### **Test 1: Pakai Artisan**
```bash
php artisan db:show
```

Jika berhasil, akan muncul info database:
```
MySQL 8.0.x .................................. your_database_name
```

### **Test 2: Pakai PHP**
```bash
php -r "
\$pdo = new PDO('mysql:host=127.0.0.1;dbname=sppqu_db', 'sppqu_user', 'password');
echo 'Connection OK!';
"
```

### **Test 3: Check Tables**
```bash
php artisan tinker
```
```php
DB::connection()->getPdo();
DB::select('SHOW TABLES');
```

---

## 📊 COMMON .env VALUES DI VPS:

### **BT-Panel (宝塔):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=website_database
DB_USERNAME=website_user
DB_PASSWORD=bt_generated_password
```

### **cPanel:**
```env
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpanel_dbname
DB_USERNAME=cpanel_dbuser
DB_PASSWORD=cpanel_password
```

### **Direct VPS (Manual Install):**
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_app_db
DB_USERNAME=your_app_user
DB_PASSWORD=strong_password_here
```

---

## ⚠️ SECURITY TIPS:

1. **Jangan pakai root user** untuk aplikasi
2. **Gunakan strong password** (min 16 karakter)
3. **Beri permission spesifik**, jangan `GRANT ALL`
4. **Backup .env** sebelum edit
5. **Jangan commit .env** ke git

---

## 🎯 CHECKLIST:

- [ ] MySQL service running
- [ ] Database sudah dibuat
- [ ] User & password benar
- [ ] User punya permission
- [ ] File .env sudah diupdate
- [ ] Cache sudah di-clear
- [ ] Connection test berhasil
- [ ] Migration jalan sukses

---

## 📞 QUICK COMMANDS:

```bash
# All-in-one fix
cd /www/wwwroot/srx.sppqu/all_in
php vps_fix_database.php

# Manual check
cat .env | grep DB_
php artisan db:show

# Clear & migrate
php artisan config:clear
php artisan cache:clear  
php artisan migrate --force

# Restart services
systemctl restart mysql
systemctl restart php8.2-fpm
systemctl restart nginx  # or apache2
```

---

## ✅ AFTER FIX:

Setelah database berhasil terkoneksi:

1. **Pull latest code:**
   ```bash
   git pull origin main
   ```

2. **Run migrations:**
   ```bash
   php artisan migrate --force
   ```

3. **Restart services:**
   ```bash
   systemctl restart php8.2-fpm
   ```

4. **Test di browser:**
   ```
   https://srx.sppqu.my.id
   ```

---

**🎉 Database connection fixed!**

