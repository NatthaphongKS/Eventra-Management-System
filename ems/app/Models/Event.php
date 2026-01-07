<?php
/*
Model : Event
Edit by : Chitdanai
*/
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $table = 'ems_event';

    public $timestamps = false;

    protected $fillable = [
        'evn_title',
        'evn_description',
        'evn_category_id',
        'evn_date',
        'evn_timestart',
        'evn_timeend',
        'evn_duration',
        'evn_location',
        'evn_file',
        'evn_create_by',
        'evn_created_at',
        'evn_deleted_at',
        'evn_deleted_by',
        'evn_status',
    ];

    protected $casts = [
        'evn_date' => 'date',
        'evn_timestart' => 'datetime:H:i:s',
        'evn_timeend' => 'datetime:H:i:s',
        'evn_created_at' => 'datetime',
        'evn_deleted_at' => 'datetime',
    ];
    //เชื่อมกับตาราง employee
    public function employees()
    {
        // pivot: ems_connect (con_event_id, con_employee_id)
        return $this->belongsToMany(Employee::class, 'ems_connect', 'con_event_id', 'con_employee_id')
                    ->withPivot(['con_answer','con_reason','con_delete_status']);
    }
    //เชื่อมกับตาราง category
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'evn_category_id');
    }
    //เชื่อมกับตาราง employee
    public function creator(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evn_create_by');
    }
    //เชื่อมกับตาราง employee
    public function deletedBy(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'evn_deleted_by');
    }
    //เชื่อมกับตาราง connect
    public function connects(): HasMany
    {
        return $this->hasMany(Connect::class, 'con_event_id');
    }
    public function files()
    {
        // แก้ไข 'File' เป็นชื่อ Model ของไฟล์ที่คุณใช้งานจริง
        return $this->hasMany(File::class, 'file_event_id');
    }
}
