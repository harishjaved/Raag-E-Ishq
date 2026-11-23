<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('venue_name')->nullable();
            $table->text('venue_address')->nullable();
            $table->dateTime('event_start_at')->nullable();
            $table->dateTime('event_end_at')->nullable();
            $table->unsignedInteger('total_capacity')->default(450);
            $table->boolean('is_published')->default(false);
            $table->boolean('live_payments_enabled')->default(false);
            $table->unsignedInteger('hold_duration_minutes')->default(10);
            $table->timestamps();
        });

        Schema::create('ticket_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->string('name');
            $table->string('display_name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('price_paise');
            $table->unsignedInteger('capacity');
            $table->unsignedInteger('display_order')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['event_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ticket_categories');
        Schema::dropIfExists('events');
    }
};
