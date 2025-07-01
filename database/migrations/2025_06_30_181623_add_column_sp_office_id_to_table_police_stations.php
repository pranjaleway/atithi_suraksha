<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('police_stations', function (Blueprint $table) {
            $table->foreignId('sp_office_id')->nullable()->after('user_id')->constrained('sp_offices')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('police_stations', function (Blueprint $table) {
            $table->dropForeign(['sp_office_id']);
        });
    }
};
