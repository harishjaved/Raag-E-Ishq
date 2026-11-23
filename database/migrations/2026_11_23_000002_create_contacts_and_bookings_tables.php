<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('full_name');
            $table->string('email')->index();
            $table->string('phone')->index();
            $table->string('country_code', 10)->default('+91');
            $table->boolean('marketing_opt_in')->default(false);
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->string('utm_content')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_reference')->unique()->index();
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('contact_id')->constrained('contacts');
            $table->unsignedBigInteger('total_amount_paise');
            $table->string('currency', 10)->default('INR');
            $table->string('status')->index(); // DRAFT, PAYMENT_PENDING, VERIFYING_PAYMENT, CONFIRMED, CANCELLED, REFUNDED, RECONCILIATION_REQUIRED, HOLD_EXPIRED, PAYMENT_FAILED
            $table->dateTime('hold_expires_at')->nullable()->index();
            $table->dateTime('confirmed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
        });

        Schema::create('booking_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories');
            $table->unsignedInteger('quantity');
            $table->unsignedBigInteger('unit_price_paise');
            $table->unsignedBigInteger('subtotal_paise');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_items');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('contacts');
    }
};
