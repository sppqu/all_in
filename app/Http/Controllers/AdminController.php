<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ClassModel;
use App\Models\Student;
use App\Models\Payment;

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\PaymentController as RekapController;

class AdminController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = auth()->user();
        
        // Redirect superadmin dan admin_yayasan ke foundation dashboard
        if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
            return redirect()->route('manage.foundation.dashboard');
        }
        
        // Redirect admin SPMB ke dashboard SPMB
        if ($user->role === 'spmb_admin' || ($user->role !== 'superadmin' && $user->spmb_admin_access)) {
            return redirect()->route('manage.spmb.index');
        }
        
        // Get current school ID untuk filtering
        $currentSchoolId = currentSchoolId();
        
        if (!$currentSchoolId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
        }
        
        // Get current school data
        $currentSchool = \App\Models\School::find($currentSchoolId);
        
        // Data untuk chart kelas - filter by school_id
        $kelas = ClassModel::where('school_id', $currentSchoolId)->orderBy('class_name')->get();
        $labels = $kelas->pluck('class_name');
        $data = $kelas->map(function($k) {
            return $k->students()->where('student_status', 1)->count();
        });

        // Statistik pembayaran dari tabel transfer - filter by school_id
        $totalPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->count();
        $successPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 1)
            ->count();
        $pendingPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 0)
            ->count();
        $failedPayments = DB::table('transfer as t')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 2)
            ->count();

        // ==========================================================
        // KARTU PEMBAYARAN: hanya transaksi pembayaran (cash log_trx + transfer sukses)
        // ==========================================================
        // Hari ini
        // Kas murni: exclude entri yang terkait transfer (hindari duplikasi) - filter by school_id
        $cashToday = (float) (
            DB::table('log_trx as lt')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $currentSchoolId)
                ->whereDate('lt.log_trx_input_date', today())
                ->whereNotExists(function($q){
                    $q->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                      ->whereRaw('td.bulan_id = lt.bulan_bulan_id')
                      ->whereIn('t.status',[1,2]);
                })
                ->sum(DB::raw('COALESCE(b.bulan_bill,0)'))
            +
            DB::table('log_trx as lt')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $currentSchoolId)
                ->whereDate('lt.log_trx_input_date', today())
                ->whereNotExists(function($q){
                    $q->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                      ->whereRaw('td.bebas_id = lt.bebas_pay_bebas_pay_id')
                      ->whereIn('t.status',[1,2]);
                })
                ->sum(DB::raw('COALESCE(bp.bebas_pay_bill,0)'))
        );
        $transferToday = (float) DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 1)
            ->whereDate('t.updated_at', today())
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->sum(DB::raw('COALESCE(td.subtotal,0)'));
        $todayPayments = $cashToday + $transferToday;

        // Bulan ini (Month-To-Date): dari awal bulan hingga saat ini - filter by school_id
        $startOfMonth = now()->copy()->startOfMonth();
        $endOfNow = now();
        $cashMonth = (float) (
            DB::table('log_trx as lt')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $currentSchoolId)
                ->whereBetween('lt.log_trx_input_date', [$startOfMonth, $endOfNow])
                ->whereNotExists(function($q){
                    $q->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                      ->whereRaw('td.bulan_id = lt.bulan_bulan_id')
                      ->whereIn('t.status',[1,2]);
                })
                ->sum(DB::raw('COALESCE(b.bulan_bill,0)'))
            +
            DB::table('log_trx as lt')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $currentSchoolId)
                ->whereBetween('lt.log_trx_input_date', [$startOfMonth, $endOfNow])
                ->whereNotExists(function($q){
                    $q->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                      ->whereRaw('td.bebas_id = lt.bebas_pay_bebas_pay_id')
                      ->whereIn('t.status',[1,2]);
                })
                ->sum(DB::raw('COALESCE(bp.bebas_pay_bill,0)'))
        );
        $transferMonth = (float) DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 1)
            ->whereBetween('t.updated_at', [$startOfMonth, $endOfNow])
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->sum(DB::raw('COALESCE(td.subtotal,0)'));
        // Hitung ulang "Bulan ini" mengikuti persis logika rekapitulasi (MTD)
        $rekapMonth = app(RekapController::class)->getRekapitulasiData(
            null,
            $startOfMonth->toDateString(),
            $endOfNow->toDateString(),
            null,
            null,
            null
        );
        $monthPayments = (float) ($rekapMonth->sum('cash_amount') + $rekapMonth->sum('transfer_amount') + $rekapMonth->sum('gateway_amount'));

        // Tahun ini (Year-To-Date): dari 1 Januari hingga saat ini - filter by school_id
        $startOfYear = now()->copy()->startOfYear();
        $cashYear = (float) (
            DB::table('log_trx as lt')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $currentSchoolId)
                ->whereBetween('lt.log_trx_input_date', [$startOfYear, $endOfNow])
                ->whereNotExists(function($q){
                    $q->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                      ->whereRaw('td.bulan_id = lt.bulan_bulan_id')
                      ->where('t.status',1);
                })
                ->sum(DB::raw('COALESCE(b.bulan_bill,0)'))
            +
            DB::table('log_trx as lt')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $currentSchoolId)
                ->whereBetween('lt.log_trx_input_date', [$startOfYear, $endOfNow])
                ->whereNotExists(function($q){
                    $q->select(DB::raw(1))
                      ->from('transfer_detail as td')
                      ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                      ->whereRaw('td.bebas_id = lt.bebas_pay_bebas_pay_id')
                      ->where('t.status',1);
                })
                ->sum(DB::raw('COALESCE(bp.bebas_pay_bill,0)'))
        );
        $transferYear = (float) DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 1)
            ->whereBetween('t.updated_at', [$startOfYear, $endOfNow])
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->sum(DB::raw('COALESCE(td.subtotal,0)'));
        // Hitung ulang "Tahun ini" untuk periode Juli-Juni (tahun fiskal)
        $currentYear = now()->year;
        $currentMonth = now()->month;
        
        // Tentukan periode Juli-Juni
        if ($currentMonth >= 7) {
            // Jika sekarang Juli-Desember, tahun fiskal = Juli tahun ini - Juni tahun depan
            $fiscalStart = now()->startOfYear()->month(7); // Juli tahun ini
            $fiscalEnd = now()->addYear()->startOfYear()->month(6)->endOfMonth(); // Juni tahun depan
        } else {
            // Jika sekarang Januari-Juni, tahun fiskal = Juli tahun lalu - Juni tahun ini
            $fiscalStart = now()->subYear()->startOfYear()->month(7); // Juli tahun lalu
            $fiscalEnd = now()->startOfYear()->month(6)->endOfMonth(); // Juni tahun ini
        }
        
        $rekapYear = app(RekapController::class)->getRekapitulasiData(
            null,
            $fiscalStart->toDateString(),
            $fiscalEnd->toDateString(),
            null,
            null,
            null
        );
        $yearPayments = (float) ($rekapYear->sum('cash_amount') + $rekapYear->sum('transfer_amount') + $rekapYear->sum('gateway_amount'));

        // Penerimaan (kas masuk) dari tabel transaksi_penerimaan (untuk kartu Penerimaan)
        $todayReceipts = DB::table('transaksi_penerimaan')
            ->whereDate('tanggal_penerimaan', today())
            ->where('status', 'confirmed')
            ->sum('total_penerimaan') ?? 0;

        $monthReceipts = DB::table('transaksi_penerimaan')
            ->whereYear('tanggal_penerimaan', now()->year)
            ->whereMonth('tanggal_penerimaan', now()->month)
            ->where('status', 'confirmed')
            ->sum('total_penerimaan') ?? 0;

        $yearReceipts = DB::table('transaksi_penerimaan')
            ->whereYear('tanggal_penerimaan', now()->year)
            ->where('status', 'confirmed')
            ->sum('total_penerimaan') ?? 0;

        // Pastikan tipe numerik
        $todayReceipts = (float) $todayReceipts;
        $monthReceipts = (float) $monthReceipts;
        $yearReceipts = (float) $yearReceipts;
        
        // Pengeluaran (expenses)
        $todayExpenses = DB::table('transaksi_pengeluaran')
            ->whereDate('tanggal_pengeluaran', today())
            ->where('status', 'confirmed')
            ->sum('total_pengeluaran') ?? 0;
        $todayExpenses = (float) $todayExpenses;

        $monthExpenses = DB::table('transaksi_pengeluaran')
            ->whereYear('tanggal_pengeluaran', now()->year)
            ->whereMonth('tanggal_pengeluaran', now()->month)
            ->where('status', 'confirmed')
            ->sum('total_pengeluaran') ?? 0;
        $monthExpenses = (float) $monthExpenses;

        $yearExpenses = DB::table('transaksi_pengeluaran')
            ->whereYear('tanggal_pengeluaran', now()->year)
            ->where('status', 'confirmed')
            ->sum('total_pengeluaran') ?? 0;
        $yearExpenses = (float) $yearExpenses;
        
        // Tabungan
        $todaySavings = DB::table('tabungan')
            ->whereDate('tabungan_input_date', today())
            ->sum('saldo') ?? 0;
        
        $monthSavings = DB::table('tabungan')
            ->whereYear('tabungan_input_date', now()->year)
            ->whereMonth('tabungan_input_date', now()->month)
            ->sum('saldo') ?? 0;
        
        $yearSavings = DB::table('tabungan')
            ->whereYear('tabungan_input_date', now()->year)
            ->sum('saldo') ?? 0;

        // Statistik tambahan - filter by school_id
        $totalStudents = DB::table('students')
            ->where('school_id', $currentSchoolId)
            ->where('student_status', 1)
            ->count();
        $totalClasses = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->count();
        $totalPos = DB::table('pos_pembayaran')->count(); // POS tidak perlu filter by school
        
        // Filter periode (tahun ajaran Juli-Juni) - perlu didefinisikan dulu
        $periods = DB::table('periods')
            ->where('school_id', $currentSchoolId)
            ->orderBy('period_id', 'desc')
            ->get();
        $selectedPeriodId = $request->input('period_id');
        if (!$selectedPeriodId && $periods->count() > 0) {
            $active = $periods->firstWhere('period_status', 1);
            $selectedPeriodId = $active->period_id ?? $periods->first()->period_id;
        }
        $selectedPeriod = $periods->firstWhere('period_id', (int)$selectedPeriodId) ?? null;
        
        // Tentukan rentang tanggal untuk tahun ajaran aktif
        if ($selectedPeriod) {
            $periodStartDate = \Carbon\Carbon::create((int)$selectedPeriod->period_start, 7, 1, 0, 0, 0)->startOfDay();
            $periodEndDate = \Carbon\Carbon::create((int)$selectedPeriod->period_end, 6, 30, 23, 59, 59)->endOfDay();
        } else {
            // Fallback: tahun ajaran saat ini (Juli - Juni)
            $currentYear = now()->year;
            if (now()->month >= 7) {
                // Jika bulan >= Juli, tahun ajaran dimulai tahun ini
                $periodStartDate = \Carbon\Carbon::create($currentYear, 7, 1, 0, 0, 0)->startOfDay();
                $periodEndDate = \Carbon\Carbon::create($currentYear + 1, 6, 30, 23, 59, 59)->endOfDay();
            } else {
                // Jika bulan < Juli, tahun ajaran dimulai tahun lalu
                $periodStartDate = \Carbon\Carbon::create($currentYear - 1, 7, 1, 0, 0, 0)->startOfDay();
                $periodEndDate = \Carbon\Carbon::create($currentYear, 6, 30, 23, 59, 59)->endOfDay();
            }
        }
        
        // Statistik untuk kartu baru - filter by school_id dan tahun ajaran aktif
        $activePeriodId = $selectedPeriodId ?? null;
        
        // Siswa Aktif (T.A. Ini) - hanya siswa yang aktif
        $siswaAktifQuery = DB::table('students')
            ->where('school_id', $currentSchoolId)
            ->where('student_status', 1);
        
        // Total siswa aktif di T.A. ini (semua siswa aktif, tidak peduli kapan masuknya)
        $totalSiswaAktif = $siswaAktifQuery->count();
        
        // Breakdown Reguler/Pindahan/Masuk Kembali berdasarkan tanggal masuk
        // Reguler: siswa yang masuk di awal tahun ajaran (Juli-September)
        $regulerStart = $periodStartDate->copy();
        $regulerEnd = $periodStartDate->copy()->addMonths(3)->endOfDay(); // Juli-September
        
        $siswaAktifReguler = (clone $siswaAktifQuery)
            ->whereBetween('student_input_date', [$regulerStart, $regulerEnd])
            ->count();
        
        // Pindahan: siswa yang masuk di tengah tahun ajaran (Oktober-Maret)
        $pindahanStart = $periodStartDate->copy()->addMonths(3)->addDay()->startOfDay(); // Oktober
        $pindahanEnd = $periodStartDate->copy()->addMonths(9)->endOfDay(); // Maret
        
        $siswaAktifPindahan = (clone $siswaAktifQuery)
            ->whereBetween('student_input_date', [$pindahanStart, $pindahanEnd])
            ->count();
        
        // Masuk Kembali: siswa yang statusnya pernah 0 kemudian kembali jadi 1 di tahun ajaran ini
        // Untuk sementara, hitung siswa yang student_input_date di akhir tahun ajaran (April-Juni)
        $masukKembaliStart = $periodStartDate->copy()->addMonths(9)->addDay()->startOfDay(); // April
        $masukKembaliEnd = $periodEndDate->copy(); // Juni
        
        $siswaAktifMasukKembali = (clone $siswaAktifQuery)
            ->whereBetween('student_input_date', [$masukKembaliStart, $masukKembaliEnd])
            ->count();
        
        // Jika total breakdown tidak sama dengan total aktif, sisanya adalah Reguler (untuk siswa yang masuk sebelum T.A. ini atau tidak masuk kategori)
        $currentReguler = $siswaAktifReguler;
        $siswaAktifReguler = max(0, $totalSiswaAktif - $siswaAktifPindahan - $siswaAktifMasukKembali);
        
        // Jumlah Kelas (T.A. Aktif) - semua kelas di sekolah ini
        $jumlahKelasTA = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->count();
        
        // Total Alumni (s.d. T.A. Aktif) - siswa yang status non-aktif
        $totalAlumni = DB::table('students')
            ->where('school_id', $currentSchoolId)
            ->where('student_status', 0)
            ->count();
        
        // Total Siswa Keluar (T.A. Aktif) - siswa yang menjadi non-aktif di tahun ajaran ini
        $totalSiswaKeluar = DB::table('students')
            ->where('school_id', $currentSchoolId)
            ->where('student_status', 0)
            ->whereBetween('student_last_update', [$periodStartDate, $periodEndDate])
            ->count();

        // Gunakan rentang tanggal periode yang sudah dihitung di atas
        $startDate = $periodStartDate;
        $endDate = $periodEndDate;
        
        // Agregat bulanan untuk chart (periode Juli-Juni)
        $monthLabels = [
            'Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'
        ];
        $receiptsMonthly = array_fill(1, 12, 0.0);
        $expensesMonthly = array_fill(1, 12, 0.0);
        $paymentsMonthly = array_fill(1, 12, 0.0);

        // Penerimaan per bulan
        $receiptsMonthlyData = DB::table('transaksi_penerimaan')
            ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->selectRaw('MONTH(tanggal_penerimaan) as m, SUM(COALESCE(total_penerimaan,0)) as s')
            ->groupBy('m')
            ->pluck('s', 'm')
            ->toArray();
        
        // Pengeluaran per bulan
        $expensesMonthlyData = DB::table('transaksi_pengeluaran')
            ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->selectRaw('MONTH(tanggal_pengeluaran) as m, SUM(COALESCE(total_pengeluaran,0)) as s')
            ->groupBy('m')
            ->pluck('s', 'm')
            ->toArray();
        
        // Pembayaran per bulan = samakan dengan rekapitulasi (cash + transfer + gateway), dibatasi periode terpilih
        for ($i = 1; $i <= 12; $i++) {
            $receiptsMonthly[$i] = (float)($receiptsMonthlyData[$i] ?? 0);
            $expensesMonthly[$i] = (float)($expensesMonthlyData[$i] ?? 0);
            $paymentsMonthly[$i] = 0.0;
        }

        // Iterasi 12 bulan dalam tahun fiskal (Juli->Juni)
        // Mulai dari Juli tahun ajaran
        $fiscalStart = $startDate->copy();
        if ($fiscalStart->month != 7) {
            if ($fiscalStart->month < 7) {
                $fiscalStart = $fiscalStart->subYear()->month(7)->startOfMonth();
            } else {
                $fiscalStart = $fiscalStart->month(7)->startOfMonth();
            }
        }
        
        $cursor = $fiscalStart->copy();
        $monthIndex = 1; // Index untuk array paymentsMonthly (1-12)
        
        for ($k = 0; $k < 12; $k++) {
            $monthStart = $cursor->copy()->startOfMonth();
            $monthEnd = $cursor->copy()->endOfMonth();

            // Batasi agar tetap dalam rentang periode
            if ($monthStart->lt($startDate)) { $monthStart = $startDate->copy(); }
            if ($monthEnd->gt($endDate)) { $monthEnd = $endDate->copy(); }

            $rekapMonthly = app(\App\Http\Controllers\PaymentController::class)->getRekapitulasiData(
                null,
                $monthStart->toDateString(),
                $monthEnd->toDateString(),
                null,
                null,
                null
            );
            $sumMonthly = (float) ($rekapMonthly->sum('cash_amount') + $rekapMonthly->sum('transfer_amount') + $rekapMonthly->sum('gateway_amount'));
            $paymentsMonthly[$monthIndex] = $sumMonthly;

            $cursor->addMonth();
            $monthIndex++;
            if ($cursor->gt($endDate)) { break; }
        }

        // Leaderboard
        $classRanking = DB::table('students as s')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->where('s.student_status', 1)
            ->selectRaw('c.class_name, COUNT(s.student_id) as total_siswa')
            ->groupBy('c.class_name')
            ->orderByDesc('total_siswa')
            ->limit(7)
            ->get();
        $rankingLabels = $classRanking->pluck('class_name');
        $rankingData = $classRanking->pluck('total_siswa');

        // Activity logs (semua aktivitas user dan siswa)
        if (DB::getSchemaBuilder()->hasTable('activity_logs')) {
            $activityLogs = DB::table('activity_logs')
                ->select('time as waktu', DB::raw('COALESCE(message, CONCAT(context, ": ", action)) as deskripsi'))
                ->orderByDesc('time')
                ->limit(10)
                ->get();
        } else {
            $activityLogs = collect();
        }
        
        // Statistik jenis pembayaran
        $bulananPayments = DB::table('bulan')->count();
        $bebasPayments = DB::table('bebas_pay')->count();
        $onlinePayments = DB::table('transfer')->where('payment_method', '!=', 'cash')->count();
        $cashPayments = DB::table('transfer')->where('payment_method', 'cash')->count() + DB::table('bulan')->count() + DB::table('bebas_pay')->count();
        
        // Count untuk footer kartu: jumlah transaksi pembayaran (cash log_trx + transfer) bulan berjalan (MTD)
        $cashCountMonth = DB::table('log_trx as lt')
            ->whereBetween('lt.log_trx_input_date', [$startOfMonth, $endOfNow])
            ->whereNotExists(function($q){
                $q->select(DB::raw(1))
                  ->from('transfer_detail as td')
                  ->join('transfer as t','td.transfer_id','=','t.transfer_id')
                  ->whereRaw('(td.bulan_id = lt.bulan_bulan_id OR td.bebas_id = lt.bebas_pay_bebas_pay_id)')
                  ->whereIn('t.status',[1,2]);
            })
            ->count();
        $transferCountMonth = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.status', 1)
            ->whereBetween('t.updated_at', [$startOfMonth, $endOfNow])
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->count();
        $todayPaymentsCount = ($cashCountMonth + $transferCountMonth) ?? 0;
        // Sinkronkan footer count dengan rekap MTD (jumlah baris transaksi rekap)
        $todayPaymentsCount = (int) $rekapMonth->count();

        $todayReceiptsCount = DB::table('transaksi_penerimaan')->whereDate('tanggal_penerimaan', today())->where('status', 'confirmed')->count() ?? 0;
        $todayExpensesCount = DB::table('transaksi_pengeluaran')->whereDate('tanggal_pengeluaran', today())->where('status', 'confirmed')->count() ?? 0;
        $todaySavingsCount = DB::table('tabungan')->whereDate('tabungan_input_date', today())->count() ?? 0;

        // ========== DATA TAMBAHAN UNTUK DASHBOARD MODERN ==========
        
        // Total pembayaran tahun ini
        $totalPaymentsThisYear = $yearPayments;
        
        // Pertumbuhan siswa (perbandingan bulan ini vs bulan lalu)
        $lastMonthStudents = DB::table('students')
            ->where('student_status', 1)
            ->whereDate('student_input_date', '<=', now()->subMonth()->endOfMonth())
            ->count();
        $currentMonthStudents = $totalStudents;
        $studentGrowthPercent = $lastMonthStudents > 0 
            ? round((($currentMonthStudents - $lastMonthStudents) / $lastMonthStudents) * 100, 1)
            : 0;
        
        // Transaksi hari ini (count)
        $todayTransactions = $todayPaymentsCount;
        
        // Hitung pertumbuhan transaksi hari ini vs kemarin - filter by school_id
        $yesterdayTransactions = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('t.status', 1)
            ->whereDate('t.updated_at', now()->subDay())
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->count();
        
        $transactionGrowthPercent = $yesterdayTransactions > 0 
            ? round((($todayTransactions - $yesterdayTransactions) / $yesterdayTransactions) * 100, 1)
            : ($todayTransactions > 0 ? 100 : 0);
        
        // Hitung jumlah transaksi bulan ini
        $startOfMonth = now()->startOfMonth();
        $endOfMonth = now()->endOfMonth();
        $monthPaymentsCount = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.status', 1)
            ->whereBetween('t.updated_at', [$startOfMonth, $endOfMonth])
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->count();
        
        // Hitung pertumbuhan pembayaran bulan ini vs bulan lalu
        $lastMonthStart = now()->subMonth()->startOfMonth();
        $lastMonthEnd = now()->subMonth()->endOfMonth();
        $lastMonthPayments = DB::table('transfer as t')
            ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
            ->where('t.status', 1)
            ->whereBetween('t.updated_at', [$lastMonthStart, $lastMonthEnd])
            ->where(function($q){
                $q->whereNull('t.bill_type')->orWhere('t.bill_type', '!=', 'tabungan');
            })
            ->sum(DB::raw('COALESCE(td.subtotal,0)'));
        
        $monthPaymentsGrowth = $lastMonthPayments > 0 
            ? round((($monthPayments - $lastMonthPayments) / $lastMonthPayments) * 100, 1)
            : ($monthPayments > 0 ? 100 : 0);
        
        // Hitung Total Tunggakan Siswa (siswa dengan tagihan belum lunas)
        $unpaidBills = DB::table('bebas')
            ->whereRaw('bebas_total_pay < bebas_bill')
            ->select(
                DB::raw('SUM(bebas_bill - bebas_total_pay) as total_arrears'),
                DB::raw('COUNT(DISTINCT student_student_id) as unpaid_count')
            )
            ->first();
        
        $totalArrears = $unpaidBills ? (float) $unpaidBills->total_arrears : 0;
        $unpaidStudentsCount = $unpaidBills ? (int) $unpaidBills->unpaid_count : 0;
        
        // Target prosentase pembayaran bulan ini (termasuk bulanan dan bebas)
        $currentMonth = now()->month;
        $currentYear = now()->year;
        
        // Hitung total tagihan bulan ini (bulanan + bebas)
        // Bulanan: tagihan yang dibuat bulan ini (berdasarkan bulan_input_date)
        $totalBulananThisMonth = DB::table('bulan as b')
            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('s.student_status', 1)
            ->whereYear('b.bulan_input_date', $currentYear)
            ->whereMonth('b.bulan_input_date', $currentMonth)
            ->count();
        
        // Bebas: tagihan yang dibuat bulan ini
        $totalBebasThisMonth = DB::table('bebas as be')
            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('s.student_status', 1)
            ->whereYear('be.bebas_input_date', $currentYear)
            ->whereMonth('be.bebas_input_date', $currentMonth)
            ->count();
            
        $totalBillsThisMonth = $totalBulananThisMonth + $totalBebasThisMonth;
        
        // Hitung tagihan yang sudah lunas bulan ini
        // Bulanan: yang sudah ada tanggal bayar dan dibuat bulan ini
        $paidBulananThisMonth = DB::table('bulan as b')
            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('s.student_status', 1)
            ->whereNotNull('b.bulan_date_pay')
            ->whereYear('b.bulan_input_date', $currentYear)
            ->whereMonth('b.bulan_input_date', $currentMonth)
            ->count();
        
        // Bebas: yang sudah lunas (total_pay >= bill) dan dibuat bulan ini
        $paidBebasThisMonth = DB::table('bebas as be')
            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
            ->where('s.school_id', $currentSchoolId)
            ->where('s.student_status', 1)
            ->whereYear('be.bebas_input_date', $currentYear)
            ->whereMonth('be.bebas_input_date', $currentMonth)
            ->whereRaw('be.bebas_total_pay >= be.bebas_bill')
            ->count();
        
        $paidBillsThisMonth = $paidBulananThisMonth + $paidBebasThisMonth;
            
        $paymentCompletionPercent = $totalBillsThisMonth > 0 
            ? round(($paidBillsThisMonth / $totalBillsThisMonth) * 100, 1)
            : 0;
        
        // Data Countdown Berlangganan (gunakan inheritance dari superadmin)
        $currentDate = now();
        $user = auth()->user();
        
        // Gunakan getCheckUserId untuk inheritance (sama seperti sistem addon)
        $checkUserId = ($user->role === 'superadmin') ? $user->id : getSuperadminId();
        
        // Ambil subscription aktif terbaru dari user yang di-check (superadmin untuk inheritance)
        $activeSubscription = null;
        if ($checkUserId) {
        $activeSubscription = DB::table('subscriptions')
                ->where('user_id', $checkUserId)
            ->where('status', 'active')
            ->whereNotNull('expires_at')
            ->orderBy('expires_at', 'desc')
            ->first();
        }
        
        if ($activeSubscription) {
            $expiresAt = \Carbon\Carbon::parse($activeSubscription->expires_at);
            $subscriptionDaysLeft = $currentDate->diffInDays($expiresAt, false); // false = bisa negatif jika sudah expired
            $subscriptionDaysLeft = $subscriptionDaysLeft > 0 ? floor($subscriptionDaysLeft) : 0; // Pembulatan ke bawah, tanpa desimal
            $subscriptionExpiresAt = $expiresAt->format('d M Y');
        } else {
            $subscriptionDaysLeft = 0;
            $subscriptionExpiresAt = '-';
        }
        
        // Total berlangganan aktif dari tabel subscriptions (untuk statistik)
        $totalActiveSubscriptions = DB::table('subscriptions')
            ->where('status', 'active')
            ->count();
            
        // Berlangganan yang expired (expires_at sudah lewat tapi status masih active)
        $expiredSubscriptions = DB::table('subscriptions')
            ->where('status', 'active')
            ->where('expires_at', '<', $currentDate)
            ->count();
            
        // Hitung persentase expired
        $expiredPercentage = $totalActiveSubscriptions > 0 
            ? round(($expiredSubscriptions / $totalActiveSubscriptions) * 100, 1)
            : 0;
            
        // Data untuk grafik
        $totalArrears = $expiredSubscriptions; // Jumlah expired
        $arrearsCount = $totalActiveSubscriptions; // Total berlangganan
        
        // Log untuk debug
        \Log::info('Subscription Stats', [
            'user_id' => $user->id,
            'check_user_id' => $checkUserId,
            'days_left' => $subscriptionDaysLeft,
            'expires_at' => $subscriptionExpiresAt,
            'total_active' => $totalActiveSubscriptions,
            'expired' => $expiredSubscriptions
        ]);
        
        // Class labels dan data untuk doughnut chart
        $classLabels = $labels;
        $classData = $data;
        
        // Transaksi per bulan (count) untuk bar chart
        $transactionsMonthly = [];
        for ($i = 1; $i <= 12; $i++) {
            $count = (int) DB::table('transfer')
                ->where('status', 1)
                ->whereYear('updated_at', now()->year)
                ->whereMonth('updated_at', $i)
                ->count();
            $transactionsMonthly[] = $count;
        }
        
        // Pastikan paymentsMonthly adalah array dengan 12 elemen
        if (!isset($paymentsMonthly) || count($paymentsMonthly) !== 12) {
            $paymentsMonthly = array_fill(0, 12, 0);
        }
        
        // Pastikan receiptsMonthly dan expensesMonthly juga 12 elemen
        if (!isset($receiptsMonthly) || count($receiptsMonthly) !== 12) {
            $receiptsMonthly = array_fill(0, 12, 0);
        }
        if (!isset($expensesMonthly) || count($expensesMonthly) !== 12) {
            $expensesMonthly = array_fill(0, 12, 0);
        }
        
        // Persentase pembayaran per kelas
        $paymentProgressByClass = [];
        foreach ($kelas as $class) {
            $totalStudentsInClass = $class->students()->where('student_status', 1)->count();
            
            if ($totalStudentsInClass > 0) {
                // Hitung siswa yang sudah lunas
                $paidStudents = DB::table('students as s')
                    ->join('bebas as b', 's.student_id', '=', 'b.student_student_id')
                    ->where('s.class_class_id', $class->class_id)
                    ->where('s.student_status', 1)
                    ->whereRaw('b.bebas_total_pay >= b.bebas_bill')
                    ->distinct('s.student_id')
                    ->count('s.student_id');
                
                $unpaidStudents = $totalStudentsInClass - $paidStudents;
                $percentage = round(($paidStudents / $totalStudentsInClass) * 100, 1);
                
                $paymentProgressByClass[] = [
                    'class_name' => $class->class_name,
                    'total_students' => $totalStudentsInClass,
                    'paid_students' => $paidStudents,
                    'unpaid_students' => $unpaidStudents,
                    'percentage' => $percentage
                ];
            }
        }
        
        // Urutkan berdasarkan persentase tertinggi
        usort($paymentProgressByClass, function($a, $b) {
            return $b['percentage'] <=> $a['percentage'];
        });
        
        // Top Rank Pembayaran User berdasarkan jumlah transaksi (max 5)
        $topPaymentUsers = DB::table('students as s')
            ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->leftJoin('transfer as t', 's.student_id', '=', 't.student_id')
            ->where('s.student_status', 1)
            ->where('t.status', 1)
            ->select(
                's.student_id',
                's.student_full_name as name',
                'c.class_name as class',
                DB::raw('COUNT(t.transfer_id) as transaction_count')
            )
            ->groupBy('s.student_id', 's.student_full_name', 'c.class_name')
            ->having('transaction_count', '>', 0)
            ->orderByDesc('transaction_count')
            ->limit(5)
            ->get()
            ->map(function($user) {
                return [
                    'name' => $user->name,
                    'class' => $user->class,
                    'transaction_count' => (int) $user->transaction_count
                ];
            })
            ->toArray();

        return view('dashboard', compact(
            'labels','data',
            'totalPayments','successPayments','pendingPayments','failedPayments',
            'todayPayments','monthPayments','yearPayments',
            'todayReceipts','monthReceipts','yearReceipts',
            'todayExpenses','monthExpenses','yearExpenses',
            'todaySavings','monthSavings','yearSavings',
            'totalStudents','totalClasses','totalPos',
            'monthLabels','receiptsMonthly','expensesMonthly','paymentsMonthly',
            'bulananPayments','bebasPayments','onlinePayments','cashPayments',
            'todayPaymentsCount','todayReceiptsCount','todayExpensesCount','todaySavingsCount',
            'periods','selectedPeriodId',
            'totalPaymentsThisYear','studentGrowthPercent','todayTransactions','transactionGrowthPercent',
            'monthPaymentsCount','monthPaymentsGrowth','totalArrears','unpaidStudentsCount',
            'paymentCompletionPercent','arrearsCount','expiredPercentage',
            'subscriptionDaysLeft','subscriptionExpiresAt',
            'classLabels','classData','transactionsMonthly','paymentProgressByClass',
            'rankingLabels','rankingData','activityLogs','topPaymentUsers',
            'currentSchool','activePeriodId',
            'siswaAktifReguler','siswaAktifPindahan','siswaAktifMasukKembali',
            'jumlahKelasTA','totalAlumni','totalSiswaKeluar','selectedPeriod'
        ));
    }
} 