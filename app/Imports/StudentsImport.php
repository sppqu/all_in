<?php

namespace App\Imports;

use App\Models\Student;
use App\Models\ClassModel;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Illuminate\Support\Facades\Hash;

class StudentsImport implements ToModel, WithHeadingRow, WithBatchInserts, WithChunkReading, SkipsOnError, SkipsEmptyRows, WithStartRow, WithCalculatedFormulas
{
    use Importable, SkipsErrors;

    private $importErrors = [];
    private $successCount = 0;
    private $errorCount = 0;
    private $currentRow = 0;

    /**
     * @return int
     */
    public function startRow(): int
    {
        return 2; // Mulai dari baris 2 (setelah header)
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        $this->currentRow++;
        
        try {
            // Debug: Log row data untuk troubleshooting
            \Log::info('Import row data:', $row);
            
            // Validasi data kosong
            if (empty($row['nis']) || empty($row['nama_lengkap'])) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": NIS atau Nama Lengkap kosong";
                $this->errorCount++;
                return null;
            }

            // Konversi dan validasi data dengan lebih fleksibel
            $nis = $this->convertNumericToString($row['nis']);
            $namaLengkap = $this->convertToString($row['nama_lengkap']);
            $jenisKelamin = $this->convertToString($row['jenis_kelamin'] ?? '');
            $tempatLahir = $this->convertToString($row['tempat_lahir'] ?? '');
            $tanggalLahir = $this->convertDateToString($row['tanggal_lahir'] ?? '');
            $kelas = $this->convertToString($row['kelas'] ?? '');
            $password = $this->convertToString($row['password'] ?? '');
            $noTelpOrangTua = $this->convertNumericToString($row['no_telp_orang_tua'] ?? '');
            $nisn = !empty($row['nisn'] ?? '') ? $this->convertNumericToString($row['nisn'] ?? '') : null;

            // Debug: Log converted data
            \Log::info('Converted data:', [
                'nis' => $nis,
                'nama_lengkap' => $namaLengkap,
                'jenis_kelamin' => $jenisKelamin,
                'tempat_lahir' => $tempatLahir,
                'tanggal_lahir' => $tanggalLahir,
                'kelas' => $kelas,
                'password' => $password,
                'no_telp_orang_tua' => $noTelpOrangTua,
                'nisn' => $nisn
            ]);

            // Validasi data wajib
            if (empty($nis) || empty($namaLengkap) || empty($jenisKelamin) || 
                empty($tempatLahir) || empty($tanggalLahir) || empty($kelas) || 
                empty($password) || empty($noTelpOrangTua)) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": Ada field wajib yang kosong";
                $this->errorCount++;
                return null;
            }

            // Validasi jenis kelamin
            if (!in_array(strtoupper($jenisKelamin), ['L', 'P'])) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": Jenis kelamin harus L atau P";
                $this->errorCount++;
                return null;
            }

            // Validasi format tanggal - lebih fleksibel
            if (!$tanggalLahir) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": Format tanggal tidak valid. Gunakan format dd/mm/yyyy atau yyyy-mm-dd";
                $this->errorCount++;
                return null;
            }

            // Cari kelas berdasarkan nama kelas
            $class = ClassModel::where('class_name', $kelas)->first();
            
            if (!$class) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": Kelas '{$kelas}' tidak ditemukan";
                $this->errorCount++;
                return null;
            }

            // Validasi NIS unik
            if (Student::where('student_nis', $nis)->exists()) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": NIS '{$nis}' sudah ada";
                $this->errorCount++;
                return null;
            }

            // Validasi NISN unik jika ada
            if ($nisn && Student::where('student_nisn', $nisn)->exists()) {
                $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": NISN '{$nisn}' sudah ada";
                $this->errorCount++;
                return null;
            }

            $this->successCount++;

            return new Student([
                'student_nis' => $nis,
                'student_nisn' => $nisn,
                'student_password' => Hash::make($password),
                'student_full_name' => $namaLengkap,
                'student_gender' => strtoupper($jenisKelamin),
                'student_born_place' => $tempatLahir,
                'student_born_date' => $tanggalLahir,
                'student_phone' => null,
                'student_hobby' => null,
                'student_address' => null,
                'student_name_of_mother' => null,
                'student_name_of_father' => null,
                'student_parent_phone' => $noTelpOrangTua,
                'class_class_id' => $class->class_id,
                'majors_majors_id' => null,
                'student_status' => 1,
            ]);

        } catch (\Exception $e) {
            $this->importErrors[] = "Baris " . ($this->currentRow + 1) . ": " . $e->getMessage();
            $this->errorCount++;
            return null;
        }
    }

    /**
     * Convert any value to string safely
     */
    private function convertToString($value)
    {
        if (is_null($value)) {
            return '';
        }
        
        // Handle Excel date format - hanya untuk field tanggal
        if (is_numeric($value) && $value > 1) {
            // Check if it might be an Excel date (hanya untuk field tertentu)
            try {
                $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                if ($excelDate) {
                    return $excelDate->format('d/m/Y');
                }
            } catch (\Exception $e) {
                // If not a valid Excel date, continue with normal conversion
            }
        }
        
        // Untuk field non-tanggal, pastikan tidak dikonversi ke tanggal
        $stringValue = (string) $value;
        
        // Hapus format tanggal yang tidak diinginkan
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $stringValue)) {
            // Jika ini bukan field tanggal_lahir, kembalikan nilai asli
            return $stringValue;
        }
        
        return trim($stringValue);
    }

    /**
     * Convert numeric values to string for NIS and NISN
     */
    private function convertNumericToString($value)
    {
        if (is_numeric($value)) {
            return (string) $value;
        }
        return $value;
    }

    /**
     * Convert date values to string for Tanggal Lahir
     */
    private function convertDateToString($dateString)
    {
        if (empty($dateString)) {
            return false;
        }

        // Try dd/mm/yyyy format
        if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $dateString)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('d/m/Y', $dateString);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        }

        // Try yyyy-mm-dd format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateString)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('Y-m-d', $dateString);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        }

        // Try dd-mm-yyyy format
        if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $dateString)) {
            try {
                $date = \Carbon\Carbon::createFromFormat('d-m-Y', $dateString);
                return $date->format('Y-m-d');
            } catch (\Exception $e) {
                return false;
            }
        }

        return false;
    }

    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 50; // Kurangi batch size untuk mengurangi memory usage
    }

    /**
     * @return int
     */
    public function chunkSize(): int
    {
        return 50; // Kurangi chunk size untuk mengurangi memory usage
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->importErrors;
    }

    /**
     * @return int
     */
    public function getSuccessCount()
    {
        return $this->successCount;
    }

    /**
     * @return int
     */
    public function getErrorCount()
    {
        return $this->errorCount;
    }
}
