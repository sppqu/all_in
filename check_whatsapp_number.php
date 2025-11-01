<?php

/**
 * Script untuk mengecek nomor WhatsApp yang digunakan di SPMB Landing Page
 * 
 * Usage: php check_whatsapp_number.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\SchoolProfile;
use App\Models\SetupGateway;

echo "========================================\n";
echo "CEK NOMOR WHATSAPP SPMB LANDING PAGE\n";
echo "========================================\n\n";

// 1. Check SchoolProfile
$schoolProfile = SchoolProfile::first();
echo "1. School Profile:\n";
if ($schoolProfile) {
    echo "   ✅ Data ditemukan\n";
    echo "   📧 Nama Sekolah: " . ($schoolProfile->nama_sekolah ?? '-') . "\n";
    echo "   📞 No. Telp: " . ($schoolProfile->no_telp ?? '-') . "\n";
} else {
    echo "   ❌ Data tidak ditemukan\n";
    echo "   📞 No. Telp: (tidak ada)\n";
}
echo "\n";

// 2. Check SetupGateway
$gateway = SetupGateway::first();
echo "2. WhatsApp Gateway Settings:\n";
if ($gateway) {
    echo "   ✅ Data ditemukan\n";
    echo "   📱 wa_gateway: " . ($gateway->wa_gateway ?? '(kosong)') . "\n";
    echo "   🔔 enable_wa_notification: " . ($gateway->enable_wa_notification ? 'Aktif' : 'Tidak Aktif') . "\n";
    echo "   🔗 url_wagateway: " . ($gateway->url_wagateway ?? '(kosong)') . "\n";
    echo "   🔑 apikey_wagateway: " . ($gateway->apikey_wagateway ? '***' . substr($gateway->apikey_wagateway, -4) : '(kosong)') . "\n";
} else {
    echo "   ❌ Data tidak ditemukan\n";
    echo "   📱 wa_gateway: (tidak ada)\n";
}
echo "\n";

// 3. Determine WhatsApp number (same logic as controller)
$whatsappNumber = null;
if ($gateway && !empty($gateway->wa_gateway)) {
    $whatsappNumber = $gateway->wa_gateway;
    $source = 'SetupGateway.wa_gateway';
} elseif ($schoolProfile && !empty($schoolProfile->no_telp)) {
    $whatsappNumber = $schoolProfile->no_telp;
    $source = 'SchoolProfile.no_telp';
} else {
    $whatsappNumber = '6281234567890';
    $source = 'Fallback (hardcoded)';
}

// Format nomor (same as controller)
$originalNumber = $whatsappNumber;
$whatsappNumber = preg_replace('/^0/', '', $whatsappNumber);
if (!preg_match('/^62/', $whatsappNumber)) {
    $whatsappNumber = '62' . ltrim($whatsappNumber, '62');
}

echo "3. Nomor WhatsApp yang Digunakan:\n";
echo "   📱 Sumber: {$source}\n";
echo "   📝 Nomor Original: {$originalNumber}\n";
echo "   ✅ Nomor Formatted: {$whatsappNumber}\n";
echo "   🔗 Link: https://wa.me/{$whatsappNumber}?text=Halo\n";
echo "\n";

// 4. Check if number is valid format
echo "4. Validasi Nomor:\n";
if (preg_match('/^62\d{9,13}$/', $whatsappNumber)) {
    echo "   ✅ Format nomor valid (62XXXXXXXXXX)\n";
    $digitCount = strlen($whatsappNumber);
    echo "   📊 Jumlah digit: {$digitCount}\n";
    if ($digitCount >= 11 && $digitCount <= 15) {
        echo "   ✅ Panjang nomor sesuai standar Indonesia\n";
    } else {
        echo "   ⚠️  Panjang nomor tidak biasa (seharusnya 11-15 digit)\n";
    }
} else {
    echo "   ❌ Format nomor tidak valid!\n";
    echo "   ⚠️  Nomor harus format: 62XXXXXXXXXX (minimal 11 digit, maksimal 15 digit)\n";
}
echo "\n";

// 5. Recommendation
echo "5. Rekomendasi:\n";
if ($source === 'Fallback (hardcoded)') {
    echo "   ⚠️  Nomor WhatsApp masih menggunakan fallback!\n";
    echo "   💡 Set nomor di:\n";
    echo "      1. General Settings → WhatsApp Gateway → No. WhatsApp\n";
    echo "      ATAU\n";
    echo "      2. General Settings → Profile Sekolah → No. Telp\n";
} elseif ($source === 'SetupGateway.wa_gateway') {
    echo "   ✅ Menggunakan nomor dari WhatsApp Gateway settings\n";
    echo "   📍 Dapat diubah di: General Settings → WhatsApp Gateway\n";
} elseif ($source === 'SchoolProfile.no_telp') {
    echo "   ✅ Menggunakan nomor dari School Profile\n";
    echo "   💡 Tips: Lebih baik set di WhatsApp Gateway untuk spesifik WhatsApp\n";
    echo "   📍 Dapat diubah di: General Settings → Profile Sekolah\n";
}
echo "\n";

echo "========================================\n";
echo "SELESAI\n";
echo "========================================\n";


