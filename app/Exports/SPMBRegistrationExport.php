<?php

namespace App\Exports;

use App\Models\SPMBRegistration;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SPMBRegistrationExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $registrations;

    public function __construct($registrations)
    {
        $this->registrations = $registrations;
    }

    public function collection()
    {
        return $this->registrations->map(function ($registration) {
            $formData = is_array($registration->form_data) ? $registration->form_data : json_decode($registration->form_data, true);
            
            return [
                'No' => $this->registrations->search($registration) + 1,
                'Nomor Pendaftaran' => $registration->nomor_pendaftaran ?? '-',
                'Nama Lengkap' => $registration->name,
                'No. HP' => $registration->phone,
                'Email' => $registration->email,
                'Tanggal Lahir' => $formData['tanggal_lahir'] ?? '-',
                'Jenis Kelamin' => $formData['jenis_kelamin'] ?? '-',
                'Alamat' => $formData['alamat'] ?? '-',
                'Kota' => $formData['kota'] ?? '-',
                'Provinsi' => $formData['provinsi'] ?? '-',
                'Kode Pos' => $formData['kode_pos'] ?? '-',
                'Nama Ayah' => $formData['nama_ayah'] ?? '-',
                'Pekerjaan Ayah' => $formData['pekerjaan_ayah'] ?? '-',
                'Nama Ibu' => $formData['nama_ibu'] ?? '-',
                'Pekerjaan Ibu' => $formData['pekerjaan_ibu'] ?? '-',
                'No. HP Orang Tua' => $formData['no_hp_ortu'] ?? '-',
                'Asal Sekolah' => $formData['asal_sekolah'] ?? '-',
                'Jurusan Sekolah' => $formData['jurusan_sekolah'] ?? '-',
                'Tahun Lulus' => $formData['tahun_lulus'] ?? '-',
                'Nilai Rata-rata' => $formData['nilai_rata_rata'] ?? '-',
                'Kejuruan Pilihan' => $registration->kejuruan ? $registration->kejuruan->nama_kejuruan : '-',
                'Status' => $this->getStatusText($registration->status),
                'Step' => $registration->step . '/6',
                'Tanggal Daftar' => $registration->created_at->format('d/m/Y H:i'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'No',
            'Nomor Pendaftaran',
            'Nama Lengkap',
            'No. HP',
            'Email',
            'Tanggal Lahir',
            'Jenis Kelamin',
            'Alamat',
            'Kota',
            'Provinsi',
            'Kode Pos',
            'Nama Ayah',
            'Pekerjaan Ayah',
            'Nama Ibu',
            'Pekerjaan Ibu',
            'No. HP Orang Tua',
            'Asal Sekolah',
            'Jurusan Sekolah',
            'Tahun Lulus',
            'Nilai Rata-rata',
            'Kejuruan Pilihan',
            'Status',
            'Step',
            'Tanggal Daftar',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 20,  // Nomor Pendaftaran
            'C' => 25,  // Nama Lengkap
            'D' => 15,  // No. HP
            'E' => 25,  // Email
            'F' => 15,  // Tanggal Lahir
            'G' => 15,  // Jenis Kelamin
            'H' => 30,  // Alamat
            'I' => 20,  // Kota
            'J' => 20,  // Provinsi
            'K' => 12,  // Kode Pos
            'L' => 20,  // Nama Ayah
            'M' => 20,  // Pekerjaan Ayah
            'N' => 20,  // Nama Ibu
            'O' => 20,  // Pekerjaan Ibu
            'P' => 18,  // No. HP Orang Tua
            'Q' => 25,  // Asal Sekolah
            'R' => 20,  // Jurusan Sekolah
            'S' => 12,  // Tahun Lulus
            'T' => 15,  // Nilai Rata-rata
            'U' => 20,  // Kejuruan Pilihan
            'V' => 15,  // Status
            'W' => 8,   // Step
            'X' => 18,  // Tanggal Daftar
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Header row
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '008060'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Set header row height
                $sheet->getRowDimension(1)->setRowHeight(25);
                
                // Apply borders to all cells
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $highestColumn . $highestRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Auto-fit column widths
                foreach (range('A', $highestColumn) as $column) {
                    $sheet->getColumnDimension($column)->setAutoSize(true);
                }
                
                // Set alternating row colors
                for ($row = 2; $row <= $highestRow; $row++) {
                    if ($row % 2 == 0) {
                        $sheet->getStyle('A' . $row . ':' . $highestColumn . $row)->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F8F9FA'],
                            ],
                        ]);
                    }
                }
                
                // Center align certain columns
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('K:K')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('S:S')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('T:T')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('V:V')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('W:W')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('X:X')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    private function getStatusText($status)
    {
        switch ($status) {
            case 'pending':
                return 'Pending';
            case 'approved':
                return 'Diterima';
            case 'rejected':
                return 'Ditolak';
            case 'completed':
                return 'Selesai';
            default:
                return ucfirst($status);
        }
    }
}

