<?php

namespace App\Http\Controllers;

use App\Models\ClassModel;
use Illuminate\Http\Request;

class ClassController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $classes = ClassModel::withCount('students')->orderBy('class_name')->get();
        return view('classes.index', compact('classes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('classes.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'class_name' => 'required|string|max:45|unique:class_models,class_name'
        ]);

        ClassModel::create($request->all());

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil ditambahkan!');
    }

    /**
     * Display the specified resource.
     */
    public function show(ClassModel $class)
    {
        $class->load(['students' => function($query) {
            $query->orderBy('student_full_name');
        }]);
        
        // Debug: Log data yang di-load
        \Log::info('Class show method called', [
            'class_id' => $class->class_id,
            'class_name' => $class->class_name,
            'students_count' => $class->students->count(),
            'students' => $class->students->map(function($student) {
                return [
                    'student_id' => $student->student_id,
                    'student_nis' => $student->student_nis,
                    'student_full_name' => $student->student_full_name,
                    'student_gender' => $student->student_gender,
                    'student_status' => $student->student_status
                ];
            })
        ]);
        
        return view('classes.show', compact('class'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(ClassModel $class)
    {
        return view('classes.edit', compact('class'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, ClassModel $class)
    {
        $request->validate([
            'class_name' => 'required|string|max:45|unique:class_models,class_name,' . $class->class_id . ',class_id'
        ]);

        $class->update($request->all());

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(ClassModel $class)
    {
        // Cek apakah ada siswa di kelas ini
        if ($class->students()->count() > 0) {
            return redirect()->route('classes.index')
                ->with('error', 'Tidak dapat menghapus kelas yang masih memiliki siswa!');
        }

        $class->delete();

        return redirect()->route('classes.index')
            ->with('success', 'Kelas berhasil dihapus!');
    }
}
