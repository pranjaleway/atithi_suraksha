<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Hotel extends Model
{
    use SoftDeletes;
    protected $table = 'hotels';

    protected $fillable = [
        'user_id',
        'police_station_id',
        'hotel_name',
        'owner_name',
        'owner_contact_number',
        'aadhar_number',
        'pan_number',
        'license_number',
        'address',
        'state_id',
        'city_id',
        'pincode',
        'contact_number',
        'email',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function police_station()
    {
        return $this->belongsTo(PoliceStation::class, 'police_station_id');
    }

public function ownerDocuments()
{
    return $this->hasMany(HotelOwnerDoc::class, 'hotel_id')->with('document');
}

}
