<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('event_settings', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique()->index();
            $table->text('value')->nullable();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('email_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings');
            $table->foreignId('ticket_id')->nullable()->constrained('tickets');
            $table->string('recipient_email')->index();
            $table->string('email_type')->index(); // BOOKING_CONFIRMATION, TICKET_RESEND
            $table->string('status')->default('PENDING')->index(); // PENDING, SENT, FAILED
            $table->unsignedInteger('attempts')->default(0);
            $table->text('error_message')->nullable();
            $table->dateTime('sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_deliveries');
        Schema::dropIfExists('event_settings');
    }
};
