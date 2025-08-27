<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;


class Category extends Model
{
    protected $table = 'ems_categories';

    protected $fillable = [
        'cat_name',
        'cat_deleted_status',
        'cat_created_at',
        'cat_created_by',
        'cat_deleted_at',
        'cat_deleted_by',

    ];

    public $timestamps = false;

    public function events()
    {
        return $this->hasMany(Event::class, 'evn_category_id');
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
