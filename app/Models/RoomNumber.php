<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RoomNumber extends Model
{
    use SoftDeletes;
    protected $table = 'room_numbers';

    protected $fillable = [
        'hotel_id',
        'room_number',
        'room_type',
        'status',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class);
    }
}
