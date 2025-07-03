<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Visitor extends Model
{
    use SoftDeletes;

    protected $table = 'visitors';

    protected $fillable = [
        'booking_id',
        'visitor_name',
        'aadhar_number',
        'contact_number',
        'age',
        'gender',
        'id_proof_path',
        'entry_time',
        'exit_time',
        'address',
        'state_id',
        'city_id',
        'pincode',
    ];

    public function booking()
    {
        return $this->belongsTo(HotelBooking::class, 'booking_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }
}
