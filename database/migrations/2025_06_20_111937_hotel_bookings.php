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
        Schema::create('hotel_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hotel_id')->constrained('hotels')->onDelete('cascade');
            $table->foreignId('hotel_employee_id')->nullable()->constrained('hotel_employees')->onDelete('cascade');
            $table->string('guest_name');
            $table->integer('parent_id')->nullable();
            $table->dateTime('check_in');
            $table->dateTime('check_out');
            $table->string('room_number');
            $table->string('aadhar_number');
            $table->string('contact_number');
            $table->string('email')->nullable();
            $table->text('id_proof_path')->nullable();
            $table->text('address')->nullable();
            $table->foreignId('state_id')->nullable()->constrained('states')->onDelete('cascade');
            $table->foreignId('city_id')->nullable()->constrained('cities')->onDelete('cascade');
            $table->string('pincode')->nullable();
            $table->tinyInteger('status')->default(0)->comment('1 = sent, 0 = not sent');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hotel_bookings');
    }
};
