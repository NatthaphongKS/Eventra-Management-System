<?php
/*
Model : Team
Edit by : Chitdanai
*/

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Team extends Model
{
    use HasFactory;

    protected $table = 'ems_team';
    public $timestamps = false;

    // ลบ guarded ออก
    // protected $guarded = [];

    protected $fillable = [
        'tm_name',
        'tm_department_id',
        'tm_delete_status',
    ];

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class, 'emp_team_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'tm_department_id');
    }

    public function positions(): HasMany
    {
        return $this->hasMany(Position::class, 'pst_team_id');
    }
}
