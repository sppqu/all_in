<?php

namespace App\Helpers;

class TransferStatusHelper
{
    /**
     * Get status text for transfer
     */
    public static function getTransferStatusText($status)
    {
        switch ($status) {
            case 0:
                return 'Menunggu Verifikasi';
            case 1:
                return 'Berhasil';
            case 2:
                return 'Ditolak';
            case 3:
                return 'Dibatalkan';
            case 4:
                return 'Expired';
            default:
                return 'Tidak Diketahui';
        }
    }

    /**
     * Get status badge class for transfer
     */
    public static function getTransferStatusBadge($status)
    {
        switch ($status) {
            case 0:
                return 'bg-warning';
            case 1:
                return 'bg-success';
            case 2:
                return 'bg-danger';
            case 3:
                return 'bg-secondary';
            case 4:
                return 'bg-dark';
            default:
                return 'bg-secondary';
        }
    }

    /**
     * Get detail status text for payment detail
     */
    public static function getDetailStatusText($paymentType, $isTabungan = 0, $transferStatus = 1)
    {
        // Untuk setoran tabungan, status selalu berdasarkan transfer status
        if ($paymentType == 3 && $isTabungan == 1) {
            if ($transferStatus == 1) {
                return 'Lunas';
            } elseif ($transferStatus == 0) {
                return 'Menunggu Verifikasi';
            } else {
                return 'Ditolak';
            }
        }

        // Untuk pembayaran bulanan dan bebas, status berdasarkan transfer status
        if ($paymentType == 1 || $paymentType == 2) {
            if ($transferStatus == 1) {
                return 'Lunas';
            } elseif ($transferStatus == 0) {
                return 'Menunggu Verifikasi';
            } elseif ($transferStatus == 2) {
                return 'Ditolak';
            } elseif ($transferStatus == 3) {
                return 'Dibatalkan';
            } elseif ($transferStatus == 4) {
                return 'Expired';
            }
        }

        return 'Belum Lunas'; // Default fallback
    }

    /**
     * Get detail status badge class for payment detail
     */
    public static function getDetailStatusBadge($paymentType, $isTabungan = 0, $transferStatus = 1)
    {
        // Untuk setoran tabungan, status selalu berdasarkan transfer status
        if ($paymentType == 3 && $isTabungan == 1) {
            if ($transferStatus == 1) {
                return 'bg-success';
            } elseif ($transferStatus == 0) {
                return 'bg-warning';
            } else {
                return 'bg-danger';
            }
        }

        // Untuk pembayaran bulanan dan bebas, status berdasarkan transfer status
        if ($paymentType == 1 || $paymentType == 2) {
            if ($transferStatus == 1) {
                return 'bg-success';
            } elseif ($transferStatus == 0) {
                return 'bg-warning';
            } elseif ($transferStatus == 2) {
                return 'bg-danger';
            } elseif ($transferStatus == 3) {
                return 'bg-secondary';
            } elseif ($transferStatus == 4) {
                return 'bg-dark';
            }
        }

        return 'bg-warning'; // Default fallback
    }

    /**
     * Get comprehensive detail status text for all payment types
     */
    public static function getComprehensiveDetailStatusText($paymentType, $isTabungan = 0, $transferStatus = 1, $paymentDate = null)
    {
        // Untuk setoran tabungan
        if ($paymentType == 3 && $isTabungan == 1) {
            return self::getDetailStatusText($paymentType, $isTabungan, $transferStatus);
        }

        // Untuk pembayaran bulanan dan bebas
        if ($paymentType == 1 || $paymentType == 2) {
            if ($transferStatus == 1) {
                return 'Lunas';
            } elseif ($transferStatus == 0) {
                return 'Menunggu Verifikasi';
            } elseif ($transferStatus == 2) {
                return 'Ditolak';
            } elseif ($transferStatus == 3) {
                return 'Dibatalkan';
            } elseif ($transferStatus == 4) {
                return 'Expired';
            }
        }

        return 'Belum Lunas';
    }

    /**
     * Get comprehensive detail status badge for all payment types
     */
    public static function getComprehensiveDetailStatusBadge($paymentType, $isTabungan = 0, $transferStatus = 1, $paymentDate = null)
    {
        // Untuk setoran tabungan
        if ($paymentType == 3 && $isTabungan == 1) {
            return self::getDetailStatusBadge($paymentType, $isTabungan, $transferStatus);
        }

        // Untuk pembayaran bulanan dan bebas
        if ($paymentType == 1 || $paymentType == 2) {
            if ($transferStatus == 1) {
                return 'bg-success';
            } elseif ($transferStatus == 0) {
                return 'bg-warning';
            } elseif ($transferStatus == 2) {
                return 'bg-danger';
            } elseif ($transferStatus == 3) {
                return 'bg-secondary';
            } elseif ($transferStatus == 4) {
                return 'bg-dark';
            }
        }

        return 'bg-warning';
    }
}
