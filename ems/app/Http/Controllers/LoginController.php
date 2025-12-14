<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Employee; // Assuming you have an Employee model

class LoginController extends Controller
{

    public function index()
    {
        return response()->json(['redirect' => '/login']);
    }
    public function login(Request $request)
    {
        //ส่วน validate ๙่องกรอก email password
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]
    );

        $employee = Employee::where('emp_email', $request->email)->first();
        //ส่วน validate กรณี ใส่ email หรือ password ผิด
        if (!$employee || !Hash::check($request->password, $employee->emp_password)) {
            return response()->json(['message' => 'Incorrect username or password. Please try again'], 404);
        }
        //ส่วน validate กรณี ใส่ email มีสถานะ disable
        if ($employee->emp_status === 'disabled') {
            return response()->json(['message' => 'Account can not login'], 403);
        }
        //ส่วน validate กรณี ใส่ account นี้โดนลบ แล้วมีสถานะ inactive
        if ($employee->emp_delete_status === 'inactive') {
            return response()->json(['message' => 'Account is inactive'], 403);
        }

        Auth::login($employee);
        $request->session()->regenerate();

        // ส่ง success กลับ
        return response()->json([
            // 'message' => 'Login success',
            'redirect' => '/employee' // path ใน Vue
        ]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return response()->json(['message' => 'Logout success']);
    }
}
