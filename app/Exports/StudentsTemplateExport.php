<?php

namespace App\Exports;

use App\Models\ClassModel;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class StudentsTemplateExport implements FromArray, WithHeadings, WithStyles, WithColumnWidths, WithTitle
{
    public function array(): array
    {
        return [
            [
                '12345',
                '123456789',
                'password123',
                'Ahmad Fadillah',
                'L',
                'Jakarta',
                '15/03/2008',
                'X IPA',
                '081234567890'
            ],
            [
                '12346',
                '123456790',
                'password123',
                'Siti Nurhaliza',
                'P',
                'Bandung',
                '20/07/2008',
                'X IPS',
                '081234567891'
            ],
            [
                '12347',
                '123456791',
                'password123',
                'Budi Santoso',
                'L',
                '',
                '',
                'XI IPA',
                '081234567892'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'NIS',
            'NISN',
            'Password',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Kelas',
            'No. Telp Orang Tua'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Style untuk header
        $sheet->getStyle('A1:I1')->applyFromArray([
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
        $sheet->getStyle('A2:I4')->applyFromArray([
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

        // Set format untuk kolom NIS dan NISN sebagai text
        $sheet->getStyle('A2:A4')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('B2:B4')->getNumberFormat()->setFormatCode('@');
        $sheet->getStyle('I2:I4')->getNumberFormat()->setFormatCode('@');

        // Style untuk kolom dengan validasi khusus
        $sheet->getStyle('E2:E4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);

        $sheet->getStyle('G2:G4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);

        $sheet->getStyle('H2:H4')->applyFromArray([
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'FFF2CC'],
            ],
        ]);

        // Tambahkan catatan penting
        $sheet->setCellValue('A6', 'PANDUAN PENGISIAN:');
        $sheet->setCellValue('A7', '1. NIS: Harus unik, tidak boleh kosong, maksimal 45 karakter');
        $sheet->setCellValue('A8', '2. NISN: Opsional, jika kosong biarkan kolom kosong');
        $sheet->setCellValue('A9', '3. Password: Wajib diisi, akan di-hash otomatis');
        $sheet->setCellValue('A10', '4. Nama Lengkap: Wajib diisi, maksimal 255 karakter');
        $sheet->setCellValue('A11', '5. Jenis Kelamin: Harus L (Laki-laki) atau P (Perempuan)');
        $sheet->setCellValue('A12', '6. Tempat Lahir: Opsional, maksimal 45 karakter');
        $sheet->setCellValue('A13', '7. Tanggal Lahir: Opsional, format dd/mm/yyyy (contoh: 15/03/2008)');
        $sheet->setCellValue('A14', '8. Kelas: Harus sesuai dengan nama kelas di sistem');
        $sheet->setCellValue('A15', '9. No. Telp Orang Tua: Wajib diisi, maksimal 45 karakter');

        // Style untuk panduan
        $sheet->getStyle('A6')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FF0000'],
                'size' => 12,
            ],
        ]);

        $sheet->getStyle('A7:A15')->applyFromArray([
            'font' => [
                'color' => ['rgb' => '000000'],
                'size' => 10,
            ],
        ]);

        // Tambahkan daftar kelas yang tersedia
        $classes = ClassModel::orderBy('class_name')->pluck('class_name')->toArray();
        $sheet->setCellValue('A17', 'DAFTAR KELAS YANG TERSEDIA:');
        $sheet->setCellValue('A18', implode(', ', $classes));

        $sheet->getStyle('A17')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => '0066CC'],
                'size' => 11,
            ],
        ]);

        $sheet->getStyle('A18')->applyFromArray([
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
            'B' => 20, // NISN
            'C' => 20, // Password
            'D' => 25, // Nama Lengkap
            'E' => 20, // Jenis Kelamin
            'F' => 20, // Tempat Lahir
            'G' => 25, // Tanggal Lahir
            'H' => 15, // Kelas
            'I' => 25, // No. Telp Orang Tua
        ];
    }

    public function title(): string
    {
        return 'Template Import Siswa';
    }
}
