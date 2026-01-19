<?php

namespace App\Http\Controllers;

use App\Models\Connect;
use Exception;
use Illuminate\Http\Request;
use App\Models\Employee;
use App\Models\Event;
use Illuminate\Support\Facades\Crypt;

class ReplyController extends Controller
{

    // app/Http/Controllers/ReplyController.php
    public function openForm($encryptURL)
    {
        try {
            $descryptURL = Crypt::decryptString($encryptURL);
            $ids = explode('/', $descryptURL);
            $message = response()->json([
                'success' => true,
                'message' => 'Form can Open'
            ]);
        } catch (Exception $e) {
            $message = response()->json([
                'success' => false,
                'message' => 'Invalid URL'
            ]);
            return view('reply', ['message' => $message]);
        }
        return view('reply', ['message' => $message]);
    }

    public function show($encryptURL)
    {
        try {
            $descryptURL = Crypt::decryptString($encryptURL);
            $ids = explode('/', $descryptURL);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid URL'
            ]);
        }


        $eveId = $ids[0];
        $empId = $ids[1];

        $employee = Employee::query()
            ->select(
                'id'
                ,
                'emp_prefix',
                'emp_firstname',
                'emp_lastname',
                'emp_email',
                'emp_phone'
            )
            ->where('id', $empId)
            ->first();
        $event = Event::query()
            ->Select(
                'id',
                'evn_title',
                'evn_date',
                'evn_timestart',
                'evn_timeend',
                'evn_location'
            )
            ->where('id', $eveId)
            ->first();
        $connect = Connect::query()
            ->where('con_event_id', $eveId)
            ->where('con_employee_id', $empId)
            ->first();

        return response()->json(
            [
                'success' => true,
                'employee' => $employee,
                'event' => $event,
                'connect' => $connect,
            ]
        );
    }


    public function store(Request $req)
    {
        $data = $req->validate([
            'evnID' => 'required|integer',
            'empID' => 'required|integer',
            'attend' => 'required|string',
            'reason' => 'nullable|string|max:500',
        ]);

        // เข้าร่วม → ล้างเหตุผล
        if ($data['attend'] === 'accept') {
            $data['reason'] = null;
        }

        // // อัปเดตถ้ามีคู่นี้อยู่แล้ว ไม่มีก็สร้างใหม่
        $updated = Connect::where('con_event_id', $data['evnID'])
            ->where('con_employee_id', $data['empID'])
            ->update([
                'con_answer' => $data['attend'],                   // หรือ $answer
                'con_reason' => $data['reason'],
            ]);



        return response()->json(['ok' => true], 201);
    }
}
