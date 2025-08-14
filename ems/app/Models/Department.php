<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'ems_department';

    public $timestamps = false;

    protected $fillable = ['dpm_name', 'dpm_delete_status'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'emp_department_id');
    }
}