<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelEmployee extends Model
{
    protected $table = 'hotel_employees';

    protected $fillable = [
        'user_id',
        'hotel_id',
        'employee_name',
        'contact_number',
        'email',
        'aadhar_number',
        'pan_number',
        'address',
        'state_id',
        'city_id',
        'pincode',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id');
    }

    public function employeeDocuments()
    {
        return $this->hasMany(HotelEmployeeDoc::class, 'hotel_employee_id')->with('document');
    }
}
