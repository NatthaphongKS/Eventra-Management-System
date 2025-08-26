<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $table = 'ems_categories';

    protected $fillable = [
        'emc_name',
        'ems_delete_status',
        'ems_create_at',
        'ems_create_by',
        'ems_delete_at',
        'ems_delete_by',

    ];

    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class, 'event_category_id');
    }
    public function creator()
    {
        return $this->belongsTo(Employee::class, 'cat_created_by');
    }
    public function deleter()
    {
        return $this->belongsTo(Employee::class, 'cat_deleted_by');
    }
}