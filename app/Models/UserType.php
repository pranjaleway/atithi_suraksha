<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserType extends Model
{
    use SoftDeletes;
    protected $table = 'user_types';

    protected $fillable = [
        'user_type', 'status'
    ];
}
