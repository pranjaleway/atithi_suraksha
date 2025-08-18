<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes;
    protected $table = 'notifications';

    protected $fillable = [
        'user_id',
        'title',
        'message',
        'image',
        'sp_id',
        'police_station_id',
        'is_read',
        'read_by',
        'deleted_by',
        'status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
