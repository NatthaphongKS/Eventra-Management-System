<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connect extends Model
{
    protected $table = 'ems_connect';

    public $timestamps = false;

    protected $fillable = [
        'ecn_event_id',
        'ecn_employee_id',
        'ecn_answer',
        'ecn_reason',
        'ecn_delete_status',
    ];

    protected $casts = [ 'ecn_answer' => 'boolean' ];

    public function event()
    {
        return $this->belongsTo(Event::class, 'ecn_event_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'ecn_employee_id');
    }
}