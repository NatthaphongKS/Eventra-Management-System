<?php

namespace App\Http\Controllers;

use App\Models\Connect;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Event;

class ReplyController extends Controller
{

    // app/Http/Controllers/ReplyController.php
    public function openForm($evn_id, $emp_id)
    {
        return view('reply', ['evnId' => $evn_id, 'empId' => $emp_id]);
    }

    public function show(Request $req)
    {
        $employee = Employee::query()
            ->select(
                'emp_prefix',
                'emp_firstname',
                'emp_lastname',
                'emp_email',
                'emp_phone'
            )
            ->where('id', $req->emp_id)
            ->first();
        $event = Event::query()
            ->Select(
                'evn_title',
                'evn_date',
                'evn_timestart',
                'evn_timeend',
                'evn_location'
            )
            ->where('id', $req->evn_id)
            ->first();
        $connect = Connect::query()
            ->where('con_event_id', $req->evn_id)
            ->where('con_employee_id', $req->emp_id)
            ->first();

        return response()->json(
            [
                'employee' => $employee,
                'event' => $event,
                'connect' => $connect,
            ]
        );
    }


    public function store(Request $req)
    {
        $data = $req->validate([
            'evn_id' => 'required|integer',
            'emp_id' => 'required|integer',
            'attend' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        // เข้าร่วม → ล้างเหตุผล
        if ($data['attend'] === 'accept') {
            $data['reason'] = null;
        }

        // // อัปเดตถ้ามีคู่นี้อยู่แล้ว ไม่มีก็สร้างใหม่
        $updated = Connect::where('con_event_id', $data['evn_id'])
            ->where('con_employee_id', $data['evn_id'])
            ->update([
                'con_answer' => $data['attend'],                   // หรือ $answer
                'con_reason' => $data['reason'],
            ]);
            


        return response()->json(['ok' => true], 201);
    }
}
