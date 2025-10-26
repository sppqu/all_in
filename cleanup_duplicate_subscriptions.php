<?php
/**
 * Script untuk membersihkan subscription duplikat
 * Upload ke root folder dan akses via browser: https://srx.sppqu.my.id/cleanup_duplicate_subscriptions.php
 * 
 * PENTING: Hapus file ini setelah selesai digunakan!
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "<h2>🧹 Cleanup Subscription Duplikat</h2>";
echo "<hr>";

try {
    // 1. Delete subscription pending tanpa payment_reference (tidak ada transaksi)
    echo "<h3>Step 1: Delete subscription pending tanpa payment_reference</h3>";
    
    $deleted1 = DB::table('subscriptions')
        ->where('status', 'pending')
        ->whereNull('payment_reference')
        ->delete();
    
    echo "✅ Deleted $deleted1 subscription pending tanpa payment_reference<br>";
    
    // 2. Untuk setiap user, jika punya subscription aktif, delete semua yang pending
    echo "<h3>Step 2: Delete subscription pending jika sudah ada yang aktif</h3>";
    
    $activeUsers = DB::table('subscriptions')
        ->where('status', 'active')
        ->pluck('user_id')
        ->unique();
    
    $deleted2 = 0;
    foreach ($activeUsers as $userId) {
        $count = DB::table('subscriptions')
            ->where('user_id', $userId)
            ->where('status', 'pending')
            ->delete();
        $deleted2 += $count;
        
        if ($count > 0) {
            echo "✅ User ID $userId: Deleted $count pending subscription(s)<br>";
        }
    }
    
    echo "<p><strong>Total deleted: $deleted2 pending subscriptions</strong></p>";
    
    // 3. Show current subscriptions status
    echo "<h3>Step 3: Status Subscription Saat Ini</h3>";
    
    $subscriptions = DB::table('subscriptions')
        ->join('users', 'subscriptions.user_id', '=', 'users.id')
        ->select(
            'subscriptions.id',
            'users.name as user_name',
            'users.email',
            'subscriptions.plan_name',
            'subscriptions.status',
            'subscriptions.amount',
            'subscriptions.activated_at',
            'subscriptions.expires_at',
            'subscriptions.created_at'
        )
        ->orderBy('subscriptions.created_at', 'desc')
        ->get();
    
    if ($subscriptions->isEmpty()) {
        echo "<p>❌ Tidak ada subscription ditemukan</p>";
    } else {
        echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f0f0f0;'>";
        echo "<th>ID</th><th>User</th><th>Email</th><th>Plan</th><th>Status</th>";
        echo "<th>Amount</th><th>Activated At</th><th>Expires At</th><th>Created At</th>";
        echo "</tr>";
        
        foreach ($subscriptions as $sub) {
            $statusColor = $sub->status === 'active' ? 'green' : ($sub->status === 'pending' ? 'orange' : 'red');
            echo "<tr>";
            echo "<td>{$sub->id}</td>";
            echo "<td>{$sub->user_name}</td>";
            echo "<td>{$sub->email}</td>";
            echo "<td>{$sub->plan_name}</td>";
            echo "<td style='color: $statusColor; font-weight: bold;'>{$sub->status}</td>";
            echo "<td>Rp " . number_format($sub->amount, 0, ',', '.') . "</td>";
            echo "<td>" . ($sub->activated_at ?? '-') . "</td>";
            echo "<td>" . ($sub->expires_at ?? '-') . "</td>";
            echo "<td>{$sub->created_at}</td>";
            echo "</tr>";
        }
        
        echo "</table>";
    }
    
    echo "<hr>";
    echo "<h3>✅ Cleanup Selesai!</h3>";
    echo "<p><strong>Total deleted:</strong> " . ($deleted1 + $deleted2) . " subscription records</p>";
    echo "<p><strong>Total remaining:</strong> " . $subscriptions->count() . " subscription records</p>";
    
    echo "<hr>";
    echo "<p style='color: red; font-weight: bold;'>⚠️ PENTING: Hapus file ini setelah selesai!</p>";
    echo "<p>File: cleanup_duplicate_subscriptions.php</p>";
    
} catch (\Exception $e) {
    echo "<p style='color: red;'><strong>❌ Error:</strong> " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><small>Generated at " . date('Y-m-d H:i:s') . "</small></p>";

