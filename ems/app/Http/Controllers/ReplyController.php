<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Event;

class ReplyController extends Controller
{
    public function show(Request $req)
    {
        $employee = Employee::find($req->emp_id)
        ->Select(
            'emp_firstname',
            'emp_lastname',
            'emp_email',
            'emp_phone'
        );
        

        $event = Event::find($req->evn_id)
        ->Select(

        );

        return respone()->json([
            'employee' => $employee,
            'event' => $event,
        ]);
    }


    public function store(Request $req)
    {
        $data = $req->validate([
            'fullname' => 'required|string|max:255',
            'email'    => 'required|email',
            'phone'    => 'required|string|max:30',
            'attend'   => 'required|boolean',
            'reason'   => 'nullable|string|max:500',
        ]);

        
        // TODO: บันทึกลง DB หรือส่งอีเมลแจ้งเตือน
        return response()->json(['ok' => true]);
    }
}
