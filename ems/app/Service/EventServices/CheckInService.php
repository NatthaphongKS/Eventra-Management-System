<?php

namespace App\Service\EventServices;

use App\Models\Connect;
use Illuminate\Support\Facades\DB;

class CheckInService
{
    public function checkin($eventId, $employeeId)
    {
        return DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where('con_employee_id', $employeeId)
            ->update(['con_checkin_status' => 'checked_in']);
    }
}
