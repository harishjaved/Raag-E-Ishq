<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('booking_item_id')->nullable()->constrained('booking_items');
            $table->foreignId('ticket_category_id')->constrained('ticket_categories');
            $table->string('ticket_number')->unique()->index();
            $table->string('qr_uuid')->unique()->index();
            $table->string('qr_nonce', 64);
            $table->string('attendee_name')->nullable();
            $table->string('status')->default('ISSUED')->index(); // ISSUED, CHECKED_IN, VOID, CANCELLED
            $table->dateTime('issued_at');
            $table->dateTime('voided_at')->nullable();
            $table->string('void_reason')->nullable();
            $table->timestamps();
        });

        Schema::create('checkins', function (Blueprint $table) {
            $table->id();
            // UNIQUE constraint: exactly one accepted check-in per ticket
            $table->foreignId('ticket_id')->unique()->constrained('tickets');
            $table->foreignId('scanned_by_user_id')->nullable()->constrained('users');
            $table->dateTime('checked_in_at')->index();
            $table->string('gate_identifier')->default('GATE_1');
            $table->string('verification_method')->default('QR_SCAN'); // QR_SCAN, MANUAL_LOOKUP
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('checkins');
        Schema::dropIfExists('tickets');
    }
};
