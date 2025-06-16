<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;
    protected $table = 'menus';

    protected $fillable = [
        'name',
        'order',
        'parent_id',
        'status',
        'icon',
        'visible_at_web',
        'visible_at_app',
        'slug',
    ];

    public function subMenus()
{
    return $this->hasMany(Menu::class, 'parent_id');
}

}
