<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->string('provider')->default('razorpay');
            $table->string('provider_qr_id')->nullable()->index();
            $table->string('provider_order_id')->nullable()->index();
            $table->string('provider_payment_id')->nullable()->index();
            $table->unsignedBigInteger('amount_paise');
            $table->string('currency', 10)->default('INR');
            $table->string('method')->default('upi_qr'); // upi_qr, upi_intent, checkout
            $table->text('qr_image_url')->nullable();
            $table->text('qr_payload')->nullable();
            $table->string('status')->default('CREATED')->index(); // CREATED, AWAITING_PAYMENT, CAPTURED, FAILED, EXPIRED, RECONCILIATION_REQUIRED
            $table->dateTime('expires_at')->nullable()->index();
            $table->dateTime('captured_at')->nullable();
            $table->string('error_code')->nullable();
            $table->text('error_description')->nullable();
            $table->timestamps();
        });

        Schema::create('gateway_webhook_events', function (Blueprint $table) {
            $table->id();
            $table->string('provider')->default('razorpay');
            $table->string('provider_event_id')->unique()->index();
            $table->string('event_type')->index();
            $table->longText('raw_payload');
            $table->string('signature')->nullable();
            $table->string('processing_status')->default('PENDING')->index(); // PENDING, PROCESSED, DUPLICATE, FAILED
            $table->text('error_message')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gateway_webhook_events');
        Schema::dropIfExists('payment_attempts');
    }
};
