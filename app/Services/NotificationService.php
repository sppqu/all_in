<?php

namespace App\Services;

use App\Models\Notification;

class NotificationService
{
    public static function createPaymentNotification($paymentData)
    {
        $notification = Notification::create([
            'type' => 'payment_online',
            'title' => 'Pembayaran Online Baru',
            'message' => "Pembayaran online sebesar Rp " . number_format($paymentData['amount']) . " dari " . $paymentData['student_name'] . " telah diterima.",
            'icon' => 'fa-credit-card',
            'color' => 'success',
            'data' => [
                'payment_id' => $paymentData['id'],
                'amount' => $paymentData['amount'],
                'student_name' => $paymentData['student_name'],
                'payment_method' => $paymentData['payment_method'] ?? 'Online Payment'
            ]
        ]);

        return $notification;
    }

    public static function createCashPaymentNotification($paymentData)
    {
        $notification = Notification::create([
            'type' => 'payment_cash',
            'title' => 'Pembayaran Tunai Baru',
            'message' => "Pembayaran tunai sebesar Rp " . number_format($paymentData['amount']) . " dari " . $paymentData['student_name'] . " telah diterima.",
            'icon' => 'fa-money-bill-wave',
            'color' => 'info',
            'data' => [
                'payment_id' => $paymentData['id'],
                'amount' => $paymentData['amount'],
                'student_name' => $paymentData['student_name'],
                'payment_method' => 'Cash Payment'
            ]
        ]);

        return $notification;
    }

    public static function getUnreadCount()
    {
        return Notification::unread()->count();
    }

    public static function getUnreadNotifications($limit = 5)
    {
        return Notification::unread()->orderBy('created_at', 'desc')->take($limit)->get();
    }
}
