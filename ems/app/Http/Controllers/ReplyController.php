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
        $event =
            Event::query()
            ->Select(
                'evn_title',
                'evn_date',
                'evn_timestart',
                'evn_timeend',
                'evn_location'
            )
            ->where('id', $req->evn_id)
            ->first();
        return response()->json(
            [
                'employee' => $employee,
                'event' => $event,
            ]
        );
    }


    public function store(Request $req)
    {
        dd($req->all());
        // แปลง/ทำความสะอาด
        $req->merge([
            'attend' => $req->boolean('attend'),
            'reason' => is_string($req->reason) ? trim($req->reason) : $req->reason,
        ]);

        // ถ้าคีย์ของ events/employees เป็น id จริง ใช้แบบนี้ได้เลย
        $data = $req->validate([
            'evn_id' => 'required|integer|exists:events,id',
            'emp_id' => 'required|integer|exists:employees,id',
            'attend' => 'required|boolean',
            'reason' => 'nullable|string|max:500',
        ]);

        // เข้าร่วม → ล้างเหตุผล
        if ($data['attend'] === true) {
            $data['reason'] = null;
        }

        // อัปเดตถ้ามีคู่นี้อยู่แล้ว ไม่มีก็สร้างใหม่
        Connect::updateOrCreate(
            ['ecn_event_id' => $data['evn_id'], 'ecn_employee_id' => $data['emp_id']],
            [
                'ecn_answer' => 'accept',
                'ecn_reason' => null,
            ]
        );

        return response()->json(['ok' => true], 201);
    }
}
