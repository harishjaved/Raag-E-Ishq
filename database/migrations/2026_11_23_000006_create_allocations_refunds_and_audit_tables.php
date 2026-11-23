<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ticket_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events');
            $table->foreignId('ticket_category_id')->constrained('ticket_categories');
            $table->string('allocation_type')->index(); // COMPLIMENTARY, SPONSOR, ORGANISER_HOLD
            $table->unsignedInteger('quantity');
            $table->string('beneficiary_name');
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users');
            $table->timestamps();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('payment_attempt_id')->nullable()->constrained('payment_attempts');
            $table->string('provider_refund_id')->nullable()->index();
            $table->unsignedBigInteger('amount_paise');
            $table->string('status')->default('PENDING')->index(); // PENDING, PROCESSED, FAILED
            $table->string('reason');
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users');
            $table->dateTime('processed_at')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users');
            $table->string('action')->index();
            $table->string('target_type')->index();
            $table->unsignedBigInteger('target_id')->nullable()->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('refunds');
        Schema::dropIfExists('ticket_allocations');
    }
};
