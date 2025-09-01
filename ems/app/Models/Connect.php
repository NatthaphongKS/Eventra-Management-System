<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connect extends Model
{
    protected $table = 'ems_connect';

    public $timestamps = false;

    protected $fillable = [
        'con_event_id',
        'con_employee_id',
        'con_answer',
        'con_reason',
        'con_delete_status',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'con_event_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'con_employee_id');
    }
}