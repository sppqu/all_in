<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RecalculateKasSaldo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'kas:recalculate-saldo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate kas saldo based on all transactions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting kas saldo recalculation...');
        
        try {
            DB::beginTransaction();
            
            // Ambil semua kas
            $kasList = DB::table('kas')->get();
            
            foreach ($kasList as $kas) {
                $this->info("Processing kas: {$kas->nama_kas} (ID: {$kas->id})");
                
                // Hitung total penerimaan untuk kas ini
                $totalPenerimaan = DB::table('transaksi_penerimaan')
                    ->where('kas_id', $kas->id)
                    ->where('status', 'confirmed')
                    ->sum('total_penerimaan') ?? 0;
                
                // Hitung total pengeluaran untuk kas ini
                // Termasuk transaksi yang menggunakan metode pembayaran dengan kas_id yang sama
                $totalPengeluaran = DB::table('transaksi_pengeluaran as tp')
                    ->leftJoin('payment_methods as pm', 'tp.metode_pembayaran_id', '=', 'pm.id')
                    ->where(function($query) use ($kas) {
                        $query->where('tp.kas_id', $kas->id)
                              ->orWhere(function($q) use ($kas) {
                                  $q->whereNull('tp.kas_id')
                                    ->where('pm.kas_id', $kas->id);
                              });
                    })
                    ->where('tp.status', 'confirmed')
                    ->sum('tp.total_pengeluaran') ?? 0;
                
                // Hitung total transfer masuk (kas tujuan)
                $totalTransferMasuk = DB::table('cash_transfers')
                    ->where('kas_tujuan_id', $kas->id)
                    ->sum('jumlah_transfer') ?? 0;
                
                // Hitung total transfer keluar (kas asal)
                $totalTransferKeluar = DB::table('cash_transfers')
                    ->where('kas_asal_id', $kas->id)
                    ->sum('jumlah_transfer') ?? 0;
                
                // Hitung saldo baru
                // Saldo = penerimaan - pengeluaran + transfer_masuk - transfer_keluar
                // (Kita asumsikan saldo awal adalah 0, atau bisa disesuaikan jika ada saldo awal)
                $saldoBaru = $totalPenerimaan - $totalPengeluaran + $totalTransferMasuk - $totalTransferKeluar;
                
                // Update saldo kas
                DB::table('kas')
                    ->where('id', $kas->id)
                    ->update(['saldo' => $saldoBaru]);
                
                $this->info("  - Total Penerimaan: " . number_format($totalPenerimaan, 2, ',', '.'));
                $this->info("  - Total Pengeluaran: " . number_format($totalPengeluaran, 2, ',', '.'));
                $this->info("  - Transfer Masuk: " . number_format($totalTransferMasuk, 2, ',', '.'));
                $this->info("  - Transfer Keluar: " . number_format($totalTransferKeluar, 2, ',', '.'));
                $this->info("  - Saldo Baru: " . number_format($saldoBaru, 2, ',', '.'));
                $this->info("");
                
                Log::info('Kas saldo recalculated', [
                    'kas_id' => $kas->id,
                    'kas_name' => $kas->nama_kas,
                    'total_penerimaan' => $totalPenerimaan,
                    'total_pengeluaran' => $totalPengeluaran,
                    'transfer_masuk' => $totalTransferMasuk,
                    'transfer_keluar' => $totalTransferKeluar,
                    'saldo_baru' => $saldoBaru
                ]);
            }
            
            DB::commit();
            
            $this->info('Kas saldo recalculation completed successfully!');
            return 0;
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Error recalculating kas saldo: ' . $e->getMessage());
            Log::error('Error recalculating kas saldo: ' . $e->getMessage());
            return 1;
        }
    }
}

