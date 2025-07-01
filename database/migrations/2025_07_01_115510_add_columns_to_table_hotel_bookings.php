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
            $table->string('booking_id')->after('hotel_employee_id')->nullable();
            $table->date('dob')->after('aadhar_number')->nullable();
            $table->integer('no_of_guest')->after('guest_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hotel_bookings', function (Blueprint $table) {
            $table->dropColumn('booking_id');
            $table->dropColumn('dob');
            $table->dropColumn('no_of_guest');
        });
    }
};
