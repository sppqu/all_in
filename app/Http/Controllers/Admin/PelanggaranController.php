<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pelanggaran;
use App\Models\PelanggaranKategori;
use Illuminate\Http\Request;

class PelanggaranController extends Controller
{
    /**
     * Display a listing of pelanggaran
     */
    public function index(Request $request)
    {
        $query = Pelanggaran::with('kategori');

        // Filter by kategori
        if ($request->has('kategori_id') && $request->kategori_id != '') {
            $query->where('kategori_id', $request->kategori_id);
        }

        // Filter by status
        if ($request->has('is_active') && $request->is_active != '') {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('kode', 'like', '%' . $request->search . '%');
            });
        }

        $pelanggaran = $query->latest()->paginate(15);
        $kategoris = PelanggaranKategori::active()->get();

        return view('manage.bk.pelanggaran.index', compact('pelanggaran', 'kategoris'));
    }

    /**
     * Show the form for creating a new pelanggaran
     */
    public function create()
    {
        $kategoris = PelanggaranKategori::active()->get();
        return view('manage.bk.pelanggaran.create', compact('kategoris'));
    }

    /**
     * Store a newly created pelanggaran
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:pelanggaran_kategori,id',
            'kode' => 'required|string|max:20|unique:pelanggaran,kode',
            'nama' => 'required|string|max:255',
            'point' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'is_active' => 'required|boolean'
        ]);

        Pelanggaran::create($validated);

        return redirect()->route('manage.bk.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil ditambahkan.');
    }

    /**
     * Display the specified pelanggaran
     */
    public function show(Pelanggaran $pelanggaran)
    {
        $pelanggaran->load(['kategori', 'pelanggaranSiswa.siswa']);
        return view('manage.bk.pelanggaran.show', compact('pelanggaran'));
    }

    /**
     * Show the form for editing the specified pelanggaran
     */
    public function edit(Pelanggaran $pelanggaran)
    {
        $kategoris = PelanggaranKategori::active()->get();
        return view('manage.bk.pelanggaran.edit', compact('pelanggaran', 'kategoris'));
    }

    /**
     * Update the specified pelanggaran
     */
    public function update(Request $request, Pelanggaran $pelanggaran)
    {
        $validated = $request->validate([
            'kategori_id' => 'required|exists:pelanggaran_kategori,id',
            'kode' => 'required|string|max:20|unique:pelanggaran,kode,' . $pelanggaran->id,
            'nama' => 'required|string|max:255',
            'point' => 'required|integer|min:1',
            'keterangan' => 'nullable|string',
            'is_active' => 'required|boolean'
        ]);

        $pelanggaran->update($validated);

        return redirect()->route('manage.bk.pelanggaran.index')
            ->with('success', 'Pelanggaran berhasil diupdate.');
    }

    /**
     * Remove the specified pelanggaran
     */
    public function destroy(Pelanggaran $pelanggaran)
    {
        try {
            $pelanggaran->delete();
            return redirect()->route('manage.bk.pelanggaran.index')
                ->with('success', 'Pelanggaran berhasil dihapus.');
        } catch (\Exception $e) {
            return redirect()->route('manage.bk.pelanggaran.index')
                ->with('error', 'Pelanggaran tidak dapat dihapus karena masih digunakan.');
        }
    }

    /**
     * Download template Excel untuk import
     */
    public function downloadTemplate()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Header styling
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4472C4']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        
        // Set headers
        $sheet->setCellValue('A1', 'KODE');
        $sheet->setCellValue('B1', 'NAMA PELANGGARAN');
        $sheet->setCellValue('C1', 'KATEGORI');
        $sheet->setCellValue('D1', 'POINT');
        $sheet->setCellValue('E1', 'KETERANGAN');
        $sheet->setCellValue('F1', 'STATUS');
        
        // Apply header style
        $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
        
        // Set column widths
        $sheet->getColumnDimension('A')->setWidth(12);
        $sheet->getColumnDimension('B')->setWidth(30);
        $sheet->getColumnDimension('C')->setWidth(20);
        $sheet->getColumnDimension('D')->setWidth(10);
        $sheet->getColumnDimension('E')->setWidth(40);
        $sheet->getColumnDimension('F')->setWidth(12);
        
        // Add example data
        $sheet->setCellValue('A2', 'P001');
        $sheet->setCellValue('B2', 'Terlambat Masuk Sekolah');
        $sheet->setCellValue('C2', 'Pelanggaran Ringan');
        $sheet->setCellValue('D2', '5');
        $sheet->setCellValue('E2', 'Datang terlambat tanpa keterangan');
        $sheet->setCellValue('F2', 'Aktif');
        
        $sheet->setCellValue('A3', 'P002');
        $sheet->setCellValue('B3', 'Tidak Mengerjakan PR');
        $sheet->setCellValue('C3', 'Pelanggaran Ringan');
        $sheet->setCellValue('D3', '3');
        $sheet->setCellValue('E3', 'Tidak mengerjakan tugas rumah');
        $sheet->setCellValue('F3', 'Aktif');
        
        // Add note
        $sheet->setCellValue('A5', 'CATATAN:');
        $sheet->setCellValue('A6', '- KATEGORI: Pelanggaran Ringan, Pelanggaran Sedang, Pelanggaran Berat');
        $sheet->setCellValue('A7', '- STATUS: Aktif atau Tidak Aktif');
        $sheet->setCellValue('A8', '- Hapus baris contoh sebelum mengisi data');
        
        $sheet->getStyle('A5:A8')->getFont()->setItalic(true)->getColor()->setRGB('FF0000');
        
        // Create writer
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        // Set headers for download
        $filename = 'Template_Master_Pelanggaran_' . date('Y-m-d') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    /**
     * Import data dari Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:2048',
        ]);
        
        try {
            $file = $request->file('file');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getRealPath());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            // Remove header row
            array_shift($rows);
            
            $imported = 0;
            $errors = [];
            
            // Check if replace existing data
            if ($request->has('replace_existing')) {
                Pelanggaran::query()->delete();
            }
            
            foreach ($rows as $index => $row) {
                $rowNumber = $index + 2; // +2 because of header and 0-based index
                
                // Skip empty rows
                if (empty($row[0]) && empty($row[1])) {
                    continue;
                }
                
                // Validate required fields
                if (empty($row[0]) || empty($row[1]) || empty($row[2]) || empty($row[3])) {
                    $errors[] = "Baris {$rowNumber}: Data tidak lengkap";
                    continue;
                }
                
                // Get kategori
                $kategori = PelanggaranKategori::where('nama', 'like', '%' . trim($row[2]) . '%')->first();
                if (!$kategori) {
                    $errors[] = "Baris {$rowNumber}: Kategori '{$row[2]}' tidak ditemukan";
                    continue;
                }
                
                // Determine is_active
                $isActive = strtolower(trim($row[5] ?? 'aktif')) == 'aktif' ? 1 : 0;
                
                try {
                    Pelanggaran::create([
                        'kode' => trim($row[0]),
                        'nama' => trim($row[1]),
                        'kategori_id' => $kategori->id,
                        'point' => (int)$row[3],
                        'keterangan' => trim($row[4] ?? ''),
                        'is_active' => $isActive,
                    ]);
                    $imported++;
                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }
            
            $message = "{$imported} data berhasil diimport.";
            if (count($errors) > 0) {
                $message .= " " . count($errors) . " data gagal: " . implode(', ', array_slice($errors, 0, 3));
                if (count($errors) > 3) {
                    $message .= ", dan " . (count($errors) - 3) . " error lainnya.";
                }
            }
            
            return redirect()->route('manage.bk.pelanggaran.index')
                ->with('success', $message);
                
        } catch (\Exception $e) {
            return redirect()->route('manage.bk.pelanggaran.index')
                ->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
