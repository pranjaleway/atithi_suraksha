<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SpOffice extends Model
{
    use SoftDeletes;
    protected $table = 'sp_offices';

    protected $fillable = [
        'user_id',
        'office_name',
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
        return $this->belongsTo(User::class);
    }
}
