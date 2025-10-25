<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class ArusKasController extends Controller
{
    public function index(Request $request)
    {
        // Filter tanggal dengan validasi
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Jika tidak ada input, gunakan default bulan ini
        if (!$startDate) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Debug: Log tanggal yang digunakan
        \Log::info('Arus KAS Filter - Raw Request: ' . json_encode($request->all()));
        \Log::info('Arus KAS Filter - Start Date: ' . $startDate . ', End Date: ' . $endDate);
        
        // Data Pemasukan dari transaksi penerimaan
        $pemasukanQuery = DB::table('transaksi_penerimaan')
            ->where('status', 'confirmed')
            ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
            ->selectRaw('
                DATE(tanggal_penerimaan) as tanggal,
                SUM(total_penerimaan) as total_pemasukan,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal');
        
        // Debug: Log query SQL
        \Log::info('Arus KAS - Query Pemasukan: ' . $pemasukanQuery->toSql());
        \Log::info('Arus KAS - Bindings Pemasukan: ' . json_encode($pemasukanQuery->getBindings()));
        
        try {
            $pemasukan = $pemasukanQuery->get();
            \Log::info('Arus KAS - Jumlah data pemasukan: ' . $pemasukan->count());
            
            // Debug: Log sample data
            if ($pemasukan->count() > 0) {
                \Log::info('Arus KAS - Sample data pemasukan: ' . json_encode($pemasukan->first()));
            }
        } catch (\Exception $e) {
            \Log::error('Arus KAS - Error query pemasukan: ' . $e->getMessage());
            \Log::error('Arus KAS - Error trace: ' . $e->getTraceAsString());
            $pemasukan = collect();
        }
        
        // Data Pengeluaran dari transaksi pengeluaran
        $pengeluaranQuery = DB::table('transaksi_pengeluaran')
            ->where('status', 'confirmed')
            ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->selectRaw('
                DATE(tanggal_pengeluaran) as tanggal,
                SUM(total_pengeluaran) as total_pengeluaran,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal');
        
        // Debug: Log query SQL
        \Log::info('Arus KAS - Query Pengeluaran: ' . $pengeluaranQuery->toSql());
        \Log::info('Arus KAS - Bindings Pengeluaran: ' . json_encode($pengeluaranQuery->getBindings()));
        
        try {
            $pengeluaran = $pengeluaranQuery->get();
            \Log::info('Arus KAS - Jumlah data pengeluaran: ' . $pengeluaran->count());
            
            // Debug: Log sample data
            if ($pengeluaran->count() > 0) {
                \Log::info('Arus KAS - Sample data pengeluaran: ' . json_encode($pengeluaran->first()));
            }
        } catch (\Exception $e) {
            \Log::error('Arus KAS - Error query pengeluaran: ' . $e->getMessage());
            \Log::error('Arus KAS - Error trace: ' . $e->getTraceAsString());
            $pengeluaran = collect();
        }
        
        // Total per periode
        $totalPemasukan = $pemasukan->sum('total_pemasukan');
        $totalPengeluaran = $pengeluaran->sum('total_pengeluaran');
        $saldoKas = $totalPemasukan - $totalPengeluaran;
        
        // Debug: Log total dan saldo
        \Log::info('Arus KAS - Total Pemasukan: ' . $totalPemasukan);
        \Log::info('Arus KAS - Total Pengeluaran: ' . $totalPengeluaran);
        \Log::info('Arus KAS - Saldo KAS: ' . $saldoKas);
        
        // Data untuk chart
        $chartData = [];
        $allDates = collect();
        
        // Gabungkan semua tanggal
        $pemasukan->each(function($item) use (&$allDates) {
            $allDates->push($item->tanggal);
        });
        $pengeluaran->each(function($item) use (&$allDates) {
            $allDates->push($item->tanggal);
        });
        
        $allDates = $allDates->unique()->sort();
        
        foreach ($allDates as $date) {
            $pemasukanHari = $pemasukan->where('tanggal', $date)->first();
            $pengeluaranHari = $pengeluaran->where('tanggal', $date)->first();
            
            $chartData[] = [
                'tanggal' => $date,
                'pemasukan' => $pemasukanHari ? $pemasukanHari->total_pemasukan : 0,
                'pengeluaran' => $pengeluaranHari ? $pengeluaranHari->total_pengeluaran : 0,
                'saldo' => ($pemasukanHari ? $pemasukanHari->total_pemasukan : 0) - ($pengeluaranHari ? $pengeluaranHari->total_pengeluaran : 0)
            ];
        }
        
        // Detail transaksi untuk tabel
        try {
            $detailPemasukan = DB::table('transaksi_penerimaan')
                ->where('status', 'confirmed')
                ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
                ->orderBy('tanggal_penerimaan', 'desc')
                ->get();
            
            // Debug: Log detail pemasukan
            \Log::info('Arus KAS - Detail pemasukan count: ' . $detailPemasukan->count());
        } catch (\Exception $e) {
            \Log::error('Arus KAS - Error query detail pemasukan: ' . $e->getMessage());
            $detailPemasukan = collect();
        }
            
        try {
            $detailPengeluaran = DB::table('transaksi_pengeluaran')
                ->where('status', 'confirmed')
                ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
                ->orderBy('tanggal_pengeluaran', 'desc')
                ->get();
            
            // Debug: Log detail pengeluaran
            \Log::info('Arus KAS - Detail pengeluaran count: ' . $detailPengeluaran->count());
        } catch (\Exception $e) {
            \Log::error('Arus KAS - Error query detail pengeluaran: ' . $e->getMessage());
            $detailPengeluaran = collect();
        }
        
        return view('arus-kas.index', compact(
            'startDate',
            'endDate',
            'pemasukan',
            'pengeluaran',
            'totalPemasukan',
            'totalPengeluaran',
            'saldoKas',
            'chartData',
            'detailPemasukan',
            'detailPengeluaran'
        ));
    }

    public function exportExcel(Request $request)
    {
        // Filter tanggal dari request atau default bulan ini
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Jika tidak ada input, gunakan default bulan ini
        if (!$startDate) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Data Pemasukan dari transaksi penerimaan
        $pemasukan = DB::table('transaksi_penerimaan')
            ->where('status', 'confirmed')
            ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
            ->selectRaw('
                DATE(tanggal_penerimaan) as tanggal,
                SUM(total_penerimaan) as total_pemasukan,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        // Data Pengeluaran dari transaksi pengeluaran
        $pengeluaran = DB::table('transaksi_pengeluaran')
            ->where('status', 'confirmed')
            ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->selectRaw('
                DATE(tanggal_pengeluaran) as tanggal,
                SUM(total_pengeluaran) as total_pengeluaran,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        // Total per periode
        $totalPemasukan = $pemasukan->sum('total_pemasukan');
        $totalPengeluaran = $pengeluaran->sum('total_pengeluaran');
        $saldoKas = $totalPemasukan - $totalPengeluaran;
        
        // Detail transaksi untuk tabel
        $detailPemasukan = DB::table('transaksi_penerimaan')
            ->where('status', 'confirmed')
            ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
            ->orderBy('tanggal_penerimaan', 'desc')
            ->get();
            
        $detailPengeluaran = DB::table('transaksi_pengeluaran')
            ->where('status', 'confirmed')
            ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->orderBy('tanggal_pengeluaran', 'desc')
            ->get();

        // Buat spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set judul
        $sheet->setCellValue('A1', 'LAPORAN ARUS KAS');
        $sheet->setCellValue('A2', 'Periode: ' . $startDate . ' - ' . $endDate);
        
        // Merge cells untuk judul
        $sheet->mergeCells('A1:F1');
        $sheet->mergeCells('A2:F2');
        
        // Style judul
        $sheet->getStyle('A1:A2')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1:A2')->getAlignment()->setHorizontal('center');
        
        // Summary
        $sheet->setCellValue('A4', 'TOTAL PEMASUKAN');
        $sheet->setCellValue('B4', 'Rp ' . number_format($totalPemasukan));
        $sheet->setCellValue('A5', 'TOTAL PENGELUARAN');
        $sheet->setCellValue('B5', 'Rp ' . number_format($totalPengeluaran));
        $sheet->setCellValue('A6', 'SALDO KAS');
        $sheet->setCellValue('B6', 'Rp ' . number_format($saldoKas));
        
        // Style summary
        $sheet->getStyle('A4:A6')->getFont()->setBold(true);
        $sheet->getStyle('B4:B6')->getFont()->setBold(true);
        
        // Header Detail Pemasukan
        $sheet->setCellValue('A8', 'DETAIL PEMASUKAN');
        $sheet->mergeCells('A8:F8');
        $sheet->getStyle('A8')->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A8')->getAlignment()->setHorizontal('center');
        
        $sheet->setCellValue('A9', 'No.');
        $sheet->setCellValue('B9', 'Tanggal');
        $sheet->setCellValue('C9', 'Keterangan');
        $sheet->setCellValue('D9', 'Nominal');
        $sheet->setCellValue('E9', 'Pajak');
        $sheet->setCellValue('F9', 'Total');
        
        // Style header
        $sheet->getStyle('A9:F9')->getFont()->setBold(true);
        $sheet->getStyle('A9:F9')->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('28A745');
        $sheet->getStyle('A9:F9')->getFont()->getColor()->setRGB('FFFFFF');
        
        // Data pemasukan
        $row = 10;
        $no = 1;
        foreach ($detailPemasukan as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->tanggal_penerimaan)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $item->keterangan_transaksi ?? '-');
            $sheet->setCellValue('D' . $row, $item->total_penerimaan);
            $sheet->setCellValue('E' . $row, 0); // Pajak tidak ada di transaksi penerimaan
            $sheet->setCellValue('F' . $row, $item->total_penerimaan);
            $row++;
            $no++;
        }
        
        // Header Detail Pengeluaran
        $startRowPengeluaran = $row + 2;
        $sheet->setCellValue('A' . $startRowPengeluaran, 'DETAIL PENGELUARAN');
        $sheet->mergeCells('A' . $startRowPengeluaran . ':F' . $startRowPengeluaran);
        $sheet->getStyle('A' . $startRowPengeluaran)->getFont()->setBold(true)->setSize(12);
        $sheet->getStyle('A' . $startRowPengeluaran)->getAlignment()->setHorizontal('center');
        
        $sheet->setCellValue('A' . ($startRowPengeluaran + 1), 'No.');
        $sheet->setCellValue('B' . ($startRowPengeluaran + 1), 'Tanggal');
        $sheet->setCellValue('C' . ($startRowPengeluaran + 1), 'Keterangan');
        $sheet->setCellValue('D' . ($startRowPengeluaran + 1), 'Nominal');
        $sheet->setCellValue('E' . ($startRowPengeluaran + 1), 'Unit POS');
        $sheet->setCellValue('F' . ($startRowPengeluaran + 1), 'Status');
        
        // Style header pengeluaran
        $sheet->getStyle('A' . ($startRowPengeluaran + 1) . ':F' . ($startRowPengeluaran + 1))->getFont()->setBold(true);
        $sheet->getStyle('A' . ($startRowPengeluaran + 1) . ':F' . ($startRowPengeluaran + 1))->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('DC3545');
        $sheet->getStyle('A' . ($startRowPengeluaran + 1) . ':F' . ($startRowPengeluaran + 1))->getFont()->getColor()->setRGB('FFFFFF');
        
        // Data pengeluaran
        $row = $startRowPengeluaran + 2;
        $no = 1;
        foreach ($detailPengeluaran as $item) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, \Carbon\Carbon::parse($item->tanggal_pengeluaran)->format('d/m/Y'));
            $sheet->setCellValue('C' . $row, $item->keterangan_transaksi ?? '-');
            $sheet->setCellValue('D' . $row, $item->total_pengeluaran);
            $sheet->setCellValue('E' . $row, $item->pos_pengeluaran_id ?? '-');
            $sheet->setCellValue('F' . $row, $item->status ?? 'Confirmed');
            $row++;
            $no++;
        }
        
        // Auto size columns
        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        // Border untuk semua data
        $lastRow = $row - 1;
        $sheet->getStyle('A9:F' . $lastRow)->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        
        // Buat file Excel
        $writer = new Xlsx($spreadsheet);
        $filename = 'Laporan_Arus_KAS_' . $startDate . '_' . $endDate . '.xlsx';
        
        // Set header untuk download
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    public function exportPDF(Request $request)
    {
        // Filter tanggal dari request atau default bulan ini
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        
        // Jika tidak ada input, gunakan default bulan ini
        if (!$startDate) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
        }
        if (!$endDate) {
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Validasi format tanggal
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
            $startDate = now()->startOfMonth()->format('Y-m-d');
            $endDate = now()->endOfMonth()->format('Y-m-d');
        }
        
        // Data Pemasukan dari transaksi penerimaan
        $pemasukan = DB::table('transaksi_penerimaan')
            ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->selectRaw('
                DATE(tanggal_penerimaan) as tanggal,
                SUM(total_penerimaan) as total_pemasukan,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        // Data Pengeluaran dari transaksi pengeluaran
        $pengeluaran = DB::table('transaksi_pengeluaran')
            ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->selectRaw('
                DATE(tanggal_pengeluaran) as tanggal,
                SUM(total_pengeluaran) as total_pengeluaran,
                COUNT(*) as jumlah_transaksi
            ')
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();
        
        // Total per periode
        $totalPemasukan = $pemasukan->sum('total_pemasukan');
        $totalPengeluaran = $pengeluaran->sum('total_pengeluaran');
        $saldoKas = $totalPemasukan - $totalPengeluaran;
        
        // Detail transaksi untuk tabel
        $detailPemasukan = DB::table('transaksi_penerimaan')
            ->whereBetween('tanggal_penerimaan', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->orderBy('tanggal_penerimaan', 'desc')
            ->get();
            
        $detailPengeluaran = DB::table('transaksi_pengeluaran')
            ->whereBetween('tanggal_pengeluaran', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->orderBy('tanggal_pengeluaran', 'desc')
            ->get();

        // Data untuk view
        $data = [
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totalPemasukan' => $totalPemasukan,
            'totalPengeluaran' => $totalPengeluaran,
            'saldoKas' => $saldoKas,
            'detailPemasukan' => $detailPemasukan,
            'detailPengeluaran' => $detailPengeluaran
        ];

        // Generate PDF
        $pdf = PDF::loadView('arus-kas.pdf', $data);
        $filename = 'Laporan_Arus_KAS_' . $startDate . '_' . $endDate . '.pdf';
        
        return $pdf->download($filename);
    }
}
