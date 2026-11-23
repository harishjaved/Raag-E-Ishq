<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->boolean('is_attending')->default(true)->after('contact_id')->index();
            $table->boolean('is_donation')->default(false)->after('is_attending')->index();
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['is_attending']);
            $table->dropIndex(['is_donation']);
            $table->dropColumn(['is_attending', 'is_donation']);
        });
    }
};
