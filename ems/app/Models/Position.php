<?php
/*
Model : Position
Edit by : Chitdanai
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use HasFactory;

    protected $table = 'ems_position';
    public $timestamps = false;

    protected $fillable = [
        'pst_name',
        'pst_team_id',
        'pst_delete_status',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'emp_position_id');
    }
}
