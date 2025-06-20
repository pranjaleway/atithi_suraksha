<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelEmployeeDoc extends Model
{
    protected $table = 'hotel_employee_docs';

    protected $fillable = [
        'hotel_employee_id',
        'document_id',
        'document_path',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function hotelEmployee()
    {
        return $this->belongsTo(HotelEmployee::class, 'hotel_employee_id');
    }
}
