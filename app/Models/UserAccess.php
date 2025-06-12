<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAccess extends Model
{
    protected $table = 'user_accesses';

    protected $fillable = [
        'user_type_id',
        'menu_id',
        'view',
        'add',
        'edit',
        'delete',
        'status',
    ];

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
