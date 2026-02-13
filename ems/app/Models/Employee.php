<?php

/*
Model : Employee
Edit by : Chitdanai
*/
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Employee extends Authenticatable
{
    use Notifiable;

    protected $table = 'ems_employees';
    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'emp_id',
        'emp_company_id',
        'emp_prefix',
        'emp_firstname',
        'emp_lastname',
        'emp_nickname',
        'emp_email',
        'email_verified_at',
        'emp_phone',
        'emp_password',
        'emp_position_id',
        'emp_department_id',
        'emp_team_id',
        'emp_permission',
        'emp_created_at',
        'emp_create_by',
        'emp_delete_status',
        'emp_delete_by',
        'emp_deleted_at',
    ];

    protected $hidden = [
        'emp_password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    //เชื่อมกับตาราง company
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'emp_company_id');
    }
    //เชื่อมกับตาราง employee
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'emp_create_by');
    }
    //เชื่อมกับตาราง event
    public function events()
    {
        return $this->belongsToMany(Event::class, 'ems_connect', 'con_employee_id', 'con_event_id')
            ->withPivot(['con_answer', 'con_reason', 'con_delete_status']);
    }
    //เชื่อมกับตาราง position
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'emp_position_id');
    }
    //เชื่อมกับตาราง department
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'emp_department_id');
    }
//เชื่อมกับตาราง team
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'emp_team_id');
    }
    public function getAuthPassword()
    {
        return $this->emp_password;
    }
}
