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

        // Auto-initialize managed PostgreSQL schema & baseline seed on first serverless request
        if (config('database.default') === 'pgsql' && !app()->runningInConsole() && !file_exists('/tmp/.pg_schema_ready')) {
            try {
                if (!Schema::hasTable('events')) {
                    \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
                }
                if (Schema::hasTable('events') && (\App\Models\Event::count() === 0 || \App\Models\User::count() === 0)) {
                    \Illuminate\Support\Facades\Artisan::call('db:seed', ['--force' => true]);
                }
                if (is_dir('/tmp')) {
                    @file_put_contents('/tmp/.pg_schema_ready', '1');
                }
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('PostgreSQL auto-bootstrap failed: ' . $e->getMessage());
            }
        }
        // Share live counters with the admin sidebar
        View::composer('layouts.admin', function ($view) {
            try {
                $statusAgg = Booking::query()
                    ->selectRaw('COALESCE(SUM(CASE WHEN status IN (?, ?) THEN 1 ELSE 0 END), 0) as pending_count', [
                        Booking::STATUS_PAYMENT_PENDING,
                        'PENDING',
                    ])
                    ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN 1 ELSE 0 END), 0) as in_process_count', [
                        Booking::STATUS_IN_PROCESS,
                    ])
                    ->first();

                $needsDispatchCount = Booking::whereDoesntHave('tickets')
                    ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_IN_PROCESS, Booking::STATUS_PAYMENT_PENDING])
                    ->count();

                $view->with([
                    'sidebarPendingCount' => (int) ($statusAgg->pending_count ?? 0),
                    'sidebarInProcessCount' => (int) ($statusAgg->in_process_count ?? 0),
                    'sidebarNeedsDispatchCount' => $needsDispatchCount,
                ]);
            } catch (\Throwable $e) {
                // Skip if database has not finished migrating yet
            }
        });
    }
}
