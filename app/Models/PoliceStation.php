<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PoliceStation extends Model
{
    use SoftDeletes;
    protected $table = 'police_stations';

    protected $fillable = [
        'user_id',
        'police_station_name',
        'address',
        'state_id',
        'city_id',
        'pincode',
        'contact_number',
        'email',
        'officer_in_charge',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
