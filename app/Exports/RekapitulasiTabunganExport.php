<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;

class RekapitulasiTabunganExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithTitle, ShouldAutoSize
{
    protected $rekapitulasiData;
    protected $startDate;
    protected $endDate;
    protected $paymentMethod;
    protected $totalSetoran;
    protected $totalPenarikan;
    protected $totalSaldo;

    public function __construct($rekapitulasiData, $startDate, $endDate, $paymentMethod, $totalSetoran, $totalPenarikan, $totalSaldo)
    {
        $this->rekapitulasiData = $rekapitulasiData;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->paymentMethod = $paymentMethod;
        $this->totalSetoran = $totalSetoran;
        $this->totalPenarikan = $totalPenarikan;
        $this->totalSaldo = $totalSaldo;
    }

    public function collection()
    {
        return $this->rekapitulasiData;
    }

    public function headings(): array
    {
        return [
            'No',
            'NIS',
            'Nama Siswa',
            'Kelas',
            'Saldo Awal',
            'Setoran Tunai',
            'Setoran Transfer Bank',
            'Setoran Payment Gateway',
            'Total Setoran',
            'Total Penarikan',
            'Saldo Akhir',
            'Jumlah Transaksi'
        ];
    }

    public function map($data): array
    {
        static $no = 0;
        $no++;

        return [
            $no,
            $data['student_nis'],
            $data['student_name'],
            $data['class_name'],
            $data['saldo_awal'], // Raw number untuk Excel
            $data['setoran_tunai'], // Raw number untuk Excel
            $data['setoran_transfer_bank'], // Raw number untuk Excel
            $data['setoran_payment_gateway'], // Raw number untuk Excel
            $data['total_setoran'], // Raw number untuk Excel
            $data['jumlah_penarikan'], // Raw number untuk Excel
            $data['saldo_akhir'], // Raw number untuk Excel
            $data['jumlah_transaksi']
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Add filter information at the top
        $sheet->setCellValue('A1', 'INFORMASI FILTER:');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true],
        ]);

        $sheet->setCellValue('A2', 'Periode:');
        $sheet->setCellValue('B2', Carbon::parse($this->startDate)->format('d/m/Y') . ' - ' . Carbon::parse($this->endDate)->format('d/m/Y'));

        $sheet->setCellValue('A3', 'Metode Pembayaran:');
        $paymentMethodText = $this->getPaymentMethodText();
        $sheet->setCellValue('B3', $paymentMethodText);

        $sheet->setCellValue('A4', 'Tanggal Export:');
        $sheet->setCellValue('B4', Carbon::now()->format('d/m/Y H:i:s'));

        // Header styles (moved down by 4 rows)
        $sheet->getStyle('A5:L5')->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '2C3E50'],
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

        // Data rows styles
        $dataRows = $this->rekapitulasiData->count();
        if ($dataRows > 0) {
            $sheet->getStyle('A6:L' . ($dataRows + 5))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => 'CCCCCC'],
                    ],
                ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ]);

            // Alternate row colors
            for ($i = 6; $i <= $dataRows + 5; $i++) {
                if ($i % 2 == 0) {
                    $sheet->getStyle('A' . $i . ':L' . $i)->applyFromArray([
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => 'F8F9FA'],
                        ],
                    ]);
                }
            }

            // Number columns alignment (E-K untuk kolom angka)
            $sheet->getStyle('E6:K' . ($dataRows + 5))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ]);

            // Number column alignment (L untuk jumlah transaksi)
            $sheet->getStyle('L6:L' . ($dataRows + 5))->applyFromArray([
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ]);

            // Format angka untuk kolom numerik (E-K)
            $sheet->getStyle('E6:K' . ($dataRows + 5))->getNumberFormat()->setFormatCode('#,##0');
        }

        // Add summary section
        $summaryRow = $dataRows + 7; // Moved down by 4 rows (5-1 = 4)
        $sheet->getStyle('A' . $summaryRow . ':L' . $summaryRow)->applyFromArray([
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '27AE60'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);

        $sheet->setCellValue('A' . $summaryRow, 'TOTAL');
        $sheet->setCellValue('E' . $summaryRow, $this->rekapitulasiData->sum('saldo_awal')); // Raw number
        $sheet->setCellValue('F' . $summaryRow, $this->rekapitulasiData->sum('setoran_tunai')); // Raw number
        $sheet->setCellValue('G' . $summaryRow, $this->rekapitulasiData->sum('setoran_transfer_bank')); // Raw number
        $sheet->setCellValue('H' . $summaryRow, $this->rekapitulasiData->sum('setoran_payment_gateway')); // Raw number
        $sheet->setCellValue('I' . $summaryRow, $this->totalSetoran); // Raw number
        $sheet->setCellValue('J' . $summaryRow, $this->totalPenarikan); // Raw number
        $sheet->setCellValue('K' . $summaryRow, $this->totalSaldo); // Raw number
        $sheet->setCellValue('L' . $summaryRow, $this->rekapitulasiData->sum('jumlah_transaksi'));

        // Format angka untuk baris summary
        $sheet->getStyle('E' . $summaryRow . ':K' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0');

        return $sheet;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // NIS
            'C' => 30,  // Nama Siswa
            'D' => 20,  // Kelas
            'E' => 15,  // Saldo Awal
            'F' => 15,  // Setoran Tunai
            'G' => 20,  // Setoran Transfer Bank
            'H' => 20,  // Setoran Payment Gateway
            'I' => 15,  // Total Setoran
            'J' => 15,  // Total Penarikan
            'K' => 15,  // Saldo Akhir
            'L' => 15,  // Jumlah Transaksi
        ];
    }

    public function title(): string
    {
        return 'Rekapitulasi Tabungan';
    }

    private function getPaymentMethodText()
    {
        switch ($this->paymentMethod) {
            case 'tunai':
                return 'Tunai';
            case 'transfer_bank':
                return 'Transfer Bank';
            case 'payment_gateway':
                return 'Payment Gateway';
            default:
                return 'Semua Metode';
        }
    }
}
