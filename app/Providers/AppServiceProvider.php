<?php

namespace App\Providers;

use App\Models\Booking;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (config('app.env') === 'production' || env('APP_ENV') === 'production' || isset($_SERVER['VERCEL'])) {
            URL::forceScheme('https');
        }
        // Share live counters with the admin sidebar
        View::composer('layouts.admin', function ($view) {
            if (Schema::hasTable('bookings')) {
                $pendingCount = Booking::whereIn('status', [Booking::STATUS_PAYMENT_PENDING, 'PENDING'])->count();
                $inProcessCount = Booking::where('status', Booking::STATUS_IN_PROCESS)->count();
                $needsDispatchCount = Booking::whereDoesntHave('tickets')
                    ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_IN_PROCESS, Booking::STATUS_PAYMENT_PENDING])
                    ->count();

                $view->with([
                    'sidebarPendingCount' => $pendingCount,
                    'sidebarInProcessCount' => $inProcessCount,
                    'sidebarNeedsDispatchCount' => $needsDispatchCount,
                ]);
            }
        });
    }
}
