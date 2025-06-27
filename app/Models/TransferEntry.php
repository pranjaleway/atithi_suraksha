<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TransferEntry extends Model
{
    use SoftDeletes;
    protected $table = 'transfer_entries';

    protected $fillable = [
        'hotel_id',
        'hotel_employee_id',
        'transfer_date',
        'status',
        'transfer_type',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id', 'id');
    }

    public function hotelEmployee()
    {
        return $this->belongsTo(HotelEmployee::class, 'hotel_employee_id', 'id');
    }


}
