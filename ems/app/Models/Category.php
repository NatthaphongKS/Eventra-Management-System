<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'ems_categories';

    protected $fillable = [
        'emc_name',
        'ems_delete_status',
    ];

    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class, 'event_category_id');
    }
}