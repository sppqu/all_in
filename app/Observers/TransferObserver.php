<?php

namespace App\Observers;

use App\Models\Transfer;
use App\Models\Notification;
use App\Services\NotificationService;

class TransferObserver
{
    /**
     * Handle the Transfer "created" event.
     */
    public function created(Transfer $transfer): void
    {
        // Hanya buat notifikasi untuk transaksi online (bukan manual)
        if ($transfer->checkout_url && $transfer->status == 0) {
            $this->createOnlinePaymentNotification($transfer);
        }
    }

    /**
     * Handle the Transfer "updated" event.
     */
    public function updated(Transfer $transfer): void
    {
        // Jika status berubah menjadi berhasil (1), buat notifikasi sukses
        if ($transfer->wasChanged('status') && $transfer->status == 1) {
            $this->createPaymentSuccessNotification($transfer);
        }
        
        // Jika status berubah menjadi ditolak (2), buat notifikasi ditolak
        if ($transfer->wasChanged('status') && $transfer->status == 2) {
            $this->createPaymentRejectedNotification($transfer);
        }
    }

    /**
     * Buat notifikasi untuk pembayaran online baru
     */
    private function createOnlinePaymentNotification(Transfer $transfer): void
    {
        // Ambil nama siswa
        $studentName = $this->getStudentName($transfer->student_id);
        
        // Tentukan jenis pembayaran
        $paymentType = $this->getPaymentTypeText($transfer);
        
        // Tentukan metode pembayaran
        $paymentMethod = $this->getPaymentMethodText($transfer);
        
        Notification::create([
            'type' => 'payment_online_new',
            'title' => 'Pembayaran Online Baru',
            'message' => "Pembayaran {$paymentType} sebesar Rp " . number_format($transfer->total_amount) . " dari {$studentName} via {$paymentMethod} menunggu verifikasi.",
            'icon' => 'fa-credit-card',
            'color' => 'warning',
            'data' => [
                'transfer_id' => $transfer->transfer_id,
                'student_id' => $transfer->student_id,
                'student_name' => $studentName,
                'amount' => $transfer->total_amount,
                'payment_type' => $paymentType,
                'payment_method' => $paymentMethod,
                'status' => $transfer->status
            ]
        ]);
    }

    /**
     * Buat notifikasi untuk pembayaran berhasil
     */
    private function createPaymentSuccessNotification(Transfer $transfer): void
    {
        $studentName = $this->getStudentName($transfer->student_id);
        $paymentType = $this->getPaymentTypeText($transfer);
        $paymentMethod = $this->getPaymentMethodText($transfer);
        
        Notification::create([
            'type' => 'payment_online_success',
            'title' => 'Pembayaran Online Berhasil',
            'message' => "Pembayaran {$paymentType} sebesar Rp " . number_format($transfer->total_amount) . " dari {$studentName} via {$paymentMethod} telah berhasil.",
            'icon' => 'fa-check-circle',
            'color' => 'success',
            'data' => [
                'transfer_id' => $transfer->transfer_id,
                'student_id' => $transfer->student_id,
                'student_name' => $studentName,
                'amount' => $transfer->total_amount,
                'payment_type' => $paymentType,
                'payment_method' => $paymentMethod,
                'status' => $transfer->status
            ]
        ]);
    }

    /**
     * Buat notifikasi untuk pembayaran ditolak
     */
    private function createPaymentRejectedNotification(Transfer $transfer): void
    {
        $studentName = $this->getStudentName($transfer->student_id);
        $paymentType = $this->getPaymentTypeText($transfer);
        $paymentMethod = $this->getPaymentMethodText($transfer);
        
        Notification::create([
            'type' => 'payment_online_rejected',
            'title' => 'Pembayaran Online Ditolak',
            'message' => "Pembayaran {$paymentType} sebesar Rp " . number_format($transfer->total_amount) . " dari {$studentName} via {$paymentMethod} telah ditolak.",
            'icon' => 'fa-times-circle',
            'color' => 'danger',
            'data' => [
                'transfer_id' => $transfer->transfer_id,
                'student_id' => $transfer->student_id,
                'student_name' => $studentName,
                'amount' => $transfer->total_amount,
                'payment_type' => $paymentType,
                'payment_method' => $paymentMethod,
                'status' => $transfer->status
            ]
        ]);
    }

    /**
     * Ambil nama siswa berdasarkan student_id
     */
    private function getStudentName($studentId): string
    {
        $student = \DB::table('students')->where('student_id', $studentId)->first();
        return $student ? $student->student_name : 'Siswa Tidak Diketahui';
    }

    /**
     * Tentukan jenis pembayaran berdasarkan bill_type
     */
    private function getPaymentTypeText(Transfer $transfer): string
    {
        switch ($transfer->bill_type) {
            case 1:
                return 'Bulanan';
            case 2:
                return 'Bebas';
            case 3:
                return 'Tabungan';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Tentukan metode pembayaran
     */
    private function getPaymentMethodText(Transfer $transfer): string
    {
        // Prioritas 1: metode_pembayaran_tabungan
        if (!empty($transfer->metode_pembayaran_tabungan)) {
            switch ($transfer->metode_pembayaran_tabungan) {
                case 'transfer_bank':
                    return 'Transfer Bank';
                case 'payment_gateway':
                    return 'Payment Gateway';
                case 'tunai':
                    return 'Tunai';
            }
        }
        
        // Prioritas 2: payment_method
        if (!empty($transfer->payment_method)) {
            switch ($transfer->payment_method) {
                case 'bank_transfer':
                case 'manual_transfer':
                    return 'Transfer Bank';
                case 'midtrans':
                case 'credit_card':
                case 'e_wallet':
                case 'online_payment':
                    return 'Payment Gateway';
                case 'cash':
                    return 'Tunai';
            }
        }
        
        return 'Online Payment';
    }
}
