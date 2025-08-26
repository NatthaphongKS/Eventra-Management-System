<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = 'ems_company';
    public $timestamps = false;
    protected $fillable = [
        'com_name',
        'com_delete_status',
    ];
}
