<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Period;
use App\Models\Pos;
use App\Models\Bebas;
use App\Models\Student;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SetupGateway;
use App\Models\School;
use App\Models\SchoolProfile; // @deprecated - kept for backward compatibility
use App\Models\ClassModel;

class PaymentController extends Controller
{
    public function index()
    {
        // Filter Payment berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $payments = Payment::where('school_id', $currentSchoolId)
            ->with(['period', 'pos'])
            ->orderBy('payment_id', 'desc')
            ->get();
        
        return view('payment.index', compact('payments'));
    }

    public function create()
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Filter periode berdasarkan sekolah yang sedang aktif
        $periods = Period::where('school_id', $currentSchoolId)
            ->orderBy('period_start')
            ->get();
        
        // Jika tidak ada periode, buat periode default untuk sekolah ini
        if ($periods->count() == 0) {
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;
            
            Period::create([
                'period_start' => $currentYear,
                'period_end' => $nextYear,
                'period_status' => 1,
                'school_id' => $currentSchoolId,
            ]);
            
            // Reload periods
            $periods = Period::where('school_id', $currentSchoolId)
                ->orderBy('period_start')
                ->get();
        }
        
        $posList = Pos::where('school_id', $currentSchoolId)
            ->orderBy('pos_name')
            ->get();
        
        return view('payment.create', compact('periods', 'posList'));
    }

    public function store(Request $request)
    {
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $request->validate([
            'payment_type' => 'required|in:BEBAS,BULAN',
            'period_period_id' => [
                'required',
                'exists:periods,period_id',
                function ($attribute, $value, $fail) use ($currentSchoolId) {
                    $period = Period::where('period_id', $value)
                        ->where('school_id', $currentSchoolId)
                        ->first();
                    if (!$period) {
                        $fail('Periode yang dipilih tidak tersedia di sekolah ini.');
                    }
                }
            ],
            'pos_pos_id' => [
                'required',
                'exists:pos_pembayaran,pos_id',
                function ($attribute, $value, $fail) use ($currentSchoolId) {
                    $pos = Pos::where('pos_id', $value)
                        ->where('school_id', $currentSchoolId)
                        ->first();
                    if (!$pos) {
                        $fail('POS yang dipilih tidak tersedia di sekolah ini.');
                    }
                }
            ],
        ]);
        
        Payment::create([
            'payment_type' => $request->payment_type,
            'is_for_spmb' => $request->has('is_for_spmb') && $request->is_for_spmb,
            'period_period_id' => $request->period_period_id,
            'pos_pos_id' => $request->pos_pos_id,
            'school_id' => $currentSchoolId,
            'payment_input_date' => now(),
            'payment_last_update' => now(),
        ]);
        
        return redirect()->route('payment.index')->with('success', 'Jenis Pembayaran berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $payment = Payment::where('school_id', $currentSchoolId)->findOrFail($id);
        
        // Filter periode berdasarkan sekolah yang sedang aktif
        $periods = Period::where('school_id', $currentSchoolId)
            ->orderBy('period_start')
            ->get();
        
        // Jika tidak ada periode, buat periode default untuk sekolah ini
        if ($periods->count() == 0) {
            $currentYear = date('Y');
            $nextYear = $currentYear + 1;
            
            Period::create([
                'period_start' => $currentYear,
                'period_end' => $nextYear,
                'period_status' => 1,
                'school_id' => $currentSchoolId,
            ]);
            
            // Reload periods
            $periods = Period::where('school_id', $currentSchoolId)
                ->orderBy('period_start')
                ->get();
        }
        
        $posList = Pos::where('school_id', $currentSchoolId)
            ->orderBy('pos_name')
            ->get();
        
        return view('payment.edit', compact('payment', 'periods', 'posList'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'payment_type' => 'required|in:BEBAS,BULAN',
            'period_period_id' => 'required|exists:periods,period_id',
            'pos_pos_id' => 'required|exists:pos_pembayaran,pos_id',
        ]);
        $payment = Payment::findOrFail($id);
        $payment->update([
            'payment_type' => $request->payment_type,
            'is_for_spmb' => $request->has('is_for_spmb') && $request->is_for_spmb,
            'period_period_id' => $request->period_period_id,
            'pos_pos_id' => $request->pos_pos_id,
            'payment_last_update' => now(),
        ]);
        return redirect()->route('payment.index')->with('success', 'Jenis Pembayaran berhasil diupdate!');
    }

    public function destroy($id)
    {
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return redirect()->route('payment.index')->with('success', 'Jenis Pembayaran berhasil dihapus!');
    }

    public function setting($id, Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        // Pastikan payment milik sekolah yang sedang aktif
        $payment = Payment::where('school_id', $currentSchoolId)
            ->with(['period', 'pos'])
            ->findOrFail($id);
        
        // Filter kelas berdasarkan sekolah yang sedang aktif - INI YANG PENTING!
        $classes = \App\Models\ClassModel::where('school_id', $currentSchoolId)
            ->orderBy('class_name')
            ->get();
        
        $selectedClass = $request->input('class_id');
        
        // Cek apakah ada tarif untuk payment dan kelas yang dipilih
        $hasTarif = false;
        $students = collect();
        
        if ($selectedClass) {
            // Pastikan kelas yang dipilih adalah milik sekolah yang sedang aktif
            $class = \App\Models\ClassModel::where('school_id', $currentSchoolId)
                ->findOrFail($selectedClass);
            
            // Ambil siswa dari kelas yang dipilih, filter berdasarkan sekolah yang sedang aktif
            $studentsQuery = Student::where('student_status', 1)
                                   ->where('class_class_id', $selectedClass)
                                   ->where('school_id', $currentSchoolId);
            $students = $studentsQuery->get();
            if ($students->count() > 0) {
                $studentIds = $students->pluck('student_id');
                if ($payment->payment_type == 'BULAN') {
                    $existingTarif = \DB::table('bulan')
                        ->where('payment_payment_id', $payment->payment_id)
                        ->whereIn('student_student_id', $studentIds)
                        ->count();
                    $hasTarif = $existingTarif > 0;
                } else {
                    $existingTarif = Bebas::where('payment_payment_id', $payment->payment_id)
                                         ->whereIn('student_student_id', $studentIds)
                                         ->count();
                    $hasTarif = $existingTarif > 0;
                }
            }
        }
        if ($payment->payment_type == 'BULAN') {
            return view('payment.setting-bulanan', compact('payment', 'classes', 'students', 'selectedClass', 'hasTarif'));
        } else {
            return view('payment.setting-bebas', compact('payment', 'classes', 'students', 'selectedClass', 'hasTarif'));
        }
    }

    public function storeTarifBebas(Request $request, $id)
    {
        try {
            \Log::info('storeTarifBebas called', ['request' => $request->all(), 'id' => $id]);
            
            // Handle JSON request
            if ($request->isJson()) {
                $data = $request->json()->all();
                $class_id = $data['class_id'];
                $tarif = $data['tarif'];
                $keterangan = $data['keterangan'] ?? null;
                \Log::info('JSON data received', ['data' => $data]);
            } else {
                $class_id = $request->input('class_id');
                $tarif = $request->input('tarif');
                $keterangan = $request->input('keterangan');
                \Log::info('Form data received', ['class_id' => $class_id, 'tarif' => $tarif, 'keterangan' => $keterangan]);
            }

            // Validate data
            $validator = \Validator::make([
                'class_id' => $class_id,
                'tarif' => $tarif,
                'keterangan' => $keterangan
            ], [
                'class_id' => 'required|exists:class_models,class_id',
                'tarif' => 'required|numeric|min:0',
                'keterangan' => 'nullable|string|max:500'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validasi gagal: ' . $validator->errors()->first()
                ], 422);
            }

        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sekolah belum dipilih.'
                ], 403);
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $payment = Payment::where('school_id', $currentSchoolId)->findOrFail($id);
        
        // Pastikan kelas milik sekolah yang sedang aktif
        $class = \App\Models\ClassModel::where('school_id', $currentSchoolId)
            ->findOrFail($class_id);
        
        // Ambil semua siswa dari kelas yang dipilih, filter berdasarkan sekolah yang sedang aktif
        $students = Student::where('class_class_id', $class_id)
                          ->where('student_status', 1) // hanya siswa aktif
                          ->where('school_id', $currentSchoolId) // Filter berdasarkan sekolah
                          ->get();

        $savedCount = 0;
        foreach ($students as $student) {
            // Cek apakah sudah ada tarif untuk siswa ini
            $existingBebas = Bebas::where('student_student_id', $student->student_id)
                                 ->where('payment_payment_id', $payment->payment_id)
                                 ->first();

            if (!$existingBebas) {
                // Buat tarif baru
                Bebas::create([
                    'student_student_id' => $student->student_id,
                    'payment_payment_id' => $payment->payment_id,
                    'bebas_bill' => $tarif,
                    'bebas_total_pay' => 0,
                    'bebas_desc' => $keterangan,
                    'bebas_input_date' => now(),
                    'bebas_last_update' => now()
                ]);
                $savedCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Berhasil menambahkan tarif untuk {$savedCount} siswa di kelas yang dipilih!",
            'saved_count' => $savedCount
        ]);
        
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateTarifBebas(Request $request, $id)
    {
        $bebas = \App\Models\Bebas::findOrFail($id);
        $request->validate([
            'bebas_bill' => 'required|numeric|min:0',
            'bebas_desc' => 'nullable|string|max:500'
        ]);
        $bebas->update([
            'bebas_bill' => $request->bebas_bill,
            'bebas_desc' => $request->bebas_desc,
            'bebas_last_update' => now()
        ]);
        return response()->json(['success' => true, 'message' => 'Tarif berhasil diupdate!']);
    }

    public function deleteTarifBebas($id)
    {
        $bebas = \App\Models\Bebas::findOrFail($id);
        // Cegah hapus jika sudah ada transaksi di tabel bebas_pay
        $hasTransactions = \DB::table('bebas_pay')->where('bebas_bebas_id', $bebas->bebas_id)->exists();
        if ($hasTransactions) {
            return response()->json([
                'success' => false,
                'message' => 'Tarif tidak dapat dihapus karena sudah ada transaksi pembayaran.',
            ], 422);
        }
        $bebas->delete();
        return response()->json(['success' => true, 'message' => 'Tarif berhasil dihapus!']);
    }

    public function bulkUpdateTarifBebas(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bebas,bebas_id',
            'bebas_bill' => 'required|numeric|min:0',
            'bebas_desc' => 'nullable|string|max:500'
        ]);
        $updated = 0;
        foreach ($request->ids as $id) {
            $bebas = \App\Models\Bebas::find($id);
            if ($bebas) {
                $bebas->update([
                    'bebas_bill' => $request->bebas_bill,
                    'bebas_desc' => $request->bebas_desc,
                    'bebas_last_update' => now()
                ]);
                $updated++;
            }
        }
        return response()->json(['success' => true, 'message' => "Berhasil update tarif untuk $updated siswa."]);
    }

    public function bulkDeleteTarifBebas(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:bebas,bebas_id',
        ]);
        $deleted = 0;
        $failed = [];
        foreach ($request->ids as $id) {
            $bebas = \App\Models\Bebas::find($id);
            if ($bebas) {
                // Cek transaksi di bebas_pay
                $hasTrans = \DB::table('bebas_pay')->where('bebas_bebas_id', $bebas->bebas_id)->exists();
                if ($hasTrans) {
                    $failed[] = $bebas->student_student_id;
                    continue;
                }
                $bebas->delete();
                $deleted++;
            }
        }
        $msg = "Berhasil menghapus $deleted tarif.";
        if (count($failed)) {
            $msg .= " Gagal hapus untuk siswa: " . implode(", ", $failed) . " karena sudah ada transaksi.";
        }
        return response()->json(['success' => true, 'message' => $msg, 'failed' => $failed]);
    }

    public function storeTarifBebasSiswa(Request $request, $id)
    {
        $request->validate([
            'kelas_id' => 'required|exists:class_models,class_id',
            'student_id' => 'required|exists:students,student_id',
            'tarif' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:500'
        ]);
        $payment = \App\Models\Payment::findOrFail($id);
        $studentId = $request->student_id;
        // Cek apakah sudah ada tarif untuk siswa ini
        $existing = \App\Models\Bebas::where('student_student_id', $studentId)
            ->where('payment_payment_id', $payment->payment_id)
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Tarif untuk siswa ini sudah ada!'], 422);
        }
        \App\Models\Bebas::create([
            'student_student_id' => $studentId,
            'payment_payment_id' => $payment->payment_id,
            'bebas_bill' => $request->tarif,
            'bebas_total_pay' => 0,
            'bebas_desc' => $request->keterangan,
            'bebas_input_date' => now(),
            'bebas_last_update' => now()
        ]);
        return response()->json(['success' => true, 'message' => 'Tarif berhasil disimpan untuk siswa!']);
    }

    public function storeTarifBulanan(Request $request, $id)
    {
        \Log::info('storeTarifBulanan: ' . json_encode($request->all()));
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            $user = auth()->user();
            if (!$currentSchoolId) {
                if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                    return redirect()->route('manage.foundation.dashboard')
                        ->with('error', 'Sekolah belum dipilih.');
                }
                abort(403, 'Akses ditolak: Sekolah belum dipilih.');
            }
            
            $request->validate([
                'class_id' => 'required|exists:class_models,class_id',
                'tarif_sama' => 'required|numeric|min:0',
                'bulan_tarif' => 'required|array',
            ]);
            
            $classId = $request->class_id;
            $tarifSama = $request->tarif_sama;
            $bulanTarif = $request->bulan_tarif;
            
            // Pastikan payment milik sekolah yang sedang aktif
            $payment = \App\Models\Payment::where('school_id', $currentSchoolId)->findOrFail($id);
            
            // Pastikan kelas milik sekolah yang sedang aktif
            $class = \App\Models\ClassModel::where('school_id', $currentSchoolId)
                ->findOrFail($classId);
            
            // Ambil siswa dari kelas yang dipilih, filter berdasarkan sekolah yang sedang aktif
            $students = \App\Models\Student::where('class_class_id', $classId)
                ->where('student_status', 1)
                ->where('school_id', $currentSchoolId)
                ->get();
            $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
            $inserted = 0;
            foreach ($students as $student) {
                foreach ($bulanList as $i => $bulan) {
                    $nominal = isset($bulanTarif[$bulan]) && $bulanTarif[$bulan] !== '' ? $bulanTarif[$bulan] : $tarifSama;
                    if ($nominal && $nominal > 0) {
                        $exists = \DB::table('bulan')->where([
                            'student_student_id' => $student->student_id,
                            'payment_payment_id' => $payment->payment_id,
                            'month_month_id' => $i+1
                        ])->exists();
                        if (!$exists) {
                            \DB::table('bulan')->insert([
                                'student_student_id' => $student->student_id,
                                'payment_payment_id' => $payment->payment_id,
                                'month_month_id' => $i+1,
                                'bulan_bill' => $nominal,
                                'bulan_status' => 1,
                                'bulan_input_date' => now(),
                                'bulan_last_update' => now()
                            ]);
                            $inserted++;
                        }
                    }
                }
            }
            return response()->json([
                'success' => true, 
                'message' => "Berhasil menyimpan tarif bulanan untuk $inserted data!",
                'inserted_count' => $inserted
            ]);
        } catch (\Exception $e) {
            \Log::error('Error simpan tarif bulanan: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTarifBulananSiswa($student_id, $payment_id)
    {
        $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
        $data = \DB::table('bulan')
            ->where('student_student_id', $student_id)
            ->where('payment_payment_id', $payment_id)
            ->get();
        $result = [];
        foreach ($bulanList as $idx => $bulan) {
            $row = $data->where('month_month_id', $idx+1)->first();
            $result[$bulan] = $row ? $row->bulan_bill : '';
        }
        return response()->json(['success' => true, 'bulan' => $result]);
    }

    public function updateTarifBulananSiswa(Request $request, $student_id, $payment_id)
    {
        $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
        $bulan = $request->input('bulan', []);
        foreach ($bulanList as $idx => $b) {
            $nominal = isset($bulan[$b]) && $bulan[$b] !== '' ? $bulan[$b] : null;
            if ($nominal !== null) {
                \DB::table('bulan')->updateOrInsert([
                    'student_student_id' => $student_id,
                    'payment_payment_id' => $payment_id,
                    'month_month_id' => $idx+1
                ], [
                    'bulan_bill' => $nominal,
                    'bulan_status' => 1,
                    'bulan_last_update' => now()
                ]);
            }
        }
        return response()->json(['success' => true, 'message' => 'Tarif bulanan berhasil diupdate!']);
    }

    public function deleteTarifBulananSiswa($student_id, $payment_id)
    {
        $adaTransaksi = \DB::table('bulan')
            ->where('student_student_id', $student_id)
            ->where('payment_payment_id', $payment_id)
            ->where(function($q) {
                $q->whereNotNull('bulan_date_pay')->orWhereNotNull('bulan_number_pay');
            })->exists();
        if ($adaTransaksi) {
            return response()->json(['success' => false, 'message' => 'Tidak bisa dihapus, sudah ada transaksi pembayaran!'], 422);
        }
        \DB::table('bulan')
            ->where('student_student_id', $student_id)
            ->where('payment_payment_id', $payment_id)
            ->delete();
        return response()->json(['success' => true, 'message' => 'Tarif bulanan berhasil dihapus!']);
    }

    public function bulkDeleteTarifBulanan(Request $request)
    {
        try {
            \Log::info('bulkDeleteTarifBulanan called with: ' . json_encode($request->all()));
            
        $request->validate([
            'student_ids' => 'required|array',
            'student_ids.*' => 'exists:students,student_id',
            'payment_id' => 'required|exists:payment,payment_id',
        ]);
            
        $deleted = 0;
        $failed = [];
            
            $studentIds = $request->input('student_ids', []);
            $paymentId = $request->input('payment_id');
            
            \Log::info('Processing student_ids: ' . json_encode($studentIds));
            \Log::info('Processing payment_id: ' . $paymentId);
            
            foreach ($studentIds as $student_id) {
                try {
                    \Log::info("Processing student_id: $student_id");
                    
            $adaTransaksi = \DB::table('bulan')
                ->where('student_student_id', $student_id)
                        ->where('payment_payment_id', $paymentId)
                ->where(function($q) {
                    $q->whereNotNull('bulan_date_pay')->orWhereNotNull('bulan_number_pay');
                })->exists();
                    
                    \Log::info("Student $student_id has transaction: " . ($adaTransaksi ? 'yes' : 'no'));
                    
            if ($adaTransaksi) {
                $failed[] = $student_id;
                        \Log::info("Student $student_id skipped due to existing transaction");
                continue;
            }
                    
            $count = \DB::table('bulan')
                ->where('student_student_id', $student_id)
                        ->where('payment_payment_id', $paymentId)
                ->delete();
                    
                    \Log::info("Deleted $count records for student $student_id");
            $deleted += $count;
                } catch (\Exception $e) {
                    \Log::error("Error deleting tarif bulanan for student $student_id: " . $e->getMessage());
                    $failed[] = $student_id;
        }
        }
            
        $msg = "Berhasil menghapus tarif bulanan untuk $deleted data.";
            if (count($failed) > 0) {
                $msg .= " Gagal hapus untuk " . count($failed) . " siswa karena sudah ada transaksi.";
            }
            
            $response = [
                'success' => true, 
                'message' => $msg, 
                'deleted_count' => $deleted,
                'failed_count' => count($failed)
            ];
            
            \Log::info('bulkDeleteTarifBulanan response: ' . json_encode($response));
            return response()->json($response);
            
        } catch (\Exception $e) {
            \Log::error('Error in bulkDeleteTarifBulanan: ' . $e->getMessage());
            return response()->json([
                'success' => false, 
                'message' => 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeTarifBulananSiswa(Request $request, $payment_id)
    {
        $request->validate([
            'kelas_id' => 'required|exists:class_models,class_id',
            'student_id' => 'required|exists:students,student_id',
            'bulan' => 'required|array',
        ]);
        $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
        $studentId = $request->student_id;
        $kelasId = $request->kelas_id;
        $bulan = $request->bulan;
        foreach ($bulanList as $idx => $b) {
            $nominal = isset($bulan[$b]) && $bulan[$b] !== '' ? $bulan[$b] : null;
            if ($nominal !== null) {
                \DB::table('bulan')->updateOrInsert([
                    'student_student_id' => $studentId,
                    'payment_payment_id' => $payment_id,
                    'month_month_id' => $idx+1
                ], [
                    'bulan_bill' => $nominal,
                    'bulan_status' => 1,
                    'bulan_last_update' => now()
                ]);
            }
        }
        return response()->json(['success' => true, 'message' => 'Tarif bulanan siswa berhasil disimpan!']);
    }

    public function updateTarifBulananMasal(Request $request, $payment_id)
    {
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            $user = auth()->user();
            if (!$currentSchoolId) {
                if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                    return redirect()->route('manage.foundation.dashboard')
                        ->with('error', 'Sekolah belum dipilih.');
                }
                abort(403, 'Akses ditolak: Sekolah belum dipilih.');
            }
            
            \Log::info('updateTarifBulananMasal called with payment_id: ' . $payment_id);
            \Log::info('Request URL: ' . $request->url());
            \Log::info('Request method: ' . $request->method());
            \Log::info('Request all data: ' . json_encode($request->all()));
            
            $request->validate([
                'class_id' => 'required|exists:class_models,class_id',
                'bulan' => 'required|array',
            ]);
            
            // Pastikan kelas milik sekolah yang sedang aktif
            $class = \App\Models\ClassModel::where('school_id', $currentSchoolId)
                ->findOrFail($request->class_id);
            
            $bulanList = ['Juli','Agustus','September','Oktober','November','Desember','Januari','Februari','Maret','April','Mei','Juni'];
            
            // Ambil siswa dari kelas yang dipilih, filter berdasarkan sekolah yang sedang aktif
            $students = \App\Models\Student::where('class_class_id', $request->class_id)
                ->where('student_status', 1)
                ->where('school_id', $currentSchoolId)
                ->get();
            
            \Log::info('Found students: ' . $students->count());
            \Log::info('Student IDs: ' . json_encode($students->pluck('student_id')->toArray()));
            \Log::info('Class ID from request: ' . $request->class_id);
            
            $updated = 0;
            foreach ($students as $student) {
                // Validasi student_id lebih ketat
                if (!is_numeric($student->student_id) || $student->student_id <= 0) {
                    \Log::warning("Skipping invalid student_id: " . $student->student_id);
                    continue;
                }
                
                \Log::info("Processing student_id: " . $student->student_id);
                foreach ($bulanList as $idx => $b) {
                    $nominal = isset($request->bulan[$b]) && $request->bulan[$b] !== '' ? $request->bulan[$b] : null;
                    if ($nominal !== null) {
                        \Log::info("Updating bulan for student " . $student->student_id . ", month " . ($idx+1) . ", nominal " . $nominal);
                        
                        try {
                        \DB::table('bulan')->updateOrInsert([
                            'student_student_id' => $student->student_id,
                            'payment_payment_id' => $payment_id,
                            'month_month_id' => $idx+1
                        ], [
                            'bulan_bill' => $nominal,
                            'bulan_status' => 1,
                            'bulan_last_update' => now()
                        ]);
                        $updated++;
                            \Log::info("Successfully updated bulan for student " . $student->student_id . ", month " . ($idx+1));
                        } catch (\Exception $e) {
                            \Log::error("Error updating bulan for student " . $student->student_id . ", month " . ($idx+1) . ": " . $e->getMessage());
                            throw $e;
                        }
                    }
                }
            }
            return response()->json(['success' => true, 'message' => "Berhasil update tarif bulanan untuk $updated data!"]);
        } catch (\Exception $e) {
            \Log::error('Error updateTarifBulananMasal: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function cashPayment()
    {
        return view('payment.cash');
    }

    public function searchStudent(Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return response()->json(['results' => []], 403);
            }
            return response()->json(['results' => []], 403);
        }

        $search = $request->input('term', '');
        $status = $request->input('status', 1); // Default to active students

        \Log::info('Search student request:', [
            'search' => $search,
            'status' => $status,
            'current_school_id' => $currentSchoolId
        ]);

        $query = Student::query()
            ->where('student_status', $status)
            ->where('school_id', $currentSchoolId) // Filter berdasarkan school_id
            ->where(function($q) use ($search) {
                $q->where('student_full_name', 'LIKE', "%{$search}%")
                ->orWhere('student_nis', 'LIKE', "%{$search}%");
            })
            ->select('student_id as id', \DB::raw("CONCAT(student_nis, ' - ', student_full_name) as text"))
            ->limit(10)
            ->get();

        \Log::info('Search student results:', [
            'count' => $query->count(),
            'current_school_id' => $currentSchoolId,
            'results' => $query->toArray()
        ]);

        return response()->json(['results' => $query]);
    }

    public function studentDetail($id)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
            }
            return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
        }

        $student = \App\Models\Student::with(['class', 'major'])
            ->where('school_id', $currentSchoolId) // Filter berdasarkan school_id
            ->select('student_id', 'student_nis', 'student_full_name', 'class_class_id', 'student_status', 'student_born_place', 'student_born_date')
            ->find($id);
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan'], 404);
        }
        
        // Ambil periode berdasarkan sekolah yang sedang aktif
        $tahunAjaran = \App\Models\Period::where('school_id', $currentSchoolId)
            ->orderBy('period_status', 'desc')
            ->orderBy('period_start', 'desc')
            ->first();
            
        return response()->json([
            'success' => true,
            'student' => [
                'nis' => $student->student_nis,
                'nama' => $student->student_full_name,
                'kelas' => $student->class ? $student->class->class_name : '-',
                'tahun_ajaran' => $tahunAjaran ? ($tahunAjaran->period_start . '/' . $tahunAjaran->period_end) : '-',
                'status' => $student->student_status ? 'Aktif' : 'Tidak Aktif',
                'tempat_lahir' => $student->student_born_place,
                'tanggal_lahir' => $student->student_born_date,
            ]
        ]);
    }

    public function studentTagihan($id)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
            }
            return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
        }

        // Validasi bahwa siswa milik sekolah yang sedang aktif
        $student = \App\Models\Student::where('student_id', $id)
            ->where('school_id', $currentSchoolId)
            ->first();
            
        if (!$student) {
            return response()->json(['success' => false, 'message' => 'Siswa tidak ditemukan atau tidak memiliki akses'], 404);
        }

        try {
            // Ambil data tahun ajaran dari tabel periods berdasarkan school_id
            $activePeriod = \DB::table('periods')
                ->where('school_id', $currentSchoolId)
                ->where('period_status', 1)
                ->first();
            $studentTahunAjaran = $activePeriod ? $activePeriod->period_start . '/' . $activePeriod->period_end : 'Tahun Ajaran';
            
            // Tagihan Bulanan - Query yang diperbaiki untuk menampilkan semua pos
            $bulanan = \DB::table('bulan')
                ->join('payment', 'bulan.payment_payment_id', '=', 'payment.payment_id')
                ->join('pos_pembayaran', 'payment.pos_pos_id', '=', 'pos_pembayaran.pos_id')
                ->leftJoin('periods', 'payment.period_period_id', '=', 'periods.period_id')
                ->where('bulan.student_student_id', $id)
                ->select(
                    'payment.payment_id',
                    'payment.payment_type',
                    'payment.period_period_id',
                    'payment.pos_pos_id', // Tambahkan pos_id untuk grouping yang benar
                    'pos_pembayaran.pos_name',
                    \DB::raw('COALESCE(CONCAT(periods.period_start, "/", periods.period_end), ?) as period_name'),
                    'bulan.month_month_id',
                    'bulan.bulan_bill',
                    'bulan.bulan_status',
                    'bulan.bulan_date_pay',
                    'bulan.bulan_number_pay'
                )
                ->addBinding($studentTahunAjaran, 'select')
                ->orderBy('payment.pos_pos_id') // Urutkan berdasarkan pos_id
                ->orderBy('bulan.month_month_id')
                ->get();

        } catch (\Exception $e) {
            \Log::error('Error fetching bulanan data: ' . $e->getMessage());
            
            // Fallback query yang lebih sederhana
            try {
                $bulanan = \DB::table('bulan')
                    ->where('student_student_id', $id)
                    ->select(
                        \DB::raw('1 as payment_id'),
                        \DB::raw('"BULAN" as payment_type'),
                        \DB::raw('1 as period_period_id'),
                        \DB::raw('1 as pos_pos_id'),
                        \DB::raw('"SPP" as pos_name'),
                        \DB::raw("'$studentTahunAjaran' as period_name"),
                        'month_month_id',
                        'bulan_bill',
                        'bulan_status',
                        'bulan_date_pay',
                        'bulan_number_pay'
                    )
                    ->get();
            } catch (\Exception $e2) {
                \Log::error('Error in fallback query: ' . $e2->getMessage());
                $bulanan = collect([]);
            }
        }

        // Tagihan Bebas - Query yang dioptimalkan dengan DISTINCT
        try {
            $bebas = \DB::table('bebas')
                ->join('payment', 'bebas.payment_payment_id', '=', 'payment.payment_id')
                ->join('pos_pembayaran', 'payment.pos_pos_id', '=', 'pos_pembayaran.pos_id')
                ->leftJoin('periods', 'payment.period_period_id', '=', 'periods.period_id')
                ->where('bebas.student_student_id', $id)
                ->where('payment.school_id', $currentSchoolId) // Filter berdasarkan school_id
                ->select(
                    'payment.payment_id',
                    'payment.payment_type',
                    'payment.period_period_id',
                    'payment.pos_pos_id', // Tambahkan pos_id
                    'pos_pembayaran.pos_name',
                    \DB::raw('COALESCE(CONCAT(periods.period_start, "/", periods.period_end), ?) as period_name'),
                    'bebas.bebas_bill',
                    'bebas.bebas_total_pay',
                    'bebas.bebas_desc'
                )
                ->addBinding($studentTahunAjaran, 'select')
                ->orderBy('payment.pos_pos_id')
                ->get();
                
        } catch (\Exception $e) {
            \Log::error('Error fetching bebas data: ' . $e->getMessage());
            
            // Fallback query yang lebih sederhana
            try {
                $bebas = \DB::table('bebas')
                    ->where('student_student_id', $id)
                    ->select(
                        \DB::raw('1 as payment_id'),
                        \DB::raw('"BEBAS" as payment_type'),
                        \DB::raw('1 as period_period_id'),
                        \DB::raw('1 as pos_pos_id'),
                        \DB::raw('"Pembayaran Bebas" as pos_name'),
                        \DB::raw("'$studentTahunAjaran' as period_name"),
                        'bebas_bill',
                        'bebas_total_pay',
                        'bebas_desc'
                    )
                    ->get();
            } catch (\Exception $e2) {
                \Log::error('Error in fallback query: ' . $e2->getMessage());
                $bebas = collect([]);
            }
        }

        return response()->json([
            'success' => true,
            'tagihan' => [
            'bulanan' => $bulanan,
            'bebas' => $bebas
            ]
        ]);
    }

    public function processPayment(Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
            }
            return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
        }

        try {
            $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'payment_id' => 'required|exists:payment,payment_id',
                'month_id' => 'required|integer|min:1|max:12',
                'amount' => 'required|numeric|min:0',
                'payment_method' => 'required|in:cash,savings,tunai,tabungan'
                // payment_date tidak diperlukan karena akan menggunakan tanggal server
            ]);

            // Validasi bahwa siswa dan payment milik sekolah yang sedang aktif
            $student = \App\Models\Student::where('student_id', $request->student_id)
                ->where('school_id', $currentSchoolId)
                ->first();
                
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }

            $payment = \App\Models\Payment::where('payment_id', $request->payment_id)
                ->where('school_id', $currentSchoolId)
                ->first();
                
            if (!$payment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jenis pembayaran tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }

            \Log::info('Processing payment:', array_merge($request->all(), [
                'current_school_id' => $currentSchoolId
            ]));

            // Mulai database transaction
            \DB::beginTransaction();

            // Cek apakah sudah ada pembayaran untuk bulan ini
            $existingPayment = \DB::table('bulan')
                ->where('student_student_id', $request->student_id)
                ->where('payment_payment_id', $request->payment_id)
                ->where('month_month_id', $request->month_id)
                ->whereNotNull('bulan_date_pay')
                ->first();

            if ($existingPayment) {
                \DB::rollback();
                return response()->json([
                    'success' => false,
                    'message' => 'Pembayaran untuk bulan ini sudah dilakukan sebelumnya!'
                ], 422);
            }

            // Validasi pembayaran berurutan - Lebih fleksibel untuk pembayaran tunai
            $currentPayment = \DB::table('payment')
                ->where('payment_id', $request->payment_id)
                ->first();

            if ($currentPayment) {
                // Untuk pembayaran tunai, validasi lebih fleksibel
                if ($request->payment_method === 'cash' || $request->payment_method === 'tunai') {
                    // Hanya log warning, tidak blokir pembayaran
                    \Log::warning('Cash payment validation - checking previous unpaid months:', [
                        'student_id' => $request->student_id,
                        'payment_id' => $request->payment_id,
                        'month_id' => $request->month_id,
                        'payment_method' => $request->payment_method
                    ]);
                } else {
                    // Untuk pembayaran non-tunai, tetap validasi ketat
                    // Cek apakah ada period sebelumnya yang belum lunas
                    $previousUnpaidPeriods = \DB::table('bulan as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->where('b.student_student_id', $request->student_id)
                        ->where('p.period_period_id', '<', $currentPayment->period_period_id)
                        ->whereNull('b.bulan_date_pay')
                        ->where('b.bulan_bill', '>', 0)
                        ->orderBy('p.period_period_id', 'asc')
                        ->orderBy('b.month_month_id', 'asc')
                        ->first();

                    if ($previousUnpaidPeriods) {
                        $periodInfo = \DB::table('periods')
                            ->where('period_id', $previousUnpaidPeriods->period_period_id)
                            ->first();

                        $periodName = $periodInfo ? ($periodInfo->period_start . '/' . $periodInfo->period_end) : 'Period Sebelumnya';
                        
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => "⚠️ Pembayaran Tidak Dapat Diproses!\n\nMasih ada pembayaran yang belum diselesaikan di tahun ajaran $periodName.\n\n📋 Silakan selesaikan semua pembayaran di tahun ajaran $periodName terlebih dahulu."
                        ], 422);
                    }

                                         // Jika period sama, cek urutan bulan untuk pembayaran non-tunai
                     $previousUnpaidMonths = \DB::table('bulan')
                         ->where('student_student_id', $request->student_id)
                         ->where('payment_payment_id', $request->payment_id)
                         ->where('month_month_id', '<', $request->month_id)
                         ->whereNull('bulan_date_pay')
                         ->where('bulan_bill', '>', 0)
                         ->orderBy('month_month_id', 'asc')
                         ->get();

                    if ($previousUnpaidMonths->count() > 0) {
                        $firstUnpaidMonth = $previousUnpaidMonths->first();
                        $monthNames = [
                            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 5 => 'November', 6 => 'Desember',
                            7 => 'Januari', 8 => 'Februari', 9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
                        ];
                        
                        $unpaidMonthName = $monthNames[$firstUnpaidMonth->month_month_id] ?? 'Unknown';
                        $currentMonthName = $monthNames[$request->month_id] ?? 'Unknown';
                        
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => "⚠️ Pembayaran Tidak Dapat Diproses!\n\nMasih ada pembayaran bulan $unpaidMonthName yang belum diselesaikan.\n\n📋 Silakan selesaikan pembayaran bulan $unpaidMonthName terlebih dahulu sebelum melanjutkan ke bulan berikutnya."
                        ], 422);
                    }
                }
            }



            // Jika pembayaran dengan tabungan, cek saldo
            if ($request->payment_method === 'tabungan' || $request->payment_method === 'savings') {
                // Cek saldo tabungan siswa
                $tabungan = \DB::table('tabungan')
                    ->where('student_student_id', $request->student_id)
                    ->first();
                
                if (!$tabungan) {
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa tidak memiliki rekening tabungan!'
                    ], 422);
                }
                
                $saldoTabungan = (float) $tabungan->saldo;
                
                if ($saldoTabungan < $request->amount) {
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ Saldo Tabungan Tidak Mencukupi!\n\nSaldo tabungan: Rp " . number_format($saldoTabungan, 0, ',', '.') . "\nNominal pembayaran: Rp " . number_format($request->amount, 0, ',', '.') . "\n\nSilakan top up tabungan terlebih dahulu atau pilih metode pembayaran lain."
                    ], 422);
                }
                
                \Log::info('Tabungan payment validation passed:', [
                    'student_id' => $request->student_id,
                    'saldo_tabungan' => $saldoTabungan,
                    'amount' => $request->amount,
                    'sisa_saldo' => $saldoTabungan - $request->amount
                ]);
            }

            // Proses pembayaran
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Update status pembayaran di tabel bulan
            $currentDate = now()->format('Y-m-d');
            
            try {
                $updateResult = \DB::table('bulan')
                    ->where('student_student_id', $request->student_id)
                    ->where('payment_payment_id', $request->payment_id)
                    ->where('month_month_id', $request->month_id)
                    ->update([
                        'bulan_date_pay' => $currentDate, // Gunakan tanggal server saat ini
                        'bulan_number_pay' => $paymentNumber,
                        'bulan_status' => 1, // Set status menjadi lunas
                        'bulan_last_update' => now()
                    ]);
                
                if ($updateResult === 0) {
                    \DB::rollback();
                    \Log::error('Failed to update bulan table - no rows affected:', [
                        'student_id' => $request->student_id,
                        'payment_id' => $request->payment_id,
                        'month_id' => $request->month_id
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal memproses pembayaran: Data tagihan tidak ditemukan atau sudah lunas!'
                    ], 422);
                }
                
                \Log::info('Bulan table updated successfully:', [
                    'student_id' => $request->student_id,
                    'payment_id' => $request->payment_id,
                    'month_id' => $request->month_id,
                    'payment_number' => $paymentNumber,
                    'payment_date' => $currentDate
                ]);
                
            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error('Error updating bulan table:', [
                    'student_id' => $request->student_id,
                    'payment_id' => $request->payment_id,
                    'month_id' => $request->month_id,
                    'error' => $e->getMessage()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal memproses pembayaran: ' . $e->getMessage()
                ], 500);
            }

            // Ambil bulan_id untuk insert ke log_trx
            $bulanRecord = \DB::table('bulan')
                ->where('student_student_id', $request->student_id)
                ->where('payment_payment_id', $request->payment_id)
                ->where('month_month_id', $request->month_id)
                ->first();

            // Insert ke log_trx untuk riwayat transaksi - cek duplikasi dulu
            if ($bulanRecord) {
                // Cek apakah sudah ada record di log_trx untuk bulan_id ini
                $existingLogTrx = \DB::table('log_trx')
                    ->where('bulan_bulan_id', $bulanRecord->bulan_id)
                    ->where('student_student_id', $request->student_id)
                    ->first();
                
                if (!$existingLogTrx) {
                    \DB::table('log_trx')->insert([
                        'student_student_id' => $request->student_id,
                        'bulan_bulan_id' => $bulanRecord->bulan_id,
                        'bebas_pay_bebas_pay_id' => null, // null karena ini pembayaran bulanan
                        'log_trx_input_date' => now(),
                        'log_trx_last_update' => now()
                    ]);
                    
                    \Log::info('Transaction logged to log_trx:', [
                        'student_id' => $request->student_id,
                        'bulan_id' => $bulanRecord->bulan_id,
                        'payment_number' => $paymentNumber
                    ]);
                } else {
                    \Log::warning('Duplicate log_trx entry prevented:', [
                        'student_id' => $request->student_id,
                        'bulan_id' => $bulanRecord->bulan_id,
                        'payment_number' => $paymentNumber,
                        'existing_log_trx_id' => $existingLogTrx->log_trx_id
                    ]);
                }
            }

            // Jika pembayaran dengan tabungan, kurangi saldo dan catat mutasi
            if ($request->payment_method === 'tabungan' || $request->payment_method === 'savings') {
                try {
                    // Kurangi saldo tabungan
                    \DB::table('tabungan')
                        ->where('student_student_id', $request->student_id)
                        ->decrement('saldo', $request->amount);
                    
                    // Update timestamp terakhir
                    \DB::table('tabungan')
                        ->where('student_student_id', $request->student_id)
                        ->update(['tabungan_last_update' => now()]);
                    
                    // Catat mutasi tabungan untuk pembayaran
                    \DB::table('log_tabungan')->insert([
                        'tabungan_tabungan_id' => $tabungan->tabungan_id,
                        'student_student_id' => $request->student_id,
                        'kredit' => 0, // Tidak ada setoran
                        'debit' => $request->amount, // Penarikan untuk pembayaran
                        'saldo' => $saldoTabungan - $request->amount, // Saldo setelah pembayaran
                        'keterangan' => "Pembayaran SPP Bulanan - " . $paymentNumber,
                        'log_tabungan_input_date' => now(),
                        'log_tabungan_last_update' => now()
                    ]);
                    
                    \Log::info('Tabungan payment processed successfully:', [
                        'student_id' => $request->student_id,
                        'tabungan_id' => $tabungan->tabungan_id,
                        'amount_debited' => $request->amount,
                        'balance_before' => $saldoTabungan,
                        'balance_after' => $saldoTabungan - $request->amount,
                        'payment_number' => $paymentNumber
                    ]);
                    
                } catch (\Exception $e) {
                    \DB::rollback();
                    \Log::error('Failed to process tabungan payment:', [
                        'student_id' => $request->student_id,
                        'amount' => $request->amount,
                        'error' => $e->getMessage()
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal memproses pembayaran dengan tabungan. Silakan coba lagi atau pilih metode pembayaran lain.'
                    ], 500);
                }
            }

                                        \Log::info('Payment processed successfully:', [
                'student_id' => $request->student_id,
                'payment_id' => $request->payment_id,
                'month_id' => $request->month_id,
                'amount' => $request->amount,
                'method' => $request->payment_method,
                'payment_number' => $paymentNumber,
                'payment_date' => $currentDate,
                'server_date' => now()->format('Y-m-d')
            ]);

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                \Log::info("Starting WhatsApp notification check for payment", [
                    'student_id' => $request->student_id,
                    'amount' => $request->amount,
                    'payment_method' => $request->payment_method
                ]);
                
                $gateway = \DB::table('setup_gateways')->first();
                \Log::info("Gateway configuration", [
                    'has_gateway' => $gateway ? 'YES' : 'NO',
                    'has_whatsapp_config' => $gateway && ($gateway->apikey_wagateway || $gateway->url_wagateway) ? 'YES' : 'NO'
                ]);
                
                // Cek apakah konfigurasi WhatsApp tersedia dan diaktifkan
                if ($gateway && $gateway->enable_wa_notification && $gateway->apikey_wagateway && $gateway->url_wagateway) {
                    // Ambil data siswa untuk notifikasi
                    $student = \DB::table('students')
                        ->where('student_id', $request->student_id)
                        ->first();
                    
                    \Log::info("Student data for notification", [
                        'student_id' => $request->student_id,
                        'has_student' => $student ? 'YES' : 'NO',
                        'has_parent_phone' => $student && $student->student_parent_phone ? 'YES' : 'NO',
                        'parent_phone' => $student->student_parent_phone ?? 'NULL'
                    ]);
                    
                    if ($student && $student->student_parent_phone) {
                        $whatsappService = new WhatsAppService();
                        
                        // Untuk pembayaran tunai, TIDAK buat transfer record
                        // Hanya kirim notifikasi dengan data yang sudah ada
                        if ($request->payment_method === 'cash' || $request->payment_method === 'tunai') {
                            // Kirim notifikasi tanpa transfer record untuk pembayaran tunai
                            \Log::info("Sending WhatsApp notification for cash bulanan payment:", [
                                'student_id' => $request->student_id,
                                'payment_number' => $paymentNumber,
                                'amount' => $request->amount,
                                'bill_type' => 'bulanan',
                                'bulan_id' => $bulanRecord->bulan_id,
                                'payment_id' => $request->payment_id
                            ]);
                            
                            $result = $whatsappService->sendPaymentSuccessNotificationWithoutTransfer(
                                $request->student_id,
                                $paymentNumber,
                                $request->amount,
                                'bulanan',
                                $bulanRecord->bulan_id
                            );
                        } else {
                            // Hanya untuk pembayaran non-tunai (tabungan, dll) yang perlu transfer record
                            $transferId = \DB::table('transfer')->insertGetId([
                                'student_id' => $request->student_id,
                                'payment_method' => $request->payment_method,
                                'status' => 1, // Success
                                'payment_number' => $paymentNumber,
                                'bill_type' => 'bulanan',
                                'bill_id' => $request->payment_id,
                                'confirm_pay' => $request->amount,
                                'confirm_date' => now(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            
                            // Buat transfer_detail record untuk notifikasi
                            \DB::table('transfer_detail')->insert([
                                'transfer_id' => $transferId,
                                'payment_type' => 1, // 1 for bulanan
                                'bulan_id' => $bulanRecord->bulan_id,
                                'bebas_id' => null,
                                'desc' => 'Pembayaran SPP Bulanan'
                            ]);
                            
                            \Log::info("Created transfer record for non-cash payment notification", [
                                'transfer_id' => $transferId,
                                'payment_number' => $paymentNumber,
                                'payment_method' => $request->payment_method
                            ]);
                            
                            // Kirim notifikasi sukses
                            $result = $whatsappService->sendPaymentSuccessNotification($transferId);
                        }
                        
                        \Log::info("WhatsApp notification result", [
                            'success' => $result ? 'YES' : 'NO',
                            'payment_method' => $request->payment_method
                        ]);
                        
                        if ($result) {
                            \Log::info("WhatsApp notification sent successfully for {$request->payment_method} payment");
                        } else {
                            \Log::error("WhatsApp notification failed for {$request->payment_method} payment");
                        }
                    } else {
                        \Log::warning("WhatsApp notification skipped - no parent phone for student_id: {$request->student_id}");
                    }
                } else {
                    \Log::info("WhatsApp notification skipped - missing configuration (apikey_wagateway or url_wagateway)");
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send WhatsApp notification for {$request->payment_method} payment: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            // Ambil data riwayat transaksi terbaru
            try {
                $latestTransactions = \DB::table('log_trx as lt')
                    ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                    ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                    ->leftJoin('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                    ->leftJoin('payment as p', function($join) {
                        $join->on('b.payment_payment_id', '=', 'p.payment_id')
                             ->orOn('be.payment_payment_id', '=', 'p.payment_id');
                    })
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('lt.student_student_id', $request->student_id)
                    ->orderBy('lt.log_trx_input_date', 'desc')
                    ->limit(5)
                    ->select(
                        'pos.pos_name',
                        \DB::raw('COALESCE(b.bulan_bill, bp.bebas_pay_bill) as amount'),
                        \DB::raw('COALESCE(b.bulan_number_pay, bp.bebas_pay_number) as payment_number'),
                        'lt.log_trx_input_date',
                        \DB::raw('COALESCE(b.bulan_date_pay, bp.bebas_pay_input_date) as payment_date'),
                        \DB::raw('CASE 
                            WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos.pos_name, "-", 
                                CASE b.month_month_id
                                    WHEN 1 THEN "Juli"
                                    WHEN 2 THEN "Agustus"
                                    WHEN 3 THEN "September"
                                    WHEN 4 THEN "Oktober"
                                    WHEN 5 THEN "November"
                                    WHEN 6 THEN "Desember"
                                    WHEN 7 THEN "Januari"
                                    WHEN 8 THEN "Februari"
                                    WHEN 9 THEN "Maret"
                                    WHEN 10 THEN "April"
                                    WHEN 11 THEN "Mei"
                                    WHEN 12 THEN "Juni"
                                    ELSE "Unknown"
                                END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                            )
                            WHEN bp.bebas_pay_id IS NOT NULL THEN CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"))
                            ELSE pos.pos_name
                        END as display_name')
                    )
                    ->get();

                \Log::info('Latest transactions fetched successfully:', [
                    'count' => $latestTransactions->count(),
                    'sample_transactions' => $latestTransactions->take(2)->map(function($t) {
                        return [
                            'payment_date' => $t->payment_date,
                            'log_trx_input_date' => $t->log_trx_input_date,
                            'payment_number' => $t->payment_number
                        ];
                    })->toArray()
                ]);
            } catch (\Exception $e) {
                \Log::error('Error fetching latest transactions: ' . $e->getMessage());
                $latestTransactions = collect([]); // Empty collection if error
            }

            // Commit transaction
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran berhasil diproses!',
                'payment_number' => $paymentNumber,
                'payment_date' => $currentDate, // Gunakan tanggal server saat ini
                'latest_transactions' => $latestTransactions
            ]);

        } catch (\Exception $e) {
            // Rollback transaction jika terjadi error
            \DB::rollback();
            
            \Log::error('Error processing payment: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function studentTabungan($id)
    {
        try {
            \Log::info('Fetching tabungan data for student:', ['student_id' => $id]);
            
            $tabungan = \DB::table('tabungan')
                ->where('student_student_id', $id)
                ->first();
            
            if (!$tabungan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak memiliki rekening tabungan'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'tabungan' => $tabungan
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Error fetching tabungan data: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data tabungan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getTransactionHistory($studentId)
    {
        try {
            \Log::info('Fetching transaction history for student ID: ' . $studentId);
            
            // Validasi student ID
            if (!$studentId || !is_numeric($studentId)) {
                \Log::error('Invalid student ID provided: ' . $studentId);
                return response()->json([
                    'success' => false,
                    'message' => 'ID siswa tidak valid'
                ], 400);
            }
            
            // Ambil riwayat transaksi bulanan
            $bulananTransactions = \DB::table('log_trx as lt')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                ->where('lt.student_student_id', $studentId)
                ->whereNotNull('lt.bulan_bulan_id')
                ->select(
                    'lt.log_trx_id',
                    'lt.log_trx_input_date',
                    'p.payment_type',
                    'pos.pos_name',
                    'b.bulan_bill as amount',
                    'b.bulan_number_pay as payment_number',
                    'b.bulan_date_pay as payment_date',
                    \DB::raw('"BULANAN" as transaction_type'),
                    \DB::raw('CONCAT(pos.pos_name, "-", 
                        CASE b.month_month_id
                            WHEN 1 THEN "Juli"
                            WHEN 2 THEN "Agustus"
                            WHEN 3 THEN "September"
                            WHEN 4 THEN "Oktober"
                            WHEN 5 THEN "November"
                            WHEN 6 THEN "Desember"
                            WHEN 7 THEN "Januari"
                            WHEN 8 THEN "Februari"
                            WHEN 9 THEN "Maret"
                            WHEN 10 THEN "April"
                            WHEN 11 THEN "Mei"
                            WHEN 12 THEN "Juni"
                            ELSE "Unknown"
                        END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                    ) as display_name')
                )
                ->get();

            // Ambil riwayat transaksi bebas
            $bebasTransactions = \DB::table('log_trx as lt')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->leftJoin('bebas as b', 'bp.bebas_bebas_id', '=', 'b.bebas_id')
                ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                ->where('lt.student_student_id', $studentId)
                ->whereNotNull('lt.bebas_pay_bebas_pay_id')
                ->select(
                    'lt.log_trx_id',
                    'lt.log_trx_input_date',
                    'p.payment_type',
                    'pos.pos_name',
                    'bp.bebas_pay_bill as amount',
                    'bp.bebas_pay_number as payment_number',
                    'bp.bebas_pay_input_date as payment_date',
                    \DB::raw('"BEBAS" as transaction_type'),
                    \DB::raw('CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026")) as display_name')
                )
                ->get();

            // Gabungkan dan urutkan di PHP
            $transactions = $bulananTransactions->concat($bebasTransactions)
                ->sortByDesc('log_trx_input_date')
                ->values();

            \Log::info('Transaction history query result:', [
                'student_id' => $studentId,
                'count' => $transactions->count(),
                'sample_data' => $transactions->take(2)->toArray()
            ]);
            


            \Log::info('Transaction history fetched successfully:', ['count' => $transactions->count()]);
            
            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);

        } catch (\Exception $e) {
            \Log::error('Error fetching transaction history: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil riwayat transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    public function processBebasPayment(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'payment_id' => 'required|exists:payment,payment_id',
                'amount' => 'required|numeric|min:1',
                'description' => 'nullable|string|max:500',
                'payment_date' => 'required|date',
                'payment_method' => 'required|in:cash,savings,tunai,tabungan',
                'bypass_validation' => 'nullable|boolean' // Tambah parameter untuk bypass validasi
            ]);

            \Log::info('Processing bebas payment:', $request->all());

            // Mulai database transaction
            \DB::beginTransaction();

            // Cek data bebas
            $bebas = \DB::table('bebas')
                ->where('student_student_id', $request->student_id)
                ->where('payment_payment_id', $request->payment_id)
                ->first();

            if (!$bebas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tagihan bebas tidak ditemukan!'
                ], 422);
            }

            // Hitung sisa tagihan
            $sisa = $bebas->bebas_bill - $bebas->bebas_total_pay;

            // Validasi nominal pembayaran
            if ($request->amount > $sisa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan!'
                ], 422);
            }

            // Jika pembayaran dengan tabungan, cek saldo
            if ($request->payment_method === 'tabungan' || $request->payment_method === 'savings') {
                // Cek saldo tabungan siswa
                $tabungan = \DB::table('tabungan')
                    ->where('student_student_id', $request->student_id)
                    ->first();
                
                if (!$tabungan) {
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa tidak memiliki rekening tabungan!'
                    ], 422);
                }
                
                $saldoTabungan = (float) $tabungan->saldo;
                
                if ($saldoTabungan < $request->amount) {
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ Saldo Tabungan Tidak Mencukupi!\n\nSaldo tabungan: Rp " . number_format($saldoTabungan, 0, ',', '.') . "\nNominal pembayaran: Rp " . number_format($request->amount, 0, ',', '.') . "\n\nSilakan top up tabungan terlebih dahulu atau pilih metode pembayaran lain."
                    ], 422);
                }
                
                \Log::info('Bebas tabungan payment validation passed:', [
                    'student_id' => $request->student_id,
                    'saldo_tabungan' => $saldoTabungan,
                    'amount' => $request->amount,
                    'sisa_saldo' => $saldoTabungan - $request->amount
                ]);
            }

            // Validasi pembayaran bebas berurutan - lebih fleksibel untuk pembayaran tunai dan tabungan
            // Skip validasi ketat jika:
            // 1. bypass_validation = true (admin override)
            // 2. Pembayaran tunai (cash, tunai) - lebih fleksibel
            // 3. Pembayaran via saldo tabungan (tabungan, savings) - lebih fleksibel
            // 
            // Validasi ketat hanya berlaku untuk pembayaran online/transfer yang memerlukan urutan
            $skipStrictValidation = $request->bypass_validation || 
                                   in_array($request->payment_method, ['cash', 'tunai', 'tabungan', 'savings']);
            
            if (!$skipStrictValidation) {
                $currentPayment = \DB::table('payment')
                    ->where('payment_id', $request->payment_id)
                    ->first();

                if ($currentPayment) {
                    // Cek apakah ada period sebelumnya yang belum lunas untuk pembayaran bebas
                    $previousUnpaidBebasPeriods = \DB::table('bebas as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                        ->where('b.student_student_id', $request->student_id)
                        ->where('p.period_period_id', '<', $currentPayment->period_period_id)
                        ->whereRaw('(b.bebas_bill - b.bebas_total_pay) > 0') // Masih ada sisa tagihan
                        ->orderBy('p.period_period_id', 'asc')
                        ->first();

                    \Log::info('Validation check - Previous unpaid bebas periods:', [
                        'student_id' => $request->student_id,
                        'payment_id' => $request->payment_id,
                        'current_period_id' => $currentPayment->period_period_id,
                        'previous_unpaid_bebas' => $previousUnpaidBebasPeriods ? $previousUnpaidBebasPeriods->pos_name : null
                    ]);

                    if ($previousUnpaidBebasPeriods) {
                        $periodInfo = \DB::table('periods')
                            ->where('period_id', $previousUnpaidBebasPeriods->period_period_id)
                            ->first();

                        $periodName = $periodInfo ? ($periodInfo->period_start . '/' . $periodInfo->period_end) : 'Period Sebelumnya';
                        $unpaidPaymentName = $previousUnpaidBebasPeriods->pos_name;

                        // Ambil informasi pembayaran yang sedang dipilih
                        $currentPaymentInfo = \DB::table('payment as p')
                            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                            ->where('p.payment_id', $request->payment_id)
                            ->first();
                        
                        $currentPeriodInfo = \DB::table('periods')
                            ->where('period_id', $currentPayment->period_period_id)
                            ->first();
                        
                        $currentPeriodName = $currentPeriodInfo ? ($currentPeriodInfo->period_start . '/' . $currentPeriodInfo->period_end) : 'Tahun Ajaran Saat Ini';
                        $currentPaymentName = $currentPaymentInfo ? $currentPaymentInfo->pos_name : 'Pembayaran Bebas';

                        \Log::info('Bebas payment rejected due to unpaid previous period:', [
                            'unpaid_period_id' => $previousUnpaidBebasPeriods->period_period_id,
                            'unpaid_period_name' => $periodName,
                            'unpaid_payment_name' => $unpaidPaymentName
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => "⚠️ Pembayaran Tidak Dapat Diproses!\n\nAnda mencoba melakukan pembayaran bebas '$currentPaymentName' untuk tahun ajaran $currentPeriodName, namun masih ada pembayaran bebas '$unpaidPaymentName' yang belum diselesaikan di tahun ajaran $periodName.\n\n📋 Silakan selesaikan pembayaran bebas '$unpaidPaymentName' di tahun ajaran $periodName terlebih dahulu sebelum melanjutkan ke tahun ajaran berikutnya."
                        ], 422);
                    }

                    // Jika period sama, cek apakah ada pembayaran bebas lain yang belum lunas
                    $otherUnpaidBebasSamePeriod = \DB::table('bebas as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                        ->where('b.student_student_id', $request->student_id)
                        ->where('p.period_period_id', $currentPayment->period_period_id)
                        ->where('b.payment_payment_id', '!=', $request->payment_id) // Exclude current payment
                        ->whereRaw('(b.bebas_bill - b.bebas_total_pay) > 0') // Masih ada sisa tagihan
                        ->orderBy('b.bebas_input_date', 'asc') // Urutkan berdasarkan tanggal input
                        ->first();

                    if ($otherUnpaidBebasSamePeriod) {
                        $unpaidPaymentName = $otherUnpaidBebasSamePeriod->pos_name;

                        // Ambil informasi pembayaran yang sedang dipilih
                        $currentPaymentInfo = \DB::table('payment as p')
                            ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                            ->where('p.payment_id', $request->payment_id)
                            ->first();
                        
                        $currentPeriodInfo = \DB::table('periods')
                            ->where('period_id', $currentPayment->period_period_id)
                            ->first();
                        
                        $currentPeriodName = $currentPeriodInfo ? ($currentPeriodInfo->period_start . '/' . $currentPeriodInfo->period_end) : 'Tahun Ajaran Saat Ini';
                        $currentPaymentName = $currentPaymentInfo ? $currentPaymentInfo->pos_name : 'Pembayaran Bebas';

                        \Log::info('Bebas payment rejected due to other unpaid bebas in same period:', [
                            'unpaid_bebas_name' => $unpaidPaymentName,
                            'current_payment_name' => $currentPaymentName,
                            'period_id' => $currentPayment->period_period_id
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => "⚠️ Pembayaran Tidak Dapat Diproses!\n\nAnda mencoba melakukan pembayaran bebas '$currentPaymentName' untuk tahun ajaran $currentPeriodName, namun masih ada pembayaran bebas '$unpaidPaymentName' yang belum diselesaikan.\n\n📋 Silakan selesaikan pembayaran bebas '$unpaidPaymentName' terlebih dahulu sebelum melanjutkan ke pembayaran berikutnya."
                        ], 422);
                    }
                }
            } else {
                // Untuk pembayaran tunai dan tabungan, validasi berurutan di-skip
                // Ini memungkinkan pembayaran yang lebih fleksibel tanpa memblokir user
                \Log::info('Bebas payment validation relaxed for flexible payment methods:', [
                    'student_id' => $request->student_id,
                    'payment_method' => $request->payment_method,
                    'bypass_validation' => $request->bypass_validation,
                    'reason' => 'Flexible payment method - allowing flexible payment order'
                ]);
                
                // Log warning untuk pembayaran yang diizinkan meskipun ada pembayaran lain yang belum lunas
                // Ini membantu admin untuk monitoring tanpa memblokir user
                if (in_array($request->payment_method, ['cash', 'tunai', 'tabungan', 'savings'])) {
                    \Log::warning('Flexible payment allowed despite unpaid previous bebas payments:', [
                        'student_id' => $request->student_id,
                        'payment_id' => $request->payment_id,
                        'payment_method' => $request->payment_method,
                        'note' => 'Flexible payment methods have relaxed validation rules'
                    ]);
                }
            }

            // Generate nomor pembayaran
            $paymentNumber = 'PAY-BEBAS-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Insert ke tabel bebas_pay
            $bebasPayId = \DB::table('bebas_pay')->insertGetId([
                'bebas_bebas_id' => $bebas->bebas_id,
                'bebas_pay_bill' => $request->amount,
                'bebas_pay_number' => $paymentNumber,
                'bebas_pay_desc' => $request->description,
                'user_user_id' => 1, // Default user ID, sesuaikan dengan sistem auth
                'bebas_pay_input_date' => $request->payment_date,
                'bebas_pay_last_update' => now()
            ]);

            // Update total pembayaran di tabel bebas
            \DB::table('bebas')
                ->where('bebas_id', $bebas->bebas_id)
                ->update([
                    'bebas_total_pay' => $bebas->bebas_total_pay + $request->amount,
                    'bebas_date_pay' => $request->payment_date, // Set tanggal pembayaran
                    'bebas_number_pay' => $paymentNumber, // Set nomor pembayaran
                    'bebas_last_update' => now()
                ]);

            // Insert ke log_trx untuk riwayat transaksi
            \DB::table('log_trx')->insert([
                'student_student_id' => $request->student_id,
                'bulan_bulan_id' => null, // null karena ini pembayaran bebas
                'bebas_pay_bebas_pay_id' => $bebasPayId,
                'log_trx_input_date' => now(),
                'log_trx_last_update' => now()
            ]);

            // Jika pembayaran dengan tabungan, kurangi saldo dan catat mutasi
            if ($request->payment_method === 'tabungan' || $request->payment_method === 'savings') {
                try {
                    // Kurangi saldo tabungan
                    \DB::table('tabungan')
                        ->where('student_student_id', $request->student_id)
                        ->decrement('saldo', $request->amount);
                    
                    // Update timestamp terakhir
                    \DB::table('tabungan')
                        ->where('student_student_id', $request->student_id)
                        ->update(['tabungan_last_update' => now()]);
                    
                    // Catat mutasi tabungan untuk pembayaran bebas
                    \DB::table('log_tabungan')->insert([
                        'tabungan_tabungan_id' => $tabungan->tabungan_id,
                        'student_student_id' => $request->student_id,
                        'kredit' => 0, // Tidak ada setoran
                        'debit' => $request->amount, // Penarikan untuk pembayaran
                        'saldo' => $saldoTabungan - $request->amount, // Saldo setelah pembayaran
                        'keterangan' => "Pembayaran Bebas - " . $paymentNumber,
                        'log_tabungan_input_date' => now(),
                        'log_tabungan_last_update' => now()
                    ]);
                    
                    \Log::info('Bebas tabungan payment processed successfully:', [
                        'student_id' => $request->student_id,
                        'tabungan_id' => $tabungan->tabungan_id,
                        'amount_debited' => $request->amount,
                        'balance_before' => $saldoTabungan,
                        'balance_after' => $saldoTabungan - $request->amount,
                        'payment_number' => $paymentNumber
                    ]);
                    
                } catch (\Exception $e) {
                    \Log::error('Failed to process bebas tabungan payment:', [
                        'student_id' => $request->student_id,
                        'amount' => $request->amount,
                        'error' => $e->getMessage()
                    ]);
                    
                    // Jangan gagalkan proses pembayaran jika pengurangan saldo gagal
                    // Tapi log error untuk investigasi
                }
            }

            \Log::info('Bebas payment processed successfully:', [
                'student_id' => $request->student_id,
                'payment_id' => $request->payment_id,
                'amount' => $request->amount,
                'payment_number' => $paymentNumber,
                'bebas_pay_id' => $bebasPayId
            ]);

            // Ambil data riwayat transaksi terbaru
            try {
                $latestTransactions = \DB::table('log_trx as lt')
                    ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                    ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                    ->leftJoin('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                    ->leftJoin('payment as p', function($join) {
                        $join->on('b.payment_payment_id', '=', 'p.payment_id')
                             ->orOn('be.payment_payment_id', '=', 'p.payment_id');
                    })
                    ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                    ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                    ->where('lt.student_student_id', $request->student_id)
                    ->orderBy('lt.log_trx_input_date', 'desc')
                    ->limit(5)
                    ->select(
                        'pos.pos_name',
                        \DB::raw('COALESCE(b.bulan_bill, bp.bebas_pay_bill) as amount'),
                        \DB::raw('COALESCE(b.bulan_number_pay, bp.bebas_pay_number) as payment_number'),
                        'lt.log_trx_input_date',
                        \DB::raw('CASE 
                            WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos.pos_name, "-", 
                                CASE b.month_month_id
                                    WHEN 1 THEN "Juli"
                                    WHEN 2 THEN "Agustus"
                                    WHEN 3 THEN "September"
                                    WHEN 4 THEN "Oktober"
                                    WHEN 5 THEN "November"
                                    WHEN 6 THEN "Desember"
                                    WHEN 7 THEN "Januari"
                                    WHEN 8 THEN "Februari"
                                    WHEN 9 THEN "Maret"
                                    WHEN 10 THEN "April"
                                    WHEN 11 THEN "Mei"
                                    WHEN 12 THEN "Juni"
                                    ELSE "Unknown"
                                END, " (", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"), ")"
                            )
                            WHEN bp.bebas_pay_id IS NOT NULL THEN CONCAT(pos.pos_name, " - ", COALESCE(CONCAT(per.period_start, "/", per.period_end), "2025/2026"))
                            ELSE pos.pos_name
                        END as display_name')
                    )
                    ->get();

                \Log::info('Latest transactions fetched after bebas payment:', ['count' => $latestTransactions->count()]);
            } catch (\Exception $e) {
                \Log::error('Error fetching latest transactions after bebas payment: ' . $e->getMessage());
                $latestTransactions = collect([]);
            }

            // Kirim notifikasi WhatsApp jika diaktifkan
            try {
                \Log::info("Starting WhatsApp notification check for bebas payment", [
                    'student_id' => $request->student_id,
                    'amount' => $request->amount
                ]);
                
                $gateway = \DB::table('setup_gateways')->first();
                \Log::info("Gateway configuration for bebas", [
                    'enable_wa_notification' => $gateway->enable_wa_notification ?? false,
                    'has_gateway' => $gateway ? 'YES' : 'NO'
                ]);
                
                if ($gateway && $gateway->enable_wa_notification) {
                    // Ambil data siswa untuk notifikasi
                    $student = \DB::table('students')
                        ->where('student_id', $request->student_id)
                        ->first();
                    
                    \Log::info("Student data for bebas notification", [
                        'student_id' => $request->student_id,
                        'has_student' => $student ? 'YES' : 'NO',
                        'has_parent_phone' => $student && $student->student_parent_phone ? 'YES' : 'NO',
                        'parent_phone' => $student->student_parent_phone ?? 'NULL'
                    ]);
                    
                    if ($student && $student->student_parent_phone) {
                        $whatsappService = new WhatsAppService();
                        
                        // Untuk pembayaran tunai, TIDAK buat transfer record
                        // Hanya kirim notifikasi dengan data yang sudah ada
                        if ($request->payment_method === 'cash' || $request->payment_method === 'tunai') {
                            // Kirim notifikasi tanpa transfer record untuk pembayaran tunai
                            \Log::info("Sending WhatsApp notification for cash bebas payment:", [
                                'student_id' => $request->student_id,
                                'payment_number' => $paymentNumber,
                                'amount' => $request->amount,
                                'bill_type' => 'bebas',
                                'bebas_pay_id' => $bebasPayId,
                                'payment_id' => $request->payment_id
                            ]);
                            
                            $result = $whatsappService->sendPaymentSuccessNotificationWithoutTransfer(
                                $request->student_id,
                                $paymentNumber,
                                $request->amount,
                                'bebas',
                                $bebasPayId
                            );
                        } else {
                            // Hanya untuk pembayaran non-tunai (tabungan, dll) yang perlu transfer record
                            $transferId = \DB::table('transfer')->insertGetId([
                                'student_id' => $request->student_id,
                                'payment_method' => $request->payment_method,
                                'status' => 1, // Success
                                'payment_number' => $paymentNumber,
                                'bill_type' => 'bebas',
                                'bill_id' => $request->payment_id,
                                'confirm_pay' => $request->amount,
                                'confirm_date' => now(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ]);
                            
                            // Buat transfer_detail record untuk notifikasi
                            \DB::table('transfer_detail')->insert([
                                'transfer_id' => $transferId,
                                'payment_type' => 2, // 2 for bebas
                                'bulan_id' => null,
                                'bebas_id' => $bebasPayId,
                                'desc' => 'Pembayaran Bebas'
                            ]);
                            
                            \Log::info("Created transfer record for non-cash bebas notification", [
                                'transfer_id' => $transferId,
                                'payment_number' => $paymentNumber,
                                'payment_method' => $request->payment_method
                            ]);
                            
                            // Kirim notifikasi sukses
                            $result = $whatsappService->sendPaymentSuccessNotification($transferId);
                        }
                        
                        \Log::info("WhatsApp notification result for bebas", [
                            'success' => $result ? 'YES' : 'NO',
                            'payment_method' => $request->payment_method
                        ]);
                        
                        if ($result) {
                            \Log::info("WhatsApp notification sent successfully for bebas payment");
                        } else {
                            \Log::error("WhatsApp notification failed for bebas payment");
                        }
                    } else {
                        \Log::warning("WhatsApp notification skipped for bebas - no parent phone for student_id: {$request->student_id}");
                    }
                } else {
                    \Log::info("WhatsApp notification disabled or gateway not configured for bebas");
                }
            } catch (\Exception $e) {
                \Log::error("Failed to send WhatsApp notification for bebas payment: " . $e->getMessage());
                \Log::error("Stack trace: " . $e->getTraceAsString());
                // Jangan gagalkan proses pembayaran jika notifikasi gagal
            }

            // Commit transaction
            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran bebas berhasil diproses!',
                'payment_number' => $paymentNumber,
                'payment_date' => $request->payment_date,
                'latest_transactions' => $latestTransactions
            ]);

        } catch (\Exception $e) {
            // Rollback transaction jika ada error
            \DB::rollback();
            
            \Log::error('Error processing bebas payment: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memproses pembayaran: ' . $e->getMessage()
            ], 500);
        }
    }

    public function deleteTransaction(Request $request)
    {
        try {
            \Log::info('Delete transaction request received:', $request->all());
            \Log::info('Request method: ' . $request->method());
            \Log::info('Request URL: ' . $request->fullUrl());
            \Log::info('Transaction ID type: ' . gettype($request->transaction_id));
            \Log::info('Transaction ID value: ' . $request->transaction_id);
            \Log::info('Student ID type: ' . gettype($request->student_id));
            \Log::info('Student ID value: ' . $request->student_id);
            
            $request->validate([
                'transaction_id' => 'required|integer|min:1',
                'student_id' => 'required|exists:students,student_id'
            ], [
                'transaction_id.required' => 'ID transaksi harus diisi',
                'transaction_id.integer' => 'ID transaksi harus berupa angka',
                'transaction_id.min' => 'ID transaksi tidak valid',
                'student_id.required' => 'ID siswa harus diisi',
                'student_id.exists' => 'Siswa tidak ditemukan'
            ]);

            \Log::info('Validation passed for transaction deletion');

            \Log::info('Validation passed, deleting transaction:', $request->all());

            // Mulai database transaction
            \DB::beginTransaction();

            // Cari transaksi di log_trx
            $transaction = \DB::table('log_trx')
                ->where('log_trx_id', $request->transaction_id)
                ->where('student_student_id', $request->student_id)
                ->first();

            if (!$transaction) {
                \Log::warning('Transaction not found for deletion:', [
                    'transaction_id' => $request->transaction_id,
                    'student_id' => $request->student_id
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan!'
                ], 404);
            }

            \Log::info('Transaction found, proceeding with deletion:', [
                'transaction_id' => $transaction->log_trx_id,
                'transaction_type' => $transaction->category ?? 'unknown',
                'bulan_id' => $transaction->bulan_bulan_id,
                'bebas_pay_id' => $transaction->bebas_pay_bebas_pay_id
            ]);

            // AMBIL DATA TRANSAKSI SEBELUM DIHAPUS UNTUK NOTIFIKASI
            $transactionData = \DB::table('log_trx as lt')
                ->join('students as s', 'lt.student_student_id', '=', 's.student_id')
                ->leftJoin('bulan as b', 'lt.bulan_bulan_id', '=', 'b.bulan_id')
                ->leftJoin('bebas_pay as bp', 'lt.bebas_pay_bebas_pay_id', '=', 'bp.bebas_pay_id')
                ->leftJoin('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                ->leftJoin('pos_pembayaran as pos_bulan', 'p_bulan.pos_pos_id', '=', 'pos_bulan.pos_id')
                ->leftJoin('pos_pembayaran as pos_bebas', 'p_bebas.pos_pos_id', '=', 'pos_bebas.pos_id')
                ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id')
                ->where('lt.log_trx_id', $request->transaction_id)
                ->where('lt.student_student_id', $request->student_id)
                ->select(
                    's.student_full_name as student_name',
                    's.student_parent_phone',
                    's.student_nis as nis',
                    'lt.log_trx_input_date as payment_date',
                    DB::raw('COALESCE(b.bulan_bill, bp.bebas_pay_bill) as amount'),
                    DB::raw('COALESCE(b.bulan_number_pay, bp.bebas_pay_number) as payment_number'),
                    DB::raw('CASE 
                        WHEN b.bulan_id IS NOT NULL THEN CONCAT(pos_bulan.pos_name, "-", 
                            CASE b.month_month_id
                                WHEN 1 THEN "Januari"
                                WHEN 2 THEN "Februari"
                                WHEN 3 THEN "Maret"
                                WHEN 4 THEN "April"
                                WHEN 5 THEN "Mei"
                                WHEN 6 THEN "Juni"
                                WHEN 7 THEN "Juli"
                                WHEN 8 THEN "Agustus"
                                WHEN 9 THEN "September"
                                WHEN 10 THEN "Oktober"
                                WHEN 11 THEN "November"
                                WHEN 12 THEN "Desember"
                                ELSE "Unknown"
                            END, " (", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"), ")"
                        )
                        WHEN bp.bebas_pay_id IS NOT NULL THEN CONCAT(pos_bebas.pos_name, " - ", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                        ELSE "Pembayaran"
                    END as pos_name')
                )
                ->first();

            $paymentType = null;

            // Jika transaksi bulanan
            if ($transaction->bulan_bulan_id) {
                $bulanRecord = \DB::table('bulan')
                    ->where('bulan_id', $transaction->bulan_bulan_id)
                    ->first();

                if ($bulanRecord) {
                    // Reset status pembayaran bulanan
                    \DB::table('bulan')
                        ->where('bulan_id', $transaction->bulan_bulan_id)
                        ->update([
                            'bulan_date_pay' => null,
                            'bulan_number_pay' => null,
                            'bulan_last_update' => now()
                        ]);

                    $paymentType = 'bulanan';
                }
            }
            // Jika transaksi bebas
            elseif ($transaction->bebas_pay_bebas_pay_id) {
                $bebasPayRecord = \DB::table('bebas_pay')
                    ->where('bebas_pay_id', $transaction->bebas_pay_bebas_pay_id)
                    ->first();

                if ($bebasPayRecord) {
                    // Kurangi total pembayaran di tabel bebas
                    \DB::table('bebas')
                        ->where('bebas_id', $bebasPayRecord->bebas_bebas_id)
                        ->decrement('bebas_total_pay', $bebasPayRecord->bebas_pay_bill);

                    // Hapus record di bebas_pay
                    \DB::table('bebas_pay')
                        ->where('bebas_pay_id', $transaction->bebas_pay_bebas_pay_id)
                        ->delete();

                    $paymentType = 'bebas';
                }
            }

            // Hapus record di log_trx
            \DB::table('log_trx')
                ->where('log_trx_id', $request->transaction_id)
                ->delete();

            \Log::info('Transaction record deleted from log_trx');

            \DB::commit();

            \Log::info('Transaction deleted successfully:', [
                'transaction_id' => $request->transaction_id,
                'student_id' => $request->student_id,
                'payment_type' => $paymentType
            ]);

            // Kirim notifikasi WhatsApp setelah transaksi berhasil dihapus
            try {
                $whatsappService = new \App\Services\WhatsAppService();
                $whatsappService->sendTransactionDeletedNotificationDirect(
                    $transactionData,
                    $paymentType
                );
                
                \Log::info('WhatsApp deletion notification sent successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to send WhatsApp deletion notification: ' . $e->getMessage());
                // Tidak throw exception karena penghapusan transaksi tetap berhasil
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus!',
                'payment_type' => $paymentType
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            
            \Log::error('Error deleting transaction: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus transaksi: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Hapus transaksi online (transfer dan payment gateway)
     */
    public function deleteOnlineTransaction(Request $request)
    {
        try {
            \Log::info('Delete online transaction request received:', $request->all());
            
            $request->validate([
                'transaction_id' => 'required|integer',
                'transaction_type' => 'required|in:transfer,online_payment'
            ]);

            \Log::info('Validation passed, deleting online transaction:', $request->all());

            // Mulai database transaction
            \DB::beginTransaction();

            $transaction = null;
            $paymentType = null;

            if ($request->transaction_type === 'transfer') {
                // Cari transaksi di tabel transfer
                $transaction = \DB::table('transfer as t')
                    ->join('students as s', 't.student_id', '=', 's.student_id')
                    ->where('t.transfer_id', $request->transaction_id)
                    ->select('t.*', 's.student_full_name', 's.student_parent_phone', 's.student_nis')
                    ->first();

                if ($transaction) {
                    // Hapus record di transfer
                    \DB::table('transfer')
                        ->where('transfer_id', $request->transaction_id)
                        ->delete();

                    $paymentType = 'transfer';
                }
            } elseif ($request->transaction_type === 'online_payment') {
                // Cari transaksi di tabel transfer
                $transaction = \DB::table('transfer as t')
                    ->join('students as s', 't.student_id', '=', 's.student_id')
                    ->where('t.transfer_id', $request->transaction_id)
                    ->select('t.*', 's.student_full_name', 's.student_parent_phone', 's.student_nis')
                    ->first();

                if ($transaction) {
                    // Hapus record di transfer
                    \DB::table('transfer')
                        ->where('transfer_id', $request->transaction_id)
                        ->delete();

                    $paymentType = 'online_payment';
                }
            }

            if (!$transaction) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transaksi tidak ditemukan!'
                ], 404);
            }

            \DB::commit();

            \Log::info('Online transaction deleted successfully:', [
                'transaction_id' => $request->transaction_id,
                'transaction_type' => $request->transaction_type,
                'payment_type' => $paymentType
            ]);

            // Kirim notifikasi WhatsApp setelah transaksi berhasil dihapus
            try {
                $whatsappService = new \App\Services\WhatsAppService();
                $whatsappService->sendOnlineTransactionDeletedNotification(
                    $transaction,
                    $paymentType
                );
                
                \Log::info('WhatsApp online deletion notification sent successfully');
            } catch (\Exception $e) {
                \Log::error('Failed to send WhatsApp online deletion notification: ' . $e->getMessage());
                // Tidak throw exception karena penghapusan transaksi tetap berhasil
            }

            return response()->json([
                'success' => true,
                'message' => 'Transaksi online berhasil dihapus!',
                'payment_type' => $paymentType
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            
            \Log::error('Error deleting online transaction: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat menghapus transaksi online: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan laporan perpos (bulanan dan bebas)
     */
    public function laporanPerpos(Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $periods = Period::where('school_id', $currentSchoolId)
            ->orderBy('period_start', 'desc')
            ->get();
        $posList = Pos::where('school_id', $currentSchoolId)
            ->orderBy('pos_name')
            ->get();
        $classes = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->orderBy('class_name')
            ->get();
        
        $selectedPeriod = $request->get('period_id');
        $selectedPos = $request->get('pos_id');
        $selectedClass = $request->get('class_id');
        $selectedStatus = $request->get('status');
        $selectedStudentStatus = $request->get('student_status');
        $selectedType = $request->get('type', 'bulanan'); // bulanan atau bebas
        
        $data = null;
        // Set period & pos untuk kebutuhan info di view berdasarkan filter yang dipilih
        $period = $selectedPeriod ? Period::find($selectedPeriod) : null;
        $pos = $selectedPos ? Pos::find($selectedPos) : null;
        
        // Debug: Log filter values
        \Log::info('Laporan Perpos Filter:', [
            'selectedPeriod' => $selectedPeriod,
            'selectedPos' => $selectedPos,
            'selectedClass' => $selectedClass,
            'selectedStatus' => $selectedStatus,
            'selectedStudentStatus' => $selectedStudentStatus,
            'selectedType' => $selectedType
        ]);
        
        if ($selectedPeriod && $selectedPos) {
            
            // Debug: Log pos data
            \Log::info('Pos data for laporan:', [
                'pos_id' => $selectedPos,
                'pos_name' => $pos ? $pos->pos_name : 'null',
                'pos_data' => $pos
            ]);
            
            if ($selectedType === 'bulanan') {
                $data = $this->getBulananData($selectedPeriod, $selectedPos, $selectedClass, $selectedStatus, $selectedStudentStatus);
                \Log::info('Bulanan data count: ' . $data->count());
            } else {
                $data = $this->getBebasData($selectedPeriod, $selectedPos, $selectedClass, $selectedStatus, $selectedStudentStatus);
                \Log::info('Bebas data count: ' . $data->count());
            }
        }
        
        // Display helper: selected period name
        $selectedPeriodName = null;
        if ($period) {
            $selectedPeriodName = $period->period_start . '/' . $period->period_end;
        }

        \App\Helpers\ActivityLogger::log('view', 'laporan-perpos', 'Membuka halaman Laporan Perpos');
        return view('payment.laporan-perpos', compact(
            'periods', 
            'posList', 
            'classes',
            'data', 
            'period', 
            'pos', 
            'selectedPeriod', 
            'selectedPos', 
            'selectedClass',
            'selectedStatus',
            'selectedStudentStatus',
            'selectedType',
            'selectedPeriodName'
        ));
    }
    
    /**
     * Export laporan perpos ke PDF
     */
    public function exportLaporanPerpos(Request $request)
    {
        // Samakan dengan filter di view: periode & pos wajib, type wajib
        $request->validate([
            'period_id' => 'required|exists:periods,period_id',
            'pos_id' => 'required|exists:pos_pembayaran,pos_id',
            'type' => 'required|in:bulanan,bebas'
        ]);
        $period = Period::find($request->period_id);
        $pos = Pos::find($request->pos_id);
        $school = currentSchool() ?? School::first();
        
        // Ambil filter tambahan dari request
        $classId = $request->get('class_id');
        $status = $request->get('status');
        $studentStatus = $request->get('student_status');
        $type = $request->get('type', 'bulanan');
        
        // Debug: Log pos data for export
        \Log::info('Pos data for export:', [
            'pos_id' => $request->pos_id,
            'pos_name' => $pos ? $pos->pos_name : 'null',
            'type' => $request->type,
            'class_id' => $classId,
            'status' => $status,
            'student_status' => $studentStatus
        ]);
        
        // Terapkan filter sama persis dengan view
        if ($type === 'bebas') {
            $data = $this->getBebasData($request->period_id, $request->pos_id, $classId, $status, $studentStatus);
            $view = 'payment.laporan-perpos-bebas-pdf';
        } else {
            $data = $this->getBulananData($request->period_id, $request->pos_id, $classId, $status, $studentStatus);
            $view = 'payment.laporan-perpos-bulanan-pdf';
        }
        
        $pdf = Pdf::loadView($view, compact('data', 'period', 'pos', 'school'));
        
        $filename = 'Laporan_' . ucfirst($type) . '_' . $pos->pos_name . '_' . $period->period_start . '-' . $period->period_end . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Export laporan perpos ke Excel
     */
    public function exportLaporanPerposExcel(Request $request)
    {
        // Samakan dengan filter di view: periode & pos wajib, type wajib
        $request->validate([
            'period_id' => 'required|exists:periods,period_id',
            'pos_id' => 'required|exists:pos_pembayaran,pos_id',
            'type' => 'required|in:bulanan,bebas'
        ]);
        $period = Period::find($request->period_id);
        $pos = Pos::find($request->pos_id);

        $classId = $request->get('class_id');
        $status = $request->get('status');
        $studentStatus = $request->get('student_status');
        $type = $request->get('type', 'bulanan');

        $data = ($type === 'bebas')
            ? $this->getBebasData($request->period_id, $request->pos_id, $classId, $status, $studentStatus)
            : $this->getBulananData($request->period_id, $request->pos_id, $classId, $status, $studentStatus);

        $filename = 'Laporan_' . ucfirst($type) . '_' . $pos->pos_name . '_' . $period->period_start . '-' . $period->period_end . '_' . date('Y-m-d') . '.xlsx';

        $school = currentSchool() ?? School::first();
        $meta = [
            'school_name' => $school->nama_sekolah ?? '',
            'printed_at' => now()->format('d/m/Y H:i'),
            'pos_name' => $pos->pos_name,
            'period_name' => $period->period_start . '/' . $period->period_end,
            'type_label' => ucfirst($type),
            'total_data' => ($type === 'bulanan') ? ($data->groupBy('student_nis')->count() . ' siswa') : ($data->count() . ' siswa'),
        ];

        // Use Maatwebsite Excel export with header meta + styling
        return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\PerposExport($data, $request->type, $meta), $filename);
    }
    
    /**
     * Ambil data pembayaran bulanan
     */
    private function getBulananData($periodId, $posId, $classId = null, $status = null, $studentStatus = null)
    {
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            $query = DB::table('bulan as b')
                ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ;
            
            // Filter berdasarkan school_id
            if ($currentSchoolId) {
                $query->where('p.school_id', $currentSchoolId)
                      ->where('s.school_id', $currentSchoolId);
            }

            // Terapkan filter periode/pos hanya jika diisi
            if ($periodId) {
                $query->where('p.period_period_id', $periodId);
            }
            if ($posId) {
                $query->where('p.pos_pos_id', $posId);
            }
            
            // Filter by class if selected
            if ($classId) {
                $query->where('s.class_class_id', $classId);
            }
            
            // Filter by status if selected
            if ($status) {
                if ($status === 'lunas') {
                    $query->whereNotNull('b.bulan_date_pay');
                } elseif ($status === 'belum_lunas') {
                    $query->whereNull('b.bulan_date_pay');
                }
            }
            
            // Filter by student status if selected
            if ($studentStatus) {
                $query->where('s.student_status', $studentStatus);
            }
            
            $data = $query->select(
                    's.student_nis',
                    's.student_full_name',
                    'c.class_name',
                    'b.month_month_id',
                    'b.bulan_bill',
                    'b.bulan_date_pay',
                    'b.bulan_number_pay',
                    'b.bulan_status',
                    'pos.pos_name'
                )
                ->orderBy('c.class_name')
                ->orderBy('s.student_full_name')
                ->orderBy('b.month_month_id')
                ->get();
            
            \Log::info('Bulanan query executed successfully', [
                'periodId' => $periodId,
                'posId' => $posId,
                'classId' => $classId,
                'status' => $status,
                'studentStatus' => $studentStatus,
                'count' => $data->count()
            ]);
            
            return $data;
        } catch (\Exception $e) {
            \Log::error('Error in getBulananData: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * Ambil data pembayaran bebas
     */
    private function getBebasData($periodId, $posId, $classId = null, $status = null, $studentStatus = null)
    {
        try {
            // Filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            
            $query = DB::table('bebas as be')
                ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                ;
            
            // Filter berdasarkan school_id
            if ($currentSchoolId) {
                $query->where('p.school_id', $currentSchoolId)
                      ->where('s.school_id', $currentSchoolId);
            }

            // Terapkan filter periode/pos hanya jika diisi
            if ($periodId) {
                $query->where('p.period_period_id', $periodId);
            }
            if ($posId) {
                $query->where('p.pos_pos_id', $posId);
            }
            
            // Filter by class if selected
            if ($classId) {
                $query->where('s.class_class_id', $classId);
            }
            
            // Filter by status if selected
            if ($status) {
                if ($status === 'lunas') {
                    $query->whereRaw('be.bebas_total_pay >= be.bebas_bill');
                } elseif ($status === 'belum_lunas') {
                    $query->whereRaw('be.bebas_total_pay < be.bebas_bill');
                }
            }
            
            // Filter by student status if selected
            if ($studentStatus) {
                $query->where('s.student_status', $studentStatus);
            }
            
            $data = $query->select(
                    's.student_nis',
                    's.student_full_name',
                    'c.class_name',
                    'be.bebas_bill',
                    'be.bebas_total_pay',
                    'be.bebas_date_pay',
                    'be.bebas_number_pay',
                    'be.bebas_desc',
                    'pos.pos_name'
                )
                ->orderBy('c.class_name')
                ->orderBy('s.student_full_name')
                ->get();
            
            return $data;
        } catch (\Exception $e) {
            \Log::error('Error in getBebasData: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * Laporan Perkelas
     */
    public function laporanPerkelas(Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $periods = Period::where('school_id', $currentSchoolId)
            ->orderBy('period_start', 'desc')
            ->get();
        $classes = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->orderBy('class_name')
            ->get();
        $posList = DB::table('pos_pembayaran')
            ->where('school_id', $currentSchoolId)
            ->get();
        $months = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
        ];
        
        $selectedPeriod = $request->get('period_id');
        $selectedClass = $request->get('class_id');
        $selectedMonth = $request->get('month');
        $selectedStudentStatus = $request->get('student_status');
        
        $data = null;
        $period = null;
        $class = null;
        
        // Debug: Log filter values
        \Log::info('Laporan Perkelas Filter:', [
            'selectedPeriod' => $selectedPeriod,
            'selectedClass' => $selectedClass,
            'selectedMonth' => $selectedMonth,
            'selectedStudentStatus' => $selectedStudentStatus
        ]);
        
        if ($selectedPeriod && $selectedClass && $selectedMonth) {
            $period = Period::find($selectedPeriod);
            $class = DB::table('class_models')->where('class_id', $selectedClass)->first();
            
            $data = $this->getPerkelasData($selectedPeriod, $selectedClass, $selectedMonth, $selectedStudentStatus);
            \Log::info('Perkelas data count: ' . $data->count());
        }
        
        return view('payment.laporan-perkelas', compact(
            'periods', 
            'classes',
            'months',
            'data', 
            'period', 
            'class', 
            'selectedPeriod', 
            'selectedClass',
            'selectedMonth',
            'selectedStudentStatus',
            'posList'
        ));
    }
    
    /**
     * Ambil data laporan perkelas
     */
    private function getPerkelasData($periodId, $classId, $month, $studentStatus = null)
    {
        try {
            // Ambil semua siswa di kelas tersebut
            $students = DB::table('students as s')
                ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                ->where('s.class_class_id', $classId);
            
            if ($studentStatus !== null) {
                $students->where('s.student_status', $studentStatus);
            }
            
            $students = $students->select('s.student_id', 's.student_nis', 's.student_full_name', 'c.class_name')
                ->orderBy('s.student_full_name')
                ->get();
            
            // Ambil semua pos pembayaran - filter berdasarkan sekolah yang sedang aktif
            $currentSchoolId = currentSchoolId();
            $posQuery = DB::table('pos_pembayaran');
            if ($currentSchoolId) {
                $posQuery->where('school_id', $currentSchoolId);
            }
            $posList = $posQuery->get();
            
            $result = collect();
            
            foreach ($students as $student) {
                $studentData = [
                    'student_nis' => $student->student_nis,
                    'student_full_name' => $student->student_full_name,
                    'class_name' => $student->class_name,
                    'pos_data' => [],
                    'subtotal' => 0
                ];
                
                foreach ($posList as $pos) {
                    $subtotalKekurangan = 0;
                    
                    // Hitung kekurangan bulanan sampai bulan yang dipilih
                    $bulananKekurangan = DB::table('bulan as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->where('b.student_student_id', $student->student_id)
                        ->where('p.period_period_id', $periodId)
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('b.month_month_id', '<=', $month)
                        ->whereNull('b.bulan_date_pay')
                        ->sum('b.bulan_bill');
                    
                    // Hitung kekurangan bebas
                    $bebasKekurangan = DB::table('bebas as be')
                        ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                        ->where('be.student_student_id', $student->student_id)
                        ->where('p.period_period_id', $periodId)
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->whereRaw('be.bebas_total_pay < be.bebas_bill')
                        ->sum(DB::raw('be.bebas_bill - be.bebas_total_pay'));
                    
                    // Cek apakah ada tagihan untuk pos ini
                    $hasBulananTagihan = DB::table('bulan as b')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->where('b.student_student_id', $student->student_id)
                        ->where('p.period_period_id', $periodId)
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->where('b.month_month_id', '<=', $month)
                        ->exists();
                    
                    $hasBebasTagihan = DB::table('bebas as be')
                        ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                        ->where('be.student_student_id', $student->student_id)
                        ->where('p.period_period_id', $periodId)
                        ->where('p.pos_pos_id', $pos->pos_id)
                        ->exists();
                    
                    $subtotalKekurangan = $bulananKekurangan + $bebasKekurangan;
                    
                    // Jika ada tagihan tapi tidak ada kekurangan, berarti lunas
                    if (($hasBulananTagihan || $hasBebasTagihan) && $subtotalKekurangan == 0) {
                    $studentData['pos_data'][$pos->pos_id] = [
                        'pos_name' => $pos->pos_name,
                            'amount' => 0, // 0 untuk menampilkan "Lunas"
                            'status' => 'lunas'
                        ];
                    } else {
                        $studentData['pos_data'][$pos->pos_id] = [
                            'pos_name' => $pos->pos_name,
                            'amount' => $subtotalKekurangan,
                            'status' => $subtotalKekurangan > 0 ? 'belum_lunas' : 'lunas'
                        ];
                    }
                    
                    $studentData['subtotal'] += $subtotalKekurangan;
                }
                
                // Tambahkan semua siswa (termasuk yang sudah lunas)
                    $result->push($studentData);
            }
            
            \Log::info('Perkelas query executed successfully', [
                'periodId' => $periodId,
                'classId' => $classId,
                'month' => $month,
                'studentStatus' => $studentStatus,
                'count' => $result->count()
            ]);
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getPerkelasData: ' . $e->getMessage());
            return collect([]);
        }
    }
    
    /**
     * Export laporan perkelas ke PDF
     */
    public function exportLaporanPerkelas(Request $request)
    {
        $request->validate([
            'period_id' => 'required|exists:periods,period_id',
            'class_id' => 'required|exists:class_models,class_id',
            'month' => 'required|integer|min:1|max:12'
        ]);
        
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $period = Period::where('school_id', $currentSchoolId)
            ->findOrFail($request->period_id);
        $class = DB::table('class_models')
            ->where('class_id', $request->class_id)
            ->where('school_id', $currentSchoolId)
            ->firstOrFail();
        $school = currentSchool() ?? School::first();
        $months = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober', 
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
        ];
        
        $data = $this->getPerkelasData($request->period_id, $request->class_id, $request->month, $request->student_status);
        $posList = DB::table('pos_pembayaran')
            ->where('school_id', $currentSchoolId)
            ->get();
        $selectedMonth = $request->month;
        
        $pdf = Pdf::loadView('payment.laporan-perkelas-pdf', compact(
            'data', 
            'period', 
            'class', 
            'school', 
            'posList', 
            'months',
            'selectedMonth'
        ));
        
        $filename = 'Laporan_Perkelas_' . $class->class_name . '_' . $period->period_start . '-' . $period->period_end . '_' . $months[$request->month] . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
    
    /**
     * Tampilkan laporan rekapitulasi
     */
    public function laporanRekapitulasi(Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return redirect()->route('manage.foundation.dashboard')
                    ->with('error', 'Sekolah belum dipilih. Silakan pilih sekolah terlebih dahulu.');
            }
            abort(403, 'Akses ditolak: Sekolah belum dipilih.');
        }
        
        $data = collect();
        $startDate = null;
        $endDate = null;
        $paymentType = null;
        $posId = null;
        $classId = null;
        
        // Ambil data untuk filter dropdown - filter berdasarkan sekolah yang sedang aktif
        $posList = DB::table('pos_pembayaran')
            ->where('school_id', $currentSchoolId)
            ->orderBy('pos_name')
            ->get();
        $classList = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->orderBy('class_name')
            ->get();
        
        // Hanya butuh tanggal
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $startDate = $request->start_date;
            $endDate = $request->end_date;
            $paymentType = $request->get('payment_type'); // Filter jenis pembayaran
            $posId = $request->get('pos_id'); // Filter pos pembayaran
            $classId = $request->get('class_id'); // Filter kelas
            
            // Debug: Log input parameters
            \Log::info('Laporan Rekapitulasi Input:', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paymentType' => $paymentType,
                'posId' => $posId,
                'classId' => $classId,
                'startDateType' => gettype($startDate),
                'endDateType' => gettype($endDate)
            ]);
            
            // Ambil data real dari database - tanpa filter periode
            // Pass currentSchoolId untuk filter berdasarkan school_id
            $data = $this->getRekapitulasiData(null, $startDate, $endDate, $paymentType, $posId, $classId, $currentSchoolId);
            
            // Debug: Log data untuk troubleshooting
            \Log::info('Laporan Rekapitulasi Debug:', [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paymentType' => $paymentType,
                'posId' => $posId,
                'classId' => $classId,
                'dataCount' => $data->count(),
                'dataIsEmpty' => $data->isEmpty(),
                'dataType' => get_class($data)
            ]);
        }
        
        return view('payment.laporan-rekapitulasi', compact(
            'data', 
            'startDate', 
            'endDate', 
            'paymentType', 
            'posId', 
            'classId',
            'posList',
            'classList'
        ));
    }
    
    /**
     * Ambil data laporan rekapitulasi
     */
    public function getRekapitulasiData($periodId, $startDate, $endDate, $paymentType = null, $posId = null, $classId = null, $currentSchoolId = null)
    {
        try {
            $result = collect();
            
            // Jika currentSchoolId tidak diberikan, ambil dari helper
            if (!$currentSchoolId) {
                $currentSchoolId = currentSchoolId();
            }
            
            \Log::info('Starting getRekapitulasiData with params:', [
                'periodId' => $periodId,
                'startDate' => $startDate,
                'endDate' => $endDate,
                'paymentType' => $paymentType,
                'posId' => $posId,
                'classId' => $classId,
                'currentSchoolId' => $currentSchoolId
            ]);
            
            // Cek apakah ada data di tabel payment
            $paymentCount = DB::table('payment')->count();
            $transferCount = DB::table('transfer')->count();
            $onlinePaymentCount = DB::table('transfer')->where('payment_method', '!=', 'cash')->count();
            $bulanCount = DB::table('bulan')->count();
            
            \Log::info('Database counts:', [
                'payment' => $paymentCount,
                'transfer' => $transferCount,
                'online_payments' => $onlinePaymentCount,
                'bulan' => $bulanCount
            ]);
            
                                    // Ambil data pembayaran bulanan dari tabel bulan (hanya jika tidak ada filter jenis pembayaran atau filter = Tunai)
            if (!$paymentType || $paymentType == 'Tunai') {
                try {
                    $bulanPayments = DB::table('bulan as b')
                        ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                        ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                        ->join('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                        ->whereNotNull('b.bulan_date_pay') // Hanya yang sudah dibayar
                        ->whereRaw('DATE(b.bulan_date_pay) >= ? AND DATE(b.bulan_date_pay) <= ?', [$startDate, $endDate]);
                    
                    // Filter berdasarkan school_id - WAJIB diterapkan
                    // Filter pada students, payment, dan pos_pembayaran untuk memastikan data sesuai
                    if ($currentSchoolId) {
                        $bulanPayments->where('s.school_id', $currentSchoolId)
                                      ->where(function($q) use ($currentSchoolId) {
                                          $q->where('p.school_id', $currentSchoolId)
                                            ->orWhereNull('p.school_id'); // Backward compatibility
                                      })
                                      ->where(function($q) use ($currentSchoolId) {
                                          $q->where('pos.school_id', $currentSchoolId)
                                            ->orWhereNull('pos.school_id'); // Backward compatibility
                                      });
                    }
                    
                    // Filter pos pembayaran
                    if ($posId) {
                        $bulanPayments->where('p.pos_pos_id', $posId);
                    }
                    
                    // Filter kelas
                    if ($classId) {
                        $bulanPayments->where('s.class_class_id', $classId);
                    }
                    
                    // Tambahkan kondisi untuk memastikan hanya pembayaran tunai yang ditampilkan
                    // dengan mengecualikan pembayaran yang dilakukan via transfer
                    $bulanPayments = $bulanPayments->whereNotExists(function($query) use ($startDate, $endDate) {
                            $query->select(DB::raw(1))
                                  ->from('transfer_detail as td')
                                  ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                                  ->whereRaw('td.bulan_id = b.bulan_id')
                                  ->where('td.payment_type', 1) // hanya exclude jika transfer untuk tagihan bulanan
                                  ->where('td.bulan_id', '>', 0) // Pastikan bulan_id valid
                                  ->whereIn('t.status', [1, 2]) // Hanya transfer yang sukses/confirmed
                                  ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate]); // batasi pada periode laporan
                        })
                        ->select(
                            's.student_full_name',
                            'c.class_name',
                            DB::raw("CONCAT(pos.pos_name, '-', CASE 
                                WHEN b.month_month_id = 1 THEN 'Juli'
                                WHEN b.month_month_id = 2 THEN 'Agustus'
                                WHEN b.month_month_id = 3 THEN 'September'
                                WHEN b.month_month_id = 4 THEN 'Oktober'
                                WHEN b.month_month_id = 5 THEN 'November'
                                WHEN b.month_month_id = 6 THEN 'Desember'
                                WHEN b.month_month_id = 7 THEN 'Januari'
                                WHEN b.month_month_id = 8 THEN 'Februari'
                                WHEN b.month_month_id = 9 THEN 'Maret'
                                WHEN b.month_month_id = 10 THEN 'April'
                                WHEN b.month_month_id = 11 THEN 'Mei'
                                WHEN b.month_month_id = 12 THEN 'Juni'
                                ELSE DATE_FORMAT(b.bulan_date_pay, '%M')
                            END, '-2025/2026') as pos_name"),
                            'b.bulan_date_pay as payment_date',
                            'b.bulan_bill as payment_amount',
                            DB::raw("'Tunai' as payment_method"),
                            DB::raw("TIME(b.bulan_date_pay) as payment_time")
                        )
                        ->get();
            
                    \Log::info('Bulan payments query result:', [
                        'count' => $bulanPayments->count(),
                        'sample_dates' => $bulanPayments->pluck('payment_date')->take(5)->toArray(),
                        'pos_names' => $bulanPayments->pluck('pos_name')->unique()->toArray(),
                        'payment_methods' => $bulanPayments->pluck('payment_method')->unique()->toArray()
                    ]);
                    
                    foreach ($bulanPayments as $payment) {
                        $result->push([
                            'student_name' => $payment->student_full_name,
                            'class_name' => $payment->class_name,
                            'pos_name' => $payment->pos_name,
                            'payment_date' => $payment->payment_date,
                            'payment_amount' => $payment->payment_amount,
                            'payment_method' => $payment->payment_method,
                            'payment_time' => $payment->payment_time ?? '00:00:00',
                            'cash_amount' => $payment->payment_amount,
                            'transfer_amount' => 0,
                            'gateway_amount' => 0
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error in bulan payments query: ' . $e->getMessage());
                }
            } else {
                $bulanPayments = collect();
            }
            
            // Ambil data pembayaran bebas (hanya jika tidak ada filter jenis pembayaran atau filter = Tunai)
            if (!$paymentType || $paymentType == 'Tunai') {
                try {
                    $bebasPayments = DB::table('bebas_pay as bp')
                        ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                        ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                        ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                        ->leftJoin('payment as p', 'be.payment_payment_id', '=', 'p.payment_id') // Left join untuk fleksibilitas
                        ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id') // Left join untuk fleksibilitas
                        ->whereNotNull('bp.bebas_pay_input_date') // Hanya yang sudah dibayar
                        ->whereRaw('DATE(bp.bebas_pay_input_date) >= ? AND DATE(bp.bebas_pay_input_date) <= ?', [$startDate, $endDate]);
                    
                    // Filter berdasarkan school_id - WAJIB diterapkan
                    // Filter utama hanya pada students, karena payment dan pos sudah terikat dengan bebas
                    // yang terikat dengan student. Jika student benar, payment dan pos-nya juga benar.
                    if ($currentSchoolId) {
                        // Student harus dari school_id yang aktif (WAJIB)
                        // Ini sudah cukup karena bebas terikat dengan student, dan payment terikat dengan bebas
                        $bebasPayments->where('s.school_id', $currentSchoolId);
                        
                        // Payment dan pos TIDAK perlu difilter karena:
                        // - Jika student sudah benar, bebas yang terikat dengan student juga benar
                        // - Payment terikat dengan bebas, jadi payment juga benar
                        // - Pos terikat dengan payment, jadi pos juga benar
                        // - Filter tambahan bisa menghilangkan data yang seharusnya muncul
                    }
                    
                    // Filter pos pembayaran
                    if ($posId) {
                        $bebasPayments->where('p.pos_pos_id', $posId);
                    }
                    
                    // Filter kelas
                    if ($classId) {
                        $bebasPayments->where('s.class_class_id', $classId);
                    }
                    
                    // Tambahkan kondisi untuk memastikan hanya pembayaran tunai yang ditampilkan
                    // dengan mengecualikan pembayaran yang dilakukan via transfer
                    $bebasPayments = $bebasPayments->whereNotExists(function($query) use ($startDate, $endDate) {
                            $query->select(DB::raw(1))
                                  ->from('transfer_detail as td')
                                  ->join('transfer as t', 'td.transfer_id', '=', 't.transfer_id')
                                  ->whereRaw('td.bebas_id = bp.bebas_bebas_id')
                                  ->where('td.payment_type', 2) // hanya exclude jika transfer untuk tagihan bebas
                                  ->where('td.bebas_id', '>', 0) // Pastikan bebas_id valid
                                  ->whereIn('t.status', [1, 2]) // Hanya transfer yang sukses/confirmed
                                  ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate]); // batasi pada periode laporan
                        })
                        ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                        ->select(
                            's.student_full_name',
                            'c.class_name',
                            DB::raw("CONCAT(COALESCE(pos.pos_name, 'Pembayaran Bebas'), '-', COALESCE(CONCAT(per.period_start, '/', per.period_end), '2025/2026')) as pos_name"),
                            'bp.bebas_pay_input_date as payment_date',
                            'bp.bebas_pay_bill as payment_amount',
                            DB::raw("'Tunai' as payment_method"),
                            DB::raw("TIME(bp.bebas_pay_last_update) as payment_time")
                        )
                        ->get();
                        
                    \Log::info('Bebas payments query result:', [
                        'count' => $bebasPayments->count(),
                        'currentSchoolId' => $currentSchoolId,
                        'sample_dates' => $bebasPayments->pluck('payment_date')->take(5)->toArray(),
                        'pos_names' => $bebasPayments->pluck('pos_name')->unique()->toArray(),
                        'sample_data' => $bebasPayments->take(3)->map(function($item) {
                            return [
                                'student' => $item->student_full_name,
                                'class' => $item->class_name,
                                'pos_name' => $item->pos_name,
                                'amount' => $item->payment_amount
                            ];
                        })->toArray()
                    ]);
                    
                    // Debug: Cek apakah ada data bebas_pay yang tidak muncul
                    if ($bebasPayments->isEmpty() && $currentSchoolId) {
                        $debugCount = DB::table('bebas_pay as bp')
                            ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                            ->where('s.school_id', $currentSchoolId)
                            ->whereNotNull('bp.bebas_pay_input_date')
                            ->whereRaw('DATE(bp.bebas_pay_input_date) >= ? AND DATE(bp.bebas_pay_input_date) <= ?', [$startDate, $endDate])
                            ->count();
                        
                        // Debug lebih detail: cek payment dan pos
                        $debugWithPayment = DB::table('bebas_pay as bp')
                            ->join('bebas as be', 'bp.bebas_bebas_id', '=', 'be.bebas_id')
                            ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                            ->leftJoin('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                            ->where('s.school_id', $currentSchoolId)
                            ->whereNotNull('bp.bebas_pay_input_date')
                            ->whereRaw('DATE(bp.bebas_pay_input_date) >= ? AND DATE(bp.bebas_pay_input_date) <= ?', [$startDate, $endDate])
                            ->select('bp.bebas_pay_id', 'be.payment_payment_id', 'p.payment_id', 'p.school_id as payment_school_id', 'p.pos_pos_id', 'pos.pos_id', 'pos.school_id as pos_school_id')
                            ->get();
                        
                        \Log::warning('Bebas payments is empty but there are bebas_pay records:', [
                            'currentSchoolId' => $currentSchoolId,
                            'startDate' => $startDate,
                            'endDate' => $endDate,
                            'bebas_pay_count_with_student_filter' => $debugCount,
                            'debug_with_payment' => $debugWithPayment->toArray()
                        ]);
                    }
                    
                    foreach ($bebasPayments as $payment) {
                        $result->push([
                            'student_name' => $payment->student_full_name,
                            'class_name' => $payment->class_name,
                            'pos_name' => $payment->pos_name,
                            'payment_date' => $payment->payment_date,
                            'payment_amount' => $payment->payment_amount,
                            'payment_method' => $payment->payment_method,
                            'payment_time' => $payment->payment_time ?? '00:00:00',
                            'cash_amount' => $payment->payment_amount,
                            'transfer_amount' => 0,
                            'gateway_amount' => 0
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error in bebas payments query: ' . $e->getMessage());
                }
            } else {
                $bebasPayments = collect();
            }
            
            // Ambil data transfer bank (hanya jika tidak ada filter jenis pembayaran atau filter = Transfer Bank)
            if (!$paymentType || $paymentType == 'Transfer Bank') {
                try {
                    // Query untuk transfer bank dari tabel transfer_detail
                    $transferPayments = DB::table('transfer as t')
                        ->join('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                        ->join('students as s', 't.student_id', '=', 's.student_id')
                        ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                        ->whereNotNull('t.confirm_pay')
                        ->where('t.status', 1) // Hanya transfer yang sukses
                        ->where('td.payment_type', '!=', 3) // Exclude tabungan transactions dari transfer_detail
                        ->where(function($query) {
                            $query->whereNotIn('t.payment_method', ['midtrans', 'tripay', 'payment_gateway'])
                                  ->orWhere('t.payment_method', '')
                                  ->orWhereNull('t.payment_method');
                        }) // Include transfer bank dan payment method kosong
                        ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate]);
                    
                    // Filter berdasarkan school_id - WAJIB diterapkan
                    if ($currentSchoolId) {
                        $transferPayments->where('s.school_id', $currentSchoolId);
                    }
                    
                    // Filter kelas
                    if ($classId) {
                        $transferPayments->where('s.class_class_id', $classId);
                    }
                    
                    // Join dengan tabel payment, pos, dan periods untuk mendapatkan nama pos dan periode yang benar
                    $transferPayments = $transferPayments
                        ->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                        ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                        ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                        ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                        ->leftJoin('pos_pembayaran as pos', function($join) {
                            $join->on('pos.pos_id', '=', 'p_bulan.pos_pos_id')
                                 ->orOn('pos.pos_id', '=', 'p_bebas.pos_pos_id');
                        })
                        ->leftJoin('periods as per_bulan', 'p_bulan.period_period_id', '=', 'per_bulan.period_id')
                        ->leftJoin('periods as per_bebas', 'p_bebas.period_period_id', '=', 'per_bebas.period_id');
                    
                    // Filter payment dan pos berdasarkan school_id untuk memastikan data sesuai
                    // Ini penting untuk memastikan nama pos yang muncul sesuai dengan school_id
                    if ($currentSchoolId) {
                        $transferPayments->where(function($q) use ($currentSchoolId) {
                            // Filter payment bulanan
                            $q->where(function($subQ) use ($currentSchoolId) {
                                $subQ->where(function($pq) use ($currentSchoolId) {
                                    $pq->where('p_bulan.school_id', $currentSchoolId)
                                       ->orWhereNull('p_bulan.school_id'); // Backward compatibility
                                })
                                ->orWhereNull('p_bulan.payment_id'); // Jika tidak ada payment bulanan
                            })
                            // Filter payment bebas
                            ->where(function($subQ) use ($currentSchoolId) {
                                $subQ->where(function($pq) use ($currentSchoolId) {
                                    $pq->where('p_bebas.school_id', $currentSchoolId)
                                       ->orWhereNull('p_bebas.school_id'); // Backward compatibility
                                })
                                ->orWhereNull('p_bebas.payment_id'); // Jika tidak ada payment bebas
                            })
                            // Filter pos
                            ->where(function($subQ) use ($currentSchoolId) {
                                $subQ->where('pos.school_id', $currentSchoolId)
                                     ->orWhereNull('pos.school_id'); // Backward compatibility
                            });
                        });
                    }
                    
                    // Filter pos pembayaran (untuk transfer, kita perlu join dengan tabel payment untuk mendapatkan pos_id)
                    if ($posId) {
                        $transferPayments->where(function($query) use ($posId) {
                            $query->where('p_bulan.pos_pos_id', $posId)
                                  ->orWhere('p_bebas.pos_pos_id', $posId);
                        });
                    }
                    
                    $transferPayments = $transferPayments
                        // Biarkan semua POS tampil saat "Semua Pos" dipilih
                        ->select(
                            's.student_full_name',
                            'c.class_name',
                            DB::raw('CONCAT(COALESCE(pos.pos_name, td.desc), CASE 
                                WHEN b.month_month_id IS NOT NULL AND b.month_month_id BETWEEN 1 AND 12 THEN CONCAT("-", CASE 
                                    WHEN b.month_month_id = 1 THEN "Juli"
                                    WHEN b.month_month_id = 2 THEN "Agustus"
                                    WHEN b.month_month_id = 3 THEN "September"
                                    WHEN b.month_month_id = 4 THEN "Oktober"
                                    WHEN b.month_month_id = 5 THEN "November"
                                    WHEN b.month_month_id = 6 THEN "Desember"
                                    WHEN b.month_month_id = 7 THEN "Januari"
                                    WHEN b.month_month_id = 8 THEN "Februari"
                                    WHEN b.month_month_id = 9 THEN "Maret"
                                    WHEN b.month_month_id = 10 THEN "April"
                                    WHEN b.month_month_id = 11 THEN "Mei"
                                    WHEN b.month_month_id = 12 THEN "Juni"
                                END, "-", COALESCE(CONCAT(per_bulan.period_start, "/", per_bulan.period_end), "2025/2026"))
                                ELSE CONCAT("-", COALESCE(CONCAT(per_bebas.period_start, "/", per_bebas.period_end), "2025/2026"))
                            END) as pos_name'),
                            't.updated_at as payment_date',
                            'td.subtotal as payment_amount', // Ambil dari transfer_detail.subtotal
                            DB::raw("'Transfer Bank' as payment_method"),
                            DB::raw("TIME(t.updated_at) as payment_time"),
                            't.bill_type'
                        )
                        ->get();
            
                    \Log::info('Transfer payments query result:', [
                        'count' => $transferPayments->count(),
                        'sample_dates' => $transferPayments->pluck('payment_date')->take(5)->toArray(),
                        'bill_types' => $transferPayments->pluck('bill_type')->toArray(),
                        'pos_names' => $transferPayments->pluck('pos_name')->unique()->toArray()
                    ]);
                    
                    foreach ($transferPayments as $payment) {
                        $result->push([
                            'student_name' => $payment->student_full_name,
                            'class_name' => $payment->class_name,
                            'pos_name' => $payment->pos_name,
                            'payment_date' => $payment->payment_date,
                            'payment_amount' => $payment->payment_amount, // Sekarang dari td.subtotal
                            'payment_method' => $payment->payment_method,
                            'payment_time' => $payment->payment_time ?? '00:00:00',
                            'cash_amount' => 0,
                            'transfer_amount' => $payment->payment_amount, // Sekarang dari td.subtotal
                            'gateway_amount' => 0
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error in transfer payments query: ' . $e->getMessage());
                    \Log::error('Stack trace: ' . $e->getTraceAsString());
                }
            } else {
                $transferPayments = collect();
            }
            
            // Ambil data payment gateway (hanya jika tidak ada filter jenis pembayaran atau filter = Payment Gateway)
            if (!$paymentType || $paymentType == 'Payment Gateway') {
                try {
                    // Query untuk payment gateway.
                    // Beberapa transaksi gateway tidak mengisi kolom confirm_pay,
                    // sehingga gunakan SUM(td.subtotal) jika tersedia.
                    $gatewayPayments = DB::table('transfer as t')
                        ->join('students as s', 't.student_id', '=', 's.student_id')
                        ->join('class_models as c', 's.class_class_id', '=', 'c.class_id')
                        ->leftJoin('transfer_detail as td', 't.transfer_id', '=', 'td.transfer_id')
                        ->whereNotNull('t.confirm_pay')
                        // Biarkan semua POS tampil saat "Semua Pos" dipilih
                        ->whereIn('t.payment_method', ['midtrans', 'tripay', 'payment_gateway'])
                        ->whereRaw('DATE(t.updated_at) >= ? AND DATE(t.updated_at) <= ?', [$startDate, $endDate]);
                    
                    // Filter berdasarkan school_id - WAJIB diterapkan
                    if ($currentSchoolId) {
                        $gatewayPayments->where('s.school_id', $currentSchoolId);
                    }
                    
                    // Filter kelas
                    if ($classId) {
                        $gatewayPayments->where('s.class_class_id', $classId);
                    }
                    
                    // Filter pos pembayaran (untuk gateway, kita perlu join dengan tabel payment untuk mendapatkan pos_id)
                    if ($posId) {
                        $gatewayPayments->leftJoin('bulan as b', 'td.bulan_id', '=', 'b.bulan_id')
                                        ->leftJoin('bebas as be', 'td.bebas_id', '=', 'be.bebas_id')
                                        ->leftJoin('payment as p_bulan', 'b.payment_payment_id', '=', 'p_bulan.payment_id')
                                        ->leftJoin('payment as p_bebas', 'be.payment_payment_id', '=', 'p_bebas.payment_id')
                                        ->where(function($query) use ($posId) {
                                            $query->where('p_bulan.pos_pos_id', $posId)
                                                  ->orWhere('p_bebas.pos_pos_id', $posId);
                                        });
                    }
                    
                    $gatewayPayments = $gatewayPayments
                        ->select(
                            's.student_full_name',
                            'c.class_name',
                            DB::raw("'Via Payment Gateway' as pos_name"),
                            't.updated_at as payment_date',
                            DB::raw('COALESCE(SUM(td.subtotal), t.confirm_pay) as payment_amount'),
                            DB::raw("'Payment Gateway' as payment_method"),
                            DB::raw("TIME(t.updated_at) as payment_time")
                        )
                        ->groupBy('s.student_full_name','c.class_name','t.updated_at','t.confirm_pay')
                        ->get();
                    
                    // Gabungkan semua query
                    $gatewayPayments = $gatewayPayments;
            
                    \Log::info('Gateway payments query result:', [
                        'count' => $gatewayPayments->count(),
                        'sample_dates' => $gatewayPayments->pluck('payment_date')->take(5)->toArray()
                    ]);
                    
                    foreach ($gatewayPayments as $payment) {
                        $result->push([
                            'student_name' => $payment->student_full_name,
                            'class_name' => $payment->class_name,
                            'pos_name' => $payment->pos_name,
                            'payment_date' => $payment->payment_date,
                            'payment_amount' => $payment->payment_amount,
                            'payment_method' => $payment->payment_method,
                            'payment_time' => $payment->payment_time ?? '00:00:00',
                            'cash_amount' => 0,
                            'transfer_amount' => 0,
                            'gateway_amount' => $payment->payment_amount
                        ]);
                    }
                } catch (\Exception $e) {
                    \Log::error('Error in gateway payments query: ' . $e->getMessage());
                }
            } else {
                $gatewayPayments = collect();
            }
            
            // Urutkan berdasarkan tanggal, waktu, nama siswa, dan jenis pembayaran untuk konsistensi
            $result = $result->sortBy([
                'payment_date',
                'payment_time',
                'student_name',
                'payment_method'
            ]);
            
            \Log::info('Rekapitulasi final result:', [
                'count' => $result->count(),
                'sample_data' => $result->take(3)->toArray(),
                'pos_names' => $result->pluck('pos_name')->unique()->toArray(),
                'payment_methods' => $result->pluck('payment_method')->unique()->toArray()
            ]);
            
            return $result;
        } catch (\Exception $e) {
            \Log::error('Error in getRekapitulasiData: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return collect([]);
        }
    }
    
    /**
     * Export laporan rekapitulasi ke PDF
     */
    public function exportLaporanRekapitulasi(Request $request)
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date'
        ]);
        
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $school = currentSchool() ?? School::first();
        
        $paymentType = $request->get('payment_type');
        $posId = $request->get('pos_id');
        $classId = $request->get('class_id');
        
        $data = $this->getRekapitulasiData(null, $request->start_date, $request->end_date, $paymentType, $posId, $classId, $currentSchoolId);
        
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        
        // Ambil data untuk filter info - filter berdasarkan sekolah yang sedang aktif
        $posList = DB::table('pos_pembayaran')
            ->where('school_id', $currentSchoolId)
            ->orderBy('pos_name')
            ->get();
        $classList = DB::table('class_models')
            ->where('school_id', $currentSchoolId)
            ->orderBy('class_name')
            ->get();
        
        $pdf = Pdf::loadView('payment.laporan-rekapitulasi-pdf', compact(
            'data', 
            'school',
            'startDate',
            'endDate',
            'paymentType',
            'posId',
            'classId',
            'posList',
            'classList'
        ));
        
        $filename = 'Laporan_Rekapitulasi_' . $request->start_date . '_' . $request->end_date . '_' . date('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    /**
     * Process bebas payment with flexible validation for cash payments
     * This method allows cash payments to bypass strict sequential validation
     */
    public function processBebasPaymentFlexible(Request $request)
    {
        try {
            $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'payment_id' => 'required|exists:payment,payment_id',
                'amount' => 'required|numeric|min:1',
                'description' => 'nullable|string|max:500',
                'payment_date' => 'required|date',
                'payment_method' => 'required|in:cash,savings,tunai,tabungan'
            ]);

            \Log::info('Processing flexible bebas payment:', $request->all());

            // Mulai database transaction
            \DB::beginTransaction();

            // Cek data bebas
            $bebas = \DB::table('bebas')
                ->where('student_student_id', $request->student_id)
                ->where('payment_payment_id', $request->payment_id)
                ->first();

            if (!$bebas) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tagihan bebas tidak ditemukan!'
                ], 422);
            }

            // Hitung sisa tagihan
            $sisa = $bebas->bebas_bill - $bebas->bebas_total_pay;

            // Validasi nominal pembayaran
            if ($request->amount > $sisa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Nominal pembayaran tidak boleh melebihi sisa tagihan!'
                ], 422);
            }

            // Jika pembayaran dengan tabungan, cek saldo
            if ($request->payment_method === 'tabungan' || $request->payment_method === 'savings') {
                // Cek saldo tabungan siswa
                $tabungan = \DB::table('tabungan')
                    ->where('student_student_id', $request->student_id)
                    ->first();
                
                if (!$tabungan) {
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Siswa tidak memiliki rekening tabungan!'
                    ], 422);
                }
                
                $saldoTabungan = (float) $tabungan->saldo;
                
                if ($saldoTabungan < $request->amount) {
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => "⚠️ Saldo Tabungan Tidak Mencukupi!\n\nSaldo tabungan: Rp " . number_format($saldoTabungan, 0, ',', '.') . "\nNominal pembayaran: Rp " . number_format($request->amount, 0, ',', '.') . "\n\nSilakan top up tabungan terlebih dahulu atau pilih metode pembayaran lain."
                    ], 422);
                }
                
                \Log::info('Bebas tabungan payment validation passed:', [
                    'student_id' => $request->student_id,
                    'saldo_tabungan' => $saldoTabungan,
                    'amount' => $request->amount,
                    'sisa_saldo' => $saldoTabungan - $request->amount
                ]);
            }

            // Untuk pembayaran tunai, skip validasi berurutan
            if (in_array($request->payment_method, ['cash', 'tunai'])) {
                \Log::info('Cash payment - skipping sequential validation for bebas payment:', [
                    'student_id' => $request->student_id,
                    'payment_method' => $request->payment_method,
                    'reason' => 'Cash payments are more flexible and can bypass sequential validation'
                ]);
            }

            // Generate nomor pembayaran
            $paymentNumber = 'PAY-BEBAS-FLEX-' . date('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Insert ke tabel bebas_pay
            $bebasPayId = \DB::table('bebas_pay')->insertGetId([
                'bebas_bebas_id' => $bebas->bebas_id,
                'bebas_pay_bill' => $request->amount,
                'bebas_pay_number' => $paymentNumber,
                'bebas_pay_desc' => $request->description,
                'user_user_id' => 1, // Default user ID, sesuaikan dengan sistem auth
                'bebas_pay_input_date' => $request->payment_date,
                'bebas_pay_last_update' => now()
            ]);

            // Update total pembayaran di tabel bebas
            \DB::table('bebas')
                ->where('bebas_id', $bebas->bebas_id)
                ->update([
                    'bebas_total_pay' => $bebas->bebas_total_pay + $request->amount,
                    'bebas_last_update' => now()
                ]);

            // Insert ke log_trx untuk riwayat transaksi
            \DB::table('log_trx')->insert([
                'transaction_date' => $request->payment_date,
                'type' => 'in',
                'category' => 'bebas',
                'description' => $request->description ?: 'Pembayaran Bebas - ' . $paymentNumber,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $paymentNumber,
                'status' => 'success',
                'student_id' => $request->student_id,
                'user_id' => 1,
                'notes' => 'Pembayaran bebas fleksibel - bypass validasi berurutan',
                'created_at' => now(),
                'updated_at' => now()
            ]);

            // Jika pembayaran dengan tabungan, kurangi saldo dan catat mutasi
            if ($request->payment_method === 'tabungan' || $request->payment_method === 'savings') {
                try {
                    // Kurangi saldo tabungan
                    \DB::table('tabungan')
                        ->where('student_student_id', $request->student_id)
                        ->decrement('saldo', $request->amount);
                    
                    // Update timestamp terakhir
                    \DB::table('tabungan')
                        ->where('student_student_id', $request->student_id)
                        ->update(['tabungan_last_update' => now()]);
                    
                    // Catat mutasi tabungan untuk pembayaran bebas
                    \DB::table('log_tabungan')->insert([
                        'student_student_id' => $request->student_id,
                        'tabungan_tabungan_id' => $tabungan->tabungan_id,
                        'log_tabungan_type' => 'out',
                        'log_tabungan_amount' => $request->amount,
                        'log_tabungan_desc' => 'Pembayaran bebas: ' . ($request->description ?: $paymentNumber),
                        'log_tabungan_input_date' => now(),
                        'log_tabungan_last_update' => now()
                    ]);
                    
                    \Log::info('Tabungan deduction successful for bebas payment:', [
                        'student_id' => $request->student_id,
                        'amount_deducted' => $request->amount,
                        'remaining_balance' => $tabungan->saldo - $request->amount
                    ]);
                } catch (\Exception $e) {
                    \Log::error('Error deducting tabungan for bebas payment: ' . $e->getMessage());
                    \DB::rollback();
                    return response()->json([
                        'success' => false,
                        'message' => 'Gagal memproses pembayaran dengan tabungan: ' . $e->getMessage()
                    ], 500);
                }
            }

            // Commit transaction
            \DB::commit();

            \Log::info('Flexible bebas payment processed successfully:', [
                'student_id' => $request->student_id,
                'payment_id' => $request->payment_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'payment_number' => $paymentNumber
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Pembayaran bebas berhasil diproses!',
                'data' => [
                    'payment_number' => $paymentNumber,
                    'amount' => $request->amount,
                    'payment_date' => $request->payment_date,
                    'sisa_tagihan' => $sisa - $request->amount
                ]
            ]);

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error processing flexible bebas payment: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses pembayaran bebas: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Process multiple cash payments (bulanan and bebas) in a single transaction
     * Request payload example:
     * {
     *   student_id: number,
     *   payment_method: "cash",
     *   payment_date?: string(YYYY-MM-DD),
     *   items: [
     *     { type: "bulanan", payment_id: number, month_id: number, amount: number, description?: string },
     *     { type: "bebas", payment_id: number, amount: number, description?: string }
     *   ]
     * }
     */
    public function processMultiCashPayment(Request $request)
    {
        // Filter berdasarkan sekolah yang sedang aktif
        $currentSchoolId = currentSchoolId();
        
        $user = auth()->user();
        if (!$currentSchoolId) {
            if (in_array($user->role, ['superadmin', 'admin_yayasan'])) {
                return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
            }
            return response()->json(['success' => false, 'message' => 'Sekolah belum dipilih'], 403);
        }

        try {
            \Log::info('Starting multi cash payment process:', [
                'request_data' => $request->all(),
                'request_headers' => $request->headers->all(),
                'current_school_id' => $currentSchoolId
            ]);

            $request->validate([
                'student_id' => 'required|exists:students,student_id',
                'payment_method' => 'required|in:cash,tunai',
                'payment_date' => 'nullable|date',
                'items' => 'required|array|min:1',
                'items.*.type' => 'required|in:bulanan,bebas',
                'items.*.payment_id' => 'required|integer',
                'items.*.amount' => 'required|numeric|min:0',
                'items.*.month_id' => 'nullable|integer|min:1|max:12',
                'items.*.description' => 'nullable|string|max:500'
            ]);

            // Validasi bahwa siswa milik sekolah yang sedang aktif
            $student = \App\Models\Student::where('student_id', $request->student_id)
                ->where('school_id', $currentSchoolId)
                ->first();
                
            if (!$student) {
                return response()->json([
                    'success' => false,
                    'message' => 'Siswa tidak ditemukan atau tidak memiliki akses'
                ], 404);
            }

            \Log::info('Validation passed for multi cash payment');

            $nowDate = $request->payment_date ?: date('Y-m-d');
            $paymentNumber = 'PAY-' . date('Ymd') . '-' . str_pad(rand(1, 999999), 6, '0', STR_PAD_LEFT);

            \DB::beginTransaction();

            $studentId = (int) $request->student_id;
            $method = $request->payment_method;
            $items = $request->items;

            $processed = [];
            $totalAmount = 0;

            foreach ($items as $idx => $item) {
                try {
                    \Log::info("Processing item {$idx}:", $item);
                    
                    $type = $item['type'];
                    $amount = (float) $item['amount'];
                    $paymentId = (int) $item['payment_id'];
                    $description = $item['description'] ?? null;

                if ($amount <= 0) {
                    $processed[] = [
                        'index' => $idx,
                        'type' => $type,
                        'status' => 'skipped',
                        'reason' => 'Amount must be > 0'
                    ];
                    continue;
                }

                if ($type === 'bulanan') {
                    $monthId = (int) ($item['month_id'] ?? 0);
                    if ($monthId < 1 || $monthId > 12) {
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Invalid month_id for bulanan item.'
                        ], 422);
                    }

                    // Validasi bahwa payment milik sekolah yang sedang aktif
                    $payment = \App\Models\Payment::where('payment_id', $paymentId)
                        ->where('school_id', $currentSchoolId)
                        ->first();
                        
                    if (!$payment) {
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Jenis pembayaran tidak ditemukan atau tidak memiliki akses untuk item index ' . $idx
                        ], 422);
                    }

                    // Pastikan record bulan ada
                    $bulan = \DB::table('bulan')
                        ->where('student_student_id', $studentId)
                        ->where('payment_payment_id', $paymentId)
                        ->where('month_month_id', $monthId)
                        ->first();

                    if (!$bulan) {
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Data bulanan tidak ditemukan untuk item index ' . $idx
                        ], 422);
                    }

                    if (!is_null($bulan->bulan_date_pay)) {
                        // Sudah dibayar, lewati item ini
                        $processed[] = [
                            'index' => $idx,
                            'type' => 'bulanan',
                            'payment_id' => $paymentId,
                            'month_id' => $monthId,
                            'status' => 'skipped',
                            'reason' => 'Already paid'
                        ];
                        continue;
                    }

                    // Update pembayaran bulanan
                    \DB::table('bulan')
                        ->where('bulan_id', $bulan->bulan_id)
                        ->update([
                            'bulan_date_pay' => $nowDate,
                            'bulan_number_pay' => $paymentNumber,
                            'bulan_last_update' => now()
                        ]);

                    // Log transaksi untuk bulanan (mengikuti skema log_trx yang ada) - cek duplikasi dulu
                    $existingLogTrx = \DB::table('log_trx')
                        ->where('bulan_bulan_id', $bulan->bulan_id)
                        ->where('student_student_id', $studentId)
                        ->first();
                    
                    if (!$existingLogTrx) {
                        \DB::table('log_trx')->insert([
                            'student_student_id' => $studentId,
                            'bulan_bulan_id' => $bulan->bulan_id,
                            'bebas_pay_bebas_pay_id' => null,
                            'log_trx_input_date' => now(),
                            'log_trx_last_update' => now()
                        ]);
                        
                        \Log::info('Multi cash payment - Transaction logged to log_trx:', [
                            'student_id' => $studentId,
                            'bulan_id' => $bulan->bulan_id,
                            'payment_number' => $paymentNumber
                        ]);
                    } else {
                        \Log::warning('Multi cash payment - Duplicate log_trx entry prevented:', [
                            'student_id' => $studentId,
                            'bulan_id' => $bulan->bulan_id,
                            'payment_number' => $paymentNumber,
                            'existing_log_trx_id' => $existingLogTrx->log_trx_id
                        ]);
                    }

                    $processed[] = [
                        'index' => $idx,
                        'type' => 'bulanan',
                        'payment_id' => $paymentId,
                        'month_id' => $monthId,
                        'bulan_id' => $bulan->bulan_id,
                        'status' => 'success',
                        'amount' => $amount
                    ];

                    $totalAmount += $amount;
                } elseif ($type === 'bebas') {
                    // Validasi bahwa payment milik sekolah yang sedang aktif
                    $payment = \App\Models\Payment::where('payment_id', $paymentId)
                        ->where('school_id', $currentSchoolId)
                        ->first();
                        
                    if (!$payment) {
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Jenis pembayaran tidak ditemukan atau tidak memiliki akses untuk item index ' . $idx
                        ], 422);
                    }

                    // Ambil record bebas untuk siswa & payment
                    $bebas = \DB::table('bebas')
                        ->where('student_student_id', $studentId)
                        ->where('payment_payment_id', $paymentId)
                        ->first();

                    if (!$bebas) {
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Data tagihan bebas tidak ditemukan untuk item index ' . $idx
                        ], 422);
                    }

                    $sisa = ((float) $bebas->bebas_bill) - ((float) $bebas->bebas_total_pay);
                    if ($amount > $sisa) {
                        \DB::rollback();
                        return response()->json([
                            'success' => false,
                            'message' => 'Nominal pembayaran bebas melebihi sisa tagihan pada item index ' . $idx
                        ], 422);
                    }

                    // Insert bebas_pay
                    $bebasPayId = \DB::table('bebas_pay')->insertGetId([
                        'bebas_bebas_id' => $bebas->bebas_id,
                        'bebas_pay_bill' => $amount,
                        'bebas_pay_number' => $paymentNumber,
                        'bebas_pay_desc' => $description,
                        'user_user_id' => 1,
                        'bebas_pay_input_date' => $nowDate,
                        'bebas_pay_last_update' => now()
                    ]);

                    // Update total bayar bebas
                    \DB::table('bebas')
                        ->where('bebas_id', $bebas->bebas_id)
                        ->update([
                            'bebas_total_pay' => ((float) $bebas->bebas_total_pay) + $amount,
                            'bebas_last_update' => now()
                        ]);

                    // Log transaksi untuk bebas (mengikuti skema log_trx yang ada)
                    \DB::table('log_trx')->insert([
                        'student_student_id' => $studentId,
                        'bulan_bulan_id' => null,
                        'bebas_pay_bebas_pay_id' => $bebasPayId,
                        'log_trx_input_date' => now(),
                        'log_trx_last_update' => now()
                    ]);

                    $processed[] = [
                        'index' => $idx,
                        'type' => 'bebas',
                        'payment_id' => $paymentId,
                        'bebas_id' => $bebas->bebas_id,
                        'status' => 'success',
                        'amount' => $amount
                    ];

                    $totalAmount += $amount;
                }
                } catch (\Exception $itemException) {
                    \Log::error("Error processing item {$idx}: " . $itemException->getMessage(), [
                        'item' => $item,
                        'error_trace' => $itemException->getTraceAsString()
                    ]);
                    $processed[] = [
                        'index' => $idx,
                        'type' => $item['type'] ?? 'unknown',
                        'status' => 'error',
                        'reason' => 'Error processing item: ' . $itemException->getMessage()
                    ];
                }
            }

            \DB::commit();

            // Kirim notifikasi WhatsApp rekap multi pembayaran tunai (jika diaktifkan)
            try {
                    $gateway = \DB::table('setup_gateways')->first();
                if ($gateway && $gateway->enable_wa_notification && $gateway->apikey_wagateway && $gateway->url_wagateway) {
                    $student = \DB::table('students')->where('student_id', $studentId)->first();
                    if ($student && $student->student_parent_phone) {
                        $wa = new \App\Services\WhatsAppService();
                        
                        // Ambil school_id dari siswa yang melakukan pembayaran (lebih akurat daripada dari session)
                        $studentSchoolId = $student->school_id ?? $currentSchoolId;

                        // Bangun ringkasan item untuk pesan
                        $summaryLines = [];
                        foreach ($processed as $it) {
                            if ($it['status'] !== 'success') { continue; }
                            
                            try {
                                if ($it['type'] === 'bulanan') {
                                    // Ambil data pos dan periode untuk bulanan dari tabel pos_pembayaran
                                    // Filter berdasarkan school_id dari siswa untuk memastikan data sesuai
                                    $paymentData = \DB::table('payment as p')
                                        ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                                        ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                                        ->where('p.payment_id', $it['payment_id'])
                                        ->where('p.school_id', $studentSchoolId) // Filter by school_id dari siswa
                                        ->select('pos.pos_name', 'per.period_start', 'per.period_end')
                                        ->first();
                                    
                                    \Log::info("Payment data for bulanan:", [
                                        'payment_id' => $it['payment_id'],
                                        'student_school_id' => $studentSchoolId,
                                        'data' => $paymentData
                                    ]);
                                    
                                    if ($paymentData && $paymentData->pos_name) {
                                        $posName = $paymentData->pos_name;
                                        $periodInfo = $paymentData->period_start && $paymentData->period_end ? 
                                            $paymentData->period_start . '/' . $paymentData->period_end : '2025/2026';
                                        $monthName = $this->getMonthName($it['month_id'] ?? 1);
                                        $summaryLines[] = "• {$posName} - {$periodInfo} ({$monthName}) - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                    } else {
                                        // Fallback: ambil data dari tabel bulan jika payment data tidak ada
                                        // Join dengan students untuk memastikan school_id sesuai dengan siswa yang melakukan pembayaran
                                        $bulanData = \DB::table('bulan as b')
                                            ->join('students as s', 'b.student_student_id', '=', 's.student_id')
                                            ->leftJoin('payment as p', 'b.payment_payment_id', '=', 'p.payment_id')
                                            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                                            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                                            ->where('b.bulan_id', $it['bulan_id'] ?? null)
                                            ->where('s.student_id', $studentId) // Pastikan bulan milik siswa yang benar
                                            ->where('p.school_id', $studentSchoolId) // Filter by school_id dari siswa
                                            ->select('pos.pos_name', 'per.period_start', 'per.period_end')
                                            ->first();
                                        
                                        if ($bulanData && $bulanData->pos_name) {
                                            $posName = $bulanData->pos_name;
                                            $periodInfo = $bulanData->period_start && $bulanData->period_end ? 
                                                $bulanData->period_start . '/' . $bulanData->period_end : '2025/2026';
                                            $monthName = $this->getMonthName($it['month_id'] ?? 1);
                                            $summaryLines[] = "• {$posName} - {$periodInfo} ({$monthName}) - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                        } else {
                                            $monthName = $this->getMonthName($it['month_id'] ?? 1);
                                            $summaryLines[] = "• Pembayaran Bulanan ({$monthName}) - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                        }
                                    }
                                } elseif ($it['type'] === 'bebas') {
                                    // Ambil data pos dan periode untuk bebas dari tabel bebas
                                    // Gunakan bebas_id yang sudah tersimpan di processed untuk mendapatkan data yang benar
                                    // Join dengan students untuk memastikan school_id sesuai dengan siswa yang melakukan pembayaran
                                    $bebasData = \DB::table('bebas as be')
                                        ->join('students as s', 'be.student_student_id', '=', 's.student_id')
                                        ->join('payment as p', 'be.payment_payment_id', '=', 'p.payment_id')
                                        ->join('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                                        ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                                        ->where('be.bebas_id', $it['bebas_id'] ?? null)
                                        ->where('s.student_id', $studentId) // Pastikan bebas milik siswa yang benar
                                        ->where('p.school_id', $studentSchoolId) // Filter by school_id dari siswa
                                        ->select('pos.pos_name', 'per.period_start', 'per.period_end')
                                        ->first();
                                    
                                    \Log::info("Bebas data for notification:", [
                                        'bebas_id' => $it['bebas_id'] ?? null,
                                        'payment_id' => $it['payment_id'] ?? null,
                                        'student_id' => $studentId,
                                        'student_school_id' => $studentSchoolId,
                                        'data' => $bebasData
                                    ]);
                                    
                                    if ($bebasData && $bebasData->pos_name) {
                                        $posName = $bebasData->pos_name;
                                        $periodInfo = $bebasData->period_start && $bebasData->period_end ? 
                                            $bebasData->period_start . '/' . $bebasData->period_end : '2025/2026';
                                        $summaryLines[] = "• {$posName} - {$periodInfo} - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                    } else {
                                        // Fallback: ambil data dari payment_id jika bebas_id tidak ada
                                        // Filter berdasarkan school_id dari siswa untuk memastikan data sesuai
                                        $paymentData = \DB::table('payment as p')
                                            ->leftJoin('pos_pembayaran as pos', 'p.pos_pos_id', '=', 'pos.pos_id')
                                            ->leftJoin('periods as per', 'p.period_period_id', '=', 'per.period_id')
                                            ->where('p.payment_id', $it['payment_id'] ?? null)
                                            ->where('p.school_id', $studentSchoolId) // Filter by school_id dari siswa
                                            ->select('pos.pos_name', 'per.period_start', 'per.period_end')
                                            ->first();
                                        
                                        if ($paymentData && $paymentData->pos_name) {
                                            $posName = $paymentData->pos_name;
                                            $periodInfo = $paymentData->period_start && $paymentData->period_end ? 
                                                $paymentData->period_start . '/' . $paymentData->period_end : '2025/2026';
                                            $summaryLines[] = "• {$posName} - {$periodInfo} - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                        } else {
                                            $summaryLines[] = "• Pembayaran Bebas - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                        }
                                    }
                                }
                            } catch (\Exception $e) {
                                \Log::error("Error building summary for item: " . $e->getMessage(), [
                                    'item' => $it,
                                    'error_trace' => $e->getTraceAsString()
                                ]);
                                // Fallback ke format sederhana
                                if ($it['type'] === 'bulanan') {
                                    $monthName = $this->getMonthName($it['month_id'] ?? 1);
                                    $summaryLines[] = "• Pembayaran Bulanan ({$monthName}) - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                } else {
                                    $summaryLines[] = "• Pembayaran Bebas - Rp " . number_format($it['amount'] ?? 0, 0, ',', '.');
                                }
                            }
                        }
                        $summary = implode("\n", $summaryLines);

                        \Log::info("Building WhatsApp message for multi cash payment:", [
                            'student_id' => $studentId,
                            'student_name' => $student->student_full_name,
                            'phone' => $student->student_parent_phone,
                            'summary_lines' => $summaryLines,
                            'total_amount' => $totalAmount,
                            'processed_items' => $processed
                        ]);

                        // Gunakan helper existing untuk kas tunai per item jika perlu; di sini kirim satu pesan rekap sederhana
                        $phone = $student->student_parent_phone;
                        $formattedPhone = $wa->formatPhoneNumber($phone);

                        \Log::info("Formatted phone number:", [
                            'original' => $phone,
                            'formatted' => $formattedPhone
                        ]);

                        $message = "✅ *PEMBAYARAN TUNAI BERHASIL*\n\n" .
                                   "👤 *Nama:* {$student->student_full_name}\n" .
                                   "🆔 *NIS:* {$student->student_nis}\n" .
                                   "📄 *No. Pembayaran:* {$paymentNumber}\n" .
                                   "📅 *Tanggal:* " . date('d/m/Y H:i') . "\n" .
                                   "💰 *Total:* Rp " . number_format($totalAmount, 0, ',', '.') . "\n\n" .
                                   "📋 *Rincian Pembayaran:*\n" . $summary . "\n\n" .
                                   "💵 *Metode:* Tunai\n" .
                                   "🙏 *Terima kasih atas kepercayaannya.*";

                        \Log::info("WhatsApp message content:", [
                            'message' => $message,
                            'message_length' => strlen($message)
                        ]);

                        // Kirim pesan
                        $result = $wa->sendMessage($formattedPhone, $message);
                        \Log::info("WhatsApp send result:", [
                            'success' => $result,
                            'phone' => $formattedPhone
                        ]);
                    } else {
                        \Log::warning("WhatsApp notification skipped for multi cash payment - no parent phone for student_id: {$studentId}");
                    }
                } else {
                    \Log::info("WhatsApp notification skipped for multi cash payment - missing configuration or disabled");
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send WA notification for multi cash payment: ' . $e->getMessage(), [
                    'student_id' => $studentId,
                    'payment_number' => $paymentNumber,
                    'error_trace' => $e->getTraceAsString()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Multi pembayaran tunai berhasil diproses',
                'payment_number' => $paymentNumber,
                'total_amount' => $totalAmount,
                'processed' => $processed
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            \DB::rollback();
            \Log::error('Validation error in multi cash payment:', [
                'errors' => $e->errors(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', array_flatten($e->errors())),
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error('Error processing multi cash payment: ' . $e->getMessage(), [
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Gagal memproses multi pembayaran tunai: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get month name by month ID
     * Urutan: 1 = Juli, 2 = Agustus, 3 = September, dst
     */
    private function getMonthName($monthId)
    {
        $months = [
            1 => 'Juli', 2 => 'Agustus', 3 => 'September', 4 => 'Oktober',
            5 => 'November', 6 => 'Desember', 7 => 'Januari', 8 => 'Februari',
            9 => 'Maret', 10 => 'April', 11 => 'Mei', 12 => 'Juni'
        ];
        
        return $months[$monthId] ?? 'Bulan ' . $monthId;
    }

} 