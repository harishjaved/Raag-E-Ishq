<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_holds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->cascadeOnDelete();
            $table->foreignId('ticket_category_id')->constrained('ticket_categories');
            $table->unsignedInteger('quantity');
            $table->dateTime('expires_at')->index();
            $table->string('status')->default('ACTIVE')->index(); // ACTIVE, RELEASED, CONVERTED
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_holds');
    }
};
