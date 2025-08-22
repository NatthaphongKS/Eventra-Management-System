<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $table = 'ems_categories';

    protected $fillable = [
        'cat_name',
        'cat_delete_status',
    ];

    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class, 'evn_category_id');
    }
}
