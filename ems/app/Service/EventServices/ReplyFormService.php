<?php

namespace App\Service\EventServices;

use App\Models\Connect;
use Illuminate\Support\Facades\DB;

class ReplyFormService
{
    public function reply($eventId, $employeeId, $answer, $reason = null)
    {
        return DB::table('ems_connect')
            ->where('con_event_id', $eventId)
            ->where('con_employee_id', $employeeId)
            ->update([
                'con_answer' => $answer,
                'con_reason' => $reason
            ]);
    }
}
