<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateExistingTransfers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transfers:update-existing {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update existing transfer records with bill information';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('🔄 Starting to update existing transfer records...');
        
        try {
            // Get transfer records that need updating
            $transfers = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->whereNotNull('t.confirm_pay')
                ->whereIn('t.payment_method', ['midtrans', 'tripay', 'payment_gateway'])
                ->where('t.bill_id', 0) // Yang belum di-link
                ->select('t.*', 's.student_full_name')
                ->get();
            
            $this->info("📊 Found {$transfers->count()} transfer records to update");
            
            if ($transfers->count() == 0) {
                $this->info('✅ No transfers need updating');
                return 0;
            }
            
            $updatedCount = 0;
            $skippedCount = 0;
            
            $progressBar = $this->output->createProgressBar($transfers->count());
            $progressBar->start();
            
            foreach ($transfers as $transfer) {
                try {
                    // Try to find matching bill record
                    $billRecord = $this->findMatchingBillRecord($transfer);
                    
                    if ($billRecord) {
                        if (!$isDryRun) {
                            // Update transfer record with bill information
                            $this->updateTransferWithBillInfo($transfer, $billRecord);
                        }
                        
                        $updatedCount++;
                        $this->line("\n✅ Updated transfer ID {$transfer->transfer_id} for {$transfer->student_full_name}");
                    } else {
                        $skippedCount++;
                        $this->line("\n⚠️  Skipped transfer ID {$transfer->transfer_id} - no matching bill found");
                    }
                    
                } catch (\Exception $e) {
                    $this->error("\n❌ Error updating transfer ID {$transfer->transfer_id}: " . $e->getMessage());
                    Log::error("Error updating transfer ID {$transfer->transfer_id}: " . $e->getMessage());
                }
                
                $progressBar->advance();
            }
            
            $progressBar->finish();
            $this->newLine();
            
            if ($isDryRun) {
                $this->info("🔍 DRY RUN RESULTS:");
                $this->info("  - Would update: {$updatedCount} transfers");
                $this->info("  - Would skip: {$skippedCount} transfers");
                $this->info("  - Total: {$transfers->count()} transfers");
            } else {
                $this->info("✅ UPDATE COMPLETED:");
                $this->info("  - Updated: {$updatedCount} transfers");
                $this->info("  - Skipped: {$skippedCount} transfers");
                $this->info("  - Total: {$transfers->count()} transfers");
            }
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error("❌ Error: " . $e->getMessage());
            Log::error("Error in UpdateExistingTransfers command: " . $e->getMessage());
            return 1;
        }
    }
    
    /**
     * Find matching bill record for transfer
     */
    private function findMatchingBillRecord($transfer)
    {
        // Search based on student, amount, and nearby date
        $startDate = date('Y-m-d', strtotime($transfer->updated_at . ' -7 days'));
        $endDate = date('Y-m-d', strtotime($transfer->updated_at . ' +7 days'));
        
        if ($transfer->bill_type === 'bulanan') {
            return DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('b.student_student_id', $transfer->student_id)
                ->where('b.bulan_bill', $transfer->confirm_pay)
                ->whereBetween('b.bulan_input_date', [$startDate, $endDate])
                ->select('b.*', 'p.pos_pos_id', 'pos.pos_name')
                ->first();
        } elseif ($transfer->bill_type === 'bebas') {
            return DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->where('be.student_student_id', $transfer->student_id)
                ->where('be.bebas_bill', $transfer->confirm_pay)
                ->whereBetween('be.bebas_input_date', [$startDate, $endDate])
                ->select('be.*', 'p.pos_pos_id', 'pos.pos_name')
                ->first();
        }
        
        return null;
    }
    
    /**
     * Update transfer record with bill information
     */
    private function updateTransferWithBillInfo($transfer, $billRecord)
    {
        $billType = isset($billRecord->bulan_id) ? 'bulanan' : 'bebas';
        $actualBillId = $billRecord->bulan_id ?? $billRecord->bebas_id;
        
        // Get month information if monthly bill
        $monthInfo = null;
        if ($billType === 'bulanan' && isset($billRecord->month_month_id)) {
            $monthNames = [
                1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
            ];
            
            $monthInfo = [
                'month_id' => $billRecord->month_month_id,
                'month_name' => $monthNames[$billRecord->month_month_id] ?? ''
            ];
        }
        
        $updateData = [
            'bill_id' => $actualBillId,
            'actual_bill_id' => $actualBillId,
            'actual_bill_type' => $billType,
            'pos_id' => $billRecord->pos_pos_id,
            'pos_name' => $billRecord->pos_name,
            'month_id' => $monthInfo['month_id'] ?? null,
            'month_name' => $monthInfo['month_name'] ?? null,
            'bill_description' => $this->generateBillDescription($billType, $billRecord, $monthInfo),
            'updated_at' => now()
        ];
        
        DB::table('transfer')
            ->where('transfer_id', $transfer->transfer_id)
            ->update($updateData);
    }
    
    /**
     * Generate bill description
     */
    private function generateBillDescription($billType, $billRecord, $monthInfo)
    {
        $posName = $billRecord->pos_name ?? 'Unknown';
        
        if ($billType === 'bulanan' && $monthInfo) {
            return "{$posName} - {$monthInfo['month_name']}";
        } elseif ($billType === 'bebas') {
            return $posName;
        }
        
        return $posName;
    }
}
