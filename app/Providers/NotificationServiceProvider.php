<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Notification;

class NotificationServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Share subscription notifications with all views
        View::composer('*', function ($view) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Get unread subscription notifications for current user
                $subscriptionNotifications = Notification::whereIn('type', ['subscription_expiring', 'subscription_expired'])
                    ->where('data->user_id', $user->id)
                    ->where('is_read', false)
                    ->orderBy('created_at', 'desc')
                    ->get();
                
                // Share with all views
                $view->with('subscriptionNotifications', $subscriptionNotifications);
            }
        });
    }
}
