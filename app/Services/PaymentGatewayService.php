<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Proses pembayaran cart dan update sistem tagihan
     */
    public function processCartPayment($studentId, $amount, $paymentMethod, $cartItems, $gatewayTransactionId = null)
    {
        try {
            DB::beginTransaction();
            
            $results = [];
            
            foreach ($cartItems as $item) {
                $result = $this->processCartItem($studentId, $item, $paymentMethod, $gatewayTransactionId);
                $results[] = $result;
            }
            
            DB::commit();
            return $results;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing cart payment: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Proses item cart individual
     */
    private function processCartItem($studentId, $item, $paymentMethod, $gatewayTransactionId = null)
    {
        $billType = $item['bill_type'] ?? null;
        $billId = $item['bill_id'] ?? null;
        $amount = $item['amount'] ?? 0;
        $posId = $item['pos_id'] ?? null;
        $monthId = $item['month_id'] ?? null;
        
        // Cari record tagihan yang sesuai
        $billRecord = $this->findBillRecord($studentId, $billType, $billId, $amount, $posId, $monthId);
        
        if (!$billRecord) {
            // Jika tidak ada record yang sesuai, buat record baru
            $billRecord = $this->createBillRecord($studentId, $billType, $amount, $posId, $monthId);
        }
        
        // Update status pembayaran di record tagihan
        $this->updateBillPaymentStatus($billRecord, $amount, $paymentMethod);
        
        // Buat record transfer dengan informasi lengkap
        $transferRecord = $this->createTransferRecord($studentId, $billRecord, $amount, $paymentMethod, $gatewayTransactionId);
        
        return [
            'success' => true,
            'bill_record' => $billRecord,
            'transfer_record' => $transferRecord,
            'message' => 'Payment processed successfully'
        ];
    }
    
    /**
     * Cari record tagihan yang sesuai
     */
    private function findBillRecord($studentId, $billType, $billId, $amount, $posId, $monthId)
    {
        if ($billType === 'bulanan') {
            return DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->where('b.student_student_id', $studentId)
                ->where('b.bulan_bill', $amount)
                ->where('p.pos_pos_id', $posId)
                ->where('b.month_month_id', $monthId)
                ->whereNull('b.bulan_date_pay') // Belum dibayar
                ->select('b.*', 'p.pos_pos_id')
                ->first();
        } elseif ($billType === 'bebas') {
            return DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->where('be.student_student_id', $studentId)
                ->where('be.bebas_bill', $amount)
                ->where('p.pos_pos_id', $posId)
                ->whereNull('be.bebas_date_pay') // Belum dibayar
                ->select('be.*', 'p.pos_pos_id')
                ->first();
        }
        
        return null;
    }
    
    /**
     * Buat record tagihan baru jika tidak ada yang sesuai
     */
    private function createBillRecord($studentId, $billType, $amount, $posId, $monthId)
    {
        // Cari payment record yang sesuai dengan pos
        $paymentRecord = DB::table('payment')
            ->where('pos_pos_id', $posId)
            ->where('payment_type', $billType === 'bulanan' ? 'BULAN' : 'BEBAS')
            ->first();
        
        if (!$paymentRecord) {
            throw new \Exception("Payment record not found for pos_id: {$posId}");
        }
        
        if ($billType === 'bulanan') {
            $billId = DB::table('bulan')->insertGetId([
                'student_student_id' => $studentId,
                'payment_payment_id' => $paymentRecord->payment_id,
                'month_month_id' => $monthId,
                'bulan_bill' => $amount,
                'bulan_date_pay' => now(),
                'bulan_status' => 1, // Lunas
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return DB::table('bulan')->where('bulan_id', $billId)->first();
        } elseif ($billType === 'bebas') {
            $billId = DB::table('bebas')->insertGetId([
                'student_student_id' => $studentId,
                'payment_payment_id' => $paymentRecord->payment_id,
                'bebas_bill' => $amount,
                'bebas_date_pay' => now(),
                'bebas_status' => 1, // Lunas
                'created_at' => now(),
                'updated_at' => now()
            ]);
            
            return DB::table('bebas')->where('bebas_id', $billId)->first();
        }
        
        return null;
    }
    
    /**
     * Update status pembayaran di record tagihan
     */
    private function updateBillPaymentStatus($billRecord, $amount, $paymentMethod)
    {
        if (isset($billRecord->bulan_id)) {
            // Update bulan record
            DB::table('bulan')
                ->where('bulan_id', $billRecord->bulan_id)
                ->update([
                    'bulan_date_pay' => now(),
                    'bulan_status' => 1, // Lunas
                    'updated_at' => now()
                ]);
        } elseif (isset($billRecord->bebas_id)) {
            // Update bebas record
            DB::table('bebas')
                ->where('bebas_id', $billRecord->bebas_id)
                ->update([
                    'bebas_date_pay' => now(),
                    'bebas_status' => 1, // Lunas
                    'updated_at' => now()
                ]);
        }
    }
    
    /**
     * Buat record transfer dengan informasi lengkap
     */
    private function createTransferRecord($studentId, $billRecord, $amount, $paymentMethod, $gatewayTransactionId = null)
    {
        // Tentukan bill_type dan actual_bill_id
        $billType = isset($billRecord->bulan_id) ? 'bulanan' : 'bebas';
        $actualBillId = $billRecord->bulan_id ?? $billRecord->bebas_id;
        
        // Ambil informasi pos
        $posInfo = $this->getPosInfo($billRecord->payment_payment_id);
        
        // Ambil informasi bulan jika tagihan bulanan
        $monthInfo = null;
        if ($billType === 'bulanan' && isset($billRecord->month_month_id)) {
            $monthInfo = $this->getMonthInfo($billRecord->month_month_id);
        }
        
        $transferData = [
            'student_id' => $studentId,
            'detail' => "Pembayaran Cart via {$paymentMethod}",
            'payment_method' => $paymentMethod,
            'gateway_transaction_id' => $gatewayTransactionId,
            'bill_type' => $billType,
            'bill_id' => $actualBillId, // Link ke record tagihan yang sebenarnya
            'confirm_pay' => $amount,
            'status' => 1, // Success
            'actual_bill_id' => $actualBillId,
            'actual_bill_type' => $billType,
            'pos_id' => $posInfo['pos_id'] ?? null,
            'pos_name' => $posInfo['pos_name'] ?? null,
            'month_id' => $monthInfo['month_id'] ?? null,
            'month_name' => $monthInfo['month_name'] ?? null,
            'bill_description' => $this->generateBillDescription($billType, $posInfo, $monthInfo),
            'created_at' => now(),
            'updated_at' => now()
        ];
        
        $transferId = DB::table('transfer')->insertGetId($transferData);
        return DB::table('transfer')->where('transfer_id', $transferId)->first();
    }
    
    /**
     * Ambil informasi pos
     */
    private function getPosInfo($paymentId)
    {
        $payment = DB::table('payment as p')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('p.payment_id', $paymentId)
            ->select('pos.pos_id', 'pos.pos_name')
            ->first();
        
        return $payment ? [
            'pos_id' => $payment->pos_id,
            'pos_name' => $payment->pos_name
        ] : null;
    }
    
    /**
     * Ambil informasi bulan
     */
    private function getMonthInfo($monthId)
    {
        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        return [
            'month_id' => $monthId,
            'month_name' => $monthNames[$monthId] ?? ''
        ];
    }
    
    /**
     * Generate deskripsi tagihan
     */
    private function generateBillDescription($billType, $posInfo, $monthInfo)
    {
        $posName = $posInfo['pos_name'] ?? 'Unknown';
        
        if ($billType === 'bulanan' && $monthInfo) {
            return "{$posName} - {$monthInfo['month_name']}";
        } elseif ($billType === 'bebas') {
            return $posName;
        }
        
        return $posName;
    }
}
