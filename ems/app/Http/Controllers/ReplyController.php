<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;

class ReplyController extends Controller
{
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
