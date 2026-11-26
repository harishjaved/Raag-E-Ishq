<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'two_factor_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('two_factor_secret')->nullable()->after('is_active');
                $table->boolean('two_factor_enabled')->default(false)->after('two_factor_secret');
                $table->dateTime('two_factor_confirmed_at')->nullable()->after('two_factor_enabled');
                $table->text('two_factor_recovery_codes')->nullable()->after('two_factor_confirmed_at');
            });
        }

        if (!Schema::hasColumn('bookings', 'utr_reference')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->string('utr_reference', 120)->nullable()->index()->after('cancellation_reason');
                $table->string('payment_screenshot_reference', 255)->nullable()->after('utr_reference');
                $table->dateTime('payment_submitted_at')->nullable()->after('payment_screenshot_reference');
                $table->dateTime('payment_verified_at')->nullable()->after('payment_submitted_at');
                $table->boolean('is_offline_booking')->default(false)->index()->after('payment_verified_at');
                $table->string('payment_channel', 40)->nullable()->after('is_offline_booking');
                $table->foreignId('created_by_user_id')->nullable()->after('payment_channel')->constrained('users')->nullOnDelete();
                $table->foreignId('verified_by_user_id')->nullable()->after('created_by_user_id')->constrained('users')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('donations')) {
            Schema::create('donations', function (Blueprint $table) {
                $table->id();
                $table->string('donation_reference')->unique()->index();
                $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
                $table->foreignId('contact_id')->constrained('contacts')->cascadeOnDelete();
                $table->unsignedBigInteger('amount_paise');
                $table->string('currency', 10)->default('INR');
                $table->string('status')->default('PENDING_PAYMENT')->index(); // PENDING_PAYMENT, PAYMENT_SUBMITTED, ACKNOWLEDGED
                $table->string('payment_method', 40)->nullable();
                $table->string('utr_reference', 120)->nullable()->index();
                $table->text('donor_note')->nullable();
                $table->dateTime('acknowledged_at')->nullable();
                $table->foreignId('verified_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->string('ip_address', 45)->nullable();
                $table->timestamps();
            });
        }

        // Safely rename seeded ticket tiers (Tier 1/2/3 -> Gold/Silver/Bronze) preserving existing bookings/FKs
        if (Schema::hasTable('ticket_categories')) {
            $renames = [
                'Tier 1' => ['name' => 'Gold', 'display_name' => 'Gold (₹2,500)', 'description' => 'Premium front seating'],
                'Tier 2' => ['name' => 'Silver', 'display_name' => 'Silver (₹1,500)', 'description' => 'Central seating'],
                'Tier 3' => ['name' => 'Bronze', 'display_name' => 'Bronze (₹1,000)', 'description' => 'General seating'],
            ];

            foreach ($renames as $oldName => $attrs) {
                $hasNew = DB::table('ticket_categories')->where('name', $attrs['name'])->exists();
                if (!$hasNew) {
                    DB::table('ticket_categories')->where('name', $oldName)->update($attrs);
                }
            }
        }

        // Upgrade hold duration setting to 30 minutes
        if (Schema::hasTable('event_settings')) {
            DB::table('event_settings')
                ->where('key', 'hold_duration_minutes')
                ->whereIn('value', ['10', '15'])
                ->update(['value' => '30']);
        }
        if (Schema::hasTable('events')) {
            DB::table('events')->update(['hold_duration_minutes' => 30]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('donations');

        if (Schema::hasColumn('bookings', 'utr_reference')) {
            Schema::table('bookings', function (Blueprint $table) {
                $table->dropConstrainedForeignId('verified_by_user_id');
                $table->dropConstrainedForeignId('created_by_user_id');
                $table->dropColumn([
                    'utr_reference',
                    'payment_screenshot_reference',
                    'payment_submitted_at',
                    'payment_verified_at',
                    'is_offline_booking',
                    'payment_channel',
                ]);
            });
        }

        if (Schema::hasColumn('users', 'two_factor_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn([
                    'two_factor_secret',
                    'two_factor_enabled',
                    'two_factor_confirmed_at',
                    'two_factor_recovery_codes',
                ]);
            });
        }
    }
};
