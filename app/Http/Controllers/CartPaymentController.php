<?php

namespace App\Http\Controllers;

use App\Services\PaymentGatewayService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CartPaymentController extends Controller
{
    protected $paymentGatewayService;
    
    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }
    
    /**
     * Show cart payment management page
     */
    public function index()
    {
        $students = \App\Models\Student::with('class')->get();
        return view('cart-payment.index', compact('students'));
    }
    
    /**
     * Proses pembayaran cart
     */
    public function processPayment(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'payment_method' => 'required|in:midtrans,tripay,payment_gateway',
                'cart_items' => 'required|array|min:1',
                'cart_items.*.bill_type' => 'required|in:bulanan,bebas',
                'cart_items.*.amount' => 'required|numeric|min:0',
                'cart_items.*.pos_id' => 'required|exists:pos_pembayaran,pos_id',
                'cart_items.*.month_id' => 'nullable|integer|min:1|max:12', // Hanya untuk tagihan bulanan
                'gateway_transaction_id' => 'nullable|string'
            ]);
            
            $studentId = $request->student_id;
            $paymentMethod = $request->payment_method;
            $cartItems = $request->cart_items;
            $gatewayTransactionId = $request->gateway_transaction_id;
            
            // Proses pembayaran menggunakan service
            $results = $this->paymentGatewayService->processCartPayment(
                $studentId,
                $request->total_amount ?? 0,
                $paymentMethod,
                $cartItems,
                $gatewayTransactionId
            );
            
            return response()->json([
                'success' => true,
                'message' => 'Payment processed successfully',
                'data' => $results
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error processing cart payment: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error processing payment: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get cart items for student
     */
    public function getCartItems(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:students,student_id'
            ]);
            
            $studentId = $request->student_id;
            
            // Ambil tagihan yang belum dibayar
            $unpaidBills = $this->getUnpaidBills($studentId);
            
            return response()->json([
                'success' => true,
                'data' => $unpaidBills
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting cart items: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error getting cart items: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Get unpaid bills for student
     */
    private function getUnpaidBills($studentId)
    {
        $unpaidBills = [];
        
        // Ambil tagihan bulanan yang belum dibayar
        $bulanBills = DB::table('bulan as b')
            ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('b.student_student_id', $studentId)
            ->whereNull('b.bulan_date_pay')
            ->select(
                'b.bulan_id as bill_id',
                DB::raw("'bulanan' as bill_type"),
                'b.bulan_bill as amount',
                'pos.pos_id',
                'pos.pos_name',
                'b.month_month_id as month_id',
                DB::raw("CASE 
                    WHEN b.month_month_id = 1 THEN 'Januari'
                    WHEN b.month_month_id = 2 THEN 'Februari'
                    WHEN b.month_month_id = 3 THEN 'Maret'
                    WHEN b.month_month_id = 4 THEN 'April'
                    WHEN b.month_month_id = 5 THEN 'Mei'
                    WHEN b.month_month_id = 6 THEN 'Juni'
                    WHEN b.month_month_id = 7 THEN 'Juli'
                    WHEN b.month_month_id = 8 THEN 'Agustus'
                    WHEN b.month_month_id = 9 THEN 'September'
                    WHEN b.month_month_id = 10 THEN 'Oktober'
                    WHEN b.month_month_id = 11 THEN 'November'
                    WHEN b.month_month_id = 12 THEN 'Desember'
                    ELSE ''
                END as month_name")
            )
            ->get();
        
        foreach ($bulanBills as $bill) {
            $unpaidBills[] = [
                'bill_id' => $bill->bill_id,
                'bill_type' => $bill->bill_type,
                'amount' => $bill->amount,
                'pos_id' => $bill->pos_id,
                'pos_name' => $bill->pos_name,
                'month_id' => $bill->month_id,
                'month_name' => $bill->month_name,
                'description' => "{$bill->pos_name} - {$bill->month_name}"
            ];
        }
        
        // Ambil tagihan bebas yang belum dibayar
        $bebasBills = DB::table('bebas as be')
            ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
            ->where('be.student_student_id', $studentId)
            ->whereNull('be.bebas_date_pay')
            ->select(
                'be.bebas_id as bill_id',
                DB::raw("'bebas' as bill_type"),
                'be.bebas_bill as amount',
                'pos.pos_id',
                'pos.pos_name',
                DB::raw('NULL as month_id'),
                DB::raw('NULL as month_name'),
                'be.bebas_desc as description'
            )
            ->get();
        
        foreach ($bebasBills as $bill) {
            $unpaidBills[] = [
                'bill_id' => $bill->bill_id,
                'bill_type' => $bill->bill_type,
                'amount' => $bill->amount,
                'pos_id' => $bill->pos_id,
                'pos_name' => $bill->pos_name,
                'month_id' => null,
                'month_name' => null,
                'description' => $bill->description ?: $bill->pos_name
            ];
        }
        
        return $unpaidBills;
    }
    
    /**
     * Update existing transfer records with bill information
     */
    public function updateExistingTransfers()
    {
        try {
            // Update transfer records yang sudah ada dengan informasi bill yang sesuai
            $transfers = DB::table('transfer as t')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->whereNotNull('t.confirm_pay')
                ->whereIn('t.payment_method', ['midtrans', 'tripay', 'payment_gateway'])
                ->where('t.bill_id', 0) // Yang belum di-link
                ->select('t.*', 's.student_full_name')
                ->get();
            
            $updatedCount = 0;
            
            foreach ($transfers as $transfer) {
                // Coba cari record tagihan yang sesuai berdasarkan student, amount, dan tanggal
                $billRecord = $this->findMatchingBillRecord($transfer);
                
                if ($billRecord) {
                    // Update transfer record dengan informasi bill
                    $this->updateTransferWithBillInfo($transfer, $billRecord);
                    $updatedCount++;
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => "Updated {$updatedCount} transfer records",
                'updated_count' => $updatedCount
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating existing transfers: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Error updating existing transfers: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Find matching bill record for transfer
     */
    private function findMatchingBillRecord($transfer)
    {
        // Cari berdasarkan student, amount, dan tanggal yang berdekatan
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
        
        // Ambil informasi bulan jika tagihan bulanan
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
