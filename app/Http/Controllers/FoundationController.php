<?php

namespace App\Http\Controllers;

use App\Models\Foundation;
use App\Models\School;
use App\Models\Student;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class FoundationController extends Controller
{
    /**
     * Dashboard Yayasan
     */
    public function dashboard(Request $request)
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        
        if (!$foundationId) {
            return redirect()->route('manage.general.setting')
                ->with('error', 'Belum ada yayasan. Silakan setup yayasan terlebih dahulu.');
        }

        $foundation = Foundation::with(['schools'])->findOrFail($foundationId);
        // Ambil semua sekolah dari foundation, tidak filter berdasarkan status
        // Semua sekolah yang dibuat di yayasan otomatis aktif
        $schools = $foundation->schools()->get();

        // Get tahun ajaran aktif
        if ($request->has('period_id')) {
            session(['selected_period_id' => $request->period_id]);
        }
        
        $selectedPeriod = session('selected_period_id') 
            ? \App\Models\Period::find(session('selected_period_id'))
            : \App\Models\Period::where('period_status', 1)->first();
        
        // Pastikan periodStart dan periodEnd selalu integer dan terdefinisi
        $currentYear = (int)date('Y');
        
        // Inisialisasi dengan nilai default
        $periodStart = $currentYear;
        $periodEnd = $currentYear + 1;
        
        // Update jika selectedPeriod ada dan valid
        if ($selectedPeriod) {
            if (isset($selectedPeriod->period_start) && $selectedPeriod->period_start) {
                $periodStart = (int)$selectedPeriod->period_start;
            }
            if (isset($selectedPeriod->period_end) && $selectedPeriod->period_end) {
                $periodEnd = (int)$selectedPeriod->period_end;
            }
        }

        // Debug: Cek siswa yang belum punya school_id
        $studentsWithoutSchool = DB::table('students')
            ->whereNull('school_id')
            ->count();
        
        // Jika ada siswa tanpa school_id, assign berdasarkan kelas mereka
        if ($studentsWithoutSchool > 0) {
            // Update siswa yang punya kelas: assign school_id dari kelas mereka
            DB::table('students as s')
                ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->whereNull('s.school_id')
                ->whereNotNull('s.class_class_id')
                ->whereNotNull('c.school_id')
                ->update([
                    's.school_id' => DB::raw('c.school_id')
                ]);
            
            $updatedByClass = DB::table('students')
                ->whereNull('school_id')
                ->count();
            
            // Siswa yang tidak punya kelas atau kelasnya tidak punya school_id
            // Assign ke sekolah pertama di foundation sebagai fallback
            if ($updatedByClass > 0 && count($schools) > 0) {
                $defaultSchoolId = $schools->first()->id;
                $remaining = DB::table('students')
                    ->whereNull('school_id')
                    ->update(['school_id' => $defaultSchoolId]);
                
                \Log::info("Updated {$remaining} siswa tanpa school_id (tidak punya kelas atau kelas tidak punya school_id) ke sekolah ID: {$defaultSchoolId}");
            }
            
            \Log::info("Auto-assigned {$studentsWithoutSchool} siswa tanpa school_id berdasarkan kelas mereka");
        }
        
        // Tentukan rentang tanggal untuk tahun ajaran
        $lastPeriodStart = Carbon::create($periodStart - 1, 7, 1)->startOfDay();
        $lastPeriodEnd = Carbon::create($periodStart, 6, 30)->endOfDay();
        $thisPeriodStart = Carbon::create($periodStart, 7, 1)->startOfDay();
        $thisPeriodEnd = Carbon::create($periodEnd, 6, 30)->endOfDay();
        
        // Get period IDs untuk filter
        $schoolIds = $foundation->schools()->pluck('id')->toArray();
        
        $lastPeriodIds = DB::table('periods')
            ->where('period_start', $periodStart - 1)
            ->whereIn('school_id', $schoolIds)
            ->pluck('period_id');
        
        $thisPeriodIds = DB::table('periods')
            ->where(function($q) use ($periodStart, $periodEnd) {
                $q->where('period_start', $periodStart)
                  ->orWhere(function($q2) use ($periodStart, $periodEnd) {
                      $q2->whereBetween('period_start', [$periodStart, $periodEnd])
                         ->whereBetween('period_end', [$periodStart, $periodEnd]);
                  });
            })
            ->whereIn('school_id', $schoolIds)
            ->pluck('period_id');
        
        // Statistik per sekolah
        $schoolStats = [];
        foreach ($schools as $school) {
            // Pemasukan T.A. Lalu - dari log_trx (tunai) dan transfer_detail (transfer)
            // Tunai Bulanan
            $tunaiBulananLastYear = DB::table('log_trx as lt')
                ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereNotNull('lt.bulan_bulan_id')
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$lastPeriodStart->format('Y-m-d'), $lastPeriodEnd->format('Y-m-d')])
                ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Tunai Bebas
            $tunaiBebasLastYear = DB::table('log_trx as lt')
                ->join('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereNotNull('lt.bebas_pay_bebas_pay_id')
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$lastPeriodStart->format('Y-m-d'), $lastPeriodEnd->format('Y-m-d')])
                ->sum(DB::raw('CAST(bp.bebas_pay_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Transfer
            $transferLastYear = DB::table('transfer_detail as td')
                ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->where('s.school_id', $school->id)
                ->where('t.status', 1)
                ->where(function($q) use ($lastPeriodIds) {
                    if ($lastPeriodIds->count() > 0) {
                        $q->whereIn('p_bulan.period_period_id', $lastPeriodIds)
                          ->orWhereIn('p_bebas.period_period_id', $lastPeriodIds);
                    }
                })
                ->whereBetween(DB::raw('DATE(t.updated_at)'), [$lastPeriodStart->format('Y-m-d'), $lastPeriodEnd->format('Y-m-d')])
                ->sum(DB::raw('COALESCE(CAST(td.subtotal AS DECIMAL(10,2)), 0)')) ?? 0;
            
            $incomeLastYear = $tunaiBulananLastYear + $tunaiBebasLastYear + $transferLastYear;

            // Pemasukan T.A. Ini
            // Tunai Bulanan
            $tunaiBulananThisYear = DB::table('log_trx as lt')
                ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereNotNull('lt.bulan_bulan_id')
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$thisPeriodStart->format('Y-m-d'), $thisPeriodEnd->format('Y-m-d')])
                ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Tunai Bebas
            $tunaiBebasThisYear = DB::table('log_trx as lt')
                ->join('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereNotNull('lt.bebas_pay_bebas_pay_id')
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$thisPeriodStart->format('Y-m-d'), $thisPeriodEnd->format('Y-m-d')])
                ->sum(DB::raw('CAST(bp.bebas_pay_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Transfer
            $transferThisYear = DB::table('transfer_detail as td')
                ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->where('s.school_id', $school->id)
                ->where('t.status', 1)
                ->where(function($q) use ($thisPeriodIds) {
                    if ($thisPeriodIds->count() > 0) {
                        $q->whereIn('p_bulan.period_period_id', $thisPeriodIds)
                          ->orWhereIn('p_bebas.period_period_id', $thisPeriodIds);
                    }
                })
                ->whereBetween(DB::raw('DATE(t.updated_at)'), [$thisPeriodStart->format('Y-m-d'), $thisPeriodEnd->format('Y-m-d')])
                ->sum(DB::raw('COALESCE(CAST(td.subtotal AS DECIMAL(10,2)), 0)')) ?? 0;
            
            $incomeThisYear = $tunaiBulananThisYear + $tunaiBebasThisYear + $transferThisYear;

            // Tunggakan T.A. Lalu - Bulanan (yang belum dibayar)
            $arrearsBulananLastYear = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereNull('b.bulan_date_pay')
                ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0')
                ->where(function($q) use ($lastPeriodIds, $periodStart) {
                    if ($lastPeriodIds->count() > 0) {
                        $q->whereIn('p.period_period_id', $lastPeriodIds);
                    } else {
                        $q->whereRaw('YEAR(p.payment_input_date) = ?', [$periodStart - 1])
                          ->orWhereRaw('YEAR(p.payment_input_date) = ? AND MONTH(p.payment_input_date) < 7', [$periodStart]);
                    }
                })
                ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Tunggakan T.A. Lalu - Bebas (selisih bill - total_pay)
            $arrearsBebasLastYear = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereRaw('CAST(be.bebas_total_pay AS DECIMAL(10,2)) < CAST(be.bebas_bill AS DECIMAL(10,2))')
                ->where(function($q) use ($lastPeriodIds, $periodStart) {
                    if ($lastPeriodIds->count() > 0) {
                        $q->whereIn('p.period_period_id', $lastPeriodIds);
                    } else {
                        $q->whereRaw('YEAR(p.payment_input_date) = ?', [$periodStart - 1])
                          ->orWhereRaw('YEAR(p.payment_input_date) = ? AND MONTH(p.payment_input_date) < 7', [$periodStart]);
                    }
                })
                ->sum(DB::raw('CAST(be.bebas_bill AS DECIMAL(10,2)) - CAST(be.bebas_total_pay AS DECIMAL(10,2))')) ?? 0;
            
            $arrearsLastYear = $arrearsBulananLastYear + $arrearsBebasLastYear;
            
            // Tunggakan T.A. Ini - Bulanan (yang belum dibayar)
            $arrearsBulananThisYear = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereNull('b.bulan_date_pay')
                ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0')
                ->where(function($q) use ($thisPeriodIds, $periodStart) {
                    if ($thisPeriodIds->count() > 0) {
                        $q->whereIn('p.period_period_id', $thisPeriodIds);
                    } else {
                        $q->whereRaw('YEAR(p.payment_input_date) = ? AND MONTH(p.payment_input_date) >= 7', [$periodStart])
                          ->orWhereRaw('YEAR(p.payment_input_date) = ?', [$periodStart + 1]);
                    }
                })
                ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Tunggakan T.A. Ini - Bebas (selisih bill - total_pay)
            $arrearsBebasThisYear = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->where('s.school_id', $school->id)
                ->whereRaw('CAST(be.bebas_total_pay AS DECIMAL(10,2)) < CAST(be.bebas_bill AS DECIMAL(10,2))')
                ->where(function($q) use ($thisPeriodIds, $periodStart) {
                    if ($thisPeriodIds->count() > 0) {
                        $q->whereIn('p.period_period_id', $thisPeriodIds);
                    } else {
                        $q->whereRaw('YEAR(p.payment_input_date) = ? AND MONTH(p.payment_input_date) >= 7', [$periodStart])
                          ->orWhereRaw('YEAR(p.payment_input_date) = ?', [$periodStart + 1]);
                    }
                })
                ->sum(DB::raw('CAST(be.bebas_bill AS DECIMAL(10,2)) - CAST(be.bebas_total_pay AS DECIMAL(10,2))')) ?? 0;
            
            $arrearsThisYear = $arrearsBulananThisYear + $arrearsBebasThisYear;

            // Pemasukan Harian (7 hari terakhir) - dari log_trx dan transfer_detail
            $dailyIncome = [];
            for ($i = 0; $i < 7; $i++) {
                $date = now()->subDays($i);
                $dateStr = $date->format('Y-m-d');
                
                // Tunai dari log_trx
                $tunaiDaily = DB::table('log_trx as lt')
                    ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                    ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                    ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                    ->where('s.school_id', $school->id)
                    ->whereDate('lt.log_trx_input_date', $dateStr)
                    ->sum(DB::raw('COALESCE(CAST(b.bulan_bill AS DECIMAL(10,2)), 0) + COALESCE(CAST(bp.bebas_pay_bill AS DECIMAL(10,2)), 0)')) ?? 0;
                
                // Transfer dari transfer_detail
                $transferDaily = DB::table('transfer_detail as td')
                    ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                    ->join('students as s', 't.student_id', '=', 's.student_id')
                    ->where('s.school_id', $school->id)
                    ->where('t.status', 1)
                    ->whereDate('t.updated_at', $dateStr)
                    ->sum(DB::raw('COALESCE(CAST(td.subtotal AS DECIMAL(10,2)), 0)')) ?? 0;
                
                $dailyIncome[$date->format('d/m')] = $tunaiDaily + $transferDaily;
            }

            // Statistik Siswa Aktif
            // Query yang lebih jelas: hitung siswa dengan status = 1 (aktif)
            $totalStudents = DB::table('students')
                ->where('school_id', $school->id)
                ->where('student_status', 1)
                ->count();
            
            // Debug log untuk troubleshooting
            \Log::debug("Sekolah: {$school->nama_sekolah} (ID: {$school->id})", [
                'total_students' => $totalStudents,
                'total_all_students' => DB::table('students')->where('school_id', $school->id)->count(),
                'students_with_status_1' => DB::table('students')
                    ->where('school_id', $school->id)
                    ->where('student_status', 1)
                    ->count(),
                'students_with_status_0' => DB::table('students')
                    ->where('school_id', $school->id)
                    ->where('student_status', 0)
                    ->count(),
                'students_with_null_status' => DB::table('students')
                    ->where('school_id', $school->id)
                    ->whereNull('student_status')
                    ->count(),
            ]);

            $schoolStats[] = [
                'school' => $school,
                'income_last_year' => $incomeLastYear ?? 0,
                'income_this_year' => $incomeThisYear ?? 0,
                'arrears_last_year' => $arrearsLastYear ?? 0,
                'arrears_this_year' => $arrearsThisYear ?? 0,
                'daily_income' => $dailyIncome,
                'total_students' => $totalStudents ?? 0,
            ];
        }

        // Total semua pemasukan - Hari Ini
        $schoolIds = $schools->pluck('id')->toArray();
        $todayStr = today()->format('Y-m-d');
        
        // Tunai hari ini
        $tunaiToday = DB::table('log_trx as lt')
            ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->whereDate('lt.log_trx_input_date', $todayStr)
            ->sum(DB::raw('COALESCE(CAST(b.bulan_bill AS DECIMAL(10,2)), 0) + COALESCE(CAST(bp.bebas_pay_bill AS DECIMAL(10,2)), 0)')) ?? 0;
        
        // Transfer hari ini
        $transferToday = DB::table('transfer_detail as td')
            ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->where('t.status', 1)
            ->whereDate('t.updated_at', $todayStr)
            ->sum(DB::raw('COALESCE(CAST(td.subtotal AS DECIMAL(10,2)), 0)')) ?? 0;
        
        $totalIncomeToday = $tunaiToday + $transferToday;

        // Total semua pemasukan - Bulan Ini
        $monthStart = now()->startOfMonth()->format('Y-m-d');
        $monthEnd = now()->endOfMonth()->format('Y-m-d');
        
        // Tunai bulan ini
        $tunaiMonth = DB::table('log_trx as lt')
            ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$monthStart, $monthEnd])
            ->sum(DB::raw('COALESCE(CAST(b.bulan_bill AS DECIMAL(10,2)), 0) + COALESCE(CAST(bp.bebas_pay_bill AS DECIMAL(10,2)), 0)')) ?? 0;
        
        // Transfer bulan ini
        $transferMonth = DB::table('transfer_detail as td')
            ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->where('t.status', 1)
            ->whereBetween(DB::raw('DATE(t.updated_at)'), [$monthStart, $monthEnd])
            ->sum(DB::raw('COALESCE(CAST(td.subtotal AS DECIMAL(10,2)), 0)')) ?? 0;
        
        $totalIncomeMonth = $tunaiMonth + $transferMonth;

        // Total semua pemasukan - Semua Waktu
        // Tunai semua
        $tunaiAll = DB::table('log_trx as lt')
            ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
            ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
            ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->sum(DB::raw('COALESCE(CAST(b.bulan_bill AS DECIMAL(10,2)), 0) + COALESCE(CAST(bp.bebas_pay_bill AS DECIMAL(10,2)), 0)')) ?? 0;
        
        // Transfer semua
        $transferAll = DB::table('transfer_detail as td')
            ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
            ->join('students as s', 't.student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->where('t.status', 1)
            ->sum(DB::raw('COALESCE(CAST(td.subtotal AS DECIMAL(10,2)), 0)')) ?? 0;
        
        $totalIncomeAll = $tunaiAll + $transferAll;

        $totalArrears = collect($schoolStats)->sum(function($stat) {
            return $stat['arrears_last_year'] + $stat['arrears_this_year'];
        });

        // Rekap statistik siswa per sekolah
        $studentStats = collect($schoolStats)->map(function($stat) {
            return [
                'school_name' => $stat['school']->nama_sekolah,
                'total_students' => $stat['total_students'] ?? 0,
            ];
        })->filter(function($stat) {
            // Tampilkan semua sekolah, termasuk yang tidak ada siswa aktif
            return true;
        });

        $totalActiveStudents = collect($schoolStats)->sum(function($stat) {
            return $stat['total_students'] ?? 0;
        });

        return view('foundation.dashboard', compact(
            'foundation',
            'schools',
            'schoolStats',
            'totalIncomeToday',
            'totalIncomeMonth',
            'totalIncomeAll',
            'totalArrears',
            'studentStats',
            'totalActiveStudents',
            'selectedPeriod'
        ));
    }

    /**
     * Set foundation context
     */
    public function setFoundation(Request $request, $foundationId)
    {
        $foundation = Foundation::findOrFail($foundationId);
        
        session(['foundation_id' => $foundation->id]);
        
            return redirect()->route('manage.foundation.dashboard')
                ->with('success', 'Yayasan berhasil dipilih.');
    }

    /**
     * Laporan Pemasukan
     */
    public function laporanPemasukan(Request $request)
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        
        if (!$foundationId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Belum ada yayasan. Silakan setup yayasan terlebih dahulu.');
        }

        $foundation = Foundation::with('schools')->findOrFail($foundationId);
        // Ambil semua sekolah dari foundation, tidak filter berdasarkan status
        $schools = $foundation->schools()->orderBy('nama_sekolah')->get();
        
        // Get selected school
        $selectedSchoolId = $request->get('school_id', session('current_school_id'));
        
        // Get time range filter
        $timeRange = $request->get('time_range', '7days');
        
        // Calculate date range based on time filter
        $endDate = now();
        switch ($timeRange) {
            case 'today':
                $startDate = now()->startOfDay();
                break;
            case '7days':
                $startDate = now()->subDays(7)->startOfDay();
                break;
            case 'month':
                $startDate = now()->startOfMonth();
                break;
            case '6months':
                $startDate = now()->subMonths(6)->startOfMonth();
                break;
            case 'year':
                $startDate = now()->startOfYear();
                break;
            default:
                $startDate = now()->subDays(7)->startOfDay();
        }
        
        // Get all payment positions
        $posList = DB::table('pos_pembayaran')
            ->whereNotNull('pos_name')
            ->orderBy('pos_name')
            ->get();
        
        $recapData = collect();
        
        foreach ($posList as $pos) {
            // Get cash transactions (tunai) from log_trx - Bulanan
            $tunaiBulanan = DB::table('log_trx as lt')
                ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->whereNotNull('lt.bulan_bulan_id')
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            if ($selectedSchoolId) {
                $tunaiBulanan->where('s.school_id', $selectedSchoolId);
            }
            
            $tunaiBulananAmount = $tunaiBulanan->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Get cash transactions (tunai) from log_trx - Bebas
            $tunaiBebas = DB::table('log_trx as lt')
                ->join('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->whereNotNull('lt.bebas_pay_bebas_pay_id')
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            
            if ($selectedSchoolId) {
                $tunaiBebas->where('s.school_id', $selectedSchoolId);
            }
            
            $tunaiBebasAmount = $tunaiBebas->sum(DB::raw('CAST(bp.bebas_pay_bill AS DECIMAL(10,2))')) ?? 0;
            
            $tunai = $tunaiBulananAmount + $tunaiBebasAmount;
            $jumlahTunai = $tunaiBulanan->count() + $tunaiBebas->count();
            
            // Get transfer transactions from transfer_detail
            // Transfer detail tidak punya pos_id langsung, harus melalui bulan/bebas -> payment -> pos
            $transferQuery = DB::table('transfer_detail as td')
                ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->leftJoin('bulan as b', function($join) {
                    $join->on('td.bulan_id', '=', 'b.bulan_id')
                         ->where('td.payment_type', '=', 1);
                })
                ->leftJoin('bebas as be', function($join) {
                    $join->on('td.bebas_id', '=', 'be.bebas_id')
                         ->where('td.payment_type', '=', 2);
                })
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->where(function($q) use ($pos) {
                    $q->where('p_bulan.pos_pos_id', $pos->pos_id)
                      ->orWhere('p_bebas.pos_pos_id', $pos->pos_id);
                })
                ->where('t.status', 1)
                ->whereBetween(DB::raw('DATE(t.updated_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->whereIn('td.payment_type', [1, 2]); // Hanya bulanan dan bebas, exclude tabungan
            
            if ($selectedSchoolId) {
                $transferQuery->where('s.school_id', $selectedSchoolId);
            }
            
            $transfer = $transferQuery->sum('td.subtotal') ?? 0;
            $jumlahTransfer = $transferQuery->count();
            
            // Only add to recap if there's data
            if ($tunai > 0 || $transfer > 0) {
                $recapData->push((object)[
                    'pos_id' => $pos->pos_id,
                    'pos_name' => $pos->pos_name,
                    'tunai' => $tunai,
                    'transfer' => $transfer,
                    'jumlah_transaksi' => $jumlahTunai + $jumlahTransfer,
                    'pembayaran_historis' => 0,
                    'jumlah_total' => $tunai + $transfer
                ]);
            }
        }
        
        return view('foundation.laporan.pemasukan', compact(
            'foundation',
            'schools',
            'selectedSchoolId',
            'timeRange',
            'startDate',
            'endDate',
            'recapData'
        ));
    }

    /**
     * Laporan Tunggakan
     */
    public function laporanTunggakan(Request $request)
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        
        if (!$foundationId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Belum ada yayasan. Silakan setup yayasan terlebih dahulu.');
        }

        $foundation = Foundation::with('schools')->findOrFail($foundationId);
        // Ambil semua sekolah dari foundation, tidak filter berdasarkan status
        $schools = $foundation->schools()->orderBy('nama_sekolah')->get();
        
        // Get filters
        $selectedSchoolId = $request->get('school_id', session('current_school_id'));
        $selectedClassId = $request->get('class_id');
        $studentStatus = $request->get('student_status', 1); // Default: aktif
        
        // Get classes based on selected school
        $classes = collect();
        if ($selectedSchoolId) {
            $classes = DB::table('class_models')
                ->where('school_id', $selectedSchoolId)
                ->orderBy('class_name')
                ->get();
        } else {
            // If no school selected, get all classes from foundation schools
            $schoolIds = $schools->pluck('id');
            $classes = DB::table('class_models')
                ->whereIn('school_id', $schoolIds)
                ->orderBy('class_name')
                ->get();
        }
        
        // Calculate total tunggakan kumulatif (all years) from all schools in foundation
        $schoolIds = $selectedSchoolId ? [$selectedSchoolId] : $schools->pluck('id')->toArray();
        
        // Get bulanan arrears
        $bulananTunggakan = DB::table('bulan as b')
            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->whereNull('b.bulan_date_pay')
            ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0');
        
        // Get bebas arrears
        $bebasTunggakan = DB::table('bebas as be')
            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
            ->whereIn('s.school_id', $schoolIds)
            ->whereRaw('CAST(be.bebas_total_pay AS DECIMAL(10,2)) < CAST(be.bebas_bill AS DECIMAL(10,2))');
        
        // Apply filters
        if ($selectedClassId) {
            $bulananTunggakan->where('s.class_class_id', $selectedClassId);
            $bebasTunggakan->where('s.class_class_id', $selectedClassId);
        }
        
        if ($studentStatus !== null) {
            $bulananTunggakan->where('s.student_status', $studentStatus);
            $bebasTunggakan->where('s.student_status', $studentStatus);
        }
        
        $totalBulanan = $bulananTunggakan->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
        $totalBebas = $bebasTunggakan->sum(DB::raw('CAST(be.bebas_bill AS DECIMAL(10,2)) - CAST(be.bebas_total_pay AS DECIMAL(10,2))')) ?? 0;
        $totalTunggakanKumulatif = $totalBulanan + $totalBebas;
        
        // Get all students with arrears
        $studentsWithArrears = DB::table('students as s')
            ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->whereIn('s.school_id', $schoolIds)
            ->where('s.student_status', $studentStatus);
        
        if ($selectedClassId) {
            $studentsWithArrears->where('c.class_id', $selectedClassId);
        }
        
        $students = $studentsWithArrears->select('s.student_id', 's.student_full_name', 's.student_nis', 's.student_status', 'c.class_id', 'c.class_name')
            ->orderBy('c.class_name')
            ->orderBy('s.student_full_name')
            ->get();
        
        // Calculate tunggakan per student
        $tunggakanData = collect();
        foreach ($students as $student) {
            // Get bulanan arrears for this student
            $bulanan = DB::table('bulan as b')
                ->where('b.student_student_id', $student->student_id)
                ->whereNull('b.bulan_date_pay')
                ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0')
                ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Get bebas arrears for this student
            $bebas = DB::table('bebas as be')
                ->where('be.student_student_id', $student->student_id)
                ->whereRaw('CAST(be.bebas_total_pay AS DECIMAL(10,2)) < CAST(be.bebas_bill AS DECIMAL(10,2))')
                ->sum(DB::raw('CAST(be.bebas_bill AS DECIMAL(10,2)) - CAST(be.bebas_total_pay AS DECIMAL(10,2))')) ?? 0;
            
            $totalTunggakan = $bulanan + $bebas;
            
            if ($totalTunggakan > 0) {
                $tunggakanData->push((object)[
                    'student_id' => $student->student_id,
                    'student_full_name' => $student->student_full_name,
                    'student_nis' => $student->student_nis,
                    'student_status' => $student->student_status,
                    'class_id' => $student->class_id,
                    'class_name' => $student->class_name,
                    'total_tunggakan' => $totalTunggakan
                ]);
            }
        }
        
        // Group by class
        $tunggakanByClass = $tunggakanData->groupBy('class_name');
        
        return view('foundation.laporan.tunggakan', compact(
            'foundation',
            'schools',
            'classes',
            'selectedSchoolId',
            'selectedClassId',
            'studentStatus',
            'totalTunggakanKumulatif',
            'tunggakanByClass',
            'tunggakanData'
        ));
    }

    /**
     * Laporan Jenis Biaya
     */
    public function laporanJenisBiaya(Request $request)
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        
        if (!$foundationId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Belum ada yayasan. Silakan setup yayasan terlebih dahulu.');
        }

        $foundation = Foundation::with('schools')->findOrFail($foundationId);
        // Ambil semua sekolah dari foundation, tidak filter berdasarkan status
        $schools = $foundation->schools()->orderBy('nama_sekolah')->get();
        
        // Get filters
        $selectedSchoolId = $request->get('school_id', session('current_school_id'));
        $selectedPeriodId = $request->get('period_id');
        $rangeMode = $request->get('range_mode', 'per_bulan'); // per_bulan, per_tahun
        $selectedMonth = $request->get('month'); // Format: YYYY-MM atau month_id
        
        // Get school IDs untuk filter
        $schoolIds = $selectedSchoolId ? [$selectedSchoolId] : $schools->pluck('id')->toArray();
        
        // Get periods - filter berdasarkan school_id dari foundation schools
        // Juga include periods yang school_id NULL untuk backward compatibility
        $periods = DB::table('periods')
            ->where(function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds)
                  ->orWhereNull('school_id'); // Backward compatibility untuk data lama
            })
            ->orderBy('period_start', 'desc')
            ->get();
        
        // Get active period if not selected - filter berdasarkan school_id
        if (!$selectedPeriodId) {
            // Prioritas 1: Cari periode aktif dari sekolah yang dipilih
            $activePeriod = DB::table('periods')
                ->where(function($q) use ($schoolIds) {
                    $q->whereIn('school_id', $schoolIds)
                      ->orWhereNull('school_id'); // Backward compatibility
                })
                ->where('period_status', 1)
                ->orderBy('period_start', 'desc')
                ->first();
                
            // Jika tidak ada, ambil periode pertama yang ada
            $selectedPeriodId = $activePeriod ? $activePeriod->period_id : ($periods->first() ? $periods->first()->period_id : null);
        }
        
        if (!$selectedPeriodId) {
            return redirect()->route('manage.foundation.laporan.jenis-biaya')
                ->with('error', 'Belum ada tahun ajaran. Silakan setup tahun ajaran terlebih dahulu.');
        }
        
        $selectedPeriod = DB::table('periods')->where('period_id', $selectedPeriodId)->first();
        
        // Parse month filter
        $monthId = null;
        $monthYear = null;
        if ($selectedMonth && $rangeMode == 'per_bulan') {
            // Parse month (could be "YYYY-MM" or month_id)
            if (preg_match('/^(\d{4})-(\d{2})$/', $selectedMonth, $matches)) {
                $monthYear = $selectedMonth;
            } else {
                $monthId = (int)$selectedMonth;
            }
        }
        
        // Calculate date range based on period and month
        // Default: gunakan seluruh tahun ajaran jika mode per_tahun atau belum ada bulan
        $startDate = $selectedPeriod ? Carbon::createFromFormat('Y', $selectedPeriod->period_start)->startOfYear() : now()->startOfYear();
        $endDate = $selectedPeriod ? Carbon::createFromFormat('Y', $selectedPeriod->period_end)->endOfYear() : now()->endOfYear();
        
        // Jika mode per_bulan dan ada bulan yang dipilih, gunakan rentang bulan
        if ($rangeMode == 'per_bulan') {
            if ($monthYear) {
                // Bulan sudah dipilih, gunakan rentang bulan tersebut
                $monthCarbon = Carbon::createFromFormat('Y-m', $monthYear);
                $startDate = $monthCarbon->copy()->startOfMonth();
                $endDate = $monthCarbon->copy()->endOfMonth();
            } elseif ($monthId) {
                // Bulan dipilih dengan month_id, konversi ke tahun ajaran yang sesuai
                // Bulan 1-6 = semester 2 (Januari-Juni), Bulan 7-12 = semester 1 (Juli-Desember)
                if ($selectedPeriod) {
                    $periodStartYear = (int)$selectedPeriod->period_start;
                    $periodEndYear = (int)$selectedPeriod->period_end;
                    
                    if ($monthId >= 7 && $monthId <= 12) {
                        // Semester 1: Juli-Desember (tahun periode_start)
                        $monthNum = $monthId - 6; // 7->1(Jul), 8->2(Aug), ..., 12->6(Dec)
                        $startDate = Carbon::create($periodStartYear, $monthId, 1)->startOfMonth();
                        $endDate = Carbon::create($periodStartYear, $monthId, 1)->endOfMonth();
                    } else {
                        // Semester 2: Januari-Juni (tahun periode_end)
                        $startDate = Carbon::create($periodEndYear, $monthId, 1)->startOfMonth();
                        $endDate = Carbon::create($periodEndYear, $monthId, 1)->endOfMonth();
                    }
                }
            }
            // Jika mode per_bulan tapi belum ada bulan dipilih, tetap gunakan seluruh tahun ajaran
            // Data akan tetap muncul, tapi akan menunjukkan semua bulan
        }
        
        // Get all POS - filter berdasarkan school_id dari foundation schools
        // Juga include POS yang school_id NULL untuk backward compatibility
        $posList = DB::table('pos_pembayaran')
            ->where(function($q) use ($schoolIds) {
                $q->whereIn('school_id', $schoolIds)
                  ->orWhereNull('school_id'); // Backward compatibility untuk data lama
            })
            ->whereNotNull('pos_name')
            ->orderBy('pos_name')
            ->get();
        
        // Separate SPP and other costs
        $sppPos = $posList->filter(function($pos) {
            return stripos($pos->pos_name, 'SPP') !== false;
        });
        $otherPos = $posList->reject(function($pos) {
            return stripos($pos->pos_name, 'SPP') !== false;
        });
        
        // Calculate Grand Totals
        $grandTotalDitagih = 0;
        $grandTotalTerbayar = 0;
        $grandTotalTunggakan = 0;
        
        // Get summary data per POS
        $summaryData = collect();
        $sppBulanData = collect();
        $otherCostsData = collect();
        
        foreach ($posList as $pos) {
            // Calculate Ditagih (billed) - from bulan and bebas
            $ditagihBulanan = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->where('p.period_period_id', $selectedPeriodId)
                ->where(function($q) use ($schoolIds) {
                    $q->whereIn('p.school_id', $schoolIds)
                      ->orWhereNull('p.school_id'); // Backward compatibility
                })
                ->whereIn('s.school_id', $schoolIds);
            
            // Filter bulan hanya jika mode per_bulan DAN ada bulan yang dipilih
            if ($rangeMode == 'per_bulan') {
                if ($monthId) {
                    $ditagihBulanan->where('b.month_month_id', $monthId);
                } elseif ($monthYear) {
                    // Filter by month if range mode is per_bulan
                    $monthCarbon = Carbon::createFromFormat('Y-m', $monthYear);
                    $ditagihBulanan->whereMonth('b.bulan_input_date', $monthCarbon->month)
                                  ->whereYear('b.bulan_input_date', $monthCarbon->year);
                }
                // Jika mode per_bulan tapi belum pilih bulan, tidak ada filter bulan (tampilkan semua bulan)
            }
            
            $ditagihBulananAmount = $ditagihBulanan->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            $ditagihBebas = DB::table('bebas as be')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->where('p.period_period_id', $selectedPeriodId)
                ->where(function($q) use ($schoolIds) {
                    $q->whereIn('p.school_id', $schoolIds)
                      ->orWhereNull('p.school_id'); // Backward compatibility
                })
                ->whereIn('s.school_id', $schoolIds);
            
            // Filter bulan hanya jika mode per_bulan DAN ada bulan yang dipilih
            if ($rangeMode == 'per_bulan' && $monthYear) {
                $monthCarbon = Carbon::createFromFormat('Y-m', $monthYear);
                $ditagihBebas->whereMonth('be.bebas_input_date', $monthCarbon->month)
                            ->whereYear('be.bebas_input_date', $monthCarbon->year);
            }
            // Jika mode per_tahun atau belum pilih bulan, tidak ada filter bulan (tampilkan semua)
            
            $ditagihBebasAmount = $ditagihBebas->sum(DB::raw('CAST(be.bebas_bill AS DECIMAL(10,2))')) ?? 0;
            $totalDitagih = $ditagihBulananAmount + $ditagihBebasAmount;
            
            // Calculate Terbayar (paid) - from log_trx and transfer
            // Bulanan
            $terbayarTunaiBulanan = DB::table('log_trx as lt')
                ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->where('p.period_period_id', $selectedPeriodId)
                ->where(function($q) use ($schoolIds) {
                    $q->whereIn('p.school_id', $schoolIds)
                      ->orWhereNull('p.school_id'); // Backward compatibility
                })
                ->whereIn('s.school_id', $schoolIds)
                ->whereNotNull('lt.bulan_bulan_id');
            
            // Filter tanggal: jika mode per_bulan dan ada bulan dipilih, filter bulan. Jika tidak, filter tahun ajaran.
            if ($rangeMode == 'per_bulan' && ($monthId || $monthYear)) {
                $terbayarTunaiBulanan->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            } else {
                // Mode per_tahun atau belum pilih bulan, filter berdasarkan tahun ajaran (Juli-Juni)
                $periodStartDate = $selectedPeriod ? Carbon::create((int)$selectedPeriod->period_start, 7, 1)->startOfDay() : $startDate;
                $periodEndDate = $selectedPeriod ? Carbon::create((int)$selectedPeriod->period_end, 6, 30)->endOfDay() : $endDate;
                $terbayarTunaiBulanan->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$periodStartDate->format('Y-m-d'), $periodEndDate->format('Y-m-d')]);
            }
            
            $terbayarTunaiBulananAmount = $terbayarTunaiBulanan->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
            
            // Bebas
            $terbayarTunaiBebas = DB::table('log_trx as lt')
                ->join('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->where('p.period_period_id', $selectedPeriodId)
                ->where(function($q) use ($schoolIds) {
                    $q->whereIn('p.school_id', $schoolIds)
                      ->orWhereNull('p.school_id'); // Backward compatibility
                })
                ->whereIn('s.school_id', $schoolIds)
                ->whereNotNull('lt.bebas_pay_bebas_pay_id');
            
            // Filter tanggal: jika mode per_bulan dan ada bulan dipilih, filter bulan. Jika tidak, filter tahun ajaran.
            if ($rangeMode == 'per_bulan' && ($monthId || $monthYear)) {
                $terbayarTunaiBebas->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            } else {
                // Mode per_tahun atau belum pilih bulan, filter berdasarkan tahun ajaran (Juli-Juni)
                $periodStartDate = $selectedPeriod ? Carbon::create((int)$selectedPeriod->period_start, 7, 1)->startOfDay() : $startDate;
                $periodEndDate = $selectedPeriod ? Carbon::create((int)$selectedPeriod->period_end, 6, 30)->endOfDay() : $endDate;
                $terbayarTunaiBebas->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$periodStartDate->format('Y-m-d'), $periodEndDate->format('Y-m-d')]);
            }
            
            $terbayarTunaiBebasAmount = $terbayarTunaiBebas->sum(DB::raw('CAST(bp.bebas_pay_bill AS DECIMAL(10,2))')) ?? 0;
            
            $terbayarTunai = $terbayarTunaiBulananAmount + $terbayarTunaiBebasAmount;
            
            $terbayarTransfer = DB::table('transfer_detail as td')
                ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                ->join('students as s', 't.student_id', '=', 's.student_id')
                ->leftJoin('bulan as b', function($join) {
                    $join->on('td.bulan_id', '=', 'b.bulan_id')
                         ->where('td.payment_type', '=', 1);
                })
                ->leftJoin('bebas as be', function($join) {
                    $join->on('td.bebas_id', '=', 'be.bebas_id')
                         ->where('td.payment_type', '=', 2);
                })
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->where(function($q) use ($pos) {
                    $q->where('p_bulan.pos_pos_id', $pos->pos_id)
                      ->orWhere('p_bebas.pos_pos_id', $pos->pos_id);
                })
                ->where('t.status', 1)
                ->where(function($q) use ($selectedPeriodId) {
                    $q->where('p_bulan.period_period_id', $selectedPeriodId)
                      ->orWhere('p_bebas.period_period_id', $selectedPeriodId);
                })
                ->where(function($q) use ($schoolIds) {
                    $q->where(function($q2) use ($schoolIds) {
                        $q2->whereIn('p_bulan.school_id', $schoolIds)
                           ->orWhereNull('p_bulan.school_id');
                    })
                    ->orWhere(function($q3) use ($schoolIds) {
                        $q3->whereIn('p_bebas.school_id', $schoolIds)
                           ->orWhereNull('p_bebas.school_id');
                    });
                })
                ->whereIn('s.school_id', $schoolIds);
            
            // Filter tanggal: jika mode per_bulan dan ada bulan dipilih, filter bulan. Jika tidak, filter tahun ajaran.
            if ($rangeMode == 'per_bulan' && ($monthId || $monthYear)) {
                $terbayarTransfer->whereBetween(DB::raw('DATE(t.updated_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            } else {
                // Mode per_tahun atau belum pilih bulan, filter berdasarkan tahun ajaran (Juli-Juni)
                $periodStartDate = $selectedPeriod ? Carbon::create((int)$selectedPeriod->period_start, 7, 1)->startOfDay() : $startDate;
                $periodEndDate = $selectedPeriod ? Carbon::create((int)$selectedPeriod->period_end, 6, 30)->endOfDay() : $endDate;
                $terbayarTransfer->whereBetween(DB::raw('DATE(t.updated_at)'), [$periodStartDate->format('Y-m-d'), $periodEndDate->format('Y-m-d')]);
            }
            
            $terbayarTransferAmount = $terbayarTransfer->whereIn('td.payment_type', [1, 2])
                ->sum('td.subtotal') ?? 0;
            
            $totalTerbayar = $terbayarTunai + $terbayarTransferAmount;
            $totalTunggakan = $totalDitagih - $totalTerbayar;
            
            // Count students
            $studentsDitagih = DB::table('bulan as b')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->where('p.pos_pos_id', $pos->pos_id)
                ->where('p.period_period_id', $selectedPeriodId)
                ->where(function($q) use ($schoolIds) {
                    $q->whereIn('p.school_id', $schoolIds)
                      ->orWhereNull('p.school_id'); // Backward compatibility
                })
                ->whereIn('s.school_id', $schoolIds)
                ->distinct('s.student_id')
                ->count('s.student_id');
            
            $studentsTerbayar = DB::table('log_trx as lt')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->leftJoin('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->where(function($q) use ($pos) {
                    $q->where('p_bulan.pos_pos_id', $pos->pos_id)
                      ->orWhere('p_bebas.pos_pos_id', $pos->pos_id);
                })
                ->where(function($q) use ($selectedPeriodId) {
                    $q->where('p_bulan.period_period_id', $selectedPeriodId)
                      ->orWhere('p_bebas.period_period_id', $selectedPeriodId);
                })
                ->where(function($q) use ($schoolIds) {
                    $q->where(function($q2) use ($schoolIds) {
                        $q2->whereIn('p_bulan.school_id', $schoolIds)
                           ->orWhereNull('p_bulan.school_id');
                    })
                    ->orWhere(function($q3) use ($schoolIds) {
                        $q3->whereIn('p_bebas.school_id', $schoolIds)
                           ->orWhereNull('p_bebas.school_id');
                    });
                })
                ->whereIn('s.school_id', $schoolIds)
                ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->distinct('s.student_id')
                ->count('s.student_id');
            
            $studentsBelum = $studentsDitagih - $studentsTerbayar;
            
            $grandTotalDitagih += $totalDitagih;
            $grandTotalTerbayar += $totalTerbayar;
            $grandTotalTunggakan += $totalTunggakan;
            
            // Check if this is SPP
            $isSPP = stripos($pos->pos_name, 'SPP') !== false;
            
            if ($isSPP && $rangeMode == 'per_bulan') {
                // Get SPP data per month
                $months = [1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
                          5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari', 
                          9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'];
                
                foreach ($months as $monthNum => $monthName) {
                    $monthDitagih = DB::table('bulan as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('p.period_period_id', $selectedPeriodId)
                        ->where('b.month_month_id', $monthNum)
                        ->where(function($q) use ($schoolIds) {
                            $q->whereIn('p.school_id', $schoolIds)
                              ->orWhereNull('p.school_id'); // Backward compatibility
                        })
                        ->whereIn('s.school_id', $schoolIds)
                        ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
                    
                    // Calculate paid for this month - tunai
                    $monthTerbayarTunai = DB::table('log_trx as lt')
                        ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('p.period_period_id', $selectedPeriodId)
                        ->where('b.month_month_id', $monthNum)
                        ->where(function($q) use ($schoolIds) {
                            $q->whereIn('p.school_id', $schoolIds)
                              ->orWhereNull('p.school_id'); // Backward compatibility
                        })
                        ->whereIn('s.school_id', $schoolIds)
                        ->whereNotNull('lt.bulan_bulan_id')
                        ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
                    
                    // Calculate paid for this month - transfer
                    $monthTerbayarTransfer = DB::table('transfer_detail as td')
                        ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                        ->join('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('students as s', 't.student_id', '=', 's.student_id')
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('p.period_period_id', $selectedPeriodId)
                        ->where('b.month_month_id', $monthNum)
                        ->where(function($q) use ($schoolIds) {
                            $q->whereIn('p.school_id', $schoolIds)
                              ->orWhereNull('p.school_id'); // Backward compatibility
                        })
                        ->whereIn('s.school_id', $schoolIds)
                        ->where('t.status', 1)
                        ->where('td.payment_type', 1)
                        ->whereBetween(DB::raw('DATE(t.updated_at)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->sum('td.subtotal') ?? 0;
                    
                    $monthTerbayar = $monthTerbayarTunai + $monthTerbayarTransfer;
                    $monthTunggakan = $monthDitagih - $monthTerbayar;
                    
                    // Count students who paid
                    $monthStudentsBayar = DB::table('log_trx as lt')
                        ->join('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('p.period_period_id', $selectedPeriodId)
                        ->where('b.month_month_id', $monthNum)
                        ->where(function($q) use ($schoolIds) {
                            $q->whereIn('p.school_id', $schoolIds)
                              ->orWhereNull('p.school_id'); // Backward compatibility
                        })
                        ->whereIn('s.school_id', $schoolIds)
                        ->whereNotNull('lt.bulan_bulan_id')
                        ->whereBetween(DB::raw('DATE(lt.log_trx_input_date)'), [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                        ->distinct('s.student_id')
                        ->count('s.student_id');
                    
                    // Count students who haven't paid
                    $monthStudentsBelum = DB::table('bulan as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                        ->leftJoin('log_trx as lt', function($join) {
                            $join->on('lt.bulan_bulan_id', '=', 'b.bulan_id')
                                 ->whereNotNull('lt.bulan_bulan_id');
                        })
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('p.period_period_id', $selectedPeriodId)
                        ->where('b.month_month_id', $monthNum)
                        ->where(function($q) use ($schoolIds) {
                            $q->whereIn('p.school_id', $schoolIds)
                              ->orWhereNull('p.school_id'); // Backward compatibility
                        })
                        ->whereIn('s.school_id', $schoolIds)
                        ->whereNull('b.bulan_date_pay')
                        ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0')
                        ->distinct('s.student_id')
                        ->count('s.student_id');
                    
                    if ($monthDitagih > 0 || $monthTerbayar > 0) {
                        $sppBulanData->push((object)[
                            'bulan' => $monthName . ' ' . ($selectedPeriod ? $selectedPeriod->period_start : date('Y')),
                            'month_id' => $monthNum,
                            'ditagih' => $monthDitagih,
                            'terbayar' => $monthTerbayar,
                            'sisa' => $monthTunggakan,
                            'siswa_bayar' => $monthStudentsBayar,
                            'siswa_belum' => $monthStudentsBelum,
                            'pos_id' => $pos->pos_id
                        ]);
                    }
                }
            }
            
            // Always process other costs (non-SPP) regardless of range mode
            if (!$isSPP) {
                $otherCostsData->push((object)[
                    'pos_id' => $pos->pos_id,
                    'jenis_biaya' => $pos->pos_name,
                    'ditagih' => $totalDitagih,
                    'terbayar' => $totalTerbayar,
                    'sisa' => $totalTunggakan,
                    'siswa_bayar' => $studentsTerbayar,
                    'siswa_belum' => $studentsBelum
                ]);
            }
        }
        
        // Get 5 largest arrears (other costs)
        $top5Arrears = $otherCostsData->sortByDesc('sisa')->take(5);
        
        // Get 5 largest delinquent students
        $top5DelinquentStudents = DB::table('students as s')
            ->leftJoin('class_models as c', 's.class_class_id', '=', 'c.class_id')
            ->leftJoin('bulan as b', function($join) {
                $join->on('b.student_student_id', '=', 's.student_id')
                     ->whereNull('b.bulan_date_pay')
                     ->whereRaw('CAST(b.bulan_bill AS DECIMAL(10,2)) > 0');
            })
            ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
            ->leftJoin('bebas as be', function($join) {
                $join->on('be.student_student_id', '=', 's.student_id')
                     ->whereRaw('CAST(be.bebas_total_pay AS DECIMAL(10,2)) < CAST(be.bebas_bill AS DECIMAL(10,2))');
            })
            ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
            ->whereIn('s.school_id', $schoolIds)
            ->where(function($q) use ($selectedPeriodId) {
                $q->where('p_bulan.period_period_id', $selectedPeriodId)
                  ->orWhere('p_bebas.period_period_id', $selectedPeriodId);
            })
            ->where(function($q) use ($schoolIds) {
                $q->where(function($q2) use ($schoolIds) {
                    $q2->whereIn('p_bulan.school_id', $schoolIds)
                       ->orWhereNull('p_bulan.school_id'); // Backward compatibility
                })
                ->orWhere(function($q3) use ($schoolIds) {
                    $q3->whereIn('p_bebas.school_id', $schoolIds)
                       ->orWhereNull('p_bebas.school_id'); // Backward compatibility
                });
            })
            ->groupBy('s.student_id', 's.student_full_name', 's.student_nis', 'c.class_name')
            ->select(
                's.student_id',
                's.student_full_name',
                's.student_nis',
                'c.class_name',
                DB::raw('SUM(COALESCE(CAST(b.bulan_bill AS DECIMAL(10,2)), 0)) + SUM(COALESCE(CAST(be.bebas_bill AS DECIMAL(10,2)) - CAST(be.bebas_total_pay AS DECIMAL(10,2)), 0)) as total_tunggakan')
            )
            ->havingRaw('total_tunggakan > 0')
            ->orderByDesc('total_tunggakan')
            ->limit(5)
            ->get();
        
        // Calculate payment percentage
        $paymentPercentage = $grandTotalDitagih > 0 ? ($grandTotalTerbayar / $grandTotalDitagih) * 100 : 0;
        
        return view('foundation.laporan.jenis-biaya', compact(
            'foundation',
            'schools',
            'periods',
            'selectedSchoolId',
            'selectedPeriodId',
            'selectedPeriod',
            'rangeMode',
            'selectedMonth',
            'monthId',
            'monthYear',
            'startDate',
            'endDate',
            'grandTotalDitagih',
            'grandTotalTerbayar',
            'grandTotalTunggakan',
            'paymentPercentage',
            'sppBulanData',
            'otherCostsData',
            'top5Arrears',
            'top5DelinquentStudents'
        ));
    }

    /**
     * Laporan Umum - Generate Laporan Keuangan
     */
    public function laporanUmum(Request $request)
    {
        $foundationId = session('foundation_id') ?? Foundation::first()?->id;
        
        if (!$foundationId) {
            return redirect()->route('manage.foundation.dashboard')
                ->with('error', 'Belum ada yayasan. Silakan setup yayasan terlebih dahulu.');
        }

        $foundation = Foundation::with('schools')->findOrFail($foundationId);
        // Ambil semua sekolah dari foundation, tidak filter berdasarkan status
        $schools = $foundation->schools()->orderBy('nama_sekolah')->get();
        
        // Get filters
        $selectedSchoolId = $request->get('school_id');
        $selectedClassId = $request->get('class_id');
        
        $classes = collect();
        if ($selectedSchoolId) {
            $classes = DB::table('class_models')
                ->where('school_id', $selectedSchoolId)
                ->orderBy('class_name')
                ->get();
        }
        
        // If both filters are selected, show preview
        $reportData = collect();
        $selectedSchool = null;
        $selectedClass = null;
        
        if ($selectedSchoolId && $selectedClassId) {
            $selectedSchool = School::find($selectedSchoolId);
            $selectedClass = DB::table('class_models')->where('class_id', $selectedClassId)->first();
            
            // Get students in the class
            $students = DB::table('students as s')
                ->where('s.class_class_id', $selectedClassId)
                ->where('s.school_id', $selectedSchoolId)
                ->orderBy('s.student_full_name')
                ->get();
            
            // Get active period
            $activePeriod = DB::table('periods')->where('period_status', 1)->first();
            $periodId = $activePeriod ? $activePeriod->period_id : null;
            
            foreach ($students as $student) {
                $studentReportData = collect();
                
                // Get all payment positions for this student - calculate from actual data
                $posData = DB::table('payment as p')
                    ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->when($periodId, function($q) use ($periodId) {
                        $q->where('p.period_period_id', $periodId);
                    })
                    ->where(function($q) use ($student) {
                        $q->whereExists(function($subQ) use ($student) {
                            $subQ->select(DB::raw(1))
                                 ->from('bulan as b')
                                 ->whereColumn('b.payment_payment_id', 'p.payment_id')
                                 ->where('b.student_student_id', $student->student_id);
                        })
                        ->orWhereExists(function($subQ) use ($student) {
                            $subQ->select(DB::raw(1))
                                 ->from('bebas as be')
                                 ->whereColumn('be.payment_payment_id', 'p.payment_id')
                                 ->where('be.student_student_id', $student->student_id);
                        });
                    })
                    ->select('pos.pos_id', 'pos.pos_name')
                    ->distinct()
                    ->get();
                
                $posList = collect();
                foreach ($posData as $pos) {
                    // Calculate bulanan
                    $bulananQuery = DB::table('bulan as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('b.student_student_id', $student->student_id);
                    
                    if ($periodId) {
                        $bulananQuery->where('p.period_period_id', $periodId);
                    }
                    
                    $bulananTagihan = $bulananQuery->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
                    $bulananDibayar = $bulananQuery->whereNotNull('b.bulan_date_pay')
                                                   ->sum(DB::raw('CAST(b.bulan_bill AS DECIMAL(10,2))')) ?? 0;
                    $bulananLastPayment = $bulananQuery->whereNotNull('b.bulan_date_pay')
                                                       ->max('b.bulan_date_pay');
                    
                    // Calculate bebas
                    $bebasQuery = DB::table('bebas as be')
                        ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('be.student_student_id', $student->student_id);
                    
                    if ($periodId) {
                        $bebasQuery->where('p.period_period_id', $periodId);
                    }
                    
                    $bebasTagihan = $bebasQuery->sum(DB::raw('CAST(be.bebas_bill AS DECIMAL(10,2))')) ?? 0;
                    $bebasDibayar = $bebasQuery->sum(DB::raw('LEAST(CAST(be.bebas_total_pay AS DECIMAL(10,2)), CAST(be.bebas_bill AS DECIMAL(10,2)))')) ?? 0;
                    $bebasLastPayment = $bebasQuery->whereNotNull('be.bebas_date_pay')
                                                    ->max('be.bebas_date_pay');
                    
                    $totalTagihan = $bulananTagihan + $bebasTagihan;
                    $totalDibayar = $bulananDibayar + $bebasDibayar;
                    $lastPaymentDate = $bulananLastPayment ?? $bebasLastPayment;
                    
                    if ($totalTagihan > 0) {
                        $posList->push((object)[
                            'pos_id' => $pos->pos_id,
                            'pos_name' => $pos->pos_name,
                            'total_tagihan' => $totalTagihan,
                            'total_dibayar' => $totalDibayar,
                            'last_payment_date' => $lastPaymentDate,
                            'last_bebas_payment_date' => $bebasLastPayment
                        ]);
                    }
                }
                
                $totalTagihanStudent = 0;
                $totalDibayarStudent = 0;
                $totalTunggakanStudent = 0;
                
                foreach ($posList as $pos) {
                    if ($pos->total_tagihan > 0) {
                        $tunggakan = $pos->total_tagihan - $pos->total_dibayar;
                        
                        // Get last payment info
                        $lastPayment = null;
                        $paymentDate = $pos->last_payment_date ?? $pos->last_bebas_payment_date;
                        if ($paymentDate) {
                            $lastPaymentDate = Carbon::parse($paymentDate);
                            $months = [1 => 'Jul', 2 => 'Agu', 3 => 'Sep', 4 => 'Okt', 5 => 'Nov', 6 => 'Des', 
                                      7 => 'Jan', 8 => 'Feb', 9 => 'Mar', 10 => 'Apr', 11 => 'Mei', 12 => 'Jun'];
                            $lastPayment = $months[$lastPaymentDate->month] . ' ' . $lastPaymentDate->year;
                        }
                        
                        $studentReportData->push([
                            'jenis_biaya' => $pos->pos_name,
                            'total_tagihan' => $pos->total_tagihan,
                            'total_dibayar' => $pos->total_dibayar,
                            'tunggakan' => $tunggakan,
                            'last_payment' => $lastPayment
                        ]);
                        
                        $totalTagihanStudent += $pos->total_tagihan;
                        $totalDibayarStudent += $pos->total_dibayar;
                        $totalTunggakanStudent += $tunggakan;
                    }
                }
                
                if ($studentReportData->count() > 0) {
                    $reportData->push([
                        'student_id' => $student->student_id,
                        'student_name' => $student->student_full_name,
                        'student_nis' => $student->student_nis,
                        'data' => $studentReportData,
                        'total_tagihan' => $totalTagihanStudent,
                        'total_dibayar' => $totalDibayarStudent,
                        'total_tunggakan' => $totalTunggakanStudent
                    ]);
                }
            }
        }
        
        return view('foundation.laporan.umum', compact(
            'foundation',
            'schools',
            'classes',
            'selectedSchoolId',
            'selectedClassId',
            'selectedSchool',
            'selectedClass',
            'reportData'
        ));
    }

}

