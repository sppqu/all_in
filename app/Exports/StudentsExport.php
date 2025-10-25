<?php

namespace App\Exports;

use App\Models\Student;
use App\Models\ClassModel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StudentsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    protected $search;
    protected $classId;
    protected $status;

    public function __construct($search = null, $classId = null, $status = null)
    {
        $this->search = $search;
        $this->classId = $classId;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Student::with(['class', 'major']);

        // Filter by NIS/Nama
        if ($this->search) {
            $query->where(function($q) {
                $q->where('student_nis', 'LIKE', "%{$this->search}%")
                  ->orWhere('student_full_name', 'LIKE', "%{$this->search}%")
                  ->orWhere('student_nisn', 'LIKE', "%{$this->search}%");
            });
        }

        // Filter by Kelas
        if ($this->classId) {
            $query->where('class_class_id', $this->classId);
        }

        // Filter by Status
        if ($this->status !== null) {
            $query->where('student_status', $this->status);
        }

        $students = $query->orderBy('student_full_name')->get();

        return $students->map(function($student) {
            return [
                $student->student_nis,
                $student->student_nisn ?? '-',
                $student->student_full_name,
                $student->gender_text,
                $student->student_born_place,
                $student->student_born_date ? \Carbon\Carbon::parse($student->student_born_date)->format('d/m/Y') : '-',
                $student->age ? $student->age . ' tahun' : '-',
                $student->class->class_name ?? '-',
                $student->student_parent_phone ?? '-',
                $student->status_text,
                $student->student_phone ?? '-',
                $student->student_hobby ?? '-',
                $student->student_address ?? '-',
                $student->student_name_of_mother ?? '-',
                $student->student_name_of_father ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Usia',
            'Kelas',
            'No. Telp Orang Tua',
            'Status',
            'No. Telp Siswa',
            'Hobi',
            'Alamat',
            'Nama Ibu',
            'Nama Ayah'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:O1')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => '000000'],
                ],
            ],
        ]);

        // Style untuk data
        $lastRow = $sheet->getHighestRow();
        if ($lastRow > 1) {
            $sheet->getStyle('A2:O' . $lastRow)->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Set format untuk kolom NIS dan NISN sebagai text
        $sheet->getStyle('A2:A' . $lastRow)->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('B2:B' . $lastRow)->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('I2:I' . $lastRow)->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('K2:K' . $lastRow)->getNumberFormat()->setFormatCode('@');

        // Tambahkan informasi filter di bawah data
        $filterInfoRow = $lastRow + 2;
        $sheet->setCellValue('A' . $filterInfoRow, 'INFORMASI FILTER:');
        
        $filterDetails = [];
        if ($this->search) {
            $filterDetails[] = "Pencarian: {$this->search}";
        }
        if ($this->classId) {
            $class = ClassModel::find($this->classId);
            $filterDetails[] = "Kelas: " . ($class ? $class->class_name : 'Tidak ditemukan');
        }
        if ($this->status !== null) {
            $filterDetails[] = "Status: " . ($this->status == '1' ? 'Aktif' : 'Non-Aktif');
        }
        
        if (!empty($filterDetails)) {
            $sheet->setCellValue('A' . ($filterInfoRow + 1), implode(', ', $filterDetails));
        }

        // Style untuk informasi filter
        $sheet->getStyle('A' . $filterInfoRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FF0000'],
                'size' => 12,
            ],
        ]);

        $sheet->getStyle('A' . ($filterInfoRow + 1))->applyFromArray([
            'font' => [
                'color' => ['rgb' => '000000'],
                'size' => 10,
            ],
        ]);

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15, // NIS
            'B' => 15, // NISN
            'C' => 25, // Nama Lengkap
            'D' => 15, // Jenis Kelamin
            'E' => 20, // Tempat Lahir
            'F' => 15, // Tanggal Lahir
            'G' => 10, // Usia
            'H' => 15, // Kelas
            'I' => 20, // No. Telp Orang Tua
            'J' => 10, // Status
            'K' => 20, // No. Telp Siswa
            'L' => 20, // Hobi
            'M' => 30, // Alamat
            'N' => 20, // Nama Ibu
            'O' => 20, // Nama Ayah
        ];
    }

    public function title(): string
    {
        $title = 'Data Peserta Didik';
        
        if ($this->search || $this->classId || $this->status !== null) {
            $title .= ' (Filtered)';
        }
        
        return $title;
    }
} 