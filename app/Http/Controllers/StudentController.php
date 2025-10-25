<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\ClassModel;
use App\Models\Major;
use App\Imports\StudentsImport;
use App\Exports\StudentsTemplateExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\StudentsExport;

class StudentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Student::with(['class', 'major']);

        // Filter by NIS/Nama
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('student_nis', 'LIKE', "%{$search}%")
                  ->orWhere('student_full_name', 'LIKE', "%{$search}%")
                  ->orWhere('student_nisn', 'LIKE', "%{$search}%");
            });
        }

        // Filter by Kelas
        if ($request->filled('class_id')) {
            $query->where('class_class_id', $request->class_id);
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('student_status', $request->status);
        }

        $students = $query->orderBy('student_full_name')->paginate(30);
        
        // Get classes for filter dropdown
        $classes = ClassModel::orderBy('class_name')->get();

        return view('students.index', compact('students', 'classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $classes = ClassModel::orderBy('class_name')->get();
        $majors = Major::orderBy('majors_name')->get();
        return view('students.create', compact('classes', 'majors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'student_nis' => 'required|string|max:45|unique:students,student_nis',
            'student_nisn' => 'nullable|string|max:45|unique:students,student_nisn',
            'student_password' => 'required|string|max:100',
            'student_full_name' => 'required|string|max:255',
            'student_gender' => 'required|in:L,P',
            'student_born_place' => 'required|string|max:45',
            'student_born_date' => 'required|date',
            'student_phone' => 'nullable|string|max:45',
            'student_hobby' => 'nullable|string|max:100',
            'student_address' => 'nullable|string',
            'student_name_of_mother' => 'nullable|string|max:255',
            'student_name_of_father' => 'nullable|string|max:255',
            'student_parent_phone' => 'required|string|max:45',
            'class_class_id' => 'required|exists:class_models,class_id',
            'majors_majors_id' => 'nullable|exists:majors,majors_id',
            'student_status' => 'boolean'
        ]);

        $data = $request->all();
        
        // Set nilai default untuk field yang disembunyikan
        $data['student_nisn'] = null;
        $data['student_phone'] = null;
        $data['student_hobby'] = null;
        $data['student_address'] = null;
        $data['student_name_of_mother'] = null;
        $data['student_name_of_father'] = null;
        $data['majors_majors_id'] = null;
        
        // Hash password
        $data['student_password'] = Hash::make($data['student_password']);

        Student::create($data);

        return redirect()->route('students.index')
            ->with('success', 'Data peserta didik berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(Student $student)
    {
        $student->load(['class', 'major']);
        return view('students.show', compact('student'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Student $student)
    {
        $classes = ClassModel::orderBy('class_name')->get();
        $majors = Major::orderBy('majors_name')->get();
        return view('students.edit', compact('student', 'classes', 'majors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Student $student)
    {
        $request->validate([
            'student_nis' => 'required|string|max:45|unique:students,student_nis,' . $student->student_id . ',student_id',
            'student_nisn' => 'nullable|string|max:45|unique:students,student_nisn,' . $student->student_id . ',student_id',
            'student_full_name' => 'required|string|max:255',
            'student_gender' => 'required|in:L,P',
            'student_born_place' => 'required|string|max:45',
            'student_born_date' => 'required|date',
            'student_phone' => 'nullable|string|max:45',
            'student_hobby' => 'nullable|string|max:100',
            'student_address' => 'nullable|string',
            'student_name_of_mother' => 'nullable|string|max:255',
            'student_name_of_father' => 'nullable|string|max:255',
            'student_parent_phone' => 'required|string|max:45',
            'class_class_id' => 'required|exists:class_models,class_id',
            'majors_majors_id' => 'nullable|exists:majors,majors_id',
            'student_status' => 'boolean'
        ]);

        $data = $request->all();
        $data['student_status'] = $request->has('student_status') ? 1 : 0;
        // Set nilai default untuk field yang disembunyikan (jika belum ada nilai)
        if (!isset($data['student_nisn'])) $data['student_nisn'] = null;
        if (!isset($data['student_phone'])) $data['student_phone'] = null;
        if (!isset($data['student_hobby'])) $data['student_hobby'] = null;
        if (!isset($data['student_address'])) $data['student_address'] = null;
        if (!isset($data['student_name_of_mother'])) $data['student_name_of_mother'] = null;
        if (!isset($data['student_name_of_father'])) $data['student_name_of_father'] = null;
        if (!isset($data['majors_majors_id'])) $data['majors_majors_id'] = null;
        // Hash password jika ada dan berubah
        if (!empty($data['student_password'])) {
            $data['student_password'] = Hash::make($data['student_password']);
        } else {
            unset($data['student_password']);
        }
        $student->update($data);

        return redirect()->route('students.index')
            ->with('success', 'Data peserta didik berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Student $student)
    {
        $student->delete();

        return redirect()->route('students.index')
            ->with('success', 'Data peserta didik berhasil dihapus!');
    }

    /**
     * Bulk delete students
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'selected_ids' => 'required|string'
        ]);

        try {
            $selectedIds = json_decode($request->selected_ids, true);
            
            if (!is_array($selectedIds) || empty($selectedIds)) {
                return redirect()->back()
                    ->with('error', 'Tidak ada data yang dipilih untuk dihapus');
            }

            $deletedCount = Student::whereIn('student_id', $selectedIds)->delete();

            return redirect()->route('students.index')
                ->with('success', "Berhasil menghapus {$deletedCount} data peserta didik");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus data: ' . $e->getMessage());
        }
    }

    /**
     * Show import form
     */
    public function importForm()
    {
        return view('students.import');
    }

    /**
     * Import students from Excel file
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:2048'
        ]);

        try {
            $import = new StudentsImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "Import selesai. Berhasil: {$successCount}, Gagal: {$errorCount}";
            
            if (!empty($errors)) {
                // Batasi jumlah error yang ditampilkan
                $maxErrors = 10;
                $displayErrors = array_slice($errors, 0, $maxErrors);
                $remainingErrors = count($errors) - $maxErrors;
                
                $message .= "\n\nError yang terjadi:\n" . implode("\n", $displayErrors);
                
                if ($remainingErrors > 0) {
                    $message .= "\n\n... dan {$remainingErrors} error lainnya.";
                }
            }

            if ($successCount > 0) {
                return redirect()->route('students.index')
                    ->with('success', $message);
            } else {
                return redirect()->back()
                    ->with('error', $message)
                    ->withInput();
            }

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat import: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Download template Excel
     */
    public function downloadTemplate()
    {
        return Excel::download(new StudentsTemplateExport(), 'template_import_siswa.xlsx');
    }

    /**
     * Export students data to Excel
     */
    public function export(Request $request)
    {
        $search = $request->get('search');
        $classId = $request->get('class_id');
        $status = $request->get('status');

        $fileName = 'Data_Peserta_Didik_' . date('Y-m-d_H-i-s');
        
        // Add filter info to filename if any
        if ($search || $classId || $status !== null) {
            $fileName .= '_Filtered';
        }
        
        $fileName .= '.xlsx';

        return Excel::download(
            new StudentsExport($search, $classId, $status), 
            $fileName
        );
    }

    /**
     * Show move class page
     */
    public function moveClass()
    {
        $classes = ClassModel::orderBy('class_name')->get();
        return view('students.move-class', compact('classes'));
    }

    /**
     * Get students by class for move class
     */
    public function getStudentsByClass(Request $request)
    {
        $classId = $request->get('class_id');
        $students = Student::where('class_class_id', $classId)
                          ->where('student_status', 1)
                          ->orderBy('student_full_name')
                          ->get(['student_id', 'student_nis', 'student_full_name']);
        return response()->json($students);
    }

    /**
     * Process move class
     */
    public function processMoveClass(Request $request)
    {
        $request->validate([
            'from_class_id' => 'required|exists:class_models,class_id',
            'to_class_id' => 'required|exists:class_models,class_id|different:from_class_id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,student_id'
        ], [
            'from_class_id.required' => 'Kelas asal harus dipilih',
            'to_class_id.required' => 'Kelas tujuan harus dipilih',
            'to_class_id.different' => 'Kelas tujuan harus berbeda dengan kelas asal',
            'student_ids.required' => 'Pilih minimal satu siswa',
            'student_ids.min' => 'Pilih minimal satu siswa'
        ]);

        try {
            $fromClass = ClassModel::findOrFail($request->from_class_id);
            $toClass = ClassModel::findOrFail($request->to_class_id);
            
            $studentIds = $request->student_ids;
            $students = Student::whereIn('student_id', $studentIds)
                              ->where('class_class_id', $request->from_class_id)
                              ->get();

            if ($students->count() !== count($studentIds)) {
                return redirect()->back()->with('error', 'Beberapa siswa tidak ditemukan di kelas asal');
            }

            // Update students class
            Student::whereIn('student_id', $studentIds)->update([
                'class_class_id' => $request->to_class_id
            ]);

            $studentCount = count($studentIds);
            $message = "Berhasil memindahkan {$studentCount} siswa dari kelas {$fromClass->class_name} ke kelas {$toClass->class_name}";

            return redirect()->route('students.move-class')->with('success', $message);

        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memindahkan siswa: ' . $e->getMessage());
        }
    }

    public function graduate()
    {
        $classes = ClassModel::orderBy('class_name')->get();
        return view('students.graduate', compact('classes'));
    }

    public function processGraduate(Request $request)
    {
        $request->validate([
            'class_id' => 'required|exists:class_models,class_id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,student_id'
        ], [
            'class_id.required' => 'Kelas harus dipilih',
            'student_ids.required' => 'Pilih minimal satu siswa',
            'student_ids.min' => 'Pilih minimal satu siswa'
        ]);

        try {
            $class = ClassModel::findOrFail($request->class_id);
            $studentIds = $request->student_ids;
            $students = Student::whereIn('student_id', $studentIds)
                              ->where('class_class_id', $request->class_id)
                              ->get();

            if ($students->count() !== count($studentIds)) {
                return redirect()->back()->with('error', 'Beberapa siswa tidak ditemukan di kelas yang dipilih');
            }

            // Update status siswa menjadi tidak aktif (lulus)
            Student::whereIn('student_id', $studentIds)->update([
                'student_status' => 0
            ]);

            $studentCount = count($studentIds);
            $message = "Berhasil meluluskan {$studentCount} siswa dari kelas {$class->class_name}";

            return redirect()->route('students.graduate')->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat meluluskan siswa: ' . $e->getMessage());
        }
    }

    public function dashboard()
    {
        $chart = \App\Http\Controllers\Controller::dashboardData();
        return view('dashboard', $chart);
    }

    /**
     * Reset password siswa menjadi default
     */
    public function resetPassword(Request $request, $id)
    {
        try {
            $student = Student::findOrFail($id);
            
            // Reset password menjadi default
            $student->update([
                'student_password' => Hash::make('password123'),
                'student_last_update' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password berhasil direset'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reset password massal untuk siswa yang dipilih
     */
    public function resetPasswordMassal(Request $request)
    {
        try {
            $request->validate([
                'student_ids' => 'required|array|min:1',
                'student_ids.*' => 'exists:students,student_id'
            ]);

            $studentIds = $request->student_ids;
            
            // Reset password untuk semua siswa yang dipilih
            Student::whereIn('student_id', $studentIds)->update([
                'student_password' => Hash::make('password123'),
                'student_last_update' => now()
            ]);

            $count = count($studentIds);

            return response()->json([
                'success' => true,
                'message' => "Password berhasil direset untuk {$count} siswa",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal reset password massal: ' . $e->getMessage()
            ], 500);
        }
    }
}
