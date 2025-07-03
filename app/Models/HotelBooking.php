<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HotelBooking extends Model
{
    use SoftDeletes;
    protected $table = 'hotel_bookings';

    protected $fillable = [
        'hotel_id',
        'hotel_employee_id',
        'booking_id',
        'guest_name',
        'no_of_guest',
        'no_of_male',
        'no_of_female',
        'no_of_children',
        'parent_id',
        'check_in',
        'check_out',
        'room_number_id',
        'room_number',
        'aadhar_number',
        'age',
        'gender',
        'contact_number',
        'email',
        'id_proof_path',
        'address',
        'state_id',
        'city_id',
        'pincode',
        'status',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }

    public function hotelEmployee()
    {
        return $this->belongsTo(HotelEmployee::class);
    }

    public function state()
    {
        return $this->belongsTo(State::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
