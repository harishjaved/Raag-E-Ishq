<?php

namespace App\Providers;

use App\Models\Booking;
use App\Models\TicketCategory;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production' || env('APP_ENV') === 'production' || isset($_SERVER['VERCEL'])) {
            URL::forceScheme('https');
        }

        // Auto-initialize and upgrade managed PostgreSQL schema on cold serverless requests
        if (config('database.default') === 'pgsql' && !app()->runningInConsole() && !file_exists('/tmp/.pg_schema_v2_ready')) {
            try {
                if (
                    !Schema::hasTable('events')
                    || !Schema::hasTable('donations')
                    || !Schema::hasColumn('users', 'two_factor_secret')
                    || !Schema::hasColumn('bookings', 'utr_reference')
                ) {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                }

                if (
                    Schema::hasTable('events')
                    && (
                        \App\Models\Event::count() === 0
                        || \App\Models\User::count() === 0
                        || TicketCategory::where('name', 'Tier 1')->exists()
                    )
                ) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                }

                if (is_dir('/tmp')) {
                    @file_put_contents('/tmp/.pg_schema_v2_ready', '1');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('PostgreSQL auto-bootstrap failed: ' . $e->getMessage());
            }
        }

        // Share live counters with the admin sidebar
        View::composer('layouts.admin', function ($view) {
            try {
                $statusAgg = Booking::query()
                    ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as pending_count', [
                        Booking::STATUS_REQUEST_SUBMITTED,
                        Booking::STATUS_PAYMENT_PENDING,
                        Booking::STATUS_PENDING,
                    ])
                    ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?, ?) THEN 1 ELSE 0 END), 0) as payment_submitted_count', [
                        Booking::STATUS_PAYMENT_SUBMITTED,
                        Booking::STATUS_VERIFYING_PAYMENT,
                        Booking::STATUS_IN_PROCESS,
                    ])
                    ->first();

                $needsDispatchCount = Booking::whereDoesntHave('tickets')
                    ->where('is_attending', true)
                    ->whereIn('status', [
                        Booking::STATUS_CONFIRMED,
                        Booking::STATUS_BOOKING_CONFIRMED,
                        Booking::STATUS_PAYMENT_VERIFIED,
                        Booking::STATUS_PAYMENT_SUBMITTED,
                        Booking::STATUS_IN_PROCESS,
                        Booking::STATUS_PAYMENT_PENDING,
                    ])
                    ->count();

                $view->with([
                    'sidebarPendingCount' => (int) ($statusAgg->pending_count ?? 0),
                    'sidebarInProcessCount' => (int) ($statusAgg->payment_submitted_count ?? 0),
                    'sidebarNeedsDispatchCount' => $needsDispatchCount,
                ]);
            } catch (\Throwable $e) {
                // Skip if database has not finished migrating yet
            }
        });
    }
}
