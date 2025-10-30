# 🔧 Fix: Payment Expiry Status Sync

## 📝 Masalah

**Reported Issue:**
```
view riwayat student tab perkuitansi belum sinkron jika request sudah expired, 
status masih tampil menunggu
```

Status pembayaran pending yang sudah expired (lebih dari 24 jam) masih ditampilkan sebagai "Menunggu Pembayaran", padahal link pembayaran sudah tidak valid lagi.

---

## ✅ Solusi yang Diimplementasikan

### **1. Controller Update** (`app/Http/Controllers/StudentAuthController.php`)

**Pengecekan Expired Payment:**

```php
// Check for expired payments and mark them
$allTransferTransactions = $allTransferTransactions->map(function($transaction) {
    if ($transaction->status == 0 && $transaction->created_at) {
        // Payment expires after 24 hours
        $createdAt = \Carbon\Carbon::parse($transaction->created_at);
        $expiryTime = $createdAt->addHours(24);
        $isExpired = now()->greaterThan($expiryTime);
        
        $transaction->is_expired = $isExpired;
    } else {
        $transaction->is_expired = false;
    }
    return $transaction;
});

// Mark expired payments in online payments
$onlinePayments = $onlinePayments->map(function($payment) {
    if ($payment->status == 0 && $payment->created_at) {
        // Payment expires after 24 hours
        $createdAt = \Carbon\Carbon::parse($payment->created_at);
        $expiryTime = $createdAt->addHours(24);
        $isExpired = now()->greaterThan($expiryTime);
        
        $payment->is_expired = $isExpired;
    } else {
        $payment->is_expired = false;
    }
    return $payment;
});
```

**Penambahan Flag `is_expired` ke Receipt Groups:**

```php
// Check if any item in this group is expired
$isExpired = $items->contains(function($item) {
    return isset($item->is_expired) && $item->is_expired === true;
});

$receiptGroups->push((object)[
    // ... existing fields ...
    'is_expired' => $isExpired,
    'created_at' => $firstItem->created_at ?? $firstItem->payment_date
]);
```

### **2. View Update** (`resources/views/student/payment-history.blade.php`)

**Status Badge untuk Expired Payment:**

```blade
@if($payment->transaction_type === 'ONLINE_PENDING')
    <span class="badge bg-info bg-opacity-10 text-info payment-method">
        <i class="fas fa-globe me-1"></i>Online
    </span>
    @if(isset($payment->is_expired) && $payment->is_expired)
        <span class="badge bg-danger bg-opacity-10 text-danger ms-1">
            <i class="fas fa-times-circle me-1"></i>Kadaluarsa
        </span>
    @else
        <span class="badge bg-warning bg-opacity-10 text-warning ms-1">
            <i class="fas fa-clock me-1"></i>Menunggu Pembayaran
        </span>
    @endif
@endif
```

**Hide Button "Bayar Sekarang" untuk Expired Payment:**

```blade
@if($payment->status == 0)
    @if(isset($payment->is_expired) && $payment->is_expired)
        <small class="text-danger d-block">
            <i class="fas fa-info-circle me-1"></i>Link pembayaran sudah kadaluarsa. Silakan buat pembayaran baru.
        </small>
    @else
        <!-- Show Bayar Sekarang button -->
        @if($showPayButton && $paymentUrl)
            <a href="{{ $paymentUrl }}" class="btn btn-primary btn-sm" target="_blank">
                <i class="fas fa-credit-card me-1"></i>Bayar Sekarang
            </a>
        @endif
    @endif
@endif
```

**Status Badge di Per Item View:**

```blade
@elseif($payment->status == 0)
    @if(isset($payment->is_expired) && $payment->is_expired)
        <span class="badge bg-danger bg-opacity-10 text-danger payment-method">
            <i class="fas fa-times-circle me-1"></i>Kadaluarsa
        </span>
        <div class="mt-1">
            <small class="text-danger">
                <i class="fas fa-exclamation-triangle me-1"></i>Link pembayaran sudah kadaluarsa
            </small>
        </div>
    @else
        <span class="badge bg-warning bg-opacity-10 text-warning payment-method">
            <i class="fas fa-clock me-1"></i>Menunggu Verifikasi
        </span>
        <!-- ... -->
    @endif
@endif
```

---

## 🎯 Hasil yang Dicapai

### **BEFORE (Sebelum Fix):**

| Status | Waktu Lewat | Tampilan | Tombol |
|--------|-------------|----------|--------|
| Pending | < 24 jam | "Menunggu Pembayaran" 🟡 | "Bayar Sekarang" ✅ |
| Pending | > 24 jam | "Menunggu Pembayaran" 🟡 | "Bayar Sekarang" ❌ (expired!) |

**Masalah:** Status tidak sinkron dengan kondisi sebenarnya!

### **AFTER (Setelah Fix):**

| Status | Waktu Lewat | Tampilan | Tombol |
|--------|-------------|----------|--------|
| Pending | < 24 jam | "Menunggu Pembayaran" 🟡 | "Bayar Sekarang" ✅ |
| Pending | > 24 jam | "Kadaluarsa" 🔴 | Pesan: "Link pembayaran sudah kadaluarsa..." |
| Success | Any | "Berhasil" ✅ | "Detail" |
| Rejected | Any | "Ditolak" 🔴 | - |

**✅ Status sekarang sinkron dengan kondisi sebenarnya!**

---

## 📱 Preview UI

### **PerKuitansi View:**

**Payment Expired:**
```
┌─────────────────────────────────────────┐
│ TRX-20241030-0001                       │
│ 30 Okt 2024                             │
│                                         │
│ Rp 150.000                              │
│ 🌐 Online  🔴 Kadaluarsa                │
│                                         │
│ ⓘ Link pembayaran sudah kadaluarsa.    │
│   Silakan buat pembayaran baru.        │
└─────────────────────────────────────────┘
```

**Payment Active (< 24 jam):**
```
┌─────────────────────────────────────────┐
│ TRX-20241030-0002                       │
│ 30 Okt 2024                             │
│                                         │
│ Rp 150.000                              │
│ 🌐 Online  🟡 Menunggu Pembayaran       │
│                                         │
│ [💳 Bayar Sekarang]                     │
└─────────────────────────────────────────┘
```

### **PerItem View:**

**Payment Expired:**
```
┌─────────────────────────────────────────┐
│ SPP Kelas 10 - Oktober                  │
│ 30/10/2024 10:30                        │
│                                         │
│ Rp 150.000                              │
│ 🔴 Kadaluarsa                           │
│ ⚠️ Link pembayaran sudah kadaluarsa     │
│                                         │
│ ⓘ Link pembayaran sudah kadaluarsa     │
└─────────────────────────────────────────┘
```

---

## 🔍 Detail Teknis

### **Expiry Rules:**
- **Waktu Expired:** 24 jam setelah payment dibuat
- **Perhitungan:** Menggunakan `created_at` dari transaksi
- **Logic:** `now() > (created_at + 24 hours)`

### **Pengecekan Expired:**
```php
$createdAt = \Carbon\Carbon::parse($transaction->created_at);
$expiryTime = $createdAt->addHours(24);
$isExpired = now()->greaterThan($expiryTime);
```

### **Payment Status Mapping:**

| Status DB | is_expired | Tampilan UI |
|-----------|------------|-------------|
| 0 (Pending) | false | 🟡 Menunggu Pembayaran |
| 0 (Pending) | true | 🔴 Kadaluarsa |
| 1 (Success) | - | ✅ Berhasil/Sukses |
| 2 (Rejected) | - | 🔴 Ditolak |

---

## 🚀 Testing Steps

### **1. Test Expired Payment:**

```bash
# Update created_at untuk test expired
php artisan tinker
```

```php
$transfer = \App\Models\Transfer::where('status', 0)->first();
$transfer->created_at = now()->subHours(25);
$transfer->save();
```

Kemudian buka riwayat pembayaran, status harus tampil "Kadaluarsa".

### **2. Test Active Payment:**

```bash
php artisan tinker
```

```php
$transfer = \App\Models\Transfer::where('status', 0)->first();
$transfer->created_at = now()->subHours(2);
$transfer->save();
```

Status harus tampil "Menunggu Pembayaran" dengan tombol "Bayar Sekarang".

### **3. Test Both Views:**

1. Akses `/student/payment-history?view=kuitansi`
2. Cek status expired payment → harus tampil "Kadaluarsa"
3. Switch ke `/student/payment-history?view=item`
4. Cek status expired payment → harus konsisten "Kadaluarsa"

---

## 📊 Impact Analysis

### **Files Modified:**
1. ✅ `app/Http/Controllers/StudentAuthController.php`
2. ✅ `resources/views/student/payment-history.blade.php`

### **Affected Features:**
- ✅ Riwayat Pembayaran Student (PerKuitansi view)
- ✅ Riwayat Pembayaran Student (PerItem view)
- ✅ Payment status display
- ✅ "Bayar Sekarang" button visibility

### **No Breaking Changes:**
- ✅ Backward compatible
- ✅ No database migration needed
- ✅ Works with existing data
- ✅ No API changes

---

## 💡 User Experience Improvements

### **Before:**
- ❌ Confusing: expired payment still shows "Menunggu"
- ❌ User bisa klik "Bayar Sekarang" tapi link expired
- ❌ Tidak ada indikasi payment expired

### **After:**
- ✅ Clear indication: "Kadaluarsa" badge
- ✅ Button "Bayar Sekarang" hidden untuk expired payment
- ✅ Helpful message: "Silakan buat pembayaran baru"
- ✅ Consistent status across both views

---

## 🛡️ Error Prevention

**Sebelumnya:**
```
User klik "Bayar Sekarang" → 
Link expired → 
Error di payment gateway → 
User confused 😕
```

**Sekarang:**
```
User lihat "Kadaluarsa" → 
Pesan jelas: "Buat pembayaran baru" → 
User paham harus buat transaksi baru ✅
```

---

## 📝 Notes

1. **Payment Expiry Time:** Default 24 jam. Bisa disesuaikan di controller jika payment gateway punya aturan berbeda.

2. **Auto-Cleanup (Optional):** Bisa tambahkan scheduled job untuk auto-update status expired payment ke database:

```php
// app/Console/Commands/UpdateExpiredPayments.php
public function handle()
{
    Transfer::where('status', 0)
        ->where('created_at', '<', now()->subHours(24))
        ->update(['status' => 3]); // 3 = Expired
}
```

3. **Future Enhancement:** Tambahkan kolom `expired_at` di database untuk tracking lebih detail.

---

## ✅ Deployment Checklist

- [x] Code changes committed
- [x] Tested locally
- [x] No linter errors
- [x] Documentation created
- [x] Pushed to repository

**Ready for deployment! 🚀**

---

**Fix Completed:** October 30, 2024  
**Developer:** AI Assistant (Claude)  
**Status:** ✅ Production Ready

