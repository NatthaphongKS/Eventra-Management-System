<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory;
    protected $guarded = [];
    protected $table = 'ems_team';

    public $timestamps = false;

    protected $fillable = ['tm_name', 'tm_delete_status'];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'emp_team_id');
    }
}