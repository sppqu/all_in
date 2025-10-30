# 📖 Panduan Pengguna SPPQU

Panduan lengkap penggunaan aplikasi SPPQU untuk semua role pengguna.

---

## 📋 Daftar Isi

- [Login & Autentikasi](#login--autentikasi)
- [Dashboard](#dashboard)
- [Panduan Superadmin](#panduan-superadmin)
- [Panduan Admin](#panduan-admin)
- [Panduan Operator](#panduan-operator)
- [Panduan Guru](#panduan-guru)
- [Panduan Siswa](#panduan-siswa)
- [Panduan Orang Tua](#panduan-orang-tua)
- [SPMB - Calon Siswa](#spmb---calon-siswa)
- [FAQ](#faq)

---

## 🔐 Login & Autentikasi

### **Akses Aplikasi**

1. Buka browser (Chrome, Firefox, Edge, Safari)
2. Akses URL: `https://your-domain.com/manage`
3. Masukkan email & password
4. Klik "Login"

### **Lupa Password**

1. Klik "Lupa Password?" di halaman login
2. Masukkan email terdaftar
3. Cek email untuk link reset password
4. Klik link & buat password baru
5. Login dengan password baru

### **Keamanan Akun**

- ✅ Gunakan password minimal 8 karakter
- ✅ Kombinasi huruf besar, kecil, angka & simbol
- ✅ Jangan share password ke orang lain
- ✅ Logout setelah selesai menggunakan
- ✅ Ganti password secara berkala

---

## 📊 Dashboard

### **Tampilan Dashboard**

Setelah login, Anda akan melihat dashboard dengan:

**1. Header:**
- Logo & nama aplikasi
- Notifikasi
- Profile user
- Logout button

**2. Sidebar Menu:**
- Dashboard
- Data Master
- Pembayaran
- Laporan
- Settings
- dll (tergantung role)

**3. Konten Utama:**
- Widget statistik
- Grafik
- Tabel data
- Quick actions

**4. Footer:**
- Copyright
- Version info
- Support links

---

## 👑 Panduan Superadmin

### **1. Manajemen User**

**Menambah User Baru:**
1. Menu: Data Master → Users
2. Klik "Tambah User"
3. Isi form:
   - Nama lengkap
   - Email (username)
   - Password
   - Role (Admin/Operator/Guru)
   - Phone (opsional)
4. Klik "Simpan"

**Edit User:**
1. Cari user di tabel
2. Klik icon "Edit"
3. Update data yang perlu diubah
4. Klik "Update"

**Hapus User:**
1. Cari user di tabel
2. Klik icon "Hapus"
3. Konfirmasi penghapusan

**Reset Password User:**
1. Edit user
2. Masukkan password baru
3. Centang "Send reset email"
4. Save

### **2. Manajemen Addon**

**Membeli Addon:**
1. Menu: Billing → Add-ons
2. Pilih addon yang ingin dibeli
3. Klik "Detail"
4. Pilih metode pembayaran (QRIS)
5. Klik "Beli Sekarang"
6. Scan QR Code dengan e-wallet
7. Tunggu konfirmasi pembayaran
8. Addon aktif otomatis

**Addon yang Tersedia:**
- ✅ SPMB (Rp 1.000.000)
- ✅ Bimbingan Konseling (Rp 500.000)
- ✅ E-Jurnal Harian (Rp 300.000)
- ✅ E-Perpustakaan (Rp 800.000)
- ✅ Payment Gateway (Rp 500.000)
- ✅ WhatsApp Gateway (Rp 300.000)

**Cek Status Addon:**
1. Menu: Billing → Add-ons
2. Tab "Add-ons Saya"
3. Lihat list addon aktif

### **3. Manajemen Subscription**

**Berlangganan:**
1. Menu: Billing → Subscription
2. Pilih paket (Monthly/Yearly)
3. Klik "Subscribe"
4. Bayar via payment gateway
5. Subscription aktif otomatis

**Perpanjang Subscription:**
1. Menu: Billing → Subscription
2. Klik "Renew"
3. Lakukan pembayaran

**Cek Status:**
- Dashboard menampilkan sisa hari subscription
- Notifikasi muncul 30 hari sebelum expired

### **4. Settings**

**General Settings:**
1. Menu: Settings → General
2. Update:
   - Nama sekolah
   - Logo
   - Alamat
   - Kontak
3. Save

**Payment Gateway:**
1. Menu: Settings → Payment Gateway
2. Configure:
   - iPaymu API Key
   - iPaymu VA
   - Sandbox mode
3. Test connection
4. Save

**WhatsApp Gateway:**
1. Menu: Settings → WhatsApp
2. Masukkan Fonnte token
3. Test send message
4. Save

---

## 🔧 Panduan Admin

### **1. Manajemen Siswa**

**Tambah Siswa:**
1. Menu: Data Master → Siswa
2. Klik "Tambah Siswa"
3. Isi data siswa:
   - NISN
   - Nama
   - Kelas
   - Tahun masuk
   - Data orang tua
4. Save

**Import Siswa (Bulk):**
1. Menu: Data Master → Siswa
2. Klik "Import"
3. Download template Excel
4. Isi data siswa di Excel
5. Upload file Excel
6. Review data
7. Confirm import

**Export Data Siswa:**
1. Menu: Data Master → Siswa
2. Klik "Export"
3. Pilih format (Excel/PDF)
4. Download file

### **2. Manajemen Pembayaran**

**Setup Pos Pembayaran:**
1. Menu: Pembayaran → Pos Bayar
2. Klik "Tambah Pos"
3. Isi:
   - Nama pos (e.g., "SPP Kelas 10")
   - Nominal
   - Tipe (Bulanan/Bebas)
   - Kelas yang berlaku
4. Save

**Catat Pembayaran Manual:**
1. Menu: Pembayaran → Transaksi
2. Klik "Tambah Pembayaran"
3. Pilih siswa
4. Pilih pos bayar
5. Input nominal
6. Upload bukti (opsional)
7. Save

**Cek Status Pembayaran:**
1. Menu: Pembayaran → Status
2. Filter by kelas/bulan
3. Lihat siswa yang sudah/belum bayar
4. Export report jika perlu

### **3. SPMB - Admin**

**Kelola Pendaftar:**
1. Menu: SPMB → Pendaftar
2. Lihat list pendaftar
3. Cek dokumen upload
4. Update status:
   - Pending
   - Approved
   - Rejected
5. Kirim notifikasi ke pendaftar

**Setup Gelombang:**
1. Menu: SPMB → Settings
2. Tab "Gelombang"
3. Tambah gelombang baru:
   - Nama (e.g., "Gelombang 1")
   - Tanggal mulai
   - Tanggal selesai
   - Kuota
   - Biaya pendaftaran
4. Save

**Setup Jurusan:**
1. Menu: SPMB → Settings
2. Tab "Jurusan"
3. Tambah jurusan:
   - Nama jurusan
   - Kuota
   - Deskripsi
4. Save

**Export Pendaftar:**
1. Menu: SPMB → Pendaftar
2. Filter by status/gelombang
3. Klik "Export"
4. Download Excel/PDF

---

## 👤 Panduan Operator

### **1. Input Nilai**

1. Menu: Akademik → Nilai
2. Pilih kelas & mata pelajaran
3. Pilih jenis nilai (UH/UTS/UAS)
4. Input nilai per siswa
5. Save

### **2. Cetak Raport**

1. Menu: Akademik → Raport
2. Pilih semester & tahun ajaran
3. Pilih kelas
4. Generate raport
5. Preview
6. Download PDF
7. Print

### **3. Absensi**

**Input Absensi:**
1. Menu: Akademik → Absensi
2. Pilih kelas & tanggal
3. Mark:
   - ✅ Hadir (H)
   - ❌ Sakit (S)
   - ❌ Izin (I)
   - ❌ Alpha (A)
4. Save

**Rekap Absensi:**
1. Menu: Akademik → Rekap Absensi
2. Filter by kelas/periode
3. Lihat statistik kehadiran
4. Export report

---

## 👨‍🏫 Panduan Guru

### **1. E-Jurnal Harian**

**Buat Jurnal:**
1. Menu: Jurnal → Buat Jurnal
2. Pilih kelas & mata pelajaran
3. Pilih tanggal
4. Isi:
   - Materi yang diajarkan
   - Kegiatan pembelajaran
   - Catatan khusus
5. Input absensi siswa
6. Upload file materi (opsional)
7. Save

**Edit Jurnal:**
1. Menu: Jurnal → Riwayat
2. Cari jurnal yang ingin diedit
3. Klik "Edit"
4. Update data
5. Save

**Lihat Riwayat:**
1. Menu: Jurnal → Riwayat
2. Filter by tanggal/kelas
3. View detail jurnal

### **2. Bimbingan Konseling**

**Catat Pelanggaran:**
1. Menu: BK → Pelanggaran
2. Klik "Tambah Pelanggaran"
3. Pilih siswa
4. Pilih kategori pelanggaran
5. Isi deskripsi
6. Upload foto bukti (opsional)
7. Save

**Riwayat Pelanggaran Siswa:**
1. Menu: BK → Siswa
2. Pilih siswa
3. Lihat riwayat pelanggaran
4. Total poin pelanggaran
5. Print laporan

**Konseling:**
1. Menu: BK → Konseling
2. Pilih siswa
3. Isi catatan konseling
4. Tindak lanjut
5. Save

### **3. Perpustakaan**

**Pinjam Buku:**
1. Menu: Perpustakaan → Peminjaman
2. Klik "Pinjam Buku"
3. Pilih siswa/guru
4. Scan QR Code buku atau pilih manual
5. Set tanggal kembali
6. Confirm

**Kembalikan Buku:**
1. Menu: Perpustakaan → Pengembalian
2. Scan QR Code buku atau input manual
3. Cek kondisi buku
4. Hitung denda (jika terlambat)
5. Confirm

**Statistik:**
1. Menu: Perpustakaan → Statistik
2. Lihat:
   - Buku terpopuler
   - Peminjam teraktif
   - Statistik per bulan
3. Export report

---

## 🎓 Panduan Siswa

### **1. Cek Pembayaran**

1. Login dengan akun siswa
2. Dashboard menampilkan:
   - Status pembayaran bulan ini
   - Tunggakan (jika ada)
   - Riwayat pembayaran
3. Klik "Bayar SPP" untuk pembayaran online

### **2. Bayar SPP Online**

**Via QRIS:**
1. Klik "Bayar SPP"
2. Pilih bulan yang ingin dibayar
3. Pilih metode: QRIS
4. Klik "Proses Pembayaran"
5. QR Code muncul
6. Buka e-wallet (GoPay/OVO/Dana/ShopeePay)
7. Scan QR Code
8. Confirm pembayaran
9. Tunggu notifikasi sukses
10. Bukti pembayaran otomatis tersimpan

**Via Virtual Account:**
1. Pilih metode: Virtual Account
2. Pilih bank (BRI/BCA/Mandiri/BNI)
3. Copy nomor VA
4. Buka mobile banking
5. Transfer ke VA tersebut
6. Pembayaran auto-verify

### **3. Lihat Nilai**

1. Menu: Akademik → Nilai
2. Pilih semester
3. Lihat nilai per mata pelajaran:
   - Nilai harian
   - UTS
   - UAS
   - Rata-rata
4. Download raport (jika sudah tersedia)

### **4. Perpustakaan**

**Cari Buku:**
1. Menu: Perpustakaan → Katalog
2. Search by:
   - Judul
   - Pengarang
   - Kategori
3. Klik buku untuk detail
4. Lihat ketersediaan

**Pinjam Buku:**
1. Cari buku di katalog
2. Klik "Pinjam"
3. Confirm
4. Datang ke perpustakaan
5. Tunjukkan QR Code peminjaman

**Riwayat Peminjaman:**
1. Menu: Perpustakaan → Riwayat
2. Lihat buku yang sedang dipinjam
3. Tanggal kembali
4. Status (aktif/terlambat)

---

## 👪 Panduan Orang Tua

### **1. Akses Akun**

Orang tua menggunakan akun siswa atau akun terpisah (jika diaktifkan sekolah).

### **2. Monitor Pembayaran**

1. Login ke aplikasi
2. Dashboard menampilkan:
   - Status pembayaran terkini
   - Tunggakan (jika ada)
   - Riwayat pembayaran 6 bulan terakhir
3. Download bukti pembayaran

### **3. Bayar SPP**

Sama seperti panduan siswa di atas.

### **4. Monitor Akademik**

**Lihat Nilai:**
1. Menu: Akademik → Nilai
2. Lihat perkembangan nilai anak

**Lihat Absensi:**
1. Menu: Akademik → Absensi
2. Cek kehadiran anak
3. Grafik kehadiran per bulan

**Pelanggaran:**
1. Menu: BK → Pelanggaran
2. Lihat catatan pelanggaran (jika ada)
3. Poin pelanggaran

### **5. Komunikasi**

**Notifikasi:**
- Notifikasi pembayaran
- Reminder jatuh tempo
- Info akademik
- Pengumuman sekolah

**Pesan ke Guru:**
(Jika fitur diaktifkan)
1. Menu: Komunikasi → Pesan
2. Pilih guru
3. Tulis pesan
4. Send

---

## 🎯 SPMB - Calon Siswa

### **1. Registrasi Akun**

1. Akses: `https://domain.com/spmb/register`
2. Isi form registrasi:
   - Nama lengkap
   - Nomor HP (gunakan yang aktif)
   - Password
3. Klik "Daftar"
4. OTP dikirim ke HP
5. Input OTP untuk verifikasi
6. Akun berhasil dibuat

### **2. Login SPMB**

1. Akses: `https://domain.com/spmb/login`
2. Masukkan nomor HP & password
3. Klik "Login"

### **3. Dashboard SPMB**

Setelah login, Anda akan melihat **5 tahap pendaftaran:**

```
Step 1: Pendaftaran     [✓ Complete]
Step 2: Biaya Pendaftaran [In Progress]
Step 3: Data Diri       [Locked]
Step 4: Upload Dokumen  [Locked]
Step 5: Selesai         [Locked]
```

### **4. Step 1: Pendaftaran**

**Status: Otomatis selesai saat registrasi**

### **5. Step 2: Biaya Pendaftaran**

**Bayar Biaya Pendaftaran:**

1. Klik "Lanjut ke Pembayaran"
2. Lihat nominal biaya (e.g., Rp 100.000)
3. Metode pembayaran: QRIS (otomatis dipilih)
4. Klik "Bayar Sekarang"
5. QR Code QRIS muncul
6. Buka e-wallet (GoPay/OVO/Dana/ShopeePay/LinkAja)
7. Pilih "Scan QR" atau "QRIS"
8. Scan QR Code di layar
9. Confirm pembayaran di e-wallet
10. Tunggu notifikasi (10-30 detik)
11. Halaman auto-refresh
12. Status berubah jadi "Lunas"
13. Step 3 terbuka otomatis

**Tips:**
- ✅ Pastikan HP terhubung internet
- ✅ Gunakan e-wallet yang ada saldo
- ✅ Jangan tutup halaman saat menunggu
- ✅ Screenshot QR jika perlu bayar nanti
- ⚠️ QR Code expired dalam 24 jam

### **6. Step 3: Data Diri**

**Isi Data Pribadi:**

1. Klik Step 3
2. Isi form (semua wajib):
   - **Data Pribadi:**
     - NIK (16 digit)
     - Nama lengkap
     - Tempat, tanggal lahir
     - Jenis kelamin
     - Alamat lengkap
     - Nomor HP
   - **Data Orang Tua:**
     - Nama ayah
     - Pekerjaan ayah
     - Nama ibu
     - Pekerjaan ibu
     - Nomor HP orang tua
   - **Pilihan Pendaftaran:**
     - Gelombang pendaftaran
     - Jurusan pilihan 1
     - Jurusan pilihan 2 (opsional)
3. Klik "Simpan & Lanjut"
4. Step 4 terbuka

**Validasi:**
- NIK harus 16 digit angka
- HP harus format valid (08xxx)
- Semua field wajib diisi (kecuali jurusan 2)

### **7. Step 4: Upload Dokumen**

**Dokumen yang Harus Diupload:**

1. **KTP** (Foto/Scan KTP orang tua)
   - Format: JPG, PNG, PDF
   - Max size: 2 MB
   - Pastikan terlihat jelas

2. **Kartu Keluarga (KK)**
   - Format: JPG, PNG, PDF
   - Max size: 2 MB
   - Semua data terlihat jelas

3. **Ijazah** (Ijazah terakhir)
   - Format: JPG, PNG, PDF
   - Max size: 2 MB
   - Lengkap dengan stempel

4. **Rapor** (Rapor semester terakhir)
   - Format: JPG, PNG, PDF
   - Max size: 2 MB
   - Halaman nilai terlihat

5. **Foto** (Foto 3x4 background merah)
   - Format: JPG, PNG
   - Max size: 1 MB
   - Formal, berpakaian rapi

**Cara Upload:**
1. Klik "Pilih File" untuk setiap dokumen
2. Browse file dari device
3. Select file
4. Preview muncul
5. Ulangi untuk semua dokumen
6. Klik "Upload Semua"
7. Tunggu proses upload (progress bar muncul)
8. Notifikasi "Upload berhasil"
9. Klik "Lanjut"
10. Step 5 terbuka

**Tips:**
- ✅ Foto dokumen dengan cahaya cukup
- ✅ Dokumen tidak blur
- ✅ Semua text terbaca jelas
- ✅ Gunakan scanner jika punya
- ⚠️ File yang terlalu besar akan ditolak

### **8. Step 5: Selesai**

**Pendaftaran Selesai!**

Anda akan melihat:
- ✅ Badge "Pendaftaran Selesai"
- Nomor pendaftaran (simpan ini!)
- Tombol "Download Formulir"
- Tombol "Print Formulir"
- Status: "Menunggu Verifikasi Admin"

**Next Steps:**
1. Download & print formulir pendaftaran
2. Tunggu email/SMS dari admin (1-3 hari kerja)
3. Status akan berubah:
   - "Diverifikasi" → Lolos
   - "Ditolak" → Harus upload ulang
4. Jika lolos, ikuti jadwal tes/wawancara

**Download Formulir:**
1. Klik "Download Formulir"
2. PDF akan otomatis download
3. Print formulir
4. Bawa saat tes/wawancara

**Cek Status:**
1. Login kapan saja
2. Dashboard menampilkan status terkini
3. Notifikasi via SMS/email jika ada update

---

## ❓ FAQ (Frequently Asked Questions)

### **Umum**

**Q: Apakah SPPQU bisa diakses via HP?**  
A: Ya, SPPQU fully responsive dan bisa diakses via HP, tablet, atau laptop.

**Q: Apakah bisa install SPPQU sebagai aplikasi?**  
A: Ya, SPPQU adalah PWA (Progressive Web App). Buka di browser, klik menu → "Add to Home Screen".

**Q: Apakah data aman?**  
A: Ya, semua data dienkripsi dan server menggunakan SSL. Kami tidak share data ke pihak ketiga.

### **Pembayaran**

**Q: Pembayaran pakai apa saja?**  
A: QRIS (semua e-wallet) dan Virtual Account (BRI, BCA, Mandiri, BNI).

**Q: Berapa lama pembayaran terverifikasi?**  
A: Pembayaran QRIS instant (10-30 detik). VA 5-10 menit.

**Q: Bagaimana jika pembayaran gagal?**  
A: Cek saldo e-wallet/rekening. Jika sudah benar, ulangi. Jika masih gagal, hubungi admin.

**Q: Apakah bisa bayar di minimarket?**  
A: Tergantung payment gateway yang diaktifkan sekolah. Tanyakan ke admin.

**Q: Bisakah orang tua bayar untuk anak?**  
A: Ya, bisa login dengan akun siswa atau akun orang tua terpisah.

### **SPMB**

**Q: Biaya pendaftaran berapa?**  
A: Tergantung sekolah. Biasanya Rp 50.000 - Rp 300.000. Cek di website sekolah.

**Q: Apakah bisa daftar tanpa bayar dulu?**  
A: Tidak. Pembayaran di Step 2 wajib sebelum lanjut ke Step 3.

**Q: Berapa lama proses verifikasi?**  
A: 1-3 hari kerja setelah upload dokumen lengkap.

**Q: Bagaimana jika dokumen ditolak?**  
A: Anda bisa upload ulang dokumen yang ditolak. Cek alasan penolakan di dashboard.

**Q: Apakah bisa ganti jurusan setelah daftar?**  
A: Ya, sebelum Step 3 disimpan. Setelah disimpan, hubungi admin untuk perubahan.

### **Teknis**

**Q: Lupa password, bagaimana?**  
A: Klik "Lupa Password" di halaman login. Link reset dikirim ke email.

**Q: Email tidak masuk, kenapa?**  
A: Cek folder spam. Jika tidak ada, hubungi admin untuk reset manual.

**Q: QR Code tidak muncul, kenapa?**  
A: Refresh halaman atau clear cache browser. Jika masih tidak muncul, hubungi admin.

**Q: Error saat upload dokumen?**  
A: Pastikan:
  - File size < 2 MB
  - Format: JPG, PNG, atau PDF
  - Internet stabil
  - Browser up-to-date

---

## 📞 Bantuan Lebih Lanjut

**Jika masih ada pertanyaan:**

1. **Help Center:** Menu → Help → Knowledge Base
2. **Live Chat:** Klik icon chat di pojok kanan bawah
3. **Email Support:** support@sppqu.com
4. **WhatsApp:** +62 812-3456-7890
5. **Kantor:** Senin-Jumat, 08:00-17:00 WIB

**Atau hubungi admin sekolah Anda langsung.**

---

**💡 Tips Sukses Menggunakan SPPQU:**

1. Pastikan data yang diinput benar & lengkap
2. Simpan screenshot/bukti pembayaran
3. Cek notifikasi secara berkala
4. Bayar tepat waktu agar tidak kena denda
5. Update profile jika ada perubahan data
6. Gunakan fitur export untuk backup data

---

**Happy using SPPQU! 🎓**

**Version:** 2.0.0  
**Last Updated:** October 2024

