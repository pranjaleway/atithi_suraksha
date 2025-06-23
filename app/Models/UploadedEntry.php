<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UploadedEntry extends Model
{
    use SoftDeletes;
    protected $table = 'uploaded_entries';

    protected $fillable = [
        'hotel_id',
        'hotel_employee_id',
        'file_path',
    ];

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function hotelEmployee()
    {
        return $this->belongsTo(HotelEmployee::class, 'hotel_employee_id');
    }

}
