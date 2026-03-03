<?php

/**
 * ชื่อไฟล์: Employee.php
 * คำอธิบาย: Model สำหรับจัดการข้อมูลพนักงาน (ems_employees)
 * ชื่อผู้เขียน/แก้ไข: Thanusin leenarat
 * วันที่จัดทำ/แก้ไข: 23 กุมภาพันธ์ 2569
 */

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;


class Employee extends Authenticatable
{
    use Notifiable, HasFactory;

    protected $table = 'ems_employees';

    protected $primaryKey = 'id';

    public $timestamps = false;

    /*
    |--------------------------------------------------------------------------
    | Constants
    |--------------------------------------------------------------------------
    */

    public const PREFIXES = [
        1 => 'นาย',
        2 => 'นาง',
        3 => 'นางสาว',
    ];

    /*
    |--------------------------------------------------------------------------
    | Mass Assignment
    |--------------------------------------------------------------------------
    */

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
        'emp_deleted_at' => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------------
    | Authentication
    |--------------------------------------------------------------------------
    */

    public function getAuthPassword(): ?string
    {
        return $this->emp_password;
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopeActive(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->where('emp_delete_status', 'active')
                ->orWhereNull('emp_delete_status')
                ->orWhere('emp_delete_status', '');
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute(): string
    {
        $prefix = self::PREFIXES[$this->emp_prefix] ?? '';

        return trim($prefix . $this->emp_firstname . ' ' . $this->emp_lastname);
    }

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'emp_company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(self::class, 'emp_create_by');
    }

    public function deleter(): BelongsTo
    {
        return $this->belongsTo(self::class, 'emp_delete_by');
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class, 'emp_position_id');
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'emp_department_id');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'emp_team_id');
    }

    public function events()
    {
        return $this->belongsToMany(
            Event::class,
            'ems_connect',
            'con_employee_id',
            'con_event_id'
        )->withPivot([
                    'con_answer',
                    'con_reason',
                    'con_delete_status',
                ]);
    }
}
