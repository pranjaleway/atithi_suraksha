<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelOwnerDoc extends Model
{
    protected $table = 'hotel_owner_docs';

    protected $fillable = [
        'hotel_id',
        'document_id',
        'document_path',
    ];

    public function document()
    {
        return $this->belongsTo(Document::class, 'document_id');
    }

    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
}
