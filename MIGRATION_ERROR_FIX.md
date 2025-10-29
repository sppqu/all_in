# 🔧 Migration Error Fix Guide

## 🔴 ERROR YANG TERJADI:

```
SQLSTATE[42000]: Syntax error or access violation: 1067 
Invalid default value for 'due_date'
Migration: add_payment_reference_to_subscription_invoices_table
```

---

## 🎯 PENYEBAB ERROR:

1. **MySQL Strict Mode** yang ketat
2. Tabel existing punya kolom `due_date` dengan **invalid default value** (`0000-00-00`)
3. Saat migration mencoba ALTER TABLE, MySQL reject karena strict mode

---

## ✅ SOLUSI OTOMATIS (RECOMMENDED):

### **Step 1: Pull Latest Fix**
```bash
cd /www/wwwroot/srx.sppqu/all_in
git pull origin main
```

### **Step 2: Run Migration (Fixed Version)**
```bash
php artisan migrate --force
```

**Migration sudah diperbaiki dengan:**
- ✅ Disable strict mode sementara
- ✅ Check column existence sebelum alter
- ✅ Restore strict mode setelah selesai

---

## 🔧 SOLUSI MANUAL (Jika Masih Error):

### **Opsi A: Pakai Script Fix**

```bash
php vps_fix_migration_error.php
```

**Script akan:**
- ✅ Check tabel subscription_invoices
- ✅ Fix kolom due_date yang bermasalah
- ✅ Prepare untuk migration

**Output:**
```
🔧 Fix Migration Error - Invalid Default Value
===============================================

🔍 Checking subscription_invoices table...
✅ Table exists

📋 Current columns:
  - id (bigint)
  - due_date (date)
    ⚠️  Invalid default value detected!
  ...

🔧 Fixing due_date column...
✅ due_date column fixed!

✅ Fix completed! Now run migration:
   php artisan migrate --force
```

### **Opsi B: Manual SQL Fix**

```bash
mysql -u your_username -p your_database
```

```sql
-- Fix due_date column
ALTER TABLE subscription_invoices 
MODIFY COLUMN due_date TIMESTAMP NULL DEFAULT NULL;

-- Verify
SHOW COLUMNS FROM subscription_invoices LIKE 'due_date';

-- Exit
EXIT;
```

Kemudian run migration:
```bash
php artisan migrate --force
```

---

## 🔍 PERUBAHAN PADA MIGRATION FILE:

### **BEFORE (Error):**
```php
public function up(): void
{
    Schema::table('subscription_invoices', function (Blueprint $table) {
        $table->string('payment_reference')->nullable()->after('midtrans_transaction_id');
    });
}
```

### **AFTER (Fixed):**
```php
public function up(): void
{
    // Disable strict mode temporarily
    $originalSqlMode = DB::selectOne("SELECT @@sql_mode as mode")->mode;
    DB::statement("SET SESSION sql_mode=''");
    
    try {
        // Check if column already exists
        if (!Schema::hasColumn('subscription_invoices', 'payment_reference')) {
            Schema::table('subscription_invoices', function (Blueprint $table) {
                $table->string('payment_reference')->nullable()->after('midtrans_transaction_id');
            });
        }
    } finally {
        // Restore original SQL mode
        DB::statement("SET SESSION sql_mode='{$originalSqlMode}'");
    }
}
```

**Improvements:**
- ✅ Disable strict mode sementara (hanya untuk migration ini)
- ✅ Check kolom sudah ada atau belum (idempotent)
- ✅ Restore strict mode setelah selesai
- ✅ Safe & tidak mempengaruhi setting global

---

## 📊 TROUBLESHOOTING:

### **Problem 1: Error Tetap Muncul Setelah Fix**

**Solution:**
```bash
# Reset migration yang error
php artisan migrate:reset --force

# Clear cache
php artisan config:clear
php artisan cache:clear

# Run migration lagi
php artisan migrate --force
```

### **Problem 2: Kolom payment_reference Sudah Ada**

**Check:**
```bash
php artisan tinker
```
```php
Schema::hasColumn('subscription_invoices', 'payment_reference');
// true = sudah ada, false = belum ada
exit
```

**If true (sudah ada):**
```bash
# Mark migration as done
php artisan migrate --force
# Will skip existing columns
```

### **Problem 3: Tabel subscription_invoices Tidak Ada**

**Berarti ini fresh install:**
```bash
# Run all migrations
php artisan migrate --force
# Will create all tables including subscription_invoices
```

---

## 🧪 VERIFY FIX BERHASIL:

### **Test 1: Check Migration Status**
```bash
php artisan migrate:status
```

**Expected:**
```
Migration name ................... Batch / Status
...
add_payment_reference_to_subscription_invoices_table ... [1] Ran
```

### **Test 2: Check Column in Database**
```bash
php artisan tinker
```
```php
DB::select("SHOW COLUMNS FROM subscription_invoices WHERE Field = 'payment_reference'");
// Should show the column info
exit
```

### **Test 3: Check SQL Mode**
```bash
php artisan tinker
```
```php
DB::selectOne("SELECT @@sql_mode as mode");
// Should show original strict mode (not empty)
exit
```

---

## 📋 COMMON SQL MODES:

### **Strict Mode (Default Modern MySQL):**
```
STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION
```

**Effects:**
- ❌ Reject invalid default values (e.g., `0000-00-00`)
- ❌ Reject invalid dates
- ✅ Data integrity enforced

### **Legacy Mode (Empty):**
```
(empty)
```

**Effects:**
- ✅ Allow invalid default values
- ⚠️  Less strict data validation
- 🔧 Used temporarily for migration only

---

## 🎯 MIGRATION STRATEGY YANG BAIK:

### **1. Always Check Column Existence:**
```php
if (!Schema::hasColumn('table', 'column')) {
    // Add column
}
```

### **2. Handle Strict Mode for Legacy Tables:**
```php
// Disable temporarily
$mode = DB::selectOne("SELECT @@sql_mode as mode")->mode;
DB::statement("SET SESSION sql_mode=''");

try {
    // Do migration
} finally {
    // Restore
    DB::statement("SET SESSION sql_mode='{$mode}'");
}
```

### **3. Use Nullable for New Columns:**
```php
$table->string('column')->nullable();
// Prevents default value issues
```

### **4. Test Migration Locally First:**
```bash
# Local test
php artisan migrate

# If OK, then deploy
git push origin main
```

---

## 📞 QUICK FIX COMMANDS:

```bash
# All-in-one fix
cd /www/wwwroot/srx.sppqu/all_in
git pull origin main
php vps_fix_migration_error.php  # Optional: pre-fix
php artisan migrate --force       # Run migration
php artisan migrate:status        # Verify

# If still error
php artisan migrate:reset --force
php artisan migrate --force

# Clear everything
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Restart services
systemctl restart php8.2-fpm
systemctl restart nginx
```

---

## ✅ CHECKLIST:

- [ ] Pull latest code (`git pull origin main`)
- [ ] Run fix script (`php vps_fix_migration_error.php`) - optional
- [ ] Run migration (`php artisan migrate --force`)
- [ ] Check status (`php artisan migrate:status`)
- [ ] Verify column exists in database
- [ ] Clear cache (`php artisan config:clear`)
- [ ] Restart PHP-FPM (`systemctl restart php8.2-fpm`)
- [ ] Test website (https://srx.sppqu.my.id)

---

## 🎉 EXPECTED SUCCESS OUTPUT:

```bash
$ php artisan migrate --force

   INFO  Running migrations.

  2025_10_26_141517_add_payment_reference_to_subscription_invoices_table ... 8.74ms DONE

$ php artisan migrate:status

  Migration name ................... Batch / Status
  ...
  add_payment_reference_to_subscription_invoices_table ... [1] Ran
```

---

## 💡 KEY TAKEAWAYS:

1. **MySQL Strict Mode** good untuk data integrity, tapi kadang bikin migration error di legacy tables
2. **Disable temporarily** untuk migration tertentu OK, asalkan di-restore lagi
3. **Always check column existence** sebelum alter table
4. **Use nullable** untuk kolom baru agar fleksibel
5. **Test locally** sebelum deploy ke production

---

**✅ Migration error fixed! System ready to use!** 🚀

