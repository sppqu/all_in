<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Payment;
use App\Models\OnlinePayment;
use Illuminate\Support\Facades\DB;

abstract class Controller
{
    public static function dashboardData()
    {
        // Data untuk chart kelas
        $kelas = ClassModel::orderBy('class_name')->get();
        $labels = $kelas->pluck('class_name');
        $data = $kelas->map(function($k) {
            return $k->students()->where('student_status', 1)->count();
        });

        // Statistik pembayaran dari transfer table
        $totalPayments = DB::table('transfer')->count();
        $successPayments = DB::table('transfer')->where('status', 1)->count();
        $pendingPayments = DB::table('transfer')->where('status', 0)->count();
        $failedPayments = DB::table('transfer')->where('status', 'failed')->count();

        // Statistik keuangan berdasarkan data sebenarnya
        // Pembayaran hari ini
        $todayPayments = DB::table('transfer')
            ->where('status', 1)
            ->whereDate('created_at', today())
            ->sum('confirm_pay');
        
        // Pembayaran bulan ini
        $monthPayments = DB::table('transfer')
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('confirm_pay');
        
        // Pembayaran tahun ini
        $yearPayments = DB::table('transfer')
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->sum('confirm_pay');

        // Penerimaan (kas masuk) = Pembayaran sukses (tunai & bank) - debit table removed
        $todayReceipts = (
            DB::table('transfer')
                ->where('status', 1)
                ->whereDate('created_at', today())
                ->sum(DB::raw('COALESCE(confirm_pay, 0)'))
        );

        $monthReceipts = (
            DB::table('transfer')
                ->where('status', 1)
                ->whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)
                ->sum(DB::raw('COALESCE(confirm_pay, 0)'))
        );

        $yearReceipts = (
            DB::table('transfer')
                ->where('status', 1)
                ->whereYear('created_at', now()->year)
                ->sum(DB::raw('COALESCE(confirm_pay, 0)'))
        );

        $todayReceipts = (float) $todayReceipts;
        $monthReceipts = (float) $monthReceipts;
        $yearReceipts = (float) $yearReceipts;
        
        // Tabungan - menggunakan kolom yang benar
        $todaySavings = DB::table('tabungan')
            ->whereDate('tabungan_input_date', today())
            ->sum(DB::raw('COALESCE(saldo, 0)'));
        
        $monthSavings = DB::table('tabungan')
            ->whereYear('tabungan_input_date', now()->year)
            ->whereMonth('tabungan_input_date', now()->month)
            ->sum(DB::raw('COALESCE(saldo, 0)'));
        
        $yearSavings = DB::table('tabungan')
            ->whereYear('tabungan_input_date', now()->year)
            ->sum(DB::raw('COALESCE(saldo, 0)'));

        // Agregat bulanan (tahun berjalan) untuk chart: Penerimaan vs Pengeluaran
        $monthLabels = [
            'Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'
        ];
        $receiptsMonthly = array_fill(1, 12, 0.0);
        $expensesMonthly = array_fill(1, 12, 0.0);

        $transferMonthly = DB::table('transfer')
            ->where('status', 1)
            ->whereYear('created_at', now()->year)
            ->selectRaw('MONTH(created_at) as m, SUM(COALESCE(confirm_pay,0)) as s')
            ->groupBy('m')
            ->pluck('s', 'm')
            ->toArray();
        for ($i = 1; $i <= 12; $i++) {
            $receiptsMonthly[$i] = (float)($transferMonthly[$i] ?? 0);
        }

        // Pengeluaran bulanan - kredit table removed
        for ($i = 1; $i <= 12; $i++) {
            $expensesMonthly[$i] = 0.0;
        }

        // Statistik tambahan untuk info pembayaran
        $totalStudents = DB::table('students')->where('student_status', 1)->count();
        $totalClasses = DB::table('class_models')->count();
        $totalPos = DB::table('pos_pembayaran')->count();
        
        // Statistik pembayaran berdasarkan jenis
        $bulananPayments = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.status', 1)
            ->where('td.payment_type', 1)
            ->count();
            
        $bebasPayments = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.status', 1)
            ->where('td.payment_type', 2)
            ->count();
            
        // Pembayaran online (berdasarkan checkout_url yang ada)
        $onlinePayments = DB::table('transfer')
            ->where('status', 1)
            ->whereNotNull('checkout_url')
            ->count();
            
        // Pembayaran cash (berdasarkan checkout_url yang kosong)
        $cashPayments = DB::table('transfer')
            ->where('status', 1)
            ->whereNull('checkout_url')
            ->count();

        return [
            'labels' => $labels,
            'data' => $data,
            'totalPayments' => $totalPayments,
            'successPayments' => $successPayments,
            'pendingPayments' => $pendingPayments,
            'failedPayments' => $failedPayments,
            'todayPayments' => $todayPayments,
            'monthPayments' => $monthPayments,
            'yearPayments' => $yearPayments,
            'todayReceipts' => $todayReceipts,
            'monthReceipts' => $monthReceipts,
            'yearReceipts' => $yearReceipts,
            'todaySavings' => $todaySavings,
            'monthSavings' => $monthSavings,
            'yearSavings' => $yearSavings,
            'totalStudents' => $totalStudents,
            'totalClasses' => $totalClasses,
            'totalPos' => $totalPos,
            'monthLabels' => $monthLabels,
            'receiptsMonthly' => array_values($receiptsMonthly),
            'expensesMonthly' => array_values($expensesMonthly),
            'bulananPayments' => $bulananPayments,
            'bebasPayments' => $bebasPayments,
            'onlinePayments' => $onlinePayments,
            'cashPayments' => $cashPayments,
        ];
    }
}
