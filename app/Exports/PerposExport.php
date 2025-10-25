<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PerposExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithEvents, WithCustomStartCell
{
    private Collection $rows;
    private string $type; // bulanan|bebas
    private array $meta;

    public function __construct(Collection $rows, string $type, array $meta = [])
    {
        $this->rows = $rows;
        $this->type = $type;
        $this->meta = $meta;
    }

    public function collection(): Collection
    {
        if ($this->type === 'bulanan') {
            // Group per siswa dan siapkan 1 baris per siswa dengan 12 kolom bulan
            $grouped = $this->rows->groupBy('student_nis');
            $result = collect();
            foreach ($grouped as $nis => $items) {
                $first = $items->first();
                $monthly = $items->keyBy('month_month_id');
                $line = [
                    $first->student_nis,
                    $first->student_full_name,
                    $first->class_name,
                ];
                for ($m = 1; $m <= 12; $m++) {
                    $md = $monthly->get($m);
                    if ($md) {
                        $line[] = $md->bulan_date_pay ? 'Lunas' : (int) $md->bulan_bill;
                    } else {
                        $line[] = '';
                    }
                }
                $result->push($line);
            }
            return $result;
        }

        // Bebas: langsung baris per siswa
        return $this->rows->map(function ($row) {
            $sisa = (int) $row->bebas_bill - (int) $row->bebas_total_pay;
            return [
                $row->student_nis,
                $row->student_full_name,
                $row->class_name,
                (int) $row->bebas_bill,
                (int) $row->bebas_total_pay,
                $sisa,
                $sisa <= 0 ? 'Lunas' : 'Belum Lunas',
            ];
        });
    }

    public function headings(): array
    {
        if ($this->type === 'bulanan') {
            return ['NIS', 'Nama Siswa', 'Kelas', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni'];
        }
        return ['NIS', 'Nama Siswa', 'Kelas', 'Jumlah Tagihan', 'Total Bayar', 'Sisa', 'Status'];
    }

    public function map($row): array
    {
        // Data sudah dipetakan di collection()
        return $row;
    }

    public function styles(Worksheet $sheet)
    {
        // Headings row at 10
        $headLastCol = $this->type === 'bulanan' ? 'O' : 'G'; // A..O (15 cols) or A..G (7 cols)
        $sheet->getStyle('A10:' . $headLastCol . '10')->getFont()->setBold(true);
        $sheet->getStyle('A10:' . $headLastCol . '10')->getAlignment()->setHorizontal('center');

        // Number formatting for currency columns (bebas) data rows start at 11
        if ($this->type !== 'bulanan') {
            $lastRow = $sheet->getHighestRow();
            $sheet->getStyle('D11:F' . $lastRow)
                ->getNumberFormat()->setFormatCode('#,##0');
        }

        return [];
    }

    public function startCell(): string
    {
        // Start table headings at row 10 to leave space for header info
        return 'A10';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $colCount = $this->type === 'bulanan' ? 15 : 7; // headings count
                $lastCol = Coordinate::stringFromColumnIndex($colCount);

                // Title
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', 'LAPORAN PERPOS ' . strtoupper($this->type));
                $sheet->getStyle('A1')->getFont()->setSize(14)->setBold(true);
                $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

                // School name
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', $this->meta['school_name'] ?? '');
                $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

                // Printed at
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Tanggal Cetak: ' . ($this->meta['printed_at'] ?? ''));
                $sheet->getStyle('A3')->getAlignment()->setHorizontal('center');

                // Info table (labels in A5..A8, values in B5..B8)
                $sheet->setCellValue('A5', 'Pos Pembayaran');
                $sheet->setCellValue('B5', ': ' . ($this->meta['pos_name'] ?? ''));
                $sheet->setCellValue('A6', 'Tahun Ajaran');
                $sheet->setCellValue('B6', ': ' . ($this->meta['period_name'] ?? ''));
                $sheet->setCellValue('A7', 'Jenis Laporan');
                $sheet->setCellValue('B7', ': ' . ($this->meta['type_label'] ?? ''));
                $sheet->setCellValue('A8', 'Total Data');
                $sheet->setCellValue('B8', ': ' . ($this->meta['total_data'] ?? ''));

                // Emphasize label
                $sheet->getStyle('A5:A8')->getFont()->setBold(true);

                // Apply border ONLY to data table (headings + rows)
                $lastRow = $sheet->getHighestRow();
                $lastCol = $lastCol; // from above
                $sheet->getStyle('A10:' . $lastCol . $lastRow)
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
            }
        ];
    }
}


