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
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->integer('age')->after('aadhar_number')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->after('age')->nullable();
            $table->integer('no_of_male')->after('no_of_guest')->nullable();
            $table->integer('no_of_female')->after('no_of_male')->nullable();
            $table->integer('no_of_children')->after('no_of_female')->nullable();
            $table->text('room_number_id')->after('check_out')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropColumn('age');
            $table->dropColumn('gender');
            $table->dropColumn('no_of_male');
            $table->dropColumn('no_of_female');
            $table->dropColumn('no_of_children');
            $table->dropColumn('room_number_id');
        });
    }
};
